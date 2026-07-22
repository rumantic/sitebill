<?php
/**
 * LoginSessionTrait — session and cookie management for Login class
 *
 * Extracted methods:
 *   - setSessionKey($key)
 *   - getSessionKey()
 *   - GenerateSessionKey($user_id, $preset_session_key)
 *   - ReGenerateSessionKey($user_id, $session_key)
 *   - getSessionUserId()
 *   - getUserId()
 *   - setUserId($user_id)
 *   - restoreUser()
 */
trait LoginSessionTrait
{
    /**
     * Set session
     * @param string $key session key
     * @return void
     */
    function setSessionKey($key)
    {
        $_SESSION['key'] = $key;
    }

    /**
     * Get session
     * @return string
     */
    function getSessionKey()
    {
        return $_SESSION['key'];
    }

    function ReGenerateSessionKey($user_id, $session_key)
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $query = 'UPDATE ' . DB_PREFIX . '_session SET `user_id`=?, `ip`=?, `start_date`=NOW() WHERE `session_key`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id, $user_ip, $session_key));
        if (!$stmt) {
            return $this->GenerateSessionKey($user_id);
        }
        return $session_key;
    }

    /**
     * Method to generate 32 - digit session key
     * @param void
     * @return string $session_key - session key
     */
    function GenerateSessionKey($user_id, $preset_session_key = false)
    {
        $this->clear_session_table();
        $user_ip = $_SERVER['REMOTE_ADDR'];
        if ($preset_session_key) {
            $session_key = $preset_session_key;
        } else {
            $session_key = md5(rand() . time() . $user_ip);
        }

        $query = 'INSERT INTO ' . DB_PREFIX . '_session (`user_id`, `ip`, `session_key`, `start_date`) values (?, ?, ?, now())';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id, $user_ip, $session_key));
        return $session_key;
    }

    /**
     * Get user ID
     * @param void
     * @return int
     */
    function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user ID
     * @param int $user_id user ID
     * @return void
     */
    function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Get session user ID
     * @param void
     * @return int
     */
    function getSessionUserId()
    {
        $key = (isset($_SESSION['key']) ? $_SESSION['key'] : '');
        if (isset(self::$Heaps['session']) && self::$Heaps['session']['user_id_none'] == 1) {
            return false;
        }

        if (isset(self::$Heaps['session']) && self::$Heaps['session']['user_id'] != '') {
            return self::$Heaps['session']['user_id'];
        }
        if ($key != '') {
            $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_session WHERE `session_key` =?';
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query, array($key));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $user_id = $ar['user_id'];
                if ($user_id == '') {
                    self::$Heaps['session']['user_id_none'] = 1;
                } else {
                    self::$Heaps['session']['user_id'] = $user_id;
                }
                //echo 'set user_id = '.$user_id;
            }
            if ($user_id != '' and $user_id != 0) {
                $this->user_id = $user_id;
                //$init->setUserId($user_id);
                return $user_id;
            } else {
                $this->user_id = 0;
                return 0;
            }
        }
        $this->user_id = 0;
        return 0;
    }

    function restoreUser()
    {

        if (isset($_COOKIE["logged_user_id"]) && (int)$_COOKIE["logged_user_id"] > 0 && isset($_COOKIE["logged_user_token"])/* &&  $_COOKIE["logged_user_token"]!='' && md5((int)$_COOKIE["logged_user_id"].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'])==$_COOKIE["logged_user_token"] */) {
            $user_id = 0;
            $DBC = DBC::getInstance();
            if ($this->hard_mode) {
                $what = array();
                $where = array();
                $where_val = array();
                $add_fieds = array();

                $what[] = 'u.`user_id`';

                $where[] = 'u.`user_id`=?';
                $where_val[] = $_COOKIE["logged_user_id"];
                $where[] = 'u.`auth_hash`=?';
                $where_val[] = $_COOKIE["logged_user_token"];

                $query = 'SELECT ' . implode(',', $what) . ' FROM ' . DB_PREFIX . '_user u WHERE ' . implode(',', $where) . ' LIMIT 1';
                //$query='SELECT '.implode(',', $what).' FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE '.implode(',', $where).' LIMIT 1';
                $stmt = $DBC->query($query, $where_val);
                if ($stmt) {
                    $user_id = intval($_COOKIE["logged_user_id"]);
                }
            } else {
                $query = 'SELECT `auth_salt` FROM ' . DB_PREFIX . '_user WHERE user_id=?';
                $stmt = $DBC->query($query, array($_COOKIE["logged_user_id"]));
                if (!$stmt) {
                    setcookie('logged_user_id', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                    setcookie('logged_user_token', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                    return false;
                }
                $ar = $DBC->fetch($stmt);
                if ($ar['auth_salt'] == '') {
                    $auth_salt = md5(rand(10000, 99999) . time());
                    $sql = 'UPDATE ' . DB_PREFIX . '_user SET `auth_salt`=? WHERE `user_id`=? ';
                    $stmt = $DBC->query($sql, array($auth_salt, $_COOKIE["logged_user_id"]));
                    setcookie('logged_user_id', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                    setcookie('logged_user_token', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                    return false;
                } else {
                    $auth_salt = $ar['auth_salt'];
                }
                $test_hash = md5(intval($_COOKIE["logged_user_id"]) . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['HTTP_USER_AGENT'] . ' ' . $auth_salt);
                //echo $_COOKIE["logged_user_token"].'@@@@';
                //echo $test_hash;
                if ($test_hash != $_COOKIE["logged_user_token"]) {
                    setcookie('logged_user_id', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                    setcookie('logged_user_token', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                    return false;
                }

                $user_id = intval($_COOKIE["logged_user_id"]);
            }

            if ($user_id > 0) {
                $session_key = $this->GenerateSessionKey($user_id);
                $this->setSessionKey($session_key);
                $this->setUserId($user_id);
                $_SESSION['user_id'] = $user_id;
                $this->loadUserInfo($user_id);
                $this->restoreFavorites($user_id);
                return true;
            } else {
                setcookie('logged_user_id', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
                setcookie('logged_user_token', '', time() - $this->get_cookie_duration_in_sec(), '/', self::$_cookiedomain);
            }
        }

        unset($_SESSION["user_id"]);
    }
}
