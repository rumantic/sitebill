<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * TPL files editor admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class tpleditor_admin extends Object_Manager {
    /**
     * Constructor
     */
	private $current_theme;
	
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('tpleditor');
        $this->current_theme=$this->getConfigValue('theme');
        $this->action = 'tpleditor';
        $this->path=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->current_theme.'/';
        $this->filelist=$this->getFileList();
    }
    
    function main(){
    	global $smarty;
    	if($this->isDemo()){
    		$smarty->assign('demomode','yes');
    	}
    	$smarty->assign('topmenu',$this->getTopMenu());
    	$smarty->assign('current_theme',$this->current_theme);
    	
    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
    	$config_admin = new config_admin();
    	 
    	switch($this->getRequestValue('do')){
    		case 'edit' : {
    			$file=$this->getRequestValue('file');
    			if(in_array($file, $this->filelist)){
    				//echo $file;
    				if(preg_match('/.*\.css$/',$file)){
    					$fc=file($this->path.'css/'.$file);
    					$smarty->assign('editor_mode','text/css');
    				}else{
    					$fc=file($this->path.$file);
    					$smarty->assign('editor_mode','text/html');
    				}
    				
    				$smarty->assign('filetext',implode('', $fc));
    				$smarty->assign('file',$file);
    				$smarty->assign('left_col_tpl',SITEBILL_DOCUMENT_ROOT.'/apps/tpleditor/admin/template/edit.tpl.html');
    			}
    			break;
    		}
    		case 'new' : {
    			$smarty->assign('editor_mode','text/html');
    			$smarty->assign('left_col_tpl',SITEBILL_DOCUMENT_ROOT.'/apps/tpleditor/admin/template/new.tpl.html');
    			break;
    		}
    		case'new_done' : {
    			if(!$this->isDemo()){
	    			$file=$this->getRequestValue('file_name');
	    			$file.='.tpl';
	    			if(!in_array($file, $this->filelist)){
	    				$f=fopen($this->path.$file,'w');
	    				$content=$_POST['content'];
						if(get_magic_quotes_gpc()){
	    					$content=stripslashes($content);
	    				}
	    				fwrite($f, $content);
	    				fclose($f);
	    			}
	    			$this->filelist=$this->getFileList();
    			}
    			break;
    		}
    		case'edit_done' : {
    			if(!$this->isDemo()){
	    			$file=$this->getRequestValue('file');
	    			if(in_array($file, $this->filelist)){
	    				if(preg_match('/.*\.css$/',$file)){
	    					$f=fopen($this->path.'css/'.$file,'w');
	    				}else{
	    					$f=fopen($this->path.$file,'w');
	    				}
	    				//$f=fopen($this->path.$file,'w');
	    				$content=$_POST['content'];
	    				if(get_magic_quotes_gpc()){
	    					$content=stripslashes($content);
	    				}
	    				fwrite($f, $content);
	    				fclose($f);
	    			}
    			}
    			break;
    		}
    		case 'logo' : {
    			$logo_confparam='template.'.$this->getConfigValue('theme').'.logo';
    			$logo=$this->getConfigValue($logo_confparam);
				$error_message='';
    			
    			if(isset($_POST['submit'])){
    				if(!$this->isDemo()){
	    				if($_FILES["new_logo"]["size"] > 1024*3*1024){
							$error_message=sprintf(Multilanguage::_('L_FILE_BIGGER_THAN'),'3');
						}else{
							if(is_uploaded_file($_FILES["new_logo"]["tmp_name"])){
								$parts=explode('.',$_FILES["new_logo"]["name"]);
								$ext=$parts[count($parts)-1];
								if(!in_array(strtolower($ext),array('jpg','jpeg','png','gif'))){
									$_FILES=array();
									$error_message=Multilanguage::_('L_INVALID_FILETYPE');
								}else{
									$fname='logo_'.time().'.'.$ext;
									move_uploaded_file($_FILES["new_logo"]["tmp_name"], SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/img/'.$fname);
									chmod(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/img/'.$fname, 0755);
									
									if ( !$config_admin->check_config_item($logo_confparam) ) {
										$config_admin->addParamToConfig($logo_confparam,$fname,'Лого шаблона');
									} else {
										$DBC=DBC::getInstance();
										$query='UPDATE '.DB_PREFIX.'_config SET value=? WHERE config_key=?';
										$DBC->query($query, array($fname, $logo_confparam));
									}
										
									$logo=$fname;
								}
								
							}else{
								$error_message=Multilanguage::_('L_UPLOADING_FILE_ERROR');
							}
						}
					  
						
    				}
    			}
				$smarty->assign('error_message',$error_message);
    			$smarty->assign('logo',$logo);
    			$smarty->assign('left_col_tpl',SITEBILL_DOCUMENT_ROOT.'/apps/tpleditor/admin/template/logo.tpl.html');
    			
    			break;
    		}
    		default : {
    			
    		}
    	}
    	$smarty->assign('tplfiles',$this->filelist);
    	$smarty->assign('right_col_tpl',SITEBILL_DOCUMENT_ROOT.'/apps/tpleditor/admin/template/grid.tpl.html');
    	$rs=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/tpleditor/admin/template/main.tpl.html');
    	return $rs;
    }
    
    function getFileList(){
    	$rs=array();
    	$path=$this->path;
		if(is_dir($path)){
			if($dh = opendir($path)){
				while(($file = readdir($dh))!== false) {
					if('file'==($ft=filetype($path . $file))){
						$rs[]=$file;
					}
				}
				closedir($dh);
			}
		}
		$path=$this->path.'css/';
		if(is_dir($path)){
			if($dh = opendir($path)){
				while(($file = readdir($dh))!== false) {
					if('file'==($ft=filetype($path . $file))){
						$rs[]=$file;
					}
				}
				closedir($dh);
			}
		}
		sort($rs);
		return $rs;
    }
   
	function getTopMenu () {
	    $rs = '';
	    $rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">'.Multilanguage::_('NEW_TEMPLATE','tpleditor').'</a>';
	    $rs .= ' <a href="?action='.$this->action.'&do=logo" class="btn btn-primary">'.Multilanguage::_('LOGOTYPE','tpleditor').'</a>';
		return $rs;
	}
	
	
}