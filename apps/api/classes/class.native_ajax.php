<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Comment REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Native_Ajax extends API_Common {

    function main () {
        //Тут обработка для uploadify
        if ( $this->request->get('is_uploadify') == 1 ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/uploadify/uploadify.php');
            $uploadify = new Sitebill_Uploadify();
            echo $uploadify->main( $this->request->get('file') );
            exit;
        }

        if ( $this->request->get('do') == 'view_data_event' ) {
            $data = \system\lib\model\eloquent\Data::where('id', $this->request->get('id'))->first();
            if ( $data ) {
                $data->view_count = ++$data->view_count;
                $data->save();
                $result = "success update id = ".$data->id;
            } else {
                $result = "error on update id = ".$data->id;
            }
            echo $result;
            exit;
        }

        if ( $this->request->get('get_cms_session') == 1 ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.oauth.php');
            $oauth = new API_oauth();
            $result = $oauth->_check_session_key();
            if ($oauth->GetErrorMessage() == 'check_session_key_failed') {
                echo $result;
                exit;
            }

            echo $this->json_string($result);
            exit;
        }

        if ( $this->request->get('exclude_id_from_grid') ) {
            $exclude_ids_array = [];
            if (isset($_COOKIE['exclude_ids_array'])) {
                $exclude_ids_array = json_decode($_COOKIE['exclude_ids_array']);
            }
            $exclude_ids_array[] = $this->request->get('exclude_id_from_grid');
            @setcookie('exclude_ids_array', json_encode($exclude_ids_array), strtotime('+30 days'), '/');
            $response = ['exclude' => 1];
            echo $this->request_success($response);
            exit;
        }


        //Тут обработка для ajax_server
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/ajax/ajax_server.php');
        $ajax_server = new Ajax_Server();
        $response = $ajax_server->main();
        if (is_scalar($response) ) {
            echo $this->request_success($response);
        } else {
            echo $this->json_string($response);
        }
        return;
    }
}
