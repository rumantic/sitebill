<?php
/**
 * AuthAccessTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait AuthAccessTrait
{
    /**
     * Get session key
     * @param void
     * @return string
     */
    function get_session_key()
    {
        return @$_SESSION['key'];
    }

    /**
     * Delete session by key
     * @param string $session_key
     * @return void
     */
    function delete_session_key($session_key)
    {
        $DBC = DBC::getInstance();
        $query = "DELETE FROM " . DB_PREFIX . "_session WHERE session_key=?";
        $stmt = $DBC->query($query, array((string)$session_key));
        return $_SESSION['key'];
    }

    function setSessionUserId($user_id)
    {
        self::$Heaps['session']['user_id'] = $user_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_id_value'] = $user_id;
    }

    /**
     * Get session user ID
     * @param void
     * @return int
     */
    function getSessionUserId()
    {
        $key = (isset($_SESSION['key']) ? $_SESSION['key'] : '');
        if (isset(self::$Heaps['session']['user_id']) && self::$Heaps['session']['user_id'] != '') {
            return self::$Heaps['session']['user_id'];
        }
        if ($key != '') {
            $DBC = DBC::getInstance();
            $query = "SELECT user_id FROM " . DB_PREFIX . "_session WHERE session_key=? LIMIT 1";
            $stmt = $DBC->query($query, array((string)$key));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $user_id = $ar['user_id'];
                if ($user_id != '' and $user_id != 0) {
                    $this->user_id = $user_id;
                    self::$Heaps['session']['user_id'] = $user_id;
                    //$init->setUserId($user_id);
                    return $user_id;
                } else {
                    $this->user_id = 0;
                    return 0;
                }
            }
        }
        $this->user_id = 0;
        return 0;
    }

    function get_ajax_auth_form()
    {
        if (SITEBILL_MAIN_URL != '') {
            $add_folder = SITEBILL_MAIN_URL . '/';
        }
        $rs .= '<form method="post" onsubmit="run_login(\'login\', \'cp1251\', \'' . $_SERVER['SERVER_NAME'] . $add_folder . '\'); return false;">';
        $rs .= '';
        $rs .= '<table border="0">';
        if ($this->getError() and $this->GetErrorMessage() != 'not login') {
            $rs .= '<tr>';
            $rs .= '<td colspan="2"><span class="error">' . $this->GetErrorMessage() . '</span></td>';
            $rs .= '</tr>';
        }
        $rs .= '<tr>';
        $rs .= '<td class="special" colspan="2"><div id="error_message"></div></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="special">' . Multilanguage::_('L_LOGIN') . ' </td>';
        $rs .= '<td class="special"><input type="text" name="login" id="login"></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="special">' . Multilanguage::_('L_PASSWORD') . ' </td>';
        $rs .= '<td class="special"><input type="password" name="password" id="password"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="special">';
        if ($this->getConfigValue('allow_register_admin')) {
            $rs .= '<a href="#" onclick="run_command(\'register\', \'cp1251\', \'' . $_SERVER['SERVER_NAME'] . $add_folder . '\'); return false;">' . Multilanguage::_('L_AUTH_REGISTRATION') . '</a>';
        }
        $rs .= '</td>';
        $rs .= '<td class="special"><input type="submit" value="' . Multilanguage::_('L_LOGIN_BUTTON') . '" onclick="run_login(\'login\', \'cp1251\', \'' . $_SERVER['SERVER_NAME'] . $add_folder . '\'); return false;"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '';
        $rs .= '</form>';
        return $rs;
    }

    /**
     * Get simple auth form
     * @param string $action
     * @param boolean $register
     * @param boolean $remind
     * @return string
     */
    function get_simple_auth_form($action = '/login/', $register = true, $remind = true)
    {
        if (SITEBILL_MAIN_URL != '') {
            $add_folder = '/' . SITEBILL_MAIN_URL;
        }
        $rs = '';

        if ($this->getConfigValue('theme') == 'albostar') {
            $rs .= '<form method="post" action="' . SITEBILL_MAIN_URL . $action . '">';
            $rs .= '';

            if ($this->getError() and $this->GetErrorMessage() != 'not login') {
                $rs .= '<div>';
                $rs .= '<span class="error">' . $this->GetErrorMessage() . '</span>';
                $rs .= '</div>';
            }


            $rs .= '<label>' . Multilanguage::_('L_AUTH_LOGIN') . '</label>';
            $rs .= '<input type="text" name="login" id="login">';
            $rs .= '<br />';

            $rs .= '<label>' . Multilanguage::_('L_AUTH_PASSWORD') . '</label>';
            $rs .= '<input type="password" name="password" id="password">';
            $rs .= '<input type="submit" value="Вход">';
            if ($register) {
                $rs .= '<br />';
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/register/">' . Multilanguage::_('L_AUTH_REGISTRATION') . '</a>';
            }
            if ($remind) {
                $rs .= '<br />';
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/remind/">' . Multilanguage::_('L_AUTH_FORGOT_PASS') . '</a>';
            }

            $rs .= '<input type="hidden" name="do" value="login">';
            $rs .= '</form>';
        } else {

            if ($action == '/admin/' && 1 === intval($this->getConfigValue('use_captcha_admin_entry'))) {
                $c['captcha']['name'] = 'captcha';
                $c['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
                $c['captcha']['value'] = '';
                $c['captcha']['length'] = 40;
                $c['captcha']['type'] = 'captcha';
                $c['captcha']['required'] = 'on';
                $c['captcha']['unique'] = 'off';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
                $form_generator = new Form_Generator();

                $el = $form_generator->compile_form_elements($c);
                $el = $el['hash']['captcha']['html'];
            } else {
                $el = '';
            }

            $rs .= '<form method="post" action="' . SITEBILL_MAIN_URL . $action . '">';
            if ($this->getError() and $this->GetErrorMessage() != 'not login') {
                $rs .= '<div class="alert alert-error" style="display:block;">';
                $rs .= $this->GetErrorMessage();
                $rs .= '</div>';
            }

            $rs .= '<input class="" placeholder="' . Multilanguage::_('L_AUTH_LOGIN') . '" type="text" name="login" id="login" />';
            $rs .= '<input class="" placeholder="' . Multilanguage::_('L_AUTH_PASSWORD') . '" type="password" name="password" id="password" />';
            $rs .= $el;
            $rs .= '<label class="checkbox">';
            $rs .= '<input type="checkbox" name="rememberme" value="1"> Запомнить меня';
            $rs .= '</label>';
            $rs .= '<button class="btn-info btn" type="submit">' . Multilanguage::_('L_AUTH_ENTER') . '</button>';
            $rs .= '<input type="hidden" name="do" value="login">';
            $rs .= '</form>';


            if ($register) {
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/register/">' . Multilanguage::_('L_AUTH_REGISTRATION') . '</a>';
            }
            if ($remind) {
                $rs .= '<br><a href="' . SITEBILL_MAIN_URL . '/remind/">' . Multilanguage::_('L_AUTH_FORGOT_PASS') . '</a>';
            }
        }
        return $rs;
    }

    protected function restoreFavorites($user_id)
    {

        if (isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites'] != '') {
            $cc = unserialize($_COOKIE['user_favorites']);
        } else {
            $cc = array();
        }
        $cc[$user_id] = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT id FROM ' . DB_PREFIX . '_userlists WHERE user_id=? AND lcode=?';
        $stmt = $DBC->query($query, array($user_id, 'fav'));

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $cc[$user_id][$ar['id']] = $ar['id'];
            }
        }

        @setcookie('user_favorites', '', time() - 7 * 24 * 3600, '/', self::$_cookiedomain);
        @setcookie('user_favorites', serialize($cc), time() + 7 * 24 * 3600, '/', self::$_cookiedomain);
        $_SESSION['favorites'] = $cc[$user_id];
        unset($cc);
    }

    function check_access_agency($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value)
    {
        if (!$this->agency_admin) {
            $this->agency_admin = $this->get_api_common()->init_custom_model_object('agency');
        }
        return $this->agency_admin->check_access_agency($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value);
    }

    /**
     * Проверка владельца записи в таблице по USER_ID, если владелец совпадает с $table_name.user_id тогда возвращаем TRUE иначе FALSE
     * @param type $table_name - название таблицы
     * @param type $user_id - идентификатор пользователя для проверки
     * @param type $control_name - тип действия (edit, delete...)
     * @param type $primary_key_name - название PRIMARY KEY в таблице
     * @param type $primary_key_value - значение PRIMARY KEY
     * @return boolean
     */
    function check_access($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value)
    {
        if (!$user_id) {
            return true;
        }
        $has_access = 0;
        if ($this->getConfigValue('apps.agency.enable')) {
            $has_access = intval($this->check_access_agency($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value));
        }

        $DBC = DBC::getInstance();
        $enable_curator_mode = false;
        if (
            1 == $this->getConfigValue('enable_curator_mode')
            or
            1 == $this->getConfigValue('enable_coworker_mode')
        ) {
            $enable_curator_mode = true;


            if (1 === intval($this->getConfigValue('curator_mode_fullaccess'))) {

                $query = 'SELECT COUNT(d.' . $primary_key_name . ') AS _cnt FROM ' . DB_PREFIX . '_' . $table_name . ' d 
                LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE d.' . $primary_key_name . '=? AND u.parent_user_id=?';
                $stmt = $DBC->query($query, array($primary_key_value, $user_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            } elseif ($table_name == 'data' && $this->getConfigValue('apps.data.enable_city_coworker')) {
                $has_access = $this->check_coworker_access_by_foreign_key(
                    $table_name,
                    $user_id,
                    $control_name,
                    $primary_key_name,
                    $primary_key_value,
                    'city');
            } else {
                $query = 'SELECT COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=? AND id=?';
                $stmt = $DBC->query($query, array($user_id, $table_name, $primary_key_value));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            }
        }


        $where = array();
        $where_val = array();

        $where[] = '`' . $primary_key_name . '`=?';
        $where_val[] = $primary_key_value;


        if ($enable_curator_mode) {
            $where[] = '(`user_id`=? OR (`user_id`!=? AND 1=' . $has_access . '))';
            $where_val[] = $user_id;
            $where_val[] = $user_id;
        } else {
            $where[] = '`user_id`=?';
            $where_val[] = $user_id;
        }


        $query = 'SELECT `' . $primary_key_name . '` FROM `' . DB_PREFIX . '_' . $table_name . '` WHERE ' . implode(' AND ', $where);
        $stmt = $DBC->query($query, $where_val);
        if (!$stmt) {
            return false;
        }
        $ar = $DBC->fetch($stmt);
        if ($ar[$primary_key_name] > 0) {
            return true;
        }
        return false;
    }

    function check_coworker_access_by_foreign_key($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value, $foreign_table)
    {
        if (!$this->cowork_object) {
            $api_common = $this->get_api_common();
            $this->cowork_object = $api_common->init_custom_model_object('cowork');
        }
        if ($this->cowork_object) {
            try {
                // Пока хардкодом прописываем выборку для city_id
                $data_record = \system\lib\model\eloquent\Data::where($primary_key_name, '=', $primary_key_value)
                    ->first();
                if ($data_record->city_id) {
                    return $this->cowork_object->check_cowork_record($foreign_table, $data_record->city_id, $user_id);
                }
            } catch (Exception $e) {
                $this->writeLog($e->getMessage());
            }
        }
        return 0;
    }

    function need_check_access($table_name)
    {
        return @$_SESSION['politics'][$table_name]['check_access'];
    }

    function get_check_access_user_id($table_name)
    {
        return @$_SESSION['politics'][$table_name]['user_id'];
    }

    /**
     * Перенаправляем неавторизованного пользователя на форму авторизации
     */
    function go_to_login()
    {
        header('location: ' . SITEBILL_MAIN_URL . '/login/');
        exit();
    }

    function get_cookie_duration_in_sec()
    {
        return 60 * 60 * 24 * 100;
    }

}
