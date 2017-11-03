<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Apps processort. Scan apps folder and run each apps if needed
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
define ('APPS_FILE_NOT_EXIST', 1);
class Apps_Processor extends SiteBill {
    private $plugins_dir;
    private $apps_executed = array();
    private $frontend_app='';
    /**
     * Constructor
     */
    function __construct() {
        $this->apps_dir = SITEBILL_APPS_DIR;
        $this->SiteBill();
    }
    
    
    /**
     * Run apps by action and interface name
     * @param string $app_name 
     * @param string $interface
     * @return string
     */
    function run ( $app_name, $interface ) {
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps'.'/'.$app_name.'/'.$interface.'/'.$interface.'.php') ) {
    		$app_filename = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps'.'/'.$app_name.'/'.$interface.'/'.$interface.'.php';
    	} else {
    		$app_filename = $this->apps_dir.'/'.$app_name.'/'.$interface.'/'.$interface.'.php';
    	}
        
        if ( is_file($app_filename) ) {
            require_once ($app_filename);
            $app_class_name = $app_name.'_'.$interface;
            $app_class_inst = new $app_class_name;
            $rs = $app_class_inst->main();
            return $rs;
        } else {
            throw new Exception('Apps file '.$app_filename.' not exist', APPS_FILE_NOT_EXIST);
        }
    }
    
    /**
     * Set executed apps
     * @param string $apps_name
     */
    private function set_executed_apps ( $apps_name ) {
    	$this->apps_executed[] = $apps_name;
    }
    
    /**
     * Get executed apps
     * @return Array
     */
    function get_executed_apps () {
    	return $this->apps_executed;
    }
    
    function get_runned_app(){
    	$app = end($this->get_executed_apps());
    	$app=preg_replace('/^(local_)/', '', $app);
    	$app=preg_replace('/(_site)$/', '', $app);
    	return $app;
    }
    
    /**
     * Run _preload method for applications
     * @param void
     * @return boolean
     */
    function run_preload (  ) {
	$time_start = microtime(true);
	
    	$Sitebill_Registry=Sitebill_Registry::getInstance();
    	$is_preload=$Sitebill_Registry->getFeedback('preload_done');
    	if($is_preload){
    		return;
    	}
    	$Sitebill_Registry->addFeedback('preload_done', true);
        
        $apps_list = $this->load_apps_from_db(array('preload' => 1));
	if ( $apps_list ) {
            foreach ( $apps_list as $name => $apps_array ) {
                $app_class_name = false;
                if ( $apps_array['local_admin_path'] != '' ) {
                    require_once ($apps_array['local_admin_path']);
                    $app_class_name = 'local_'.$name.'_admin';
		    if ( !class_exists($app_class_name) ) {
			$app_class_name = $name.'_admin';
			if ( !class_exists($app_class_name) ) {
			    $app_class_name = false;
			}
		    }
                } elseif ( $apps_array['admin_path'] != '' ) {
                    require_once ($apps_array['admin_path']);
                    $app_class_name = $name.'_admin';
                }
                /*
                if ( $apps_array['local_site_path'] != ''  ) {
                    require_once ($apps_array['site_path']);
                    require_once ($apps_array['local_site_path']);
                    $app_class_name = 'local_'.$name.'_site';
                } elseif ( $apps_array['site_path'] != '' ) {
                    require_once ($apps_array['site_path']);
                    $app_class_name = $name.'_site';
                }
                 */
                if ( $app_class_name ) {
                    //echo $app_class_name.'<br>';
                    $app_class_inst = new $app_class_name;
                    $app_class_inst->_preload();
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;	
            //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
            
            return;
	    //return $apps_array;
	}
        
        
        
    	if (is_dir($this->apps_dir)) {
    		if ($dh = opendir($this->apps_dir)) {
    			while (($app_dir = readdir($dh)) !== false) {
    				//echo '$app_dir = '.$app_dir.'<br>';
    				if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
    					if ( is_file($this->apps_dir.'/'.$app_dir.'/site/site.php') ) {
    						if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/admin/admin.php') ) {
    							require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/admin/admin.php');
    						} else {
    							require_once ($this->apps_dir.'/'.$app_dir.'/admin/admin.php');
    						}
    
    						if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/site/site.php') ) {
    							require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/site/site.php');
    						} else {
    							require_once ($this->apps_dir.'/'.$app_dir.'/site/site.php');
    						}
    						$app_class_name = $app_dir.'_site';
    						//echo $app_class_name.'<br>';
    						$app_class_inst = new $app_class_name;
    						$app_class_inst->_preload();
    					}/*elseif( is_file($this->apps_dir.'/'.$app_dir.'/admin/admin.php') ){
    						require_once ($this->apps_dir.'/'.$app_dir.'/admin/admin.php');
    						$app_class_name = $app_dir.'_admin';
    						$app_class_inst = new $app_class_name;
    						$app_class_inst->_preload();
    					}*/
    				}
    			}
    			closedir($dh);
    		}
    	}
	$time_end = microtime(true);
	$time = $time_end - $time_start;	
	//$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
	
    }
    
    /**
     * Run frontend apps
     * @param void 
     * @return boolean
     */
    function run_frontend (  ) {
	$time_start = microtime(true);

        $apps_list = $this->load_apps_from_db(array('frontend' => 1));
	if ( $apps_list ) {
            //print_r($apps_list);
            foreach ( $apps_list as $name => $apps_array ) {
                $app_class_name = false;
                if ( $apps_array['local_admin_path'] != '' ) {
                    require_once ($apps_array['local_admin_path']);
                } elseif ( $apps_array['admin_path'] != '' ) {
                    require_once ($apps_array['admin_path']);
                }

                if ( $apps_array['local_site_path'] != ''  ) {
                    require_once ($apps_array['site_path']);
                    require_once ($apps_array['local_site_path']);
                    $app_class_name = 'local_'.$name.'_site';
		    if ( !class_exists($app_class_name) ) {
			$app_class_name = $name.'_site';
			if ( !class_exists($app_class_name) ) {
			    $app_class_name = false;
			}
		    }
                } elseif ( $apps_array['site_path'] != '' ) {
                    require_once ($apps_array['site_path']);
                    $app_class_name = $name.'_site';
                }
                if ( $app_class_name ) {
                    //echo $app_class_name.'<br>';
                    $app_class_inst = new $app_class_name;
                    if ( $app_class_inst->frontend() ) {
                        $this->set_executed_apps($app_class_name);
                     }
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;	
            //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
            
            return;
	    //return $apps_array;
	}
        
        
	
        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                	//echo '$app_dir = '.$app_dir.'<br>';
                    if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
                        if ( is_file($this->apps_dir.'/'.$app_dir.'/site/site.php') ) {
                        	
                        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/admin/admin.php') ) {
                        		require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/admin/admin.php');
                        	} else {
                            	require_once ($this->apps_dir.'/'.$app_dir.'/admin/admin.php');
                        	}
                        	
                        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/site/local_site.php') ) {
                        		require_once ($this->apps_dir.'/'.$app_dir.'/site/site.php');
                        		require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/site/local_site.php');
                        		$app_class_name = 'local_'.$app_dir.'_site';
                        	} elseif ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/site/site.php') ) {
                        		require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$app_dir.'/site/site.php');
                        		$app_class_name = $app_dir.'_site';
                        	} else {
                        		require_once ($this->apps_dir.'/'.$app_dir.'/site/site.php');
                        		$app_class_name = $app_dir.'_site';
                        	}
                            
                            //echo $app_class_name.'<br>';
                            $app_class_inst = new $app_class_name;
                            if ( $app_class_inst->frontend() ) {
                            	$this->set_executed_apps($app_class_name);
                                //closedir($dh);
                                //return true;
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
	$time_end = microtime(true);
	$time = $time_end - $time_start;	
	//$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
	
    }
    
    /**
     * Check apps compatibility
     * @param void
     * @return boolean
     */
    function check_installed_apps () {
	$time_start = microtime(true);
	
    	if (is_dir($this->apps_dir)) {
    		if ($dh = opendir($this->apps_dir)) {
    			 
    			while (($app_dir = readdir($dh)) !== false) {
    				if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\.{1,}/', $app_dir) ) {
    					if ( is_file($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml') ) {
    						return true;
    					} else {
    						return false;
    					}
    				}
    			}
    		}
    	}
	$time_end = microtime(true);
	$time = $time_end - $time_start;	
	//$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
	
    }
    
    function check_apps_compatibility() {
    	if ( $this->check_installed_apps() ) {
    		if ( !class_exists('DomDocument') ) {
    			return false;
    		}
    	}
    	return true;
    }
    
    function load_apps_array () {
	$time_start = microtime(true);
	
	//echo 'load_apps_array';
	if (!function_exists('file_get_html')) {
	    if (file_exists(SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php')) {
		require_once SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php';
	    } else {
		require_once SITEBILL_DOCUMENT_ROOT . '/third/simple_html_dom/simple_html_dom.php';
	    }
	}
    	$apps_array = array();
    	
    	if (is_dir($this->apps_dir)) {
    		if ($dh = opendir($this->apps_dir)) {
    			while (($app_dir = readdir($dh)) !== false) {
    				if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
    					if ( is_file($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml') ) {
    						 
    						//Parsing by simple_xml_dom
    						$xml = @file_get_html($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml');
    						if($xml && is_object($xml)){
    							$title=SiteBill::iconv('utf-8', SITE_ENCODING, $xml->find('administration',0)->find('menu',0)->innertext());
    							$action=(string)$xml->find('name',0)->innertext();
    							$apps_array[$action]['xml'] = true;
    							if ( is_file($this->apps_dir.'/'.$app_dir.'/update.php') ) { 
    								$apps_array[$action]['update'] = true;
    							}
    						}
    					}
    				}
    			}
    			closedir($dh);
    		}
    	}
	$time_end = microtime(true);
	$time = $time_end - $time_start;	
	//$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
    	return $apps_array;
    }
    
    
    /**
     * Load installed apps and generate menu items from XML-files
     * @param boolean $load_not_active load not active menu items
     * @return array
     */
    function load_apps_menu ( $load_not_active = false ) {
	$apps_array = $this->load_apps_from_db(array('backend_menu' => 1));
	if ( $apps_array ) {
	     return $apps_array;
	}
	$time_start = microtime(true);

	//return;
	//echo 'load_apps_menu';
	
    	if ( !$this->check_installed_apps() ) {
    		return false;
    	}
	if (!function_exists('file_get_html')) {
	    if (file_exists(SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php')) {
		require_once SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php';
	    } else {
		require_once SITEBILL_DOCUMENT_ROOT . '/third/simple_html_dom/simple_html_dom.php';
	    }
	}
        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
                        if ( is_file($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml') ) {
	                        
                        	//Parsing by simple_xml_dom
	                        $xml = @file_get_html($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml');
							if($xml && is_object($xml)){
								$title=SiteBill::iconv('utf-8', SITE_ENCODING, $xml->find('administration',0)->find('menu',0)->innertext());
								$action=(string)$xml->find('name',0)->innertext();
								$backendMenu = 'false';
								if ($xml->find('backendMenu',0)) {
								    $backendMenu = (string)$xml->find('backendMenu',0)->innertext();
								}
								
							    $add_to_menu = false;
								if ( $backendMenu != 'false' ) {
								    $add_to_menu = true;
								}
								if ($load_not_active) {
								    $add_to_menu = true;
								}
								if ( $add_to_menu ) {
								    $menu[$action]['title'] = $title;
								    $menu[$action]['href'] = 'index.php?action='.$action;
								    if ( isset($_REQUEST[$action]) AND ($_REQUEST[$action] == $action) ) {
								    	$menu[$action]['active'] = 1;
								    }
								}
                                                                $menu_all[$action]['title'] = $title;
								$menu_all[$action]['href'] = 'index.php?action='.$action;
                                                                $menu_all[$action]['backend_menu'] = $add_to_menu;
                                                                
							}
                        }
                        
                    }
                }
                closedir($dh);
            }
        }
        uasort($menu, array($this, 'appsSort'));
        uasort($menu_all, array($this, 'appsSort'));
	$time_end = microtime(true);
	$time = $time_end - $time_start;	
	//$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
	if ( !$this->getConfigValue('apps_cache_disable') ) {
	    $this->update_apps_cache($menu_all);
	}
        return $menu;        
    }
    
    function appsSort($a, $b){
    	if($a['title']>$b['title']){
    		return 1;
    	}elseif($a['title']<$b['title']){
    		return -1;
    	}else{
    		return 0;
    	}
    }
    
    /**
     * Обновляем записи в таблице apps
     * @param type $apps_array
     */
    function update_apps_cache ( $apps_array ) {
	$DBC=DBC::getInstance();
	
	//Сначала создадим таблицу apps для хранения информации о приложениях
	$query_create_table = "CREATE TABLE `".DB_PREFIX."_apps` (
  `apps_id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `backend_menu` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `href_admin` varchar(255) NOT NULL,
  `admin_path` text,
  `local_admin_path` text,
  `site_path` text,
  `local_site_path` text,
  `preload` tinyint(4) NOT NULL DEFAULT '0',
  `frontend` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`apps_id`)
  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
	$stmt=$DBC->query($query_create_table, array());
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
	
	foreach ( $apps_array as $apps_name => $apps_items ) {
	    if ( !$this->get_apps_by_name($apps_name) and $apps_name != 'shoplog'  ) {
		$app_class_name_admin = '';
		$app_class_name = '';
		$site_path = '';
		$admin_path = '';
		$local_site_path = '';
		$local_admin_path = '';
		$preload = 0;
		$frontend = 0;
		
		if (file_exists ($this->apps_dir.'/'.$apps_name.'/admin/admin.php')) {
		    $admin_path = $this->apps_dir.'/'.$apps_name.'/admin/admin.php';
		    //$app_class_name_admin = $apps_name.'_admin';
		}
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$apps_name.'/admin/admin.php') ) {
		    $local_admin_path = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$apps_name.'/admin/admin.php';
		    //require_once ($local_admin_path);
		    //$app_class_name_admin = $apps_name.'_admin';
		}
		
		
		if ( file_exists($this->apps_dir.'/'.$apps_name.'/site/site.php') ) {
		    require_once ($admin_path);
		    $site_path = $this->apps_dir.'/'.$apps_name.'/site/site.php';
		    require_once ($site_path);
		    $app_class_name = $apps_name.'_site';
		}		
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$apps_name.'/site/site.php') ) {
		    require_once ($admin_path);
		    $local_site_path = $this->apps_dir.'/'.$apps_name.'/site/site.php';
		    require_once ($local_site_path);
		    $app_class_name = $apps_name.'_site';
		}
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$apps_name.'/site/local_site.php') ) {
		    require_once ($admin_path);
		    $local_site_path = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/'.$apps_name.'/site/local_site.php';
		    $app_class_name = 'local_'.$apps_name.'_site';
		    require_once ($local_site_path);
		}
		
		if ($app_class_name != '') {
		    //echo $app_class_name.'<br>';
		    
		    $app_class_inst = new $app_class_name;
		    
		    if (method_exists($app_class_inst, 'frontend') ) {
                        //if ( $app_class_inst->frontend() ) {
                            $frontend = 1;
                        //}
		    }

                    if (method_exists($app_class_inst, '_preload') ) {
			$preload = 1;
		    }
                    unset($app_class_inst);
                    
		}
                
		
		$query='INSERT INTO '.DB_PREFIX.'_apps (`name`, `title`, `active`, `href_admin`, `admin_path`, `local_admin_path`, `site_path`, `local_site_path`, `preload`, `frontend`, `backend_menu`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$stmt=$DBC->query($query, array($apps_name, $apps_items['title'], 1, $apps_items['href'], $admin_path, $local_admin_path, $site_path, $local_site_path, $preload, $frontend, $apps_items['backend_menu']));
		//echo $DBC->getLastError();
	    }
	}
    }
    
    /**
     * Загрузка массива приложений из базы данных
     * @return array массив приложений
     */
    function load_apps_from_db ( $params = array() ) {
	if ( $this->getConfigValue('apps_cache_disable') ) {
	    return false;
	}
	$DBC=DBC::getInstance();
        if ( isset($params['frontend']) && $params['frontend'] == 1 ) {
            $query='SELECT * FROM `'.DB_PREFIX.'_apps` WHERE `active`=? and `frontend`=?';
            $stmt=$DBC->query($query, array(1, 1));
        } elseif ( isset($params['preload']) && $params['preload'] == 1 ) {
            $query='SELECT * FROM `'.DB_PREFIX.'_apps` WHERE `active`=? and `preload`=?';
            $stmt=$DBC->query($query, array(1, 1));
        } elseif ( isset($params['backend_menu']) && $params['backend_menu'] == 1 ) {
            $query='SELECT * FROM `'.DB_PREFIX.'_apps` WHERE `active`=? and `backend_menu`=?';
            $stmt=$DBC->query($query, array(1, 1));
        } else {
            $query='SELECT * FROM `'.DB_PREFIX.'_apps` WHERE `active`=?';
            $stmt=$DBC->query($query, array(1));
        }
        
	$ra = array();
    	if($stmt){
	    while ( $ar=$DBC->fetch($stmt) ) {
		if ( $ar['apps_id'] > 0 ) {
		    $ra[$ar['name']]['title'] = $ar['title'];
		    $ra[$ar['name']]['href'] = $ar['href_admin'];
		    $ra[$ar['name']]['admin_path'] = $ar['admin_path'];
		    $ra[$ar['name']]['local_admin_path'] = $ar['local_admin_path'];
		    $ra[$ar['name']]['site_path'] = $ar['site_path'];
		    $ra[$ar['name']]['local_site_path'] = $ar['local_site_path'];
		    $ra[$ar['name']]['preload'] = $ar['preload'];
		    $ra[$ar['name']]['frontend'] = $ar['frontend'];
		}
	    }
    	}
	if ( count($ra) > 0 ) {
	    return $ra;
	}
	return false;
    }
    
    /**
     * Получаем массив данных о приложении из таблицы apps по имени
     * @param type $name
     * @return mixed массив с данными о приложении или false если приложение не найдено
     */
    function get_apps_by_name ( $name ) {
	$DBC=DBC::getInstance();
	$query='SELECT * FROM `'.DB_PREFIX.'_apps` WHERE `name`=?';
	$stmt=$DBC->query($query, array($name));
    	if($stmt){
	    $ar=$DBC->fetch($stmt);
	    if ( $ar['apps_id'] > 0 ) {
		return $ar;
	    }
    	}
	return false;
    }
}
?>
