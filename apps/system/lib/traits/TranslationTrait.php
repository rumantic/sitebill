<?php
/**
 * TranslationTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait TranslationTrait
{
    public function getWord($params)
    {
        $parts = explode('.', $params['key']);
        $placeholders = [];
        if(isset($params['placeholders']) && is_array($params['placeholders']) && !empty($params['placeholders'])){
            $placeholders = $params['placeholders'];
        }
        if (count($parts) == 2) {
            return Multilanguage::_($parts[1], $parts[0], $placeholders);
        } elseif (count($parts) == 1) {
            return Multilanguage::_($parts[0], '', $placeholders);
        }
        return '##ERROR##';
        /*$key = (isset($params['key']) ? $params['key'] : '');
        $app = (isset($params['app']) ? $params['app'] : $this->getConfigValue('theme') . '_template');
        return Multilanguage::_($key, $app);*/
    }

    /**
     * Return lang postfix for column name in format _lang
     * @param string $curlang Current lang code
     * @return string
     */
    function getLangPostfix($curlang)
    {

        $postfix = '';

        $default_lng = '';
        if (1 == $this->getConfigValue('apps.language.use_default_as_ru')) {
            $default_lng = 'ru';
        } elseif ('' != trim($this->getConfigValue('apps.language.use_as_default'))) {
            $default_lng = trim($this->getConfigValue('apps.language.use_as_default'));
        }

        if ($default_lng != '' && $default_lng == $curlang) {

        } else {
            $postfix = '_' . $curlang;
        }

        return $postfix;
    }

    public static function setLangSession()
    {

        $C = SConfig::getInstance();

        $langs = array();

        $langlist = trim($C::getConfigValueStatic('apps.language.languages'));

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
        }

        if (isset($_GET['_lang'])) {

            $lang = trim(preg_replace('/[^a-z]/i', '', $_GET['_lang']));
            if ($lang != '' && isset($langs[$lang])) {
                $_SESSION['_lang'] = $lang;
            }
        }
        if (!isset($_SESSION['_lang']) || $_SESSION['_lang'] == '') {

            if ('' == trim($C->getConfigValue('apps.language.default_lang_code'))) {
                $_SESSION['_lang'] = 'ru';
            } else {
                $_SESSION['_lang'] = trim($C->getConfigValue('apps.language.default_lang_code'));
            }
        }
    }

    public function yandex_translate($value, $language)
    {
        if ($language == 'ge') {
            $language = 'ka';
        }
        $api_key = $this->getConfigValue('apps.language.yandex_translate_api_key');
        if ($api_key == '') {
            return '';
        }
        if ($value == '') {
            return '';
        }

        $url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $api_key . '&format=html&lang=' . $language . '&text=' . urlencode($value);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($ch);
        curl_close($ch);

        if (false === $result) {
            return '';
        }
        $res = json_decode($result);
        if ($res->code == '200') {
            return $res->text[0];
        } elseif ($res->code == '403') {
            $err = 'Превышено суточное ограничение на количество запросов';
        } elseif ($res->code == '404') {
            //resetCurrentYandexKey();
            $err = 'Превышено суточное ограничение на объем переведенного текста';
        } elseif ($res->code == '413') {
            $err = 'Превышен максимально допустимый размер текста';
        } elseif ($res->code == '422') {
            $err = 'Текст не может быть переведен';
        } elseif ($res->code == '402') {
            //resetCurrentYandexKey();
            $err = 'Ключ API заблокирован';
        } else {
            $err = 'Другая ошибка';
        }
        $this->writeLog(__METHOD__ . ', value = ' . $value . ', target_language = ' . $language . ', error = ' . $err);
        return '';
    }

    public function google_translate_array($api_key, $array_values, $language)
    {
        //$url = 'https://translation.googleapis.com/language/translate/v2?q=Привет&q=Мир';
        $url = 'https://translation.googleapis.com/language/translate/v2';

        $params = array(
            'key' => $api_key,
            'format' => 'html',
            'target' => $language,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params) . "&q=" . implode('&q=', $array_values));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function google_translate_string($api_key, $value, $language)
    {
        $url = 'https://translation.googleapis.com/language/translate/v2';

        $params = array(
            'key' => $api_key,
            'format' => 'html',
            'target' => $language,
            'q' => $value
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function google_translate($value, $language)
    {
        if ($language == 'ge') {
            $language = 'ka';
        }
        if ($language == 'tj') {
            $language = 'tg';
        }
        if ($language == 'ua') {
            $language = 'ukr';
        }

        $api_key = $this->getConfigValue('apps.language.google_translate_api_key');
        if ($api_key == '') {
            return $value;
        }
        if ($value == '') {
            return '';
        }
        if (is_array($value)) {
            $output = $this->google_translate_array($api_key, $value, $language);
            $langdata = json_decode($output, true);
        } else {
            $output = $this->google_translate_string($api_key, $value, $language);
            $langdata = json_decode($output, true);

        }
        if (isset($output['error'])) {
            $this->riseError('Google translation error: ' . $output['error']['message']);
        }
        $this->writeLog(__METHOD__ . ', value = ' . $value . ', target_language = ' . $language . ', langdata = ' . var_export($langdata, true));

        if (is_string($value) and $langdata['data']['translations'][0]['translatedText'] != '') {
            return $langdata['data']['translations'][0]['translatedText'];
        } elseif (is_array($value)) {
            return $this->parse_pure_array_from_google_tranlations($langdata['data']['translations']);
        }
        return '';
    }

    private function parse_pure_array_from_google_tranlations($translations)
    {
        foreach ($translations as $key => $value) {
            $ra[] = $value['translatedText'];
        }
        return $ra;
    }

    function api_translate($value, $language)
    {
        if (1 == intval($this->getRequestValue('apps.language.autotrans_api'))) {
            return $this->yandex_translate($value, $language);
        } else {
            return $this->google_translate($value, $language);
        }
    }

    public function mtphn($s)
    {
        if (!function_exists('transliterator_transliterate') or !function_exists('metaphone')) {
            echo 'Для работы функции метафона нужно установить (PHP 5 >= 5.4.0, PHP 7, PECL intl >= 2.0.0';
            exit;
        }
        $key = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $s);
        $key = preg_replace('/[-\s]+/', '-', $key);
        $key = str_replace('ʼ', '', $key);
        //echo $key.'<br>';
        return metaphone($key);
    }

    function getSessionLanguage()
    {
        return $_SESSION['_lang'];
    }

}
