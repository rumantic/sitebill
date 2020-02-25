<?php

/**
 * Logout class
 * @author Kondin Dmitry <kondin@etown.ru>
 */
class Logout extends SiteBill {

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }

    /**
     * Main 
     */
    function main() {
        $this->clear_session_and_cookies();
        header('location: ' . SITEBILL_MAIN_URL . '/');
        exit();
        echo '<script type="text/javascript">location.replace(\'http://' . $_SERVER['SERVER_NAME'] . SITEBILL_MAIN_URL . '\'); </script>';
        return 'logout complete';
    }

    function clear_session_and_cookies() {
        setcookie('logged_user_id', '', time() - 60 * 60 * 24 * 5, '/', self::$_cookiedomain);
        setcookie('logged_user_token', '', time() - 60 * 60 * 24 * 5, '/', self::$_cookiedomain);
        $_SESSION['key'] = '';
        $this->oauth_logout($_SESSION['user_id']);
        $this->delete_session_key($_SESSION['session_key']);
        unset($_SESSION['user_id']);
        unset($_SESSION['group']);
        unset($_SESSION['current_user_group_name']);
        unset($_SESSION['current_user_group_id']);
        unset($_SESSION['viewOptions']);
        unset($_SESSION['session_key']);
        unset($_SESSION['current_user_name']);
        unset($_SESSION['current_user_group_title']);
        unset($_SESSION['current_user_info']);
        unset($_SESSION['current_user_tariff_info']);
        unset($_SESSION['key']);
        unset($_SESSION['favorites']);
        session_destroy();
    }

    function oauth_logout ( $user_id ) {
        $DBC = DBC::getInstance();
        $query = "DELETE FROM " . DB_PREFIX . "_oauth WHERE user_id=?";
        $stmt = $DBC->query($query, array((string) $user_id));
    }

}
