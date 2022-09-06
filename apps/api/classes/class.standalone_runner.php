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

        if ($this->request->get('form_mode')) {
            $form_mode = 'form_mode="true"';
        }

        return '
        <base href="'.SITEBILL_MAIN_URL.'/apps/angular/dist/">
<link href="'.SITEBILL_MAIN_URL.'/apps/admin3/resources/assets/icons/meteocons/style.css" rel="stylesheet">
<link href="'.SITEBILL_MAIN_URL.'/apps/admin3/resources/assets/icons/material-icons/outline/style.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Muli:300,400,600,700" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />


<link rel="stylesheet" href="'.\bridge\Helpers\Helpers::get_angular_file('styles').'"></head>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('runtime').'" defer></script>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('polyfills-es5').'" nomodule defer></script>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('polyfills').'" defer></script>
<script src="'.\bridge\Helpers\Helpers::get_angular_file('main').'" defer></script>

        <!-- FUSE Splash Screen CSS -->
        <style type="text/css">
            #fuse-splash-screen {
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: whitesmoke;
                z-index: 99999;
                pointer-events: none;
            }

            #fuse-splash-screen .center {
                display: block;
                width: 100%;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
            }

            #fuse-splash-screen .logo {
                width: 43px;
                color: white;
                margin: 0 auto;
            }

            #fuse-splash-screen .logo img {
                filter: drop-shadow(0px 10px 6px rgba(0, 0, 0, 0.2))
            }

            #fuse-splash-screen .spinner-wrapper {
                display: block;
                position: relative;
                width: 100%;
                min-height: 100px;
                height: 100px;
            }

            #fuse-splash-screen .spinner-wrapper .spinner {
                position: absolute;
                overflow: hidden;
                left: 50%;
                margin-left: -50px;
                animation: outer-rotate 2.91667s linear infinite;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner {
                width: 100px;
                height: 100px;
                position: relative;
                animation: sporadic-rotate 5.25s cubic-bezier(0.35, 0, 0.25, 1) infinite;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .gap {
                position: absolute;
                left: 49px;
                right: 49px;
                top: 0;
                bottom: 0;
                border-top: 10px solid;
                box-sizing: border-box;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .left,
            #fuse-splash-screen .spinner-wrapper .spinner .inner .right {
                position: absolute;
                top: 0;
                height: 100px;
                width: 50px;
                overflow: hidden;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .left .half-circle,
            #fuse-splash-screen .spinner-wrapper .spinner .inner .right .half-circle {
                position: absolute;
                top: 0;
                width: 100px;
                height: 100px;
                box-sizing: border-box;
                border: 10px solid #4285F4;
                border-bottom-color: transparent;
                border-radius: 50%;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .left {
                left: 0;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .left .half-circle {
                left: 0;
                border-right-color: transparent;
                animation: left-wobble 1.3125s cubic-bezier(0.35, 0, 0.25, 1) infinite;
                -webkit-animation: left-wobble 1.3125s cubic-bezier(0.35, 0, 0.25, 1) infinite;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .right {
                right: 0;
            }

            #fuse-splash-screen .spinner-wrapper .spinner .inner .right .half-circle {
                right: 0;
                border-left-color: transparent;
                animation: right-wobble 1.3125s cubic-bezier(0.35, 0, 0.25, 1) infinite;
                -webkit-animation: right-wobble 1.3125s cubic-bezier(0.35, 0, 0.25, 1) infinite;
            }

            @keyframes outer-rotate {
                0% {
                    transform: rotate(0deg) scale(0.5);
                }
                100% {
                    transform: rotate(360deg) scale(0.5);
                }
            }

            @keyframes left-wobble {
                0%, 100% {
                    transform: rotate(130deg);
                }
                50% {
                    transform: rotate(-5deg);
                }
            }

            @keyframes right-wobble {
                0%, 100% {
                    transform: rotate(-130deg);
                }
                50% {
                    transform: rotate(5deg);
                }
            }

            @keyframes sporadic-rotate {
                12.5% {
                    transform: rotate(135deg);
                }
                25% {
                    transform: rotate(270deg);
                }
                37.5% {
                    transform: rotate(405deg);
                }
                50% {
                    transform: rotate(540deg);
                }
                62.5% {
                    transform: rotate(675deg);
                }
                75% {
                    transform: rotate(810deg);
                }
                87.5% {
                    transform: rotate(945deg);
                }
                100% {
                    transform: rotate(1080deg);
                }
            }
        </style>
        <!-- / FUSE Splash Screen CSS -->
        <!-- FUSE Splash Screen -->
        <fuse-splash-screen id="fuse-splash-screen">
            <div class="center">
                <!-- Material Design Spinner -->
                <div class="spinner-wrapper">
                    <div class="spinner">
                        <div class="inner">
                            <div class="gap"></div>
                            <div class="left">
                                <div class="half-circle"></div>
                            </div>
                            <div class="right">
                                <div class="half-circle"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- / Material Design Spinner -->
            </div>
        </fuse-splash-screen>

<app
        id="app_root"
        class="angular"
        standalone_mode="true"
        '.$form_mode.'
        success_message="'.$success_message.'"
        component="'.$component.'"
        table_name="'.$table_name.'"
        primary_key="'.$primary_key.'"
        object_id="'.$object_id.'"
        object_type="'.$object_type.'"
        entity_uri="'.$entity_uri.'"
        only_field_name="'.$only_field_name.'"
>
</app>
        ';
    }

}
