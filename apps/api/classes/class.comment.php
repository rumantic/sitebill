<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Comment REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_comment extends API_Common {

    private $comment_admin;

    function __construct() {
        parent::__construct();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile.php');
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile_using_model.php');
        $this->user_profile = new User_Profile_Model;

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/comment/admin/admin.php');
        $this->comment_admin = new comment_admin;
    }

    public function _add() {
        $model_name = $this->request->get('model_name');
        $primary_key = $this->request->get('primary_key');
        $key_value = $this->request->get('key_value');
        $comment_text = $this->request->get('comment_text');
        $comment_date = date('Y-m-d H:i:s');
        $user_id = $this->get_my_user_id();
        $form_data = $this->comment_admin->data_model;
        //$form_data = $this->data_model;
        $new_comment_id = $this->comment_admin->add_comment($model_name, $key_value, $user_id, $comment_text, $comment_date);
        if ($this->comment_admin->getError()) {
            $ret = array(
                'status' => 'error',
                'message' => $this->comment_admin->GetErrorMessage(),
            );
        } else {
            $comment_new_data = $this->comment_admin->load_by_id($new_comment_id);
            $user_data = $this->user_profile->load_by_id($comment_new_data['user_id']['value']);
            $comment_new_data['user_id']['avatar'] = $this->get_user_avatar($user_data['imgfile']['value']);
            $comment_new_data['user_id']['fio'] = $user_data['fio']['value'];
            
            $ret = array(
                'status' => 'ok',
                'message' => 'new comment_id '.$new_comment_id,
                'comment_data' => $comment_new_data
            );
        }
        return $this->json_string($ret);
    }

    public function _get() {
        $model_name = $this->request->get('model_name');
        $primary_key = $this->request->get('primary_key');
        $key_value = $this->request->get('key_value');


        $params['page'] = 1;
        $params['per_page'] = 100;
        $params['grid_conditions']['object_id'] = $key_value;
        $params['grid_conditions']['object_type'] = $model_name;
        $default_params['grid_item'] = array('comment_id', 'user_id', 'comment_text', 'comment_date', 'parent_comment_id', 'object_type', 'object_id', 'is_published');
        //$default_params['grid_item_extended'] = array('user_id');
        $this->setRequestValue('_sortby', 'comment_date');
        $this->setRequestValue('_sortdir', 'ASC');

        $rows = $this->comment_admin->grid_array($params, $default_params);
        foreach ($rows as $index => $item) {
            $user_data = $this->user_profile->load_by_id($item['user_id']['value']);
            $rows[$index]['user_id']['avatar'] = $this->get_user_avatar($user_data['imgfile']['value']);
            $rows[$index]['user_id']['fio'] = $user_data['fio']['value'];
        }

        $ret = array(
            'name' => $model_name,
            'rows' => $rows,
        );
        //$this->writeLog(__METHOD__ . ', name = ' . $model_name);
        //$this->writeLog(__METHOD__.', model = <pre>'. var_export($customentity_admin->data_model, true).'</pre>');
        //$this->writeLog(__METHOD__ . ', rows = <pre>' . var_export($rows, true) . '</pre>');
        return $this->json_string($ret);
    }
    
    private function get_user_avatar( $img_name ) {
        if ( $img_name != '' ) {
            return 'http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/img/data/user/' . $img_name;
        } else {
            return 'https://api.sitebill.ru/apps/cloudprovider/assets/images/avatars/profile.jpg';
        }
    }

}
