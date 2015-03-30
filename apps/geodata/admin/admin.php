<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * GeoData admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class geodata_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
    	$this->SiteBill();
    	Multilanguage::appendAppDictionary('geodata');
    	$this->app_title=Multilanguage::_('APP_NAME','geodata');
    	$this->action = 'geodata';
        //кол-во для гугля и яндекса
        //гугль 2500
        //yandex 25000
        //геокодировать неполные данные
        //http://maps.googleapis.com/maps/api/geocode/xml?address=%D0%9B%D1%8C%D0%B2%D0%BE%D0%B2+%D0%91%D0%BE%D0%B4%D0%BD%D0%B0%D1%80%D1%81%D0%BA%D0%B0%D1%8F,+16&sensor=false
		//http://geocode-maps.yandex.ru/1.x/?geocode=%D0%9B%D1%8C%D0%B2%D0%BE%D0%B2,+%D0%91%D0%BE%D0%B4%D0%BD%D0%B0%D1%80%D1%81%D0%BA%D0%B0%D1%8F,+16
    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
    	$config_admin = new config_admin();
    	 
    	if ( !$config_admin->check_config_item('apps.geodata.enable') ) {
    		$config_admin->addParamToConfig('apps.geodata.enable','0','Включить приложение GeoData');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.query_count') ) {
    		$config_admin->addParamToConfig('apps.geodata.query_count','2500','Количество запросов на геокодирование');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.per_step') ) {
    		$config_admin->addParamToConfig('apps.geodata.per_step','100','Количество записей за проход');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.geocode_partial') ) {
    		$config_admin->addParamToConfig('apps.geodata.geocode_partial','0','Геокодировать неполные данные');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.geocode_element_name') ) {
    		$config_admin->addParamToConfig('apps.geodata.geocode_element_name','geo','Имя элемента геоданных');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.new_map_center') ) {
    		$config_admin->addParamToConfig('apps.geodata.new_map_center','55.751667,37.617778','Широта и долгота центра карты для указания положения недвижимости в формате ХХ.XXXXXX,XX.XXXXXX');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.on_home') ) {
    		$config_admin->addParamToConfig('apps.geodata.on_home','1','Выводить карту на главной странице');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.show_grid_map') ) {
    		$config_admin->addParamToConfig('apps.geodata.show_grid_map','0','Выводить карту вместе со списком объявлений');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.map_zoom_default') ) {
    		$config_admin->addParamToConfig('apps.geodata.map_zoom_default','','Масштаб карты');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.allow_view_coding') ) {
    		$config_admin->addParamToConfig('apps.geodata.allow_view_coding','0','Разрешить геокодирование при просмотре объявления');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.geodata.try_encode') ) {
    		$config_admin->addParamToConfig('apps.geodata.try_encode','0','Включить попытку геокодировать положение при сохранении\изменении объявления');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.try_encode_fields') ) {
    		$config_admin->addParamToConfig('apps.geodata.try_encode_fields','','Список системных имен полей для геокодирования при сохранении\изменении объявления(разделитель - запятая)');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.try_encode_anycase') ) {
    		$config_admin->addParamToConfig('apps.geodata.try_encode_anycase','0','Проводить геокодирование даже если координаты указаны');
    	}
    	 
    }
    
    public function ajax(){
    	if ( $this->getRequestValue('action') == 'get_address_string' ) {
    		$ret=array();
    		$url='https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.$this->getRequestValue('input').'&key=AIzaSyAE2c_0O3OqmJ7Gwn7fjhyLutG_rlLNuaA&components=country:ua&types[]=region';
    		$curl = curl_init();
    		curl_setopt($curl, CURLOPT_URL, $url);
    		curl_setopt($curl, CURLOPT_POST, 0);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    		$result = curl_exec($curl);
    		curl_close($curl);
    		if($result!=''){
    			$data=json_decode($result, true);
    			print_r($data);
    			foreach ($data['predictions'] as $d){
    				if(in_array('route', $d['types'])){
    					$ret[]=$d;
    				}
    			}
    		}
    		return json_encode($ret);
    	}elseif($this->getRequestValue('action') == 'geocode_me'){
    		
    		$city_id=(int)$this->getRequestValue('city_id');
    		$city_name='';
    		if($city_id!=0){
    			$DBC=DBC::getInstance();
    			$query='SELECT name FROM '.DB_PREFIX.'_city WHERE city_id=? LIMIT 1';
    			$stmt=$DBC->query($query, array($city_id));
    			if($stmt){
    				$ar=$DBC->fetch($stmt);
    				if($ar['name']!=''){
    					$city_name=$ar['name'].', ';
    				}
    			}
    		}
    		$str=$city_name.urldecode($this->getRequestValue('input'));
    		//echo $str;
    		$url='https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.urlencode($str).'&types=geocode&sensor=false&key=AIzaSyAE2c_0O3OqmJ7Gwn7fjhyLutG_rlLNuaA';
    		//$url='http://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
    		//echo $url;
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL, $url);
    		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    		$output = curl_exec($ch);
    		curl_close($ch);
    		
    		$return=array();
    		
    		$output_array=json_decode($output, true);
    		
    		//print_r($output_array);
    		
    		if(isset($output_array['predictions']) && is_array($output_array['predictions']) && count($output_array['predictions'])>0){
    			foreach ($output_array['predictions'] as $prediction){
    				if(in_array('route', $prediction['types'])){
    					$return[]=$prediction['description'];
    				}
    			}
    		}
    		
    		
    		return json_encode($return);
    	}
    	return false;
    }
    
    public function geocode_me($input){
    	$str=urldecode($input);
    	
    	
    	$url='https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.urlencode($str).'&types=geocode&sensor=false&key=AIzaSyAE2c_0O3OqmJ7Gwn7fjhyLutG_rlLNuaA';
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	
    	$return='';
    	
    	$output_array=json_decode($output, true);
    	
    	if(isset($output_array['predictions']) && is_array($output_array['predictions']) && count($output_array['predictions'])>0){
    		foreach ($output_array['predictions'] as $prediction){
    			if(in_array('route', $prediction['types']) && $prediction['description']==$input){
    				$return=$prediction['terms'][0]['value'];
    			}
    		}
    	}
    	return $return;
    	
    }
    
    public function _preload(){
    	$this->template->assert('apps_geodata_new_map_center', $this->getConfigValue('apps.geodata.new_map_center'));
    	if ( $this->getConfigValue('apps.geodata.show_grid_map') ) {
    		$this->template->assert('geodata_show_grid_map', 1);
    	}else{
    		$this->template->assert('geodata_show_grid_map', 0);
    	}
    	 
    }
    
	function main () {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
		$rs = $this->getTopMenu();

		switch( $this->getRequestValue('do') ){
			case 'geocode' : {
				
				//echo $parsed;
				$rs .= $this->parse();
				break;
			}
			case 'geocode_city' : {
			
				//echo $parsed;
				$rs .= $this->parse_city();
				break;
			}
			default : {
			    $rs .= $this->grid($user_id);
			}
		}
		$rs_new = $this->get_app_title_bar();
		$rs_new .= $rs;
		return $rs_new;
	}
    
    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu () {
    	$rs = '';
    	$rs .= '<a href="?action='.$this->action.'&do=geocode" class="btn btn-primary">'.Multilanguage::_('L_GEOCODE','geodata').'</a>';
    	//$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
    	return $rs;
    }
    
   
    
    /**
     * Grid
     * @param void
     * @return string
     */
    
    function grid ($params=array()) {
    	
    }
    
    function parse(){
    	
    	$start=(int)$_SESSION['geodata']['not_geocoded'];
    	$parse_step=$this->getConfigValue('apps.geodata.per_step');
    	$max_per_day=$this->getConfigValue('apps.geodata.query_count');
    	
    	$stat=$this->loadGeodataParserStat();
    	
    	$_x=date('Y-m-d',time());
    	if(isset($stat[$_x])){
    		$geocoded=$stat[$_x];
    	}else{
    		$geocoded=0;
    	}
    	
    	if($geocoded>=$max_per_day){
    		return 'Дневной лимит геокодирования использован';
    	}
    	
    	if(($max_per_day-$geocoded)<$parse_step){
    		$parse_step=$max_per_day-$geocoded;
    	}
    	
    	$rs.=$this->_parse($start, $parse_step);
    	
    	
    	$this->saveGeodataParserStat(array(date('Y-m-d',time())=>($geocoded+$_SESSION['geodata']['gc'])));
    	
    	
    	//header('location: /admin/index.php?action=geodata&do=geocode');
    	//$rs.='<a href="/admin/index.php?action=geodata&do=geocode">Next</a>';
    	//return $rs;
    }
    
    function parse_city(){
    	$geo_lat=$this->getConfigValue('apps.geodata.geocode_element_name').'_lat';
    	$geo_lng=$this->getConfigValue('apps.geodata.geocode_element_name').'_lng';
    	$query='SELECT city_id, name 
    			FROM '.DB_PREFIX.'_city WHERE geo_lat IS NULL LIMIT 100';
    	
    	$this->db->exec($query);
    	while($this->db->fetch_assoc()){
    		$data[]=$this->db->row;
    	}
    	
    	if(count($data)>0){
    		foreach($data as $d){
    			$answer=$this->geocode_city($d['name']);
    			if(false!==$answer){
    				$query='UPDATE '.DB_PREFIX.'_city SET '.$geo_lat.'='.$answer['lat'].', '.$geo_lng.'='.$answer['lng'].' WHERE city_id='.$d['city_id'];
    				//echo $query;
			    	$this->db->exec($query);
    			}
    			
    		}
    		
    	}
    }
    
    private function _parse($start, $parse_step){
    	$_SESSION['geodata']['gc']=0;
    	$data=array();
    	$report=array();
    	$geo_lat=$this->getConfigValue('apps.geodata.geocode_element_name').'_lat';
    	$geo_lng=$this->getConfigValue('apps.geodata.geocode_element_name').'_lng';
    	$query='SELECT d.id, d.number, c.name AS city, s.name AS street, r.name AS region
    			FROM '.DB_PREFIX.'_data d
    			LEFT JOIN '.DB_PREFIX.'_region r ON r.region_id=d.region_id
    			LEFT JOIN '.DB_PREFIX.'_city c ON d.city_id=c.city_id
    			LEFT JOIN '.DB_PREFIX.'_street s ON d.street_id=s.street_id
    			WHERE (d.'.$geo_lat.' IS NULL OR d.'.$geo_lng.' IS NULL) AND d.city_id<>0 AND d.street_id<>0 order by d.id desc LIMIT '.$start.', '.$parse_step;
    	//echo $query;
    	 
    	$this->db->exec($query);
    	while($this->db->fetch_assoc()){
    		if($this->db->row['city']=='' && $this->getConfigValue('city')!=''){
    			$this->db->row['city']=$this->getConfigValue('city');
    		}
    		$data[]=$this->db->row;
    	}
    	
    	if(count($data)>0){
    		foreach($data as $d){
    			if($d['city']==''){
    				$report[]=array(
    						'id'=>$d['id'],
    						'report'=>'Геокодинг не возможен. Не указан город.',
    						'error_status'=>1
    				);
    				$_SESSION['geodata']['not_geocoded']++;
    				continue;
    			}
    			 
    			if($d['street']==''){
    				$report[]=array(
    						'id'=>$d['id'],
    						'report'=>'Геокодинг не возможен. Не указана улица.',
    						'error_status'=>1
    				);
    				$_SESSION['geodata']['not_geocoded']++;
    				continue;
    			}
    			 
    			if(($d['number']=='' || $d['number']==0) && 1!=$this->getConfigValue('apps.geodata.geocode_partial')){
    				$report[]=array(
    						'id'=>$d['id'],
    						'report'=>'Геокодинг не возможен. Не указан номер дома.',
    						'error_status'=>1
    				);
    				$_SESSION['geodata']['not_geocoded']++;
    				continue;
    			}else{
    				$answer=$this->geocode($d);
    				$geocoded++;
    				if(false!==$answer){
    					$report[]=array(
    							'id'=>$d['id'],
    							'report'=>'Геокодирование удалось. Адрес: '.$answer['address'],
    							'error_status'=>0
    					);
    					$this->updateCoords($d['id'], $answer['lat'], $answer['lng']);
    				}else{
    					$report[]=array(
    							'id'=>$d['id'],
    							'report'=>'Не удалось геокодировать. Адрес: '.$answer['address'],
    							'error_status'=>1
    					);
    					$_SESSION['geodata']['not_geocoded']++;
    				}
    			}
    		}
    		$rs='';
    		foreach($report as $k=>$r){
    			$rs.=$k.' Запись ID: '.$r['id'].' ';
    			if($r['error_status']==1){
    				$rs.='ERROR: '.$r['report'];
    			}else{
    				$rs.='SUCCESS: '.$r['report'];
    			}
    			$rs.="\r\n";
    		}
    		$file=SITEBILL_DOCUMENT_ROOT.'/cache/geodata.log.txt';
    		if(!file_exists($file)){
    			$f=fopen($file,'w');
    		}else{
    			$f=fopen($file,'a');
    		}
    		fwrite($f,date('Y-m-d H:i:s',time()).' '.$rs);
    		fclose($f);
    	}
    	
    	//return $rs;
    }
    
    private function updateCoords($id, $lat, $lng){
    	$geo_lat=$this->getConfigValue('apps.geodata.geocode_element_name').'_lat';
    	$geo_lng=$this->getConfigValue('apps.geodata.geocode_element_name').'_lng';
    	$query='UPDATE '.DB_PREFIX.'_data SET '.$geo_lat.'='.$lat.', '.$geo_lng.'='.$lng.' WHERE id='.$id;
    	//echo $query;
    	$this->db->exec($query);
    }
	
	private function loadGeodataParserStat(){
		$file=SITEBILL_DOCUMENT_ROOT.'/cache/geodata.cache.txt';
		if(file_exists($file)){
			
		}else{
			$f=fopen($file,'w');
			fclose($f);
		}
		$d=file_get_contents($file);
		return unserialize($d);
	}
	
	private function saveGeodataParserStat($data=array()){
		$file=SITEBILL_DOCUMENT_ROOT.'/cache/geodata.cache.txt';
		$f=fopen($file,'w');
		fwrite($f,serialize($data));
		fclose($f);
	}
    
    private function geocode($data){
    	
    	$str=$data['city'].', '.$data['street'].(($data['number']=='' || $data['number']==0) ? '' : ', '.$data['number']);
    	$url='http://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
    	/*if(isset($_SESSION['geodata']['address'][$str])){
    		list($lng,$lat)=explode(' ',$_SESSION['geodata']['address'][$str]);
    		return(array('address'=>$str, 'lat'=>$lat, 'lng'=>$lng));
    	}*/
    	
    	$geodata = simplexml_load_file($url);
    	if($geodata && $geodata instanceof SimpleXMLElement){
    		$founded=$geodata->GeoObjectCollection[0]->metaDataProperty->GeocoderResponseMetaData->found;
    		if((int)$founded>0){
    			$pos=$geodata->GeoObjectCollection[0]->featureMember[0]->GeoObject[0]->Point[0]->pos[0];
    			if($pos!=''){
    				/*$_SESSION['geodata']['address'][$str]=(string)$pos;*/
    				$_SESSION['geodata']['gc']++;
    				list($lng,$lat)=explode(' ',$pos);
    				$geodata=NULL;
    				return(array('address'=>$str, 'lat'=>$lat, 'lng'=>$lng));
    			}
    			//echo $data['id'].' '.$str.' '.$lat.' '.$lng.'<br />';
    		}
    		
    		
    	}
    	return false;
    }
    
    public static function geocode_address_by_google($address){
    	$str=$address;
    	if($address==''){
    		return false;
    	}
    	$url='http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($str);
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	$geodata = json_decode($output, true);
    	//return $geodata;
    	
    	if($geodata['status']=='OK'){
    		$g=$geodata['results'][0]['geometry']['location'];
    		return(array('address'=>$str, 'lat'=>$g['lat'], 'lng'=>$g['lng']));
    	}
    	return false;
    }
    
    public function try_geocode($form_data){
    	$geofield='';
    	//
    	foreach ($form_data as $form_field){
    		if($form_field['type']=='geodata'){
    			$geofield=$form_field['name'];
    			break;
    		}
    	}
    		
    	$geocodefields=$this->getConfigValue('apps.geodata.try_encode_fields');
    	if($geocodefields==''){
    		$geocodefields_array=array();
    	}else{
    		$geocodefields_array=explode(',', $geocodefields);
    	}
    		
    	if($geofield!='' && !empty($geocodefields_array)){
    		if(1==$this->getConfigValue('apps.geodata.try_encode_anycase') || (1!=$this->getConfigValue('apps.geodata.try_encode_anycase') && ($form_data[$geofield]['value']['lat']=='' || $form_data[$geofield]['value']['lng']==''))){
    			$vals=array();
    			//print_r($form_data['country_id']);
    			foreach ($geocodefields_array as $gf){
    				$val='';
    				$gf=trim($gf);
    				if(isset($form_data[$gf])){
    					if(in_array($form_data[$gf]['type'], array('select_box', 'select_by_query'))){
    						$val=$form_data[$gf]['value_string'];
    					}elseif(in_array($form_data[$gf]['type'], array('safe_string', 'gadres'))){
    						$val=$form_data[$gf]['value'];
    					}
    				}
    				if($val!=''){
    					$vals[]=$val;
    				}
    			}
    			$str='';
    			if(!empty($vals)){
    				$str=implode(',', $vals);
    			}
    				
    			if($str!=''){
    				//require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
    				$res=self::geocode_address_by_google($str);
    				if($res){
    					$form_data[$geofield]['value']['lat']=$res['lat'];
    					$form_data[$geofield]['value']['lng']=$res['lng'];
    				}
    			}
    		}
    	}
    	return $form_data;
    }
    
    public static function geocode_address($address){
    	$str=$address;
    	if($address==''){
    		return false;
    	}
    	$url='http://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	$geodata = simplexml_load_string($output);
    	
    	
    	//$geodata = simplexml_load_file($url);
    	if($geodata && $geodata instanceof SimpleXMLElement){
    		$founded=$geodata->GeoObjectCollection[0]->metaDataProperty->GeocoderResponseMetaData->found;
    		if((int)$founded>0){
    			$pos=$geodata->GeoObjectCollection[0]->featureMember[0]->GeoObject[0]->Point[0]->pos[0];
    			if($pos!=''){
    				list($lng,$lat)=explode(' ',$pos);
    				return(array('address'=>$str, 'lat'=>$lat, 'lng'=>$lng));
    			}
    		}
    	}
    	return false;
    }
    
    private function geocode_city($data){
    	if($data==''){
    		return false;
    	} 
    	$str=$data;
    	$url='http://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
    	$geodata = simplexml_load_file($url);
    	if($geodata && $geodata instanceof SimpleXMLElement){
    		$founded=$geodata->GeoObjectCollection[0]->metaDataProperty->GeocoderResponseMetaData->found;
    		if((int)$founded>0){
    			$pos=$geodata->GeoObjectCollection[0]->featureMember[0]->GeoObject[0]->Point[0]->pos[0];
    			if($pos!=''){
    				$_SESSION['geodata']['gc']++;
    				list($lng,$lat)=explode(' ',$pos);
    				$geodata=NULL;
    				return(array('address'=>$str, 'lat'=>$lat, 'lng'=>$lng));
    			}
    		}
    	}
    	return false;
    }
	
	
}