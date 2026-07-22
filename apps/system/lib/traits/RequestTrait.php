<?php
/**
 * RequestTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait RequestTrait
{
    /**
     * Проверка csrf-токена с зависимостью от адреса сервера и user-agent
     * @param type $csrf_token
     * @return boolean
     */
    public function checkCSRFToken($csrf_token)
    {
        list($valid_thru, $token) = explode(':', $csrf_token);
        $n = $valid_thru . ':' . base64_encode(
                hash_hmac(
                    'sha256',
                    $valid_thru . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SESSION['key'],
                    $this->getConfigValue('csrf_salt'),
                    true
                )
            );
        if ($n === $csrf_token && $valid_thru >= time()) {
            return true;
        }
        return false;
    }

    public function generateCSRFToken($len = 40)
    {
        $array = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $p = array();
        for ($i = 1; $i <= $len; $i++) {
            shuffle($array);
            $p[] = $array[0];
        }
        return implode('', $p);
    }

    /**
     * Set new locale
     * @param string $lang
     */
    public static function setRequest($lang)
    {

        $r['prevlocale'] = '';
        $r['request_lang_prefix'] = '';
        $r['locale'] = $lang;

        $SConfig = SConfig::getInstance();
        if (1 == intval($SConfig->getConfigValue('apps.language.use_langs')) && 1 == intval($SConfig->getConfigValue('apps.language.prefixmode'))) {

            $prefix_list = array();
            $prefixlistconf = trim($SConfig->getConfigValue('apps.language.language_prefix_list'));
            if ($prefixlistconf !== '') {
                $prefix_pairs = explode('|', $prefixlistconf);
                if (count($prefix_pairs) > 0) {
                    foreach ($prefix_pairs as $lp) {
                        list($pr, $lo) = explode('=', $lp);
                        $prefix_list[$pr] = $lo;
                    }
                }
            }

            foreach ($prefix_list as $pr => $lo) {
                if ($lo == $lang) {
                    $r['request_lang_prefix'] = $pr;
                    $r['locale'] = $lo;
                    break;
                }
            }
        }

        self::$_request = $r;
    }

    public static function initRequest()
    {
        if (!is_null(self::$_request)) {
            return;
        }

        $r = array();
        $r['clearRequestUri'] = null;
        $r['request_lang_prefix'] = '';
        $r['locale'] = '';
        $r['prevlocale'] = '';

        $REQUEST_URI = $_SERVER['REQUEST_URI'];
        $REQUEST_URI = str_replace('\\', '/', $REQUEST_URI);
        $REQUEST_URI = ltrim($REQUEST_URI, '/');
        $parts = explode('/', $REQUEST_URI);
        $SConfig = SConfig::getInstance();
        if (1 === (int)$SConfig->getConfigValue('apps.language.use_langs') && 1 === (int)$SConfig->getConfigValue('apps.language.prefixmode')) {

            $prefix_list = array();
            $prefixlistconf = trim($SConfig->getConfigValue('apps.language.language_prefix_list'));
            if ($prefixlistconf !== '') {
                $prefix_pairs = explode('|', $prefixlistconf);
                if (count($prefix_pairs) > 0) {
                    foreach ($prefix_pairs as $lp) {
                        list($pr, $lo) = explode('=', $lp);
                        $prefix_list[$pr] = $lo;
                    }
                }
            }

            if (!empty($prefix_list) && isset($prefix_list[$parts[0]])) {
                $_SESSION['_lang'] = $prefix_list[$parts[0]];
                $r['request_lang_prefix'] = $parts[0];
                $r['locale'] = $prefix_list[$parts[0]];
            } elseif (!empty($prefix_list) && isset($prefix_list[''])) {
                $_SESSION['_lang'] = $prefix_list[''];
                $r['request_lang_prefix'] = '';
                $r['locale'] = $prefix_list[''];
            }



            /*$langlist = trim($SConfig->getConfigValue('apps.language.languages'));

            if ($langlist !== '') {
                $lang_pairs = explode('|', $langlist);
                if (count($lang_pairs) > 0) {
                    foreach ($lang_pairs as $lp) {
                        $matches = array();
                        if (preg_match('/([a-z]+)=(.+)/', trim($lp), $matches)) {
                            $langs[$matches[1]] = $matches[2];
                        }
                    }
                }
                if(isset($langs[$parts[0]])){
                    $_SESSION['_lang'] = $parts[0];
                    $r['request_lang_prefix'] = $parts[0];
                    $r['locale'] = $parts[0];
                }
            }*/
        }


        self::$_request = $r;
    }

    function get_phpinput_value($key)
    {
        $flags = ENT_COMPAT | ENT_HTML401;
        if (empty($this->phpinput_data)) {
            $this->phpinput_data = json_decode(file_get_contents('php://input'), true);
        }
        if (!empty($this->phpinput_data[$key])) {
            return $this->sanitize($this->phpinput_data[$key], $flags);
        }
        return null;
    }

    /**
     * Get value
     * @param string $key key
     * @return string
     */
    function getRequestValue($key, $type = '', $from = '')
    {
        $flags = ENT_COMPAT | ENT_HTML401;
        $value = NULL;
        switch ($from) {
            case 'get' :
            {
                if (isset($_GET[$key])) {
                    $value = htmlspecialchars($_GET[$key], $flags, SITE_ENCODING);
                }
                break;
            }
            case 'post' :
            {
                if (isset($_POST[$key])) {
                    $value = $this->escape($_POST[$key]);
                }
                break;
            }
            default :
            {
                if (isset($_GET[$key])) {
                    $value = $_GET[$key];
                    $value = $this->sanitize($value, $flags);
                } elseif (isset($_POST[$key])) {
                    $value = $_POST[$key];
                    $value = $this->sanitize($value, $flags);
                } elseif (isset($_REQUEST[$key])) {
                    $value = $_REQUEST[$key];
                    $value = $this->sanitize($value, $flags);
                }
            }
        }

        //Попробуем получить из PHP://INPUT значение
        if ($value === NULL) {
            $value = $this->get_phpinput_value($key);
        }


        if ($value === NULL) {
            return $value;
        }

        if (!is_array($value)) {
            $value = trim($value);
            $value = $this->getSafeValue($value);
            if ($this->getConfigValue('sql_paranoid_mode')) {
                if (preg_match('/union/i', $value)) {
                    return NULL;
                }
                if (preg_match('/left\sjoin/i', $value)) {
                    return NULL;
                }

                if (preg_match('/sleep[\s]*\(/i', $value)) {
                    return NULL;
                }
                if (preg_match('/benchmark/i', $value)) {
                    return NULL;
                }

                if (preg_match_all('/select/i', $value, $matches)) {
                    if (count($matches[0]) > 1) {
                        return NULL;
                    }
                }
            }
            return $value;
        } elseif (is_array($value)) {
            $values = $value;
            foreach ($values as $k => $v) {
                if (!is_array($v)) {
                    $v = trim($v);
                    $v = $this->getSafeValue($v);
                    if (($v === '' || preg_match('/union/i', $v) || preg_match('/select/i', $v) || preg_match('/left\sjoin/i', $v) || preg_match('/sleep[\s]*\(/i', $v)) and $this->getConfigValue('sql_paranoid_mode')) {
                        unset($values[$k]);
                    } else {
                        $values[$k] = $v;
                    }
                }
            }
            if (count($values) == 0) {
                return array();
            } else {
                return $values;
            }
        }

        switch ($type) {
            case 'int' :
            {
                if (!is_array($value)) {
                    $value = (int)$value;
                } else {
                    $value = 0;
                }

                break;
            }
            case 'bool' :
            {
                $value = (bool)$value;
                break;
            }
            case 'float' :
            {
                $value = preg_replace('/[^\d\.,]/', '', $value);
                break;
            }
        }

        return $value;
    }

    private function xssProtect($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = htmlspecialchars($v);
            }
        } else {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    private function getSafeValue($value)
    {
        return preg_replace('/(\/\*[^\/]*\*\/)/', '', $value);
    }

    /**
     * Set request value
     * @param string $key key
     * @param string $value value
     * @return void
     */
    function setRequestValue($key, $value)
    {
        $_REQUEST[$key] = $value;
        $_POST[$key] = $value;
        return;
    }

    function abort($code = 404, $message = "Not Found")
    {
        header("Status: $code $message");
        $this->template->assert('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
        $this->template->assert('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
        $this->template->assert('error_message', '<h1>' . Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND') . '</h1>');
        $this->template->assert('main_file_tpl', 'error_message.tpl');
        global $smarty;
        $smarty->display('main.tpl');
        exit;
    }

}
