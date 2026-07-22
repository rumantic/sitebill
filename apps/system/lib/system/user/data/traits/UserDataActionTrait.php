<?php
/**
 * UserDataActionTrait — action handler methods extracted from User_Data_Manager.
 *
 * Methods: _upAction, _edit_doneAction, _editAction, _new_doneAction,
 *          _deleteAction, _newAction, _defaultAction
 */
trait UserDataActionTrait
{
    protected function _upAction()
    {
        $user_id = (int)$_SESSION['user_id'];
        $id = (int)$this->getRequestValue('id');
        $rs = '';
        if (!$this->check_access_to_data($user_id, $id)) {
            $rs = Multilanguage::_('L_ACCESS_DENIED');
        }

        if ($this->getConfigValue('apps.billing.enable')) {
            $DBC = DBC::getInstance();
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
            $Account = new Account;
            if ($this->getConfigValue('apps.company.enable')) {
                $company_profile = $Account->get_company_profile($user_id);
                if ($company_profile['limit_up']['value'] < 1) {
                    $rs = 'Превышен лимит поднятий объявлений.';
                    return $rs;
                }
            }
            //get max id from DB
            $query = 'SELECT max(id) AS mid FROM ' . DB_PREFIX . '_data';
            $stmt = $DBC->query($query);
            $ar = $DBC->fetch($stmt);
            $mid = (int)$ar['mid'];
            $mid++;

            $tryupdate = $this->setUpdatedAtDate($id);
            if (!$tryupdate) {
                $query = 'UPDATE ' . DB_PREFIX . '_data SET date_added=?, id=? WHERE id=?';
                $stmt = $DBC->query($query, array(date('Y-m-d H:i:s'), $mid, $id));
                $query = 'UPDATE ' . DB_PREFIX . '_data_image SET id=? WHERE id=?';
                $stmt = $DBC->query($query, array($mid, $id));
            }


            //minus point from company.limit_up
            $new_limit_up = $company_profile['limit_up']['value'] - 1;
            $query = 'UPDATE ' . DB_PREFIX . '_company SET limit_up=? WHERE company_id=?';
            $stmt = $DBC->query($query, array($new_limit_up, $company_profile['company_id']['value']));

            $rs .= $this->grid_e($user_id, $this->getRequestValue('topic_id'));
        }
        return $rs;
    }

    protected function _edit_doneAction()
    {
        $user_id = (int)$_SESSION['user_id'];
        $id = (int)$this->getRequestValue('id');

        $rs = '';
        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        if(isset($form_data['data']['fio'])){
            $form_data['data']['fio']['required'] = 'off';
        }

        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }
        $form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);


        $new_values = $this->getRequestValue('_new_value');
        if (1 === (int)$this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data['data'] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . random_int(100, 999));
                    $remove_this_names[] = $id;

                    $form_data['data'][$id]['value'] = $new_values[$fd['name']];
                    $form_data['data'][$id]['type'] = 'auto_add_value';
                    $form_data['data'][$id]['dbtype'] = 'notable';
                    $form_data['data'][$id]['value_table'] = $form_data['data'][$fd['name']]['primary_key_table'];
                    $form_data['data'][$id]['value_primary_key'] = $form_data['data'][$fd['name']]['primary_key_name'];
                    $form_data['data'][$id]['value_field'] = $form_data['data'][$fd['name']]['value_name'];
                    $form_data['data'][$id]['assign_to'] = $fd['name'];
                    $form_data['data'][$id]['required'] = 'off';
                    $form_data['data'][$id]['unique'] = 'off';
                }
            }
        }

        if (isset($form_data['data']['user_id'])) {
            $form_data['data']['user_id']['value'] = $user_id;
        }
        unset($form_data['data']['view_count']);
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }
        $data_model->forse_auto_add_values($form_data['data']);
        $data_model->forse_injected_values($form_data['data']);
        //$data_model->clear_auto_add_values($form_data['data']);
        $form_data['data'] = $this->_before_check_action($form_data['data'], 'edit');
        if (!$this->check_data($form_data['data'])) {
            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
            $rs = $this->get_form($form_data['data'], 'edit');
        } else {
            $form_data['data'] = $this->_before_edit_done_action($form_data['data']);
            $this->edit_data($form_data['data']);
            if ($this->getError()) {
                $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                $rs = $this->get_form($form_data['data'], 'edit');
            } else {

                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($form_data['data']['id']['value'], $user_id, 'edit', 'data', 'id');
                }
                header('Location: ' . SITEBILL_MAIN_URL . '/account/data/');
                die();
            }
        }
        return $rs;
    }

    protected function _editAction()
    {
        $id = (int)$this->getRequestValue('id');
        $user_id = (int)$_SESSION['user_id'];

        $rs = '';
        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        if (isset($form_data['data']['fio'])) {
            $form_data['data']['fio']['required'] = 'off';
        }


        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }

        $_form_data = $form_data;
        $form_data['data'] = $data_model->init_model_data_from_db('data', 'id', $this->getRequestValue('id'), $form_data['data']);

        if (1 == $this->getConfigValue('divide_step_form') && isset($_POST['submit'])) {
            $_form_data['data'] = $data_model->init_model_data_from_request($_form_data['data']);
            foreach ($_form_data['data'] as $fdk => $fdv) {
                if ($fdv['type'] === 'uploadify_image') {
                    unset($_form_data['data'][$fdk]);
                }
            }
            $form_data['data'] = array_merge($form_data['data'], $_form_data['data']);
        }

        $form_data['data']['user_id']['type'] = 'hidden';
        unset($form_data['data']['view_count']);
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }

        $rs .= $this->get_form($form_data['data'], 'edit');
        return $rs;
    }

    protected function _new_doneAction()
    {
        /* $rtoken=$_POST['csrftoken'];
          $rhash=$_POST['csrfhash'];
          var_dump($rtoken);
          var_dump($rhash);

          if($rtoken==''){
          exit();
          }

          if(md5($rtoken.$_SESSION['csrfsecret'])!=$rhash){
          exit();
          } */

        $user_id = (int)$_SESSION['user_id'];

        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                if (!$this->checkAdvAbonent()) {
                    $rs = 'Недостаточно средств на счету';
                    return $rs;
                }
            }
        }


        $rs = '';


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }

        if (isset($form_data['data']['fio'])) {
            $form_data['data']['fio']['required'] = 'off';
        }


        if ($this->getConfigValue('special_advert_cost') > 0 && isset($form_data['data']['hot'])) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }
        $form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);

        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data['data'] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . rand(100, 999));
                    $remove_this_names[] = $id;
                    $form_data['data'][$id]['value'] = $new_values[$fd['name']];
                    $form_data['data'][$id]['type'] = 'auto_add_value';
                    $form_data['data'][$id]['dbtype'] = 'notable';
                    $form_data['data'][$id]['value_table'] = $form_data['data'][$fd['name']]['primary_key_table'];
                    $form_data['data'][$id]['value_primary_key'] = $form_data['data'][$fd['name']]['primary_key_name'];
                    $form_data['data'][$id]['value_field'] = $form_data['data'][$fd['name']]['value_name'];
                    $form_data['data'][$id]['assign_to'] = $fd['name'];
                    $form_data['data'][$id]['required'] = 'off';
                    $form_data['data'][$id]['unique'] = 'off';
                }
            }
        }


        $form_data['data']['user_id']['value'] = $user_id;
        $form_data['data']['user_id']['type'] = 'hidden';
        $form_data['data']['date_added']['value'] = date('Y-m-d H:i:s', time());

        $data_model->forse_auto_add_values($form_data['data']);
        $data_model->forse_injected_values($form_data['data']);
        $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name]);
        if (!$this->check_data($form_data['data']) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data['data']))) {

            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
            $rs = $this->get_form($form_data['data'], 'new');
        } else {
            $form_data[$this->table_name] = $this->_before_add_done_action($form_data['data']);
            $new_record_id = $this->add_data($form_data['data']);
            if ($this->getError()) {
                $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                $rs = $this->get_form($form_data['data']);
            } else {

                if ($this->getConfigValue('apps.billing.enable')) {
                    if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                        $this->setAdvAbonent($new_record_id);
                    }
                }

                if (1 == $this->getConfigValue('notify_about_added_realty')) {
                    $this->notifyUserAboutAdding($form_data['data']['user_id']['value'], $new_record_id, $form_data['data']['topic_id']['value']);
                }
                if (1 == $this->getConfigValue('moderate_first')) {
                    $this->notifyAboutNewAdvert($new_record_id);
                }
                /* TODO:
                 * добавить нотификацию админу о новом объекте
                 */

                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($new_record_id, $user_id, 'new', 'data', 'id');
                }
                header('Location: ' . SITEBILL_MAIN_URL . '/account/data/');
                die();
            }
        }
        return $rs;
    }

    protected function _deleteAction()
    {
        $user_id = (int)$_SESSION['user_id'];
        $id = (int)$this->getRequestValue('id');

        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);

        if (1 === (int)$this->getConfigValue('apps.realty.use_predeleting') && isset($model['data']['archived'])) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
            $DBC->query($query, array($id));
            $this->setUpdatedAtDate($id);
        } else {
            if ($this->getConfigValue('apps.realtylogv2.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                $Logger = new realtylogv2_admin();
                $Logger->addLog($id, $user_id, 'delete', 'data', 'id');
            }
            $this->delete_data('data', 'id', $id);
        }
        header('location: ' . SITEBILL_MAIN_URL . '/account/data/');
        exit();
    }

    protected function _newAction()
    {

        $user_id = (int)$_SESSION['user_id'];
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        if (isset($form_data['data']['fio'])) {
            $form_data['data']['fio']['required'] = 'off';
        }


        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }

        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
                $billing = new Billing();

                $user_limits = $billing->getUserLimits($user_id, 'limit_data');

                if ($user_limits && $user_limits['total'] >= $user_limits['limits']) {
                    $rs = 'Превышен лимит объявлений. Вы разместили все <b>' . $user_limits['total'] . '</b> из доступных <b>' . $user_limits['limits'] . '</b> объявлений за <b>' . $user_limits['period_key'] . '</b>';
                    return $rs;
                }
                if (method_exists($billing, 'getUserAdvLimits')) {

                    $user_limits = $billing->getUserAdvLimits($user_id, 'advlimit_data');

                    if ($user_limits && $user_limits['total'] >= $user_limits['limits']) {
                        $rs = 'Превышен лимит объявлений. Вы разместили все <b>' . $user_limits['total'] . '</b> из доступных <b>' . $user_limits['limits'] . '</b> объявлений';
                        return $rs;
                    }
                }

                if (!$billing->checkAdvAbonent($_SESSION['user_id'])) {
                    $rs = 'Недостаточно средств на счету для размещения объекта';
                    return $rs;
                }


            } else {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
                $Account = new Account;
                $company_profile = $Account->get_company_profile($user_id);
                if ($company_profile['limit_data_left']['value'] < 1) {
                    $rs = 'Превышен лимит объявлений. Вы разместили все ' . $company_profile['limit_data']['value'] . ' объявлений. Для увеличения лимита обратитесь к администратору.';
                    return $rs;
                }
            }
        }

        $form_data['data']['user_id']['value'] = $user_id;
        $form_data['data']['user_id']['type'] = 'hidden';
        $form_data['data']['active']['value'] = 1;
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }

        return $this->get_form($form_data['data']);
    }

    protected function _defaultAction()
    {
        $user_id = (int)$_SESSION['user_id'];
        return $this->grid_e($user_id, $this->getRequestValue('topic_id'));
    }
}
