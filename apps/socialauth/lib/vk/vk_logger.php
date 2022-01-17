<?php

require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/common_logger.php';

class Vk_Logger extends Common_Logger {

    private static $instance = NULL;
    private $config = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new Vk_Logger();
        }
        return self::$instance;
    }

    private function configure() {
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $Config = new config_admin();

        $this->config = array(
            'VK_APP_ID' => $Config->getConfigValue('apps.socialauth.vk.api_key'), // ID приложения
            'VK_APP_SECRET' => $Config->getConfigValue('apps.socialauth.vk.secret'), // Защищенный ключ
            'REDIRECT_URI' => (1 === (int) $Config->getConfigValue('work_on_https') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/socialauth/login?do=login_vk',
            'DISPLAY' => 'popup', // page OR popup OR touch OR wap
            'SCOPE' => array(
            //'notify', // Пользователь разрешил отправлять ему уведомления.
            //'friends', // Доступ к друзьям. 
            //'photos', // Доступ к фотографиям. 
            //'audio', // Доступ к аудиозаписям. 
            //'video', // Доступ к видеозаписям.
            //'docs', // Доступ к документам.
            //'notes', // Доступ заметкам пользователя. 
            //'pages', // Доступ к wiki-страницам. 
            //'wall', // Доступ к обычным и расширенным методам работы со стеной. 
            //'groups', // Доступ к группам пользователя. 
            //'ads', // Доступ к расширенным методам работы с рекламным API. 
            //'offline' // Доступ к API в любое время со стороннего сервера. 
            ),
            'VK_URI_AUTH' => 'http://oauth.vk.com/authorize?client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&display={DISPLAY}',
            'VK_URI_ACCESS_TOKEN' => 'https://oauth.vk.com/access_token?client_id={CLIENT_ID}&client_secret={APP_SECRET}&code={CODE}&redirect_uri={REDIRECT_URI}',
            'VK_URI_METHOD' => 'https://api.vk.com/method/{METHOD_NAME}?{PARAMETERS}&access_token={ACCESS_TOKEN}'
        );
    }

    public function prelogin() {
        $url = $this->getLoginURL();
        header('location: ' . $url);
        exit();
    }

    public function login() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
        $login_object = new Login();
        $Config = new config_admin();
        $answer = '';
        if (isset($_REQUEST['code'])) {
            $code = $_REQUEST['code'];
            $array = array(
                '{CLIENT_ID}' => $this->config['VK_APP_ID'],
                '{APP_SECRET}' => $this->config['VK_APP_SECRET'],
                '{CODE}' => $code,
                '{REDIRECT_URI}' => $this->config['REDIRECT_URI']
            );

            //print_r($array);

            $url = strtr($this->config['VK_URI_ACCESS_TOKEN'], $array);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $json = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($json);

            //echo($json);

            if (isset($result->error)) {
                throw new Exception('Ошибка получения Access Token Error: ' . $result->error . ' , Description: ' . $result->error_description);
            } else {
                $sRequest = "https://api.vk.com/method/users.get?uids=" . $result->user_id . "&access_token=" . $result->access_token . "&fields=has_mobile,photo_400_orig,contacts,personal&v=5.81";
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $sRequest);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                $json = curl_exec($ch);
                //echo curl_error($ch);
                curl_close($ch);
                //echo $sRequest;
                //print_r($json);
                $oResponce = json_decode($json);

                if (isset($oResponce->error)) {
                    $answer = 'Ошибка при попытке авторизации.';
                } else {
                    $result = $oResponce->response[0];
                    $_login = 'vk' . $result->id;
                    $_pass = $_pass = Sitebill::genPassword();
                    $email = $_login . '@vk.com';
                    //$_pass_md5=md5($_pass);

                    $ssInfo['ssType'] = 'vk';
                    $ssInfo['id'] = $result->id;
                    $ssInfo['email'] = $result->email;
                    $ssInfo['name'] = $result->first_name . ' ' . $result->last_name;
                    //$ssInfo['link']=$userInfo->link;
                    $ssInfo['picture'] = $userInfo->photo_400_orig;
                    $ssInfo['_email'] = 'vk' . $result->id . '@vk.com';
                    $ssInfo['_login'] = 'vk' . $result->id;
                    $ssInfo['_pass'] = $_pass;
                    $_SESSION['ssAuthData'] = $ssInfo;
                    //$this->authUser($_login, $_pass, SiteBill::iconv('utf-8', SITE_ENCODING, $result->first_name.' '.$result->last_name), $email);
                    return true;
                }
            }
        } else {
            $answer = $this->getLoginLink();
        }
        return $answer;
    }

    public function getLoginLink() {
        $array = array(
            '{CLIENT_ID}' => $this->config['VK_APP_ID'],
            // add id of the restaurant in order to redirect to necessary page after login
            '{REDIRECT_URI}' => $this->config['REDIRECT_URI'],
            '{SCOPE}' => implode(',', $this->config['SCOPE']),
            '{DISPLAY}' => $this->config['DISPLAY']
        );
        $href = strtr($this->config['VK_URI_AUTH'], $array);
        return '<a href="' . $href . '" class="vk_button"></a>';
    }

    public function getLoginURL() {
        $array = array(
            '{CLIENT_ID}' => $this->config['VK_APP_ID'],
            '{REDIRECT_URI}' => $this->config['REDIRECT_URI'],
            '{SCOPE}' => implode(',', $this->config['SCOPE']),
            '{DISPLAY}' => $this->config['DISPLAY']
        );
        $href = strtr($this->config['VK_URI_AUTH'], $array);
        return $href;
    }

    private function __construct() {
        $this->configure();
        if (isset($_SESSION['current_user']) && $_SESSION['current_user']['user_id'] > 0) {
            $this->user_id = $_SESSION['current_user']['user_id'];
            $this->user_name = $_SESSION['current_user']['name'];
        }
    }

    private function __clone() {
        
    }

}
