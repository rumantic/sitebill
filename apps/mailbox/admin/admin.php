<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * mailbox admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

class mailbox_admin extends Object_Manager {
    private $data_manager_export;
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('mailbox');
        $this->table_name = 'mailbox';
        $this->action = 'mailbox';
        
        
        $this->primary_key = 'mailbox_id';
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
         
        if ( !$config_admin->check_config_item('apps.mailbox.enable') ) {
        	$config_admin->addParamToConfig('apps.mailbox.enable','0','Включить приложение Mailbox');
        }
        if ( !$config_admin->check_config_item('apps.mailbox.claim_address') ) {
        	$config_admin->addParamToConfig('apps.mailbox.claim_address','','Адрес электронной почты для отправки жалоб');
        }
        if ( !$config_admin->check_config_item('apps.mailbox.show_claim_button') ) {
        	$config_admin->addParamToConfig('apps.mailbox.show_claim_button','0','Показывать кнопку добавления жалобы');
        }
        
        //$this->install();
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/admin/mailbox_model.php');
        $Object=new Mailbox_Model();
        $this->data_model=$Object->get_model();
        
    }
    
    protected function _installAction(){
    	$this->install();
    }
    
    public function _preload(){
    	if ( $this->getConfigValue('apps.mailbox.enable') ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
	    	$Login = new Login();
	    	$this->template->assert('mailbox_panel', $this->getMailboxPanel($Login->getSessionUserId()));
	    	$this->template->assert('mailbox_on',1);
	    	$this->template->assert('estate_folder',SITEBILL_MAIN_URL);
	    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/mailbox/site/template/form.tpl')){
	    		$this->template->assert('apps_mailbox_block',SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/mailbox/site/template/form.tpl');
	    	}else{
	    		$this->template->assert('apps_mailbox_block',SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/site/template/form.tpl');
	    	}
	    	if(1==$this->getConfigValue('apps.mailbox.show_claim_button')){
	    		$this->template->assert('apps_mailbox_show_claim_button',1);
	    	}
	    	
    	}else{
    		$this->template->assert('mailbox_panel', '');
    		$this->template->assert('mailbox_on',0);
    	}
    	
    }
    
    function install () {
    	$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_mailbox` (
			  `mailbox_id` int(11) NOT NULL AUTO_INCREMENT,
			  `sender_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `reciever_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `theme` varchar(255) NOT NULL,
			  `message` text NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `phone` varchar(30) NOT NULL,
			  `email` varchar(100) NOT NULL,
			  `realty_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `status` tinyint(4) NOT NULL DEFAULT '0',
			  `creation_date` datetime NOT NULL,
			  PRIMARY KEY (`mailbox_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1 ;";
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    	$rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        return $rs;
    }
    
    function grid () {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
    	$common_grid = new Common_Grid($this);
    
    
    	$common_grid->add_grid_item($this->primary_key);
    	$common_grid->add_grid_item('theme');
    	$common_grid->add_grid_item('message');
    
    	$common_grid->add_grid_control('edit');
    	$common_grid->add_grid_control('delete');
    
    	$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
    
    	//$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by ".$this->primary_key." asc");
    	$rs = $common_grid->construct_grid();
    	return $rs;
    }
    
    function getTopMenu () {
    	$rs = '';
    	$rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">'.Multilanguage::_('L_ADD_RECORD_BUTTON').'</a> ';
    	$rs .= '<a href="?action='.$this->action.'&do=massnew" class="btn btn-primary">Отправить всем</a> ';
    	return $rs;
    }
    
    function ajax () {
    	if ( $this->getRequestValue('action') == 'get_logged_user_data' ) {
    		return $this->get_logged_user_data();
    	}elseif ( $this->getRequestValue('action') == 'send_message' ) {
    		return $this->save_message();
    	}elseif ( $this->getRequestValue('action') == 'send_admin_message' ) {
    		$captcha=$this->getRequestValue('captcha');
    		$captcha_key=$this->getRequestValue('captcha_key');
    		$DBC=DBC::getInstance();
    		$query='SELECT COUNT(*) AS cnt FROM '.DB_PREFIX.'_captcha_session WHERE captcha_session_key=? AND captcha_string=?';
    		$stmt=$DBC->query($query, array($captcha_key, $captcha));
    		$ar=$DBC->fetch($stmt);
    		if($ar['cnt']==1){
    			$this->setRequestValue('reciever_id', $this->getAdminUserId());
    			return $this->save_message();
    		}else{
    			return json_encode(array('answer'=>'invalid_captcha'));
    		}
    		
    	}elseif ( $this->getRequestValue('action') == 'send_friend_message' ) {
    		return $this->send_friend_message();
    	}elseif ( $this->getRequestValue('action') == 'read_message' ) {
    		return $this->read_message();
    	}elseif ( $this->getRequestValue('action') == 'get_complaint_form' ) {
    		return $this->get_complaint_form();
    	}elseif ( $this->getRequestValue('action') == 'save_complaint' ) {
    		return $this->save_complaint();
    	}else {
    		//return $this->xls_parser();
    	}
    	return false;
    }
    
    private function save_complaint(){
    	if(1!=$this->getConfigValue('apps.mailbox.show_claim_button')){
    		return 1;
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	//$form_data = $this->data_model;
    	$form_data = array();
    	
    	$form_data['email']['name'] = 'email';
    	$form_data['email']['title'] = 'E-mail';
    	$form_data['email']['value'] = '';
    	$form_data['email']['length'] = 40;
    	$form_data['email']['type'] = 'safe_string';
    	$form_data['email']['required'] = 'off';
    	$form_data['email']['unique'] = 'off';
    	
    	$form_data['realty_id']['name'] = 'realty_id';
    	$form_data['realty_id']['title'] = 'realty_id';
    	$form_data['realty_id']['value'] = '';
    	$form_data['realty_id']['length'] = 40;
    	$form_data['realty_id']['type'] = 'hidden';
    	$form_data['realty_id']['required'] = 'off';
    	$form_data['realty_id']['unique'] = 'off';
    	
    	$form_data['message']['name'] = 'message';
    	$form_data['message']['title'] = 'Сообщение';
    	$form_data['message']['value'] = '';
    	$form_data['message']['length'] = 40;
    	$form_data['message']['type'] = 'textarea';
    	$form_data['message']['required'] = 'off';
    	$form_data['message']['unique'] = 'off';
    	$form_data['message']['rows'] = '10';
    	$form_data['message']['cols'] = '40';
    	
    	$form_data['captcha']['name'] = 'captcha';
        $form_data['captcha']['title'] = 'Защитный код';
        $form_data['captcha']['value'] = '';
        $form_data['captcha']['length'] = 40;
        $form_data['captcha']['type'] = 'captcha';
        $form_data['captcha']['required'] = 'on';
        $form_data['captcha']['unique'] = 'off';
        
        $form_data = $data_model->init_model_data_from_request($form_data);
       
        if ( !$this->check_data( $form_data ) ) {
        	return 0;
        } else {
        	$body=nl2br($form_data['message']['value']).'<br />';
        	$body.='Email отправителя '.$form_data['email']['value'].'<br /><br />';
			/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
			$mailer = new Mailer();*/
			$subject = $_SERVER['SERVER_NAME'].': Жалоба на объявление ID'.$form_data['realty_id']['value'];
			$from = $this->getConfigValue('order_email_acceptor');
			if(''==$this->getConfigValue('apps.mailbox.claim_address')){
				$n_email = $this->getConfigValue('order_email_acceptor');
			}else{
				$n_email = $this->getConfigValue('apps.mailbox.claim_address');
			}
			
			/*if ( $this->getConfigValue('use_smtp') ) {
				$mailer->send_smtp($n_email, $from, $subject, $body, 1);
			} else {
				$mailer->send_simple($n_email, $from, $subject, $body, 1);
			}*/
			$this->sendFirmMail($n_email, $from, $subject, $body);
        	return 1;
        }
        
    	
    }
    
    private function get_complaint_form(){
    	if(1!=$this->getConfigValue('apps.mailbox.show_claim_button')){
    		return '';
    	}
    	$id=(int)$this->getRequestValue('realty_id');
    	global $smarty;
    	$form_data = array();
    	 
    	$form_data['email']['name'] = 'email';
    	$form_data['email']['title'] = 'E-mail';
    	$form_data['email']['value'] = '';
    	$form_data['email']['length'] = 40;
    	$form_data['email']['type'] = 'safe_string';
    	$form_data['email']['required'] = 'off';
    	$form_data['email']['unique'] = 'off';
    	
    	$form_data['realty_id']['name'] = 'realty_id';
    	$form_data['realty_id']['title'] = 'realty_id';
    	$form_data['realty_id']['value'] = $id;
    	$form_data['realty_id']['length'] = 40;
    	$form_data['realty_id']['type'] = 'hidden';
    	$form_data['realty_id']['required'] = 'off';
    	$form_data['realty_id']['unique'] = 'off';
    	 
    	$form_data['message']['name'] = 'message';
    	$form_data['message']['title'] = 'Сообщение';
    	$form_data['message']['value'] = '';
    	$form_data['message']['length'] = 40;
    	$form_data['message']['type'] = 'textarea';
    	$form_data['message']['required'] = 'off';
    	$form_data['message']['unique'] = 'off';
    	$form_data['message']['rows'] = '10';
    	$form_data['message']['cols'] = '40';
    	 
    	$form_data['captcha']['name'] = 'captcha';
    	$form_data['captcha']['title'] = 'Защитный код';
    	$form_data['captcha']['value'] = '';
    	$form_data['captcha']['length'] = 40;
    	$form_data['captcha']['type'] = 'captcha';
    	$form_data['captcha']['required'] = 'on';
    	$form_data['captcha']['unique'] = 'off';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
    	$form_generator = new Form_Generator();
    		
    		
    	$rs .= '<form method="post" class="form" action="'.$action.'" enctype="multipart/form-data">';
    		
    		
    	$el = $form_generator->compile_form_elements($form_data);
    	$el['form_header']=$rs;
    	$el['form_footer']='</form>';
    		
    
    		
    
    
    
    
    	$smarty->assign('form_elements',$el);
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
    	}else{
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
    	}
    	return $smarty->fetch($tpl_name);
    }
    
    function read_message($user_id){
    	$id=$this->getRequestValue('id');
    	$q='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET status=1 WHERE mailbox_id='.$id;
    	$this->db->exec($q);
    	
    }
    
    function getRealtyHref($realty_id){
    	$q='SELECT topic_id FROM '.DB_PREFIX.'_data WHERE id='.$realty_id;
    	$this->db->exec($q);
    	$this->db->fetch_assoc();
    	$topic_id=(int)$this->db->row['topic_id'];
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
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
    	if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$realty_id.'.html';
    	}else{
    		$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$realty_id;
    	}
    	return $href;
    }
    
    function getMailboxPanel($user_id){
    	if($user_id==0){
    		return '';
    	}else{
    		$total_msgs=$this->getUserIncomingMessages($user_id);
    		$unreaded_msgs=$this->getUserIncomingMessagesUnreaded($user_id);
    		
    		/*if($total_msgs['count']>0){
    			return '<a href="'.SITEBILL_MAIN_URL.'/mailbox/">Сообщения: '.$total_msgs['count'].' ('.$unreaded_msgs['count'].')</a>';
    		}else{
    			return 'Сообщения: '.$total_msgs['count'].' ('.$unreaded_msgs['count'].')';
    		}*/
    		return Multilanguage::_('MESSAGES','mailbox').': <span class="mailbox_allmsg">'.$total_msgs['count'].'</span> (<span class="mailbox_unrmsg">'.$unreaded_msgs['count'].'</span>)';
    	}
    }
    
    function getUserIncomingMessages($user_id){
    	$ret=array();
    	$q='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE reciever_id='.$user_id.' ORDER BY creation_date DESC';
    	$this->db->exec($q);
    	while($this->db->fetch_assoc()){
    		$ret[]=$this->db->row;
    	}
    	
    	if(count($ret)>0){
    		foreach($ret as &$r){
    			$r['href']=$this->getRealtyHref($r['realty_id']);
    		}
    	}
    	
    	return array('count'=>count($ret),'messages'=>$ret);
    }
    
    function getUserIncomingMessagesUnreaded($user_id){
    	$ret=array();
    	$q='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE status=0 AND reciever_id='.$user_id.' ORDER BY creation_date DESC';
    	$this->db->exec($q);
    	while($this->db->fetch_assoc()){
    		$ret[]=$this->db->row;
    	}
    	if(count($ret)>0){
    		foreach($ret as &$r){
    			$r['href']=$this->getRealtyHref($r['id'], $r['topic_id'], $category_structure);
    		}
    	}
    	return array('count'=>count($ret),'messages'=>$ret);
    }
    
    function get_logged_user_data(){
    	///echo $_SESSION['user_id'];
    	$uid=(int)$_SESSION['user_id'];
    	if($uid>0){
    		$q='SELECT* FROM '.DB_PREFIX.'_user WHERE user_id='.$uid;
    		$this->db->exec($q);
    		$this->db->fetch_assoc();
    		return json_encode(array_map(array('mailbox_admin','conv'), $this->db->row));
    	}else{
    		return json_encode(array('res'=>'no_user'));
    	}
    	
    }
    
    protected function _massnewAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	foreach($form_data[$this->table_name] as $k=>$v){
    		if(!in_array($k, array('theme', 'message'))){
    			unset($form_data[$this->table_name][$k]);
    		}
    	}
    	
    	$rs = $this->get_simple_form($form_data[$this->table_name], $do = 'massnew_done');
    	return $rs;
    }
    
    protected function _massnew_doneAction(){
    	$rs='';
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$current_user=(int)$this->getAdminUserId();
    	$except_user[]=$current_user;
    	if(0!=(int)$this->getUnregisteredUserId()){
    		$except_user[]=(int)$this->getUnregisteredUserId();
    	}
    	
    	$form_data = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT user_id, email FROM '.DB_PREFIX.'_user WHERE user_id NOT IN ('.implode(',', $except_user).')';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			
    			$m=$form_data;
    			$m['sender_id']['value']=$current_user;
    			$m['reciever_id']['value']=$ar['user_id'];
    			$m['creation_date']['value']=date('Y-m-d H:i:s', time());
    			$new_record_id=$this->add_data($m);
    		}
    	}
    	$rs='Сообщение разослано';
    	
    	return $rs;
    }
    
    function get_simple_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php' ) {
    
    	
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
    
    	$el['private'][]=array('html'=>'<input type="hidden" name="do" value="massnew_done" />');
    	$el['private'][]=array('html'=>'<input type="hidden" name="action" value="'.$this->action.'">');
    	$el['private'][]=array('html'=>'<input type="hidden" name="language_id" value="'.$language_id.'">');
    
    	$el['form_header']=$rs;
    	$el['form_footer']='</form>';
    		
    	$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
    		
    	$smarty->assign('form_elements',$el);
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
    	}else{
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
    	}
    	return $smarty->fetch($tpl_name);
    }
    
    //function save_admin_message
    
    function save_message(){
    	global $smarty;
    	$uid=(int)$_SESSION['user_id'];
    	$this->setRequestValue('sender_id', $uid);
    	
    	
    	$to=(int)$this->getRequestValue('reciever_id');
    	//echo $to;
    	if($to==0){
    		return json_encode(array('answer'=>'no_reciever'));
    	}
    	$q='SELECT user_id, email FROM '.DB_PREFIX.'_user WHERE user_id='.$to;
    	$this->db->exec($q);
    	$this->db->fetch_assoc();
    	if((int)$this->db->row['user_id']==0 || $this->db->row['email']==''){
    		return json_encode(array('answer'=>'no_reciever'));
    	} else {
    	    $n_email=$this->db->row['email'];
    	}
    	
    	
    	
    	
    	$this->setRequestValue('theme', SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('theme')));
    	$this->setRequestValue('message', SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('message')));
    	$this->setRequestValue('name', SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('name')));
    	$theme=$this->getRequestValue('theme');
    	$message=$this->getRequestValue('message');
    	$name=$this->getRequestValue('name');
    	$email=$this->getRequestValue('email');
    	$realty_id=$this->getRequestValue('realty_id');
    	$phone=$this->getRequestValue('phone');
    	
    	if($theme=='' || $message=='' || $name=='' || $email==''){
    		return json_encode(array('answer'=>'fields_not_specified'));
    	}
    	
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    	$form_data[$this->table_name]['creation_date']['value']=date('Y-m-d H:i:s',time());
    	$form_data[$this->table_name]['status']['value']=0;
    	$this->add_data($form_data[$this->table_name]);
    	//return print_r($form_data);
    	
    	
    	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
    	$mailer = new Mailer();*/
    	$smarty->assign('message',$message);
    	$smarty->assign('theme',$theme);
    	$smarty->assign('realty_id',$realty_id);
    	$smarty->assign('realty_href',$this->getRealtyHref($realty_id));
    	$smarty->assign('server_name',$_SERVER['SERVER_NAME']);
    	$smarty->assign('email',$email);
    	$smarty->assign('email_signature',$this->getConfigValue('email_signature'));
    	 
    	$smarty->assign('name',$name);
    	$smarty->assign('phone',$phone);
    	$body=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/admin/template/email.tpl.html');
    	//$this->set_apps_template('accountsms', $this->getConfigValue('theme'), 'main_file_tpl', 'payment_edit_form.tpl.html');
    	$subject = $_SERVER['SERVER_NAME'].': '.$theme;
    	$from = $this->getConfigValue('order_email_acceptor');
    	$this->sendFirmMail($n_email, $from, $subject, $body);
    	/*if ( $this->getConfigValue('use_smtp') ) {
    		$mailer->send_smtp($n_email, $from, $subject, $body, 1);
    	} else {
    		$mailer->send_simple($n_email, $from, $subject, $body, 1);
    	}*/
    	return json_encode(array('answer'=>'sended'));
    	
    }
    
    function send_friend_message(){
    	global $smarty;
    	$uid=(int)$_SESSION['user_id'];
    	$this->setRequestValue('sender_id', $uid);
    	 
    	$link=$this->getRequestValue('link');
    	$to=$this->getRequestValue('to');
    	$message=strip_tags(SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('message')));
    	$email=$this->getRequestValue('email');
    	//echo $to;
    	if($to==''){
    		return json_encode(array('answer'=>'no_reciever'));
    	}
    	$recievers=array();
    	$_recievers=explode(',',$to);
    	foreach($_recievers as $r){
    		$r=trim(strip_tags($r));
    		if(preg_match('/(.+)@(.+)/',$r)){
    			$recievers[]=$r;
    		}
    	}
    	if(empty($recievers)){
    		return json_encode(array('answer'=>'no_reciever'));
    	}
    	
    	$theme='Ссылка от друга';
    	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
    	$mailer = new Mailer();*/
    	$smarty->assign('message',$message);
    	$smarty->assign('link',$link);
    	$smarty->assign('theme',$theme);
    	$body=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/admin/template/email_friend.tpl.html');
    	$subject = $_SERVER['SERVER_NAME'].': '.$theme;
    	$from = $email;
    	foreach($recievers as $r){
    		$this->sendFirmMail($r, $from, $subject, $body);
    		/*if ( $this->getConfigValue('use_smtp') ) {
    			$mailer->send_smtp($r, $from, $subject, $body, 1);
    		} else {
    			$mailer->send_simple($r, $from, $subject, $body, 1);
    		}*/
    	}
    	return json_encode(array('answer'=>'sended'));
    	 
    }
    
    //function main();
    
    private function conv($n) {
    	return SiteBill::iconv(SITE_ENCODING, "utf-8", $n);
    }
}