<?php
namespace system\traits\admin;

trait NestedEntity {
    /**
     * Constructor
     */
    function __construct($parent_action, $action, $primary_key = 'id') {
        parent::__construct();
        $this->table_name = $action;
        $this->action = $parent_action.':'.$action;
        parent::set_mod($action);
        $this->primary_key = $primary_key;
        $this->create_or_update_table();
    }

    function getDoButtons () {
        return [
            [
                'do' => 'new',
                'title' => _e('Добавить запись')
            ]
        ];
    }
}
