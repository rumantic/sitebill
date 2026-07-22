<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once __DIR__ . '/traits/LoginSessionTrait.php';
require_once __DIR__ . '/traits/LoginAuthTrait.php';
require_once __DIR__ . '/traits/LoginUserInfoTrait.php';
require_once __DIR__ . '/traits/LoginFormTrait.php';

/**
 * Login class
 * @author Kondin Dmitry <kondin@etown.ru>
 */
class Login extends SiteBill
{
    use LoginSessionTrait;
    use LoginAuthTrait;
    use LoginUserInfoTrait;
    use LoginFormTrait;

    var $user_id = 0;
    private $hard_mode;
    private $last_activity_table = 'user';
    private $last_activity_field = 'login_date';

    /**
     * Constructor
     */
    function Login()
    {

        parent::__construct();
        $this->hardmode = false;
        if (!isset($_SESSION['key'])) {
            $this->setSessionKey($this->GenerateSessionKey(0));
        }

        if (empty($_SESSION['user_id']) && !preg_match('/\/logout/', $_SERVER['REQUEST_URI'])) {
            $this->restoreUser();
        }

        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            if (!$this->isStillActive($_SESSION['user_id'])) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/logout.php';
                $Logout = new Logout();
                $Logout->main();
            } else {
                $this->loadUserInfo($_SESSION['user_id']);
                if (isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites'] != '') {
                    $cc = unserialize($_COOKIE['user_favorites']);
                    $_SESSION['favorites'] = $cc[(int)$_SESSION['user_id']];
                }

                if (!isset($_SESSION['user_dayly_activity']) || intval($_SESSION['user_dayly_activity']) != 1) {

                    $DBC = DBC::getInstance();
                    $query = 'INSERT INTO ' . DB_PREFIX . '_stat_useractivity_d (`user_id`, `date`) VALUES (?,?)';
                    $stmt = $DBC->query($query, array($_SESSION['user_id'], date('Y-m-d')));
                    if ($stmt) {
                        $_SESSION['user_dayly_activity'] = 1;
                    }
                }

                $diff = 300;
                if (!isset($_SESSION['user_last_activity']) || (time() - $_SESSION['user_last_activity']) > $diff) {
                    $_SESSION['user_last_activity'] = time();
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET `last_activity`=? WHERE `user_id`=?';
                    $stmt = $DBC->query($query, array(date('Y-m-d H:i:s'), $_SESSION['user_id']));
                }
            }
        }
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {

        if (isset($_SESSION['go_after_login']) && $_SESSION['go_after_login'] != '') {
            $back_url = $_SESSION['go_after_login'];
        } else {
            $back_url = $_SERVER['HTTP_REFERER'];
            if (parse_url($back_url, PHP_URL_HOST) != $_SERVER['HTTP_HOST']) {
                $back_url = $this->getServerFullUrl();
            }
            $_SESSION['go_after_login'] = $back_url;
        }

        if ($this->getSessionUserId() > 0) {
            $rs = $this->wellcomePage();
            return $rs;
        }
        $this->template->assign('title', Multilanguage::_('L_AUTH_TITLE'));
        $do = $this->getRequestValue('do');
        switch ($do) {
            case 'login':
                $login = $this->getRequestValue('login');
                $password = $this->getRequestValue('password');
                $this->checkLogin($login, $password, $this->getRequestValue('rememberme'));
                //echo "error_message = ".$this->error_message."<br>";
                if ($this->GetError()) {
                    $rs = $this->loginForm();
                } else {
                    $rs = $this->wellcomePage();
                    $this->restoreFavorites($this->getSessionUserId());
                }
                break;
            default:
                if ($this->getSessionUserId() > 0) {
                    $rs = $this->wellcomePage();
                } else {
                    $rs = $this->loginForm();
                }
        }
        return $rs;
    }

}
