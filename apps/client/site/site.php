<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Client multiorder frontend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class client_site extends client_admin {
	function frontend () {
		
		$reserved_urls=array('ciodart'=>'raschet');
		
		if ( !$this->getConfigValue('apps.client.enable') ) {
			return false;
		}
		
		$REQUESTURIPATH=$this->getClearRequestURI();
		
		$this->root_url=SITEBILL_MAIN_URL.'/'.($this->getConfigValue('apps.client.namespace')!='' ? $this->getConfigValue('apps.client.namespace').'/' : '');
		
		
		
		if(!key_exists($REQUESTURIPATH, $reserved_urls) && ($this->getConfigValue('apps.client.namespace')!='' && !preg_match('/^'.$this->getConfigValue('apps.client.namespace').'/', $REQUESTURIPATH))){
			return false;
		}
		
		
		
		if($this->getConfigValue('apps.client.namespace')!=''){
			$clearrequesturi=trim(preg_replace('/^'.$this->getConfigValue('apps.client.namespace').'/', '', $REQUESTURIPATH), '/');
		}else{
			$clearrequesturi=$requesturi;
		}
		
		//$action=$this->getRequestValue('subaction')
		
		if(preg_match('/^order\/(\w+)[\/]?$/', $clearrequesturi, $matches) || key_exists($REQUESTURIPATH, $reserved_urls)){
			
			if(key_exists($REQUESTURIPATH, $reserved_urls)){
				$order_model=$reserved_urls[$REQUESTURIPATH];
			}else{
				$order_model=$matches[1];
			}
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/client_order.php';
			$Client_Order=new Client_Order();
			
			$page_url='client_order_'.$order_model;
			//echo $page_url;
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/page/admin/admin.php';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/page/site/site.php';
			$PA=new page_site();
			$pageInfo=$PA->getPageByURI($page_url);
			if(is_array($pageInfo) && !empty($pageInfo)){
				$this->template->assert('title', $pageInfo['title']);
				$this->template->assert('meta_title', $pageInfo['meta_title']);
				$this->template->assert('meta_keywords', $pageInfo['meta_keywords']);
				$this->template->assert('meta_description', $pageInfo['meta_description']);
				$this->template->assert('pagebody', $pageInfo['body']);
			}
			//print_r($PA->getPageByURI($page_url));
			$form=$Client_Order->makeClientOrder($order_model);
			if(!$form){
				return false;
			}
			
			
			$this->template->assign('form', $form);
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/client_order_'.$order_model.'.tpl')){
				$this->template->assign('main_file_tpl', 'client_order_'.$order_model.'.tpl');
				//$this->set_apps_template('client', $this->getConfigValue('theme'), 'main_file_tpl', 'client_order_'.$order_model.'.tpl');
			}else{
				$this->set_apps_template('client', $this->getConfigValue('theme'), 'main_file_tpl', 'client_order.tpl');
			}
			
			return true;
		}
		
		if(1==$this->getConfigValue('apps.client.allow-redirect_url_for_orders') && preg_match('/^order\/(\w+)\/online-(\w+)[\/]?$/', $clearrequesturi, $matches)){
			
			if($matches[1]==$matches[2]){
				$this->template->assign('form', '<div class="alert alert-success">'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT').'</div>');
				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/client_order_'.$order_model.'.tpl')){
					$this->template->assign('main_file_tpl', 'client_order_'.$order_model.'.tpl');
				}else{
					$this->set_apps_template('client', $this->getConfigValue('theme'), 'main_file_tpl', 'client_order.tpl');
				}
				return true;
			}
		}
		
		$app_alias=$this->getConfigValue('apps.client.namespace');
		
		$breadcrumbs=array();
		$breadcrumbs[]= array('href'=>(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/', 'title' => Multilanguage::_('L_HOME'));
		$breadcrumbs[]= array('href'=>(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/'.$app_alias.'/', 'title' => $this->getConfigValue('apps.client.folder_title'), 'last' => 'true');
		$this->template->assert('breadcrumbs_array', $breadcrumbs);
		
		
		$page_array = $this->getPageByURI('client_text');
		if ( $page_array ) {
			$this->template->assert('client_text', $page_array['body']);
			$this->template->assert('title', $page_array['title']);
			$this->template->assert('meta_keywords', $page_array['meta_keywords']);
			$this->template->assert('meta_description', $page_array['meta_description']);
		}
		
		$draw_form = true;
		/*
		if ( preg_match('/fiz/', $requesturi) ) {
			$this->set_client_topic_id(1);
		} elseif ( preg_match('/ur/', $requesturi) ) {
			$this->set_client_topic_id(6121);
		} else {
			$draw_form = false;
		}
		*/
		if ( $draw_form ) {
			$this->template->assign('form',$this->drawForm());
			$this->set_apps_template('client', $this->getConfigValue('theme'), 'main_file_tpl', 'form.tpl.html');
		} else {
			$this->set_apps_template('client', $this->getConfigValue('theme'), 'main_file_tpl', 'choose_type.tpl.html');
		}
		
		//echo 'client frontend';
		return true;
		
	}
	
	
	
	public function get_email_list () {
		$email='';
		$DBC=DBC::getInstance();
		$query = 'SELECT email FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
		$stmt=$DBC->query($query, array($this->getAdminUserId()));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$email[$ar['email']]=$ar['email'];
		}
		
		$query = "select email from ".DB_PREFIX."_user where notify='1' limit 1";
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if($ar['email']!=''){
				$email[$ar['email']]=$ar['email'];
			}
		}
		$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'Get email = '.$email, 'type' => NOTICE));
		
		return array_values($email);
	}

	private function drawForm(){
		global $smarty;
		
		$data_model = $this->model;
		$form_data = $this->data_model;
		 
		 
		 
		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
				$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				$data_model->forse_auto_add_values($form_data[$this->table_name]);
				if ( !$this->check_data( $form_data[$this->table_name] ) ) {
					$rs = $this->get_form($form_data[$this->table_name]);
				} else {
					unset($form_data[$this->table_name]['captcha']);
					$new_record_id=$this->add_data($form_data[$this->table_name]);
					if ( $this->getError() ) {
						$rs = $this->get_form($form_data[$this->table_name]);
					} else {
						if ( $this->getRequestValue('topic_id') == 1 ) {
							$type_id = 'fiz';
						} elseif ( $this->getRequestValue('topic_id') == 6121 ) {
							$type_id = 'ur';
						} else {
							$type_id = 'usual';
						}
						$query = "update ".DB_PREFIX."_client set date=".time().", type_id='".$type_id."', status_id='new' where client_id=".$new_record_id;
						$this->db->exec($query);
						foreach($form_data as $hvd){
							if($hvd['tab']==''){
								$hvd_tabbed[$this->getConfigValue('default_tab_name')][]=$hvd;
							}else{
								$hvd_tabbed[$hvd['tab']][]=$hvd;
							}
						
						}
						/*
						echo '<pre>';
						print_r($hvd_tabbed);
						echo '</pre>';
						*/
						
						 
						$this->template->assert('form_data',$form_data);

						$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/apps/client/site/template/';
						$order_mail_body = $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/client/site/template/order_mail.tpl.html');
						$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme');
						
						/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
						
						$mailer = new Mailer();*/
						$subject = $_SERVER['SERVER_NAME'].': Новая заявка от клиента';
						$to = $this->get_email_list();
						$from = $this->getConfigValue('order_email_acceptor');
						
						/*if ( $this->getConfigValue('use_smtp') ) {
							$mailer->send_smtp($to, $from, $subject, $order_mail_body, 1);
						} else {
							$mailer->send_simple($to, $from, $subject, $order_mail_body, 1);
						}*/
						$this->sendFirmMail($to, $from, $subject, $order_mail_body);
						
						$form_data = $this->data_model;
						//$rs .= $this->get_form($form_data[$this->table_name]);
						
						$page_array = $this->getPageByURI('client_text_thanks');
						if ( $page_array ) {
							$this->template->assert('client_text', $page_array['body']);
							$this->template->assert('title', $page_array['title']);
							$this->template->assert('meta_keywords', $page_array['meta_keywords']);
							$this->template->assert('meta_description', $page_array['meta_description']);
						} else {
							$rs .= '<p>Спасибо за вашу заявку</p>';
						}
						
					}
				}
				break;
			}
			default : {
				$rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'));
			}
		}
		return $rs;
	}
}
