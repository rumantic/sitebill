<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Memory list admin
 * Класс для обработки сохраненных списков пользователей (подборки на просмотр квартир, списки сравнения, избранные варианты)
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class memorylist_user extends Object_Manager {
    function __construct($realty_type = false)
    {
        $this->SiteBill();
        $this->table_name = 'memorylist_user';
        $this->action = 'memorylist_user';
        $this->primary_key = 'id';
        $this->data_model = $this->get_model();
    }

    public function get_model()
    {
        return array(
            'memorylist_user' => array (
                'id' => array (
                    'name' => 'id',
                    'title' => 'id',
                    'type' => 'primary_key',
                    'value' => '0',
                    'required' => 'off',
                ),
                'memorylist_id' => array (
                    'name' => 'memorylist_id',
                    'title' => 'Выбрать из сохраненных списков',
                    'type' => 'select_by_query',
                    'primary_key_name' => 'memorylist_id',
                    'primary_key_table' => 'memorylist',
                    'value' => '0',
                    'value_string' => '',
                    'required' => 'on',
                    'query' => 'select * from '.DB_PREFIX.'_memorylist',
                    'title_default' => 'Выбрать из сохраненных списков',
                    'value_default' => 0,
                    'value_name' => 'title',
                    'parameters' => array(
                        'only_owner_access' => 1
                    )
                ),
            ),
        );
    }

}
