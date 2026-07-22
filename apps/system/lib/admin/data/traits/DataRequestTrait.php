<?php
/**
 * DataRequestTrait — Request parameter gathering and tag processing for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: gatherRequestParams(), add_tags_params(), parse_id_values_from_model(),
 *          add_tagged_parms_to_where()
 */
trait DataRequestTrait
{
    function gatherRequestParams() {
        $params = array();
        $var = $this->getRequestValue('user_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['user_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['user_id'] = $var;
            }
        }

        $var = $this->getRequestValue('topic_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['topic_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['topic_id'] = $var;
            }
        }

        $var = $this->getRequestValue('country_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['country_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['country_id'] = $var;
            }
        }

        $var = $this->getRequestValue('region_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['region_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['region_id'] = $var;
            }
        }

        $var = $this->getRequestValue('city_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['city_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['city_id'] = $var;
            }
        }

        $var = $this->getRequestValue('district_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['district_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['district_id'] = $var;
            }
        }

        $var = $this->getRequestValue('metro_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['metro_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['metro_id'] = $var;
            }
        }

        $var = $this->getRequestValue('street_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['street_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['street_id'] = $var;
            }
        }

        $var = intval($this->getRequestValue('page'));
        if ($var > 0) {
            $params['page'] = $var;
        }

        $var = trim($this->getRequestValue('order'));
        if ($var != '') {
            $params['order'] = $var;
        }

        $var = trim($this->getRequestValue('asc'));
        if ($var != '') {
            $params['asc'] = $var;
        }

        $var = trim($this->getRequestValue('active'));
        if ($var != '') {
            $params['active'] = $var;
        }

        $var = intval($this->getRequestValue('hot'));
        if ($var > 0) {
            $params['hot'] = $var;
        }

        $var = $this->getRequestValue('id');
        if (!is_array($var) && intval($var) > 0) {
            $params['id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['id'] = $var;
            }
        }

        $var = intval($this->getRequestValue('status_id'));
        if ($var > 0) {
            $params['status_id'] = $var;
        }

        $var = intval($this->getRequestValue('client_id'));
        if ($var > 0) {
            $params['client_id'] = $var;
        }

        $var = intval($this->getRequestValue('archived'));
        if ($var > 0) {
            $params['archived'] = $var;
        }

        $params['price'] = $this->getRequestValue('price');

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $var = intval($this->getRequestValue('vip_status'));
            if ($var > 0) {
                $params['vip_status'] = $var;
            }
            $var = intval($this->getRequestValue('premium_status'));
            if ($var > 0) {
                $params['premium_status'] = $var;
            }
            $var = intval($this->getRequestValue('bold_status'));
            if ($var > 0) {
                $params['bold_status'] = $var;
            }
        }



        if (isset($this->data_model[$this->table_name]['uniq_id'])) {
            $var = intval($this->getRequestValue('uniq_id'));
            if ($var > 0) {
                $params['uniq_id'] = $var;
            }
            //$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
            //$smarty->assign('show_uniq_id', 'true');
        }
        if ($this->getRequestValue('srch_export_cian') == 'on' || $this->getRequestValue('srch_export_cian') == '1') {
            $var = intval($this->getRequestValue('srch_export_cian'));
            if ($var > 0) {
                $params['srch_export_cian'] = 1;
            }
            //$params['srch_export_cian'] = 1;
        }

        $var = $this->getRequestValue('srch_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['id'] = $var;
            }
        }

        $var = trim($this->getRequestValue('srch_word'));
        if ($var != '') {
            $params['srch_word'] = $var;
        }
        $var = trim($this->getRequestValue('srch_phone'));
        if ($var != '') {
            $params['srch_phone'] = $var;
        }
        $var = trim($this->getRequestValue('srch_date_from'));
        if ($var != '') {
            $params['srch_date_from'] = $var;
        } else {
            $params['srch_date_from'] = 0;
        }
        $var = trim($this->getRequestValue('srch_date_to'));
        if ($var != '') {
            $params['srch_date_to'] = $var;
        } else {
            $params['srch_date_to'] = 0;
        }

        return $params;
    }

    function add_tags_params($params) {
        if (isset($_SESSION['tags_array']) && is_array($_SESSION['tags_array'])) {
            foreach ($_SESSION['tags_array'] as $column_name => $column_values) {
                $model = $this->get_model();
                $column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->get_model());
                if($model[$this->table_name][$column_name]['type'] === 'select_by_query_multi'){
                    $pkname = '';
                    foreach ($model[$this->table_name] as $k => $v){
                        if($v['type'] === 'primary_key'){
                            $pkname = $k;
                            break;
                        }
                    }
                    unset($params[$column_name]);
                    $params[$pkname] = $column_values;
                    //$params['id'] = $column_values;
                }elseif (isset($params[$column_name]) and ! is_array($params[$column_name])) {
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

    function parse_id_values_from_model($column_name, $column_values, $data_model) {
        if ($data_model[$this->table_name][$column_name]['type'] == 'select_by_query') {
            foreach ($column_values as $idx => $value) {

                $namefield = $data_model[$this->table_name][$column_name]['value_name'];
                $langpostfix = $this->getLangPostfix($this->getCurrentLang());
                if(1 == $this->getConfigValue('apps.language.use_langs')){
                    if(isset($data_model[$this->table_name][$column_name]['parameters']['no_ml']) && $data_model[$this->table_name][$column_name]['parameters']['no_ml'] == 1){

                    }else{
                        $namefield = $namefield.$langpostfix;
                    }
                }

                $val = $this->data_model_object->get_value_id_by_name($data_model[$this->table_name][$column_name]['primary_key_table'], $namefield, $data_model[$this->table_name][$column_name]['primary_key_name'], $value);

                if (0 != (int) $val) {
                    $column_values[$idx] = $val;
                } else {
                    unset($column_values[$idx]);
                }
            }
        } elseif($data_model[$this->table_name][$column_name]['type'] == 'select_by_query_multi') {
            foreach ($column_values as $idx => $value) {
                $val = $this->data_model_object->get_value_id_by_name($data_model[$this->table_name][$column_name]['primary_key_table'], $data_model[$this->table_name][$column_name]['value_name'], $data_model[$this->table_name][$column_name]['primary_key_name'], $value);

                if (0 != (int) $val) {
                    $column_values[$idx] = $val;
                } else {
                    unset($column_values[$idx]);
                }
            }
            if(!empty($column_values)){
                //$model_array[$key]['value'] = array();
                //$model_array[$key]['value_string'] = '';
                $DBC = DBC::getInstance();
                $query = 'SELECT DISTINCT `primary_id` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `field_value` IN ('.implode(',', array_fill(0, count($column_values), '?')).')';
                $stmt = $DBC->query($query, array_merge(array($this->table_name, $column_name), $column_values));

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ids[] = $ar['primary_id'];
                    }
                    $column_values = $ids;
                }else{
                    $column_values = array(-1);
                }

                //print_r($ids);
            }
        } elseif ($data_model[$this->table_name][$column_name]['type'] == 'select_box' and count($column_values) > 0) {
            $select_data = array_flip($data_model[$this->table_name][$column_name]['select_data']);
            $ra = array();
            foreach ($column_values as $idx => $value) {
                if ($select_data[$value]) {
                    $ra[] = $select_data[$value];
                }
            }
            return $ra;
        } elseif ($data_model[$this->table_name][$column_name]['type'] == 'select_box_structure' and count($column_values) > 0) {
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

    function add_tagged_parms_to_where($where_array, $tagged_params) {

        foreach ($tagged_params as $column_name => $column_values) {
            if (is_array($column_values) && count($column_values) > 0) {
                //$column_values=array_filter($column_values, function($a){if($a!=''){return $a;}});
                if (!empty($column_values)) {
                    $type = $this->data_model['data'][$column_name]['type'];
                    if ($type == 'dtdatetime') {
                        if(isset($column_values['min']) || isset($column_values['max'])){
                            if (isset($column_values['min'])) {
                                $where_array[] = "(re_data.`" . $column_name . "` >= '" . preg_replace('/[^0-9\-]/', '', $column_values['min']) . " 00:00:00')";
                            }
                            if (isset($column_values['max'])) {
                                $where_array[] = "(re_data.`" . $column_name . "` <= '" . preg_replace('/[^0-9\-]/', '', $column_values['max']) . " 23:59:59')";
                            }
                        } /*elseif($column_values == 'today') {
                            $where_array[] = "(re_data.`" . $column_name . "` >= '" . date('Y-m-d 00:00:00') . "')";
                            $where_array[] = "(re_data.`" . $column_name . "` <= '" . date('Y-m-d 23:59:59') . " ')";
                        } elseif($column_values == 'yesterday') {
                            $where_array[] = "(re_data.`" . $column_name . "` >= '" . date('Y-m-d 00:00:00', (time() - 24*3600)) . "')";
                            $where_array[] = "(re_data.`" . $column_name . "` <= '" . date('Y-m-d 23:59:59', (time() - 24*3600)) . "')";
                        } elseif($column_values == 'thismonth') {
                            $where_array[] = "(re_data.`" . $column_name . "` >= '" . date('Y-m-d 00:00:00', (time() - 24*3600)) . "')";
                            $where_array[] = "(re_data.`" . $column_name . "` <= '" . date('Y-m-d 23:59:59', (time() - 24*3600)) . "')";
                        } */else {
                            $safe_values = array_map('addslashes', $column_values);
                            $where_array[] = "(re_data." . $column_name . " IN ('" . implode('\',\'', $safe_values) . "'))";
                        }
                    } elseif (isset($column_values['min']) || isset($column_values['max'])) {
                        if (isset($column_values['min'])) {
                            $where_array[] = "(re_data.`" . $column_name . "`*1 >= '" . (float)$column_values['min'] . "')";
                        }
                        if (isset($column_values['max'])) {
                            $where_array[] = "(re_data.`" . $column_name . "`*1 <= '" . (float)$column_values['max'] . "')";
                        }
                    } elseif ($type == 'client_id') {
                        $where_fio_phone_array = array();
                        foreach ($column_values as $fio_phone) {
                            list($fio, $phone) = explode(',', $fio_phone);
                            $fio = addslashes(trim($fio));
                            $phone = addslashes(trim($phone));
                            $where_fio_phone_array[] = ' client_id IN (SELECT client_id FROM ' . DB_PREFIX . '_client WHERE fio=\'' . $fio . '\' AND phone=\'' . $phone . '\') ';
                        }

                        $where_array[] = implode(' OR ', $where_fio_phone_array);
                    } else {
                        $safe_values = array_map('addslashes', $column_values);
                        $where_array[] = "(re_data." . $column_name . " IN ('" . implode('\',\'', $safe_values) . "'))";
                    }
                }
            }
        }
        return $where_array;
    }
}
