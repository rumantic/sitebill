<?php
/**
 * User data manager
 * @author http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
class User_Data_Manager extends SiteBill_Rent_Editor {
	protected $_grid_constructor;
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        $this->_grid_constructor = new Grid_Constructor();
        
        $data_model = new Data_Model();
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
    	if ( $this->getConfigValue('hide_contact_input_user_data') ) {
    		unset($this->data_model['data']['fio']);
    		unset($this->data_model['data']['phone']);
    		unset($this->data_model['data']['email']);
    	}
    }
    
    function init_more_fields ( $form_data ) {
    	return $form_data;
    }
    
    protected function _upAction(){
    	$user_id=$this->getSessionUserId();
    	$rs='';
    	if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
			$rs = 'Доступ запрещен';
		}
		
		if ( $this->getConfigValue('apps.billing.enable') ) {
			$DBC=DBC::getInstance();
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
			$Account = new Account;
			if ( $this->getConfigValue('apps.company.enable') ) {
				$company_profile = $Account->get_company_profile($user_id);
				if ( $company_profile['limit_up']['value'] < 1 ) {
					$rs = 'Превышен лимит поднятий объявлений.';
					return $rs;
				}
			}
			//get max id from DB
			$query = 'SELECT max(id) AS mid FROM '.DB_PREFIX.'_data';
			$stmt=$DBC->query($query);
			$ar=$DBC->fetch($stmt);
			$mid = (int)$ar['mid'];
			$mid++;
			
			$query = 'UPDATE '.DB_PREFIX.'_data SET date_added=?, id=? WHERE id=?';
			$stmt=$DBC->query($query, array(date('Y-m-d H:i:s'), $mid, (int)$this->getRequestValue('id')));
			
			
			//update image link
			$query = 'UPDATE '.DB_PREFIX.'_data_image SET id=? WHERE id=?';
			$stmt=$DBC->query($query, array($mid, (int)$this->getRequestValue('id')));
			
			//minus point from company.limit_up
			$new_limit_up = $company_profile['limit_up']['value'] - 1;
			$query = 'UPDATE '.DB_PREFIX.'_company SET limit_up=? WHERE company_id=?';
			$stmt=$DBC->query($query, array($new_limit_up, $company_profile['company_id']['value']));
					
			$rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
				
		}
    	return $rs;
    }
    
    protected function _edit_doneAction(){
    	$user_id=$this->getSessionUserId();
    	$rs='';
    	if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
		        return 'Доступ запрещен';
		    }
		    
		    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		    $data_model = new Data_Model();
		    $form_data = $this->data_model;
		    
		    if ( $this->getConfigValue('more_fields_in_lk') ) {
		    	$form_data = $this->init_more_fields($form_data);
		    }
		    $form_data['data']['fio']['required'] = 'off';
		    
		    
		    if ( $this->getConfigValue('special_advert_cost') > 0 ) {
		    	$form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>'.$this->getConfigValue('special_advert_cost').' руб.</b>)';
		    }
		    
		    if($this->getConfigValue('user_add_street_enable')!=1){
		    	if(isset($form_data['data']['new_street'])){
		    		unset($form_data['data']['new_street']);
		    	}
		    }
		    
            $form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);
            
            
            
            
            $new_values=$this->getRequestValue('_new_value');
            if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
            	$remove_this_names=array();
            	foreach($form_data['data'] as $fd){
            		if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
            			$id=md5(time().'_'.rand(100,999));
            			$remove_this_names[]=$id;
            			
            			$form_data['data'][$id]['value'] = $new_values[$fd['name']];
            			$form_data['data'][$id]['type'] = 'auto_add_value';
            			$form_data['data'][$id]['dbtype'] = 'notable';
            			$form_data['data'][$id]['value_table'] = $form_data['data'][$fd['name']]['primary_key_table'];
            			$form_data['data'][$id]['value_primary_key'] = $form_data['data'][$fd['name']]['primary_key_name'];
            			$form_data['data'][$id]['value_field'] = $form_data['data'][$fd['name']]['value_name'];
            			$form_data['data'][$id]['assign_to'] = $fd['name'];
            			$form_data['data'][$id]['required'] = 'off';
            			$form_data['data'][$id]['unique'] = 'off';
            		}
            	}
            }
            
            
            $form_data['data']['user_id']['value'] = $user_id; 
		    
            $y_id='';
            if(strpos($form_data['data']['youtube']['value'],'youtube.com')!==FALSE){
				$d=parse_url($form_data['data']['youtube']['value']);
				if(isset($d['query'])){
					parse_str($d['query'],$a);
					$y_id=$a['v'];
				}
			}else{
			
				if(preg_match('/.*([-_A-Za-z0-9]+).*/',$form_data['data']['youtube']['value'],$matches)){
					$y_id=$matches[0];
				}
			}
			$form_data['data']['youtube']['value']=$y_id;
			unset($form_data['data']['view_count']);	
			$data_model->forse_auto_add_values($form_data['data']);
    		//$data_model->clear_auto_add_values($form_data['data']);
            
            if ( !$this->check_data( $form_data['data'] ) ) {
            	$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
		        $rs = $this->get_form($form_data['data'], 'edit');
		    } else {
		        $this->edit_data($form_data['data']);
		        if ( $this->getError() ) {
		        	$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
		            $rs = $this->get_form($form_data['data'], 'edit');
		        } else {
		        	
		        	if($this->getConfigValue('apps.realtylog.enable')){
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
			        	$Logger=new realtylog_admin();
			        	$Logger->addLog($form_data['data']['id']['value'], $user_id, 'edit', 'data');
		        	}
		        	if($this->getConfigValue('apps.realtylogv2.enable')){
		        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
		        		$Logger=new realtylogv2_admin();
		        		$Logger->addLog($form_data['data']['id']['value'], $user_id, 'edit', 'data', 'id');
		        	}
		        	header('Location: '.SITEBILL_MAIN_URL.'/account/data/');
		        	die();
		        }
		    }
    	return $rs;
    }
    
    protected function _editAction(){
    	
    	$user_id=$this->getSessionUserId();
    	$rs='';
    	if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
    		return 'Доступ запрещен';
    	}
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	
    	
    	if ( $this->getConfigValue('more_fields_in_lk') ) {
    		$form_data = $this->init_more_fields($form_data);
    	}
    	$form_data['data']['fio']['required'] = 'off';
    	
    	
    	if ( $this->getConfigValue('special_advert_cost') > 0 ) {
    		$form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>'.$this->getConfigValue('special_advert_cost').' руб.</b>)';
    	}
    	
    	if($this->getConfigValue('user_add_street_enable')!=1){
    		if(isset($form_data['data']['new_street'])){
    			unset($form_data['data']['new_street']);
    		}
    	}
    
    	$_form_data=$form_data;
		$form_data['data'] = $data_model->init_model_data_from_db ( 'data', 'id', $this->getRequestValue('id'), $form_data['data'] );
               
		if(1==$this->getConfigValue('divide_step_form') && isset($_POST['submit'])){
			$_form_data['data'] = $data_model->init_model_data_from_request($_form_data['data']);
			foreach($_form_data['data'] as $fdk=>$fdv){
				if($fdv['type']=='uploadify_image'){
					unset($_form_data['data'][$fdk]);
				}
			}
			$form_data['data']=array_merge($form_data['data'],$_form_data['data']);
		}
                
		$form_data['data']['user_id']['type']='hidden';
		unset($form_data['data']['view_count']);
		if ( $this->getConfigValue('enable_special_in_account') != 1 ) {
			unset($form_data['data']['hot']);
		}
                
		$rs .= $this->get_form($form_data['data'], 'edit');
		if($this->getConfigValue('apps.realtylog.enable')){
			$rs .= '<h2>Лог изменений</h2>';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/site/site.php';
			$Logger=new realtylog_site();
			$rs .= $Logger->getLogs($this->getRequestValue('id'),$user_id);
		}
    	return $rs;
    }
    
    protected function _new_doneAction(){
    	$user_id=$this->getSessionUserId();
    	$rs='';
    	
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	 
    	if ( $this->getConfigValue('more_fields_in_lk') ) {
    		$form_data = $this->init_more_fields($form_data);
    	}
    	$form_data['data']['fio']['required'] = 'off';
    	
    	 
    	if ( $this->getConfigValue('special_advert_cost') > 0 ) {
    		$form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>'.$this->getConfigValue('special_advert_cost').' руб.</b>)';
    	}
    	 
    	if($this->getConfigValue('user_add_street_enable')!=1){
    		if(isset($form_data['data']['new_street'])){
    			unset($form_data['data']['new_street']);
    		}
    	}
    
    	$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);
	            
            $new_values=$this->getRequestValue('_new_value');
            if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
            	$remove_this_names=array();
            	foreach($form_data['data'] as $fd){
            		if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
            			$id=md5(time().'_'.rand(100,999));
            			$remove_this_names[]=$id;
            			$form_data['data'][$id]['value'] = $new_values[$fd['name']];
            			$form_data['data'][$id]['type'] = 'auto_add_value';
            			$form_data['data'][$id]['dbtype'] = 'notable';
            			$form_data['data'][$id]['value_table'] = $form_data['data'][$fd['name']]['primary_key_table'];
            			$form_data['data'][$id]['value_primary_key'] = $form_data['data'][$fd['name']]['primary_key_name'];
            			$form_data['data'][$id]['value_field'] = $form_data['data'][$fd['name']]['value_name'];
            			$form_data['data'][$id]['assign_to'] = $fd['name'];
            			$form_data['data'][$id]['required'] = 'off';
            			$form_data['data'][$id]['unique'] = 'off';
            		}
            	}
            }
            
            $form_data['data']['user_id']['value'] = $user_id; 
            $form_data['data']['user_id']['type']='hidden';
            $form_data['data']['date_added']['value'] = date('Y-m-d H:i:s',time());
             
             
			$y_id='';
            if(strpos($form_data['data']['youtube']['value'],'youtube.com')!==FALSE){
				$d=parse_url($form_data['data']['youtube']['value']);
				if(isset($d['query'])){
					parse_str($d['query'],$a);
					$y_id=$a['v'];
				}
			}else{
			
				if(preg_match('/.*([-_A-Za-z0-9]+).*/',$form_data['data']['youtube']['value'],$matches)){
					$y_id=$matches[0];
				}
			}
			$form_data['data']['youtube']['value']=$y_id;
			
		    $data_model->forse_auto_add_values($form_data['data']);
    		
		    if ( !$this->check_data( $form_data['data'] ) || (1==$this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data['data'])) ) {
		    	
		    	$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
		    	$rs = $this->get_form($form_data['data'], 'new');
		        
		    } else {
		    	
		        $new_record_id = $this->add_data($form_data['data']);
		        if ( $this->getError() ) {
		        	$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
		        	$rs = $this->get_form($form_data['data']);
		        } else {
		        	if($this->getConfigValue('apps.realtylog.enable')){
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
			        	$Logger=new realtylog_admin();
			        	$Logger->addLog($new_record_id, $user_id, 'new', 'data');
		        	}
		        	if(1==$this->getConfigValue('notify_about_added_realty')){
		        		$this->notifyUserAboutAdding($form_data['data']['user_id']['value'], $new_record_id, $form_data['data']['topic_id']['value']);
		        	}
		        	
		        	if($this->getConfigValue('apps.realtylogv2.enable')){
		        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
		        		$Logger=new realtylogv2_admin();
		        		$Logger->addLog($new_record_id, $user_id, 'new', 'data', 'id');
		        	}
		        	header('Location: '.SITEBILL_MAIN_URL.'/account/data/');
		        	die();
		        }
		    }
    	return $rs;
    }
    
    protected function _deleteAction(){
    	$user_id=$this->getSessionUserId();
    	$id=(int)$this->getRequestValue('id');
    	$rs='';
    	if ( !$this->check_access_to_data($user_id, $id) ) {
    		return 'Доступ запрещен';
    	}
    	
    	$data_model = new Data_Model();
    	$model=$data_model->get_kvartira_model(false, true);
    	
    	if(1==(int)$this->getConfigValue('apps.realty.use_predeleting') && isset($model['data']['is_predeleted'])){
    		$DBC=DBC::getInstance();
    		$query='UPDATE '.DB_PREFIX.'_data SET is_predeleted=1 WHERE `id`=?';
    		$stmt=$DBC->query($query, array($id));
    	}else{
    		if($this->getConfigValue('apps.realtylog.enable')){
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
    			$Logger=new realtylog_admin();
    			$Logger->addLog($id, $user_id, 'delete', 'data');
    		}
    		if($this->getConfigValue('apps.realtylogv2.enable')){
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
    			$Logger=new realtylogv2_admin();
    			$Logger->addLog($id, $user_id, 'delete', 'data', 'id');
    		}
    		$this->delete_data('data', 'id', $id);
    	}
	   	$rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
    	return $rs;
    }
    
    protected function _newAction(){
    	$user_id=$this->getSessionUserId();
    	$rs='';
    	
    	
    	
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    
    	if ( $this->getConfigValue('more_fields_in_lk') ) {
    		$form_data = $this->init_more_fields($form_data);
    	}
    	$form_data['data']['fio']['required'] = 'off';
    	
    
    	if ( $this->getConfigValue('special_advert_cost') > 0 ) {
    		$form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>'.$this->getConfigValue('special_advert_cost').' руб.</b>)';
    	}
    
    	if($this->getConfigValue('user_add_street_enable')!=1){
    		if(isset($form_data['data']['new_street'])){
    			unset($form_data['data']['new_street']);
    		}
    	}
    
    	//$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);
    	
    	if ( $this->getConfigValue('apps.billing.enable') ) {
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/billing.xml') ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php');
				$billing = new Billing();
				$user_limits = $billing->getUserLimits($user_id, 'limit_data');
				if ( $user_limits && $user_limits['total'] >= $user_limits['limits'] ) {
					$rs = 'Превышен лимит объявлений. Вы разместили все <b>'.$user_limits['total'].'</b> из доступных <b>'.$user_limits['limits'].'</b> объявлений за <b>'.$user_limits['period_key'].'</b>';
					return $rs;
				}
				if(method_exists($billing, 'getUserAdvLimits')){
					$user_limits = $billing->getUserAdvLimits($user_id, 'advlimit_data');
					
					if ( $user_limits && $user_limits['total'] >= $user_limits['limits'] ) {
						$rs = 'Превышен лимит объявлений. Вы разместили все <b>'.$user_limits['total'].'</b> из доступных <b>'.$user_limits['limits'].'</b> объявлений';
						return $rs;
					}
				}
				
				
			} else {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
				$Account = new Account;
				$company_profile = $Account->get_company_profile($user_id);
				if ( $company_profile['limit_data_left']['value'] < 1 ) {
					$rs = 'Превышен лимит объявлений. Вы разместили все '.$company_profile['limit_data']['value'].' объявлений. Для увеличения лимита обратитесь к администратору.';
					return $rs;
				}
			}
		}
		//$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);
		$form_data['data']['user_id']['value'] = $user_id;
		$form_data['data']['user_id']['type']='hidden';
		$form_data['data']['active']['value']=1;
		if ( $this->getConfigValue('enable_special_in_account') != 1 ) {
			unset($form_data['data']['hot']);
		}
		
		$rs = $this->get_form($form_data['data']);
    	return $rs;
    }
    
    protected function _defaultAction(){
    	$user_id=$this->getSessionUserId();
    	$rs='';
    	$rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
    	return $rs;
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
    	
		$user_id=$this->getSessionUserId();
		if ( $user_id == '' or $user_id < 1 ) {
		    return 'Доступ запрещен';
		}
		
		
		
		$rs = $this->getTopMenu();
		
		$do=$this->getRequestValue('do');
		$action='_'.$do.'Action';
		
		if(!method_exists($this, $action)){
			$action='_defaultAction';
		}
		$rs .= $this->$action();
		
		
		return $rs;
	}
	
	
	
	function checkUniquety($form_data){
		$DBC=DBC::getInstance();
		$query='SELECT COUNT(id) AS cnt FROM '.DB_PREFIX.'_data WHERE city_id=? AND street_id=? AND number=?';
		$stmt=$DBC->query($query, array((int)$form_data['city_id']['value'], (int)$form_data['street_id']['value'], (int)$form_data['number']['value']));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if($ar['cnt']>0){
				$this->riseError('Такое объявление уже существует');
				return FALSE;
			}
		}
		return TRUE;
	}
	
	private function notifyAboutModerationNeed($id, $action='new'){
				
		/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
		$mailer = new Mailer();*/
		$subject = $_SERVER['SERVER_NAME'].': объявление требует модерации';
		$from = $this->getConfigValue('order_email_acceptor');
		$useremail = $this->getConfigValue('order_email_acceptor');
		$body='';
		if($action=='edit'){
			$body.='Было изменено объявление с ID '.$id.'<br />';
			$body.='Объявление снято с публикации и ожидает модерации.<br />';
		}else{
			$body.='Было добавлено объявление с ID '.$id.'<br />';
			$body.='Объявление ожидает модерации.<br />';
		}
		
		
		$body.=$this->getConfigValue('email_signature');
		/*if ( $this->getConfigValue('use_smtp') ) {
			$mailer->send_smtp($useremail, $from, $subject, $body, 1);
		} else {
			$mailer->send_simple($useremail, $from, $subject, $body, 1);
		}*/
		$this->sendFirmMail($useremail, $from, $subject, $body);
		return;
	}
	
	private function notifyUserAboutAdding($user_id ,$id, $topic_id){
		$DBC=DBC::getInstance();
		
		$useremail='';
		$fio='';
		$query='SELECT fio, email FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
		$stmt=$DBC->query($query, array($user_id));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$fio=$ar['fio'];
			$useremail=$ar['email'];
		}
		
		$translit_alias='';
		$query='SELECT translit_alias FROM '.DB_PREFIX.'_data WHERE id=? LIMIT 1';
		$stmt=$DBC->query($query, array($id));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$translit_alias=$ar['translit_alias'];
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		
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
		
		$href='http://'.$_SERVER['HTTP_HOST'].$href;
		/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
		$mailer = new Mailer();*/
		$subject = $_SERVER['SERVER_NAME'].': размещение объявления';
		$from = $this->getConfigValue('order_email_acceptor');
		$body='';
		$body.='Уважемый, '.$fio.'!<br />';
		$body.='Ваше объявление размещено.<br />';
		$body.='Адрес объявления <a href="'.$href.'">'.$href.'</a><br />';
		$body.=$this->getConfigValue('email_signature');
		/*if ( $this->getConfigValue('use_smtp') ) {
			$mailer->send_smtp($useremail, $from, $subject, $body, 1);
		} else {
			$mailer->send_simple($useremail, $from, $subject, $body, 1);
		}*/
		$this->sendFirmMail($useremail, $from, $subject, $body);
		return;
	}
	
	protected function removeTemporaryFields(&$model,$remove_this_names=array()){
		if(count($remove_this_names)>0){
			foreach($remove_this_names as $r){
				unset($model[$r]);
			}
		}
		return $model;
	}
	
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$DBC=DBC::getInstance();
		
		
		$data_model = new Data_Model();
		$model=$data_model->get_kvartira_model(false, true);
		
		
		
		$model = $data_model->init_model_data_from_db ( $table_name, $primary_key, $primary_key_value, $model[$table_name] );
		$uploads=array();
		$uploadify=false;
		foreach($model as $model_field){
			if($model_field['type']=='uploads' && !empty($model_field['value'])){
				foreach($model_field['value'] as $upload){
					$uploads[]=$upload['preview'];
					$uploads[]=$upload['normal'];
				}
			}elseif($model_field['type']=='uploadify_image'){
				$uploadify=true;
			}
		}
		
		
		$query = 'DELETE FROM '.DB_PREFIX.'_'.$table_name.' WHERE `'.$primary_key.'` = ?';
		$stmt=$DBC->query($query, array($primary_key_value));
		if(!$stmt){
			return false;
		}
		if(!empty($uploads)){
			foreach($uploads as $upload){
				@unlink(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$upload);
			}
		}
		
		if($uploadify){
			$imgs_ids=array();
			$query='SELECT image_id FROM '.DB_PREFIX.'_'.$table_name.'_image WHERE '.$primary_key.'=?';;
			$stmt=$DBC->query($query, array($primary_key_value));
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$imgs_ids[]=$ar['image_id'];
				}
			}
			
			if(count($imgs_ids)>0){
				foreach($imgs_ids as $im){
					$this->deleteImage($table_name, $im);
				}
			}
		}
	}
	
	/**
	 * Check access to data
	 * @param int $user_id
	 * @param int $data_id
	 * @return boolean
	 */
	function check_access_to_data ( $user_id, $data_id ) {
		$DBC=DBC::getInstance();
		if(1==(int)$this->getConfigValue('apps.realty.use_predeleting')){
			$query = "SELECT id FROM ".DB_PREFIX."_data WHERE user_id=? AND id=? AND is_predeleted=0";
		}else{
			$query = "SELECT id FROM ".DB_PREFIX."_data WHERE user_id=? AND id=?";
		}
	    
	    $stmt=$DBC->query($query, array($user_id, $data_id));
	    if($stmt){
	    	$ar=$DBC->fetch($stmt);
	    	if($ar['id'] > 0){
	    		return true;
	    	}
	    }
	    return false;
	}
	
	/**
	 * Return grid
	 * @param int $user_id user id
	 * @param int $current_category_id current category id
	 * @return string
	 */
	function grid ( $user_id, $current_category_id ) {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_tree=$Structure_Manager->get_category_tree_control($current_category_id, $user_id);
	    
        $rs .= '<div class="grids">';
        
        if(1==$this->getConfigValue('show_cattree_left')){
        	 
        	 $rs .= '<table border="0" width="99%" cellpadding="0" cellspacing="0">';
        
	        $rs .= '<tr>';
	        $rs .= '<td style="vertical-align: top;" id="lk_tree">';
	        	$rs .= $category_tree;
	        	$rs .= '</td>';
	        $rs .= '<td style="vertical-align: top;">';
	        $rs .= $this->get_data_grid($user_id, $current_category_id);
	        $rs .= '</td>';
	        $rs .= '</tr>';
	        
	        $rs .= '</table>';
        }else{
        	$rs .= $this->get_data_grid($user_id, $current_category_id);
        }
        
        
       
        $rs .= '</div>';
        
        $this->template->assert('category_tree_account',$category_tree);
        
        return $rs;
        
	}
	
	/**
	 * Get data grid
	 * @param int $user_id
	 * @return string
	 */
	function get_data_grid ( $user_id, $current_category_id = false) {
	    
        global $smarty;
        
        //require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        //$grid_constructor = new Grid_Constructor();
        $grid_constructor=$this->_grid_constructor;
        
        
        $params['topic_id'] = $this->getRequestValue('topic_id');
        $params['order'] = $this->getRequestValue('order');
        $params['region_id'] = $this->getRequestValue('region_id');
        $params['city_id'] = $this->getRequestValue('city_id');
        $params['district_id'] = $this->getRequestValue('district_id');
        $params['metro_id'] = $this->getRequestValue('metro_id');
        $params['street_id'] = $this->getRequestValue('street_id');
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['price'] = $this->getRequestValue('price');
        $params['active'] = $this->getRequestValue('active');
        $params['user_id'] = $user_id;
        $params['id'] = (int)$this->getRequestValue['id'];
        if((int)$this->getRequestValue('page_limit')!=0){
        	$params['page_limit'] = (int)$this->getRequestValue('page_limit');
        }
        $params['admin'] = true;
		if($this->getRequestValue('srch_export_cian')=='on' || $this->getRequestValue('srch_export_cian')=='1'){
			$params['srch_export_cian'] = 1;
		}
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        
        $res = $grid_constructor->get_sitebill_adv_ext( $params );
        $this->template->assign('grid_items', $res);
        //$this->template->assign('category_tree', $grid_constructor->get_category_tree( $params, $category_structure ) );
        //$this->template->assign('breadcrumbs', $grid_constructor->get_category_breadcrumbs( $params, $category_structure ) );
        //$grid_constructor->get_sales_grid($res);
        
        //$grid_constructor->main($params);
        $smarty->assign('admin', 1);
        $smarty->assign('topic_id', $params['topic_id']);
        
        $html = $smarty->fetch( "realty_grid_account.tpl" );
        return $html;
	}
	
	/**
	 * Get offer list
	 * @param int $user_id
	 * @param mixed $current_category_id
	 * @return mixed
	 */
	function getOfferList($user_id, $current_category_id = false){
		$ret=array();
		if ( $current_category_id ) {
		    $query='SELECT * FROM '.DB_PREFIX.'_data WHERE user_id='.$user_id.' and topic_id = '.$current_category_id;
		} else {
		    $query='SELECT * FROM '.DB_PREFIX.'_data WHERE user_id='.$user_id;
		}
		$this->db->exec($query);
		if($this->db->success){
			while($this->db->fetch_assoc()){
				$ret[]=$this->db->row;
			}
		}
        return $ret;
	}
	
	
	/**
	 * Add data
	 * @param array $form_data form data
	 * @return boolean
	 */
	function add_data ( $form_data ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
	    $account = new Account();
	    $user_balance = $account->getAccountValue($this->getSessionUserId());
	    
	    $form_data['price']['value']=str_replace(' ', '', $form_data['price']['value']);
	     
	    //check balance and cost of service
	    $need_money = 0;
	    if ( $this->getConfigValue('advert_cost') > 0 ) {
	        $need_money += $this->getConfigValue('advert_cost'); 
	    }
	    if ( $this->getConfigValue('special_advert_cost') > 0  and $form_data['hot']['value'] == 1 ) {
	        $need_money += $this->getConfigValue('special_advert_cost'); 
	    }
	    if ( $user_balance < $need_money ) {
	        $this->riseError('Недостаточно средств на счете для операции. <a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill_done&bill='.$need_money.'">Пополнить баланс на '.$need_money.' руб.</a>');
	        return false;
	    }
	    
	    
	    if(1==$this->getConfigValue('moderate_first')){
	    	$form_data['active']['value'] = 0;
	    }
	    
	    if(1==$this->getConfigValue('apps.geodata.try_encode') && 1==$this->getConfigValue('apps.geodata.enable')){
	    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
	    	$GA=new geodata_admin();
	    	$form_data=$GA->try_geocode($form_data);
	    }
	    
	    $query = $data_model->get_insert_query(DB_PREFIX.'_data', $form_data);
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	    	echo $this->db->error;
	    }
	    
	    
	    
	    $new_record_id = $this->db->last_insert_id();
	    
	    if(1==$this->getConfigValue('moderate_first')){
	    	$this->notifyAboutModerationNeed($new_record_id, 'new');
	    }
	    
	    $imgs=array();
	     
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads('data', $form_item, 'id', $new_record_id);
	    		if(is_array($ims) && count($ims)>0){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}
	    }
	     
	    $ims=$this->editImageMulti('data', 'data', 'id', $new_record_id);
	    if(is_array($ims) && count($ims)>0){
	    	$imgs=array_merge($imgs, $ims);
	    }
	    
		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && isset($form_data['translit_alias']) && $form_data['translit_alias']['value']==''){
	    	$this->saveTranslitAlias($new_record_id);
	    }
	    
		if($this->getConfigValue('is_watermark')){
			$filespath = SITEBILL_DOCUMENT_ROOT.'/img/data/';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.php';
			$Watermark=new Watermark();
			$Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
			$Watermark->setOffsets(array(
				$this->getConfigValue('apps.watermark.offset_left'),
				$this->getConfigValue('apps.watermark.offset_top'),
				$this->getConfigValue('apps.watermark.offset_right'),
				$this->getConfigValue('apps.watermark.offset_bottom')
			));
			if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
				$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark/';
				foreach($imgs as $v){
					copy($filespath.$v['normal'], $copy_folder.$v['normal']);
				}
			}
			if(!empty($imgs)){
				foreach($imgs as $v){
					$Watermark->printWatermark($filespath.$v['normal']);
				}
			}
		}
	    
	    if ( $new_record_id > 0 ) {
	        if ( $this->getConfigValue('advert_cost') > 0 ) {
	            $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('advert_cost'));
	        }
	        if ( $this->getConfigValue('special_advert_cost') > 0  and $form_data['hot']['value'] == 1 ) {
	            $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('special_advert_cost'));
	        }
	        
	        if($this->getConfigValue('apps.twitter.enable') && 1==(int)$this->getConfigValue('apps.twitter.allow_posting_from_account')){
	        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/twitter/admin/admin.php';
	        	$Twitter=new twitter_admin();
	        	$Twitter->sendTwit($new_record_id);
	        }
	        
	    }
	    return $new_record_id;
	    
	    //echo "new_record_id = $new_record_id<br>";
	    //echo $query;
	}
	
	/**
	 * Edit data
	 * @param array $form_data form data
	 * @return boolean
	 */
	function edit_data ( $form_data ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
	    $account = new Account();
	    $user_balance = $account->getAccountValue($this->getSessionUserId());
	    
	    $form_data['price']['value']=str_replace(' ', '', $form_data['price']['value']);
	     
	    $form_data_tmp = $form_data;
	    
	    //get prev state
        $form_data_tmp = $data_model->init_model_data_from_db ( 'data', 'id', $this->getRequestValue('id'), $form_data_tmp );
	    //if prev hot is 0 and new is 1, calculate money
	    if ( $form_data_tmp['hot']['value'] == 0 and  $form_data['hot']['value'] == 1 ) {
	        if ( $user_balance < $this->getConfigValue('special_advert_cost') ) {
	        	$this->riseError('Недостаточно средств на счете для операции. <a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill_done&bill='.$this->getConfigValue('special_advert_cost').'">Пополнить баланс на '.$this->getConfigValue('special_advert_cost').' руб.</a>');
	            return false;
	        } else {
	            $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('special_advert_cost'));
	        }
	    }
	    
	    if(1==$this->getConfigValue('moderate_first')){
	    	$form_data['active']['value'] = 0;
	    }
	    
	    if(1==$this->getConfigValue('apps.geodata.try_encode') && 1==$this->getConfigValue('apps.geodata.enable')){
	    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
	    	$GA=new geodata_admin();
	    	$form_data=$GA->try_geocode($form_data);
	    }
	    
	    $query = $data_model->get_edit_query(DB_PREFIX.'_data', 'id', (int)$this->getRequestValue('id'), $form_data);
	    
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	    	echo $this->db->error;
	    }
	    
	    if(1==$this->getConfigValue('moderate_first')){
	    	$this->notifyAboutModerationNeed($this->getRequestValue('id'), 'edit');
	    }
	    
	    $imgs=array();
	    
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads('data', $form_item, 'id', (int)$this->getRequestValue('id'));
	    		if(is_array($ims) && count($ims)>0){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}
	    }
	    	
	    $ims=$this->editImageMulti('data', 'data', 'id', (int)$this->getRequestValue('id'));
	    if(is_array($ims) && count($ims)>0){
	    	$imgs=array_merge($imgs, $ims);
	    }
	    
	    
	    
	    if(1==$this->getConfigValue('apps.seo.data_alias_enable') && isset($form_data['translit_alias']) && $form_data['translit_alias']['value']==''){
	    	$this->saveTranslitAlias($this->getRequestValue('id'));
	    }
	    
		if($this->getConfigValue('is_watermark')){
			$filespath = SITEBILL_DOCUMENT_ROOT.'/img/data/';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.php';
			$Watermark=new Watermark();
			$Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
			$Watermark->setOffsets(array(
					$this->getConfigValue('apps.watermark.offset_left'),
					$this->getConfigValue('apps.watermark.offset_top'),
					$this->getConfigValue('apps.watermark.offset_right'),
					$this->getConfigValue('apps.watermark.offset_bottom')
			));
			if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
				$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark/';
				foreach($imgs as $v){
					copy($filespath.$v['normal'], $copy_folder.$v['normal']);
				}
			}
			if(!empty($imgs)){
				foreach($imgs as $v){
					//$Watermark->printWatermark($filespath.$v['preview']);
					$Watermark->printWatermark($filespath.$v['normal']);
				}
			}
		}
	    
	    //$new_record_id = $this->db->last_insert_id();
	    //echo "new_record_id = $new_record_id<br>";
	    //echo $query;
	}
	
	/**
	 * Check data
	 * @param array $form_data
	 * @return boolean
	 */
	function check_data ( $form_data ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    if ( !$data_model->check_data($form_data) ) {
	        $this->riseError($data_model->GetErrorMessage());
	        return false;
	    }
	    if ( $this->getConfigValue('apps.billing.enable') ) {

	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
	    	$Account = new Account;
	    	$company_profile = $Account->get_company_profile($form_data['user_id']['value']);
	    	
	    	if ( $company_profile['limit_special_left']['value'] < 1 and $form_data['hot']['value'] == 1 ) {
	    		$this->riseError('Превышен лимит спецпредложений');
	    		return false;
	    	}
	    	if ( $company_profile['limit_best_left']['value'] < 1 and $form_data['best']['value'] == 1 ) {
	    		$this->riseError('Превышен лимит лучших предложений');
	    		return false;
	    	}
	    	
	    }
	    return true;
	}
	
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
		if ( $this->getRequestValue('do') != 'new' and $this->getRequestValue('do') != 'edit'  ) {
			$rs = '';
			$rs .= '<a class="btn btn-primary" href="'.SITEBILL_MAIN_URL.'/account/data/?do=new">Добавить объявление</a>';
			$rs .= '<div class="clear"></div>';
			//$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
			return $rs;
		}
		return '';
	}
   
   
	
	function get_form ( $form_data=array(), $do = 'new' ) {
		$_SESSION['allow_disable_root_structure_select']=true;
		if(1==$this->getConfigValue('divide_step_form')){
			return $this->_get_form_step_divided($form_data, $do);
		}else{
			return $this->_get_form_standart($form_data, $do);
		}
	}
	
	function getSteps($form_data,$step){
	
		$default_tab_name=$this->getConfigValue('default_tab_name');
		$tabs=array($default_tab_name);
			
		foreach ( $form_data as $item_id => $item_array ) {
			if(isset($item_array['tab']) && $item_array['tab']!=''){
				$tabs[$item_array['tab']]=$item_array['tab'];
			}
	
		}
		$tabs_array=array();
		$i=1;
		foreach($tabs as $t){
			if($i < $step){
				$tabs_array[$i]=array('name'=>$t, 'step'=>$i, 'status'=>'done');
			}elseif($i==$step){
				$tabs_array[$i]=array('name'=>$t, 'step'=>$i, 'status'=>'current');
			}else{
				$tabs_array[$i]=array('name'=>$t, 'step'=>$i, 'status'=>'further');
			}
			$i++;
		}
		return $tabs_array;
	}
	
	function _get_form_step_divided($form_data=array(), $do = 'new', $language_id = 0, $button_title = ''){
	
	
	
		//$step=(int)$this->getRequestValue('step')
		$requesturi=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
		if(SITEBILL_MAIN_URL!=''){
			preg_replace('/^'.trim(SITEBILL_MAIN_URL,'/').'/','',$requesturi);
		}
		if(preg_match('/step(\d+)$/',$requesturi,$matches)){
			$step=(int)$matches[1];
		}else{
			$step=1;
		}
		//echo $step;
	
		$steps_names=$this->getSteps($form_data,$step);
		$last_step=$steps_names[count($steps_names)]['name'];
	
		if(isset($form_data['captcha'])){
			$form_data['captcha']['tab']=$last_step;
		}
	
		foreach($form_data as $k=>$v){
			if($v[type]=='uploadify_image'){
				$form_data[$k]['tab']=$last_step;
			}
		}
		$steps_names=$this->getSteps($form_data,$step);
		
		$steps_total=count($steps_names);
	
	
		$Sitebill_Registry=Sitebill_Registry::getInstance();
		$Sitebill_Registry->addFeedback('divide_step_form',true);
		$Sitebill_Registry->addFeedback('step',$step);
		
		
		global $smarty;
		$el=array();
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		 
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
		$account = new Account();
		$account_value = $account->getAccountValue($this->getSessionUserId());
		$rs .= '<div class="clear"></div>';
		$rs .= $this->get_ajax_functions();
		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		//$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/account/data/">';
		if(1==$this->getConfigValue('use_combobox')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/mycombobox.js"></script>';
			$rs .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/css/mycombobox.css" />';
		}
		
		$el = $form_generator->compile_form_elements($form_data);
		
		$topic_id=(int)$form_data['topic_id']['value'];
		$current_id=(int)$form_data['id']['value'];
		
		if($topic_id!=0 && $current_id!=0){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
			$Structure_Manager = new Structure_Manager();
			$category_structure = $Structure_Manager->loadCategoryStructure();
			 
			if(1==$this->getConfigValue('apps.seo.level_enable')){
			  
				if($category_structure['catalog'][$topic_id]['url']!=''){
					$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
				}else{
					$parent_category_url='';
				}
			}else{
				$parent_category_url='';
			}
			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $form_data['translit_alias']['value']!=''){
				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$form_data['translit_alias']['value'];
			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$current_id.'.html';
			}else{
				$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$current_id;
			}
			 
			$rs .= '<a class="btn btn-success pull-right" href="'.$href.'" target="_blank">'.Multilanguage::_('L_SEE_AT_SITE').'</a>';
			 
		}
		
		if($step < $steps_total){
			$rs .= '<form id="step_form" method="post" action="'.SITEBILL_MAIN_URL.'/account/data/step'.(1+$step).'" enctype="multipart/form-data" class="user_add_form">';
		}else{
			$rs .= '<form id="step_form" method="post" action="'.SITEBILL_MAIN_URL.'/account/data/step'.$steps_total.'" enctype="multipart/form-data" class="user_add_form">';
		}
		 
		if ( $this->getConfigValue('advert_cost') > 0 and ($do == 'new' or $do == 'new_done' ) ) {
		
			$rs .= '<p><b>Стоимость размещения одного объявления '.$this->getConfigValue('advert_cost').' '.$this->getConfigValue('ue_name').'</b></p>';
		
			if ( $account_value <  $this->getConfigValue('advert_cost') ) {
				$rs .= '<p>Ваш баланс '.$account_value.' '.$this->getConfigValue('ue_name').'</p>';
				$rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill">пополнить</a></b></td>';
				return $rs;
			}
		}
		
		
		
		if ( $this->getError() ) {
			$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
		}
		
		
		
		$el['form_header']=$rs;
		$el['form_footer']='</form>';
		
		
		if($step < $steps_total){
			if ( $do == 'new' ) {
				$el['private'][]=array('html'=>'<input type="hidden" name="do" value="new" />');
			} else {
				$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit" />');
			}
		}else{
			if ( $do == 'new' ) {
				$el['private'][]=array('html'=>'<input type="hidden" name="do" value="new_done" />');
			} else {
				$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
			}
		}
		
		
		if($step>1){
			$el['controls']['back']=array('html'=>'<input type="submit" name="submit" alt="'.($step-1).'" id="formsubmit_back" value="Назад" />');
		}
		
		if($step < $steps_total){
			$button_title='Следующий шаг';
		}else{
			$button_title='Сохранить';
		}
		
		$el['controls']['submit']=array('html'=>'<input type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="'.$button_title.'" />');
		
		
		$smarty->assign('current_step',$step);
		$smarty->assign('divide_by_step',1);
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		
		return $smarty->fetch($tpl_name);
	
	}
	
	function _get_form_standart($form_data=array(), $do = 'new', $language_id = 0, $button_title = ''){
	
		global $smarty;
		$el=array();
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
	    $account = new Account();
	    $account_value = $account->getAccountValue($this->getSessionUserId());
        $rs .= '<div class="clear"></div>';
	    $rs .= $this->get_ajax_functions();
	    $rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
	    //$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/form_tabs.js"></script>';
	    //$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/css/form_tabs.css" />';
	    //$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/css/form_decorator.css" />';
	    if(1==$this->getConfigValue('use_combobox')){
	    	$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/mycombobox.js"></script>';
	    	$rs .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/css/mycombobox.css" />';
	    }
	    
	    
	    $topic_id=(int)$form_data['topic_id']['value'];
	    $current_id=(int)$form_data['id']['value'];
	     
	    if($topic_id!=0 && $current_id!=0){
	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
	    	$Structure_Manager = new Structure_Manager();
	    	$category_structure = $Structure_Manager->loadCategoryStructure();
	    	 
	    	if(1==$this->getConfigValue('apps.seo.level_enable')){
	    
	    		if($category_structure['catalog'][$topic_id]['url']!=''){
	    			$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
	    		}else{
	    			$parent_category_url='';
	    		}
	    	}else{
	    		$parent_category_url='';
	    	}
	    	if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $form_data['translit_alias']['value']!=''){
	    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$form_data['translit_alias']['value'];
	    	}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
	    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$current_id.'.html';
	    	}else{
	    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$current_id;
	    	}
	    	 
	    	$rs .= '<a class="btn btn-success" href="'.$href.'" target="_blank">'.Multilanguage::_('L_SEE_AT_SITE').'</a>';
	    
	    }
	    
	    $rs .= '<form method="post" class="form-horizontal" action="'.SITEBILL_MAIN_URL.'/account/data/" enctype="multipart/form-data">';
	    
		if ( $this->getConfigValue('advert_cost') > 0 and ($do == 'new' or $do == 'new_done' ) ) {
		    
			$rs .= '<p><b>Стоимость размещения одного объявления '.$this->getConfigValue('advert_cost').' '.$this->getConfigValue('ue_name').'</b></p>';
		    
		    if ( $account_value <  $this->getConfigValue('advert_cost') ) {
		        $rs .= '<p>Ваш баланс '.$account_value.' '.$this->getConfigValue('ue_name').'</p>';
		        $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="'.SITEBILL_MAIN_URL.'/account/balance/?do=add_bill">пополнить</a></b></td>';
		        return $rs;
	        }
		}
				
		
		
		if ( $this->getError() ) {
			$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
		}
		
		$el = $form_generator->compile_form_elements($form_data);
		
		$el['form_header']=$rs;
		
		$el['form_footer']='</form>';
		
		//$el['form_footer']='</form>';
		
		if ( $do == 'new' ) {
			$el['private'][]=array('html'=>'<input type="hidden" name="do" value="new_done" />');
		} else {
			$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
			$el['private'][]=array('html'=>'<input type="hidden" name="id" value="'.$form_data['id']['value'].'">');
		}
		
		$el['controls']['submit']=array('html'=>'<input class="btn btn-primary" type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="Сохранить" />');
		 
		$smarty->assign('do',$do);
		$smarty->assign('id',$form_data['id']['value']);
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_front.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_front.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form_front.tpl';
		}
		return $smarty->fetch($tpl_name);
		
	}
	
protected function createTranslitAliasByFields($id, $fields_for_alias){
		$alias='';
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data_shared = $data_model->get_kvartira_model(false, true);
		
		$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared['data'], true );
		$values=array();
		foreach($fields_for_alias as $v){
			$key=trim($v);
			if(isset($form_data_shared[$key])){
				if(($form_data_shared[$key]['type']=='select_box_structure' || $form_data_shared[$key]['type']=='select_by_query' || $form_data_shared[$key]['type']=='select_box') && $form_data_shared[trim($v)]['value_string']!='' ){
					$values[]=$form_data_shared[trim($v)]['value_string'];
				}elseif($form_data_shared[trim($v)]['value']!=''){
					$values[]=$form_data_shared[trim($v)]['value'];
				}
			}
			
		}
		if(!empty($values)){
			foreach ($values as $k=>$v){
				$values[$k]=$this->transliteMe($v);
			}
			$alias=implode('-', $values);
		}
		return $alias;
	}
	
	protected function makeUniqueAlias($alias, $id){
		$is_similar_alias_exists=false;
		$DBC=DBC::getInstance();
		$query="SELECT COUNT(*) AS cnt FROM ".DB_PREFIX."_data WHERE translit_alias=? AND id<>? ORDER BY translit_alias DESC LIMIT 1";
		$stmt=$DBC->query($query, array($alias, $id));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if((int)$ar['cnt']>0){
				$is_similar_alias_exists=true;
			}
		}
		
		if($is_similar_alias_exists){
			$query="SELECT translit_alias FROM ".DB_PREFIX."_data WHERE translit_alias LIKE '".$alias."%' AND id<>? ORDER BY translit_alias DESC LIMIT 1";
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				if(preg_match('/'.$alias.'-(\d+)/', $ar['translit_alias'], $matches)){
					$alias.='-'.((int)$matches[1]+1);
				}else{
					$alias.='-1';
				}
			}
			
		}
		//echo $alias;
		return $alias;
		
	}
	
	protected function saveTranslitAlias($id){
		
		
		$new_alias='';
		if(1==$this->getConfigValue('apps.seo.allow_custom_realty_aliases')){
			
			$DBC=DBC::getInstance();
			$query='SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$old_alias=$ar['translit_alias'];
			}
			
			if($old_alias==''){
				
				if(''!=$this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')){
					
					$new_alias=$this->createTranslitAliasByFields($id, explode(',',$this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')));
					
				}
				
				if(''!=$new_alias){
					$new_alias=$this->makeUniqueAlias($new_alias, $id);
				}
			}
		}
		
		/*
		if(1==$this->getConfigValue('apps.seo.allow_custom_realty_aliases')){
			$old_alias='';
			$fields_for_alias=explode(',', $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields'));
			$DBC=DBC::getInstance();
			$query='SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$old_alias=$ar['translit_alias'];
			}
			if($old_alias==''){
				if(count($fields_for_alias)>0 && $fields_for_alias[0]!='');
			}
		}*/
		
		
		if($new_alias==''){
			$DBC=DBC::getInstance();
			$new_alias=$this->createTranslitAliasByFields($id, array('city_id', 'street_id', 'number'));
			if(''!=$new_alias){
				$new_alias=$this->makeUniqueAlias($new_alias, $id);
			}
			/*$query='SELECT re_city.name AS city, re_street.name AS street, re_data.number
				FROM re_data
				LEFT JOIN re_city ON re_city.city_id=re_data.city_id
				LEFT JOIN re_street ON re_street.street_id=re_data.street_id
				WHERE re_data.id='.$id;
			$stmt=$DBC->query($query);
			//this->db->exec($query);
			if($stmt){
				$p=array();
				$this->db->fetch_assoc();
				if($this->db->row['city']!=''){
					$p[]=$this->transliteMe($this->db->row['city']);
				}
				if($this->db->row['street']!=''){
					$p[]=$this->transliteMe($this->db->row['street']);
				}
				if((int)$this->db->row['number']!=0){
					$p[]=(int)$this->db->row['number'];
				}
				if(!empty($p)){
					$alias=implode('-',$p);
					$q="SELECT translit_alias FROM ".DB_PREFIX."_data WHERE translit_alias LIKE '".$alias."%' AND id<>".$id." ORDER BY translit_alias DESC LIMIT 1";
			
					$this->db->exec($q);
					$this->db->fetch_assoc();
			
					if($this->db->row['translit_alias']!=''){
						if(preg_match('/'.$alias.'-(\d+)/',$this->db->row['translit_alias'],$matches)){
							$alias.='-'.((int)$matches[1]+1);
						}else{
							$alias.='-1';
						}
					}
			
					$query='UPDATE re_data SET translit_alias=\''.$alias.'\' WHERE id='.$id;
					$this->db->exec($query);
				}
			
			}*/
		}
		
		$query='UPDATE re_data SET translit_alias=? WHERE id=?';
		$stmt=$DBC->query($query, array($new_alias, $id));
		
		
		
		
	
	}
	
}
?>
