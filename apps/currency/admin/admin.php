<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Currencies options and courses admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');

class currency_admin extends Object_Manager {

    private $courses = array();

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();

        $this->table_name = 'currency';
        $this->action = 'currency';
        $this->primary_key = 'currency_id';

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.currency.cron_pass')) {
            $config_admin->addParamToConfig('apps.currency.cron_pass', '', 'Пароль запуска Cron-задач');
        }

        if (!$config_admin->check_config_item('apps.currency.default_grid_currency_id')) {
            $config_admin->addParamToConfig('apps.currency.default_grid_currency_id', '', 'ID валюты используемой при рассчете цен в списке');
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/currency_model.php');
        $this->data_model_object = new Currency_Model();
        $this->data_model = $this->data_model_object->get_model();

        //$this->install();
        $this->loadCourses();
    }

    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="' . SITEBILL_MAIN_URL . '/admin/index.php?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_PARAMETER') . '</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=recalc_courses&from=cbrf" class="btn btn-primary">Пересчитать курсы (ЦБ РФ)</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=recalc_courses&from=nbu" class="btn btn-primary">Пересчитать курсы (НБ Украина)</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=recalc_courses&from=nbrb" class="btn btn-primary">Пересчитать курсы (НБ РБ)</a> ';
        //$rs .= '</div>';
        //$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
        return $rs;
    }

    function ajax() {
        if ($this->getRequestValue('action') == 'set_default') {
            $user_id = intval($_SESSION['user_id_value']);
            $id = intval($this->getRequestValue('id'));
            $access_allow = false;
            $ret['status'] = 0;

            if ($user_id == 0) {
                
            } elseif ($_SESSION['current_user_group_name'] == 'admin') {
                $access_allow = true;
            } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                $permission = new Permission();
                if (!$permission->get_access($_SESSION['user_id_value'], 'client', 'access')) {
                    $access_allow = false;
                } else {
                    $access_allow = true;
                }
            }

            if (!$access_allow) {
                //$ret['status']=0;
            } else {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `is_default`=0 WHERE 1';
                $stmt = $DBC->query($query);
                $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `is_default`=1 WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($query, array($id));
                if ($stmt) {
                    $ret['status'] = 1;
                }
            }
            return json_encode($ret);
        }
        return false;
    }

    protected function goToMain() {
        header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=' . $this->action);
        exit();
    }

    function main() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        unset($form_data[$this->table_name]['is_default']);
        $rs = $this->getTopMenu();

        switch ($this->getRequestValue('do')) {
            case 'structure' : {
                    $rs = $this->structure_processor();
                    break;
                }

            case 'edit_done' : {
                    $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                    $form_data[$this->table_name]['course']['value'] = str_replace(',', '.', $form_data[$this->table_name]['course']['value']);


                    $data_model->forse_auto_add_values($form_data[$this->table_name]);
                    if (!$this->check_data($form_data[$this->table_name])) {
                        $rs = $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        $this->edit_data($form_data[$this->table_name]);
                        if ($this->getError()) {
                            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                            $rs = $this->get_form($form_data[$this->table_name], 'edit');
                        } else {
                            $rs .= $this->grid();
                        }
                    }
                    break;
                }

            case 'edit' : {

                    if ($this->getRequestValue('language_id') > 0 and ! $this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id'))) {
                        $rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
                    } else {
                        if ($this->getRequestValue('language_id') > 0) {
                            $form_data[$this->table_name] = $data_model->init_model_data_from_db_language($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id'));
                        } else {
                            $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
                        }
                        $rs = $this->get_form($form_data[$this->table_name], 'edit');
                    }

                    break;
                }
            case 'delete' : {
                    $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                    if ($this->getError()) {
                        $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
                        $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
                        $rs .= '</div>';
                    } else {
                        $rs .= $this->grid();
                    }


                    break;
                }

            case 'new_done' : {
                    $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                    $form_data[$this->table_name]['course']['value'] = str_replace(',', '.', $form_data[$this->table_name]['course']['value']);


                    $data_model->forse_auto_add_values($form_data[$this->table_name]);
                    if (!$this->check_data($form_data[$this->table_name]) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))) {
                        $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                        $rs = $this->get_form($form_data[$this->table_name], 'new');
                    } else {
                        $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
                        if ($this->getError()) {
                            $rs = $this->get_form($form_data[$this->table_name], 'new');
                        } else {
                            $rs .= $this->grid();
                        }
                    }
                    break;
                }
            case 'recalc_courses' : {
                    $from = $this->getRequestValue('from');
                    if ($from == 'nbu') {
                        $courses = $this->getNBUCourses();
                    } elseif ($from == 'nbrb') {
                        $courses = $this->getNBRBCourses();
                    }/* elseif($from=='usb'){
                      echo 1;
                      } */ else {
                        $courses = $this->getCBRFCourses();
                    }

                    $this->recalcCourses($courses);

                    $this->goToMain();
                    break;
                }

            case 'new' : {

                    $rs = $this->get_form($form_data[$this->table_name]);
                    break;
                }
            case 'mass_delete' : {
                    $id_array = array();
                    $ids = trim($this->getRequestValue('ids'));
                    if ($ids != '') {
                        $id_array = explode(',', $ids);
                    }
                    $rs .= $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
                    break;
                }
            default : {
                    $rs .= $this->grid($user_id);
                }
        }
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        return $rs_new;
    }

    public function recalcCoursesByCron() {
        $pass = trim($this->getConfigValue('apps.currency.cron_pass'));
        $cron_pass = trim($_GET['pass']);
        if ($pass == '' || $cron_pass != $pass) {
            return;
        }
        $from = trim($_GET['from']);
        if ($from == 'nbu') {
            $courses = $this->getNBUCourses();
        } elseif ($from == 'nbrb') {
            $courses = $this->getNBRBCourses();
        } elseif ($from == 'cbrf') {
            $courses = $this->getCBRFCourses();
        } else {
            return;
        }
        $this->recalcCourses($courses);
    }

    protected function recalcCourses($courses = false) {
        /* $courses=$this->getCBRFCourses();

          $courses=$this->getNBUCourses();

          $courses=$this->getNBRBCourses(); */


        if (false !== $courses) {
            $DBC = DBC::getInstance();
            $query = 'SELECT `currency_id`, LOWER(`code`) AS `code`, `is_default` FROM ' . DB_PREFIX . '_' . $this->table_name;
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ($ar['code'] == 'rur') {
                        $ar['code'] = 'rub';
                    }
                    if ($ar['is_default'] == 1) {
                        $default_curr = $ar['code'];
                    }
                    $valutes[$ar['code']] = $ar;
                }
            }
            //print_r($courses);	
            //$default_curr='ue';
            if (!isset($courses[$default_curr])) {
                return;
            }
            $k = $courses[$default_curr]['cv'];
            //var_dump($k);	
            foreach ($valutes as $ik => $val) {
                if (isset($courses[$ik])) {
                    $this_val_c = round(($courses[$ik]['cv']) / $k, 6);
                    $valutes[$ik]['this_val_c'] = $this_val_c;
                }
            }

            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `course`=? WHERE `currency_id`=?';
            foreach ($valutes as $ik => $val) {
                if (isset($val['this_val_c'])) {
                    $stmt = $DBC->query($query, array($val['this_val_c'], $val['currency_id']));
                }
            }
        }
    }

    private function getCBRAZCourses() {
        $url = 'http://www.cbar.az/currencies/' . date('d.m.Y') . '.xml';
        $ret = array();
        $xml = $this->loadExternalCourses($url);
        if ($xml) {
            $ret['azn']['cv'] = 1;
            foreach ($xml->ValType as $ValType) {
                foreach ($ValType->Valute as $valute) {
                    $char = strtolower(strval($valute['Code']));
                    $n = floatval(str_replace(',', '.', strval($valute->Nominal)));
                    $v = floatval(str_replace(',', '.', strval($valute->Value)));
                    $cv = $v / $n;
                    $ret[$char]['cv'] = $cv;
                }
            }
        } else {
            return false;
        }
        return $ret;
    }

    private function getNBRBCourses() {
        $url = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate=' . date('m/d/Y');
        $ret = array();
        $xml = $this->loadExternalCourses($url);
        if ($xml) {
            $ret['byr']['cv'] = 1;
            foreach ($xml->Currency as $valute) {
                $char = strtolower(strval($valute->CharCode));
                $n = floatval(str_replace(',', '.', strval($valute->Scale)));
                $v = floatval(str_replace(',', '.', strval($valute->Rate)));
                $cv = $v / $n;
                $ret[$char]['cv'] = $cv;
            }
        } else {
            return false;
        }
        return $ret;
    }

    private function getNBUCourses() {

        $url = 'http://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?date=' . date('Ymd');
        $ret = array();
        $xml = $this->loadExternalCourses($url);
        if ($xml) {
            $ret['uah']['cv'] = 1;
            foreach ($xml->currency as $valute) {
                $char = strtolower(strval($valute->cc));
                $n = 1;
                $v = floatval(str_replace(',', '.', strval($valute->rate)));
                $cv = $v / $n;
                $ret[$char]['cv'] = $cv;
            }
        } else {
            return false;
        }
        return $ret;
    }

    private function getCBRFCourses() {
        $url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d/m/Y');
        $ret = array();
        $xml = $this->loadExternalCourses($url);
        if ($xml) {
            $ret['rub']['cv'] = 1;
            foreach ($xml->Valute as $valute) {
                $char = strtolower(strval($valute->CharCode));
                $n = floatval(str_replace(',', '.', strval($valute->Nominal)));
                $v = floatval(str_replace(',', '.', strval($valute->Value)));
                $cv = $v / $n;
                $ret[$char]['cv'] = $cv;
            }
        } else {
            return false;
        }
        return $ret;
    }

    protected function loadExternalCourses($url) {
        include_once(SITEBILL_APPS_DIR . '/third/idna_convert/idna_convert.class.php');
        $url = SiteBill::iconv(SITE_ENCODING, 'utf-8', $url);
        $converter = new idna_convert();
        $domain = parse_url($url, PHP_URL_HOST);
        $encoded_domain = $converter->encode($domain);
        $url = str_replace($domain, $encoded_domain, $url);
        $resource = curl_init();
        curl_setopt($resource, CURLOPT_URL, $url);
        curl_setopt($resource, CURLOPT_TIMEOUT, 30);
        curl_setopt($resource, CURLOPT_MAXREDIRS, 10);
        curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($resource, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($resource);
        curl_close($resource);

        if ($response) {
            $data = @simplexml_load_string($response);
            if (is_object($data) AND $data !== FALSE) {
                return $data;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    private function loadCourses() {
        $query = 'SELECT `currency_id`, `course` FROM ' . DB_PREFIX . '_' . $this->table_name;
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $this->courses[$ar['currency_id']] = $ar['course'];
            }
        }
    }

    public function getActiveCurrencies() {
        $cachefile = SITEBILL_DOCUMENT_ROOT . '/cache/apps_currency_vlutes.cache.txt';
        if (!file_exists($cachefile) || (time() - filemtime($cachefile)) > 3600) {
            $currencies = array();
            $DBC = DBC::getInstance();
            $query = 'SELECT `currency_id`, `code`, `name`, `course` FROM ' . DB_PREFIX . '_currency WHERE `is_active`=1 ORDER BY `sort_order` ASC';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $currencies[$ar['currency_id']] = $ar;
                }
            }
            $f = fopen($cachefile, 'w');
            fwrite($f, serialize($currencies));
            fclose($f);
        } else {
            $currencies = unserialize(file_get_contents($cachefile));
        }

        return $currencies;
    }

    public function getUEPrice($sum, $curency_id) {
        if (isset($this->courses[$curency_id])) {
            return $sum * $this->courses[$curency_id];
        } else {
            return $sum;
        }
    }

    public function getUECoefficient($currency_from, $currency_to) {
        if (isset($this->courses[$currency_from]) && isset($this->courses[$currency_to])) {
            return $this->courses[$currency_from] / $this->courses[$currency_to];
        } else {
            return 1;
        }
    }

    public function getCourse($currency) {
        if (isset($this->courses[$currency])) {
            return $this->courses[$currency];
        } else {
            return 1;
        }
    }

    function grid($params = array(), $default_params = array()) {

        $request_params = array();
        $request_params['action'] = 'client';



        $DBC = DBC::getInstance();



        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' ORDER BY `sort_order`';

        $stmt = $DBC->query($query, $where_p);

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar;
            }
        }

        global $smarty;

        $smarty->assign('valutes', $ret);
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/template/grid.tpl');



        $params = array();
        $params['action'] = $this->action;



        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page.php');
        $common_page = new Common_Page();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/tab.php');
        $common_tab = new Common_Tab();
        $url = '/admin/index.php?action=' . $this->action;

        $common_grid->add_grid_item('currency_id');
        $common_grid->add_grid_item('code');
        $common_grid->add_grid_item('name');
        $common_grid->add_grid_item('is_active');
        $common_grid->add_grid_item('sort_order');
        $common_grid->add_grid_item('course');

        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
        $common_grid->set_grid_query("SELECT * FROM " . DB_PREFIX . "_" . $this->table_name . " ORDER BY sort_order ASC, code ASC, currency_id ASC");
        $params['page'] = $this->getRequestValue('page');
        $params['per_page'] = $this->getConfigValue('common_per_page');

        $common_grid->setPagerParams($params);

        $common_page->setTab($common_tab);
        $common_page->setGrid($common_grid);

        $rs .= $common_page->toString();
        return $rs;
    }

    function install() {
        $success_result = true;
        $DBC = DBC::getInstance();
        $query = "CREATE TABLE IF NOT EXISTS `re_currency` (
		  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` varchar(3) NOT NULL,
		  `name` varchar(30) NOT NULL,
		  `sort_order` tinyint(4) NOT NULL,
		  `course` varchar(10) NOT NULL,
		  `is_default` tinyint(4) NOT NULL DEFAULT '0',
		  `is_active` tinyint(4) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`currency_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;

        $query = "ALTER TABLE " . DB_PREFIX . "_data ADD COLUMN `currency_id` INT(11) NOT NULL DEFAULT 0";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;
        if (!$success_result) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
            ;
        }
        return $rs;
    }

    function convert($sum, $from_currency, $to_currency) {
        $result = $sum;
        $courses = array();
        $koefficient = 1;
        $DBC = DBC::getInstance();
        $query = 'SELECT `currency_id`, `course` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . $this->primary_key . ' IN (?,?)';
        $stmt = $DBC->query($query, array((int) $from_currency, (int) $to_currency));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $courses[$ar['currency_id']] = $ar['course'];
            }
        }
        if (!empty($courses)) {
            if (floatval($courses[$from_currency]) != 0 && floatval($courses[$to_currency]) != 0) {
                $koefficient = $courses[$from_currency] / $courses[$to_currency];
            }
        }
        return $sum * $koefficient;
    }

}
