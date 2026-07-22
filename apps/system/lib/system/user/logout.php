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
        parent::__construct();
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
        // Track logout before clearing session
        $user_id = $_SESSION['user_id'] ?? null;
        $this->trackUserLogoutMetrics($user_id);
        
        $cookies_list = array('logged_user_id', 'logged_user_token', 'user_favorites', 'last_viewed_data');
        foreach ($cookies_list as $c){
            setcookie($c, '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
        }
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

    /**
     * Track user logout metrics
     * @param int|null $user_id
     */
    private function trackUserLogoutMetrics($user_id)
    {
        if (class_exists('Metrics') && $user_id) {
            $metadata = [
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            Metrics::trackLogout($user_id, $metadata);
        }
    }

}
