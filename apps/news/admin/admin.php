<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * News admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class news_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('news');
        $this->action = 'news';
        $this->use_topics=false;
        $this->app_title = Multilanguage::_('APPLICATION_NAME','news');
        
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_model.php');
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_topic_model.php');
        $section=$this->getRequestValue('section');
        if($section===NULL){
        	if(isset($_SESSION['_news_section']) && $_SESSION['_news_section']!=''){
        		$this->section=$_SESSION['_news_section'];
        	}else{
        		$this->section='news';
        		$_SESSION['_news_section']='news';
        	}
        }else{
        	$this->section=$this->getRequestValue('section');
        	$_SESSION['_news_section']=$this->section;
        }
        if($this->section=='topic'){
        	$this->initNewsTopicModel();
        }else{
        	$this->initNewsModel();
        }
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
         
        if ( !$config_admin->check_config_item('apps.news.enable') ) {
        	$config_admin->addParamToConfig('apps.news.enable','0','Включить News.Apps');
        }
        
        if ( !$config_admin->check_config_item('apps.news.use_news_topics') ) {
        	$config_admin->addParamToConfig('apps.news.use_news_topics','0','Использовать категории для новостей');
        }
        
       if(1==$this->getConfigValue('apps.news.use_news_topics')){
        	$this->use_topics=true;
        }
        
        if ( !$config_admin->check_config_item('apps.news.alias') ) {
        	$config_admin->addParamToConfig('apps.news.alias','news','Алиас адресов приложения');
        }
        
        if ( !$config_admin->check_config_item('apps.news.item_alias') ) {
        	$config_admin->addParamToConfig('apps.news.item_alias','news','Подстановочный алиас');
        }
        if ( !$config_admin->check_config_item('apps.news.app_title') ) {
        	$config_admin->addParamToConfig('apps.news.app_title','Архив новостей','Заголовок приложения');
        }
        if ( !$config_admin->check_config_item('apps.news.folder_title') ) {
        	$config_admin->addParamToConfig('apps.news.folder_title','Новости','Заголовок приложения в хлебных крошках');
        }
        
        if ( !$config_admin->check_config_item('apps.news.append_more_news_view') ) {
        	$config_admin->addParamToConfig('apps.news.append_more_news_view','1','Выводить дополнительные новости в просмотре новости');
        }
        
        if ( !$config_admin->check_config_item('apps.news.append_more_news_view_count') ) {
        	$config_admin->addParamToConfig('apps.news.append_more_news_view_count','2','Количество дополнительных новостей в просмотре новости');
        }
        
        if ( !$config_admin->check_config_item('apps.news.meta_title') ) {
        	$config_admin->addParamToConfig('apps.news.meta_title','','META заголовок');
        }
        
        if ( !$config_admin->check_config_item('apps.news.meta_desription') ) {
        	$config_admin->addParamToConfig('apps.news.meta_desription','','META описание');
        }
        
        if ( !$config_admin->check_config_item('apps.news.meta_keywords') ) {
        	$config_admin->addParamToConfig('apps.news.meta_keywords','','META ключевые слова');
        }
        //$this->install();
    }
    
    protected function _newAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$form_data[$this->table_name]['date']['value']=time();
    	$rs = $this->get_form($form_data[$this->table_name]);
    	return $rs;
    }
    /*
    function main(){
    	$rs.=parent::main();
    	return $rs;
    }
    */
    protected function initNewsModel(){
    	$this->table_name = 'news';
    	$this->primary_key = 'news_id';
    	$form_data = array();
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    		$ATH=new Admin_Table_Helper();
    		$form_data=$ATH->load_model($this->table_name, false);
    		if(empty($form_data)){
    			$form_data = array();
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_model.php');
    			$Object=new News_Model();
    			$form_data = $Object->get_model();
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
    			$TA=new table_admin();
    			$TA->create_table_and_columns($form_data, $this->table_name);
    			$form_data = array();
    			$form_data=$ATH->load_model($this->table_name, false);
    		}
    	
    	}else{
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_model.php');
    		$Object=new News_Model();
    		$form_data = $Object->get_model();
    	}
    	
    	$this->data_model=$form_data;
    	
    }
    
    protected function initNewsTopicModel(){
    	$this->table_name = 'news_topic';
    	$this->primary_key = 'id';
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    		$ATH=new Admin_Table_Helper();
    		$form_data=$ATH->load_model($this->table_name, false);
    		if(empty($form_data)){
    			$form_data = array();
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_topic_model.php');
    			$Object=new News_Topic_Model();
    			$form_data = $Object->get_model();
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
    			$TA=new table_admin();
    			$TA->create_table_and_columns($form_data, $this->table_name);
    			$form_data = array();
    			$form_data=$ATH->load_model($this->table_name, false);
    		}
    		 
    	}else{
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/news_topic_model.php');
    		$Object=new News_Topic_Model();
    		$form_data = $Object->get_model();
    	}
    	 
    	$this->data_model=$form_data;
    	
    }
    
    protected function _installAction(){
    	$this->install();
    }
    
    
    
	function add_data ( $form_data ) {
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    
	     
	    if(isset($form_data['date'])){
	    	if($form_data['date']['value']!='' && $form_data['date']['value']!='0'){
	    		$time=date('H:i:s',$form_data['date']['value']);
	    		if($time=='00:00:00'){
	    			$form_data['date']['value']=strtotime(date('d-m-Y',$form_data['date']['value']).' '.date('H:i:s',time()));
	    		}
	    	}else{
	    		$form_data['date']['value']=time();
	    	}
		    
	    }
	    
	    if(isset($form_data['newsalias']) && $form_data['newsalias']['value']==''){
	    	$form_data['newsalias']['value']=$this->get_transliteration($form_data['title']['value']);
	    }
	    
	    if(isset($form_data['newsalias']['value'])){
	    	//$form_data['newsalias']['value']=$this->get_transliteration($form_data['newsalias']['value']);
	    	$form_data['newsalias']['value']=preg_replace('/[^a-zA-Z0-9-_]/', '', $form_data['newsalias']['value']);
	    }
	    
	    if($this->section=='topic'){
	    	if($form_data['url']['value']==''){
	    		$form_data['url']['value']=$this->transliteMe($form_data['name']['value']);
	    	}
	    	
	    	$form_data['url']['value']=preg_replace('/[^a-zA-Z0-9-_]/', '', $form_data['url']['value']);
	    }
	    
	    $query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data);
	    $this->db->exec($query);
	    
	    if ( !$this->db->success ) {
	    	$this->riseError($this->db->error);
	    	return false;
	    }
	    $new_record_id = $this->db->last_insert_id();
	    
	    $imgs=array();
	     
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
	    		if(is_array($ims) && count($ims)>0){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}
	    }
	    
	    $ims=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);
	    if(is_array($ims) && count($ims)>0){
	    	$imgs=array_merge($imgs, $ims);
	    }
	    
	    //$imgs=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);
		return true;
	}
	
	function edit_data ( $form_data ) {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    if(isset($form_data['imgfile'])){
	    	$img=$form_data['imgfile'];
	    	unset($form_data['imgfile']);
	    }
	    
	    if(isset($form_data['newsalias']) && $form_data['newsalias']['value']==''){
	    	$form_data['newsalias']['value']=$this->get_transliteration($form_data['title']['value']);
	    }
	    
	    if(isset($form_data['newsalias']['value'])){
	    	//$form_data['newsalias']['value']=$this->get_transliteration($form_data['newsalias']['value']);
	    	$form_data['newsalias']['value']=preg_replace('/[^a-zA-Z0-9-_]/', '', $form_data['newsalias']['value']);
	    }
	    
	    if(isset($form_data['date'])){
	    	if($form_data['date']['value']!='' && $form_data['date']['value']!='0'){
	    	$time=date('H:i:s',$form_data['date']['value']);
		    if($time=='00:00:00'){
		    	$form_data['date']['value']=strtotime(date('d-m-Y',$form_data['date']['value']).' '.date('H:i:s',time()));
		    }
	    	}else{
	    		$form_data['date']['value']=time();
	    	}
	    }
	    
	  
	    
	    if($this->section=='topic'){
	    	if($form_data['url']['value']==''){
	    		$form_data['url']['value']=$this->transliteMe($form_data['name']['value']);
	    	}
	    	
	    	$form_data['url']['value']=preg_replace('/[^a-zA-Z0-9-_]/', '', $form_data['url']['value']);
	    }
	    
		
	    $query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data);
	    
	    
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	    	$this->riseError($this->db->error);
	    	return false;
	    }
	    
	    $imgs=array();
	    
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
	    		if(is_array($ims) && count($ims)>0){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}
	    }
	    	
	    $ims=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
	    if(is_array($ims) && count($ims)>0){
	    	$imgs=array_merge($imgs, $ims);
	    }
	    
	    //$imgs=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
	}
	
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		
		$vendor_info=$this->getVendorInfoById($primary_key_value);
        if($vendor_info['imgfile']!=''){
        	unlink(SITEBILL_DOCUMENT_ROOT.'/img/data/vendor/'.$vendor_info['imgfile']);
        }
		
	    $query = "delete from ".DB_PREFIX."_".$table_name." where $primary_key = $primary_key_value";
	    //echo $query;
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	        $this->riseError($this->db->error);
	        return false;
	    }
	}
    
    
    function install () {
        //create tables
        $query = "
			CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_news` (
			  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL DEFAULT '',
			  `description` mediumtext,
			  `date` int(11) NOT NULL DEFAULT '0',
			  `anons` mediumtext,
			  `meta_title` varchar(255) NOT NULL,
			  `meta_keywords` text NOT NULL,
			  `meta_description` text NOT NULL,
			  `newsalias` varchar(255) NOT NULL,
			  PRIMARY KEY (`news_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING.";";
        $this->db->exec($query);
        $query = "
			CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_news_image` (
			  `news_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `news_id` int(11) NOT NULL DEFAULT '0',
			  `image_id` int(11) NOT NULL DEFAULT '0',
			  `sort_order` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`news_image_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." ;
        ";
        $this->db->exec($query);
        if($this->use_topics){
        	$query = "
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_news_topic` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `url` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." ;
        ";
        	$this->db->exec($query);
        }
        
	}
	
	function getTopMenu(){
		$rs.='<a href="?action='.$this->action.'&section=news" class="btn btn-primary">Все новости</a>';
		$rs.=' <a href="?action='.$this->action.'&section=news&do=new" class="btn btn-primary">Добавить новость</a>';
		if($this->use_topics){
			$rs.=' <a href="?action='.$this->action.'&section=topic" class="btn btn-primary">Структура новостей</a>';
			$rs.=' <a href="?action='.$this->action.'&section=topic&do=new" class="btn btn-primary">Добавить раздел</a>';
		}
		return $rs;
	}
    
    /**
     * Grid
     * @param void
     * @return string
     */
     
     function grid () {
    	
    	$params=array();
    	$params['action']=$this->action;
    	
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page.php');
        $common_page = new Common_Page();
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    	$common_tab = new Common_Tab();
		$url='/admin/index.php?action='.$this->action;
		
		$common_grid->set_grid_table($this->table_name);
		if($this->section=='topic'){
			$params['section']=$this->section;
			$common_grid->add_grid_item('id');
			$common_grid->add_grid_item('name');
			$common_grid->add_grid_item('url');
			//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY name DESC, id DESC");
		}else{
			$common_grid->add_grid_item('news_id');
	        $common_grid->add_grid_item('date');
	    	$common_grid->add_grid_item('title');
	    	$common_grid->add_grid_item('anons');
	    	if($this->use_topics){
	    		$common_grid->add_grid_item('news_topic_id');
	    	}
	        //$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY date DESC, news_id DESC");
		}
		
    	
        
        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
		//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY date DESC, news_id DESC");
		$params['page']=$this->getRequestValue('page');
		$params['per_page']=$this->getConfigValue('common_per_page');
        
        $common_grid->setPagerParams($params);
        
        $common_page->setTab($common_tab);
        $common_page->setGrid($common_grid);
        
		$rs .= $common_page->toString();
		return $rs;
    }
    
    function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php' ) {
    
    	$_SESSION['allow_disable_root_structure_select']=true;
    	global $smarty;
    	if($button_title==''){
    		$button_title = Multilanguage::_('L_TEXT_SAVE');
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
    	$form_generator = new Form_Generator();
    	
    		
    	$rs .= $this->get_ajax_functions();
    	if(1==$this->getConfigValue('apps.geodata.enable')){
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
    	}
    	$rs .= '<form method="post" class="form-horizontal" action="'.$action.'" enctype="multipart/form-data">';
    	if($this->section!='topic'){
    	$rs.='<a class="btn btn-info alias_create" href="">'.Multilanguage::_('CREATE_ALIAS','news').'</a>';
    	$rs.='<script>
    			$(document).ready(function(){
    			$(\'.alias_create\').click(function(){
    			var parent=$(this).parents(\'form\').eq(0);
    			var title=parent.find(\'input[name=title]\');
    			var newsalias=parent.find(\'input[name=newsalias]\');
    			if(title && newsalias && title.val()!=\'\'){
    				$.ajax({
    					url: \''.SITEBILL_MAIN_URL.'/apps/news/js/ajax.php\',
    					type: \'post\',
    					data: {action: \'get_transliteration\', word: title.val()},
    					dataType: \'text\',
    					success: function(text){
    						newsalias.val(text);	
    					}
    				});
    			}
    			
    			return false;
    			});
    			});</script>';
    	}
    	if ( $this->getError() ) {
    		$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
    	}
    		
    	$el = $form_generator->compile_form_elements($form_data);
    
    	if ( $do == 'new' ) {
    		$el['private'][]=array('html'=>'<input type="hidden" name="do" value="new_done" />');
    		$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'" />');
    	} else {
    		$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
    		$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'" />');
    	}
    	$el['private'][]=array('html'=>'<input type="hidden" name="action" value="'.$this->action.'">');
    	$el['private'][]=array('html'=>'<input type="hidden" name="language_id" value="'.$language_id.'">');
    	$el['private'][]=array('html'=>'<input type="hidden" name="section" value="'.$this->section.'">');
    	
    	$el['form_header']=$rs;
    	$el['form_footer']='</form>';
    		
    	$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
    		
    	$smarty->assign('form_elements',$el);
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
    	}else{
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
    	}
    	return $smarty->fetch($tpl_name);
    
    
    }
    
    function _preload(){
    	global $smarty;
    	if($this->getConfigValue('apps.news.enable')){
    		//$news=$this->getNewsList();
    		$smarty->assign('news_list_column_html', $this->getNewsListBlock());
    	}else{
    		$smarty->assign('news_list_column_html', '');
    	}
    	return true;
    }
    
    function ajax () {
    	if ( $this->getRequestValue('action') == 'get_transliteration' ) {
    		$word=$this->getRequestValue('word');
    		return $this->get_transliteration($word);
    	}
    	return false;
    }
    
    private function get_transliteration($word){
    	return $this->transliteMe($word);
    }
    
    function getNewsListBlock(){
    	global $smarty;
    	$news=$this->getNewsList();
    	$smarty->assign('news_list_column', $news);
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/news_list_column.tpl')){
    		return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/news_list_column.tpl');
    	}else{
    		return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/news/site/template/news_list_column.tpl');
    	}
    }
    
    function getNewsList(){
    	$where=array();
    	$news=array();
    	if(isset($this->data_model[$this->table_name]['spec'])){
    		$control_spec=true;
    		$where[]='n.`spec`=1';
    	}else{
    		$control_spec=false;
    	}
    	
    	if(''!=$this->getConfigValue('apps.news.item_alias')){
    		$app_item_alias=$this->getConfigValue('apps.news.item_alias');
    	}else{
    		$app_item_alias='news';
    	}
    	
    	$count=$this->getConfigValue('apps.news.news_line.per_page');
    	if($count==0){
    		$count=4;
    	}
    	
    	$hasUploadify=false;
    	$uploads=false;
    	foreach ($this->data_model[$this->table_name] as $mitem){
    		if($mitem['type']=='uploadify_image'){
    			$hasUploadify=true;
    			continue;
    		}
    	}
    	if(!$hasUploadify){
    		foreach ($this->data_model[$this->table_name] as $mitem){
    			if($mitem['type']=='uploads'){
    				$uploads=$mitem['name'];
    				continue;
    			}
    		}
    	}
    	/*
    	if(1==$this->getConfigValue('apps.news.use_news_topics')){
    		$query='SELECT '.$this->primary_key.' FROM '.DB_PREFIX.'_'.$this->table_name.' '.($control_spec ? ' WHERE `n.spec`=1' : '').' ORDER BY `date` DESC LIMIT '.$count;
    		 
    	}else{
    		$query='SELECT '.$this->primary_key.' FROM '.DB_PREFIX.'_'.$this->table_name.($control_spec ? ' WHERE `spec`=1' : '').' ORDER BY `date` DESC LIMIT '.$count;
    	}
    	
    	$ids=array();
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ids[]=$ar[$this->primary_key];
    		}
    	}
    	print_r($ids);*/
    	
    	if(isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id']!=0){
    		$where[]='n.`user_id`='.$_SESSION['user_domain_owner']['user_id'];
    	}
    	
    	
    	
    	if(1==$this->getConfigValue('apps.news.use_news_topics')){
    		$query='SELECT n.*, nt.name AS news_topic_id FROM '.DB_PREFIX.'_'.$this->table_name.' n LEFT JOIN '.DB_PREFIX.'_news_topic nt ON nt.id=n.news_topic_id'.(!empty($where) ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY n.`date` DESC LIMIT '.$count;
    		 
    	}else{
    		$query='SELECT n.* FROM '.DB_PREFIX.'_'.$this->table_name.' n'.(!empty($where) ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY n.`date` DESC LIMIT '.$count;
    	}
    	
    	
    	
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		$i=0;
    		while($ar=$DBC->fetch($stmt)){
    			$ar['date']=date('d.m.Y', $ar['date']);
    			$news[$i]=$ar;
    			$news[$i]['href']=$this->getNewsRoute($ar['news_id'], $ar['newsalias']);
    			$i++;
    		}
    	}
    	 
    	
    	if(count($news)>0){
    
    		foreach($news as $k=>$n){
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    			$data_model = new Data_Model();
    			if($hasUploadify){
    				$image_array = $data_model->get_image_array ( 'news', 'news', 'news_id', $n['news_id'] );
    				if ( count($image_array) > 0 ) {
    					$news[$k]['img_preview']=$image_array[0]['img_preview'];
    				}
    			}elseif($uploads!==false){
    				$ims=$news[$k][$uploads];
    				if($ims!=''){
    					$ims=unserialize($ims);
    				}else{
    					$ims=array();
    				}
    				if(isset($ims[0])){
    					$news[$k]['img_preview']=SITEBILL_MAIN_URL.'/img/data/'.$ims[0]['preview'];
    				}
    			}
    			
    
    		}
    	}
    	return $news;
    }
    
	public function detectNewsTopic($url){
    	$DBC=DBC::getInstance();
    	$query='SELECT id, name, url FROM '.DB_PREFIX.'_news_topic WHERE url=? LIMIT 1';
    	$stmt=$DBC->query($query, array($url));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    			
    		return $ar;
    	}
    	return false;
    }
    
    public function detectNews($url){
    	$DBC=DBC::getInstance();
    	$query='SELECT news_id FROM '.DB_PREFIX.'_news WHERE newsalias=? LIMIT 1';
    	$stmt=$DBC->query($query, array($url));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		return $ar;
    	}
    	return false;
    }
	
    public function getNewsTopicsList(){
    	if(''!=$this->getConfigValue('apps.news.alias')){
    		$app_alias=$this->getConfigValue('apps.news.alias');
    	}else{
    		$app_alias='news';
    	}
    	$ret=array();
    	$DBC=DBC::getInstance();
    	$query='SELECT id, name, url FROM '.DB_PREFIX.'_news_topic ORDER BY name';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ar['url']=SITEBILL_MAIN_URL.'/'.$app_alias.'/'.$ar['url'].'/';
    			$ret[$ar['id']]=$ar;
    		}
    	}
    	return $ret;
    }
    
    protected function getNewsRoute($news_id, $news_alias=''){
    	if(''!=$this->getConfigValue('apps.news.alias')){
    		$app_news_alias=$this->getConfigValue('apps.news.alias');
    	}else{
    		$app_news_alias='news';
    	}
    	if(''!=$this->getConfigValue('apps.news.item_alias')){
    		$app_item_alias=$this->getConfigValue('apps.news.item_alias');
    	}else{
    		$app_item_alias='news';
    	}
    	if($news_alias!=''){
    		return SITEBILL_MAIN_URL.'/'.$app_news_alias.'/'.$news_alias.'/';
    	}else{
    		return SITEBILL_MAIN_URL.'/'.$app_item_alias.$news_id.'.html';
    	}
    }
}