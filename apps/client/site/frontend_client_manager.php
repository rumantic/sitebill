<?php

use client\Events\ClientVisitEvent;
use client\Listeners\SendVisitEmail;
use Illuminate\Database\Capsule\Manager as Capsule;


defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Client admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class frontend_client_manager extends client_admin {

    /**
     * @var comment_site
     */
    private $comment_site;

    function __construct()
    {
        parent::__construct();
        $this->save_url = 'empty';

        if ( $this->getConfigValue('apps.comment.enable') ) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/site/site.php';
            $this->comment_site = new comment_site();
        }

    }

    function main() {
        if ( !$this->getSessionUserId() ) {
            return false;
        }
        $this->template->assert('is_account', '1');

        $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
            array(
                '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                '<a href="' . $this->createUrlTpl($this->getConfigValue('apps.client.front_manager_alias')) . '">'._e('Клиенты').'</a>'
            )));



        $rs = '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/css/font-awesome.min.css" />';
        $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/data/css/style.css" />';
        $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
        if ( $bootstrap_version == '3' ) {
            $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap3-typeahead.min.js"></script>';
        }
        $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/js/bootstrap-tag.min.js"></script>';

        $rs .= parent::main();
        return $rs;

    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = '?') {
        return parent::get_form($form_data, $do, $language_id, $button_title, '?');
    }

    function _edit_doneAction()
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        $visit_event = false;

        if ( $this->getConfigValue('apps.client.front_manager.event.enable') &&
            $form_data[$this->table_name]['status_id']['value'] == 'visit' ) {
            $visit_event = true;
            // Делаем поле realty_id обязательным, чтобы менеджер указывал куда собрался клиент
            $this->data_model[$this->table_name]['realty_id']['required'] = 'on';
        }

        $rs = parent::_edit_doneAction();

        if ( !$this->getError() && $visit_event && \SiteBill::event_dispatcher() ) {
            \SiteBill::event_dispatcher()->listen([ClientVisitEvent::class], SendVisitEmail::class);
            \SiteBill::event_dispatcher()->dispatch(
                new ClientVisitEvent($form_data[$this->table_name][$this->primary_key]['value'])
            );
        }

        return $rs;
    }

    function _new_doneAction()
    {
        $rs = parent::_new_doneAction();

        if ( $this->getError() == '' and $this->getConfigValue('apps.comment.enable') ) {
            $user_id = $this->getSessionUserId();

            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
            $Users_Manager = new User_Object_Manager();
            $user_info = $Users_Manager->load_by_id($user_id);


            $this->comment_site->add_comment(
                $this->table_name,
                $this->get_new_record_id(),
                $user_id,
                'Action: <strong>New client</strong> User: '.$user_info['fio']['value'].', user_id = '.$user_id,
                date('Y-m-d H:i:s')
            );
            echo $this->comment_site->getError();

        }

        return $rs;
    }


    function _viewAction()
    {
        if ( $this->getConfigValue('apps.comment.enable') ) {
            $this->comment_site->generateCommentPanel($this->getSessionUserId(), 'client', $this->getRequestValue($this->primary_key));
        }


        return parent::_viewAction();
    }

    function grid($params = array(), $default_params = array()) {
        if ( isset($this->data_model[$this->table_name]['user_id']) ) {
            $this->data_model[$this->table_name]['user_id']['type'] = 'select_by_query';
        }


        $REQUESTURIPATH = Sitebill::getClearRequestURI();

        $this->template->assign('pdf_enable', 0);
        $this->template->assign('disable_excel_export', 1);
        $this->template->assign('disable_excel_import', 1);
        $this->template->assign('disable_format_grid', 0);



        if ( !$this->permission->get_access($this->getSessionUserId(), 'client', 'admin_access') ) {
            //Устанавливаем параметр USER_ID для функции импорта XLS файла.
            //Чтобы при загрузке из XLS пользоатель не смог получить доступ к чужим записям
            $_SESSION['politics']['client']['check_access'] = true;
            $_SESSION['politics']['client']['user_id'] = $this->getSessionUserId();

            if ( 1 == $this->getConfigValue('enable_coworker_mode') ) {
                $coworked_object_ids = $this->get_coworked_object_ids();
            }
            if ( !empty($coworked_object_ids) ) {
                $my_object_ids = $this->get_user_object_ids($this->getSessionUserId());
                $params['grid_conditions'][$this->primary_key] = array_merge($coworked_object_ids, $my_object_ids);

            } else {
                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
            }
            $default_params['render_user_id'] = $this->getSessionUserId();
        }




        if ( $this->getConfigValue('apps.client.default_grid_item') ) {
            $default_params['grid_item'] = explode(',', $this->getConfigValue('apps.client.default_grid_item'));
            $params['grid_item'] = $default_params['grid_item'];
        } else {
            $default_params['grid_item'] = array('client_id', 'fio', 'phone', 'user_id');
        }

        $params['grid_controls'] = array(
            'view',
            'edit',
            'delete',
        );

        if ( $this->permission->get_access($this->getSessionUserId(), 'cowork', 'access') ) {
            array_push($params['grid_controls'],
                array(
                    'component' => 'cowork',
                    'modal_title' => _e('Ответственные менеджеры'),
                    'type' => 'iframe_modal',
                    'object_type' => 'client',
                    'btnicon' => 'ace-icon fa fa-user',
                )
            );
        }

        $params['url'] = '/' . $REQUESTURIPATH;
        $default_params['pager_params']['page_url'] = '/' . $REQUESTURIPATH;


        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array('client_user_' . $this->getSessionUserId()));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
            $params['grid_item'] = $used_fields;
        }

        $rs = Object_Manager::grid($params, $default_params);

        return $rs;
    }

    function check_access($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value) {
        if ( $this->permission->get_access($this->getSessionUserId(), 'client', 'admin_access') ) {
            return true;
        }
        return parent::check_access($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value);
    }


    function _add_coworkAction () {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/cowork/cowork.php';
        $CW = new Cowork();
        $coworker_id = 37;
        $object_id = 162;

        $CW->setCoworkerToObject($this->table_name, $object_id, $coworker_id);
        $coworked_object_ids = $this->get_coworked_object_ids();
        /*
        echo '<pre>';
        print_r($coworked_object_ids);
        echo '</pre>';
        */

        return 'add_coworkAction';
    }

    function get_coworked_object_ids () {
        $DBC = DBC::getInstance();
        $user_id = $this->getSessionUserId();

        $query = 'SELECT id FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=?';
        $stmt = $DBC->query($query, array($user_id, $this->table_name));
        $coworked = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $coworked[] = $ar['id'];
            }
        }

        $supervised_user_ids = $this->get_supervised_user_ids($user_id, $this->table_name);
        if ( $supervised_user_ids ) {
            $supervised_object_ids = $this->get_user_object_ids($supervised_user_ids);
            if ( is_array($supervised_object_ids) and count($supervised_object_ids) > 0 ) {
                $coworked = array_merge($coworked, $supervised_object_ids);
            }

        }
        return $coworked;
    }

    function get_supervised_user_ids ( $parent_user_id, $object_type ) {
        if ( $this->getConfigValue('enable_curator_mode') != 1 ) {
            return false;
        }
        // Получим список user_id по $parent_user_id
        $raws = Capsule::table('user')
            ->selectRaw(
                'user_id'
            )
            ->where('parent_user_id', '=', $parent_user_id)
            ->get();
        $user_ids = array();
        if ( $raws ) {
            foreach ( $raws as $item ) {
                $user_ids[] = $item->user_id;
            }
            if ( count($user_ids) > 0 ) {
                return $user_ids;
            }
        }
        return false;
    }

    function get_user_object_ids ( $user_id ) {
        $DBC = DBC::getInstance();

        if ( is_array($user_id) ) {
            $query = 'SELECT 
                    '.$this->primary_key.' 
                  FROM 
                    ' . DB_PREFIX . '_'.$this->table_name.' 
                  WHERE user_id in ('.implode(',', $user_id).')';
            $stmt = $DBC->query($query, array());
        } else {
            $query = 'SELECT 
                    '.$this->primary_key.' 
                  FROM 
                    ' . DB_PREFIX . '_'.$this->table_name.' 
                  WHERE user_id=?';
            $stmt = $DBC->query($query, array($user_id));
        }
        $ids = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ids[] = $ar[$this->primary_key];
            }
        }
        return $ids;
    }

}
