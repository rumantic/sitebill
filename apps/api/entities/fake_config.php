<?php
namespace api\entities;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * fake_config object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class fake_config extends \Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'config';
        $this->action = 'fake_config';
        $this->primary_key = 'id';
        $this->create_or_update_table();
    }

    function create_or_update_table()
    {
        parent::create_or_update_table();
    }

    function get_model () {
        return array(
            $this->table_name => array(
                    'id' => array(
                        'name' => 'id',
                        'title' => 'id',
                        'type' => \system\types\model\Dictionary::PRIMARY_KEY,
                    ),
                    'config_key' => array(
                        'name' => 'config_key',
                        'title' => 'config_key',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::SAFE_STRING,
                        'required' => 'on',
                    ),
                    'value' => array(
                        'name' => 'value',
                        'title' => 'value',
                        'value' => '',
                        'type' => \system\types\model\Dictionary::UPLOADS,
                        'required' => 'on',
                    ),
                )
        );
    }

    function appendUploads($table, $field, $pk_field, $record_id, $name_template = '') {
        $record_id = $this->get_native_config_record_id($this->request()->get('image_field'));
        $field = array(
            'name' => 'value',
            'title' => 'value',
            'value' => '',
            'uploadify_field_name' => $this->request()->get('image_field'),
            'type' => \system\types\model\Dictionary::UPLOADS,
            'required' => 'off',
        );
        $table = 'config';
        return parent::appendUploads($table, $field, $pk_field, $record_id, $name_template);
    }

    function get_native_config_record_id ( $config_key ) {
        $result = Capsule::table('config')
            ->selectRaw(
                'id'
            )
            ->where('config_key', '=', $config_key)
            ->first();
        if ( $result ) {
            return $result->id;
        }
        return 0;
    }
}
