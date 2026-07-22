<?php
/**
 * DataCrudTrait — Core CRUD operations for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: set_prev_form_data(), get_prev_form_data(), edit_data(), add_data(),
 *          delete_data(), mass_delete_data()
 */
trait DataCrudTrait
{
    function set_prev_form_data( $form_data ) {
        $this->prev_form_data = $form_data;
    }

    function get_prev_form_data() {
        return $this->prev_form_data;
    }

    /**
     * Инвалидирует кэш счётчиков re_data (ключи data_cnt_*).
     * Вызывается после любого изменения данных в таблице.
     */
    protected function _invalidate_data_counts_cache(): void
    {
        $DBC = DBC::getInstance();
        $DBC->query("DELETE FROM " . DB_PREFIX . "_cache WHERE `parameter` LIKE 'data\\_cnt\\_\%'");
    }


    /**
     * Edit data
     * @param array $form_data form data
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {


        $id = intval($this->getRequestValue('id'));
        $this->set_prev_form_data($this->load_by_id($id));

        $need_send_message = 0;
        $status_changed = false;

        if (isset($form_data['tmp_password']) && $form_data['tmp_password']['value'] == '') {
            $form_data['tmp_password']['value'] = substr(md5(time()), 1, 6);
        }

        if (isset($form_data['price'])) {
            $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);
        }

        $DBC = DBC::getInstance();

        if ($this->getConfigValue('apps.billing.enable') == 1) {
            if (isset($form_data['vip_status_end']) && isset($form_data['premium_status_end']) && isset($form_data['bold_status_end'])) {
                $current_vip_status_end = 0;
                $current_premium_status_end = 0;
                $current_bold_status_end = 0;
                $q = 'SELECT vip_status_end, premium_status_end, bold_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($q, array($id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_vip_status_end = (int) $ar['vip_status_end'];
                    $current_premium_status_end = (int) $ar['premium_status_end'];
                    $current_bold_status_end = (int) $ar['bold_status_end'];
                }
                $new_vip_date = $this->prepareVipStatsDateValue($current_vip_status_end, $form_data['vip_status_end']['value']);
                if ($new_vip_date === FALSE) {
                    unset($form_data['vip_status_end']);
                } else {
                    $form_data['vip_status_end']['value'] = $new_vip_date;
                }
                $new_premium_date = $this->prepareVipStatsDateValue($current_premium_status_end, $form_data['premium_status_end']['value']);
                if ($new_premium_date === FALSE) {
                    unset($form_data['premium_status_end']);
                } else {
                    $form_data['premium_status_end']['value'] = $new_premium_date;
                }

                $new_bold_date = $this->prepareVipStatsDateValue($current_bold_status_end, $form_data['bold_status_end']['value']);
                if ($new_bold_date === FALSE) {
                    unset($form_data['bold_status_end']);
                } else {
                    $form_data['bold_status_end']['value'] = $new_bold_date;
                }
            } elseif (isset($form_data['vip_status_end'])) {
                $current_vip_status_end = 0;
                $q = 'SELECT vip_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';

                $stmt = $DBC->query($q, array((int) $this->getRequestValue('id')));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_vip_status_end = (int) $ar['vip_status_end'];
                }

                $new_vip_date = $this->prepareVipStatsDateValue($current_vip_status_end, $form_data['vip_status_end']['value']);
                if ($new_vip_date === FALSE) {
                    unset($form_data['vip_status_end']);
                } else {
                    $form_data['vip_status_end']['value'] = $new_vip_date;
                }
            } elseif (isset($form_data['bold_status_end'])) {
                $current_bold_status_end = 0;
                $q = 'SELECT bold_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($q, array((int) $this->getRequestValue('id')));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_bold_status_end = (int) $ar['bold_status_end'];
                }

                $new_bold_date = $this->prepareVipStatsDateValue($current_bold_status_end, $form_data['bold_status_end']['value']);
                if ($new_bold_date === FALSE) {
                    unset($form_data['bold_status_end']);
                } else {
                    $form_data['bold_status_end']['value'] = $new_bold_date;
                }
            } elseif (isset($form_data['premium_status_end'])) {
                $current_premium_status_end = 0;
                $q = 'SELECT premium_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($q, array((int) $this->getRequestValue('id')));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_premium_status_end = (int) $ar['premium_status_end'];
                }

                $new_premium_date = $this->prepareVipStatsDateValue($current_premium_status_end, $form_data['premium_status_end']['value']);
                if ($new_premium_date === FALSE) {
                    unset($form_data['premium_status_end']);
                } else {
                    $form_data['premium_status_end']['value'] = $new_premium_date;
                }
            }
        } else {
            unset($form_data['premium_status_end']);
            unset($form_data['bold_status_end']);
            unset($form_data['vip_status_end']);
        }

        if (1 === (int) $this->getConfigValue('notify_about_publishing') || 1 === (int) $this->getConfigValue('apps.twitter.enable')) {
            $query = 'SELECT active, hot FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';

            $stmt = $DBC->query($query, array((int) $this->getRequestValue('id')));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_active_status = $ar['active'];
                $current_hot_status = $ar['hot'];
            }
        }

        if (isset($form_data['status_id'])) {
            $current_status_id = 0;
            $query = 'SELECT status_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_status_id = intval($ar['status_id']);
            }

            if ($current_status_id !== intval($form_data['status_id']['value'])) {
                $status_changed = true;
            }
        }

        if ($this->getConfigValue('notify_about_publishing')) {

            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                $need_send_message = 1;
            }
            if ($current_hot_status == 1 AND $form_data['hot']['value'] == 0) {
                $need_send_message = 1;
            }

            if ($need_send_message == 1) {
                $n_id = $id;
                $n_pass = @$form_data['tmp_password']['value'];
                $n_email = @$form_data['email']['value'];
                $n_phone = @$form_data['phone']['value'];
                $n_fio = @$form_data['fio']['value'];

                $user_id = @$form_data['user_id']['value'];
                if ($user_id > 0) {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT email, phone, user_id, fio, group_id, login FROM ' . DB_PREFIX . '_user WHERE user_id=?';
                    $stmt = $DBC->query($query, array($user_id));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        if ($ar['login'] != '_unregistered') {
                            $n_pass = $form_data['tmp_password']['value'];
                            $n_email = $ar['email'];
                            $n_phone = $ar['phone'];
                            $n_fio = $ar['fio'];
                        }
                    }
                }
            }
        }

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $form_data[$this->primary_key]['value'], $form_data);
        $query_params_vals = $query_params['p'];
        $query = $query_params['q'];
        //$this->writeArrayLog($query_params);


        $stmt = $DBC->query($query, $query_params_vals, $rows, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $ims = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $id);
                if (is_array($ims) && count($ims) > 0 && 0 == intval($form_item['parameters']['no_watermark'])) {
                    $imgs = array_merge($imgs, $ims);
                }
            } elseif ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $id);
            } elseif ($form_item['type'] == 'select_by_query_multi') {
                //echo 1;
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $vals;
                }
                $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $id));
                //echo $DBC->getLastError();
                if (!empty($vals)) {
                    //refresh
                    $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $id, $val));
                    }
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', $this->primary_key, $id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }

        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($id);
        }

        if ($status_changed) {
            $this->setStatusDate($id);
        }

        /* Send notify messages */
        if ($need_send_message) {
            if ($n_email != '') {
                $this->notifyEmailAboutActivation($n_id, $n_email, array('fio' => $n_fio));
            } elseif ($n_phone != '' and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php')) {
                $body = $this->getConfigValue('apps.fasteditor.sms_send_password_text_long');
                $body = str_replace('{password}', $n_pass, $body);
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php');
                $SMSSender = new sms_admin();
                if ($SMSSender->send($n_phone, $body)) {

                } else {

                }
            }
        }

        /* Add twit */

        if ($this->getConfigValue('apps.twitter.enable')) {
            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($this->getRequestValue('id'));
            }
        }

        if ($this->getConfigValue('apps.telegram.enable')) {
            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/telegram/admin/admin.php';
                $Telegram = new telegram_admin();
                $Telegram->sendPost($this->getRequestValue('id'));
            }
        }

        if ($this->getConfigValue('apps.facebook.enable')) {
            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/facebook/admin/admin.php';
                $Facebook = new facebook_admin();
                $Facebook->sendPost($this->getRequestValue('id'));
            }
        }



        if ($this->getConfigValue('is_watermark')) {
            $this->do_watermark($imgs);
        }

        // Обработка handler
        $this->tryHandlers('data', 'edit_data', $form_data, $id);
        // Установка даты обновления (если есть поле updated_at)
        if ( isset($form_data['updated_at']) and $form_data['updated_at']['type'] == 'dtdatetime' ) {
            $data_record = \system\lib\model\eloquent\Data::where('id', $id)->first();
            if ( $data_record ) {
                $data_record->updated_at = date('Y-m-d H:i:s');
                $data_record->save();
            }
        }

        $page = $this->getRequestValue('page');
        $_POST = array();
        $_POST['page'] = $page;
        $this->_invalidate_data_counts_cache();
        return $id;
    }

    /**
     * Add data
     * @param array $form_data form data
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {

        $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);
        //$form_data['date_added']['value'] = date('Y-m-d H:i:s', time());

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $queryp = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data, $language_id);

        $DBC = DBC::getInstance();

        $stmt = $DBC->query($queryp['q'], $queryp['p'], $row, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        $new_record_id = $DBC->lastInsertId();

        if ($new_record_id > 0 && isset($form_data['status_id'])) {
            $this->setStatusDate($new_record_id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $ims = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                if (is_array($ims) && count($ims) > 0 && 0 == intval($form_item['parameters']['no_watermark'])) {
                    $imgs = array_merge($imgs, $ims);
                }
            } elseif ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
            } elseif ($form_item['type'] == 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $vals;
                }
                $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id));
                //echo $DBC->getLastError();
                if (!empty($vals)) {
                    //refresh
                    $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id, $val));
                    }
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', 'id', $new_record_id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }
        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($new_record_id);
        }

        if ($this->getConfigValue('is_watermark')) {
            $this->do_watermark($imgs);
        }

        $messenger_post = false;
        $DBC = DBC::getInstance();
        $query = 'SELECT `active` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($new_record_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['active'] == 1) {
                $messenger_post = true;
            }
        }


        if ($this->getConfigValue('apps.twitter.enable')) {
            if ($messenger_post) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($new_record_id);
            }
        }
        if ($this->getConfigValue('apps.telegram.enable')) {
            if ($messenger_post) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/telegram/admin/admin.php';
                $Telegram = new telegram_admin();
                $Telegram->sendPost($new_record_id);
            }
        }
        if ($this->getConfigValue('apps.facebook.enable')) {
            if ($messenger_post) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/facebook/admin/admin.php';
                $Facebook = new facebook_admin();
                $Facebook->sendPost($new_record_id);
            }
        }

        // Обработка handler
        $this->tryHandlers('data', 'edit_data', $form_data, $new_record_id);

        $page = $this->getRequestValue('page');
        $_POST = array();
        $_POST['page'] = $page;
        $this->_invalidate_data_counts_cache();
        return $new_record_id;
    }

    function mass_delete_data($table_name, $primary_key, $ids) {
        $errors = '';
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $cuser_id = (int) $_SESSION['user_id_value'];
            if (count($ids) > 0) {
                foreach ($ids as $k => $id) {
                    if (!$this->checkOwning($id, $cuser_id)) {
                        unset($ids[$k]);
                    }
                }
            }
        }

        if (count($ids) > 0) {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {

                $archived_yet = array();
                $DBC = DBC::getInstance();
                //Получаем список ID объектов, которые уже пребывают в архивном состоянии
                $ids = array_map('intval', $ids);
                $query = 'SELECT `id` FROM ' . DB_PREFIX . '_data WHERE `id` IN (' . implode(',', $ids) . ') AND `archived` = 1';
                $stmt = $DBC->query($query);
                if($stmt){
                    while($ar = $DBC->fetch($stmt)){
                        $archived_yet[] = $ar['id'];
                    }
                }
                //Отбираем объекты, которые нужно перенести в архив
                $to_archive = array_diff($ids, $archived_yet);

                if(!empty($to_archive)){
                    $to_archive = array_map('intval', $to_archive);
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET archived=1 WHERE `id` IN (' . implode(',', $to_archive) . ')';
                    $stmt = $DBC->query($query);
                    $this->_invalidate_data_counts_cache();
                }
                //Отправляем на удаление объекты, которые и так находились в архиве
                $ids = $archived_yet;
            }
        }

        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $log_id = false;
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $log_id = $Logger->addLog($id, $_SESSION['user_id_value'], 'delete', $this->table_name, $this->primary_key);
                }
                $this->enable_shard_queue();
                $this->delete_data($this->table_name, $this->primary_key, $id);
                if ($this->getError()) {
                    if ($log_id !== false) {
                        $Logger->deleteLog($log_id);
                    }
                    $errors .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ' ID=' . $id . ': ' . $this->GetErrorMessage() . '<br>';
                    $errors .= '</div>';
                    $this->error_message = false;
                }
            }
            $this->run_shard_task();
            if ($errors != '') {
                $rs .= $errors . '<div align="center"><a href="?action=' . $this->action . '">ОК</a></div>';
            } else {
                header('location: ?action=' . $this->action);
                exit();
                $rs .= $this->grid($user_id);
            }
            return $rs;
        }

    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        $DBC = DBC::getInstance();
        $imgs_ids = array();
        $query = 'SELECT image_id FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE ' . $primary_key . '=?';

        $stmt = $DBC->query($query, array($primary_key_value));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $imgs_ids[] = $ar['image_id'];
            }
        }
        $form_data = $this->load_by_id($primary_key_value);

        $delete_result = parent::delete_data($table_name, $primary_key, $primary_key_value);
        if ($delete_result) {
            if (count($imgs_ids) > 0) {
                foreach ($imgs_ids as $im) {
                    $this->deleteImage($table_name, $im);
                }
            }
            $query = 'DELETE FROM ' . DB_PREFIX . '_memorylist_item WHERE `id` = ?';
            $stmt = $DBC->query($query, array($primary_key_value));

            $query = 'DELETE FROM ' . DB_PREFIX . '_userlists WHERE `id` = ? AND `lcode` = ?';
            $stmt = $DBC->query($query, array($primary_key_value, 'fav'));
            // Обработка handler
            $this->tryHandlers('data', 'delete_data', $form_data, $primary_key_value);
        }
        $this->_invalidate_data_counts_cache();
        return $delete_result;
    }
}
