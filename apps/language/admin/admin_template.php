<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class language_admin_template extends language_admin {

    public function saveTemplateWords($template_name, $terms, $values) {
        if (count($terms) == 0 || count($values) == 0 || $template_name == '') {
            return;
        }
        if (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/')) {
            return;
        }
        $first_key = array_shift(array_keys($values));
        //echo $first_key;
        $langs = array_keys($values[$first_key]);
        foreach ($langs as $lang) {
            
            if (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/')) {
                mkdir(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language');
            }
            if (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/' . $lang)) {
                mkdir(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/' . $lang);
            }
            $f = fopen(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/' . $lang . '/dictionary.ini', 'w');
            //echo SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $template_name . '/language/' . $lang . '/dictionary.ini<br>';
            $str = array();
            foreach ($terms as $term_k => $term) {
                if ($this->clear($term) != '') {
                    $v = $this->clear($values[$term][$lang]);
                    $v = str_replace('"', '\"', $v);
                    $str[] = $this->clear($term) . '="' . $v . '"';
                }
            }
            if (!empty($str)) {
                //echo implode("\n<br>", $str).'<br>';
                fwrite($f, implode("\n", $str));
            } else {
                fwrite($f, '');
            }

            fclose($f);
            //echo $str;
        }

        //print_r($langs);
        //$langs=array_keys(array);
    }

    public function getTemplateWordsArray($template_name, $lang) {
        if (file_exists($path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$template_name . '/language/')) {
            $path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$template_name . '/language/';
            $dictionary = array();
            $skip = array('.', '..');

            $words = array();
            $words = $this->getTemplateDictionary($template_name, $lang);
            $dictionary[$lang] = $words;

            $x = array();
            $langs_array = array();
            if (count($dictionary) > 0) {
                foreach ($dictionary as $lang => $words) {
                    $langs_array[$lang] = $lang;
                    foreach ($words as $key => $trans) {
                        $x[$key][$lang] = $trans;
                    }
                }
            }
            $keys = array_keys($x);
        }
        
        $ra['langs'] = $langs_array;
        $ra['keys'] = $keys;
        $ra['words'] = $x;

        return $ra;
    }
    
    
    private function getTemplateDictionary($template_name, $dictionary) {
        $words = array();
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$template_name . '/language/' . $dictionary . '/dictionary.ini')) {
            $words = parse_ini_file(SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$template_name . '/language/' . $dictionary . '/dictionary.ini', true);
        }
        return $words;
    }
    

}
