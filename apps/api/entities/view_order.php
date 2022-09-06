<?php
namespace api\entities;

use system\traits\PermissionsTrait;

/**
 * view_order object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class view_order extends \Object_Manager {
    use PermissionsTrait;
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'view_order';
        $this->action = 'view_order';
        $this->app_title = 'Заявки на просмотр';
        $this->primary_key = 'id';
        $this->create_or_update_table();
        if ( method_exists($this, 'create_custom_entity') ) {
            if ( $this->create_custom_entity($this->app_title) ) {

            }
        }
        $this->enableNobodyAccess();
    }

    function get_default_grid_items () {
        return array($this->primary_key, 'fio', 'email','phone','object_href', 'created_at');
    }

    function get_model () {
        return array(
            $this->table_name => array(
                'id' => array(
                    'name' => 'id',
                    'title' => 'id',
                    'type' => \system\types\model\Dictionary::PRIMARY_KEY,
                ),
                'fio' => array(
                    'name' => 'fio',
                    'title' => 'ФИО',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'on',
                ),
                'email' => array(
                    'name' => 'email',
                    'title' => 'Email',
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
                'object_href' => array(
                    'name' => 'object_href',
                    'title' => 'Ссылка на объект',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::SAFE_STRING,
                    'required' => 'off',
                    'parameters' => [
                        'gcomposed' => '1',
                        'function' => 'def_link'
                    ]
                ),
                'created_at' => array(
                    'name' => 'created_at',
                    'title' => 'Дата',
                    'value' => '',
                    'type' => \system\types\model\Dictionary::DTDATETIME,
                    'required' => 'off',
                ),
            )
        );
    }
}
