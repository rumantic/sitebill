<?php
namespace bridge\traits\config;

trait ConfigTrait {
    private $config_name = null;

    function set_config_name ( $config_name ) {
        $this->config_name = $config_name;
    }

    function get_config_name () {
        if ( $this->getConfigValue('apps.dashboard.config') != '' ) {
            return $this->getConfigValue('apps.dashboard.config');
        }
        return $this->config_name;
    }


    function load_config_file ( $config_file ) {
        if ( file_exists($config_file) ) {
            return include($config_file);
        }
        return false;
    }

    function getCommonTplData(){

        $data = array();
        $local_config_name = '';
        if ( $this->get_config_name() ) {
            $local_config_name = '/'.$this->get_config_name();
        }

        $template_config = $this->load_config_file(
            SITEBILL_DOCUMENT_ROOT.'/template/frontend/local'.$local_config_name.'/config/template.php'
        );
        if ( $template_config ) {
            $data = array_merge($data, $template_config);
        }
        return $data;
    }

    function initConfig ( $config_array )
    {
        if ( is_array($config_array) ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
            $config_admin = new \config_admin();
            foreach ( $config_array as $key => $item ) {
                $config_admin->addParamToConfig(
                    $key,
                    $item['default'],
                    $item['title'],
                    $item['type'],
                    $item['params']
                );
            }
        }
    }

}
