<?php

/**
 * Construct grid
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

require_once __DIR__ . '/traits/GridConfigTrait.php';
require_once __DIR__ . '/traits/GridQueryTrait.php';
require_once __DIR__ . '/traits/GridHeaderTrait.php';
require_once __DIR__ . '/traits/GridRenderTrait.php';
require_once __DIR__ . '/traits/GridBillingTrait.php';
require_once __DIR__ . '/traits/GridDataTrait.php';

class Common_Grid extends Sitebill
{
    use GridConfigTrait;
    use GridQueryTrait;
    use GridHeaderTrait;
    use GridRenderTrait;
    use GridBillingTrait;
    use GridDataTrait;

    /**
     * Array with list of grid items
     * @var array
     */
    protected $grid_items = array();

    /**
     * Массив объектов для рендеринга вывода элемента
     */
    protected $grid_items_render_objects = array();

    /**
     * Array with list of grid controls (edit/delete/structure/manual)
     * @var array
     */
    protected $grid_controls = array();

    /*
     * Enable howing button of batch list update
     */
    protected $batchUpdate = false;
    protected $batchActivate = false;
    protected $batchUpdateUrl = '';
    protected $massDeleteUrl = '';

    /**
     *
     * @var string
     */
    protected $grid_query;
    protected $action;
    protected $table_name;
    protected $conditions = array();
    protected $conditions_sql = array();
    protected $conditions_left_join = array();
    protected $controls_params = array();

    /**
     * Grid object
     * @var Object_Manager
     */
    protected $grid_object;
    protected $pager_params = array();
    /*
     * Идентификатор пользователя для которого запущен рендеринг грида
     * По-умолчанию false - т.е. для любого, проверки прав доступа не выполняется
     */
    protected $render_user_id = false;

    protected $total_count = 0;
    /**
     * @var $API_standalone_runner
     */
    private $API_standalone_runner;

    /**
     * CSS class mapping for grid elements — supports Bootstrap and Tailwind.
     * @var array
     */
    protected $grid_css = array();

    function __construct($grid_object)
    {
        parent::__construct();
        $this->grid_object = $grid_object;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $this->data_model_controller = new Data_Model();
        $this->initGridCss();
    }

    /**
     * Initialize CSS class mapping based on bootstrap_version config.
     * When bootstrap_version = 'tailwind', uses Tailwind utility classes.
     * Otherwise uses Bootstrap 2/3 classes (default).
     */
    protected function initGridCss()
    {
        $version = trim($this->getConfigValue('bootstrap_version'));

        if ($version === 'tailwind') {
            $this->grid_css = array(
                'table'           => 'w-full text-sm text-left border-collapse',
                'thead'           => 'bg-gray-50 border-b border-gray-200',
                'th'              => 'px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider',
                'tbody'           => 'divide-y divide-gray-100',
                'tr'              => 'hover:bg-gray-50 transition-colors',
                'tr_inactive'     => 'bg-red-50 text-red-700',
                'tr_warning'      => 'bg-yellow-50 text-yellow-700',
                'td'              => 'px-4 py-3 text-sm text-gray-700',
                'td_controls'     => 'px-4 py-3 whitespace-nowrap',
                'btn_edit'        => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors',
                'btn_delete'      => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-red-50 text-red-700 hover:bg-red-100 transition-colors',
                'btn_active_on'   => 'inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded bg-green-500 text-white hover:bg-green-600 transition-colors',
                'btn_active_off'  => 'inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded bg-red-500 text-white hover:bg-red-600 transition-colors',
                'icon_active'     => 'fa fa-power-off',
                'btn_view'        => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors',
                'btn_warning'     => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition-colors',
                'btn_danger'      => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-red-50 text-red-700 hover:bg-red-100 transition-colors',
                'btn_inverse'     => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-gray-700 text-white hover:bg-gray-800 transition-colors',
                'btn_reservation' => 'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors',
                'btn_mini'        => 'inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-red-50 text-red-700 hover:bg-red-100 transition-colors',
                'icon_edit'       => 'fa fa-pencil',
                'icon_delete'     => 'fa fa-times',
                'icon_view'       => 'fa fa-info-circle',
                'icon_download'   => 'fa fa-download',
                'icon_upload'     => 'fa fa-upload',
                'icon_tasks'      => 'fa fa-tasks',
                'icon_check'      => 'fa fa-check',
                'icon_eye'        => 'fa fa-eye',
                'icon_money'      => 'fa fa-usd',
                'checkbox'        => 'h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500',
                'pager'           => 'px-4 py-3',
                'alert_empty'     => 'px-4 py-8 text-center text-gray-500',
                'sort_link'       => 'inline-flex items-center gap-1 text-gray-600 hover:text-gray-900',
                'sort_asc'        => 'text-blue-600',
                'sort_desc'       => 'text-blue-600',
                'clear_link'      => 'text-xs text-gray-400 hover:text-red-500',
                'ranged_btn_danger'  => 'inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-600 hover:bg-red-200',
                'ranged_btn_success' => 'inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-600 hover:bg-green-200',
                'ranged_btn_cancel'  => 'inline-flex items-center px-2 py-1 text-xs rounded bg-gray-100 text-gray-600 hover:bg-gray-200',
                'ranged_input'    => 'w-20 px-2 py-1 text-xs border border-gray-300 rounded',
                'grid_wrapper'    => 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden',
            );
        } else {
            $this->grid_css = array(
                'table'           => 'table table-striped table-hover dataTable',
                'thead'           => '',
                'th'              => '',
                'tbody'           => '',
                'tr'              => '',
                'tr_inactive'     => 'notactive danger alert-danger',
                'tr_warning'      => 'warning alert-warning',
                'td'              => '',
                'td_controls'     => 'account-grid-controls',
                'btn_edit'        => 'btn btn-info',
                'btn_delete'      => 'btn btn-danger',
                'btn_active_on'   => 'btn btn-success btn-mini',
                'btn_active_off'  => 'btn btn-danger btn-mini',
                'icon_active'     => 'icon-white icon-off',
                'btn_view'        => 'btn btn-info',
                'btn_warning'     => 'btn btn-warning',
                'btn_danger'      => 'btn btn-danger',
                'btn_inverse'     => 'btn btn-inverse',
                'btn_reservation' => 'btn btn-info',
                'btn_mini'        => 'btn btn-danger btn-mini',
                'icon_edit'       => 'icon-white icon-pencil',
                'icon_delete'     => 'icon-white icon-remove',
                'icon_view'       => 'icon-white icon-info-sign',
                'icon_download'   => 'icon-white icon-download-alt',
                'icon_upload'     => 'icon-white icon-upload',
                'icon_tasks'      => 'icon-white icon-tasks',
                'icon_check'      => 'icon-white icon-ok',
                'icon_eye'        => 'icon-white icon-eye-open',
                'icon_money'      => 'fa fa-usd',
                'checkbox'        => 'grid_check_one',
                'pager'           => 'pager',
                'alert_empty'     => 'alert',
                'sort_link'       => 'common-grid-sortable',
                'sort_asc'        => 'common-grid-sorted-asc',
                'sort_desc'       => 'common-grid-sorted-desc',
                'clear_link'      => 'tags-clear',
                'ranged_btn_danger'  => 'btn btn-danger',
                'ranged_btn_success' => 'btn btn-success',
                'ranged_btn_cancel'  => 'btn',
                'ranged_input'    => 'tagged_input',
                'grid_wrapper'    => '',
            );
        }
    }

    /**
     * Check if grid is in Tailwind mode.
     * @return bool
     */
    public function isTailwind()
    {
        return trim($this->getConfigValue('bootstrap_version')) === 'tailwind';
    }

    /**
     * Get CSS class by key from grid_css mapping.
     * @param string $key
     * @return string
     */
    public function css($key)
    {
        return isset($this->grid_css[$key]) ? $this->grid_css[$key] : '';
    }

}
