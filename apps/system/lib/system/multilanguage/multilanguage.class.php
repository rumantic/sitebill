<?php
if(!function_exists('md5_key')){
    /**
     * Create md5-hash for text label
     * @param string $text
     * @return string
     */
    function md5_key( $text ) {
        if ( preg_match('/L_/', $text) || preg_match('/LT_/', $text) ) {
            $key = $text;
        } else {
            $key = md5($text);
        }
        return $key;
    }
}

if(!function_exists('_trans')){
    /**
     * Return word value
     * @param string $key Dictionary key/label (for apps use 'appcode.label')
     * @param array $replacements Array of placeholders ['varname1' => 'value1', 'varname2' => 'value2']
     * @return string
     */
    function _trans($key, $replacements = [])
    {
        $parts = explode('.', $key);
        $placeholders = [];
        if(isset($replacements) && is_array($replacements) && !empty($replacements)){
            $placeholders = $replacements;
        }
        if (count($parts) == 2) {
            return Multilanguage::_($parts[1], $parts[0], $placeholders);
        } elseif (count($parts) == 1) {
            return Multilanguage::_($parts[0], '', $placeholders);
        }
        return '##ERROR##';
    }
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

    // Loaded dictionaries
    //private static $loaded = [];

    private static $instance = NULL;

    // Стартовые параметры
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
     * Return word value
     * placeholder must be marked with colon inside text. Ex. 'Some text with :placeholder written here'
     * @param string $key Key/label
     * @param string $app App code
     * @param array $placeholders Array of placeholders ['varname1' => 'value1', 'varname2' => 'value2']
     * @return string
     */
    public static function _($key, $app = '', $placeholders = []) {
        if ($app != '' && isset(self::$apps_words[$app])) {
            if (isset(self::$apps_words[$app][$key])) {
                $word = self::$apps_words[$app][$key];
            } else {
                self::insert_lang_words($app, self::$current_lang, $key, $key);
                $word = $app . '.' . $key;
            }
        } else {
            if (isset(self::$words[$key])) {
                $word = self::$words[$key];
            } else {
                self::insert_lang_words('empty', self::$current_lang, $key, $key);
                $word = $key;
            }
        }

        if(!empty($placeholders)){
            $replaces = [];
            foreach ($placeholders as $pk => $pv){
                $replaces[':'.$pk] = $pv;
            }
            $word = strtr($word, $replaces);
        }
        return $word;
    }

    /**
     * Получение текстового значения перевода по коду
     * @param string $key
     * @return string
     */
    public static function text($key) {
        if (isset(self::$words[$key])) {
            return self::$words[$key];
        } else {
            return $key;
        }
    }

    /**
     * Загрузка словаря приложения
     * @param string $app_name код приложения
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


        global $smarty;

        $appwords = [];
        $nativeappdictionary = SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_name . '/language/' . $current_language . '/dictionary.ini';
        $nativeappdictionary_replacement = SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_name . '/language/' . self::$default_lang . '/dictionary.ini';
        if (file_exists($nativeappdictionary)) {
            $appwords = parse_ini_file($nativeappdictionary, true);
        }elseif (file_exists($nativeappdictionary_replacement)) {
            $appwords = parse_ini_file($nativeappdictionary_replacement, true);
        }

        $SConfig = SConfig::getInstance();
        $template = $SConfig->getConfigValue('theme');

        if ($template != '' && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template . '/apps/' . $app_name . '/language/' . $current_language . '/dictionary.ini')) {
            $appwords = array_merge($appwords, parse_ini_file(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template . '/apps/' . $app_name . '/language/' . $current_language . '/dictionary.ini', true));
        }
        self::$apps_words[$app_name] = $appwords;
        self::init_db_lang_words(self::$apps_words);
        self::assign($smarty);
    }

    /**
     * Пакетная запись слов в БД
     * @param $app
     * @param $words
     * @param $lang
     * @param $all_db_records
     * @return bool
     */
    private static function storeWords($app, $words, $lang, $all_db_records){

        $start = 0;
        $step = 100;
        $insert = array_slice($words, $start, $step);

        $DBC = DBC::getInstance();

        while(!empty($insert)){
            $insertableplaceholders = array();
            $insertablevalues = array();
            foreach ($insert as $key => $value) {
                if ( !isset($all_db_records[$app]) || $all_db_records[$app][$key] != true ) {
                    $insertableplaceholders[] = '(?, ?, ?, ?, ?)';
                    $insertablevalues[] = $app;
                    $insertablevalues[] = $lang;
                    $insertablevalues[] = $key;
                    $insertablevalues[] = $value;
                    $insertablevalues[] = mb_substr($value, 0, 50, 'utf-8');
                }
            }

            if(!empty($insertableplaceholders)){
                $query = 'INSERT INTO ' . DB_PREFIX . '_lang_words (`word_app`, `lang_key`, `word_key`, `word_default`, `word_pack`) VALUES '.implode(',', $insertableplaceholders).' ON DUPLICATE KEY UPDATE
word_default = VALUES(word_default), word_pack = VALUES(word_pack)';
                $stmt = $DBC->query($query, $insertablevalues);
            }
            $start += $step;
            $insert = array_slice($words, $start, $step);
        }

        return true;
    }

    /**
     * Запись в базу массива слов
     * @param array $words массив слов
     * @return bool
     */
    public static function init_db_lang_words($words) {

        // Использование пакетной записи слов в БД
        /*
        foreach($words as $app => $app_array){
            if (!is_array($app_array)) {
                $result = self::storeWords('empty', $words, self::$current_lang, self::$all_db_records);
            }else{
                $result = self::storeWords($app, $app_array, self::$current_lang, self::$all_db_records);
            }
        }

        return $result;
        */


        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_lang_words (word_app, lang_key, word_key, word_default, word_pack) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE word_default = VALUES(word_default), word_pack = VALUES(word_pack)';



        foreach ($words as $app => $app_array) {
            if (!is_array($app_array)) {
                $app = 'empty';
                foreach ($words as $key => $value) {
                    if ( !isset(self::$all_db_records[$app]) || self::$all_db_records[$app][$key] != true ) {
                        $stmt = $DBC->query($query, array($app, self::$current_lang, $key, $value, mb_substr($value, 0, 50, 'utf-8')), $success);
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
                        $stmt = $DBC->query($query, array($app, self::$current_lang, $key, $value, mb_substr($value, 0, 50, 'utf-8')), $success);
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
            $stmt = $DBC->query($query, array($app, $lang, $key, $value, mb_substr($value, 0, 50, 'utf-8')), $success);
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

    /**
     * Импорт словарей в переменные Smarty
     * @param Smarty $smarty
     * @return false|void
     */
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

    /**
     * Горячая перезагрузка словарей
     */
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

    /**
     * Загрузка фронтенд-бекенд словарей из приложения language
     */
    public static function loadWords() {
        if (empty(self::$words)) {
            self::loadBackendWords();
            self::loadFrontendWords();
            self::$words = array_merge(self::$words, self::$backend_words);
            self::$words = array_merge(self::$words, self::$frontend_words);
        }
    }

    /**
     * Загрузка словаря шаблона
     * @param string $template_name
     * @param bool $force
     * @return bool|void
     */
    public static function appendTemplateDictionary($template_name, $force = false) {
        if (self::$is_tpl_loaded and !$force) {
            return;
        }
        global $smarty;
        $file_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/' . self::$current_lang . '/dictionary.ini';

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

    /**
     * Получение массива используемых языков
     * @return array
     */
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
    }

    public static function get_current_language() {
        return $_SESSION['_lang'];
    }

    /**
     * Получение используемых языков за вычетом RU
     * @return array
     */
    public static function foreignLanguages() {
        $languages = self::availableLanguages();
        unset($languages['ru']);
        return $languages;
    }

    /**
     * Установка нового значения словарной переменной или создание новой переменной
     * @param string $key
     * @param mixed $value
     * @param string $app
     */
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

    /**
     * Load dictionary for locale
     * @param string $locale
     */
    /*private static function load($locale){
        if(!isset(self::$loaded[$locale])){
            self::$loaded[$locale] = [];
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_lang_words WHERE lang_key = ?';
        $stmt = $DBC->query($query, array($locale));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                self::$loaded[$locale][$ar['word_app']][$ar['word_key']] = $ar['word_default'];
            }
        }
    }*/

    /**
     * Equal for Sitebill::getWord
     * Translate label to text by dictionary
     * @param array $params
     * @return string
     */
    /*public static function _trans($params){
        $parts = explode('.', $params['key']);
        if (count($parts) == 2) {
            return Multilanguage::_($parts[1], $parts[0]);
        } elseif (count($parts) == 1) {
            return Multilanguage::_($parts[0]);
        }
        return '##ERROR##';
    }*/

}
