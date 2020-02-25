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
        $rs .= _e('Перегрузка слов выполнена успешно').'<br>';
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
