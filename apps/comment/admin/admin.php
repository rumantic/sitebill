<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * mailbox admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class comment_admin extends Object_Manager {

    private $data_manager_export;

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('comment');
        $this->table_name = 'comment';
        $this->action = 'comment';


        $this->primary_key = 'comment_id';

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.comment.enable')) {
            $config_admin->addParamToConfig('apps.comment.enable', '0', 'Включить приложение Комментарии');
        }

        if (!$config_admin->check_config_item('apps.comment.premoderation_enabled')) {
            $config_admin->addParamToConfig('apps.comment.premoderation_enabled', '0', 'Не публиковать комментарии без модерации');
        }

        if (!$config_admin->check_config_item('apps.comment.delta_time')) {
            $config_admin->addParamToConfig('apps.comment.delta_time', '30', 'Время в секундах между комментариями одного пользователя');
        }

        if (!$config_admin->check_config_item('apps.comment.simple_auth')) {
            $config_admin->addParamToConfig('apps.comment.simple_auth', '0', 'Авторизация на странице /login/');
        }


        //$this->install();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/comment/admin/comment_model.php');
        $Object = new Comment_Model();
        $this->data_model = $Object->get_model();
    }

    public function _preload() {
        if ($this->getConfigValue('apps.comment.enable')) {
            $this->template->assert('apps_comment_on', 1);
        } else {
            $this->template->assert('apps_comment_on', 0);
        }
    }

    public static function getCommentsWithUser($object_type, $object_id) {
        $DBC = DBC::getInstance();
        $object_id = (int) $object_id;
        $comments = array();
        if ($object_type != '' && $object_id != 0) {
            $query = 'SELECT c.*, u.fio, u.imgfile 
					FROM ' . DB_PREFIX . '_comment c
					LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id)
					WHERE c.object_type=? AND c.object_id=? AND c.is_published=1
					ORDER BY c.comment_date DESC';
            $stmt = $DBC->query($query, array($object_type, $object_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $comments[$ar['comment_id']] = $ar;
                    $comments[$ar['comment_id']]['attachments'] = SiteBill::getAttachments('comment', $ar['comment_id']);
                }
            }
        }
        return $comments;
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_comment` (
		  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `comment_text` text NOT NULL,
		  `comment_date` datetime NOT NULL,
		  `parent_comment_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `object_type` varchar(255) NOT NULL,
		  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '1',
		  PRIMARY KEY (`comment_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1 ;";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
            ;
        }
        return $rs;
    }

    function grid($params = array(), $default_params = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);


        $common_grid->add_grid_item($this->primary_key);
        $common_grid->add_grid_item('comment_text');
        $common_grid->add_grid_item('comment_date');

        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');

        $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));

        //$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by ".$this->primary_key." asc");
        $rs = $common_grid->construct_grid();
        return $rs;
    }

    function ajax() {
        if ($this->getRequestValue('action') == 'get_comments') {
            global $smarty;
            $comments = $this->getComments($this->getRequestValue('object_type'), $this->getRequestValue('object_id'));
            $this->template->assign('app_comment_comments', $comments);
            return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/comment/site/template/list.tpl');
        } elseif ($this->getRequestValue('action') == 'get_form') {
            return 'Тут форма';
        } elseif ($this->getRequestValue('action') == 'save_comment') {
            return $this->saveComment();
        }
        return false;
    }

    protected function getComments($object_type, $object_id) {
        $DBC = DBC::getInstance();
        if (1 == $this->getConfigValue('apps.comment.premoderation_enabled')) {
            $only_published = true;
        } else {
            $only_published = false;
        }
        $object_id = (int) $object_id;
        $comments = array();
        if ($object_type != '' && $object_id != 0) {
            $params = array();
            $params[] = $object_type;
            $params[] = $object_id;
            if ($only_published) {
                $params[] = 1;
            }

            $query = 'SELECT c.*, u.fio FROM ' . DB_PREFIX . '_' . $this->table_name . ' c LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE c.object_type=? AND c.object_id=?' . ($only_published ? ' AND c.is_published=?' : '') . ' ORDER BY c.comment_date DESC';
            $stmt = $DBC->query($query, $params);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $comments[] = $ar;
                }
            }
        }
        return $comments;
    }

    private function saveComment($admin = false) {

        $comments_delta_time = 30;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (!$admin && $form_data[$this->table_name]['user_id']['value'] != $_SESSION['user_id']) {
            return 'Error';
        }

        if (isset($_SESSION['apps_comment']['last_comment'][$_SESSION['user_id']]) && (time() - $_SESSION['apps_comment']['last_comment'][$_SESSION['user_id']]) < $comments_delta_time) {
            return 'Error';
        }

        if (1 == $this->getConfigValue('apps.comment.premoderation_enabled')) {
            $form_data[$this->table_name]['is_published']['value'] = 0;
        } else {
            $form_data[$this->table_name]['is_published']['value'] = 1;
        }

        $form_data[$this->table_name]['comment_date']['value'] = date('Y-m-d H:i:s', time());

        foreach ($form_data[$this->table_name] as $k => $v) {
            $form_data[$this->table_name][$k]['value'] = SiteBill::iconv('utf-8', SITE_ENCODING, $v['value']);
        }

        $form_data[$this->table_name]['comment_text']['value'] = nl2br(strip_tags($form_data[$this->table_name]['comment_text']['value']));

        if (!$this->check_data($form_data[$this->table_name])) {
            $rs = $this->get_form($form_data[$this->table_name], 'new');
            return 'Error';
        } else {
            $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if (!$admin) {
                $_SESSION['apps_comment']['last_comment'][$_SESSION['user_id']] = time();
            }
            if ($this->getError()) {
                return $this->getError();
            } else {
                return 'Ok';
            }
        }
    }

    public function saveCommentNotAjax($admin = false) {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (!$admin && $form_data[$this->table_name]['user_id']['value'] != $_SESSION['user_id']) {
            return 'Error';
        }


        $form_data[$this->table_name]['is_published']['value'] = 1;
        $form_data[$this->table_name]['comment_date']['value'] = date('Y-m-d H:i:s', time());

        $form_data[$this->table_name]['comment_text']['value'] = strip_tags($form_data[$this->table_name]['comment_text']['value']);

        if (!$this->check_data($form_data[$this->table_name])) {
            $rs = $this->get_form($form_data[$this->table_name], 'new');
            return 'Error';
        } else {
            $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if ($this->getError()) {
                return $this->getError();
            } else {
                return $new_record_id;
            }
        }
    }

}
