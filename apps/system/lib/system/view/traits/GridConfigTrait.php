<?php
/**
 * GridConfigTrait — Configuration setters/getters for Common_Grid.
 *
 * Manages: grid items, controls, action, table name, batch/mass-delete,
 * conditions, pager params, render user ID, total count.
 */
trait GridConfigTrait
{
    function setBatchUpdateUrl($batch_update_url)
    {
        $this->batchUpdateUrl = $batch_update_url;
    }

    function setMAssDeleteUrl($mass_delete_url)
    {
        $this->massDeleteUrl = $mass_delete_url;
    }

    /**
     * Add grid item
     * @param string $name
     * @return void
     */
    function add_grid_item($name, $item_render_object = false)
    {
        array_push($this->grid_items, $name);
        if ($item_render_object != false) {
            $this->grid_items_render_objects[$name] = $item_render_object;
        }
    }

    /**
     * Set action name
     */
    function set_action($action = '')
    {
        $this->action = $action;
    }

    /**
     * Get action name
     */
    function get_action()
    {
        return $this->action;
    }

    /**
     * Set table name
     */
    function set_table_name($table_name = '')
    {
        $this->table_name = $table_name;
    }

    /**
     * Get table name
     */
    function get_table_name()
    {
        return $this->table_name;
    }

    public function enableBatchUpdate()
    {
        $this->batchUpdate = true;
    }

    public function enableMassDelete()
    {
        $this->massDelete = true;
    }

    public function enableBatchActivate()
    {
        $this->batchActivate = true;
    }

    /**
     * Add grid control
     * @param string $name
     * @return void
     */
    function add_grid_control($name)
    {
        array_push($this->grid_controls, $name);
    }

    function add_control_param($name, $value)
    {
        $this->controls_params[$name] = $value;
    }

    function set_grid_url($url)
    {
        $this->grid_url = $url;
    }

    /**
     * Set SQL-query for load records
     * @param string $query
     * @return void
     */
    function set_grid_query($query)
    {
        $this->grid_query = $query;
    }

    function set_grid_table($table)
    {
        $this->grid_table = $table;
    }

    function set_conditions($conditions)
    {
        $this->conditions = $conditions;
    }

    function set_conditions_sql($conditions)
    {
        $this->conditions_sql = $conditions;
    }

    function set_conditions_left_join($conditions)
    {
        $this->conditions_left_join = $conditions;
    }

    function get_grid_query()
    {
        return $this->grid_query;
    }

    function set_render_user_id($user_id)
    {
        $this->render_user_id = $user_id;
    }

    function get_render_user_id()
    {
        return $this->render_user_id;
    }

    function set_total_count($total_count)
    {
        $this->total_count = $total_count;
    }

    function get_total_count()
    {
        return $this->total_count;
    }

    function setPagerParams($params = array())
    {
        if (isset($params['per_page']) and ($params['per_page'] != 0)) {
            $this->per_page = (int)$params['per_page'];
        } else {
            $this->per_page = 10;
        }

        if (isset($params['page']) and ($params['page'] != 0)) {
            $this->current_page = (int)$params['page'];
        } else {
            $this->current_page = 1;
        }

        unset($params['per_page']);
        unset($params['page']);

        $this->pager_params = $params;
    }
}
