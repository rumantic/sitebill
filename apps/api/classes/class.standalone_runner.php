<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Layers REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_standalone_runner extends API_Common {
    use \system\traits\blade\BladeTrait;

    function __construct()
    {
        parent::__construct();
        $this->add_apps_local_and_root_resource_paths('api');
    }

    public function _iframe_button ($button_title, $modal_title, $component, $component_params = array()) {
        $component_params['anonymous'] = true;

        $params = array(
            'modal_id' => uniqid($component),
            'button_title' => $button_title,
            'modal_title' => $modal_title,
            'btnicon' => $component_params['btnicon'],
            'params_string' => http_build_query($component_params),
        );

        return $this->view('apps.api.resources.views.standalone_runner_button', $params);
    }

    public function _run () {
        $component = $this->request->get('component');
        $table_name = $this->request->get('table_name');
        $primary_key = $this->request->get('primary_key');
        $success_message = $this->request->get('success_message');
        $object_id = $this->request->get('object_id');
        $object_type = $this->request->get('object_type');
        $entity_uri = $this->request->get('entity_uri');
        $only_field_name = $this->request->get('only_field_name');


        return '
        <base href="'.SITEBILL_MAIN_URL.'/apps/angular/dist/">
<link href="https://api.sitebill.ru/api/apps/cloudprovider/assets/icons/meteocons/style.css" rel="stylesheet">
<link href="https://api.sitebill.ru/api/apps/cloudprovider/assets/icons/material-icons/outline/style.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Muli:300,400,600,700" rel="stylesheet">

<link rel="stylesheet" href="'.\bridge\Helpers\Helpers::get_angular_file('styles').'"></head>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('runtime').'" defer></script>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('polyfills-es5').'" nomodule defer></script>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('polyfills').'" defer></script>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('main').'" defer></script>

<app
        id="app_root"
        class="angular"
        standalone_mode="true"
        form_mode="true"
        success_message="'.$success_message.'"
        component="'.$component.'"
        table_name="'.$table_name.'"
        primary_key="'.$primary_key.'"
        object_id="'.$object_id.'"
        object_type="'.$object_type.'"
        entity_uri="'.$entity_uri.'"
        only_field_name="'.$only_field_name.'"
></app>
        ';
    }

}
