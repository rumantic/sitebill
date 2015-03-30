<?php
/**
 * Profile editor using model
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class User_Profile_Model extends User_Profile {
	function main () {
		$user_id=$this->getSessionUserId();
		if($user_id!=0){
			$this->setRequestValue($this->primary_key, $user_id);
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
			$data_model = new Data_Model();
			$form_data = $this->data_model;
			
			$rs.='<h1>'.Multilanguage::_('PROFILE','system').'</h1>';
			$rs.=$this->getTopMenu();
			
			
	
			switch( $this->getRequestValue('do') ){
	
				case 'edit_done' : {
					
					$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
					if(isset($_POST['delpic'])){
						$this->deleteUserpic($user_id);
					}
					unset($form_data[$this->table_name]['company_id']);
					unset($form_data[$this->table_name]['group_id']);
					unset($form_data[$this->table_name]['login']);
					unset($form_data[$this->table_name]['publication_limit']);
					unset($form_data[$this->table_name]['captcha']);
					unset($form_data[$this->table_name]['active']);
	
					if ( !$this->check_data( $form_data[$this->table_name] ) ) {
						
						$rs = $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/account/profile/'  );
					} else {
						$this->edit_data($form_data[$this->table_name]);
						if ( $this->getError() ) {
							$rs = $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/account/profile/');
						} else {
							$this->updateUserPicture($user_id);
							$rs .= $this->showProfile($user_id);
						}
					}
					break;
				}
				 
				case 'edit' : {
					
					
					if ( $this->getRequestValue('subdo') == 'delete_image' ) {
						$this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
					}
	
					if ( $this->getRequestValue('subdo') == 'up_image' ) {
						$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key),'up');
					}
	
					if ( $this->getRequestValue('subdo') == 'down_image' ) {
						$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
					}
	
					if ( $this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id')) ) {
						$rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'), '', SITEBILL_MAIN_URL.'/account/profile/');
					} else {
						if ( $this->getRequestValue('language_id') > 0 ) {
							$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
						} else {
							$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
						}
						unset($form_data[$this->table_name]['company_id']);
						unset($form_data[$this->table_name]['group_id']);
						unset($form_data[$this->table_name]['login']);
						unset($form_data[$this->table_name]['publication_limit']);
						unset($form_data[$this->table_name]['captcha']);
						unset($form_data[$this->table_name]['active']);
						$rs = $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/account/profile/');
					}
						
					break;
				}
	
				default : {
					$rs .= $this->showProfile($user_id);
				}
			}
		}else{
			$rs='';
		}
		 
		return $rs;
	}
	
	
	/*
	protected function deleteUserImage($user_id){
		$DBC=DBC::getInstance();
		$query='SELECT imgfile FROM '.DB_PREFIX.'_user WHERE user_id=?';
		$stmt=$DBC->query($query, array($user_id));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$imgfile_directory=SITEBILL_DOCUMENT_ROOT.'/img/data/user/';
			@unlink($imgfile_directory.$ar['imgfile']);
			$query='UPDATE '.DB_PREFIX.'_user SET imgfile=\'\' WHERE user_id=?';
			$stmt=$DBC->query($query, array($user_id));
		}
	}
	*/
	
	private function showProfile($user_id){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $user_id, $this->data_model[$this->table_name] );
		unset($form_data['captcha']);
		unset($form_data['active']);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_view.php');
		$form_view = new Form_View_Generator();

		$rs .= '<table>';
		$rs .= $form_view->compile_form($form_data);
		$rs .= '</table>';
		//return $rs;
		
		$rs='<div id="view_table">'.$rs.'</div>';
		$rs.='<form method="post">';
		$rs.='<input type="hidden" name="do" value="edit" />';
		$rs.='<input type="submit" name="submit" class="btn btn-primary" value="'.Multilanguage::_('EDIT_PROFILE','system').'" />';
		$rs.='</form>';
		return $rs;
	}
}