<?php
/**
 * UserDataCrudTrait — CRUD operations extracted from User_Data_Manager.
 *
 * Methods: add_data, edit_data, check_data, mass_delete_data
 */
trait UserDataCrudTrait
{
    /**
     * Add data
     * @param array $form_data form data
     * @return boolean
     */
    function add_data($form_data, $language_id = 0)
    {

        $curator_id = 0;
        $user_id = (int)$_SESSION['user_id'];

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($user_id);

        $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);

        //check balance and cost of service
        $need_money = 0;
        if ($this->getConfigValue('advert_cost') > 0) {
            $need_money += $this->getConfigValue('advert_cost');
        }
        if ($this->getConfigValue('special_advert_cost') > 0 and $form_data['hot']['value'] == 1) {
            $need_money += $this->getConfigValue('special_advert_cost');
        }

        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
                $billing = new Billing();

                $need_money += $billing->getAdvAbonentPayment($_SESSION['user_id']);
            }
        }

        if ($user_balance < $need_money) {
            $this->riseError('Недостаточно средств на счете для операции. <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill_done&bill=' . $need_money . '">Пополнить баланс на ' . $need_money . ' ' . $this->getConfigValue('ue_name') . '</a>');
            return false;
        }


        $moderation_mode = false;
        if (1 == $this->getConfigValue('moderate_first')) {
            $moderation_mode = true;
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT free_from_moderation FROM ' . DB_PREFIX . '_user WHERE user_id=?';
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['free_from_moderation'] == 1) {
                $moderation_mode = false;
            }
        }

        if ($moderation_mode) {
            if (isset($form_data['active'])) {
                $form_data['active']['value'] = 0;
            }
        }

        if (1 === (int)$this->getConfigValue('enable_curator_mode') && 0 === (int)$this->getConfigValue('curator_mode_fullaccess')) {
            $query = 'SELECT parent_user_id FROM ' . DB_PREFIX . '_user WHERE user_id=?';
            $stmt = $DBC->query($query, array($user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ((int)$ar['parent_user_id'] > 0) {
                    $curator_id = (int)$ar['parent_user_id'];
                }
            }
        }


        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        $queryp = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data);

        $DBC = DBC::getInstance();

        $stmt = $DBC->query($queryp['q'], $queryp['p'], $row, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        $new_record_id = $DBC->lastInsertId();

        if ($moderation_mode) {
            $this->notifyAboutModerationNeed($new_record_id, 'new');
        }

        if ($new_record_id > 0) {
            $this->setUpdatedAtDate($new_record_id);
        }

        if ($curator_id > 0) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/cowork/cowork.php';
            $CW = new Cowork();
            $CW->setCoworkerToObject($this->table_name, $new_record_id, $curator_id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] === 'uploads') {
                $ims = $this->appendUploads('data', $form_item, 'id', $new_record_id);
                if (is_array($ims) && count($ims) > 0) {
                    $imgs = array_merge($imgs, $ims);
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', 'id', $new_record_id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }

        foreach ($form_data as $form_item) {
            if ($form_item['type'] === 'docuploads') {
                $imgs_uploads = $this->appendDocUploads('data', $form_item, 'id', $new_record_id);
            }
        }

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if ($form_item['type'] === 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array)$mutiitems[$k];
                }
                if (!empty($vals)) {
                    $mutiitems[$k] = $vals;
                } else {
                    $mutiitems[$k] = array();
                }
            }
        }

        if (!empty($mutiitems)) {
            $keys = array_keys($mutiitems);

            $params = array();
            $params[] = 'data';
            $params = array_merge($params, $keys);
            $params[] = $new_record_id;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($keys), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);

            $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
            foreach ($mutiitems as $key => $vals) {
                if (!empty($vals)) {
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array('data', $key, $new_record_id, $val));
                        //echo $DBC->getLastError();
                    }
                }
            }
        }

        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($new_record_id);
        }

        if ($this->getConfigValue('is_watermark')) {
            $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
            $Watermark = $this->createWatermarkInstance(true);

            if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                $copy_folder = MEDIA_FOLDER . '/nowatermark/';
                if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                    $foldeformat = 'Ymd';
                } else {
                    $foldeformat = 'Ym';
                }
                $folder_name = date($foldeformat, time());
                $locs = $copy_folder . '/' . $folder_name;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . '/' . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                    }
                }
            } else {
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark($filespath . $v['normal']);
                    }
                }
            }
        } else {

        }

        /* if (!$moderation_mode) {
          $this->notifyAboutNewAdvert($new_record_id);
          } */

        if ($new_record_id > 0) {
            if ($this->getConfigValue('advert_cost') > 0) {
                $account->minusMoney($user_id, $this->getConfigValue('advert_cost'));
            }
            if ($this->getConfigValue('special_advert_cost') > 0 and $form_data['hot']['value'] == 1) {
                $account->minusMoney($user_id, $this->getConfigValue('special_advert_cost'));
            }

            if ($this->getConfigValue('apps.twitter.enable') && 1 == (int)$this->getConfigValue('apps.twitter.allow_posting_from_account')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($new_record_id);
            }
            if ($this->getConfigValue('apps.telegram.enable') && 1 == (int)$this->getConfigValue('apps.telegram.allow_posting_from_account')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/telegram/admin/admin.php';
                $Telegram = new telegram_admin();
                $Telegram->sendPost($new_record_id);
            }
        }
        return $new_record_id;
    }

    /**
     * Edit data
     * @param array $form_data form data
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false)
    {
        $id = (int)$this->getRequestValue('id');
        $user_id = (int)$_SESSION['user_id'];
        if ($id == 0) {
            return false;
        }
        $status_changed = false;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($user_id);

        if (isset($form_data['price'])) {
            $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);
        }


        $form_data_tmp = $form_data;

        //get prev state
        $form_data_tmp = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_tmp);
        //if prev hot is 0 and new is 1, calculate money
        if ($form_data_tmp['hot']['value'] == 0 and $form_data['hot']['value'] == 1) {
            if ($user_balance < $this->getConfigValue('special_advert_cost')) {
                $this->riseError('Недостаточно средств на счете для операции. <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill_done&bill=' . $this->getConfigValue('special_advert_cost') . '">Пополнить баланс на ' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</a>');
                return false;
            } else {
                $account->minusMoney($user_id, $this->getConfigValue('special_advert_cost'));
            }
        }
        if (isset($form_data['active']) && ($form_data_tmp['active']['value'] == 0 and $form_data['active']['value'] == 1)) {
            if (!$this->checkAdvAbonent($id)) {
                $this->riseError('Вы не можете изменить статус активности');
                return;
            } else {
                $this->setAdvAbonent($id);
            }

        }

        $moderation_mode = false;
        if (1 == $this->getConfigValue('moderate_first')) {
            $moderation_mode = true;
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT free_from_moderation FROM ' . DB_PREFIX . '_user WHERE user_id=?';
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['free_from_moderation'] == 1) {
                $moderation_mode = false;
            }
        }

        if ($moderation_mode) {
            $form_data['active']['value'] = 0;
        }

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        if (isset($form_data['status_id'])) {
            $current_status_id = 0;
            $DBC = DBC::getInstance();
            $query = 'SELECT status_id FROM ' . DB_PREFIX . '_data WHERE `id`=?';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_status_id = intval($ar['status_id']);
            }

            if ($current_status_id !== intval($form_data['status_id']['value'])) {
                $status_changed = true;
            }
        }

        $queryp = $data_model->get_prepared_edit_query(DB_PREFIX . '_data', 'id', $id, $form_data);
        $DBC = DBC::getInstance();

        $row = 0;
        $success_mark = false;
        $stmt = $DBC->query($queryp['q'], $queryp['p'], $rows, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return;
        }


        if ($moderation_mode) {
            $this->notifyAboutModerationNeed($id, 'edit');
        }

        if ($success_mark && $status_changed) {
            $this->setStatusDate($id);
        }

        if ($success_mark && (0 === intval($this->getConfigValue('apps.billing.enable')) || (1 === intval($this->getConfigValue('apps.billing.enable')) && 0 === intval($this->getConfigValue('apps.upper.enable'))))) {
            $this->setUpdatedAtDate($id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] === 'uploads') {
                $ims = $this->appendUploads('data', $form_item, 'id', $id);
                if (is_array($ims) && count($ims) > 0) {
                    $imgs = array_merge($imgs, $ims);
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', 'id', $id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }

        foreach ($form_data as $form_item) {
            if ($form_item['type'] === 'docuploads') {
                $imgs_uploads = $this->appendDocUploads('data', $form_item, 'id', $id);
            }
        }

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if ($form_item['type'] === 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array)$mutiitems[$k];
                }
                if (!empty($vals)) {
                    $mutiitems[$k] = $vals;
                } else {
                    $mutiitems[$k] = array();
                }
            }
        }

        if (!empty($mutiitems)) {
            $keys = array_keys($mutiitems);

            $params = array();
            $params[] = 'data';
            $params = array_merge($params, $keys);
            $params[] = $id;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($keys), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);

            $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
            foreach ($mutiitems as $key => $vals) {
                if (!empty($vals)) {
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array('data', $key, $id, $val));
                        //echo $DBC->getLastError();
                    }
                }
            }
        }


        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($id);
        }

        if ($this->getConfigValue('is_watermark')) {
            $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
            $Watermark = $this->createWatermarkInstance(true);
            if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                $copy_folder = MEDIA_FOLDER . '/nowatermark/';
                if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                    $foldeformat = 'Ymd';
                } else {
                    $foldeformat = 'Ym';
                }
                $folder_name = date($foldeformat, time());
                $locs = $copy_folder . '/' . $folder_name;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . '/' . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                    }
                }
            } else {
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark($filespath . $v['normal']);
                    }
                }
            }
        } else {

        }
    }

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        if (!$data_model->check_data($form_data)) {
            $this->riseError($data_model->GetErrorMessage());
            return false;
        }
        if ($this->getConfigValue('apps.billing.enable')) {

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
            $Account = new Account;
            $company_profile = $Account->get_company_profile($form_data['user_id']['value']);

            if ($company_profile['limit_special_left']['value'] < 1 and $form_data['hot']['value'] == 1) {
                $this->riseError('Превышен лимит спецпредложений');
                return false;
            }
            if ($company_profile['limit_best_left']['value'] < 1 and $form_data['best']['value'] == 1) {
                $this->riseError('Превышен лимит лучших предложений');
                return false;
            }
        }
        return true;
    }

    function mass_delete_data($table_name, $primary_key, $ids)
    {
        $rs = '';
        $cuser_id = (int)$_SESSION['user_id'];

        if ($cuser_id == 0) {
            return '';
        }
        $errors = '';

        if (count($ids) > 0) {
            foreach ($ids as $k => $id) {
                if (!$this->check_access_to_data($cuser_id, $id)) {
                    unset($ids[$k]);
                }
            }
        }

        if (count($ids) > 0) {
            if (1 === (int)$this->getConfigValue('apps.realty.use_predeleting')) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET archived=1 WHERE `id` IN (' . implode(',', $ids) . ')';
                $DBC->query($query);
                header('location: ' . SITEBILL_MAIN_URL . '/account/data/');
                exit();
            } else {
                foreach ($ids as $id) {
                    $log_id = false;
                    if ($this->getConfigValue('apps.realtylogv2.enable')) {
                        require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                        $Logger = new realtylogv2_admin();
                        $log_id = $Logger->addLog($id, $cuser_id, 'delete', $table_name, $primary_key);
                    }
                    $this->delete_data($table_name, $primary_key, $id);
                    if ($this->getError()) {
                        if ($log_id !== false) {
                            $Logger->deleteLog($log_id);
                        }
                        $errors .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ' ID=' . $id . ': ' . $this->GetErrorMessage() . '<br>';
                        $errors .= '</div>';
                        $this->error_message = false;
                    }
                }
                if ($errors != '') {
                    $rs .= $errors . '<div align="center"><a href="' . SITEBILL_MAIN_URL . '/accoutn/data/">ОК</a></div>';
                } else {
                    header('location: ' . SITEBILL_MAIN_URL . '/account/data/');
                    exit();
                }
                return $rs;
            }
            return $rs;
        }
    }
}
