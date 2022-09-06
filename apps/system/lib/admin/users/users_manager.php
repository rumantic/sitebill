<?php
/**
 * Users manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 * @url http://www.sitebill.ru
 */
define('DB_PREFIX', 're');
//define('SITEBILL_MAIN_URL', 'http://'.$_SERVER['SERVER_NAME']);
//$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'];

class Users_Manager extends SiteBill_Krascap {
    var $user_image_dir = '/img/data/user/';

    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
		$user_id = $this->getRequestValue('user_id');

        switch ( $this->getRequestValue('do') ) {

            case 'edit':
                $hash = $this->load($this->getRequestValue('user_id'));
                $rs = $this->getEditForm('edit');
            break;

            case 'new_done':
                if ( !$this->checkData() ) {
                    $rs = $this->getEditForm('new');
                } else {
                    $user_id = $this->add_user();
					$rs.=$this->updateUserPicture($user_id);

                    $rs = $this->get_top_menu();
                    $rs .= $this->grid();
                }
            break;

            case 'delete':
                $this->delete_user($this->getRequestValue('user_id'));
                $rs = $this->get_top_menu();
                $rs .= $this->grid();
            break;

            case 'new':
                $rs = $this->getEditForm('new');
            break;

            case 'edit_done':
                if ( !$this->checkData() ) {
                    $rs = $this->getEditForm('edit');
                } else {
					$nd=array();
					$nd['fio']=$this->prepareData($this->getRequestValue('fio'));
					$nd['email']=$this->prepareData($this->getRequestValue('email'));
					$nd['phone']=$this->prepareData($this->getRequestValue('phone'));
					$nd['site']=$this->prepareData($this->getRequestValue('site'));
					if($this->getRequestValue('delpic')=='yes'){
						$nd['imgfile']='';
					}
					$this->updateUserProfile($user_id,$nd);
					$rs.=$this->updateUserPicture($user_id);
					if ( !$this->getConfigValue('ajax_auth_form') ) {
                        if ( $this->getRequestValue('newpass') != '' ) {
                            $this->editPassword($this->getRequestValue('user_id'), $this->getRequestValue('newpass'));
                        }
					}
                    $rs = $this->get_top_menu();
                    $rs .= $this->grid();
                }
            break;

            default:
                $rs = $this->get_top_menu();
                $rs .= $this->grid();
        }
        return $rs;
    }

    /**
     * Add ajax user
     * @param int $user_id
     * @param string $fio
     * @param string $email
     * @param array $params
     */
    function add_ajax_user ( $user_id, $fio, $email, $params = array() ) {
        $phone = $params['phone'];
        $mobile = $params['mobile'];
        $icq = $params['icq'];
        $site = $params['site'];

        $query = "insert into ".DB_PREFIX."_user (user_id, reg_date, fio, email, phone, mobile, icq, site) values (".$user_id.",now(), '".$fio."', '".$email."',  '".$phone."', '".$mobile."', '".$icq."',  '".$site."' )";
        $DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    }

    /**
     * Add user
     * @param void
     * @return int - new user ID
     */
    function add_user () {
        $data = $this->initDataFromRequest();
        $query = "insert into ".DB_PREFIX."_user (login, reg_date, password, fio, email, phone, site) values ('".$data['login']."', now(), '".md5($data['password'])."', '".$data['fio']."', '".$data['email']."','".$data['phone']."','".$data['site']."')";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
    }

    /**
     * Delete user
     * @param int $user_id
     */
    function delete_user ( $user_id ) {
        $query = "delete from ".DB_PREFIX."_user where user_id=$user_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
    }

    /**
     * Edit password
     * @param int $user_id user id
     * @param string $password password
     * @return boolean
     */
    function editPassword ( $user_id, $password ) {
        $query = "update ".DB_PREFIX."_user set password='".md5($password)."' where user_id=$user_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
        return true;
    }

    function get_top_menu () {
        if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = SITEBILL_MAIN_URL.'/';
        }
        if ( $this->getConfigValue('ajax_auth_form') ) {
            $rs = '<a href="#" onclick="run_command(\'add_user\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].$add_folder.'\', \''.$_SESSION['session_key'].'\'); return false;">Добавить пользователя</a>';
        } else {
            $rs = '<a href="?action=users&do=new">'.Multilanguage::_('ADD_USER','system').'</a>';
        }
        return $rs;
    }


    /**
     * Check password
     * @param int $user_id user id
     * @param string $password password
     * @return boolean
	 */
    function checkPassword ( $user_id, $password ) {
       	$query = "select user_id from ".DB_PREFIX."_user where user_id=$user_id and password='".md5($password)."'";
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
    	if($stmt){
			$ar=$DBC->fetch($stmt);
			if ( $ar['user_id'] > 0 ) {
				return true;
			}
		}
		return false;
    }

    /**
     * Check crypt
     * @param string $password password
     * @return boolean
	 */
    function checkCrypt ( $password ) {
        //echo "<br>checkCrypt<br>";
        //echo "password = $password<br>";
        if ( strlen($password) < 5 ) {
            $this->riseError(sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH','system'),'5'));
            return false;
        }
		return true;
    }


    /**
     * Check data
     * @param void
     * @return boolean
     */
    function checkData () {
        global $__user;
        /*
       	if ( !$this->checkPassword($this->getRequestValue('user_id'), $this->getRequestValue('pass')) ) {
           	$this->riseError('Неправильно указан текущий пароль');
           	return false;
       	}
        */
        //print_r($_REQUEST);
        if ( eregi('etown', $__user) ) {
            $this->riseError(Multilanguage::_('L_MESSAGE_THIS_IS_TRIAL_PASS'));
            return false;
        }
        if ( $this->getRequestValue('do') == 'new_done' ) {
        }

        if ( !$this->getConfigValue('ajax_auth_form') ) {
            if ( $this->getRequestValue('fio') == '' ) {
                $this->riseError(Multilanguage::_('L_ERROR_NAME_NOT_SPECIFIED'));
                return false;
            }

            if ( $this->getRequestValue('login') == '' ) {
                $this->riseError(Multilanguage::_('L_ERROR_LOGIN_NOT_SPECIFIED'));
                return false;
            }

            if ( !$this->checkLogin($this->getRequestValue('login')) and $this->getRequestValue('do') != 'edit_done' ) {
                $this->riseError(Multilanguage::_('LOGIN_IS_REGISTERED','system'));
                return false;
            }
            if ( $this->getRequestValue('do') == 'new_done' or $this->getRequestValue('newpass') != '' ) {
                if ( strlen($this->getRequestValue('newpass')) < 5 ) {
                    $this->riseError(sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH','system'),'5'));
                    return false;
                }
                if ( $this->getRequestValue('newpass') != $this->getRequestValue('newpassconfirm') ) {
                    $this->riseError(Multilanguage::_('PASSWORDS_NOT_EQUAL','system'));
                    return false;
                }
            }

        }

        return true;
    }

    /**
     * Check login
     * @param string $login login
     * @return boolean
     */
    function checkLogin ( $login ) {
		$query = 'select count(*) as cid from '.DB_PREFIX.'_user where login=\''.$login.'\'';
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if ( $ar['cid'] > 0 ) {
				return false;
			}
		}
		return true;
    }


	private function prepareData($d){
		$dd=$d;
		return $dd;
	}

	private function updateUserPicture($user_id){
        /*if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = SITEBILL_MAIN_URL.'/';
        }*/


	    //global $sitebill_document_root;
	    //echo '$sitebill_document_root = '.$sitebill_document_root.'<br>';
	    //echo '$add_folder = '.$add_folder.'<br>';

	    $imgfile_directory=$this->user_image_dir;
	    /*$document_root = $_SERVER['DOCUMENT_ROOT'].$add_folder; */

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

						if(! move_uploaded_file($_FILES['imgfile']['tmp_name'], SITEBILL_DOCUMENT_ROOT.$imgfile_directory.$preview_name_tmp) ){

						}else{
                            list($width,$height)=$this->makePreview(SITEBILL_DOCUMENT_ROOT.$imgfile_directory.$preview_name_tmp, SITEBILL_DOCUMENT_ROOT.$imgfile_directory.$preview_name, 160,160, $ext,1);
                            unlink(SITEBILL_DOCUMENT_ROOT.$imgfile_directory.$preview_name_tmp);

							$query='UPDATE '.DB_PREFIX.'_user SET imgfile="'.$preview_name.'" WHERE user_id='.$user_id;
							$DBC=DBC::getInstance();
							$stmt=$DBC->query($query);
						}
					}

				}
			}
		}
		//return $ret;
	}

	/**
	 * Init data from
	 * @param void
	 * @return array
	 */
	function initDataFromRequest() {
	    $data = array();
	    if ( !$this->getConfigValue('ajax_auth_form') ) {
	        $data['login'] = $this->getRequestValue('login');
	        $data['password'] = $this->getRequestValue('newpass');
	    }
	    $data['fio'] = $this->getRequestValue('fio');
	    $data['phone'] = $this->getRequestValue('phone');
	    $data['site'] = $this->getRequestValue('site');
	    $data['email'] = $this->getRequestValue('email');
	    return $data;
	}

	/**
	 * Get edit from
	 * @param string $form
	 * @return string
	 */
	function getEditForm($form = 'edit'){
        if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = SITEBILL_MAIN_URL.'/';
        }

		$imgfile_directory=$this->user_image_dir;
	    if ( $form == 'edit' ) {
		    $data = $this->getUserProfileData($this->getRequestValue('user_id'));
	    } else {
		    $data = $this->initDataFromRequest();
	    }
		$ret='';
		$ret.='<table border="0">';
		$ret.='<form method="post" action="?action=users" enctype="multipart/form-data">';
        if ( $this->GetError() ) {
            $ret .= '<tr>';
            $ret .= '<td colspan="2"><span class="error" style="color: red;">'.$this->GetError().'</span></td>';
            $ret .= '</tr>';
        }
		$ret.='<tr><td>Имя <span class="error">*</span></td><td><input type="text" name="fio" value="'.$data['fio'].'" /></td><td></td>';
		$ret.='<tr><td>Телефон</td><td><input type="text" name="phone" value="'.$data['phone'].'" /></td><td></td>';
		$ret.='<tr><td>Сайт</td><td><input type="text" name="site" value="'.$data['site'].'" /></td><td></td>';
		if ( !$this->getConfigValue('ajax_auth_form') ) {
    		$ret.='<tr><td>'.Multilanguage::_('L_LOGIN').' <span class="error">*</span></td><td><input type="text" name="login" value="'.$data['login'].'" /></td><td></td>';
	    	$ret.='<tr><td>'.Multilanguage::_('NEW_PASS','system').'</td><td><input type="password" name="newpass" value="" /></td><td></td>';
		    $ret.='<tr><td>'.Multilanguage::_('RETYPE_NEW_PASS','system').'</td><td><input type="password" name="newpassconfirm" value="" /></td><td></td>';
		}
		$ret.='<tr><td>E-mail</td><td><input type="text" name="email" value="'.$data['email'].'" /></td><td></td>';
		if($data['imgfile']!=''){
			$ret.='<tr><td></td><td><img src="'.SITEBILL_MAIN_URL.''.$imgfile_directory.''.$data['imgfile'].'"><br>
			Удалить <input type="checkbox" name="delpic" value="yes" />
			</td>';
		}
		$ret.='<tr><td>'.Multilanguage::_('L_PHOTO').': </td><td><input type="file" name="imgfile" /></td><td></td>';

		if ( $this->getConfigValue('ajax_auth_form') ) {
		    $ret.='<tr><td>'.Multilanguage::_('L_PASSWORD').': </td><td><div id="admin_area"><input type="password" value="******" disabled><a href="#" onclick="run_command(\'change_password&user_id='.$this->getRequestValue('user_id').'\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].$add_folder.'\', \''.$_SESSION['session_key'].'\'); return false;">'.Multilanguage::_('CHANGE_PASSWORD','system').'</a></div></td><td></td>';
		}
		if ( $form == 'edit' ) {
		    $ret .= '<input type="hidden" name="do" value="edit_done" />';
		    $ret.='<input type="hidden" name="user_id" value="'.$this->getRequestValue('user_id').'" />';
		} else {
		    $ret .= '<input type="hidden" name="do" value="new_done" />';
		}

		$ret.='<input type="hidden" name="action" value="users" />';
		$ret.='<tr><td colspan="3"><input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SAVE').'" /></td>';
		$ret.='</form>';
		$ret.='</table>';
		return $ret;
	}

	function getUserProfileData($user_id){
		$query = 'SELECT * FROM '.DB_PREFIX.'_user WHERE user_id='.$user_id;
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			return $DBC->fetch($stmt);
		}
        return array();
	}

	function updateUserProfile($user_id,$nd){
		$qparts=array();
		foreach($nd as $k=>$v){
			$qparts[]=$k.'="'.$v.'"';
		}
		$query = 'UPDATE '.DB_PREFIX.'_user SET '.implode(',',$qparts).' WHERE user_id='.$user_id;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			return TRUE;
		}else{
			return FALSE;
		}
	}

    /**
     * Load
     * @param int $record_id record ID
     * @return boolean
     */
    function load ( $record_id ) {
        $query = "select * from ".DB_PREFIX."_user where user_id=$record_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$this->setRequestValue('login', $ar['login']);
		}
    }


    /**
     * Grid
     * @param void
     * @return string
     */
    function grid () {
        if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = SITEBILL_MAIN_URL.'/';
        }

        global $_SESSION;
        $query = "select * from ".DB_PREFIX."_user order by user_id asc";
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);

        $rs .= '<div id="admin_area">';
        $rs .= '<div align="left"><table border="0" width="20%">';
        $rs .= '<td ><b>Имя</b></td>';
        $rs .= '<td></td>';
        $rs .= '<td></td>';
        $rs .= '</tr>';
        if($stmt){
        	while ( $ar=$DBC->fetch($stmt) ) {
	            $j++;
	            if ( ceil($j/2) > floor($j/2)  ) {
	                $row_class = "row1";
	            } else {
	                $j = 0;
	                $row_class = "row2";
	            }
	            $rs .= '<tr>';
	            $rs .= '<td class="'.$row_class.'" nowrap width="99%">'.$ar['fio'].'</td>';
	            $rs .= '<td width="10%" nowrap>';
	            $rs .= '<a href="?action=users&do=edit&user_id='.$ar['user_id'].'"><img src="'.SITEBILL_MAIN_URL.'/img/edit.gif" border="0"></a>';
	            $rs .= '</td>';
	            $rs .= '<td width="10%" nowrap>';
	            if ( $this->getConfigValue('ajax_auth_form') ) {
	                $rs .= '<a href="?action=users&do=delete&user_id='.$ar['user_id'].'" onclick="run_command(\'delete_user&user_id='.$ar['user_id'].'\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].$add_folder.'\', \''.$_SESSION['session_key'].'\'); return false;"><img src="'.SITEBILL_MAIN_URL.'/img/delete.gif" border="0"></a>';
	            } else {
	                $rs .= '<a href="?action=users&do=delete&user_id='.$ar['user_id'].'" onclick="return confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\');"><img src="'.SITEBILL_MAIN_URL.'/img/delete.gif" border="0"></a>';
	            }
	            $rs .= '</td>';
	            $rs .= '</tr>';
	        }
        }

        $rs .= '</table></div>';
        $rs .= '</div>';

        return $rs;
    }

}
