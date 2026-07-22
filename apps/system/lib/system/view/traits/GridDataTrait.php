<?php
/**
 * GridDataTrait — Data retrieval and transformation for Common_Grid.
 *
 * Manages: degradate_grid, construct_grid_array, getPager.
 */
trait GridDataTrait
{
    /**
     * Временная функция
     */
    function degradate_grid($grid)
    {

        foreach ($grid as $k => $row_data) {
            //$row_data = $this->grid_object->load_by_id($primary_key_value);
            $data = array();
            foreach ($row_data as $item_name => $v) {
                //$item_name=$item_id['name'];
                if ($row_data[$item_name]['type'] == 'select_by_query' || $row_data[$item_name]['type'] == 'select_by_query_multi') {
                    $data[$item_name]['value'] = $row_data[$item_name]['value'];
                    $data[$item_name]['value_string'] = $row_data[$item_name]['value_string'];
                } elseif ($row_data[$item_name]['type'] == 'date') {
                    $data[$item_name] = $row_data[$item_name]['value'];
                } elseif ($row_data[$item_name]['type'] == 'uploadify_image') {
                    $data['image_array'] = $row_data[$item_name]['image_array'];
                } elseif ($row_data[$item_name]['type'] == 'select_box') {
                    if ($row_data[$item_name]['parameters']['multiselect'] == 1) {
                        $data[$item_name]['value'] = $row_data[$item_name]['value'];
                        $data[$item_name]['value_string'] = $row_data[$item_name]['value_string'];
                    } else {
                        $data[$item_name]['value'] = $row_data[$item_name]['value'];
                        $data[$item_name]['value_string'] = $row_data[$item_name]['select_data'][$row_data[$item_name]['value']];
                    }
                } elseif ($row_data[$item_name]['type'] == 'geodata') {
                    $data[$item_name] = implode(',', $row_data[$item_name]['value']);
                } else {
                    $data[$item_name] = $row_data[$item_name]['value'];
                }
            }
            $ret[$k] = $data;
        }
        return $ret;
    }

    function construct_grid_array()
    {
        $ra = array();
        $DBC = DBC::getInstance();

        $query = $this->grid_query . ' LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page;

        $stmt = $DBC->query($query);

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar[$this->grid_object->primary_key]] = $ar;
            }
        } else {
            $this->writeLog(__METHOD__ . ', query = ' . $query . ', query error = ' . $DBC->getLastError());
        }

        $ret = array();
        $ids = array();
        foreach ($ra as $primary_key_value => $item_array) {
            $ids[$primary_key_value] = $primary_key_value;
        }
        if (!empty($ids)) {
            $row_datas = $this->grid_object->load_by_id($ids);
        }

        if (!empty($row_datas)) {
            foreach ($row_datas as $primary_key_value => $item_array) {
                $data = array();
                foreach ($this->grid_items as $item_id => $item_name) {
                    $data[$item_name] = $item_array[$item_name];
                }
                $ret[] = $data;
            }
        }
        //print_r($ret);
        return $ret;
    }

    function getPager()
    {
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($this->grid_query);
        $total = 0;
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $total++;
            }
        }

        $ret = $this->get_page_links_list($this->current_page, $total, $this->per_page, $this->pager_params);
        return $ret;
    }
}
