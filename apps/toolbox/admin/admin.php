<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Tags Manager admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class toolbox_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        $this->action='toolbox';
        
        $geography=array(
        		'country_id'=>array('country_id', 'linked'=>'region_id'),
        		'region_id'=>array('region_id', 'linked'=>'city_id', 'depended'=>'country_id'),
        		'city_id'
        		);
        
        $geography['country_id']=array(
        	'table'=>'country',
        	'pk'=>'country_id',
        	'dv'=>'name',
        	'next'=>array('region_id'),
        	'prev'=>array()
        );
        $geography['region_id']=array(
        	'table'=>'region',
       		'pk'=>'region_id',
        	'dv'=>'name',
        	'next'=>array('city_id'),
        	'prev'=>array('country_id'=>array('ownkey'=>'country_id'))
        );
        $geography['city_id']=array(
        	'table'=>'city',
        	'pk'=>'city_id',
        	'dv'=>'name',
        	'prev'=>array('region_id'=>array('ownkey'=>'region_id'))
        );
        
        //$this->joinCities(5, 2);
    }
    
    function joinCities($c1, $c2){
    	$DBC=DBC::getInstance();
    	$query='SELECT r.country_id, c.region_id FROM '.DB_PREFIX.'_city c LEFT JOIN '.DB_PREFIX.'_region r USING(region_id) WHERE city_id=?';
    	$stmt=$DBC->query($query, array($c1));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    	}
    	
    	$query='UPDATE '.DB_PREFIX.'_data SET country_id=?, region_id=?, city_id=? WHERE city_id=?';
    	$stmt=$DBC->query($query, array($ar['country_id'], $ar['region_id'], $c1, $c2));
    	
    	$query='DELETE '.DB_PREFIX.'_city WHERE city_id=?';
    	$stmt=$DBC->query($query, array($c2));
    }
    
    function joinRegions($c1, $c2){
    	$DBC=DBC::getInstance();
    	$query='SELECT country_id FROM '.DB_PREFIX.'_region WHERE region_id=?';
    	$stmt=$DBC->query($query, array($c1));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    	}
    	 
    	$query='UPDATE '.DB_PREFIX.'_data SET country_id=?, region_id=? WHERE region_id=?';
    	$stmt=$DBC->query($query, array($ar['country_id'], $c1, $c2));
    	
    	
    	$query='UPDATE '.DB_PREFIX.'_city SET region_id=? WHERE region_id=?';
    	$stmt=$DBC->query($query, array($c1, $c2));
    	
    	$query='DELETE '.DB_PREFIX.'_region WHERE region_id=?';
    	$stmt=$DBC->query($query, array($c2));
    }
    
    function removeRegion(){
    	
    }
    
    
    function getTopMenu () {
    	return '';
    }
    
    public function ajax(){
    	switch($this->getRequestValue('action')){
    		case 'load' : {
    			$what=$this->getRequestValue('what');
    			$DBC=DBC::getInstance();
    			if($what=='country'){
    				$query='SELECT country_id, name FROM '.DB_PREFIX.'_country ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}elseif($what=='region'){
    				$country_id=(int)$this->getRequestValue('country_id');
    				$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($country_id));
    			}elseif($what=='city'){
    				$region_id=(int)$this->getRequestValue('region_id');
    				$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($region_id));
    			}
	    		
		    	
		    	if($stmt){
		    		while($ar=$DBC->fetch($stmt)){
		    			$gdata[]=$ar;
		    		}
		    	}
    			return json_encode($gdata);
    			break;
    		}
    		case 'load_html' : {
    			$what=$this->getRequestValue('what');
    			$DBC=DBC::getInstance();
    			if($what=='country'){
    				$query='SELECT country_id, name FROM '.DB_PREFIX.'_country ORDER BY name ASC';
    				$stmt=$DBC->query($query);
    			}elseif($what=='region'){
    				$country_id=(int)$this->getRequestValue('country_id');
    				$query='SELECT region_id, name FROM '.DB_PREFIX.'_region WHERE country_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($country_id));
    			}elseif($what=='city'){
    				$region_id=(int)$this->getRequestValue('region_id');
    				$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE region_id=? ORDER BY name ASC';
    				$stmt=$DBC->query($query, array($region_id));
    			}
    			 
    			 
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$gdata[]=$ar;
    				}
    			}
    			if($what=='country'){
	    			foreach($gdata as $gd){
	    				$ret.=$this->GEO_getCountryLine($gd);
	    			}
    			}elseif($what=='region'){
    				foreach($gdata as $gd){
	    				$ret.=$this->GEO_getRegionLine($gd);
	    			}
    			}elseif($what=='city'){
    				foreach($gdata as $gd){
	    				$ret.=$this->GEO_getCityLine($gd);
	    			}
    			}
    			
    			
    			return $ret;
    			break;
    		}
    		 
    	}
    }
    
    protected function GEO_getCityLine($gd){
    	$ret.='<li class="dd-item" data-id="1" data-type="city">';
    	$ret.='<div class="dd-handle">';
    	$ret.='['.$gd['city_id'].'] '.$gd['name'];
    	$ret.='<div class="pull-right action-buttons">';
    	$ret.='<a class="red structure_control_clear_function reload" href="#">Влить в город</i></a>';
    	$ret.='<a class="red structure_control_clear_function reload" href="#">Сменить регион</i></a>';
    	$ret.='<a class="red structure_control_clear_function reload" href="#"><i class="icon-repeat bigger-130"></i></a>';
    	$ret.='<a class="blue structure_control_edit_function" href="/admin/?action=country&do=edit&amp;country_id='.$gd['city_id'].'"><i class="icon-pencil bigger-130"></i></a>';
    	$ret.='<a class="green structure_control_new_function" href="/admin/index.php?action=structure&do=new&parent_id='.$gd['city_id'].'"><i class="icon-plus bigger-130"></i></a>';
    	$ret.='</div>';
    	$ret.='</div>';
    	$ret.='</li>';
    	return $ret;
    }
    
    protected function GEO_getRegionLine($gd){
    	$ret.='<li class="dd-item" data-id="1" data-type="region">';
    	$ret.='<div class="dd-handle">';
    	$ret.='['.$gd['region_id'].'] '.$gd['name'];
    	$ret.='<div class="pull-right action-buttons">';
    	$ret.='<a class="red structure_control_clear_function reload" href="#">Влить в регион</i></a>';
    	$ret.='<a data-id="'.$gd['region_id'].'" class="red geo_control" href="#">Сменить страну</i></a>';
    	$ret.='<a class="red structure_control_clear_function reload" href="#"><i class="icon-repeat bigger-130"></i></a>';
    	$ret.='<a class="blue structure_control_edit_function" href="/admin/?action=country&do=edit&amp;country_id='.$gd['region_id'].'"><i class="icon-pencil bigger-130"></i></a>';
    	$ret.='<a class="green structure_control_new_function" href="/admin/index.php?action=structure&do=new&parent_id='.$gd['region_id'].'"><i class="icon-plus bigger-130"></i></a>';
    	$ret.='</div>';
    	$ret.='</div>';
    	$ret.='</li>';
    	return $ret;
    }
    
    protected function GEO_getCountryLine($gd){
    	$ret.='<li class="dd-item" data-id="1" data-type="country">';
    	$ret.='<div class="dd-handle">';
    	$ret.='['.$gd['country_id'].'] '.$gd['name'];
    	$ret.='<div class="pull-right action-buttons">';
    	$ret.='<a class="red structure_control_clear_function reload" href="#"><i class="icon-repeat bigger-130">Слить с городом</i></a>';
    	$ret.='<a class="red structure_control_clear_function reload" href="#"><i class="icon-repeat bigger-130"></i></a>';
    	$ret.='<a class="blue structure_control_edit_function" href="/admin/?action=country&do=edit&amp;country_id='.$gd['country_id'].'"><i class="icon-pencil bigger-130"></i></a>';
    	$ret.='<a class="green structure_control_new_function" href="/admin/index.php?action=structure&do=new&parent_id='.$gd['country_id'].'"><i class="icon-plus bigger-130"></i></a>';
    	$ret.='</div>';
    	$ret.='</div>';
    	$ret.='</li>';
    	return $ret;
    }
    
    
	protected function _defaultAction () {
		$ret='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdopt">Подгонка превьюшек (Uploadify)</a> ';
		$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdoptUploads">Подгонка превьюшек (Uploads)</a> ';
		$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=fromUploadify">Из Uploadify в Uploads (для объявлений)</a> ';
		$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=fromUploadifyNews">Из Uploadify в Uploads (для новостей)</a> ';
		if(defined('DEVMODE') && DEVMODE==1){
			$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=userHeap">Do User Heap</a> ';
			$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=workGeography">workGeography</a> ';
			$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=imageRewriter">imageRewriter</a> ';
		}
		$ret.='<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=dataAliasStat">Статистика алиасов объявлений</a> ';
    	return $ret;
    }
    
    protected function _previewAdoptAction () {
    	$this->previewAdopt();
    	return $this->_defaultAction();
    }
    
    protected function _dataAliasStatAction () {
    	$DBC=DBC::getInstance();
    	if($_GET['subdo']=='edit' && $_GET['alias']!=''){
    		$repair=array();
    		$alias=trim($_GET['alias']);
    		
    		$query='SELECT id FROM `'.DB_PREFIX.'_data` WHERE `translit_alias`=?';
    		$stmt=$DBC->query($query, array($alias));
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$repair[]=$ar['id'];
    			}
    		}
    		
    		if(!empty($repair)){
    			$query='UPDATE `'.DB_PREFIX.'_data` SET `translit_alias`=\'\' WHERE `id` IN ('.implode(',', $repair).')';
    			$stmt=$DBC->query($query);
    			foreach ($repair as $r){
    				$this->saveTranslitAlias($r);
    			}
    		}
    	}
    	
    	$query='SELECT COUNT( `id` ) AS _cnt, `translit_alias` FROM  `'.DB_PREFIX.'_data` GROUP BY translit_alias ORDER BY _cnt DESC';
    	$stmt=$DBC->query($query);
    	//$errors=array();
    	$hasDup=false;
    	$hasEmpty=0;
    	if($stmt){
    		$ret='<table>';
    		while($ar=$DBC->fetch($stmt)){
    			if($ar['translit_alias']==''){
    				$hasEmpty=$ar['_cnt'];
    			}
    			if(!$hasDup && $ar['_cnt']>1 && $ar['translit_alias']!=''){
    				$hasDup=true;
    			}
    			if($ar['_cnt']>1 && $ar['translit_alias']!=''){
    				$ret.='<tr><td>'.$ar['translit_alias'].'</td><td>'.$ar['_cnt'].(($ar['_cnt']>1 && $ar['translit_alias']!='') ? '<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=toolbox&do=dataAliasStat&subdo=edit&alias='.$ar['translit_alias'].'">Исправить</a>' : '').'</td></tr>';
    			}
    		}
    		$ret.='<table>';
    	}
    	if($hasDup || $hasEmpty>0){
    		if($hasDup){
    			$ret='<div class="alert alert-error">Наличествуют дубли</div>'.$ret;
    		}
    		if($hasEmpty>0){
    			$ret='<div class="alert alert-error">Наличествуют пустые алиасы ('.$hasEmpty.')</div>'.$ret;
    		}
    	}else{
    		$ret='<div class="alert alert-success">Ошибок не найдено</div>'.$ret;
    	}
    	
    	//$this->previewAdoptUploads();
    	return $this->_defaultAction().$ret;
    }
    
    protected function _previewAdoptUploadsAction () {
    	$this->previewAdoptUploads();
    	return $this->_defaultAction();
    }
    
    protected function _imageRewriterAction () {
    	$this->imageRewriter();
    	return $this->_defaultAction();
    }
    
    private function imageRewriter(){
    	$start=(int)$this->getRequestValue('start');
    	$step=10;
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$data_model = $data_model->get_kvartira_model(false, true);
    	$data_model=$data_model['data'];
    	$uploads_fields=array();
    	foreach ($data_model as $dt){
    		if($dt['type']=='uploads'){
    			$uploads_fields[]=$dt['name'];
    		}
    	}
    	 
    	if(count($uploads_fields)==0){
    		return;
    	}
    	 
    	foreach($uploads_fields as $uf){
    		$where[]='`'.$uf.'`<>\'\'';
    	}
    	 
    	$DBC=DBC::getInstance();
    	$query='SELECT id, `'.implode('`,`', $uploads_fields).'` FROM '.DB_PREFIX.'_data WHERE ('.implode(') OR (', $where).') ORDER BY id LIMIT '.$start.', '.$step;
    	 
    	$stmt=$DBC->query($query);
    	$realty=array();
    	 
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$realty[]=$ar;
    		}
    	}
    	
    	$images_for_rewrite=array();
    	
    	if(!empty($realty)){
    		foreach($realty as $r){
    			foreach($uploads_fields as $uf){
    				$images=array();
    				if(''!=$r[$uf]){
    					$images=unserialize($r[$uf]);
    					if(!empty($images)){
    						foreach ($images as $img){
    							$images_for_rewrite[]=$img['normal'];
    						}
    					}
    				}
    			}
    		}
    	}else{
    		return;
    	}
    	
    	//print_r($images_for_rewrite);
    	
    	if(!empty($images_for_rewrite)){
    		foreach($images_for_rewrite as $r){
    			
    			$parts=explode('.', $r);
    			$ext=end($parts);
    			$src=SITEBILL_DOCUMENT_ROOT.'/img/data/'.$r;
    			if ($ext=='jpg' || $ext=='jpeg'){
    				$source_img=@ImageCreateFromJPEG($src);
    			} elseif ($ext=='png') {
    				$source_img=@ImageCreateFromPNG($src);
    			} elseif ($ext=='gif') {
    				$source_img=@ImageCreateFromGIF($src);
    			}
    			
    			if($source_img===false){
    				//echo $src.' failed in '.$ext.'<br>';
    				$source_img=@ImageCreateFromJPEG($src);
    			}
    			
    			if($source_img===false){
    				continue;
    			}
    			/*
    			$tmp_img=imageCreateTrueColor($dest_width, $dest_height);
    			imageAlphaBlending($tmp_img, false);
    			imageSaveAlpha($tmp_img, true);
    			*/
    			if ($ext=='jpg' || $ext=='jpeg'){
    				imagejpeg($source_img, $src, (int)$this->getConfigValue('jpeg_quality'));
    			}elseif($ext=='png'){
    				imagepng($source_img, $src, (int)$this->getConfigValue('png_quality'));
    			}elseif($ext=='gif'){
    				imagegif($source_img, $src);
    			}
    			ImageDestroy($source_img);
    		}
    	}
    	 
    	
    	 
    	header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=imageRewriter&start='.($start+$step));
    	exit();
    	 
    	 
    	
    }
    
    protected function _userHeapAction () {
    	$DBC=DBC::getInstance();
    	$query='SELECT u.*, g.system_name FROM re_user u LEFT JOIN re_group g USING(group_id)';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$users[$ar['user_id']]=$ar;
    		}
    	}
    	//print_r($users);
    	//$_str='$userHeap=array(';
    	foreach ($users as $user){
    		foreach($user as $k=>$v){
    			$els[]='\''.$k.'\'=>\''.$v.'\'';
    		}
    		$_str[]='\''.$user['user_id'].'\'=>array('.implode(',', $els).')';
    	}
    	$str='<?php $userHeap=array('.implode(',', $_str).');';
    	$f=fopen(SITEBILL_DOCUMENT_ROOT.'/user_heap.php', 'w');
    	fwrite($f, $str);
    	fclose($f);
    	//var_dump($str);
    	return $this->_defaultAction();
    }
    
    protected function _workGeographyAction () {
    	$DBC=DBC::getInstance();
    	global $smarty;
    	
    	$gdata=array();
    	
    	
    	$query='SELECT country_id, name FROM '.DB_PREFIX.'_country ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$gdata['country'][$ar['country_id']]=$ar;
    		}
    	}
    	
    	$query='SELECT region_id, name, country_id FROM '.DB_PREFIX.'_region ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$gdata['region'][$ar['region_id']]=$ar;
    			$gdata['region_to_country'][$ar['country_id']][$ar['region_id']]=$ar['region_id'];
    		}
    	}
    	
    	$query='SELECT city_id, name, region_id FROM '.DB_PREFIX.'_city ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$gdata['city'][$ar['city_id']]=$ar;
    			$gdata['city_to_region'][$ar['region_id']][$ar['city_id']]=$ar['city_id'];
    		}
    	}
    	
    	$smarty->assign('gdata', $gdata);
    	
    	$enities=array();
    	
    	
    	$query='SELECT d.id, d.name, d.city_id, c.city_id, c.name AS city_name, r.region_id, r.name AS region_name, r.country_id, cn.name AS country_name 
    			FROM '.DB_PREFIX.'_district d 
    			LEFT JOIN '.DB_PREFIX.'_city c USING(city_id) 
    			LEFT JOIN '.DB_PREFIX.'_region r USING(region_id) 
    			LEFT JOIN '.DB_PREFIX.'_country cn USING(country_id) 
    			ORDER BY d.name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$geography['district'][]=$ar;
    		}
    	}
    	
    	$query='SELECT id, name, city_id FROM '.DB_PREFIX.'_district ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$district[$ar['id']]=$ar;
    			$ar['_type']='district';
    			$ar['_id']=$ar['id'];
    			$districts[$ar['city_id']][$ar['id']]=$ar;
    		}
    	}
    	
    	$query='SELECT city_id, name, url AS href, region_id FROM '.DB_PREFIX.'_city ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$city[$ar['city_id']]=$ar;
    			$ar['_type']='city';
    			$ar['_id']=$ar['city_id'];
    			if(isset($districts[$ar['city_id']])){
    				$ar['_childs']=$districts[$ar['city_id']];
    			}
    			$cities[$ar['region_id']][$ar['city_id']]=$ar;
    		}
    	}
    	
    	$query='SELECT region_id, name, alias AS href, country_id FROM '.DB_PREFIX.'_region ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$region[$ar['region_id']]=$ar;
    			$ar['_type']='region';
    			$ar['_id']=$ar['region_id'];
    			if(isset($cities[$ar['region_id']])){
    				$ar['_childs']=$cities[$ar['region_id']];
    			}
    			$regions[$ar['country_id']][$ar['region_id']]=$ar;
    		}
    	}
    	
    	
    	
    	
    	$addr_str[]=$last_str;
    	 
    	$data[]=implode(', ', $addr_str);
    	
    	$query='SELECT country_id, name, url AS href FROM '.DB_PREFIX.'_country ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$country[$ar['country_id']]=$ar;
    			$ar['_type']='country';
    			$ar['_id']=$ar['country_id'];
    			if(isset($regions[$ar['country_id']])){
    				$ar['_childs']=$regions[$ar['country_id']];
    			}
    			$enities[$ar['country_id']]=$ar;
    		}
    	}
    	
    	/*
    	
    	$query='SELECT region_id, name, alias AS href, country_id FROM '.DB_PREFIX.'_region ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ar['_type']='region';
    			$ar['_id']='region_id';
    			$enities[$ar['country_id']]['_childs'][$ar['region_id']]=$ar;
    		}
    	}
    	$query='SELECT city_id, name, url AS href, region_id FROM '.DB_PREFIX.'_city ORDER BY name ASC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$enities['city'][$ar['city_id']]=$ar;
    		}
    	}*/
    	
    	
    	$smarty->assign('incfile', SITEBILL_DOCUMENT_ROOT.'/apps/toolbox/admin/template/work_geography_inc_action.tpl');
    	$smarty->assign('enities', $enities);
    	$smarty->assign('geography', $geography);
    	$smarty->assign('geography_js', json_encode($geography['district']));
    	return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/toolbox/admin/template/work_geography_action.tpl');
    	return '';
    }
    
    
    private function previewAdoptUploads(){
    	$start=(int)$this->getRequestValue('start');
    	$step=10;
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$data_model = $data_model->get_kvartira_model(false, true);
    	$data_model=$data_model['data'];
    	$uploads_fields=array();
    	foreach ($data_model as $dt){
    		if($dt['type']=='uploads'){
    			$uploads_fields[]=$dt['name'];
    		}
    	}
    	
    	if(count($uploads_fields)==0){
    		return;
    	}
    	
    	foreach($uploads_fields as $uf){
    		$where[]='`'.$uf.'`<>\'\'';
    	}
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT id, `'.implode('`,`', $uploads_fields).'` FROM '.DB_PREFIX.'_data WHERE ('.implode(') OR (', $where).') ORDER BY id LIMIT '.$start.', '.$step;
    	
    	$stmt=$DBC->query($query);
    	$realty=array();
    	
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$realty[]=$ar;
    		}
    	}
    	
    	if(!empty($realty)){
    		$params=array();
    		foreach($uploads_fields as $uf){
    			$params[$uf]['pw']=$this->getConfigValue('apps.realty.data_image_preview_width');
    			$params[$uf]['ph']=$this->getConfigValue('apps.realty.data_image_preview_width');
    			
    			if(isset($data_model[$uf]['parameters']['prev_width']) && (int)$data_model[$uf]['parameters']['prev_width']!=0){
    				$params[$uf]['pw']=(int)$data_model[$uf]['parameters']['prev_width'];
    			}
    			if(isset($data_model[$uf]['parameters']['prev_height']) && (int)$data_model[$uf]['parameters']['prev_height']!=0){
    				$params[$uf]['ph']=(int)$data_model[$uf]['parameters']['prev_height'];
    			}
    			
    		}
    	}else{
    		return;
    	}
    	
    	foreach($realty as $r){
    		foreach($uploads_fields as $uf){
    			$images=array();
    			if(''!=$r[$uf]){
    				$images=unserialize($r[$uf]);
    				if(!empty($images)){
    					foreach ($images as $img){
    						$prev=$img['preview'];
    						$normal=$img['normal'];
    						$must_resize=false;
    						if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal)){
    							if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev)){
    								$sizes=getimagesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev);
    								if($params[$uf]['pw']!=$sizes[0] || $params[$uf]['ph']!=$sizes[1]){
    									$must_resize=true;
    								}
    							}else{
    								$must_resize=true;
    							}
    						
    							if($must_resize){
    								$arr=explode('.', $prev);
    								$ext=strtolower($arr[count($arr)-1]);
    								$this->makePreview(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev, $params[$uf]['pw'], $params[$uf]['ph'], $ext, 'smart');
    							}
    						}
    					}
    				}
    			}
    		}
    	}
    	
    	header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdoptUploads&start='.($start+$step));
    	exit();
    	
    	
    	print_r($params);
    	//echo $query;
    	print_r($uploads_fields);
    	exit();
    
    	 
    	$pw=$this->getConfigValue('apps.realty.data_image_preview_width');
    	$ph=$this->getConfigValue('apps.realty.data_image_preview_height');
    	 
    	$DBC=DBC::getInstance();
    	$query='SELECT i.* FROM re_data_image di LEFT JOIN re_image i USING(image_id) ORDER BY di.data_image_id LIMIT '.$start.', '.$step;
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ret[]=$ar;
    		}
    		foreach ($ret as $img){
    			$prev=$img['preview'];
    			$normal=$img['normal'];
    			$must_resize=false;
    			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal)){
    				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev)){
    					$sizes=getimagesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev);
    					if($pw!=$sizes[0] || $ph!=$sizes[1]){
    						$must_resize=true;
    					}
    				}else{
    					$must_resize=true;
    				}
    
    				if($must_resize){
    					$arr=explode('.', $prev);
    					$ext=strtolower($arr[count($arr)-1]);
    					$this->makePreview(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev, $pw, $ph, $ext, 'smart');
    				}
    			}
    		}
    		header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdopt&start='.($start+$step));
    		exit();
    	}
    	header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action);
    	exit();
    }
    
    private function previewAdopt(){
    	$start=(int)$this->getRequestValue('start');
    	$step=100;
    	

    	
    	$pw=$this->getConfigValue('apps.realty.data_image_preview_width');
    	$ph=$this->getConfigValue('apps.realty.data_image_preview_height');
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT i.* FROM re_data_image di LEFT JOIN re_image i USING(image_id) ORDER BY di.data_image_id LIMIT '.$start.', '.$step;
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ret[]=$ar;
    		}
    		foreach ($ret as $img){
    			$prev=$img['preview'];
    			$normal=$img['normal'];
    			$must_resize=false;
    			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal)){
    				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev)){
    					$sizes=getimagesize(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev);
    					if($pw!=$sizes[0] || $ph!=$sizes[1]){
    						$must_resize=true;
    					}
    				}else{
    					$must_resize=true;
    				}
    				
    				if($must_resize){
    					$arr=explode('.', $prev);
    					$ext=strtolower($arr[count($arr)-1]);
    					$this->makePreview(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$prev, $pw, $ph, $ext, 'smart');
    				}
    			}
    		}
    		header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=previewAdopt&start='.($start+$step));
    		exit();
    	}
    	header('location: '.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action);
    	exit();
    }
    
    function _fromUploadifyNewsAction () {
    	$rs = '';
    	if(isset($_POST['uploads_field']) && trim($_POST['uploads_field'])!=''){
    		$rs .= 'Запуск миграции картинок для поля '.$_POST['uploads_field'].'<br>';
    		$field=trim($_POST['uploads_field']);
    		$ret=array();
    		$DBC=DBC::getInstance();
    		
    		$pdata=array();
    		$query='SELECT news_id, `'.$field.'` FROM '.DB_PREFIX.'_news WHERE `'.$field.'`<>\'\'';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$x=unserialize($ar[$field]);
    				if(!is_array($x) || empty($x)){
    					$pdata[]=$ar['news_id'];
    				}
    			}
    		}
    		
    		if(!empty($pdata)){
    			$rs .= 'Загружен массив записей с картинками uploads <pre>'.var_export($pdata, true).'</pre><br>';
    			 
    			$query='UPDATE '.DB_PREFIX.'_news SET `'.$field.'`=\'\' WHERE news_id=?';
    			foreach($pdata as $id){
    				$stmt=$DBC->query($query, array($id));
    			}
    		} else {
    			$rs .= 'Массив записей с картинками uploads пуст!<br>';
    		}
    		
    		$query='SELECT di.news_id, i.* FROM '.DB_PREFIX.'_news_image di LEFT JOIN '.DB_PREFIX.'_image i USING(image_id) ORDER BY di.sort_order';
		//echo $query.'<br>';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$ret[]=$ar;
    			}
    		}
    		if(!empty($ret)){
    			$rs .= 'В базе имеется массив картинок от uploadify <pre>'.var_export($ret, true).'</pre>';
    			 
    			foreach($ret as $rt){
    				//$_rt=$rt;
    				if(isset($rt['normal'])){
    					$_rt['normal']=$rt['normal'];
    				}else{
    					$_rt['normal']='';
    				}
    				if(isset($rt['preview'])){
    					$_rt['preview']=$rt['preview'];
    				}else{
    					$_rt['preview']='';
    				}
    				if(isset($rt['title'])){
    					$_rt['title']=$rt['title'];
    				}else{
    					$_rt['title']='';
    				}
    				if(isset($rt['description'])){
    					$_rt['description']=$rt['description'];
    				}else{
    					$_rt['description']='';
    				}
    				//unset($_rt['id']);
    				$images[$rt['news_id']][]=$_rt;
    			}
    		}
    		if(!empty($images)){
    			foreach($images as $id=>$idimages){
    				foreach($idimages as $k=>$im){
    					$images[$id][$k]['mime']=strtolower(end(explode('.', $im['normal'])));
    					$images[$id][$k]['type']='graphic';
    				}
    			}
    			$query='UPDATE '.DB_PREFIX.'_news SET `'.$field.'`=? WHERE `'.$field.'`=\'\' AND news_id=?';
    			foreach($images as $id=>$idimages){
    				$stmt=$DBC->query($query, array(serialize($idimages), $id));
    			}
    		}
    		
    		return $rs .= $this->_defaultAction();
    	}else{
    		return '<form action="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=fromUploadifyNews" method="post">Системное имя поля Uploads для картинок <input type="text" name="uploads_field"> <input type="submit" name="submit" value="Пуск" class="btn"></form>';
    	}
    	
    }
    
    
    function _fromUploadifyAction () {
    	$rs = '';
    	if(isset($_POST['uploads_field']) && trim($_POST['uploads_field'])!=''){
    		$rs .= 'Запуск миграции картинок для поля '.$_POST['uploads_field'].'<br>';
    		$field=trim($_POST['uploads_field']);
    		$ret=array();
    		$DBC=DBC::getInstance();
    		
    		$pdata=array();
    		$query='SELECT id, `'.$field.'` FROM '.DB_PREFIX.'_data WHERE `'.$field.'`<>\'\'';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$x=unserialize($ar[$field]);
    				if(!is_array($x) || empty($x)){
    					$pdata[]=$ar['id'];
    				}
    			}
    		}
    		
    		if(!empty($pdata)){
    			$rs .= 'Загружен массив записей с картинками uploads <pre>'.var_export($pdata, true).'</pre><br>';
    			 
    			$query='UPDATE '.DB_PREFIX.'_data SET `'.$field.'`=\'\' WHERE id=?';
    			foreach($pdata as $id){
    				$stmt=$DBC->query($query, array($id));
    			}
    		} else {
    			$rs .= 'Массив записей с картинками uploads пуст!<br>';
    		}
    		
    		$query='SELECT di.id, i.* FROM '.DB_PREFIX.'_data_image di LEFT JOIN '.DB_PREFIX.'_image i USING(image_id) ORDER BY di.sort_order';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$ret[]=$ar;
    			}
    		}
    		if(!empty($ret)){
    			$rs .= 'В базе имеется массив картинок от uploadify <pre>'.var_export($ret, true).'</pre>';
    			 
    			foreach($ret as $rt){
    				//$_rt=$rt;
    				if(isset($rt['normal'])){
    					$_rt['normal']=$rt['normal'];
    				}else{
    					$_rt['normal']='';
    				}
    				if(isset($rt['preview'])){
    					$_rt['preview']=$rt['preview'];
    				}else{
    					$_rt['preview']='';
    				}
    				if(isset($rt['title'])){
    					$_rt['title']=$rt['title'];
    				}else{
    					$_rt['title']='';
    				}
    				if(isset($rt['description'])){
    					$_rt['description']=$rt['description'];
    				}else{
    					$_rt['description']='';
    				}
    				//unset($_rt['id']);
    				$images[$rt['id']][]=$_rt;
    			}
    		}
    		if(!empty($images)){
    			foreach($images as $id=>$idimages){
    				foreach($idimages as $k=>$im){
    					$images[$id][$k]['mime']=strtolower(end(explode('.', $im['normal'])));
    					$images[$id][$k]['type']='graphic';
    				}
    			}
    			$query='UPDATE '.DB_PREFIX.'_data SET `'.$field.'`=? WHERE `'.$field.'`=\'\' AND id=?';
    			foreach($images as $id=>$idimages){
    				$stmt=$DBC->query($query, array(serialize($idimages), $id));
    			}
    		}
    		
    		return $rs .= $this->_defaultAction();
    	}else{
    		return '<form action="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=fromUploadify" method="post">Системное имя поля Uploads для картинок <input type="text" name="uploads_field"> <input type="submit" name="submit" value="Пуск" class="btn"></form>';
    	}
    	
    }
}