<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * RSS v2.0 Exporter at Harvard Law (http://cyber.law.harvard.edu/rss/rss.html) admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class rss_admin extends Object_Manager {
	
	private $rss_title;
	private $site_link;
	private $rss_description;
	private $language;
	private $generator;
	private $editor_email;
	private $webmaster_email;
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
        
        $this->rss_title=$this->getConfigValue('apps.rss.title');
        $this->rss_description=$this->getConfigValue('apps.rss.description');
        $this->language=$this->getConfigValue('apps.rss.language');
        $this->generator=$this->getConfigValue('apps.rss.generator');
        $this->editor_email=$this->getConfigValue('apps.rss.editor_email');
        $this->webmaster_email=$this->getConfigValue('apps.rss.webmaster_email');
        $this->length=$this->getConfigValue('apps.rss.length');
        $this->site_link='http://'.$_SERVER['SERVER_NAME'].(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL.'/' : '/');
        $this->output_file=SITEBILL_DOCUMENT_ROOT.'/rss.xml';
    }
    
    function main(){
        $rs .= $this->get_app_title_bar();
        $rs .= Multilanguage::_('RSS_STREAM_ADDRESS','rss').': <a href="'.$this->site_link.'rss/" target="_blank">'.$this->site_link.'rss/</a>';
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
    	}
    	unset($CF);
    }
    
    protected function generateRSSText($type=''){
    	$ret='';
    	$ret.='<?xml version="1.0" ?>'."\n";
    	$ret.='<rss version="2.0">'."\n";
    	$ret.='<channel>'."\n";
    	$ret.=$this->generateChannelInfo();
    	if($type=='realty'){
    		$ret.=$this->getRealtyItems();
    	}else{
    		$ret.=$this->getItems();
    	}
    	
    	$ret.='</channel>'."\n";
    	$ret.='</rss>';
    	return SiteBill::iconv(SITE_ENCODING, 'utf-8', $ret);
    }
    
    private function generateChannelInfo(){
    	$ret='';
    	$ret.='<title>'.$this->rss_title.'</title>'."\n";
      	$ret.='<link>'.$this->site_link.'</link>'."\n";
      	$ret.='<description>'.$this->rss_description.'</description>'."\n";
      	$ret.='<language>'.$this->language.'</language>'."\n";
      	$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T',time()).'</pubDate>'."\n";
      	$ret.='<lastBuildDate>'.gmdate('D, d M Y H:i:s T',time()).'</lastBuildDate>'."\n";
      	$ret.='<docs>http://blogs.law.harvard.edu/tech/rss</docs>'."\n";
      	$ret.='<generator>'.$this->generator.'</generator>'."\n";
      	$ret.='<managingEditor>'.$this->editor_email.'</managingEditor>'."\n";
      	$ret.='<webMaster>'.$this->webmaster_email.'</webMaster>'."\n";
      	return $ret;
    }
    
    private function generateItem($data){
    	$ret='';
    	$ret.='<item>'."\n";
    	$ret.='<title>'.$data['title'].'</title>'."\n";
  		$ret.='<link>'.$this->site_link.'news'.$data['news_id'].'.html</link>'."\n";
    	$ret.='<description><![CDATA['.$data['anons'].']]></description>'."\n";
      	$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T',$data['date']).' ('.date('d M Y H:i:s',$data['date']).')</pubDate>'."\n";
      	$ret.='</item>'."\n";
      	return $ret;
    }
    
    private function getItems(){
    	$ret='';
    	$query='SELECT * FROM '.DB_PREFIX.'_news ORDER BY `date` DESC LIMIT '.$this->length;
    	//echo $query;
    	$this->db->exec($query);
    	if($this->db->success){
    		while($this->db->fetch_assoc()){
    			$ret.=$this->generateItem($this->db->row);
    		}
    	}
    	
    	 
    	return $ret;
    }
    
    private function getRealtyItems(){
    	
    	$ret='';
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
    		$form_data_shared=$base_form_data['data'];
    		$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );
    		
    		if($hasTlocation){
    				$form_data_shared['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    				$form_data_shared['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    				$form_data_shared['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    				$form_data_shared['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    				$form_data_shared['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    			}
    		
    		
    		$meta_data=$Kvartira_View->getPublicMetaData($form_data_shared, $hasTlocation, $tlocationElement);
    		
    		
    		$data=$Kvartira_View->getPublicAutoOutputData($form_data_shared);
    		
    		//$smarty->assign('_hvdata', $data);
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
    		
    		
    		//$rs[]=;
    		$ret.='<item>'."\n";
    		$ret.='<title>'.$meta_data['title'].'</title>'."\n";
    		$ret.='<link>http://'.$_SERVER['HTTP_HOST'].$href.'</link>'."\n";
    		$ret.='<description><![CDATA[';
    		$ret.=str_replace(array("\n", "\r"), '', $smarty->fetch($tpl));
    		$ret.=']]></description>'."\n";
    		//$ret.='<description><![CDATA[';
    		//$ret.=$form_data_shared['text']['value'];
    		//$ret.=']]></description>'."\n";
    		$ret.='<pubDate>'.gmdate('D, d M Y H:i:s T', strtotime($form_data_shared['date_added']['value'])).' ('.date('d M Y H:i:s', strtotime($form_data_shared['date_added']['value'])).')</pubDate>'."\n";
    		$ret.='</item>'."\n";
    	}
    	//print_r($rs);
    	/*$this->db->exec($query);
    	if($this->db->success){
    		while($this->db->fetch_assoc()){
    			$ret.=$this->generateRealtyItem($this->db->row);
    		}
    	}*/
    	 
    
    	return $ret;
    }
    
}