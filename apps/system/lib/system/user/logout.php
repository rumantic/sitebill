<?php
/**
 * Logout class
 * @author Kondin Dmitry <kondin@etown.ru>
 */
class Logout extends SiteBill {
    /**
     * Constructor
     */
    function Logout () {
        $this->SiteBill();
    }
    
    /**
     * Main 
     */
    function main () {
    	setcookie('logged_user_id', '', time()-60*60*24*5, '/');
    	setcookie('logged_user_token', '', time()-60*60*24*5, '/');
        $_SESSION['key'] = '';
        $this->delete_session_key($_SESSION['session_key']);
        unset($_SESSION['user_id']);
        unset($_SESSION['group']);
		unset($_SESSION['current_user_group_name']);
		unset($_SESSION['current_user_group_id']);
		unset($_SESSION['viewOptions']);
        unset($_SESSION['session_key']);
        unset($_SESSION['current_user_name']);
        unset($_SESSION['key']);
        
        //unset($_SESSION['Sitebill_User']);
        unset($_SESSION['favorites']);
        /*$Sitebill_User=Sitebill_User::getInstance();
        $Sitebill_User->logoutUser();*/
        header('location: '.SITEBILL_MAIN_URL.'/');
        exit();
        echo '<script type="text/javascript">location.replace(\'http://'.$_SERVER['SERVER_NAME'].SITEBILL_MAIN_URL.'\'); </script>';
        return 'logout complete';
    }
}
?>