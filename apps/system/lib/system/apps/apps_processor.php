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
    /**
     * Constructor
     */
    function __construct() {
        $this->apps_dir = SITEBILL_DOCUMENT_ROOT.'/apps';
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
        //echo $app_filename.'<br>';
        if ( is_file($app_filename) ) {
            require_once ($app_filename);
            $app_class_name = $app_name.'_'.$interface;
            //echo $app_class_name.'<br>';
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
    
    /**
     * Run _preload method for applications
     * @param void
     * @return boolean
     */
    function run_preload (  ) {
    	$Sitebill_Registry=Sitebill_Registry::getInstance();
    	$is_preload=$Sitebill_Registry->getFeedback('preload_done');
    	if($is_preload){
    		return;
    	}
    	$Sitebill_Registry->addFeedback('preload_done', true);
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
    }
    
    /**
     * Run frontend apps
     * @param void 
     * @return boolean
     */
    function run_frontend (  ) {
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
    }
    
    /**
     * Check apps compatibility
     * @param void
     * @return boolean
     */
    function check_installed_apps () {
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
    }
    
    function check_apps_compatibility() {
    	if ( $this->check_installed_apps() ) {
    		if ( !class_exists('DomDocument') ) {
    			return false;
    		}
    	}
    	return true;
    }
    
    
    /**
     * Load installed apps and generate menu items from XML-files
     * @param boolean $load_not_active load not active menu items
     * @return array
     */
    function load_apps_menu ( $load_not_active = false ) {
    	if ( !$this->check_installed_apps() ) {
    		return false;
    	}
        //$dom = new DomDocument();
        require_once SITEBILL_DOCUMENT_ROOT.'/third/simple_html_dom/simple_html_dom.php';
        
        
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
							}
                        }
                        
                    }
                }
                closedir($dh);
            }
        }
        uasort($menu, array($this, 'appsSort'));
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
}
?>
