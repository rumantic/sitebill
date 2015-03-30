<?php
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/user_register.php');
class User_Shop_Register extends User_Register {
	
    function User_Shop_Register() {
        $this->SiteBill();
        $this->table_name = 'user';
        $this->action = 'register';
        $this->primary_key = 'user_id';
        
        
        
        $this->data_model = $this->get_user_shop_register_form_model();
    }
    

    
	function main () {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    
		//$rs = $this->getTopMenu();

		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
        		
	            $form_data['user'] = $data_model->init_model_data_from_request($form_data['user']);
	            
	            if ( !$this->check_data( $form_data['user'] ) ) {
			        $rs = $this->get_form($form_data['user'], 'new');
			        
			    } else {
			    	
			    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/group/group_manager.php');
				    $group_manager = new Group_Manager();
				    $group_array = $group_manager->load_by_system_name('user');
				    
				    $form_data['user']['group_id']['value'] = $group_array['group_id']['value'];
					$form_data['user']['group_id']['type'] = 'hidden';
					
					$form_data['user']['publication_limit']['value'] = $this->getConfigValue('user_publication_limit');
		
				    unset($form_data['user']['captcha_string']);
				    $wanttoup=$form_data['user']['wanttoup'];
				    
				    //print_r($form_data['user']['wanttoup']);
			        unset($form_data['user']['wanttoup']);
				    
				    
				    $this->add_data($form_data['user'], $this->getRequestValue('language_id'));
			        require_once( SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
                    $Login = new Login;
                    $Login->checkLogin($form_data['user']['login']['value'], $form_data['user']['newpass']['value']);
                    
                    $user_info_string = $Login->getUserInfo($Login->getUserId());
			    	if($wanttoup['value']==1){
				    	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					    $mailer = new Mailer();*/
					    $body='Пользователь <a href="http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?action=user&do=edit&user_id=7">'.($user_info_string!='' ? $user_info_string : $Login->getUserId()).'</a> зарегистрировался и хочет подавать объявления на сайте';
	                    $subject = $_SERVER['SERVER_NAME'].': заявка пользователя';
	                    $to = $this->getConfigValue('order_email_acceptor');
	                    $from = $this->getConfigValue('order_email_acceptor');
	                    /*if ( $this->getConfigValue('use_smtp') ) {
	                        $mailer->send_smtp($to, $from, $subject, $body, 1);
	                    } else {
	                        $mailer->send_simple($to, $from, $subject, $body, 1);
	                    }*/
	                    $this->sendFirmMail($to, $from, $subject, $body);
				    }
				    
                   	$rs = '<h3>Поздравляем! Регистрация прошла успешно.</h3><br>';
                    $rs .= 'Далее вы можете пройти в <a href="'.SITEBILL_MAIN_URL.'/account/">личный кабинет</a>';
                    $rs .= '<script>document.location.href=\''.SITEBILL_MAIN_URL.'/account/\';</script>';
                    return $rs;
			    }
				break;
			}
			
			case 'new' : {
			    $rs = $this->get_form($form_data['user']);
				break;
			}
			default : {
			    $rs .= $this->get_form($form_data['user']);
			}
		}
		return $rs;
	}
	
    
    
	function get_user_shop_register_form_model () {
		$register_form = array();
		
		$register_form=$this->get_user_register_form_model();
		
		$agreement = $this->getPageByURI('agreement.html');
		
		$register_form['user']['wanttoup']['name'] = 'wanttoup';
		$register_form['user']['wanttoup']['title'] = 'Xочу размещать свои объявления<br /> на БУВИТРИНА.РФ';
		$register_form['user']['wanttoup']['value'] = 0;
		$register_form['user']['wanttoup']['length'] = 40;
		$register_form['user']['wanttoup']['type'] = 'checkbox';
		$register_form['user']['wanttoup']['ajax_popup'] = '
<div id="agreement" style="display:none">
'.$agreement['body'].'
</div>		
		';
		$register_form['user']['wanttoup']['required'] = 'off';
		$register_form['user']['wanttoup']['unique'] = 'off';
		
		
		return $register_form;
    }
}