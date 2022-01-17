<?php
namespace table\Http\ViewComposers;

use system\traits\blade\BladeTrait;

class TableComposer extends \SiteBill {
    use BladeTrait;
    function __construct()
    {
        parent::SiteBill();
        $this->add_apps_local_and_root_resource_paths('table');
    }

    function render ( $params ) {
        return $this->view('apps.table.resources.views.table', $params);

    }
}
