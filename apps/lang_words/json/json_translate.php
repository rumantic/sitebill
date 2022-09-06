<?php
require_once('../../system/bootstrap.php');


$s_translate = new STranslate();

$s_translate->next();


class STranslate {
    function load_json ( $lang ) {
        $json_string = file_get_contents('./files/'.$lang.'.json');
        $json_array = json_decode($json_string, true);
        return $json_array;
    }

    function next () {
        $source = 'en';
        $target = 'ru';
        $step = 100;

        $source_json = $this->load_json($source);
        $target_json = $this->load_json($target);
        if ( is_array($target_json) ) {
            foreach ( $target_json as $key => $value ) {
                unset($source_json[$key]);
            }
        }
        if ( count($source_json) == 0 ) {
            echo 'translate complete';
            exit;
        }
        $i = 0;
        $translate_json = array();
        if ( is_array($source_json) and count($source_json) > 0 ) {
            foreach ( $source_json as $key => $value ) {
                $i++;
                if ( $i >= $step ) {
                    break;
                }
                $translate_json[$key] = $value;
            }
        }

        $translate_json = $this->translate_hash($translate_json, $target);

        foreach ( $translate_json as $key => $value ) {
            $target_json[$key] = $value;
        }

        $this->write_json(json_encode($target_json, JSON_PRETTY_PRINT), $target);
        echo '<pre>';
        print_r($translate_json);
        echo '</pre>';
    }

    function translate_hash ($input, $target_language) {
        $values = array_values($input);
        $input_keys = array_keys($input);


        $api_key = SConfig::getConfigValueStatic('apps.language.google_translate_api_key');
        $translate = $this->google_translate_array(
            $api_key,
            $values,
            $target_language
        );

        for( $i = 0; $i < sizeof($input_keys); $i++ ) {
            $input[$input_keys[$i]] = $translate['data']['translations'][$i]['translatedText'];
        }

        return $input;
    }

    function write_json ($json, $language) {
        $replacedString = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $json);
        $json = mb_convert_encoding($replacedString, 'UTF-8', 'HTML-ENTITIES');
        file_put_contents('./files/'.$language.'.json', $json);
    }

    public function google_translate_array($api_key, $array_values, $language)
    {
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
        return json_decode($output, true);
    }

}

