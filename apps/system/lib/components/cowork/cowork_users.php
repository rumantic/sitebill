<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Cowork-users object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Cowork_Users extends User_Object_Manager {
    use \system\traits\PermissionsTrait;
    function __construct()
    {
        $this->disable_redirect();
        parent::__construct();

        $this->data_model[$this->table_name]['parent_user_id']['name'] = 'parent_user_id';
        $this->data_model[$this->table_name]['parent_user_id']['type'] = 'hidden';
        $this->data_model[$this->table_name]['parent_user_id']['value'] = $this->getSessionUserId();

        $this->data_model[$this->table_name]['group_id']['name'] = 'group_id';
        $this->data_model[$this->table_name]['group_id']['type'] = 'hidden';
        $this->data_model[$this->table_name]['group_id']['value'] = $this->getConfigValue('newuser_registration_groupid');

    }

    function main()
    {
        if ( !$this->get_access($this->getSessionUserId(), 'cowork_users', 'access') ) {
            return _e('Доступ запрещен');
        }
        return parent::main();
    }

    protected function _deleteAction() {
        $rs = parent::_deleteAction();
        return $this->finalize_action($rs);
    }

    private function finalize_action ($rs) {
        if ( !$this->getError() ) {
            header('location: ' . SITEBILL_MAIN_URL . '/account/coworker/');
            exit();
        }
        return $rs;
    }


    protected function _edit_doneAction() {
        $rs = parent::_edit_doneAction();
        return $this->finalize_action($rs);
    }

    protected function _new_doneAction() {
        $rs = parent::_new_doneAction();
        return $this->finalize_action($rs);
    }

    function grid($params = array(), $default_params = array()) {
        $rs = '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/css/font-awesome.min.css" />';
        $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/data/css/style.css" />';
        $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
        if ($bootstrap_version == '3') {
            $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap3-typeahead.min.js"></script>';
        }
        $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/js/bootstrap-tag.min.js"></script>';

        $params['grid_conditions'] = [
            'parent_user_id' => $this->getSessionUserId()
        ];

        $params['grid_item'] = array('user_id', 'login', 'fio', 'reg_date', 'email', 'phone');
        $this->template->assign('disable_excel_import', 1);
        $this->template->assign('disable_excel_export', 1);
        $this->template->assign('disable_format_grid', 1);

        $rs .= parent::grid($params, $default_params);
        return $rs;
    }
}
