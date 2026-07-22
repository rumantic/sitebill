<?php

/**
 * User data manager
 * @author http://www.sitebill.ru
 */

//require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');

require_once __DIR__ . '/traits/UserDataWatermarkTrait.php';
require_once __DIR__ . '/traits/UserDataActionTrait.php';
require_once __DIR__ . '/traits/UserDataCrudTrait.php';
require_once __DIR__ . '/traits/UserDataNotificationTrait.php';
require_once __DIR__ . '/traits/UserDataAccessTrait.php';
require_once __DIR__ . '/traits/UserDataGridTrait.php';
require_once __DIR__ . '/traits/UserDataFormTrait.php';
require_once __DIR__ . '/traits/UserDataAliasTrait.php';
require_once __DIR__ . '/traits/UserDataBillingTrait.php';

class User_Data_Manager extends Object_Manager
{
    use UserDataWatermarkTrait;
    use UserDataActionTrait;
    use UserDataCrudTrait;
    use UserDataNotificationTrait;
    use UserDataAccessTrait;
    use UserDataGridTrait;
    use UserDataFormTrait;
    use UserDataAliasTrait;
    use UserDataBillingTrait;

    public $table_name = 'data';
    public $primary_key = 'id';
    protected $nowatermark_folder_with_id = false;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');

        $data_model = new Data_Model();
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
        if ($this->getConfigValue('hide_contact_input_user_data')) {
            unset($this->data_model['data']['fio']);
            unset($this->data_model['data']['phone']);
            unset($this->data_model['data']['email']);
        }
        if ($this->getConfigValue('dadata_autocomplete_force')) {
            $this->data_model['data'] = $this->prepare_model_for_dadata($this->data_model['data']);
        }
    }

    function init_more_fields($form_data)
    {
        return $form_data;
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main($params = array())
    {

        $user_id = (int)$_SESSION['user_id'];
        if ($user_id === 0) {
            return sprintf(Multilanguage::_('L_NEED_AUTH_WITH_LINK'), '"' . SITEBILL_MAIN_URL . '/login/"');
        }


        $rs = $this->getTopMenu();

        $do = $this->getRequestValue('do');
        $action = '_' . $do . 'Action';

        if (!method_exists($this, $action)) {
            $action = '_defaultAction';
        }
        $rs .= $this->$action();


        return $rs;
    }

    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu()
    {
        if ($this->getRequestValue('do') != 'new' and $this->getRequestValue('do') != 'edit') {
            $rs = '';
            $rs .= '<a class="btn btn-primary" href="' . SITEBILL_MAIN_URL . '/account/data/?do=new">' . Multilanguage::_('L_ADD_ADV') . '</a>';
            $rs .= '<div class="clear"></div>';
            //$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
            return $rs;
        }
        return '';
    }
}
