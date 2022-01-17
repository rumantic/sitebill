<?php


namespace system\traits\blade;
use Jenssegers\Blade\Blade;

trait BladeTrait
{
    /**
     * @var array
     */
    protected $resource_path;

    /**
     * @var Blade
     */
    protected $blade;

    function add_resource_path ( $path ) {
        $this->resource_path[] = $path;
    }

    function add_apps_local_and_root_resource_paths ( $app_name ) {
        $config_instance = $this;
        if ( !method_exists($this, 'getConfigValue') ) {
            $config_instance = $this->sitebill;
        }
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$config_instance->getConfigValue('theme').'/resources/views/');
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/resources/views/');
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT);
    }

    function factory () {
        if ( !isset($this->blade) ) {
            $this->resource_path[] = SITEBILL_DOCUMENT_ROOT.'/apps/admin/resources/views';
            $this->blade = new Blade($this->resource_path, SITEBILL_DOCUMENT_ROOT.'/cache/compile');
        }
    }

    function view ($template, $params = array()): string {
        $this->factory();
        try {
            return $this->blade->render($template, $params);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
