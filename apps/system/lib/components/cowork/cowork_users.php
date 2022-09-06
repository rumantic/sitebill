<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Cowork-users object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Cowork_Users extends User_Object_Manager {
    use \system\traits\PermissionsTrait;

    /**
     * @var agency_admin
     */
    protected $agency_admin;

    function __construct()
    {
        $this->disable_redirect();
        parent::__construct();
        if ( $this->getConfigValue('apps.agency.enable') ) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/agency/admin/admin.php');
            $this->agency_admin = new agency_admin();
        }

        if ( $this->get_access($this->getSessionUserId(), 'agency') ) {

            $this->data_model[$this->table_name]['agency_id']['query'] =
                'select * from '.DB_PREFIX.'_agency where parent_id='.
                $this->agency_admin->get_agency_id($this->getSessionUserId());

            if ( $this->data_model[$this->table_name]['group_id']['parameters']['agency_query'] ) {
                $this->data_model[$this->table_name]['group_id']['query'] =
                    $this->data_model[$this->table_name]['group_id']['parameters']['agency_query'];
            }

        } else {
            if ( isset($this->data_model[$this->table_name]['parent_user_id']) ) {
                $this->data_model[$this->table_name]['parent_user_id']['name'] = 'parent_user_id';
                $this->data_model[$this->table_name]['parent_user_id']['type'] = 'hidden';
                $this->data_model[$this->table_name]['parent_user_id']['value'] = $this->getSessionUserId();
            }

            if ( isset($this->data_model[$this->table_name]['agency_id']) ) {
                $my_agency_id = $this->agency_admin->get_agency_id($this->getSessionUserId());
                $this->data_model[$this->table_name]['agency_id']['value'] = $my_agency_id;
                $this->data_model[$this->table_name]['agency_id']['query'] =
                    'select * from '.DB_PREFIX.'_agency where id='.$my_agency_id;
            }
            if ( $this->data_model[$this->table_name]['group_id']['parameters']['agency_staff_query'] ) {
                $this->data_model[$this->table_name]['group_id']['query'] =
                    $this->data_model[$this->table_name]['group_id']['parameters']['agency_staff_query'];
            }

            //$this->data_model[$this->table_name]['group_id']['name'] = 'group_id';
            //$this->data_model[$this->table_name]['group_id']['type'] = 'hidden';
            //$this->data_model[$this->table_name]['group_id']['value'] = $this->getConfigValue('newuser_registration_groupid');
        }
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
        $rs = $this->bootstrap_and_css_header();
        $params['grid_item'] = array('user_id', 'login', 'fio', 'reg_date', 'email', 'phone');

        if ( $this->getConfigValue('apps.agency.enable') ) {
            $params['grid_conditions'] = [
                'user_id' => $this->agency_admin->get_agency_user_id_array($this->getSessionUserId())
            ];
            $params['grid_item'][] = 'agency_id';
            $params['grid_item'][] = 'group_id';
        } else {
            $params['grid_conditions'] = [
                'parent_user_id' => $this->getSessionUserId()
            ];
        }

        $this->template->assign('disable_excel_import', 1);
        $this->template->assign('disable_excel_export', 1);
        $this->template->assign('disable_format_grid', 1);

        $rs .= parent::grid($params, $default_params);
        return $rs;
    }
}
