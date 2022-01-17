<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Lang words object
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class lang_words_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'lang_words';
        $this->action = 'lang_words';
        $this->primary_key = 'word_id';
        //$form_data = $this->get_lang_words_model();
        //$this->data_model = $form_data;
        $this->initModel();
    }

    private function initModel() {
        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($this->table_name, false);
            if (empty($form_data)) {
                $form_data = array();
                $form_data = $this->get_lang_words_model($ajax);
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $this->table_name);
                $form_data = array();
                $form_data = $ATH->load_model($this->table_name, false);
            }
        } else {
            $form_data = $this->get_lang_words_model();
        }
        $this->data_model = $form_data;
    }

    protected function _reloadAction() {
        $rs .= $this->create_lang_words_table().'<br>';
        Multilanguage::reLoadWords();
        $available_languages = Multilanguage::availableLanguages();
        if ( is_array($available_languages) and @count($available_languages) > 0 ) {
            $init_languages_array = $available_languages;
        } else {
            $init_languages_array = array('ru');
        }
        foreach ( $init_languages_array as $lang_key ) {
            Multilanguage::set_current_lang($lang_key);
            Multilanguage::appendTemplateDictionary($this->getConfigValue('theme'), true);
        }

        $rs .= _e('Перегрузка слов выполнена успешно').'<br>';
        return $rs;
    }

    private function update_lang_item (  ) {

    }

    private function update_lang_record ( $language, $key, $value ) {
        $id = $this->get_id_by_filter('word_key', $key, array('lang_key' => $language));
        $record = $this->load_by_id($id);
        $rs = "id = $id, update language = $language, key = $key, value = $value<br>";
        $record['word_default']['value'] = $value;
        $this->edit_data($record, 0, $id);
        return $rs;
    }

    private function insert_lang_record ( $language, $key, $value, $source_language ) {
        $source_id = $this->get_id_by_filter('word_key', $key, array('lang_key' => $source_language));
        $record = $this->load_by_id($source_id);
        $rs = "new record = $language, key = $key, value = $value<br>";
        $record['word_default']['value'] = $value;
        $record['lang_key']['value'] = $language;
        $this->add_data($record);
        if ( $this->getError() ) {
            $rs .= '<span class="alert-danger">'.$this->getError().'</span><br>';
        }
        return $rs;
    }

    protected function _force_autotranslateAction() {
        $source_language = 'ru';
        $target_language = $this->getRequestValue('target_language');
        $rs = '';
        if ( $target_language == '' ) {
            $rs .= '<p>Не указан язык для перевода. ?action=lang_words&do=force_autotranslate&target_language=TARGET</p>';
            return $rs;
        }

        $records = \lang_words\model\LangWords::where('lang_key', '=', $source_language)->get();

        foreach ( $records as $item ) {

            $rs .= $this->insert_lang_record(
                $target_language, $item->word_key,
                $this->api_translate($item->word_default, $target_language),
                $source_language
            );
        }
        return $rs;
    }



    protected function _autotranslateAction() {
        $source_language = 'ru';
        $target_language = $this->getRequestValue('target_language');
        if ( $target_language == '' ) {
            $rs = '<p>Не указан язык для перевода. ?action=lang_words&do=autotranslate&target_language=TARGET</p>';
            return $rs;
        }


        Multilanguage::set_current_lang($source_language);
        Multilanguage::set_empty_words_array();
        Multilanguage::loadWords();
        Multilanguage::load_db_lang_words();
        $source_array = Multilanguage::get_words();

        Multilanguage::set_current_lang($target_language);
        Multilanguage::set_empty_words_array();
        Multilanguage::loadWords();
        Multilanguage::load_db_lang_words();
        $target_array = Multilanguage::get_words();
        $count = 0;
        $rs = '<p></p>';
        foreach ( $source_array as $key => $value ) {
            //echo $key.'<br>';
            if ( $target_array[$key] == $source_array[$key] ) {
                // Обновляем
                $count++;
                if ( $value != '' and $key != $value and $value != 'ID') {
                    $rs .= $this->update_lang_record($target_language, $key, $this->api_translate($value, $target_language));
                } else {
                    $rs .= "missed key = $key, value = $value<br>";
                }
            }
            if (  !isset($target_array[$key]) ) {
                // Создаем новую запись
                $count++;
                if ( $value != '' and $key != $value and $value != 'ID'  ) {
                    $rs .= $this->insert_lang_record(
                        $target_language, $key,
                        $this->api_translate($value, $target_language),
                        $source_language
                    );
                } else {
                    $rs .= "missed key = $key, value = $value<br>";
                }
            }
        }
        $rs .= 'need translate count = '.$count.'<br>';
        return $rs;
    }


    private function create_lang_words_table () {
        $DBC = DBC::getInstance();

        $query_data[] = "CREATE TABLE `" . DB_PREFIX . "_lang_words` (
  `word_id` int(10) UNSIGNED NOT NULL,
  `lang_id` mediumint(4) UNSIGNED NOT NULL,
  `word_app` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `word_pack` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `word_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `word_default` text COLLATE utf8_unicode_ci NOT NULL,
  `word_custom` text COLLATE utf8_unicode_ci,
  `word_default_version` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `word_custom_version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `word_js` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `lang_key` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";
        $query_data[] = "ALTER TABLE `" . DB_PREFIX . "_lang_words`
  ADD PRIMARY KEY (`word_id`),
  ADD UNIQUE KEY `lang_key_word` (`lang_key`,`word_key`,`word_app`),
  ADD KEY `word_js` (`word_js`);
";
        $query_data[] = "ALTER TABLE `" . DB_PREFIX . "_lang_words`
  MODIFY `word_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
";

        foreach ($query_data as $query) {
            $success = false;
            $rows = 0;
            $stmt = $DBC->query($query, array(), $rows, $success);
            if (!$success) {
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
            }
        }
        return $rs;
    }


    function getTopMenu() {
        $rs = parent::getTopMenu();
        $rs .= '<a href="?action=' . $this->action . '&do=reload" class="btn btn-primary">' . _e('Перегрузить базу переводов') . '</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=autotranslate" class="btn btn-primary">' . _e('Автоперевод') . '</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=force_autotranslate" class="btn btn-primary">' . _e('Форсированное создание нового языка из RU') . '</a> ';
        return $rs;
    }

    public function get_lang_words_model() {
        $form_data = array();

        $form_data['lang_words']['word_id']['name'] = 'word_id';
        $form_data['lang_words']['word_id']['title'] = _e('ID');
        $form_data['lang_words']['word_id']['value'] = 0;
        $form_data['lang_words']['word_id']['length'] = 40;
        $form_data['lang_words']['word_id']['type'] = 'primary_key';
        $form_data['lang_words']['word_id']['required'] = 'off';
        $form_data['lang_words']['word_id']['unique'] = 'off';

        $form_data['lang_words']['word_app']['name'] = 'word_app';
        $form_data['lang_words']['word_app']['title'] = _e('Приложение');
        $form_data['lang_words']['word_app']['value'] = '';
        $form_data['lang_words']['word_app']['length'] = 40;
        $form_data['lang_words']['word_app']['type'] = 'safe_string';
        $form_data['lang_words']['word_app']['required'] = 'on';
        $form_data['lang_words']['word_app']['unique'] = 'off';

        $form_data['lang_words']['word_pack']['name'] = 'word_pack';
        $form_data['lang_words']['word_pack']['title'] = _e('Метка для поиска');
        $form_data['lang_words']['word_pack']['value'] = '';
        $form_data['lang_words']['word_pack']['length'] = 40;
        $form_data['lang_words']['word_pack']['type'] = 'safe_string';
        $form_data['lang_words']['word_pack']['required'] = 'on';
        $form_data['lang_words']['word_pack']['unique'] = 'off';

        $form_data['lang_words']['word_key']['name'] = 'word_key';
        $form_data['lang_words']['word_key']['title'] = _e('Уникальный ключ');
        $form_data['lang_words']['word_key']['value'] = '';
        $form_data['lang_words']['word_key']['length'] = 40;
        $form_data['lang_words']['word_key']['type'] = 'safe_string';
        $form_data['lang_words']['word_key']['required'] = 'on';
        $form_data['lang_words']['word_key']['unique'] = 'off';

        $form_data['lang_words']['word_default']['name'] = 'word_default';
        $form_data['lang_words']['word_default']['title'] = _e('Перевод');
        $form_data['lang_words']['word_default']['value'] = '';
        $form_data['lang_words']['word_default']['length'] = 40;
        $form_data['lang_words']['word_default']['type'] = 'textarea';
        $form_data['lang_words']['word_default']['required'] = 'on';
        $form_data['lang_words']['word_default']['unique'] = 'off';

        $form_data['lang_words']['lang_key']['name'] = 'lang_key';
        $form_data['lang_words']['lang_key']['title'] = _e('Язык');
        $form_data['lang_words']['lang_key']['value'] = '';
        $form_data['lang_words']['lang_key']['length'] = 40;
        $form_data['lang_words']['lang_key']['type'] = 'safe_string';
        $form_data['lang_words']['lang_key']['required'] = 'on';
        $form_data['lang_words']['lang_key']['unique'] = 'off';

        return $form_data;
    }

}
