<?php

/**
 * Ajax server class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');

require_once __DIR__ . '/traits/AjaxPaymentTrait.php';
require_once __DIR__ . '/traits/AjaxTopicActionsTrait.php';
require_once __DIR__ . '/traits/AjaxMapTrait.php';
require_once __DIR__ . '/traits/AjaxResponseTrait.php';

class Ajax_Server extends SiteBill
{

    protected $ajax_user_mode;
    protected $ajax_controller_user_id;
    use AjaxPaymentTrait,
        AjaxTopicActionsTrait,
        AjaxMapTrait,
        AjaxResponseTrait;


    /**
     * Construct
     */
    function __construct()
    {
        parent::__construct();
        Multilanguage::appendTemplateDictionary($this->getConfigValue('theme'));
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {

        /* $ajax_action=$this->getRequestValue('action');
          $_ajax_action=$this->getRequestValue('_action');
          $controller_action='_'.$ajax_action.'AjaxAction';
          if(!method_exists($this, $action)){
          $controller_action='_defaultAjaxAction';
          } */

        if (1 == $this->getConfigValue('is_underconstruction') and !isset($_SESSION['user_id_value'])) {
            $access_allowed = false;
            $ip = $_SERVER['REMOTE_ADDR'];

            if ($ip != '') {
                $allowed_ips = array();

                if ('' !== trim($this->getConfigValue('is_underconstruction_allowed_ip'))) {
                    $allowed_ips = explode(',', trim($this->getConfigValue('is_underconstruction_allowed_ip')));
                }

                if (count($allowed_ips) > 0) {
                    foreach ($allowed_ips as $allowed_ip) {
                        $testing_ip = str_replace(array('*', '.'), array('(\d+)', '\.'), $allowed_ip);
                        if (preg_match('/^' . $testing_ip . '$/', $ip)) {
                            $access_allowed = true;
                            break;
                        }
                    }
                }
            }


            if (!$access_allowed) {
                return false;
            }
        }

        /*
          $ref=$_SERVER['HTTP_REFERER'];
          if($ref!=''){
          $dom=parse_url($ref, PHP_URL_HOST);
          $host = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);
          //$this->writeLog($dom.' = '.$host);
          if($dom!=$host){
          return false;
          }
          }
         *
         */


        /* if(1==$this->getConfigValue('is_underconstruction')){
          $ip=$_SERVER['REMOTE_ADDR'];
          if($ip=='' || $ip!=$this->getConfigValue('is_underconstruction_allowed_ip')){
          return false;
          }
          } */

        $is_local = (int)$this->getRequestValue('local_ajax');
        if ($is_local == 1 && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/ajax/local_ajax_server.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/ajax/local_ajax_server.php';
            $LAS = new Local_Ajax_Server();
            return $LAS->main();
        }

        global $estate_folder;
        global $smarty;
        $smarty->assign('estate_folder', $estate_folder);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $kvartira_model = $data_model->get_kvartira_model(true);

        $ajax_controller_user_id = (int)$_SESSION['user_id'];
        $this->ajax_user_mode = 'guest';

        if ($ajax_controller_user_id == 0) {
            $ajax_controller_user_id = (int)$_SESSION['user_id_value'];
        }

        $this->ajax_controller_user_id = $ajax_controller_user_id;


        if ($ajax_controller_user_id != 0) {
            $DBC = DBC::getInstance();
            $query = 'SELECT system_name FROM ' . DB_PREFIX . '_group WHERE group_id=(SELECT group_id FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1)';
            $stmt = $DBC->query($query, array($ajax_controller_user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['system_name'] == 'admin') {
                    $this->ajax_user_mode = 'admin';
                } else {
                    $this->ajax_user_mode = 'user';
                }
            }
        }


        /*
         * Подключение ajax-обработчиков компонентов
         * пока не определены интерфейсы для них, используем именное подключение
         */
        if ($this->getRequestValue('_component') !== NULL) {
            $component = trim($this->getRequestValue('_component'));
            if ($component == 'datacompare') {
                $component_path = SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/' . $component . '/' . $component . '.php';
                $component_class = $component;
                if (!file_exists($component_path)) {
                    exit();
                }
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once($component_path);

                $component_ajax = new $component_class();
                if (method_exists($component_ajax, 'ajax')) {
                    return $component_ajax->ajax();
                }
                exit();
            }
        }

        if ($this->getRequestValue('_app') !== NULL) {
            $app = trim($this->getRequestValue('_app'));
            $app_class = $app . '_admin';
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/' . $app . '/admin/admin.php');
            $app_ajax = new $app_class();
            if (method_exists($app_ajax, 'ajax')) {
                return $app_ajax->ajax();
            }
            exit();
        }

        if ($this->getRequestValue('_action') != '') {
            switch ($this->getRequestValue('_action')) {

                case 'save_changes' :
                {
                    if ($this->ajax_user_mode == 'guest') {
                        return 'error';
                    }

                    $allow_edit = false;

                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');

                    if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/data/data_manager.php')) {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/data/data_manager.php');
                        $DM = new Data_Manager_Local();
                        //return 3;
                    } else {
                        $DM = new Data_Manager();
                        //return 2;
                    }


                    //$DM=new Data_Manager();
                    $Model = new Data_Model();
                    $form_data = $DM->data_model;
                    $table = $DM->table_name;
                    $form_data[$table] = $Model->init_model_data_from_request($form_data[$table]);

                    if ($this->ajax_user_mode == 'user') {
                        $DBC = DBC::getInstance();
                        $query = 'SELECT COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_data WHERE id=? AND user_id=?';
                        $stmt = $DBC->query($query, array($form_data[$table]['id']['value'], $ajax_controller_user_id));
                        if ($stmt) {
                            $ar = $DBC->fetch($stmt);
                            if ($ar['_cnt'] == 1) {
                                $allow_edit = true;
                            }
                        }
                    } elseif ($this->ajax_user_mode == 'admin') {
                        $allow_edit = true;
                    }

                    if ($allow_edit) {
                        foreach ($form_data[$table] as $k => $fd) {
                            if (!is_array($form_data[$table][$k]['value'])) {
                                $form_data[$table][$k]['value'] = SiteBill::iconv('utf-8', SITE_ENCODING, $form_data[$table][$k]['value']);
                            }
                        }
                        $data_model->forse_auto_add_values($form_data[$table]);

                        if (!$DM->check_data($form_data[$table])) {
                            return 'error';
                        } else {
                            $DM->edit_data($form_data[$table]);
                            if ($DM->getError()) {
                                return 'error';
                            } else {
                                if ($this->getConfigValue('apps.realtylog.enable')) {
                                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                                    $Logger = new realtylog_admin();
                                    $Logger->addLog($form_data[$table]['id']['value'], $_SESSION['user_id_value'], 'edit', 'data');
                                }
                                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                                    $Logger = new realtylogv2_admin();
                                    $Logger->addLog($form_data[$table]['id']['value'], $_SESSION['user_id_value'], 'edit', 'data', 'id');
                                }
                                return 'saved';
                            }
                        }
                    } else {
                        return 'error';
                    }
                    break;
                }
            }
        }

        switch ($this->getRequestValue('action')) {

            /* case 'location' : {

              $term = trim($this->getRequestValue('term'));
              $DBC = DBC::getInstance();
              $query = 'SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE name LIKE ?';
              $stmt = $DBC->query($query, array($term));
              while($ar = $DBC->fetch($stmt)){
              $ret[] = $ar;
              }
              return json_encode($ret);
              exit();
              } */

            case 'ipinfo' : {
                $ip = $_SERVER['REMOTE_ADDR'];

                if(isset($_SESSION['user_geoip']) && $_SESSION['user_geoip']['ip'] == $ip){
                    echo json_encode(array(
                        'ip' => $_SESSION['user_geoip']['ip'],
                        'country' => $_SESSION['user_geoip']['country']
                    ));
                    exit();
                }

                $_SESSION['user_geoip'] = [
                    'ip' => $ip,
                    'country' => ''
                ];

                if(ip2long($ip) >= ip2long('127.0.0.0') && ip2long($ip) <= ip2long('127.255.255.255')){
                    $_SESSION['user_geoip']['country'] = 'us';
                }else{
                    $DBC = DBC::getInstance();
                    $query = 'SELECT country FROM '.DB_PREFIX.'_iplookup WHERE ip = ?';
                    $stmt = $DBC->query($query, array(ip2long($ip)));
                    if($stmt){
                        $ar = $DBC->fetch($stmt);
                        $_SESSION['user_geoip']['country'] = $ar['country'];
                    }else{
                        $url = 'https://ipinfo.io/'.$ip.'';

                        $resource = curl_init();
                        curl_setopt($resource, CURLOPT_URL, $url);
                        curl_setopt($resource, CURLOPT_TIMEOUT, 30);
                        curl_setopt($resource, CURLOPT_MAXREDIRS, 10);
                        curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($resource, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($resource, CURLOPT_HEADER, 1);
                        curl_setopt($resource, CURLOPT_HTTPHEADER, [
                            'Accept: application/json'
                        ]);
                        $response = curl_exec($resource);

                        $RESPONSE_CODE = curl_getinfo($resource, CURLINFO_RESPONSE_CODE);

                        if($RESPONSE_CODE === 200){
                            $header_size = curl_getinfo($resource, CURLINFO_HEADER_SIZE);
                            $body = substr($response, $header_size);
                            curl_close($resource);
                            $data = json_decode($body, true);
                            $query = 'INSERT INTO '.DB_PREFIX.'_iplookup (`ip`, `country`) VALUES (?, ?)';
                            $DBC->query($query, array(ip2long($ip), mb_strtolower($data['country'])));
                            $_SESSION['user_geoip']['country'] = $data['country'];
                        }
                    }
                }

                echo json_encode(array(
                    'ip' => $_SESSION['user_geoip']['ip'],
                    'country' => $_SESSION['user_geoip']['country']
                ));
                exit();
            }

            case 'multiselect-options': {
                $model = $this->getRequestValue('model');
                $element = $this->getRequestValue('element');
                $term = urldecode($this->getRequestValue('term'));
                $existing = $this->getRequestValue('existing');

                $ATH = new Admin_Table_Helper();

                $modelInstance = $ATH->load_model($model, false, false);
                $modelInstance = $modelInstance[$model];
                if(!isset($modelInstance[$element])){
                    throw new \Exception('Boo');
                }

                $elementInstance = $modelInstance[$element];

                if($elementInstance['type'] !== 'select_by_query_multi' || $elementInstance['parameters']['mode'] !== 'tag'){
                    throw new \Exception('Boo');
                }

                $DBC = DBC::getInstance();

                $value_name = $elementInstance['value_name'];
                $value_name_l = $elementInstance['value_name'].$this->getLangPostfix($this->getCurrentLang());

                $where = [];
                $where_v = [];

                $where[] = '`' . $value_name_l . '` LIKE ?';
                $where_v[] = '%'.$term.'%';

                if(!empty($existing)){
                    $where[] = '`' . $elementInstance['primary_key_name'] . '` NOT IN ('.implode(',', array_fill(0, count($existing), '?')).')';
                    $where_v = array_merge($where_v, $existing);
                }

                $query = 'SELECT `' . $elementInstance['primary_key_name'] . '`, `' . $value_name_l . '` AS ' . $value_name . ' FROM ' . DB_PREFIX . '_' . $elementInstance['primary_key_table'].' WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $value_name . ' ASC';

                $stmt = $DBC->query($query, $where_v);

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $list[$ar[$elementInstance['primary_key_name']]] = $ar[$value_name];
                    }
                }

                return json_encode($list);
                exit();
            }

            case 'save_contactus' :
            {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/contactus.php';
                $CA = new contactus_Form();

                return $CA->save_message();

                break;
            }

            case 'set_grid_type' :
            {
                $_SESSION['grid_type'] = trim($_POST['type']);
                return json_encode(array('status' => 1));
            }

            case 'set_lang' :
            {
                $lang = trim(strtolower($_POST['lang']));
                if (in_array($lang, Multilanguage::availableLanguages())) {
                    $_SESSION['_lang'] = $lang;
                    return json_encode(array('status' => 1));
                } else {
                    return json_encode(array('status' => 0));
                }
            }


            case 'build_captcha' :
            {
                $c['captcha']['name'] = 'captcha';
                $c['captcha']['title'] = 'Защитный код';
                $c['captcha']['value'] = '';
                $c['captcha']['length'] = 40;
                $c['captcha']['type'] = 'captcha';
                $c['captcha']['required'] = 'on';
                $c['captcha']['unique'] = 'off';

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
                $form_generator = new Form_Generator();

                $el = $form_generator->compile_form_elements($c);
                //var_dump($el['hash']['captcha']);

                return $el['hash']['captcha']['html'];

                break;
            }

            case 'city_load_data' :
            {
                //EXPERIMENTAL
                if ($this->ajax_user_mode == 'admin') {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/city/city_manager.php';
                    $CA = new city_manager();
                    return $CA->ajax();
                } else {
                    return '';
                }

                break;
            }

            case 'markers' :
            {
                $lb = $this->getRequestValue('lb');
                $rt = $this->getRequestValue('rt');
                $ret = array();

                $DBC = DBC::getInstance();
                $query = 'SELECT geo_lat, geo_lng FROM ' . DB_PREFIX . '_data WHERE (geo_lat BETWEEN ? AND ?) AND (geo_lng BETWEEN ? AND ?) LIMIT 1000';
                $stmt = $DBC->query($query, array($lb[0], $rt[0], $lb[1], $rt[1]));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ret[] = $ar;
                    }
                }
                return json_encode($ret);
                break;
            }
            case 'map_search' :
            {

                $grid = $this->_getGridConstructor();
                return $grid->map_search();
            }
            case 'map_search_listing' :
            {

                $grid = $this->_getGridConstructor();
                return $grid->map_search_listing();
            }
            case 'map_search_items_html' :
            {

                $grid = $this->_getGridConstructor();
                return $grid->map_search_items_html($this->getRequestValue('ids'));
            }
            case 'map_search_items' :
            {

                $grid = $this->_getGridConstructor();
                return $grid->map_search_items($this->getRequestValue('ids'));
            }
            case 'iframe_map' :
            {
                echo $this->_iframe_mapAjaxAction();
                exit();
                break;
            }

            case 'get_courses' :
            {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
                $CA = new currency_admin();
                $currencies = $CA->getActiveCurrencies();
                $from_curid = intval($this->getRequestValue('curid'));

                /* $DBC=DBC::getInstance();
                  $query='SELECT currency_id, course, name FROM '.DB_PREFIX.'_currency';
                  $stmt=$DBC->query($query);
                  if($stmt){
                  while($ar=$DBC->fetch($stmt)){
                  $currencies[$ar['currency_id']]=$ar;
                  }
                  } */

                $koef = 1;
                $koef = $koef / $currencies[$from_curid]['course'];

                foreach ($currencies as $k => $v) {
                    $currencies[$k]['course'] = $koef * $v['course'];
                    $currencies[$k]['name'] = $v['name'];
                }

                return json_encode($currencies);
                break;
            }

            case 'change_element_name' :
            {
                $ret = array('status' => 0);
                if ($this->ajax_user_mode !== 'admin') {
                    return json_encode($ret);
                }
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $this->getRequestValue('table'));
                $key = preg_replace('/[^a-zA-Z0-9_]/', '', $this->getRequestValue('key'));
                $target_id = intval($this->getRequestValue('target_id'));
                $value = $this->getRequestValue('value');
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `name`=? WHERE ' . $key . '=?';
                //echo $query;
                $stmt = $DBC->query($query, array($value, $target_id));
                if ($stmt) {
                    $ret['status'] = 1;
                    $ret['text'] = $value;
                }
                return json_encode($ret);
                break;
            }

            case 'fast_preview' :
            {
                $allow_fast_preview = false;
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                $permission = new Permission();

                if ($this->getConfigValue('check_permissions') and $permission->get_access($_SESSION['user_id_value'], 'data', 'access')) {
                    if ($this->getConfigValue('data_adv_share_access') and $this->check_access('data', $_SESSION['user_id_value'], 'edit', 'id', intval($this->getRequestValue('id')))) {
                        $allow_fast_preview = true;
                    }
                    if (!$this->getConfigValue('data_adv_share_access')) {
                        $allow_fast_preview = true;
                    }
                }
                if ($this->ajax_user_mode == 'admin') {
                    $allow_fast_preview = true;
                }

                if ($allow_fast_preview) {
                    $fields = array();
                    if ('' !== trim($this->getConfigValue('apps.realty.admin_fast_view'))) {
                        $matches = array();
                        preg_match_all('/([^,\s]+)/i', trim($this->getConfigValue('apps.realty.admin_fast_view')), $matches);
                        if (!empty($matches[1])) {
                            $fields = $matches[1];
                        }
                    }
                    $id = intval($this->getRequestValue('id'));
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                    $data_model = new Data_Model();
                    $form_data_shared = $data_model->get_kvartira_model(false, true);

                    if (!empty($fields)) {
                        foreach ($form_data_shared['data'] as $item => $v) {
                            if (!in_array($item, $fields)) {
                                unset($form_data_shared['data'][$item]);
                            }
                        }
                    }


                    $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
                    $form_data_shared = $data_model->init_language_values($form_data_shared);
                    $form_data_shared = $data_model->applyGCompose($form_data_shared);

                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
                    $table_view = new Table_View();
                    $order_table = '';
                    $order_table .= '<table class="table">';
                    $order_table .= $table_view->compile_view($form_data_shared, true);
                    $order_table .= '</table>';

                    $notes = array();
                    $DBC = DBC::getInstance();
                    $query = 'SELECT dn.*, u.fio FROM ' . DB_PREFIX . '_data_note dn LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE dn.id=? ORDER BY dn.added_at ASC';
                    $stmt = $DBC->query($query, array($id));
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $notes[] = $ar;
                        }
                    }
                    if (count($notes) > 0) {
                        $order_table .= '<h4>Заметки</h4>';
                        $order_table .= '<table class="table">';
                        foreach ($notes as $note) {
                            $order_table .= '<tr><td>';

                            $order_table .= '<b>' . $note['fio'] . ' (' . $note['added_at'] . ')</b><br>';
                            $order_table .= nl2br($note['message']);
                            $order_table .= '</td></tr>';
                        }
                        $order_table .= '</table>';
                    }


                    return $order_table;
                } else {
                    return '';
                }
                exit();
                break;
            }

            case 'fast_preview_public' :
            {
                $fields = array();
                if ('' !== trim($this->getConfigValue('apps.realty.admin_fast_view'))) {
                    $matches = array();
                    preg_match_all('/([^,\s]+)/i', trim($this->getConfigValue('apps.realty.admin_fast_view')), $matches);
                    if (!empty($matches[1])) {
                        $fields = $matches[1];
                    }
                }
                $id = intval($this->getRequestValue('id'));
                if($id == 0){
                    $responce = array(
                        'data' => '',
                        'href' => ''
                    );

                    return json_encode($responce);
                }
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                //сначала тут было включено игнорировать доступность по группам. Думаю правильнее включить проверку доступа по группам
                $form_data_shared = $data_model->get_kvartira_model(false, false);

                if (!empty($fields)) {
                    foreach ($form_data_shared['data'] as $item => $v) {
                        if (!in_array($item, $fields)) {
                            unset($form_data_shared['data'][$item]);
                        }
                    }
                }


                $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
                $form_data_shared = $data_model->init_language_values($form_data_shared);
                $form_data_shared = $data_model->applyGCompose($form_data_shared);

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
                $table_view = new Table_View();
                $order_table = '';
                $order_table .= '<table class="table">';
                $order_table .= $table_view->compile_view($form_data_shared, true);
                $order_table .= '</table>';

                $notes = array();
                $DBC = DBC::getInstance();
                $query = 'SELECT dn.*, u.fio FROM ' . DB_PREFIX . '_data_note dn LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE dn.id=? ORDER BY dn.added_at ASC';
                $stmt = $DBC->query($query, array($id));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $notes[] = $ar;
                    }
                }
                if (count($notes) > 0) {
                    $order_table .= '<h4>Заметки</h4>';
                    $order_table .= '<table class="table">';
                    foreach ($notes as $note) {
                        $order_table .= '<tr><td>';

                        $order_table .= '<b>' . $note['fio'] . ' (' . $note['added_at'] . ')</b><br>';
                        $order_table .= nl2br($note['message']);
                        $order_table .= '</td></tr>';
                    }
                    $order_table .= '</table>';
                }

                $order_table = iconv('UTF-8', 'UTF-8//IGNORE', $order_table);

                $responce = array(
                    'data' => $order_table,
                    'href' => $this->getRealtyHREF($id)
                );

                return json_encode($responce);
                exit();


                return $order_table;
                exit();
                break;
            }

            /* case 'voter' : {
              $user_identity = md5($_SERVER['HTTP_USER_AGENT'] . '_' . $_SERVER['REMOTE_ADDR']);
              $resultcode = (int) $_POST['resultcode'];
              $realty_id = (int) $_POST['realty_id'];
              if ($realty_id == 0) {
              return json_encode(array('result' => 'ERROR'));
              }
              $DBC = DBC::getInstance();
              $query = 'SELECT COUNT(*) AS _cnt FROM ' . DB_PREFIX . '_likevoter WHERE user_identity=? AND realty_id=?';

              $DBC = DBC::getInstance();
              $stmt = $DBC->query($query, array($user_identity, $realty_id));

              if ($stmt) {
              $ar = $DBC->fetch($stmt);
              if ($ar['_cnt'] > 0) {
              return json_encode(array('result' => 'ERROR'));
              } else {
              $query = 'INSERT INTO ' . DB_PREFIX . '_likevoter (user_identity, realty_id, resultcode) VALUES (?, ?, ?)';
              $stmt = $DBC->query($query, array($user_identity, $realty_id, $resultcode));

              $query = 'SELECT COUNT(*) AS _cnt FROM ' . DB_PREFIX . '_likevoter WHERE realty_id=? AND resultcode=?';
              $stmt = $DBC->query($query, array($realty_id, $resultcode));
              if ($stmt) {
              $ar = $DBC->fetch($stmt);
              return json_encode(array('result' => 'OK', 'count' => $ar['_cnt']));
              }
              }
              }
              break;
              } */

            case 'get_options' :
            {
                $elname = trim($this->getRequestValue('frommodelfield'));
                $datavalue = trim($this->getRequestValue('value'));
                $byfield = trim($this->getRequestValue('byfield'));
                $model = trim($this->getRequestValue('model'));

                $formatted = intval($this->getRequestValue('formatted'));

                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
                $ATH = new Admin_Table_Helper();
                $form_data = $ATH->load_model($model, false);
                if (!empty($form_data)) {

                    $responce = array();
                    $options = array();

                    if (isset($form_data[$model][$elname]) && ($form_data[$model][$elname]['type'] == 'select_by_query' || $form_data[$model][$elname]['type'] == 'select_by_query_multi')) {
                        $options = $this->_getOptionsData($form_data[$model][$elname]['primary_key_name'], $form_data[$model][$elname]['value_name'], $form_data[$model][$elname]['primary_key_table'], $byfield, $datavalue, $form_data[$model][$elname]['parameters']);
                    } else {
                        return '';
                    }


                    if (!empty($options)) {
                        if ($formatted == 1) {
                            $str = array();
                            if (isset($form_data[$model][$elname]['parameters']['mode']) && $form_data[$model][$elname]['parameters']['mode'] == 'checkbox') {
                                foreach ($options as $r) {
                                    $str[] = '<div class="multiselect_set_item"' . ($form_data[$model][$elname]['parameters']['data_field'] > '' ? ' data-' . $form_data[$model][$elname]['parameters']['data_field'] . '="' . $r['id'] . '"' : '') . '><label><input type="checkbox" name="' . $elname . '[]" value="' . $r['id'] . '"><span>' . $r['name'] . '</span></label></div>';
                                }
                            } else {
                                foreach ($options as $r) {
                                    $str[] = '<option value="' . $r['id'] . '">' . $r['name'] . '</option>';
                                }
                            }
                            $responce = array('html' => implode('', $str));
                        } else {
                            $responce = $options;
                        }
                    } else {
                        if ($formatted == 1) {
                            $responce = array('html' => '');
                        } else {
                            $responce = $options;
                        }
                    }

                    return json_encode($responce);
                }
                break;
            }

            case 'get_user_info' :
            {
                $id = (int)$this->getRequestValue('user_id');
                $DBC = DBC::getInstance();
                $query = 'SELECT u.fio, u.login, u.email, u.imgfile, u.phone, g.name AS groupname, (SELECT COUNT(id) FROM ' . DB_PREFIX . '_data WHERE user_id=?) AS data_count FROM ' . DB_PREFIX . '_user u LEFT JOIN ' . DB_PREFIX . '_group g USING(group_id) WHERE u.user_id=? LIMIT 1';
                $stmt = $DBC->query($query, array($id, $id));
                $user = array();
                if ($stmt) {
                    $user = $DBC->fetch($stmt);
                }

                $ret = '<div class="user_info">';
                $ret .= '<div class="user_info_media">';
                $ret .= '<img class="img-polaroid" src="' . ($user['imgfile'] != '' ? SITEBILL_MAIN_URL . '/img/data/user/' . $user['imgfile'] : SITEBILL_MAIN_URL . '/img/user_nophoto.png') . '" />';
                $ret .= '</div>';
                $ret .= '<div class="user_info_data">';
                $ret .= '<address>';


                if ($user['fio'] != '') {
                    $ret .= '<span class="user_info_data_title">' . $user['fio'] . '</span>';

                    $ret .= '<span>' . $user['login'] . '</span>';
                } else {
                    $ret .= '<span class="user_info_data_title">' . $user['login'] . '</span>';
                }
                $ret .= '<br /><span>Advs: ' . $user['data_count'] . '</span>';
                if ($user['groupname'] != '') {
                    $ret .= '<div class="user_info_data_in">';
                    $ret .= '<i class="icon-user"></i> ' . $user['groupname'];
                    $ret .= '</div>';
                }
                if ($user['phone'] != '') {
                    $ret .= '<div class="user_info_data_in">';
                    $ret .= '<i class="icon-headphones"></i> ' . $user['phone'];
                    $ret .= '</div>';
                }
                if ($user['email'] != '') {
                    $ret .= '<div class="user_info_data_in">';
                    $ret .= '<i class="icon-envelope"></i> ' . $user['email'];
                    $ret .= '</div>';
                }
                $ret .= '</address>';
                $ret .= '</div>';
                $ret .= '</div>';
                echo $ret;
                exit();
                break;
            }

            case 'add_note' :
            {
                return $this->_add_noteAjaxAction();
                break;
            }

            case 'delete_note' :
            {
                return $this->_delete_noteAjaxAction();
                break;
            }

            case 'save_topic_sort' :
            {
                return $this->_save_topic_sortAjaxAction();
                break;
            }
            case 'save_rubric_sort' :
            {
                return $this->_save_rubric_sortAjaxAction();
                break;
            }
            case 'set_realty_status' :
            {
                return $this->_set_realty_statusAjaxAction();
                break;
            }
            case 'topic_source' :
            {
                //echo 1;
                return $this->_topic_sourceAjaxAction();
                break;
            }

            case 'topic_delete' :
            {
                return $this->_topic_deleteAjaxAction();
                break;
            }

            case 'topic_publish' :
            {
                return $this->_topic_publishAjaxAction();
                break;
            }

            case 'get_grid_data' :
            {
                $params['page'] = $this->getRequestValue('page');
                $params['asc'] = $this->getRequestValue('asc');
                $params['order'] = $this->getRequestValue('order');
                //print_r($params);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/mobile/grid/local_grid_constructor.php';
                $grid_constructor = new Local_Grid_Constructor();
                return $grid_constructor->main($params);
                break;
            }
            case 'admin_data_getter' :
            {

                global $smarty;
                $params = $this->getRequestValue('params');
                $USER_ID = $this->this_user;
                $params['_collect_user_info'] = 1;


                if (isset($params['topic_id']) && !is_array($params['topic_id'])) {
                    $params['topic_id'] = (array)$params['topic_id'];
                }


                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/GridConstructorFactory.php';
                $grid_constructor = GridConstructorFactory::create();


                $res = $grid_constructor->get_sitebill_adv_ext_base_ajax($params);


                $smarty->assign('items_in_memory', $items_in_memory);

                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template/data/datagrid_grid.tpl';
                $smarty->assign('grid_items', $res['data']);
                $grid = $smarty->fetch($tpl);

                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template/data/datagrid_pager.tpl';
                $smarty->assign('pager_array', $res['paging']);
                //print_r($res['paging']);
                $pager = $smarty->fetch($tpl);

                return json_encode(array('grid' => $grid, 'pager' => $pager, '_total_records' => $res['_total_records'], 'order' => $res['order']));
            }
            case 'collect_data' :
            {
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/data_collector.php')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/data_collector.php';
                    $DC = new Data_Collector();
                    return $DC->collect_data();
                }
                return null;
                break;
            }
            case 'get_form_element' :
                $element_name = $this->getRequestValue('element');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
                $DM = new Data_Manager();
                $body = $DM->get_element($element_name);

                return $body;
                break;
            case 'go_up' :
                $body = '';
                $id = (int)$this->getRequestValue('id');
                $date = date('Y-m-d H:i:s', time());
                $answer = date('d.m', time());
                $DBC = DBC::getInstance();
                if ($this->ajax_user_mode == 'admin') {
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET active=1, date_added=? WHERE id=?';
                    $stmt = $DBC->query($query, array($date, $id));
                } elseif ($this->ajax_user_mode == 'user') {
                    $access_allow = false;
                    if ($this->getConfigValue('check_permissions') && (1 != (int)$this->getConfigValue('data_adv_share_access'))) {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                        $permission = new Permission();
                        if ($permission->get_access($_SESSION['user_id_value'], 'data', 'access')) {
                            $access_allow = true;
                        }
                    }

                    if ($access_allow) {
                        $query = 'UPDATE ' . DB_PREFIX . '_data SET active=1, date_added=? WHERE id=?';
                        $stmt = $DBC->query($query, array($date, $id));
                    } else {
                        $query = 'UPDATE ' . DB_PREFIX . '_data SET active=1, date_added=? WHERE id=? AND user_id=?';
                        $stmt = $DBC->query($query, array($date, $id, $ajax_controller_user_id));
                    }
                } else {
                    $body = '';
                }


                if ($stmt) {
                    $body = $answer;
                }

                break;
            case 'get_form_fields_rules' :
            {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/data/data_manager.php')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/data/data_manager.php');
                    $DM = new Data_Manager_Local();
                } else {
                    $DM = new Data_Manager();
                }
                $form_data = $DM->data_model;
                $table = $DM->table_name;
                $r = array();
                if (!empty($form_data[$table])) {

                    foreach ($form_data[$table] as $k => $v) {

                        if (isset($v['active_in_topic']) && $v['active_in_topic'] != 0) {
                            //$topics=explode(',', $v['active_in_topic']);
                            $active_array_ids = explode(',', $v['active_in_topic']);
                            $r[$k]['topic_id'] = $active_array_ids;
                        } else {
                            $r[$k]['topic_id'][] = 'all';
                        }
                        if (isset($v['parameters']['active_in_optype']) && $v['parameters']['active_in_optype'] != '') {
                            $active_array_ids = explode(',', $v['parameters']['active_in_optype']);
                            $r[$k]['optype'] = $active_array_ids;
                        } else {
                            $r[$k]['optype'][] = 'all';
                        }
                    }
                }
                $r['_meta_model_info']['model_name'] = 'data';
                return json_encode($r);
                break;
            }

            case 'get_form_fields_rules_by_model' :
            {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $SM = new Structure_Manager();
                $category_structure = $SM->loadCategoryStructure();
                if ($this->getRequestValue('model') == 'client') {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php');
                    $DM = new client_admin();
                }
                $form_data = $DM->data_model;
                $table = $DM->table_name;
                $r = array();

                if (!empty($form_data[$table])) {
                    foreach ($form_data[$table] as $k => $v) {
                        if (isset($v['active_in_topic']) && $v['active_in_topic'] != 0) {

                            $topics = explode(',', $v['active_in_topic']);

                            $active_array_ids = explode(',', $v['active_in_topic']);

                            $child_cats = array();
                            foreach ($active_array_ids as $item_id => $check_active_id) {
                                //echo '$check_active_id = '.$check_active_id.'<br>';
                                $child_cats_compare = $SM->get_all_childs($check_active_id, $category_structure);
                                if (is_array($child_cats_compare)) {
                                    $child_cats = array_merge($child_cats, $child_cats_compare);
                                }
                                $child_cats[] = $check_active_id;
                            }

                            $r[$k] = $child_cats;
                        } else {
                            $r[$k][] = 'all';
                        }
                    }
                }
                //return print_r($r,true);
                return json_encode($r);
                return print_r($form_data, true);
                break;
            }

            case 'avatar' :
            {
                $what = $this->getRequestValue('what');
                $table = $this->getRequestValue('table_name');
                $id = (int)$this->getRequestValue('id');
                $id_key = $this->getRequestValue('key');
                $field_name = $this->getRequestValue('field_name');

                $DBC = DBC::getInstance();
                $query = 'SELECT `' . $field_name . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $id_key . '`=?';
                //echo $query;
                $stmt = $DBC->query($query, array($id));
                //var_dump($stmt);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $ar[$field_name]);
                    $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $field_name . '`=? WHERE `' . $id_key . '`=?';
                    $stmt = $DBC->query($query, array('', $id));
                    $body = 'ok';
                }
                return $body;
                break;
            }

            case 'delete_image' :
            {
                $table = $this->getRequestValue('table_name');
                $image_id = (int)$this->getRequestValue('image_id');
                $data_id = (int)$this->getRequestValue('data_id');
                $key = $this->getRequestValue('key');
                $body = 'error';
                if ($table == '' || $image_id == 0 || $data_id == 0) {

                } else {
                    if ($_SESSION['user_id'] === 'true' || $this->ajax_user_mode = 'admin') {
                        $this->deleteImage($table, $image_id);
                        $body = 'ok';
                    } elseif ((int)$_SESSION['user_id'] > 0) {
                        $DBC = DBC::getInstance();
                        if ($table == 'booking_apartment') {
                            $query = 'SELECT user_id FROM ' . DB_PREFIX . '_booking_hotel WHERE hotel_id=(SELECT hotel_id
									FROM ' . DB_PREFIX . '_' . $table . '
									WHERE `' . $key . '`=(
											SELECT ' . $key . '
											FROM ' . DB_PREFIX . '_' . $table . '_image
											WHERE image_id=? AND `' . $key . '`=?' . '
											))';
                            $stmt = $DBC->query($query, array($image_id, $data_id));
                        } else {
                            $query = 'SELECT user_id FROM ' . DB_PREFIX . '_' . $table . ' WHERE ' . $key . '=(SELECT `' . $key . '` FROM ' . DB_PREFIX . '_' . $table . '_image WHERE image_id=? AND `' . $key . '`=?)';
                            $stmt = $DBC->query($query, array($image_id, $data_id));
                        }
                        //echo $query;

                        if ($stmt) {
                            $ar = $DBC->fetch($stmt);
                            if ((int)$ar['user_id'] == (int)$_SESSION['user_id']) {
                                $this->deleteImage($table, $image_id);
                                $body = 'ok';
                            }
                        }
                    }
                }
                return $body;
                break;
            }
            case 'make_main_image' :
            {
                $table = $this->getRequestValue('table_name');
                $image_id = (int)$this->getRequestValue('image_id');
                $key = $this->getRequestValue('key');
                $key_value = (int)$this->getRequestValue('key_value');
                $this->makeImageMain($table, $image_id, $key, $key_value);
                break;
            }
            case 'rotate_image' :
            {
                $table = $this->getRequestValue('table_name');
                $image_id = (int)$this->getRequestValue('image_id');
                $key = $this->getRequestValue('key');
                $key_value = (int)$this->getRequestValue('key_value');
                $rot_dir = $this->getRequestValue('rot_dir');
                if ($rot_dir != 'ccw' && $rot_dir != 'cw') {
                    $rot_dir = 'cw';
                }

                $this->rotateImage($table, $image_id, $key, $key_value, $rot_dir);
                break;
            }
            case 'dz_imagework' :
            {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/ajax/dz_imagework.php');
                $dz_imagework = new dz_imagework();
                return $dz_imagework->work();
                break;
            }
            case 'reorder_image' :
            {
                $table = $this->getRequestValue('table_name');
                $image_id = (int)$this->getRequestValue('image_id');
                $key = $this->getRequestValue('key');
                $key_value = (int)$this->getRequestValue('key_value');
                $reorder = $this->getRequestValue('reorder');
                if ($reorder == 'up') {
                    $this->reorderImage($table, $image_id, $key, $key_value, 'up');
                } elseif ($reorder == 'down') {
                    $this->reorderImage($table, $image_id, $key, $key_value, 'down');
                }
                break;
            }
            case 'change_image_title' :
            {
                $title = $this->getRequestValue('title');
                $image_id = (int)$this->getRequestValue('image_id');
                $title = trim($title);
                $title = SiteBill::iconv('utf-8', SITE_ENCODING, $title);
                if ($image_id != 0) {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_image SET title=? WHERE image_id=?';
                    $DBC->query($query, array($title, $image_id));
                }
                return '';
            }

            case 'change_image_description' :
            {
                $description = $this->getRequestValue('description');
                $image_id = (int)$this->getRequestValue('image_id');
                $description = trim($description);
                $description = SiteBill::iconv('utf-8', SITE_ENCODING, $description);
                if ($image_id != 0) {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_image SET description=? WHERE image_id=?';
                    $DBC->query($query, array($description, $image_id));
                }
                return '';
            }

            case 'show_contact':
                $body = '';
                $id = (int)$this->getRequestValue('id');
                if ($id != 0 && $this->ajax_user_mode == 'admin') {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET show_contact=1 WHERE id=?';
                    $stmt = $DBC->query($query, array($id));
                    if ($stmt) {
                        $body = 'OK';
                    }
                }
                break;

            case 'get_districts_by_city_id':

                $body = '';
                $id = (int)$this->getRequestValue('loginreg-city_id');
                if ($id != 0) {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=?';
                    $stmt = $DBC->query($query, array($id));

                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret[] = array('district_id' => $ar['id'], 'name' => SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
                        }

                        return json_encode($ret);
                    }
                }
                break;
            case 'add_to_agentphones' :
            {
                $phone = preg_replace('/\D/', '', $this->getRequestValue('phone'));
                $DBC = DBC::getInstance();
                $query = 'SELECT COUNT(*) AS added_yet FROM ' . DB_PREFIX . '_agentphones WHERE phone=?';
                $stmt = $DBC->query($query, array($phone));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if (0 == $ar['added_yet']) {
                        $query = 'INSERT INTO ' . DB_PREFIX . '_agentphones (phone) VALUES (?)';
                        $stmt = $DBC->query($query, array($phone));
                    }
                }
                break;
            }

            case 'get_search_form':
                global $smarty;
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/search/kvartira_search.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $Structure_Manager = new Structure_Manager();

                $this->template->assert('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id')));

                $kvartira_search_form = new Kvartira_Search_Form();
                $kvartira_search_form->main();
                $form_code = $smarty->fetch('search_form.tpl');
                $ra = array();
                $ra['response']['body'] = htmlentities($form_code, ENT_QUOTES, SITE_ENCODING);
                return json_encode($ra);
                break;
            case 'hide_contact':
                $body = '';
                $id = (int)$this->getRequestValue('id');
                if ($id != 0 && $this->ajax_user_mode == 'admin') {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET show_contact=0 WHERE id=?';
                    $stmt = $DBC->query($query, array($id));
                    if ($stmt) {
                        $body = 'OK';
                    }
                }
                break;
            case 'add_to_favorites':
                $id = (int)$this->getRequestValue('id');
                $user_id = $this->getSessionUserId();

                if ($user_id != 0) {


                    if ($id != 0) {

                        $DBC = DBC::getInstance();
                        $query = 'INSERT INTO ' . DB_PREFIX . '_userlists (user_id, id, lcode) VALUES (?, ?, ?)';
                        $stmt = $DBC->query($query, array($user_id, $id, 'fav'));

                        if (isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites'] != '') {
                            $cc = unserialize($_COOKIE['user_favorites']);
                        } else {
                            $cc = array();
                        }

                        if (!isset($cc[$user_id][$id])) {
                            $cc[$user_id][$id] = $id;
                            $body = 'OK';
                        } else {
                            $body = '';
                        }
                        setcookie("user_favorites", serialize($cc), time() + 7 * 24 * 3600, '/', self::$_cookiedomain);
                        $_SESSION['favorites'] = $cc[$user_id];
                    }
                    //echo 1;
                    //$body = 'OK';
                } else {

                    if ($id != 0) {
                        if (!isset($_SESSION['favorites'][$id])) {
                            $_SESSION['favorites'][$id] = $id;
                            $body = 'OK';
                        } else {
                            $body = '';
                        }
                    }
                }

                //$body = 'OK';
                /* if($id!=0){
                  if(!isset($_SESSION['favorites'][$id])){
                  $_SESSION['favorites'][$id] = $id;
                  $body = 'OK';
                  }else{
                  $body = '';
                  }
                  } */
                break;
            case 'remove_from_favorites':
                $id = (int)$this->getRequestValue('id');
                $user_id = (int)$this->getSessionUserId();
                if ($user_id != 0) {

                    if (isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites'] != '') {
                        $cc = unserialize($_COOKIE['user_favorites']);
                    } else {
                        $cc = array();
                    }

                    $DBC = DBC::getInstance();
                    $query = 'DELETE FROM ' . DB_PREFIX . '_userlists WHERE user_id=? AND id=? AND lcode=?';
                    $stmt = $DBC->query($query, array($user_id, $id, 'fav'));

                    if ($id != 0 && isset($cc[$user_id][$id])) {

                        unset($cc[$user_id][$id]);
                        $body = 'OK';
                    } else {
                        $body = '';
                    }
                    setcookie("user_favorites", serialize($cc), time() + 7 * 24 * 3600, '/', self::$_cookiedomain);
                    $_SESSION['favorites'] = $cc[$user_id];
                } else {
                    if ($id != 0) {
                        if (isset($_SESSION['favorites'][$id])) {
                            unset($_SESSION['favorites'][$id]);
                            $body = 'OK';
                        } else {
                            $body = '';
                        }
                    }
                }

                break;
            case 'clear_favorites':
                $user_id = (int)$this->getSessionUserId();
                if ($user_id != 0) {
                    setcookie("user_favorites", '', time() - 1000, '/', self::$_cookiedomain);
                    unset($_SESSION['favorites']);

                    $DBC = DBC::getInstance();
                    $query = 'DELETE FROM ' . DB_PREFIX . '_userlists WHERE user_id=? AND lcode=?';
                    $stmt = $DBC->query($query, array($user_id, 'fav'));
                } else {
                    unset($_SESSION['favorites']);
                }
                $body = 'OK';
                break;
            case 'get_specialoffers':
                global $smarty;

                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/GridConstructorFactory.php';
                if ($this->getConfigValue('theme') == 'kupikuban') {
                    require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/grid_constructor_local.php';
                    $GC = new Grid_Constructor_Local();
                    $adv = $GC->vip_array(array('vip' => '1'));
                } else {
                    $GC = GridConstructorFactory::create();
                    $adv = $GC->get_sitebill_adv_ext(array('hot' => '1'));
                }
                if ($GC->get_grid_total_records() > 0) {
                    $this->template->assert('grid_items', $adv);
                    $rs = $smarty->fetch('realty_grid.tpl');
                } else {
                    $rs = '<h2>' . Multilanguage::_('L_NO_HOT') . '</h2>';
                }

                $ra['response']['body'] = htmlentities($rs, ENT_QUOTES, SITE_ENCODING);
                return json_encode($ra);

                break;
            case 'get_recomendation':
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/grid/grid_constructor.php')) {
                    global $smarty;
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
                    require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/grid/grid_constructor.php';
                    $GC = new Grid_Constructor_Local();
                    $adv = $GC->get_sitebill_adv_ext(array('recomendation' => '1'));

                    if ($GC->get_grid_total_records() > 0) {
                        $this->template->assert('grid_items', $adv);
                        $rs = $smarty->fetch('realty_grid.tpl');
                    } else {
                        $rs = '<h2>' . Multilanguage::_('L_NO_RECOMENDATION') . '</h2>';
                    }

                    $ra['response']['body'] = htmlentities($rs, ENT_QUOTES, SITE_ENCODING);
                    return json_encode($ra);
                }
                break;
            case 'get_station_list':
            {
                $metro = array();
                $DBC = DBC::getInstance();
                $query = 'SELECT metro_id, LOWER(name) AS name FROM ' . DB_PREFIX . '_metro';
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $metro[] = array('id' => $ar['metro_id'], 'name' => SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
                    }
                }
                return json_encode($metro);
                break;
            }


            case 'get_my_favorites':
                global $smarty;


                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                $GC = $this->_getGridConstructor();
                //require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
                //$GC=new Grid_Constructor();
                if (count($_SESSION['favorites']) == 0) {
                    $rs = '<h2>' . Multilanguage::_('L_NO_FAVORITES') . '</h2>';
                } else {
                    $adv = $GC->get_sitebill_adv_ext(array('favorites' => $_SESSION['favorites']));

                    $this->template->assert('grid_items', $adv);
                    //$smarty->assign('grid_items', $adv);
                    $rs = $smarty->fetch('realty_grid.tpl');
                }

                $ra['response']['body'] = htmlentities($rs, ENT_QUOTES, SITE_ENCODING);
                return json_encode($ra);

                //$body=$rs;

                break;
            /* case 'remove_from_favorites':
              if((int)$this->getRequestValue('id')!=0){
              if(isset($_SESSION['favorites'][(int)$this->getRequestValue('id')])){
              unset($_SESSION['favorites'][(int)$this->getRequestValue('id')]);
              }
              }
              $body = 'OK';
              break; */
            case 'add_my_city':
                if ($this->getRequestValue('city_id') == '') {
                    unset($_SESSION['city_id']);
                } else {
                    $_SESSION['city_id'] = $this->getRequestValue('city_id');
                }
                $body = 'OK';
                break;
            case 'get_city_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['city_id']);
                if ($form_generator->get_total_in_select('city_id') == 0) {
                    $body = '<div id="city_id_div"></div>';
                }
                break;

            case 'get_region_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['region_id']);
                if ($form_generator->get_total_in_select('region_id') == 0) {
                    $body = '<div id="region_id_div"></div>';
                }
                break;

            case 'get_metro_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['metro_id']);
                if ($form_generator->get_total_in_select('metro_id') == 0) {
                    $body = '<div id="metro_id_div"></div>';
                }
                break;

            case 'get_district_id':
                if ('yes' == $this->getRequestValue('multiple_mode')) {
                    $body = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['district_id']);
                } else {
                    $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['district_id']);
                }

                if ($form_generator->get_total_in_select('district_id') == 0) {
                    $body = '<div id="district_id_div"></div>';
                }
                break;

            case 'get_street_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['street_id']);
                if ($form_generator->get_total_in_select('street_id') == 0) {
                    $body = '<div id="street_id_div"></div>';
                }
                break;


            case 'get_mark_list':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_mark_select_box($this->getRequestValue('parent_id'), 0, $current_mark_id);
                break;

            case 'get_coachwork_list':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_coachwork_select_box($this->getRequestValue('parent_id'), 0, $current_mark_id);
                break;

            case 'get_model_list':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_model_select_box($this->getRequestValue('mark_id'), $current_model_id);
                break;

            case 'get_modification_list':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_modification_select_box($this->getRequestValue('model_id'), $current_modification_id);
                break;

            case 'delete_user':
                if ($_SESSION['group'] == 'nanoadmin') {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/users_manager.php');
                    $user_manager = new Users_Manager();
                    $user_manager->delete_user($this->getRequestValue('user_id'));
                }
                break;


            case 'register_complete':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/users_manager.php');
                $user_manager = new Users_Manager();

                $params['phone'] = $this->getRequestValue('phone');
                $params['mobile'] = $this->getRequestValue('mobile');
                $params['icq'] = $this->getRequestValue('icq');
                $params['site'] = $this->getRequestValue('site');
                $user_manager->add_ajax_user($this->getRequestValue('user_id'), $this->getRequestValue('fio'), $this->getRequestValue('email'), $params);
                break;

            case 'restorepassword':
            {

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/user.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/remind.php');
                $remind = new Remind;
                echo $remind->ajax();
                exit();
            }
            case 'ajax_login':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
                $Login = new Login();
                /* $userlogin=SiteBill::iconv('utf-8', SITE_ENCODING, $_GET['login']);
                  $userpassword=SiteBill::iconv('utf-8', SITE_ENCODING, $_GET['password']); */

                if ($this->getConfigValue('email_as_login')) {
                    $userlogin = $this->getRequestValue('login');
                } else {
                    $userlogin = preg_replace('/([^a-zA-Z-_0-9\.@])/', '', $this->getRequestValue('login'));
                }


                $userpassword = trim($this->getRequestValue('password'));
                $rememberme = (int)$this->getRequestValue('rememberme');

                if (TRUE === $Login->checkLogin($userlogin, $userpassword, $rememberme)) {
                    $body = 'Authorized';
                    if ($this->getConfigValue('apps.accountsms.enable')) {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/accountsms/admin/admin.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/accountsms/site/site.php');
                        $Accountsms_Site = new accountsms_site();
                        $_SESSION['viewOptions'] = $Accountsms_Site->getViewOptions($this->getSessionUserId());
                    }
                } else {
                    $body = 'error';
                }
                break;

            case 'ajax_register':

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php');
                    $Register = new Local_Register_Using_Model();
                } else {
                    $Register = new Register_Using_Model();
                }

                $this->setRequestValue('do', 'new_done');
                $rs1 = $Register->ajaxRegister();
                return $rs1;
                break;
            case 'ajax_activate_sms':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php');
                    $Register = new Local_Register_Using_Model();
                } else {
                    $Register = new Register_Using_Model();
                }

                //$this->setRequestValue('do', 'new_done');
                $rs1 = $Register->ajax_activate_sms();
                return $rs1;
                break;
            case 'login':
                $_SESSION['user_id'] = $this->getRequestValue('user_id');
                $_SESSION['group'] = $this->getRequestValue('group');
                $_SESSION['session_key'] = $this->getRequestValue('session_key');
                $_SESSION['key'] = $this->getRequestValue('session_key');
                $user_ip = $_SERVER['REMOTE_ADDR'];
                $DBC = DBC::getInstance();
                $query = 'INSERT INTO ' . DB_PREFIX . '_session (user_id, ip, session_key, start_date) VALUES (?, ?, ?, NOW())';
                $stmt = $DBC->query($query, array($_SESSION['user_id'], $user_ip, $_SESSION['key']));
                break;

            case 'get_cart_count':
                $items_count = 0;
                $summ = 0;
                $positions_count = count($_SESSION['product_list']);
                if ($positions_count != 0) {
                    foreach ($_SESSION['product_list'] as $v) {
                        $items_count += $v['count'];
                        $summ += $v['sum'];
                    }
                }
                if (IS_NUKUPI == 1) {
                    $body = 'У вас в <a href="' . SITEBILL_MAIN_URL . '/cart/">Корзине</a> <br /><strong>' . $items_count . ' покупок</strong> <br />на <strong>' . $summ . ' руб.</strong>';
                } else {
                    $body = 'Корзина (' . $items_count . ')';
                }
                break;

            case 'check_address':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/ajax/check_address/check_address.php');
                $check_address = new Check_Address_Ajax();
                $body = $check_address->check($this->getRequestValue('address'));
                break;

            case 'add_to_cart':
                $product_data = $this->load_product_data($this->getRequestValue('product_id'));
                if ($product_data) {
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_name'] = $product_data['product_name'];
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_price'] = $product_data['product_price'];
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_id'] = $product_data['product_id'];

                    $product_count = $_SESSION['product_list'][$this->getRequestValue('product_id')]['count'];
                    $product_count++;
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['count'] = $product_count;

                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['sum'] = $product_data['product_price'] * $product_count;

                    $body = 'add ' . $this->getRequestValue('product_id');
                } else {
                    $body = 'Товар не найден';
                }
                break;

            case 'delete_from_cart':
                $product_data = $this->load_product_data($this->getRequestValue('product_id'));
                unset($_SESSION['product_list'][$this->getRequestValue('product_id')]);

                break;

            case 'update_quantity':
                $new_qty = $this->getRequestValue('quantity');
                $product_id = $this->getRequestValue('product_id');
                if ($new_qty > 0) {
                    $_SESSION['product_list'][$product_id]['count'] = $new_qty;
                    $_SESSION['product_list'][$product_id]['sum'] = $_SESSION['product_list'][$product_id]['product_price'] * $_SESSION['product_list'][$product_id]['count'];
                } else {
                    unset($_SESSION['product_list'][$product_id]);
                }

                break;

            case 'delete_uploadify_image':
                $img_name = $this->getRequestValue('img_name');
                $this->delete_uploadify_image($img_name);
                $body = 'OK';
                break;

            case 'autocomplete':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/realtyautocomplete/lib/realty_autocomplete.php');
                $realty_autocomplete = new realty_autocomplete();
                $q = $_GET["term"];
                if (!$q)
                    return;

                $result = $realty_autocomplete->generate_array($q);
                echo $this->array_to_json($result);
                exit;
                break;
            case 'get_districts' :
            {
                $districts = array();
                $city_id = $this->getRequestValue('city_id');
                $DBC = DBC::getInstance();
                $stmt = $DBC->query('SELECT id, name FROM ' . DB_PREFIX . '_district WHERE city_id=?', array($city_id));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $districts[] = $ar;
                    }
                }
                return json_encode(array('districts' => $districts));
                break;
            }

            case 'dropzone_xls':
            {
                if ($this->ajax_user_mode != 'admin' and $this->ajax_user_mode != 'user') {
                    return '';
                }
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/dropzone_xls/dropzone.php');
                $dropzone = new DropZone();
                return $dropzone->ajax();
                break;
            }
            case 'gstags':
            {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/model_tags/model_tags.php');
                    $model_tags = new model_tags();
                    return $model_tags->gsajax();
                    break;
            }
            case 'get_tags':
            {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/model_tags/model_tags.php');
                $model_tags = new model_tags();
                return $model_tags->ajax();
                break;
            }

            case 'get_vip_cost':
                echo $this->getConfigValue('vip_cost');
                exit;
                break;

            case 'make_special_payment':
            {
                $this->make_special_payment();
                break;
            }
            case 'add_bill':
            {
                $resp = array(
                    'status' => 'error',
                    'data' => array()
                );
                $user_id = $this->getSessionUserId();
                $payment_value = $this->getRequestValue('payment_value');
                if ($user_id != 0 && $payment_value > 0) {
                    $query = 'INSERT INTO ' . DB_PREFIX . '_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?,0)';
                    $stmt = $DBC->query($query, array((int)$user_id, $payment_value), time(), 'Пополнение счета пользователем ID: ' . (int)$user_id);
                    if ($stmt) {
                        $bill_id = $DBC->lastInsertId();
                        $signature = md5($this->getConfigValue('robokassa_login') . ':' . $payment_value . ':' . $bill_id . ':' . $this->getConfigValue('robokassa_password1'));
                        $resp['status'] = 'ok';
                        $resp['data'] = array('id' => $bill_id, 'signature' => $signature, 'sum' => $payment_value);
                    }
                }
                return json_encode($resp);
                exit;
                break;
            }
        }


        $body = str_replace("\r\n", ' ', $body);
        $body = str_replace("\n", ' ', $body);
        $body = addslashes($body);


        $rs = '
{
   	"response":{
        "to":"Tove",
        "from":"Jani",
        "body":"' . $body . '"
    }
}
        ';

        if ($_REQUEST['callback'] != '') {
            $rs = $_REQUEST['callback'] . '(' . $rs . ')';
        }

        return $rs;
    }

}