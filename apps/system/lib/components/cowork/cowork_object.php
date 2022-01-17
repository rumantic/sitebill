<?php
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Cowork object
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Cowork_Object extends Object_Manager {
    /**
     * @var Array
     */
    private $cache;

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'cowork';
        $this->action = 'cowork';
        $this->primary_key = 'cowork_id';

        $this->data_model = $this->get_model();
    }

    function check_cowork_record ($object_type, $object_id, $coworker_id) {
        if ( isset($this->cache[$object_type][$object_id][$coworker_id])  ) {
            return 1;
        }
        $result = Capsule::table($this->table_name)
            ->selectRaw(
                'cowork_id'
            )
            ->where('object_type', '=', $object_type)
            ->where('id', '=', $object_id)
            ->where('coworker_id', '=', $coworker_id)
            ->first();
        if ( $result->cowork_id ) {
            $this->cache[$object_type][$object_id][$coworker_id] = true;
            return 1;
        }
        return 0;
    }

    function get_model () {
        $model = array(
            'cowork' => array(
                'cowork_id' => array(
                    'name' => 'cowork_id',
                    'title' => 'cowork_id',
                    'type' => 'primary_key',
                ),
                'id' => array(
                    'name' => 'id',
                    'title' => 'id',
                    'value' => 0,
                    'type' => 'hidden',
                    'required' => 'on',
                ),
                'object_type' => array(
                    'name' => 'object_type',
                    'title' => 'object_type',
                    'value' => '',
                    'type' => 'hidden',
                    'required' => 'on',
                ),
                'coworker_id' => array(
                    'name' => 'coworker_id',
                    'title' => _e('Ко-воркер'),
                    'value' => 0,
                    'type' => 'select_by_query',
                    'primary_key_name' => 'user_id',
                    'primary_key_table' => 'user',
                    'query' => 'select user_id, fio from ' . DB_PREFIX . '_user order by fio',
                    'value_name' => 'fio',
                    'value_default' => 0,
                    'title_default' => _e('выбрать ко-воркера'),
                    'required' => 'on',
                ),
            ),
        );
        return $model;
    }
}
