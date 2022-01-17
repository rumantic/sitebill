<?php


namespace excelfree\api;


use api\aliases\API_model_alias;
use api\aliases\API_response_alias;

class excelfree extends \API_Common
{
    function _export () {
        $input_params = $this->request()->get('input_params');

        $object = $this->init_custom_model_object(
            $this->request()->get('model_name')
        );
        $api_model = new API_model_alias();
        $params = $api_model->convert_to_grid_conditions(
            array(),
            $input_params,
            $object,
            $this->request()->get('model_name')
        );

        $object->public_export($params);
        exit;
    }
}
