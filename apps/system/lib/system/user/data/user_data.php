<?php

/**
 * User data manager
 * @author http://www.sitebill.ru
 */
//require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');

class User_Data_Manager extends Object_Manager {

    public $table_name = 'data';
    public $primary_key = 'id';

    //public $_grid_constructor;
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        //require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        //$this->_grid_constructor = new Grid_Constructor();

        $data_model = new Data_Model();
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
        if ($this->getConfigValue('hide_contact_input_user_data')) {
            unset($this->data_model['data']['fio']);
            unset($this->data_model['data']['phone']);
            unset($this->data_model['data']['email']);
        }
        if ( $this->getConfigValue('dadata_autocomplete_force') ) {
            $this->data_model['data'] = $this->prepare_model_for_dadata($this->data_model['data']);
        }
    }

    public function _before_edit_done_action($form_data) {
        return $form_data;
    }

    protected function _before_add_done_action($form_data) {
        return $form_data;
    }

    public function _before_check_action($form_data, $type = 'new') {
        return $form_data;
    }

    function init_more_fields($form_data) {
        return $form_data;
    }

    protected function _upAction() {
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));
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
            $mid = (int) $ar['mid'];
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

    
    protected function _deprotect_imagesAction(){
        
        if(1==intval($this->getConfigValue('is_watermark'))){
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }
        if(0==intval($this->getConfigValue('watermark_user_control'))){
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }
        
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));
        
        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }
        
        $need_clear_watermark = false;
            
        $DBC = DBC::getInstance();
        $query = 'SELECT watermark_images FROM ' . DB_PREFIX . '_data WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if($stmt){
            $ar = $DBC->fetch($stmt);
            if($ar['watermark_images'] == 0){
                return $this->formatAnswer_deprotect_images(0, 'nonprotected');
            }else{
                $need_clear_watermark = true;
            }
            
        }else{
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }
        
        if(!$need_clear_watermark){
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }
        
        
        $fold = $this->notwatermarked_folder;
        
       
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        
        $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, 'id', $id, $form_data['data']);
        
       
        foreach($form_data[$this->table_name] as $name=>$item){
            if($item['type']=='uploads'){
                if(is_array($item['value']) && !empty($item['value'])){
                    foreach($item['value'] as $val){
                        $images[] = $val['normal'];
                    }
                }
            }
        }
        
        $query = 'UPDATE ' . DB_PREFIX . '_data SET watermark_images=0 WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if(!$stmt){
            return $this->formatAnswer_deprotect_images(0, 'denied');
        }
        
        $restored_count = 0;
        
        if(!empty($images)){
            if($this->nowatermark_folder_with_id){
                $dest=$fold.$id.'/';
            }else{
                $dest=$fold;
            }
            
            foreach ($images as $image){
                if(file_exists($dest.$image)){
                    copy($dest.$image, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image);
                    @unlink($dest.$image);
                    $restored_count++;
                }
            }
        }
        
        return $this->formatAnswer_deprotect_images(1, 'done', count($images), $restored_count);
        
    }
    
    public function formatAnswer_protect_images($status, $code, $updated_photo_count=0){
        if($status==0){
            switch($code){
                case 'denied' : {
                   return 'Обшибка. Доступ запрещен.';
                   break;
                }
                case 'protected' : {
                    return 'Обшибка. Защита уже включена.';
                   break;
                }
            }
        }else{
            return 'Защита включена. Обработано '.$updated_photo_count.' фото';
        }
    }
    
    public function formatAnswer_deprotect_images($status, $code, $updated_photo_count=0, $restored_photo_count=0){
        if($status==0){
            switch($code){
                case 'denied' : {
                   return 'Обшибка. Доступ запрещен.';
                   break;
                }
                case 'protected' : {
                    return 'Обшибка. Защита не использовалась для этого объекта.';
                   break;
                }
            }
        }else{
            return 'Защита выключена. Восстановлено '.$restored_photo_count.' из '.$updated_photo_count.' фото';
        }
    }
    
    public function protectImagesByWatermark($id){
        
        $images = array();
        
        $fold = $this->notwatermarked_folder;
        
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        
        $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, 'id', $id, $form_data['data']);
        
        $fields = array();
        foreach($form_data[$this->table_name] as $name=>$item){
            if($item['type']=='uploads'){
                if(is_array($item['value']) && !empty($item['value'])){
                    foreach($item['value'] as $val){
                        $images[] = $val['normal'];
                    }
                }
            }
        }
        
        /*$query = 'UPDATE ' . DB_PREFIX . '_data SET watermark_images=1 WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if(!$stmt){
            return $this->formatAnswer_protect_images(0, 'denied');
        }*/
        
        if(!empty($images)){
            
            if($this->nowatermark_folder_with_id){
                $copy_path=$fold.$id.'/';
                mkdir($copy_path);
            }else{
                $copy_path=$fold;
            }
            
            foreach ($images as $image){
                copy(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image, $copy_path.$image);
                
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
                $Watermark = new Watermark();
                $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
                $Watermark->setOffsets(array(
                    $this->getConfigValue('apps.watermark.offset_left'),
                    $this->getConfigValue('apps.watermark.offset_top'),
                    $this->getConfigValue('apps.watermark.offset_right'),
                    $this->getConfigValue('apps.watermark.offset_bottom')
                ));
                $Watermark->printWatermark(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image);
            }
        }
        
        return count($images);
    }
    
    protected function _protect_imagesAction(){
        
        $status = 0;
        $error_msg = '';
        
        if(1==intval($this->getConfigValue('is_watermark'))){
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        if(0==intval($this->getConfigValue('watermark_user_control'))){
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        $need_watermark = false;
        
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));
        
        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }
            
        $DBC = DBC::getInstance();
        $query = 'SELECT watermark_images FROM ' . DB_PREFIX . '_data WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if($stmt){
            $ar = $DBC->fetch($stmt);
            if($ar['watermark_images'] == 1){
                return $this->formatAnswer_protect_images(0, 'protected');
            }else{
                $need_watermark = true;
            }
            
        }else{
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        
       
        if(!$need_watermark){
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        
        
        //$fold = $this->notwatermarked_folder;
        
        
        $query = 'UPDATE ' . DB_PREFIX . '_data SET watermark_images=1 WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if(!$stmt){
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        
        $resp = $this->protectImagesByWatermark($id);
        
        return $this->formatAnswer_protect_images(1, 'done', count($resp));
        
        
        /*require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        
        $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, 'id', $id, $form_data['data']);
        
        $fields = array();
        foreach($form_data[$this->table_name] as $name=>$item){
            if($item['type']=='uploads'){
                if(is_array($item['value']) && !empty($item['value'])){
                    foreach($item['value'] as $val){
                        $images[] = $val['normal'];
                    }
                }
            }
        }
        
        $query = 'UPDATE ' . DB_PREFIX . '_data SET watermark_images=1 WHERE id=?';
        $stmt = $DBC->query($query, array($id));
        if(!$stmt){
            return $this->formatAnswer_protect_images(0, 'denied');
        }
        
        if(!empty($images)){
            
            if($this->nowatermark_folder_with_id){
                $copy_path=$fold.$id.'/';
                mkdir($copy_path);
            }else{
                $copy_path=$fold;
            }
            
            foreach ($images as $image){
                copy(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image, $copy_path.$image);
                
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
                $Watermark = new Watermark();
                $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
                $Watermark->setOffsets(array(
                    $this->getConfigValue('apps.watermark.offset_left'),
                    $this->getConfigValue('apps.watermark.offset_top'),
                    $this->getConfigValue('apps.watermark.offset_right'),
                    $this->getConfigValue('apps.watermark.offset_bottom')
                ));
                $Watermark->printWatermark(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image);
            }
        }
        
        return $this->formatAnswer_protect_images(1, 'done', count($images));*/
       
    }


    protected function _edit_doneAction() {
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));

        $aggregroup = -1;
        $cgroup_id = intval($_SESSION['current_user_group_id']);

        $rs = '';
        if ($cgroup_id == $aggregroup) {
            if (!$this->check_access_to_aggregated_data($user_id, $id)) {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } elseif (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }


        /* $rs='';
          if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
          return Multilanguage::_('L_ACCESS_DENIED');
          } */

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        $form_data['data']['fio']['required'] = 'off';


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

        if (isset($form_data['data']['user_id'])) {
            $form_data['data']['user_id']['value'] = $user_id;
        }


        $y_id = '';
        if (strpos($form_data['data']['youtube']['value'], 'youtube.com') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['query'])) {
                parse_str($d['query'], $a);
                $y_id = $a['v'];
            }
        } elseif (strpos($form_data['data']['youtube']['value'], 'youtu.be') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                $y_id = trim($d['path'], '/');
            }
        } else {

            if (preg_match('/.*([-_A-Za-z0-9]+).*/', $form_data['data']['youtube']['value'], $matches)) {
                $y_id = $matches[0];
            }
        }
        $form_data['data']['youtube']['value'] = $y_id;
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

                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($form_data['data']['id']['value'], $user_id, 'edit', 'data');
                }
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

    protected function _editAction() {
        $id = intval($this->getRequestValue('id'));
        $user_id = $this->getSessionUserId();

        $aggregroup = -1;
        $cgroup_id = intval($_SESSION['current_user_group_id']);

        $rs = '';
        if ($cgroup_id == $aggregroup) {
            if (!$this->check_access_to_aggregated_data($user_id, $id)) {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } elseif (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        /* if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
          return Multilanguage::_('L_ACCESS_DENIED');
          } */

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        $form_data['data']['fio']['required'] = 'off';


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
                if ($fdv['type'] == 'uploadify_image') {
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
        if ($this->getConfigValue('apps.realtylog.enable')) {
            $rs .= '<h2>Лог изменений</h2>';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/site/site.php';
            $Logger = new realtylog_site();
            $rs .= $Logger->getLogs($this->getRequestValue('id'), $user_id);
        }
        return $rs;
    }

    protected function _new_doneAction() {
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



        $user_id = $this->getSessionUserId();
        $user_id = intval($_SESSION['user_id']);
        
        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                if(!$this->checkAdvAbonent()){
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


        $y_id = '';
        if (strpos($form_data['data']['youtube']['value'], 'youtube.com') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['query'])) {
                parse_str($d['query'], $a);
                $y_id = $a['v'];
            }
        } elseif (strpos($form_data['data']['youtube']['value'], 'youtu.be') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                $y_id = trim($d['path'], '/');
            }
        } else {

            if (preg_match('/.*([-_A-Za-z0-9]+).*/', $form_data['data']['youtube']['value'], $matches)) {
                $y_id = $matches[0];
            }
        }

        $form_data['data']['youtube']['value'] = $y_id;

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
        
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($new_record_id, $user_id, 'new', 'data');
                }
                if (1 == $this->getConfigValue('notify_about_added_realty')) {
                    $this->notifyUserAboutAdding($form_data['data']['user_id']['value'], $new_record_id, $form_data['data']['topic_id']['value']);
                }
                if (1 != $this->getConfigValue('moderate_first')) {
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

    protected function _deleteAction() {
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));
        $rs = '';
        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);

        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($model['data']['archived'])) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
            $stmt = $DBC->query($query, array($id));
            $this->setUpdatedAtDate($id);
        } else {
            if ($this->getConfigValue('apps.realtylog.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                $Logger = new realtylog_admin();
                $Logger->addLog($id, $user_id, 'delete', 'data');
            }
            if ($this->getConfigValue('apps.realtylogv2.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                $Logger = new realtylogv2_admin();
                $Logger->addLog($id, $user_id, 'delete', 'data', 'id');
            }
            $this->delete_data('data', 'id', $id);
        }
        header('location: ' . SITEBILL_MAIN_URL . '/account/data/');
        exit();
        $rs .= $this->grid_e($user_id, $this->getRequestValue('topic_id'));
        return $rs;
    }
    
    /*protected function setAdvAbonent($id){
        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                
                $has_abonent_payment = false;
                //проверяем возможность оплаты
                foreach($_SESSION['current_user_tariff_info']['services'] as $service_code=>$service){
                    if(preg_match('/^advabonent_/', $service_code, $matches)){
                        $has_abonent_payment = true;
                        $service_info = $service;
                        break;

                    }
                }
                
                if($has_abonent_payment){
                    $advcost = floatval($service['cost']);
                    $advlong = intval($service['period']);
                    $DBC = DBC::getInstance();
                    $now = date('Y-m-d H:i:s', time()-$advlong*24*3600);
                    //$query = 'INSERT INTO '.DB_PREFIX.'_billing_log2 (`user_id`, `item_id`, `date`) VALUES (?,?,?)';
                    $query = 'UPDATE '.DB_PREFIX.'_data SET `abonent_payment`=? WHERE id=?';
                    $stmt = $DBC->query($query, array($now, $id));

                    $query = 'UPDATE '.DB_PREFIX.'_user SET `account`=(`account`-'.$advcost.') WHERE user_id=?';
                    $stmt = $DBC->query($query, array($_SESSION['user_id']));
                }
            }
        }
    }*/
    
    /*protected function checkAdvAbonent($id = 0){
        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                
                $has_abonent_payment = false;
                //проверяем возможность оплаты
                foreach($_SESSION['current_user_tariff_info']['services'] as $service_code=>$service){
                    if(preg_match('/^advabonent_/', $service_code, $matches)){
                        $has_abonent_payment = true;
                        $service_info = $service;
                        break;

                    }
                }

                if($has_abonent_payment){
                    $advcost = floatval($service['cost']);
                    $advlong = intval($service['period']);
                    $DBC = DBC::getInstance();

                    if($id>0){
                        $now = date('Y-m-d H:i:s', time()-$advlong*24*3600);
                        //$query = 'SELECT COUNT(item_id) AS _c FROM '.DB_PREFIX.'_billing_log2 WHERE user_id=? AND item_id=? AND `date`>?';
                        
                        $query = 'SELECT COUNT(id) AS _c FROM '.DB_PREFIX.'_data WHERE user_id=? AND id=? AND `abonent_payment`>?';
                        
                        $stmt = $DBC->query($query, array($_SESSION['user_id'], $id, $now));
                        if($stmt){
                            $ar = $DBC->fetch($stmt);
                            if($ar['_c']>0){
                                return true;
                            }
                        }
                    }



                    $query = 'SELECT (`account`-'.$advcost.') AS rst FROM '.DB_PREFIX.'_user WHERE user_id=?';
                    $stmt = $DBC->query($query, array($_SESSION['user_id']));
                    if($stmt){
                        $ar = $DBC->fetch($stmt);
                        $rst = $ar['rst'];
                        if($rst<0){
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }*/

    protected function _newAction() {

        $user_id = $this->getSessionUserId();
        $rs = '';




        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        // var_dump($form_data['data']['square_rooms']);

        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        $form_data['data']['fio']['required'] = 'off';


        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }

        //$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);

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
                
                if(!$billing->checkAdvAbonent($_SESSION['user_id'])){
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
        //$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);
        $form_data['data']['user_id']['value'] = $user_id;
        $form_data['data']['user_id']['type'] = 'hidden';
        $form_data['data']['active']['value'] = 1;
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }

        $rs = $this->get_form($form_data['data']);
        return $rs;
    }

    protected function _defaultAction() {

        $user_id = $this->getSessionUserId();
        $rs = '';
        $rs .= $this->grid_e($user_id, $this->getRequestValue('topic_id'));
        return $rs;
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main($params = array()) {

        $user_id = $this->getSessionUserId();
        $user_id = intval($_SESSION['user_id']);
        if ($user_id == '' or $user_id < 1) {
            return sprintf(Multilanguage::_('L_NEED_AUTH_WITH_LINK'), '"' . SITEBILL_MAIN_URL . '/login/"');
        }



        $rs = $this->getTopMenu();

        $do = $this->getRequestValue('do');
        $action = '_' . $do . 'Action';

        if (!method_exists($this, $action)) {
            $action = '_defaultAction';
        }
        $rs .= $this->$action();


        return $rs;
    }

    function checkUniquety($form_data) {
        $unque_fields = trim($this->getConfigValue('apps.realty.uniq_params'));
        //$unque_fields='city_id,topic_id,price';
        
        $id = 0;
		if(intval($form_data['id']) != 0){
			$id = intval($form_data['id']);
		}

        $fields = array();
        if ('' !== $unque_fields) {
            $matches = array();
            preg_match_all('/([^,\s]+)/i', $unque_fields, $matches);
            if (!empty($matches[1])) {
                $fields = $matches[1];
            }
        }
        
        $where = array();
        $where_val = array();

        if (!empty($fields)) {
            foreach ($fields as $f) {
                if (isset($form_data[$f])) {
                    if ($form_data[$f]['dbtype'] == 1 || ($form_data[$f]['dbtype'] != 'notable' && $form_data[$f]['dbtype'] != '0')) {
                        $where[] = '`' . $f . '`=?';
                        $where_val[] = $form_data[$f]['value'];
                    }
                }
            }
            if($id > 0){
				$where[] = '`id`<>?';
				$where_val[] = $id;
			}
        } elseif (isset($form_data['city_id']) && isset($form_data['street_id']) && isset($form_data['number'])) {
            $where[] = '`city_id`=?';
            $where_val[] = (int) $form_data['city_id']['value'];
            $where[] = '`street_id`=?';
            $where_val[] = (int) $form_data['street_id']['value'];
            $where[] = '`number`=?';
            $where_val[] = $form_data['number']['value'];
            if($id > 0){
				$where[] = '`id`<>?';
				$where_val[] = $id;
			}
        } else {
            return TRUE;
        }

        $DBC = DBC::getInstance();

        $uns = array();
        $query = 'SELECT id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . implode(' AND ', $where);

        $stmt = $DBC->query($query, $where_val);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $uns[] = $ar['id'];
            }
        }
        if (count($uns) > 0) {
            $this->riseError('Такое объявление уже существует (' . implode(',', $uns) . ')');
            return FALSE;
        }
        return TRUE;
    }

    private function notifyAboutNewAdvert($id) {

        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */
        $subject = $_SERVER['SERVER_NAME'] . ': добавлено новое объявление';

        $from = $this->getConfigValue('system_email');
        $useremail = $this->getConfigValue('order_email_acceptor');
        $body = '';
        $body .= 'Было добавлено объявление с ID ' . $id . '<br />';

        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);
        $model = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $id, $model[$this->table_name]);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new Table_View();
        $table_view->setAbsoluteUrls();
        $body .= '<table border="1" cellpadding="2" cellspacing="2" class="table table-striped table-hover">';
        $body .= $table_view->compile_view($model);
        $body .= '</table>';

        $body .= $this->getConfigValue('email_signature');


        $this->template->assign('target_url', $this->getServerFullUrl() . '/admin/?action=data&do=edit&id=' . $id);
        if ($action == 'edit') {
            $this->template->assign('edit_action', 1);
        }
        $this->template->assign('id', $id);
        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('new_adv_nomoderate');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'new_adv_nomoderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            ////$this->writeLog($message_array);
        }

        $this->sendFirmMail($useremail, $from, $subject, $body);
        return;
    }

    private function notifyAboutModerationNeed($id, $action = 'new') {

        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */
        $subject = $_SERVER['SERVER_NAME'] . ': объявление требует модерации';
        $from = $this->getConfigValue('system_email');
        $useremail = $this->getConfigValue('order_email_acceptor');
        $body = '';
        if ($action == 'edit') {
            $body .= 'Было изменено объявление с ID ' . $id . '<br />';
            $body .= 'Объявление снято с публикации и ожидает модерации.<br />';
        } else {
            $body .= 'Было добавлено объявление с ID ' . $id . '<br />';
            $body .= 'Объявление ожидает модерации.<br />';
        }


        $body .= $this->getConfigValue('email_signature');
        /* if ( $this->getConfigValue('use_smtp') ) {
          $mailer->send_smtp($useremail, $from, $subject, $body, 1);
          } else {
          $mailer->send_simple($useremail, $from, $subject, $body, 1);
          } */

        $this->template->assign('target_url', $this->getServerFullUrl() . '/admin/?action=data&do=edit&id=' . $id);
        if ($action == 'edit') {
            $this->template->assign('edit_action', 1);
        }
        $this->template->assign('id', $id);
        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('need_moderate');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'need_moderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            ////$this->writeLog($message_array);
        }

        $this->sendFirmMail($useremail, $from, $subject, $body);
        return;
    }

    private function notifyUserAboutAdding($user_id, $id, $topic_id) {
        $DBC = DBC::getInstance();

        $useremail = '';
        $fio = '';
        $query = 'SELECT fio, email FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $fio = $ar['fio'];
            $useremail = $ar['email'];
        }

        $translit_alias = '';
        $query = 'SELECT translit_alias FROM ' . DB_PREFIX . '_data WHERE id=? LIMIT 1';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $translit_alias = $ar['translit_alias'];
        }

        $href = $this->getRealtyHREF($id, true, array('topic_id' => $topic_id, 'alias' => $translit_alias));

        /* require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
          $Structure_Manager = new Structure_Manager();
          $category_structure = $Structure_Manager->loadCategoryStructure();

          if(1==$this->getConfigValue('apps.seo.level_enable')){

          if($category_structure['catalog'][$topic_id]['url']!=''){
          $parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
          }else{
          $parent_category_url='';
          }
          }else{
          $parent_category_url='';
          }
          if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
          $href=$this->getServerFullUrl().'/'.$parent_category_url.$translit_alias;
          }elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
          $href=$this->getServerFullUrl().'/'.$parent_category_url.'realty'.$id.'.html';
          }else{
          $href=$this->getServerFullUrl().'/'.$parent_category_url.'realty'.$id;
          } */

        //$href='http://'.$_SERVER['HTTP_HOST'].$href;
        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */

        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/reguser_add_notify.tpl';
        if (file_exists($tpl)) {
            //$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/user_registration_conf.tpl';
            global $smarty;
            $smarty->assign('mail_adv_link', $href);
            $smarty->assign('mail_user_fio', $fio);
            $smarty->assign('mail_adv_id', $id);
            if (1 == $this->getConfigValue('moderate_first')) {
                $smarty->assign('mail_moderate_first', 1);
            }
            $smarty->assign('mail_signature', $this->getConfigValue('email_signature'));
            $body = $smarty->fetch($tpl);
        } else {
            $body = '';
            $body .= sprintf(Multilanguage::_('DEAR_FIO', 'system'), $fio) . '<br />';
            $body .= Multilanguage::_('YOUR_ADV_ADD', 'system') . '<br />';
            $body .= Multilanguage::_('YOUR_ADV_LINK', 'system') . ' <a href="' . $href . '">' . $href . '</a><br />';
            if (1 == $this->getConfigValue('moderate_first')) {
                $body .= Multilanguage::_('ADV_NEED_MODERATING_FIRST', 'system') . '<br />';
            }
            $body .= $this->getConfigValue('email_signature');
        }


        $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('REGU_ADDNOTE_SUBJ', 'system');
        $from = $this->getConfigValue('system_email');
        /* $body='';
          $body.='Уважаемый, '.$fio.'!<br />';
          $body.='Ваше объявление размещено.<br />';
          $body.='Адрес объявления <a href="'.$href.'">'.$href.'</a><br />';
          $body.=$this->getConfigValue('email_signature'); */
        /* if ( $this->getConfigValue('use_smtp') ) {
          $mailer->send_smtp($useremail, $from, $subject, $body, 1);
          } else {
          $mailer->send_simple($useremail, $from, $subject, $body, 1);
          } */

        $this->template->assign('target_url', $href);
        $this->template->assign('edit_url', $this->getServerFullUrl() . '/account/data/?do=edit&id=' . $id);
        $this->template->assign('moderate_first', $this->getConfigValue('moderate_first'));
        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('user_notify_about_adding');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'user_notify_about_adding';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            //$this->writeLog($message_array);
        }

        $this->sendFirmMail($useremail, $from, $subject, $body);
        return;
    }

    protected function removeTemporaryFields(&$model, $remove_this_names = array()) {
        if (is_array($remove_this_names) && count($remove_this_names) > 0) {
            foreach ($remove_this_names as $r) {
                unset($model[$r]);
            }
        }
        return $model;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {

        $DBC = DBC::getInstance();

        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);

        $model = $data_model->init_model_data_from_db($table_name, $primary_key, $primary_key_value, $model[$table_name]);

        $uploadify = false;
        $uploads = array();
        $docuploads = array();
        $avtars = array();
        $multiitems = array();
        foreach ($model as $model_field) {
            if ($model_field['type'] == 'uploads' && !empty($model_field['value'])) {
                foreach ($model_field['value'] as $upload) {
                    $uploads[] = $upload['preview'];
                    $uploads[] = $upload['normal'];
                }
            } elseif ($model_field['type'] == 'docuploads' && !empty($model_field['value'])) {
                foreach ($model_field['value'] as $upload) {
                    $docuploads[] = $upload['normal'];
                }
            } elseif ($model_field['type'] == 'avatar' && $model_field['value'] != '') {
                $avtars[] = $model_field['value'];
            } elseif ($model_field['type'] == 'select_by_query_multi') {
                $multiitems[] = $model_field['name'];
            } elseif ($model_field['type'] == 'uploadify_image') {
                $uploadify = true;
            }
        }


        $query = 'DELETE FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE `' . $primary_key . '` = ?';
        $stmt = $DBC->query($query, array($primary_key_value));
        if (!$stmt) {
            return false;
        }
        if (!empty($uploads)) {
            foreach ($uploads as $upload) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $upload);
                @unlink($this->notwatermarked_folder . $upload);
            }
        }

        if (!empty($docuploads)) {
            foreach ($docuploads as $upload) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/' . $upload);
            }
        }
        if (!empty($avtars)) {
            foreach ($avtars as $avtar) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $avtar);
            }
        }
        if (!empty($multiitems)) {

            $params = array();
            $params[] = $table_name;
            $params = array_merge($params, $multiitems);
            $params[] = $primary_key_value;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($multiitems), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);
        }

        if ($uploadify) {
            $imgs_ids = array();
            $query = 'SELECT image_id FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE ' . $primary_key . '=?';
            $stmt = $DBC->query($query, array($primary_key_value));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $imgs_ids[] = $ar['image_id'];
                }
            }

            if (count($imgs_ids) > 0) {
                foreach ($imgs_ids as $im) {
                    $this->deleteImage($table_name, $im);
                }
            }
        }
    }

    /**
     * Check access to data
     * @param int $user_id
     * @param int $data_id
     * @return boolean
     */
    function check_access_to_data($user_id, $data_id) {
        $DBC = DBC::getInstance();
        $enable_curator_mode = false;
        if (1 == $this->getConfigValue('enable_curator_mode')) {
            $enable_curator_mode = true;
            $has_access = 0;
            
            if(1 === intval($this->getConfigValue('curator_mode_fullaccess'))){
                
                $query = 'SELECT COUNT(d.id) AS _cnt FROM ' . DB_PREFIX . '_data d LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE d.id=? AND u.parent_user_id=?';
                $stmt = $DBC->query($query, array($data_id, $user_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            }else{
                $query = 'SELECT COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=? AND id=?';
                $stmt = $DBC->query($query, array($user_id, 'data', $data_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            }

            
        }

        $where = array();
        $where_val = array();

        $where[] = '`id`=?';
        $where_val[] = $data_id;
        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
            $where[] = '`archived`=0';
        }

        if ($enable_curator_mode) {
            $where[] = '(`user_id`=? OR (`user_id`!=? AND 1=' . $has_access . '))';
            $where_val[] = $user_id;
            $where_val[] = $user_id;
        } else {
            $where[] = '`user_id`=?';
            $where_val[] = $user_id;
        }

        /* if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
          $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id=? AND id=? AND archived=0";
          } else {
          $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id=? AND id=?";
          }
          $stmt = $DBC->query($query, array($user_id, $data_id)); */

        $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE ' . implode(' AND ', $where);
        $stmt = $DBC->query($query, $where_val);

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check access to data
     * @param int $user_id
     * @param int $data_id
     * @return boolean
     */
    function check_access_to_aggregated_data($user_id, $data_id) {
        $DBC = DBC::getInstance();

        $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE puser_id=?';
        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id IN (SELECT user_id FROM " . DB_PREFIX . "_user WHERE puser_id=? OR user_id=?) AND id=? AND archived=0";
        } else {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id IN (SELECT user_id FROM " . DB_PREFIX . "_user WHERE puser_id=? OR user_id=?) AND id=?";
        }

        $stmt = $DBC->query($query, array($user_id, $user_id, $data_id));


        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return grid
     * @param int $user_id user id
     * @param int $current_category_id current category id
     * @return string
     */
    function grid_e($user_id, $current_category_id) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_tree = $Structure_Manager->get_category_tree_control($current_category_id, $user_id);

        $rs .= '<div class="grids">';

        if (1 == $this->getConfigValue('show_cattree_left')) {

            $rs .= '<table border="0" width="99%" cellpadding="0" cellspacing="0">';

            $rs .= '<tr>';
            $rs .= '<td style="vertical-align: top;" id="lk_tree">';
            $rs .= $category_tree;
            $rs .= '</td>';
            $rs .= '<td style="vertical-align: top;">';
            $rs .= $this->get_data_grid($user_id, $current_category_id);
            $rs .= '</td>';
            $rs .= '</tr>';

            $rs .= '</table>';
        } else {
            $this->template->assert('category_tree_account', $category_tree);
            $rs .= $this->get_data_grid($user_id, $current_category_id);
        }



        $rs .= '</div>';
        //global $smarty;
        //$smarty->assign();


        return $rs;
    }

    /**
     * Get data grid
     * @param int $user_id
     * @return string
     */
    function get_data_grid($user_id, $current_category_id = false) {

        $FM = new frontend_main();
        $params = $FM->gatherRequestParams();
        $gid = intval($_SESSION['current_user_group_id']);
        $searched_user_id = intval($this->getRequestValue('user_id'));
        $aggregroup = -1;

        $DBC = DBC::getInstance();
        /* $query='SELECT group_id FROM '.DB_PREFIX.'_user WHERE user_id=?';
          $stmt=$DBC->query($query, array($user_id));
          if($stmt){
          $ar=$DBC->fetch($stmt);
          $gid=intval($ar['group_id']);
          } */


        $incusers = array();
        if ($gid == $aggregroup) {
            $owned_users = array();
            $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE puser_id=?';
            $stmt = $DBC->query($query, array($user_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $owned_users[$ar['user_id']] = $ar['user_id'];
                }
            }

            if ($searched_user_id > 0 && (isset($owned_users[$searched_user_id]) || $searched_user_id == $user_id)) {
                $params['user_id'] = $searched_user_id;
            } else {
                $incusers = $owned_users;
                $incusers[$user_id] = $user_id;
                $params['agg_user_id'] = $incusers;
            }
        } else {
            $params['user_id'] = $user_id;
        }

        global $smarty;

        //require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        //$grid_constructor = new Grid_Constructor();
        //$grid_constructor=$this->_grid_constructor;
        $grid_constructor = $this->_getGridConstructor();



        /* $params['topic_id'] = $this->getRequestValue('topic_id');
          $params['order'] = $this->getRequestValue('order');
          $params['region_id'] = $this->getRequestValue('region_id');
          $params['city_id'] = $this->getRequestValue('city_id');
          $params['district_id'] = $this->getRequestValue('district_id');
          $params['metro_id'] = $this->getRequestValue('metro_id');
          $params['street_id'] = $this->getRequestValue('street_id');
          $params['page'] = $this->getRequestValue('page');
          $params['asc'] = $this->getRequestValue('asc');
          $params['price'] = $this->getRequestValue('price');
          $params['price_min'] = $this->getRequestValue('price_min');
          $params['active'] = $this->getRequestValue('active'); */
        /* if(!empty($incusers)){
          $params['agg_user_id'] = $incusers;
          }else{
          $params['user_id'] = $user_id;
          } */

        $params['active'] = $this->getRequestValue('active');
        $params['id'] = (int) $this->getRequestValue('id');

        //$params['per_page'] = 2;

        if ((int) $this->getRequestValue('page_limit') != 0) {
            $params['page_limit'] = (int) $this->getRequestValue('page_limit');
        }else{
            if(0!=intval($this->getConfigValue('per_page_account'))){
                $params['page_limit'] = intval($this->getConfigValue('per_page_account'));
            }
        }
        $params['admin'] = true;
        if ($this->getRequestValue('srch_export_cian') == 'on' || $this->getRequestValue('srch_export_cian') == '1') {
            $params['srch_export_cian'] = 1;
        }

        $coworked = array();
        if (1 == $this->getConfigValue('enable_curator_mode')) {
            
            $DBC = DBC::getInstance();
            
            if(1 == $this->getConfigValue('curator_mode_fullaccess')){
                $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE parent_user_id=?';
                $stmt = $DBC->query($query, array($user_id));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $coworked[] = $ar['user_id'];
                    }
                }
                $params['coworked_users'] = $coworked;
            }else{
                $query = 'SELECT id FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=?';
                $stmt = $DBC->query($query, array($user_id, 'data'));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $coworked[] = $ar['id'];
                    }
                }
                $params['coworked_ids'] = $coworked;
            }
            
            
            
        }


        //$params['pager_url']='account/data';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $res = $grid_constructor->get_sitebill_adv_ext($params);
        if (!empty($res) && $this->getConfigValue('apps.mailbox.enable') == 1) {
            $ids = array();
            foreach ($res as $i => $d) {
                $ids[$d[$this->primary_key]] = $i;
                $res[$i]['_mailbox_cnt']['l'] = SITEBILL_MAIN_URL . '/mailbox/?realty_id=' . $d[$this->primary_key];
            }
            $DBC = DBC::getInstance();
            $query = 'SELECT COUNT(mailbox_id) AS _cnt, realty_id, `status` FROM ' . DB_PREFIX . '_mailbox WHERE realty_id IN (' . implode(',', array_keys($ids)) . ') GROUP BY realty_id, `status`';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ($ar['status'] == 1) {
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['r'] = $ar['_cnt'];
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['t'] += $ar['_cnt'];
                    } else {
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['u'] = $ar['_cnt'];
                        $res[$ids[$ar['realty_id']]]['_mailbox_cnt']['t'] += $ar['_cnt'];
                    }
                }
            }
        }
        $this->template->assign('grid_items', $res);
        //$this->template->assign('category_tree', $grid_constructor->get_category_tree( $params, $category_structure ) );
        //$this->template->assign('breadcrumbs', $grid_constructor->get_category_breadcrumbs( $params, $category_structure ) );
        //$grid_constructor->get_sales_grid($res);
        //$grid_constructor->main($params);
        $smarty->assign('admin', 1);
        $smarty->assign('topic_id', $params['topic_id']);

        $html = $smarty->fetch("realty_grid_account.tpl");
        return $html;
    }

    /**
     * Get offer list
     * @param int $user_id
     * @param mixed $current_category_id
     * @return mixed
     */
    function getOfferList($user_id, $current_category_id = false) {
        $ret = array();
        if ($current_category_id) {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_data WHERE user_id=' . $user_id . ' and topic_id = ' . $current_category_id;
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_data WHERE user_id=' . $user_id;
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar;
            }
        }
        return $ret;
    }

    /**
     * Add data
     * @param array $form_data form data
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {

        $curator_id = 0;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($this->getSessionUserId());

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

        $user_id = intval($_SESSION['user_id']);
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
            if(isset($form_data['active'])){
                $form_data['active']['value'] = 0;
            }
        }

        if (1 == $this->getConfigValue('enable_curator_mode') && 0 === intval($this->getConfigValue('curator_mode_fullaccess'))) {
            $query = 'SELECT parent_user_id FROM ' . DB_PREFIX . '_user WHERE user_id=?';
            $stmt = $DBC->query($query, array($user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if (intval($ar['parent_user_id']) > 0) {
                    $curator_id = intval($ar['parent_user_id']);
                }
            }
        }


        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        //$query = $data_model->get_insert_query(DB_PREFIX.'_data', $form_data);
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
            if ($form_item['type'] == 'uploads') {
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
            if ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads('data', $form_item, 'id', $new_record_id);
            }
        }

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if ($form_item['type'] == 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $mutiitems[$k];
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
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
            $Watermark = new Watermark();
            $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
            $Watermark->setOffsets(array(
                $this->getConfigValue('apps.watermark.offset_left'),
                $this->getConfigValue('apps.watermark.offset_top'),
                $this->getConfigValue('apps.watermark.offset_right'),
                $this->getConfigValue('apps.watermark.offset_bottom')
            ));

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
        }else{
            
            /*$need_watermark = false;
            
            if(1==intval($this->getConfigValue('watermark_user_control'))){
                $DBC = DBC::getInstance();
                $query = 'SELECT watermark_images FROM ' . DB_PREFIX . '_data WHERE id=?';
                $stmt = $DBC->query($query, array($new_record_id));
                if($stmt){
                    $ar = $DBC->fetch($stmt);
                    if($ar['watermark_images'] == 1){
                        $need_watermark = true;
                    }
                }
            }
            
            if(!empty($imgs) && $need_watermark){
                
                $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
                $copy_folder = $this->notwatermarked_folder;
                if($this->nowatermark_folder_with_id){
                    $copy_folder = $copy_folder.$new_record_id.'/';
                }
                
                if (!is_dir($copy_folder)) {
                    mkdir($copy_folder);
                }
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
                $Watermark = new Watermark();
                $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
                $Watermark->setOffsets(array(
                    $this->getConfigValue('apps.watermark.offset_left'),
                    $this->getConfigValue('apps.watermark.offset_top'),
                    $this->getConfigValue('apps.watermark.offset_right'),
                    $this->getConfigValue('apps.watermark.offset_bottom')
                ));
                foreach ($imgs as $v) {
                    $parts = explode('/', $v['normal']);

                    if(count($parts)>1){
                        $nam = end($parts);
                        $parts = array_slice($parts, 0, count($parts)-1);
                        for($i=1; $i<=count($parts); $i++){
                            $locs = $copy_folder . '/' . implode('/', array_slice($parts, 0, $i));
                            if (!is_dir($locs)) {
                                mkdir($locs);
                            }
                        }

                    }
                    copy($filespath . $v['normal'], $copy_folder . $v['normal']);

                    $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                }
                
            }
            
            */
        }

        /* if (!$moderation_mode) {
          $this->notifyAboutNewAdvert($new_record_id);
          } */

        if ($new_record_id > 0) {
            if ($this->getConfigValue('advert_cost') > 0) {
                $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('advert_cost'));
            }
            if ($this->getConfigValue('special_advert_cost') > 0 and $form_data['hot']['value'] == 1) {
                $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('special_advert_cost'));
            }

            if ($this->getConfigValue('apps.twitter.enable') && 1 == (int) $this->getConfigValue('apps.twitter.allow_posting_from_account')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($new_record_id);
            }
            if ($this->getConfigValue('apps.telegram.enable') && 1 == (int) $this->getConfigValue('apps.telegram.allow_posting_from_account')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/telegram/admin/admin.php';
                $Telegram = new telegram_admin();
                $Telegram->sendPost($new_record_id);
            }
        }
        return $new_record_id;

        //echo "new_record_id = $new_record_id<br>";
        //echo $query;
    }

    /**
     * Edit data
     * @param array $form_data form data
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {
        $id = intval($this->getRequestValue('id'));
        if ($id == 0) {
            return false;
        }
        $status_changed = false;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($this->getSessionUserId());

        if(isset($form_data['price'])){
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
                $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('special_advert_cost'));
            }
        }
        if(isset($form_data['active']) && ($form_data_tmp['active']['value'] == 0 and $form_data['active']['value'] == 1)){
            if(!$this->checkAdvAbonent($id)){
                $this->riseError('Вы не можете изменить статус активности');
                return;
            }else{
                $this->setAdvAbonent($id);
            }
            
        }

        $moderation_mode = false;
        if (1 == $this->getConfigValue('moderate_first')) {
            $moderation_mode = true;
        }

        $user_id = intval($_SESSION['user_id']);
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

        if ($success_mark && (0 === intval($this->getConfigValue('apps.billing.enable')) || (1 === intval($this->getConfigValue('apps.billing.enable')) && 0 === $this->getConfigValue('apps.upper.enable')))) {
            $this->setUpdatedAtDate($id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
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
            if ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads('data', $form_item, 'id', $id);
            }
        }

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if ($form_item['type'] == 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $mutiitems[$k];
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
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
            $Watermark = new Watermark();
            $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
            $Watermark->setOffsets(array(
                $this->getConfigValue('apps.watermark.offset_left'),
                $this->getConfigValue('apps.watermark.offset_top'),
                $this->getConfigValue('apps.watermark.offset_right'),
                $this->getConfigValue('apps.watermark.offset_bottom')
            ));
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
        }else{
            
            /*$need_watermark = false;
            
            if(1==intval($this->getConfigValue('watermark_user_control'))){
                $DBC = DBC::getInstance();
                $query = 'SELECT watermark_images FROM ' . DB_PREFIX . '_data WHERE id=?';
                $stmt = $DBC->query($query, array($id));
                if($stmt){
                    $ar = $DBC->fetch($stmt);
                    if($ar['watermark_images'] == 1){
                        $need_watermark = true;
                    }
                }
            }
            
            if(!empty($imgs) && $need_watermark){
                
                $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
                $copy_folder = $this->notwatermarked_folder;
                if($this->nowatermark_folder_with_id){
                    $copy_folder = $copy_folder.$id.'/';
                }
                
                if (!is_dir($copy_folder)) {
                    mkdir($copy_folder);
                }
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
                $Watermark = new Watermark();
                $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
                $Watermark->setOffsets(array(
                    $this->getConfigValue('apps.watermark.offset_left'),
                    $this->getConfigValue('apps.watermark.offset_top'),
                    $this->getConfigValue('apps.watermark.offset_right'),
                    $this->getConfigValue('apps.watermark.offset_bottom')
                ));
                foreach ($imgs as $v) {
                    $parts = explode('/', $v['normal']);

                    if(count($parts)>1){
                        $nam = end($parts);
                        $parts = array_slice($parts, 0, count($parts)-1);
                        for($i=1; $i<=count($parts); $i++){
                            $locs = $copy_folder . '/' . implode('/', array_slice($parts, 0, $i));
                            if (!is_dir($locs)) {
                                mkdir($locs);
                            }
                        }

                    }
                    copy($filespath . $v['normal'], $copy_folder . $v['normal']);

                    $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                }
                
            }*/
            
            
        }
    }

    public function setStatusDate($id, $date = '') {
        $DBC = DBC::getInstance();
        if ($date == '') {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET status_change=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id));
    }

    public function setUpdatedAtDate($id) {
        $field = trim($this->getConfigValue('apps.realty.updated_at_field'));
        /*
         * $type = 1|0 - 1-date, 0-dtdatetime
         */
        $type = intval($this->getConfigValue('apps.realty.updated_at_field_type'));
        $update_date_added = intval($this->getConfigValue('apps.realty.update_date_added'));

        if ($field == '' && 1 === $update_date_added) {
            $field = 'date_added';
            $type = 0;
        }


        if ($field == '' || $type > 1) {
            return false;
        }

        $DBC = DBC::getInstance();
        if ($type == 1) {
            $date = time();
        } else {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id));
        if ($stmt) {
            return true;
        }
        return false;
    }

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data) {
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

    /**
     * Get top menu
     * @param void 
     * @return string
     */
    function getTopMenu() {
        if ($this->getRequestValue('do') != 'new' and $this->getRequestValue('do') != 'edit') {
            $rs = '';
            $rs .= '<a class="btn btn-primary" href="' . SITEBILL_MAIN_URL . '/account/data/?do=new">' . Multilanguage::_('L_ADD_ADV') . '</a>';
            $rs .= '<div class="clear"></div>';
            //$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
            return $rs;
        }
        return '';
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {
        $_SESSION['allow_disable_root_structure_select'] = true;
        if (1 == $this->getConfigValue('divide_step_form')) {
            return $this->_get_form_step_divided($form_data, $do);
        } else {
            return $this->_get_form_standart($form_data, $do);
        }
    }

    function getSteps($form_data, $step) {

        $default_tab_name = $this->getConfigValue('default_tab_name');
        $tabs = array($default_tab_name);

        foreach ($form_data as $item_id => $item_array) {
            if (isset($item_array['tab']) && $item_array['tab'] != '') {
                $tabs[$item_array['tab']] = $item_array['tab'];
            }
        }
        $tabs_array = array();
        $i = 1;
        foreach ($tabs as $t) {
            if ($i < $step) {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'done');
            } elseif ($i == $step) {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'current');
            } else {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'further');
            }
            $i++;
        }
        return $tabs_array;
    }

    function _get_form_step_divided($form_data = array(), $do = 'new', $language_id = 0, $button_title = '') {



        //$step=(int)$this->getRequestValue('step')
        $requesturi = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if (SITEBILL_MAIN_URL != '') {
            preg_replace('/^' . trim(SITEBILL_MAIN_URL, '/') . '/', '', $requesturi);
        }
        if (preg_match('/step(\d+)$/', $requesturi, $matches)) {
            $step = (int) $matches[1];
        } else {
            $step = 1;
        }
        //echo $step;

        $steps_names = $this->getSteps($form_data, $step);
        $last_step = $steps_names[count($steps_names)]['name'];

        if (isset($form_data['captcha'])) {
            $form_data['captcha']['tab'] = $last_step;
        }

        foreach ($form_data as $k => $v) {
            if ($v[type] == 'uploadify_image') {
                $form_data[$k]['tab'] = $last_step;
            }
        }
        $steps_names = $this->getSteps($form_data, $step);

        $steps_total = count($steps_names);


        $Sitebill_Registry = Sitebill_Registry::getInstance();
        $Sitebill_Registry->addFeedback('divide_step_form', true);
        $Sitebill_Registry->addFeedback('step', $step);


        global $smarty;
        $el = array();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $account_value = $account->getAccountValue($this->getSessionUserId());
        $rs .= '<div class="clear"></div>';
        $rs .= $this->get_ajax_functions();
        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        //$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/account/data/">';
        if (1 == $this->getConfigValue('use_combobox')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/mycombobox.js"></script>';
            $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/css/mycombobox.css" />';
        }

        $el = $form_generator->compile_form_elements($form_data);

        $topic_id = (int) $form_data['topic_id']['value'];
        $current_id = (int) $form_data['id']['value'];

        if ($topic_id != 0 && $current_id != 0) {

            $href = $this->getRealtyHREF($current_id, true, array('topic_id' => $topic_id, 'alias' => $form_data['translit_alias']['value']));
            $rs .= '<a class="btn btn-success pull-right" href="' . $href . '" target="_blank">' . Multilanguage::_('L_SEE_AT_SITE') . '</a>';
        }

        if ($step < $steps_total) {
            $rs .= '<form id="step_form" method="post" action="' . SITEBILL_MAIN_URL . '/account/data/step' . (1 + $step) . '" enctype="multipart/form-data" class="user_add_form">';
        } else {
            $rs .= '<form id="step_form" method="post" action="' . SITEBILL_MAIN_URL . '/account/data/step' . $steps_total . '" enctype="multipart/form-data" class="user_add_form">';
        }

        if ($this->getConfigValue('advert_cost') > 0 and ( $do == 'new' or $do == 'new_done' )) {

            $rs .= '<p><b>Стоимость размещения одного объявления ' . $this->getConfigValue('advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b></p>';

            if ($account_value < $this->getConfigValue('advert_cost')) {
                $rs .= '<p>Ваш баланс ' . $account_value . ' ' . $this->getConfigValue('ue_name') . '</p>';
                $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">пополнить</a></b></td>';
                return $rs;
            }
        }



        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }



        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';


        if ($step < $steps_total) {
            if ($do == 'new') {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="new" />');
            } else {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit" />');
            }
        } else {
            if ($do == 'new') {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
            } else {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            }
        }


        if ($step > 1) {
            $el['controls']['back'] = array('html' => '<input type="submit" name="submit" alt="' . ($step - 1) . '" id="formsubmit_back" value="Назад" />');
        }

        if ($step < $steps_total) {
            $button_title = 'Следующий шаг';
        } else {
            $button_title = 'Сохранить';
        }

        $el['controls']['submit'] = array('html' => '<input type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="' . $button_title . '" />');


        $smarty->assign('current_step', $step);
        $smarty->assign('divide_by_step', 1);
        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }

        return $smarty->fetch($tpl_name);
    }

    function _get_form_standart($form_data = array(), $do = 'new', $language_id = 0, $button_title = '') {

        global $smarty;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        $el = array();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $account_value = $account->getAccountValue($this->getSessionUserId());
        $rs .= '<div class="clear"></div>';
        $rs .= $this->get_ajax_functions();
        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        if (1 == $this->getConfigValue('use_combobox')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/mycombobox.js"></script>';
            $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/css/mycombobox.css" />';
        }


        $topic_id = (int) $form_data['topic_id']['value'];
        $current_id = (int) $form_data['id']['value'];

        if ($topic_id != 0 && $current_id != 0) {
            $href = $this->getRealtyHREF($current_id, true, array('topic_id' => $topic_id, 'alias' => $form_data['translit_alias']['value']));
            $rs .= '<a class="btn btn-success form-cntrl form-cntrl-siteview" href="' . $href . '" target="_blank">' . Multilanguage::_('L_SEE_AT_SITE') . '</a>';
        }

        $rs .= '<form method="post" class="form-horizontal" action="' . SITEBILL_MAIN_URL . '/account/data' . SiteBill::$_trslashes . '" enctype="multipart/form-data">';

        if ($this->getConfigValue('advert_cost') > 0 and ( $do == 'new' or $do == 'new_done' )) {

            $rs .= '<p><b>Стоимость размещения одного объявления ' . $this->getConfigValue('advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b></p>';

            if ($account_value < $this->getConfigValue('advert_cost')) {
                $rs .= '<p>Ваш баланс ' . $account_value . ' ' . $this->getConfigValue('ue_name') . '</p>';
                $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">пополнить</a></b></td>';
                return $rs;
            }
        }



        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);
        $el['form_header'] = $rs;
        $el['form_header_action'] = SITEBILL_MAIN_URL . '/account/data/';
        $el['form_header_class'] = 'form-horizontal';
        $el['form_header_enctype'] = 'multipart/form-data';
        $el['form_footer'] = '</form>';
        if ($do == 'new') {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            //$el['private'][]=array('html'=>'<input type="hidden" name="id" value="'.$form_data['id']['value'].'">');
        }

        /* $token = md5(uniqid(mt_rand() . microtime()));
          $hash = md5($token.$_SESSION['csrfsecret']);
          $el['private'][] = array('html' => '<input type="hidden" name="csrftoken" value="'.$token.'" />');
          $el['private'][] = array('html' => '<input type="hidden" name="csrfhash" value="'.$hash.'" />');
         */
        $el['controls']['submit'] = array('html' => '<input class="btn btn-primary" type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="' . $button_title . '" />');

        $smarty->assign('do', $do);
        $smarty->assign('id', $form_data['id']['value']);
        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_front.tpl')) {

            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_front.tpl';
        } else {
            /* if (defined('RUN_WITH3BOOTSTRAP') && RUN_WITH3BOOTSTRAP == 1) {
              $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
              } else {
              $tpl_name = $this->getAdminTplFolder() . '/data_form_front.tpl';
              } */

            $tpl_name = $this->getAdminTplFolder() . '/data_form_front.tpl';
        }

        return $smarty->fetch($tpl_name);
    }

    protected function createTranslitAliasByFields($id, $fields_for_alias) {
        $alias = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);

        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
        $values = array();
        foreach ($fields_for_alias as $v) {
            $key = trim($v);
            if (isset($form_data_shared[$key])) {
                if (($form_data_shared[$key]['type'] == 'select_box_structure' || $form_data_shared[$key]['type'] == 'select_by_query' || $form_data_shared[$key]['type'] == 'select_box') && $form_data_shared[trim($v)]['value_string'] != '') {
                    $values[] = $form_data_shared[trim($v)]['value_string'];
                } elseif ($form_data_shared[trim($v)]['value'] != '') {
                    $values[] = $form_data_shared[trim($v)]['value'];
                }
            }
        }
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $values[$k] = $this->transliteMe($v);
            }
            $alias = implode('-', $values);
        }
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        $alias = strtr($alias, $unwanted_array);
        return $alias;
    }

    protected function makeUniqueAlias($alias, $id) {
        $is_similar_alias_exists = false;
        $DBC = DBC::getInstance();
        $query = "SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "_data WHERE translit_alias=? AND id<>? ORDER BY translit_alias DESC LIMIT 1";
        $stmt = $DBC->query($query, array($alias, $id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int) $ar['cnt'] > 0) {
                $is_similar_alias_exists = true;
            }
        }

        if ($is_similar_alias_exists) {
            $query = "SELECT translit_alias FROM " . DB_PREFIX . "_data WHERE translit_alias LIKE '" . $alias . "%' AND id<>? ORDER BY translit_alias DESC LIMIT 1";
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if (preg_match('/' . $alias . '-(\d+)/', $ar['translit_alias'], $matches)) {
                    $alias .= '-' . ((int) $matches[1] + 1);
                } else {
                    $alias .= '-1';
                }
            }
        }
        //echo $alias;
        return $alias;
    }
    
    function mass_delete_data($table_name, $primary_key, $ids) {
        
        $cuser_id = (int) $_SESSION['user_id'];
        
        if($cuser_id==0){
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
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET archived=1 WHERE `id` IN (' . implode(',', $ids) . ')';
                $stmt = $DBC->query($query);
                header('location: '.SITEBILL_MAIN_URL.'/account/data/');
                exit();
            } else {
                foreach ($ids as $id) {
                    $log_id = false;
                    if ($this->getConfigValue('apps.realtylog.enable')) {
                        require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                        $Logger = new realtylog_admin();
                        $log_id = $Logger->addLog($id, $cuser_id, 'delete', $table_name);
                    }
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
                    $rs .= $errors . '<div align="center"><a href="'.SITEBILL_MAIN_URL.'/accoutn/data/">ОК</a></div>';
                } else {
                    header('location: '.SITEBILL_MAIN_URL.'/account/data/');
                    exit();
                }
                return $rs;
            }
            return $rs;
        }
    }

    protected function saveTranslitAlias($id) {
        $new_alias = '';
        $old_alias = '';
        if (1 == $this->getConfigValue('apps.seo.allow_custom_realty_aliases')) {
            $DBC = DBC::getInstance();
            $query = 'SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $old_alias = $ar['translit_alias'];
            }

            if ($old_alias == '') {
                if ('' != $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')) {
                    $fields = explode(',', $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields'));
                    foreach ($fields as $k => $v) {
                        $fields[$k] = trim($v);
                    }
                    $new_alias = $this->createTranslitAliasByFields($id, $fields);
                }

                if ('' != $new_alias) {
                    $new_alias = $this->makeUniqueAlias($new_alias, $id);
                }
            } else {
                return;
            }
        }

        if ($new_alias == '') {
            $DBC = DBC::getInstance();
            $new_alias = $this->createTranslitAliasByFields($id, array('city_id', 'street_id', 'number'));
            if ('' != $new_alias) {
                $new_alias = $this->makeUniqueAlias($new_alias, $id);
            }
        }

        $query = 'UPDATE re_data SET translit_alias=? WHERE id=?';
        $stmt = $DBC->query($query, array($new_alias, $id));
    }

}
