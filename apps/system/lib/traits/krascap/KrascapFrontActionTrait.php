<?php
/**
 * KrascapFrontActionTrait — All FrontAction_* methods extracted from SiteBill_Krascap.
 *
 * 25 methods: FrontAction_grid_find, FrontAction_grid_country, FrontAction_grid_region,
 * FrontAction_grid_complex, FrontAction_grid_favorites, FrontAction_grid_city,
 * FrontAction_index, FrontAction_add, FrontAction_account, FrontAction_login,
 * FrontAction_register, FrontAction_remind, FrontAction_ipotekaorder,
 * FrontAction_contactus, FrontAction_logout, FrontAction_grid_common,
 * FrontAction_grid_user, FrontAction_myfavorites, FrontAction_404,
 * FrontAction_grid_topic, FrontAction_grid_citytopic, FrontAction_grid_predefined,
 * FrontAction_grid_custom, FrontAction_isunderconstruct, FrontAction_yandexrealty_export
 */
trait KrascapFrontActionTrait
{
    protected function FrontAction_grid_find($REQUESTURIPATH)
    {
        $grid_constructor = $this->_getGridConstructor();
        if (Multilanguage::is_set('LT_FIND_URL_TITLE', '_template')) {
            $title = Multilanguage::_('LT_FIND_URL_TITLE', '_template');
        } else {
            $title = Multilanguage::_('FIND_URL_TITLE', 'system');
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $title);
        $this->setRequestValue('find_url_catched', 1);

        $params_r = $this->gatherRequestParams();
        if (!empty($params)) {
            $params = array_merge($params, $params_r);
        } else {
            $params = $params_r;
        }

        $grid_constructor->main($params);
    }

    protected function FrontAction_grid_country($REQUESTURIPATH, $country_info)
    {

        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();
            $lang_postfix = '_' . $curlang;
            if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                $lang_postfix = '';
            }
        } else {
            $lang_postfix = '';
        }
        $meta_title = '';
        if (isset($country_info['meta_title' . $lang_postfix]) && $country_info['meta_title' . $lang_postfix] != '') {
            $meta_title = $country_info['meta_title' . $lang_postfix];
        } elseif ($country_info['meta_title'] != '') {
            $meta_title = $country_info['meta_title'];
        }

        if (isset($country_info['name' . $lang_postfix]) && $country_info['name' . $lang_postfix] != '') {
            $title = $country_info['name' . $lang_postfix];
        } else {
            $title = $country_info['name'];
        }

        if ($meta_title == '') {
            $meta_title = $title;
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        if (isset($country_info['description' . $lang_postfix]) && $country_info['description' . $lang_postfix] != '') {
            $this->template->assert('description', $country_info['description' . $lang_postfix]);
        } elseif ($country_info['description'] != '') {
            $this->template->assert('description', $country_info['description']);
        }
        if (isset($country_info['meta_description' . $lang_postfix]) && $country_info['meta_description' . $lang_postfix] != '') {
            $this->template->assert('meta_description', $country_info['meta_description' . $lang_postfix]);
        } elseif ($country_info['meta_description'] != '') {
            $this->template->assert('meta_description', $country_info['meta_description']);
        } else {
            $this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
        }
        if (isset($country_info['meta_keywords' . $lang_postfix]) && $country_info['meta_keywords' . $lang_postfix] != '') {
            $this->template->assert('meta_keywords', $country_info['meta_keywords' . $lang_postfix]);
        } elseif ($country_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $country_info['meta_keywords']);
        } else {
            $this->template->assert('meta_keywords', $this->getConfigValue('meta_keywords_main'));
        }

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        $this->setRequestValue('country_id', intval($country_info['country_id']));
        $params['country_id'] = intval($country_info['country_id']);
        $grid_constructor->main($params);
    }

    protected function FrontAction_grid_region($REQUESTURIPATH, $region_info)
    {
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();
            $lang_postfix = '_' . $curlang;
            if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                $lang_postfix = '';
            }
        } else {
            $lang_postfix = '';
        }

        if (isset($region_info['public_title']) && $region_info['public_title'] != '') {
            $title = $region_info['public_title'];
        } else {
            $title = $region_info['name'];
        }
        if ($region_info['meta_title'] != '') {
            $meta_title = $region_info['meta_title'];
        } else {
            $meta_title = $region_info['name'];
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        if ($region_info['description'] != '') {
            $this->template->assert('description', $region_info['description']);
        }
        if ($region_info['meta_description'] != '') {
            $this->template->assert('meta_description', $region_info['meta_description']);
        } else {
            $this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
        }
        if ($region_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $region_info['meta_keywords']);
        } else {
            $this->template->assert('meta_keywords', $this->getConfigValue('meta_keywords_main'));
        }

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        $this->setRequestValue('region_id', intval($region_info['region_id']));
        $params['region_id'] = intval($region_info['region_id']);
        $grid_constructor->main($params);
    }

    protected function FrontAction_grid_complex($REQUESTURIPATH, $complex_info)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/complex/admin/admin.php');
        $complex_admin = new complex_admin();
        $data_model = new Data_Model();
        $complex_data = $complex_admin->data_model;
        $complex_data = $data_model->init_model_data_from_db('complex', 'complex_id', (int)$complex_info['complex_id'], $complex_data['complex'], true);
        $complex_data['image']['image_array'] = $this->get_image_array('complex', 'complex', 'complex_id', (int)$ar['complex_id']);

        $this->template->assert('complex_data', $complex_data);

        if ($complex_info['meta_title'] != '') {
            $title = $complex_info['name'];
            $meta_title = $complex_info['meta_title'];
        } else {
            $title = $meta_title = $complex_info['name'];
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        if ($complex_info['description'] != '') {
            $this->template->assert('description', $complex_info['description']);
        }
        if ($complex_info['meta_description'] != '') {
            $this->template->assert('meta_description', $complex_info['meta_description']);
        } else {
            $this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
        }
        if ($complex_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $complex_info['meta_keywords']);
        } else {
            $this->template->assert('meta_keywords', $this->getConfigValue('meta_keywords_main'));
        }


        //$this->setRequestValue('complex_view', $REQUESTURIPATH);

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        $this->setRequestValue('complex_id', (int)$complex_info['complex_id']);
        $params['complex_id'] = intval($complex_info['complex_id']);
        $grid_constructor->main($params);

        //$this->setRequestValue('city_id', (int)$city_info['city_id']);
        //$this->setRequestValue('city_view', $REQUESTURIPATH);
    }

    protected function FrontAction_grid_favorites($REQUESTURIPATH)
    {
        $this->template->assert('title', 'Избранное');
        $grid_constructor = $this->_getGridConstructor();
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['order'] = $this->getRequestValue('order');
        if (count($_SESSION['favorites']) != 0) {
            $params['favorites'] = $_SESSION['favorites'];
        } else {
            $params['favorites'] = array(-1);
        }
        $grid_constructor->main($params);
    }

    protected function FrontAction_grid_city($REQUESTURIPATH, $city_info)
    {
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
            $title = $city_info['public_title' . $lang_postfix];
        } elseif (isset($city_info['public_title']) && $city_info['public_title'] != '') {
            $title = $city_info['public_title'];
        } else {
            $title = $city_info['name'];
        }
        if (isset($city_info['meta_title' . $lang_postfix]) && $city_info['meta_title' . $lang_postfix] != '') {
            $meta_title = $city_info['meta_title' . $lang_postfix];
        } elseif ($city_info['meta_title'] != '') {
            $meta_title = $city_info['meta_title'];
        } else {
            $meta_title = $title;
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        if (isset($city_info['description' . $lang_postfix]) && $city_info['description' . $lang_postfix] != '') {
            $this->template->assert('description', $city_info['description' . $lang_postfix]);
        } elseif ($city_info['description'] != '') {
            $this->template->assert('description', $city_info['description']);
        }
        if (isset($city_info['meta_description' . $lang_postfix]) && $city_info['meta_description' . $lang_postfix] != '') {
            $this->template->assert('meta_description', $city_info['meta_description' . $lang_postfix]);
        } elseif ($city_info['meta_description'] != '') {
            $this->template->assert('meta_description', $city_info['meta_description']);
        } else {
            $this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
        }
        if (isset($city_info['meta_keywords' . $lang_postfix]) && $city_info['meta_keywords' . $lang_postfix] != '') {
            $this->template->assert('meta_keywords', $city_info['meta_keywords' . $lang_postfix]);
        } elseif ($city_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $city_info['meta_keywords']);
        } else {
            $this->template->assert('meta_keywords', $this->getConfigValue('meta_keywords_main'));
        }

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        $this->setRequestValue('city_id', intval($city_info['city_id']));
        $params['city_id'] = intval($city_info['city_id']);
        $grid_constructor->main($params);

        $this->setRequestValue('city_id', (int)$city_info['city_id']);
        $this->setRequestValue('city_view', $REQUESTURIPATH);
    }

    protected function FrontAction_index()
    {
        /* $grid_constructor=$this->_getGridConstructor();
          $params=$this->gatherRequestParams();
          $params['city_id']=1;
          $grid_constructor->main($params); */
    }

    protected function FrontAction_add($REQUESTURIPATH)
    {
        if ($_SESSION['user_id'] > 0) {
            header('location: ' . SITEBILL_MAIN_URL . '/account/data/?do=new');
            exit();
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/add.php');
        $user_add = new User_Add();
        $this->template->assert('main', $user_add->main());
    }

    protected function FrontAction_account($REQUESTURIPATH)
    {

        $this->template->assert('right_column', '');
        $this->template->assert('is_account', '1');
        $this->template->assert('search_form_template', '');

        //return;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $Account = new Account;

        if (1 == $this->getConfigValue('apps.upper.enable')) {
            $user_id = $Account->get_user_id();
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/upper/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/upper/site/site.php');

            $upper_site = new upper_site();
            $upps_left = $upper_site->checkUserLimits($user_id);
            $packs_left = $upper_site->checkUserPacks($user_id);

            $this->template->assert('apps_upper_enable', 1);
            $this->template->assert('upps_left', $upps_left);
            $this->template->assert('packs_left', $packs_left);
        }


        if ($Account->get_user_id() > 0) {
            $company_profile = $Account->get_company_profile($Account->get_user_id());
            $this->template->assert('company', $company_profile);
        }


        $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
            array(
                '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>'
            )));

        if (preg_match('/^account\/profile/', $REQUESTURIPATH)) {
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
        } elseif (preg_match('/^account\/balance/', $REQUESTURIPATH)) {

            $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                array(
                    '<a href="' . $this->createUrlTpl('') . '">' . Multilanguage::_('L_HOME') . '</a>',
                    '<a href="' . $this->createUrlTpl('account') . '">Личный кабинет</a>',
                    '<a href="' . $this->createUrlTpl('account/balance') . '">Баланс</a>'
                )));

            $this->template->assert('main', $Account->main());
        } elseif (preg_match('/^account\/user/', $REQUESTURIPATH)) {
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

            if (preg_match('/add/', $REQUESTURIPATH)) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_admin.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/data/user_data.php');
                $user_data_manager = new User_Data_Manager();
                $this->template->assert('main', $user_data_manager->add());
            } else {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_admin.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/data/user_data.php');
                $user_data_manager = new User_Data_Manager();
                $this->template->assert('main', $user_data_manager->main());
            }
        }
        $work_subcontroller = 'account';
        $has_result = true;
    }

    protected function FrontAction_login($REQUESTURIPATH)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
        $Login = new Login();
        $this->template->assert('main', $Login->main());
        if ($Login->getSessionUserId() > 0) {
            $this->template->assert('auth_menu', $Login->getAuthMenu());
        }
    }

    protected function FrontAction_register($REQUESTURIPATH)
    {
        if (!$this->getConfigValue('allow_register_account')) {
            $this->template->assert('main', 'Функция регистрации отключена администратором');
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
            $Register = new Register_Using_Model();
            $rs1 = $Register->main();
            $this->template->assert('main', $rs1);
        }
    }

    protected function FrontAction_remind($REQUESTURIPATH)
    {
        if (!$this->getConfigValue('allow_remind_password')) {
            $this->template->assert('main', 'Функция напоминания пароля отключена администратором');
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/user.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/remind.php');
            $remind = new Remind;
            $this->template->assert('main', $remind->main());
        }
    }

    protected function FrontAction_ipotekaorder($REQUESTURIPATH)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/ipoteka.php');
        //require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/form/local_ipoteka.php');
        //$ipoteka_order = new Local_Ipoteka_Order_Form();
        $ipoteka_order = new Ipoteka_Order_Form();
        $this->template->assert('main', $ipoteka_order->main());
    }

    protected function FrontAction_contactus($REQUESTURIPATH)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/contactus.php');
        $contactus_form = new contactus_Form();
        $this->template->assert('main', $contactus_form->main());
    }

    protected function FrontAction_logout($REQUESTURIPATH)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/logout.php');
        $Logout = new Logout;
        $Logout->main();
    }

    protected function FrontAction_grid_common()
    {
        $grid_constructor = $this->_getGridConstructor();
        $params = $this->gatherRequestParams();
        $grid_constructor->main($params);
    }

    protected function FrontAction_grid_user($REQUESTURIPATH, $info = array())
    {

        $user_id = intval($info['user_id']);
        if ($user_id == 0) {
            return $this->FrontAction_404($REQUESTURIPATH);
        } else {
            $fio = '';
            $DBC = DBC::getInstance();
            $query = 'SELECT fio FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1';
            $stmt = $DBC->query($query, array((int)$user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $fio = $ar['fio'];
            } else {
                return $this->FrontAction_404($REQUESTURIPATH);
            }
            $title = Multilanguage::_('AGENT_ADS', 'system') . ' ' . $fio;
            $meta_title = $title;

            if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
                if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                    if ($title != '') {
                        $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                    }
                    if ($meta_title != '') {
                        $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                    }
                }
            }

            $this->template->assert('title', $title);
            $this->template->assert('meta_title', $meta_title);

            $grid_constructor = $this->_getGridConstructor();
            $params = $this->gatherRequestParams();
            $params['user_id'] = $user_id;
            $grid_constructor->main($params);
        }
    }

    protected function FrontAction_myfavorites($REQUESTURIPATH, $info = array())
    {
        //$favorites=$_SESSION['favorites'];
        if (count($_SESSION['favorites']) != 0) {
            $grid_constructor = $this->_getGridConstructor();
            $params = $this->gatherRequestParams();
            $params['favorites'] = $_SESSION['favorites'];
            $grid_constructor->main($params);
        }
    }

    protected function FrontAction_404($REQUESTURIPATH)
    {
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
    }

    protected function FrontAction_grid_topic($REQUESTURIPATH, $topic_info)
    {
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();
            $lang_postfix = '_' . $curlang;
            if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                $lang_postfix = '';
            }
        }


        if (isset($topic_info['meta_title' . $lang_postfix]) && $topic_info['meta_title' . $lang_postfix] != '') {
            $meta_title = $topic_info['meta_title' . $lang_postfix];
        } elseif ($topic_info['meta_title'] != '') {
            $meta_title = $topic_info['meta_title'];
        } else {
            $meta_title = '';
        }

        if (isset($topic_info['name' . $lang_postfix]) && $topic_info['name' . $lang_postfix] != '') {
            $title = $topic_info['name' . $lang_postfix];
        } else {
            $title = $topic_info['name'];
        }

        if (isset($topic_info['public_title' . $lang_postfix]) && $topic_info['public_title' . $lang_postfix] != '') {
            $title = $topic_info['public_title' . $lang_postfix];
        } elseif (isset($topic_info['public_title']) && $topic_info['public_title'] != '') {
            $title = $topic_info['public_title'];
        }

        if ($meta_title == '') {
            $meta_title = $title;
        }

        if (isset($topic_info['description' . $lang_postfix]) && $topic_info['description' . $lang_postfix] != '') {
            $this->template->assert('description', $topic_info['description' . $lang_postfix]);
        } elseif ($topic_info['description'] != '') {
            $this->template->assert('description', $topic_info['description']);
        }
        if (isset($topic_info['meta_description' . $lang_postfix]) && $topic_info['meta_description' . $lang_postfix] != '') {
            $this->template->assert('meta_description', $topic_info['meta_description' . $lang_postfix]);
        } elseif ($topic_info['meta_description'] != '') {
            $this->template->assert('meta_description', $topic_info['meta_description']);
        }
        if (isset($topic_info['meta_keywords' . $lang_postfix]) && $topic_info['meta_keywords' . $lang_postfix] != '') {
            $this->template->assert('meta_keywords', $topic_info['meta_keywords' . $lang_postfix]);
        } elseif ($topic_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $topic_info['meta_keywords']);
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        $this->setRequestValue('topic_id', intval($topic_info['id']));
        $params['topic_id'] = intval($topic_info['id']);
        $grid_constructor->main($params);
    }

    protected function FrontAction_grid_citytopic($REQUESTURIPATH, $info)
    {

        $topic_info = $this->getTopicFullInfo($info[1]);
        $gorod_name = $info[2];

        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();
            $lang_postfix = '_' . $curlang;
            if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                $lang_postfix = '';
            }
        }


        if (isset($topic_info['meta_title' . $lang_postfix]) && $topic_info['meta_title' . $lang_postfix] != '') {
            $meta_title = $topic_info['meta_title' . $lang_postfix];
        } elseif ($topic_info['meta_title'] != '') {
            $meta_title = $topic_info['meta_title'];
        } else {
            $meta_title = '';
        }

        if (isset($topic_info['name' . $lang_postfix]) && $topic_info['name' . $lang_postfix] != '') {
            $title = $topic_info['name' . $lang_postfix];
        } else {
            $title = $topic_info['name'];
        }

        if (isset($topic_info['public_title' . $lang_postfix]) && $topic_info['public_title' . $lang_postfix] != '') {
            $title = $topic_info['public_title' . $lang_postfix];
        } elseif (isset($topic_info['public_title']) && $topic_info['public_title'] != '') {
            $title = $topic_info['public_title'];
        }

        if ($meta_title == '') {
            $meta_title = $title;
        }


        if (isset($topic_info['description' . $lang_postfix]) && $topic_info['description' . $lang_postfix] != '') {
            $this->template->assert('description', $topic_info['description' . $lang_postfix]);
        } elseif ($topic_info['description'] != '') {
            $this->template->assert('description', $topic_info['description']);
        }
        if (isset($topic_info['meta_description' . $lang_postfix]) && $topic_info['meta_description' . $lang_postfix] != '') {
            $this->template->assert('meta_description', $topic_info['meta_description' . $lang_postfix]);
        } elseif ($topic_info['meta_description'] != '') {
            $this->template->assert('meta_description', $topic_info['meta_description']);
        }
        if (isset($topic_info['meta_keywords' . $lang_postfix]) && $topic_info['meta_keywords' . $lang_postfix] != '') {
            $this->template->assert('meta_keywords', $topic_info['meta_keywords' . $lang_postfix]);
        } elseif ($topic_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $topic_info['meta_keywords']);
        }

        if ($gorod_name) {
            $title .= ' - ' . $gorod_name;
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        $this->setRequestValue('topic_id', intval($topic_info['id']));
        $params['topic_id'] = intval($topic_info['id']);
        $grid_constructor->main($params);

        //$this->setRequestValue('city_id', (int)$city_info['city_id']);
        //$this->setRequestValue('city_view', $REQUESTURIPATH);
    }

    protected function FrontAction_grid_predefined($REQUESTURIPATH, $predefined_info)
    {
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();
            $lang_postfix = '_' . $curlang;
            if (1 === intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang == 'ru') {
                $lang_postfix = '';
            }
        } else {
            $lang_postfix = '';
        }

        if ($lang_postfix != '') {
            foreach ($predefined_info as $k => $v) {
                if (isset($predefined_info[$k . $lang_postfix]) && $predefined_info[$k . $lang_postfix] != '') {
                    $predefined_info[$k] = $predefined_info[$k . $lang_postfix];
                }
            }
        }

        if (isset($predefined_info['meta_title' . $lang_postfix]) && $predefined_info['meta_title' . $lang_postfix] != '') {
            $meta_title = $predefined_info['meta_title' . $lang_postfix];
        } else {
            $meta_title = $predefined_info['meta_title'];
        }

        if (isset($predefined_info['title' . $lang_postfix]) && $predefined_info['title' . $lang_postfix] != '') {
            $title = $predefined_info['title' . $lang_postfix];
        } else {
            $title = $predefined_info['title'];
        }

        if ($meta_title == '') {
            $meta_title = $title;
        }

        if (intval($this->getRequestValue('page')) > 1 && 1 == $this->getConfigValue('add_pagenumber_title')) {
            if (0 == (int)$this->getConfigValue('add_pagenumber_title_place') && $title != '') {
                $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (1 == (int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title != '') {
                $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
            } elseif (2 == (int)$this->getConfigValue('add_pagenumber_title_place')) {
                if ($title != '') {
                    $title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
                if ($meta_title != '') {
                    $meta_title .= ' [' . Multilanguage::_('L_PAGE') . ' ' . intval($this->getRequestValue('page')) . ']';
                }
            }
        }

        $this->template->assert('title', $title);
        $this->template->assert('meta_title', $meta_title);

        if (isset($predefined_info['description' . $lang_postfix]) && $predefined_info['description' . $lang_postfix] != '') {
            $this->template->assert('description', $predefined_info['description' . $lang_postfix]);
        } elseif ($predefined_info['description'] != '') {
            $this->template->assert('description', $predefined_info['description']);
        }
        if (isset($predefined_info['meta_description' . $lang_postfix]) && $predefined_info['meta_description' . $lang_postfix] != '') {
            $this->template->assert('meta_description', $predefined_info['meta_description' . $lang_postfix]);
        } elseif ($predefined_info['meta_description'] != '') {
            $this->template->assert('meta_description', $predefined_info['meta_description']);
        } else {
            $this->template->assert('meta_description', $this->getConfigValue('meta_description_main'));
        }
        if (isset($predefined_info['meta_keywords' . $lang_postfix]) && $predefined_info['meta_keywords' . $lang_postfix] != '') {
            $this->template->assert('meta_keywords', $predefined_info['meta_keywords' . $lang_postfix]);
        } elseif ($predefined_info['meta_keywords'] != '') {
            $this->template->assert('meta_keywords', $predefined_info['meta_keywords']);
        } else {
            $this->template->assert('meta_keywords', $this->getConfigValue('meta_keywords_main'));
        }


        $this->setRequestValue('predefined_info', $predefined_info);

        $grid_constructor = $this->_getGridConstructor();

        $params = $this->gatherRequestParams();
        if (count($predefined_info['params']) > 0) {
            foreach ($predefined_info['params'] as $k => $v) {
                $this->setRequestValue($k, $v);
                $params[$k] = $v;
            }
        }
        //$this->setRequestValue('city_id', intval($city_info['city_id']));

        $grid_constructor->main($params);

        //$this->setRequestValue('city_id', (int)$city_info['city_id']);
        //$this->setRequestValue('city_view', $REQUESTURIPATH);
    }

    public function FrontAction_grid_custom($REQUESTURIPATH)
    {
        return false;
    }

    function FrontAction_isunderconstruct()
    {
        return false;
    }

    function FrontAction_yandexrealty_export()
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/nmarket/admin/admin.php') and 1 == $this->getConfigValue('apps.nmarket.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/nmarket/admin/admin.php');
            $nmarket = new nmarket_admin();
            echo $nmarket->export();
            exit;
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/rss/admin/admin.php')) {
            require(SITEBILL_DOCUMENT_ROOT . '/apps/rss/admin/admin.php');
            $rss = new rss_admin;
            echo $rss->export();
        }
        exit;
    }
}
