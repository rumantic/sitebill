<?php
class Client_Order extends client_site {
	
	public function makeClientOrder($order_model){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->loadOrderModel($order_model);
		
		if(empty($form_data)){
			return false;
		}
		
		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
				$form_data = $data_model->init_model_data_from_request($form_data);
				$new_values=$this->getRequestValue('_new_value');
				if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
					$remove_this_names=array();
					foreach($form_data as $fd){
						if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
							$id=md5(time().'_'.rand(100,999));
							$remove_this_names[]=$id;
							$form_data[$id]['value'] = $new_values[$fd['name']];
							$form_data[$id]['type'] = 'auto_add_value';
							$form_data[$id]['dbtype'] = 'notable';
							$form_data[$id]['value_table'] = $form_data[$fd['name']]['primary_key_table'];
							$form_data[$id]['value_primary_key'] = $form_data[$fd['name']]['primary_key_name'];
							$form_data[$id]['value_field'] = $form_data[$fd['name']]['value_name'];
							$form_data[$id]['assign_to'] = $fd['name'];
							$form_data[$id]['required'] = 'off';
							$form_data[$id]['unique'] = 'off';
						}
					}
				}
				$data_model->forse_auto_add_values($form_data);
				if ( !$this->check_data( $form_data ) || (1==$this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data))  ) {
					$form_data=$this->removeTemporaryFields($form_data, $remove_this_names);
					$rs = $this->get_form($form_data, 'new', 0, Multilanguage::_('L_TEXT_SEND'));
						
				} else {
					
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
					$table_view = new Table_View();
					$order_table = '';
					$order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
					$order_table .= $table_view->compile_view($form_data);
					$order_table .= '</table>';
					
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
						
					$mailer = new Mailer();*/
					$subject = $_SERVER['SERVER_NAME'].': Новая заявка от клиента на расчет';
					$to = $this->get_email_list();
					$from = $this->getConfigValue('order_email_acceptor');
						
					/*if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $order_table, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $order_table, 1);
					}*/
					$this->sendFirmMail($to, $from, $subject, $order_table);
					
					if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/client/client.xml') ) {
	    				require_once ((SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/admin.php'));
	    				$client_admin = new client_admin();
	    			
	    				$client_admin->data_model['client']['type_id']['value'] = $order_model;
	    				$client_admin->data_model['client']['status_id']['value'] = 'new';
	    				$client_admin->data_model['client']['date']['value'] = time();
	    				$client_admin->data_model['client']['fio']['value'] = $form_data['fio']['value'];
	    				$client_admin->data_model['client']['email']['value'] = $form_data['email']['value'];
	    				$client_admin->data_model['client']['phone']['value'] = $form_data['phone']['value'];
	    				unset($form_data['fio']);
	    				unset($form_data['email']);
	    				unset($form_data['phone']);
	    				$client_admin->data_model['client']['order_text']['value'] = $order_table;
	    			
	    				$client_admin->add_data($client_admin->data_model['client']);
	    				if ( $client_admin->getError() ) {
	    					$rs = $client_admin->GetErrorMessage();
	    				}else{
	    					if(1==$this->getConfigValue('apps.client.allow-redirect_url_for_orders')){
	    						header('location: '.SITEBILL_MAIN_URL.'/client/order/'.$order_model.'/online-'.$order_model.'/');
	    					}else{
	    						$rs = '<div class="alert alert-success">'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT').'</div>';
	    					}
	    					
	    				}
	    			}
				}
				break;
			}
			default : {
				$rs = $this->get_form($form_data, 'new', 0, Multilanguage::_('L_TEXT_SEND'));
				break;
			}
		}
		return $rs;
	}
	
	
	
	
	function save_order_form($order_model){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data=$this->loadOrderModel($order_model);
		$form_data = $data_model->init_model_data_from_request($form_data);
		$new_values=$this->getRequestValue('_new_value');
		if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
			$remove_this_names=array();
			foreach($form_data as $fd){
				if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
					$id=md5(time().'_'.rand(100,999));
					$remove_this_names[]=$id;
					$form_data[$id]['value'] = $new_values[$fd['name']];
					$form_data[$id]['type'] = 'auto_add_value';
					$form_data[$id]['dbtype'] = 'notable';
					$form_data[$id]['value_table'] = $form_data[$fd['name']]['primary_key_table'];
					$form_data[$id]['value_primary_key'] = $form_data[$fd['name']]['primary_key_name'];
					$form_data[$id]['value_field'] = $form_data[$fd['name']]['value_name'];
					$form_data[$id]['assign_to'] = $fd['name'];
					$form_data[$id]['required'] = 'off';
					$form_data[$id]['unique'] = 'off';
				}
			}
		}
		$data_model->forse_auto_add_values($form_data);
		if ( !$this->check_data( $form_data )) {
			$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'check_error: '.$this->GetErrorMessage(), 'type' => ERROR));
				
			return json_encode(array('status'=>'error', 'message'=>$this->GetErrorMessage()));
			$form_data=$this->removeTemporaryFields($form_data, $remove_this_names);
			$rs = $this->get_form($form_data, 'new', 0, Multilanguage::_('L_TEXT_SEND'));
				
		} else {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
			$table_view = new Table_View();
			$order_table = '';
			$order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
			$order_table .= $table_view->compile_view($form_data);
			$order_table .= '</table>';
				
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/client/client.xml') ) {
				require_once ((SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/admin.php'));
				$client_admin = new client_admin();
			
				$client_admin->data_model['client']['type_id']['value'] = $order_model;
				$client_admin->data_model['client']['status_id']['value'] = 'new';
				$client_admin->data_model['client']['date']['value'] = time();
				$client_admin->data_model['client']['fio']['value'] = $form_data['fio']['value'];
				$client_admin->data_model['client']['email']['value'] = $form_data['email']['value'];
				$client_admin->data_model['client']['phone']['value'] = $form_data['phone']['value'];
				unset($form_data['fio']);
				unset($form_data['email']);
				unset($form_data['phone']);
				$client_admin->data_model['client']['order_text']['value'] = $order_table;
			
				$client_admin->add_data($client_admin->data_model['client']);
				$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'add client record', 'type' => NOTICE));
				
				if ( $client_admin->getError() ) {
					$rs = $client_admin->GetErrorMessage();
					$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'client_add_error: '.$client_admin->GetErrorMessage(), 'type' => ERROR));
					return json_encode(array('status'=>'error', 'message'=>'<div class="alert alert-success">'.$client_admin->GetErrorMessage().'</div>'));
				}else{
						
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					
					$mailer = new Mailer();*/
						
					$subject = $_SERVER['SERVER_NAME'].': Новая заявка от клиента / '.$client_admin->data_model['client']['type_id']['select_data'][$order_model];
					$to = $this->get_email_list();
						
					$from = $this->getConfigValue('order_email_acceptor');
					$order_mail_body=$order_table;
					$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'send_email to'.$to, 'type' => NOTICE));
					$this->sendFirmMail($to, $from, $subject, $order_mail_body);
					/*if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $order_mail_body, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $order_mail_body, 1);
					}*/
					return json_encode(array('status'=>'ok', 'message'=>'<div class="alert alert-success">'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT').'</div>'));
				}
			}else{
				
			}
			
		}
	}
	
	function get_order_form($model_name, $options=array()){
		$form_data=$this->loadOrderModel($model_name);
		if(!empty($options)){
			foreach ($options as $k=>$opt){
				if(isset($form_data[$k])){
					$form_data[$k]['value']=htmlspecialchars($opt);
				}
			}
		}
		//return $this->get_form($form_data, 'new');
		
		
		$_SESSION['allow_disable_root_structure_select']=true;
		global $smarty;
		if($button_title==''){
			$button_title = Multilanguage::_('L_TEXT_SEND');
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
		
		$rs .= $this->get_ajax_functions();
		if(1==$this->getConfigValue('apps.geodata.enable')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		}
		$rs .= '<form method="post" class="form-horizontal" action="" enctype="multipart/form-data" id="client_form">';
		
		if ( $this->getError() ) {
			$smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
		}
		
		$el = $form_generator->compile_form_elements($form_data);
		
		
		$el['form_header']=$rs;
		$el['form_footer']='</form>';
		
		/*if ( $do != 'new' ) {
		 $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
		}*/
		$el['controls']['submit']=array('html'=>'<input type="submit" class="btn btn-primary" value="'.$button_title.'">');
		
		
		
		
		
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		return $smarty->fetch($tpl_name);
		
		
		//return $this->get_form($form_data, 'new');
	}
	
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
		$_SESSION['allow_disable_root_structure_select']=true;
		global $smarty;
		if($button_title==''){
			$button_title = Multilanguage::_('L_TEXT_SAVE');
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	
	
		$rs .= $this->get_ajax_functions();
		if(1==$this->getConfigValue('apps.geodata.enable')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		}
		$rs .= '<form method="post" class="form-horizontal" action="" enctype="multipart/form-data" id="client_form">';
	
		if ( $this->getError() ) {
			$smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
		}
	
		$el = $form_generator->compile_form_elements($form_data);
	
		if ( $do == 'new' ) {
			$el['private'][]=array('html'=>'<input type="hidden" name="do" value="new_done" />');
			$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'" />');
		} else {
			$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
			$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'" />');
		}
		$el['private'][]=array('html'=>'<input type="hidden" name="action" value="'.$this->action.'">');
		$el['private'][]=array('html'=>'<input type="hidden" name="language_id" value="'.$language_id.'">');
		$el['private'][]=array('html'=>'<input type="hidden" name="topic_id" id="topic_id" value="'.$this->client_topic_id.'">');
	
		$el['form_header']=$rs;
		$el['form_footer']='</form>';
	
		/*if ( $do != 'new' ) {
		 $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
		}*/
		$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
	
	
	
	
	
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		return $smarty->fetch($tpl_name);
	}
	
	private function loadOrderModel($model_name){
		
		$DBC=DBC::getInstance();
		$query='SELECT COUNT(table_id) AS cnt FROM '.DB_PREFIX.'_table WHERE name=?';
		$stmt=$DBC->query($query, array($model_name));
		if(!stmt){
			return false;
		}
		
		$ar=$DBC->fetch($stmt);
		if($ar['cnt']==0){
			return false;
		}
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
			$ATH=new Admin_Table_Helper();
			$form_data=$ATH->load_model($model_name, $ignore_user_group);
			if($form_data){
				$form_data = $ATH->add_ajax($form_data);
			}
			
		}
		
		if(!$form_data){
			return false;
		}
		
		
		if(!isset($form_data[$model_name]['fio'])){
			$form_data[$model_name]['fio']['name'] = 'fio';
			$form_data[$model_name]['fio']['title'] = 'ФИО';
			$form_data[$model_name]['fio']['value'] = '';
			$form_data[$model_name]['fio']['length'] = 40;
			$form_data[$model_name]['fio']['type'] = 'safe_string';
			$form_data[$model_name]['fio']['required'] = 'off';
			$form_data[$model_name]['fio']['unique'] = 'off';
		}
		
		if(!isset($form_data[$model_name]['phone'])){
			$form_data[$model_name]['phone']['name'] = 'phone';
			$form_data[$model_name]['phone']['title'] = 'Телефон';
			$form_data[$model_name]['phone']['value'] = '';
			$form_data[$model_name]['phone']['length'] = 40;
			$form_data[$model_name]['phone']['type'] = 'safe_string';
			$form_data[$model_name]['phone']['required'] = 'off';
			$form_data[$model_name]['phone']['unique'] = 'off';
		}
		
		if(!isset($form_data[$model_name]['email'])){
			$form_data[$model_name]['email']['name'] = 'email';
			$form_data[$model_name]['email']['title'] = 'E-mail';
			$form_data[$model_name]['email']['value'] = '';
			$form_data[$model_name]['email']['length'] = 40;
			$form_data[$model_name]['email']['type'] = 'safe_string';
			$form_data[$model_name]['email']['required'] = 'off';
			$form_data[$model_name]['email']['unique'] = 'off';
		}
		
		return $form_data[$model_name];
	}
	
}