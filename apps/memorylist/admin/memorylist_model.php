<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class memorylist_model extends Data_Model {

    public function __construct(){
        parent::__construct();
    }

    public function get_model(){

        $form_data = array(
            'memorylist' => array (
                'memorylist_id' => array (
                    'name' => 'memorylist_id',
                    'title' => 'memorylist_id',
                    'type' => 'primary_key',
                    'value' => '0',
                    'required' => 'off',
                ),
                'user_id' => array (
                    'name' => 'user_id',
                    'title' => 'Владелец',
                    'type' => 'select_by_query',
                    'primary_key_name' => 'user_id',
                    'primary_key_table' => 'user',
                    'value' => '0',
                    'value_string' => '',
                    'required' => 'on',
                    'query' => 'select * from '.DB_PREFIX.'_user order by fio',
                    'title_default' => 'выбрать пользователя',
                    'value_default' => 0,
                    'value_name' => 'fio',
                ),
                'title' => array (
                    'name' => 'title',
                    'title' => 'Название списка',
                    'type' => 'safe_string',
                    'value' => '',
                    'required' => 'on',
                ),
                'domain' => array (
                    'name' => 'domain',
                    'title' => 'Домен',
                    'type' => 'safe_string',
                    'value' => '',
                    'required' => 'on',
                ),
                'deal_id' => array (
                    'name' => 'deal_id',
                    'title' => 'ИД сделки',
                    'type' => 'safe_string',
                    'value' => '',
                    'required' => 'on',
                ),
            )
        );

        return $form_data;
    }
}
