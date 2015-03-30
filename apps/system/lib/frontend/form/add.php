<?php
/**
 * User add (not login)
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class User_Add extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        global $smarty;
        $smarty->assign('search_form_template', '');
        $this->SiteBill();
        $this->table_name = 'data';
        $this->action = 'data';
        $this->primary_key = 'id';
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
		
		
	    
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_user'));
        //print_r($this->data_model);
        /*
        if($this->getConfigValue('captcha_type')!=2){
        	$this->data_model[$this->table_name]['captcha']['name'] = 'captcha';
        	$this->data_model[$this->table_name]['captcha']['title'] = 'Защитный код';
        	$this->data_model[$this->table_name]['captcha']['value'] = '';
        	$this->data_model[$this->table_name]['captcha']['length'] = 40;
        	$this->data_model[$this->table_name]['captcha']['type'] = 'captcha';
        	$this->data_model[$this->table_name]['captcha']['required'] = 'on';
        	$this->data_model[$this->table_name]['captcha']['unique'] = 'off';
        }
        */
        $this->data_model[$this->table_name]['captcha']['name'] = 'captcha';
        $this->data_model[$this->table_name]['captcha']['title'] = 'Защитный код';
        $this->data_model[$this->table_name]['captcha']['value'] = '';
        $this->data_model[$this->table_name]['captcha']['length'] = 40;
        $this->data_model[$this->table_name]['captcha']['type'] = 'captcha';
        $this->data_model[$this->table_name]['captcha']['required'] = 'on';
        $this->data_model[$this->table_name]['captcha']['unique'] = 'off';
        
        
        $this->data_model[$this->table_name]['user_id']['value'] = $this->getUnregisteredUserId();
        $this->data_model[$this->table_name]['user_id']['type'] = 'hidden';
        
        $this->data_model[$this->table_name]['date_added']['value'] = date('Y-m-d H:i:s');
        $this->data_model[$this->table_name]['date_added']['type'] = 'hidden';
        
        $this->data_model[$this->table_name]['hot']['type'] = 'hidden';
        $this->data_model[$this->table_name]['active']['type'] = 'hidden';
        
        if($this->getConfigValue('user_add_street_enable')!=1){
        	if(isset($this->data_model[$this->table_name]['new_street'])){
        		unset($this->data_model[$this->table_name]['new_street']);
        	}
        }
        
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    
		$rs = $this->getTopMenu();
		$this->template->assign('is_account', 1);

		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
        		
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				$new_values=$this->getRequestValue('_new_value');
				if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
					$remove_this_names=array();
					foreach($form_data[$this->table_name] as $fd){
						if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
							$id=md5(time().'_'.rand(100,999));
							$remove_this_names[]=$id;
							$form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
							$form_data[$this->table_name][$id]['type'] = 'auto_add_value';
							$form_data[$this->table_name][$id]['dbtype'] = 'notable';
							$form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
							$form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
							$form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
							$form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
							$form_data[$this->table_name][$id]['required'] = 'off';
							$form_data[$this->table_name][$id]['unique'] = 'off';
						}
					}
				}
				$data_model->forse_auto_add_values($form_data[$this->table_name]);
	    		if ( !$this->check_data( $form_data[$this->table_name] ) ) {
	    			$form_data[$this->table_name]=$this->removeTemporaryFields($form_data[$this->table_name],$remove_this_names);
			        $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/add/');
			        
			    } else {
			    	unset($form_data[$this->table_name]['captcha']);
			        //$form_data[$this->table_name]['fio']['title'] = "<b>ФИО заявителя</b>";
			       	//$form_data[$this->table_name]['phone']['title'] = "<b>Телефон заявителя</b>";
			        $form_data[$this->table_name]['active']['value'] = 0;
			        $form_data[$this->table_name]['date_added']['value'] = date('Y-m-d H:i:s',time());
			         
			        if(1==$this->getConfigValue('autoreg_enable')){
			        	$uid=$this->quickAddUser($form_data[$this->table_name]['email']['value']);
			        	//unset($form_data[$this->table_name]['email']);			        	//var_dump($uid);
			        	if($uid==='error'){
			        		$rs = '<h1>'.Multilanguage::_('L_ERROR_ON_AUTOREGISTER').'</h1>';
			        	}elseif($uid===FALSE){
			        		$rs = '<h1>'.sprintf(Multilanguage::_('L_REGISTERED_EMAIL'),$form_data[$this->table_name]['email']['value']).'</h1>';
			        	}else{
			        		$form_data[$this->table_name]['user_id']['value']=$uid;
				        	$order_table = $this->add_data($form_data[$this->table_name]);
					        if($order_table!==FALSE){
					        	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
			
			                    $mailer = new Mailer();*/
			                    $subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('NEW_ORDER','system');
			                    $to = ($this->getConfigValue('add_notification_email')!='' ? $this->getConfigValue('add_notification_email') : $this->getConfigValue('order_email_acceptor'));
			                    $from = $this->getConfigValue('order_email_acceptor');
			                    /*if ( $this->getConfigValue('use_smtp') ) {
			                        $mailer->send_smtp($to, $from, $subject, $order_table, 1);
			                    } else {
			                        $mailer->send_simple($to, $from, $subject, $order_table, 1);
			                    }*/
			                    $this->sendFirmMail($to, $from, $subject, $order_table);
			                    $rs = '<h1>'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED').'</h1>';
			                    $rs .= '<p>'.Multilanguage::_('L_MESSAGE_ON_MODERATION').'</p>';
			                    $rs .= '<form action="'.SITEBILL_MAIN_URL.'/"><input type="submit" value="OK" /></form>';
			                    $rs .= $order_table;
					        }else{
					        	$rs = '<h1>Произошла ошибка при добавлении. Попробуйте еще раз.</h1>';
					        }
			        	}
			  		}else{
			  			//unset($form_data[$this->table_name]['email']);
			  			$form_data[$this->table_name]['user_id']['value']=$this->getUnregisteredUserId();
				        $order_table = $this->add_data($form_data[$this->table_name]);
				        if($order_table!==FALSE){
				        	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
		
		                    $mailer = new Mailer();*/
		                    $subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('NEW_ORDER','system');
		                    $to = ($this->getConfigValue('add_notification_email')!='' ? $this->getConfigValue('add_notification_email') : $this->getConfigValue('order_email_acceptor'));
		                    $from = $this->getConfigValue('order_email_acceptor');
		                    /*if ( $this->getConfigValue('use_smtp') ) {
		                        $mailer->send_smtp($to, $from, $subject, $order_table, 1);
		                    } else {
		                        $mailer->send_simple($to, $from, $subject, $order_table, 1);
		                    }*/
		                    $this->sendFirmMail($to, $from, $subject, $order_table);
		                    $rs = '<h1>'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED').'</h1>';
		                    $rs .= '<p>'.Multilanguage::_('L_MESSAGE_ON_MODERATION').'</p>';
			                    $rs .= '<form action="'.SITEBILL_MAIN_URL.'/"><input type="submit" value="OK" /></form>';
		                    $rs .= $order_table;
				        }else{
				        	$rs = '<h1>'.Multilanguage::_('L_ERROR_ON_ADD').'. '.Multilanguage::_('L_MESSAGE_TRY_AGAIN_LATER').'.</h1>';
				        }
			        }
			    }
				break;
			}
			
			default : {
				//$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				$rs .= $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/add/');
			}
		}
		return $rs;
	}
	
	function quickAddUser($email){
		
		
		$email=strip_tags($email);
		if($email==''){
			return 'error';
		}
		$login=$email;
		$fio = strip_tags($this->getRequestValue('fio'));
		
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php';
		
		$UM=new Users_Manager();
		if($UM->checkLogin($login)){
			$password=substr(md5(time()),1,6);
			$DBC=DBC::getInstance();
			$query='INSERT INTO '.DB_PREFIX.'_user (login, password, email, active, fio, reg_date, group_id) VALUES (?,?,?,1,?,?,?)';
			$stmt=$DBC->query($query, array(mysql_real_escape_string($login), md5($password), $email, mysql_real_escape_string($fio), date('Y-m-d H:i:s', time()), (int)$this->getConfigValue('newuser_autoregistration_groupid')));
			
			if ( !$stmt ) {
				return 'error';
	        } else {
	        	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
	
                $mailer = new Mailer();*/
                $subject = $_SERVER['SERVER_NAME'].': регистрация на сайте';
                
                if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/mails/'.Multilanguage::get_current_language().'/user_registration.tpl')){
                	$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/mails/'.Multilanguage::get_current_language().'/user_registration.tpl';
                }elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/'.Multilanguage::get_current_language().'/user_registration.tpl')){
                	$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/'.Multilanguage::get_current_language().'/user_registration.tpl';
                }else{
                	$tpl='';
                }
                
                if($tpl==''){
                	$body=sprintf(Multilanguage::_('L_MESSAGE_REGISTER_WELLCOME'),$_SERVER['SERVER_NAME'],$login,$password);
                }else{
                	global $smarty;
                	$smarty->assign('mail_login', $login);
                	$smarty->assign('mail_password', $password);
                	$smarty->assign('mail_server', $_SERVER['SERVER_NAME']);
                	$body=$smarty->fetch($tpl);
                }
                
                $to = $email;
                $from = $this->getConfigValue('order_email_acceptor');
                /*if ( $this->getConfigValue('use_smtp') ) {
                	$mailer->send_smtp($to, $from, $subject, $body, 1);
				} else {
					$mailer->send_simple($to, $from, $subject, $body, 1);
				}*/
				$this->sendFirmMail($to, $from, $subject, $body);
	        	return $DBC->lastInsertId();
	        }
		}else{
			return FALSE;
		}
		
		
	}
	
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    $rs = '';
	    $rs .= '<h1>'.Multilanguage::_('PLEASE_PUT_ORDER','system').'</h1>';
	    if(Multilanguage::is_set('LT_PUBLICADD_FORM_PRETEXT', '_template') && ''!=Multilanguage::_('LT_PUBLICADD_FORM_PRETEXT', '_template')){
	    	$rs .= '<div class="publicadd_form_pretext">'.Multilanguage::_('LT_PUBLICADD_FORM_PRETEXT', '_template').'</div>';
	    }
	    return $rs;
	}
    
	/**
	 * Add data
	 * @param array $form_data form data
	 * @return boolean
	 */
	function add_data ( $form_data ) {
		
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data['price']['value']=str_replace(' ', '', $form_data['price']['value']);
	    
	    if(1==$this->getConfigValue('apps.geodata.try_encode') && 1==$this->getConfigValue('apps.geodata.enable')){
	    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
	    	$GA=new geodata_admin();
	    	$form_data=$GA->try_geocode($form_data);
	    }
	    
	    $new_record_id = parent::add_data($form_data);
	    
		if ( $new_record_id===FALSE ) {
		    echo $this->GetErrorMessage();
	        return FALSE;
	    }
	    
	    
	    
		if($this->getConfigValue('apps.realtylog.enable')){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
        	$Logger=new realtylog_admin();
        	$Logger->addLog($new_record_id, $this->getUnregisteredUserId(), 'new', $this->table_name);
        }
	    
	    //$imgs=$this->editImageMulti('data', 'data', 'id', $new_record_id);
	    
	    if(1==$this->getConfigValue('apps.seo.data_alias_enable')){
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
			$imgs = $this->get_imgs();
			if(!empty($imgs)){
				foreach($imgs as $v){
					$Watermark->printWatermark($filespath.$v['normal']);
				}
			}
		}
	    	     
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
	    $table_view = new Table_View();
        $rs .= '<table border="1" cellpadding="2" cellspacing="2" class="table table-striped table-hover">';
	    $rs .= $table_view->compile_view($form_data);
	    $rs .= '</table>';
	    
	    /*
	    $query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data);
	    //echo $query.'<br>';
	    
	    $this->db->exec($query);
	    $new_record_id = $this->db->last_insert_id();
	    //echo "new_record_id = $new_record_id<br>";
	    //echo $query;
	     */
	    return $rs;
	}
	
	
	/**
	 * Get form for edit or new record
	 * @param array $form_data
	 * @param string $do
	 * @param int $language_id
	 * @param string $button_title
	 * @return string
	 */
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php' ) {
		
		if(1==$this->getConfigValue('divide_step_form')){
			return $this->_get_form_step_divided($form_data, $do, $language_id, $button_title);
		}else{
			return $this->_get_form_standart($form_data, $do, $language_id, $button_title, $action);
		}
		
		
		
		
	}
	
	function _get_form_step_divided($form_data=array(), $do = 'new', $language_id = 0, $button_title = ''){
		
		
		
		
		$requesturi=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
		if(SITEBILL_MAIN_URL!=''){
			preg_replace('/^'.trim(SITEBILL_MAIN_URL,'/').'/','',$requesturi);
		}
		if(preg_match('/step(\d+)$/',$requesturi,$matches)){
			$step=(int)$matches[1];
		}else{
			$step=1;
		}
		
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
		///////////
		//print_r($steps_names);
		$steps_total=count($steps_names);
		
		
		$Sitebill_Registry=Sitebill_Registry::getInstance();
		$Sitebill_Registry->addFeedback('divide_step_form',true);
		$Sitebill_Registry->addFeedback('step',$step);
		
		
		
		if($button_title==''){
			$button_title=Multilanguage::_('L_TEXT_SEND');
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
		$rs .= $this->get_ajax_functions();
		 
		$rs .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>';
		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		if(1==$this->getConfigValue('use_combobox')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/mycombobox.js"></script>';
			$rs .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/css/mycombobox.css" />';
		}
		
		
		$rs .= '<div class="steps">';
		foreach($steps_names as $stepn){
			
			switch($stepn['status']){
				case 'current' : {
					$rs.='<div class="current"><a class="go_to_step" alt="'.$stepn['step'].'" href="/add/step'.$stepn['step'].'">'.$stepn['name'].'</a></div>';
					break;
				}
				case 'done' : {
					$rs.='<div class="done"><a class="go_to_step" alt="'.$stepn['step'].'" href="/add/step'.$stepn['step'].'">'.$stepn['name'].'</a></div>';
					break;
				}
				default :{
					$rs.= '<div class="future">'.$stepn['name'].'</div>';
				}
			}
			
			
			/*if($stepn['status'] < $step){
				$rs.='<a href="'.$step_i.'">'.$step_name.'</a>';
			}elseif($step_i > $step){
				$rs.=$step_name;
			}else{
				$rs.='<a href="'.$step_i.'">'.$step_name.'</a>';
			}*/
			
		}
		$rs .= '</div>';
		$rs .= '<div class="clear"></div>';
		//print_r($steps_names);
		//$rs.=implode(' | ',$steps_names);
		
		$form_content=$form_generator->compile_form($form_data);
		//echo $Sitebill_Registry->getFeedback('step').'_'.$Sitebill_Registry->getFeedback('steps');
		if($step < $steps_total){
			$rs .= '<form id="step_form" method="post" action="'.SITEBILL_MAIN_URL.'/add/step'.(1+$step).'" enctype="multipart/form-data" class="user_add_form">';
		}else{
			$rs .= '<form id="step_form" method="post" action="'.SITEBILL_MAIN_URL.'/add/step'.$steps_total.'" enctype="multipart/form-data" class="user_add_form">';
		}
		//$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/add/step'.(1+$Sitebill_Registry->getFeedback('step')).'" enctype="multipart/form-data" class="user_add_form">';
		$rs .= '<table>';
		if ( $this->getError() ) {
			$rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		//echo 'start compile<br>';
		$rs .= $form_content;
		 
		//$rs .= '<input type="hidden" name="do" value="new">';
		//$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">';
		if($step < $steps_total){
			$rs .= '<input type="hidden" name="do" value="new">';
			$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">';
		}else{
			if ( $do == 'new' ) {
				$rs .= '<input type="hidden" name="do" value="new_done">';
				$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">';
			} else {
				$rs .= '<input type="hidden" name="do" value="edit_done">';
				$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'">';
			}
		}
		 
		$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
		$rs .= '<input type="hidden" name="language_id" value="'.$language_id.'">';
		 
		 
		if($this->getConfigValue('post_form_agreement_enable')==1 && $step == $steps_total){
			 
			$rs .= '<script type="text/javascript">';
			$rs.='$(document).ready(function(){';
			$rs.='	if($("#i_am_agree_in_form").is(":checked")){';
			 
			$rs.='	}else{';
			$rs.='		$("#formsubmit").prop("disabled", true);';
			$rs.='	}';
			 
			$rs.='	$("#i_am_agree_in_form").change(function(){';
			$rs.='			if($(this).is("checked")){';
			$rs.='				$("#formsubmit").prop("disabled", false);';
			 
			$rs.='			}else{';
			$rs.='				$("#formsubmit").attr("disabled", true);';
			 
			$rs.='			}';
			$rs.='	});';
			 
			$rs.='});';
			$rs .= '</script>';
			 
			
			$rs .= '<tr>';
			$rs .= '<td><input type="checkbox" id="i_am_agree_in_form" />'.$this->getConfigValue('post_form_agreement_text_add').'</td>';
			$rs .= '</tr>';
		}
		 
		$rs .= '<tr>';
		if($step < $steps_total){
			$button_title='Следующий шаг';
		}
	
		$rs .= '<td>';
		if($step>1){
			$rs .= '<input type="submit" name="submit" id="formsubmit_back" alt="'.($step-1).'" value="Назад">';
		}
		$rs .= '<input type="submit" name="submit" id="formsubmit" onClick="SitebillCore.formsubmit();" value="'.$button_title.'">';
		$rs .= '</td>';
		 
		$rs .= '</tr>';
		$rs .= '</table>';
		$rs .= '</form>';
		$rs.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/css/form_tabs_divided.css" />';
		
		return $rs;
	}
	
	function getSteps($form_data,$step){
		
		$default_tab_name=$this->getConfigValue('default_tab_name');
		$tabs=array($default_tab_name);
		 
		foreach ( $form_data as $item_id => $item_array ) {
			if(isset($item_array['tab']) && $item_array['tab']!=''){
				$tabs[$item_array['tab']]=$item_array['tab'];
			}
		
		}
		//return array_values($tabs);
		/*$tabs_array=array();
		$i=1;
		foreach($tabs as $t){
			if($i<=$step){
				$tabs_array[$i]='<a class="step_done" href="">'.$t.'</a>';
			}else{
				$tabs_array[$i]='<a class="step_undone" href="'.SITEBILL_MAIN_URL.'/add/step'.$i.'">'.$t.'</a>';
			}
			
			$i++;
		}*/
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
			
			//$tabs_array[$i]=$t;
			$i++;
		}
		return $tabs_array;
	}
	
	function _get_form_standart($form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php'){
		$_SESSION['allow_disable_root_structure_select']=true;
		global $smarty;
		if($button_title==''){
			$button_title=Multilanguage::_('L_TEXT_SEND');
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
		$rs .= $this->get_ajax_functions();
		
		if(1==$this->getConfigValue('apps.geodata.enable')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		}
		
		if(1==$this->getConfigValue('use_combobox')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/mycombobox.js"></script>';
			$rs .= '<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/system/css/mycombobox.css" />';
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
			$rs.='		$("#formsubmit").prop("disabled", true);';
			$rs.='	}';
			 
			$rs.='	$("#i_am_agree_in_form").change(function(){';
			$rs.='			if($(this).is(":checked")){';
			$rs.='				$("#formsubmit").prop("disabled", false);';
			 
			$rs.='			}else{';
			$rs.='				$("#formsubmit").prop("disabled", true);';
			 
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
		
		
		 
		if ( $do != 'new' ) {
			$el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
		}
		$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
		 
		 
		 
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_add.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_add.tpl';
		}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		
		
		
		return $smarty->fetch($tpl_name);
	}
}
?>
