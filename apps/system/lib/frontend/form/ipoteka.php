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

		switch( $this->getRequestValue('do') ){
			case 'new_done' : {
        		
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
	            //echo '<pre>';
	            //print_r($form_data['data']);
			    
			    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			        $rs .= $this->get_form($form_data[$this->table_name], 'new');
			        
			    } else {
			         
			        $order_table = $this->add_data($form_data[$this->table_name]);
                    /*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');

                    $mailer = new Mailer();*/
                    $subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('IPOTEKA_NEW_ORDER','system');
                    $to = $this->getConfigValue('order_email_acceptor');
                    $from = $this->getConfigValue('order_email_acceptor');
                    
                   /* if ( $this->getConfigValue('use_smtp') ) {
                        $mailer->send_smtp($to, $from, $subject, $order_table, 1);
                    } else {
                        $mailer->send_simple($to, $from, $subject, $order_table, 1);
                    }*/
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
				break;
			}
			
			default : {
			    $rs .= $this->get_form($form_data[$this->table_name]);
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
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
		if($button_title==''){
			$button_title=Multilanguage::_('L_TEXT_SEND');
		}
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
        $rs .= $this->get_ajax_functions();
		
		$rs .= '<form method="post" action="index.php" enctype="multipart/form-data">';
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
		$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
		$rs .= '<input type="hidden" name="language_id" value="'.$language_id.'">';
		
		
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
			$rs.='				$("#formsubmit").attr("disabled", true);';
			 
			$rs.='			}';
			$rs.='	});';
			 
			$rs.='});';
			$rs .= '</script>';
        	$rs .= '<tr>';
	        $rs .= '<td><input type="checkbox" id="i_am_agree_in_form" /></td><td>'.$this->getConfigValue('post_form_agreement_text').'</td>';
	        $rs .= '</tr>';
        }
		
		$rs .= '<tr>';
		$rs .= '<td></td>';
		$rs .= '<td><input type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="'.$button_title.'"></td>';
		$rs .= '</tr>';
		$rs .= '</table>';
		$rs .= '</form>';
		
		return $rs;
		
	}
	
}
?>
