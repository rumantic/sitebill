<?php
namespace api\entities;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * messages client report object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class messages_client_report extends \Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'messages_client_report';
        $this->action = 'messages_client_report';
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

        create view re_messages_client_report as
        SELECT
            m.message_id, m.client_id, m.data_id, m.created_at, deal.deal_id, deal.status_id,
            concat(city.name, ', ', street.name, ' ', d.number) as address, d.square_all, d.cost_meter_per_month4rent, comment.comment_text
        FROM
            `re_data` d
                LEFT JOIN `re_street` street ON street.street_id=d.street_id
                LEFT JOIN `re_city` city ON city.city_id=d.city_id,
            `re_messages` m LEFT JOIN `re_deal` deal ON deal.message_id=m.message_id
        LEFT JOIN (select * from re_comment order by comment_id desc LIMIT 999999999999999) as comment ON comment.object_type='deal' AND comment.object_id=deal.deal_id
        WHERE d.id=m.data_id
        GROUP by m.data_id, m.client_id
        ORDER BY m.created_at DESC

        // Если view без subquery
        create view re_comment_view as
        select * from re_comment order by comment_id desc LIMIT 999999999999999

        create view re_messages_client_report as
        SELECT
            m.message_id, m.client_id, m.data_id, m.created_at, deal.deal_id, deal.status_id,
            concat(city.name, ', ', street.name, ' ', d.number) as address, d.square_all, d.cost_meter_per_month4rent, comment.comment_text
        FROM
            `re_data` d
                LEFT JOIN `re_street` street ON street.street_id=d.street_id
                LEFT JOIN `re_city` city ON city.city_id=d.city_id,
            `re_messages` m LEFT JOIN `re_deal` deal ON deal.message_id=m.message_id
        LEFT JOIN re_comment_view as comment ON comment.object_type='deal' AND comment.object_id=deal.deal_id
        WHERE d.id=m.data_id
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
                    'address' => array(
                        'name' => 'address',
                        'title' => 'Адрес',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                        'required' => 'off',
                        'parameters' => [
                            'messenger' => '1',
                            'report_type' => 'data'
                        ]
                    ),
                    'square_all' => array(
                        'name' => 'square_all',
                        'title' => 'Площадь',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                        'required' => 'off',
                    ),
                    'cost_meter_per_month4rent' => array(
                        'name' => 'cost_meter_per_month4rent',
                        'title' => 'Ставка аренды',
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
