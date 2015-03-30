<?php
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
class User_Register extends User_Object_Manager {
	
    function User_Register() {
        $this->SiteBill();
        $this->table_name = 'user';
        $this->action = 'register';
        $this->primary_key = 'user_id';
        
        
        
        $this->data_model = $this->get_user_register_form_model();
        //print_r($this->get_user_register_form_model());
    }
    
    function main () {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    
		//$rs = $this->getTopMenu();

		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
        		
	            $form_data['user'] = $data_model->init_model_data_from_request($form_data['user']);
	            //echo '<pre>';
	            //print_r($form_data[$this->table_name]);
			    
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
			        
				    
				    
				    $this->add_data($form_data['user'], $this->getRequestValue('language_id'));
			        require_once( SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
                    $Login = new Login;
                    $Login->checkLogin($form_data['user']['login']['value'], $form_data['user']['password']['value']);
                    
                    $user_info_string = $Login->getUserInfo($Login->getUserId());
                   	$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
                    $rs .= '<a href="'.SITEBILL_MAIN_URL.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>';
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
	

    function get_form ( $form_data=array(), $do = 'new', $language_id = 0 ) {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
        $rs .= $this->get_ajax_functions();
		
		$rs .= '<form method="post" action="/'.$this->action.'/" enctype="multipart/form-data">';
        $rs .= '<table>';
		if ( $this->getError() ) {
		    $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		$rs .= $form_generator->compile_form($form_data);
		
		if ( $do == 'new' ) {
		    $rs .= '<input type="hidden" name="do" value="new_done">';
		} else {
		    $rs .= '<input type="hidden" name="do" value="edit_done">';
		}
		$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
		$rs .= '<input type="hidden" name="language_id" value="'.$language_id.'">';
		
		$rs .= '<tr>';
		$rs .= '<td></td>';
		$rs .= '<td><input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SAVE').'"></td>';
		$rs .= '</tr>';
		$rs .= '</table>';
		$rs .= '</form>';
		
		return $rs;
		
	}
    
	function get_user_register_form_model () {
		$register_form = array();
		
		$register_form=$this->get_user_model();
		unset($register_form['user']['user_id']);
		unset($register_form['user']['imgfile']);
		$register_form['user']['newpass']['title'] = Multilanguage::_('L_AUTH_PASSWORD');
		$register_form['user']['newpass']['required'] = 'on';
		$register_form['user']['newpass_retype']['title'] = Multilanguage::_('L_AUTH_RETYPE_PASSWORD');
		$register_form['user']['newpass_retype']['required'] = 'on';
		$register_form['user']['publication_limit']['type'] = 'hidden';
		
		$register_form['user']['company_id']['required'] = 'off';
		$register_form['user']['company_id']['type'] = 'hidden';
		
		$register_form['user']['group_id']['required'] = 'off';
		$register_form['user']['group_id']['type'] = 'hidden';
		
		
		$register_form['user']['captcha_string']['name'] = 'captcha_string';
		$register_form['user']['captcha_string']['title'] = Multilanguage::_('L_CAPTCHA');
		$register_form['user']['captcha_string']['value'] = '';
		$register_form['user']['captcha_string']['length'] = 40;
		$register_form['user']['captcha_string']['type'] = 'captcha';
		$register_form['user']['captcha_string']['required'] = 'on';
		$register_form['user']['captcha_string']['unique'] = 'off';
		
		return $register_form;
    }
}