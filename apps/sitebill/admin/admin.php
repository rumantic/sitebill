<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Sitebill admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
function untrailingslashit($string) {
	return rtrim($string, '/');
}
function trailingslashit($string) {
	return untrailingslashit($string) . '/';
}

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/sitebill/class/error.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/sitebill/class/filesystem.php');
class sitebill_admin extends Object_Manager {
    private $apps_dir;
    /**
     * Constructor
     */
    function __construct() {
        $this->action = 'sitebill';
        if ( method_exists('Multilanguage', 'appendAppDictionary') ) {
            Multilanguage::appendAppDictionary('sitebill');
        }
        $this->app_title = Multilanguage::_('APPLICATION_NAME','sitebill');
        $this->apps_dir = SITEBILL_DOCUMENT_ROOT.'/apps';
        $this->SiteBill();
        $this->filesystem = new Sitebill_Filesystem($arg);
        
    }
    
    function ajax(){
    	if ( $this->getRequestValue('action') == 'ajax_update_app' ) {
    		$app_name = $this->getRequestValue('app_name');
    		$secret_key = $this->getRequestValue('secret_key');
    		$ra = $this->update_app_js($app_name, $secret_key);
                $this->clear_apps_cache();
    		return json_encode($ra);
    	} else {
    		$apps = $this->load_apps();
                $this->clear_apps_cache();
    		return json_encode($apps);
    	}
    }
    
    function main(){
        if ( $this->getRequestValue('do') == 'update' ) {
            $rs = $this->update_app( $this->getRequestValue('app') );
        } elseif ( $this->getRequestValue('do') == 'update_all' ) {
        	$apps = $this->load_apps();
        	$license_key = $this->getConfigValue('license_key');
        	 
        	//$update_info['apps'] = $apps;
        	$update_info['license_key'] = $license_key;
        	$update_info['encoding'] = SITE_ENCODING;
        	$update_info['host'] = 'http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL;
        	$update_info['apps'] = $this->load_apps();
        	$update_info['secret_key'] = $this->getRequestValue('secret_key');
        	$json_string = json_encode($update_info);
        	$rs = '';
        	$rs .= '<script type="text/javascript">
        var update_info_json_string = \''.$json_string.'\';
</script>';
        	 
            $rs .= $this->update_all( $this->getRequestValue('secret_key') );
        } elseif ( $this->getRequestValue('do') == 'install' ) {
            $rs = $this->install_app( $this->getRequestValue('app') );
        } else {
            $apps = $this->load_apps();
            $license_key = $this->getConfigValue('license_key');
           
            //$update_info['apps'] = $apps;
            $update_info['license_key'] = $license_key;
            $update_info['encoding'] = SITE_ENCODING;
            $update_info['host'] = 'http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL;
            $update_info['apps'] = $this->load_apps();
            $json_string = json_encode($update_info);
            
            //echo 'license key = '.$license_key.'<br>';
            //echo $json_string.'<br>';
            /*
             echo '<pre>';
            print_r($apps);
            echo '</pre>';
            */
           /* $rs .= '<script type="text/javascript">
        var apps_status = \''.json_encode($apps).'\'; 
</script>';*/
            $rs .= '<div id="admin_area">';
            $rs .= Multilanguage::_('REGISTER_FOR_ACCESS','sitebill').' <a href="http://www.sitebill.ru/">sitebill.ru</a>';
            $rs .= '</div>';
            $rs .= $this->get_js_update_function($json_string);
            
            $check_rs = $this->check_system_requirements_failed();
            if ( $check_rs ) {
                $rs .= $check_rs;
            } else {
                $rs .= '<div id="updater_wrapper"></div>';
            }
        }
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        $this->clear_apps_cache();
        return $rs_new;
    }
    
    function check_system_requirements_failed () {
        $rs = false;
        $rs_new = false;
        if ( !class_exists('ZipArchive') ) {
            $rs .= Multilanguage::_('NEED_SUPPORT','sitebill').' <a href="http://php.net/manual/ru/class.ziparchive.php" target="_blank">ZipArchive</a><br>';
        }
        if ( !function_exists('curl_version') ) {
            $rs .= Multilanguage::_('NEED_SUPPORT','sitebill').' <a href="http://php.net/manual/ru/book.curl.php" target="_blank">CURL-extension</a><br>';
        }     
        if ( !is_writable(SITEBILL_DOCUMENT_ROOT.'/apps') ) {
            $rs .= Multilanguage::_('NO_WRITING_ACCESS','sitebill').' '.SITEBILL_DOCUMENT_ROOT.'/apps'.'<br>';
        }   
        if ( $rs ) {
            $rs_new = '<p class="error"><br>'.Multilanguage::_('FOR_AUTOUPDATE','sitebill').':<br>';
            $rs_new .= $rs;
            $rs_new .= '</p>';
        }
        return $rs_new;
    }
    
    function update_all () {
    	$rs = $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/sitebill/admin/template/update_wizard.tpl');
    	return $rs;
    }
    
    function update_app_js ( $app_name, $secret_key ) {
    	$this->download($app_name, $secret_key);
    	if ( $this->getError() ) {
    		$ra['error'] = $this->GetErrorMessage();
    	} else {
    		//run update procedure
    		if ( is_file(SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/update.php') ) {
    			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/update.php');
    			$update_class_name = $app_name.'_update';
    			$update_app_class = new $update_class_name;
    			$update_app_class->main($secret_key);
    		}
    		$ra['success'] = 1;
    	}
    	return $ra;
    }
    
    function update_app ( $app_name, $secret_key = '' ) {
    	if ( $secret_key == '' ) {
    		$secret_key = $this->getRequestValue('secret_key');
    	}
        $rs .= sprintf(Multilanguage::_('APP_UPDATE','sitebill'), $app_name).'<br>';
        $rs .= sprintf(Multilanguage::_('UPADTES_LOAD','sitebill'), $app_name).'<br>';
        $this->download($app_name, $secret_key);
        if ( $this->getError() ) {
        	$rs .= sprintf(Multilanguage::_('ERROR_ON_UPDATE','sitebill'), $this->GetErrorMessage()).'<br>';
        	return $rs;
        } else {
            //run update procedure
            if ( is_file(SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/update.php') ) {
                require_once (SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/update.php');
                $update_class_name = $app_name.'_update';
                $update_app_class = new $update_class_name;
                $rs .= $update_app_class->main($secret_key);
            }
        	$rs .= sprintf(Multilanguage::_('SUCCESS_UPDATE','sitebill'), $app_name).'<br>';
        }
        return $rs;
    }
    
    function install_app ( $app_name ) {
    	$rs .= sprintf(Multilanguage::_('APP_INSTALL','sitebill'), $app_name).'<br>';
    	$rs .= sprintf(Multilanguage::_('INSTALL_LOAD','sitebill'), $app_name).'<br>';
    	$this->download($app_name, $this->getRequestValue('secret_key'));
    	if ( $this->getError() ) {
    		$rs .= sprintf(Multilanguage::_('ERROR_ON_INSTALL','sitebill'), $this->GetErrorMessage()).'<br>';
    		return $rs;
    	} else {
    		//run update procedure
    		if ( is_file(SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/update.php') ) {
    			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/update.php');
    			$update_class_name = $app_name.'_update';
    			$update_app_class = new $update_class_name;
    			if ( method_exists($update_app_class, 'install') ) {
    			    $rs .= $update_app_class->install();
    			}
    		}
    		$rs .= sprintf(Multilanguage::_('SUCCESS_INSTALL','sitebill'), $app_name).'<br>';
    	}
    	return $rs;
    }
    
    
    function download ( $app_name, $secret_key ) {
        
        $to_file_name = SITEBILL_DOCUMENT_ROOT.'/cache/upl/'.$app_name.'.zip';
        $unzip_dest = SITEBILL_DOCUMENT_ROOT.'/apps/';

        //download file
        $to_file = fopen($to_file_name,'w');
        if ( !$this->getCurlContent('https://www.sitebill.ru/update/?app_name='.$app_name.'&secret_key='.$secret_key, $to_file) ) {
            $this->riseError(Multilanguage::_('ERROR_CANT_LOAD','sitebill'));
            fclose($to_file);
            return false;
        }
        fclose($to_file);
        $result_size = filesize($to_file_name);
        if ( $result_size < 1000 ) {
            $error_string = file_get_contents($to_file_name);
            if ( preg_match('/error:/', $error_string) ) {
                $error_array = explode(':', $error_string);
                $this->riseError($error_array[1]);
                unlink($to_file_name);
                return false;
            }
        }
        
        //unzip file
        $this->_unzip_file_ziparchive($to_file_name, $unzip_dest);
        if ( $this->getError() ) {
            return false;
        }
        
        //install apps
        $this->filesystem->chmod($unzip_dest, 0755, true);
        if ( $this->getError() ) {
        	return false;
        }
        $this->filesystem->delete($to_file_name);
        return true;
    }
    
    function getCurlContent($url,$to_file=''){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
    	curl_setopt ($ch, CURLOPT_FAILONERROR,true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	 
    	if (!empty($to_file)){
    		curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
    		curl_setopt($ch, CURLOPT_FILE,$to_file);
    	}
    	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
    	curl_setopt($ch,CURLOPT_TIMEOUT,60);
    	$res=curl_exec($ch);
    	$error=curl_error($ch);
    	$errno=curl_errno($ch);
    	if ($errno!=0){
    		return false;
    	}
    	curl_close($ch);
    	return $res;
    }
    
    /**
     * This function should not be called directly, use unzip_file instead. Attempts to unzip an archive using the ZipArchive class.
     * Assumes that Sitebill_Filesystem() has already been called and set up.
     *
     * @since 3.0.0
     * @see unzip_file
     * @access private
     *
     * @param string $file Full path and filename of zip archive
     * @param string $to Full path on the filesystem to extract archive to
     * @param array $needed_dirs A partial list of required folders needed to be created.
     * @return mixed Sitebill_Error on failure, True on success
     */
    function _unzip_file_ziparchive($file, $to, $needed_dirs = array() ) {
    	global $wp_filesystem;
    
    	$z = new ZipArchive();
    
    	// PHP4-compat - php4 classes can't contain constants
    	$zopen = $z->open($file, /* ZIPARCHIVE::CHECKCONS */ 4);
    	if ( true !== $zopen ) {
    	    $this->riseError('incompatible_archive');
    	    return false;
    	}
    
    	for ( $i = 0; $i < $z->numFiles; $i++ ) {
    		if ( ! $info = $z->statIndex($i) ) {
    		    $this->riseError('stat_failed: Could not retrieve file from archive.');
    		    return false;
    		}
    
    		if ( '__MACOSX/' === substr($info['name'], 0, 9) ) // Skip the OS X-created __MACOSX directory
    			continue;
    
    		if ( '/' == substr($info['name'], -1) ) // directory
    			$needed_dirs[] = $to . $this->untrailingslashit($info['name']);
    		else
    			$needed_dirs[] = $to . $this->untrailingslashit(dirname($info['name']));
    	}
    
    	$needed_dirs = array_unique($needed_dirs);
    	foreach ( $needed_dirs as $dir ) {
    		// Check the parent folders of the folders all exist within the creation array.
    		if ( $this->untrailingslashit($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
    			continue;
    		if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
    			continue;
    
    		$parent_folder = dirname($dir);
    		while ( !empty($parent_folder) && $this->untrailingslashit($to) != $parent_folder && !in_array($parent_folder, $needed_dirs) ) {
    			$needed_dirs[] = $parent_folder;
    			$parent_folder = dirname($parent_folder);
    		}
    	}
    	asort($needed_dirs);
    
    	// Create those directories if need be:
    	foreach ( $needed_dirs as $_dir ) {
    		if ( ! $this->filesystem->mkdir($_dir, 0755) && ! $this->filesystem->is_dir($_dir) ) { // Only check to see if the Dir exists upon creation failure. Less I/O this way.
    		    $this->riseError('mkdir_failed: Could not create directory '. $_dir);
    		    return false;
    		} 
    	}
    	unset($needed_dirs);
    
    	for ( $i = 0; $i < $z->numFiles; $i++ ) {
    		if ( ! $info = $z->statIndex($i) ) {
    		    $this->riseError('stat_failed: Could not retrieve file from archive.');
    		    return false;
    		}
    
    		if ( '/' == substr($info['name'], -1) ) // directory
    			continue;
    
    		if ( '__MACOSX/' === substr($info['name'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
    			continue;
    
    		$contents = $z->getFromIndex($i);
    		if ( false === $contents ) {
    		    $this->riseError('extract_failed: Could not extract file from archive.'.$info['name']);
    		    return false;
    		}
    
    		if ( ! $this->filesystem->put_contents( $to . $info['name'], $contents, 0755) ) {
    		    $this->riseError('copy_failed: Could not copy file. '.$to . $info['name']);
    		    return false;
    		}
    	}
    
    	$z->close();
    
    	return true;
    }
    
    function untrailingslashit($string) {
    	return rtrim($string, '/');
    }
    
    
    
    function get_js_update_function ( $json_string ) {
        
        $rs .= '<script type="text/javascript">
        var update_info_json_string = \''.$json_string.'\'; 
</script>';
        $rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/sitebill/js/updater.js"></script> ';
        return $rs;        
    }
    
    /**
     * Load installed apps and generate menu items from XML-files
     * @param void
     * @return array
     */
    function load_apps () {
	if (!function_exists('file_get_html')) {
	    if (file_exists(SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php')) {
		require_once SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php';
	    } else {
		require_once SITEBILL_DOCUMENT_ROOT . '/third/simple_html_dom/simple_html_dom.php';
	    }
	}
	
    	$menu=array();
    
    	if (is_dir($this->apps_dir)) {
    		if ($dh = opendir($this->apps_dir)) {
    			while (($app_dir = readdir($dh)) !== false) {
    				if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
    					if ( is_file($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml') ) {
    						 
    						//Parsing by simple_xml_dom
    						$xml = @file_get_html($this->apps_dir.'/'.$app_dir.'/'.$app_dir.'.xml');
    						if($xml && is_object($xml)){
    							//$title=SiteBill::iconv('UTF-8', 'UTF-8', $xml->find('administration',0)->find('menu',0)->innertext());
    							$action=(string)$xml->find('name',0)->innertext();
    							$version=(string)$xml->find('version',0)->innertext();
    							//$menu[$action]['name'] = $action;
    							//$menu[$action]['title'] = '';
    							$menu[$action]['version'] = $version;
    							//$menu[$action]['href'] = 'index.php?action='.$action;
    							if ( isset($_REQUEST[$action]) AND ($_REQUEST[$action] == $action) ) {
    								$menu[$action]['active'] = 1;
    							}
    						}
    					}
    
    				}
    			}
    			closedir($dh);
    		}
    	}
    	if(!empty($menu)){
    		ksort($menu);
    	}
    	return $menu;
    }
}