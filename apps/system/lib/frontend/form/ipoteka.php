<?php
/**
 * Ipoteka order form
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Ipoteka_Order_Form extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'ipoteka';
        $this->action = 'ipoteka';
        $this->primary_key = 'ipoteka_id';
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
		$this->data_model = $data_model->get_ipoteka_model();
    }
    
   	protected function _defaultAction(){
    	$rs='';
    	$form_data = $this->data_model;
    	$rs .= $this->get_form($form_data[$this->table_name]);
    	return $rs;
    }
    
    protected function _new_doneAction(){
    	$rs='';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    	if ( !$this->check_data( $form_data[$this->table_name] ) ) {
    		$rs .= $this->get_form($form_data[$this->table_name], 'new');
    	} else {
    		$order_table = $this->add_data($form_data[$this->table_name]);
    		$subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('IPOTEKA_NEW_ORDER','system');
    		$to = $this->getConfigValue('order_email_acceptor');
    		$from = $this->getConfigValue('system_email');
    		$this->sendFirmMail($to, $from, $subject, $order_table);
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/client/client.xml') ) {
    			require_once ((SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/admin.php'));
    			$client_admin = new client_admin();
    	
    			$client_admin->data_model['client']['type_id']['value'] = 'ipoteka';
    			$client_admin->data_model['client']['status_id']['value'] = 'new';
    			$client_admin->data_model['client']['date']['value'] = time();
    			$client_admin->data_model['client']['fio']['value'] = $form_data[$this->table_name]['fio']['value'];
    			$client_admin->data_model['client']['email']['value'] = $form_data[$this->table_name]['email']['value'];
    			$client_admin->data_model['client']['phone']['value'] = $form_data[$this->table_name]['phone']['value'];
    			unset($form_data[$this->table_name]['fio']);
    			unset($form_data[$this->table_name]['email']);
    			unset($form_data[$this->table_name]['phone']);
    			$order_text = $this->add_data($form_data[$this->table_name]);
    	
    			$client_admin->data_model['client']['order_text']['value'] = $order_text;
    			 
    			$client_admin->add_data($client_admin->data_model['client']);
    			if ( $client_admin->getError() ) {
    				$rs = $client_admin->GetErrorMessage();
    				return $rs;
    			}
    		}
    		$rs = '<h1>'.Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED').'</h1>';
    		$rs .= $order_table;
    	}
    	return $rs;
    }
    
    
    
    /**
     * Main
     * @param void
     * @return string
     */
   function main () {
	    $rs = $this->getTopMenu();
	    $rs = '';
	    $this->template->assign('title', Multilanguage::_('IPOTEKA_ORDER','system'));
		$do=$this->getRequestValue('do');
		switch($do){
			case 'new_done' : {
				$rs .= $this->_new_doneAction();
				break;
			}
			default : {
				$rs .= $this->_defaultAction();
			}
		}
		
		return $rs;
	}
	
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    $rs = '';
	    $rs .= '<h1>'.Multilanguage::_('IPOTEKA_ORDER','system').'</h1>';
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
	    
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
	    $table_view = new Table_View();
        $rs .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
	    $rs .= $table_view->compile_view($form_data);
	    $rs .= '</table>';
	    
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
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
		if($button_title==''){
			$button_title=Multilanguage::_('L_TEXT_SEND');
		}
		global $smarty;
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		/*
        $rs .= $this->get_ajax_functions();
		
		$rs .= '<form method="post" class="ipoteka_order" action="index.php" enctype="multipart/form-data">';
        $rs .= '<table>';
		if ( $this->getError() ) {
		    $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		$rs .= $form_generator->compile_form($form_data);
		*/
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
			$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">');
		} else {
			$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
			$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'">');
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
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/ipotekaorder/site/template/data_form.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/ipotekaorder/site/template/data_form.tpl';
		}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
		}
		return $smarty->fetch($tpl_name);
		
	}
	
}