<?php
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
class SiteBill_Rent_Editor extends SiteBill_Krascap_Admin {

    private $adminPanelSettings = [];
    private $permissionManager = null;
    private $appsList = null;
    private $customEntites = null;


    function __construct()
    {
        parent::__construct();
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
        $this->permissionManager = new Permission();
        $this->initAdminPanelSettings();
    }

    /**
     * Return array of all apps
     * @return array
     */
    public function getAppList(){
        if(is_null($this->appsList)){
            $this->loadAppsList();
        }
        return $this->appsList;
    }

    /**
     * Return array of all custom entities
     * @return array
     */
    public function getCustomEntities(){
        if(is_null($this->customEntites)){
            $this->loadCustomEntities();
        }
        return $this->customEntites;
    }

    /**
     * Load app list
     * @return void
     */
    protected function loadAppsList(){
        $this->appsList = [];
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $this->appsList = $apps_processor->load_apps_menu(false, 'admin');
    }

    /**
     * Load custom entities list
     * @return void
     */
    protected function loadCustomEntities(){
        $this->customEntites = [];
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
            $this->customEntites = customentity_admin::getEntityList();
        }
    }

    /**
     * Init admin interface settings
     * @return void
     */
    protected function initAdminPanelSettings(){

        /** HOWTO Localize sidebar and top menu in admin panel
         * https://wiki.sitebill.ru/index.php?title=%D0%9D%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B0_%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D1%84%D0%B5%D0%B9%D1%81%D0%B0_%D0%B0%D0%B4%D0%BC%D0%B8%D0%BD-%D0%BF%D0%B0%D0%BD%D0%B5%D0%BB%D0%B8
         */

        $settings = [
            'sections' => [
                'data' => true, // Data link
                'structure' => true, // Structure/topics link
                'client' => true, // App Client link
                'references' => true, // Refereces block
                'references.country' => true, // Countries dictionary link
                'references.region' => true, // Regions dictionary link
                'references.city' => true, // Cities dictionary link
                'references.district' => true, // City districts dictionary link
                'references.metro' => true, // Metro dictionary link
                'references.street' => true, // Street dictionary link
                'components' => true, // Components block
                'content' => true, // Content block
                'content.news' => true, // App News link
                'content.page' => true, // App Page link
                'content.menu' => true, // App Menu link
                'config' => true, // App Config link
                'sitebill' => true, // App Update link
                'user' => true, // App User link
                'table' => true, // App Table link
                'recentapps' => true, // Recent apps list block
                'mobilephoto' => true, // Mobile photo app link
                'access' => true // Access block (Groups, Permissions etc)
            ],
            'knowlegebase' => true, // Knowlege base block on top menu (FAQ, Sitebill site etc)
            'gotosite' => true, // Go to frontend link on top menu
            'admin3' => true // Go to app Admin3 on top menu
        ];

        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/local/apps/admin/config.php')){
            $localconfig = include SITEBILL_DOCUMENT_ROOT.'/local/apps/admin/config.php';
            if(!empty($localconfig)){
                foreach ($localconfig as $name => $lcsection){
                    if(is_array($lcsection)){
                        if(!isset($settings[$name])){
                            $settings[$name] = [];
                        }
                        $settings[$name] = array_merge($settings[$name], $lcsection);
                    }else{
                        $settings[$name] = $lcsection;
                    }

                }
            }
        }
        $this->adminPanelSettings = $settings;
    }

    /**
     * Get not active adv count
     * @return string
     */
    function getNotActiveAdvCount() {
        $query = "select count(id) as cid from ".DB_PREFIX."_data where active=0";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			return $ar['cid'];
		}
		return 0;
    }

    /**
     * Return array with menu items for rabota.sitebill.ru
     * @param void
     * @return array
     */
    function getRabotaAdminMenu () {
        $menu['vacancy']['title'] = Multilanguage::_('L_ADMIN_MENU_VACANCIES');
        $menu['vacancy']['href'] = 'index.php?action=vacancy';
        if ( $_REQUEST['action'] == 'vacancy' ) {
            $menu['vacancy']['active'] = 1;
        }

        $menu['structure']['title'] = Multilanguage::_('L_ADMIN_MENU_SPECSPHERES');
        $menu['structure']['href'] = 'index.php?action=structure';
        if ( $_REQUEST['action'] == 'structure' ) {
            $menu['structure']['active'] = 1;
        }

            $menu_sub1['country']['title'] = Multilanguage::_('L_ADMIN_MENU_COUNTRIES');
            $menu_sub1['country']['href'] = 'index.php?action=country';
            if ( $_REQUEST['action'] == 'country' ) {
                $menu_sub1['country']['active'] = 1;
            }





            $menu_sub1['region']['title'] = Multilanguage::_('L_ADMIN_MENU_REGIONS');
            $menu_sub1['region']['href'] = 'index.php?action=region';
            if ( $_REQUEST['action'] == 'region' ) {
                $menu_sub1['region']['active'] = 1;
            }

            $menu_sub1['city']['title'] = Multilanguage::_('L_ADMIN_MENU_CITIES');
            $menu_sub1['city']['href'] = 'index.php?action=city';
            if ( $_REQUEST['action'] == 'city' ) {
                $menu_sub1['city']['active'] = 1;
            }

            $menu_sub1['district']['title'] = Multilanguage::_('L_ADMIN_MENU_DISTRICTS');
            $menu_sub1['district']['href'] = 'index.php?action=district';
            if ( $_REQUEST['action'] == 'district' ) {
                $menu_sub1['district']['active'] = 1;
            }

            $menu_sub1['metro']['title'] = Multilanguage::_('L_ADMIN_MENU_METRO');
            $menu_sub1['metro']['href'] = 'index.php?action=metro';
            if ( $_REQUEST['action'] == 'metro' ) {
                $menu_sub1['metro']['active'] = 1;
            }

            $menu_sub1['street']['title'] = Multilanguage::_('L_ADMIN_MENU_STREETS');
            $menu_sub1['street']['href'] = 'index.php?action=street';
            if ( $_REQUEST['action'] == 'street' ) {
                $menu_sub1['street']['active'] = 1;
            }
        $menu['references']['title'] = Multilanguage::_('L_ADMIN_MENU_REFERENCES');
        $menu['references']['href'] = 'index.php?action=country';
        $menu['references']['childs'] = $menu_sub1;
        if ( $_REQUEST['action'] == 'references' ) {
            $menu['references']['active'] = 1;
        }
        $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
        $menu_sub3['news']['href'] = 'index.php?action=news';
        if ( $_REQUEST['action'] == 'news' ) {
            $menu_sub3['news']['active'] = 1;
        }

        $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu_sub3['page']['href'] = 'index.php?action=page';
        if ( $_REQUEST['action'] == 'page' ) {
            $menu_sub3['page']['active'] = 1;
        }

        $menu['content']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu['content']['href'] = 'index.php?action=page';
        $menu['content']['childs'] = $menu_sub3;
        if ( $_REQUEST['action'] == 'content' ) {
            $menu['content']['active'] = 1;
        }

        $menu['user']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
        $menu['user']['href'] = 'index.php?action=user';
        if ( $_REQUEST['action'] == 'user' ) {
            $menu['user']['active'] = 1;
        }

        $menu['menu']['title'] = Multilanguage::_('L_ADMIN_MENU_MENUS');
        $menu['menu']['href'] = 'index.php?action=menu';
        if ( $_REQUEST['action'] == 'menu' ) {
            $menu['menu']['active'] = 1;
        }

        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $menu['apps']['title'] = Multilanguage::_('L_ADMIN_MENU_APPLICATIONS');
        $menu['apps']['href'] = 'index.php?action=apps';
        $menu['apps']['childs'] = $apps_processor->load_apps_menu();
        if ( $_REQUEST['action'] == 'apps' ) {
            $menu['apps']['active'] = 1;
        }

        $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
        $menu['config']['href'] = 'index.php?action=config';
        if ( $_REQUEST['action'] == 'config' ) {
            $menu['config']['active'] = 1;
        }

        $menu['updater']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
        $menu['updater']['href'] = 'index.php?action=updater';
        if ( $_REQUEST['action'] == 'updater' ) {
            $menu['updater']['active'] = 1;
        }

        $menu['site']['title'] = Multilanguage::_('L_ADMIN_MENU_GOTOSITE');
        $menu['site']['href'] = SITEBILL_MAIN_URL.'/';
        $menu['site']['target'] = '_blank';
        if ( $_REQUEST['action'] == 'site' ) {
            $menu['site']['active'] = 1;
        }



        return $this->compile_menu($menu);
    }

    /**
     * Return array with menu items for orders.sitebill.ru
     * @param void
     * @return array
     */
    function getOrdersAdminMenu () {
        $menu['zapros']['title'] = Multilanguage::_('L_ADMIN_MENU_DEMANDS');
        $menu['zapros']['href'] = 'index.php?action=zapros';
        if ( $_REQUEST['action'] == 'zapros' ) {
            $menu['zapros']['active'] = 1;
        }
        $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
        $menu_sub3['news']['href'] = 'index.php?action=news';
        if ( $_REQUEST['action'] == 'news' ) {
            $menu_sub3['news']['active'] = 1;
        }

        $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu_sub3['page']['href'] = 'index.php?action=page';
        if ( $_REQUEST['action'] == 'page' ) {
            $menu_sub3['page']['active'] = 1;
        }

        $menu['content']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu['content']['href'] = 'index.php?action=page';
        $menu['content']['childs'] = $menu_sub3;
        if ( $_REQUEST['action'] == 'content' ) {
            $menu['content']['active'] = 1;
        }

        $menu['user']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
        $menu['user']['href'] = 'index.php?action=user';
        if ( $_REQUEST['action'] == 'user' ) {
            $menu['user']['active'] = 1;
        }

        $menu['menu']['title'] = Multilanguage::_('L_ADMIN_MENU_MENUS');
        $menu['menu']['href'] = 'index.php?action=menu';
        if ( $_REQUEST['action'] == 'menu' ) {
            $menu['menu']['active'] = 1;
        }

            $menu_sub1['country']['title'] = Multilanguage::_('L_ADMIN_MENU_COUNTRIES');
            $menu_sub1['country']['href'] = 'index.php?action=country';
            if ( $_REQUEST['action'] == 'country' ) {
                $menu_sub1['country']['active'] = 1;
            }

            $menu_sub1['region']['title'] = Multilanguage::_('L_ADMIN_MENU_REGIONS');
            $menu_sub1['region']['href'] = 'index.php?action=region';
            if ( $_REQUEST['action'] == 'region' ) {
                $menu_sub1['region']['active'] = 1;
            }

            $menu_sub1['city']['title'] = Multilanguage::_('L_ADMIN_MENU_CITIES');
            $menu_sub1['city']['href'] = 'index.php?action=city';
            if ( $_REQUEST['action'] == 'city' ) {
                $menu_sub1['city']['active'] = 1;
            }

            $menu_sub1['district']['title'] = Multilanguage::_('L_ADMIN_MENU_DISTRICTS');
            $menu_sub1['district']['href'] = 'index.php?action=district';
            if ( $_REQUEST['action'] == 'district' ) {
                $menu_sub1['district']['active'] = 1;
            }

            $menu_sub1['metro']['title'] = Multilanguage::_('L_ADMIN_MENU_METRO');
            $menu_sub1['metro']['href'] = 'index.php?action=metro';
            if ( $_REQUEST['action'] == 'metro' ) {
                $menu_sub1['metro']['active'] = 1;
            }

            $menu_sub1['street']['title'] = Multilanguage::_('L_ADMIN_MENU_STREETS');
            $menu_sub1['street']['href'] = 'index.php?action=street';
            if ( $_REQUEST['action'] == 'street' ) {
                $menu_sub1['street']['active'] = 1;
            }

        $menu['references']['title'] = Multilanguage::_('L_ADMIN_MENU_REFERENCES');
        $menu['references']['href'] = 'index.php?action=country';
        $menu['references']['childs'] = $menu_sub1;
        if ( $_REQUEST['action'] == 'references' ) {
            $menu['references']['active'] = 1;
        }

        $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
        $menu['config']['href'] = 'index.php?action=config';
        if ( $_REQUEST['action'] == 'config' ) {
            $menu['config']['active'] = 1;
        }

        $menu['updater']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
        $menu['updater']['href'] = 'index.php?action=updater';
        if ( $_REQUEST['action'] == 'updater' ) {
            $menu['updater']['active'] = 1;
        }

        $menu['site']['title'] = Multilanguage::_('L_ADMIN_MENU_GOTOSITE');
        $menu['site']['href'] = SITEBILL_MAIN_URL.'/';
        $menu['site']['target'] = '_blank';
        if ( $_REQUEST['action'] == 'site' ) {
            $menu['site']['active'] = 1;
        }



        return $this->compile_menu($menu);
    }


    /**
     * Return array with menu items for shop.sitebill.ru
     * @param void
     * @return array
     */
    function getShopAdminMenu () {
    	//$menu['product']['title'] = 'Товары';
    	//$MD=Multilanguage::getInstance();
    	$menu['product']['title'] = Multilanguage::_('L_ADMIN_MENU_PRODUCTS');
        $menu['product']['href'] = 'index.php?action=product';
        if ( $_REQUEST['action'] == 'product' ) {
           $menu['product']['active'] = 1;
        }

    	$menu['shop_order']['title'] = Multilanguage::_('L_ADMIN_MENU_ORDERS');
        $menu['shop_order']['href'] = 'index.php?action=shop_order';
        if ( $_REQUEST['action'] == 'shop_order' ) {
           $menu['shop_order']['active'] = 1;
        }

        $menu_sub1['city']['title'] = Multilanguage::_('L_ADMIN_MENU_CITIES');
        $menu_sub1['city']['href'] = 'index.php?action=city';
        if ( $_REQUEST['action'] == 'city' ) {
        	$menu_sub1['city']['active'] = 1;
        }

        $menu['structure']['title'] = Multilanguage::_('L_ADMIN_MENU_CATEGORIES');
        $menu['structure']['href'] = 'index.php?action=structure';
        if ( $_REQUEST['action'] == 'structure' ) {
            $menu['structure']['active'] = 1;
        }

        $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
        $menu_sub3['news']['href'] = 'index.php?action=news';
        if ( $_REQUEST['action'] == 'news' ) {
            $menu_sub3['news']['active'] = 1;
        }

        $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu_sub3['page']['href'] = 'index.php?action=page';
        if ( $_REQUEST['action'] == 'page' ) {
            $menu_sub3['page']['active'] = 1;
        }

        $menu['content']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu['content']['href'] = 'index.php?action=page';
        $menu['content']['childs'] = $menu_sub3;
        if ( $_REQUEST['action'] == 'content' ) {
            $menu['content']['active'] = 1;
        }

        $menu['references']['title'] = Multilanguage::_('L_ADMIN_MENU_REFERENCES');
        $menu['references']['href'] = 'index.php?action=country';
        $menu['references']['childs'] = $menu_sub1;
        if ( $_REQUEST['action'] == 'references' ) {
        	$menu['references']['active'] = 1;
        }


        $menu['user']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
        $menu['user']['href'] = 'index.php?action=user';
        if ( $_REQUEST['action'] == 'user' ) {
            $menu['user']['active'] = 1;
        }

        $menu_sub_group['component']['title'] = Multilanguage::_('L_ADMIN_MENU_COMPONENTS');
        $menu_sub_group['component']['href'] = 'index.php?action=component';
        if ( $_REQUEST['action'] == 'component' ) {
            $menu_sub_group['component']['active'] = 1;
        }

        $menu_sub_group['function']['title'] = Multilanguage::_('L_ADMIN_MENU_FUNCTIONS');
        $menu_sub_group['function']['href'] = 'index.php?action=function';
        if ( $_REQUEST['action'] == 'function' ) {
            $menu_sub_group['function']['active'] = 1;
        }

        $menu['group']['title'] = Multilanguage::_('L_ADMIN_MENU_GROUPS');
        $menu['group']['href'] = 'index.php?action=group';
        $menu['group']['childs'] = $menu_sub_group;
        if ( $_REQUEST['action'] == 'group' ) {
            $menu['group']['active'] = 1;
        }


        $menu['menu']['title'] = Multilanguage::_('L_ADMIN_MENU_MENUS');
        $menu['menu']['href'] = 'index.php?action=menu';
        if ( $_REQUEST['action'] == 'menu' ) {
            $menu['menu']['active'] = 1;
        }

        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $menu['apps']['title'] = Multilanguage::_('L_ADMIN_MENU_APPLICATIONS');
        $menu['apps']['href'] = 'index.php?action=apps';
        $menu['apps']['childs'] = $apps_processor->load_apps_menu();
        if ( $_REQUEST['action'] == 'apps' ) {
            $menu['apps']['active'] = 1;
        }

        $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
        $menu['config']['href'] = 'index.php?action=config';
        if ( $_REQUEST['action'] == 'config' ) {
            $menu['config']['active'] = 1;
        }

        $menu['updater']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
        $menu['updater']['href'] = 'index.php?action=updater';
        if ( $_REQUEST['action'] == 'updater' ) {
            $menu['updater']['active'] = 1;
        }

        $menu['site']['title'] = Multilanguage::_('L_ADMIN_MENU_GOTOSITE');
        $menu['site']['href'] = SITEBILL_MAIN_URL.'/';
        $menu['site']['target'] = '_blank';
        if ( $_REQUEST['action'] == 'site' ) {
            $menu['site']['active'] = 1;
        }



        return $this->compile_menu($menu);
    }

    /**
     * Return array with menu items for shop.sitebill.ru
     * @param void
     * @return array
     */
    function getSitebillAdminMenu () {
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $menu['structure']['title'] = Multilanguage::_('L_ADMIN_MENU_CATEGORIES');
        $menu['structure']['href'] = 'index.php?action=structure';
        if ( $_REQUEST['action'] == 'structure' ) {
            $menu['structure']['active'] = 1;
        }

        $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
        $menu_sub3['news']['href'] = 'index.php?action=news';
        if ( $_REQUEST['action'] == 'news' ) {
            $menu_sub3['news']['active'] = 1;
        }

        $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu_sub3['page']['href'] = 'index.php?action=page';
        if ( $_REQUEST['action'] == 'page' ) {
            $menu_sub3['page']['active'] = 1;
        }

        $menu['content']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu['content']['href'] = 'index.php?action=page';
        $menu['content']['childs'] = $menu_sub3;
        if ( $_REQUEST['action'] == 'content' ) {
            $menu['content']['active'] = 1;
        }

        $menu['user']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
        $menu['user']['href'] = 'index.php?action=user';
        if ( $_REQUEST['action'] == 'user' ) {
            $menu['user']['active'] = 1;
        }

        $menu['menu']['title'] = Multilanguage::_('L_ADMIN_MENU_MENUS');
        $menu['menu']['href'] = 'index.php?action=menu';
        if ( $_REQUEST['action'] == 'menu' ) {
            $menu['menu']['active'] = 1;
        }

        $menu['apps']['title'] = Multilanguage::_('L_ADMIN_MENU_APPLICATIONS');
        $menu['apps']['href'] = 'index.php?action=apps';
        $menu['apps']['childs'] = $apps_processor->load_apps_menu();
        if ( $_REQUEST['action'] == 'apps' ) {
            $menu['apps']['active'] = 1;
        }

        $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
        $menu['config']['href'] = 'index.php?action=config';
        if ( $_REQUEST['action'] == 'config' ) {
            $menu['config']['active'] = 1;
        }

        $menu['updater']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
        $menu['updater']['href'] = 'index.php?action=updater';
        if ( $_REQUEST['action'] == 'updater' ) {
            $menu['updater']['active'] = 1;
        }

        $menu['site']['title'] = Multilanguage::_('L_ADMIN_MENU_GOTOSITE');
        $menu['site']['href'] = SITEBILL_MAIN_URL.'/';
        $menu['site']['target'] = '_blank';
        if ( $_REQUEST['action'] == 'site' ) {
            $menu['site']['active'] = 1;
        }



        return $this->compile_menu($menu);
    }

    /**
     * Compile dropdown menu from array
     * @param array $menu
     * @return string
     */
    function compile_menu ( $menu ) {
        foreach ( $menu as $menu_key => $menu_item ) {
            $menu_string .= '<li><a class="mainlevel-son-of-suckerfish-horizontal" href="'.$menu_item['href'].'" target="'.$menu_item['target'].'"><span>'.$menu_item['title'].'</span></a>';
            if ( is_array($menu_item['childs'])  ) {
                //print_r($menu_item['childs']);
                $menu_string .= '<ul id="menulist_10-son-of-suckerfish-horizontal"><li class="submenu_top"></li>';
                foreach ( $menu_item['childs'] as $child_id => $child_array ) {
                    $menu_string .= '<li><a href="'.$child_array['href'].'" class="sublevel-son-of-suckerfish-horizontal"><span>'.$child_array['title'].'</span></a> </li>';
                }
                $menu_string .= '<li class="submenu_bottom"></li>';
                $menu_string .= '</ul>';
            }
            $menu_string .= '</li>';
        }

        return $menu_string;
    }

    /**
     * Get admin menu
     * @param void
     * @return string
     */
    function getAdminMenu () {

        $menu=$this->getAdminMenuArray();


        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
        $permission = new Permission();

        $menu_string='';
        //echo $_SESSION['user_id_value'].'<br>';
        foreach ( $menu as $menu_key => $menu_item ) {

        	if ( !$permission->get_access($_SESSION['user_id_value'], $menu_key, 'access') and $this->getConfigValue('check_permissions') ) {
        		//echo 'access deny '.$menu_key.'<br>';
        		continue;
        	}

            $menu_string .= '<li><a class="mainlevel-son-of-suckerfish-horizontal" href="'.$menu_item['href'].'" target="'.(isset($menu_item['target']) ? $menu_item['target'] : '').'"><span>'.$menu_item['title'].'</span></a>';
            if ( isset($menu_item['childs']) AND is_array($menu_item['childs'])  ) {
                //print_r($menu_item['childs']);
                $menu_string .= '<ul id="menulist_10-son-of-suckerfish-horizontal"><li class="submenu_top"></li>';
                foreach ( $menu_item['childs'] as $child_id => $child_array ) {
                	//echo "insert into re_component (name) values ('{$child_id}');<br>";

                	if ( !$permission->get_access($_SESSION['user_id_value'], $child_id, 'access') and $this->getConfigValue('check_permissions') ) {
                		continue;
                	}
                    if ( is_array($child_array) and isset($child_array['href']) and isset($child_array['title']) ) {
                        $menu_string .= '<li><a href="'.$child_array['href'].'" class="sublevel-son-of-suckerfish-horizontal"><span>'.$child_array['title'].'</span></a> </li>';
                    }

                }
                $menu_string .= '<li class="submenu_bottom"></li>';
                $menu_string .= '</ul>';
            }
            $menu_string .= '</li>';
        }
        return $menu_string;
    }

    /**
     * Get admin menu
     * @param void
     * @return string
     */
    function getSimpleAdminMenu () {
        $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
        $menu_sub3['news']['href'] = 'index.php?action=news';
        if ( $_REQUEST['action'] == 'news' ) {
            $menu_sub3['news']['active'] = 1;
        }

        $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu_sub3['page']['href'] = 'index.php?action=page';
        if ( $_REQUEST['action'] == 'page' ) {
            $menu_sub3['page']['active'] = 1;
        }


        $menu['content']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu['content']['href'] = 'index.php?action=page';
        $menu['content']['childs'] = $menu_sub3;
        if ( $_REQUEST['action'] == 'content' ) {
            $menu['content']['active'] = 1;
        }


        $menu['users']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
        $menu['users']['href'] = 'index.php?action=users';
        if ( $_REQUEST['action'] == 'users' ) {
            $menu['users']['active'] = 1;
        }

        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $menu['apps']['title'] = Multilanguage::_('L_ADMIN_MENU_APPLICATIONS');
        $menu['apps']['href'] = 'index.php?action=apps';
        $menu['apps']['childs'] = $apps_processor->load_apps_menu();
        if ( $_REQUEST['action'] == 'apps' ) {
            $menu['apps']['active'] = 1;
        }

        $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
        $menu['config']['href'] = 'index.php?action=config';
        if ( $_REQUEST['action'] == 'config' ) {
            $menu['config']['active'] = 1;
        }

        $menu['updater']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
        $menu['updater']['href'] = 'index.php?action=updater';
        if ( $_REQUEST['action'] == 'updater' ) {
            $menu['updater']['active'] = 1;
        }

        $menu['site']['title'] = Multilanguage::_('L_ADMIN_MENU_GOTOSITE');
        $menu['site']['href'] = SITEBILL_MAIN_URL.'/';
        $menu['site']['target'] = '_blank';
        if ( $_REQUEST['action'] == 'site' ) {
            $menu['site']['active'] = 1;
        }

        foreach ( $menu as $menu_key => $menu_item ) {
            $menu_string .= '<li><a class="mainlevel-son-of-suckerfish-horizontal" href="'.$menu_item['href'].'" target="'.$menu_item['target'].'"><span>'.$menu_item['title'].'</span></a>';
            if ( count($menu_item['childs']) > 0  ) {
                //print_r($menu_item['childs']);
                $menu_string .= '<ul id="menulist_10-son-of-suckerfish-horizontal"><li class="submenu_top"></li>';
                foreach ( $menu_item['childs'] as $child_id => $child_array ) {
                    $menu_string .= '<li><a href="'.$child_array['href'].'" class="sublevel-son-of-suckerfish-horizontal"><span>'.$child_array['title'].'</span></a> </li>';
                }
                $menu_string .= '<li class="submenu_bottom"></li>';
                $menu_string .= '</ul>';
            }
            $menu_string .= '</li>';
        }

        return $menu_string;
    }

    public function getAdminMenuArray($admin_path = 'admin'){
        $menu = array();

        //$topics_print = "<li><a href='index.php?action=adv_moderator'>Объявления для модерации (".$this->getNotActiveAdvCount().")</a></li>\n";
        //$topics_print .= "<li><a href='reqlist.php'><b>Заявки</b></a></li>\n";
        /*
         $menu['land']['title'] = 'Участки (pro)';
        $menu['land']['href'] = 'index.php?action=land';
        */

        $menu['data']['title'] = Multilanguage::_('L_ADMIN_MENU_AUTOADVERTS');
        $menu['data']['href'] = 'index.php?action=data';
        $menu['data']['icon'] = 'fa fa-book';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'data') ) {
            $menu['data']['active'] = 1;
        }



        $menu_sub4['data']['title'] = Multilanguage::_('L_ADMIN_MENU_ESTATEDATA');
        $menu_sub4['data']['href'] = 'index.php?action=data';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'data') ) {
            $menu_sub4['data']['active'] = 1;
        }

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php';
        $DM=new Data_Manager();

        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);
        $statuses=array();
        if(isset($model['data']['status_id'])){
            if($model['data']['status_id']['type']=='select_box'){
                foreach($model['data']['status_id']['select_data'] as $k=>$v){
                    $statuses[$k]=$v;
                }
            }
        }

        /*$statuses2=array();
        if(isset($model['data']['optype'])){
            if($model['data']['optype']['type']=='select_box'){
                foreach($model['data']['optype']['select_data'] as $k=>$v){
                    $statuses2[$k]=$v;
                }
            }
        }*/

        $stat_params=array();
        if(!empty($statuses)){
            $stat_params[]='status_id';
        }
        /*if(!empty($statuses2)){
            $stat_params[]='optype';
        }*/

        $stat=$DM->getDataStatInfo($stat_params);

        $menu_sub5['sdata_1']['title'] = 'Все ('.intval($stat['total']).')';
        $menu_sub5['sdata_1']['href'] = SITEBILL_MAIN_URL.'/admin?action=data';

        if(!empty($statuses)){
            foreach($statuses as $k=>$v){
                $menu_sub5[]=array('title'=>$v.' ('.intval($stat['status']['status_id'][$k]).')', 'href'=>SITEBILL_MAIN_URL.'/admin?action=data&status_id='.$k);
            }
        }

        /*if(!empty($statuses2)){
            foreach($statuses2 as $k=>$v){
                $menu_sub5[]=array('title'=>$v.' ('.intval($stat['status']['optype'][$k]).')', 'href'=>SITEBILL_MAIN_URL.'/admin/?action=data&optype='.$k);
            }
        }*/

        /*
        $menu_sub5['sdata_1']['title'] = 'Актуальные ('.intval($stat['status'][1]).')';
        $menu_sub5['sdata_1']['href'] = SITEBILL_MAIN_URL.'/admin/?action=data&status_id=1';

        $menu_sub5['sdata_2']['title'] = 'На прозвон ('.intval($stat['status'][2]).')';
        $menu_sub5['sdata_2']['href'] = SITEBILL_MAIN_URL.'/admin/?action=data&status_id=2';

        $menu_sub5['sdata_3']['title'] = 'Не дозвонились ('.intval($stat['status'][3]).')';
        $menu_sub5['sdata_3']['href'] = SITEBILL_MAIN_URL.'/admin/?action=data&status_id=3';
        */
        $menu_sub5['sdata_4']['title'] = 'Модерация ('.intval($stat['active'][0]).')';
        $menu_sub5['sdata_4']['href'] = SITEBILL_MAIN_URL.'/admin?action=data&active=notactive';

        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php') && $this->getConfigValue('apps.realtylogv2.enable')==1){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
            $RL=new realtylogv2_admin();

            $menu_sub5['sdata_5']['title'] = 'Архив ('.$RL->getDeletedCount().')';
            $menu_sub5['sdata_5']['href'] = SITEBILL_MAIN_URL.'/admin?action=realtylogv2';
        }

        $menu_sub5['sdata_6']['title'] = 'Активные ('.intval($stat['active'][1]).')';
        $menu_sub5['sdata_6']['href'] = SITEBILL_MAIN_URL.'/admin?action=data&active=1';

        $menu_sub5['sdata_7']['title'] = 'Неактивные ('.intval($stat['active'][0]).')';
        $menu_sub5['sdata_7']['href'] = SITEBILL_MAIN_URL.'/admin?action=data&active=notactive';

        $menu_sub4['data']['childs'] = $menu_sub5;


        $menu_sub4['structure']['title'] = Multilanguage::_('L_ADMIN_MENU_STRUCTURE');
        $menu_sub4['structure']['href'] = 'index.php?action=structure';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'structure') ) {
            $menu_sub4['structure']['active'] = 1;
        }

        $menu['datamain']['title'] = Multilanguage::_('L_ADMIN_MENU_ADVERTS');
        $menu['datamain']['href'] = 'index.php?action=data';
        $menu['datamain']['childs'] = $menu_sub4;
        $menu['datamain']['icon'] = 'fa fa-book';

        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'datamain') ) {
            $menu['datamain']['active'] = 1;
        }

        $menu['client']['title'] = Multilanguage::_('L_CLIENT_MENU');
        $menu['client']['href'] = 'index.php?action=client';
        $menu['client']['icon'] = 'icon-ace-icon fa fa-heart bigger-125';

        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'client') ) {
            $menu['client']['active'] = 1;
        }

        $menu['references']['title'] = Multilanguage::_('L_ADMIN_MENU_REFERENCES');
        $menu['references']['href'] = 'index.php?action=country';
        $menu['references']['icon'] = 'icon-globe';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'references') ) {
            $menu['references']['active'] = 1;
        }
        $menu_sub1['country']['title'] = Multilanguage::_('L_ADMIN_MENU_COUNTRIES');
        $menu_sub1['country']['href'] = 'index.php?action=country';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'country') ) {
            $menu['references']['active'] = 1;
            $menu_sub1['country']['active'] = 1;
        }

        $menu_sub1['region']['title'] = Multilanguage::_('L_ADMIN_MENU_REGIONS');
        $menu_sub1['region']['href'] = 'index.php?action=region';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'region') ) {
            $menu['references']['active'] = 1;
            $menu_sub1['region']['active'] = 1;
        }

        $menu_sub1['city']['title'] = Multilanguage::_('L_ADMIN_MENU_CITIES');
        $menu_sub1['city']['href'] = 'index.php?action=city';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'city') ) {
            $menu['references']['active'] = 1;
            $menu_sub1['city']['active'] = 1;
        }

        $menu_sub1['district']['title'] = Multilanguage::_('L_ADMIN_MENU_DISTRICTS');
        $menu_sub1['district']['href'] = 'index.php?action=district';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'district') ) {
            $menu['references']['active'] = 1;
            $menu_sub1['district']['active'] = 1;
        }

        $menu_sub1['metro']['title'] = Multilanguage::_('L_ADMIN_MENU_METRO');
        $menu_sub1['metro']['href'] = 'index.php?action=metro';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'metro') ) {
            $menu['references']['active'] = 1;
            $menu_sub1['metro']['active'] = 1;
        }

        $menu_sub1['street']['title'] = Multilanguage::_('L_ADMIN_MENU_STREETS');
        $menu_sub1['street']['href'] = 'index.php?action=street';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'street') ) {
            $menu['references']['active'] = 1;
            $menu_sub1['street']['active'] = 1;
        }
        $menu['references']['childs'] = $menu_sub1;



        $menu['content']['title'] = Multilanguage::_('L_CONTENT_MENU');
        $menu['content']['href'] = 'index.php?action=page';
        $menu['content']['icon'] = 'icon-coffee';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'content') ) {
            $menu['content']['active'] = 1;
        }
        $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
        $menu_sub3['news']['href'] = 'index.php?action=news';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'news') ) {
            $menu_sub3['news']['active'] = 1;
            $menu['content']['active'] = 1;
        }

        $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
        $menu_sub3['page']['href'] = 'index.php?action=page';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'page') ) {
            $menu_sub3['page']['active'] = 1;
            $menu['content']['active'] = 1;
        }

        $menu_sub3['menu']['title'] = Multilanguage::_('L_ADMIN_MENU_MENUS');
        $menu_sub3['menu']['href'] = 'index.php?action=menu';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'menu') ) {
            $menu_sub3['menu']['active'] = 1;
            $menu['content']['active'] = 1;
        }
        $menu['content']['childs'] = $menu_sub3;



        $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
        $menu['config']['href'] = 'index.php?action=config';
        $menu['config']['icon'] = 'icon-cog';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'config') ) {
            $menu['config']['active'] = 1;
        }

        $menu['sitebill']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
        $menu['sitebill']['href'] = 'index.php?action=sitebill';
        $menu['sitebill']['icon'] = 'fa fa-sync';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'sitebill') ) {
            $menu['sitebill']['active'] = 1;
        }

        $menu['user']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
        $menu['user']['href'] = 'index.php?action=user';
        $menu['user']['icon'] = 'icon-user';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'user') ) {
            $menu['user']['active'] = 1;
        }

        $menu['structure']['title'] = Multilanguage::_('L_ADMIN_MENU_STRUCTURE');
        $menu['structure']['href'] = 'index.php?action=structure';
        $menu['structure']['icon'] = 'icon-th-list';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'structure') ) {
            $menu['structure']['active'] = 1;
        }

        $menu['table']['title'] = Multilanguage::_('L_TABLE_MENU');
        $menu['table']['href'] = 'index.php?action=table';
        $menu['table']['icon'] = 'fa fa-edit';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'table') ) {
            $menu['table']['active'] = 1;
        }

        $menu_sub_group['group']['title'] = Multilanguage::_('L_ADMIN_MENU_GROUPS');
        $menu_sub_group['group']['href'] = 'index.php?action=group';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'group') ) {
            $menu_sub_group['group']['active'] = 1;
            $menu['access']['active'] = 1;
        }

        $menu_sub_group['component']['title'] = Multilanguage::_('L_ADMIN_MENU_COMPONENTS');
        $menu_sub_group['component']['href'] = 'index.php?action=component';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'component') ) {
            $menu_sub_group['component']['active'] = 1;
            $menu['access']['active'] = 1;
        }

        $menu_sub_group['function']['title'] = Multilanguage::_('L_ADMIN_MENU_FUNCTIONS');
        $menu_sub_group['function']['href'] = 'index.php?action=function';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'function') ) {
            $menu_sub_group['function']['active'] = 1;
            $menu['access']['active'] = 1;
        }

        $menu['access']['title'] = Multilanguage::_('L_ADMIN_MENU_ACCESS');
        $menu['access']['href'] = '';
        $menu['access']['childs'] = $menu_sub_group;
        $menu['access']['icon'] = 'fa fa-group';




        /*Компоненты*/


        $menu_sub101=array();
        if(1==$this->getConfigValue('enable_curator_mode')){
            $menu_sub101['cowork']['title'] = 'Куратор';
            $menu_sub101['cowork']['href'] = 'index.php?action=cowork';
            if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'cowork') ) {
                $menu['components']['cowork'] = 1;
                //$menu_sub101['cowork']['active'] = 1;
            }
        }

        if(!empty($menu_sub101)){
            $menu['components']['title'] = Multilanguage::_('L_ADMIN_MENU_EXTCOMPONENTS');
            $menu['components']['href'] = '#';

            if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'components') ) {
                $menu['components']['active'] = 1;
            }

            $menu['components']['childs'] = $menu_sub101;
        }

        $menus_from_db = array();

        if ( class_exists('\menu\api\menu') ) {
            $menu_api = new \menu\api\menu();
            $menus_from_db = $menu_api->load_menus();
            if ( $menus_from_db['admin'] ) {
                $menu = $menus_from_db['admin'];
            }
        }

        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $menu['apps']['title'] = Multilanguage::_('L_ADMIN_MENU_APPLICATIONS');
        $menu['apps']['href'] = 'index.php?action=apps';
        if ( $menus_from_db['apps'] ) {
            $apps_array = $menus_from_db['apps'];
        } else {
            $apps_array = $apps_processor->load_apps_menu(false, $admin_path);
        }

        $menu['apps']['childs'] = $apps_array;
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'apps') ) {
            $menu['apps']['active'] = 1;
        }

        $menu['nested_apps'] = $this->make_nested_apps_array($apps_array);

        $menu['site']['title'] = Multilanguage::_('L_ADMIN_MENU_GOTOSITE');
        $menu['site']['href'] = SITEBILL_MAIN_URL.'/';
        $menu['site']['target'] = '_blank';
        if ( isset($_REQUEST['action']) AND ($_REQUEST['action'] == 'site') ) {
            $menu['site']['active'] = 1;
        }

        if(isset($_SESSION['recently_apps']) && is_array($_SESSION['recently_apps']) && !empty($_SESSION['recently_apps'])){
            $menu['recentapps']['title'] = Multilanguage::_('L_ADMIN_MENU_RECENTAPPS');
            $menu['recentapps']['childs'] = $_SESSION['recently_apps'];
        }

        return $menu;
    }



    private function make_nested_apps_array ($apps_array) {
        $ra = array();
        $uncategorized = array();
        $uncategorizedtitle = 'Прочее';
        $uncategorizedid = 0;
        foreach ($apps_array as $app_name => $item) {
            if ( !empty($item['params']['category']) ) {
                $ra[$item['params']['category']]['title'] = $item['params']['category'];
                $ra[$item['params']['category']]['childs'][$app_name] = $item;
            } else {
                $uncategorized[$app_name] = $item;
            }
        }
        if(!empty($ra) && !empty($uncategorized)){
            $ra[$uncategorizedid]['title'] = $uncategorizedtitle;
            $ra[$uncategorizedid]['childs'] = $uncategorized;
        }
        return $ra;
    }


    /**
     * Get additional menu
     * @param void
     * @return string
     */
    function getAdditionalMenu () {
        $rs = '<a href="?action=street">'.Multilanguage::_('L_ADMIN_MENU_STREETS').'</a><br>';
        $rs .= '<a href="?action=district">'.Multilanguage::_('L_ADMIN_MENU_DISTRICTS').'</a><br>';
        return $rs;
    }

    /**
     * Create link for admin panel
     * @param string $path
     * @return string
     */
    protected function buildAdminUrl($path){
        return $this->createUrlTpl($path, false, true);
    }

    /**
     * Return array of recently used apps
     * @return array
     */
    public function getRecentAppsMenu(){
        $menu = [];
        if(isset($_SESSION['recently_apps']) && is_array($_SESSION['recently_apps']) && !empty($_SESSION['recently_apps'])){
            $menu['title'] = Multilanguage::_('L_ADMIN_MENU_RECENTAPPS');
            $menu['childs'] = $_SESSION['recently_apps'];
        }
        return $menu;
    }

    /**
     * Create Knowlege base menu array
     * @return array
     */
    public function getAdminKnowlegebaseMenu(){
        $settings = $this->adminPanelSettings;

        if($settings['knowlegebase']){
            $menu[] = [
                'title' => 'База знаний',
                'href' => 'http://wiki.sitebill.ru/',
                'hreftarget' => '_blank',
                'icon' => 'icon-book'
            ];

            $menu[] = [
                'title' => 'Форум',
                'href' => 'https://www.sitebill.ru/s/',
                'hreftarget' => '_blank',
                'icon' => 'icon-comment'
            ];

            $menu[] = [
                'title' => 'Видео-уроки',
                'href' => 'http://www.youtube.com/user/DMn1c',
                'hreftarget' => '_blank',
                'icon' => 'icon-film'
            ];

            $menu[] = [
                'title' => 'Наш сайт',
                'href' => 'http://www.sitebill.ru/',
                'hreftarget' => '_blank',
                'icon' => 'icon-heart'
            ];

            $menu[] = [
                'title' => 'Мобильное приложение',
                'href' => 'https://play.google.com/store/apps/details?id=ru.sitebill.mobilecms',
                'hreftarget' => '_blank',
                'icon' => 'icon-camera'
            ];
        }
        return $menu;
    }

    /**
     * Create interface items
     * @return array
     */
    protected function initInterfaceItems(){

        $admin_path = 'admin';

        $items = [];

        $userID = $_SESSION['user_id'];

        $settings = $this->adminPanelSettings;

        $sections = $settings['sections'];

        $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');


        // SIDEBAR
        $menu = [];

        $menu['home']['title'] = Multilanguage::_('L_HOME');
        $menu['home']['href'] = $this->buildAdminUrl('admin');
        $menu['home']['icon'] = 'icon-x fa-home';


        if($sections['data'] && $this->permissionManager->get_access($userID, 'data', 'access')){

            $menu['data']['title'] = Multilanguage::_('L_ADMIN_MENU_ESTATEDATA');
            $menu['data']['href'] = 'index.php?action=data';
            $menu['data']['icon'] = 'icon-x fa-book';
            if ($action == 'data' || $action == 'realtylogv2') {
                $menu['data']['active'] = 1;
            }

            if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template1/local_data_menu.tpl')){
                $menu['data']['incfile'] = SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template1/local_data_menu.tpl';
            }

            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php';
            $DM=new Data_Manager();

            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $model = $data_model->get_kvartira_model(false, true);
            $statuses=array();
            if(isset($model['data']['status_id'])){
                if($model['data']['status_id']['type']=='select_box'){
                    foreach($model['data']['status_id']['select_data'] as $k=>$v){
                        $statuses[$k]=$v;
                    }
                }
            }

            $stat_params=array();
            if(!empty($statuses)){
                $stat_params[]='status_id';
            }

            $stat=$DM->getDataStatInfo($stat_params);

            $menu_sub5['sdata_1']['title'] = 'Все';
            $menu_sub5['sdata_1']['count_key'] = 'all';
            $menu_sub5['sdata_1']['href'] = $this->createUrlTpl('admin?action=data');
            $menu_sub5['sdata_1']['active'] = (($action == 'data' && !isset($_GET['active'])) ? 1 : 0);

            $menu_sub5['sdata_6']['title'] = 'Активные';
            $menu_sub5['sdata_6']['count_key'] = 'active';
            $menu_sub5['sdata_6']['href'] = $this->createUrlTpl('admin?action=data&active=1');
            $menu_sub5['sdata_6']['active'] = (($action == 'data' && $_GET['active'] == '1') ? 1 : 0);

            $menu_sub5['sdata_7']['title'] = 'Неактивные';
            $menu_sub5['sdata_7']['count_key'] = 'notactive';
            $menu_sub5['sdata_7']['href'] = $this->createUrlTpl('admin?action=data&active=notactive');
            $menu_sub5['sdata_7']['active'] = (($action == 'data' && $_GET['active'] == 'notactive') ? 1 : 0);

            if(!empty($statuses)){
                foreach($statuses as $k => $v){
                    $menu_sub5[] = array(
                        'title' => $v,
                        'href'  => SITEBILL_MAIN_URL.'/admin?action=data&status_id='.$k,
                        'active' => (($action == 'data' && $_GET['status_id'] == $k) ? 1 : 0)
                    );
                }
            }
            /*$menu_sub5['sdata_4']['title'] = 'Модерация ('. (int)$stat['active'][0] .')';
            $menu_sub5['sdata_4']['href'] = $this->createUrlTpl('admin?action=data&active=notactive');
            $menu_sub5['sdata_4']['active'] = (($action == 'data' && $_GET['active'] == 'notactive') ? 1 : 0);*/

            if($this->permissionManager->get_access($userID, 'realtylogv2', 'access')){
                if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php') && $this->getConfigValue('apps.realtylogv2.enable')==1){
                    require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
                    $RL=new realtylogv2_admin();

                    $menu_sub5['sdata_5']['title'] = 'Корзина';
                    $menu_sub5['sdata_5']['count_key'] = 'realtylogv2_deleted';
                    $menu_sub5['sdata_5']['href'] = $this->createUrlTpl('admin?action=realtylogv2');
                    $menu_sub5['sdata_5']['active'] = (($action == 'realtylogv2') ? 1 : 0);
                }
            }

            $menu['data']['childs'] = $menu_sub5;
        }

        if($sections['client'] && $this->permissionManager->get_access($userID, 'client', 'access')){
            $menu['client']['title'] = Multilanguage::_('L_CLIENT_MENU');
            $menu['client']['href'] = $this->buildAdminUrl('admin?action=client');
            $menu['client']['icon'] = 'icon-x fa-heart';
            $menu['client']['active'] = ($action == 'client' ? 1 : 0);
        }

        if($sections['references']){
            $menu['references']['title'] = Multilanguage::_('L_ADMIN_MENU_REFERENCES');
            $menu['references']['href'] = '#';
            $menu['references']['icon'] = 'icon-x fa-globe';
            $menu['references']['active'] = 0;
            $menu_sub1 = [];


            if($sections['references.country']){
                $menu_sub1['country']['title'] = Multilanguage::_('L_ADMIN_MENU_COUNTRIES');
                $menu_sub1['country']['href'] = $this->buildAdminUrl('admin?action=country');
                if ($action == 'country') {
                    $menu['references']['active'] = 1;
                    $menu_sub1['country']['active'] = 1;
                }
            }


            if($sections['references.region']){
                $menu_sub1['region']['title'] = Multilanguage::_('L_ADMIN_MENU_REGIONS');
                $menu_sub1['region']['href'] = $this->buildAdminUrl('admin?action=region');
                if ($action == 'region') {
                    $menu['references']['active'] = 1;
                    $menu_sub1['region']['active'] = 1;
                }
            }
            if($sections['references.city']){
                $menu_sub1['city']['title'] = Multilanguage::_('L_ADMIN_MENU_CITIES');
                $menu_sub1['city']['href'] = $this->buildAdminUrl('admin?action=city');
                if ($action == 'city') {
                    $menu['references']['active'] = 1;
                    $menu_sub1['city']['active'] = 1;
                }
            }
            if($sections['references.district']){
                $menu_sub1['district']['title'] = Multilanguage::_('L_ADMIN_MENU_DISTRICTS');
                $menu_sub1['district']['href'] = $this->buildAdminUrl('admin?action=district');
                if ($action == 'district') {
                    $menu['references']['active'] = 1;
                    $menu_sub1['district']['active'] = 1;
                }
            }
            if($sections['references.metro']){
                $menu_sub1['metro']['title'] = Multilanguage::_('L_ADMIN_MENU_METRO');
                $menu_sub1['metro']['href'] = $this->buildAdminUrl('admin?action=metro');
                if ($action == 'metro') {
                    $menu['references']['active'] = 1;
                    $menu_sub1['metro']['active'] = 1;
                }
            }
            if($sections['references.street']){
                $menu_sub1['street']['title'] = Multilanguage::_('L_ADMIN_MENU_STREETS');
                $menu_sub1['street']['href'] = $this->buildAdminUrl('admin?action=street');
                if ($action == 'street') {
                    $menu['references']['active'] = 1;
                    $menu_sub1['street']['active'] = 1;
                }
            }

            if(!empty($menu_sub1)){
                foreach ($menu_sub1 as $app => $d){
                    if(!$this->permissionManager->get_access($userID, $app, 'access')){
                        unset($menu_sub1[$app]);
                    }
                }
            }

            if(isset($settings['add']['references'])){
                $apps_array = $this->getAppList();
                $customEntity = $this->getCustomEntities();
                foreach ($settings['add']['references'] as $addvariant){
                    if(isset($apps_array[$addvariant])){
                        if($this->permissionManager->get_access($userID, $addvariant, 'access')){
                            $menu_sub1[$addvariant] = [
                                'title' => $apps_array[$addvariant]['title'],
                                'href' => $apps_array[$addvariant]['href']
                            ];
                            if ($action == $addvariant) {
                                $menu_sub1[$addvariant]['active'] = 1;
                                $menu['references']['active'] = 1;
                            }
                        }

                    }elseif(isset($customEntity[$addvariant])){
                        $menu_sub1[$addvariant] = [
                            'title' => $customEntity[$addvariant]['title'],
                            'href' => $customEntity[$addvariant]['href']
                        ];
                        if ($action == $addvariant) {
                            $menu_sub1[$addvariant]['active'] = 1;
                            $menu['references']['active'] = 1;
                        }
                    }

                }
            }

            if(!empty($menu_sub1)){
                $menu['references']['childs'] = $menu_sub1;
            }else{
                unset($menu['references']);
            }
        }

        /*Компоненты*/
        if($sections['components']){

            $menu['components']['title'] = Multilanguage::_('L_ADMIN_MENU_EXTCOMPONENTS');
            $menu['components']['href'] = '#';
            $menu['components']['icon'] = 'icon-x fa-gavel';
            $menu['components']['active'] = 0;

            $menu_sub101 = array();
            if(1==$this->getConfigValue('enable_curator_mode')){
                $menu_sub101['cowork']['title'] = 'Куратор';
                $menu_sub101['cowork']['href'] = 'index.php?action=cowork';
                $menu_sub101['cowork']['active'] = ($action == 'cowork' ? 1 : 0);
                $menu['components']['active'] = ($action == 'cowork' ? 1 : 0);
            }

            if(!empty($menu_sub101)){
                foreach ($menu_sub101 as $app => $d){
                    if(!$this->permissionManager->get_access($userID, $app, 'access')){
                        unset($menu_sub101[$app]);
                    }
                }
            }

            if(!empty($menu_sub101)){
                $menu['components']['childs'] = $menu_sub101;
            }else{
                unset($menu['components']);
            }



        }

        if($sections['content']){

            $menu['content']['title'] = Multilanguage::_('L_CONTENT_MENU');
            $menu['content']['href'] = $this->buildAdminUrl('admin?action=page');
            $menu['content']['icon'] = 'icon-x fa-coffee';
            $menu['content']['active'] = 0;

            $menu_sub3 = [];

            if($sections['content.news']){
                $menu_sub3['news']['title'] = Multilanguage::_('L_ADMIN_MENU_NEWS');
                $menu_sub3['news']['href'] = $this->buildAdminUrl('admin?action=news');
                if ($action == 'news') {
                    $menu_sub3['news']['active'] = 1;
                    $menu['content']['active'] = 1;
                }
            }
            if($sections['content.page']){
                $menu_sub3['page']['title'] = Multilanguage::_('L_ADMIN_MENU_PAGES');
                $menu_sub3['page']['href'] = $this->buildAdminUrl('admin?action=page');
                if ($action == 'page') {
                    $menu_sub3['page']['active'] = 1;
                    $menu['content']['active'] = 1;
                }
            }
            if($sections['content.menu']){
                $menu_sub3['menu']['title'] = Multilanguage::_('L_ADMIN_MENU_MENUS');
                $menu_sub3['menu']['href'] = $this->buildAdminUrl('admin?action=menu');
                if ($action == 'menu') {
                    $menu_sub3['menu']['active'] = 1;
                    $menu['content']['active'] = 1;
                }
            }

            if(!empty($menu_sub3)){
                foreach ($menu_sub3 as $app => $d){
                    if(!$this->permissionManager->get_access($userID, $app, 'access')){
                        unset($menu_sub3[$app]);
                    }
                }
            }

            if(isset($settings['add']['content'])){
                $apps_array = $this->getAppList();
                foreach ($settings['add']['content'] as $addvariant){
                    if(isset($apps_array[$addvariant])){
                        if($this->permissionManager->get_access($userID, $addvariant, 'access')){
                            $menu_sub3[$addvariant] = [
                                'title' => $apps_array[$addvariant]['title'],
                                'href' => $apps_array[$addvariant]['href']
                            ];
                            if ($action == $addvariant) {
                                $menu_sub3[$addvariant]['active'] = 1;
                                $menu['content']['active'] = 1;
                            }
                        }

                    }

                }
            }

            if(!empty($menu_sub3)){
                $menu['content']['childs'] = $menu_sub3;
            }else{
                unset($menu['content']);
            }
        }

        if($sections['config'] && $this->permissionManager->get_access($userID, 'config', 'access')){
            $menu['config']['title'] = Multilanguage::_('L_ADMIN_MENU_SETTINGS');
            $menu['config']['href'] = $this->buildAdminUrl('admin?action=config');
            $menu['config']['icon'] = 'icon-x fa-cog';
            $menu['config']['active'] = ($action == 'config' ? 1 : 0);
        }

        if($sections['sitebill'] && $this->permissionManager->get_access($userID, 'sitebill', 'access')){
            $menu['sitebill']['title'] = Multilanguage::_('L_ADMIN_MENU_UPDATES');
            $menu['sitebill']['href'] = $this->buildAdminUrl('admin?action=sitebill');
            $menu['sitebill']['icon'] = 'icon-x fa-refresh';
            $menu['sitebill']['active'] = ($action == 'sitebill' ? 1 : 0);
        }

        if($sections['user'] && $this->permissionManager->get_access($userID, 'user', 'access')){
            $menu['user']['title'] = Multilanguage::_('L_ADMIN_MENU_USERS');
            $menu['user']['href'] = $this->buildAdminUrl('admin?action=user');
            $menu['user']['icon'] = 'icon-x fa-user';
            $menu['user']['active'] = ($action == 'user' ? 1 : 0);
        }

        if($sections['structure'] && $this->permissionManager->get_access($userID, 'structure', 'access')){
            $menu['structure']['title'] = Multilanguage::_('L_ADMIN_MENU_STRUCTURE');
            $menu['structure']['href'] = $this->buildAdminUrl('admin?action=structure');
            $menu['structure']['icon'] = 'icon-x fa-th-list';
            $menu['structure']['active'] = ($action == 'structure' ? 1 : 0);
        }

        if($sections['table'] && $this->permissionManager->get_access($userID, 'table', 'access')){
            $menu['table']['title'] = Multilanguage::_('L_TABLE_MENU');
            $menu['table']['href'] = $this->buildAdminUrl('admin?action=table');
            $menu['table']['icon'] = 'icon-x fa-edit';
            $menu['table']['active'] = ($action == 'table' ? 1 : 0);
        }

        if($sections['access']){
            $menu['access']['title'] = Multilanguage::_('L_ADMIN_MENU_ACCESS');
            $menu['access']['href'] = '';
            $menu['access']['icon'] = 'icon-x fa-group';
            $menu['access']['active'] = 0;

            $menu_sub_group = [];

            $menu_sub_group['group']['title'] = Multilanguage::_('L_ADMIN_MENU_GROUPS');
            $menu_sub_group['group']['href'] = $this->buildAdminUrl('admin?action=group');
            if ($action == 'group') {
                $menu_sub_group['group']['active'] = 1;
                $menu['access']['active'] = 1;
            }

            $menu_sub_group['component']['title'] = Multilanguage::_('L_ADMIN_MENU_COMPONENTS');
            $menu_sub_group['component']['href'] = $this->buildAdminUrl('admin?action=component');
            if ($action == 'component') {
                $menu_sub_group['component']['active'] = 1;
                $menu['access']['active'] = 1;
            }

            $menu_sub_group['function']['title'] = Multilanguage::_('L_ADMIN_MENU_FUNCTIONS');
            $menu_sub_group['function']['href'] = $this->buildAdminUrl('admin?action=function');
            if ($action == 'function') {
                $menu_sub_group['function']['active'] = 1;
                $menu['access']['active'] = 1;
            }

            if(!empty($menu_sub_group)){
                foreach ($menu_sub_group as $app => $d){
                    if(!$this->permissionManager->get_access($userID, $app, 'access')){
                        unset($menu_sub_group[$app]);
                    }
                }
            }

            if(!empty($menu_sub_group)){
                $menu['access']['childs'] = $menu_sub_group;
            }else{
                unset($menu['access']);
            }

        }

        /**/
        if(isset($settings['newsections']) && !empty($settings['newsections'])){
            $apps_array = $this->getAppList();
            $customEntity = $this->getCustomEntities();


            foreach ($settings['newsections'] as $code => $newsection){
                $menu[$code]['title'] = $newsection['title'];
                $menu[$code]['icon'] = 'icon-x'.($newsection['icon'] != '' ? ' '.$newsection['icon'] : '');
                $menu_sub1 = [];
                foreach ($newsection['childs'] as $child){
                    if(isset($apps_array[$child])){
                        if($this->permissionManager->get_access($userID, $child, 'access')){
                            $menu_sub1[$child] = [
                                'title' => $apps_array[$child]['title'],
                                'href' => $apps_array[$child]['href']
                            ];
                            if ($action == $child) {
                                $menu_sub1[$child]['active'] = 1;
                                $menu[$code]['active'] = 1;
                            }
                        }
                    }elseif(isset($customEntity[$child])){
                        $menu_sub1[$child] = [
                            'title' => $customEntity[$child]['title'],
                            'href' => $customEntity[$child]['href']
                        ];
                        if ($action == $child) {
                            $menu_sub1[$child]['active'] = 1;
                            $menu[$code]['active'] = 1;
                        }
                    }
                }
                if(!empty($menu_sub1)){
                    $menu[$code]['childs'] = $menu_sub1;
                }else{
                    unset($menu[$code]);
                }
            }
        }

        if(isset($sections['mobilephoto']) && false === $sections['mobilephoto']){

        }elseif(!isset($sections['mobilephoto']) && (defined('BRANDING') && BRANDING != 1)){

        }else{
            $menu['mobilephoto']['title'] = _e('Мобильное фото');
            $menu['mobilephoto']['href'] = 'https://play.google.com/store/apps/details?id=ru.sitebill.mobilecms';
            $menu['mobilephoto']['hreftarget'] = '_blank';
            $menu['mobilephoto']['icon'] = 'icon-x fa-camera';
        }

        $items['sidebar'] = $menu;

        // CUSTOM SITEBAR MENU
        $menus_from_db = array();

        if ( class_exists('\menu\api\menu') ) {
            $menu_api = new \menu\api\menu();
            $menus_from_db = $menu_api->load_menus();
            if ( $menus_from_db['admin'] ) {
                $items['sidebar'] = $menus_from_db['admin'];
            }
        }

        // APPS
        if($menus_from_db['apps']){
            $apps_array = $menus_from_db['apps'];
        }else{
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
            $apps_processor = new Apps_Processor();
            $apps_array = $apps_processor->load_apps_menu(false, $admin_path);
        }

        if(!empty($apps_array)){
            foreach ($apps_array as $action => $app_info ) {
                if ( !$this->permissionManager->get_access($userID, $action, 'access') ) {
                    unset($apps_array[$action]);
                }
            }
        }

        if(!empty($apps_array)){
            $items['apps']['title'] = Multilanguage::_('L_ADMIN_MENU_APPLICATIONS');
            $items['apps']['href'] = $this->createUrlTpl('admin?action=apps');
            $items['apps']['childs'] = $apps_array;
        }


        $items['nested_apps'] = $this->make_nested_apps_array($apps_array);

        // OTHER

        $customentitymenu = $this->getCustomEntities();
        if(!empty($customentitymenu)){
            $items['custom_entity_menu'] = $customentitymenu;
        }

        if($this->adminPanelSettings['gotosite']){
            $items['admin_site'] = [
                'href' => $this->createUrlTpl(''),
                'icon' => 'icon-x icon-eye-open',
                'title' => Multilanguage::_('L_ADMIN_MENU_GOTOSITE')
            ];
        }

        if($this->adminPanelSettings['admin3']){
            if($this->getConfigValue('apps.admin3.enable')){
                $items['admin_admin3'] = [
                    'href' => ('' != $this->getConfigValue('apps.admin3.alias') ? $this->createUrlTpl($this->getConfigValue('apps.admin3.alias')) : $this->createUrlTpl('apps/admin')),
                    'icon' => 'icon-x fa fa-tachometer',
                    'title' => 'Новая админка'
                ];
            }
        }

        $items['langswitcher'] = $this->getAdminLangMenu();

        if($this->adminPanelSettings['knowlegebase']){
            $items['knowlegebase'] = $this->getAdminKnowlegebaseMenu();
        }

        $items['recentapps'] = $this->getRecentAppsMenu();

        return $items;
    }

    /**
     * Init interface items to template
     */
    public function buildInterface(){

        $this->template->assign('interface', $this->initInterfaceItems());

    }

    /**
     * Create Lang switcher
     * @return array
     */
    public function getAdminLangMenu(){
        $available_langs = Multilanguage::availableLanguages();
        $CurrLang = $this->getCurrentLang();

        $switcher = [
            'current' => $CurrLang,
            'variants' => []
        ];
        if(!empty($available_langs)){
            foreach ($available_langs as $lang){
                $switcher['variants'][] = [
                    'href' => $this->createUrlTpl('admin?_lang='.$lang),
                    'name' => $lang,
                    'active' => ($lang === $CurrLang ? 1 : 0)
                ];
            }

        }
        return $switcher;
    }
}
