<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Static pages handler backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class page_admin extends Object_Manager {

    private $pages = array();

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        Multilanguage::appendAppDictionary('page');
        $this->table_name = 'page';
        $this->action = 'page';
        $this->app_title = Multilanguage::_('APPLICATION_NAME', 'page');
        $this->primary_key = 'page_id';


        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($this->table_name, false);
            if (empty($form_data)) {
                $form_data = array();
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/page/admin/page_model.php');
                $Object = new Page_Model();
                $form_data = $Object->get_model();
                //$form_data = $this->get_banner_model();
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $this->table_name);
                $form_data = array();
                $form_data = $ATH->load_model($this->table_name, false);
            }
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/page/admin/page_model.php');
            $Object = new Page_Model();
            $form_data = $Object->get_model();
        }

        $this->data_model = $form_data;

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        $config_admin->addParamToConfig('apps.page.enable', '1', 'Включить Apps.Page', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.page.per_page', '3', 'Количество объектов на страницу');
        $config_admin->addParamToConfig('apps.page.count_on_main', '3', 'Количество объектов на главной');
        $config_admin->addParamToConfig('apps.page.blog_enable', '1', 'Включить вывод /blog/', SConfig::$fieldtypeCheckbox);
        $config_admin->addParamToConfig('apps.page.recommendations_enable', '1', 'Включить вывод /recommendations/', SConfig::$fieldtypeCheckbox);

    }

    public function sitemap_pages_count($sitemap) {
        $cnt = 0;
        $DBC = DBC::getInstance();
        $query = 'SELECT is_service FROM ' . DB_PREFIX . '_page LIMIT 1';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $has_service = true;
        } else {
            $has_service = false;
        }

        if ($has_service) {
            $query = 'SELECT COUNT(`page_id`) AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE (`is_service` <> 1 OR `is_service` IS NULL) AND `uri` <> ? ';
        } else {
            $query = 'SELECT COUNT(`page_id`) AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `uri` <> ? ';
        }

        $stmt = $DBC->query($query, array(''));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $cnt += $ar['_cnt'];
        }
        if($cnt > 0){
            $cnt = intval(ceil($cnt/$sitemap->getPerPageCount()));
        }
        return $cnt;
    }

    public function sitemap($sitemap) {
        $urls = array();

        $changefreq = (intval($this->getConfigValue('apps.sitemap.changefreq.page')) < 7 ? intval($this->getConfigValue('apps.sitemap.changefreq.page')) : '6');
        $changefreq = $sitemap->validateFrequency($changefreq);
        $priority = $sitemap->validatePriority($this->getConfigValue('apps.sitemap.priority.page'));

        $DBC = DBC::getInstance();
        $query = 'SELECT is_service FROM ' . DB_PREFIX . '_page LIMIT 1';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $has_service = true;
        } else {
            $has_service = false;
        }

        if ($has_service) {
            $query = 'SELECT uri FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE is_service <> 1 or `is_service` is NULL ';
        } else {
            $query = 'SELECT uri FROM ' . DB_PREFIX . '_' . $this->table_name;
        }


        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                /* if($ar['uri']!=''){
                  $url=trim(str_replace('\\', '/', $ar['uri']),'/');
                  } */
                if ($ar['uri'] != '') {
                    //$url=trim(str_replace('\\', '/', $ar['uri']),'/');
                    $url = SITEBILL_MAIN_URL . '/' . $ar['uri'];
                    $urls[] = array('url' => $url . (false !== strpos($url, '.') ? '' : self::$_trslashes), 'changefreq' => $changefreq, 'priority' => $priority);
                }
            }
        }
        return $urls;
    }

    public function sitemapHTML($sitemap) {
        if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
            $trailing_slashe = '';
        } else {
            $trailing_slashe = '/';
        }
        $urls = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT is_service FROM ' . DB_PREFIX . '_page LIMIT 1';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $has_service = true;
        } else {
            $has_service = false;
        }


        if ($has_service) {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `is_service` <> 1  or `is_service` is NULL  ORDER BY ' . $this->primary_key . ' DESC';
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' ORDER BY ' . $this->primary_key . ' DESC';
        }

        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['href'] = SITEBILL_MAIN_URL . '/' . trim($ar['uri'], '/') . (false !== strpos($ar['uri'], '.') ? '' : $trailing_slashe);
                $urls[] = array('t' => $ar['title'], 'h' => $ar['href']);
            }
        }
        return $urls;
    }

    public function _preload() {
        $this->getPagesColumn();
    }

    function ajax() {
        if ($this->getRequestValue('action') == 'get_transliteration') {
            return $this->transliteMe($this->getRequestValue('word'));
        }
        return false;
    }

    public function _before_check_action($form_data, $type = 'new'){
        $form_data = parent::_before_check_action($form_data, $type);
        if (isset($form_data['uri']) && $form_data['uri']['value'] == '' && isset($form_data['title']) && $form_data['title']['value'] != '') {
            $form_data['uri']['value'] = $this->transliteMe($form_data['title']['value']);
            $form_data['uri']['value'] = preg_replace('/[^a-zA-Z0-9_\/.-]/', '', $form_data['uri']['value']);
        }
        return $form_data;
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {

        $rs = '';

        $_SESSION['allow_disable_root_structure_select'] = true;
        global $smarty;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();


        $rs .= $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }

        $rs .= '<form method="post" class="form-horizontal" action="' . $action . '" enctype="multipart/form-data">';
        $rs .= '<a class="btn btn-mini btn-info alias_create" href="">' . Multilanguage::_('CREATE_ALIAS', 'page') . '</a>
		<a href="javascript:void(0);" rel="popover" class="tooltipe_block btn btn-info btn-mini" data-content="Правильный URI страницы должен состоять только из латинских букв, цифр и - _ При нажатии на эту кнопку из заголовка будет автоматически создано значение в транслите в URI. Укажите заголовок." data-original-title="" title=""> <i class="icon-question-sign icon-white"></i></a>    	
    	';
        $rs .= '<script>
    			$(document).ready(function(){
    			$(\'.alias_create\').click(function(){
    			var parent=$(this).parents(\'form\').eq(0);
    			var title=parent.find(\'input[name=title]\');
    			var uri=parent.find(\'input[name=uri]\');
    			if(title && uri && title.val()!=\'\'){
    				$.ajax({
    					url: \'' . SITEBILL_MAIN_URL . '/apps/page/js/ajax.php\',
    					type: \'post\',
    					data: {action: \'get_transliteration\', word: title.val()},
    					dataType: \'text\',
    					success: function(text){
    						uri.val(text);
    					}
    				});
    			}
    	
    			return false;
    			});
    			});</script>';
        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);

        if ($do == 'new') {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $this->getRequestValue($this->primary_key) . '" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $form_data[$this->primary_key]['value'] . '" />');
        }
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';

        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');
        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    protected function getPagesColumn() {
        if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
            $trailing_slashe = '';
        } else {
            $trailing_slashe = '/';
        }
        $ret = '';
        $Records = array();
        $DBC = DBC::getInstance();
        $page = 1;
        $per_page = $this->getConfigValue('apps.page.count_on_main');

        $start = ($page - 1) * $per_page;
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' ORDER BY ' . $this->primary_key . ' DESC LIMIT ' . $start . ', ' . $per_page;
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['href'] = SITEBILL_MAIN_URL . '/' . trim($ar['uri'], '/') . (false !== strpos($ar['uri'], '.') ? '' : $trailing_slashe);
                $fp = strpos($ar['body'], '<p>');
                $lp = strpos($ar['body'], '</p>');

                if ($fp !== false && $lp !== false) {
                    $ar['body'] = strip_tags(substr($ar['body'], $fp, $lp));
                } else {
                    $ar['body'] = substr(strip_tags($ar['body']), 0, 200);
                }
                $Records[] = $ar;
            }
        }

        global $smarty;

        $smarty->assign('PagesColumnRecords', $Records);
        $theme = $this->getConfigValue('theme');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/page/site/template/pages_column.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/page/site/template/pages_column.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/page/site/template/pages_column.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/page/site/template/pages_column.tpl';
        } else {
            $tpl = false;
        }
        if ($tpl && !empty($Records)) {
            $ret = $smarty->fetch($tpl);
        }
        $this->template->assert('apps_pages_column', $ret);
    }

    function main() {
        $rs .= parent::main();
        return $rs;
    }

    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('ADD_PAGE', 'page') . '</a>';
        $rs .= $this->get_extended_items();
        return $rs;
    }

    function grid($params = array(), $default_params = array()) {
        return parent::grid($params, $default_params);
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_" . $this->table_name . "` (
		  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `uri` varchar(255) NOT NULL DEFAULT '',
		  `meta_keywords` text,
		  `meta_description` text,
		  `body` text,
		  `date` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`page_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " ;";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

}
