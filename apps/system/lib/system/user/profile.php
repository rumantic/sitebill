<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php';
/**
 * User profile manager
 * @author http://www.sitebill.ru 
 */
class User_Profile extends User_Object_Manager {
    var $user_image_dir = 'img/data/user/'; 
    /**
     * Constructor
     */    
    function __construct() {
    	parent::__construct();
    	//unset($this->data_model[$this->table_name]['group_id']);
    	unset($this->data_model[$this->table_name]['tariff_id']);
    	unset($this->data_model[$this->table_name]['newpass']);
    	unset($this->data_model[$this->table_name]['newpass_retype']);
    	 
    	/*
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
    	$this->data_model_object=new Data_Model();
    	$this->data_model=$this->get_user_model();
    	$this->table_name='user';
    	$this->primary_key='user_id';
    	*/
        //$this->Account();
    }
    
    
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
				    unset($form_data[$this->table_name]['company_id']);
                    unset($form_data[$this->table_name]['group_id']);
                    unset($form_data[$this->table_name]['login']);
                    unset($form_data[$this->table_name]['publication_limit']);
                    unset($form_data[$this->table_name]['active']);
                    
		            if ( !$this->check_data( $form_data[$this->table_name] ) ) {
				        $rs = $this->get_form($form_data[$this->table_name], 'edit');
				    } else {
				        $this->edit_data($form_data[$this->table_name]);
				        if ( $this->getError() ) {
				            $rs = $this->get_form($form_data[$this->table_name], 'edit');
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
	    			    $rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
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
	                    unset($form_data[$this->table_name]['active']);
	                    $rs = $this->get_form($form_data[$this->table_name], 'edit');
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
    
    /**
     * Main
     * @param void
     * @return string
     */
	/*
    function main1 () {
		$user_id=$this->getSessionUserId();
	    $rs.='<h1>Профиль</h1>';
		if($user_id!=0){
			switch($_POST['action']){
				case 'edit' : {
					$rs.=$this->getEditForm($user_id);
					break;
				}
				case 'save' : {
					if(isset($_POST['submit'])){
					
						$nd=array();
						$nd['fio']=$this->prepareData($this->getRequestValue('fio'));
						$nd['email']=$this->prepareData($this->getRequestValue('email'));
						$nd['phone']=$this->prepareData($this->getRequestValue('phone'));
						$nd['mobile']=$this->prepareData($this->getRequestValue('mobile'));
						$nd['icq']=$this->prepareData($this->getRequestValue('icq'));
						$nd['site']=$this->prepareData($this->getRequestValue('site'));
						if($this->getRequestValue('delpic')=='yes'){
							$nd['imgfile']='';
						}
						$this->updateUserProfile($user_id,$nd);
						$rs.=$this->updateUserPicture($user_id);
					}else{
						
					}
					$rs.='Профиль сохранен<br>';
					$rs .= '<a href="'.SITEBILL_MAIN_URL.'/account/profile">Вернуться к просмотру профиля</a>';
					break;
				}
				case 'editpass' : {
					$rs.='pass editing';
					break;
				}
				default : {
					$data=$this->getUserProfileData($user_id);
					$rs.='<table>';
					$rs.='<tr><td>Имя</td><td>'.$data['fio'].'</td>';
					$rs.='<tr><td>Телефон</td><td>'.$data['phone'].'</td>';
					$rs.='<tr><td>Мобильный телефон</td><td>'.$data['mobile'].'</td>';
					$rs.='<tr><td>ICQ#</td><td>'.$data['icq'].'</td>';
					$rs.='<tr><td>Сайт</td><td>'.$data['site'].'</td>';
					$rs.='<tr><td>E-mail</td><td>'.$data['email'].'</td>';
					if($data['imgfile']!=''){
						$rs.='<tr><td colspan="2"><img src="'.SITEBILL_MAIN_URL.'/img/data/user/'.$data['imgfile'].'"><td></td>';
					}
					$rs.='</table>';
			
					$rs.='<form method="post">';
					$rs.='<input type="hidden" name="action" value="edit" />';
					$rs.='<input type="submit" name="submit" value="Изменить профиль" />';
					$rs.='</form>';
				}
			}		
		}else{
			$rs.='';
		}
        return '<div id="view_table">'.$rs.'</div>';
    }
	*/
	
	
	private function showProfile($user_id){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
		$form_data = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $user_id, $this->data_model[$this->table_name] );
		
		$rs='';
		if(1==$this->getConfigValue('apps.company.enable')){
			$rs.='<div class="service_links">';
			$rs.='<a href="javascript:void(0);" onClick="companyeditor.hideCompanyPage(this);" class="active">Профиль</a>';
			if ( $this->getConfigValue('apps.company.profile_in_lk') ) {
				$rs.='<a href="javascript:void(0);" onClick="companyeditor.showCompanyPage(this);">Компания</a>';
			}
			$rs.='</div>';
		}
		
		
		$rs.='<div id="view_table">';
		$rs.='<table>';
		$rs.='<tr><td>'.Multilanguage::_('L_FIO').'</td><td>'.$form_data['fio']['value'].'</td>';
		$rs.='<tr><td>'.Multilanguage::_('L_PHONE').'</td><td>'.$form_data['phone']['value'].'</td>';
		$rs.='<tr><td>'.Multilanguage::_('L_CELLPHONE').'</td><td>'.$form_data['mobile']['value'].'</td>';
		$rs.='<tr><td>'.Multilanguage::_('L_ICQNR').'</td><td>'.$form_data['icq']['value'].'</td>';
		$rs.='<tr><td>'.Multilanguage::_('L_SITE').'</td><td>'.$form_data['site']['value'].'</td>';
		$rs.='<tr><td>'.Multilanguage::_('L_EMAIL').'</td><td>'.$form_data['email']['value'].'</td>';
		if($form_data['imgfile']['value']!=''){
			$rs.='<tr><td colspan="2"><img src="'.SITEBILL_MAIN_URL.'/img/data/user/'.$form_data['imgfile']['value'].'"><td></td>';
		}
		$rs.='</table>';
		//.$rs/*.'</div>'*/;
		$rs.='<form method="post">';
		$rs.='<input type="hidden" name="do" value="edit" />';
		$rs.='<input type="submit" name="submit" value="'.Multilanguage::_('EDIT_PROFILE','system').'" />';
		$rs.='</form>';
		$rs.='</div>';
		
		
		if(1==$this->getConfigValue('apps.company.enable')){
			//require_once SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php';
			//$CA=new company_admin();
			//$company_info=$CA->getUserCompany($user_id);
			//print_r($company_info);
			$rs.='<div id="company_view_table" style="display: none;">';
			$rs.='<h3>Моя компания</h3>';
			$rs.='<div id="company_data_container"></div>';
			$rs.='<div id="company_data_view">';
			
			$rs.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/company/js/utils.js"></script>';
			$rs.='<script type="text/javascript">companyeditor.showCompany("company_data_view");</script>';
			$rs.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/company/site/template/css/style.css" media="screen">';
			$rs.='<a href="javascript:void(0);" onClick="companyeditor.edit();">Редактировать</a>';
			$rs.='</div>';
			$rs.='</div>';
		}
		
		return $rs;
	}
	
	public function updateUserPicture($user_id){
	    $imgfile_directory=$this->user_image_dir;
		$avial_ext=array('jpg', 'jpeg', 'gif', 'png');
		if(isset($_FILES['imgfile'])){
			
			if(($_FILES['imgfile']['error']!=0)OR($_FILES['imgfile']['size']==0)){
				//echo 'Не указан или указан не верно файл для загрузки<br>';
			}else{
				//$ret='No errors';
				$fprts=explode('.',$_FILES['imgfile']['name']);
				//print_r($fprts,true);
				
				if(count($fprts)>1){
					$ext=strtolower($fprts[count($fprts)-1]);
					if(in_array($ext,$avial_ext)){
						$usrfilename=time().'.'.$ext;
						//echo $imgfile_directory.$usrfilename;
                        $i = rand(0, 999);
						$preview_name="img".uniqid().'_'.time()."_".$i.".".$ext;
                        $preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
						
						if(! move_uploaded_file($_FILES['imgfile']['tmp_name'], SITEBILL_DOCUMENT_ROOT.'/'.$imgfile_directory.$preview_name_tmp) ){
							
						}else{
							$mode=1;
							if(1==$this->getConfigValue('user_pic_smart')){
								$mode='smart';
							}
							$mode='smart';
							
                            list($width,$height)=$this->makePreview(SITEBILL_DOCUMENT_ROOT.'/'.$imgfile_directory.$preview_name_tmp, $sitebill_document_root.'/'.$imgfile_directory.$preview_name, $this->getConfigValue('user_pic_width'),$this->getConfigValue('user_pic_height'), $ext,$mode);
                            unlink(SITEBILL_DOCUMENT_ROOT.'/'.$imgfile_directory.$preview_name_tmp);
                            
							$query='UPDATE '.DB_PREFIX.'_user SET imgfile=? WHERE user_id=?';
							$DBC=DBC::getInstance();
							$stmt=$DBC->query($query, array($preview_name, $user_id));
						}
					}
					
				}
			}
		}
	}
	

	
	function getEditForm($user_id){
		$imgfile_directory=$this->user_image_dir;
	    
		$data=$this->getUserProfileData($user_id);
		$ret='';
		$ret.='<table>';
		$ret.='<form method="post" enctype="multipart/form-data">';
		$ret.='<tr><td>'.Multilanguage::_('L_FIO').'</td><td><input type="text" name="fio" value="'.$data['fio'].'" /></td><td></td>';
		$ret.='<tr><td>'.Multilanguage::_('L_PHONE').'</td><td><input type="text" name="phone" value="'.$data['phone'].'" /></td><td></td>';
		$ret.='<tr><td>'.Multilanguage::_('L_CELLPHONE').'</td><td><input type="text" name="mobile" value="'.$data['mobile'].'" /></td><td></td>';
		$ret.='<tr><td>'.Multilanguage::_('L_ICQNR').'</td><td><input type="text" name="icq" value="'.$data['icq'].'" /></td><td></td>';
		$ret.='<tr><td>'.Multilanguage::_('L_SITE').'</td><td><input type="text" name="site" value="'.$data['site'].'" /></td><td></td>';
		//$ret.='<tr><td>Логин</td><td><input type="text" name="login" value="'.$data['login'].'" /></td><td></td>';
		//$ret.='<tr><td>Новый Пароль</td><td><input type="password" name="newpass" value="" /></td><td></td>';
		//$ret.='<tr><td>Повторить Новый Пароль</td><td><input type="password" name="newpassconfirm" value="" /></td><td></td>';
		$ret.='<tr><td>'.Multilanguage::_('L_EMAIL').'</td><td><input type="text" name="email" value="'.$data['email'].'" /></td><td></td>';
		if($data['imgfile']!=''){
			$ret.='<tr><td colspan="2"><img src="'.SITEBILL_MAIN_URL.'/'.$imgfile_directory.''.$data['imgfile'].'"><td><td>Удалить <input type="checkbox" name="delpic" value="yes" /></td></td>';
		}
		$ret.='<tr><td>Picture</td><td><input type="file" name="imgfile" /></td><td></td>';
		$ret.='<input type="hidden" name="action" value="save" />';
		$ret.='<tr><td colspan="3"><input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SAVE').'" /></td>';
		$ret.='</form>';
		$ret.='</table>';
		return $ret;
	}
	
	function getUserProfileData($user_id){
		$query = 'SELECT * FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query, array($user_id));
		if($stmt){
			return $DBC->fetch($stmt);
		}
        return array();
	}
	
	function updateUserProfile($user_id,$nd){
	    $is_new = false;
	    if ( !$this->getUserProfileData($user_id) )  {
	        $is_new = true;
	    }
	    
	    if ( $is_new ) {
		    $qparts=array();
		    $set[] = 'user_id';
		    $values[] = $user_id;
		    foreach($nd as $k=>$v){
		        $set[] = '`'.$k.'`';
		        $values[] = '\''.$v.'\'';
		    }
		    $query = 'INSERT INTO '.DB_PREFIX.'_user ('.implode(' , ',$set).') VALUES ('.implode(' , ',$values).') ';
		    //echo $query;
	    } else {
		    $qparts=array();
		    foreach($nd as $k=>$v){
			    $qparts[]=$k.'="'.$v.'"';
		    }
		    $query = 'UPDATE '.DB_PREFIX.'_user SET '.implode(',',$qparts).' WHERE user_id='.$user_id;
	    }
	    $DBC=DBC::getInstance();
	    $stmt=$DBC->query($query);
        
		if($stmt){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	function getTopMenu () {
	    $rs = '';
	    return $rs;
	}
}