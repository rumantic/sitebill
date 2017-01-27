<?php
class Multilanguage {
    private static $instance=NULL;
    private static $default_lang='ru';
    private static $default_mode='backend';
    private static $current_lang='';
    private static $current_mode='';
    
    private $language='ru';
    private $mode='frontend';
    private static $words=array();
    private static $apps_words=array();
    private static $backend_words=array();
    private static $frontend_words=array();
    
    private static $is_tpl_loaded=false;
    
    public static function start($mode='',$lang_code=''){
    	self::setOptions($mode,$lang_code);
    }


    public static function getInstance($mode='',$lang_code=''){
    	if(self::$instance==NULL){
            self::$instance=new Multilanguage();
            self::$instance->setOpt($mode,$lang_code);
        }else{
        	self::$instance->setOpt($mode,$lang_code);
        }
        return self::$instance;
    }
    
    public static function is_set($key, $app=''){
    	if($app!='' && isset(self::$apps_words[$app])){
    		if(isset(self::$apps_words[$app][$key])){
    			return true;
    		}else{
    			return false;
    		}
    	}else{
    		if(isset(self::$words[$key])){
    			return true;
    		}else{
    			return false;
    		}
    	} 
    }
   
    public static function _($key,$app=''){
    	if($app!='' && isset(self::$apps_words[$app])){
    		if(isset(self::$apps_words[$app][$key])){
    			return self::$apps_words[$app][$key];
    		}else{
    			return $app.'.'.$key;
    		}
    	}else{
    		if(isset(self::$words[$key])){
    			return self::$words[$key];
    		}else{
    			return $key;
    		}
    	}
    	
    }
    
	public static function text($key){
		if(isset(self::$words[$key])){
    		return self::$words[$key];
		}else{
			return $key;
		}
    }
    
    public static function appendAppDictionary($app_name, $template=''){
    	if(isset(self::$apps_words[$app_name])){
    		return;
    	}elseif(self::$current_lang==''){
    		return;
    	}
    	//echo $app_name.'='.self::$current_lang.'<br>';
    	global $smarty;
    	$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/language/'.self::$current_lang.'/dictionary.ini';
    	if(file_exists($file_name)){
    		self::$apps_words[$app_name]=parse_ini_file($file_name,true);
    	}else{
    		$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/language/'.self::$default_lang.'/dictionary.ini';
    		if(file_exists($file_name)){
    			self::$apps_words[$app_name]=parse_ini_file($file_name,true);
    		}
    	}
    	
    	$SConfig=SConfig::getInstance();
    	$template=$SConfig->getConfigValue('theme');
    	
    	if($template!='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$template.'/apps/'.$app_name.'/language/'.self::$current_lang.'/dictionary.ini')){
    		$words=self::$apps_words[$app_name];
    		$new_words=parse_ini_file(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$template.'/apps/'.$app_name.'/language/'.self::$current_lang.'/dictionary.ini', true);
    		if(isset(self::$apps_words[$app_name])){
    			self::$apps_words[$app_name]=array_merge(self::$apps_words[$app_name], $new_words);
    		}else{
    			self::$apps_words[$app_name]=$new_words;
    		}
    		
    	}
    	
    	self::assign($smarty);
    }
    
    public static function assign(&$smarty){
        if ( !is_object($smarty) ) {
            return false;
        }
    	foreach(self::$words as $k=>$w){
    		$smarty->assign($k,$w);
    	}
    	
    	$smarty->assign('apps_words',self::$apps_words);
    }
    
    private function __construct(){
    	
    }
    
    private function __clone(){
        
    }
    
	private static function setOptions($mode, $lang_code){
		$lang_code=trim(preg_replace('/[^a-z]/i', '', $lang_code));
		if($mode!='' AND in_array($mode,array('frontend','backend'))){
    		self::$current_mode=$mode;
    	}else{
    		self::$current_mode=(self::$current_mode=='' ? self::$default_mode : self::$current_mode);
    	}
    	if($lang_code!=''){
    		self::$current_lang=$lang_code;
    	}else{
    		self::$current_lang=(self::$current_lang=='' ? self::$default_lang : self::$current_lang);
    	}
    	self::loadWords();
    	global $smarty;
    	self::assign($smarty);
    	
    }
    
    private function setOpt($mode,$lang_code){
    	if($mode!='' AND in_array($mode,array('frontend','backend'))){
    		self::$current_mode=$mode;
    	}else{
    		self::$current_mode=(self::$current_mode=='' ? self::$default_mode : self::$current_mode);
    	}
    	if($lang_code!=''){
    		self::$current_lang=$lang_code;
    	}else{
    		self::$current_lang=(self::$current_lang=='' ? self::$default_lang : self::$current_lang);
    	}
    	self::loadWords();
    	global $smarty;
    	self::assign($smarty);
    	
    }
    
    private static function loadWords(){
    	$dictionary=array();
    	if(empty(self::$words)){
    		self::loadBackendWords();
    		self::loadFrontendWords();
    		self::$words = array_merge(self::$words, self::$backend_words);
    		self::$words = array_merge(self::$words, self::$frontend_words);
    	}
    	/* 
    	$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$current_lang.'/'.self::$current_mode.'.ini';
        if(file_exists($file_name)){
        	
        }else{
        	$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$default_lang.'/'.self::$default_mode.'.ini';
        }
        self::$words=parse_ini_file($file_name,true);*/
    	
        /*if ( self::$current_mode == 'frontend' ) {
            self::loadBackendWords();
            self::$words = array_merge(self::$words, self::$backend_words);
        }*/
        
    }
    
    
    
    public static function appendTemplateDictionary($template_name){
    	if(self::$is_tpl_loaded){
    		return;
    	}
    	global $smarty;
    	$file_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$template_name.'/language/'.self::$current_lang.'/dictionary.ini';
    	
    	if(file_exists($file_name)){
    		
    		$words=parse_ini_file($file_name, true);
	    	if ( !is_object($smarty) ) {
	            return false;
	        }
	    	foreach($words as $k=>$w){
	    		self::$apps_words['_template'][$k]=$w;
	    		self::$words[$k]=$w;
	    		$smarty->assign($k,$w);
	    	}
	    	self::$is_tpl_loaded=true;
    	}
    }
    
    private static function loadBackendWords () {
        $file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$current_lang.'/backend.ini';
        if(file_exists($file_name)){
        	 
        }else{
        	$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$default_lang.'/backend.ini';
        }
        self::$backend_words=parse_ini_file($file_name,true);
        
    }
    
    private static function loadFrontendWords () {
    	$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$current_lang.'/frontend.ini';
    	if(file_exists($file_name)){
    
    	}else{
    		$file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$default_lang.'/frontend.ini';
    	}
    	self::$frontend_words=parse_ini_file($file_name,true);
    
    }
    
    public static function availableLanguages(){
    	$langs=array();
    	
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/language/admin/admin.php';
    	$LM=new language_admin();
    	
    	$_langs=$LM->getLanguages();
    	if(count($_langs)>0){
    		foreach($_langs as $lk=>$lv){
    			$langs[$lk]=$lk;
    		}
    	}
    	return $langs;
    	/*$path=SITEBILL_DOCUMENT_ROOT.'/apps/system/language/';
    	$skip = array('.', '..', '.svn');
    	$files = scandir($path);
    	foreach($files as $file) {
    		if(!in_array($file, $skip)){
    			$langs[$file]=$file;
    		}
    	}
    	return $langs;*/
    }
    
    public static function get_current_language () {
        return $_SESSION['_lang'];
    }
    
    public static function foreignLanguages(){
        $languages = self::availableLanguages();
        unset($languages['ru']);
        return $languages;
    }
}