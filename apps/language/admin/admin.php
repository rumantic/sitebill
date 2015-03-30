<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Language admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class language_admin extends Object_Manager {
	
	private $apps_path;
	private $_lang_codes=array();
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
    	$this->SiteBill();
    	$this->apps_path=SITEBILL_DOCUMENT_ROOT.'/apps/';
    	$this->action='language';
    	
        //print_r($this->getLanguages());
        //echo '<pre>';
        //$this->loadAppWords('news');
        
    	$this->_lang_codes=array(
    		'ru'=>'Русский',
    		'en'=>'English'
    	);
        
    }
    
    function _preload(){
    	//echo 1;
    	$this->template->assert('available_langs',$this->getLanguages());
    }
    
    function main () {
    	   $rs=$this->getTopMenu();
    	switch( $this->getRequestValue('do') ){
    		case 'structure' : {
    			break;
    		}
    
    		case 'edit_done' : {
    			break;
    		}
    		
    		case 'edit_dictionary' : {
    			$rs.=$this->getAppDictionaryEditForm($this->getRequestValue('app_name'), $this->getRequestValue('dictionary'));
    			break;
    		}
    		case 'save_words' : {
    			//echo '<pre>';
    			//print_r($_POST);
    			//dictionary_key
    			//dictionary_value
    			$app_name=$this->getRequestValue('app_name');
    			$this->saveWords($app_name, $this->getRequestValue('dictionary_key'), $this->getRequestValue('dictionary_value'));
    			$rs.=$this->grid();
    			//$rs.=$this->getAppDictionaryEditForm($this->getRequestValue('app_name'), $this->getRequestValue('dictionary'));
    			break;
    		}
    		case 'all_words' : {
    			$rs.=$this->getAllWordsEditForm($this->getRequestValue('app_name'));
    			break;
    		}
    
    		case 'edit' : {
    			break;
    		}
    		case 'delete' : {
    			break;
    		}
    			
    		case 'new_done' : {
    			break;
    		}
    			
    		case 'new' : {
    			break;
    		}
    		case 'mass_delete' : {
    			break;
    		}
    		default : {
    			
    			$rs .= $this->grid();
    		}
    	}
    	$rs_new = $this->get_app_title_bar();
    	$rs_new .= $rs;
    	return $rs_new;
    }
    
    function getLanguages(){
    	$langs=array();
    	$path=SITEBILL_DOCUMENT_ROOT.'/apps/system/language/';
	    $skip = array('.', '..', '.svn');
		$files = scandir($path);
		foreach($files as $file) {
		    if(!in_array($file, $skip)){
		    	if ( isset($this->_lang_codes[$file]) && $this->_lang_codes[$file] != '' ) {
		    		$langs[$file]=$this->_lang_codes[$file];
		    	} else {
		    		$langs[$file]=$file;
		    	}
		    }
		        
		}
		//print_r($langs);
		/*return array(
				'ru'=>'Русский',
				'en'=>'Англйский'
		);*/
		return $langs;
    }
    
    private function saveWords($app_name, $terms, $values){
    	if(count($terms)==0 || count($values)==0 || $app_name==''){
    		return;
    	}
    	if(!file_exists($this->apps_path.$app_name.'/')){
    		return;
    	}
    	$first_key=array_shift(array_keys($values));
    	//echo $first_key;
    	$langs=array_keys($values[$first_key]);
    	foreach($langs as $lang){
    		if(!file_exists($this->apps_path.$app_name.'/language/')){
    			mkdir($this->apps_path.$app_name.'/language');
    		}
    		if(!file_exists($this->apps_path.$app_name.'/language/'.$lang)){
    			mkdir($this->apps_path.$app_name.'/language/'.$lang);
    		}
    		$f=fopen($this->apps_path.$app_name.'/language/'.$lang.'/dictionary.ini','w');
    		$str='';
    		foreach($terms as $term_k=>$term){
    			if($this->clear($term)!='')
    			$str.=$this->clear($term).'="'.$this->clear($values[$term_k][$lang]).'"'."\n";
    		}
    		fwrite($f,$str);
    		fclose($f);
    		//echo $str;
    	}
    	
    	//print_r($langs);
    	//$langs=array_keys(array);
    }
    
    private function clear($val){
    	if(get_magic_quotes_gpc()){
    		$val=stripslashes($val);
    	}
    	return trim($val);
    }
    
    private function getAllWordsEditForm($app_name){
    	if(file_exists($path=$this->apps_path.$app_name.'/language/')){
    		$path=$this->apps_path.$app_name.'/language/';
    		$dictionary=array();
    		$skip = array('.', '..');
    		$langs = scandir($path);
    		foreach($langs as $lang) {
    			if(!in_array($lang, $skip)){
    				$words=array();
    				$words=$this->getAppDictionary($app_name, $lang);
    				$dictionary[$lang]=$words;
    			}
    			 
    		}
    		$x=array();
    		$langs_array=array();
    		if(count($dictionary)>0){
    			foreach($dictionary as $lang=>$words){
    				$langs_array[$lang]=$lang;
    				foreach($words as $key=>$trans){
    					$x[$key][$lang]=$trans;
    				}
    			}
    		}
    		$keys=array_keys($x);
    	}
    	
    	
    	global $smarty;
    	$rs='';
    	//$words=$this->getAppDictionary($app_name, $dictionary);
    	$smarty->assign('app_name',$app_name);
    	$smarty->assign('langs',$langs_array);
    	$smarty->assign('keys',$keys);
    	$smarty->assign('words',$x);
    	$rs=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/language/admin/template/apps_words_edit_form.tpl.html');
    	 
    	return $rs;
    }
    
    private function getAppDictionaryEditForm($app_name,$dictionary){
    	global $smarty;
    	$rs='';
    	$words=$this->getAppDictionary($app_name, $dictionary);
    	$smarty->assign('words',$words);
    	$rs=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/language/admin/template/apps_dictionary_edit_form.tpl.html');
    	
    	return $rs;
    }
    
    
    private function getAppDictionary($app_name,$dictionary){
    	$words=array();
    	if(file_exists($this->apps_path.$app_name.'/language/'.$dictionary.'/dictionary.ini')){
    		$words=parse_ini_file($this->apps_path.$app_name.'/language/'.$dictionary.'/dictionary.ini',true);
    	}
    	return $words;
    }
    
    /*function loadAllWords(){
    	$apps=array();
    	$path=$this->apps_path;
    	$skip = array('.', '..');
    	$appsf = scandir($path);
    	foreach($appsf as $app) {
    		if(!in_array($app, $skip)){
    			$apps[$app]=array();
    		}
    	}
    	foreach($apps as $k=>$app) {
    		if(file_exists($path.$k.'/language/')){
    			$lang_folders=scandir($path.$k.'/language/');
    			foreach($lang_folders as $lang_folder) {
    				if(!in_array($lang_folder, $skip)){
    					$apps[$k][$lang_folder]=array();
    				}
    			
    			}
    		}
    		
    	}
    	foreach($apps as $k=>$app) {
    		if(count($app)>0){
    			foreach($app as $l=>$v) {
    				if(file_exists($path.$k.'/language/'.$l.'/dictionary.ini')){
    					$apps[$k][$l]=parse_ini_file($path.$k.'/language/'.$l.'/dictionary.ini',true);
    				}
    				
    			}
    		}
    	}
    	
    	foreach($apps as $k=>$app){
    		foreach($app as $lk=>$ld){
    			foreach($ld as $wk=>$wv){
    				$words[$k][$wk][$lk]=$wv;
    				 
    			}	
    		}
    	}
    	print_r($words);
    	return $apps;
    }*/
    
    /*function loadAppWords($app_name){
    	$words=array();
    	$skip = array('.', '..');
    	$path=$this->apps_path;
    	if(file_exists($path.$app_name.'/language/')){
    		$lang_folders=scandir($path.$app_name.'/language/');
    		foreach($lang_folders as $lang_folder) {
    			if(!in_array($lang_folder, $skip)){
    				if(file_exists($path.$app_name.'/language/'.$lang_folder.'/dictionary.ini')){
    					$words[$lang_folder]=parse_ini_file($path.$app_name.'/language/'.$lang_folder.'/dictionary.ini',true);
    				}
    			}
    		}
    	}
    	if(count($words)>0){
    		foreach($words as $l=>$lang){
    			foreach($lang as $k=>$v){
    				$_words[$k][$l]=$v;
    			}
    		}
    	}
    	print_r($words);
    	print_r($_words);
    }*/
    
    //function 
    
	
    
    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu () {
    	$rs = '';
    	$rs .= '<a href="?action='.$this->action.'" class="btn btn-primary">Список</a>';
    	return $rs;
    }
    
   
    
    /**
     * Grid
     * @param void
     * @return string
     */
    
    function grid () {
    	global $smarty;
    	$apps=array();
    	$path=$this->apps_path;
    	$skip = array('.', '..');
    	$appsf = scandir($path);
    	foreach($appsf as $app) {
    		if(!in_array($app, $skip)){
    			$apps[$app]=array();
    			if(file_exists($path.$app.'/language/')){
    				$appsfl = scandir($path.$app.'/language/');
    				foreach($appsfl as $appl) {
    					if(!in_array($appl, $skip)){
    						if(file_exists($path.$app.'/language/'.$appl.'/dictionary.ini')){
    							$apps[$app][$appl]=1;
    						}else{
    							//$apps[$app][$appl]=0;
    						}
    						
    					}
    				}
    			}
    			
    		}
    	}
    	if(count($apps)>0)
    	$smarty->assign('apps',$apps);
    	$rs=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/language/admin/template/apps_grid.tpl.html');
    	
    	return $rs;
    	
    }
    
   
}