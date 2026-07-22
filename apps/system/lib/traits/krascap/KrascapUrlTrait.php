<?php
/**
 * KrascapUrlTrait — URL analysis and detection methods extracted from SiteBill_Krascap.
 *
 * Methods: urlAnalizer, _detectUrlParams, topicUrlFind, cityTopicUrlFind,
 *          getIDfromURI, isTopicExists, isHomePage, isAccountDetected, isRealtyDetected
 */
trait KrascapUrlTrait
{
    function urlAnalizer()
    {
        $topic_id = FALSE;
        if (preg_match('/topic(\d*).html/', $_SERVER['REQUEST_URI'], $matches)) {
            $topic_id = $matches[1];
        } elseif ($x = $this->topicUrlFind($_SERVER['REQUEST_URI'])) {
            $topic_id = $x;
        } else {
            $topic_id = FALSE;
        }
        return $topic_id;
    }

    function _detectUrlParams($server_request_uri)
    {

        $server_request_uri = urldecode($server_request_uri);
        $server_request_uri = parse_url($server_request_uri, PHP_URL_PATH);
        $topic_id = FALSE;
        $city_id = FALSE;
        $gorod_name = FALSE;

        $server_request_uri = SiteBill::getClearRequestURI();

        if (preg_match('/topic(\d*).html/', $server_request_uri, $matches) && $this->isTopicExists($matches[1])) {
            //$this->setRequestValue('topic_id', $matches[1]);
            $topic_id = (int)$matches[1];
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure = new Structure_Manager();
            $urls = $Structure->loadCategoriesUrls();
            //print_r($urls);
            if (isset($urls[$topic_id]) && $urls[$topic_id] != '') {
                header('location:' . SITEBILL_MAIN_URL . '/' . $urls[$topic_id]);
                exit();
            }
        } else {
            if ($x = $this->cityTopicUrlFind($server_request_uri)) {
                $topic_id = $x[1];
                $city_id = $x[0];
                $gorod_name = $x[2];
            } elseif ($x = $this->topicUrlFind($server_request_uri)) {
                $topic_id = $x;
            } else {
                if ($this->getConfigValue('apps.seo.level_enable') == 1) {
                    $ru = $server_request_uri;
                    if (substr($ru, 0, 1) === '/') {
                        $ru = substr($ru, 1);
                    }
                    if (substr($ru, -1, 1) === '/') {
                        $ru = substr($ru, 0, strlen($ru) - 1);
                    }
                    //$ru=trim($server_request_uri,'/');
                    if (SITEBILL_MAIN_URL != '') {
                        $ru = str_replace(trim(SITEBILL_MAIN_URL, '/') . '/', '', $ru);
                    }
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure = new Structure_Manager();
                    $urls = $Structure->loadCategoriesUrls();

                    $urls_to_ids = array_flip($urls);

                    $parts = explode('?', $ru);

                    if (strlen($parts[0]) > 0) {
                        if (isset($urls_to_ids[$parts[0]])) {

                            //$this->setRequestValue('topic_id', $urls_to_ids[$parts[0]]);
                            $topic_id = $urls_to_ids[$parts[0]];
                        }
                    }
                }
            }
        }
        return array(
            'topic_id' => $topic_id,
            'city_id' => $city_id,
            'gorod_name' => $gorod_name,
        );
    }

    function topicUrlFind($request_uri)
    {

        $url_parts = parse_url(urldecode($request_uri));

        $path = $url_parts['path'];
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }
        if (substr($path, -1, 1) === '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }


        $topic_name = str_replace('/', '', $url_parts['path']);


        $topic_name = $path;


        $topic_name = SiteBill::getClearRequestURI();
        if ($topic_name == '') {
            return false;
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure = new Structure_Manager();
        $urls = $Structure->loadCategoriesUrls();

        if ($this->getConfigValue('apps.seo.level_enable') == 1) {

        } else {
            foreach ($urls as $k => $u) {
                $up = explode('/', $u);
                $urls[$k] = end($up);
            }
        }

        $urls_to_ids = array_flip($urls);
        if (isset($urls_to_ids[$topic_name])) {
            return $urls_to_ids[$topic_name];
        } else {
            return FALSE;
        }
        if (strlen($topic_name) > 0) {
            $DBC = DBC::getInstance();
            $query = 'SELECT id FROM ' . DB_PREFIX . '_topic WHERE url=? LIMIT 1';
            $stmt = $DBC->query($query, array($topic_name));

            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                return $ar['id'];
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    function cityTopicUrlFind($request_uri)
    {
        $request_uri = urldecode($request_uri);

        $cid = NULL;
        $tid = NULL;
        $request_uri = trim($request_uri, '/');
        if (strpos($request_uri, '-') != false) {
            $request_uri = str_replace('.html', '', $request_uri);
            $parts = array();
            $parts = explode('-', $request_uri);
            /* print_r($parts); */
            $parts_count = count($parts);
            for ($i = 1; $i < $parts_count; $i++) {
                $cid = NULL;
                $tid = NULL;
                $city_name = '';

                $left_part = array();
                $right_part = array();
                $left_part = array_slice($parts, 0, $i);
                $right_part = array_slice($parts, $i);

                $DBC = DBC::getInstance();
                $query = 'SELECT city_id, name FROM ' . DB_PREFIX . '_city WHERE translit_name=? LIMIT 1';

                $stmt = $DBC->query($query, array(implode('-', $left_part)));


                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $cid = $ar['city_id'];
                    $city_name = $ar['name'];
                }

                $query = 'SELECT id FROM ' . DB_PREFIX . '_topic WHERE translit_name=?';
                $stmt = $DBC->query($query, array(implode('-', $right_part)));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $tid = $ar['id'];
                }

                if ($cid !== NULL && $tid != NULL) {
                    return array($cid, $tid, $city_name);
                }
            }
            return FALSE;
        }
        return FALSE;
    }

    /**
     * Get ID from URI
     * @param string $uri uri
     * @return int
     */
    function getIDfromURI($uri)
    {
        if (trim($this->getConfigValue('apps.seo.realty_alias')) != '') {
            $realty_alias = trim($this->getConfigValue('apps.seo.realty_alias'));
        } else {
            $realty_alias = 'realty';
        }
        preg_match('/' . $realty_alias . '(\d+)(.html)?/s', $uri, $matches);
        if ($matches[1] > 0) {
            return $matches[1];
        }
        return false;
    }

    function isTopicExists($topic_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(id) AS cnt FROM ' . DB_PREFIX . '_topic WHERE id=?';
        $stmt = $DBC->query($query, array((int)$topic_id));

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['cnt'] > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    protected function isHomePage($REQUESTURIPATH)
    {
        if ($REQUESTURIPATH == '' && strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && empty($_GET)) {
            return true;
        }
        return false;
    }

    function isAccountDetected($requesturi)
    {
        $has_result = false;
        $apps_processor = new Apps_Processor();
        $apps_processor->run_account();
        if (count($apps_processor->get_executed_apps()) > 0) {
            $work_subcontroller = 'apps';
            $has_result = true;
            echo $apps_processor->get_runned_app();
            return true;
        }

        if (!$has_result && preg_match('/^account/', $requesturi)) {
            $this->template->assert('right_column', '');
            $this->template->assert('is_account', '1');
            $this->template->assert('search_form_template', '');

            //return;
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
            $Account = new Account;

            if ($Account->get_user_id() > 0) {
                $company_profile = $Account->get_company_profile($Account->get_user_id());
                $this->template->assert('company', $company_profile);
            }

            $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                array(
                    '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                    '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>'
                )
            )
            );

            if (preg_match('/^account\/profile/', $requesturi)) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile_using_model.php');
                $profile = new User_Profile_Model();
                $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                    array(
                        '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                        '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>',
                        '<a href="' . $this->createUrlTpl('account/profile') . '">Профиль</a>'
                    )));

                $this->template->assert('main', $profile->main());
            } elseif (preg_match('/^account\/balance/', $requesturi)) {

                $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                    array(
                        '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                        '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>',
                        '<a href="' . $this->createUrlTpl('account/balance') . '">Баланс</a>'
                    )));

                $this->template->assert('main', $Account->main());
            } elseif (preg_match('/^account\/user/', $requesturi)) {
                if ($this->getConfigValue('apps.company.enable')) {
                    $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                        array(
                            '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                            '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>',
                            '<a href="' . $this->createUrlTpl('account/user') . '">Риелторы</a>'
                        )));

                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/user/user_company_manager.php');
                    $user_company_manager = new User_Company_Manager();
                    $this->template->assert('main', $user_company_manager->frontend_main());
                }
            } else {

                $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                    array(
                        '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                        '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>',
                        '<a href="' . $this->createUrlTpl('account/data') . '">Мои объявления</a>'
                    )));

                if (preg_match('/add/', $requesturi)) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_admin.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/data/user_data.php');
                    $user_data_manager = new User_Data_Manager();
                    $this->template->assert('main', $user_data_manager->add());
                } else {
                    /* require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
                      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
                      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
                      $user_data_manager = new User_Data_Manager();
                      $this->template->assert('main', $user_data_manager->main());
                     */
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/admin/admin.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/site/site.php');
                    $data_site = new data_site();
                    $this->template->assert('main', $data_site->main());
                }
            }


            /*
              $this->template->render();
              $rs = $this->template->toHTML();
              return $rs; */
        }
    }

    function isRealtyDetected($requesturi)
    {

        $hard_mode = false; //decline all aliased url if they have no determined alias
        $result = false;
        $unknown_address = false;
        $realty_id = false;

        $querytail = '';
        $request = $this->request();
        $get = $request->query->all();
        if (!empty($get)) {
            $querytail = '?' . http_build_query($get);
        }


        if (!$result && 1 == $this->getConfigValue('apps.seo.data_alias_enable')) {
            $url_string_parts = explode('/', $requesturi);
            if (count($url_string_parts) > 0) {
                $possible_alias = $url_string_parts[count($url_string_parts) - 1];

                $possible_alias = preg_replace('/[^A-Za-z0-9_-]/', '', urldecode($possible_alias));
                if ($possible_alias != '') {
                    $DBC = DBC::getInstance();
                    $q = 'SELECT id, topic_id, translit_alias FROM ' . DB_PREFIX . '_data WHERE translit_alias=?';
                    $stmt = $DBC->query($q, array($possible_alias));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        //$this->db->fetch_assoc();
                        if ((int)$ar['id'] > 0) {
                            if (1 == $this->getConfigValue('apps.seo.level_enable') && count($url_string_parts) == 1) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                                $Structure_Manager = new Structure_Manager();
                                $urls = $Structure_Manager->loadCategoriesUrls();
                                if (isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']] != '') {
                                    $new_location = $this->createUrlTpl($urls[$ar['topic_id']] . '/' . $ar['translit_alias'] . $querytail);
                                    $this->go301($new_location);
                                    return false;
                                } else {
                                    return false;
                                }
                            } elseif (1 == $this->getConfigValue('apps.seo.level_enable') && count($url_string_parts) > 1) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                                $Structure_Manager = new Structure_Manager();
                                $urls = $Structure_Manager->loadCategoriesUrls();
                                array_pop($url_string_parts);
                                $facturl = implode('/', $url_string_parts);
                                if (!isset($urls[$ar['topic_id']]) || $urls[$ar['topic_id']] == '' || $urls[$ar['topic_id']] != $facturl) {
                                    return false;
                                }
                            } elseif (0 == $this->getConfigValue('apps.seo.level_enable') && count($url_string_parts) > 1) {

                                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                                $Structure_Manager = new Structure_Manager();
                                $urls = $Structure_Manager->loadCategoriesUrls();
                                array_pop($url_string_parts);

                                $facturl = implode('/', $url_string_parts);

                                if (isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']] != '' && $urls[$ar['topic_id']] == $facturl) {
                                    $new_location = $this->createUrlTpl($ar['translit_alias'] . $querytail);
                                    $this->go301($new_location);
                                    return false;
                                } else {
                                    return false;
                                }
                            }
                            $realty_id = (int)$ar['id'];
                            $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                            $kvartira_view = $this->_getRealtyViewer();
                            if ($html = $kvartira_view->main($realty_id)) {
                                $this->template->assert('main', $html);
                                $result = true;
                            }
                        }
                    }
                }
            }
        }
        if (trim($this->getConfigValue('apps.seo.realty_alias')) != '') {
            $realty_alias = trim($this->getConfigValue('apps.seo.realty_alias'));
        } else {
            $realty_alias = 'realty';
        }

        if (!$result && preg_match('/' . $realty_alias . '/', $requesturi)) {
            $realty_id = $this->getIDfromURI($requesturi);
            if (!$realty_id) {
                return false;
            }

            if (1 == $this->getConfigValue('apps.seo.data_alias_enable')) {
                $DBC = DBC::getInstance();
                $query = 'SELECT topic_id, translit_alias FROM ' . DB_PREFIX . '_data WHERE id=?';
                $stmt = $DBC->query($query, array($realty_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['translit_alias'] != '') {
                        if (1 == $this->getConfigValue('apps.seo.level_enable')) {
                            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                            $Structure = new Structure_Manager();
                            $urls = $Structure->loadCategoriesUrls();
                            if (isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']] != '') {
                                $new_location = $this->createUrlTpl($urls[$ar['topic_id']] . '/' . $ar['translit_alias'] . $querytail);
                                $this->go301($new_location);
                                return false;
                            } else {
                                $new_location = $this->createUrlTpl($ar['translit_alias'] . $querytail);
                                $this->go301($new_location);
                                return false;
                            }
                        } else {
                            $new_location = $this->createUrlTpl($ar['translit_alias'] . $querytail);
                            $this->go301($new_location);
                            return false;
                        }
                    } elseif ($hard_mode && $ar['translit_alias'] == '') {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            if (1 == $this->getConfigValue('apps.seo.level_enable') && preg_match('/^' . $realty_alias . '/', $requesturi)) {

                $realty_id = $this->getIDfromURI($requesturi);
                if (!$realty_id) {
                    return false;
                }
                $DBC = DBC::getInstance();
                $query = 'SELECT topic_id FROM ' . DB_PREFIX . '_data WHERE id=?';
                $stmt = $DBC->query($query, array($realty_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);

                    $topic_id = intval($ar['topic_id']);
                    //echo $topic_id;
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure_Manager = new Structure_Manager();
                    $category_structure = $Structure_Manager->loadCategoryStructure();

                    if ($category_structure['catalog'][$topic_id]['url'] != '') {
                        $parent_category_url = $category_structure['catalog'][$topic_id]['url'] . '/';
                    } else {
                        $parent_category_url = '';
                    }
                    $new_location = $this->createUrlTpl($parent_category_url . $realty_alias . $realty_id . (1 == $this->getConfigValue('apps.seo.html_prefix_enable') ? '.html' : '') . $querytail);
                    $this->go301($new_location);
                    return false;
                } else {
                    return false;
                }
            } elseif (1 == $this->getConfigValue('apps.seo.level_enable') && !preg_match('/^' . $realty_alias . '/', $requesturi)) {

                $realty_id = $this->getIDfromURI($requesturi);

                if (!$realty_id) {
                    return false;
                }
                $DBC = DBC::getInstance();
                $query = 'SELECT topic_id FROM ' . DB_PREFIX . '_data WHERE id=?';
                $stmt = $DBC->query($query, array($realty_id));
                if ($stmt) {
                    $ti = $DBC->fetch($stmt);
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure_Manager = new Structure_Manager();
                    $urls = $Structure_Manager->loadCategoriesUrls();
                    $real_turl = $urls[$ti['topic_id']];
                    $comparative_url = $real_turl . '/' . $realty_alias . $realty_id;
                    if (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                        $comparative_url .= '.html';
                    }
                    //echo preg_quote($real_turl, '/');
                    if (!preg_match('/^' . preg_quote($comparative_url, '/') . '$/', ltrim($requesturi, '/'))) {
                        $new_location = $this->createUrlTpl($real_turl . '/' . $realty_alias . $realty_id . (1 == $this->getConfigValue('apps.seo.html_prefix_enable') ? '.html' : '') . $querytail);
                        $this->go301($new_location);
                        return false;
                    }
                }
                $kvartira_view = $this->_getRealtyViewer();
                $html = $kvartira_view->main($realty_id);
                if ($html) {
                    $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                    $this->template->assert('main', $html);
                    $result = true;
                }
            } elseif (0 == $this->getConfigValue('apps.seo.level_enable') && preg_match('/^' . $realty_alias . '/', $requesturi)) {
                $realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
                if (!$realty_id) {
                    return false;
                }
                $comparative_url = $realty_alias . $realty_id;
                if (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                    $comparative_url .= '.html';
                }
                if (!preg_match('/^' . preg_quote($comparative_url, '/') . '$/', ltrim($requesturi, '/'))) {
                    $new_location = $this->createUrlTpl($realty_alias . $realty_id . (1 == $this->getConfigValue('apps.seo.html_prefix_enable') ? '.html' : '') . $querytail);
                    $this->go301($new_location);
                    return false;
                }
                $kvartira_view = $this->_getRealtyViewer();
                $html = $kvartira_view->main($realty_id);
                if ($html) {
                    $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                    $this->template->assert('main', $html);
                    $result = true;
                }
            } elseif (0 == $this->getConfigValue('apps.seo.level_enable') && !preg_match('/^' . $realty_alias . '/', $requesturi)) {
                $realty_id = $this->getIDfromURI($requesturi);

                if (!$realty_id) {
                    return false;
                }
                $DBC = DBC::getInstance();
                $query = 'SELECT topic_id FROM ' . DB_PREFIX . '_data WHERE id=?';
                $stmt = $DBC->query($query, array($realty_id));
                if ($stmt) {
                    $ti = $DBC->fetch($stmt);
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                    $Structure_Manager = new Structure_Manager();
                    $urls = $Structure_Manager->loadCategoriesUrls();
                    $real_turl = $urls[$ti['topic_id']];
                    $comparative_url = $real_turl . '/' . $realty_alias . $realty_id;
                    if (preg_match('/^' . preg_quote($comparative_url, '/') . '/', ltrim($requesturi, '/'))) {
                        $new_location = $this->createUrlTpl($realty_alias . $realty_id . (1 == $this->getConfigValue('apps.seo.html_prefix_enable') ? '.html' : '') . $querytail);
                        $this->go301($new_location);
                        return false;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                $kvartira_view = $this->_getRealtyViewer();
                $html = $kvartira_view->main($realty_id);
                if ($html) {
                    $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                    $this->template->assert('main', $html);
                    $result = true;
                }
            }
        }

        if (!$result && 0 == $this->getConfigValue('apps.seo.data_alias_enable')) {
            $url_string_parts = explode('/', $requesturi);
            if (count($url_string_parts) > 0) {
                $possible_alias = end($url_string_parts);

                $possible_alias = preg_replace('/[^A-Za-z0-9_-]/', '', urldecode($possible_alias));
                if ($possible_alias != '') {
                    $DBC = DBC::getInstance();
                    $q = 'SELECT id, topic_id, translit_alias FROM ' . DB_PREFIX . '_data WHERE translit_alias=?';
                    $stmt = $DBC->query($q, array($possible_alias));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);

                        if ((int)$ar['id'] > 0) {
                            if (1 == $this->getConfigValue('apps.seo.level_enable')) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                                $Structure_Manager = new Structure_Manager();
                                $urls = $Structure_Manager->loadCategoriesUrls();
                                if (isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']] != '') {
                                    $new_location = $urls[$ar['topic_id']] . '/' . $realty_alias . $ar['id'];
                                } else {
                                    $new_location = $realty_alias . $ar['id'];
                                }
                                if (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                                    $new_location = $new_location . '.html';
                                }
                                $new_location = $this->createUrlTpl($new_location . $querytail);

                                $this->go301($new_location);
                                return false;
                            } elseif (0 == $this->getConfigValue('apps.seo.level_enable')) {
                                $new_location = $realty_alias . $ar['id'];
                                if (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                                    $new_location = $new_location . '.html';
                                }
                                $new_location = $this->createUrlTpl($new_location . $querytail);
                                $this->go301($new_location);
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
}
