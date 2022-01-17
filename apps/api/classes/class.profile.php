<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Profile REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_profile extends API_Common {
    /**
     * @var Permission
     */
    protected $permission;
    /**
     * @var User_Profile_Model
     */
    private $profile;

    function __construct()
    {
        parent::__construct();
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $this->permission = new Permission();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile_using_model.php');
        $this->profile = new \User_Profile_Model();


    }
    public function _delete_avatar () {
        $error = false;

        try {
            $user_id = $this->get_my_user_id();
            if ( !$user_id ) {
                throw new Exception('cant define user_id');
            }
            $update_user_id = $this->request()->get('update_user_id');
            if (
                $update_user_id and
                $user_id != $update_user_id and
                !$this->permission->get_access($user_id, 'user', 'access')
            ) {
                throw new Exception('permission denied');
            } elseif ( !isset($update_user_id) ) {
                throw new Exception('undefined update_user_id');
            }

            $this->profile->deleteUserpic($update_user_id);
            if ( $this->profile->getError() ) {
                throw new Exception($this->profile->getError());
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }



        if ( $error ) {
            $ret = array(
                'status' => 'error',
                'message' => $error,
            );
        } else {
            $ret = array(
                'status' => 'ok',
                'message' => 'avatar deleted successfully',
            );
        }
        return $this->json_string($ret);
    }

    public function _update_avatar () {
        $error = false;

        try {
            $user_id = $this->get_my_user_id();
            if ( !$user_id ) {
                throw new Exception('cant define user_id');
            }
            $update_user_id = $this->request()->get('update_user_id');
            if (
                $update_user_id and
                $user_id != $update_user_id and
                !$this->permission->get_access($user_id, 'user', 'access')
            ) {
                throw new Exception('permission denied');
            } elseif ( !isset($update_user_id) ) {
                throw new Exception('undefined update_user_id');
            }

            $file = $this->request()->file('img');
            $avial_ext = array('jpg', 'jpeg', 'gif', 'png');

            if ( $file->isValid() and  in_array($file->extension(), $avial_ext)) {

                // Удалить предыдущий аватар
                if ( $update_user_id != 'new' ) {
                    $this->profile->deleteUserpic($update_user_id);
                    if ( $this->profile->getError() ) {
                        throw new Exception($this->profile->getError());
                    }
                }
                $avatar_name = "usr" . uniqid() . '_' . time() . "." . $file->extension();
                $file->move($this->profile->get_avatar_dir_full_path(), $avatar_name);
                if ( $update_user_id != 'new' ) {
                    $this->profile->update_user_pic($update_user_id, $avatar_name);
                    if ( $this->profile->getError() ) {
                        throw new Exception($this->profile->getError());
                    }
                } else {
                    $_SESSION['new_avatar_img'] = $avatar_name;
                }
            } else {
                throw new Exception('upload file failed');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if ( $error ) {
            $ret = array(
                'status' => 'error',
                'message' => $error,
            );
        } else {
            $ret = array(
                'status' => 'ok',
                'message' => 'avatar updated successfully',
            );
        }
        return $this->json_string($ret);
    }

}
