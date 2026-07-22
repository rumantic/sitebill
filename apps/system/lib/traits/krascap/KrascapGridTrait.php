<?php
/**
 * KrascapGridTrait — Grid display methods extracted from SiteBill_Krascap.
 *
 * Methods: grid_data, grid_adv, grid_adv2, grid_adv_favorites,
 *          grid_special, grid_special_right, grid_special_right_with_params
 */
trait KrascapGridTrait
{
    protected function grid_data()
    {
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
    }

    function grid_adv($params = array(), $label = '')
    {
        /* возможны вариант отдачи списка в виде эксель-таблицы */
        /* $to_excell=false;
          if(isset($params['format']) && $params['format']='excell'){
          $to_excell=true;
          unset($params['format']);
          } */

        $any_url_catched = false;

        // признак адреса по стране
        $country_url_catched = false;

        // признак адреса поиска
        $find_url_catched = false;

        // признак адреса по городу
        $city_url_catched = false;

        // признак адреса по метро
        $metro_url_catched = false;

        // признак адреса по региону
        $region_url_catched = false;

        // признак адреса по району
        $district_url_catched = false;

        // признак адреса по линкменеджеру
        $predefined_url_catched = false;

        // признак адреса по роуту
        $route_catched = false;

        // признак адреса по ЖК
        $complex_url_catched = false;

        // признак адреса по пользователю
        $user_url_catched = false;
        $system_route_catched = false;

        // признак адреса поиска
        $REQUESTURIPATH = Sitebill::getClearRequestURI();

        $grid_constructor = $this->_getGridConstructor($label);

        $DBC = DBC::getInstance();

        if (!$any_url_catched && $REQUESTURIPATH == 'find') {
            $find_url_catched = true;
            $any_url_catched = true;
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/router/router.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/router/router.php';
                $Router = new Router();
                if ($Router->checkUrl($REQUESTURIPATH)) {
                    $route_catched = true;
                    $any_url_catched = true;
                    $work_params = $Router->getWorkParams();
                    foreach ($work_params['params'] as $k => $v) {
                        $this->setRequestValue($k, $v);
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (preg_match('/^user(\d+)\.html/', $REQUESTURIPATH, $matches)) {

                $user_id = $matches[1];
                $query = 'SELECT * FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1';
                $stmt = $DBC->query($query, array($user_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    if ((int)$ar['user_id'] != 0) {
                        $user_url_catched = true;
                        $user_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/predefinedlinks/admin/admin.php') && 1 == $this->getConfigValue('apps.predefinedlinks.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/predefinedlinks/admin/admin.php';
                $PDLA = new predefinedlinks_admin();
                if ($predefined_info = $PDLA->checkAlias($REQUESTURIPATH)) {
                    $predefined_url_catched = true;
                    $any_url_catched = true;
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_country_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_country WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    if ((int)$ar['country_id'] != 0) {
                        $country_url_catched = true;
                        $country_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_region_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_region WHERE alias=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['region_id'] != 0) {
                        $region_url_catched = true;
                        $region_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '' && str_contains($REQUESTURIPATH, '/')) {
            $country_and_city = explode('/', $REQUESTURIPATH);
            if ( is_array($country_and_city) && count($country_and_city) == 2 ) {
                $query = 'SELECT c.* FROM ' . DB_PREFIX . '_city c, ' . DB_PREFIX . '_country co  WHERE co.url=? and c.url=? LIMIT 1';
                $stmt = $DBC->query($query, array($country_and_city[0], $country_and_city[1]));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['city_id'] != 0) {
                        $city_url_catched = true;
                        $city_info = $ar;
                        $this->template->assign('city_info', $city_info);
                        $any_url_catched = true;
                    }
                }
            }

        }


        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_city_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_city WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['city_id'] != 0) {
                        $city_url_catched = true;
                        $city_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_metro_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_metro WHERE `alias`=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['metro_id'] != 0) {
                        $metro_url_catched = true;
                        $metro_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if (intval($this->getConfigValue('apps.seo.no_district_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_district WHERE `url`=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['id'] != 0) {
                        $district_url_catched = true;
                        $district_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }

        if (!$any_url_catched && $REQUESTURIPATH != '') {
            if ($this->getConfigValue('apps.complex.enable')) {
                $DBC = DBC::getInstance();
                $query = 'SELECT * FROM ' . DB_PREFIX . '_complex WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if (intval($ar['complex_id']) !== 0) {
                        $complex_url_catched = true;
                        $complex_info = $ar;
                        $any_url_catched = true;
                    }
                }
            }
        }


        $gorod_name = false;

        if ($find_url_catched) {
            if (Multilanguage::is_set('LT_FIND_URL_TITLE', '_template')) {
                $title = Multilanguage::_('LT_FIND_URL_TITLE', '_template');
            } else {
                $title = Multilanguage::_('FIND_URL_TITLE', 'system');
            }

            $this->template->assert('title', $title);
            $this->template->assert('meta_title', $title);
            $this->setRequestValue('find_url_catched', 1);
        }

        if ($route_catched) {
            //$work_params=$Router->getWorkParams();
            //$this->setRequestValue('router_info', $work_params);
        }

        if ($predefined_url_catched) {

            $meta = $this->extractMetaFromRawData($predefined_info);
            if ($meta['meta_title'] == '') {
                $meta['meta_title'] = $meta['title'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            foreach ($meta as $mkey => $mvalue) {
                $this->template->assert($mkey, $mvalue);
            }

            if (count($predefined_info['params']) > 0) {
                foreach ($predefined_info['params'] as $k => $v) {
                    $this->setRequestValue($k, $v);
                }
            }

            $this->setRequestValue('predefined_info', $predefined_info);
        }

        if ($country_url_catched) {

            $meta = $this->extractMetaFromRawData($country_info);
            if ($meta['meta_title'] == '') {
                $meta['meta_title'] = $meta['title'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            foreach ($meta as $mkey => $mvalue) {
                $this->template->assert($mkey, $mvalue);
            }

            if ( $this->getConfigValue('apps.seo.add_city_list_inside_country') ) {
                $seo_api = new \seo\api\seo();
                $city_list = $seo_api->get_city_list((int)$country_info['country_id']);
                $this->template->assign('city_list', $city_list);
            }
            $this->template->assign('country_info', $country_info);

            $this->setRequestValue('country_id', (int)$country_info['country_id']);
            $this->setRequestValue('country_view', $REQUESTURIPATH);
        }

        if ($district_url_catched) {

            $meta = $this->extractMetaFromRawData($district_info);
            if ($meta['meta_title'] == '') {
                $meta['meta_title'] = $meta['title'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            foreach ($meta as $mkey => $mvalue) {
                $this->template->assert($mkey, $mvalue);
            }

            $params['district_id'] = intval($district_info['id']);

            $this->setRequestValue('district_id', (int)$district_info['id']);
            $this->setRequestValue('district_view', $REQUESTURIPATH);
        }

        if ($city_url_catched) {

            $meta = array(
                'meta_title' => '',
                'title' => '',
                'description' => '',
                'meta_description' => '',
                'meta_keywords' => ''
            );

            if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
                $curlang = $this->getCurrentLang();
                $lang_postfix = '_' . $curlang;
                if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                    $lang_postfix = '';
                }
            } else {
                $lang_postfix = '';
            }

            if (isset($city_info['public_title' . $lang_postfix]) && $city_info['public_title' . $lang_postfix] != '') {
                $meta['title'] = $city_info['public_title' . $lang_postfix];
            } elseif (isset($city_info['public_title']) && $city_info['public_title'] != '') {
                $meta['title'] = $city_info['public_title'];
            } else {
                $meta['title'] = $city_info['name'];
                if ($this->getConfigValue('apps.seo.city_title_postfix') != '') {
                    $meta['title'] .= ' ' . $this->getConfigValue('apps.seo.city_title_postfix');
                }
            }
            if (isset($city_info['meta_title' . $lang_postfix]) && $city_info['meta_title' . $lang_postfix] != '') {
                $meta['meta_title'] = $city_info['meta_title' . $lang_postfix];
            } elseif ($city_info['meta_title'] != '') {
                $meta['meta_title'] = $city_info['meta_title'];
            } else {
                $meta['meta_title'] = $meta['title'];
            }

            if (isset($city_info['description' . $lang_postfix]) && $city_info['description' . $lang_postfix] != '') {
                $meta['description'] = $city_info['description' . $lang_postfix];
            } elseif ($city_info['description'] != '') {
                $meta['description'] = $city_info['description'];
            }
            if (isset($city_info['meta_description' . $lang_postfix]) && $city_info['meta_description' . $lang_postfix] != '') {
                $meta['meta_description'] = $city_info['meta_description' . $lang_postfix];
            } elseif ($city_info['meta_description'] != '') {
                $meta['meta_description'] = $city_info['meta_description'];
            }

            if (isset($city_info['meta_keywords' . $lang_postfix]) && $city_info['meta_keywords' . $lang_postfix] != '') {
                $meta['meta_keywords'] = $city_info['meta_keywords' . $lang_postfix];
            } elseif ($city_info['meta_keywords'] != '') {
                $meta['meta_keywords'] = $city_info['meta_keywords'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            foreach ($meta as $mkey => $mvalue) {
                $this->template->assert($mkey, $mvalue);
            }


            $this->setRequestValue('city_id', (int)$city_info['city_id']);
            $this->setRequestValue('city_view', $REQUESTURIPATH);
        }

        if ($metro_url_catched) {

            $meta = $this->extractMetaFromRawData($metro_info);
            if ($meta['meta_title'] == '') {
                $meta['meta_title'] = $meta['title'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            foreach ($meta as $mkey => $mvalue) {
                $this->template->assert($mkey, $mvalue);
            }

            $this->setRequestValue('metro_id', intval($metro_info['metro_id']));
            $this->setRequestValue('metro_view', $REQUESTURIPATH);
        }

        if ($region_url_catched) {

            $meta = $this->extractMetaFromRawData($region_info);
            if ($meta['meta_title'] == '') {
                $meta['meta_title'] = $meta['title'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            foreach ($meta as $mkey => $mvalue) {
                $this->template->assert($mkey, $mvalue);
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            $this->setRequestValue('region_id', intval($region_info['region_id']));
            $this->setRequestValue('region_view', $REQUESTURIPATH);
        }

        if ($complex_url_catched) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/complex/admin/admin.php');
            $complex_admin = new complex_admin();
            $data_model = new Data_Model();
            $complex_data = $complex_admin->data_model;
            $complex_data = $data_model->init_model_data_from_db('complex', 'complex_id', (int)$ar['complex_id'], $complex_data['complex'], true);
            $complex_data['image']['image_array'] = $this->get_image_array('complex', 'complex', 'complex_id', (int)$ar['complex_id']);

            $this->template->assert('complex_data', $complex_data);


            if ($complex_info['meta_title'] != '') {
                $title = $complex_info['name'];
                $meta_title = $complex_info['meta_title'];
            } else {
                $title = $meta_title = $complex_info['name'];
            }

            $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

            $this->template->assert('title', $title);
            $this->template->assert('meta_title', $meta_title);

            if ($complex_info['description'] != '') {
                $this->template->assert('description', $complex_info['description']);
            }
            if ($complex_info['meta_description'] != '') {
                $this->template->assert('meta_description', $complex_info['meta_description']);
            } else {
                //$this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
            }
            if ($complex_info['meta_keywords'] != '') {
                $this->template->assert('meta_keywords', $complex_info['meta_keywords']);
            } else {

            }


            $this->setRequestValue('complex_id', (int)$ar['complex_id']);
            $this->setRequestValue('complex_view', $REQUESTURIPATH);
        }

        if ($user_url_catched) {
            $fio = '';
            $fio = $user_info['fio'];
            $title = Multilanguage::_('AGENT_ADS', 'system') . ' ' . $fio;
            $meta_title = $title;

            $this->setRequestValue('user_id', $user_info['user_id']);
            $this->setRequestValue('user_view', $REQUESTURIPATH);

            $this->template->assert('title', $title);
            $this->template->assert('meta_title', $meta_title);

        }

        if (!$any_url_catched) {

            $result = $this->_detectUrlParams(parse_url($REQUESTURIPATH, PHP_URL_PATH));


            if ($result['topic_id']/* && !is_array($result['topic_id']) */) {
                $this->setRequestValue('topic_id', $result['topic_id']);
            }
            if ($result['city_id']) {
                $this->setRequestValue('city_id', $result['city_id']);
            }
            $gorod_name = $result['gorod_name'];


            $url_info = parse_url($REQUESTURIPATH);

            if (SITEBILL_MAIN_URL != '') {
                $cmp_url = SITEBILL_MAIN_URL . '';
                $url_info['path'] .= SITEBILL_MAIN_URL;
            } else {
                $cmp_url = '';
            }
            if (
                $this->getRequestValue('country_id') == '' &&
                $this->getRequestValue('city_id') == '' &&
                $this->getRequestValue('topic_id') == '' and
                (
                    //
                    $url_info['path'] != $cmp_url and
                    $url_info['path'] != $cmp_url . 'index.php' and
                    $url_info['path'] != $cmp_url . 'search/' and
                    $url_info['path'] != $cmp_url . 'apps/api/rest.php'
                ) and
                $this->getRequestValue('user_id') === NULL
            ) {
                $sapi_name = php_sapi_name();

                if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
                    header('Status: 404 Not Found');
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                }
                $this->template->assert('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
                $this->template->assert('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
                $this->template->assert('error_message', '<h1>' . Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND') . '</h1>');
                $this->template->assert('main_file_tpl', 'error_message.tpl');
                //exit();
                //echo 1;
                return false;
            } elseif ((!is_array($result['topic_id']) && $this->getRequestValue('topic_id') > 0) or ($gorod_name != '' and is_array($this->getRequestValue('topic_id')))) {
                if (is_array($this->getRequestValue('topic_id'))) {
                    $tmp_tppc = $this->getRequestValue('topic_id');
                    $topic = $this->getTopicFullInfo($tmp_tppc[0]);
                } else {
                    $topic = $this->getTopicFullInfo($this->getRequestValue('topic_id'));
                }

                if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
                    $curlang = $this->getCurrentLang();
                    $lang_postfix = '_' . $curlang;
                    if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                        $lang_postfix = '';
                    }
                }


                if (isset($topic['meta_title' . $lang_postfix]) && $topic['meta_title' . $lang_postfix] != '') {
                    $meta_title = $topic['meta_title' . $lang_postfix];
                } elseif ($topic['meta_title'] != '') {
                    $meta_title = $topic['meta_title'];
                } else {
                    $meta_title = '';
                }

                if (isset($topic['name' . $lang_postfix]) && $topic['name' . $lang_postfix] != '') {
                    $title = $topic['name' . $lang_postfix];
                } else {
                    $title = $topic['name'];
                }

                if (isset($topic['public_title' . $lang_postfix]) && $topic['public_title' . $lang_postfix] != '') {
                    $title = $topic['public_title' . $lang_postfix];
                } elseif (isset($topic['public_title']) && $topic['public_title'] != '') {
                    $title = $topic['public_title'];
                    /* if($meta_title==''){
                      $meta_title=$title;
                      } */
                }

                if ($meta_title == '') {
                    $meta_title = $title;
                }

                if (isset($topic['description' . $lang_postfix]) && $topic['description' . $lang_postfix] != '') {
                    $this->template->assert('description', $topic['description' . $lang_postfix]);
                } elseif ($topic['description'] != '') {
                    $this->template->assert('description', $topic['description']);
                }
                if (isset($topic['meta_description' . $lang_postfix]) && $topic['meta_description' . $lang_postfix] != '') {
                    $this->template->assert('meta_description', $topic['meta_description' . $lang_postfix]);
                } elseif ($topic['meta_description'] != '') {
                    $this->template->assert('meta_description', $topic['meta_description']);
                }
                if (isset($topic['meta_keywords' . $lang_postfix]) && $topic['meta_keywords' . $lang_postfix] != '') {
                    $this->template->assert('meta_keywords', $topic['meta_keywords' . $lang_postfix]);
                } elseif ($topic['meta_keywords'] != '') {
                    $this->template->assert('meta_keywords', $topic['meta_keywords']);
                }
                if ($gorod_name) {
                    $title .= ' - ' . $gorod_name;
                }

                $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));

                $this->template->assert('title', $title);
                $this->template->assert('meta_title', $meta_title);
            } else {
                if ($this->getConfigValue('meta_title_main') != '') {
                    $title = $this->getConfigValue('site_title');
                    $meta_title = $this->getConfigValue('meta_title_main');
                } else {
                    $title = $meta_title = $this->getConfigValue('site_title');
                }
                $meta = $this->appendPageNumberTail($meta, intval($this->getRequestValue('page')));
                if (!$this->lock_title) {
                    $this->template->assert('title', $title);
                }
                $this->template->assert('meta_title', $meta_title);
                $this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
                $this->template->assert('meta_keywords', $this->getConfigValue('meta_keywords_main'));
            }
        }


        $this->setGridViewType();


        if ($route_catched) {

        } elseif ($predefined_url_catched) {

        } elseif ($country_url_catched) {

        } elseif ($district_url_catched) {

        } elseif ($city_url_catched) {
            if (method_exists($this, 'cityFrontPage')) {
                return $this->cityFrontPage($city_info);
            }
        } elseif ($region_url_catched) {

        } elseif ($complex_url_catched) {

        } else {

        }

        $params_r = $this->gatherRequestParams();

        if (isset($routedparams)) {
            $params_r['routed_params'] = $routedparams;
            $params_r['pager_url'] = $REQUESTURIPATH;
        }


        if (!empty($params)) {
            $params = array_merge($params, $params_r);
        } else {
            $params = $params_r;
        }

        /* if($to_excell){
          $params['no_portions']=1;
          $data=$grid_constructor->get_sitebill_adv_core($params, false, false, false, false);
          return $this->getRealtyListAsExcell($data);
          } */

        $grid_constructor->main($params);


        return '';
    }

    function grid_adv2($params = array())
    {
        $country_url_catched = false;
        $find_url_catched = false;
        $city_url_catched = false;
        $region_url_catched = false;
        $predefined_url_catched = false;
        $route_catched = false;
        $REQUESTURIPATH = Sitebill::getClearRequestURI();

        $grid_constructor = $this->_getGridConstructor();
        //	echo 1;
        $this->setGridViewType();

        if ($REQUESTURIPATH != '') {

            $trailing_slashe = '/';
            if (1 == (int)$this->getConfigValue('apps.seo.no_trailing_slashes')) {
                $trailing_slashe = '';
            }

            if ($REQUESTURIPATH == 'find') {
                return $this->FrontAction_grid_find($REQUESTURIPATH);
            }

            if ($REQUESTURIPATH == 'myfavorites') {
                return $this->FrontAction_grid_favorites($REQUESTURIPATH);
            }


            if (preg_match('/^user(\d).html$/', $REQUESTURIPATH, $matches)) {
                return $this->FrontAction_grid_user($REQUESTURIPATH, array('user_id' => $matches[1]));
            }
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/predefinedlinks/admin/admin.php') && 1 == $this->getConfigValue('apps.predefinedlinks.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/predefinedlinks/admin/admin.php';
                $PDLA = new predefinedlinks_admin();
                if ($predefined_info = $PDLA->checkAlias($REQUESTURIPATH)) {
                    return $this->FrontAction_grid_predefined($REQUESTURIPATH, $predefined_info);
                }
            }


            $DBC = DBC::getInstance();

            if (intval($this->getConfigValue('apps.seo.no_country_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_country WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    if ((int)$ar['country_id'] != 0) {
                        if ($ar['url'] != $REQUESTURIPATH) {
                            $new_location = SITEBILL_MAIN_URL . '/' . $ar['url'] . $trailing_slashe;
                            $this->go301($new_location);
                        }
                        return $this->FrontAction_grid_country($REQUESTURIPATH, $ar);
                    }
                }
            }

            if (intval($this->getConfigValue('apps.seo.no_region_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_region WHERE alias=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ((int)$ar['region_id'] != 0) {
                        if ($ar['alias'] != $REQUESTURIPATH) {
                            $new_location = SITEBILL_MAIN_URL . '/' . $ar['alias'] . $trailing_slashe;
                            $this->go301($new_location);
                        }
                        return $this->FrontAction_grid_region($REQUESTURIPATH, $ar);
                    }
                }
            }

            if (intval($this->getConfigValue('apps.seo.no_city_url')) === 0) {
                $query = 'SELECT * FROM ' . DB_PREFIX . '_city WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    if ((int)$ar['city_id'] != 0) {
                        if ($ar['url'] != $REQUESTURIPATH) {
                            $new_location = SITEBILL_MAIN_URL . '/' . $ar['url'] . $trailing_slashe;
                            $this->go301($new_location);
                        }
                        return $this->FrontAction_grid_city($REQUESTURIPATH, $ar);
                    }
                }
            }

            if ($this->getConfigValue('apps.complex.enable') && intval($this->getConfigValue('apps.complex.no_grid_catch')) === 0) {
                $DBC = DBC::getInstance();
                $query = 'SELECT * FROM ' . DB_PREFIX . '_complex WHERE url=? LIMIT 1';
                $stmt = $DBC->query($query, array($REQUESTURIPATH));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if (intval($ar['complex_id']) !== 0) {
                        if ($ar['url'] != $REQUESTURIPATH) {
                            $new_location = SITEBILL_MAIN_URL . '/' . $ar['url'] . $trailing_slashe;
                            $this->go301($new_location);
                        }
                        return $this->FrontAction_grid_complex($REQUESTURIPATH, $ar);
                    }
                }
            }

            if (intval($this->getConfigValue('apps.seo.no_topic_url')) === 0) {
                if (preg_match('/topic(\d*).html/', $REQUESTURIPATH, $matches)) {
                    $topic_id = (int)$matches[1];
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure = new Structure_Manager();
                    $urls = $Structure->loadCategoriesUrls();

                    if (isset($urls[$topic_id]) && $urls[$topic_id] != '') {
                        $new_location = SITEBILL_MAIN_URL . '/' . $urls[$topic_id] . $trailing_slashe;
                        $this->go301($new_location);
                        exit();
                    } elseif (isset($urls[$topic_id])) {
                        $topic_info = $this->getTopicFullInfo($topic_id);
                        return $this->FrontAction_grid_topic($REQUESTURIPATH, $topic_info);
                    }
                } else {
                    $topic_id = 0;
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure = new Structure_Manager();
                    $urls = $Structure->loadCategoriesUrls();
                    foreach ($urls as $k => $v) {
                        if ($v == '') {
                            unset($urls[$k]);
                        }
                    }
                    $urls_to_ids = array_flip($urls);
                    if (isset($urls_to_ids[$REQUESTURIPATH])) {
                        $topic_id = $urls_to_ids[$REQUESTURIPATH];
                    }

                    if ($topic_id > 0) {
                        $topic_info = $this->getTopicFullInfo($topic_id);
                        return $this->FrontAction_grid_topic($REQUESTURIPATH, $topic_info);
                    }
                }
            }

            if (intval($this->getConfigValue('apps.seo.no_city_topic_url')) === 0) {
                $x = $this->cityTopicUrlFind($REQUESTURIPATH);
                if (false != $x) {
                    return $this->FrontAction_grid_citytopic($REQUESTURIPATH, $x);
                }
            }

            /* if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/trouter/trouter.php')){
              require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/trouter/trouter.php';
              $Router=new TRouter();
              $Router->setAlias($REQUESTURIPATH);
              if($Router->detectAlias()){
              return $Router->run();
              }
              } */

            if (false !== ($r = $this->FrontAction_grid_custom($REQUESTURIPATH))) {
                $c = $r[0];
                if (method_exists($this, $c)) {
                    return $this->$c($REQUESTURIPATH, $r[1]);
                }
            }

            if (intval($this->getConfigValue('apps.seo.no_index_search')) === 0 && $REQUESTURIPATH == 'index.php') {
                return $this->FrontAction_grid_common();
            }

            return $this->FrontAction_404($REQUESTURIPATH);
        } else {
            if (intval($this->getConfigValue('apps.seo.no_index_search')) === 0) {
                return $this->FrontAction_grid_common();
            } else {
                return $this->FrontAction_index();
            }
        }
    }

    function grid_adv_favorites()
    {

        //$grid_constructor = $this->_grid_constructor;
        $grid_constructor = $this->_getGridConstructor();

        /* $params['id'] = $this->getRequestValue('id');
          $params['topic_id'] = '';
          $params['order'] = $this->getRequestValue('order');
          $params['region_id'] = $this->getRequestValue('region_id');
          $params['city_id'] = $this->getRequestValue('city_id');
          $params['district_id'] = $this->getRequestValue('district_id');
          $params['metro_id'] = $this->getRequestValue('metro_id');
          $params['street_id'] = $this->getRequestValue('street_id');
          $params['page'] = $this->getRequestValue('page'); */
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['order'] = $this->getRequestValue('order');

        if (count($_SESSION['favorites']) != 0) {
            $params['favorites'] = $_SESSION['favorites'];
        } else {
            $params['favorites'] = array(-1);
        }


        /* $params['price'] = $this->getRequestValue('price');
          $this->template->assert('price', $params['price']);

          $params['price_min'] = $this->getRequestValue('price_min');
          $this->template->assert('price_min', $params['price_min']);

          $params['house_number'] = $this->getRequestValue('house_number');
          $this->template->assert('house_number', $params['house_number']); */

        $params['onlyspecial'] = $this->getRequestValue('onlyspecial');
        $this->template->assert('onlyspecial', $params['onlyspecial']);

        $grid_constructor->main($params);
        $this->template->assert('breadcrumbs', $this->get_breadcrumbs(array('<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>', _e('Избранное'))));
        $this->template->assert('breadcrumbs_array', array('<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>', _e('Избранное')));


        return;
    }

    /**
     * Get special grid
     * @param
     * @return
     */
    function grid_special()
    {
        $params['spec'] = 'spec';
        $grid_constructor = $this->_getGridConstructor();
        $grid_constructor->special($params);
    }

    /**
     * Get special right grid
     * @param
     * @return
     */
    function grid_special_right()
    {
        $params['spec'] = 'spec';
        $grid_constructor = $this->_getGridConstructor();
        $grid_constructor->special_right($params);
    }

    /**
     * Get special right grid with params
     * @param
     * @return
     */
    function grid_special_right_with_params($params)
    {
        $params['spec'] = 'spec';
        $grid_constructor = $this->_getGridConstructor();
        $grid_constructor->special_right($params);
    }
}
