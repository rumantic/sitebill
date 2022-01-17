<?php
namespace system\lib\model\traits\lifecycle;

use Illuminate\Support\Str;

trait AfterRequestInitTrait
{
    function after_request_init( $model_array ) {
        foreach ($model_array as $key => $item_array) {
            if (isset($model_array[$key]['parameters']) and is_array($model_array[$key]['parameters']) and count($model_array[$key]['parameters']) > 0) {
                $model_array = $this->process_parameters($model_array, $key);
            }
        }
        return $model_array;
    }

    function process_parameters ( $model_array, $key ) {

        $parameters = $model_array[$key]['parameters'];
        foreach ($parameters as $param_key => $param_value) {
            switch ($param_key) {
                case 'slug':
                    $model_array = $this->process_slug($model_array, $key, $param_key, $param_value);
                break;
            }

        }
        return $model_array;
    }
    function process_slug ( $model_array, $key, $param_key, $param_value ) {
        $model_array[$key]['value'] = Str::slug($model_array[$param_value]['value'], '-');
        return $model_array;
    }

}
