<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Table REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_table_controller extends API_Common {
    public function _update_column_params () {
        $data_layer_class = $this->request->get('data_layer_class');
        $data_external_model = $this->request->get('data_external_model');
        $data_sitebill_model = $this->request->get('data_sitebill_model');
        $data_column = $this->request->get('data_column');

        //Получаем предыдущие значения columns.parameters

        $ret = array(
            'status' => 'ok',
            'message' => 'd',
        );
        return $this->json_string($ret);
    }

}
