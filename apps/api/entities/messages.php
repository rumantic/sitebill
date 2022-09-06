<?php
namespace api\entities;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * messages object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class messages extends \Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'messages';
        $this->action = 'messages';
        $this->primary_key = 'message_id';
        $this->create_or_update_table();
    }

    function create_or_update_table()
    {
        parent::create_or_update_table();
        $this->create_unique_index($this->table_name, 'id');
    }

    function get_message_count_by_client_id ($client_id) {
        if ( defined('API_MODE') and API_MODE ) {
            $result = Capsule::table($this->table_name)
                ->selectRaw(
                    'count(message_id) as message_count'
                )
                ->where('client_id', '=', $client_id)
                ->first();
            return $result->message_count;
        }
        return 0;
    }

    function get_model () {
        return array(
            $this->table_name => array(
                'message_id' => array(
                    'name' => 'message_id',
                    'title' => 'message_id',
                    'type' => \system\types\model\Dictionary::PRIMARY_KEY,
                ),
                'id' => array(
                    'name' => 'id',
                    'title' => 'id',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'on',
                ),
                'chatId' => array(
                    'name' => 'chatId',
                    'title' => 'chatId',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'on',
                ),
                'file_name' => array(
                    'name' => 'file_name',
                    'title' => 'Файл',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'off',
                ),
                'content' => array(
                    'name' => 'content',
                    'title' => 'Сообщение',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::TEXTAREA,
                    'required' => 'off',
                ),
                'isMedia' => array(
                    'name' => 'isMedia',
                    'title' => 'isMedia',
                    'value' => 0,
                    'type' => \system\types\model\Dictionary::CHECKBOX,
                    'required' => 'off',
                ),
                'created_at' => array(
                    'name' => 'created_at',
                    'title' => 'Дата',
                    'value' => 'now',
                    'type' => \system\types\model\Dictionary::DTDATETIME,
                    'required' => 'off',
                ),
            )
                + \system\factories\model\Item::select_by_query(
                    'client_id',
                    'Клиент',
                    'client_id',
                    'client',
                    'select client_id from '.DB_PREFIX.'_client',
                    'client_id'
                )
                + \system\factories\model\Item::select_by_query(
                    'data_id',
                    'Объект',
                    'id',
                    'data',
                    'select id from '.DB_PREFIX.'_data',
                    'id'
                )

        );
    }

    protected function _after_add_done_action($form_data) {
        if ( $form_data[$this->table_name]['client_id']['value'] > 0 and $form_data[$this->table_name]['data_id']['value'] > 0) {
            $deal_object = $this->get_api_common()->init_custom_model_object('deal');
            $deal_data = $deal_object->data_model;
            $deal_data['deal']['status_id']['value'] = 1;
            $deal_data['deal']['message_id']['value'] = $form_data[$this->table_name]['message_id']['value'];
            $deal_data['deal']['created_at']['value'] = date('Y-m-d H:i:s');
            $deal_object->add_data($deal_data['deal']);
            if ( $deal_object->getError() ) {
                $this->riseError($deal_object->getError());
            }
        }
        return $form_data;
    }

}
