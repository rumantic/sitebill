<?php
/**
 * LoginUserInfoTrait — user data loading and info methods for Login class
 *
 * Extracted methods:
 *   - loadUserInfo($user_id)
 *   - appendAuthorizedUserData($user_id)
 *   - getFio($user_id)
 *   - getLogin($user_id)
 *   - getUserInfo($user_id)
 *   - getAuthData()
 *   - direct_add_user($login, $pass, $fio, $email)
 *   - storeLastActivity($user_id)
 *   - get_data($url)
 */
trait LoginUserInfoTrait
{
    function appendAuthorizedUserData($user_id){
        $additional_data = array();
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/local/auth_appender.php')){
            require_once SITEBILL_DOCUMENT_ROOT.'/local/auth_appender.php';
            $auth_appender = new auth_appender();
            $additional_data = $auth_appender->getData($user_id);
        }

        return $additional_data;
    }

    function loadUserInfo($user_id)
    {
        $DBC = DBC::getInstance();
        $what = array();
        $where = array();
        $where_val = array();
        $add_fieds = array();

        if ('' != trim($this->getConfigValue('login_user_data_ad'))) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('user', true);
            $form_data = $form_data['user'];
            $fields_str = explode(',', str_replace("\n", ',', $this->getConfigValue('login_user_data_ad')));

            foreach ($fields_str as $k) {
                if ('' != trim($k) && isset($form_data[trim($k)]) && $form_data[trim($k)]['dbtype'] != 'notable' && $form_data[trim($k)]['dbtype'] != '0') {
                    $add_fieds[] = trim($k);
                }
            }
        }

        $getTariffInfo = false;

        if (!empty($add_fieds)) {
            foreach ($add_fieds as $k) {
                if ($k == 'tariff_id') {
                    $getTariffInfo = true;
                }
                $what[] = 'u.`' . $k . '`';
            }
        }

        $what[] = 'u.`login`';
        $what[] = 'u.`user_id`';
        $what[] = 'u.`fio`';
        $what[] = 'u.`group_id`';
        $what[] = 'g.`system_name`';
        $what[] = 'g.`name` AS gname';
        $what[] = 'u.`email`';

        $query = 'SELECT ' . implode(',', $what) . ' FROM ' . DB_PREFIX . '_user u LEFT JOIN ' . DB_PREFIX . '_group g USING(group_id) WHERE `user_id`=?';
        $stmt = $DBC->query($query, array($user_id));

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $udata = $ar;
            $_SESSION['user_id'] = $udata['user_id'];

            $_SESSION['current_user_name'] = $udata['fio'];
            $_SESSION['current_user_group_name'] = $udata['system_name'];
            if ($_SESSION['current_user_group_name'] == 'admin') {
                $_SESSION['user_id_value'] = $udata['user_id'];
            } else {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                $permission = new Permission();
                if ($permission->get_access($_SESSION['user_id'], 'admin_panel', 'login')) {
                    $_SESSION['user_id_value'] = $udata['user_id'];
                }
            }
            $_SESSION['current_user_login'] = $udata['login'];
            $_SESSION['current_user_email'] = $udata['email'];
            $_SESSION['current_user_group_id'] = $udata['group_id'];
            $_SESSION['current_user_group_title'] = $udata['gname'];

            $_SESSION['current_user_tariff_info'] = array();
            if ($getTariffInfo && $udata['tariff_id'] > 0) {
                $query = 'SELECT title, service_id FROM ' . DB_PREFIX . '_tariff WHERE tariff_id=?';
                $stmt = $DBC->query($query, array($udata['tariff_id']));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $_SESSION['current_user_tariff_info'] = array('tariff_id' => $udata['tariff_id'], 'title' => $ar['title']);
                    $sid = $ar['service_id'];
                    if ('' != trim($sid)) {
                        $sid = explode(',', $sid);
                        foreach ($sid as $k => $iid) {
                            if (intval($iid) < 1) {
                                unset($sid[$k]);
                            }
                        }
                        if (!empty($sid)) {
                            $query = 'SELECT * FROM ' . DB_PREFIX . '_service WHERE service_id IN (' . implode(',', $sid) . ')';
                            $stmt = $DBC->query($query);
                            if ($stmt) {
                                while ($ar = $DBC->fetch($stmt)) {
                                    $_SESSION['current_user_tariff_info']['services'][$ar['name']] = $ar;
                                }
                            }

                        }
                    }


                }
            }

            $add_user_data = array();
            if (!empty($add_fieds)) {
                foreach ($add_fieds as $k) {
                    $add_user_data[$k] = $udata[$k];
                }
            }
            $additional_data = $this->appendAuthorizedUserData($user_id);
            if(!empty($additional_data)){
                $add_user_data = array_merge($add_user_data, $additional_data);
            }
            $_SESSION['current_user_info'] = $add_user_data;
        }
    }

    public function getAuthData()
    {
        global $smarty;
        $user_id = $this->getSessionUserId();

        if ($user_id > 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
            $Account = new Account;
            return array('is_logged' => 1,
                'fio' => $this->getFio($user_id),
                'ballance' => $Account->getAccountValue($user_id),
                'total_data_count' => $Account->get_user_data_count($user_id),
            );
        } else {
            return array('is_logged' => 0);
        }
    }

    /**
     * Get user info string
     * @param int $user_id user id
     * @return string
     */
    function getUserInfo($user_id)
    {
        $query = "select * from " . DB_PREFIX . "_user where user_id=$user_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $rs = '';
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $rs .= 'ФИО: ' . $ar['fio'] . '<br>';
            $rs .= 'email: ' . $ar['email'] . '<br>';
        }
        return $rs;
    }

    /**
     * Get fio
     * @param int $user_id user id
     * @return string
     */
    function getFio($user_id)
    {
        $query = "select fio from " . DB_PREFIX . "_user where user_id=$user_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['fio'];
        }
    }

    /**
     * Get login
     * @param int $user_id user id
     * @return string
     */
    function getLogin($user_id)
    {
        $query = "select login from " . DB_PREFIX . "_user where user_id=$user_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['login'];
        }
    }

    function direct_add_user($login, $pass, $fio, $email)
    {
        $query = 'INSERT INTO ' . DB_PREFIX . '_user (login, password, fio, email, reg_date) VALUES (?, ?, ?, ?, ?)';
        $DBC = DBC::getInstance();
        if (1 == intval($this->getConfigValue('email_as_login'))) {
            $login = $email;
        }
        $stmt = $DBC->query($query, array($login, $pass, $fio, $email, date('Y-m-d H:i:s')));
        if ($stmt) {
            return $DBC->lastInsertId();
        }
        return false;
    }

    function storeLastActivity($user_id)
    {
        if ((time() - $_SESSION['last_activity_date']) < 300) {
            return;
        }
        //store to user data
        //store to stat
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `last_activity_date`=? WHERE `user_id`=?';
        $stmt = $DBC->query($query, array(date('Y-m-d H:i:s'), $id));
    }

    function get_data($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
