<?php
/**
 * LoginAuthTrait — authentication logic for Login class
 *
 * Extracted methods:
 *   - checkLogin($login, $password, $rememberme, $oauth_key)
 *   - makeUserLogged($user_id, $rememberme, $oauth_key, $save_last_auth)
 *   - setLoggedUser($id)
 *   - isStillActive($user_id)
 *   - saveLastAuthDate($id)
 *   - trackUserLoginMetrics($user_id, $oauth_key)
 */
trait LoginAuthTrait
{
    function isStillActive($user_id)
    {
        //return true;
        $DBC = DBC::getInstance();
        if (1 == $this->getConfigValue('use_registration_email_confirm')) {
            $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_user WHERE `user_id`=? AND `active`=?';
            $stmt = $DBC->query($query, array($user_id, 1));
        } else {
            $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_user WHERE `user_id`=?';
            $stmt = $DBC->query($query, array($user_id));
        }
        if ($stmt) {
            return true;
        }
        return false;
    }

    function saveLastAuthDate($id)
    {
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `last_auth_date`=? WHERE `user_id`=?';
        $stmt = $DBC->query($query, array(date('Y-m-d H:i:s'), $id));
    }

    function setLoggedUser($id)
    {
        $DBC = DBC::getInstance();

        $what = array();
        $where = array();
        $where_val = array();

        $what[] = '`user_id`';
        $where[] = '`user_id`=?';
        $where_val[] = $id;

        if (1 == $this->getConfigValue('use_registration_email_confirm')) {
            $where[] = '`active`=?';
            $where_val[] = 1;
        }

        $query = 'SELECT ' . implode(',', $what) . ' FROM ' . DB_PREFIX . '_user WHERE ' . implode(' AND ', $where);
        $stmt = $DBC->query($query, $where_val);
        if ($stmt) {
            $session_key = $this->GenerateSessionKey($id);
            $this->setSessionKey($session_key);
            $this->setUserId($id);
            $this->loadUserInfo($id);
            $this->restoreFavorites($id);
            $this->saveLastAuthDate($id);
            return true;
        }

        return false;
    }


    function makeUserLogged($user_id, $rememberme = 0, $oauth_key = false, $save_last_auth = true)
    {

        if ($save_last_auth) {
            $this->saveLastAuthDate($user_id);
        }

        if ($oauth_key) {
            $session_key = $this->GenerateSessionKey($user_id, $oauth_key);
        } else {
            $session_key = $this->GenerateSessionKey($user_id);
        }
        $this->setSessionKey($session_key);
        $this->setUserId($user_id);

        $this->loadUserInfo($user_id);
        $this->restoreFavorites($user_id);

        if ($rememberme == 1) {
            $auth_salt = '';
            //$auth_salt=$ar['auth_salt'];
            $DBC = DBC::getInstance();
            $sql = 'SELECT `auth_salt` FROM ' . DB_PREFIX . '_user WHERE user_id=?';
            $stmt = $DBC->query($sql, array($user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $auth_salt = $ar['auth_salt'];
            }

            if ($this->hard_mode) {
                $str = $auth_hash;
            } else {
                if ($auth_salt == '') {
                    $auth_salt = md5(rand(10000, 99999) . time());
                    $sql = 'UPDATE ' . DB_PREFIX . '_user SET `auth_salt`=? WHERE `user_id`=? ';
                    $stmt = $DBC->query($sql, array($auth_salt, $user_id));
                }
                $str = md5($user_id . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['HTTP_USER_AGENT'] . ' ' . $auth_salt);
            }

            setcookie('logged_user_id', $user_id, time() + $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
            setcookie('logged_user_token', $str, time() + $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
        }

        // Track user login metrics
        $this->trackUserLoginMetrics($user_id, $oauth_key);
    }

    /**
     * Check login
     * @param string $login login
     * @param string $password password
     * @return boolean
     */
    function checkLogin($login, $password, $rememberme = 0, $oauth_key = false)
    {
        if (@$_SESSION['user_id'] > 0/* || $_SESSION['Sitebill_User']['user_id']>0 */) {
            return true;
        }
        if ($this->getConfigValue('ajax_auth_form')) {
            $this->riseError('not login');
            unset($_SESSION['user_id']);
            return false;
        } else {
            //$oauth_key - если содержит ключ из oauth.session_key для авторизации REST API вызовов
            if ($login != '' and $password != '' or $oauth_key) {

                $DBC = DBC::getInstance();

                $max_try_count = intval($this->getConfigValue('max_login_try_count'));
                if ($max_try_count == 0) {
                    $max_try_count = 5;
                }
                $block_time = intval($this->getConfigValue('login_block_time'));

                $query = 'SELECT try_count, blocked_to FROM ' . DB_PREFIX . '_user_blocked_logins WHERE login=? LIMIT 1';
                $stmt = $DBC->query($query, array($login));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $try_count = intval($ar['try_count']);
                    $blocked_to = strtotime($ar['blocked_to']);
                } else {
                    $try_count = 0;
                    //$blocked_to=date('Y-m-d H:');
                }

                if (isset($blocked_to) and $blocked_to > time()) {
                    $this->riseError(Multilanguage::_('L_ACCOUNT_LOGIN_FROZEN'));
                    return false;
                }

                $what = array();
                $where = array();
                $where_val = array();

                $what[] = 'u.`user_id`';

                $where[] = 'u.password=?';
                $where_val[] = md5($password);
                if (1 == $this->getConfigValue('use_registration_email_confirm')) {
                    $where[] = 'u.`active`=1';
                }
                if (1 == intval($this->getConfigValue('email_as_login'))) {
                    $where[] = '(u.`email`=? OR u.`login`=?)';
                    $where_val[] = $login;
                    $where_val[] = $login;
                } else {
                    $where[] = '(u.`login`=? OR u.`email`=?)';
                    $where_val[] = $login;
                    $where_val[] = $login;
                }

                if ($oauth_key) {
                    $query = 'SELECT user_id FROM ' . DB_PREFIX . '_oauth WHERE session_key=?';
                    $stmt = $DBC->query($query, array($oauth_key));
                } else {
                    $query = 'SELECT ' . implode(',', $what) . ' FROM ' . DB_PREFIX . '_user u WHERE ' . implode(' AND ', $where);
                    $stmt = $DBC->query($query, $where_val);
                }

                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['user_id'] != '') {

                        $udata = $ar;


                        $this->makeUserLogged($ar['user_id'], $rememberme, $oauth_key, true);

                        $query = 'DELETE FROM ' . DB_PREFIX . '_user_blocked_logins WHERE login=?';
                        $stmt = $DBC->query($query, array($login));

                        return true;
                    }
                }

                $try_count++;
                if ($try_count >= $max_try_count) {
                    if ($max_try_count > 1) {
                        $query = 'UPDATE ' . DB_PREFIX . '_user_blocked_logins SET try_count=?, blocked_to=? WHERE login=?';
                        $stmt = $DBC->query($query, array($try_count, date('Y-m-d H:i:s', time() + $block_time * 60), $login));
                    } else {
                        $query = 'INSERT INTO ' . DB_PREFIX . '_user_blocked_logins (login, try_count, blocked_to) VALUES (?,?,?)';
                        //$query='UPDATE '.DB_PREFIX.'_user_blocked_logins SET try_count=?, blocked_to=? WHERE login=?';
                        $stmt = $DBC->query($query, array($login, $try_count, date('Y-m-d H:i:s', time() + $block_time * 60)));
                    }
                } elseif ($try_count == 1) {
                    $query = 'INSERT INTO ' . DB_PREFIX . '_user_blocked_logins (login, try_count) VALUES (?,?)';
                    $stmt = $DBC->query($query, array($login, $try_count));
                } else {
                    $query = 'UPDATE ' . DB_PREFIX . '_user_blocked_logins SET try_count=? WHERE login=?';
                    $stmt = $DBC->query($query, array($try_count, $login));
                }

                $this->riseError(Multilanguage::_('L_ERROR_LOGIN_PASS'));
                return false;
            }
            $this->riseError('not login');
            unset($_SESSION['user_id']);
            return false;
        }
    }

    /**
     * Track user login metrics
     * @param int $user_id
     * @param string|bool $oauth_key
     */
    private function trackUserLoginMetrics($user_id, $oauth_key = false)
    {
        if (class_exists('Metrics')) {
            $loginMethod = $oauth_key ? 'oauth' : 'password';
            $metadata = [
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            if ($oauth_key) {
                $metadata['oauth_key'] = substr($oauth_key, 0, 10) . '...'; // Only log first 10 chars for privacy
            }
            
            Metrics::trackLogin($user_id, $loginMethod, $metadata);
        }
    }
}
