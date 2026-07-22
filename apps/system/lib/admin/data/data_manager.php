<?php

/**
 * Data manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */

require_once __DIR__ . '/traits/DataCountTrait.php';
require_once __DIR__ . '/traits/DataMenuTrait.php';
require_once __DIR__ . '/traits/DataPermissionTrait.php';
require_once __DIR__ . '/traits/DataPriceBatchTrait.php';
require_once __DIR__ . '/traits/DataCrudTrait.php';
require_once __DIR__ . '/traits/DataGridTrait.php';
require_once __DIR__ . '/traits/DataFormTrait.php';
require_once __DIR__ . '/traits/DataRequestTrait.php';
require_once __DIR__ . '/traits/DataNotificationTrait.php';
require_once __DIR__ . '/traits/DataActionsTrait.php';

class Data_Manager extends Object_Manager {
    use \system\traits\PermissionsTrait;
    use DataCountTrait;
    use DataMenuTrait;
    use DataPermissionTrait;
    use DataPriceBatchTrait;
    use DataCrudTrait;
    use DataGridTrait;
    use DataFormTrait;
    use DataRequestTrait;
    use DataNotificationTrait;
    use DataActionsTrait;

    protected $billing_mode_on = false;
    protected $data_model_object;
    public $notwatermarked_folder = SITEBILL_DOCUMENT_ROOT.'/img/nwtm/';
    protected $nowatermark_folder_with_id = false;
    private $prev_form_data;

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'data';
        $this->action = 'data';
        $this->app_title = Multilanguage::_('DATA_APP_NAME', 'system');
        $this->primary_key = 'id';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->data_model_object = $data_model;
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));


        if ($this->getConfigValue('theme') === 'albostar') {
            $this->data_model['data']['date_added']['type'] = 'safe_string';
        }

        $this->data_model['data']['user_id']['name'] = 'user_id';
        $this->data_model['data']['user_id']['primary_key_name'] = 'user_id';
        $this->data_model['data']['user_id']['primary_key_table'] = 'user';
        $this->data_model['data']['user_id']['title'] = Multilanguage::_('USER');
        $this->data_model['data']['user_id']['value_string'] = '';
        $this->data_model['data']['user_id']['value'] = 0;
        $this->data_model['data']['user_id']['length'] = 40;
        $this->data_model['data']['user_id']['type'] = 'select_by_query';
        if ($this->getConfigValue('theme') === 'ipn') {
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user  where group_id <> 3 order by fio';
        } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && (@$_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access')) and $this->getConfigValue('data_adv_share_access_extended') != '') {
            $extended_user_list = explode(',', $this->getConfigValue('data_adv_share_access_extended'));
            array_push($extended_user_list, (int) $_SESSION['user_id_value']);
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user  where user_id in (' . implode(',', $extended_user_list) . ') order by fio';
        } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && (@$_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access')) and (int) $this->getConfigValue('data_adv_share_access_user_list_strict') == 1) {
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user  where user_id = ' . (int) $_SESSION['user_id_value'] . ' order by fio';
        } else {
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by fio';
        }
        /* $this->data_model['data']['user_id']['value_name'] = 'fio';
          $this->data_model['data']['user_id']['title_default'] = Multilanguage::_('L_CHOOSE_USER'); */
        $this->data_model['data']['user_id']['value_default'] = 0;
        $this->data_model['data']['user_id']['required'] = 'on';
        $this->data_model['data']['user_id']['unique'] = 'off';

        /* var_dump($_SESSION['user_id_value']);
          if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
          $user_id=(int)$_SESSION['user_id_value'];

          //$this->setRequestValue('user_id', $user_id);
          $this->data_model['data']['user_id']['value'] = $user_id;
          }else{
          $this->data_model['data']['user_id']['value'] = $this->getAdminUserId();
          } */
        $user_id = 0;
        if (isset($_SESSION['user_id_value'])) {
            $user_id = (int) $_SESSION['user_id_value'];
        }

        $this->data_model['data']['user_id']['value'] = $user_id;

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $this->billing_mode_on = true;
        }
        if ($this->getConfigValue('dadata_autocomplete_force')) {
            $this->data_model['data'] = $this->prepare_model_for_dadata($this->data_model['data']);
        }
        if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/bitrix24/handler/HandlerController.php') ) {
            $Sitebill_Registry = Sitebill_Registry::getInstance();
            $Sitebill_Registry::add_handler('bitrix24\handler\HandlerController');
        }
        $this->template->assign('data_grid_filters', $this->getFilters());
    }

    function get_model() {
        return $this->data_model;
    }
}

