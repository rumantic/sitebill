<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * RSS v2.0 Exporter at Harvard Law (http://cyber.law.harvard.edu/rss/rss.html) admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class rss_admin extends Object_Manager {
	
	private $length;
	private $output_file;
	
	
    /**
     * Constructor
     */
	function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('rss');
        $this->checkConfiguration();
        $this->app_title = Multilanguage::_('APPLICATION_NAME','rss');
        $this->action = 'rss';
        $this->output_file=SITEBILL_DOCUMENT_ROOT.'/rss.xml';
    }
    
    function main(){
        $rs .= $this->get_app_title_bar();
        $rs .= Multilanguage::_('RSS_STREAM_ADDRESS','rss').': <a href="'.$this->getServerFullUrl().'/rss/" target="_blank">'.$this->getServerFullUrl().'/rss/</a>';
        return $rs;
    	//$f=fopen($this->output_file,'w');
    	//fwrite($f,$this->generateRSSText());
    	//fclose($f);
    }
    
    private function checkConfiguration(){
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php';
    	$CF=new config_admin();
    	if($CF){
    		if ( !$CF->check_config_item('apps.rss.enable') ) {
				$CF->addParamToConfig('apps.rss.enable','1','Включить экспорт RSS');
			}
    		
	    	if ( !$CF->check_config_item('apps.rss.title') ) {
				$CF->addParamToConfig('apps.rss.title','Название RSS канала','Название RSS канала');
			}
			
			if ( !$CF->check_config_item('apps.rss.length') ) {
				$CF->addParamToConfig('apps.rss.length','50','Длинна RSS канала');
			}
			
			if ( !$CF->check_config_item('apps.rss.description') ) {
				$CF->addParamToConfig('apps.rss.description','Описание RSS канала','Описание RSS канала');
			}
			
			if ( !$CF->check_config_item('apps.rss.language') ) {
				$CF->addParamToConfig('apps.rss.language','ru-RU','Код языка канала согласно <a target="_blank" href="http://cyber.law.harvard.edu/rss/languages.html">http://cyber.law.harvard.edu/rss/languages.html</a>');
			}
			
			if ( !$CF->check_config_item('apps.rss.generator') ) {
				$CF->addParamToConfig('apps.rss.generator','CMS Sitebill Application RSS','Название генератора RSS канала');
			}
			
			if ( !$CF->check_config_item('apps.rss.editor_email') ) {
				$CF->addParamToConfig('apps.rss.editor_email','editor_email@somemail.ru','Адрес электронной почты лица, ответственного за редакционное содержание');
			}
			
			if ( !$CF->check_config_item('apps.rss.webmaster_email') ) {
				$CF->addParamToConfig('apps.rss.webmaster_email','webmaster_email@somemail.ru','Адрес электронной почты лица, ответственного за технические вопросы, касающиеся канала');
			}
			
			if ( !$CF->check_config_item('apps.rss.enable_realty') ) {
				$CF->addParamToConfig('apps.rss.enable_realty','0','Разрешить RSS для объявлений');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_image') ) {
				$CF->addParamToConfig('apps.rss.data_image','image','Системное имя поля фото объекта');
			}
						
			if ( !$CF->check_config_item('apps.rss.news_text') ) {
				$CF->addParamToConfig('apps.rss.news_text','','Системное имя поля с текстом для новости');
			}
			
			if ( !$CF->check_config_item('apps.rss.news_image') ) {
				$CF->addParamToConfig('apps.rss.news_image','image','Системное имя поля фото новости');
			}
			
			if ( !$CF->check_config_item('apps.rss.news_title') ) {
				$CF->addParamToConfig('apps.rss.news_title','','Системное имя поля заголовка новости');
			}
			
    	 	if ( !$CF->check_config_item('apps.rss.data_title') ) {
				$CF->addParamToConfig('apps.rss.data_title','','Системное имя поля заголовка объекта');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_description') ) {
				$CF->addParamToConfig('apps.rss.data_description','','Системное имя поля описания объекта');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_description_max') ) {
				$CF->addParamToConfig('apps.rss.data_description_max','','Максимальная длинна описания');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_descriptionfields') ) {
				$CF->addParamToConfig('apps.rss.data_descriptionfields','','Список системных имен полей для описания объекта');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_date') ) {
				$CF->addParamToConfig('apps.rss.data_date','','Системное имя поля даты размещения объекта');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_mode') ) {
				$CF->addParamToConfig('apps.rss.data_mode','0','Тип формирования фида объявлений (0-стандартный, 1-расширенный)');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_length') ) {
				$CF->addParamToConfig('apps.rss.data_length','50','Длинна RSS канала объявлений');
			}
			
			if ( !$CF->check_config_item('apps.rss.articles_length') ) {
				$CF->addParamToConfig('apps.rss.articles_length','50','Длинна RSS канала статей');
			}
			
			if ( !$CF->check_config_item('apps.rss.articles_text') ) {
				$CF->addParamToConfig('apps.rss.articles_text','','Системное имя поля описания статьи');
			}
			
			if ( !$CF->check_config_item('apps.rss.articles_image') ) {
				$CF->addParamToConfig('apps.rss.articles_image','image','Системное имя поля изображения статьи');
			}
				
			if ( !$CF->check_config_item('apps.rss.articles_title') ) {
				$CF->addParamToConfig('apps.rss.articles_title','','Системное имя поля заголовка статьи');
			}
			
			
			if ( !$CF->check_config_item('apps.rss.articles_cachediff') ) {
				$CF->addParamToConfig('apps.rss.articles_cachediff',0,'Время кеширование в секундах для фида статей');
			}
			
			if ( !$CF->check_config_item('apps.rss.data_cachediff') ) {
				$CF->addParamToConfig('apps.rss.data_cachediff',0,'Время кеширование в секундах для фида объявлений');
			}
			
			if ( !$CF->check_config_item('apps.rss.news_cachediff') ) {
				$CF->addParamToConfig('apps.rss.news_cachediff',0,'Время кеширование в секундах для фида новостей');
			}
			
		}
    	unset($CF);
    }
    
    protected function exportRssData(){
    	if(intval($this->getConfigValue('apps.rss.data_cachediff'))>0){
    		$with_cache=true;
    		$cashe_diff=intval($this->getConfigValue('apps.rss.data_cachediff'));
    	}else{
    		$with_cache=false;
    	}
    	
    	$cache_file=SITEBILL_DOCUMENT_ROOT.'/cache/rss_data.xml';
    	if($with_cache){
    		if(file_exists($cache_file) && ((time()-filemtime($cache_file))>$cashe_diff)){
    			unlink($cache_file);
    		}
    		if(file_exists($cache_file)){
    			$handle = @fopen($cache_file, "r");
    			if($handle) {
    				while (($buffer = fgets($handle, 4096)) !== false) {
    					echo $buffer;
    				}
    				fclose($handle);
    				return;
    			}
    		}
    		ob_start();
    	}
    	echo '<?xml version="1.0" ?>';
    	echo '<rss version="2.0">';
    	echo '<channel>';
    	echo $this->generateChannelInfo();
    	$mode=intval($this->getConfigValue('apps.rss.data_mode'));
    	switch($mode){
    		case 1 : {
    			$this->getRealtyItemsExtended();
    			break;
    		}
    		default : {
    			$this->getRealtyItemsStandart();
    		}
    	}
    	 
    	echo '</channel>';
    	echo '</rss>';
    	if($with_cache){
    		$d=ob_get_contents();
    		ob_end_clean();
    		$f=fopen($cache_file, 'w');
    		fwrite($f, $d);
    		fclose($f);
    		echo $d;
    	}
    }
    
    protected function exportRssNews(){
    	if(intval($this->getConfigValue('apps.rss.news_cachediff'))>0){
    		$with_cache=true;
    		$cashe_diff=intval($this->getConfigValue('apps.rss.news_cachediff'));
    	}else{
    		$with_cache=false;
    	}
    	 
    	$cache_file=SITEBILL_DOCUMENT_ROOT.'/cache/rss_news.xml';
    	if($with_cache){
    		if(file_exists($cache_file) && ((time()-filemtime($cache_file))>$cashe_diff)){
    			unlink($cache_file);
    		}
    		if(file_exists($cache_file)){
    			$handle = @fopen($cache_file, "r");
    			if($handle) {
    				while (($buffer = fgets($handle, 4096)) !== false) {
    					echo $buffer;
    				}
    				fclose($handle);
    				return;
    			}
    		}
    		ob_start();
    	}
    	echo '<?xml version="1.0" ?>';
    	echo '<rss version="2.0">';
    	echo '<channel>';
    	echo $this->generateChannelInfo();
    	$this->getNewsItems();
    	echo '</channel>';
    	echo '</rss>';
    	if($with_cache){
    		$d=ob_get_contents();
    		ob_end_clean();
    		$f=fopen($cache_file, 'w');
    		fwrite($f, $d);
    		fclose($f);
    		echo $d;
    	}
    }
    
    protected function exportRssArticles(){
    	if(intval($this->getConfigValue('apps.rss.articles_cachediff'))>0){
    		$with_cache=true;
    		$cashe_diff=intval($this->getConfigValue('apps.rss.articles_cachediff'));
    	}else{
    		$with_cache=false;
    	}
    	
    	$cache_file=SITEBILL_DOCUMENT_ROOT.'/cache/rss_articles.xml';
    	if($with_cache){
    		if(file_exists($cache_file) && ((time()-filemtime($cache_file))>$cashe_diff)){
    			unlink($cache_file);
    		}
    		if(file_exists($cache_file)){
    			$handle = @fopen($cache_file, "r");
				if($handle) {
				    while (($buffer = fgets($handle, 4096)) !== false) {
				        echo $buffer;
				    }
				    fclose($handle);
				    return;
				}
    		}
    		ob_start();
    	}
    	//echo 1;
    	echo '<?xml version="1.0" ?>';
    	echo '<rss version="2.0">';
    	echo '<channel>';
    	echo $this->generateChannelInfo();
    	$this->getArticlesItems();
    	echo '</channel>';
    	echo '</rss>';
    	if($with_cache){
	    	$d=ob_get_contents();
	    	ob_end_clean();
	    	$f=fopen($cache_file, 'w');
	    	fwrite($f, $d);
	    	fclose($f);
	    	echo $d;
    	}
    }
    
    private function getArticlesItems(){
    	$count=intval($this->getConfigValue('apps.rss.articles_length'));
    	
    	if($count==0){
    		return;
    	}
    	
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/articles/admin/admin.php';
    	$AA=new articles_admin();
    	$data=$AA->getRSSArticlesList($count, 1);
    	return; 
    }
    
    private function getNewsItems(){
    	
    	$count=intval($this->getConfigValue('apps.rss.length'));
    	
    	if($count==0){
    		return;
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/admin.php';
    	$NA=new news_admin();
    	$news_model=$NA->data_model['news'];

    	$needle_fields=array();
    	 
    	$text_field=trim($this->getConfigValue('apps.rss.news_text'));
    	if($text_field==''){
    		$text_field='anons';
    	}
    	$needle_fields[$text_field]=$text_field;
    	 
    	$image_field=trim($this->getConfigValue('apps.rss.news_image'));
    
    	$image_field_type='';
    
    	if($image_field==''){
    		$image_field=false;
    	}elseif(isset($news_model[$image_field]) && in_array($news_model[$image_field]['type'], array('uploads', 'uploadify_image'))){
    		$image_field_type=$news_model[$image_field]['type'];
    		$needle_fields[$image_field]=$image_field;
    		//$image_field=true;
    	}else{
    		$image_field=false;
    	}
    	
    	$title_field=trim($this->getConfigValue('apps.rss.news_title'));
    	if($title_field==''){
    		$title_field='title';
    	}
    	$needle_fields[$title_field]=$title_field;
    	 
    	$where='';
    	if($news_model['date']['type']=='dtdatetime'){
    		$where='`date`<=\''.date('Y-m-d H:i:s', time()).'\'';
    	}else{
    		$where='`date`<='.time();
    	}
    	$needle_fields['date']='date';
    	$needle_fields['news_id']='news_id';
    	$needle_fields['newsalias']='newsalias';
    
    	$query='SELECT news_id FROM '.DB_PREFIX.'_news WHERE '.$where.' ORDER BY `date` DESC LIMIT '.$count;
    	
    	
    	$ids=array();
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ids[$ar['news_id']]=$ar['news_id'];
    		}
    	}
    	
    	if(!empty($ids)){
    		foreach ($news_model as $k=>$f){
    			if(!isset($needle_fields[$k])){
    				unset($news_model[$k]);
    			}
    		}
    		
    		foreach($ids as $id){
    			$form_data_shared=$news_model;
    			$form_data_shared = $data_model->init_model_data_from_db ( 'news', 'news_id', $id, $form_data_shared, true );
    			echo '<item>';
    			echo '<title>'.htmlspecialchars($form_data_shared[$title_field]['value']).'</title>';
    			echo '<link>'.$NA->getNewsRoute($form_data_shared['news_id']['value'], $form_data_shared['newsalias']['value'], true).'</link>';
    			if($image_field!==false){
    				$image='';
    				if($image_field_type=='uploads' && count($form_data_shared[$image_field]['value'])>0){
    					$image=$form_data_shared[$image_field]['value'][0]['normal'];
    				}elseif($image_field_type=='uploadify_image' && isset($form_data_shared[$image_field]['image_array'][0])){
    					$image=$form_data_shared[$image_field]['image_array'][0]['normal'];
    				}
    				if($image!=''){
    					$fn=explode('.', $image);
    					$ext=end($fn);
    					$mime='';
    					if($ext=='jpeg' || $ext=='jpg'){
    						$mime='image/jpeg';
    					}elseif($ext=='png'){
    						$mime='image/png';
    					}elseif($ext=='gif'){
    						$mime='image/gif';
    					}
    					$is=filesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image);
    					//print_r($is);
    					echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$image.'"'.($mime!='' ? ' type="'.$mime.'"' : '').' length="'.$is.'"'.'/>';
    				}
    			}
    			echo '<description><![CDATA['.$form_data_shared[$text_field]['value'].']]></description>'."\n";
    			if($form_data_shared['date']['type']=='dtdatetime'){
    				echo '<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date']['value'])).'</pubDate>';
    			}else{
    				echo '<pubDate>'.gmdate('D, d M Y H:i:s T', $form_data_shared['date']['value']).'</pubDate>';
    			}
    			echo '</item>';
    		}
    	}
    }
    
    /*protected function generateRSSText($type=''){
    	$ret='';
    	$ret.='<?xml version="1.0" ?>'."\n";
    	$ret.='<rss version="2.0">'."\n";
    	$ret.='<channel>'."\n";
    	$ret.=$this->generateChannelInfo();
    	if($type=='realty'){
    		$mode=1;
    		switch($mode){
    			case 1 : {
    				$ret.=$this->getRealtyItemsExtended();
    				break;
    			}
    			default : {
    				$ret.=$this->getRealtyItemsStandart();
    			}
    		}
    	}else{
    		$ret.=$this->getItems();
    	}
    	
    	$ret.='</channel>'."\n";
    	$ret.='</rss>';
    	return SiteBill::iconv(SITE_ENCODING, 'utf-8', $ret);
    }*/
    
    private function generateChannelInfo(){
    	$ret='';
    	$ret.='<title>'.htmlspecialchars(trim($this->getConfigValue('apps.rss.title'))).'</title>'."\n";
      	$ret.='<link>'.$this->getServerFullUrl().'</link>'."\n";
      	$ret.='<description>'.htmlspecialchars(trim($this->getConfigValue('apps.rss.description'))).'</description>'."\n";
      	$ret.='<language>'.htmlspecialchars(trim($this->getConfigValue('apps.rss.language'))).'</language>'."\n";
      	$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T',time()).'</pubDate>'."\n";
      	$ret.='<lastBuildDate>'.gmdate('D, d M Y H:i:s T',time()).'</lastBuildDate>'."\n";
      	$ret.='<docs>http://blogs.law.harvard.edu/tech/rss</docs>'."\n";
      	$ret.='<generator>'.htmlspecialchars(trim($this->getConfigValue('apps.rss.generator'))).'</generator>'."\n";
      	$ret.='<managingEditor>'.htmlspecialchars(trim($this->getConfigValue('apps.rss.editor_email'))).'</managingEditor>'."\n";
      	$ret.='<webMaster>'.htmlspecialchars(trim($this->getConfigValue('apps.rss.webmaster_email'))).'</webMaster>'."\n";
      	return $ret;
     }
    
    /*private function generateItem($data){
    	$text_field=trim($this->getConfigValue('apps.rss.news_text'));
    	if($text_field==''){
    		$text_field='anons';
    	}
    	$ret='';
    	$ret.='<item>'."\n";
    	$ret.='<title>'.$data['title'].'</title>'."\n";
  		$ret.='<link>'.$this->site_link.'news'.$data['news_id'].'.html</link>'."\n";
    	$ret.='<description><![CDATA['.$data[$text_field].']]></description>'."\n";
      	$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T',$data['date']).' ('.date('d M Y H:i:s',$data['date']).')</pubDate>'."\n";
      	$ret.='</item>'."\n";
      	return $ret;
    }*/
    
   /* private function getItems(){
    	$ret='';
    	
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/admin.php';
    	$NA=new news_admin();
    	$news_model=$NA->data_model['news'];
    	
    	
    	$text_field=trim($this->getConfigValue('apps.rss.news_text'));
    	if($text_field==''){
    		$text_field='anons';
    	}
    	
    	$image_field=trim($this->getConfigValue('apps.rss.data_image'));
    	 
    	$image_field_type='';
    	 
    	if($image_field==''){
    		$image_field=false;
    	}elseif(isset($news_model[$image_field]) && in_array($news_model[$image_field]['type'], array('uploads', 'uploadify_image'))){
    		$image_field_type=$news_model[$image_field]['type'];
    	}else{
    		$image_field=false;
    	}
    	
    	$where='';
    	if($news_model['date']['type']=='dtdatetime'){
    		$where='`date`<=\''.date('Y-m-d H:i:s', time()).'\'';
    	}else{
    		$where='`date`<='.time();
    	}
    	    	
    	$query='SELECT * FROM '.DB_PREFIX.'_news WHERE '.$where.' ORDER BY `date` DESC LIMIT '.$this->length;
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			
    			
    			//$ret='';
    			$ret.='<item>'."\n";
    			$ret.='<title>'.$ar['title'].'</title>'."\n";
    			$ret.='<link>'.$NA->getNewsRoute($ar['news_id'], $ar['newsalias'], true).'</link>'."\n";
    			if($image_field){
    				if($image_field_type=='uploads' && isset($ar[$image_field]) && $ar[$image_field]!=''){
    					$im=unserialize($ar[$image_field]);
    					$ret.='<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$im[0]['normal'].'"/>'."\n";
    				}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    					$ret.='<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>'."\n";
    				}
    			}
    			$ret.='<description><![CDATA['.$ar[$text_field].']]></description>'."\n";
    			if($news_model['date']['type']=='dtdatetime'){
    				$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($ar['date'])).' ('.date('d M Y H:i:s', strtotime($ar['date'])).')</pubDate>'."\n";
    			}else{
    				$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T', $ar['date']).' ('.date('d M Y H:i:s', $ar['date']).')</pubDate>'."\n";
    			}
    			
    			$ret.='</item>'."\n";
    			
    			//$ret.=$this->generateItem($ar);
    		}
    	}
    	return $ret;
    }*/
    
    private function getExportedRealtyDataIds(){
    	$ids=array();
    	$count=intval($this->getConfigValue('apps.rss.data_length'));
    	if($count>0){
    		$DBC=DBC::getInstance();
    		$query='SELECT `id` FROM '.DB_PREFIX.'_data WHERE `active`=1 ORDER BY `date_added` DESC LIMIT '.$count;
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$ids[]=$ar['id'];
    			}
    		}
    	}
    	return $ids;
    }
    
    private function getRealtyItemsStandart(){
    	$ids=$this->getExportedRealtyDataIds();
    	
    	if(empty($ids)){
    		echo '';
    		return;
    	}
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	 
    	$Kvartira_View=new Kvartira_View();
    	$data_model = new Data_Model();
    	$base_form_data = $data_model->get_kvartira_model(false, true);
    	$base_form_data = $base_form_data['data'];
    	 
    	$trimmed_form_data = $data_model->get_kvartira_model(false);
    	$trimmed_form_data=$trimmed_form_data['data'];
    	
    	$image_field=trim($this->getConfigValue('apps.rss.data_image'));
    	 
    	$image_field_type='';
    	 
    	if($image_field==''){
    		$image_field=false;
    	}elseif(isset($base_form_data[$image_field]) && in_array($base_form_data[$image_field]['type'], array('uploads', 'uploadify_image'))){
    		$image_field_type=$base_form_data[$image_field]['type'];
    	}else{
    		$image_field=false;
    	}
    	 
    	 
    	 
    	$hasTlocation=false;
    	$tlocationElement='';
    	 
    	foreach($base_form_data as $key=>$val){
    		if($val['type']=='tlocation'){
    			$hasTlocation=true;
    			$tlocationElement=$key;
    		}
    	}
    	
    	$rs=array();
    	 
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/rss/site/template/realty_view.tpl') ) {
    		$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/rss/site/template/realty_view.tpl';
    	} else {
    		$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/rss/site/template/realty_view.tpl';
    	}
    	
    	global $smarty;
    	 
    	foreach($ids as $id){
    		$form_data_shared=$base_form_data;
    		$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );
    	
    		if($hasTlocation){
    			$form_data_shared['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    			$form_data_shared['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    			$form_data_shared['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    			$form_data_shared['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    			$form_data_shared['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    		}
    	
    	
    		$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    	
    	
    		$delta=array();
    		foreach($form_data_shared as $k=>$v){
    			if(isset($trimmed_form_data[$k])){
    				$delta[$k]=$v;
    			}
    		}
    		
    		$smarty->assign('_hvdata', $delta);
    	
    		$topic_id=$form_data_shared['topic_id']['value'];
    		$translit_alias=$form_data_shared['translit_alias']['value'];
    		
    		$href=$this->getRealtyHREF($id, true, array('topic_id'=>$topic_id, 'alias'=>$translit_alias));
    	
    		/*if(1==$this->getConfigValue('apps.seo.level_enable')){
    	
    			if($category_structure['catalog'][$topic_id]['url']!=''){
    				$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    			}else{
    				$parent_category_url='';
    			}
    		}else{
    			$parent_category_url='';
    		}
    	
    		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
    			$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$translit_alias;
    		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id.'.html';
    		}else{
    			$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id;
    		}*/
    		
    		$description=str_replace(array("\n", "\r"), '', $smarty->fetch($tpl));
    	
    	
    		echo '<item>'."\n";
    		echo '<title>'.htmlspecialchars($meta_data['title']).'</title>';
    		//echo '<link>http://'.$_SERVER['HTTP_HOST'].$href.'</link>';
    		echo '<link>'.$href.'</link>';
    		if($image_field){
    			$image='';
    			if($image_field_type=='uploads' && isset($form_data_shared[$image_field]['value'][0])){
    				$image=$form_data_shared['image']['value'][0]['normal'];
    				//echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>';
    			}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    				$image=$form_data_shared['image']['image_array'][0]['normal'];
    				//echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>';
    			}
    			if($image!=''){
    				$fn=explode('.', $image);
    				$ext=end($fn);
    				$mime='';
    				if($ext=='jpeg' || $ext=='jpg'){
    					$mime='image/jpeg';
    				}elseif($ext=='png'){
    					$mime='image/png';
    				}elseif($ext=='gif'){
    					$mime='image/gif';
    				}
    				$is=filesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image);
    				echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$image.'"'.($mime!='' ? ' type="'.$mime.'"' : '').' length="'.$is.'"'.'/>';
    				//echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$image.'"/>';
    			}
    			
    		}
    		echo '<description><![CDATA['.$description.']]></description>';
    		echo '<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date_added']['value'])).'</pubDate>';
    		echo '</item>'."\n";
    	}
    }
    
    private function getRealtyItemsExtended(){
    	
    	$ids=$this->getExportedRealtyDataIds();
    	if(empty($ids)){
    		return;
    	}
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	 
    	$Kvartira_View=new Kvartira_View();
    	$data_model = new Data_Model();
    	$base_form_data = $data_model->get_kvartira_model(false, true);
    	$base_form_data = $base_form_data['data'];
    	
    	$needle_fields=array();
    	 
    	 
    	$fields_date=trim($this->getConfigValue('apps.rss.data_date'));
    	if($fields_date=='' || !isset($base_form_data[$fields_date])){
    		$fields_date='date_added';
    	}
    	 
    	$needle_fields[$fields_date]=$fields_date;
    	 
    	$image_field=trim($this->getConfigValue('apps.rss.data_image'));
    	if($image_field==''){
    		$image_field=='image';
    	}
    	
    	$image_field_type='';
    	
    	if(isset($base_form_data[$image_field]) && in_array($base_form_data[$image_field]['type'], array('uploads', 'uploadify_image'))){
    		$image_field_type=$base_form_data[$image_field]['type'];
    		$needle_fields[$image_field]=$image_field;
    	}else{
    		$image_field='';
    	}
    	 
    	$hasTlocation=false;
    	$tlocationElement='';
    	
    	foreach($base_form_data as $key=>$val){
    		if($val['type']=='tlocation'){
    			$hasTlocation=true;
    			$tlocationElement=$key;
    			$needle_fields[$tlocationElement]=$tlocationElement;
    		}
    	}
    	 
    	$fields_title=trim($this->getConfigValue('apps.rss.data_title'));
    	if($fields_title!='' && isset($base_form_data[$fields_title])){
    		$needle_fields[$fields_title]=$fields_title;
    	}else{
    		$fields_title='';
    	}
    	 
    	if($fields_title==''){
    		$title_str=trim($this->getConfigValue('apps.realty.title_preg'));
    		 
    		if($title_str!='' && preg_match_all('/{([^}]+)}/', $title_str, $matches)){
    			$str_parts=array();
    			if(count($matches[1])>0){
    				foreach ($matches[1] as $key=>$keyval){
    					if($keyval=='!topic_path'){
    						$needle_fields['topic_id']='topic_id';
    					}elseif(isset($base_form_data[$keyval])){
    						$needle_fields[$keyval]=$keyval;
    					}
    				}
    			}
    		}else{
    			$title_parts=array();
    			if($hasTlocation){
    				$needle_fields['topic_id']='topic_id';
    				$needle_fields['price']='price';
    	
    			}else{
    				$needle_fields['topic_id']='topic_id';
    				$needle_fields['city_id']='city_id';
    				$needle_fields['street_id']='street_id';
    				$needle_fields['price']='price';
    			}
    		}
    	}
    	 
    	 
    	$fields_description=trim($this->getConfigValue('apps.rss.data_description'));
    	if($fields_description!='' && isset($base_form_data[$fields_description])){
    		$needle_fields[$fields_description]=$fields_description;
    	}else{
    		$fields_description='';
    	}
    	 
    	$fields_descriptionfields=array();
    	$fields_descriptionfields_c=trim($this->getConfigValue('apps.rss.data_descriptionfields'));
    	if($fields_descriptionfields_c!=''){
    		$fields_descriptionfields_elements=explode(',', $fields_descriptionfields_c);
    		foreach($fields_descriptionfields_elements as $fields_descriptionfields_element){
    			$key=trim($fields_descriptionfields_element);
    			if(isset($base_form_data[$key])){
    				$needle_fields[$key]=$key;
    				$fields_descriptionfields[]=$key;
    			}
    		}
    	}
    	 
    	 
    	$needle_fields['topic_id']='topic_id';
    	$needle_fields['translit_alias']='translit_alias';
    	 
    	$needle_fields['id']='id';
    	
    	foreach ($base_form_data as $k=>$v){
    		if(!in_array($k, $needle_fields)){
    			unset($base_form_data[$k]);
    		}
    	}
    	
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/rss/site/template/realty_view_ext.tpl') ) {
    		$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/rss/site/template/realty_view_ext.tpl';
    	} else {
    		$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/rss/site/template/realty_view_ext.tpl';
    	}
    	
    	global $smarty;
    	
    	foreach($ids as $id){
    		$form_data_shared=$base_form_data;
    		$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );
    	
    		if($hasTlocation){
    			$form_data_shared['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    			$form_data_shared['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    			$form_data_shared['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    			$form_data_shared['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    			$form_data_shared['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    		}
    	
    	
    		if($fields_title!=''){
    			$title=$form_data_shared[$fields_title]['value'];
    		}else{
    			$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    			$title=$meta_data['title'];
    		}
    		
    		$topic_id=$form_data_shared['topic_id']['value'];
    		$translit_alias=$form_data_shared['translit_alias']['value'];
    		//$id=$form_data_shared['translit_alias']['value'];
    		 
    		/*if(1==$this->getConfigValue('apps.seo.level_enable')){
    			if($category_structure['catalog'][$topic_id]['url']!=''){
    				$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    			}else{
    				$parent_category_url='';
    			}
    		}else{
    			$parent_category_url='';
    		}
    		 
    		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
    			$href=$this->getServerFullUrl().'/'.$parent_category_url.$translit_alias;
    		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$href=$this->getServerFullUrl().'/'.$parent_category_url.'realty'.$id.'.html';
    		}else{
    			$href=$this->getServerFullUrl().'/'.$parent_category_url.'realty'.$id;
    		}*/
    		
    		$href=$this->getRealtyHREF($id, true, array('topic_id'=>$topic_id, 'alias'=>$translit_alias));
    	
    		$description_trim=intval($this->getConfigValue('apps.rss.data_description_max'));
    		$description='';
    		if($fields_description!=''){
    			$description=$form_data_shared[$fields_description]['value'];
    			if($description_trim>0){
    				$description='<p>'.mb_substr(strip_tags($description), 0, $description_trim, 'utf-8').'...</p>';
    			}
    		}
    		
    		if(!empty($fields_descriptionfields)){
    			$data_set=array();
    			foreach($fields_descriptionfields as $df){
    				$data_set[]=$form_data_shared[$df];
    			}
    			$smarty->assign('data_set', $data_set);
    			$description.=$smarty->fetch($tpl);
    		}
    	
    		if($form_data_shared[$fields_date]['type']=='datetime'){
    			$date1=gmdate('D, d M Y H:i:s T', $form_data_shared[$fields_date]['value']);
    		}elseif($form_data_shared[$fields_date]['type']=='dtdatetime'){
    			$date1=gmdate('D, d M Y H:i:s T', strtotime($form_data_shared[$fields_date]['value']));
    		}else{
    			$date1='';
    		}
    	
    		echo '<item>';
    		echo '<title>'.htmlspecialchars($title).'</title>';
    		echo '<link>'.$href.'</link>';
    		if($image_field){
    			$image='';
    			if($image_field_type=='uploads' && isset($form_data_shared[$image_field]['value'][0])){
    				$image=$form_data_shared['image']['value'][0]['normal'];
    				//echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>';
    			}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    				$image=$form_data_shared['image']['image_array'][0]['normal'];
    				//echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>';
    			}
    			if($image!=''){
    				$fn=explode('.', $image);
    				$ext=end($fn);
    				$mime='';
    				if($ext=='jpeg' || $ext=='jpg'){
    					$mime='image/jpeg';
    				}elseif($ext=='png'){
    					$mime='image/png';
    				}elseif($ext=='gif'){
    					$mime='image/gif';
    				}
    				$is=filesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$image);
    				echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$image.'"'.($mime!='' ? ' type="'.$mime.'"' : '').' length="'.$is.'"'.'/>';
    			}
    			
    			//echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$image.'"/>';
    		}
    		/*if($image_field!=''){
    			if($image_field_type=='uploads' && isset($form_data_shared[$image_field]['value'][0])){
    				echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>';
    			}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    				echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>';
    			}
    		}*/
    		echo '<description><![CDATA['.$description.']]></description>';
    	
    		echo '<pubDate>'.$date1.'</pubDate>';
    		echo '</item>';
    	}
    }
    
    /*private function getRealtyItems(){
    	
    	$ret='';
    	
    	
    	$simple_mode=false;
    	
    	$ids=$this->getExportedRealtyDataIds();
    	 
    	if(empty($ids)){
    		return $ret;
    	}
    	
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	
    	$Kvartira_View=new Kvartira_View();
    	$data_model = new Data_Model();
    	$base_form_data = $data_model->get_kvartira_model(false, true);
    	$base_form_data = $base_form_data['data'];
    	
    	
    	
    	
    	
    	$trimmed_form_data = $data_model->get_kvartira_model(false);
    	$trimmed_form_data=$trimmed_form_data['data'];
    	 
    	if($simple_mode){
    		$fields=array();
    		if($image_field){
    			$fields[$image_field]=$image_field;
    		}
    		
    		if($hasTlocation){
    			$fields[$tlocationElement]=$tlocationElement;
    		}
    		
    		$fields['topic_id']='topic_id';
    		$fields['translit_alias']='translit_alias';
    		$fields['date_added']='date_added';
    		$fields['id']='id';
    		$fields['text']='text';
    		
    		$title_str=trim($this->getConfigValue('apps.realty.title_preg'));
    		 
    		if($title_str!=''){
    			//$title_str='';
    			 
    		
    			preg_match_all('/{([^}]+)}/', $title_str, $matches);
    			 
    			$str_parts=array();
    			if(count($matches[1])>0){
    				foreach ($matches[1] as $key=>$keyval){
    					if($keyval=='!topic_path'){
    						$fields['topic_id']='topic_id';
    					}elseif(isset($base_form_data[$keyval])){
    						$fields[$keyval]=$keyval;
    					}
    				}
    			}
    		}else{
    			$title_parts=array();
    			if($hasTlocation){
    				$fields['topic_id']='topic_id';
    				$fields['price']='price';
    				
    			}else{
    				$fields['topic_id']='topic_id';
    				$fields['city_id']='city_id';
    				$fields['street_id']='street_id';
    				$fields['price']='price';
    			}
    		}
    		
    		foreach ($base_form_data as $k=>$v){
    			if(!in_array($k, $fields)){
    				unset($base_form_data[$k]);
    			}
    		}
    		
    		foreach($ids as $id){
    			$form_data_shared=$base_form_data;
    			$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );
    		
    			if($hasTlocation){
    				$form_data_shared['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    				$form_data_shared['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    				$form_data_shared['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    				$form_data_shared['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    				$form_data_shared['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    			}
    		
    		
    			$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    		
    		
    			//$data=$Kvartira_View->getPublicAutoOutputData($form_data_shared);
    		
    			//$smarty->assign('_hvdata', $data);
    			//$smarty->assign('_hvdata', $form_data_shared);
    		
    			$topic_id=$form_data_shared['topic_id']['value'];
    			$translit_alias=$form_data_shared['translit_alias']['value'];
    		
    			if(1==$this->getConfigValue('apps.seo.level_enable')){
    		
    				if($category_structure['catalog'][$topic_id]['url']!=''){
    					$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    				}else{
    					$parent_category_url='';
    				}
    			}else{
    				$parent_category_url='';
    			}
    		
    			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$translit_alias;
    			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id.'.html';
    			}else{
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id;
    			}
    		
    		
    			//$rs[]=;
    			$ret.='<item>'."\n";
    			$ret.='<title>'.htmlspecialchars($meta_data['title']).'</title>'."\n";
    			$ret.='<link>http://'.$_SERVER['HTTP_HOST'].$href.'</link>'."\n";
    			if($image_field){
    				if($image_field_type=='uploads' && isset($form_data_shared[$image_field]['value'][0])){
    					$ret.='<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>'."\n";
    				}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    					$ret.='<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>'."\n";
    				}
    			}
    			 
    		
    			//if ( isset($form_data_shared['image']['value'][0])) {
    			// $ret.='<enclosure url="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>'."\n";
    			//}
    		
    			$ret.='<description><![CDATA[';
    			$ret.=$form_data_shared['text']['value'];
    			$ret.=']]></description>'."\n";
    			$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date_added']['value'])).' ('.date('d M Y H:i:s', strtotime($form_data_shared['date_added']['value'])).')</pubDate>'."\n";
    			$ret.='</item>'."\n";
    		}
    	}else{
    		$rs=array();
    		 
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/rss/site/template/realty_view.tpl') ) {
    			$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/rss/site/template/realty_view.tpl';
    		} else {
    			$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/rss/site/template/realty_view.tpl';
    		}
    		
    		global $smarty;
    		 */
    		/*$form_data_shared=$base_form_data['data'];
    		 $ms=$data_model->init_model_data_from_db_multi ( 'data', 'id', $ids, $form_data_shared, true );
    		foreach($ms as $form_data_shared){
    		$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    		$data=$Kvartira_View->getPublicAutoOutputData($form_data_shared);
    		$smarty->assign('_hvdata', $form_data_shared);
    		$topic_id=$form_data_shared['topic_id']['value'];
    		$translit_alias=$form_data_shared['translit_alias']['value'];
    		if(1==$this->getConfigValue('apps.seo.level_enable')){
    		if($category_structure['catalog'][$topic_id]['url']!=''){
    		$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    		}else{
    		$parent_category_url='';
    		}
    		}else{
    		$parent_category_url='';
    		}
    		 
    		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$translit_alias;
    		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id.'.html';
    		}else{
    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id;
    		}
    		$ret.='<item>'."\n";
    		$ret.='<title>'.$meta_data['title'].'</title>'."\n";
    		$ret.='<link>http://'.$_SERVER['HTTP_HOST'].$href.'</link>'."\n";
    		if ( isset($form_data_shared['image']['value'][0])) {
    		$ret.='<enclosure url="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>'."\n";
    		}
    		$ret.='<description><![CDATA[';
    		$ret.=str_replace(array("\n", "\r"), '', $smarty->fetch($tpl));
    		$ret.=']]></description>'."\n";
    		$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date_added']['value'])).' ('.date('d M Y H:i:s', strtotime($form_data_shared['date_added']['value'])).')</pubDate>'."\n";
    		$ret.='</item>'."\n";
    		}*/
    		 
    		 
    		/* 
    		foreach($ids as $id){
    			$form_data_shared=$base_form_data;
    			$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );
    		
    			if($hasTlocation){
    				$form_data_shared['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    				$form_data_shared['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    				$form_data_shared['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    				$form_data_shared['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    				$form_data_shared['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    			}
    		
    		
    			$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    		
    		
    			$delta=array();
    			foreach($form_data_shared as $k=>$v){
    				if(isset($trimmed_form_data[$k])){
    					$delta[$k]=$v;
    				}
    			}
    			$smarty->assign('_hvdata', $delta);
    		
    			$topic_id=$form_data_shared['topic_id']['value'];
    			$translit_alias=$form_data_shared['translit_alias']['value'];
    		
    			if(1==$this->getConfigValue('apps.seo.level_enable')){
    		
    				if($category_structure['catalog'][$topic_id]['url']!=''){
    					$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    				}else{
    					$parent_category_url='';
    				}
    			}else{
    				$parent_category_url='';
    			}
    		
    			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$translit_alias;
    			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id.'.html';
    			}else{
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id;
    			}
    		
    		
    			//$rs[]=;
    			$ret.='<item>'."\n";
    			$ret.='<title>'.htmlentities($meta_data['title']).'</title>'."\n";
    			$ret.='<link>http://'.$_SERVER['HTTP_HOST'].$href.'</link>'."\n";
    			if($image_field){
    				if($image_field_type=='uploads' && isset($form_data_shared[$image_field]['value'][0])){
    					$ret.='<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>'."\n";
    				}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    					$ret.='<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>'."\n";
    				}
    			}
    			 
    		
    			//if ( isset($form_data_shared['image']['value'][0])) {
    			// $ret.='<enclosure url="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>'."\n";
    			//}
    		
    			$ret.='<description><![CDATA[';
    			$ret.=str_replace(array("\n", "\r"), '', $smarty->fetch($tpl));
    			$ret.=']]></description>'."\n";
    			//$ret.='<description><![CDATA[';
    			//$ret.=$form_data_shared['text']['value'];
    			//$ret.=']]></description>'."\n";
    			$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date_added']['value'])).' ('.date('d M Y H:i:s', strtotime($form_data_shared['date_added']['value'])).')</pubDate>'."\n";
    			//$ret.='<enclosure url="http://icdn.lenta.ru/images/2015/10/20/11/20151020111415900/pic_b08abd54f37cd6aa1e0063b57229e414.jpg" length="84070" type="image/jpeg"/>'."\n";
    			$ret.='</item>'."\n";
    		}
    	}
    	//$end=memory_get_usage();
    	//$diff=$end-$start;
    	//$unit=array('b','kb','mb','gb','tb','pb');
    	//return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    	//echo @round($diff/pow(1024,($i=floor(log($diff,1024)))),2).' '.$unit[$i]."\n"; // 36640
    	//echo @round($end/pow(1024,($i=floor(log($end,1024)))),2).' '.$unit[$i]."\n"; // 36640
    	
    	return $ret;
    }*/
    
    protected function generateRSSTextF(){
    	echo '<?xml version="1.0" ?>'."\n";
    	echo '<rss version="2.0">'."\n";
    	echo '<channel>'."\n";
    	echo $this->generateChannelInfo();
    	$this->getRealtyItemsF();
    	echo '</channel>'."\n";
    	echo '</rss>';
    }
    
    private function getRealtyItemsF(){
    	$simple_mode=true;
    	$ids=array();
    	$DBC=DBC::getInstance();
    	$query='SELECT `id` FROM '.DB_PREFIX.'_data WHERE `active`=1 ORDER BY `date_added` DESC LIMIT '.$this->length;
    	$stmt=$DBC->query($query, array($this->length));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ids[]=$ar['id'];
    		}
    	}
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	 
    	$Kvartira_View=new Kvartira_View();
    	$data_model = new Data_Model();
    	$base_form_data = $data_model->get_kvartira_model(false, true);
    	$base_form_data = $base_form_data['data'];
    	 
    	 
    	 
    	$image_field=trim($this->getConfigValue('apps.rss.data_image'));
    	$image_field='image';
    	 
    	$image_field_type='';
    	 
    	if($image_field==''){
    		$image_field=false;
    	}elseif(isset($base_form_data[$image_field]) && in_array($base_form_data[$image_field]['type'], array('uploads', 'uploadify_image'))){
    		$image_field_type=$base_form_data[$image_field]['type'];
    	}else{
    		$image_field=false;
    	}
    	 
    	 
    	 
    	$hasTlocation=false;
    	$tlocationElement='';
    	 
    	foreach($base_form_data as $key=>$val){
    		if($val['type']=='tlocation'){
    			$hasTlocation=true;
    			$tlocationElement=$key;
    		}
    	}
    	 
    	 
    	 
    	if($simple_mode){
    		$fields=array();
    		if($image_field){
    			$fields[$image_field]=$image_field;
    		}
    
    		if($hasTlocation){
    			$fields[$tlocationElement]=$tlocationElement;
    		}
    
    		$fields['topic_id']='topic_id';
    		$fields['translit_alias']='translit_alias';
    		$fields['date_added']='date_added';
    		$fields['id']='id';
    		$fields['text']='text';
    
    		$title_str=trim($this->getConfigValue('apps.realty.title_preg'));
    		 
    		if($title_str!=''){
    			//$title_str='';
    
    
    			preg_match_all('/{([^}]+)}/', $title_str, $matches);
    
    			$str_parts=array();
    			if(count($matches[1])>0){
    				foreach ($matches[1] as $key=>$keyval){
    					if($keyval=='!topic_path'){
    						$fields['topic_id']='topic_id';
    					}elseif(isset($base_form_data[$keyval])){
    						$fields[$keyval]=$keyval;
    					}
    				}
    			}
    		}else{
    			$title_parts=array();
    			if($hasTlocation){
    				$fields['topic_id']='topic_id';
    				$fields['price']='price';
    
    			}else{
    				$fields['topic_id']='topic_id';
    				$fields['city_id']='city_id';
    				$fields['street_id']='street_id';
    				$fields['price']='price';
    			}
    		}
    
    		foreach ($base_form_data as $k=>$v){
    			if(!in_array($k, $fields)){
    				unset($base_form_data[$k]);
    			}
    		}
    			
    		$ms=$data_model->init_model_data_from_db_multi ( 'data', 'id', $ids, $base_form_data, true, true );
    		foreach($ms as $form_data_shared){
    			if($hasTlocation){
    				$form_data_shared['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    				$form_data_shared['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    				$form_data_shared['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    				$form_data_shared['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    				$form_data_shared['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    			}
    
    
    			$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    
    
    			$topic_id=$form_data_shared['topic_id']['value'];
    			$translit_alias=$form_data_shared['translit_alias']['value'];
    			
    			$href=$this->getRealtyHREF($id, true, array('topic_id'=>$topic_id, 'alias'=>$translit_alias));
    
    			/*if(1==$this->getConfigValue('apps.seo.level_enable')){
    				if($category_structure['catalog'][$topic_id]['url']!=''){
    					$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    				}else{
    					$parent_category_url='';
    				}
    			}else{
    				$parent_category_url='';
    			}
    
    			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$translit_alias;
    			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id.'.html';
    			}else{
    				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$id;
    			}*/
    
    			echo '<item>'."\n";
    			echo '<title>'.htmlspecialchars($meta_data['title']).'</title>'."\n";
    			//echo '<link>http://'.$_SERVER['HTTP_HOST'].$href.'</link>'."\n";
    			echo '<link>'.$href.'</link>'."\n";
    			if($image_field){
    				if($image_field_type=='uploads' && isset($form_data_shared[$image_field]['value'][0])){
    					echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['value'][0]['normal'].'"/>'."\n";
    				}elseif($image_field_type=='uploadify_image' && isset($form_data_shared['image']['image_array'][0])){
    					echo '<enclosure url="'.$this->getServerFullUrl().'/img/data/'.$form_data_shared['image']['image_array'][0]['normal'].'"/>'."\n";
    				}
    			}
    			 
    			echo '<description><![CDATA[';
    			echo $form_data_shared['text']['value'];
    			echo ']]></description>'."\n";
    			echo '<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date_added']['value'])).' ('.date('d M Y H:i:s', strtotime($form_data_shared['date_added']['value'])).')</pubDate>'."\n";
    			echo '</item>'."\n";
    		}
    
    	}
    }
    
}