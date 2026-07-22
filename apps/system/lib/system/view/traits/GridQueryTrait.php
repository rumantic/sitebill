<?php
/**
 * GridQueryTrait — Query building and tag filtering for Common_Grid.
 *
 * Manages: construct_query, parse_id_values_from_model, add_tags_params,
 * add_tagged_parms_to_where.
 */
trait GridQueryTrait
{
    function construct_query()
    {
        $pager_params = $this->pager_params;
        foreach ($pager_params as $key => $value) {
            if ($key != 'per_page') {
                if ($key == 'page') {
                    //$sort_params[]='page=1';
                } else {
                    $sort_params[] = $key . '=' . $value;
                }
            }
        }
        $sort_params[] = 'page=1';


        $sortby = $this->getRequestValue('_sortby');
        if ($sortby == '') {
            $sortby = $this->grid_object->primary_key;
        }
        $sortdir = $this->getRequestValue('_sortdir');
        if ($sortdir == '') {
            $sortdir = 'DESC';
        }
        $pager_params['_sortby'] = $sortby;
        $pager_params['_sortdir'] = $sortdir;

        $where = array();
        if (!empty($this->conditions)) {
            foreach ($this->conditions as $key => $value) {
                $where[] = '(`' . $key . '`=\'' . $value . '\')';
                $sort_params[] = $key . '=' . $value;
            }
            $pager_params = array_merge($pager_params, $this->conditions);
        }

        $tagged_params = $this->add_tags_params();
        $where = $this->add_tagged_parms_to_where($where, $tagged_params, $this->grid_object->table_name);

        //$sort_params=array_merge($sort_params, $this->conditions);
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . (!empty($where) ? ' WHERE ' . implode('AND', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ';
        $this->set_grid_query($query);
    }

    function parse_id_values_from_model($column_name, $column_values, $data_model)
    {
        if ($data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_by_query') {
            foreach ($column_values as $idx => $value) {
                $langpostfix = $this->getLangPostfix($this->getCurrentLang());
                $namefield = $data_model[$this->grid_object->table_name][$column_name]['value_name'];
                $namefield = $namefield . $langpostfix;

                $val = $this->data_model_controller->get_value_id_by_name(
                    $data_model[$this->grid_object->table_name][$column_name]['primary_key_table'],
                    $namefield,
                    $data_model[$this->grid_object->table_name][$column_name]['primary_key_name'],
                    $value
                );

                if (0 != (int)$val) {
                    $column_values[$idx] = $val;
                } else {
                    unset($column_values[$idx]);
                }
            }
        } elseif ($data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_box' and count($column_values) > 0) {
            $select_data = array_flip($data_model[$this->grid_object->table_name][$column_name]['select_data']);
            $ra = array();
            foreach ($column_values as $idx => $value) {
                if ($select_data[$value]) {
                    $ra[] = $select_data[$value];
                }
            }
            return $ra;
        } elseif ($data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_box_structure' and count($column_values) > 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure = new Structure_Manager();
            $x = $Structure->createCatalogChains();
            $categoryChain = $x['txt'];
            $categoryChainRev = array_flip($categoryChain);
            foreach ($column_values as $idx => $value) {
                $value_array = explode(' / ', $value);
                $var = implode("|", $value_array);
                $var = mb_strtolower($var);
                if (isset($categoryChainRev[$var])) {
                    $column_values[$idx] = $categoryChainRev[$var];
                } else {
                    unset($column_values[$idx]);
                }
            }
        }
        return $column_values;
    }

    function add_tags_params($params = array())
    {

        if (isset($_SESSION['model_tags']) && is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'])) {
            foreach ($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'] as $column_name => $column_values) {
                $model = $this->grid_object->data_model[$this->grid_object->table_name];

                $column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->grid_object->data_model);
                if ($model[$column_name]['type'] == 'select_by_query_multi') {
                    $pkname = '';
                    foreach ($model as $k => $v) {
                        if ($v['type'] == 'primary_key') {
                            $pkname = $k;
                            break;
                        }
                    }
                    unset($params[$column_name]);
                    $params[$pkname] = $column_values;
                    //$params['id'] = $column_values;
                } elseif (isset($params[$column_name]) and !is_array($params[$column_name])) {
                    if ($params[$column_name] != 0) {
                        array_push($column_values, $params[$column_name]);
                    }
                    $params[$column_name] = $column_values;
                } elseif (isset($params[$column_name]) and is_array($params[$column_name])) {
                    $params[$column_name] = array_merge($params[$column_name], $column_values);
                } elseif (is_array($column_values)) {
                    $params[$column_name] = $column_values;
                }
            }
        }
        return $params;
    }

    function add_tagged_parms_to_where($where_array, $tagged_params, $table_name)
    {
        foreach ($tagged_params as $column_name => $column_values) {
            if (is_array($column_values) && count($column_values) > 0) {
                //$column_values=array_filter($column_values, function($a){if($a!=''){return $a;}});
                if (!empty($column_values)) {
                    $type = $this->grid_object->data_model[$table_name][$column_name]['type'];
                    if (isset($column_values['min']) || isset($column_values['max'])) {
                        if (isset($column_values['min'])) {
                            $where_array[] = "(" . DB_PREFIX . "_" . $table_name . "." . $column_name . " >= '" . $column_values['min'] . "')";
                        }
                        if (isset($column_values['max'])) {
                            $where_array[] = "(" . DB_PREFIX . "_" . $table_name . "." . $column_name . " <= '" . $column_values['max'] . "')";
                        }
                    } elseif ($type == 'client_id') {
                        $where_fio_phone_array = array();
                        foreach ($column_values as $fio_phone) {
                            list($fio, $phone) = explode(',', $fio_phone);
                            $fio = trim($fio);
                            $phone = trim($phone);
                            $where_fio_phone_array[] = " client_id in (select client_id from " . DB_PREFIX . "_client where fio='$fio' and phone='$phone') ";
                        }

                        $where_array[] = implode(' or ', $where_fio_phone_array);
                    } else {
                        $where_array[] = "(" . DB_PREFIX . "_" . $table_name . "." . $column_name . " IN ('" . implode('\',\'', $column_values) . "'))";
                    }
                }
            }
        }
        return $where_array;
    }
}
