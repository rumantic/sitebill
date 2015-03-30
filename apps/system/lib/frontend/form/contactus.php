<?php
/**
 * contactus form
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class contactus_Form extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'contactus';
        $this->action = 'contactus';
        $this->primary_key = 'contactus_id';
	    
        $this->data_model = $this->get_contactus_model();
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
			        $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), SITEBILL_MAIN_URL.'/contactus/');
			        
			    } else {
			        $order_table = $this->add_data($form_data[$this->table_name]);
                    /*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');

                    $mailer = new Mailer();*/
                    $subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('NEW_MESSAGE_FROM_SITE','system');
                    $to = $this->getConfigValue('order_email_acceptor');
                    $from = $this->getConfigValue('order_email_acceptor');
                    
                    /*if ( $this->getConfigValue('use_smtp') ) {
                        $mailer->send_smtp($to, $from, $subject, $order_table, 1);
                    } else {
                        $mailer->send_simple($to, $from, $subject, $order_table, 1);
                    }*/
                    $this->sendFirmMail($to, $from, $subject, $order_table);
                    $rs = '<h1>'.Multilanguage::_('MESSAGE_SENT','system').'</h1>';
                    $rs .= $order_table;
			        
			    }
				break;
			}
			
			default : {
			    $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), SITEBILL_MAIN_URL.'/contactus/');
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
	    $rs .= '<h1>Напишите нам</h1>';
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
	
	function get_contactus_model ( $ajax = false ) {
		$form_data = array();
		$table_name='contactus';
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
			$ATH=new Admin_Table_Helper();
			$form_data=$ATH->load_model($table_name, $ignore_user_group);
		
		
			if(empty($form_data)){
				$form_data = array();
				$form_data = $this->_get_contactus_model($ajax);
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
				$TA=new table_admin();
				$TA->create_table_and_columns($form_data, $table_name);
				$form_data = array();
				$form_data=$ATH->load_model($table_name, $ignore_user_group);
			}
		
			$form_data = $ATH->add_ajax($form_data);
		
		
		}else{
			$form_data = $this->_get_contactus_model($ajax);
		}
		return $form_data;
	}
	
    /**
     * Get contactus model
     * @param boolean $ajax mode
     * @return array
     */
    function _get_contactus_model ( $ajax = false ) {
		$form_data = array();
		
		$form_data['contactus']['id']['name'] = 'id';
		$form_data['contactus']['id']['title'] = Multilanguage::_('L_ID');
		$form_data['contactus']['id']['value'] = 0;
		$form_data['contactus']['id']['length'] = 40;
		$form_data['contactus']['id']['type'] = 'primary_key';
		$form_data['contactus']['id']['required'] = 'off';
		$form_data['contactus']['id']['unique'] = 'off';
		
		$form_data['contactus']['fio']['name'] = 'fio';
		$form_data['contactus']['fio']['title'] = Multilanguage::_('NAME_OR_COMPANY_NAME','system');
		$form_data['contactus']['fio']['value'] = '';
		$form_data['contactus']['fio']['length'] = 40;
		$form_data['contactus']['fio']['type'] = 'safe_string';
		$form_data['contactus']['fio']['required'] = 'on';
		$form_data['contactus']['fio']['unique'] = 'off';
		
		$form_data['contactus']['phone']['name'] = 'phone';
		$form_data['contactus']['phone']['title'] = Multilanguage::_('L_PHONE');
		$form_data['contactus']['phone']['value'] = '';
		$form_data['contactus']['phone']['length'] = 40;
		$form_data['contactus']['phone']['type'] = 'safe_string';
		$form_data['contactus']['phone']['required'] = 'on';
		$form_data['contactus']['phone']['unique'] = 'off';

		$form_data['contactus']['email']['name'] = 'email';
		$form_data['contactus']['email']['title'] = Multilanguage::_('L_EMAIL');
		$form_data['contactus']['email']['value'] = '';
		$form_data['contactus']['email']['length'] = 40;
		$form_data['contactus']['email']['type'] = 'safe_string';
		$form_data['contactus']['email']['required'] = 'on';
		$form_data['contactus']['email']['unique'] = 'off';
		
		$form_data['contactus']['text']['name'] = 'text';
		$form_data['contactus']['text']['title'] = Multilanguage::_('L_TEXT');
		$form_data['contactus']['text']['value'] = '';
		$form_data['contactus']['text']['length'] = 40;
		$form_data['contactus']['text']['type'] = 'textarea';
		$form_data['contactus']['text']['required'] = 'on';
		$form_data['contactus']['text']['unique'] = 'off';
		$form_data['contactus']['text']['rows'] = '10';
		$form_data['contactus']['text']['cols'] = '40';
		
		$form_data['contactus']['captcha']['name'] = 'captcha';
		$form_data['contactus']['captcha']['title'] = Multilanguage::_('L_CAPTCHA');
		$form_data['contactus']['captcha']['value'] = '';
		$form_data['contactus']['captcha']['length'] = 40;
		$form_data['contactus']['captcha']['type'] = 'captcha';
		$form_data['contactus']['captcha']['required'] = 'on';
		$form_data['contactus']['captcha']['unique'] = 'off';
		

        //$item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'];
		
		return $form_data;
    }
}
?>
