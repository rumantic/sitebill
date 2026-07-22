<?php
/**
 * DataInitTrait — extracted from Data_Model class (model.php)
 * Auto-generated, do not edit manually.
 */
trait DataInitTrait
{
    /**
     * Init model data from request
     * @param array $model_array
     * @return boolean
     */
    function init_model_data_from_request($model_array, $ignore_topic_activity = false, $no_insert = false)
    {
        $primary_key_value = 0;

        $topic_id = 0;
        $optype = 0;
        $DBC = DBC::getInstance();
        if (isset($model_array['topic_id'])) {
            $topic_id = (isset($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : 0);
        }
        if (isset($model_array['optype'])) {
            $optype = (isset($_REQUEST['optype']) ? intval($_REQUEST['optype']) : 0);
        }
        if ($topic_id != 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $SM = new Structure_Manager();
            $category_structure = $SM->loadCategoryStructure();
        }
        foreach ($model_array as $key => $item_array) {
            if ($item_array['type'] == 'primary_key') {
                $primary_key_value = $this->getRequestValue($item_array['name']);
                break;
            }
        }
        foreach ($model_array as $key => $item_array) {

            if (isset($model_array[$key]['parameters'])) {
                $parameters = $model_array[$key]['parameters'];
            } else {
                $parameters = array();
            }

            if (!$ignore_topic_activity) {
                if ($topic_id != 0 && isset($item_array['active_in_topic']) && $item_array['active_in_topic'] != 0) {
                    $active_array_ids = explode(',', $item_array['active_in_topic']);
                    $child_cats = $active_array_ids;
                    if (!in_array($topic_id, $child_cats)) {
                        unset($model_array[$key]);
                        continue;
                    }
                }
                if ($optype != 0 && isset($parameters['active_in_optype']) && $parameters['active_in_optype'] != '') {
                    $active_array_optype_ids = explode(',', $parameters['active_in_optype']);
                    if (!in_array($optype, $active_array_optype_ids)) {
                        unset($model_array[$key]);
                        continue;
                    }
                }
            }

            // --- FieldTypeHandler dispatch (Этап 3) ---
            $__fieldType = $model_array[$key]['type'] ?? '';
            if ($__fieldType !== '' && $__fieldType !== 'primary_key') {
                static $__fieldRegistry = null;
                static $__fieldContext = null;
                if ($__fieldRegistry === null) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/field/FieldTypeRegistry.php';
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/field/SiteBillFieldContext.php';
                    $__fieldRegistry = FieldTypeRegistry::getInstance();
                    $__fieldContext = new SiteBillFieldContext($this);
                }
                if ($__fieldRegistry->has($__fieldType)) {
                    $__handler = $__fieldRegistry->get($__fieldType);
                    if ($__handler->hydrateFromRequest($model_array[$key], $__fieldContext)) {
                        continue;
                    }
                }
            }
            // --- End FieldTypeHandler dispatch ---

            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'gadres') {
                $value = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($key)));
                $old_value = $this->getRequestValue('gadres');

                $old_value = strip_tags($this->htmlspecialchars_decode($old_value[$item_array['name']]));
                if ($value != '') {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
                    $GA = new geodata_admin();
                    $value = $GA->geocode_me($value);
                } else {
                    $value = $old_value;
                }

                $model_array[$key]['value'] = $value;
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'checkbox') {

                if (NULL !== $this->getRequestValue($key) && 0 !== intval($this->getRequestValue($key))) {
                    $model_array[$key]['value'] = 1;
                } else {
                    $model_array[$key]['value'] = 0;
                }
                continue;
            }

            if ($model_array[$key]['type'] == 'uploadify_image' or $model_array[$key]['type'] == 'uploadify_file') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
            }
            if ($model_array[$key]['type'] == 'uploads' || $model_array[$key]['type'] == 'docuploads') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                //$model_array[$key]['primary_key'] = $primary_key_name;
                //$model_array[$key]['table_name'] = $table_name;
            }


            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'avatar') {
                continue;
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                if (!isset($_FILES[$key]) || $_FILES[$key]['error'] != 0) {
                    unset($model_array[$key]);
                } elseif (!in_array($_FILES[$key]['type'], array('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png'))) {
                    unset($model_array[$key]);
                } else {
                    $fprts = explode('.', $_FILES[$key]['name']);
                    $ext = strtolower(end($fprts));
                    $name = md5(time() . rand(10, 99)) . '.' . $ext;

                    if (!move_uploaded_file($_FILES[$key]['tmp_name'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name)) {
                        unset($model_array[$key]);
                    } else {
                        $res = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name, 200, 200, $ext, 'smart');
                        if ($res !== false) {
                            $model_array[$key]['value'] = $name;
                        } else {
                            unset($model_array[$key]);
                        }
                    }
                }

                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_by_query') {


                if (isset($parameters['autocomplete']) && $parameters['autocomplete'] == 1) {
                    $_no_insert = $no_insert;
                    if (isset($parameters['autocomplete_notappend']) && 0 != (int)$parameters['autocomplete_notappend']) {
                        $_no_insert = true;
                    }

                    $filters = array();
                    $autocomplete_dep_el = (isset($parameters['autocomplete_dep_el']) ? $parameters['autocomplete_dep_el'] : '');
                    $autocomplete_dep_el_key = (isset($parameters['autocomplete_dep_el_key']) ? $parameters['autocomplete_dep_el_key'] : '');
                    if ($autocomplete_dep_el != '' && $autocomplete_dep_el_key != '' && isset($model_array[$autocomplete_dep_el])) {
                        $filters[$autocomplete_dep_el_key] = $this->getRequestValue($autocomplete_dep_el);
                    }

                    $id_value = (int)$this->getRequestValue($key);
                    $geoautocomplete_text_value = $this->getRequestValue('geoautocomplete');

                    $geoautocomplete_text_value[$key] = @trim(strip_tags($this->htmlspecialchars_decode($geoautocomplete_text_value[$key])));

                    if ($geoautocomplete_text_value[$key] != '') {
                        $name = $model_array[$key]['value_name'];
                        if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === intval($parameters['no_ml'])) {
                            $name .= $this->getLangPostfix($this->getCurrentLang());
                        }
                        $real_id = $this->get_value_id_by_name($item_array['primary_key_table'], $name, $item_array['primary_key_name'], $geoautocomplete_text_value[$key], $filters);
                        if ($real_id != 0) {
                            $id_value = $real_id;
                        } elseif ($_no_insert) {
                            $id_value = 0;
                        } else {

                            if ($autocomplete_dep_el != '' && $autocomplete_dep_el_key != '') {
                                $pid_value = intval($this->getRequestValue($autocomplete_dep_el));

                                $query = 'INSERT INTO ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' (`' . $item_array['value_name'] . '`, `' . $autocomplete_dep_el_key . '`) VALUES (?, ?)';
                                $stmt = $DBC->query($query, array($geoautocomplete_text_value[$key], $pid_value));
                            } else {
                                $query = 'INSERT INTO ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' (`' . $item_array['value_name'] . '`) VALUES (?)';
                                $stmt = $DBC->query($query, array($geoautocomplete_text_value[$key]));
                            }


                            if ($stmt) {
                                $id_value = $DBC->lastInsertId();
                            } else {
                                $id_value = 0;
                            }
                        }
                    } elseif ($id_value != 0) {

                    } else {
                        $id_value = 0;
                    }

                    $model_array[$key]['value'] = $id_value;
                    unset($_REQUEST['geoautocomplete'][$key]);
                } else {
                    $result = $this->getRequestValue($key);

                    if (is_array($result)) {
                        foreach ($result as $r1 => $r2) {
                            $result[$r1] = strip_tags($this->htmlspecialchars_decode($r2));
                        }
                        $model_array[$key]['value'] = $result;
                    } else {
                        if ($result !== NULL) {
                            $model_array[$key]['value'] = strip_tags($this->htmlspecialchars_decode($result));
                        }
                    }
                }
                if (!is_array($model_array[$key]['value'])) {
                    $model_array[$key]['value_string'] = $this->get_string_value_by_id($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $model_array[$key]['value_name'], $model_array[$key]['value'], false);
                }
                continue;
            }


            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'date') {
                if (isset($parameters['formattype']) && $parameters['formattype'] == 'date') {
                    $model_array[$key]['value'] = date('Y-m-d', strtotime($this->getRequestValue($key)));
                } elseif (isset($parameters['formattype']) && $parameters['formattype'] == 'datetime') {
                    $model_array[$key]['value'] = date('Y-m-d H:i:s', strtotime($this->getRequestValue($key)));
                } else {
                    $model_array[$key]['value'] = strtotime($this->getRequestValue($key));
                }
                /* echo $this->getRequestValue($key).'<br />';
                  echo strtotime($this->getRequestValue($key)).'<br />';
                  echo date('Y-m-d', strtotime($this->getRequestValue($key))).'<br />';
                  $model_array[$key]['value'] = strtotime($this->getRequestValue($key)); */
                // Как вариант использовать следующую строку для задания даты со временем, а не просто даты
                //$model_array[$key]['value'] = strtotime($this->getRequestValue($key).' '.date('H:i:s',time()));
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'datetime') {
                $model_array[$key]['value'] = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($key)));
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'dtdatetime') {
                $val = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($key)));
                if ($val == '' && $model_array[$key]['value'] == 'now') {
                    $val = date('Y-m-d H:i:s', time());
                } else {
                    if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $val)) {

                    } elseif (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $val)) {
                        $val .= ' 00:00:00';
                    } else {
                        $val = Sitebill_Datetime::getDatetimeCanonicalFromFormat($val);
                    }
                }
                //var_dump($val);
                $model_array[$key]['value'] = $val;
                continue;
            }

            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'dtdate') {
                $val = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($key)));
                if ($val == '' && $model_array[$key]['value'] == 'now') {
                    $val = date('Y-m-d 00:00:00', time());
                } else {
                    if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $val)) {
                        $val .= ' 00:00:00';
                    } else {
                        $val = Sitebill_Datetime::getDateCanonicalFromFormat($val);
                    }
                }
                $model_array[$key]['value'] = $val;
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'dttime') {
                $val = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($key)));
                if ($val == '' && $model_array[$key]['value'] == 'now') {
                    $val = date('0000-00-00 H:i:s', time());
                } else {
                    $val = Sitebill_Datetime::getTimeCanonicalFromFormat($val);
                }
                $model_array[$key]['value'] = $val;
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'parameter') {
                $params = array();
                $p = $this->htmlspecialchars_decode($this->getRequestValue($key));


                if (is_array($p) && isset($p['name']) && is_array($p['name']) && count($p['name']) > 0) {
                    foreach ($p['name'] as $k => $n) {
                        $paramname = trim($n);
                        $paramvalue = trim($p['value'][$k]);
                        if ($paramname != '') {
                            $params[$paramname] = $paramvalue;
                        }
                    }
                } elseif (is_array($p)) {
                    $params = $p;
                }
                $model_array[$key]['value'] = $params;
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'tlocation') {
                $model_array[$key]['value']['country_id'] = (int)$this->getRequestValue('country_id');
                $model_array[$key]['value']['region_id'] = (int)$this->getRequestValue('region_id');
                $model_array[$key]['value']['city_id'] = (int)$this->getRequestValue('city_id');
                $model_array[$key]['value']['district_id'] = (int)$this->getRequestValue('district_id');
                $model_array[$key]['value']['street_id'] = (int)$this->getRequestValue('street_id');
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'geodata') {
                $geodata = array();
                $geodata = $this->getRequestValue($model_array[$key]['name']);
                $model_array[$key]['value'] = array();
                if (!is_null($geodata)) {
                    if (isset($geodata['lat']) && preg_match('/^(-?)([0-9]?)([0-9])((\.?)(\d*)?)$/', trim($geodata['lat']))) {
                        $model_array[$key]['value']['lat'] = trim($geodata['lat']);
                    } else {
                        $model_array[$key]['value']['lat'] = '';
                    }
                    if (isset($geodata['lng']) && preg_match('/^(-?)([0-9]?)([0-9]?)([0-9])((\.?)(\d*)?)$/', trim($geodata['lng']))) {
                        $model_array[$key]['value']['lng'] = trim($geodata['lng']);
                    } else {
                        $model_array[$key]['value']['lng'] = '';
                    }
                } else {
                    $model_array[$key]['value']['lat'] = '';
                    $model_array[$key]['value']['lng'] = '';
                }

                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'price') {
                $model_array[$key]['value'] = preg_replace('/[^0-9.,]/', '', $this->getRequestValue($key));
                continue;
            }

            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'photo' && isset($_FILES[$model_array[$key]['name']])) {
                $model_array[$key]['value'] = $_FILES[$model_array[$key]['name']]['name'];
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_box_structure_simple_multiple') {
                $model_array[$key]['values_array'] = $this->getRequestValue($key);
                if (is_array($model_array[$key]['values_array']) && count($model_array[$key]['values_array']) != 0) {
                    $model_array[$key]['value'] = implode(',', $model_array[$key]['values_array']);
                }
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_by_query_multi') {
                $model_array[$key]['value'] = $this->getRequestValue($key);
                if (!is_array($model_array[$key]['value'])) {
                    $model_array[$key]['value'] = (array)$model_array[$key]['value'];
                }
                /* if(is_array($model_array[$key]['values_array']) && count($model_array[$key]['values_array'])!=0){
                  $model_array[$key]['value']=implode(',', $model_array[$key]['values_array']);
                  } */
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_box_structure_multiple_checkbox') {
                $model_array[$key]['values_array'] = $this->getRequestValue($key);
                if (is_array($model_array[$key]['values_array']) && count($model_array[$key]['values_array']) != 0) {
                    $model_array[$key]['value'] = implode(',', $model_array[$key]['values_array']);
                }
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_box_structure') {
                $v = $this->getRequestValue($key);
                if ($v !== NULL) {
                    $model_array[$key]['value'] = $v;
                }
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'mobilephone') {
                $model_array[$key]['value'] = preg_replace('/\D/', '', $this->getRequestValue($key));
                continue;
            }


            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_by_query_multiple') {
                $model_array[$key]['values_array'] = $this->getRequestValue($key);
                if (is_array($model_array[$key]['values_array']) && count($model_array[$key]['values_array']) != 0) {
                    $model_array[$key]['value'] = implode(',', $model_array[$key]['values_array']);
                }
                continue;
            }

            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'select_box') {

                if (isset($parameters['multiselect']) && 1 == (int)$parameters['multiselect']) {
                    $model_array[$key]['values_array'] = (array)$this->getRequestValue($key);
                    if (is_array($model_array[$key]['values_array']) && count($model_array[$key]['values_array']) != 0) {
                        $model_array[$key]['value'] = implode(',', $model_array[$key]['values_array']);
                    }
                } else {
                    $model_array[$key]['value'] = $this->getRequestValue($model_array[$key]['name']);
                    if (!is_array($model_array[$key]['value'])) {
                        if (isset($model_array[$key]['select_data'][$model_array[$key]['value']])) {
                            $model_array[$key]['value_string'] = $model_array[$key]['select_data'][$model_array[$key]['value']];
                        } else {
                            $model_array[$key]['value_string'] = '';
                        }
                    }
                }
                continue;
            }

            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'safe_string') {
                if (!is_array($this->getRequestValue($model_array[$key]['name']))) {

                    $sval = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($model_array[$key]['name'])));

                    if (isset($parameters['rules']) && $parameters['rules'] != '') {
                        $rules_string = $parameters['rules'];

                        $rules_parts = explode(',', $rules_string);
                        foreach ($rules_parts as $r => $rp) {
                            $rules_parts[$r] = trim($rp);
                        }


                        foreach ($rules_parts as $rp) {
                            $x = explode(':', $rp);
                            $rules[trim($x[0])] = (isset($x[1]) ? trim($x[1]) : '');
                        }

                        if (!isset($rules['Type'])) {
                            $rules['Type'] = 'string';
                        }

                        switch ($rules['Type']) {
                            case 'decimal' :
                            {
                                if ($sval != '') {
                                    $sval = str_replace(',', '.', $sval);
                                }
                                break;
                            }
                        }
                    }

                    $model_array[$key]['value'] = $sval;

                } else {
                    $xvalue = $this->getRequestValue($model_array[$key]['name']);
                    if (!empty($xvalue)) {
                        foreach ($xvalue as $xk => $xv) {
                            $xvalue[$xk] = strip_tags(htmlspecialchars_decode($xv));
                        }
                    }
                    $model_array[$key]['value'] = $xvalue;
                }
                continue;
            }

            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'textarea') {
                if (isset($model_array[$key]['parameters'])) {
                    $parameters = $model_array[$key]['parameters'];
                } else {
                    $parameters = array();
                }
                if (is_array($parameters) and (isset($parameters['allow_htmltags']) && (int)$parameters['allow_htmltags'] == 1) || (isset($parameters['html']) && (int)$parameters['html'] == 1)) {
                    $model_array[$key]['value'] = $this->htmlspecialchars_decode($this->getRequestValue($model_array[$key]['name']));
                } elseif (is_array($parameters) and $parameters['serialize_array'] and $parameters['serialize_array'] == 1) {
                    if ($this->getRequestValue($model_array[$key]['name']) != '') {
                        $explode_array = explode($this->getConfigValue('apps.excel.images_delimiter'), $this->getRequestValue($model_array[$key]['name']));
                        if (is_array($explode_array) and count($explode_array) > 0) {
                            $model_array[$key]['value'] = serialize($explode_array);
                        }
                    }
                } elseif (is_array($parameters) and $parameters['structure_chain'] and $parameters['structure_chain'] == 1) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/admin/data_manager_export.php';
                    $data_manager_export = new Data_Manager_Export();
                    $model_array[$key]['value'] = $data_manager_export->getTopicIdFromChain($this->getRequestValue($model_array[$key]['name']));
                } else {
                    $model_array[$key]['value'] = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($model_array[$key]['name'])));
                }

                //$model_array[$key]['value'] = trim($this->clearEmojisFromText($model_array[$key]['value']));

                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'textarea_editor') {
                $val = $this->htmlspecialchars_decode($this->getRequestValue($model_array[$key]['name']));

                //$val = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?).*$)@', 'http://***', $val);
                $model_array[$key]['value'] = $val;
                //$model_array[$key]['value']=preg_replace('/style=/', '', $model_array[$key]['value']);
                continue;
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'youtube') {
                $val = $this->getRequestValue($model_array[$key]['name']);
                $yid = '';
                if ($val != '') {
                    if (strpos($val, 'shorts') !== FALSE) {
                        $yid = $val;
                    } elseif (strpos($val, 'youtube.com') !== FALSE) {
                        $d = parse_url($val);
                        if (isset($d['query'])) {
                            parse_str($d['query'], $a);
                            $yid = $a['v'];
                        }
                    } elseif (strpos($val, 'youtu.be') !== FALSE) {
                        $d = parse_url($val);
                        if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                            $yid = trim($d['path'], '/');
                        }
                    } else {
                        if (preg_match('/.*([-_A-Za-z0-9]+).*/', $val, $matches)) {
                            $yid = $matches[0];
                        }
                    }
                }
                $model_array[$key]['value'] = $yid;
                continue;
            }

            $model_array[$key]['value'] = $this->getRequestValue($model_array[$key]['name']);
        }
        $model_array = $this->after_request_init($model_array);
        return $model_array;
    }

    /**
     *
     * @param string $table_name
     * @param string $primary_key_name
     * @param int $primary_key_value
     * @param array $model_array
     * @param bool $force_select_values
     * @return array|bool
     */
    function init_model_data_from_db($table_name, $primary_key_name, $primary_key_value, $model_array, $force_select_values = false)
    {

        /**
         * TODO выключить использование кода init_model_data_from_db заменив его вызовом init_model_data_from_db_multi по одному ID

        $m = $this->init_model_data_from_db_multi($table_name, $primary_key_name, array($primary_key_value), $model_array, $force_select_values);
        if(false !== $m && isset($m[$primary_key_value])){
        return $m[$primary_key_value];
        }
        return false;
         */

        $this->set_table_name($table_name);
        $uselangs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $uselangs = true;
            $postfix = $this->getLangPostfix($this->getCurrentLang());
        }
        $DBC = DBC::getInstance();
        $row = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE `' . $primary_key_name . '` = ? LIMIT 1';

        $stmt = $DBC->query($query, array($primary_key_value));
        if ($stmt) {
            $row = $DBC->fetch($stmt);
        }

        if (!isset($row[$primary_key_name]) || $row[$primary_key_name] == '') {
            $this->riseError(Multilanguage::_('L_ERROR_RECORD_NOT_FOUND'));
            return false;
        }

        $need_compose_columns = array();

        foreach ($model_array as $key => $item_array) {

            if (isset($model_array[$key]['parameters'])) {
                $parameters = $model_array[$key]['parameters'];
            } else {
                $parameters = array();
            }

            if (isset($parameters['composed']) && '' != $parameters['composed']) {
                $need_compose_columns[] = $key;
            }

            if (isset($row[$key])) {
                $model_array[$key]['value'] = $row[$key];
            }
            if ($model_array[$key]['type'] == 'primary_key') {
                $need_compose_columns[] = $key;
                $model_array[$key]['value_string'] = $row[$key];
            }

            if ($model_array[$key]['type'] == 'hidden') {
                $model_array[$key]['value_string'] = $row[$key];
            }

            if ($model_array[$key]['type'] == 'safe_string') {
                $model_array[$key]['value_string'] = (isset($row[$key]) ? $row[$key] : '');
            }
            if ($model_array[$key]['type'] == 'checkbox') {
                $model_array[$key]['value_string'] = $model_array[$key]['value'];
            }
            if ($model_array[$key]['type'] == 'uploadify_image' or $model_array[$key]['type'] == 'uploadify_file') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
            }
            if (($model_array[$key]['type'] == 'uploads' || $model_array[$key]['type'] == 'docuploads')) {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                $model_array[$key]['primary_key'] = $primary_key_name;
                $model_array[$key]['table_name'] = $table_name;
            }


            if (($model_array[$key]['type'] == 'uploads' || $model_array[$key]['type'] == 'docuploads') && $key == 'image') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                $model_array[$key]['primary_key'] = $primary_key_name;
                $model_array[$key]['table_name'] = $table_name;
                if ($model_array[$key]['type'] == 'uploads' && isset($row['image_cache']) && $row['image_cache'] != '') {
                    $model_array[$key]['image_cache'] = unserialize($row['image_cache']);
                }else{

                }
            }
            if ($model_array[$key]['type'] == 'avatar') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                $model_array[$key]['primary_key'] = $primary_key_name;
                $model_array[$key]['table_name'] = $table_name;
            }
            if ($model_array[$key]['type'] == 'price') {
                $need_compose_columns[] = $key;
                $model_array[$key]['value_string'] = $row[$key];
            }
            if ($model_array[$key]['name'] == 'phone') {
                $need_compose_columns[] = $key;
            }

            if (isset($model_array[$key]['parameters']['messenger']) && $model_array[$key]['parameters']['messenger'] == '1') {
                $need_compose_columns[] = $key;
            }
            if ($model_array[$key]['type'] == 'select_box_structure_simple_multiple') {
                $model_array[$key]['value'] = $row[$key];
                $model_array[$key]['values_array'] = explode(',', $row[$key]);
            }
            if ($model_array[$key]['type'] == 'tlocation') {
                $model_array[$key]['value']['country_id'] = $row['country_id'];
                $model_array[$key]['value']['region_id'] = $row['region_id'];
                $model_array[$key]['value']['city_id'] = $row['city_id'];
                $model_array[$key]['value']['district_id'] = $row['district_id'];
                $model_array[$key]['value']['street_id'] = $row['street_id'];

                $model_array[$key]['value_string']['country_id'] = $this->get_string_value_by_id('country', 'country_id', 'name', $row['country_id'], true);
                $model_array[$key]['value_string']['region_id'] = $this->get_string_value_by_id('region', 'region_id', 'name', $row['region_id'], true);
                $model_array[$key]['value_string']['city_id'] = $this->get_string_value_by_id('city', 'city_id', 'name', $row['city_id'], true);
                $model_array[$key]['value_string']['district_id'] = $this->get_string_value_by_id('district', 'id', 'name', $row['district_id'], true);
                $model_array[$key]['value_string']['street_id'] = $this->get_string_value_by_id('street', 'street_id', 'name', $row['street_id'], true);

                $tlocation_string = '';
                $temp = array();
                foreach ($model_array[$key]['value_string'] as $ma) {
                    if ($ma != '') {
                        $temp[] = $ma;
                    }
                }
                if (!empty($temp)) {
                    $tlocation_string = implode(', ', $temp);
                }
                $model_array[$key]['tlocation_string'] = $tlocation_string;
            }
            if ($model_array[$key]['type'] == 'geodata') {
                $model_array[$key]['value'] = array();
                $model_array[$key]['value']['lat'] = $row[$model_array[$key]['name'] . '_lat'];
                $model_array[$key]['value']['lng'] = $row[$model_array[$key]['name'] . '_lng'];
                if ($model_array[$key]['value']['lat'] != '' && $model_array[$key]['value']['lng'] != '') {
                    $model_array[$key]['value_string'] = implode(',', $model_array[$key]['value']);
                } else {
                    $model_array[$key]['value_string'] = '';
                }
            }
            if ($model_array[$key]['type'] == 'select_by_query_multi') {

                $name = $model_array[$key]['value_name'];

                $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                if ($uselangs && 0 === $no_ml) {
                    $name .= $postfix;
                }

                $model_array[$key]['value'] = array();
                $model_array[$key]['value_string'] = array();
                $model_array[$key]['value_string_implode'] = '';
                $DBC = DBC::getInstance();
                $query = 'SELECT `field_value` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                $stmt = $DBC->query($query, array($table_name, $key, $primary_key_value));

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        if ($ar['field_value'] > 0) {
                            $model_array[$key]['value'][] = $ar['field_value'];
                        }
                    }
                }

                if (!empty($model_array[$key]['value'])) {
                    $query = 'SELECT `' . $model_array[$key]['primary_key_name'] . '`, `' . $name . '` FROM ' . DB_PREFIX . '_' . $model_array[$key]['primary_key_table'] . ' WHERE `' . $model_array[$key]['primary_key_name'] . '` IN (' . implode(',', $model_array[$key]['value']) . ')';

                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $model_array[$key]['value_string'][$ar[$model_array[$key]['primary_key_name']]] = $ar[$name];
                        }
                    }
                    $model_array[$key]['value_string_implode'] = implode(',', $model_array[$key]['value_string']);
                }
            }
            if ($model_array[$key]['type'] == 'select_by_query_multiple') {
                $model_array[$key]['value'] = $row[$key];
                $model_array[$key]['values_array'] = explode(',', $row[$key]);
                if ($force_select_values) {
                    if (!empty($model_array[$key]['values_array'])) {
                        $t = array();
                        $name = $model_array[$key]['value_name'];
                        $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                        if ($uselangs && 0 === $no_ml) {
                            $name .= $postfix;
                        }
                        foreach ($model_array[$key]['values_array'] as $vi) {
                            $t[] = $this->get_string_value_by_id($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $name, $vi, true);
                        }
                        $model_array[$key]['value_string'] = implode(',', $t);
                    } else {
                        $model_array[$key]['value_string'] = '';
                    }
                } else {
                    $model_array[$key]['value_string'] = '';
                }
            }
            if ($model_array[$key]['type'] == 'select_box_structure_multiple_checkbox') {
                $model_array[$key]['value'] = $row[$key];
                $model_array[$key]['values_array'] = explode(',', $row[$key]);
            }

            if ($model_array[$key]['type'] == 'select_by_query' and $force_select_values) {

                $name = $model_array[$key]['value_name'];

                $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                if ($uselangs && 0 === $no_ml) {
                    $name .= $postfix;
                }
                $model_array[$key]['value_string'] = $this->get_string_value_by_id($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $name, $model_array[$key]['value'], true);
            }

            if ($model_array[$key]['type'] == 'client_id') {
                $val = intval($model_array[$key]['value']);
                if ($val != 0) {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT fio, phone FROM ' . DB_PREFIX . '_client WHERE client_id=? LIMIT 1';
                    $stmt = $DBC->query($query, array($val));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $model_array[$key]['value_string'] = $ar['fio'] . '<br>' . $ar['phone'];
                    } else {
                        $model_array[$key]['value_string'] = '';
                    }
                } else {
                    $model_array[$key]['value_string'] = '';
                }
            }
            if ($model_array[$key]['type'] == 'uploadify_image') {
                $model_array[$key]['image_array'] = $this->get_image_array($model_array[$key]['action'], $model_array[$key]['table_name'], $model_array[$key]['primary_key'], $primary_key_value);
            }
            if ($model_array[$key]['type'] == 'uploadify_file') {
                $model_array[$key]['image_array'] = $this->get_image_array($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $primary_key_value);
            }
            if ($model_array[$key]['type'] == 'values_list') {
                $model_array[$key]['value'] = $this->get_values_list($model_array[$key]['what'], $model_array[$key]['primary_table'], $model_array[$key]['primary_key'], $model_array[$key]['secondary_table'], $model_array[$key]['secondary_key'], $primary_key_value);
                //SELECT tag_name FROM re_tag WHERE tag_id IN (SELECT tag_id FROM re_shop_product_tag WHERE shop_product_id=5)
            }
            if ($model_array[$key]['type'] == 'parameter') {
                if (isset($parameters['type']) && $parameters['type'] == 'json') {
                    $model_array[$key]['value'] = json_decode($model_array[$key]['value'], true);
                } else {
                    $model_array[$key]['value'] = unserialize($model_array[$key]['value']);
                }
            }
            if ($model_array[$key]['type'] == 'uploads' || $model_array[$key]['type'] == 'docuploads') {
                if (isset($parameters['tagged']) && $parameters['tagged'] == 1) {
                    $tagged = true;
                    $taggedlng = false;
                } else {
                    $tagged = false;
                    $taggedlng = false;
                }

                if ($model_array[$key]['type'] == 'uploads' &&
                    $this->getConfigValue('apps.excel.use_image_cache') == 1 and
                    $this->getConfigValue('apps.excel.image_cache_source') == 1 and
                    $row['image_cache'] != '' and
                    $model_array[$key]['value'] == '' and
                    $key == 'image' and
                    $model_array[$key]['value'] == ''
                ) {
                    //echo $row['image_cache'];

                    $model_array[$key]['value'] = $this->init_image_from_cache(unserialize($row['image_cache']));
                    $model_array[$key]['image_array'] = $model_array[$key]['value'];
                } elseif ($model_array[$key]['value'] != '') {
                    $model_array[$key]['value'] = unserialize($model_array[$key]['value']);
                    if (!empty($model_array[$key]['value']) and @count($model_array[$key]['value']) > 0) {

                        $tags_used = array();

                        foreach ($model_array[$key]['value'] as $i => $items) {

                            if ($tagged) {
                                if (isset($items['tags']) && !empty($items['tags'])) {
                                    foreach ($items['tags'] as $tg) {
                                        $tags_used['list'][] = $tg;
                                        $tags_used['where'][$i][] = $tg;
                                    }
                                }
                            }

                            if (!empty($model_array[$key]['value'][$i]['remote']) and $model_array[$key]['value'][$i]['remote'] === 'true') {
                                $model_array[$key]['value'][$i]['normal_url'] = $model_array[$key]['value'][$i]['normal'];
                                $model_array[$key]['value'][$i] = $this->sharder_mirror($model_array[$key]['value'][$i]);
                            } else {
                                $model_array[$key]['value'][$i]['normal_url'] = $this->getServerFullUrl() .
                                    (($model_array[$key]['type'] == 'uploads') ? $this->getImgDataDir() : $this->getMediaDocsDir()) .
                                    $model_array[$key]['value'][$i]['normal'];
                            }
                        }
                    }

                    if ($tagged) {
                        if (!empty($tags_used['list'])) {
                            $DBC = DBC::getInstance();
                            $query = 'SELECT imagetag_id, name FROM ' . DB_PREFIX . '_imagetag WHERE `code` = ? AND imagetag_id IN (' . implode(',', array_unique($tags_used['list'])) . ')';
                            $stmt = $DBC->query($query, array($model_array[$key]['table_name'] . '.' . $key));
                            if ($stmt) {
                                while ($ar = $DBC->fetch($stmt)) {
                                    $_tags[$ar['imagetag_id']] = $ar['name'];
                                }
                            }

                            foreach ($tags_used['where'] as $itemid => $tgs) {
                                $tt = array();
                                foreach ($tgs as $tgid) {
                                    if (isset($_tags[$tgid])) {
                                        $tt[$tgid] = $_tags[$tgid];
                                    }
                                }
                                $model_array[$key]['value'][$itemid]['_tags'] = $tt;
                            }
                        }
                    }


                    $model_array[$key]['image_array'] = $model_array[$key]['value'];
                } else {
                    //try get images from uploadify
                    if ($table_name == 'data') {
                        //$model_array[$key]['image_array'] = $this->get_image_array ( 'data', 'data', 'id', $primary_key_value );
                        $model_array[$key]['image_array'] = array();
                    } else {
                        $model_array[$key]['value'] = array();
                    }
                }
            }
            if ($model_array[$key]['type'] == 'date' and $force_select_values) {
                if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $model_array[$key]['value'])) {
                    //$model_array[$key]['value'] = date('d.m.Y', strtotime($model_array[$key]['value']));
                    $model_array[$key]['value_string'] = date('d.m.Y', strtotime($model_array[$key]['value']));
                } elseif (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/', $model_array[$key]['value'])) {
                    //$model_array[$key]['value'] = date('d.m.Y', strtotime($model_array[$key]['value']));
                    $model_array[$key]['value_string'] = date('d.m.Y', strtotime($model_array[$key]['value']));
                } elseif ($model_array[$key]['value'] == 0 || $model_array[$key]['value'] == '') {
                    $model_array[$key]['value'] = '';
                    $model_array[$key]['value_string'] = '';
                } else {
                    //$model_array[$key]['value'] = date('d.m.Y', $model_array[$key]['value']);
                    $model_array[$key]['value_string'] = date('d.m.Y', $model_array[$key]['value']);
                }
            }
            if ($model_array[$key]['type'] == 'datetime') {
                $model_array[$key]['value'] = Sitebill_Datetime::getDatetimeFormattedFromCanonical($row[$key], $model_array[$key]['parameters']);
            }
            if ($model_array[$key]['type'] == 'dtdatetime') {
                //$model_array[$key]['value'] =  Sitebill_Datetime::getDatetimeFormattedFromCanonical($model_array[$key]['value'], $model_array[$key]['parameters']);
                $model_array[$key]['value_string'] = Sitebill_Datetime::getDatetimeFormattedFromCanonical($model_array[$key]['value'], $model_array[$key]['parameters']);
            }
            if ($model_array[$key]['type'] == 'dtdate') {
                $model_array[$key]['value_string'] = Sitebill_Datetime::getDateFormattedFromCanonical($model_array[$key]['value'], $model_array[$key]['parameters']);
            }
            if ($model_array[$key]['type'] == 'dttime') {
                $model_array[$key]['value_string'] = Sitebill_Datetime::getTimeFormattedFromCanonical($model_array[$key]['value'], $model_array[$key]['parameters']);
            }
            if ($model_array[$key]['type'] == 'select_box' and $force_select_values) {
                $fname = 'select_data';
                if (isset($parameters['multiselect']) && 1 == (int)$parameters['multiselect']) {

                    $model_array[$key]['value'] = $row[$key];
                    if ($row[$key] != '') {
                        $model_array[$key]['values_array'] = explode(',', $row[$key]);
                        $vals = array();
                        foreach ($model_array[$key]['values_array'] as $mav) {
                            if (isset($model_array[$key]['select_data'][$mav])) {
                                $vals[] = $model_array[$key]['select_data'][$mav];
                            }
                        }
                        $model_array[$key]['value_string'] = implode(',', $vals);
                        $model_array[$key]['value_variants_array'] = $vals;
                    } else {
                        $model_array[$key]['values_array'] = array();
                        $model_array[$key]['value_string'] = '';
                        $model_array[$key]['value_variants_array'] = array();
                    }
                } else {
                    $model_array[$key]['value'] = $row[$key];
                    $model_array[$key]['value_string'] = '';
                    if (isset($model_array[$key][$fname][$model_array[$key]['value']])) {
                        $model_array[$key]['value_string'] = $model_array[$key][$fname][$model_array[$key]['value']];
                    }
                }
            } elseif ($model_array[$key]['type'] == 'select_box') {
                if (isset($parameters['multiselect']) && 1 == (int)$parameters['multiselect']) {
                    $model_array[$key]['value'] = $row[$key];
                    if ($row[$key] != '') {
                        $model_array[$key]['values_array'] = explode(',', $row[$key]);
                    } else {
                        $model_array[$key]['values_array'] = array();
                    }
                    $model_array[$key]['value_string'] = '';
                } else {
                    $model_array[$key]['value'] = $row[$key];
                }
            }
            if ($model_array[$key]['type'] == 'structure' and $force_select_values) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
                $Manager = Structure_Implements::getManager($model_array[$key]['entity']);
                $cs = $Manager->loadCategoryStructure();
                $model_array[$key]['value_string'] = $cs['catalog'][$model_array[$key]['value']]['name'];
            }
            if ($model_array[$key]['type'] == 'select_box_structure' && $force_select_values) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
                $Manager = Structure_Implements::getManager();
                $cs = $Manager->loadCategoryStructure();
                $fname = 'name';
                if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
                    $name .= $postfix;
                }
                $model_array[$key]['value_string'] = '';
                if (isset($cs['catalog'][$model_array[$key]['value']])) {
                    $model_array[$key]['value_string'] = $cs['catalog'][$model_array[$key]['value']][$fname];
                }
            }
            if (isset($model_array[$key]['parameters']['only_owner_access']) && $model_array[$key]['parameters']['only_owner_access'] == 1) {
                $has_access = $this->check_access($table_name, $this->getSessionUserId(), $check_control_name, $primary_key_name, $primary_key_value);
                //$this->writeLog(__METHOD__.', has_access = '.$has_access."$table_name, ".$this->getSessionUserId()." , $check_control_name, $primary_key_name, $primary_key_value");
                //$this->writeLog(__METHOD__. '<pre>'.var_export($model_array[$key], true).'</pre>');
                if (
                    !$has_access and
                    $_SESSION['current_user_group_name'] != 'admin' and
                    !$this->permission->get_access($this->getSessionUserId(), $table_name, 'admin_access')
                ) {
                    $model_array[$key]['value_string'] = _e('скрыто');
                    $model_array[$key]['value'] = _e('скрыто');
                }
            }


            if ($model_array[$key]['type'] == 'compose') {
                $need_compose_columns[] = $key;
            }

        }
        $model_array = $this->compile_compose_columns($model_array, $need_compose_columns);

        return $model_array;
    }

    /**
     *
     * @param string $table_name
     * @param string $primary_key_name
     * @param array $primary_key_values
     * @param array $model_array
     * @param bool $force_select_values
     * @param bool $simplificate
     * @param bool $trimmed_data
     * @return array|bool
     */
    function init_model_data_from_db_multi($table_name, $primary_key_name, $primary_key_values, $model_array, $force_select_values = false, $simplificate = false, $trimmed_data = false)
    {
        $this->set_table_name($table_name);

        $uselangs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $uselangs = true;
            $postfix = $this->getLangPostfix($this->getCurrentLang());
        }

        $collected_data = array();

        $ids = array();
        if (!is_array($primary_key_values)) {
            $ids[] = intval($primary_key_values);
            $collected_data[intval($primary_key_values)] = array();
        } else {
            foreach ($primary_key_values as $pkv) {
                if (0 !== intval($pkv)) {
                    $ids[] = intval($pkv);
                    $collected_data[intval($pkv)] = array();
                }
            }
        }

        if (empty($ids)) {
            return false;
        }

        // Признак использования кешированных фото из excel
        $excelcachhingused = false;
        $tables_with_excel_caching = array('data', 'products');

        if ($this->getConfigValue('apps.excel.use_image_cache') == 1 && $this->getConfigValue('apps.excel.image_cache_source') == 1) {
            $excelcachhingused = true;
        }


        $select_by_query = array();
        $structures = array();
        $has_uploadify_image = false;
        $has_multi_elements = array();
        $uploadify_image_collector = array();
        $fields = array();
        $hasclients = false;

        foreach ($model_array as $model_item) {
            if (@$model_item['dbtype'] == 'notable' && @$model_item['type'] != 'select_by_query_multi') {
                continue;
            }
            if (!isset($model_item['type'])) {
                continue;
            }
            switch ($model_item['type']) {
                case 'safe_string' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'youtube' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'hidden' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'mobilephone' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'checkbox' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'select_box_structure' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'select_by_query' :
                {
                    $parameters = @$model_item['parameters'];
                    $fname = $model_item['value_name'];
                    $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                    if ($uselangs && 0 === $no_ml) {
                        $fname .= $postfix;
                    }
                    $select_by_query[$model_item['name']] = array(
                        'primary_key_table' => $model_item['primary_key_table'],
                        'primary_key_name' => $model_item['primary_key_name'],
                        'value_name' => $fname,
                        '_value_name' => $model_item['value_name']
                    );

                    $fields[] = $model_item['name'];
                    break;
                }
                case 'client_id' : {
                    $hasclients = true;
                    $fields[] = $model_item['name'];
                }
                case 'select_box' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'auto_add_value' :
                {
                    break;
                }
                case 'price' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'textarea' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'uploadify_image' :
                {
                    $has_uploadify_image = true;
                    break;
                }
                case 'uploadify_file' :
                {
                    break;
                }
                case 'select_by_query_multi' :
                {
                    $has_multi_elements[$model_item['name']] = $model_item['name'];
                    break;
                }
                case 'password' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'photo' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'geodata' :
                {
                    $fields[] = $model_item['name'] . '_lat';
                    $fields[] = $model_item['name'] . '_lng';
                    break;
                }
                case 'structure' :
                {
                    $fields[] = $model_item['name'];
                    $structures[$model_item['entity']] = $model_item['entity'];
                    break;
                }
                case 'textarea_editor' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'date' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'attachment' :
                {
                    break;
                }
                case 'tlocation' :
                {
                    $select_by_query['country_id'] = array(
                        'primary_key_table' => 'country',
                        'primary_key_name' => 'country_id',
                        'value_name' => 'name'
                    );
                    $select_by_query['region_id'] = array(
                        'primary_key_table' => 'region',
                        'primary_key_name' => 'region_id',
                        'value_name' => 'name'
                    );
                    $select_by_query['city_id'] = array(
                        'primary_key_table' => 'city',
                        'primary_key_name' => 'city_id',
                        'value_name' => 'name'
                    );
                    $select_by_query['district_id'] = array(
                        'primary_key_table' => 'district',
                        'primary_key_name' => 'id',
                        'value_name' => 'name'
                    );
                    $select_by_query['street_id'] = array(
                        'primary_key_table' => 'street',
                        'primary_key_name' => 'street_id',
                        'value_name' => 'name'
                    );

                    $fields[] = 'country_id';
                    $fields[] = 'region_id';
                    $fields[] = 'city_id';
                    $fields[] = 'district_id';
                    $fields[] = 'street_id';
                    break;
                }
                case 'captcha' :
                {
                    break;
                }
                case 'dtdatetime' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'dtdate' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'dttime' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'uploads' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'gadres' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'grade' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'separator' :
                {
                    break;
                }
                case 'spacer_text' :
                {
                    break;
                }
                case 'primary_key' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'values_list' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'parameter' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'select_box_structure_multiple_checkbox' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                default :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
            }
        }

        // Извлечение списка полей записи
        if($excelcachhingused && in_array($table_name, $tables_with_excel_caching)){
            $fields[] = 'image_cache';
        }


        $DBC = DBC::getInstance();
        $query = 'SELECT `' . implode('`, `', $fields) . '` FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE `' . $primary_key_name . '` IN (' . implode(',', $ids) . ')';

        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            echo $DBC->getLastError();
        }

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $collected_data[$ar[$primary_key_name]] = $ar;
            }
        }


        if (empty($collected_data)) {
            return false;
        }

        // Eagerload структурных элементов
        if (!empty($structures)) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
            foreach ($structures as $entity => $v) {
                $Manager = Structure_Implements::getManager($entity);
                $structures[$entity] = $Manager->loadCategoryStructure();
            }
        }

        // Eagerload элементов client
        if($hasclients && !empty($clientkeys)){
            foreach ($collected_data as $cdata) {
                if ((int)$cdata['client_id'] !== 0) {
                    $clientkeys[$cdata['client_id']] = array();
                }
            }
            $DBC = DBC::getInstance();
            $query = 'SELECT `client_id`, `fio`, `phone` FROM ' . DB_PREFIX . '_client WHERE `client_id` IN (' . implode(',', array_keys($clientkeys)) . ')';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $clientkeys[$ar['client_id']] = $ar;
                }
            }
        }

        // Eagerload элементов из справочников (oneToMany)
        if (count($select_by_query) > 0) {
            foreach ($select_by_query as $k => $external_quer) {
                foreach ($collected_data as $cdata) {
                    if ((int)$cdata[$k] !== 0) {
                        $select_by_query[$k]['keys'][$cdata[$k]] = $cdata[$k];
                    }
                }
            }

            $DBC = DBC::getInstance();
            foreach ($select_by_query as $k => $eq) {
                if (isset($eq['keys']) && !empty($eq['keys'])) {
                    $query = 'SELECT `' . $eq['primary_key_name'] . '`, `' . $eq['value_name'] . '` FROM ' . DB_PREFIX . '_' . $eq['primary_key_table'] . ' WHERE `' . $eq['primary_key_name'] . '` IN (' . implode(',', $eq['keys']) . ')';
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $select_by_query[$k]['vals'][$ar[$eq['primary_key_name']]] = $ar[$eq['value_name']];
                        }
                    }
                }
            }
        }

        // Eagerload мультиэлементов (manyToMany)
        if (!empty($has_multi_elements)) {
            $melements_variants = array();
            $DBC = DBC::getInstance();
            $query = 'SELECT `field_name`, `primary_id`, `field_value` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (\'' . implode('\',\'', array_keys($has_multi_elements)) . '\') AND `primary_id` IN (' . implode(',', $ids) . ')';

            $stmt = $DBC->query($query, array($table_name));

            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $melements_values[$ar['primary_id']][$ar['field_name']][] = $ar['field_value'];
                    $melements_variants[$ar['field_name']][$ar['field_value']] = $ar['field_value'];
                }
            }

            foreach ($melements_variants as $key => $me_variants) {
                $parameters = $model_array[$key]['parameters'];
                $name = $model_array[$key]['value_name'];

                $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                if ($uselangs && 0 === $no_ml) {
                    $name .= $postfix;
                }

                $query = 'SELECT `' . $model_array[$key]['primary_key_name'] . '`, `' . $name . '` FROM ' . DB_PREFIX . '_' . $model_array[$key]['primary_key_table'] . ' WHERE `' . $model_array[$key]['primary_key_name'] . '` IN (' . implode(',', $me_variants) . ')';

                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $melements_variants[$key][$ar[$model_array[$key]['primary_key_name']]] = $ar[$name];
                    }
                }
            }
        }

        $returned_models = array();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
        $Manager = Structure_Implements::getManager();
        $cs = $Manager->loadCategoryStructure();

        // Apply composed data
        $need_compose_columns = array();
        foreach ($model_array as $key => $model_item) {
            if (isset($model_item['parameters']['composed']) && '' != $model_item['parameters']['composed']) {
                $need_compose_columns[$key] = $key;
            }
            if (isset($model_item['parameters']['messenger']) && $model_item['parameters']['messenger'] == '1') {
                $need_compose_columns[$key] = $key;
            }

            if ($model_item['type'] == 'primary_key') {
                $need_compose_columns[$key] = $key;
            } elseif ($model_item['type'] == 'price') {
                $need_compose_columns[$key] = $key;
            } elseif ($model_item['type'] == 'compose') {
                $need_compose_columns[$key] = $key;
            } elseif ($model_item['name'] == 'phone') {
                $need_compose_columns[$key] = $key;
            }
        }

        // Заполнение данными возвращаемых массивов моделей
        foreach ($collected_data as $pkid => $cdata) {
            if ($trimmed_data) {
                $model = array();
                $model = $model_array;
            } else {
                $model = $model_array;
            }


            foreach ($model_array as $key => $model_item) {

                if (isset($model_item['parameters'])) {
                    $parameters = $model[$key]['parameters'];
                } else {
                    $parameters = array();
                }

                /*if(isset($model_item['parameters']['composed']) && '' != $model_item['parameters']['composed']){
                    $need_compose_columns[$key] = $key;
                }*/

                if ($model_item['type'] == 'safe_string') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'client_id') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = '';
                    if (isset($clientkeys) && isset($clientkeys[$cdata[$key]])) {
                        $model[$key]['value_string'] = $clientkeys[$cdata[$key]]['fio'].'<br>'.$clientkeys[$cdata[$key]]['phone'];
                    }
                }
                if ($model_item['type'] == 'youtube') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'mobilephone') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'primary_key') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                    //$need_compose_columns[$key] = $key;
                }
                if ($model_item['type'] == 'hidden') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'checkbox') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'select_box_structure') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cs['catalog'][$cdata[$key]]['name'];
                }
                if ($model_item['type'] == 'select_by_query') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = '';
                    if (isset($select_by_query[$key]) && isset($select_by_query[$key]['vals']) && isset($select_by_query[$key]['vals'][$cdata[$key]])) {
                        $model[$key]['value_string'] = $select_by_query[$key]['vals'][$cdata[$key]];
                    }
                }
                if ($model_item['type'] == 'select_by_query_multiple') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['values_array'] = explode(',', $cdata[$key]);
                }
                if ($model_item['type'] == 'select_by_query_multi') {

                    $model[$key]['value'] = array();
                    $model[$key]['value_string'] = array();
                    $model[$key]['value_string_implode'] = '';

                    if (isset($melements_values[$pkid][$key])) {
                        $model[$key]['value'] = $melements_values[$pkid][$key];
                        foreach ($melements_values[$pkid][$key] as $k) {
                            $model[$key]['value_string'][$k] = $melements_variants[$key][$k];
                        }
                    }
                    if (is_array($model[$key]['value_string']) and count($model[$key]['value_string']) > 0) {
                        $model[$key]['value_string_implode'] = implode(',', $model[$key]['value_string']);
                    }
                }
                if ($model_item['type'] == 'select_box') {

                    if (isset($model_item['parameters'])) {
                        $parameters = $model[$key]['parameters'];
                    } else {
                        $parameters = array();
                    }
                    if (isset($parameters['multiselect']) && 1 == (int)$parameters['multiselect']) {

                        $model[$key]['value'] = $cdata[$key];
                        if ($cdata[$key] != '') {
                            $model[$key]['values_array'] = explode(',', $cdata[$key]);
                            $vals = array();
                            foreach ($model[$key]['values_array'] as $mav) {
                                if (isset($model_item['select_data'][$mav])) {
                                    $vals[] = $model_item['select_data'][$mav];
                                }
                            }
                            $model[$key]['value_string'] = implode(',', $vals);
                            $model[$key]['value_variants_array'] = $vals;
                        } else {
                            $model[$key]['values_array'] = array();
                            $model[$key]['value_string'] = '';
                            $model[$key]['value_variants_array'] = array();
                        }
                    } else {
                        $model[$key]['value'] = $cdata[$key];
                        $model[$key]['value_string'] = '';
                        if (isset($model_item['select_data'][$cdata[$key]])) {
                            $model[$key]['value_string'] = $model_item['select_data'][$cdata[$key]];
                        }
                    }
                }
                if ($model_item['type'] == 'price') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                    //$need_compose_columns[$key] = $key;
                }
                if ($model_item['type'] == 'textarea') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'uploadify_image') {
                    if (isset($uploadify_image_collector[$cdata[$primary_key_name]])) {
                        $model[$key]['value'] = $uploadify_image_collector[$cdata[$primary_key_name]];
                        $model[$key]['value_string'] = $uploadify_image_collector[$cdata[$primary_key_name]];
                        $model[$key]['image_array'] = $uploadify_image_collector[$cdata[$primary_key_name]];
                    } else {
                        $model[$key]['value'] = array();
                        $model[$key]['value_string'] = array();
                        $model[$key]['image_array'] = array();
                    }
                }
                if ($model_item['type'] == 'uploadify_file') {

                }
                if ($model_item['type'] == 'password') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'photo') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'geodata') {
                    $model[$key]['value'] = array();
                    $model[$key]['value']['lat'] = $cdata[$key . '_lat'];
                    $model[$key]['value']['lng'] = $cdata[$key . '_lng'];
                    if ($cdata[$key . '_lat'] != '' && $cdata[$key . '_lng'] != '') {
                        $model[$key]['value_string'] = $cdata[$key . '_lat'] . ',' . $cdata[$key . '_lng'];
                    } else {
                        $model[$key]['value_string'] = '';
                    }
                }
                if ($model_item['type'] == 'structure') {
                    $model[$key]['value'] = $cdata[$key];
                    if (isset($structures[$model_item['entity']])) {
                        $model[$key]['value_string'] = $structures[$model[$key]['entity']]['catalog'][$cdata[$key]]['name'];
                    }
                }
                if ($model_item['type'] == 'textarea_editor') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'date') {
                    if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $cdata[$key])) {
                        $model[$key]['value'] = $cdata[$key];
                        $model[$key]['value_string'] = date('d.m.Y', strtotime($cdata[$key]));
                    } elseif (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/', $cdata[$key])) {
                        $model[$key]['value'] = $cdata[$key];
                        $model[$key]['value_string'] = date('d.m.Y', strtotime($cdata[$key]));
                    } elseif ($cdata[$key] == 0 || $cdata[$key] == '') {
                        $model[$key]['value'] = '';
                        $model[$key]['value_string'] = '';
                    } else {
                        $model[$key]['value'] = $cdata[$key];
                        $model[$key]['value_string'] = date('d.m.Y', $cdata[$key]);
                    }
                }
                if ($model_item['type'] == 'attachment') {

                }
                if ($model_item['type'] == 'tlocation') {
                    $model[$key]['value']['country_id'] = $cdata['country_id'];
                    $model[$key]['value']['region_id'] = $cdata['region_id'];
                    $model[$key]['value']['city_id'] = $cdata['city_id'];
                    $model[$key]['value']['district_id'] = $cdata['district_id'];
                    $model[$key]['value']['street_id'] = $cdata['street_id'];

                    $model[$key]['value_string']['country_id'] = $select_by_query['country_id']['vals'][$cdata['country_id']];
                    $model[$key]['value_string']['region_id'] = $select_by_query['region_id']['vals'][$cdata['region_id']];
                    $model[$key]['value_string']['city_id'] = $select_by_query['city_id']['vals'][$cdata['city_id']];
                    $model[$key]['value_string']['district_id'] = $select_by_query['district_id']['vals'][$cdata['district_id']];
                    $model[$key]['value_string']['street_id'] = $select_by_query['street_id']['vals'][$cdata['street_id']];

                    $tlocation_string = '';
                    $temp = array();
                    foreach ($model[$key]['value_string'] as $ma) {
                        if ($ma != '') {
                            $temp[] = $ma;
                        }
                    }
                    if (!empty($temp)) {
                        $tlocation_string = implode(', ', $temp);
                    }
                    $model[$key]['tlocation_string'] = $tlocation_string;
                }
                if ($model_item['type'] == 'dtdatetime') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = Sitebill_Datetime::getDatetimeFormattedFromCanonical($cdata[$key], $parameters);
                    $simplied[$key]['value'] = $cdata[$key];
                    $simplied[$key]['value_string'] = Sitebill_Datetime::getDatetimeFormattedFromCanonical($cdata[$key], $parameters);
                }
                if ($model_item['type'] == 'dtdate') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = Sitebill_Datetime::getDateFormattedFromCanonical($cdata[$key], $parameters);
                }
                if ($model_item['type'] == 'dttime') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = Sitebill_Datetime::getTimeFormattedFromCanonical($cdata[$key], $parameters);
                }
                if ($model_item['type'] == 'gadres') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                }
                if ($model_item['type'] == 'grade') {

                }
                if ($model_item['type'] == 'docuploads') {
                    $model[$key]['image_array'] = array();
                    if ($cdata[$key] != '') {
                        $unserialize = @unserialize($cdata[$key]);
                        if (is_array($unserialize)) {
                            foreach ( $unserialize as $u_key => $u_item ) {
                                $unserialize[$u_key]['normal_url'] = $this->getServerFullUrl() .
                                    $this->getMediaDocsDir() .
                                    $u_item['normal'];;
                            }
                        }
                        $model[$key]['value'] = $unserialize;
                        $model[$key]['image_array'] = $model[$key]['value'];
                    }
                }
                if ($model_item['type'] == 'uploads') {
                    $model[$key]['image_cache'] = unserialize($cdata['image_cache']);
                    if (isset($cdata['image_cache']) && $cdata['image_cache'] != ''/* && $cdata[$key] == ''*/) {
                        $model[$key]['image_cache'] = unserialize($cdata['image_cache']);
                        $model[$key]['value'] = $this->init_image_from_cache($model[$key]['image_cache']);
                        $model[$key]['image_array'] = $model[$key]['value'];
                    } elseif ($cdata[$key] != '') {
                        $model[$key]['value'] = unserialize($cdata[$key]);

                        if ( is_array($model[$key]['value']) and @count($model[$key]['value']) > 0 ) {
                            foreach ($model[$key]['value'] as $i => $items) {
                                if ( !empty($model[$key]['value'][$i]['remote']) and $model[$key]['value'][$i]['remote'] === 'true' ) {
                                    $model[$key]['value'][$i]['normal_url'] = $model[$key]['value'][$i]['normal'];
                                    $model[$key]['value'][$i] = $this->sharder_mirror($model[$key]['value'][$i]);
                                } else {
                                    $model[$key]['value'][$i]['normal_url'] = $this->getServerFullUrl().$this->getMediaDocsDir().$model[$key]['value'][$i]['normal'];
                                }
                            }
                        }

                        $model[$key]['image_array'] = $model[$key]['value'];
                    } else {
                        if ($table_name == 'data') {
                            $model[$key]['image_array'] = $this->get_image_array('data', 'data', 'id', $cdata[$primary_key_name]);
                        } else {
                            $model[$key]['value'] = array();
                        }
                    }
                    $model[$key]['primary_key_value'] = $pkid;
                    $model[$key]['primary_key'] = $primary_key_name;
                    $model[$key]['table_name'] = $table_name;
                }
                if ($model_item['type'] == 'values_list') {
                }
                if ($model_item['type'] == 'parameter') {
                    if (isset($parameters['type']) && $parameters['type'] == 'json') {
                        $model[$key]['value'] = $model[$key]['value_string'] = json_decode($cdata[$key], true);
                    } else {
                        $model[$key]['value'] = $model[$key]['value_string'] = unserialize($cdata[$key]);
                    }
                }
                if ($model_item['type'] == 'compose') {
                    //$need_compose_columns[$key] = $key;
                }
                if ($model_item['type'] == 'injector') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $this->init_injector_value($key, $model, $model_item);
                }
                if (isset($model_array[$key]['parameters']['only_owner_access']) && $model_array[$key]['parameters']['only_owner_access'] == 1) {
                    $has_access = $this->check_access($table_name, $this->getSessionUserId(), $check_control_name, $primary_key_name, $pkid);
                    //$this->writeLog(__METHOD__.', has_access = '.$has_access."$table_name, ".$this->getSessionUserId()." , $check_control_name, $primary_key_name, $primary_key_value");
                    //$this->writeLog(__METHOD__. '<pre>'.var_export($model_array[$key], true).'</pre>');
                    if (
                        !$has_access and
                        $_SESSION['current_user_group_name'] != 'admin' and
                        !$this->permission->get_access($this->getSessionUserId(), $table_name, 'admin_access')
                    ) {
                        $model[$key]['value_string'] = _e('скрыто');
                        $model[$key]['value'] = _e('скрыто');
                    }
                }

            }

            if (count($need_compose_columns) > 0) {
                $model = $this->compile_compose_columns($model, $need_compose_columns);
            }

            if ($trimmed_data) {
                /*foreach($model as $k => $m){
                    if(!in_array($k, array('value', 'value_string'))){
                        unset($model[$k]);
                    }
                }
                $returned_models[$pkid] = $model;*/
                $returned_models[$pkid] = Sitebill::modelSimplification($model);
            } elseif ($simplificate) {
                $returned_models[$pkid] = Sitebill::modelSimplification($model);
            } else {
                $returned_models[$pkid] = $model;
            }
        }
        return $returned_models;
    }

    function init_model_data_from_var($var, $primary_key_value, $model_array, $force_select_values = false)
    {

        $row = $var;

        foreach ($model_array as $key => $item_array) {

            $model_array[$key]['value'] = $row[$key];

            if ($model_array[$key]['type'] == 'uploadify_image' or $model_array[$key]['type'] == 'uploadify_file') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
            }
            if ($model_array[$key]['type'] == 'select_box_structure_simple_multiple') {
                $model_array[$key]['value'] = $row[$key];
                $model_array[$key]['values_array'] = explode(',', $row[$key]);
            }
            if ($model_array[$key]['type'] == 'geodata') {
                $model_array[$key]['value']['lat'] = $row[$model_array[$key]['name'] . '_lat'];
                $model_array[$key]['value']['lng'] = $row[$model_array[$key]['name'] . '_lng'];
            }
            if ($model_array[$key]['type'] == 'select_by_query_multiple') {
                $model_array[$key]['value'] = $row[$key];
                $model_array[$key]['values_array'] = explode(',', $row[$key]);
            }
            if ($model_array[$key]['type'] == 'select_by_query' && $force_select_values) {

                $name = $model_array[$key]['value_name'];

                $no_ml = (isset($parameters['no_ml']) ? intval($parameters['no_ml']) : 0);
                if ($uselangs && 0 === $no_ml) {
                    $name .= $postfix;
                }


                $model_array[$key]['value_string'] = $this->get_string_value_by_id($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $name, $model_array[$key]['value']);
            }
            if ($model_array[$key]['type'] == 'uploadify_image') {
                $model_array[$key]['image_array'] = $this->get_image_array('data', 'data', 'id', $primary_key_value);
            }
            if ($model_array[$key]['type'] == 'uploadify_file') {
                $model_array[$key]['image_array'] = $this->get_image_array($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $primary_key_value);
            }
            if ($model_array[$key]['type'] == 'values_list') {
                $model_array[$key]['value'] = $this->get_values_list($model_array[$key]['what'], $model_array[$key]['primary_table'], $model_array[$key]['primary_key'], $model_array[$key]['secondary_table'], $model_array[$key]['secondary_key'], $primary_key_value);
            }
            if ($model_array[$key]['type'] == 'date' and $force_select_values) {
                if (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})/', $model_array[$key]['value'])) {
                    $model_array[$key]['value'] = date('d.m.Y', strtotime($model_array[$key]['value']));
                } elseif (preg_match('/(\d{4,4})-(\d{2,2})-(\d{2,2})/', $model_array[$key]['value'])) {
                    $model_array[$key]['value'] = date('d.m.Y', strtotime($model_array[$key]['value']));
                } elseif ($model_array[$key]['value'] == 0 || $model_array[$key]['value'] == '') {

                    $model_array[$key]['value'] = '';
                } else {
                    $model_array[$key]['value'] = date('d.m.Y', $model_array[$key]['value']);
                }
            }
            if ($model_array[$key]['type'] == 'select_box' and $force_select_values) {
                $model_array[$key]['value_string'] = $model_array[$key]['select_data'][$model_array[$key]['value']];
            }
            if ($model_array[$key]['type'] == 'structure' and $force_select_values) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
                $Manager = Structure_Implements::getManager($model_array[$key]['entity']);
                $cs = $Manager->loadCategoryStructure();
                $model_array[$key]['value_string'] = $cs['catalog'][$model_array[$key]['value']]['name'];
            }
            if (isset($model_array[$key]['type']) && $model_array[$key]['type'] == 'youtube') {
                $val = $model_array[$key]['value'];
                $yid = '';
                if ($val != '') {
                    if (strpos($val, 'shorts') !== FALSE) {
                        $yid = $val;
                    } elseif (strpos($val, 'youtube.com') !== FALSE) {
                        $d = parse_url($val);
                        if (isset($d['query'])) {
                            parse_str($d['query'], $a);
                            $yid = $a['v'];
                        }
                    } elseif (strpos($val, 'youtu.be') !== FALSE) {
                        $d = parse_url($val);
                        if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                            $yid = trim($d['path'], '/');
                        }
                    } else {
                        if (preg_match('/.*([-_A-Za-z0-9]+).*/', $val, $matches)) {
                            $yid = $matches[0];
                        }
                    }
                }
                $model_array[$key]['value'] = $yid;
            }
            if ($model_array[$key]['type'] == 'select_box_structure' && $force_select_values) {

                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
                $Manager = Structure_Implements::getManager();
                $cs = $Manager->loadCategoryStructure();
                $fname = 'name';
                if ($uselangs) {
                    $fname .= $postfix;
                }
                $model_array[$key]['value_string'] = '';
                if (isset($cs['catalog'][$model_array[$key]['value']])) {
                    $model_array[$key]['value_string'] = $cs['catalog'][$model_array[$key]['value']][$fname];
                }
            }
        }
        return $model_array;
    }

    /**
     * DEPRECATED
     * Init model data from db (language_version)
     * @param string $table_name
     * @param string $primary_key_name primary key name
     * @param int $primary_key_value primary key
     * @param array $model_array
     * @param boolean $force_select_values
     * @param int $language_id
     * @return boolean
     */
    function init_model_data_from_db_language($table_name, $primary_key_name, $primary_key_value, $model_array, $force_select_values, $language_id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE link_id = ? AND language_id = ?';
        $stmt = $DBC->query($query, array($primary_key_value, $language_id));
        if ($stmt) {
            $row = $DBC->fetch($stmt);
        } else {
            $this->riseError(Multilanguage::_('L_ERROR_RECORD_NOT_FOUND'));
            return false;
        }

        foreach ($model_array as $key => $item_array) {
            $model_array[$key]['value'] = $row[$key];
            if ($model_array[$key]['type'] == 'uploadify_image') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
            }
            if ($model_array[$key]['type'] == 'select_by_query' and $force_select_values) {
                $model_array[$key]['value_string'] = $this->get_string_value_by_id($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $model_array[$key]['value_name'], $model_array[$key]['value']);
            }
            if ($model_array[$key]['type'] == 'uploadify_image') {
                $model_array[$key]['image_array'] = $this->get_image_array('data', 'data', 'id', $primary_key_value);
            }
        }
        return $model_array;
    }

    function init_model_data_auto_translate($model_array)
    {
        foreach ($model_array as $key => $item_array) {
            if ($model_array[$key]['type'] == 'safe_string' or $model_array[$key]['type'] == 'textarea' or $model_array[$key]['type'] == 'textarea_editor') {
                if (preg_match('/_(' . implode('|', array_values(Multilanguage::availableLanguages())) . ')$/', $key, $matches)) {
                    //echo $matches[0].'<br>';
                    $parent_key = str_replace($matches[0], '', $key);
                    $need_translate = false;
                    if ($model_array[$parent_key]['value'] != '' && $model_array[$key]['value'] == '') {
                        $lang_key = str_replace('_', '', $matches[0]);
                        $target_language = $lang_key;
                        $source_key = $parent_key;
                        $target_key = $key;
                        $need_translate = true;
                    } elseif ($model_array[$parent_key]['value'] == '' && $model_array[$key]['value'] != '') {
                        $target_language = $this->getConfigValue('apps.language.default_lang_code');
                        $source_key = $key;
                        $target_key = $parent_key;
                        $need_translate = true;
                    }
                    if ($need_translate) {
                        if ($model_array[$parent_key]['name'] == 'select_data') {
                            $model_array[$target_key]['value'] = $this->translate_select_data_value($model_array[$source_key]['value'], $target_language);

                        } else {
                            $model_array[$target_key]['value'] = $this->api_translate($model_array[$source_key]['value'], $target_language);
                        }
                    }
                }
            }
        }
        return $model_array;
    }

    function translate_select_data_value($value, $target_language)
    {
        if (!isset($this->helper)) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php');
            $this->helper = new Admin_Table_Helper();
        }
        $select_data_array = $this->helper->unserializeSelectData($value);
        $select_data_values = array_values($select_data_array);
        $select_data_keys = array_keys($select_data_array);
        $translated_select_data_values = $this->api_translate($select_data_values, $target_language);
        $translated_select_data_array = array_combine($select_data_keys, $translated_select_data_values);
        return $this->helper->serializeSelectData($translated_select_data_array);
    }

    /**
     * Init language values in model array with data
     * @param array $model_array
     * @return array
     */
    function init_language_values($model_array, $model_array_language = array())
    {
        if (empty($model_array_language)) {
            $model_array_language = $model_array;
        }
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $postfix = $this->getLangPostfix($this->getCurrentLang());
            if ($postfix != '' and is_array($model_array)) {
                foreach ($model_array as $key => $item_array) {
                    $lang_key = $key . $postfix;
                    if (1 === intval($this->getConfigValue('apps.language.fulltransmode'))) {
                        if(isset($model_array_language[$lang_key])){
                            $model_array[$key]['value'] = $model_array_language[$lang_key]['value'];
                        }
                    } elseif ($model_array_language[$lang_key]['value'] != '') {
                        $model_array[$key]['value'] = $model_array_language[$lang_key]['value'];
                    }
                }
            }
        }

        return $model_array;
    }

    function init_injector_value($key, $model, $model_item)
    {
        $DBC = DBC::getInstance();

        if ($key == 'contact_id') {
            $contact_form_injector = new \client\admin\Form_Injection();
            $contact_form_injector->get_client_info($model[$key], true);
            $rs = $contact_form_injector->get_client_name();
            $rs .= ' ' . @implode(',<br> ', $contact_form_injector->get_client_info_additional());
            return $rs;
        }
    }

    function init_image_from_cache($image_array)
    {
        foreach ($image_array as $image) {
            if ($this->getConfigValue('apps.sharder.mirroring.enable')) {
                $image =
                    str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image);
            }

            if (preg_match('/640x480/', $image) and preg_match('/avito/', $image)) {
                $preview = str_replace('640x480', '208x156', $image);
            } else {
                $preview = $image;
            }
            $ra[] = array('preview' => $preview, 'normal' => $image, 'remote' => 'true');
        }
        return $ra;
    }

    function init_compose_column($model, $key)
    {
        //Добавляем к цене массив с currency_id
        if ($model[$key]['type'] == 'price') {
            if (isset($model['currency_id'])) {
                $model[$key]['currency_id'] = $model['currency_id'];
            }
            return $model;
        }
        if ($model[$key]['type'] == 'primary_key') {
            $model[$key]['permissions'] = $this->get_crud_permissions($model, $key);
            return $model;
        }

        if ($model[$key]['name'] == 'phone' or (isset($model[$key]['parameters']['messenger']) and $model[$key]['parameters']['messenger'] == '1')) {
            $messages_object = $this->get_api_common()->init_custom_model_object('messages');
            try {
                if (is_object($messages_object) and method_exists($messages_object, 'get_message_count_by_client_id')) {
                    $model[$key]['whatsapp_history'] = @$messages_object->get_message_count_by_client_id($model['client_id']['value']);
                }
            } catch (Exception $e) {

            }
            return $model;
        }


        if (isset($model[$key]['parameters']['columns'])) {
            $columns = explode(',', $model[$key]['parameters']['columns']);
        } else {
            $columns = array();
        }

        if (isset($model[$key]['parameters']['separator'])) {
            $separator = $model[$key]['parameters']['separator'];
        }

        if (!isset($separator) || $separator == '') {
            $separator = ', ';
        }
        $composed_items = array();
        if (!empty($columns)) {
            foreach ($columns as $idx => $column_item) {
                if ($model[$column_item]['value_string'] != '') {
                    $composed_items[] = $model[$column_item]['value_string'];
                } elseif ($model[$column_item]['value'] != '' and $model[$column_item]['value'] != 0) {
                    $composed_items[] = $model[$column_item]['value'];
                }
            }
        }

        $function_name = trim($model[$key]['parameters']['function']);
        if (method_exists($this->compose_functions, $function_name)) {
            $value = $this->compose_functions->$function_name($model, $key);
        } elseif (function_exists($function_name)) {
            $value = $this->exec_function($function_name, $model, $key);
        } else {
            $value = implode($separator, $composed_items);
        }
        $model[$key]['value'] = $value;
        $model[$key]['value_string'] = $value;

        return $model;
    }

    function compile_compose_columns($model, $compose_columns)
    {
        if (count($compose_columns) > 0) {
            foreach ($compose_columns as $idx => $key) {
                $model = $this->init_compose_column($model, $key);
            }
        }
        return $model;
    }

    function sharder_mirror($image_array, $from_array = false)
    {
        if (!$this->getConfigValue('apps.sharder.mirroring.enable')) {
            return $image_array;
        }
        if ($from_array and is_array($image_array)) {
            foreach ($image_array as $index => $item) {
                $image_array[$index] = $this->replace_one_mirror_item($item);
            }
        } else {
            $image_array = $this->replace_one_mirror_item($image_array);
        }
        return $image_array;
    }

    function replace_one_mirror_item($image_array)
    {
        if ( is_array($image_array) ) {
            $image_array['normal_url'] =
                str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image_array['normal_url']);

            $image_array['preview'] =
                str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image_array['preview']);
            $image_array['normal'] =
                str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image_array['normal']);
        }
        return $image_array;
    }

    function applyGCompose($model_array)
    {
        $need_compose_columns = array();

        foreach ($model_array as $key => $item_array) {

            if (isset($item_array['parameters']['gcomposed']) && '' != $item_array['parameters']['gcomposed']) {
                $need_compose_columns[] = $key;
            }
        }

        if (!empty($need_compose_columns)) {
            $model_array = $this->compile_compose_columns($model_array, $need_compose_columns);
        }

        return $model_array;
    }

    /**
     * TODO Проверить используемость функции
     */
    protected function intit_from_db_mass($table_name, $primary_key_name, $primary_key_value, $model_array)
    {
        $mass_items = array();
        foreach ($model_array as $model_item) {
            if ($model_item['type'] == 'select_by_query') {
                $mass_items[$model_item['name']] = array(
                    'from' => $model_item['primary_key_table'],
                    'key_in_pt' => $model_item['primary_key_name'],
                    'value_in_pt' => $model_item['value_name']
                );
            }
        }
    }

}
