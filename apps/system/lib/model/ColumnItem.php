<?php
namespace system\lib\model;


class ColumnItem
{

    private $_data;

    public function __construct($item )
    {
        $this->_data = $item;
    }

    public function get_name() {
        return $this->_data['name'];
    }

    public function get_type() {
        return $this->_data['type'];
    }

    public function get_title() {
        return $this->_data['title'];
    }

    public function get_value() {
        return $this->_data['value'];
    }

    public function get_primary_key_name() {
        return $this->_data['primary_key_name'];
    }
    public function get_primary_key_table() {
        return $this->_data['primary_key_table'];
    }
    public function get_value_string() {
        return $this->_data['value_string'];
    }
    public function get_query() {
        return $this->_data['query'];
    }
    public function get_value_name() {
        return $this->_data['value_name'];
    }
    public function get_title_default() {
        return $this->_data['title_default'];
    }
    public function get_value_default() {
        return $this->_data['value_default'];
    }
    public function get_value_table() {
        return $this->_data['value_table'];
    }
    public function get_value_primary_key() {
        return $this->_data['value_primary_key'];
    }
    public function get_value_field() {
        return $this->_data['value_field'];
    }
    public function get_assign_to() {
        return $this->_data['assign_to'];
    }
    public function get_dbtype() {
        return $this->_data['dbtype'];
    }
    public function get_table_name() {
        return $this->_data['table_name'];
    }
    public function get_primary_key() {
        return $this->_data['primary_key'];
    }
    public function get_primary_key_value() {
        return $this->_data['primary_key_value'];
    }
    public function get_action() {
        return $this->_data['action'];
    }
    public function get_tab() {
        return $this->_data['tab'];
    }
    public function get_hint() {
        return $this->_data['hint'];
    }
    public function get_active_in_topic() {
        return $this->_data['active_in_topic'];
    }
    public function get_entity() {
        return $this->_data['entity'];
    }
    public function get_group_id() {
        return $this->_data['group_id'];
    }
    public function get_combo() {
        return $this->_data['combo'];
    }
    public function get_parameters() {
        return $this->_data['parameters'];
    }
    public function get_required() {
        return $this->_data['required'];
    }
    public function get_unique() {
        return $this->_data['unique'];
    }
    public function get_select_data() {
        return $this->_data['select_data'];
    }
    public function get_select_data_reverse() {
        return $this->_data['select_data_reverse'];
    }
}
