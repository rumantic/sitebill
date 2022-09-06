<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Banner admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class banner_admin extends Object_Manager
{

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_name = 'banner';
        $this->action = 'banner';
        $this->primary_key = 'banner_id';

        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($this->table_name, false);
            if (empty($form_data) || count($form_data[$this->table_name]) == 0) {
                $form_data = array();
                $form_data = $this->get_banner_model();
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $this->table_name);
                $form_data = array();
                $form_data = $ATH->load_model($this->table_name, false);
            }
        } else {
            $form_data = $this->get_banner_model();
        }

        $this->data_model = $form_data;
    }

    function getTopMenu()
    {
        $rs = '<p>';
        $rs .= '<a href="?action=' . $this->action . '" class="btn btn-primary">Баннеры</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">Добавить баннер</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=informers" class="btn btn-primary">Информеры</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=informers&subdo=new" class="btn btn-primary">Добавить информер</a> ';
        $rs .= '</p>';
        return $rs;
    }

    function getInformer()
    {
        $client = $_GET['client'];

        $informer_cache = SITEBILL_DOCUMENT_ROOT . '/cache/informer_cache_' . $client . '.txt';
        if (file_exists($informer_cache) && (time() - filemtime($informer_cache)) < 3600) {
            return file_get_contents($informer_cache);
            exit();
        }

        $client_info = $this->getClientInfo($client);
        if (false === $client_info) {
            return '';
        }


        if ($client_info['informer_parameters']['domain'] != '') {
            $referer = $_SERVER['HTTP_REFERER'];
            $referer = trim($referer);
            $referer = preg_replace('/^(https:\/\/)/', '', $referer);
            $referer = preg_replace('/^(http:\/\/)/', '', $referer);
            $referer = trim($referer, '/');
            if (0 !== strpos($referer, $client_info['informer_parameters']['domain'])) {
                return '';
            }
        }
        if ((int)$client_info['is_active'] === 0) {
            return '';
        }
        //echo $referer;

        $site_url = $this->getServerFullUrl();
        $DBC = DBC::getInstance();
        $num = (int)$client_info['informer_parameters']['num'];
        if ($num === 0) {
            $num = 1;
        }


        $result = false;


        if ($client_info['informer_parameters']['source'] == 'complex') {
            $source = 'complex';
        } else {
            $source = 'data';
        }

        if ($source == 'data') {

            $filter = $client_info['informer_parameters']['filters'];

            if ($filter == 'hot=1') {
                $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE active=1 AND hot=1 ORDER BY date_added DESC LIMIT ' . $num;
            } else {
                $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE active=1 ORDER BY date_added DESC LIMIT ' . $num;
            }

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ids[] = $ar['id'];
                }
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data_shared = $data_model->get_kvartira_model(false, true);

            $form_data = array();

            foreach ($ids as $id) {
                $form_data[] = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
            }
            if (count($form_data) > 0) {
                foreach ($form_data as $item_id => $v) {
                    $form_data[$item_id]['_href'] = $this->getRealtyHREF($v['id']['value'], true, array('topic_id' => $v['topic_id']['value'], 'alias' => $v['translit_alias']['value']));
                }
            }
        } else {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('complex', true);
            if (empty($form_data)) {
                return '';
            }
            $form_data = $form_data['complex'];
            $search_params = array();
            $filter = trim($client_info['informer_parameters']['filters']);
            if ($filter != '') {
                parse_str($filter, $result);
                //print_r($result);
                foreach ($result as $pname => $pval) {
                    if (isset($form_data[$pname])) {
                        if ($form_data[$pname]['type'] == 'checkbox' && in_array($pval, array(0, 1))) {
                            $search_params[] = '`' . $pname . '`=' . $pval;
                        }
                    }
                }
            }

            $ids = array();

            $query = 'SELECT complex_id FROM ' . DB_PREFIX . '_complex' . (!empty($search_params) ? ' WHERE ' . implode(' AND ', $search_params) : '') . ' ORDER BY name DESC LIMIT ' . $num;

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ids[] = $ar['complex_id'];
                }
            }

            if (empty($ids)) {
                return '';
            }

            /*$form_data = array();

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
                $ATH = new Admin_Table_Helper();
                $form_data = $ATH->load_model('complex', true);
                if (empty($form_data)) {
                    return '';
                }
            } else {
                return '';
            }*/


            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');


            $data_model = new Data_Model();
            $form_data_shared = $form_data;

            $form_data = array();

            foreach ($ids as $id) {
                $form_data[] = $data_model->init_model_data_from_db('complex', 'complex_id', $id, $form_data_shared, true);
            }
            if (count($form_data) > 0) {
                foreach ($form_data as $item_id => $v) {
                    $form_data[$item_id]['_href'] = $site_url . '/' . $this->getConfigValue('apps.complex.alias') . '/' . $form_data[$item_id]['url']['value'];
                }
            }
        }

        $photofield = trim($client_info['informer_parameters']['photofield']);
        if ($photofield === '') {
            $photofield = 'image';
        }

        $textblock = trim($client_info['informer_parameters']['textblock']);
        $replacements = array();
        $fields = array();
        if ($textblock !== '') {
            if (preg_match_all('/\{([^}]+)\}/', $textblock, $matches)) {
                $replacements = $matches[0];
                $fields = $matches[1];
            }
        }


        if (empty($form_data)) {
            return '';
        }

        foreach ($form_data as $item_id => $v) {
            if (isset($v[$photofield]) && is_array($v[$photofield]['image_array']) && count($v[$photofield]['image_array']) > 0) {
                $form_data[$item_id]['_photofield'] = $site_url . '/img/data/' . $v[$photofield]['image_array'][0]['preview'];
            } else {
                $form_data[$item_id]['_photofield'] = $site_url . '/img/no_foto.png';
            }
            $texts = array();
            if (!empty($fields)) {
                foreach ($fields as $k => $f) {
                    if (isset($form_data[$item_id][$f])) {
                        if (in_array($form_data[$item_id][$f]['type'], array('select_by_query', 'select_box', 'structure'))) {
                            $texts[$k] = $form_data[$item_id][$f]['value_string'];
                        } else {
                            $texts[$k] = $form_data[$item_id][$f]['value'];
                        }
                    } else {
                        $texts[$k] = '';
                    }
                }
                $form_data[$item_id]['_textblock'] = str_replace($replacements, $texts, $textblock);
            } else {
                $form_data[$item_id]['_textblock'] = $textblock;
            }
        }


        global $smarty;
        $tpl = '';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/banner/site/template/informer.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/banner/site/template/informer.tpl';
        } else {
            if ($client_info['informer_parameters']['view_type'] == 'vs') {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/banner/site/template/informer_vertslider.tpl';
            } elseif ($client_info['informer_parameters']['view_type'] == 'hs' || $client_info['informer_parameters']['view_type'] == 'hs2') {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/banner/site/template/informer.tpl';
            } elseif ($client_info['informer_parameters']['view_type'] == 'hs_o') {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/banner/site/template/informer_jcr.tpl';
            }
        }
        $smarty->assign('realty', $form_data);
        $smarty->assign('view_type', $client_info['informer_parameters']['view_type']);
        $smarty->assign('biid', $client_info['biid']);
        $smarty->assign('ewidth', (intval($client_info['informer_parameters']['ewidth']) > 0 ? intval($client_info['informer_parameters']['ewidth']) : 200));
        $smarty->assign('eheight', (intval($client_info['informer_parameters']['eheight']) > 0 ? intval($client_info['informer_parameters']['eheight']) : 100));
        $smarty->assign('site_url', $site_url);
        $html = $smarty->fetch($tpl);
        $result['view_type'] = $client_info['informer_parameters']['view_type'];
        $result['autoslide'] = $client_info['informer_parameters']['autoslide'];
        $result['visels'] = (intval($client_info['informer_parameters']['visels']) == 0 ? 2 : intval($client_info['informer_parameters']['visels']));
        $result['biid'] = $client_info['biid'];
        $result['ewidth'] = (intval($client_info['informer_parameters']['ewidth']) > 0 ? intval($client_info['informer_parameters']['ewidth']) : 200);
        $result['eheight'] = (intval($client_info['informer_parameters']['eheight']) > 0 ? intval($client_info['informer_parameters']['eheight']) : 100);
        $result['data'] = $html;


        $stpl = SITEBILL_DOCUMENT_ROOT . '/apps/banner/site/template/runscript.js';
        //$stpl=SITEBILL_DOCUMENT_ROOT.'/apps/banner/site/template/slider_vert.js';
        $smarty->assign('data', json_encode($result));
        $text = $smarty->fetch($stpl);

        $f = fopen($informer_cache, 'w');
        fwrite($f, $text);
        fclose($f);

        return $text;
        /* if($client==1){
          $text='(function(g) {document.getElementById("sInformer").innerHTML=g.data})('.json_encode($result).')';
          }else{
          $text='(function(g) {document.getElementById("sInformer").innerHTML=g.data; $.getScript("http://estatecms.ru/apps/banner/site/template/example.js", function(){$(".brief").each(function() {$(this).children(".text").hide();var $this = $(this),children = $this.find("img");$(this).css({width: children.width()});$(this).children(".text").show();}); $("div.gallery-3").slider();}); })('.json_encode($result).')';
          }

          return $text; */
    }

    protected function getClientInfo($access_code)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_banner_informer WHERE `access_code`=?';
        $stmt = $DBC->query($query, array($access_code));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $ar['informer_parameters'] = unserialize($ar['informer_parameters']);
            return $ar;
        }
        return false;
        $clients = array(
            '1' => array('num' => 2),
            '2' => array('num' => 5),
            '3' => array('num' => 1),
        );
        if (isset($clients[$client])) {
            return $clients[$client];
        } else {
            return false;
        }
    }

    function _preload()
    {
        $requesturi = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $banners = array();
        $banners = $this->get_banners_list();
        if (count($banners) > 0) {
            $this->template->assert('random_banner', $banners[array_rand($banners, 1)]['body']);

            foreach ($banners as $v) {
                $banner_str = '';
                $active_url = false;
                if (isset($v['active_url']) && $v['active_url'] != '') {
                    $active_url = trim($v['active_url'], '/');
                }

                if ($active_url) {

                    if (preg_match('/^' . str_replace('/', '\/', $active_url) . '[\/]?/', $requesturi)) {
                        if ($v['url'] != '') {
                            $banner_str = '<a href="' . $v['url'] . '" class="thumbnail">' . $v['body'] . '</a>';
                        } else {
                            $banner_str = $v['body'];
                        }
                        $this->template->assert($v['title'], $banner_str);
                    }
                } else {
                    if ($v['url'] != '') {
                        $banner_str = '<a href="' . $v['url'] . '" class="thumbnail">' . $v['body'] . '</a>';
                    } else {
                        $banner_str = $v['body'];
                    }
                    $this->template->assert($v['title'], $banner_str);
                }
            }
        }
    }

    /**
     * Enter description here ...
     * @return unknown
     */
    function get_banners_list()
    {
        $DBC = DBC::getInstance();
        $query = "SELECT * FROM " . DB_PREFIX . "_banner WHERE published=1 ORDER BY " . $this->primary_key . " ASC";
        $ra = array();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar;
            }
        }
        return $ra;
    }

    function main()
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $rs = $this->getTopMenu();

        switch ($this->getRequestValue('do')) {
            case 'structure' :
            {
                $rs = $this->structure_processor();
                break;
            }

            case 'edit_done' :
            {
                $rs .= $this->_edit_doneAction();
                break;
            }

            case 'edit' :
            {
                $rs .= $this->_editAction();
                break;
            }
            case 'delete' :
            {
                $rs .= $this->_deleteAction();
                break;
            }

            case 'new_done' :
            {
                $rs .= $this->_new_doneAction();
                break;
            }

            case 'new' :
            {
                $rs .= $this->_newAction();
                break;
            }
            case 'informers' :
            {
                $rs .= $this->Informer();

                break;
            }
            case 'mass_delete' :
            {
                $id_array = array();
                $ids = trim($this->getRequestValue('ids'));
                if ($ids != '') {
                    $id_array = explode(',', $ids);
                }
                $rs .= $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
                break;
            }
            case 'change_param' :
            {
                $id_array = array();
                $ids = trim($this->getRequestValue('ids'));
                $param_name = trim($this->getRequestValue('param_name'));
                $param_value = trim($this->getRequestValue('new_param_value'));

                if (isset($form_data[$this->table_name][$param_name]) && $ids != '') {
                    //echo 1;
                    $id_array = explode(',', $ids);
                    $rs .= $this->mass_change_param($this->table_name, $this->primary_key, $id_array, $param_name, $param_value);
                } else {
                    $rs .= $this->grid();
                }
                break;
            }
            default :
            {
                $rs .= $this->grid($user_id);
            }
        }
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        return $rs_new;
    }


    protected function Informer_CreateCode()
    {
        //$codes=array();
        $code = substr(md5(time() . rand(100, 999)), 0, 10);
        $DBC = DBC::getInstance();
        $query = 'SELECT `access_code` FROM ' . DB_PREFIX . '_banner_informer WHERE `access_code`=?';
        $stmt = $DBC->query($query, array($code));
        if ($stmt) {
            return $this->Informer_CreateCode();
        }
        return $code;
    }

    protected function Informer_grid()
    {
        $bi = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_banner_informer';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['informer_parameters'] = unserialize($ar['informer_parameters']);
                $bi[] = $ar;
            }
        }
        global $smarty;
        $smarty->assign('bi', $bi);
        $rs = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/banner/admin/template/informer_list.tpl');
        return $rs;
    }

    protected function Informer()
    {
        $subdo = $this->getRequestValue('subdo');
        switch ($subdo) {
            case 'edit' :
            {
                $id = (int)$this->getRequestValue('biid');
                if (isset($_POST['submit'])) {
                    $bi = array();
                    $bi['is_active'] = (int)$this->getRequestValue('is_active');
                    $bi['access_code'] = trim($this->getRequestValue('access_code'));
                    if ('' === $bi['access_code']) {
                        $bi['access_code'] = $this->Informer_CreateCode();
                    }
                    $informer_parameters = $_POST['informer_parameters'];
                    if ('' !== trim($informer_parameters['domain'])) {
                        $domain = trim($informer_parameters['domain']);
                        $domain = preg_replace('/^(http:\/\/)/', '', $domain);
                        $domain = trim($domain, '/');
                        //preg_replace('/^(http)/', '', $domain);
                        $informer_parameters['domain'] = $domain;
                    }
                    $bi['informer_parameters'] = serialize($informer_parameters);
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_banner_informer SET `is_active`=?, `access_code`=?, `informer_parameters`=? WHERE biid=?';
                    $stmt = $DBC->query($query, array($bi['is_active'], $bi['access_code'], $bi['informer_parameters'], $id));
                    $rs = $this->Informer_grid();
                } else {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT * FROM ' . DB_PREFIX . '_banner_informer WHERE biid=?';
                    $stmt = $DBC->query($query, array($id));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $ar['informer_parameters'] = unserialize($ar['informer_parameters']);
                        $bi = $ar;
                    }

                    global $smarty;
                    $smarty->assign('bi', $bi);
                    $rs = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/banner/admin/template/informer_form.tpl');
                }

                break;
            }
            case 'new' :
            {
                //$id=(int)$this->getRequestValue('biid');
                if (isset($_POST['submit'])) {
                    $bi = array();
                    $bi['is_active'] = (int)$this->getRequestValue('is_active');
                    $bi['access_code'] = trim($this->getRequestValue('access_code'));
                    if ('' === $bi['access_code']) {
                        $bi['access_code'] = $this->Informer_CreateCode();
                    }
                    $informer_parameters = $_POST['informer_parameters'];
                    if ('' !== trim($informer_parameters['domain'])) {
                        $domain = trim($informer_parameters['domain']);
                        $domain = preg_replace('/^(http:\/\/)/', '', $domain);
                        $domain = trim($domain, '/');
                        $informer_parameters['domain'] = $domain;
                    }
                    $bi['informer_parameters'] = serialize($informer_parameters);
                    $DBC = DBC::getInstance();
                    $query = 'INSERT INTO ' . DB_PREFIX . '_banner_informer (`is_active`, `access_code`, `informer_parameters`) VALUES (?,?,?)';
                    $stmt = $DBC->query($query, array($bi['is_active'], $bi['access_code'], $bi['informer_parameters']));
                    $rs = $this->Informer_grid();
                } else {
                    $bi = array();
                    global $smarty;
                    $smarty->assign('bi', $bi);
                    $rs = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/banner/admin/template/informer_form.tpl');
                }

                break;
            }
            case 'code' :
            {
                $id = (int)$this->getRequestValue('biid');

                $DBC = DBC::getInstance();
                $query = 'SELECT * FROM ' . DB_PREFIX . '_banner_informer WHERE biid=?';
                $stmt = $DBC->query($query, array($id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $ar['informer_parameters'] = unserialize($ar['informer_parameters']);
                    $bi = $ar;
                }
                $c .= '<!-- Sitebill informer START -->' . "\n";
                $c .= '<div id="sInformer' . $bi['biid'] . '"></div>' . "\n";
                $c .= '<script src="' . $this->getServerFullUrl() . '/apps/banner/informer.php?client=' . $bi['access_code'] . '"></script>' . "\n";
                $c .= '<!-- Sitebill informer END -->';
                $rs = '<textarea style="height: 200px; width: 100%;">' . $c . '</textarea>';

                break;
            }
            default :
            {
                $rs = $this->Informer_grid();
            }
        }

        return $rs;
    }

    function install()
    {
        $DBC = DBC::getInstance();
        //create tables
        $query = "
CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_banner` (
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text,
  `image` text,
  `catalog_id` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " ;
        ";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');;
        }
        return $rs;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid($params = array(), $default_params = array())
    {

        $params = array();
        $params['action'] = $this->action;


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page.php');
        $common_page = new Common_Page();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/tab.php');
        $common_tab = new Common_Tab();
        $url = '/admin/index.php?action=' . $this->action;

        $common_grid->add_grid_item('banner_id');
        if (isset($this->data_model[$this->table_name]['human_title'])) {
            $common_grid->add_grid_item('human_title');
        }

        $common_grid->add_grid_item('title');
        $common_grid->add_grid_item('description');
        $common_grid->add_grid_item('published');


        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');

        $common_grid->set_grid_query("SELECT * FROM " . DB_PREFIX . "_" . $this->table_name . " ORDER BY banner_id DESC");
        $params['page'] = $this->getRequestValue('page');
        $params['per_page'] = $this->getConfigValue('common_per_page');

        $common_grid->setPagerParams($params);

        $common_page->setTab($common_tab);
        $common_page->setGrid($common_grid);

        $rs .= $common_page->toString();
        return $rs;
    }

    /**
     * Get gallery model
     * @param
     * @return
     */
    function get_banner_model()
    {
        $form_banner = array();

        $form_banner['banner']['banner_id']['name'] = 'banner_id';
        $form_banner['banner']['banner_id']['title'] = Multilanguage::_('L_ID');
        $form_banner['banner']['banner_id']['value'] = 0;
        $form_banner['banner']['banner_id']['length'] = 40;
        $form_banner['banner']['banner_id']['type'] = 'primary_key';
        $form_banner['banner']['banner_id']['required'] = 'off';
        $form_banner['banner']['banner_id']['unique'] = 'off';

        /* $form_banner['banner']['human_title']['name'] = 'human_title';
          $form_banner['banner']['human_title']['title'] = 'human_title';
          $form_banner['banner']['human_title']['value'] = '';
          $form_banner['banner']['human_title']['length'] = 40;
          $form_banner['banner']['human_title']['type'] = 'safe_string';
          $form_banner['banner']['human_title']['required'] = 'off';
          $form_banner['banner']['human_title']['unique'] = 'off'; */

        $form_banner['banner']['title']['name'] = 'title';
        $form_banner['banner']['title']['title'] = Multilanguage::_('L_BANNER_MARK');
        $form_banner['banner']['title']['value'] = '';
        $form_banner['banner']['title']['length'] = 40;
        $form_banner['banner']['title']['type'] = 'safe_string';
        $form_banner['banner']['title']['required'] = 'on';
        $form_banner['banner']['title']['unique'] = 'off';

        $form_banner['banner']['body']['name'] = 'body';
        $form_banner['banner']['body']['title'] = Multilanguage::_('L_BANNER_BODY');
        $form_banner['banner']['body']['value'] = '';
        $form_banner['banner']['body']['type'] = 'textarea';
        $form_banner['banner']['body']['required'] = 'on';
        $form_banner['banner']['body']['unique'] = 'off';
        $form_banner['banner']['body']['rows'] = '10';
        $form_banner['banner']['body']['cols'] = '60';

        $form_banner['banner']['description']['name'] = 'description';
        $form_banner['banner']['description']['title'] = 'Описание';
        $form_banner['banner']['description']['value'] = '';
        $form_banner['banner']['description']['type'] = 'textarea';


        $form_banner['banner']['published']['name'] = 'published';
        $form_banner['banner']['published']['title'] = Multilanguage::_('L_PUBLISHED');
        $form_banner['banner']['published']['value'] = '0';
        $form_banner['banner']['published']['type'] = 'checkbox';
        $form_banner['banner']['published']['required'] = 'off';
        $form_banner['banner']['published']['unique'] = 'off';

        $form_banner['banner']['url']['name'] = 'url';
        $form_banner['banner']['url']['title'] = Multilanguage::_('L_LINK');
        $form_banner['banner']['url']['value'] = '';
        $form_banner['banner']['url']['length'] = 40;
        $form_banner['banner']['url']['type'] = 'safe_string';
        $form_banner['banner']['url']['required'] = 'off';
        $form_banner['banner']['url']['unique'] = 'off';

        $form_banner['banner']['image']['name'] = 'image';
        $form_banner['banner']['image']['title'] = _e('Фото');
        $form_banner['banner']['image']['value'] = '';
        $form_banner['banner']['image']['length'] = 40;
        $form_banner['banner']['image']['type'] = 'uploads';
        $form_banner['banner']['image']['required'] = 'off';
        $form_banner['banner']['image']['unique'] = 'off';
        $form_banner['banner']['image']['parameters'] = array(
            'norm_width' => 1920,
            'norm_height' => 1080,
        );

        /*


          $form_banner['banner']['image']['name'] = 'image';
          $form_banner['banner']['image']['table_name'] = 'banner';
          $form_banner['banner']['image']['primary_key'] = 'id';
          $form_banner['banner']['image']['primary_key_value'] = 0;
          $form_banner['banner']['image']['action'] = 'banner';
          $form_banner['banner']['image']['title'] = 'Картинка';
          $form_banner['banner']['image']['value'] = '';
          $form_banner['banner']['image']['length'] = 40;
          $form_banner['banner']['image']['type'] = 'uploadify_image';
          $form_banner['banner']['image']['required'] = 'off';
          $form_banner['banner']['image']['unique'] = 'off';
         */
        return $form_banner;
    }

}
