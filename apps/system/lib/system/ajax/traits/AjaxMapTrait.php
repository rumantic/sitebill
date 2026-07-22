<?php
/**
 * AjaxMapTrait — extracted from Ajax_Server (ajax_server.php)
 */
trait AjaxMapTrait
{
    protected function _iframe_mapAjaxAction()
    {

        $ref = $_SERVER['HTTP_REFERER'];
        $u = parse_url($_SERVER['HTTP_REFERER']);
        $host = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);
        if ($u['host'] != $host) {
            //	return'';
        }

        //$path=$this->getClearRequestURI($u['path']);

        $url = urldecode($u['path']);
        $url = str_replace('\\', '/', $url);
        if (preg_match('/(\/(\/+))/', $url)) {
            return $url;
        }
        $path = parse_url($url, PHP_URL_PATH);

        if ($path == false) {
            $path = urldecode($u['path']);
        }
        /* if('/'===$path){
          return '';
          } */
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }
        if (substr($path, -1, 1) === '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (SITEBILL_MAIN_URL != '') {
            $path = trim(preg_replace('/^' . trim(SITEBILL_MAIN_URL, '/') . '/', '', $path), '/');
        }

        //$this->writeLog(__METHOD__.$path);

        $catched = false;
        $params = array();

        //Передаем параметры из REQUEST
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
        $sitebill_krascap = new SiteBill_Krascap();
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/main.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/main.php');
            $frontend_main = new frontend_main();
            $params = $frontend_main->gatherRequestParams();
        } else {
            $params = $sitebill_krascap->gatherRequestParams();
        }
        if ($this->request()->get('map_bounds')) {
            $params['map_bounds'] = $this->request()->get('map_bounds');
        }
        if ($this->request()->get('polylineString')) {
            $params['polylineString'] = $this->request()->get('polylineString');
        }


        $search_params = $params;


        $DBC = DBC::getInstance();

        $this->template->assign('language', $this->getSessionLanguage());

        if (isset($_GET['custom_center'])) {
            $crds = preg_replace('/[^\d\.,-]/', '', $_GET['custom_center']);
            if ($crds != '') {
                $this->template->assign('custom_center', $crds);
            }
        }
        if (isset($_GET['defaultZoom'])) {
            $dz = intval($_GET['defaultZoom']);
            if ($dz > 0 && $dz < 21) {
                $this->template->assign('defaultZoom', $dz);
            }
        }


        if (1 == $this->getConfigValue('work_on_https')) {
            $this->template->assign('work_on_https', 1);
        } else {
            $this->template->assign('work_on_https', 0);
        }
        $this->template->assign('g_api_key', trim($this->getConfigValue('google_api_key')));
        $this->template->assign('y_api_key', trim($this->getConfigValue('yandex_map_key')));
        $w = $this->getRequestValue('w');
        if ($w == '') {
            $w = '100%';
        }
        $h = $this->getRequestValue('h');
        if ($h == '') {
            $h = '100%';
        }
        $this->template->assign('map_w', $w);
        $this->template->assign('map_h', $h);
        $this->template->assign('scroll_zoom', $this->getConfigValue('apps.geodata.iframe_scroll_zoom'));

        /*
                if(isset($_GET['frame'])){
                    $frame = $_GET['frame'];
                    if(is_array($frame) && count($frame) == 3){
                        $frame[0] = preg_replace('/[^\d\.-]/', '', $frame[0]);
                        $frame[1] = preg_replace('/[^\d\.-]/', '', $frame[1]);
                        if(intval($frame[2]) < 1 || intval($frame[2]) > 20){
                            $frame[2] = '';
                        }
                        if($frame[0] != '' && $frame[1] != '' && $frame[2] != ''){
                            $this->template->assign('mapframe', implode(';', $_GET['frame']));
                        }
                    }
                }
       */

        if ('' != trim($this->getConfigValue('apps.geodata.new_map_center'))) {
            list($lat, $lng) = explode(',', $this->getConfigValue('apps.geodata.new_map_center'));
            $lat = trim($lat);
            $lng = trim($lng);
            $this->template->assign('map_center', array($lat, $lng));
        } else {
            $this->template->assign('map_center', array(55.753215, 37.622504));
        }

        if (0 != intval($this->getConfigValue('apps.geodata.map_zoom_default'))) {
            $this->template->assign('map_zoom', intval($this->getConfigValue('apps.geodata.map_zoom_default')));
        } else {
            $this->template->assign('map_zoom', 14);
        }

        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/iframe_map.tpl';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/iframe_map.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/iframe_map.tpl';
        }

        if ($this->getConfigValue('apps.geodata.map_cache_time') > 0) {
            //Попробуем получить данные карты из кэша
            $query = 'SELECT `value` FROM ' . DB_PREFIX . '_cache WHERE `parameter`=? and valid_for > ?';
            $stmt = $DBC->query($query, array('map_cache', time()));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['value'] != '') {
                    return $ar['value'];
                }
            } else {
                echo $DBC->getLastError();
            }
        }
        //echo 'test';
        /* $grid_constructor = $this->_getGridConstructor();
          if ($this->getConfigValue('apps.geodata.iframe_map_limit') > 0) {
          $params['page_limit'] = $this->getConfigValue('apps.geodata.iframe_map_limit');
          } else {
          $params['no_portions'] = 1;
          }
          $params['no_premium_filtering'] = 1;
          $res = $grid_constructor->get_sitebill_adv_core($params, false, false, false, true);
         */
        if (1 == $this->getConfigValue('use_google_map')) {
            $this->template->assign('map_type', 'google');
        } elseif (2 == $this->getConfigValue('use_google_map')) {
            $this->template->assign('map_type', 'leaflet_osm');
        } else {
            $this->template->assign('map_type', 'yandex');
        }

        /* $this->template->assign('iframe_grid_data', json_encode($res['geoobjects_collection_clustered'])); */
        $this->template->assign('iframe_grid_params', json_encode($search_params));
        $html = $this->template->fetch($tpl);
        /* if ($this->getConfigValue('apps.geodata.map_cache_time') > 0) {
          //очистим предудущий кэш
          $query = 'delete FROM ' . DB_PREFIX . '_cache WHERE `parameter`=?';
          $stmt = $DBC->query($query, array('map_cache'));
          if (!$stmt) {
          echo $DBC->getLastError();
          }
          //создадим новую запись кэша
          $query = "insert into " . DB_PREFIX . "_cache (`parameter`, `value`, `created_at`, `valid_for`) values (?, ?, ?, ?)";
          $stmt = $DBC->query($query, array('map_cache', $html, time(), time() + $this->getConfigValue('apps.geodata.map_cache_time')));
          if (!$stmt) {
          echo $DBC->getLastError();
          }
          } */
        return $html;
    }

}
