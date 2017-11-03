<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * complex fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class customentity_site extends customentity_admin {
	
	function catchEntityList($REQUESTURIPATH){
		$cent=false;
		if(preg_match('/^([a-z0-9_-]+)$/', $REQUESTURIPATH, $matches)){
			$DBC=DBC::getInstance();
			$query='SELECT * FROM '.DB_PREFIX.'_customentity WHERE `alias`=? AND `is_public`=? LIMIT 1';
			$stmt=$DBC->query($query, array($matches[1], 1));
			if($stmt){
				$cent=$DBC->fetch($stmt);
			}
		}
		return $cent;
	}
	
	function showList($cent){
		$page=intval($this->getRequestValue('page'));
		if($page==0){
			$page=1;
		}
		$per_page=intval($cent['per_page']);
		if($per_page==0){
			$per_page=10;
		}
		$data=$this->getEntityListData($cent, $page);
		$this->template->assign('entity_list', $data['data']);
		$this->template->assign('entity_pager', $this->get_page_links_list($page, $data['total'], $per_page, array('page_url'=>$cent['alias'])));
		if($cent['list_tpl']!=''){
			$this->set_apps_template('customentity', $this->getConfigValue('theme'), 'main_file_tpl', $cent['list_tpl']);
		}else{
			$this->set_apps_template('customentity', $this->getConfigValue('theme'), 'main_file_tpl', 'list.tpl');
		}
		
		$title=$this->getEntityListTitle($cent);
		if($title!=''){
			$this->template->assign('breadcrumbs', $this->getEntityListBreadcrumbs($cent, $title));
		}
	}
	
	function catchEntityItem($REQUESTURIPATH){
		$cent=false;
		if(preg_match('/^([a-z0-9_-]+)\/(\d+)$/', $REQUESTURIPATH, $matches)){
			$DBC=DBC::getInstance();
			$query='SELECT * FROM '.DB_PREFIX.'_customentity WHERE `alias`=? AND `is_public`=? LIMIT 1';
			$stmt=$DBC->query($query, array($matches[1], 1));
			if($stmt){
				$cent=$DBC->fetch($stmt);
				return array('entity'=>$cent, 'item_id'=>$matches[2]);
			}
		}
		return false;
	}
	
	function getEntityListBreadcrumbs($cent, $title){
		$breadcrumbs_str='';
		$breadcrumbs=array();
		$breadcrumbs[]=array('title'=>Multilanguage::_('L_HOME'), 'href'=>SITEBILL_MAIN_URL.'/');
		$breadcrumbs[]=array('title'=>$title, 'href'=>'');
		if(!empty($breadcrumbs)){
			$bc_ar=array();
			foreach($breadcrumbs as $bc){
				if($bc['href']!=''){
					$bc_ar[]='<a href="'.$bc['href'].'">'.$bc['title'].'</a>';
				}else{
					$bc_ar[]=$bc['title'];
				}
			}
		}
		$breadcrumbs_str=implode(' / ', $bc_ar);
		return $breadcrumbs_str;
	}
	
	function getEntityItemTitle($cent, $data){
		$title=trim($cent['view_title']);
		if($title==''){
			if(isset($data['name'])){
				$title=trim($data['name']['value']);
			}
		}else{
			if(false!==preg_match_all('/\{\$([^}]+)\}/', $title, $matches)){
				if(count($matches[0])>0){
					$replace_what=array();
					$replace_by=array();
					foreach($matches[1] as $t){
						$replace_what[]='{$'.$t.'}';
						if(Multilanguage::is_set($t)){
							$replace_by[]=Multilanguage::_($t);
						}else{
							$replace_by[]='';
						}
					}
					$title=str_replace($replace_what, $replace_by, $title);
				}
			}
			if(false!==preg_match_all('/\{([^}]+)\}/', $title, $matches)){
				if(count($matches[0])>0){
					$replace_what=array();
					$replace_by=array();
					foreach($matches[1] as $t){
						$replace_what[]='{'.$t.'}';
						if(isset($data[$t])){
							if(in_array($data[$t]['type'], array('select_box', 'select_by_query', 'select_by_query_multi'))){
								$replace_by[]=$data[$t]['value_string'];
							}else{
								$replace_by[]=$data[$t]['value'];
							}
						}else{
							$replace_by[]='';
						}
					}
					$title=str_replace($replace_what, $replace_by, $title);
				}
			}
		}
		return $title;
	}
	
	function getEntityListTitle($cent){
		$title=trim($cent['list_title']);
		if(false!==preg_match_all('/\{\$([^}]+)\}/', $title, $matches)){
			if(count($matches[0])>0){
				$replace_what=array();
				$replace_by=array();
				foreach($matches[1] as $t){
					$replace_what[]='{$'.$t.'}';
					if(Multilanguage::is_set($t)){
						$replace_by[]=Multilanguage::_($t);
					}else{
						$replace_by[]='';
					}
				}
				$title=str_replace($replace_what, $replace_by, $title);
			}
		}
		return $title;
	}
	
	function showItem($cent, $item_id){
		
		$data=$this->getEntityItemData($cent, $item_id);
		
		if(empty($data)){
			return false;
		}
		$this->template->assign('entity_item', $data);
		$this->template->assign('entity_info', $cent);
		if($cent['view_tpl']!=''){
			$this->set_apps_template('customentity', $this->getConfigValue('theme'), 'main_file_tpl', $cent['view_tpl']);
		}else{
			$this->set_apps_template('customentity', $this->getConfigValue('theme'), 'main_file_tpl', 'view.tpl');
		}
		
		$title=$this->getEntityItemTitle($cent, $data);
		if($title!=''){
			$this->template->assert('title', $title);
		}
		$ctitle=$this->getEntityListTitle($cent);
		$this->template->assign('breadcrumbs', $this->getEntityItemBreadcrumbs($cent, $ctitle, $title));
		return true;
	}
	
	function getEntityItemBreadcrumbs($cent, $ctitle, $title){
		$breadcrumbs_str='';
		if($title!='' || $ctitle!=''){
			$breadcrumbs=array();
			$breadcrumbs[]=array('title'=>Multilanguage::_('L_HOME'), 'href'=>SITEBILL_MAIN_URL.'/');
			if($ctitle!=''){
				$breadcrumbs[]=array('title'=>$ctitle, 'href'=>SITEBILL_MAIN_URL.'/'.$cent['alias'].self::$_trslashes);
			}
			if($title!=''){
				$breadcrumbs[]=array('title'=>$title, 'href'=>'');
			}
				
			
			if(!empty($breadcrumbs)){
				$bc_ar=array();
				foreach($breadcrumbs as $bc){
					if($bc['href']!=''){
						$bc_ar[]='<a href="'.$bc['href'].'">'.$bc['title'].'</a>';
					}else{
						$bc_ar[]=$bc['title'];
					}
				}
			}
			$this->template->assign('breadcrumbs', implode(' / ', $bc_ar));
			$breadcrumbs_str=implode(' / ', $bc_ar);
		}
		
		
		return $breadcrumbs_str;
	}
	

	function frontend () {
		$REQUESTURIPATH=$this->getClearRequestURI();
		if(false!==$cent=$this->catchEntityList($REQUESTURIPATH)){
			$this->showList($cent);
			return true;
		}
		
		if(false!==$cent=$this->catchEntityItem($REQUESTURIPATH)){
			if($this->showItem($cent['entity'], $cent['item_id'])){
				return true;
			}
		}
		
		
		return false;
	}
	
	public function getEntityListData($entity, $page=1){
		
		$ids=array();
		$total=0;
		$primary_key_name='';
		$table_name = $entity['entity_name'];
		$per_page=intval($entity['per_page']);
		if($per_page==0){
			$per_page=10;
		}
		$start=($page-1)*$per_page;
		//var_dump($table_name);
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
		$ATH=new Admin_Table_Helper();
		$form_data=$ATH->load_model($table_name, false);
		$form_data=$form_data[$table_name];
		//var_dump($form_data);
		if(!$form_data){
			return array();
		}
		
		foreach ($form_data as $fdi){
			if($fdi['type']=='primary_key'){
				$primary_key_name=$fdi['name'];
				break;
			}
		}
		
		$order=$entity['sortby'];
		if(!isset($form_data[$order])){
			$order=$primary_key_name;
		}
		
		$orderdir=strtolower($entity['sortorder']);
		if($orderdir!='asc' && $orderdir!='desc'){
			$orderdir='desc';
		}
		
		$DBC=DBC::getInstance();
		$query='SELECT SQL_CALC_FOUND_ROWS `'.$primary_key_name.'` FROM '.DB_PREFIX.'_'.$table_name.' ORDER BY `'.$order.'` '.$orderdir.' LIMIT '.$start.','.$per_page;
		
		$stmt=$DBC->query($query);
		if(!$stmt){
			return array();
		}
		
		while ($ar=$DBC->fetch($stmt)){
			$ids[]=$ar[$primary_key_name];
		}
		
		$query='SELECT FOUND_ROWS() AS _cnt';
		$stmt=$DBC->query($query);
		$ar=$DBC->fetch($stmt);
		$total=$ar['_cnt'];
		
		if(empty($ids)){
			return array();
		}
		
		//print_r($ids);
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$data=$data_model->init_model_data_from_db_multi($table_name, $primary_key_name, $ids, $form_data, true, true, true);
		
		foreach ($data as $k=>$v){
			$data[$k]['_href']=SITEBILL_MAIN_URL.'/'.$entity['alias'].'/'.$data[$k][$primary_key_name]['value'].self::$_trslashes;
		}
		
		return array('data'=>$data, 'total'=>$total);
	}
	
	protected function getEntityItemData($entity, $id){
		
		$primary_key_name='';
		$table_name = $entity['entity_name'];
		
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
		$ATH=new Admin_Table_Helper();
		$form_data=$ATH->load_model($table_name, false);
		$form_data=$form_data[$table_name];
		//var_dump($form_data);
		if(!$form_data){
			return array();
		}
		
		foreach ($form_data as $fdi){
			if($fdi['type']=='primary_key'){
				$primary_key_name=$fdi['name'];
				break;
			}
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$data=$data_model->init_model_data_from_db($table_name, $primary_key_name, $id, $form_data, true, true, true);
		
	
		
		return $data;
		
	}
}