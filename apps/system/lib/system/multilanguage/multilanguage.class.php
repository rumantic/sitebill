<?php
function md5_key( $text ) {
    if ( preg_match('/L_/', $text) or preg_match('/LT_/', $text) ) {
        $key = $text;
    } else {
        $key = md5($text);
    }
    return $key;
}

/**
 * Обычная процедурная функция подключается в шаблоне и выполняет перевод с помощью google_translate в шаблонах
 * Создает для каждой переводимой строки транслит ключ и записывает в /template/frontend/шаблон/language/ЯЗЫК/dictionary.ini нужный перевод для этого ключа
 * В шаблоне вместо текстовой статичной строки Привет мир! Писать так {_e t="Привет мир!"}
 * @param array $t array('t' => 'value')
 * @return string
 */
function _translate($t) {
    //return $t['t'];
    $sitebill = new SiteBill();
    /*
    if (function_exists('transliterator_transliterate')) {
        $key = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $t['t']);
        $key = preg_replace('/[-\s]+/', '-', $key);
        $key = str_replace('ʼ', '', $key);
    } else {
        $key = $sitebill->transliteMe($t['t']);
    }
     *
     */
    $key = md5_key($t['t']);

    $template_key = $sitebill->getConfigValue('theme') . '_template';
    /*
    if (strlen($key) > 100) {
        $key = substr($key, 0, 100) . '_' . substr(md5($key), 0, 7);
    }
     *
     */
    Multilanguage::appendTemplateDictionary($sitebill->getConfigValue('theme'));
    if (!Multilanguage::is_set_any($key, $template_key)) {
        //echo 'from db '.$key;
        $lang = Multilanguage::get_current_language();
        $translate = $sitebill->google_translate($t['t'], $lang);
        if ($translate != '') {
            //require_once (SITEBILL_DOCUMENT_ROOT . '/apps/language/admin/admin.php');
            //require_once (SITEBILL_DOCUMENT_ROOT . '/apps/language/admin/admin_template.php');
            //$language_admin_template = new language_admin_template();
            //$template_languages = $language_admin_template->getTemplateWordsArray($sitebill->getConfigValue('theme'), $lang);
            //$terms = $template_languages['keys'];
            //$values = $template_languages['words'];
            //@array_push($terms, $key);
            //$values[$key][$lang] = $translate;
            //array_push($values, $translate);
            //$terms[0] = $key;
            //$values[0][$lang] = $translate;
            //$language_admin_template->saveTemplateWords($sitebill->getConfigValue('theme'), $terms, $values);
            Multilanguage::insert_lang_words($sitebill->getConfigValue('theme') . '_template', $lang, $key, $translate);
            return $translate;
        } else {
            Multilanguage::insert_lang_words($sitebill->getConfigValue('theme') . '_template', $lang, $key, $t['t']);
        }
        return $t['t'];
    } else {
        return Multilanguage::_any($key, $template_key);
    }
}


function _e($value) {
    return _translate(array('t' => $value));
}
function _ed($value, $editable = false) {
    $name = 'lang_words';
    $uri = md5_key($value);
    $key = 'word_default';
    $value_key = 'value';
    $content = _translate(array('t' => $value));
    return \bridge\Helpers\Helpers::editor_wrapper($content, $name, $uri, $key, $value_key);
}

class Multilanguage {

    private static $instance = NULL;

    // Старотовые параметры
    private static $default_lang = 'ru';
    private static $default_mode = 'backend';
    private static $current_lang = '';
    private static $current_mode = '';
    private $language = 'ru';
    private $mode = 'frontend';

    // массив системных слов
    private static $words = array();

    private static $words_in_smarty_inited = false;

    // массив системных словарей приложений
    private static $apps_words = array();

    // массив слов бекенда
    private static $backend_words = array();

    // массив слов фронтенда
    private static $frontend_words = array();

    // признак загруженности словаря шаблона
    private static $is_tpl_loaded = false;

    // массив-индекс всех загруженных слов
    private static $all_db_records = array();

    public static function set_current_lang ($lang) {
        self::$current_lang = $lang;
    }

    public static function start($mode = '', $lang_code = '') {
        self::setOptions($mode, $lang_code);
    }

    public static function getInstance($mode = '', $lang_code = '') {
        if (self::$instance == NULL) {
            self::$instance = new Multilanguage();
            self::$instance->setOpt($mode, $lang_code);
        } else {
            self::$instance->setOpt($mode, $lang_code);
        }
        return self::$instance;
    }

    /**
     * Проверка наличия любого варианта слова по коду или учетом приложения в текущем словаре
     * @param string $key код слова
     * @param string $app код приложения
     * @return bool
     */
    public static function is_set_any($key, $app) {
        if (isset(self::$apps_words['empty'][$key])) {
            return true;
        } elseif (isset(self::$words[$key])) {
            return true;
        }

        if ($app != '' && isset(self::$apps_words[$app])) {
            if (isset(self::$apps_words[$app][$key])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проверка наличия варианта слова по коду с учетом приложения в текущем словаре
     * @param string $key код слова
     * @param string $app код приложения
     * @return bool
     */
    public static function is_set($key, $app = '') {
        if ($app != '' && isset(self::$apps_words[$app])) {
            if (isset(self::$apps_words[$app][$key])) {
                return true;
            } else {
                return false;
            }
        } else {
            if (isset(self::$apps_words['empty'][$key])) {
                return true;
            } elseif (isset(self::$words[$key])) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Возврат любого доступного варианта значения слова в текущем загруженном словаре либо обратно кода
     * @param string $key ключ слова
     * @param string $app код приложения
     * @return string
     */
    public static function _any($key, $app = '') {
        //echo 'key = '.$key.', app = '.$app.'<br>';
            if (isset(self::$words[$key])) {
                return self::$words[$key];
            } elseif (isset(self::$apps_words['empty'][$key])) {
                return self::$apps_words['empty'][$key];
            }

        if ($app != '' && isset(self::$apps_words[$app])) {
            if (isset(self::$apps_words[$app][$key])) {
                return self::$apps_words[$app][$key];
            } else {
                return $app . '.' . $key;
            }
        }
        return $key;
    }


    /**
     * Получение значения слова
     * @param string $key ключ слова
     * @param string $app код приложения
     * @return string
     */
    public static function _($key, $app = '') {
        //echo 'key = '.$key.', app = '.$app.'<br>';
        if ($app != '' && isset(self::$apps_words[$app])) {
            if (isset(self::$apps_words[$app][$key])) {
                return self::$apps_words[$app][$key];
            } else {
                self::insert_lang_words($app, self::$current_lang, $key, $key);
                return $app . '.' . $key;
            }
        } else {
            if (isset(self::$words[$key])) {
                return self::$words[$key];
            } else {
                self::insert_lang_words('empty', self::$current_lang, $key, $key);
                return $key;
            }
        }
    }

    public static function text($key) {
        if (isset(self::$words[$key])) {
            return self::$words[$key];
        } else {
            return $key;
        }
    }

    /**
     * Загрузка словаря приложения
     * @param $app_name код приложения
     * @param string $template имя шаблона, если локализация
     * @param bool $force признак принудительно перезаписи значений
     * @param bool $reload_language код языка, для которого выполняется подключение словаря
     */
    public static function appendAppDictionary($app_name, $template = '', $force = false, $reload_language = false) {
        //return;
        if ( $reload_language ) {
            $current_language = $reload_language;
        } else {
            $current_language = self::$current_lang;
        }

        if (isset(self::$apps_words[$app_name]) and !$force) {
            return;
        } elseif ($current_language == '') {
            return;
        }
        //if ( !$force ) {
        //    return;
        //}
        //echo $app_name.'='.self::$current_lang.'<br>';
        //echo 'app '.$app_name.'<br>';

        global $smarty;
        $file_name = SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_name . '/language/' . $current_language . '/dictionary.ini';

        if (file_exists($file_name)) {
            self::$apps_words[$app_name] = parse_ini_file($file_name, true);
            //echo 'init a 1 '.$file_name.'<br>';
            self::init_db_lang_words(self::$apps_words);
        } else {
            $file_name = SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_name . '/language/' . self::$default_lang . '/dictionary.ini';
            if (file_exists($file_name)) {
                //echo $file_name . '<br>';
                self::$apps_words[$app_name] = parse_ini_file($file_name, true);
                self::init_db_lang_words(self::$apps_words);
            }
        }

        $SConfig = SConfig::getInstance();
        $template = $SConfig->getConfigValue('theme');

        if ($template != '' && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template . '/apps/' . $app_name . '/language/' . $current_language . '/dictionary.ini')) {
            $words = self::$apps_words[$app_name];

            $new_words = parse_ini_file(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template . '/apps/' . $app_name . '/language/' . $current_language . '/dictionary.ini', true);
            if (isset(self::$apps_words[$app_name])) {
                self::$apps_words[$app_name] = array_merge(self::$apps_words[$app_name], $new_words);
            } else {
                self::$apps_words[$app_name] = $new_words;
            }
            self::init_db_lang_words(self::$apps_words);
        }

        self::assign($smarty);
    }

    /**
     * Запись в базу массива слов
     * @param array $words массив слов
     * @return bool
     */
    public static function init_db_lang_words($words) {
        //return;
        //echo '<pre>';
        //print_r($words);
        //echo '</pre>';
        //echo 'init_db_lang_words<br>';
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_lang_words (word_app, lang_key, word_key, word_default, word_pack) values (?, ?, ?, ?, ?)';
        foreach ($words as $app => $app_array) {
            if (!is_array($app_array)) {
                $app = 'empty';
                foreach ($words as $key => $value) {
                    if ( !isset(self::$all_db_records[$app]) || self::$all_db_records[$app][$key] != true ) {
                        $stmt = $DBC->query($query, array($app, self::$current_lang, $key, $value, substr($value, 0, 50)), $success);
                        if (!$success) {
                            //echo $DBC->getLastError() . '<br>';
                        } else {
                            //echo 'app = '.$app.', key = '.$key.', value = '.$value.', lang_key = '.self::$current_lang.'<br>';
                        }
                    }

                }
                return true;
            } else {
                foreach ($app_array as $key => $value) {
                    if ( !isset(self::$all_db_records[$app]) || self::$all_db_records[$app][$key] != true ) {
                        $stmt = $DBC->query($query, array($app, self::$current_lang, $key, $value, substr($value, 0, 50)), $success);
                        if (!$success) {
                            //echo $DBC->getLastError() . '<br>';
                        } else {
                            //echo 'app = '.$app.', key = '.$key.', value = '.$value.', lang_key = '.self::$current_lang.'<br>';
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Вставка варианта слова с проверкой
     * @param $app секция слов (приложение или шаблон)
     * @param $lang код языка
     * @param $key ключ слова
     * @param $value значение слова
     */
    public static function insert_lang_words($app, $lang, $key, $value) {
        if ( @self::$all_db_records[$app][$key] != true ) {
            $DBC = DBC::getInstance();
            $query = 'INSERT INTO ' . DB_PREFIX . '_lang_words (word_app, lang_key, word_key, word_default, word_pack) values (?, ?, ?, ?, ?)';
            $stmt = $DBC->query($query, array($app, $lang, $key, $value, substr($value, 0, 50)), $success);
        }
    }

    /**
     * Загрузка словаря из базы по текущему языку
     */
    public static function load_db_lang_words() {
        //return;

        $SConfig = SConfig::getInstance();
        $template_key = $SConfig->getConfigValue('theme') . '_template';

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_lang_words WHERE lang_key=?';
        //echo self::$current_lang;
        $stmt = $DBC->query($query, array(self::$current_lang));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                self::$all_db_records[$ar['word_app']][$ar['word_key']] = true;
                //echo $ar['word_key'].' = '.$ar['word_default'].'<br>';
                self::$apps_words[$ar['word_app']][$ar['word_key']] = $ar['word_default'];
                if ($ar['word_app'] == 'empty' || $ar['word_app'] == $template_key) {
                    self::$words[$ar['word_key']] = $ar['word_default'];
                }
                if ($ar['word_app'] == $template_key) {
                    self::$is_tpl_loaded = true;
                }
            }
        }
    }

    public static function assign(&$smarty) {
        if ( self::$words_in_smarty_inited ) {
             return;
        }
        if (!is_object($smarty)) {
            return false;
        }
        foreach (self::$words as $k => $w) {
            $smarty->assign($k, $w);
        }

        $smarty->assign('apps_words', self::$apps_words);
        self::$words_in_smarty_inited = true;
    }

    private function __construct() {

    }

    private function __clone() {

    }

    private static function setOptions($mode, $lang_code) {
        $lang_code = trim(preg_replace('/[^a-z]/i', '', $lang_code));
        if ($mode != '' AND in_array($mode, array('frontend', 'backend'))) {
            self::$current_mode = $mode;
        } else {
            self::$current_mode = (self::$current_mode == '' ? self::$default_mode : self::$current_mode);
        }
        if ($lang_code != '') {
            self::$current_lang = $lang_code;
        } else {
            self::$current_lang = (self::$current_lang == '' ? self::$default_lang : self::$current_lang);
        }
        self::load_db_lang_words();
        self::loadWords();
        //self::init_db_lang_words(self::$words);
        global $smarty;
        self::assign($smarty);
    }

    private function setOpt($mode, $lang_code) {
        if ($mode != '' AND in_array($mode, array('frontend', 'backend'))) {
            self::$current_mode = $mode;
        } else {
            self::$current_mode = (self::$current_mode == '' ? self::$default_mode : self::$current_mode);
        }
        if ($lang_code != '') {
            self::$current_lang = $lang_code;
        } else {
            self::$current_lang = (self::$current_lang == '' ? self::$default_lang : self::$current_lang);
        }
        self::loadWords();
        global $smarty;
        self::assign($smarty);
    }

    public static function reLoadWords() {
        $init_languages_array = array('ru');
        $available_languages = self::availableLanguages();
        if ( is_array($available_languages) and @count($available_languages) > 0 ) {
            $init_languages_array = $available_languages;
        }

        if ( is_array(self::$apps_words) ) {
            foreach ( $init_languages_array as $lang_key ) {
                self::$all_db_records = array();
                self::$current_lang = $lang_key;
                foreach ( self::$apps_words as $app_name => $app_array ) {
                    self::appendAppDictionary($app_name, '', true, $lang_key);
                }
                self::loadBackendWords();
                self::loadFrontendWords();
                self::$words = array_merge(self::$words, self::$backend_words);
                self::$words = array_merge(self::$words, self::$frontend_words);
            }
        }
    }

    public static function loadWords() {
        $dictionary = array();
        if (empty(self::$words)) {
            self::loadBackendWords();
            self::loadFrontendWords();
            self::$words = array_merge(self::$words, self::$backend_words);
            self::$words = array_merge(self::$words, self::$frontend_words);
            //echo '<pre>';
            //print_r(self::$words);
            //echo '</pre>';
            //self::init_db_lang_words(self::$words);
        }
        /*
          $file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$current_lang.'/'.self::$current_mode.'.ini';
          if(file_exists($file_name)){

          }else{
          $file_name=SITEBILL_DOCUMENT_ROOT.'/apps/language/language/'.self::$default_lang.'/'.self::$default_mode.'.ini';
          }
          self::$words=parse_ini_file($file_name,true); */

        /* if ( self::$current_mode == 'frontend' ) {
          self::loadBackendWords();
          self::$words = array_merge(self::$words, self::$backend_words);
          } */
    }

    public static function appendTemplateDictionary($template_name, $force = false) {
        if (self::$is_tpl_loaded and !$force) {
            return;
        }
        global $smarty;
        $file_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/' . self::$current_lang . '/dictionary.ini';
        //echo $file_name.'<br>';

        if (file_exists($file_name)) {
            //echo $file_name . '<br>';
            $words = parse_ini_file($file_name, true);
            if (!is_object($smarty)) {
                return false;
            }
            foreach ($words as $k => $w) {
                self::$apps_words[$template_name . '_template'][$k] = $w;
                self::$words[$k] = $w;
                $smarty->assign($k, $w);
            }
            //echo 'init t<br>';

            self::init_db_lang_words(self::$apps_words);
            self::$is_tpl_loaded = true;
        }
    }

    private static function loadBackendWords() {
        $file_name = SITEBILL_DOCUMENT_ROOT . '/apps/language/language/' . self::$current_lang . '/backend.ini';
        if (file_exists($file_name)) {

        } else {
            $file_name = SITEBILL_DOCUMENT_ROOT . '/apps/language/language/' . self::$default_lang . '/backend.ini';
        }
        //echo $file_name . '<br>';
        self::$backend_words = parse_ini_file($file_name, true);
        //echo 'init b<br>';

        self::init_db_lang_words(self::$backend_words);
    }

    private static function loadFrontendWords() {
        $file_name = SITEBILL_DOCUMENT_ROOT . '/apps/language/language/' . self::$current_lang . '/frontend.ini';
        if (file_exists($file_name)) {

        } else {
            $file_name = SITEBILL_DOCUMENT_ROOT . '/apps/language/language/' . self::$default_lang . '/frontend.ini';
        }
        //echo $file_name . '<br>';

        self::$frontend_words = parse_ini_file($file_name, true);
        //echo 'init f<br>';
        self::init_db_lang_words(self::$frontend_words);
    }

    public static function availableLanguages() {
        $langs = array();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/language/admin/admin.php';
        $LM = new language_admin();

        $_langs = $LM->getLanguages();
        if (count($_langs) > 0) {
            foreach ($_langs as $lk => $lv) {
                $langs[$lk] = $lk;
            }
        }
        return $langs;
        /* $path=SITEBILL_DOCUMENT_ROOT.'/apps/system/language/';
          $skip = array('.', '..', '.svn');
          $files = scandir($path);
          foreach($files as $file) {
          if(!in_array($file, $skip)){
          $langs[$file]=$file;
          }
          }
          return $langs; */
    }

    public static function get_current_language() {
        return $_SESSION['_lang'];
    }

    public static function foreignLanguages() {
        $languages = self::availableLanguages();
        unset($languages['ru']);
        return $languages;
    }

    public static function set_word($key, $value, $app = '') {
        if ($app != '' && isset(self::$apps_words[$app])) {
            self::$apps_words[$app][$key] = $value;
        } else {
            self::$words[$key] = $value;
        }
    }

    public static function get_words () {
        return self::$words;
    }

    public static function set_empty_words_array() {
        self::$words = array();
    }

    public static function get_apps_words () {
        return self::$apps_words;
    }

}
