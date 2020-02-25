<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Memorylist REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_memorylist extends API_Common {

    public function _toggle() {
        $domain = $this->request->get('domain');
        $deal_id = $this->request->get('deal_id');
        $title = $this->request->get('title');
        $data_id = $this->request->get('data_id');
        $user_id = $this->get_my_user_id();
        
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
        $ML = new Memory_List();
        $memorylist_id = $ML->get_domain_memory_list_id($domain, $user_id, $deal_id, $title);
        if ( $memorylist_id ) {
            $operation = $ML->toggle_item($memorylist_id, $data_id);
        }

        $response = new API_Response('success', 'memorylist toggle '.$memorylist_id, array('operation' => $operation));
        return $this->json_string($response->get());
    }

}
