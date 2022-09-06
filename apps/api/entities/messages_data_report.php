<?php
namespace api\entities;

/**
 * messages data report object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class messages_data_report extends \Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'messages_data_report';
        $this->action = 'messages_data_report';
        $this->primary_key = 'message_id';
        $this->create_or_update_table();
    }

    function create_or_update_table()
    {
        parent::create_or_update_table();
    }

    function create_view () {
        /*
         *
         *

        create view re_messages_data_report as
        SELECT
            m.message_id, m.client_id, m.data_id, m.created_at, deal.deal_id, deal.status_id,
            c.company, concat(c.first_name, ' ', c.first_name) as fio, c.phone, comment.comment_text
        FROM
            `re_client` c,
            `re_messages` m LEFT JOIN `re_deal` deal ON deal.message_id=m.message_id
        LEFT JOIN (select * from re_comment order by comment_id desc LIMIT 999999999999999) as comment ON comment.object_type='deal' AND comment.object_id=deal.deal_id
        WHERE c.client_id=m.client_id
        GROUP by m.data_id, m.client_id
        ORDER BY m.created_at DESC

        // Если view без subquery
        create view re_comment_view as
        select * from re_comment order by comment_id desc LIMIT 999999999999999

        create view re_messages_data_report as
        SELECT
            m.message_id, m.client_id, m.data_id, m.created_at, deal.deal_id, deal.status_id,
            c.company, concat(c.first_name, ' ', c.first_name) as fio, c.phone, comment.comment_text
        FROM
            `re_client` c,
            `re_messages` m LEFT JOIN `re_deal` deal ON deal.message_id=m.message_id
        LEFT JOIN re_comment_view as comment ON comment.object_type='deal' AND comment.object_id=deal.deal_id
        WHERE c.client_id=m.client_id
        GROUP by m.data_id, m.client_id
        ORDER BY m.created_at DESC


         *
         *
         */
    }

    function get_model () {
        return array(
            $this->table_name => array(
                    'message_id' => array(
                        'name' => 'message_id',
                        'title' => 'id',
                        'type' => \system\types\model\Dictionary::PRIMARY_KEY,
                    ),
                    'client_id' => array(
                        'name' => 'client_id',
                        'title' => 'client_id',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                    ),
                    'data_id' => array(
                        'name' => 'data_id',
                        'title' => 'data_id',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                    ),
                    'company' => array(
                        'name' => 'company',
                        'title' => 'Компания',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                        'required' => 'off',
                    ),
                    'fio' => array(
                        'name' => 'fio',
                        'title' => 'Контактное лицо',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                        'required' => 'off',
                    ),
                    'phone' => array(
                        'name' => 'phone',
                        'title' => 'Телефон',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                        'required' => 'off',
                    ),
                    'created_at' => array(
                        'name' => 'created_at',
                        'title' => 'Дата отправки',
                        'type' => \system\types\model\Dictionary::DTDATETIME,
                    ),
                    'comment_text' => array(
                        'name' => 'comment_text',
                        'title' => 'Комментарий',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                    ),
                )
                + \system\factories\model\Item::select_by_query(
                    'status_id',
                    'Статус',
                    'id',
                    'deal_statuses',
                    'select * from '.DB_PREFIX.'_deal_statuses',
                    'name'
                )
                + \system\factories\model\Item::select_by_query(
                    'deal_id',
                    'Сделка',
                    'deal_id',
                    'deal',
                    'select * from '.DB_PREFIX.'_deal',
                    'deal_id'
                )

        );
    }
}
