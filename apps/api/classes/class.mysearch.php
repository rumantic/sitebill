<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * MySearch REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_mysearch extends API_Common {

    public function _save() {
        $params = $this->request->get('params');
        $this->setRequestValue('params', json_encode($params));
        $this->setRequestValue('params_raw_data', $params);
        $this->setRequestValue('send_by_email', 1);
        $this->setRequestValue('name', $this->request->get('search_title'));
        $user_id = $this->get_my_user_id();
        $this->setRequestValue('user_id', $user_id);
        $_SESSION['user_id'] = $user_id;

        //$params_sql = $this->convert_to_legacy_params($params);
        //$this->setRequestValue('params_sql', json_encode($params_sql));
        //$this->setRequestValue('params_sql', 'test');


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/mysearch/admin/admin.php';
        $mysearch_admin = new mysearch_admin();
        $mysearch_admin->enable_grid_conditions();
        $result = $mysearch_admin->save_search();

        $response = new API_Response('success', 'mysearch saved', array('result' => $result));
        return $this->json_string($response->get());
    }
}
