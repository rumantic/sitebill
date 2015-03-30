<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Getrent fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class getrent_site extends getrent_admin {

	
	
	function frontend () {
		global $smarty;
		$this->type='question';
		if ( !$this->getConfigValue('apps.getrent.enable') ) {
			return false;
		}
		
		$breadcrumbs=array();
		//$breadcrumbs[]= array('href'=>(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/', 'title' => Multilanguage::_('L_HOME'));
		//$breadcrumbs[]= array('href'=>(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/rentlist/', 'title' => $this->getConfigValue('apps.getrent.folder_title'), 'last' => 'false');
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		
		if(preg_match('/^rentlist/', $REQUESTURIPATH)){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
			$data_model = new Data_Model();
				
			if(preg_match('/\/rentlist\/view/', $_SERVER['REQUEST_URI'])){
				$form_data = $this->data_model;
				$form_data[$this->table_name] = $this->load_by_id($this->getIDfromURI($_SERVER['REQUEST_URI']));
				
				unset($form_data[$this->table_name]['name']);
				unset($form_data[$this->table_name]['phone']);
				unset($form_data[$this->table_name]['email']);
				
				$this->template->assert('form_data',$form_data);
				
				
				
				$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/apps/getrent/site/template/';
				$rent_view_body = $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/getrent/site/template/rent_view_body.tpl.html');
				$this->template->assert('rent_view_body',$rent_view_body);
				$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme');
				
				$breadcrumbs[]= array('href'=>(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL : '').'/rentlist/view/'.$this->getIDfromURI($_SERVER['REQUEST_URI']), 'title' => 'Просмотр заявки', 'last' => 'true');
				$this->set_apps_template('getrent', $this->getConfigValue('theme'), 'main_file_tpl', 'view_rent.tpl.html');
			} else {
				$grid_array = $this->grid();
				
				$this->template->assert('grid', $grid_array['grid_array']);
				$this->template->assert('pager', $grid_array['pager']);
				
				$this->set_apps_template('getrent', $this->getConfigValue('theme'), 'main_file_tpl', 'rent_list.tpl.html');
			}
				
			$this->template->assert('breadcrumbs_array', $breadcrumbs);
			return true;
				
		}
		
		
		if(preg_match('/^getrent[\/]?/', $REQUESTURIPATH)){
			
			$breadcrumbs[]='<a href="/">'.Multilanguage::_('L_HOME').'</a>';
			$breadcrumbs[]=$this->getConfigValue('apps.getrent.folder_title');
			
			if(''!=$this->getConfigValue('apps.getrent.meta_title')){
				$this->template->assert('meta_title', $this->getConfigValue('apps.getrent.meta_title'));
			}else{
				$this->template->assert('meta_title', $this->getConfigValue('apps.getrent.title'));
			}
			
			if(''!=$this->getConfigValue('apps.getrent.meta_keywords')){
				$this->template->assert('meta_keywords', $this->getConfigValue('apps.getrent.meta_keywords'));
			}
			
			if(''!=$this->getConfigValue('apps.getrent.meta_description')){
				$this->template->assert('meta_description', $this->getConfigValue('apps.getrent.meta_description'));
			}
			
			$this->template->assert('title', $this->getConfigValue('apps.getrent.title'));
			$this->template->assert('breadcrumbs', implode(' / ',$breadcrumbs));
		
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
			$data_model = new Data_Model();
			if ( $this->getConfigValue('captcha_type') != 2 ) {
				$this->data_model[$this->table_name]['captcha']['name'] = 'captcha';
				$this->data_model[$this->table_name]['captcha']['title'] = 'Защитный код';
				$this->data_model[$this->table_name]['captcha']['value'] = '';
				$this->data_model[$this->table_name]['captcha']['length'] = 40;
				$this->data_model[$this->table_name]['captcha']['type'] = 'captcha';
				$this->data_model[$this->table_name]['captcha']['required'] = 'on';
				$this->data_model[$this->table_name]['captcha']['unique'] = 'off';
			}
			
			$form_data = $this->data_model;
			
			$requesturi=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
			if(SITEBILL_MAIN_URL!=''){
				$requesturi=preg_replace('/^'.trim(SITEBILL_MAIN_URL,'/').'/','',$requesturi);
			}
			$clearrequesturi=$requesturi;
			
				
			if(1==$this->getConfigValue('apps.client.allow-redirect_url_for_orders') && $clearrequesturi == 'getrent/online-getrent'){
				$this->template->assign('main', '<div class="alert alert-success">'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT').'</div>');
				$this->set_apps_template('getrent', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl.html');
				return true;
			}
				
		
			switch( $this->getRequestValue('do') ){
				case 'new_done' : {
					$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
					
					
					$data_model->forse_auto_add_values($form_data[$this->table_name]);
					if ( !$this->check_data( $form_data[$this->table_name] ) ) {
						$rs = $this->get_form($form_data[$this->table_name], 'new');
						 
					} else {
						unset($form_data[$this->table_name]['captcha']);
						$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
						if ( $this->getError() ) {
							$rs = $this->get_form($form_data[$this->table_name], 'new');
						} else {
							$this->template->assert('form_data',$form_data);
							
							$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/apps/getrent/site/template/';
							$order_mail_body = $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/getrent/site/template/order_mail.tpl.html');
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
							if(1==$this->getConfigValue('apps.client.allow-redirect_url_for_orders')){
								header('location: '.SITEBILL_MAIN_URL.'/getrent/online-getrent/');
							}else{
								$rs = '<div class="alert alert-success">'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT').'</div>';
							}	
							
							//$rs = Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT');
						}
					}
					break;
				}
					
				default : {
					$smarty->assign('description',$this->getConfigValue('apps.getrent.description'));
						
					$rs = $this->get_form($form_data[$this->table_name]);
				}
			}
			
			$smarty->assign('main',$rs);
			$this->set_apps_template('getrent', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl.html');
			return true;
		}
		return false;
	}
	
	/**
	 * Get ID from URI
	 * @param string $uri uri
	 * @return int
	 */
	function getIDfromURI ( $uri ) {
		preg_match('/rentlist\/view\/(\d+)?/s', $uri, $matches);
		if ( $matches[1] > 0 ) {
			return $matches[1];
		}
		return false;
		 
	}
	
	
	/**
	 * Grid
	 * @param void
	 * @return string
	 */
	function grid () {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
		$common_grid = new Common_Grid($this);
	
	
		$common_grid->add_grid_item($this->primary_key);
		$common_grid->add_grid_item('city_id');
		$common_grid->add_grid_item('district_id');
		$common_grid->add_grid_item('street_id');
		$common_grid->add_grid_item('square_min');
		$common_grid->add_grid_item('square_max');
		$common_grid->add_grid_item('price_min');
		$common_grid->add_grid_item('price_max');
		
		$common_grid->add_grid_control('edit');
		$common_grid->add_grid_control('delete');
	
		$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
	
		$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by ".$this->primary_key." asc");
		
		$ra = array();
		$ra['grid_array'] = $common_grid->construct_grid_array();
		$ra['pager'] = $common_grid->getPager();
		
		return $ra; 
	}
	
	
	protected function get_email_list () {
		$DBC=DBC::getInstance();
		$query = 'SELECT email FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
		$stmt=$DBC->query($query, array($this->getAdminUserId()));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			return $ar['email'];
		}
		return '';
	}
	
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php' ) {
	
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
		$rs .= '<form method="post" class="form-horizontal" action="'.$action.'" enctype="multipart/form-data">';
			
		if ( $this->getError() ) {
			$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
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
	
		$el['form_header']=$rs;
		
		if($this->getConfigValue('post_form_agreement_enable')==1){
		
			$rs .= '<script type="text/javascript">';
			$rs.='$(document).ready(function(){';
			$rs.='	if($("#i_am_agree_in_form").is(":checked")){';
		
			$rs.='	}else{';
			$rs.='		$("#getrent_submit").prop("disabled", true);';
			$rs.='	}';
		
			$rs.='	$("#i_am_agree_in_form").change(function(){';
			$rs.='			if($(this).is(":checked")){';
			$rs.='				$("#getrent_submit").prop("disabled", false);';
		
			$rs.='			}else{';
			$rs.='				$("#getrent_submit").prop("disabled", true);';
		
			$rs.='			}';
			$rs.='	});';
		
			$rs.='});';
			$rs .= '</script>';
			$rs .= '<div class="control-group">';
			$rs .= '<div class="controls">';
			$rs .= '<label class="checkbox">';
			$rs .= '<input type="checkbox" id="i_am_agree_in_form" />'.$this->getConfigValue('post_form_agreement_text_add');
			$rs .= '</label>';
			$rs .= '</div>';
			$rs .= '</div>';
				
				
			$el['form_footer']=$rs.'</form>';
			$rs='';
		}else{
			$el['form_footer']='</form>';
		}
		
		
		$el['form_footer']='</form>';
			
		$el['controls']['submit']=array('html'=>'<button id="getrent_submit" name="submit" class="btn btn-primary">'.$button_title.'</button>');
			
	
	
	
	
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/getrent/site/template/data_form.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/getrent/site/template/data_form.tpl';
		}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		return $smarty->fetch($tpl_name);
	}
	
	public function getStandaloneForm(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		if ( $this->getConfigValue('captcha_type') != 2 ) {
			$this->data_model[$this->table_name]['captcha']['name'] = 'captcha';
			$this->data_model[$this->table_name]['captcha']['title'] = 'Защитный код';
			$this->data_model[$this->table_name]['captcha']['value'] = '';
			$this->data_model[$this->table_name]['captcha']['length'] = 40;
			$this->data_model[$this->table_name]['captcha']['type'] = 'captcha';
			$this->data_model[$this->table_name]['captcha']['required'] = 'on';
			$this->data_model[$this->table_name]['captcha']['unique'] = 'off';
		}
			
		$form_data = $this->data_model;
		
		$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', $action = SITEBILL_MAIN_URL.'/getrent/');
		return $rs;
	}
	
	/*
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
	
		if($button_title==''){
			$button_title = Multilanguage::_('L_TEXT_SAVE');
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	
		$rs .= $this->get_ajax_functions();
		//$rs .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>';
		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		if(1==$this->getConfigValue('use_combobox')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/mycombobox.js"></script>';
			$rs .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/css/mycombobox.css" />';
		}
		$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/getrent/" enctype="multipart/form-data">';
		$rs .= '<table>';
		
		
		if ( $this->getError() ) {
			$rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		$rs .= $form_generator->compile_form($form_data);
	
		if ( $do == 'new' ) {
			$rs .= '<input type="hidden" name="do" value="new_done">';
			$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">';
		} else {
			$rs .= '<input type="hidden" name="do" value="edit_done">';
			$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'">';
		}
		//$rs .= '<input type="hidden" name="page" value="'.$_SESSION['rem_page'].'">';
		$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
		$rs .= '<input type="hidden" name="language_id" value="'.$language_id.'">';
	
		if($this->getConfigValue('post_form_agreement_enable')==1){
			 
			$rs .= '<script type="text/javascript">';
			$rs.='$(document).ready(function(){';
			$rs.='	if($("#i_am_agree_in_form").attr("checked")){';
	
			$rs.='	}else{';
			$rs.='		$("#getrent_submit").attr("disabled","disabled");';
			$rs.='	}';
			 
			$rs.='	$("#i_am_agree_in_form").change(function(){';
			$rs.='			if($(this).attr("checked")){';
			$rs.='				$("#getrent_submit").removeAttr("disabled");';
			 
			$rs.='			}else{';
			$rs.='				$("#getrent_submit").attr("disabled","disabled");';
			 
			$rs.='			}';
			 
			 
			
			 
			$rs.='});';
			
			
			 
			$rs.='});';
			$rs .= '</script>';
			 
			//$rs .= '</script>';
			$rs .= '<tr>';
			$rs .= '<td><input type="checkbox" id="i_am_agree_in_form" /></td><td>'.$this->getConfigValue('post_form_agreement_text').'</td>';
			$rs .= '</tr>';
		}
		
		
		
		$rs .= '<tr>';
		$rs .= '<td></td><td><input type="submit" value="Отправить" id="getrent_submit"></td>';
		$rs .= '</tr>';
		
		
		$rs .= '</table>';
		$rs .= '</form>';
		return $rs;
	
	}
	*/
	
	
	
}