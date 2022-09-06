<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Dashboard admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class dashboard_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        //Multilanguage::appendAppDictionary('dashboard');
        $this->action = 'dashboard';

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.dashboard.enable')) {
            $config_admin->addParamToConfig('apps.dashboard.enable', '0', 'Включить приложение Помощник', 1);
        }
        $this->onInit();
    }

    public function _preload() {
        if ($this->getConfigValue('apps.dashboard.enable')) {
            $this->template->assert('dashboard', $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/dashboard/admin/template/start_dashboard_js_code.tpl'));
            if ( $this->getSessionUserId() > 0 ) {
                \SConfig::setConfigValueStatic('editor_mode', true);
            }
        }
    }

    protected function onInit () {

    }

    protected function onInitAjax () {
        //$this->writeLog(__METHOD__);
    }

    private function first_session_run () {
        if ( $_SESSION['first_run'] != 'run' ) {
            $_SESSION['first_run'] = 'run';
            $this->sendFirmMail('report@etown.ru', 'info@etown.ru', 'first run '.$_SERVER['HTTP_HOST'], '<pre>'.var_export($_REQUEST, true).'</pre>');
        }

    }

    public function ajax() {
        $this->first_session_run();
        $this->onInitAjax();
        if ($this->getRequestValue('action') == 'iframe') {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_mask.php');
            $CM = new Config_Mask();
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
            $form_generator = new Form_Generator();

            $theme_items['name'] = 'theme';
            $theme_items['select_data'] = $CM->get_themes_array();
            $theme_items['value'] = $this->getConfigValue('theme');

            $this->template->assign('theme_select', $form_generator->get_select_box($theme_items));
            $local_dashboard_template = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/dashboard/site/template/main_dashboard.tpl';
            if (file_exists($local_dashboard_template) ) {
                echo $this->template->fetch($local_dashboard_template);
            } else {
                echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/dashboard/admin/template/main_dashboard.tpl');
            }
        } elseif ($this->getRequestValue('action') == 'editor') {
            echo $this->editor();
            exit;
        } elseif ($this->getRequestValue('action') == 'save') {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
            $config_admin = new config_admin();
            if ($this->getRequestValue('theme') != '') {
                $DBC = DBC::getInstance();
                $query = "UPDATE `" . DB_PREFIX . "_config` SET `value`=? WHERE `config_key`=?";
                $stmt = $DBC->query($query, array($this->getRequestValue('theme'), 'theme'));
                if ($this->getRequestValue('theme') == 'novosel') {
                    $stmt = $DBC->query($query, array('3', 'bootstrap_version'));
                } else {
                    $stmt = $DBC->query($query, array('', 'bootstrap_version'));
                }
                $this->sendFirmMail('report@etown.ru', 'info@etown.ru', ''.$_SERVER['HTTP_HOST'].', theme = '.$this->getRequestValue('theme'), '<pre>'.var_export($_REQUEST, true).'</pre>');
            }
            $this->clear_apps_cache();
            $ra['result'] = 'success';
            echo json_encode($ra);
            exit;
        } else {
            echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/dashboard/admin/template/dashboard_iframe_code.tpl');
        }
    }

    /**
     * Функция редактирования шаблонов
     */
    function editor() {
        //$ra['result'] = 'editor_complete' . $this->getRequestValue('edit_content');

        $elid = trim($this->getRequestValue('elid'));
        $editable_file_name = trim(str_replace(array('/', '..', '\\'), '', $this->getRequestValue('file')));
        if ($editable_file_name == '') {
            return json_encode(array('status' => 0));
        }
        if (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$this->getConfigValue('theme').'/' . $editable_file_name)) {
            return json_encode(array('status' => 0));
        }

        /* if (!function_exists('file_get_html')) {
          if (file_exists(SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php')) {
          require_once SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php';
          } else {
          require_once SITEBILL_DOCUMENT_ROOT . '/third/simple_html_dom/simple_html_dom.php';
          }
          } */


        $data = @file_get_contents(SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$this->getConfigValue('theme').'/' . $editable_file_name);
        if (!$data) {
            return json_encode(array('status' => 0));
        }

        if (!preg_match('/<editable([^>]*)id="' . $elid . '"([^>]*)>/', $data, $matches)) {
            return json_encode(array('status' => 0));
        }
        $opentag = $matches[0];
        if (!preg_match('/data-file="' . preg_quote($editable_file_name) . '"/', $opentag)) {
            return json_encode(array('status' => 0));
        }
        $closetag = '</editable>';


        $pos = mb_strpos($data, $opentag, 0, 'utf-8');
        if (false === $pos) {
            return json_encode(array('status' => 0));
        }
        $pos = $pos + mb_strlen($opentag, 'utf-8');
        $pos2 = mb_strpos($data, $closetag, $pos, 'utf-8');
        if (false === $pos2) {
            return json_encode(array('status' => 0));
        }
        //$pos=$pos+mb_strlen('<editable id="'.$elid.'" contenteditable="true">', 'utf-8');
        //echo mb_substr($data, $pos2, null, 'utf-8');
        //echo $pos;
        //echo '<br >';
        //echo $pos2;
        $str = mb_substr($data, $pos, $pos2 - $pos, 'utf-8');
        $file_parts = array();
        $file_parts[0] = mb_substr($data, 0, $pos, 'utf-8');
        $file_parts[1] = mb_substr($data, $pos, $pos2 - $pos, 'utf-8');
        $file_parts[2] = mb_substr($data, $pos2, mb_strlen($data, 'utf-8'), 'utf-8');
        $content = $_POST['edit_content'];
        $file_parts[1] = $content;
        $data = implode('', $file_parts);
        //print_r($file_parts);
        //echo $str;
        /*
         * Нужно в $data найти контент для id="site_slogan_edit"
         * Заменить innerHTML на $this->getRequestValue('edit_content')
         * и сохранить измененный файл обратно в header_contact_add.tpl
         * Только непонятно как через DOM будут пересохранятся smarty теги и будут ли вообще
         * Нужно подумать как вариант поиска и замены выбрать, может быть через регулярные выражения
         */


        //$data = file_get_contents(SITEBILL_DOCUMENT_ROOT."/template/frontend/realia/header_contact_add.tpl");
        //$data = str_replace("2","",$data); // Заменить 2-ки на пустые места
        //$data = str_replace("6","",$data); // Заменить 6-ки на пкстые места
        $handle = fopen(SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$this->getConfigValue('theme').'/' . $editable_file_name, "w+"); // Открыть файл, сделать его пустым
        fwrite($handle, $data); // Записать переменную в файл
        fclose($handle); // Закрыть файл
        return json_encode(array('status' => 1));
        //echo json_encode($ra);
    }

}

?>
