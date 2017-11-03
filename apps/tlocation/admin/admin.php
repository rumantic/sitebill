<?php
/*
 * параметр visibles - перечисляет видымые элементы
 */
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * TLocation admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class tlocation_admin extends Object_Manager {
     
    function install () {
    	
    }
    
    function main(){
    	
    }
    
	function ajax(){
		switch($this->getRequestValue('action')){
			case 'world_select_list' : {
				return $this->getCountries();
				break;
			}
			case 'country_select_list' : {
				echo $this->getCountries();
				break;
			}
			case 'region_select_list' : {
				echo $this->getRegions($this->getRequestValue('country_id'));
				break;
			}
			case 'get_geolist' : {
				$dep_key=$this->getRequestValue('depelkey');
				$dep_val=$this->getRequestValue('dep_val');
				echo $this->_getGeolist($this->getRequestValue('from'), SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('term')), $dep_key, $dep_val);
				//echo $this->_getRegions(SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('term')));
				break;
			}
			/*case 'get_geoitem' : {
				echo $this->_getGeoitem($this->getRequestValue('from'), $this->getRequestValue('id'));
				break;
			}*/
			case 'subregion_select_list' : {
			
				break;
			}
			case 'city_select_list' : {
				echo $this->getCities($this->getRequestValue('region_id'));
				break;
			}
			case 'district_select_list' : {
				echo $this->getDistricts($this->getRequestValue('city_id'));
				break;
			}
			case 'street_select_list' : {
				if((int)$this->getRequestValue('district_id')!=0){
					echo $this->getStreets($this->getRequestValue('district_id'));
				}elseif((int)$this->getRequestValue('city_id')!=0){
					echo $this->getStreets($this->getRequestValue('city_id'));
				}
				
				break;
			}
			default : {
				return false;
			}
		}
		
	}
	
	private function getCountries(){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT country_id, name FROM '.DB_PREFIX.'_country ORDER BY name ASC';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('country_id'=>$ar['country_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function getRegions($country_id){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
		$stmt=$DBC->query($query, array($country_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('region_id'=>$ar['region_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	/*
	private function _getGeoitem($from, $id){
		switch($from){
			case 'region' : {
				$DBC=DBC::getInstance();
				$query='SELECT name FROM '.DB_PREFIX.'_region WHERE region_id=? LIMIT 1';
				$stmt=$DBC->query($query, array($id));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					return $ar['name'];
				}
				break;
			}
			case 'city' : {
				$DBC=DBC::getInstance();
				$query='SELECT name FROM '.DB_PREFIX.'_city WHERE city_id=? LIMIT 1';
				$stmt=$DBC->query($query, array($id));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					return $ar['name'];
				}
				break;
			}
			case 'district' : {
				$DBC=DBC::getInstance();
				$query='SELECT name FROM '.DB_PREFIX.'_district WHERE id=? LIMIT 1';
				$stmt=$DBC->query($query, array($id));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					return $ar['name'];
				}
				break;
			}
			case 'street' : {
				$DBC=DBC::getInstance();
				$query='SELECT name FROM '.DB_PREFIX.'_street WHERE street_id=? LIMIT 1';
				$stmt=$DBC->query($query, array($id));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					return $ar['name'];
				}
				break;
			}
			default : {
	
			}
		}
	}
	*/
	private function _getGeolist($from, $term, $dep_key='', $dep_val=0){
		/*if(strtolower($_SERVER['REQUEST_METHOD'])!='post'){
			return '';
		}*/
		switch($from){
			case 'country' : {
				return $this->_getCountries($term, $dep_key, $dep_val);
			}
			case 'district' : {
				return $this->_getDistricts($term, $dep_key, $dep_val);
			}
			case 'region' : {
				return $this->_getRegions($term, $dep_key, $dep_val);
			}
			case 'city' : {
				return $this->_getCities($term, $dep_key, $dep_val);
			}
			case 'street' : {
				return $this->_getStreets($term, $dep_key, $dep_val);
			}
			case 'metro' : {
				return $this->_getMetros($term, $dep_key, $dep_val);
			}
			default : {
				$model=$this->getRequestValue('model');
				if($model!='' && $model!='data'){
					require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
					$ATH=new Admin_Table_Helper();
					$data_model_shared=$ATH->load_model($model, false, false);
				}
				
				if(!$data_model_shared || empty($data_model_shared[$model])){
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
					$data_model = new Data_Model();
					$data_model_shared = $data_model->get_kvartira_model(false, false);
					$model='data';
				}
				
				$data_model_shared=$data_model_shared[$model];
				
	    		
				
				//echo $from;
				//print_r($data_model_shared['data'][$from]);
				foreach($data_model_shared as $key=>$value){
					if($value['type']=='select_by_query' && $value['primary_key_table']==$from){
						//print_r($value);
						return $this->_getAbstractData($term, $value['primary_key_table'], $value['primary_key_name'], $value['value_name']);
					}
				}
				//if(isset($data_model_shared['data'][$from]) && $data_model_shared['data'][$from]['type']=='select_by_query'){
					
				//}
				//print_r($data_model_shared);
			}
		}
	}
	
	private function _getAbstractData($term, $table, $key, $field){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT '.$key.', '.$field.' FROM '.DB_PREFIX.'_'.$table.' WHERE '.$field.' LIKE \'%'.$term.'%\' ORDER BY '.$field.' ASC LIMIT 50';
		//echo $query;
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$pos=strpos(mb_strtolower($ar[$field], SITE_ENCODING), mb_strtolower($term, SITE_ENCODING));
				$ret[]=array($key=>$ar[$key], $field=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar[$field]), 'pos'=>$pos);
			}
		}
		usort($ret, array($this, 'sortByPosition'));
	
	
		return json_encode($ret);
	}
	
	private function _getStreets($term, $dep_key='', $dep_val=0){
		$ret=array();
		$DBC=DBC::getInstance();
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$lang=$this->getCurrentLang();
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
			$data_model = new Data_Model();
			$data_model_shared = $data_model->get_kvartira_model(false, true);
			$lang_key = 'name';
			if(isset($data_model_shared['data']) && 0==intval($data_model_shared['data']['street_id']['parameters']['no_ml'])){
				if($lang!='ru'){
					$lang_key = 'name_'.$lang;
				}else{
					$lang_key = 'name';
				}
			}elseif(isset($data_model_shared['data']) && 1==intval($data_model_shared['data']['street_id']['parameters']['no_ml'])){
				$lang_key = 'name';
			}
		}else{
			$lang_key = 'name';
		}
		
		
		
		if($dep_key!='' && $dep_val!=0){
			$query='SELECT street_id, `'.$lang_key.'` AS name FROM '.DB_PREFIX.'_street WHERE `'.$lang_key.'` LIKE \'%'.$term.'%\' AND `'.$dep_key.'`=? ORDER BY name ASC LIMIT 50';
			$stmt=$DBC->query($query, array($dep_val));
		}else{
			$query='SELECT street_id, `'.$lang_key.'` AS name FROM '.DB_PREFIX.'_street WHERE `'.$lang_key.'` LIKE \'%'.$term.'%\' ORDER BY name ASC LIMIT 50';
			$stmt=$DBC->query($query);
		}
		
		//echo $query;
		
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				
				if($ar['lname']!=''){
					$ar['name']=$ar['lname'];
				}
				$pos=strpos(mb_strtolower($ar['name'], SITE_ENCODING), mb_strtolower($term, SITE_ENCODING));
				$ret[]=array('street_id'=>$ar['street_id'], 'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']), 'pos'=>$pos);
			}
		}
		usort($ret, array($this, 'sortByPosition'));
		return json_encode($ret);
	}
	
	private function sortByPosition($a, $b){
		if($a['pos']>$b['pos']){
			return 1;
		}else{
			return -1;
		}
	}
	
	private function _getMetros($term, $dep_key='', $dep_val=0){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT metro_id, name FROM '.DB_PREFIX.'_metro WHERE name LIKE \'%'.$term.'%\' ORDER BY name ASC LIMIT 50';
		//echo $query;
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('metro_id'=>$ar['metro_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function _getCountries($term, $dep_key='', $dep_val=0){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT country_id, name FROM '.DB_PREFIX.'_country WHERE name LIKE \'%'.$term.'%\' ORDER BY name ASC';
		//echo $query;
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('country_id'=>$ar['country_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function _getDistricts($term, $dep_key='', $dep_val=0){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE name LIKE \'%'.$term.'%\' ORDER BY name ASC';
		//echo $query;
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('id'=>$ar['id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function _getRegions($term, $dep_key='', $dep_val=0){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE name LIKE \'%'.$term.'%\' ORDER BY name ASC';
		//echo $query;
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('region_id'=>$ar['region_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function _getCities($term, $dep_key='', $dep_val=0){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE name LIKE \'%'.$term.'%\' ORDER BY name ASC';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('city_id'=>$ar['city_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function getCities($region_id){
		$ret=array();
		$DBC=DBC::getInstance();
		if(is_array($region_id)){
			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id IN ('.implode(', ', $region_id).') ORDER BY name ASC';
		}else{
			$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
		}
		
		$stmt=$DBC->query($query, array($region_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('city_id'=>$ar['city_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function getDistricts($city_id){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=? ORDER BY name ASC';
		$stmt=$DBC->query($query, array($city_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('district_id'=>$ar['id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	private function getStreets($district_id){
		$ret=array();
		$DBC=DBC::getInstance();
		
		if(1==$this->getConfigValue('link_street_to_city')){
			$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE city_id=? ORDER BY name ASC';
		}else{
			$query='SELECT street_id, name FROM '.DB_PREFIX.'_street WHERE district_id=? ORDER BY name ASC';
		}
		
		
		$stmt=$DBC->query($query, array($district_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=array('street_id'=>$ar['street_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
			}
		}
		return json_encode($ret);
	}
	
	public function getStartBlock(){
		return $this->_getStartBlock();
	}
	
	private function _getStartBlock(){
		return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/tlocation/admin/template/tlocation_block.tpl');
	}
	
	public static function adoptTLocationModel($model){
		if(isset($model['tlocation'])){
		
			$model['country_id']['name'] = 'country_id';
			$model['country_id']['primary_key_name'] = 'country_id';
			$model['country_id']['primary_key_table'] = 'country';
			$model['country_id']['title'] = Multilanguage::_('L_COUNTRY');
			$model['country_id']['value_string'] = '';
			$model['country_id']['value'] = 0;
			$model['country_id']['length'] = 40;
			$model['country_id']['type'] = 'select_by_query';
			$model['country_id']['query'] = 'select * from '.DB_PREFIX.'_country order by name';
			$model['country_id']['value_name'] = 'name';
			$model['country_id']['value_default'] = 0;
		
		
			$model['region_id']['name'] = 'region_id';
			$model['region_id']['primary_key_name'] = 'region_id';
			$model['region_id']['primary_key_table'] = 'region';
			$model['region_id']['title'] = Multilanguage::_('L_REGION');
			$model['region_id']['value_string'] = '';
			$model['region_id']['value'] = 0;
			$model['region_id']['length'] = 40;
			$model['region_id']['type'] = 'select_by_query';
			$model['region_id']['query'] = 'select * from '.DB_PREFIX.'_region order by name';
			$model['region_id']['value_name'] = 'name';
			$model['region_id']['value_default'] = 0;
		
			$model['city_id']['name'] = 'city_id';
			$model['city_id']['primary_key_name'] = 'city_id';
			$model['city_id']['primary_key_table'] = 'city';
			$model['city_id']['title'] = Multilanguage::_('L_CITY');
			$model['city_id']['value_string'] = '';
			$model['city_id']['value'] = 0;
			$model['city_id']['length'] = 40;
			$model['city_id']['type'] = 'select_by_query';
			$model['city_id']['query'] = 'select * from '.DB_PREFIX.'_city order by name';
			$model['city_id']['value_name'] = 'name';
			$model['city_id']['value_default'] = 0;
		
			$model['district_id']['name'] = 'district_id';
			$model['district_id']['primary_key_name'] = 'id';
			$model['district_id']['primary_key_table'] = 'district';
			$model['district_id']['title'] = Multilanguage::_('L_DISTRICT');
			$model['district_id']['value_string'] = '';
			$model['district_id']['value'] = 0;
			$model['district_id']['length'] = 40;
			$model['district_id']['type'] = 'select_by_query';
			$model['district_id']['query'] = 'select * from '.DB_PREFIX.'_district order by name';
			$model['district_id']['value_name'] = 'name';
			$model['district_id']['value_default'] = 0;
		
			$model['street_id']['name'] = 'street_id';
			$model['street_id']['primary_key_name'] = 'street_id';
			$model['street_id']['primary_key_table'] = 'street';
			$model['street_id']['title'] = Multilanguage::_('L_STREET');
			$model['street_id']['value_string'] = '';
			$model['street_id']['value'] = 0;
			$model['street_id']['length'] = 40;
			$model['street_id']['type'] = 'select_by_query';
			$model['street_id']['query'] = 'select * from '.DB_PREFIX.'_street order by name';
			$model['street_id']['value_name'] = 'name';
			$model['street_id']['value_default'] = 0;
		
			unset($model['tlocation']);
		}
		
		return $model;
	}
	
}