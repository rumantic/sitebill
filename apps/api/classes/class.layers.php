<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Layers REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_layers extends API_Common {
    use \layers\traits\LayerEditor\LayerEditor;
    public function _update_dot () {
        $data_layer_name = $this->request->get('data_layer_name');
        $data_dot_in = $this->request->get('data_dot_in');
        $data_dot_out = $this->request->get('data_dot_out');

        $result = $this->create_connection($data_layer_name, $data_dot_in, $data_dot_out);

        if ( $result ) {
            $ret = array(
                'status' => 'ok',
                'message' => 'dot added successfully',
            );
        } else {
            $ret = array(
                'status' => 'error',
                'message' => 'dot not added',
            );
        }

        return $this->json_string($ret);
    }

}
