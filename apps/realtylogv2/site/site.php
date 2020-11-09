<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Realtypro fronend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class realtylogv2_site extends realtylogv2_admin {
	
	function frontend () {
		if ( !$this->getConfigValue('apps.realtylogv2.enable') ) {
			return false;
		}
		
		$namespace=trim(SITEBILL_MAIN_URL.'/'.$this->getConfigValue('apps.realtylogv2.namespace'),'/');
		//$request_uri=trim($_SERVER['REQUEST_URI'],'/');
		$request_uri=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
		//echo $request_uri;
				
		if(!preg_match('/^'.str_replace('/', '\/', $namespace).'/', $request_uri)){
			return false;
		}
		
		global $smarty;
		$this->set_apps_template('realtylogv2', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl.html');
		$uid=(int)$_SESSION['user_id'];
		if($uid==0){
			$this->set_apps_template('realtylogv2', $this->getConfigValue('theme'), 'realtylogv2_inc_file', 'error.tpl.html');
			return true;
		}
		
		
		if(preg_match('/^'.$namespace.'\/trash$/', $request_uri)){
			$smarty->assign('logdata',$this->getTrashLogList($uid));
			$this->set_apps_template('realtylogv2', $this->getConfigValue('theme'), 'realtylogv2_inc_file', 'trash_list.tpl.html');
            return true;
		}elseif(preg_match('/'.$namespace.'\/alllogs/', $request_uri)){
			$data=array();
			$query='SELECT realtylog_id, action, id, log_date FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE editor_id='.$uid.' ORDER BY id, log_date DESC';
			$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			if($stmt){
				$i=0;
				while($ar=$DBC->fetch($stmt)){
					$data[$i]=$ar;
					$data[$i]['actiondate']=date('d.m.Y H:i', strtotime($ar['log_date']));
					$i++;
				}
			}
			$smarty->assign('logdata', $data);
			$this->set_apps_template('realtylogv2', $this->getConfigValue('theme'), 'realtylogv2_inc_file', 'log_list.tpl.html');
            return true;
		}elseif(preg_match('/^'.$namespace.'\/trash\/clear((\/(\d+)?)?)$/', $request_uri, $matches)){
			if(isset($matches[3])){
				$this->clearTrash($uid,(int)$matches[3]);
			}else{
				$this->clearTrash($uid);
			}
			$smarty->assign('logdata',$this->getTrashLogList($uid));
			$this->set_apps_template('realtylogv2', $this->getConfigValue('theme'), 'realtylogv2_inc_file', 'trash_list.tpl.html');
            return true;
		}elseif(preg_match('/^'.$namespace.'\/restore\/(\d+)$/', $request_uri, $matches)){
			
			$this->restoreLog((int)$matches[1]);
			$smarty->assign('logdata',$this->getTrashLogList($uid));
			$this->set_apps_template('realtylogv2', $this->getConfigValue('theme'), 'realtylogv2_inc_file', 'trash_list.tpl.html');
            return true;
		}
		return false;
	}
    
    function getTrashLogList($uid){
    	$data=array();
    	$query='SELECT realtylog_id, id, log_date, log_data FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE editor_id='.$uid.' AND action=\'delete\' ORDER BY log_date DESC';
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$i=0;
			while($ar=$DBC->fetch($stmt)){
				$data[$i]=$ar;
				$data[$i]['actiondate']=date('d.m.Y H:i', strtotime($ar['log_date']));
				$i++;
			}
		}
    	if(count($data)>0){
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    		$data_model = new Data_Model();
    		$form_data = $data_model->get_kvartira_model(false);
    		foreach($data as $k=>$d){
    			$title_parts=array();
    			$form_data['data'] = $data_model->init_model_data_from_var(unserialize($d['log_data']), $d['id'], $form_data['data'],true);
    			if(isset($form_data['data']['topic_id']) && $form_data['data']['topic_id']['value_string']!=''){
    				$title_parts[]=$form_data['data']['topic_id']['value_string'];
    			}
    			if(isset($form_data['data']['city_id']) && $form_data['data']['city_id']['value_string']!=''){
    				$title_parts[]=$form_data['data']['city_id']['value_string'];
    			}
    			if(isset($form_data['data']['district_id']) && $form_data['data']['district_id']['value_string']!=''){
    				$title_parts[]=$form_data['data']['district_id']['value_string'];
    			}
    			if(isset($form_data['data']['street_id']) && $form_data['data']['street_id']['value_string']!=''){
    				
    				$x=$form_data['data']['street_id']['value_string'];
    				if(isset($form_data['data']['number']) && $form_data['data']['number']['value']!=='' && $form_data['data']['number']['value']!=0){
    					//$title_parts[]='Дом: '.$form_data['data']['number']['value'];
    					$x.=', '.$form_data['data']['number']['value'];
    				}
    				$title_parts[]=$x;
    			}
    			if(isset($form_data['data']['price']) && $form_data['data']['price']['value']!=='' && $form_data['data']['price']['value']!=0){
    				if(isset($form_data['data']['currency_id']) && $form_data['data']['currency_id']['value']!=0){
                        $title_parts[]='Цена: '.$form_data['data']['price']['value'].''.$form_data['data']['currency_id']['value_string'];
                    }else{
                        $title_parts[]='Цена: '.$form_data['data']['price']['value'];
                    }
                }
    			
    			$data[$k]['title']=implode('; ',$title_parts);
    			unset($data[$k]['log_data']);
    		}
    	}
    	return $data;
    }
    
    private function clearTrash($uid, $log_id=0){
    	$ids=array();
    	if($log_id==0){
    		$query='SELECT id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE editor_id='.$uid.' AND action=\'delete\'';
    	}else{
    		$query='SELECT id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE editor_id='.$uid.' AND action=\'delete\' AND realtylog_id='.$log_id;
    	}
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ids[]=(int)$ar['id'];
			}
		}
		if(!empty($ids)){
    		foreach($ids as $id){
    			$query='DELETE FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE id='.$id;
    			$stmt=$DBC->query($query);
    		}
    	}
    	return;
    }
    
}