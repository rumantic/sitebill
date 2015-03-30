<?php
/**
 * Ajax server class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Ajax_Server extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->SiteBill();
        Multilanguage::appendTemplateDictionary($this->getConfigValue('theme'));
    }
    
    private function _getOptionsData($key, $field, $table, $fieldby, $value){
    	$ret=array();
    	$DBC=DBC::getInstance();
    	$query='SELECT `'.$key.'` AS id, `'.$field.'` AS name FROM '.DB_PREFIX.'_'.$table.' WHERE `'.$fieldby.'` = ? ORDER BY `'.$field.'` ASC';
    	$stmt=$DBC->query($query, array($value));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ret[]=$ar;
    		}
    	}
    	return json_encode($ret);
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
    	
    	if(1==$this->getConfigValue('is_underconstruction')){
    		$ip=$_SERVER['REMOTE_ADDR'];
    		if($ip=='' || $ip!=$this->getConfigValue('is_underconstruction_allowed_ip')){
    			return false;
    		}
    	}
    	
    	$is_local=(int)$this->getRequestValue('local_ajax');
    	if($is_local==1 && file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/ajax/local_ajax_server.php')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/ajax/local_ajax_server.php';
    		$LAS=new Local_Ajax_Server();
    		return $LAS->main();
    	}
    	
    	global $estate_folder;
    	global $smarty;
    	$smarty->assign('estate_folder', $estate_folder);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    $kvartira_model = $data_model->get_kvartira_model(true);
	    
	    $ajax_controller_user_id=(int)$_SESSION['user_id'];
	    $ajax_user_mode='guest';
	    	    
	    if($ajax_controller_user_id==0){
	    	$ajax_controller_user_id=(int)$_SESSION['user_id_value'];
	    }
	    
	    
	    if($ajax_controller_user_id!=0){
	    	$DBC=DBC::getInstance();
	    	$query='SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1)';
	    	$stmt=$DBC->query($query, array($ajax_controller_user_id));
	    	if($stmt){
		    	$ar=$DBC->fetch($stmt);
		    	if($ar['system_name']=='admin'){
		    		$ajax_user_mode='admin';
		    	}else{
		    		$ajax_user_mode='user';
		    	}
	    	}
	    }
	    
        if($this->getRequestValue('_action')!=''){
        	switch($this->getRequestValue('_action')){
        		case 'save_changes' : {
        			if( $ajax_user_mode=='guest' ){
        				return 'error';
        			}
        			
        			$allow_edit=false;
        			
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
        			
        			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/data/data_manager.php') ) {
        				require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/data/data_manager.php');
        				$DM = new Data_Manager_Local();
        				//return 3;
        			} else {
        				$DM = new Data_Manager();
        				//return 2;
        			}
        			
        			
        			
        			
        			//$DM=new Data_Manager();
        			$Model=new Data_Model();
        			$form_data=$DM->data_model;
        			$table=$DM->table_name;
        			$form_data[$table] = $Model->init_model_data_from_request($form_data[$table]);
        			
        			if($ajax_user_mode=='user'){
        				$DBC=DBC::getInstance();
        				$query='SELECT COUNT(id) AS _cnt FROM '.DB_PREFIX.'_data WHERE id=? AND user_id=?';
        				$stmt=$DBC->query($query, array($form_data[$table]['id']['value'], $ajax_controller_user_id));
        				if($stmt){
        					$ar=$DBC->fetch($stmt);
        					if($ar['_cnt']==1){
        						$allow_edit=true;
        					}
        				}
        			}elseif($ajax_user_mode=='admin'){
        				$allow_edit=true;
        			}
        			
        			if($allow_edit){
        				foreach($form_data[$table] as $k=>$fd){
        					if(!is_array($form_data[$table][$k]['value'])){
        						$form_data[$table][$k]['value']=SiteBill::iconv('utf-8', SITE_ENCODING, $form_data[$table][$k]['value']);
        					}
        				
        				}
        				$data_model->forse_auto_add_values($form_data[$table]);
        				 
        				if ( !$DM->check_data( $form_data[$table] ) ) {
        					return 'error';
        				} else {
        					$DM->edit_data($form_data[$table]);
        					if ( $DM->getError() ) {
        						return 'error';
        					} else {
        						if($this->getConfigValue('apps.realtylog.enable')){
        							require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
        							$Logger=new realtylog_admin();
        							$Logger->addLog($form_data[$table]['id']['value'], $_SESSION['user_id_value'], 'edit', 'data');
        						}
        						if($this->getConfigValue('apps.realtylogv2.enable')){
        							require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
        							$Logger=new realtylogv2_admin();
        							$Logger->addLog($form_data[$table]['id']['value'], $_SESSION['user_id_value'], 'edit', 'data', 'id');
        						}
        						return 'saved';
        					}
        				}
        			}else{
        				return 'error';
        			}
        			break;
        		}
        	}
        }
        
        switch ( $this->getRequestValue('action') ) {
        	
        	case 'voter' : {
        		$user_identity=md5($_SERVER['HTTP_USER_AGENT'].'_'.$_SERVER['REMOTE_ADDR']);
        		$resultcode=(int)$_POST['resultcode'];
        		$realty_id=(int)$_POST['realty_id'];
        		if($realty_id==0){
        			return json_encode(array('result'=>'ERROR'));
        		}
        		$DBC=DBC::getInstance();
        		$query='SELECT COUNT(*) AS _cnt FROM '.DB_PREFIX.'_likevoter WHERE user_identity=? AND realty_id=?';
        		
        		$DBC=DBC::getInstance();
        		$stmt=$DBC->query($query, array($user_identity, $realty_id));
        		
        		if($stmt){
        			$ar=$DBC->fetch($stmt);
        			if($ar['_cnt']>0){
        				return json_encode(array('result'=>'ERROR'));
        			}else{
        				$query='INSERT INTO '.DB_PREFIX.'_likevoter (user_identity, realty_id, resultcode) VALUES (?, ?, ?)';
        				$stmt=$DBC->query($query, array($user_identity, $realty_id, $resultcode));
        				
        				$query='SELECT COUNT(*) AS _cnt FROM '.DB_PREFIX.'_likevoter WHERE realty_id=? AND resultcode=?';
        				$stmt=$DBC->query($query, array($realty_id, $resultcode));
        				if($stmt){
        					$ar=$DBC->fetch($stmt);
        					return json_encode(array('result'=>'OK', 'count'=>$ar['_cnt']));
        				}
        			}
        		}
        		break;
        	}
        	
        	case 'get_options' : {
        		$elname=$this->getRequestValue('frommodelfield');
        		$datavalue=$this->getRequestValue('value');
        		$byfield=$this->getRequestValue('byfield');
        		
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        		$data_model = new Data_Model();
        		$data_model_shared = $data_model->get_kvartira_model(false, true);
        		foreach($data_model_shared['data'] as $key=>$value){
        			if($value['type']=='select_by_query' && $value['name']==$elname){
        				return $this->_getOptionsData($value['primary_key_name'], $value['value_name'], $value['primary_key_table'], $byfield, $datavalue);
        			}
        		}
        		break;
        		$array=array(
        			array('id'=>11, 'name'=>'Ahdshsfd'),
        			array('id'=>13, 'name'=>'ewqweqe'),
        			array('id'=>14, 'name'=>'2weweffwef')
        		);
        		return json_encode($array);
        		break;
        	}
        	
        	case 'get_user_info' : {
        		$id=(int)$this->getRequestValue('user_id');
        		$DBC=DBC::getInstance();
        		$query='SELECT u.fio, u.login, u.email, u.imgfile, u.phone, g.name AS groupname, (SELECT COUNT(id) FROM '.DB_PREFIX.'_data WHERE user_id=?) AS data_count FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.user_id=? LIMIT 1';
        		$stmt=$DBC->query($query, array($id, $id));
        		$user=array();
        		if($stmt){
        			$user=$DBC->fetch($stmt);
        		}
        		
        		$ret='<div class="user_info">';
        		$ret.='<div class="user_info_media">';
        		$ret.='<img class="img-polaroid" src="'.($user['imgfile']!='' ? SITEBILL_MAIN_URL.'/img/data/user/'.$user['imgfile'] : SITEBILL_MAIN_URL.'/img/user_nophoto.png').'" />';
        		$ret.='</div>';
        		$ret.='<div class="user_info_data">';
        		$ret.='<address>';
        		
        		
        		if($user['fio']!=''){
        			$ret.='<span class="user_info_data_title">'.$user['fio'].'</span>';
        		
        			$ret.='<span>'.$user['login'].'</span>';
        		}else{
        			$ret.='<span class="user_info_data_title">'.$user['login'].'</span>';
        		}
        		$ret.='<br /><span>Advs: '.$user['data_count'].'</span>';
        		if($user['groupname']!=''){
        			$ret.='<div class="user_info_data_in">';
        			$ret.='<i class="icon-user"></i> '.$user['groupname'];
        			$ret.='</div>';
        		}
        		if($user['phone']!=''){
        			$ret.='<div class="user_info_data_in">';
        			$ret.='<i class="icon-headphones"></i> '.$user['phone'];
        			$ret.='</div>';
        		}
        		if($user['email']!=''){
        			$ret.='<div class="user_info_data_in">';
        			$ret.='<i class="icon-envelope"></i> '.$user['email'];
        			$ret.='</div>';
        		}
        		$ret.='</address>';
        		$ret.='</div>';
        		$ret.='</div>';
        		echo $ret;
        		exit();
        		break;
        		
        	}
        	
        	case 'save_topic_sort' : {
        		if($ajax_user_mode=='admin'){
        			$ids=array();
        			$parent_id=(int)$this->getRequestValue('parent_topic_id');
        			$ids=explode(',', $this->getRequestValue('child_topics'));
        			if(!empty($ids) && !in_array($parent_id, $ids)){
        				$DBC=DBC::getInstance();
        				$query='UPDATE '.DB_PREFIX.'_topic SET `parent_id`=?, `order`=? WHERE `id`=?';
        				foreach($ids as $k=>$id){
        					$stmt=$DBC->query($query, array($parent_id, $k, $id));
        				}
        			}
        		}
        		
        		exit();
        	}
        	case 'set_realty_status' : {
        		
        		$id=(int)$this->getRequestValue('id');
        		$status=(int)$this->getRequestValue('status');
        		
        		
        		$DBC=DBC::getInstance();
        		if($ajax_user_mode=='admin'){
        			$query='UPDATE '.DB_PREFIX.'_data SET `active`=? WHERE `id`=?';
        			$stmt=$DBC->query($query, array($status, $id));
        		}elseif($ajax_user_mode=='user'){
        			$query='UPDATE '.DB_PREFIX.'_data SET `active`=? WHERE `id`=? AND user_id=?';
        			$stmt=$DBC->query($query, array($status, $id, $ajax_controller_user_id));
        		}else{
        			return 'ERROR';
        		}
        		
        		
        		if($stmt){
        			return 'OK';
        		}else{
        			return 'ERROR';
        		}
        		exit();
        	} 
        	case 'topic_source' : {
        	
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
        		$id=(int)$this->getRequestValue('id');
        		$DBC=DBC::getInstance();
        		$result=array();
        	
        		$query='SELECT `name`, `id` FROM '.DB_PREFIX.'_topic WHERE `parent_id`=? ORDER BY `order` ASC, `name` ASC';
        		$stmt=$DBC->query($query, array($id));
        		if($stmt){
        			while($ar=$DBC->fetch($stmt)){
        				$node = array();
        				$node['id'] = $ar['id'];
        				$node['text'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']);
        				$node['state'] = Structure_Manager::has_child($ar['id']) ? 'closed' : 'open';
        				array_push($result, $node);
        			}
        		}
        	
        		echo json_encode($result);
        	
        		exit();
        	}
        	
        	case 'topic_delete' : {
        		if($ajax_user_mode!='admin'){
        			echo json_encode(array('status'=>'error', 'message'=>'have no access'));
        			exit();
        		}
        		$clear_option=(string)$this->getRequestValue('clear_option');
        		$clear_advs=(string)$this->getRequestValue('clear_advs');
        		$id=(int)$this->getRequestValue('id');
        		
        		if($clear_option==='' && $clear_advs===''){
        			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
        			$Structure_Manager=new Structure_Manager();
        			
        			$message='';
        			$status='ok';
        			$DBC=DBC::getInstance();
        			
        			$category_structure = $Structure_Manager->loadCategoryStructure();
        			if ( count($category_structure['childs'][$id]) > 0 ) {
        				$message.=Multilanguage::_('CATEGORY_HAS_CHILDS','system').'<br>';
        				$status='error';
        			}
        			
        			$query='SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE topic_id=?';
        			$stmt=$DBC->query($query, array($id));
        			$ar=$DBC->fetch($stmt);
        			if($ar['rs']!=0){
        				$message.=Multilanguage::_('NOT_EMPTY_CATEGORY','system').'<br>';
        				$status='error';
        			}
        			if($status=='ok'){
        				$Structure_Manager->deleteRecord($id);
        			}
        			$result=array('status'=>$status, 'message'=>$message);
        		}else{
        			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
        			$Structure_Manager=new Structure_Manager();
        			$Structure_Manager->deleteTopicItem($id, $clear_option, $clear_advs);
        			$message='';
        			$status='ok';
        			$result=array('status'=>$status, 'message'=>$message);
        		}
        		echo json_encode($result);
        		exit();
        	}
        	
        	case 'get_grid_data' : {
        		$params['page']=$this->getRequestValue('page');
        		$params['asc']=$this->getRequestValue('asc');
        		$params['order']=$this->getRequestValue('order');
        		//print_r($params);
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        		require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/mobile/grid/local_grid_constructor.php';
        		$grid_constructor = new Local_Grid_Constructor();
        		return $grid_constructor->main($params);
        		break;
        	}
        	
        	case 'collect_data' : {
        		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/data_collector.php')){
        			require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/data_collector.php';
        			$DC=new Data_Collector();
        			return $DC->collect_data();
        		}
        		return null;
        		break;
        	}
        	case 'get_form_element' :
        		$element_name=$this->getRequestValue('element');
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
        		$DM=new Data_Manager();
        		$body=$DM->get_element($element_name);
        		
        		return $body;
        	break;
			case 'go_up' :
				$body = '';
        		$id=(int)$this->getRequestValue('id');
				$date=date('Y-m-d H:i:s', time());
				$answer=date('d.m',time());
				$DBC=DBC::getInstance();
				if($ajax_user_mode=='admin'){
					$query='UPDATE '.DB_PREFIX.'_data SET date_added=? WHERE id=?';
					$stmt=$DBC->query($query, array($date, $id));
				}elseif($ajax_user_mode=='user'){
					$query='UPDATE '.DB_PREFIX.'_data SET date_added=? WHERE id=? AND user_id=?';
					$stmt=$DBC->query($query, array($date, $id, $ajax_controller_user_id));
				}else{
					$body = '';
				}
				
				
				if($stmt){
					$body = $answer;
				}
				
			break;
			case 'get_form_fields_rules' : {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
				if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/data/data_manager.php') ) {
					require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/data/data_manager.php');
					$DM = new Data_Manager_Local();
				} else {
					$DM = new Data_Manager();
				}
				$form_data=$DM->data_model;
				$table=$DM->table_name;
				$r=array();
				if(!empty($form_data[$table])){
					foreach($form_data[$table] as $k=>$v){
						if(isset($v['active_in_topic']) && $v['active_in_topic']!=0){
							$topics=explode(',',$v['active_in_topic']);
							$active_array_ids = explode(',',$v['active_in_topic']);
							$r[$k] = $active_array_ids;
						}else{
							$r[$k][]='all';
						}
						
					}
				}
				return json_encode($r);
				break;
			}
			
			case 'get_form_fields_rules_by_model' : {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
				$SM=new Structure_Manager();
				$category_structure=$SM->loadCategoryStructure();
				if ( $this->getRequestValue('model') == 'client'  ) {
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/admin.php');
					$DM = new client_admin();
				}
				$form_data=$DM->data_model;
				$table=$DM->table_name;
				$r=array();
				
				if(!empty($form_data[$table])){
					foreach($form_data[$table] as $k=>$v){
						if(isset($v['active_in_topic']) && $v['active_in_topic']!=0){
								
							$topics=explode(',',$v['active_in_topic']);
								
							$active_array_ids = explode(',',$v['active_in_topic']);
								
							$child_cats = array();
							foreach ($active_array_ids as $item_id => $check_active_id) {
								//echo '$check_active_id = '.$check_active_id.'<br>';
								$child_cats_compare = $SM->get_all_childs($check_active_id, $category_structure);
								if ( is_array($child_cats_compare) ) {
									$child_cats = array_merge($child_cats, $child_cats_compare);
								}
								$child_cats[]=$check_active_id;
							}
								
							$r[$k] = $child_cats;
						}else{
							$r[$k][]='all';
						}
			
					}
				}
				//return print_r($r,true);
				return json_encode($r);
				return print_r($form_data,true);
				break;
			}
				
			case 'delete_image' : {
				$table=$this->getRequestValue('table_name');
				$image_id=(int)$this->getRequestValue('image_id');
				$data_id=(int)$this->getRequestValue('data_id');
				$key=$this->getRequestValue('key');
				$body='error';
				if($table=='' || $image_id==0 || $data_id==0){
					
				}else{
					if($_SESSION['user_id']==='true'){
						$this->deleteImage($table, $image_id);
						$body='ok';
					}elseif((int)$_SESSION['user_id']>0){
						$DBC=DBC::getInstance();
						if($table=='booking_apartment'){
							$query='SELECT user_id FROM '.DB_PREFIX.'_booking_hotel WHERE hotel_id=(SELECT hotel_id 
									FROM '.DB_PREFIX.'_'.$table.' 
									WHERE `'.$key.'`=(
											SELECT '.$key.' 
											FROM '.DB_PREFIX.'_'.$table.'_image 
											WHERE image_id=? AND `'.$key.'`=?'.'
											))';
							$stmt=$DBC->query($query, array($image_id, $data_id));
							
						}else{
							$query='SELECT user_id FROM '.DB_PREFIX.'_'.$table.' WHERE '.$key.'=(SELECT `'.$key.'` FROM '.DB_PREFIX.'_'.$table.'_image WHERE image_id=? AND `'.$key.'`=?)';
							$stmt=$DBC->query($query, array($image_id, $data_id));
						}
						//echo $query;
						
						if($stmt){
							$ar=$DBC->fetch($stmt);
							if((int)$ar['user_id']==(int)$_SESSION['user_id']){
								$this->deleteImage($table, $image_id);
								$body='ok';
							}
						}
					}
				}
				return $body;
				break;
			}
			case 'make_main_image' : {
				$table=$this->getRequestValue('table_name');
				$image_id=(int)$this->getRequestValue('image_id');
				$key=$this->getRequestValue('key');
				$key_value=(int)$this->getRequestValue('key_value');
				$this->makeImageMain($table, $image_id, $key, $key_value);
				break;
			}
			case 'dz_imagework' : {
				$what=$this->getRequestValue('what');
				
				$user_id=(int)$_SESSION['user_id'];
				if($user_id==0){
					$user_id=(int)$_SESSION['user_id_value'];
				}
				$admin_mode=false;
				
				if($user_id==0){
					return 'error';
				}
				$DBC=DBC::getInstance();
				$query='SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1)';
				$stmt=$DBC->query($query, array($user_id));
				if(!$stmt){
					return 'error';
				}
				$ar=$DBC->fetch($stmt);
				if($ar['system_name']=='admin'){
					$admin_mode=true;
				}
				
				switch($what){
					case 'reorder' : {
						$table=$this->getRequestValue('table_name');
						$field_name=$this->getRequestValue('field_name');
						$current_position=(int)$this->getRequestValue('current_position');
						$key=$this->getRequestValue('key');
						$key_value=(int)$this->getRequestValue('key_value');
						$reorder=$this->getRequestValue('reorder');
						if($reorder=='up'){
							$new_position=$current_position-1;
						}elseif($reorder=='down'){
							$new_position=$current_position+1;
						}
						$DBC=DBC::getInstance();
						if($admin_mode){
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value));
						}else{
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? AND user_id=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value, $user_id));
						}
						
						if(!$stmt){
							return 'error';
						}
						$ar=$DBC->fetch($stmt);
						if($ar[$field_name]==''){
							return 'error';
						}
						$uploads=unserialize($ar[$field_name]);
						if(!isset($uploads[$current_position]) || !isset($uploads[$new_position])){
							return 'error';
						}
						$temp=$uploads[$current_position];
						$uploads[$current_position]=$uploads[$new_position];
						$uploads[$new_position]=$temp;
						$query='UPDATE `'.DB_PREFIX.'_'.$table.'` SET `'.$field_name.'`=? WHERE `'.$key.'`=?';
						$stmt=$DBC->query($query, array(serialize($uploads), $key_value));
						if($stmt){
							return 'ok';
						}
						return 'error';
						break;
					}
					case 'delete' : {
						$table=$this->getRequestValue('table_name');
						$field_name=$this->getRequestValue('field_name');
						$current_position=(int)$this->getRequestValue('current_position');
						$key=$this->getRequestValue('key');
						$key_value=(int)$this->getRequestValue('key_value');
						$DBC=DBC::getInstance();
						
						if($admin_mode){
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value));
						}else{
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? AND user_id=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value, $user_id));
						}
						
						
						if(!$stmt){
							return 'error';
						}
						$ar=$DBC->fetch($stmt);
						if($ar[$field_name]==''){
							return 'error';
						}
						$uploads=unserialize($ar[$field_name]);
						if(!isset($uploads[$current_position])){
							return 'error';
						}
						$path = SITEBILL_DOCUMENT_ROOT.$this->storage_dir;
						$preview = $uploads[$current_position]['preview'];
						$normal = $uploads[$current_position]['normal'];
						@unlink($path.$preview);
						@unlink($path.$normal);
						unset($uploads[$current_position]);
						$uploads=array_values($uploads);
						if(count($uploads)==0){
							$nuploads='';
						}else{
							$nuploads=serialize($uploads);
						}
						$query='UPDATE `'.DB_PREFIX.'_'.$table.'` SET `'.$field_name.'`=? WHERE `'.$key.'`=?';
						$stmt=$DBC->query($query, array($nuploads, $key_value));
						if($stmt){
							return 'ok';
						}
						return 'error';
						break;
					}
					case 'delete_all' : {
						$table=$this->getRequestValue('table_name');
						$field_name=$this->getRequestValue('field_name');
						$key=$this->getRequestValue('key');
						$key_value=(int)$this->getRequestValue('key_value');
						$DBC=DBC::getInstance();
						
						if($admin_mode){
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value));
						}else{
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? AND user_id=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value, $user_id));
						}
						
						if(!$stmt){
							return 'error';
						}
						$ar=$DBC->fetch($stmt);
						if($ar[$field_name]==''){
							return 'ok';
						}
						
						$uploads=unserialize($ar[$field_name]);
						foreach($uploads as $upl){
							$path = SITEBILL_DOCUMENT_ROOT.$this->storage_dir;
							$preview = $upl['preview'];
							$normal = $upl['normal'];
							@unlink($path.$preview);
							@unlink($path.$normal);
						}
					
						if($admin_mode){
							$query='UPDATE `'.DB_PREFIX.'_'.$table.'` SET `'.$field_name.'`=\'\' WHERE `'.$key.'`=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value));
						}else{
							$query='UPDATE `'.DB_PREFIX.'_'.$table.'` SET `'.$field_name.'`=\'\' WHERE `'.$key.'`=? AND user_id=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value, $user_id));
						}
						return 'ok';
					
						break;
					}
					case 'make_main' : {
						$table=$this->getRequestValue('table_name');
						$field_name=$this->getRequestValue('field_name');
						$current_position=(int)$this->getRequestValue('current_position');
						$key=$this->getRequestValue('key');
						$key_value=(int)$this->getRequestValue('key_value');
						$DBC=DBC::getInstance();
						if($admin_mode){
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value));
						}else{
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? AND user_id=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value, $user_id));
						}
						if(!$stmt){
							return 'error';
						}
						$ar=$DBC->fetch($stmt);
						if($ar[$field_name]==''){
							return 'error';
						}
						$uploads=unserialize($ar[$field_name]);
						if(!isset($uploads[$current_position])){
							return 'error';
						}
						$temp=$uploads[$current_position];
						unset($uploads[$current_position]);
						array_unshift($uploads, $temp);
						$uploads=array_values($uploads);
						$query='UPDATE `'.DB_PREFIX.'_'.$table.'` SET `'.$field_name.'`=? WHERE `'.$key.'`=?';
						$stmt=$DBC->query($query, array(serialize($uploads), $key_value));
						if($stmt){
							return 'ok';
						}
						return 'error';
						break;
					}
					case 'change_title' : {
						$title=htmlspecialchars($this->getRequestValue('title'));
						$title=substr($title, 0, 100);
						
						$table=$this->getRequestValue('table_name');
						$field_name=$this->getRequestValue('field_name');
						$current_position=(int)$this->getRequestValue('current_position');
						$key=$this->getRequestValue('key');
						$key_value=(int)$this->getRequestValue('key_value');
						$DBC=DBC::getInstance();
						if($admin_mode){
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value));
						}else{
							$query='SELECT `'.$field_name.'` FROM `'.DB_PREFIX.'_'.$table.'` WHERE `'.$key.'`=? AND user_id=? LIMIT 1';
							$stmt=$DBC->query($query, array($key_value, $user_id));
						}
						if(!$stmt){
							return false;
						}
						$ar=$DBC->fetch($stmt);
						if($ar[$field_name]==''){
							return false;
						}
						$uploads=unserialize($ar[$field_name]);
						if(!isset($uploads[$current_position])){
							return false;
						}
						$uploads[$current_position]['title']=$title;
						$query='UPDATE `'.DB_PREFIX.'_'.$table.'` SET `'.$field_name.'`=? WHERE `'.$key.'`=?';
						$stmt=$DBC->query($query, array(serialize($uploads), $key_value));
						if($stmt){
							return $title;
						}
						break;
					}
				}
			}
			case 'reorder_image' : {
				$table=$this->getRequestValue('table_name');
				$image_id=(int)$this->getRequestValue('image_id');
				$key=$this->getRequestValue('key');
				$key_value=(int)$this->getRequestValue('key_value');
				$reorder=$this->getRequestValue('reorder');
				if($reorder=='up'){
					$this->reorderImage($table, $image_id, $key, $key_value, 'up');
				}elseif($reorder=='down'){
					$this->reorderImage($table, $image_id, $key, $key_value, 'down');
				}
				break;
			}
			case 'change_image_title' : {
				$title=$this->getRequestValue('title');
				$image_id=(int)$this->getRequestValue('image_id');
				if(get_magic_quotes_gpc()){
					$title=stripslashes($title);
				}
				$title=trim($title);
				$title=SiteBill::iconv('utf-8', SITE_ENCODING, $title);
				if($image_id!=0){
					$DBC=DBC::getInstance();
					$query='UPDATE '.DB_PREFIX.'_image SET title=? WHERE image_id=?';
					$DBC->query($query, array(mysql_real_escape_string($title), $image_id));
				}
				return '';
			}
			
			case 'change_image_description' : {
				$description=$this->getRequestValue('description');
				$image_id=(int)$this->getRequestValue('image_id');
				if(get_magic_quotes_gpc()){
					$title=stripslashes($title);
				}
				$description=trim($description);
				$description=SiteBill::iconv('utf-8', SITE_ENCODING, $description);
				if($image_id!=0){
					$DBC=DBC::getInstance();
					$query='UPDATE '.DB_PREFIX.'_image SET description=\''.mysql_real_escape_string($description).'\' WHERE image_id='.$image_id;
					$DBC->query($query, array(mysql_real_escape_string($description), $image_id));
				}
				return '';
			}
				
			case 'show_contact':
        		$body = '';
        		$id=(int)$this->getRequestValue('id');
        		if($id!=0 && $ajax_user_mode=='admin'){
        			$DBC=DBC::getInstance();
        			$query='UPDATE '.DB_PREFIX.'_data SET show_contact=1 WHERE id=?';
        			$stmt=$DBC->query($query, array($id));
        			if($stmt){
        				$body = 'OK';
        			}
        		}
        	break;
			
			case 'get_districts_by_city_id':
			
        		$body = '';
        		$id=(int)$this->getRequestValue('loginreg-city_id');
        		if($id!=0){
        			$DBC=DBC::getInstance();
        			$query='SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=?';
        			$stmt=$DBC->query($query, array($id));
        			
        			if($stmt){
						while($ar=$DBC->fetch($stmt)){
							$ret[]=array('district_id'=>$ar['id'], 'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
						}
						
        				return json_encode($ret);
        			}
        		}
        	break;
        	case 'add_to_agentphones' : {
        		$phone=preg_replace('/\D/','',$this->getRequestValue('phone'));
        		$DBC=DBC::getInstance();
				$query='SELECT COUNT(*) AS added_yet FROM '.DB_PREFIX.'_agentphones WHERE phone=?';
				$stmt=$DBC->query($query, array($phone));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					if(0==$ar['added_yet']){
						$query='INSERT INTO '.DB_PREFIX.'_agentphones (phone) VALUES (?)';
						$stmt=$DBC->query($query, array($phone));
					}
				}
				break;
           	}
        	
        	case 'get_search_form':
        		global $smarty;
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/search/kvartira_search.php');
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        		$Structure_Manager = new Structure_Manager();
        		
        		$this->template->assert('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id') ));
        		
        		$kvartira_search_form = new Kvartira_Search_Form();
        		$kvartira_search_form->main();
        		$form_code = $smarty->fetch('search_form.tpl');
        		$ra = array();
        		$ra['response']['body'] = htmlentities( $form_code, ENT_QUOTES, SITE_ENCODING);
        		return json_encode($ra);
        	break;
            case 'hide_contact':
        		$body = '';
        		$id=(int)$this->getRequestValue('id');
        		if($id!=0 && $ajax_user_mode=='admin'){
        			$DBC=DBC::getInstance();
        			$query='UPDATE '.DB_PREFIX.'_data SET show_contact=0 WHERE id=?';
        			$stmt=$DBC->query($query, array($id));
        			if($stmt){
        				$body = 'OK';
        			}
        		}
            break;
            case 'add_to_favorites':
            	$id=(int)$this->getRequestValue('id');
            	$user_id=(int)$this->getSessionUserId();
            	
            	if($user_id!=0){
            		if(isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites']!=''){
            			$cc=unserialize($_COOKIE['user_favorites']);
            		}else{
            			$cc=array();
            		}
            		
            		if($id!=0){
            			if(!isset($cc[$user_id][$id])){
            				$cc[$user_id][$id]=$id;
            				$body = 'OK';
            			}else{
            				$body = '';
            			}
            			setcookie("user_favorites", serialize($cc), time()+7*24*3600, '/');
            			$_SESSION['favorites']=$cc[$user_id];
            		}
            		//echo 1;
            		
            		//$body = 'OK';
            	}else{
            		if($id!=0){
            			if(!isset($_SESSION['favorites'][$id])){
            				$_SESSION['favorites'][$id] = $id;
            				$body = 'OK';
            			}else{
            				$body = '';
            			}
            		}
            	}
            	
            	//$body = 'OK';
        		/*if($id!=0){
        			if(!isset($_SESSION['favorites'][$id])){
        				$_SESSION['favorites'][$id] = $id;
        				$body = 'OK';
        			}else{
        				$body = '';
        			}
        		}*/
        	break;
        	case 'remove_from_favorites':
            	$id=(int)$this->getRequestValue('id');
            	$user_id=(int)$this->getSessionUserId();
            	if($user_id!=0){
            		if(isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites']!=''){
            			$cc=unserialize($_COOKIE['user_favorites']);
            		}else{
            			$cc=array();
            		}
            		if($id!=0 && isset($cc[$user_id][$id])){
            			unset($cc[$user_id][$id]);
            			$body = 'OK';
            			
            		}else{
            			$body = '';
            		}
            		setcookie("user_favorites", serialize($cc), time()+7*24*3600, '/');
            		$_SESSION['favorites']=$cc[$user_id];
            		
            	}else{
            		if($id!=0){
            			if(isset($_SESSION['favorites'][$id])){
            				unset($_SESSION['favorites'][$id]);
            				$body = 'OK';
            			}else{
            				$body = '';
            			}
            		}
            	}
        		
        	break;
        	
        	case 'get_specialoffers':
        		global $smarty;
        	
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php';
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        		if ( $this->getConfigValue('theme') == 'kupikuban' ) {
        			require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/grid_constructor_local.php';
        			$GC=new Grid_Constructor_Local();
        			$adv=$GC->vip_array(array('vip'=>'1'));
        		} else {
        			$GC=new Grid_Constructor();
        			$adv=$GC->get_sitebill_adv_ext(array('hot'=>'1'));
        		}
        		if ( $GC->get_grid_total_records() > 0 ) {
        			$this->template->assert('grid_items',$adv);
        			$rs=$smarty->fetch('realty_grid.tpl');
        		} else {
        			$rs = '<h2>'.Multilanguage::_('L_NO_HOT').'</h2>';
        		}

        		$ra['response']['body'] = htmlentities( $rs, ENT_QUOTES, SITE_ENCODING);
        		return json_encode($ra);
        	
        	break;
        	case 'get_recomendation':
        		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/grid/grid_constructor.php') ) {
        			global $smarty;
        			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php';
        			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        			require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/grid/grid_constructor.php';
        			$GC=new Grid_Constructor_Local();
        			$adv=$GC->get_sitebill_adv_ext(array('recomendation'=>'1'));
        			
        			if ( $GC->get_grid_total_records() > 0 ) {
        				$this->template->assert('grid_items',$adv);
        				$rs=$smarty->fetch('realty_grid.tpl');
        			} else {
        				$rs = '<h2>'.Multilanguage::_('L_NO_RECOMENDATION').'</h2>';
        			}
        			 
        			$ra['response']['body'] = htmlentities( $rs, ENT_QUOTES, SITE_ENCODING);
        			return json_encode($ra);
	       		}
        	break;
        	case 'get_station_list': {
        		$metro=array();
        		$DBC=DBC::getInstance();
        		$query='SELECT metro_id, LOWER(name) AS name FROM '.DB_PREFIX.'_metro';
        		$stmt=$DBC->query($query);
        		if($stmt){
        			while($ar=$DBC->fetch($stmt)){
        				$metro[]=array('id'=>$ar['metro_id'],'name'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $ar['name']));
        			}
        		}
        		return json_encode($metro);
        		break;
        	}
        		
        		 
        	case 'get_my_favorites':
        		global $smarty;
        		
        		
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php';
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        		$GC=new Grid_Constructor();
        		if ( count($_SESSION['favorites']) == 0 ) {
        			$rs = '<h2>'.Multilanguage::_('L_NO_FAVORITES').'</h2>';
        		} else {
        			$adv=$GC->get_sitebill_adv_ext(array('favorites'=>$_SESSION['favorites']));
        			 
        			$this->template->assert('grid_items', $adv);
        			//$smarty->assign('grid_items', $adv);
        			$rs=$smarty->fetch('realty_grid.tpl');
        		}
        		
        		$ra['response']['body'] = htmlentities( $rs, ENT_QUOTES, SITE_ENCODING);
        		return json_encode($ra);
        		
        		//$body=$rs;
	        	
        	break;
            case 'remove_from_favorites':
            	if((int)$this->getRequestValue('id')!=0){
            		if(isset($_SESSION['favorites'][(int)$this->getRequestValue('id')])){
            			unset($_SESSION['favorites'][(int)$this->getRequestValue('id')]);
            		}
        		}
        		$body = 'OK';
            break;
        	case 'add_my_city':
        		if($this->getRequestValue('city_id')==''){
        			unset($_SESSION['city_id']);
        		}else{
        			$_SESSION['city_id'] = $this->getRequestValue('city_id');
        		}
        		$body = 'OK';
            break;
            case 'get_city_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['city_id']);
                if ( $form_generator->get_total_in_select('city_id') == 0 ) {
                    $body = '<div id="city_id_div"></div>';
                }
            break;
            
            case 'get_region_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['region_id']);
                if ( $form_generator->get_total_in_select('region_id') == 0 ) {
                    $body = '<div id="region_id_div"></div>';
                }
            break;
            
            case 'get_metro_id':
            	$body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['metro_id']);
                if ( $form_generator->get_total_in_select('metro_id') == 0 ) {
                    $body = '<div id="metro_id_div"></div>';
                }
            break;
            
            case 'get_district_id':
            	if('yes'==$this->getRequestValue('multiple_mode')){
            		$body = $form_generator->get_single_select_box_by_query_multiple($kvartira_model['data']['district_id']);
            	}else{
            		$body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['district_id']);
            	}
                
                if ( $form_generator->get_total_in_select('district_id') == 0 ) {
                    $body = '<div id="district_id_div"></div>';
                }
            break;
            
            case 'get_street_id':
                $body = $form_generator->get_single_select_box_by_query($kvartira_model['data']['street_id']);
                if ( $form_generator->get_total_in_select('street_id') == 0 ) {
                    $body = '<div id="street_id_div"></div>';
                }
            break;
            
            
            case 'get_mark_list':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_mark_select_box($this->getRequestValue('parent_id'), 0, $current_mark_id);
            break;
            
            case 'get_coachwork_list':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_coachwork_select_box($this->getRequestValue('parent_id'), 0, $current_mark_id);
            break;
            
            case 'get_model_list':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_model_select_box($this->getRequestValue('mark_id'), $current_model_id);
            break;
            
            case 'get_modification_list':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
                $structure_manager = new Structure_Manager();
                $body = $structure_manager->get_flat_modification_select_box($this->getRequestValue('model_id'), $current_modification_id);
            break;
            
            case 'delete_user':
                if ( $_SESSION['group'] == 'nanoadmin' ) {
                    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php');
                    $user_manager = new Users_Manager();
                    $user_manager->delete_user($this->getRequestValue('user_id'));
                }
            break;
            
            
            case 'register_complete':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php');
                $user_manager = new Users_Manager();
                
                $params['phone'] = $this->getRequestValue('phone');
                $params['mobile'] = $this->getRequestValue('mobile');
                $params['icq'] = $this->getRequestValue('icq');
                $params['site'] = $this->getRequestValue('site');
                $user_manager->add_ajax_user($this->getRequestValue('user_id'), $this->getRequestValue('fio'), $this->getRequestValue('email'), $params  );
            break;
            
            case 'ajax_login':
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
            	$Login = new Login();
            	/*$userlogin=SiteBill::iconv('utf-8', SITE_ENCODING, $_GET['login']);
            	$userpassword=SiteBill::iconv('utf-8', SITE_ENCODING, $_GET['password']);*/
            	$userlogin=preg_replace('/([^a-zA-Z-_0-9\.@])/', '', $_GET['login']);
            	$userpassword=$_GET['password'];
            	$rememberme=(int)$_GET['rememberme'];
            	
            	if(TRUE===$Login->checkLogin($userlogin, $userpassword, $rememberme)){
            		$body='Authorized';
	            	if($this->getConfigValue('apps.accountsms.enable')){
	            		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
	            		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/accountsms/admin/admin.php');
	            		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/accountsms/site/site.php');
	            		$Accountsms_Site=new accountsms_site();
	            		$_SESSION['viewOptions']=$Accountsms_Site->getViewOptions($this->getSessionUserId());
	            	}
            	}else{
            		$body='error';
            	}
            break;
            
            case 'ajax_register':
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register_using_model.php');
            	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/register/local_register_using_model.php')){
            		require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/register/local_register_using_model.php');
            		$Register = new Local_Register_Using_Model();
            	}else{
            		$Register = new Register_Using_Model();
            	}
            	
            	$this->setRequestValue('do', 'new_done');
            	$rs1 = $Register->ajaxRegister();
            	return $rs1;
            	break;
            
            case 'login':
                $_SESSION['user_id'] = $this->getRequestValue('user_id');
                $_SESSION['group'] = $this->getRequestValue('group');
                $_SESSION['session_key'] = $this->getRequestValue('session_key');
                $_SESSION['key'] = $this->getRequestValue('session_key');
                $user_ip = $_SERVER['REMOTE_ADDR'];
                $DBC=DBC::getInstance();
                $query = 'INSERT INTO '.DB_PREFIX.'_session (user_id, ip, session_key, start_date) VALUES (?, ?, ?, NOW())';
                $stmt=$DBC->query($query, array($_SESSION['user_id'], $user_ip, $_SESSION['key']));
            break;
            
            case 'get_cart_count':
            	$items_count=0;
            	$summ=0;
				$positions_count=count($_SESSION['product_list']);
				if($positions_count!=0){
					foreach($_SESSION['product_list'] as $v){
						$items_count+=$v['count'];
						$summ+=$v['sum'];
					}
				}
				if(IS_NUKUPI==1){
					$body='У вас в <a href="'.SITEBILL_MAIN_URL.'/cart/">Корзине</a> <br /><strong>'.$items_count.' покупок</strong> <br />на <strong>'.$summ.' руб.</strong>';
				}else{
					$body='Корзина ('.$items_count.')';
				}
			break;
            
            case 'check_address':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/ajax/check_address/check_address.php');
                $check_address = new Check_Address_Ajax();
                $body = $check_address->check($this->getRequestValue('address'));
            break;
            
        	case 'add_to_cart': 
                $product_data = $this->load_product_data($this->getRequestValue('product_id'));
                if ( $product_data ) {
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_name'] = $product_data['product_name'];
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_price'] = $product_data['product_price'];
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_id'] = $product_data['product_id'];
                    
                    $product_count = $_SESSION['product_list'][$this->getRequestValue('product_id')]['count'];
                    $product_count++; 
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['count'] = $product_count;
                    
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['sum'] = $product_data['product_price']*$product_count;
                    
                    $body = 'add '.$this->getRequestValue('product_id');
                } else {
                    $body = 'Товар не найден';
                }
            break;
            
            case 'delete_from_cart': 
                $product_data = $this->load_product_data($this->getRequestValue('product_id'));
                unset($_SESSION['product_list'][$this->getRequestValue('product_id')]);
                
            break;
            
            case 'update_quantity':
            	$new_qty=$this->getRequestValue('quantity');
            	$product_id=$this->getRequestValue('product_id');
		        if($new_qty>0){
		    		$_SESSION['product_list'][$product_id]['count'] = $new_qty;
		        	$_SESSION['product_list'][$product_id]['sum'] = $_SESSION['product_list'][$product_id]['product_price']*$_SESSION['product_list'][$product_id]['count'];
		    	}else{
		    		unset($_SESSION['product_list'][$product_id]);
    			}
                
            break;
            
            case 'delete_uploadify_image':
            	$img_name=$this->getRequestValue('img_name');
            	$this->delete_uploadify_image($img_name);
            	$body = 'OK';
            break;
            
            case 'autocomplete':
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/realtyautocomplete/lib/realty_autocomplete.php');
                $realty_autocomplete = new realty_autocomplete();
                $q = $_GET["term"];
                if (!$q) return;
                
                $result = $realty_autocomplete->generate_array($q);
                echo $this->array_to_json($result);
                exit;
            break;
            case 'get_districts' : {
            	$districts=array();
            	$city_id=$this->getRequestValue('city_id');
            	$DBC=DBC::getInstance();
            	$stmt=$DBC->query('SELECT id, name FROM '.DB_PREFIX.'_district WHERE city_id=?', array($city_id));
            	if($stmt){
            		while($ar=$DBC->fetch($stmt)){
            			$districts[]=$ar;
            		}
            	}
            	return json_encode(array('districts'=>$districts));
            	break;
            }
            
            case 'get_vip_cost':
            	echo $this->getConfigValue('vip_cost');
            	exit;
            break;
            case 'make_special_payment': {
            	$current_account=0;
            	$user_id=$this->getSessionUserId();
            	$realty_id=(int)$this->getRequestValue('realty_id');
            	$days=(int)$this->getRequestValue('days');
            	//$per_day=abs($this->getRequestValue('per_day'));
            	$per_day=0;
            	$payment_type=$this->getRequestValue('payment_type');
            	
            	switch($payment_type){
            		case 'vip' : {
            			$per_day=$this->getConfigValue('vip_cost');
            			break;
            		}
            		case 'premium' : {
            			$per_day=$this->getConfigValue('premium_cost');
            			break;
            		}
            		case 'bold' : {
            			$per_day=$this->getConfigValue('bold_cost');
            			break;
            		}
            		case 'buy_ups' : {
            			$per_day=$this->getConfigValue('ups_price');
            			break;
            		}
            		case 'make_up' : {
            			$per_day=$this->getConfigValue('ups_price');
            			$days=1;
            			break;
            		}
            	}
            	$sum=$days*$per_day;
            	if($sum==0){
            		echo 'error';
            		exit;
            	}
            	//if()
            	
            	if($user_id!=0 && $days>0 && in_array($payment_type,array('vip','premium','bold','buy_ups', 'make_up'))){
            		if($payment_type!='buy_ups' && $realty_id==0){
            			echo 'error';
            		}
            		
            		$DBC=DBC::getInstance();
            		
            		$query='SELECT account FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
            		$stmt=$DBC->query($query, array($user_id));
            		if($stmt){
            			$ar=$DBC->fetch($stmt);
            			$current_account=$ar['account'];
            		}
            		
            		$last_account=$current_account-$sum;
            		if($last_account<0){
            			$html='Недостаточно денег на вашем счету. <a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill">Пополнить счет</a>';
            		}else{
            			if($payment_type=='vip'){
            				$query='INSERT INTO '.DB_PREFIX.'_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, 1)';
            				$stmt=$DBC->query($query, array((int)$user_id, $sum, time(), 'Оплата VIP состояния объявления ID='.$realty_id.' на срок '.$days.' дней'));
            				
            				
            				if(!$stmt){
            					echo 'error';
            				} 
            				           				
            				$query='UPDATE '.DB_PREFIX.'_user SET account=? WHERE user_id=?';
            				$stmt=$DBC->query($query, array($last_account, $user_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				 
            				$query='UPDATE '.DB_PREFIX.'_data SET vip_status_end=? WHERE id=?';
            				$stmt=$DBC->query($query, array(time()+$days*86400, $realty_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				 
            				$html='Статус VIP присвоен';
            			}elseif($payment_type=='premium'){
            				$query='INSERT INTO '.DB_PREFIX.'_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, 1)';
            				$stmt=$DBC->query($query, array((int)$user_id, $sum, time(), 'Оплата Премиум состояния объявления ID='.$realty_id.' на срок '.$days.' дней'));

            				if(!$stmt){
            					echo 'error';
            				}
            				 
            				$query='UPDATE '.DB_PREFIX.'_user SET account=? WHERE user_id=?';
            				$stmt=$DBC->query($query, array($last_account, $user_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				             				
            				$query='UPDATE '.DB_PREFIX.'_data SET premium_status_end=? WHERE id=?';
            				$stmt=$DBC->query($query, array(time()+$days*86400, $realty_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				 
            				$html='Премиум статус присвоен';
            			}elseif($payment_type=='bold'){
            				$query='INSERT INTO '.DB_PREFIX.'_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?, 1)';
            				$stmt=$DBC->query($query, array((int)$user_id, $sum, time(), 'Оплата выделенного состояния объявления ID='.$realty_id.' на срок '.$days.' дней'));

            				if(!$stmt){
            					echo 'error';
            				}
            				 
            				$query='UPDATE '.DB_PREFIX.'_user SET account=? WHERE user_id=?';
            				$stmt=$DBC->query($query, array($last_account, $user_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				             				
            				$query='UPDATE '.DB_PREFIX.'_data SET bold_status_end=? WHERE id=?';
            				$stmt=$DBC->query($query, array(time()+$days*86400, $realty_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				 
            				
            				$html='Выделенный статус присвоен';
            			}elseif($payment_type=='buy_ups'){
            				$query='INSERT INTO '.DB_PREFIX.'_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?,1)';
            				$stmt=$DBC->query($query, array((int)$user_id, $sum, time(), 'Покупка пакета подъемов в количестве '.$days));
            				if(!$stmt){
            					echo 'error';
            				}
            				$query='UPDATE '.DB_PREFIX.'_user SET account=? WHERE user_id=?';
            				$stmt=$DBC->query($query, array($last_account, $user_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				
            				$query='SELECT COUNT(user_id) AS cnt FROM '.DB_PREFIX.'_upper_packet WHERE user_id=?';
            				$stmt=$DBC->query($query, array($user_id));
            				if($stmt){
            					$ar=$DBC->fetch($stmt);
            					if($ar['cnt']>0){
            						$query='UPDATE '.DB_PREFIX.'_upper_packet SET quantity=quantity+'.$days.' WHERE user_id=?';
            						$stmt=$DBC->query($query, array($user_id));
            					}else{
            						$query='INSERT INTO '.DB_PREFIX.'_upper_packet (`quantity`,`user_id`) VALUES (?, ?)';
            						$stmt=$DBC->query($query, array($days, $user_id));
            					}
            				}
            				
            				$html='Пакет подъемов оплачен';
            			}elseif($payment_type=='make_up'){
            				$query='INSERT INTO '.DB_PREFIX.'_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?,1)';
            				$stmt=$DBC->query($query, array((int)$user_id, $sum, time(), 'Поднятие объявления ID: '.$realty_id));
            				if(!$stmt){
            					echo 'error';
            				}
            				$query='UPDATE '.DB_PREFIX.'_user SET account=? WHERE user_id=?';
            				$stmt=$DBC->query($query, array($last_account, $user_id));

            				if(!$stmt){
            					echo 'error';
            				}
            				
            				$query='UPDATE '.DB_PREFIX.'_data SET date_added=? WHERE user_id=? AND id=?';
            				$stmt=$DBC->query($query, array(date('Y-m-d H:i:s', time()), $user_id, $realty_id));
            				            				
            				$html='Поднятие выполнено';
            			}
            			
            
            			 
            		}
            		echo $html;
            	}else{
            		echo 'error';
            	}
            	exit;
            	break;
            }
            case 'add_bill': {
            	$resp=array(
            			'status'=>'error',
            			'data'=>array()
            		);
            	$user_id=$this->getSessionUserId();
            	$payment_value=$this->getRequestValue('payment_value');
            	if($user_id!=0 && $payment_value>0){
            		$query='INSERT INTO '.DB_PREFIX.'_bill (`user_id`, `sum`, `date`, `description`, `status`) VALUES (?, ?, ?, ?,0)';
            		$stmt=$DBC->query($query, array((int)$user_id, $payment_value), time(), 'Пополнение счета пользователем ID: '.(int)$user_id);
            		if($stmt){
            			$bill_id = $DBC->lastInsertId();
            			$signature=md5($this->getConfigValue('robokassa_login').':'.$payment_value.':'.$bill_id.':'.$this->getConfigValue('robokassa_password1'));
            			$resp['status']='ok';
            			$resp['data']=array('id'=>$bill_id, 'signature'=>$signature, 'sum'=>$payment_value);
            		}
            		
            		
            	}
            	return json_encode($resp);
            	exit;
            	break;
            }
            	
        }
        
                    
        $body = str_replace("\r\n", ' ', $body);
        $body = str_replace("\n", ' ', $body);
        $body = addslashes($body);
        
        
        $rs = '
{
   	"response":{
        "to":"Tove",
        "from":"Jani",
        "body":"'.$body.'"
    }
}
        ';
		
		if ( $_REQUEST['callback'] != '' ) {
            $rs = $_REQUEST['callback'].'('.$rs.')';
        }
        
        return $rs;
    }
    
    function array_to_json( $array ){
    
    	if( !is_array( $array ) ){
    		return false;
    	}
    
    	$associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
    	if( $associative ){
    
    		$construct = array();
    		foreach( $array as $key => $value ){
    
    			// We first copy each key/value pair into a staging array,
    			// formatting each key and value properly as we go.
    
    			// Format the key:
    			if( is_numeric($key) ){
    				$key = "key_$key";
    			}
    			$key = "\"".addslashes($key)."\"";
    
    			// Format the value:
    			if( is_array( $value )){
    				$value = array_to_json( $value );
    			} else if( !is_numeric( $value ) || is_string( $value ) ){
    				$value = "\"".addslashes($value)."\"";
    			}
    
    			// Add to staging array:
    			$construct[] = "$key: $value";
    		}
    
    		// Then we collapse the staging array into the JSON form:
    		$result = "{ " . implode( ", ", $construct ) . " }";
    
    	} else { // If the array is a vector (not associative):
    
    		$construct = array();
    		foreach( $array as $value ){
    
    			// Format the value:
    			if( is_array( $value )){
    				$value = $this->array_to_json( $value );
    			} else if( !is_numeric( $value ) || is_string( $value ) ){
    				$value = "'".addslashes($value)."'";
    			}
    
    			// Add to staging array:
    			$construct[] = $value;
    		}
    
    		// Then we collapse the staging array into the JSON form:
    		$result = "[ " . implode( ", ", $construct ) . " ]";
    	}
    
    	return $result;
    }
    
    
	function load_product_data ( $product_id ) {
		$DBC=DBC::getInstance();
        $query = 'SELECT * FROM '.DB_PREFIX.'_shop_product WHERE product_id=? LIMIT 1';
        $stmt=$DBC->query($query, array($product_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar;
        }
        return false;
    }
    
}