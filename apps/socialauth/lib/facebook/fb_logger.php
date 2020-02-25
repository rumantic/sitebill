<?php

require_once "facebook.php";

class FB_Logger {

    private static $instance = NULL;
    private $config = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new FB_Logger();
        }
        return self::$instance;
    }

    private function configure() {
        $Config = Config::getInstance();
        $this->config = array(
            'FB_APP_ID' => $Config->getConfigValue('fblogin.api_key'), // ID приложения
            'FB_APP_SECRET' => $Config->getConfigValue('fblogin.secret'), // Защищенный ключ
            'FB_REDIRECT_URI' => (1 === (int) $Config->getConfigValue('work_on_https') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/socialauth/login?do=login_fb', /* $Config->getConfigValue('fblogin.redirect_url'), */
            'DISPLAY' => 'page', // page OR popup OR touch OR wap
            'SCOPE' => array(),
        );
    }

    public function login() {
        $answer = '';

        // Get User ID
        $user = $this->facebook->getUser();

        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $this->facebook->api('/me');

                $Realty_User = Realty_User::getInstance();

                $_login = 'fb' . $user_profile['id'];
                $_pass = md5('fk' . $user_profile['id'] . $user_profile['username'] . $Config->getConfigValue('apps.socialauth.salt'));
                if (!$Realty_User->checkUserExistence($_login, $_pass)) {
                    $now = (string) time();
                    $user_verification_code = md5(uniqid() . $now);
                    $user_verification_time = $now + 3600 * 24;
                    $user_creation_date = date('Y-m-d H:i:s');
                    $Realty_User->registerUser($_login, $_pass, $user_profile['first_name'], $user_profile['last_name'], '', $user_verification_code, date('Y-m-d H:i:s', $user_verification_time), $user_creation_date, '1');
                    $Realty_User->login($_login, $_pass, true);
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: /');
                    die();
                } else {
                    $Realty_User->login($_login, $_pass, true);
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: /');
                    die();
                }
            } catch (FacebookApiException $e) {
                //error_log($e);
                $user = null;
                $answer = $this->getLoginLink();
            }
        }

        return $answer;
    }

    function getLoginLink() {
        $params['network'] = 'fb';
        $params['redirect_url'] = $this->config['FB_REDIRECT_URI'];
        $url = $this->facebook->getLoginUrl($params);
        return '<a href="' . $url . '">Facebook</a>';
    }

    function getLoginURL() {
        $params['network'] = 'fb';
        $params['redirect_url'] = $this->config['FB_REDIRECT_URI'];
        $url = $this->facebook->getLoginUrl($params);
        return $url;
    }

    private function __construct() {
        $this->configure();

        //print_r($this->config);
        $this->facebook = new Facebook(array(
            'appId' => $this->config['FB_APP_ID'],
            'secret' => $this->config['FB_APP_SECRET'],
            'redirect_url' => $this->config['FB_REDIRECT_URI'],
        ));

        if (isset($_SESSION['current_user']) && $_SESSION['current_user']['user_id'] > 0) {
            $this->user_id = $_SESSION['current_user']['user_id'];
            $this->user_name = $_SESSION['current_user']['name'];
        }
    }

    private function __clone() {
        
    }

}
