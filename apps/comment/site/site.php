<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * mailbox fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class comment_site extends comment_admin {

	/*function __construct(){
		$this->type='question';
        $_SESSION['_faq_type']='question';
		parent::__construct();
	}*/
	
	function frontend () {
		if ( !$this->getConfigValue('apps.comment.enable') ) {
			return false;
		}
		$REQUESTURIPATH=trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),'/');
			
		if(SITEBILL_MAIN_URL!=''){
			$REQUESTURIPATH=preg_replace('/^'.trim(SITEBILL_MAIN_URL,'/').'/','',$REQUESTURIPATH);
		}
		/*
		$object_type=$this->getRequestValue('object_type');
		$object_id=$this->getRequestValue('object_id');
		*/
		if(preg_match('/^comments\/([a-z]*)\/([0-9]*)(\/?)/', $REQUESTURIPATH, $matches)){
			
			$object_type=$matches[1];
			$object_id=$matches[2];
			
			$form=$this->getFrontForm($this->getSessionUserId(), $object_type, $object_id);
			$comments=$this->getComments($object_type, $object_id);
			$this->template->assign('app_comment_form', $form);
			$this->template->assign('app_comment_comments', $comments);
			$this->set_apps_template('comment', $this->getConfigValue('theme'), 'main_file_tpl', 'comments_list.tpl');
			//$this->generateCommentPanel($this->getSessionUserId(), $object_type, $object_id);
			return true;
		}
			
		return false;
	}
	
	public function generateCommentPanel($user_id, $object_type, $object_id){
		$comments=$this->getComments($object_type, $object_id);
		$form=$this->getForm($user_id, $object_type, $object_id);
		$this->template->assign('app_comment_form', $form);
		$this->template->assign('app_comment_comments', $comments);
		$this->set_apps_template('comment', $this->getConfigValue('theme'), 'app_comment_list', 'list.tpl');
		$this->set_apps_template('comment', $this->getConfigValue('theme'), 'app_comment_panel', 'app_comment_panel.tpl');
	}
	
	
	
	private function getPublicModel(){
		$form_data=$this->data_model;
		
		$form_data[$this->table_name]['captcha']['name'] = 'captcha';
		$form_data[$this->table_name]['captcha']['title'] = 'Защитный код';
		$form_data[$this->table_name]['captcha']['value'] = '';
		$form_data[$this->table_name]['captcha']['length'] = 40;
		$form_data[$this->table_name]['captcha']['type'] = 'captcha';
		$form_data[$this->table_name]['captcha']['required'] = 'on';
		$form_data[$this->table_name]['captcha']['unique'] = 'off';
		
		unset($form_data[$this->table_name]['is_published']);
		unset($form_data[$this->table_name]['parent_comment_id']);
		unset($form_data[$this->table_name]['comment_date']);
		
		return $form_data;
	}
	
	public function getComments($object_type, $object_id){
		$object_id=(int)$object_id;
		$comments=array();
		if($object_type!='' && $object_id!=0){
			$DBC=DBC::getInstance();
			$query='SELECT c.*, u.fio  
					FROM '.DB_PREFIX.'_'.$this->table_name.' c 
					LEFT JOIN '.DB_PREFIX.'_user u USING(user_id) 
					WHERE c.object_type=? AND c.object_id=? AND c.is_published=1 
					ORDER BY c.comment_date DESC';
			$stmt=$DBC->query($query, array($object_type, $object_id));
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$comments[]=$ar;
				}
			}
		}
		return $comments;
	}
	
	protected function getFrontForm($user_id, $object_type, $object_id){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		if($user_id>0){
			
			if(isset($_POST['submit'])){
				$form_data = $this->getPublicModel();
				$form_data[$this->table_name]['user_id']['type']='hidden';
				$form_data[$this->table_name]['user_id']['value']=$user_id;
				
				$form_data[$this->table_name]['object_id']['type']='hidden';
				$form_data[$this->table_name]['object_id']['value']=$object_id;
				
				$form_data[$this->table_name]['object_type']['type']='hidden';
				$form_data[$this->table_name]['object_type']['value']=$object_type;
				
				$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				
			
			
				$form_data[$this->table_name]['is_published']['value']=1;
				$form_data[$this->table_name]['comment_date']['value']=date('Y-m-d H:i:s', time());
			
				$form_data[$this->table_name]['comment_text']['value']=strip_tags($form_data[$this->table_name]['comment_text']['value']);
			
				if ( !$this->check_data( $form_data[$this->table_name] ) ) {
					return $this->get_form($form_data);
					 
				} else {
					unset($form_data[$this->table_name]['captcha']);
					$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
					
					if ( $this->getError() ) {
						return $this->getError();
					} else {
						$form_data = $this->getPublicModel();
						$form_data[$this->table_name]['user_id']['type']='hidden';
						$form_data[$this->table_name]['user_id']['value']=$user_id;
						
						$form_data[$this->table_name]['object_id']['type']='hidden';
						$form_data[$this->table_name]['object_id']['value']=$object_id;
						
						$form_data[$this->table_name]['object_type']['type']='hidden';
						$form_data[$this->table_name]['object_type']['value']=$object_type;
						return $this->get_form($form_data);
					}
				}
			}else{
				$form_data = $this->getPublicModel();
				$form_data[$this->table_name]['user_id']['type']='hidden';
				$form_data[$this->table_name]['user_id']['value']=$user_id;
				
				$form_data[$this->table_name]['object_id']['type']='hidden';
				$form_data[$this->table_name]['object_id']['value']=$object_id;
				
				$form_data[$this->table_name]['object_type']['type']='hidden';
				$form_data[$this->table_name]['object_type']['value']=$object_type;
				return $this->get_form($form_data);
				
				
			}
			
			
		}else{
			if ( $this->getConfigValue('apps.comment.simple_auth') ) {
				return 'Для того, что бы оставить комментарий, Вам необходимо <a  href="'.SITEBILL_MAIN_URL.'/login/">авторизироваться</a>';
			} else {
				return 'Для того, что бы оставить комментарий, Вам необходимо <a  href="#" data-toggle="modal" data-target="#prettyLogin">авторизироваться</a>';
			}
		}
		 
	}
	
	function get_form($form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php'){
		global $smarty;
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
			
		
		$rs .= '<form class="form-horizontal" method="post">';
		$rs .= '<div class="errors" style="display: none;">Необходимо создать комментарий</div>';
		if ( $this->getError() ) {
			$smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
		}
		
		$el = $form_generator->compile_form_elements($form_data[$this->table_name]);
		
		$el['form_header']=$rs;
		$el['form_footer']='</form>';
		$el['controls']['submit']=array('html'=>'<input type="submit" class="btn" name="submit" value="Добавить" />');
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		return $smarty->fetch($tpl_name);
	}
	
	protected function getForm($user_id, $object_type, $object_id){
		
	    if($user_id>0){
	    	global $smarty;
	    	$this->template->assign('app_comment_user_id', $user_id);
	    	$this->template->assign('app_comment_object_type', $object_type);
	    	$this->template->assign('app_comment_object_id', $object_id);
	    	return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/comment/site/template/form.tpl');
	    	//return 'Форма';
	    }else{
	    	if ( $this->getConfigValue('apps.comment.simple_auth') ) {
	    		return 'Для того, что бы оставить комментарий, Вам необходимо <a  href="'.SITEBILL_MAIN_URL.'/login/">авторизироваться</a>';
	    	} else {
	    		return 'Для того, что бы оставить комментарий, Вам необходимо <a  href="#" data-toggle="modal" data-target="#prettyLogin">авторизироваться</a>';
	    	}
	    }
	    
	}
	
}