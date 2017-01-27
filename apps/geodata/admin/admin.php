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
    	if ( !$config_admin->check_config_item('apps.geodata.map_cache_time') ) {
    		$config_admin->addParamToConfig('apps.geodata.map_cache_time','0','Время жизни кэша для карты в секундах (0 - кэш выклюен)');
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
    	if ( !$config_admin->check_config_item('apps.geodata.save_geocoder') ) {
    		$config_admin->addParamToConfig('apps.geodata.save_geocoder','g','Геокодер используемый при сохранении\изменении (g - Google, y - Yandex)');
    	}
    	if ( !$config_admin->check_config_item('apps.geodata.prevtext') ) {
    		$config_admin->addParamToConfig('apps.geodata.prevtext','','Предварительный текст для геокодирования на форме');
    	} 
    	if ( !$config_admin->check_config_item('apps.geodata.no_scroll_zoom') ) {
    		$config_admin->addParamToConfig('apps.geodata.no_scroll_zoom','0','Выключить зуммирование карты скроллом',1);
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
    			//print_r($data);
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
	}/* elseif ($this->getRequestValue('action') == 'iframe_content') {
	    //Тут генерируется код содержимого для iframe
	    echo $this->iframe_grid();
		
	} */elseif($this->getRequestValue('action') == 'geocode_fast'){
    		$input=$this->getRequestValue('input');
    		$result=array();
    		
    		
    		
    		if(trim($this->getConfigValue('apps.geodata.prevtext'))!=''){
    			$prevtext=trim($this->getConfigValue('apps.geodata.prevtext')).',';
    		}else{
    			$prevtext='';
    		}
    		
    		$str=urldecode($prevtext.$input);
    		
    		if(1===intval($this->getConfigValue('use_google_map'))){
    			$res=$this->geocode_address_by_google($str);
    			if($res){
    				$result=$res;
    			}
    		}else{
    			$res=$this->geocode_address($str);
    			if($res){
    				$result=$res;
    			}
    		}
    		return json_encode($result);
    	}
    	return false;
    }
    
    /*public function iframe_grid () {
    	if($this->getConfigValue('use_google_map')){
    		$this->template->assign('map_type', 'google');
    	}else{
    		$this->template->assign('map_type', 'yandex');
    	}
    	$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/geodata/site/template/iframe_map.tpl';
    	$grid_constructor = $this->_getGridConstructor();
		$params['no_portions']=1;
		$res=$grid_constructor->get_sitebill_adv_core( $params, false, false, false, true );
		
		$this->template->assign('iframe_grid_data', json_encode($res['geoobjects_collection_clustered']));
		$html=$this->template->fetch($tpl);
		
		return $html;
    }*/
    
    public function geocode_me($input){
    	$str=urldecode($input);
    	
    	
    	$url='https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.urlencode($str).'&types=geocode&sensor=false&key=AIzaSyAE2c_0O3OqmJ7Gwn7fjhyLutG_rlLNuaA';
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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
			    /*$string='49.769069,24.014407;49.774342,24.014268;49.77764,24.014172;49.78148,24.014072;49.78352,24.014194;49.783959,24.014211;49.785909,24.01458;49.794193,24.016371;49.80172,24.018023;49.801893,24.018013;49.805279,24.018806;49.808152,24.01917;49.814694,24.019878;49.818404,24.020232;49.81801,24.023718;49.817934,24.023814;49.817865,24.02435;49.817892,24.024618;49.817968,24.024811;49.818334,24.025208;49.820362,24.027364;49.821421,24.028608;49.821594,24.028897;49.821718,24.02924;49.821787,24.02954;49.82214,24.032297;49.822216,24.03264;49.822182,24.032865;49.821968,24.033111;49.821602,24.033389;49.821927,24.034343;49.821907,24.034858;49.821941,24.035641;49.821996,24.03681;49.822169,24.037786;49.822328,24.038054;49.822944,24.038461;49.81992,24.040317;49.819567,24.040617;49.81927,24.041046;49.818377,24.043653;49.818031,24.044082;49.817651,24.044339;49.817409,24.044457;49.817457,24.044789;49.817381,24.045143;49.817415,24.04555;49.817235,24.045625;49.816661,24.045583;49.816142,24.045754;49.815789,24.046226;49.815748,24.046526;49.815831,24.046654;49.816232,24.046547;49.816335,24.046632;49.816355,24.046825;49.81713,24.050118;49.817226,24.050214;49.817558,24.051619;49.817724,24.051501;49.818395,24.054408;49.819924,24.05369;49.820983,24.055674;49.818969,24.057454;49.818769,24.05769;49.818271,24.058569;49.816672,24.061476;49.816506,24.061636;49.816236,24.0617;49.816043,24.061785;49.815981,24.061956;49.815926,24.062846;49.816071,24.06644;49.816147,24.067566;49.816147,24.068445;49.816044,24.069131;49.815851,24.070128;49.815311,24.072295;49.814155,24.076565;49.814045,24.077165;49.813838,24.078441;49.81082,24.079535;49.810377,24.079535;49.809893,24.079471;49.809215,24.078914;49.807983,24.077498;49.805782,24.078528;49.805982,24.079622;49.805809,24.080534;49.804051,24.081231;49.80365,24.081552;49.803083,24.081573;49.802516,24.081626;49.801803,24.081872;49.801644,24.081947;49.801471,24.082086;49.801305,24.082161;49.801174,24.081947;49.798072,24.085541;49.797823,24.085562;49.797643,24.084575;49.797436,24.082623;49.789998,24.084479;49.789721,24.082838;49.788835,24.082281;49.787547,24.082366;49.78691,24.078847;49.783655,24.079619;49.783433,24.078911;49.782353,24.076616;49.781872,24.076258;49.781163,24.073076;49.780997,24.069536;49.780804,24.062091;49.781108,24.056555;49.781095,24.054259;49.781219,24.05177;49.781745,24.052048;49.782908,24.05192;49.783406,24.05222;49.785165,24.051899;49.785913,24.051556;49.785761,24.049346;49.786938,24.049218;49.786883,24.048296;49.787575,24.048039;49.788004,24.04806;49.788114,24.047674;49.788391,24.047674;49.787907,24.045636;49.787464,24.044456;49.786716,24.043126;49.785747,24.041646;49.786675,24.039007;49.787478,24.034866;49.784264,24.033161;49.783727,24.031977;49.781718,24.031012;49.776963,24.030319;49.768004,24.030319;49.768683,24.020213';
    	
			    
			    $lines=self::buidLinesArrayFromPolygoneString($string);
			    
			    
			    $points=self::converStringToLinesArray($string);
			    $points_count=count($points);
			    
			    $i=0;
			    foreach ($points as $k=>$point){
			    	$lines[$k]['s']['lat']=$point[0];
			    	$lines[$k]['s']['lng']=$point[1];
			    	$lines[$k]['e']['lat']=$points[$k+1][0];
			    	$lines[$k]['e']['lng']=$points[$k+1][1];
			    	$i++;
			    	if($i==$points_count-1){
			    		break;
			    	}
			    }
			    49.781009, 24.039068
			    foreach ($lines as $k=>$line){
			    	$lines[$k]=array_merge($lines[$k], self::detectLineType($line['s'], $line['e']));
			    }
			    
			    $p=array('lat'=>49.781009, 'lng'=>24.039068);
			    
			    var_dump(self::isInRegion($p, $lines));
			    
			    global $smarty;
			    $smarty->assign('crds', $string);
			    $smarty->assign('point', json_encode($p));
			    $rs.=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/geodata/template/test.tpl');
			    */
				//print_r($lines);
			    
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
    	
	return '<div><br><br>'.nl2br($rs).'</div>';
    	
    	//header('location: /admin/index.php?action=geodata&do=geocode');
    	//$rs.='<a href="/admin/index.php?action=geodata&do=geocode">Next</a>';
    	//return $rs;
    }
    
    function parse_city(){
    	$geo_lat=$this->getConfigValue('apps.geodata.geocode_element_name').'_lat';
    	$geo_lng=$this->getConfigValue('apps.geodata.geocode_element_name').'_lng';
    	$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE geo_lat IS NULL LIMIT 100';
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$data[]=$ar;
    		}
    	}
    	    	
    	
    	if(count($data)>0){
    		foreach($data as $d){
    			$answer=$this->geocode_city($d['name']);
    			if(false!==$answer){
    				$query='UPDATE '.DB_PREFIX.'_city SET '.$geo_lat.'='.$answer['lat'].', '.$geo_lng.'='.$answer['lng'].' WHERE city_id='.$d['city_id'];
    				$stmt=$DBC->query($query);
    			}
    		}
    	}
    }
    
    private function _parse($start, $parse_step){
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $data_model->get_kvartira_model(false, true);
    	$form_data = $form_data['data'];
    	
    	/*$geofield='';
    	//
    	foreach ($form_data as $form_field){
    		if($form_field['type']=='geodata'){
    			$geofield=$form_field['name'];
    			break;
    		}
    	}*/
    	
    	
    	$_SESSION['geodata']['gc']=0;
    	$ids=array();
    	$data=array();
    	$report=array();
    	
    	$geofield=$this->getConfigValue('apps.geodata.geocode_element_name');
    	$geo_lat=$this->getConfigValue('apps.geodata.geocode_element_name').'_lat';
    	$geo_lng=$this->getConfigValue('apps.geodata.geocode_element_name').'_lng';
    	$query='SELECT d.id FROM '.DB_PREFIX.'_data d WHERE (d.'.$geo_lat.' IS NULL OR d.'.$geo_lng.' IS NULL) ORDER BY d.id DESC LIMIT '.$start.', '.$parse_step;
    	//echo $query;
    	//echo $query;
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ids[]=$ar['id'];
    		}
    	}
    	
    	$update_query='UPDATE '.DB_PREFIX.'_data SET `'.$geo_lat.'`=?, `'.$geo_lng.'`=? WHERE id=?';
    	if(!empty($ids)){
    		foreach ($ids as $id){
    			$form_data_shared = $form_data;
    			$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared, true );
    			$form_data_shared=$this->try_geocode($form_data_shared);
    			//print_r($form_data_shared[$geofield]);
    			if($form_data_shared[$geofield]['value']['lat']!='' && $form_data_shared[$geofield]['value']['lng']!=''){
    				$stmt=$DBC->query($update_query, array($form_data_shared[$geofield]['value']['lat'], $form_data_shared[$geofield]['value']['lng'], $id));
    				$geocoded++;
    				$report[]=array(
    						'id'=>$id,
    						'report'=>'Геокодирование удалось.',
    						'error_status'=>0
    				);
    			}else{
    				$report[]=array(
    						'id'=>$id,
    						'report'=>'Геокодинг не возможен.',
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
    	
    	return $rs;
    	
    	
    	
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
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			if($ar['city']=='' && $this->getConfigValue('city')!=''){
	    			$ar['city']=$this->getConfigValue('city');
	    		}
	    		$data[]=$ar;
    		}
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
    	$DBC=DBC::getInstance();
    	$DBC->query($query);
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
    	$url='https://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
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
    	$url='https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($str);
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	$geodata = json_decode($output, true);
    	//return $geodata;
    	
    	if($geodata['status']=='OK'){
    		$g=$geodata['results'][0]['geometry']['location'];
    		return(array('address'=>$str, 'lat'=>str_replace(',', '.', $g['lat']), 'lng'=>str_replace(',', '.', $g['lng'])));
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
    				$str=implode(', ', $vals);
    			}
    			
    			
    				
    			if($str!=''){
    				//require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
    				if('y'==$this->getConfigValue('apps.geodata.save_geocoder')){
    					$res=self::geocode_address($str);
    					
    					//echo $str.'<br/>';
    					//print_r($res);
    				}else{
    					$res=self::geocode_address_by_google($str);
    				}
    				
    				
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
    	$url='https://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
    	//echo $url;
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	$geodata = simplexml_load_string($output);
    	if($geodata && $geodata instanceof SimpleXMLElement){
    		$founded=$geodata->GeoObjectCollection[0]->metaDataProperty->GeocoderResponseMetaData->found;
    		if((int)$founded>0){
    			$pos=$geodata->GeoObjectCollection[0]->featureMember[0]->GeoObject[0]->Point[0]->pos[0];
    			if($pos!=''){
    				list($lng,$lat)=explode(' ',$pos);
    				return(array('address'=>$str, 'lat'=>str_replace(',', '.', $lat), 'lng'=>str_replace(',', '.', $lng)));
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
    	$url='https://geocode-maps.yandex.ru/1.x/?geocode='.urlencode($str);
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
    
    public static function isBetween($point, $fp1, $fp2){
    	$start=$fp1;
    	if($fp2<$start){
    		$start=$fp2;
    		$end=$fp1;
    	}else{
    		$end=$fp2;
    	}
    	if($point>=$start && $point<=$end){
    		return true;
    	}
    	return false;
    }
    
    public static function converStringToLinesArray($string){
    	$pairs=explode(';', $string);
    	foreach ($pairs as $p){
    		$points[]=explode(',', $p);
    	}
    	$endel=end($points);
    	reset($points);
    	if($endel[0]!=$points[0][0] && $endel[1]!=$points[0][1]){
    		$points[]=$points[0];
    	}
    	return $points;
    }
    
    public static function buidLinesArrayFromPolygoneString($polygone){
    	$pairs=explode(';', $polygone);
    	foreach ($pairs as $p){
    		$points[]=explode(',', $p);
    	}
    	$endel=end($points);
    	reset($points);
    	if($endel[0]!=$points[0][0] && $endel[1]!=$points[0][1]){
    		$points[]=$points[0];
    	}
    	 
    	$lines=array();
    	$count=count($points);
    	$i=0;
    	$max_lat=false;
    	$min_lat=false;
    	$max_lng=false;
    	$min_lng=false;
    	foreach ($points as $k=>$point){
    		$lines[$k]['s']['lat']=$point[0];
    		$lines[$k]['s']['lng']=$point[1];
    		$lines[$k]['e']['lat']=$points[$k+1][0];
    		$lines[$k]['e']['lng']=$points[$k+1][1];
    		$delta_lat=$lines[$k]['e']['lat']-$lines[$k]['s']['lat'];
    		$delta_lng=$lines[$k]['e']['lng']-$lines[$k]['s']['lng'];
    		if($delta_lng==0){
    			$lines[$k]['type']='v';
    			$koef=0;
    		}elseif($delta_lat==0){
    			$lines[$k]['type']='h';
    			$koef=0;
    		}else{
    			$lines[$k]['type']='c';
    			$koef=($delta_lat)/($delta_lng);
    		}
    	
    		$lines[$k]['koef']=$koef;
    		if($lines[$k]['type']=='c'){
    			$lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
    		}else{
    			$lines[$k]['ckoef']=0;
    		}
    		//$lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
    		//echo $point[0].'<br>';
    		//echo $point[1].'<br>';
    		if($max_lat!==false && $point[0]>$max_lat){
    			$max_lat=$point[0];
    		}elseif($max_lat===false){
    			$max_lat=$point[0];
    		}
    		if($min_lat!==false && $point[0]<$min_lat){
    			$min_lat=$point[0];
    		}elseif($min_lat===false){
    			$min_lat=$point[0];
    		}
    		if($max_lng!==false && $point[1]>$max_lng){
    			$max_lng=$point[1];
    		}elseif($max_lng===false){
    			$max_lng=$point[1];
    		}
    		if($min_lng!==false && $point[1]<$min_lng){
    			$min_lng=$point[1];
    		}elseif($min_lng===false){
    			$min_lng=$point[1];
    		}
    		$i++;
    		if($i==$count-1){
    			break;
    		}
    	}
    	return $lines;
    }
    
   /* public static function detectLineType($point1, $point2){
       	$delta_lat=$point2['lat']-$point1['lat'];
    	$delta_lng=$point2['lng']-$point1['lng'];
    	if($delta_lng==0){
    		$type='v';
    		$koef=0;
    	}elseif($delta_lat==0){
    		$type='h';
    		$koef=0;
    	}else{
    		$type='c';
    		$koef=($delta_lat)/($delta_lng);
    	}
    	
    	//$lines[$k]['koef']=$koef;
    	if($type=='c'){
    		$ckoef=$point1['lat']-$koef*$point1['lng'];
    	}else{
    		$ckoef=0;
    	}
    	return array('type'=>$type, 'koef'=>$koef, 'ckoef'=>$ckoef);
    }*/
    
    public static function isInRegion($point, $lines){
    	//$line='49.769069,24.014407;49.774342,24.014268;49.77764,24.014172;49.78148,24.014072;49.78352,24.014194;49.783959,24.014211;49.785909,24.01458;49.794193,24.016371;49.80172,24.018023;49.801893,24.018013;49.805279,24.018806;49.808152,24.01917;49.814694,24.019878;49.818404,24.020232;49.81801,24.023718;49.817934,24.023814;49.817865,24.02435;49.817892,24.024618;49.817968,24.024811;49.818334,24.025208;49.820362,24.027364;49.821421,24.028608;49.821594,24.028897;49.821718,24.02924;49.821787,24.02954;49.82214,24.032297;49.822216,24.03264;49.822182,24.032865;49.821968,24.033111;49.821602,24.033389;49.821927,24.034343;49.821907,24.034858;49.821941,24.035641;49.821996,24.03681;49.822169,24.037786;49.822328,24.038054;49.822944,24.038461;49.81992,24.040317;49.819567,24.040617;49.81927,24.041046;49.818377,24.043653;49.818031,24.044082;49.817651,24.044339;49.817409,24.044457;49.817457,24.044789;49.817381,24.045143;49.817415,24.04555;49.817235,24.045625;49.816661,24.045583;49.816142,24.045754;49.815789,24.046226;49.815748,24.046526;49.815831,24.046654;49.816232,24.046547;49.816335,24.046632;49.816355,24.046825;49.81713,24.050118;49.817226,24.050214;49.817558,24.051619;49.817724,24.051501;49.818395,24.054408;49.819924,24.05369;49.820983,24.055674;49.818969,24.057454;49.818769,24.05769;49.818271,24.058569;49.816672,24.061476;49.816506,24.061636;49.816236,24.0617;49.816043,24.061785;49.815981,24.061956;49.815926,24.062846;49.816071,24.06644;49.816147,24.067566;49.816147,24.068445;49.816044,24.069131;49.815851,24.070128;49.815311,24.072295;49.814155,24.076565;49.814045,24.077165;49.813838,24.078441;49.81082,24.079535;49.810377,24.079535;49.809893,24.079471;49.809215,24.078914;49.807983,24.077498;49.805782,24.078528;49.805982,24.079622;49.805809,24.080534;49.804051,24.081231;49.80365,24.081552;49.803083,24.081573;49.802516,24.081626;49.801803,24.081872;49.801644,24.081947;49.801471,24.082086;49.801305,24.082161;49.801174,24.081947;49.798072,24.085541;49.797823,24.085562;49.797643,24.084575;49.797436,24.082623;49.789998,24.084479;49.789721,24.082838;49.788835,24.082281;49.787547,24.082366;49.78691,24.078847;49.783655,24.079619;49.783433,24.078911;49.782353,24.076616;49.781872,24.076258;49.781163,24.073076;49.780997,24.069536;49.780804,24.062091;49.781108,24.056555;49.781095,24.054259;49.781219,24.05177;49.781745,24.052048;49.782908,24.05192;49.783406,24.05222;49.785165,24.051899;49.785913,24.051556;49.785761,24.049346;49.786938,24.049218;49.786883,24.048296;49.787575,24.048039;49.788004,24.04806;49.788114,24.047674;49.788391,24.047674;49.787907,24.045636;49.787464,24.044456;49.786716,24.043126;49.785747,24.041646;49.786675,24.039007;49.787478,24.034866;49.784264,24.033161;49.783727,24.031977;49.781718,24.031012;49.776963,24.030319;49.768004,24.030319;49.768683,24.020213;';
    	$point_lat=$point['lat'];
    	$point_lng=$point['lng'];
    	    	 
    	foreach($lines as $line){
    		if($line['type']=='v' && self::isBetween($point_lat, $line['s']['lat'], $line['e']['lat']) && $point_lng==$line['s']['lng']){
    			return true;
    		}elseif($line['type']=='h' && self::isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat==$line['s']['lat']){
    			return true;
    		}
    	}
    	 
    	$intersectCount=0;
    	 
    	foreach($lines as $line){
    		if($line['type']=='v'){
    			 
    		}elseif($line['type']=='h' && self::isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat<$line['s']['lat']){
    			$intersectCount++;
    		}else{
    			//echo 'LINE: '.$line['s']['lng'].' '.$line['e']['lng']."\n\r";
    			if(self::isBetween($point_lng, $line['s']['lng'], $line['e']['lng'])){
    				$intersect_lat=$line['koef']*$point_lng+$line['ckoef'];
    				if($intersect_lat>=$point_lat){
    					$intersectCount++;
    				}
    			}
    		}
    	}
    	//echo $intersectCount;
    	 
    	if($intersectCount==0){
    		return false;
    	}
    	if($intersectCount==1){
    		return true;
    	}
    	if($intersectCount%2==0){
    		return false;
    	}
    	return true;
    }
	
	
}