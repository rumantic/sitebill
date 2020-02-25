<?php

/**
 * User object manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class User_Object_Manager extends Object_Manager {

    private $user_image_dir;
    private $new_user_id;

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'user';
        $this->action = 'user';
        $this->app_title = Multilanguage::_('USER_APP_NAME', 'system');
        $this->primary_key = 'user_id';

        $this->data_model = $this->get_user_model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_manager = new config_admin();
        if (!$config_manager->check_config_item('apps.shop.user_limit_enable')) {
            $config_manager->addParamToConfig('apps.shop.user_limit_enable', '0', 'Активировать режим временных ограничений пользовательских публикаций');
        }

        if (!$config_manager->check_config_item('user_pic_width')) {
            $config_manager->addParamToConfig('user_pic_width', '270', 'Ширина картинки пользователя');
        }

        if (!$config_manager->check_config_item('user_pic_height')) {
            $config_manager->addParamToConfig('user_pic_height', '270', 'Высота картинки пользователя');
        }
    }

    private function set_new_user_id($user_id) {
        $this->new_user_id = $user_id;
    }

    function get_new_user_id() {
        return $this->new_user_id;
    }

    protected function _edit_doneAction() {

        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name]['newpass']['required'] = 'off';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'off';
        unset($form_data[$this->table_name]['captcha']);
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name])) {
            $rs = $this->get_form($form_data[$this->table_name], 'edit');
        } else {
            //$message_array = array('apps_name' => 'user', 'method' => __METHOD__, 'message' => 'Редактирование пользоателя id = '.$form_data[$this->table_name]['user_id']['value'].'<pre>'.var_export($form_data[$this->table_name], true).'</pre>', 'type' => '' );
            //$this->writeLog($message_array);
            $this->edit_data($form_data[$this->table_name]);
            if ($this->getError()) {
                $rs = $this->get_form($form_data[$this->table_name], 'edit');
            } else {
                if ( $this->isRedirectDisabled() ) {
                    return true;
                }
                header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=user');
                exit();
                $rs .= $this->grid();
            }
        }
        return $rs;
    }

    function delete_data($table_name, $primary_key, $primary_key_value) {

        $search_queries = array(
            Multilanguage::_('TABLE_ADS', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_data WHERE user_id=?'
        );
        $ans = array();
        $DBC = DBC::getInstance();
        foreach ($search_queries as $k => $v) {
            $stmt = $DBC->query($v, array($primary_key_value));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['rs'] != 0) {
                    $ans[] = sprintf(Multilanguage::_('MESSAGE_CANT_DELETE', 'system'), $k);
                }
            }
        }
        if (empty($ans)) {
            return parent::delete_data($table_name, $primary_key, $primary_key_value);
        } else {
            return $this->riseError(implode('<br />', $ans));
        }
    }

    /**
     * Просмотр истории действий пользователя из таблицы logger
     * По-умолчанию выборка идет по user_id выбранного пользователя
     * @return string Возвращает таблицу логов сгенерированную через apps.logger.grid()
     */
    protected function _viewlogAction() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/logger/admin/admin.php');
        $logger_admin = new logger_admin();
        $rs = $logger_admin->grid();
        return $rs;
    }

    protected function _editAction() {
        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name]['newpass']['required'] = 'off';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'off';
        unset($form_data[$this->table_name]['captcha']);
        if ($this->getRequestValue('subdo') == 'delete_image') {
            $this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
        }

        if ($this->getRequestValue('subdo') == 'up_image') {
            $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'up');
        }

        if ($this->getRequestValue('subdo') == 'down_image') {
            $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
        }

        if ($this->getRequestValue('language_id') > 0 and ! $this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id'))) {
            $rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
        } else {
            if ($this->getRequestValue('language_id') > 0) {
                $form_data[$this->table_name] = $data_model->init_model_data_from_db_language($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id'));
            } else {
                $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
            }
            $rs = $this->get_form($form_data[$this->table_name], 'edit');
        }
        return $rs;
    }

    function external_add_user() {
        $rs = '';
        $DBC = DBC::getInstance();


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';

        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (isset($form_data[$this->table_name]['reg_date'])) {
            $form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
        }

        if (!$this->check_data($form_data[$this->table_name])) {
            $form_data[$this->table_name]['imgfile']['value'] = '';
            return false;
        } else {
            $new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if ($this->getError()) {
                return false;
            } else {
                $this->set_new_user_id($new_user_id);

                if (1 == $this->getConfigValue('use_registration_email_confirm')) {
                    $activation_code = md5(time() . '_' . rand(100, 999));
                    $query = "UPDATE " . DB_PREFIX . "_user SET pass='" . $activation_code . "' WHERE user_id=" . $new_user_id;
                    $stmt = $DBC->query($query);

                    $activation_link = '<a href="http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $form_data[$this->table_name]['email']['value'] . '">http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $form_data[$this->table_name]['email']['value'] . '</a>';
                    $message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY', 'system'), $activation_link);
                    $subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE', 'system'), $_SERVER['HTTP_HOST']);

                    $to = $form_data[$this->table_name]['email']['value'];
                    $from = $this->getConfigValue('system_email');
                    $this->sendFirmMail($to, $from, $subject, $message);
                    $query = "delete from " . DB_PREFIX . "_cache where parameter='{$activation_code}'";
                    $stmt = $DBC->query($query);
                    $query = "insert into " . DB_PREFIX . "_cache (parameter, `value`) values ('$activation_code', '$password')";
                    $stmt = $DBC->query($query);
                }
                return $new_user_id;
            }
        }
        //$this->_new_doneAction();
    }

    protected function _new_doneAction() {
        $rs = '';
        $DBC = DBC::getInstance();


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
        unset($form_data[$this->table_name]['captcha']);
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (isset($form_data[$this->table_name]['reg_date'])) {
            $form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
        }

        if (!$this->check_data($form_data[$this->table_name])) {
            $form_data[$this->table_name]['imgfile']['value'] = '';
            $rs = $this->get_form($form_data[$this->table_name], 'new');
        } else {
            $new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if ($this->getError()) {
                $form_data[$this->table_name]['imgfile']['value'] = '';
                $rs = $this->get_form($form_data[$this->table_name], 'new');
            } else {
                $this->set_new_user_id($new_user_id);

                if (1 == $this->getConfigValue('use_registration_email_confirm')) {
                    
                    $password = $form_data[$this->table_name]['newpass']['value'];
                    
                    $activation_code = md5(time() . '_' . rand(100, 999));
                    $query = "UPDATE " . DB_PREFIX . "_user SET pass='" . $activation_code . "' WHERE user_id=" . $new_user_id;
                    $stmt = $DBC->query($query);

                    if (1 == intval($this->getConfigValue('email_as_login'))) {
                        $mail_login = $form_data[$this->table_name]['email']['value'];
                    } else {
                        $mail_login = $form_data[$this->table_name]['login']['value'];
                    }
                    $mail_pass = $form_data[$this->table_name]['newpass']['value'];

                    $activation_link = '<a href="http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $form_data[$this->table_name]['email']['value'] . '">http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $form_data[$this->table_name]['email']['value'] . '</a>';
                    $message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY', 'system'), $activation_link);
                    $subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE', 'system'), $_SERVER['HTTP_HOST']);

                    $to = $form_data[$this->table_name]['email']['value'];
                    $from = $this->getConfigValue('system_email');

                    $this->sendFirmMail($to, $from, $subject, $message);

                    $query = "delete from " . DB_PREFIX . "_cache where parameter='{$activation_code}'";
                    $stmt = $DBC->query($query);
                    $query = "insert into " . DB_PREFIX . "_cache (parameter, `value`) values ('$activation_code', '$password')";
                    $stmt = $DBC->query($query);
                }
                if ( $this->isRedirectDisabled() ) {
                    return true;
                }
                
                header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=user');
                exit();
                $rs .= $this->grid();
            }
        }
        return $rs;
    }

    protected function _newAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
        unset($form_data[$this->table_name]['captcha']);
        $rs = $this->get_form($form_data[$this->table_name]);
        return $rs;
    }

    protected function _showlogAction() {
        $rs = '';
        if ($this->getConfigValue('apps.logger.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/logger/admin/admin.php')) {
            header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=logger&user_id=' . intval($this->getRequestValue('user_id')));
            exit();
        }
        return 'Функционал недоступен';
    }

    /**
     * Edit data
     * @param array $form_data form data
     * @param int $language_id language id
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {
        if ( !$primary_key_value ) {
            $primary_key_value = $this->getRequestValue($this->primary_key);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $queryp = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $primary_key_value, $form_data, $language_id);
        $DBC = DBC::getInstance();

        $stmt = $DBC->query($queryp['q'], $queryp['p'], $row, $success);
        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        if (isset($_POST['delpic'])) {
            $user_id = (int) $primary_key_value;
            $this->deleteUserpic($user_id);
        }

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int) $primary_key_value);
                $this->set_imgs($imgs_uploads);
            }
        }

        if (strlen($form_data['imgfile']['value']) > 0) {
            //$this->user_image_dir = $form_data['imgfile']['path']; 
            $this->update_photo($primary_key_value);
        }

        if ($form_data['newpass']['value'] != '') {
            $this->editPassword($primary_key_value, $form_data['newpass']['value']);
        }
    }

    function load_profile($record_id) {
        $data = $this->load_by_id($record_id);

        return $data;
    }


    function appendUploadsUser($table, $pk_field, $record_id, $name_template = '') {
        $field_name = 'imgfile';
        $session_key = (string) $this->get_session_key();


        $action = $table;
        if (!isset($record_id) || $record_id == 0) {
            return false;
        }

        $DBC = DBC::getInstance();

        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/user/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $uploads = $this->load_uploadify_images($session_key, $field_name);
        if (!$uploads) {
            $uploads = $this->getExternalUploadifyImageArray();
            if (!$uploads) {
                return false;
            }
        }

        $max_img_count = 1;

        $attached_yet = array();
        
        $i = 0;
        $max_filesize = (int) str_replace('M', '', ini_get('upload_max_filesize'));
        if (isset($parameters['max_file_size']) && (int) $parameters['max_file_size'] != 0) {
            $max_filesize = (int) $parameters['max_file_size'];
        }

        if ($max_img_count > -1) {
            $last_count = $max_img_count - count($attached_yet);
            if ($last_count > 0) {
                $uploads = array_slice($uploads, 0, $last_count);
            } else {
                $uploads = array();
            }
        }
        if (!empty($uploads)) {

            $folder_name = '';

            $uniq_file_name = uniqid() . '_' . time();

            foreach ($uploads as $image_name) {
                $i++;
                $need_prv = 0;
                $preview_name = '';
                $filesize = filesize($uploadify_path . $image_name) / (1024 * 1024);
                if ($filesize > $max_filesize) {
                    continue;
                }
                if (!empty($image_name)) {
                    $arr = explode('.', $image_name);
                    $ext = strtolower(end($arr));



                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($uploadify_path . $image_name, 0, true);
                        if (isset($exif['IFD0']) && isset($exif['IFD0']['Orientation']) && false === empty($exif['IFD0']['Orientation'])) {
                            switch ($exif['IFD0']['Orientation']) {
                                case 8:
                                    $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, 90);
                                    break;
                                case 3:
                                    $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, 180);
                                    break;
                                case 6:
                                    $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, -90);
                                    break;
                            }
                        }
                    }
                    //$ext=strtolower($arr[count($arr)-1]);
                    if ((1 == $this->getConfigValue('seo_photo_name_enable')) AND ( $name_template != '')) {
                        $name_template = substr($name_template, 0, 150);
                        if ($i == 0) {
                            $preview_name_no_ext = $name_template;
                            $prv_no_ext = $name_template . "_prev";
                        } else {
                            $preview_name_no_ext = $name_template . "_" . $i;
                            $prv_no_ext = $name_template . "_prev" . $i;
                        }

                        if (file_exists($path . $preview_name_no_ext . "." . $ext)) {
                            $rand = rand(0, 1000);
                            while (file_exists($path . $preview_name_no_ext . "_" . $rand . "." . $ext)) {
                                $rand = rand(0, 1000);
                            }
                            $preview_name = $preview_name_no_ext . "_" . $rand . "." . $ext;
                            $prv = $prv_no_ext . "_" . $rand . "." . $ext;
                        } else {
                            $preview_name = $preview_name_no_ext . "." . $ext;
                            $prv = $prv_no_ext . "." . $ext;
                        }
                    } else {
                        $nm = $uniq_file_name . '_' . $i;
                        $preview_name = 'img' . $nm . "." . $ext;
                        $prv = "prv" . $nm . "." . $ext;
                        $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                    }

                    if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
                        $big_width = $this->getConfigValue('user_pic_width');
                        $big_height = $this->getConfigValue('user_pic_height');

                        if (isset($parameters['norm_width']) && (int) $parameters['norm_width'] != 0) {
                            $big_width = (int) $parameters['norm_width'];
                        }

                        if (isset($parameters['norm_height']) && (int) $parameters['norm_height'] != 0) {
                            $big_height = (int) $parameters['norm_height'];
                        }

                        if (isset($parameters['prev_width']) && (int) $parameters['prev_width'] != 0) {
                            $preview_width = (int) $parameters['prev_width'];
                        }

                        if (isset($parameters['prev_height']) && (int) $parameters['prev_height'] != 0) {
                            $preview_height = (int) $parameters['prev_height'];
                        }

                        if ($folder_name != '') {
                            $preview_name = $folder_name . '/' . $preview_name;
                            $prv = $folder_name . '/' . $prv;
                        }
                        if (intval($parameters['normal_smart_resizing']) == 1) {
                            $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 'smart');
                        } else {
                            $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 1);
                        }

                        if ($rn) {
                            chmod($path . $preview_name, 0644);
                            /**/
                            $ra[$i]['normal'] = $preview_name;
                            $ra[$i]['preview'] = $preview_name;
                        }
                    }
                    if ($rn) {
                        $attached_yet[] = array('normal' => $preview_name, 'type' => 'graphic', 'mime' => $ext);
                    }
                }
            }

            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $field_name . '`=? WHERE `' . $pk_field . '`=?';
            $stmt = $DBC->query($query, array($preview_name, $record_id));
        }

        $this->delete_uploadify_images($session_key, $field_name);
        return $ra;
    }
    

    protected function deleteUserpic($user_id) {
        $DBC = DBC::getInstance();
        $query = 'SELECT imgfile FROM ' . DB_PREFIX . '_user WHERE user_id=?';
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);

            $imgfile_directory = SITEBILL_DOCUMENT_ROOT . '/img/data/user/';
            if ($ar['imgfile'] != '' && file_exists($imgfile_directory . $ar['imgfile'])) {
                @unlink($imgfile_directory . $ar['imgfile']);
            }

            $query = 'UPDATE ' . DB_PREFIX . '_user SET imgfile=\'\' WHERE user_id=?';
            $stmt = $DBC->query($query, array($user_id));
        }
    }

    /**
     * Get group ID by group name
     * @param string $group_name group name
     * @return integer
     */
    function getGroupIdByName($group_name) {
        $query = "select group_id from " . DB_PREFIX . "_group where system_name='$group_name'";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['group_id'];
        }
        return 0;
    }

    /**
     * Add data
     * @param array $form_data form data
     * @param int $language_id
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        //$query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
        $queryp = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data, $language_id);

        $DBC = DBC::getInstance();

        $stmt = $DBC->query($queryp['q'], $queryp['p'], $row, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        $new_record_id = $DBC->lastInsertId();

        if (strlen($form_data['imgfile']['value']) > 0) {
            //$this->user_image_dir = $form_data['imgfile']['path']; 
            //$this->user_image_dir='/img/data/user/';
            $this->update_photo($new_record_id);
        }

        if ($form_data['newpass']['value'] != '') {
            $this->editPassword($new_record_id, $form_data['newpass']['value']);
        }

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                $this->set_imgs($imgs_uploads);
            }
        }

        //echo "new_record_id = $new_record_id<br>";
        //echo $query;
        return $new_record_id;
    }

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        if (!empty($form_data['login'])) {
            if ($this->getRequestValue('do') != 'edit_done') {
                if (!$this->checkLogin($form_data['login']['value'])) {
                    $this->riseError('Такой login уже зарегистрирован');
                    return false;
                }
            } else {
                if (!$this->checkDiffLogin($form_data['login']['value'], $form_data['user_id']['value'])) {
                    $this->riseError('Такой login уже зарегистрирован');
                    return false;
                }
            }
        } else {
            if (isset($form_data['email'])) {
                if ($this->getRequestValue('do') != 'edit_done') {
                    if (!$this->checkEmail($form_data['email']['value'])) {
                        $this->riseError('Такой email уже зарегистрирован');
                        return false;
                    }
                } else {
                    if (!$this->checkDiffEmail($form_data['email']['value'], $form_data['user_id']['value'])) {
                        $this->riseError('Такой email уже зарегистрирован!');
                        return false;
                    }
                }
            }
        }

        if (!$data_model->check_data($form_data)) {
            $this->riseError($data_model->GetErrorMessage());
            return false;
        }

        if (!preg_match('/^([a-zA-Z0-9-_\.@]*)$/', $form_data['login']['value'])) {
            $this->riseError('Логин может содержать только латинские буквы, цифры, подчеркивание, тире, амперсанд и точку');
            return false;
        }

        if ($form_data['newpass']['value'] != '') {
            if (strlen($form_data['newpass']['value']) < 5) {
                $this->riseError(sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH', 'system'), '5'));
                return false;
            }
            if ($form_data['newpass']['value'] != $form_data['newpass_retype']['value']) {
                $this->riseError(Multilanguage::_('PASSWORDS_NOT_EQUAL', 'system'));
                return false;
            }
        }

        return true;
    }

    /**
     * Check login
     * @param string $login login
     * @return boolean
     */
    function checkLogin($login) {
        $query = 'select count(*) as cid from ' . DB_PREFIX . '_user where login=\'' . $login . '\'';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['cid'] > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check diff login not for this record id
     * @param string $login login
     * @param int $user_id
     * @return boolean
     */
    function checkDiffLogin($login, $user_id) {
        $query = 'select count(*) as cid from ' . DB_PREFIX . '_user where login=\'' . $login . '\' and user_id<>' . $user_id;
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['cid'] > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check email
     * @param string $email email
     * @return boolean
     */
    function checkEmail($email) {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(*) AS cid FROM ' . DB_PREFIX . '_user WHERE LOWER(email)=?';
        $stmt = $DBC->query($query, array(strtolower($email)));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['cid'] > 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Check diff email not for this record id
     * @param string $email email
     * @param int $user_id
     * @return boolean
     */
    function checkDiffEmail($email, $user_id) {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(*) AS cid FROM ' . DB_PREFIX . '_user WHERE LOWER(email)=? AND user_id<>?';
        $stmt = $DBC->query($query, array(strtolower($email), $user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['cid'] > 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Edit password
     * @param int $user_id user id
     * @param string $password password
     * @return boolean
     */
    function editPassword($user_id, $password) {
        $query = "update " . DB_PREFIX . "_user set password='" . md5($password) . "' where user_id=$user_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        return true;
    }

    function update_photo($user_id) {
        /* if ( SITEBILL_MAIN_URL != '' ) {
          $add_folder = SITEBILL_MAIN_URL.'/';
          } */


        //global $sitebill_document_root;
        //echo '$sitebill_document_root = '.$sitebill_document_root.'<br>';
        //echo '$add_folder = '.$add_folder.'<br>';
        $this->user_image_dir = '/img/data/user/';
        $imgfile_directory = $this->user_image_dir;

        //$document_root = $_SERVER['DOCUMENT_ROOT'].$add_folder; 


        $avial_ext = array('jpg', 'jpeg', 'gif', 'png');
        if (isset($_FILES['imgfile'])) {

            if (($_FILES['imgfile']['error'] != 0) OR ( $_FILES['imgfile']['size'] == 0)) {
                //echo 'Не указан или указан не верно файл для загрузки<br>';
            } else {
                $fprts = explode('.', $_FILES['imgfile']['name']);
                if (count($fprts) > 1) {
                    $ext = strtolower($fprts[count($fprts) - 1]);

                    if (in_array($ext, $avial_ext)) {

                        $mode = 1;
                        if (1 == $this->getConfigValue('user_pic_smart')) {
                            $mode = 'f';
                        }

                        $usrfilename = time() . '.' . $ext;
                        $i = rand(0, 999);
                        if ($mode == 'f') {
                            $preview_name = "usr" . uniqid() . '_' . time() . ".png";
                        } else {
                            $preview_name = "usr" . uniqid() . '_' . time() . "." . $ext;
                        }

                        $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;

                        if (!move_uploaded_file($_FILES['imgfile']['tmp_name'], SITEBILL_DOCUMENT_ROOT . $imgfile_directory . $preview_name_tmp)) {
                            
                        } else {
                            $this->deleteUserpic($user_id);


                            if ($mode == 'f') {
                                $r = $this->makePreview(SITEBILL_DOCUMENT_ROOT . $imgfile_directory . $preview_name_tmp, SITEBILL_DOCUMENT_ROOT . $imgfile_directory . $preview_name, $this->getConfigValue('user_pic_width'), $this->getConfigValue('user_pic_height'), $ext, $mode, 'png');
                            } else {
                                $r = $this->makePreview(SITEBILL_DOCUMENT_ROOT . $imgfile_directory . $preview_name_tmp, SITEBILL_DOCUMENT_ROOT . $imgfile_directory . $preview_name, $this->getConfigValue('user_pic_width'), $this->getConfigValue('user_pic_height'), $ext, $mode);
                            }

                            unlink(SITEBILL_DOCUMENT_ROOT . $imgfile_directory . $preview_name_tmp);
                            if (false !== $r) {
                                $query = 'UPDATE ' . DB_PREFIX . '_user SET imgfile="' . $preview_name . '" WHERE user_id=' . $user_id;
                                $DBC = DBC::getInstance();
                                $stmt = $DBC->query($query);
                            }
                        }
                    }
                }
            }
        }
    }

    function get_user_model($ignore_user_group = false) {
        $form_data = array();
        $table_name = 'user';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name, $ignore_user_group);
            if (empty($form_data)) {
                $form_data = array();
                $form_data = $this->_get_user_model();
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name, $ignore_user_group);
            }
        } else {
            $form_data = $this->_get_user_model($ajax);
        }

        /* if ( $this->getConfigValue('use_registration_email_confirm')  ) {
          $form_data['user']['active']['name'] = 'active';
          $form_data['user']['active']['title'] = 'Активен';
          $form_data['user']['active']['value'] = '';
          $form_data['user']['active']['type'] = 'checkbox';
          } */


        return $form_data;
    }

    /**
     * Get user model
     * @param void
     * @return array
     */
    function _get_user_model() {
        $form_user = array();

        $form_user['user']['user_id']['name'] = 'user_id';
        $form_user['user']['user_id']['title'] = Multilanguage::_('L_ID');
        $form_user['user']['user_id']['value'] = '';
        $form_user['user']['user_id']['length'] = 40;
        $form_user['user']['user_id']['type'] = 'primary_key';
        $form_user['user']['user_id']['required'] = 'off';
        $form_user['user']['user_id']['unique'] = 'off';

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/group/group_manager.php')) {
            $form_user['user']['group_id']['name'] = 'group_id';
            $form_user['user']['group_id']['primary_key_name'] = 'group_id';
            $form_user['user']['group_id']['primary_key_table'] = 'group';
            $form_user['user']['group_id']['title'] = Multilanguage::_('GROUP', 'system');
            $form_user['user']['group_id']['value'] = 0;
            $form_user['user']['group_id']['length'] = 40;
            $form_user['user']['group_id']['type'] = 'select_by_query';
            $form_user['user']['group_id']['query'] = 'select * from ' . DB_PREFIX . '_group order by name';
            $form_user['user']['group_id']['value_name'] = 'name';
            $form_user['user']['group_id']['title_default'] = Multilanguage::_('L_CHOOSE_GROUP');
            $form_user['user']['group_id']['value_default'] = 0;
            $form_user['user']['group_id']['required'] = 'on';
            $form_user['user']['group_id']['unique'] = 'off';
            $form_user['user']['group_id']['group_id'] = '1';
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/company/admin/admin.php')) {
            if ($this->getConfigValue('apps.company.enable')) {
                $form_user['user']['company_id']['name'] = 'company_id';
                $form_user['user']['company_id']['primary_key_name'] = 'company_id';
                $form_user['user']['company_id']['primary_key_table'] = 'company';
                $form_user['user']['company_id']['title'] = Multilanguage::_('COMPANY', 'system');
                $form_user['user']['company_id']['value'] = 0;
                $form_user['user']['company_id']['length'] = 40;
                $form_user['user']['company_id']['type'] = 'select_by_query';
                $form_user['user']['company_id']['query'] = 'select * from ' . DB_PREFIX . '_company order by name';
                $form_user['user']['company_id']['value_name'] = 'name';
                $form_user['user']['company_id']['title_default'] = Multilanguage::_('L_CHOOSE_COMPANY');
                $form_user['user']['company_id']['value_default'] = 0;
                $form_user['user']['company_id']['required'] = 'on';
                $form_user['user']['company_id']['unique'] = 'off';
            }
        }

        $form_user['user']['notify']['name'] = 'notify';
        $form_user['user']['notify']['title'] = 'Получать уведомления на почту';
        $form_user['user']['notify']['value'] = 0;
        $form_user['user']['notify']['type'] = 'checkbox';
        $form_user['user']['notify']['required'] = 'off';
        $form_user['user']['notify']['unique'] = 'off';
        $form_user['user']['notify']['group_id'] = '1';
        
        $form_user['user']['active']['name'] = 'active';
        $form_user['user']['active']['title'] = 'Активен';
        $form_user['user']['active']['value'] = 0;
        $form_user['user']['active']['type'] = 'checkbox';
        $form_user['user']['active']['required'] = 'off';
        $form_user['user']['active']['unique'] = 'off';
        $form_user['user']['active']['group_id'] = '1';

        $form_user['user']['login']['name'] = 'login';
        $form_user['user']['login']['title'] = 'Login';
        $form_user['user']['login']['value'] = '';
        $form_user['user']['login']['length'] = 40;
        $form_user['user']['login']['type'] = 'safe_string';
        $form_user['user']['login']['required'] = 'on';
        $form_user['user']['login']['unique'] = 'off';

        $form_user['user']['reg_date']['name'] = 'reg_date';
        $form_user['user']['reg_date']['title'] = Multilanguage::_('REG_DATE', 'system');
        $form_user['user']['reg_date']['value'] = date('Y-m-d H:i:s', time());
        $form_user['user']['reg_date']['length'] = 40;
        $form_user['user']['reg_date']['type'] = 'hidden';
        $form_user['user']['reg_date']['required'] = 'off';
        $form_user['user']['reg_date']['unique'] = 'off';

        $form_user['user']['newpass']['name'] = 'newpass';
        $form_user['user']['newpass']['title'] = Multilanguage::_('NEW_PASS', 'system');
        $form_user['user']['newpass']['value'] = '';
        $form_user['user']['newpass']['length'] = 40;
        $form_user['user']['newpass']['type'] = 'password';
        $form_user['user']['newpass']['dbtype'] = 'notable';
        $form_user['user']['newpass']['required'] = 'off';
        $form_user['user']['newpass']['unique'] = 'off';

        $form_user['user']['newpass_retype']['name'] = 'newpass_retype';
        $form_user['user']['newpass_retype']['title'] = Multilanguage::_('RETYPE_NEW_PASS', 'system');
        $form_user['user']['newpass_retype']['value'] = '';
        $form_user['user']['newpass_retype']['length'] = 40;
        $form_user['user']['newpass_retype']['type'] = 'password';
        $form_user['user']['newpass_retype']['dbtype'] = 'notable';
        $form_user['user']['newpass_retype']['required'] = 'off';
        $form_user['user']['newpass_retype']['unique'] = 'off';

        $form_user['user']['fio']['name'] = 'fio';
        $form_user['user']['fio']['title'] = Multilanguage::_('L_FIO');
        $form_user['user']['fio']['value'] = '';
        $form_user['user']['fio']['length'] = 40;
        $form_user['user']['fio']['type'] = 'safe_string';
        $form_user['user']['fio']['required'] = 'on';
        $form_user['user']['fio']['unique'] = 'off';

        $form_user['user']['email']['name'] = 'email';
        $form_user['user']['email']['title'] = 'Email';
        $form_user['user']['email']['value'] = '';
        $form_user['user']['email']['length'] = 40;
        $form_user['user']['email']['type'] = 'safe_string';
        $form_user['user']['email']['required'] = 'on';
        $form_user['user']['email']['unique'] = 'on';

        $form_user['user']['phone']['name'] = 'phone';
        $form_user['user']['phone']['title'] = Multilanguage::_('L_PHONE');
        $form_user['user']['phone']['value'] = '';
        $form_user['user']['phone']['length'] = 40;
        $form_user['user']['phone']['type'] = 'safe_string';
        $form_user['user']['phone']['required'] = 'off';
        $form_user['user']['phone']['unique'] = 'off';

        $form_user['user']['mobile']['name'] = 'mobile';
        $form_user['user']['mobile']['title'] = Multilanguage::_('L_CELLPHONE');
        $form_user['user']['mobile']['value'] = '';
        $form_user['user']['mobile']['length'] = 40;
        $form_user['user']['mobile']['type'] = 'safe_string';
        $form_user['user']['mobile']['required'] = 'off';
        $form_user['user']['mobile']['unique'] = 'off';

        $form_user['user']['icq']['name'] = 'icq';
        $form_user['user']['icq']['title'] = Multilanguage::_('L_ICQNR');
        $form_user['user']['icq']['value'] = '';
        $form_user['user']['icq']['length'] = 40;
        $form_user['user']['icq']['type'] = 'safe_string';
        $form_user['user']['icq']['required'] = 'off';
        $form_user['user']['icq']['unique'] = 'off';

        $form_user['user']['site']['name'] = 'site';
        $form_user['user']['site']['title'] = Multilanguage::_('L_SITE');
        $form_user['user']['site']['value'] = '';
        $form_user['user']['site']['length'] = 40;
        $form_user['user']['site']['type'] = 'safe_string';
        $form_user['user']['site']['required'] = 'off';
        $form_user['user']['site']['unique'] = 'off';

        $form_user['user']['imgfile']['name'] = 'imgfile';
        $form_user['user']['imgfile']['title'] = Multilanguage::_('L_PHOTO');
        $form_user['user']['imgfile']['value'] = '';
        $form_user['user']['imgfile']['length'] = 40;
        $form_user['user']['imgfile']['type'] = 'photo';
        $form_user['user']['imgfile']['path'] = '/img/data/user/';
        $form_user['user']['imgfile']['required'] = 'off';
        $form_user['user']['imgfile']['unique'] = 'off';

        if ($this->getConfigValue('user_account_enable')) {
            $form_user['user']['account']['name'] = 'account';
            $form_user['user']['account']['title'] = Multilanguage::_('L_ACCOUNT');
            $form_user['user']['account']['value'] = 0;
            $form_user['user']['account']['length'] = 40;
            $form_user['user']['account']['type'] = 'safe_string';
            $form_user['user']['account']['required'] = 'off';
            $form_user['user']['account']['unique'] = 'off';
        }

        if ($this->getConfigValue('apps.shop.user_limit_enable')) {
            $form_user['user']['publication_limit']['name'] = 'publication_limit';
            $form_user['user']['publication_limit']['title'] = Multilanguage::_('PUBLICATION_TIMELIMIT', 'system');
            $form_user['user']['publication_limit']['value'] = $this->getConfigValue('user_publication_limit');
            $form_user['user']['publication_limit']['length'] = 40;
            $form_user['user']['publication_limit']['type'] = 'safe_string';
            $form_user['user']['publication_limit']['required'] = 'off';
            $form_user['user']['publication_limit']['unique'] = 'off';
        }

        return $form_user;
    }

    function grid_v() {
        $srch_p = $this->getRequestValue('srch');
        $ret = array();

        $where_parts = array();
        $where_vals = array();
        if ($srch_p !== NULL) {
            if (isset($srch_p['group_id'])) {
                if (!is_array($srch_p['group_id'])) {
                    $srch_p['group_id'] = (array) $srch_p['group_id'];
                }
                foreach ($srch_p['group_id'] as $k => $v) {
                    if (intval($v) == 0) {
                        unset($srch_p['group_id'][$k]);
                    }
                }
                if (empty($srch_p['group_id'])) {
                    unset($srch_p['group_id']);
                }
            }
            if (isset($srch_p['company_id'])) {
                if (!is_array($srch_p['company_id'])) {
                    $srch_p['company_id'][] = $srch_p['company_id'];
                }
                foreach ($srch_p['company_id'] as $k => $v) {
                    if (intval($v) == 0) {
                        unset($srch_p['company_id'][$k]);
                    }
                }
                if (empty($srch_p['company_id'])) {
                    unset($srch_p['company_id']);
                }
            }
            if (isset($srch_p['login'])) {
                $srch_p['login'] = trim($srch_p['login']);
                if ($srch_p['login'] == '') {
                    unset($srch_p['login']);
                }
            }
            if (isset($srch_p['email'])) {
                $srch_p['email'] = trim($srch_p['email']);
                if ($srch_p['email'] == '') {
                    unset($srch_p['email']);
                }
            }
            if (isset($srch_p['fio'])) {
                $srch_p['fio'] = trim($srch_p['fio']);
                if ($srch_p['fio'] == '') {
                    unset($srch_p['fio']);
                }
            }



            foreach ($srch_p as $k => $value) {
                /* if($k=='group_id'){
                  $where_parts[]='(`group_id` IN ('.implode(',', array_fill(0, count($value), '?')).'))';
                  $where_vals=array_merge($where_vals, $value);
                  }
                  if($k=='company_id'){
                  $where_parts[]='(`company_id` IN ('.implode(',', array_fill(0, count($value), '?')).'))';
                  $where_vals=array_merge($where_vals, $value);
                  }
                  if($k=='login'){
                  $where_parts[]='(`login` LIKE ?)';
                  $where_vals[]='%'.$value.'%';
                  }
                  if($k=='email'){
                  $where_parts[]='(`email` LIKE ?)';
                  $where_vals[]='%'.$value.'%';
                  }
                  if($k=='fio'){
                  $where_parts[]='(`fio` LIKE ?)';
                  $where_vals[]='%'.$value.'%';
                  } */
                if ($k == 'group_id') {
                    $where_parts[] = '(`group_id` IN (' . implode(',', $value) . '))';
                    $where_vals = array_merge($where_vals, $value);
                }
                if ($k == 'company_id') {
                    $where_parts[] = '(`company_id` IN (' . implode(',', $value) . '))';
                    $where_vals = array_merge($where_vals, $value);
                }
                if ($k == 'login') {
                    $where_parts[] = '(`login` LIKE \'%' . $value . '%\')';
                    $where_vals[] = '%' . $value . '%';
                }
                if ($k == 'email') {
                    $where_parts[] = '(`email` LIKE \'%' . $value . '%\')';
                    $where_vals[] = '%' . $value . '%';
                }
                if ($k == 'fio') {
                    $where_parts[] = '(`fio` LIKE \'%' . $value . '%\')';
                    $where_vals[] = '%' . $value . '%';
                }
                if ($k == 'reg_date_from') {
                    $where_parts[] = '(`reg_date` >= \'' . $value . ' 23:59:59' . '\')';
                    $where_vals[] = '%' . $value . '%';
                }
                if ($k == 'reg_date_for') {
                    $where_parts[] = '(`reg_date` <= \'' . $value . ' 23:59:59' . '\')';
                    $where_vals[] = '%' . $value . '%';
                }
            }
        }


        $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_user' . (!empty($where_parts) ? ' WHERE ' . implode(' AND ', $where_parts) : '');
        //echo $query;
        //print_r($where_vals);
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($query));

        //echo $DBC->getLastError();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar['user_id'];
            }
        }
        //var_dump($ret);
        if (!empty($ret)) {
            $query = 'SELECT COUNT(id) AS _cnt, user_id FROM ' . DB_PREFIX . '_data WHERE user_id IN (' . implode(',', $ret) . ') GROUP BY user_id';
            $stmt = $DBC->query($query, array($query));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret_count['data'][$ar['user_id']] = $ar['_cnt'];
                }
            }
            $query = 'SELECT COUNT(comment_id) AS _cnt, user_id FROM ' . DB_PREFIX . '_comment WHERE user_id IN (' . implode(',', $ret) . ') GROUP BY user_id';
            $stmt = $DBC->query($query, array($query));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret_count['comment'][$ar['user_id']] = $ar['_cnt'];
                }
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();

            foreach ($ret as $k => $id) {

                $form_data = $this->data_model;

                $ret[$k] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $id, $form_data[$this->table_name], true);
                if (!empty($ret_count)) {
                    foreach (array_keys($ret_count) as $key) {
                        if (isset($ret_count[$key][$id])) {
                            $ret[$k]['_' . $key] = $ret_count[$key][$id];
                        } else {
                            $ret[$k]['_' . $key] = 0;
                        }
                    }
                }

                $ret[$k]['_href'] = $this->getUserHREF($ret[$k]['user_id']['value']);

                $time_with_us = time() - strtotime($ret[$k]['reg_date']['value']);
                if ($time_with_us / 31536000 > 1) {
                    $ret[$k]['_time_with_us'] = ceil($time_with_us / 31536000) . ' лет';
                } elseif ($time_with_us / 2592000 > 1) {
                    $ret[$k]['_time_with_us'] = ceil($time_with_us / 2592000) . ' месяцев';
                } elseif ($time_with_us / 86400 > 1) {
                    $ret[$k]['_time_with_us'] = ceil($time_with_us / 86400) . ' дней';
                } elseif ($time_with_us / 3600 > 1) {
                    $ret[$k]['_time_with_us'] = ceil($time_with_us / 3600) . ' часов';
                } else {
                    $ret[$k]['_time_with_us'] = ceil($time_with_us / 60) . ' минут';
                }
            }

            /* $rs='';
              foreach($ret as $k=>$user){
              //print_r($user['user_id']);
              $rs.=$user['user_id']['value'].'<br>';


              } */
        }
        global $smarty;
        $this->template->assign('user_list', $ret);
        $rs = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/admin.users.list.tpl');


        return $rs;

        $default_params['grid_item'] = array('user_id', 'login', 'fio', 'reg_date', 'group_id', 'email', 'phone');

        $where_parts = array();
        $group_id = (int) $this->getRequestValue('group_id');
        $company_id = (int) $this->getRequestValue('company_id');

        $conditions = array();

        if ($group_id > 0) {
            $where_parts[] = 'group_id=' . $group_id;
            $conditions['group_id'] = $group_id;
        }

        if ($company_id > 0) {
            $where_parts[] = 'company_id=' . $company_id;
            $conditions['company_id'] = $company_id;
        }
        if (count($conditions) > 0) {
            $params['grid_conditions'] = $conditions;
        }
        return parent::grid($params, $default_params);
    }

    function grid($params = array(), $default_params = array()) {
        $default_params['grid_item'] = array('user_id', 'login', 'fio', 'reg_date', 'group_id', 'email', 'phone');

        $where_parts = array();
        $group_id = (int) $this->getRequestValue('group_id');
        $company_id = (int) $this->getRequestValue('company_id');

        $conditions = array();

        if ($group_id > 0) {
            $where_parts[] = 'group_id=' . $group_id;
            $conditions['group_id'] = $group_id;
        }

        if ($company_id > 0) {
            $where_parts[] = 'company_id=' . $company_id;
            $conditions['company_id'] = $company_id;
        }
        if (count($conditions) > 0) {
            $params['grid_conditions'] = $conditions;
        }

        $params['grid_controls'] = array('edit', 'delete');
        if ($this->getConfigValue('apps.logger.enable')) {
            array_push($params['grid_controls'], 'viewlog');
        }
        return parent::grid($params, $default_params);
    }

    /**
     * Get top menu
     * @param void 
     * @return string
     */
    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('ADD_USER', 'system') . '</a>';

        //select * from re_company order by name
        //$rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">Добавить пользователя</a>';
        return $rs;
    }

    function getAdditionalSearchForm() {
        $query = 'select * from re_group order by name';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ret .= '<form method="post" action="' . SITEBILL_MAIN_URL . '/admin/index.php?action=user">';
        $ret .= '<select name="group_id">';
        $ret .= '<option value="">' . Multilanguage::_('ANY_GROUP', 'system') . '</option>';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($this->getRequestValue('group_id') == $ar['group_id']) {
                    $ret .= '<option value="' . $ar['group_id'] . '" selected="selected">' . $ar['name'] . '</option>';
                } else {
                    $ret .= '<option value="' . $ar['group_id'] . '">' . $ar['name'] . '</option>';
                }
            }
        }

        $ret .= '</select>';
        if ($this->getConfigValue('apps.company.enable') == 1) {
            $query = 'select * from re_company order by name';
            $stmt = $DBC->query($query);
            $ret .= '<select name="company_id">';
            $ret .= '<option value="">' . Multilanguage::_('ANY_COMPANY', 'system') . '</option>';
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ($this->getRequestValue('company_id') == $ar['company_id']) {
                        $ret .= '<option value="' . $ar['company_id'] . '" selected="selected">' . $ar['name'] . '</option>';
                    } else {
                        $ret .= '<option value="' . $ar['company_id'] . '">' . $ar['name'] . '</option>';
                    }
                }
            }
            $ret .= '</select>';
        }
        $ret .= '<input type="hidden" name="action" value="' . $this->action . '">';
        $ret .= '<input type="submit" name="submit" value="' . Multilanguage::_('L_TEXT_SELECT') . '">';
        $ret .= '</form>';
        return $ret;
    }

}
