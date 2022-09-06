<?php

use system\lib\model\compose_functions;

/**
 * Data model
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Data_Model extends SiteBill
{
    use \system\lib\model\traits\lifecycle\AfterRequestInitTrait;

    static $cache;

    /**
     * @var Permission
     */
    private $permission;
    /**
     * @var compose_functions
     */
    private $compose_functions;
    private $table_name;

    /**
     * Construct
     */
    function __construct()
    {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
        $this->permission = new Permission();
        $this->compose_functions = new compose_functions();
    }

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
                if (NULL !== $this->getRequestValue($key)) {
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
                if ((isset($parameters['allow_htmltags']) && (int)$parameters['allow_htmltags'] == 1) || (isset($parameters['html']) && (int)$parameters['html'] == 1)) {
                    $model_array[$key]['value'] = $this->htmlspecialchars_decode($this->getRequestValue($model_array[$key]['name']));
                } elseif ($parameters['serialize_array'] and $parameters['serialize_array'] == 1) {
                    if ( $this->getRequestValue($model_array[$key]['name']) != '' ) {
                        $explode_array = explode($this->getConfigValue('apps.excel.images_delimiter'), $this->getRequestValue($model_array[$key]['name']));
                        if ( is_array($explode_array) and count($explode_array) > 0 ) {
                            $model_array[$key]['value'] = serialize($explode_array);
                        }
                    }
                } elseif ($parameters['structure_chain'] and $parameters['structure_chain'] == 1) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/admin/data_manager_export.php';
                    $data_manager_export = new Data_Manager_Export();
                    $model_array[$key]['value'] = $data_manager_export->getTopicIdFromChain($this->getRequestValue($model_array[$key]['name']));
                } else {
                    $model_array[$key]['value'] = strip_tags($this->htmlspecialchars_decode($this->getRequestValue($model_array[$key]['name'])));
                }

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
                    if (strpos($val, 'youtube.com') !== FALSE) {
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

    public function convertDistMeashures($val, $from, $to = 'm')
    {
        $def_m = 'm';
        $convars = array(
            'm' => array('km' => 0.001, 'm' => 1, 'mil' => 0.000621371, 'yar' => 1.09361, 'ft' => 3.28084, 'smil' => 0.000539957)
        );
        if ($from == $to) {
            return $val;
        } elseif ($from == $def_m) {
            if (isset($convars[$def_m][$to])) {
                return $convars[$def_m][$to] * $val;
            }
        } else {
            $from_k = 0;
            $to_k = 0;
            foreach ($convars[$def_m] as $k => $v) {
                if ($k == $from) {
                    $from_k = $v;
                }
                if ($k == $to) {
                    $to_k = $v;
                }
            }
            if ($from_k != 0 && $to_k != 0) {
                return $to_k * $val / $from_k;
            }
        }
        return $val;
    }

    public function convertAreaMeashures($val, $from, $to = 'sqm')
    {
        $def_m = 'sqm';
        $convars = array(
            'sqm' => array('ha' => 0.0001, 'sqm' => 1, 'ar' => 0.01, 'sqf' => 10.7639, 'sqy' => 1.19599, 'acr' => 0.000247105)
        );
        if ($from == $to) {
            return $val;
        } elseif ($from == $def_m) {
            if (isset($convars[$def_m][$to])) {
                return $convars[$def_m][$to] * $val;
            }
        } else {
            $from_k = 0;
            $to_k = 0;
            foreach ($convars[$def_m] as $k => $v) {
                if ($k == $from) {
                    $from_k = $v;
                }
                if ($k == $to) {
                    $to_k = $v;
                }
            }
            if ($from_k != 0 && $to_k != 0) {
                return $to_k * $val / $from_k;
            }
        }
        return $val;
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
            if ($postfix != '') {
                foreach ($model_array as $key => $item_array) {
                    $lang_key = $key . $postfix;
                    if (1 === intval($this->getConfigValue('apps.language.fulltransmode'))) {
                        $model_array[$key]['value'] = $model_array_language[$lang_key]['value'];
                    } elseif ($model_array_language[$lang_key]['value'] != '') {
                        $model_array[$key]['value'] = $model_array_language[$lang_key]['value'];
                    }
                }
            }
        }

        return $model_array;
    }

    /*
    function getObject($model, $conditions){
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $model_array = $ATH->load_model($model, true);
        if(is_null($model_array)){
            return null;
        }

        $select_conditions = array();
        $select_values = array();

        foreach ($model_array as $modelitem_name => $model_item) {
            if(isset($conditions[$modelitem_name])){
                if(is_array($conditions[$modelitem_name])){
                    $select_conditions[] = '`'.$modelitem_name.'` IN ('.implode(',', array_fill(0, count($conditions[$modelitem_name]), '?')).')';
                    $select_values = array_merge($select_values, $conditions[$modelitem_name]);
                }else{
                    $select_conditions[] = '`'.$modelitem_name.'` IN (?)';
                    $select_values[] = $conditions[$modelitem_name];
                }
            }
        }

        return 1;
    }
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
            $ids[] = (int)$primary_key_values;
            $collected_data[(int)$primary_key_values] = array();
        } else {
            foreach ($primary_key_values as $pkv) {
                if (0 != (int)$pkv) {
                    $ids[] = (int)$pkv;
                    $collected_data[(int)$pkv] = array();
                }
            }
        }

        if (empty($ids)) {
            return false;
        }


        $select_by_query = array();
        $structures = array();
        $has_uploadify_image = false;
        $has_multi_elements = array();
        $uploadify_image_collector = array();
        $fields = array();
        //$externals=array('select_box_structure', 'select_by_query', 'structure', 'uploadify_image', 'uploadify_file', 'tlocation');
        //$no_select=array('auto_add_value', 'uploadify_image', 'uploadify_file', 'captcha', 'spacer_text', 'separator');
        //$direct=array('safe_string', 'hidden', 'primary_key', 'checkbox', 'select_box_structure');
        foreach ($model_array as $model_item) {
            //echo $model_item['name'].'='.$model_item['dbtype'].'<br>';
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
                    if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
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
                case 'select_box' :
                {
                    $fields[] = $model_item['name'];
                    break;
                }
                case 'auto_add_value' :
                {
                    //$fields[]=$model_item['name'];
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
                    //$fields[]=$model_item['name'];
                    $has_uploadify_image = true;
                    break;
                }
                case 'uploadify_file' :
                {
                    //$fields[]=$model_item['name'];
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
                    //$fields[]=$model_item['name'];
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
                    //$fields[]=$model_item['name'];
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
                    //$fields[]=$model_item['name'];
                    break;
                }
                case 'spacer_text' :
                {
                    //$fields[]=$model_item['name'];
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
                    //$this->get_values_list($model_array[$key]['what'], $model_array[$key]['primary_table'], $model_array[$key]['primary_key'], $model_array[$key]['secondary_table'], $model_array[$key]['secondary_key'], $primary_key_value);
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

        if (
            $this->getConfigValue('apps.excel.use_image_cache') == 1 and
            $this->getConfigValue('apps.excel.image_cache_source') == 1 and (
                $table_name == 'data' or $table_name == 'products'
            )
        ) {
            $fields[] = 'image_cache';
        }


        $DBC = DBC::getInstance();
        $query = 'SELECT `' . implode('`, `', $fields) . '` FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE `' . $primary_key_name . '` IN (' . implode(',', $ids) . ')';

        $stmt = $DBC->query($query, array(), $success);
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

        if (!empty($structures)) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php';
            foreach ($structures as $entity => $v) {
                $Manager = Structure_Implements::getManager($entity);
                $structures[$entity] = $Manager->loadCategoryStructure();
            }
        }


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

        if (!empty($has_multi_elements)) {
            $melements_variants = array();
            //var_dump($has_multi_elements);
            $DBC = DBC::getInstance();
            $query = 'SELECT `field_name`, `primary_id`, `field_value` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (\'' . implode('\',\'', array_keys($has_multi_elements)) . '\') AND `primary_id` IN (' . implode(',', $primary_key_values) . ')';

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

                $no_ml = 0;
                if (isset($parameters['no_ml'])) {
                    $no_ml = intval($parameters['no_ml']);
                }
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
                } elseif ($model_item['type'] == 'youtube') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'mobilephone') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'primary_key') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                    //$need_compose_columns[$key] = $key;
                } elseif ($model_item['type'] == 'hidden') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'checkbox') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'select_box_structure') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = @$cs['catalog'][$cdata[$key]]['name'];
                } elseif ($model_item['type'] == 'select_by_query') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = '';
                    if (isset($select_by_query[$key]) && isset($select_by_query[$key]['vals']) && isset($select_by_query[$key]['vals'][$cdata[$key]])) {
                        $model[$key]['value_string'] = $select_by_query[$key]['vals'][$cdata[$key]];
                    }
                } elseif ($model_item['type'] == 'select_by_query_multiple') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['values_array'] = explode(',', $cdata[$key]);
                } elseif ($model_item['type'] == 'select_by_query_multi') {

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
                } elseif ($model_item['type'] == 'select_box') {

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
                } elseif ($model_item['type'] == 'price') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                    //$need_compose_columns[$key] = $key;
                } elseif ($model_item['type'] == 'textarea') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'uploadify_image') {
                    if (isset($uploadify_image_collector[$cdata[$primary_key_name]])) {
                        $model[$key]['value'] = $uploadify_image_collector[$cdata[$primary_key_name]];
                        $model[$key]['value_string'] = $uploadify_image_collector[$cdata[$primary_key_name]];
                        $model[$key]['image_array'] = $uploadify_image_collector[$cdata[$primary_key_name]];
                    } else {
                        $model[$key]['value'] = array();
                        $model[$key]['value_string'] = array();
                        $model[$key]['image_array'] = array();
                    }
                } elseif ($model_item['type'] == 'uploadify_file') {

                } elseif ($model_item['type'] == 'password') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'photo') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'geodata') {
                    $model[$key]['value'] = array();
                    $model[$key]['value']['lat'] = $cdata[$key . '_lat'];
                    $model[$key]['value']['lng'] = $cdata[$key . '_lng'];
                    if ($cdata[$key . '_lat'] != '' && $cdata[$key . '_lng'] != '') {
                        $model[$key]['value_string'] = $cdata[$key . '_lat'] . ',' . $cdata[$key . '_lng'];
                    } else {
                        $model[$key]['value_string'] = '';
                    }
                } elseif ($model_item['type'] == 'structure') {
                    $model[$key]['value'] = $cdata[$key];
                    if (isset($structures[$model_item['entity']])) {
                        $model[$key]['value_string'] = $structures[$model[$key]['entity']]['catalog'][$cdata[$key]]['name'];
                    }
                } elseif ($model_item['type'] == 'textarea_editor') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'date') {
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
                } elseif ($model_item['type'] == 'attachment') {

                } elseif ($model_item['type'] == 'tlocation') {
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
                } elseif ($model_item['type'] == 'dtdatetime') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = Sitebill_Datetime::getDatetimeFormattedFromCanonical($cdata[$key], $parameters);
                    $simplied[$key]['value'] = $cdata[$key];
                    $simplied[$key]['value_string'] = Sitebill_Datetime::getDatetimeFormattedFromCanonical($cdata[$key], $parameters);
                } elseif ($model_item['type'] == 'dtdate') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = Sitebill_Datetime::getDateFormattedFromCanonical($cdata[$key], $parameters);
                } elseif ($model_item['type'] == 'dttime') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = Sitebill_Datetime::getTimeFormattedFromCanonical($cdata[$key], $parameters);
                } elseif ($model_item['type'] == 'gadres') {
                    $model[$key]['value'] = $cdata[$key];
                    $model[$key]['value_string'] = $cdata[$key];
                } elseif ($model_item['type'] == 'grade') {

                } elseif ($model_item['type'] == 'docuploads') {
                    if ($cdata[$key] != '') {
                        $model[$key]['value'] = @unserialize($cdata[$key]);
                        $model[$key]['image_array'] = $model[$key]['value'];
                    }
                } elseif ($model_item['type'] == 'uploads') {
                    if (isset($cdata['image_cache']) && $cdata['image_cache'] != '' && $cdata[$key] == '' && $key == 'image') {
                        $model[$key]['image_cache'] = unserialize($cdata['image_cache']);
                        $model[$key]['value'] = $this->init_image_from_cache($model[$key]['image_cache']);
                        $model[$key]['image_array'] = $model[$key]['value'];
                    } elseif ($cdata[$key] != '') {
                        $model[$key]['value'] = unserialize($cdata[$key]);


                        $model[$key]['value'] = $this->sharder_mirror($model[$key]['value'], true);
                        $model[$key]['image_array'] = $model[$key]['value'];

                    } else {
                        if ($table_name == 'data') {
                            $model[$key]['image_array'] = $this->get_image_array('data', 'data', 'id', $cdata[$primary_key_name]);
                        } else {
                            $model[$key]['value'] = array();
                        }
                    }
                } elseif ($model_item['type'] == 'values_list') {
                } elseif ($model_item['type'] == 'parameter') {
                    if (isset($parameters['type']) && $parameters['type'] == 'json') {
                        $model_array[$key]['value'] = $model[$key]['value_string'] = json_decode($cdata[$key], true);
                    } else {
                        $model_array[$key]['value'] = $model[$key]['value_string'] = unserialize($cdata[$key]);
                    }
                } elseif ($model_item['type'] == 'compose') {
                    //$need_compose_columns[$key] = $key;
                } elseif ($model_item['type'] == 'injector') {
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

    function compile_compose_columns($model, $compose_columns)
    {
        if (count($compose_columns) > 0) {
            foreach ($compose_columns as $idx => $key) {
                $model = $this->init_compose_column($model, $key);
            }
        }
        return $model;
    }

    function can_edit($model, $key)
    {
        if ($this->get_table_name() == 'data' && $this->getConfigValue('apps.realty.data.disable_edit')) {
            return false;
        }
        return true;
    }

    function get_crud_permissions($model, $key)
    {
        $current_user_id = $this->getSessionUserId();
        $component_name = (isset($model[$key]['table_name']) ? $model[$key]['table_name'] : '');
        $crud = array(
            'C' => $this->permission->get_access($current_user_id, $component_name, 'create'),
            'R' => true, // пока всем можно читать
            'U' => $this->permission->get_access($current_user_id, $component_name, 'update'),
            'D' => $this->permission->get_access($current_user_id, $component_name, 'delete'),
        );

        if (isset($model['user_id']) && $current_user_id == $model['user_id']['value']) {
            $crud = array(
                'C' => true,
                'R' => true,
                'U' => $this->can_edit($model, $key),
                'D' => true,
            );
        }

        if ($this->permission->is_admin($current_user_id)) {
            $crud = array(
                'C' => true,
                'R' => true,
                'U' => true,
                'D' => true,
            );
        }

        return $crud;
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


    function filter_danger_names($function_name)
    {
        $danger_functions = array('exec', 'system', 'passthru', 'pcntl_exec', 'popen proc_open', 'shell_exec');
        if (in_array($function_name, $danger_functions)) {
            return true;
        }
        return false;
    }

    function exec_function($function_name, $model, $key)
    {
        if ($this->filter_danger_names($function_name)) {
            return 'function exec failed - banned name ' . $function_name;
        }
        return $function_name($model, $key);
    }

    function init_image_from_cache($image_array)
    {
        foreach ($image_array as $image) {
            if (preg_match('/640x480/', $image) and preg_match('/avito/', $image)) {
                $preview = str_replace('640x480', '208x156', $image);
            } else {
                $preview = $image;
            }
            $ra[] = array('preview' => $preview, 'normal' => $image, 'remote' => 'true');
        }
        return $ra;
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


    function set_table_name($table_name)
    {
        $this->table_name = $table_name;
    }

    function get_table_name()
    {
        return $this->table_name;
    }

    function init_model_data_from_db($table_name, $primary_key_name, $primary_key_value, $model_array, $force_select_values = false)
    {
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
            }

            if ($model_array[$key]['type'] == 'safe_string') {
                $model_array[$key]['value_string'] = (isset($row[$key]) ? $row[$key] : '');
            }
            if ($model_array[$key]['type'] == 'uploadify_image' or $model_array[$key]['type'] == 'uploadify_file') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
            }
            if ( ($model_array[$key]['type'] == 'uploads' || $model_array[$key]['type'] == 'docuploads') && $key == 'image') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                $model_array[$key]['primary_key'] = $primary_key_name;
                $model_array[$key]['table_name'] = $table_name;
                if (isset($row['image_cache']) && $row['image_cache'] != '') {
                    $model_array[$key]['image_cache'] = unserialize($row['image_cache']);
                }
            }
            if ($model_array[$key]['type'] == 'avatar') {
                $model_array[$key]['primary_key_value'] = $primary_key_value;
                $model_array[$key]['primary_key'] = $primary_key_name;
                $model_array[$key]['table_name'] = $table_name;
            }
            if ($model_array[$key]['type'] == 'price') {
                $need_compose_columns[] = $key;
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
            }
            if ($model_array[$key]['type'] == 'select_by_query_multi') {

                $name = $model_array[$key]['value_name'];
                $langs = Multilanguage::availableLanguages();

                $no_ml = 0;
                if (isset($parameters['no_ml'])) {
                    $no_ml = intval($parameters['no_ml']);
                }
                if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === $no_ml) {
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
                        $langs = Multilanguage::availableLanguages();
                        $no_ml = 0;
                        if (isset($parameters['no_ml'])) {
                            $no_ml = intval($parameters['no_ml']);
                        }
                        if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === $no_ml) {
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
                $langs = Multilanguage::availableLanguages();
                $no_ml = 0;
                if (isset($parameters['no_ml'])) {
                    $no_ml = intval($parameters['no_ml']);
                }
                if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === $no_ml) {
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

                if (
                    $this->getConfigValue('apps.excel.use_image_cache') == 1 and
                    $this->getConfigValue('apps.excel.image_cache_source') == 1 and
                    $row['image_cache'] != '' and
                    $model_array[$key]['value'] == '' and
                    $key == 'image'
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


                $langs = Multilanguage::availableLanguages();
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

    function sharder_mirror ($image_array, $from_array = false) {
        if ( !$this->getConfigValue('apps.sharder.mirroring.enable') ) {
            return $image_array;
        }
        if ( $from_array and is_array($image_array) ) {
            foreach ($image_array as $index => $item) {
                $image_array[$index] = $this->replace_one_mirror_item($item);
            }
        } else {
            $image_array = $this->replace_one_mirror_item($image_array);
        }
        return $image_array;
    }

    function replace_one_mirror_item ($image_array) {
        $image_array['normal_url'] =
            str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image_array['normal_url']);
        $image_array['preview'] =
            str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image_array['preview']);
        $image_array['normal'] =
            str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $image_array['normal']);
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
            if ($model_array[$key]['type'] == 'select_by_query' and $force_select_values) {
                $model_array[$key]['value_string'] = $this->get_string_value_by_id($model_array[$key]['primary_key_table'], $model_array[$key]['primary_key_name'], $model_array[$key]['value_name'], $model_array[$key]['value']);
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
                    if (strpos($val, 'youtube.com') !== FALSE) {
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
        }
        return $model_array;
    }

    /**
     * Get string value by ID
     * @param string $primary_key_table
     * @param string $primary_key_name
     * @param string $value_name
     * @param string $primary_key_value
     * @param boolean $cache use cache
     * @return string
     */
    function get_string_value_by_id($primary_key_table, $primary_key_name, $value_name, $value, $cache = false)
    {

        if ($value == '' || $value == '0') {
            return '';
        }
        $DBC = DBC::getInstance();
        if ($cache) {

            if (!isset(self::$cache[$primary_key_table][$value][$value_name])) {
                //exit;
                $value_name = str_replace(' ', '', $value_name);
                $value_name = str_replace('`', '', $value_name);

                $primary_key_name = str_replace('`', '', $primary_key_name);
                $primary_key_name = str_replace(' ', '', $primary_key_name);

                //$query = 'SELECT `'.$primary_key_name.'`, `'.$value_name.'` FROM '.DB_PREFIX.'_'.$primary_key_table.' WHERE `'.$primary_key_name.'` = ?';
                $query = 'SELECT `' . $primary_key_name . '`, `' . $value_name . '` FROM ' . DB_PREFIX . '_' . $primary_key_table . '';

                $stmt = $DBC->query($query, array($value));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        self::$cache[$primary_key_table][$ar[$primary_key_name]][$value_name] = $ar[$value_name];
                    }
                    //echo '<pre>';
                    //print_r($ar);
                    //echo '</pre>';
                }
            }
            return @self::$cache[$primary_key_table][$value][$value_name];
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $primary_key_table . ' WHERE `' . $primary_key_name . '` = ?';

            $stmt = $DBC->query($query, array($value));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                return $ar[$value_name];
            } else {
                return '';
            }
        }
    }

    /**
     * Get string values from outer table by ID
     * @param string $primary_key_table
     * @param string $primary_key_name
     * @param string $value_name
     * @param string $primary_key_value
     * @return string
     */
    function get_values_list($what, $primary_table_name, $primary_key_name, $secondary_table_name, $secondary_key_name, $value)
    {
        $ret = array();
        $query = 'SELECT ' . $what . ' FROM ' . $primary_table_name . ' WHERE ' . $primary_key_name . ' IN (SELECT ' . $primary_key_name . ' FROM ' . $secondary_table_name . ' WHERE ' . $secondary_key_name . '=' . $value . ')';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar[$what];
            }
        }
        if (count($ret) > 0) {
            return implode(', ', $ret);
        } else {
            return '';
        }
    }

    function checkIsValueUnique($value, $field, $table, $primary_key_name, $primary_key_value)
    {
        $DBC = DBC::getInstance();

        if ($primary_key_value != 0) {
            $query = 'SELECT COUNT(*) AS _c FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $field . '`=? AND `' . $primary_key_name . '`!=?';
            $stmt = $DBC->query($query, array($value, $primary_key_value));
        } else {
            $query = 'SELECT COUNT(*) AS _c FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $field . '`=?';
            $stmt = $DBC->query($query, array($value));
        }

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['_c'] > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check data
     * @param array $model_array
     * @return boolean
     */
    function check_data($model_array, &$error_fields = array())
    {
        $errors = array();

        $agreement_el = intval($this->getRequestValue('agreement_el'));
        $agreement = intval($this->getRequestValue('agreement'));
        if ($agreement_el && !$agreement) {
            $errors[] = Multilanguage::_('L_ERROR_AGREEMENT');
        }

        /*
        $session_key = (string)$this->get_session_key();
        $fieldswithuploads = array();
        foreach ($model_array as $form_item) {
            if ($form_item['type'] == 'uploads') {
                if(isset($form_item['parameters']['min_img_count']) && 0 < intval($form_item['parameters']['min_img_count'])){
                    $fieldswithuploads[$form_item['name']] = intval($form_item['parameters']['min_img_count']);
                }

                if(!empty($fieldswithuploads)){
                    foreach ($fieldswithuploads as $fname => $mincount){
                        $ims = $this->load_uploadify_images($session_key, $fname);
                        if(!is_array($ims) || $mincount > count($ims)){
                            $errors[] = 'Согласно правилам сайта, необходимо добавить не менее '.$mincount.' фотографий';
                            $error_fields[$fname][] = 'Согласно правилам сайта, необходимо добавить не менее '.$mincount.' фотографий';
                        }
                    }
                }
            }
        }
        */
        foreach ($model_array as $key => $item_array) {

            $isUnique = false;
            if ($item_array['unique'] == 'on' || $item_array['unique'] == '1') {
                $isUnique = true;
            }

            if ($isUnique && $item_array['value'] != '') {
                $DBC = DBC::getInstance();
                $tname = $item_array['table_name'];
                $query = 'SELECT name FROM ' . DB_PREFIX . '_columns WHERE `type`=? AND table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=? LIMIT 1)';
                $stmt = $DBC->query($query, array('primary_key', $tname));
                $primary_key_value = 0;
                $primary_key_name = '';
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $primary_key_name = $ar['name'];
                    $primary_key_value = intval($model_array[$primary_key_name]['value']);
                }
            }

            if (isset($item_array['parameters'])) {
                $parameters = $item_array['parameters'];
            } else {
                $parameters = array();
            }

            $is_field_required = false;
            if ($model_array[$key]['required'] == 'on') {
                $is_field_required = true;
            }

            $req_off = array();
            if (isset($item_array['parameters']['reqoff']) && $item_array['parameters']['reqoff'] != '') {
                $ro = $item_array['parameters']['reqoff'];
                list($field, $vals) = explode(':', $ro);
                if ($field && $vals) {
                    $vals_array = explode(',', $vals);
                    $req_off[$field] = array();
                    foreach ($vals_array as $vals1) {
                        list($start, $end) = explode('-', $vals1);
                        if ($start !== null && $end !== null) {
                            $mar = range($start, $end);
                            if (is_array($mar)) {
                                $req_off[$field] = array_merge($req_off[$field], $mar);
                            }
                        } else {
                            $req_off[$field][] = trim($vals1);
                        }
                    }
                }
            }

            if (!empty($req_off)) {
                foreach ($req_off as $field => $vals) {
                    $cval = $model_array[$field]['value'];
                    if (in_array($cval, $vals)) {
                        $is_field_required = false;
                    }
                }
            }

            $req_off = array();
            if (isset($item_array['parameters']['reqoff_cond']) && $item_array['parameters']['reqoff_cond'] != '') {
                $ro = $item_array['parameters']['reqoff_cond'];
                $ro_list = explode('|', $ro);
                if (!empty($ro_list)) {
                    foreach ($ro_list as $ro_variant) {
                        list($field, $vals) = explode('=', $ro_variant);
                        if ($field && $vals) {
                            $req_off[] = array($field, $vals);
                        }
                    }
                }
            }

            if ($is_field_required && !empty($req_off)) {
                $s = 1;
                foreach ($req_off as $v) {
                    if ($model_array[$v[0]]['value'] == $v[1]) {
                        $s *= 1;
                    } else {
                        $s *= 0;
                        break;
                    }
                }

                if ($s == 1) {
                    $is_field_required = false;
                }
            }

            $rules = array();
            if (isset($item_array['parameters']['rules']) && $item_array['parameters']['rules'] != '') {
                $rules_string = $item_array['parameters']['rules'];

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

                if (isset($rules['NotBlank']) && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_FIELD_NOT_FILLED') . ' ' . $model_array[$key]['title'];
                }


                switch ($rules['Type']) {
                    case 'string' :
                    {
                        $compare_text = strip_tags($model_array[$key]['value']);
                        $compare_text = str_replace(array("\n", "\r"), '', $compare_text);
                        if (isset($rules['MinLength']) && $rules['MinLength'] !== '') {
                            $min_l = (int)$rules['MinLength'];
                            $compare_text = strip_tags($model_array[$key]['value']);
                            $compare_text = str_replace(array("\n", "\r"), '', $compare_text);
                            if (mb_strlen($compare_text, SITE_ENCODING) < $min_l) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min_l);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min_l);
                            }
                        }
                        if (isset($rules['MaxLength']) && $rules['MaxLength'] !== '') {
                            $max_l = (int)$rules['MaxLength'];
                            if (mb_strlen($compare_text, SITE_ENCODING) > $max_l) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max_l);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max_l);
                            }
                        }
                        if (isset($rules['Email']) && $model_array[$key]['value'] != '' && !$this->validateEmailFormat($model_array[$key]['value'])) {
                            $errors[] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                            $error_fields[$key][] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                        }
                        break;
                    }
                    case 'numeric' :
                    {

                        if ($model_array[$key]['value'] !== '' && preg_match('/([^0-9])/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_NUM'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_NUM'), $model_array[$key]['title']);
                        }
                        if (isset($rules['MinLength']) && $rules['MinLength'] !== '') {
                            $min = (int)$rules['MinLength'];
                            if ($model_array[$key]['value'] != '' && strlen($model_array[$key]['value']) < $min) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MINLENGTH'), $model_array[$key]['title'], $min);
                            }
                        }
                        if (isset($rules['MaxLength']) && $rules['MaxLength'] !== '') {
                            $max = (int)$rules['MaxLength'];
                            if ($model_array[$key]['value'] != '' && strlen($model_array[$key]['value']) > $max) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_MAXLENGTH'), $model_array[$key]['title'], $max);
                            }
                        }
                        break;
                    }
                    case 'int' :
                    {

                        if ($model_array[$key]['value'] !== '' && !preg_match('/^[-+]?[0-9]*$/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_INT'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_INT'), $model_array[$key]['title']);
                        }
                        if (isset($rules['Min']) && $rules['Min'] !== '') {
                            $min = (int)$rules['Min'];
                            if ((int)$model_array[$key]['value'] != 0 && (int)$model_array[$key]['value'] < $min) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                            }
                        }
                        if (isset($rules['Max']) && $rules['Max'] !== '') {
                            $max = (int)$rules['Max'];
                            if ((int)$model_array[$key]['value'] > $max) {
                                $errors[] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                                $error_fields[$key][] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                            }
                        }
                        break;
                    }
                    case 'decimal' :
                    {

                        if ($model_array[$key]['value'] !== '' && !preg_match('/^[-+]?[0-9]*[.]?[0-9]+$/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_DEC'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_FORMAT_INVALID_DEC'), $model_array[$key]['title']);
                        }
                        if (isset($rules['Min']) && $rules['Min'] !== '') {
                            $min = (float)$rules['Min'];
                            //echo $min;
                            if (trim($model_array[$key]['value']) != '' && (float)$model_array[$key]['value'] < $min) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_VALUE_CANTBE_LESS'), $model_array[$key]['title'], $min);
                            }
                        }
                        if (isset($rules['Max']) && $rules['Max'] !== '') {
                            $max = (float)$rules['Max'];
                            if ((float)$model_array[$key]['value'] > $max) {
                                $errors[] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                                $error_fields[$key][] = 'Значения поля ' . $model_array[$key]['title'] . ' не может быть больше ' . $max;
                            }
                        }
                        break;
                    }
                }
            }


            if ($model_array[$key]['type'] == 'safe_string' || $model_array[$key]['type'] == 'textarea') {
                if ($is_field_required && $model_array[$key]['value'] == '') {
                    //$this->riseError(sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']));
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_FIELD_NOT_FILLED') . ' ' . $model_array[$key]['title'];
                }
            } elseif ($model_array[$key]['type'] == 'captcha') {
                $captcha_type = $this->getConfigValue('captcha_type');
                if ($captcha_type == 2) {

                } elseif ($captcha_type == 4) {
                    $recaptcha_token = $this->getRequestValue('g-recaptcha-response');

                    $url = 'https://www.google.com/recaptcha/api/siteverify';
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, "secret=6LfB4TgUAAAAALr-PM6PzvF5Hi5vXLQM93jpGHlJ&response=" . $recaptcha_token);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($curl);
                    curl_close($curl);
                    $resp = json_decode($result, true);
                    if (!isset($resp['success']) || !$resp['success']) {
                        $errors[] = Multilanguage::_('L_ERROR_RECAPTCHA');
                        //$errors[] = Multilanguage::_('L_ERROR_CAPTCHA_INVALID');
                        $error_fields[$key][] = Multilanguage::_('L_ERROR_RECAPTCHA');
                    }
                } else {
                    if ($model_array[$key]['value'] == '' || $model_array[$key]['value'] != $_SESSION[$this->getRequestValue('captcha_session_key')]) {
                        //$this->riseError(Multilanguage::_('L_ERROR_CAPTCHA_INVALID'));
                        $errors[] = Multilanguage::_('L_ERROR_CAPTCHA_INVALID');
                        $error_fields[$key][] = Multilanguage::_('L_ERROR_CAPTCHA_INVALID');
                        //return false;
                    }
                }
            } elseif ($model_array[$key]['type'] == 'email') {
                if ($is_field_required && $model_array[$key]['value'] == '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
                if ($model_array[$key]['value'] != '' && !$this->validateEmailFormat($model_array[$key]['value'])) {
                    $errors[] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_EMAIL_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                }
            } elseif ($model_array[$key]['type'] == 'mobilephone') {
                if ($is_field_required && $model_array[$key]['value'] == '') {
                    $this->riseError(sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']));
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
                if (isset($parameters['mask'])) {
                    $mask = $parameters['mask'];
                    $mask = preg_replace('/[^h\d]/', '', $mask);
                    if ($mask != '') {
                        $mask = str_replace('h', '\d', $mask);
                    } else {
                        $mask = '';
                    }
                } else {
                    $mask = '';
                }

                if (($model_array[$key]['value'] != '') && (!$this->validateMobilePhoneNumberFormat($model_array[$key]['value'], $mask))) {
                    $errors[] = Multilanguage::_('L_ERROR_PHONE_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                    $error_fields[$key][] = Multilanguage::_('L_ERROR_PHONE_FORMAT_INVALID') . ' ' . $model_array[$key]['title'];
                }
            } elseif ($model_array[$key]['type'] == 'select_box_structure_simple_multiple') {
                if ($is_field_required && count($model_array[$key]['values_array']) == 0) {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'select_box_structure') {
                if ( $is_field_required and $model_array[$key]['value'] == 0 ) {
                    $errors[] = _e('Укажите тип недвижимости') . ': ' . $model_array[$key]['title'];
                    $error_fields[$key][] = _e('Укажите тип недвижимости') . ': ' . $model_array[$key]['title'];
                }
                if ( @$parameters['level_required'] > 0 ) {
                    if ( ($model_array[$key]['value'] != '') && (!$this->validateLevelRequired($model_array[$key]['value'], $parameters['level_required'])) ) {
                        $errors[] = _e('Укажите подтип недвижимости') . ': ' . $model_array[$key]['title'];
                        $error_fields[$key][] = _e('Укажите подтип недвижимости') . ': ' . $model_array[$key]['title'];
                    }
                }

            } elseif ($model_array[$key]['type'] == 'select_by_query_multiple') {
                if ($is_field_required && count($model_array[$key]['values_array']) == 0) {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'dtdatetime') {
                if ($is_field_required && $model_array[$key]['value'] !== '' && !Sitebill_Datetime::checkDTDatetime($model_array[$key]['value'], $model_array[$key]['parameters'])) {
                    $errors[] = 'Invalid date format on field ' . $model_array[$key]['title'];
                } elseif ($is_field_required && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'dtdate') {
                if ($is_field_required && $model_array[$key]['value'] !== '' && !Sitebill_Datetime::checkDTDatetime($model_array[$key]['value'], $model_array[$key]['parameters'])) {
                    $errors[] = 'Invalid date format on field ' . $model_array[$key]['title'];
                } elseif ($is_field_required && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } elseif ($model_array[$key]['type'] == 'dttime') {
                if ($is_field_required && $model_array[$key]['value'] !== '' && !Sitebill_Datetime::checkDTTime($model_array[$key]['value'], $model_array[$key]['parameters'])) {
                    $errors[] = 'Invalid date format on field ' . $model_array[$key]['title'];
                } elseif ($is_field_required && $model_array[$key]['value'] === '') {
                    $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                }
            } else {
                if ($is_field_required) {
                    if (!is_array($model_array[$key]['value'])) {
                        if (!preg_match('/.+/', $model_array[$key]['value']) || preg_match('/^[0]$/', $model_array[$key]['value'])) {
                            $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                            $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                        }
                    } elseif (empty($model_array[$key]['value'])) {
                        $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                        $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                    } else {
                        $values = $model_array[$key]['value'];
                        foreach ($values as $value) {
                            if (!preg_match('/.+/', $value) || preg_match('/^[0]$/', $value)) {
                                $errors[] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                                $error_fields[$key][] = sprintf(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED'), $model_array[$key]['title']);
                            }
                        }
                    }
                }
            }

            if ($isUnique && $item_array['value'] != '' && !$this->checkIsValueUnique($item_array['value'], $item_array['name'], $item_array['table_name'], $primary_key_name, $primary_key_value)) {

                $errors[] = _e('Значение поля не уникально: ') . ' ' . $model_array[$key]['title'];
                $error_fields[$key][] = _e('Значение поля не уникально: ') . ' ' . $model_array[$key]['title'];
            }
        }
        if (!empty($errors)) {
            $this->riseError(implode('<br />', $errors));
            return false;
        }
        return true;
    }

    function forse_auto_add_values(&$model_array)
    {
        foreach ($model_array as $key => $item_array) {
            if ($item_array['type'] == 'auto_add_value' and $item_array['value'] != '') {
                $id = $this->get_value_id_by_name($item_array['value_table'], $item_array['value_field'], $item_array['value_primary_key'], strip_tags($item_array['value']));
                if ($id === FALSE) {
                    $id = 0;
                    $DBC = DBC::getInstance();
                    $query = 'INSERT INTO ' . DB_PREFIX . '_' . $item_array['value_table'] . ' (`' . $item_array['value_field'] . '`) VALUES (?)';
                    $stmt = $DBC->query($query, array(strip_tags($item_array['value'])));
                    if ($stmt) {
                        $id = $DBC->lastInsertId();
                    }

                    if ($id != 0) {
                        $model_array[$item_array['assign_to']]['value'] = $id;
                    }
                } else {
                    $model_array[$item_array['assign_to']]['value'] = $id;
                }
            }
        }
    }

    function forse_autocalc_values(&$model_array)
    {
    }

    function forse_injected_values(&$model_array)
    {
    }

    /**
     * Получить ID записи по значению одного из столбцов
     * @params $table - название таблицы
     * @params $filed - название столбца из таблицы, по которому ведем поиск
     * @params $primary_key - название ключа таблицы (ID)
     * @params $value - значение для поиска
     * @params $filters - необязательный параметр устанавливает фильтры для условия выборки
     * @return возвращаем ID записи или FALSE если запись не найдена
     */
    function get_value_id_by_name($table, $field, $primary_key, $value, $filters = array())
    {
        $query_params = array();
        $query_values = array();

        $query_params[] = '`' . $field . '`=?';
        $query_values[] = $value;

        if (!empty($filters)) {
            foreach ($filters as $k => $op) {
                $query_params[] = '`' . $k . '`=?';
                $query_values[] = $op;
            }
        }

        $DBC = DBC::getInstance();
        if ($this->getConfigValue('use_metaphone')) {
            $metaphone = $this->mtphn($value);
            $query = 'SELECT ' . $primary_key . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE ' . 'damlevlim("' . $metaphone . '",metaphone,20)<2 LIMIT 1';
            $this->writeLog(__METHOD__ . ' use_metaphone ' . "table = $table, field = $field, primary_key = $primary_key, value = $value, metaphone = $metaphone");
        } else {
            $query = 'SELECT ' . $primary_key . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE ' . implode(' AND ', $query_params);
        }

        $stmt = $DBC->query($query, $query_values);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar[$primary_key] != 0) {
                if ($this->getConfigValue('use_metaphone')) {
                    $this->writeLog(__METHOD__ . ' metaphone result = ' . $ar[$primary_key]);
                }
                return $ar[$primary_key];
            } else {
                return FALSE;
            }
        } else {
            //echo $DBC->getLastError();
            return FALSE;
        }
    }

    public function add_new_record($table, $field, $primary_key, $value)
    {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $table . ' (`' . $field . '`) VALUES (?)';
        //echo $query.'<br>';
        $stmt = $DBC->query($query, array($value));
        $this->writeLog($DBC->getLastError());
        if ($stmt) {
            return true;
        }
        return false;
    }

    /**
     * Get insert query
     * @param string $table_name table name
     *
     * @param array $model_array
     * @param int $language_id
     * @return boolean
     */
    function get_insert_query($table_name, $model_array, $language_id = 0)
    {
        $set = array();
        $values = array();
        unset($model_array['image']);

        foreach ($model_array as $key => $item_array) {


            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];

                //echo "primary_key = $primary_key<br>";
                //echo "value = ".$model_array[$primary_key]['value'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads' || $item_array['type'] == 'docuploads') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'datetime') {
                $set[] = '`' . $key . '`';
                $values[] = "'" . Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']) . "'";
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                $set[] = "`" . $key . "`";
                //$values[]="'".Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $values[] = "'" . $item_array['value'] . "'";
                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                $set[] = "`" . $key . "`";
                //$values[]="'".Sitebill_Datetime::getDateCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $values[] = "'" . $item_array['value'] . "'";
                continue;
            }
            if ($item_array['type'] == 'dttime') {
                $set[] = "`" . $key . "`";
                //$values[]="'".Sitebill_Datetime::getTimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $values[] = "'" . $item_array['value'] . "'";
                continue;
            }
            if ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0') {

                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }


                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $set[] = '`' . $k . '`';
                                    $values[] = "'" . (int)$v . "'";
                                }
                            } else {
                                $set[] = '`' . $k . '`';
                                $values[] = "'" . (int)$v . "'";
                            }
                        }
                    }
                }
                continue;
            }

            if ($item_array['type'] == 'geodata') {
                $set[] = '`' . $key . '_lat`';
                if ($item_array['value']['lat'] == '') {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $this->escape($item_array['value']['lat']) . "'";
                }

                $set[] = '`' . $key . '_lng`';

                if ($item_array['value']['lng'] == '') {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $this->escape($item_array['value']['lng']) . "'";
                }
                continue;
            }

            $set[] = '`' . $key . '`';
            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            $values[] = "'" . $this->escape($item_array['value']) . "'";
        }
        //echo "primary_key = $primary_key<br>";
        //echo '$this->getRequestValue($primary_key) = '.$this->getRequestValue($primary_key).'<br>';
        if ($language_id > 0) {
            $set[] = '`language_id`';
            $values[] = "'" . $language_id . "'";
            $set[] = '`link_id`';
            $values[] = "'" . $this->getRequestValue($primary_key) . "'";
        }
        $query = "insert into $table_name (" . implode(' , ', $set) . ") values (" . implode(' , ', $values) . ")";
        //echo $query;
        return $query;
    }

    function get_prepared_insert_query($table_name, $model_array, $language_id = 0)
    {

        $set = array();
        $values = array();
        unset($model_array['image']);
        $qparts = array();
        $qvals = array();

        foreach ($model_array as $key => $item_array) {
            if ( !isset($item_array['type']) ) {
                $item_array['type'] = '';
            }

            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'select_by_query_multi') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'parameter') {
                $qparts[] = '`' . $key . '`';

                if (isset($item_array['parameters']) && isset($item_array['parameters']['type']) && $item_array['parameters']['type'] == 'json') {
                    $qvals[] = json_encode($item_array['value']);
                } else {
                    $qvals[] = serialize($item_array['value']);
                }
                continue;
            }

            if ($item_array['type'] == 'datetime') {
                $qparts[] = '`' . $key . '`';
                $qvals[] = Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']);
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                $qparts[] = "`" . $key . "`";
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                $qparts[] = "`" . $key . "`";
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dttime') {
                $qparts[] = "`" . $key . "`";
                $qvals[] = $item_array['value'];
                continue;
            }
            if (isset($item_array['dbtype']) && ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0')) {
                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }


                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $qparts[] = "`" . $k . "`";
                                    $qvals[] = (int)$v;
                                }
                            } else {
                                $qparts[] = "`" . $k . "`";
                                $qvals[] = (int)$v;
                            }
                        }
                    }
                }
                continue;
            }

            if ($item_array['type'] == 'geodata') {
                //$qparts[] = "`".$key."_lat`";
                //$qvals[] = $item_array['value'];
                if (!isset($item_array['value']['lat']) || $item_array['value']['lat'] == '') {
                    //$values[] = "NULL";
                } else {
                    $qparts[] = "`" . $key . "_lat`";
                    $qvals[] = $this->escape($item_array['value']['lat']);
                }

                //$set[] = '`'.$key.'_lng`';

                if (!isset($item_array['value']['lng']) || $item_array['value']['lng'] == '') {
                    //$values[] = "NULL";
                } else {
                    $qparts[] = "`" . $key . "_lng`";
                    $qvals[] = $this->escape($item_array['value']['lng']);
                    //$values[] = "'".$this->escape($item_array['value']['lng'])."'";
                }
                continue;
            }
            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            //$values[] = "'".$this->escape($item_array['value'])."'";
            $qparts[] = "`" . $key . "`";
            $qvals[] = $this->escape($item_array['value']);
        }
        //echo "primary_key = $primary_key<br>";
        //echo '$this->getRequestValue($primary_key) = '.$this->getRequestValue($primary_key).'<br>';
        if ($language_id > 0) {
            //$set[] = '`language_id`';
            //$values[] = "'".$language_id."'";
            $qparts[] = "`language_id`";
            $qvals[] = $language_id;
            //$set[] = '`link_id`';
            //$values[] = "'".$this->getRequestValue($primary_key)."'";
            $qparts[] = "`link_id`";
            $qvals[] = $this->getRequestValue($primary_key);
        }
        //print_r($qparts);
        //print_r($qvals);
        //echo count($qvals);
        $count = count($qvals);
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $query = 'INSERT INTO ' . $table_name . ' (' . implode(' , ', $qparts) . ') VALUES (' . implode(', ', array_fill(0, $count, '?')) . ')';

        return array('q' => $query, 'p' => $qvals);
        return $query;
    }

    function get_prepared_edit_query($table_name, $primary_key_name, $primary_key_value, $model_array, $language_id = 0)
    {
        unset($model_array['image']);
        $qparts = array();
        $qvals = array();
        foreach ($model_array as $key => $item_array) {
            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads' || $item_array['type'] == 'docuploads') {
                continue;
            }

            if ($item_array['type'] == 'avatar') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'select_by_query_multi') {
                continue;
            }
            if ($item_array['type'] == 'parameter') {
                $qparts[] = '`' . $key . '`=?';
                if (isset($item_array['parameters']) && isset($item_array['parameters']['type']) && $item_array['parameters']['type'] == 'json') {
                    $qvals[] = json_encode($item_array['value']);
                } else {
                    $qvals[] = serialize($item_array['value']);
                }
                continue;
            }

            if ($item_array['type'] == 'datetime') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']);
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = $item_array['value'];
                continue;
            }
            if ($item_array['type'] == 'dttime') {
                $qparts[] = '`' . $key . '`=?';
                $qvals[] = $item_array['value'];
                continue;
            }
            if (isset($item_array['dbtype']) && ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0')) {
                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }

                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $qparts[] = '`' . $k . '`=?';
                                    $qvals[] = (int)$v;
                                }
                            } else {
                                $qparts[] = '`' . $k . '`=?';
                                $qvals[] = (int)$v;
                            }
                        }
                    }
                }
                continue;
            }
            if ($item_array['type'] == 'geodata') {

                if (@$item_array['value']['lat'] == '') {
                    $qparts[] = '`' . $key . '_lat`=NULL';
                } else {
                    $qparts[] = '`' . $key . '_lat`=?';
                    $qvals[] = $this->escape($item_array['value']['lat']);
                }

                if (@$item_array['value']['lng'] == '') {
                    $qparts[] = '`' . $key . '_lng`=NULL';
                } else {
                    $qparts[] = '`' . $key . '_lng`=?';
                    $qvals[] = $this->escape($item_array['value']['lng']);
                }


                continue;
            }


            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            $qparts[] = '`' . $key . '`=?';
            $qvals[] = $this->escape($item_array['value']);
        }
        if ($language_id > 0) {

            $qparts[] = '`language_id`=?';
            $qvals[] = $language_id;

            $qparts[] = '`link_id`=?';
            $qvals[] = $this->getRequestValue($primary_key);

            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $qparts) . ' WHERE `link_id`=' . $primary_key_value;
        } else {
            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $qparts) . ' WHERE `' . $primary_key_name . '`=' . $primary_key_value;
        }

        return array('q' => $query, 'p' => $qvals);
    }

    /**
     * Get edit query
     * @param string $table_name table name
     * @param string $primary_key_name primary key name
     * @param int $primary_key_value primary key
     * @param array $model_array
     * @param int $language_id
     * @return boolean
     */
    function get_edit_query($table_name, $primary_key_name, $primary_key_value, $model_array, $language_id = 0)
    {
        unset($model_array['image']);

        //$set = array();
        //$values = array();
        $pairs = array();

        foreach ($model_array as $key => $item_array) {
            if ($item_array['type'] == 'primary_key') {
                $primary_key = $item_array['name'];
                continue;
            }

            if ($item_array['type'] == 'separator') {
                continue;
            }

            if ($item_array['type'] == 'spacer_text') {
                continue;
            }

            if ($item_array['type'] == 'uploads') {
                continue;
            }

            if ($item_array['type'] == 'photo') {
                continue;
            }
            if ($item_array['type'] == 'datetime') {
                $pairs[] = "`" . $key . "` = '" . Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters']) . "'";
                continue;
            }
            if ($item_array['type'] == 'dtdatetime') {
                //$pairs[] = "`".$key."` = '".Sitebill_Datetime::getDatetimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $pairs[] = "`" . $key . "` = '" . $item_array['value'] . "'";

                continue;
            }
            if ($item_array['type'] == 'dtdate') {
                //$pairs[] = "`".$key."` = '".Sitebill_Datetime::getDateCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $pairs[] = "`" . $key . "` = '" . $item_array['value'] . "'";

                continue;
            }
            if ($item_array['type'] == 'dttime') {
                //$pairs[] = "`".$key."` = '".Sitebill_Datetime::getTimeCanonicalFromFormat($item_array['value'], $item_array['parameters'])."'";
                $pairs[] = "`" . $key . "` = '" . $item_array['value'] . "'";

                continue;
            }
            if ($item_array['dbtype'] == 'notable' || $item_array['dbtype'] == '0') {
                if ($item_array['type'] == 'tlocation') {

                    if (isset($item_array['parameters']['visibles'])) {
                        $visibles = explode('|', $item_array['parameters']['visibles']);
                    } else {
                        $visibles = array();
                    }

                    if (!empty($item_array['value'])) {
                        foreach ($item_array['value'] as $k => $v) {
                            if (!empty($visibles)) {
                                if (in_array($k, $visibles)) {
                                    $pairs[] = '`' . $k . '` = ' . (int)$v;
                                }
                            } else {
                                $pairs[] = '`' . $k . '` = ' . (int)$v;
                            }
                        }
                    }
                }
                continue;
            }
            if ($item_array['type'] == 'geodata') {
                if ($item_array['value']['lat'] == '') {
                    $pairs[] = '`' . $key . '_lat` = NULL';
                } else {
                    $pairs[] = '`' . $key . '_lat` = ' . "'" . $this->escape($item_array['value']['lat']) . "'";
                }

                if ($item_array['value']['lng'] == '') {
                    $pairs[] = '`' . $key . '_lng` = NULL';
                } else {
                    $pairs[] = '`' . $key . '_lng` = ' . "'" . $this->escape($item_array['value']['lng']) . "'";
                }


                continue;
            }


            $item_array['value'] = preg_replace('/<script.*\/script>/', '', $item_array['value']);
            $pairs[] = '`' . $key . '` = ' . "'" . $this->escape($item_array['value']) . "'";
        }
        if ($language_id > 0) {

            $pairs[] = '`language_id` = ' . "'" . $language_id . "'";
            $pairs[] = '`link_id` = ' . "'" . $this->getRequestValue($primary_key) . "'";
            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $pairs) . ' WHERE `link_id`=' . $primary_key_value;
        } else {
            $query = 'UPDATE `' . $table_name . '` SET ' . implode(', ', $pairs) . ' WHERE `' . $primary_key_name . '`=' . $primary_key_value;
        }

        //echo $query;
        return $query;
    }

    /**
     * Get city model
     * @param
     * @return
     */
    function get_city_model()
    {
        $form_city = array();

        $form_city['city']['city_id']['name'] = 'city_id';
        $form_city['city']['city_id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_city['city']['city_id']['value'] = 0;
        $form_city['city']['city_id']['length'] = 40;
        $form_city['city']['city_id']['type'] = 'primary_key';
        $form_city['city']['city_id']['required'] = 'off';
        $form_city['city']['city_id']['unique'] = 'off';

        $form_city['city']['region_id']['name'] = 'region_id';
        $form_city['city']['region_id']['primary_key_name'] = 'region_id';
        $form_city['city']['region_id']['primary_key_table'] = 'region';
        $form_city['city']['region_id']['title'] = Multilanguage::_('L_REGION');
        $form_city['city']['region_id']['value'] = 0;
        $form_city['city']['region_id']['length'] = 40;
        $form_city['city']['region_id']['type'] = 'select_by_query';
        $form_city['city']['region_id']['query'] = 'select * from ' . DB_PREFIX . '_region order by name';
        $form_city['city']['region_id']['value_name'] = 'name';
        $form_city['city']['region_id']['title_default'] = Multilanguage::_('L_CHOOSE_REGION');
        $form_city['city']['region_id']['value_default'] = 0;
        $form_city['city']['region_id']['required'] = 'off';
        $form_city['city']['region_id']['unique'] = 'off';

        $form_city['city']['name']['name'] = 'name';
        $form_city['city']['name']['title'] = Multilanguage::_('L_TEXT_TITLE');
        $form_city['city']['name']['value'] = '';
        $form_city['city']['name']['length'] = 40;
        $form_city['city']['name']['type'] = 'safe_string';
        $form_city['city']['name']['required'] = 'on';
        $form_city['city']['name']['unique'] = 'off';
        if ($this->getConfigValue('theme') == 'etown') {
            $form_city['city']['geo']['name'] = 'geo';
            $form_city['city']['geo']['title'] = Multilanguage::_('L_GEO_COORDS');
            $form_city['city']['geo']['value'] = '';
            $form_city['city']['geo']['length'] = 40;
            $form_city['city']['geo']['type'] = 'geodata';
            $form_city['city']['geo']['required'] = 'off';
            $form_city['city']['geo']['unique'] = 'off';
        }

        $form_data = array();
        $table_name = 'city';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data[$table_name])) {
                $form_data = array();
                $form_data = $form_city;
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $form_city;
            //$form_data = $this->_get_big_city_kvartira_model2($ajax);
        }

        return $form_data;
    }

    /**
     * Get region model
     * @param
     * @return
     */
    function get_region_model()
    {
        $form_region = array();

        $form_region['region']['region_id']['name'] = 'region_id';
        $form_region['region']['region_id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_region['region']['region_id']['value'] = 0;
        $form_region['region']['region_id']['length'] = 40;
        $form_region['region']['region_id']['type'] = 'primary_key';
        $form_region['region']['region_id']['required'] = 'off';
        $form_region['region']['region_id']['unique'] = 'off';

        $form_region['region']['country_id']['name'] = 'country_id';
        $form_region['region']['country_id']['primary_key_table'] = 'country';
        $form_region['region']['country_id']['primary_key_name'] = 'country_id';
        $form_region['region']['country_id']['primary_key_table'] = 'country';
        $form_region['region']['country_id']['title'] = 'Страна';
        $form_region['region']['country_id']['value'] = 0;
        $form_region['region']['country_id']['length'] = 40;
        $form_region['region']['country_id']['type'] = 'select_by_query';
        $form_region['region']['country_id']['query'] = 'select * from ' . DB_PREFIX . '_country order by name';
        $form_region['region']['country_id']['value_name'] = 'name';
        $form_region['region']['country_id']['title_default'] = Multilanguage::_('L_CHOOSE_COUNTRY');
        $form_region['region']['country_id']['value_default'] = 0;
        $form_region['region']['country_id']['required'] = 'off';
        $form_region['region']['country_id']['unique'] = 'off';

        $form_region['region']['name']['name'] = 'name';
        $form_region['region']['name']['title'] = Multilanguage::_('L_TEXT_TITLE');
        $form_region['region']['name']['value'] = '';
        $form_region['region']['name']['length'] = 40;
        $form_region['region']['name']['type'] = 'safe_string';
        $form_region['region']['name']['required'] = 'on';
        $form_region['region']['name']['unique'] = 'off';
        $form_data = array();
        $table_name = 'region';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data[$table_name])) {
                $form_data = array();
                $form_data = $form_region;
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $form_region;
        }
        return $form_data;
    }

    /**
     * Get district model
     * @param
     * @return
     */
    function get_district_model()
    {
        $form_district = array();

        $form_district['district']['id']['name'] = 'id';
        $form_district['district']['id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_district['district']['id']['value'] = 0;
        $form_district['district']['id']['length'] = 40;
        $form_district['district']['id']['type'] = 'primary_key';
        $form_district['district']['id']['required'] = 'off';
        $form_district['district']['id']['unique'] = 'off';

        $form_district['district']['city_id']['name'] = 'city_id';
        $form_district['district']['city_id']['primary_key_table'] = 'city';
        $form_district['district']['city_id']['primary_key_name'] = 'city_id';
        $form_district['district']['city_id']['primary_key_table'] = 'city';
        $form_district['district']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_district['district']['city_id']['value'] = 0;
        $form_district['district']['city_id']['length'] = 40;
        $form_district['district']['city_id']['type'] = 'select_by_query';
        $form_district['district']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_district['district']['city_id']['value_name'] = 'name';
        $form_district['district']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_district['district']['city_id']['value_default'] = 0;
        $form_district['district']['city_id']['required'] = 'off';
        $form_district['district']['city_id']['unique'] = 'off';

        $form_district['district']['name']['name'] = 'name';
        $form_district['district']['name']['title'] = Multilanguage::_('L_TEXT_TITLE');
        $form_district['district']['name']['value'] = '';
        $form_district['district']['name']['length'] = 40;
        $form_district['district']['name']['type'] = 'safe_string';
        $form_district['district']['name']['required'] = 'on';
        $form_district['district']['name']['unique'] = 'off';

        $form_data = array();
        $table_name = 'district';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data[$table_name])) {
                $form_data = array();
                $form_data = $form_district;
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $form_district;
        }
        return $form_data;
    }

    function try_get_model_from_db($table_name, $exist_model)
    {
        $form_data = array();
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data[$table_name])) {
                $form_data = array();
                $form_data = $exist_model;
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $exist_model;
            //$form_data = $this->_get_big_city_kvartira_model2($ajax);
        }
        return $form_data;
    }

    /**
     * Get metro model
     * @param
     * @return
     */
    function get_metro_model()
    {
        $form_metro = array();

        $form_metro['metro']['metro_id']['name'] = 'metro_id';
        $form_metro['metro']['metro_id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_metro['metro']['metro_id']['value'] = 0;
        $form_metro['metro']['metro_id']['length'] = 40;
        $form_metro['metro']['metro_id']['type'] = 'primary_key';
        $form_metro['metro']['metro_id']['required'] = 'off';
        $form_metro['metro']['metro_id']['unique'] = 'off';

        $form_metro['metro']['city_id']['name'] = 'city_id';
        $form_metro['metro']['city_id']['primary_key_table'] = 'city';
        $form_metro['metro']['city_id']['primary_key_name'] = 'city_id';
        $form_metro['metro']['city_id']['primary_key_table'] = 'city';
        $form_metro['metro']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_metro['metro']['city_id']['value'] = 0;
        $form_metro['metro']['city_id']['length'] = 40;
        $form_metro['metro']['city_id']['type'] = 'select_by_query';
        $form_metro['metro']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_metro['metro']['city_id']['value_name'] = 'name';
        $form_metro['metro']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_metro['metro']['city_id']['value_default'] = 0;
        $form_metro['metro']['city_id']['required'] = 'off';
        $form_metro['metro']['city_id']['unique'] = 'off';

        $form_metro['metro']['name']['name'] = 'name';
        $form_metro['metro']['name']['title'] = Multilanguage::_('L_TEXT_TITLE');
        $form_metro['metro']['name']['value'] = '';
        $form_metro['metro']['name']['length'] = 40;
        $form_metro['metro']['name']['type'] = 'safe_string';
        $form_metro['metro']['name']['required'] = 'on';
        $form_metro['metro']['name']['unique'] = 'off';

        return $form_metro;
    }

    /**
     * Get street model
     * @param
     * @return
     */
    function get_street_model()
    {
        $form_street = array();

        $form_street['street']['street_id']['name'] = 'street_id';
        $form_street['street']['street_id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_street['street']['street_id']['value'] = 0;
        $form_street['street']['street_id']['length'] = 40;
        $form_street['street']['street_id']['type'] = 'primary_key';
        $form_street['street']['street_id']['required'] = 'off';
        $form_street['street']['street_id']['unique'] = 'off';

        //if($this->getConfigValue('link_street_to_city')){
        $form_street['street']['city_id']['name'] = 'city_id';
        $form_street['street']['city_id']['primary_key_table'] = 'city';
        $form_street['street']['city_id']['primary_key_name'] = 'city_id';
        $form_street['street']['city_id']['primary_key_table'] = 'city';
        $form_street['street']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_street['street']['city_id']['value'] = 0;
        $form_street['street']['city_id']['length'] = 40;
        $form_street['street']['city_id']['type'] = 'select_by_query';
        $form_street['street']['city_id']['query'] = 'select city_id, name  from ' . DB_PREFIX . '_city order by name';
        $form_street['street']['city_id']['value_name'] = 'name';
        $form_street['street']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_street['street']['city_id']['value_default'] = 0;
        $form_street['street']['city_id']['required'] = 'off';
        $form_street['street']['city_id']['unique'] = 'off';
        //}else{
        $form_street['street']['district_id']['name'] = 'district_id';
        $form_street['street']['district_id']['primary_key_table'] = 'district';
        $form_street['street']['district_id']['primary_key_name'] = 'id';
        $form_street['street']['district_id']['primary_key_table'] = 'district';
        $form_street['street']['district_id']['title'] = Multilanguage::_('L_DISTRICT');
        $form_street['street']['district_id']['value'] = 0;
        $form_street['street']['district_id']['length'] = 40;
        $form_street['street']['district_id']['type'] = 'select_by_query';
        $form_street['street']['district_id']['query'] = 'select d.id, CONCAT_WS(\'/\',d.name,c.name) as name  from ' . DB_PREFIX . '_district d LEFT JOIN ' . DB_PREFIX . '_city c ON d.city_id=c.city_id order by name';
        $form_street['street']['district_id']['value_name'] = 'name';
        $form_street['street']['district_id']['title_default'] = Multilanguage::_('L_CHOOSE_DISTRICT');
        $form_street['street']['district_id']['value_default'] = 0;
        $form_street['street']['district_id']['required'] = 'off';
        $form_street['street']['district_id']['unique'] = 'off';
        //}


        $form_street['street']['name']['name'] = 'name';
        $form_street['street']['name']['title'] = Multilanguage::_('L_TEXT_TITLE');
        $form_street['street']['name']['value'] = '';
        $form_street['street']['name']['length'] = 40;
        $form_street['street']['name']['type'] = 'safe_string';
        $form_street['street']['name']['required'] = 'on';
        $form_street['street']['name']['unique'] = 'off';

        $form_street = $this->try_get_model_from_db('street', $form_street);

        return $form_street;
    }

    /**
     * Get country model
     * @param
     * @return
     */
    function get_country_model()
    {
        $form_country = array();

        $form_country['country']['country_id']['name'] = 'country_id';
        $form_country['country']['country_id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_country['country']['country_id']['value'] = 0;
        $form_country['country']['country_id']['length'] = 40;
        $form_country['country']['country_id']['type'] = 'primary_key';
        $form_country['country']['country_id']['required'] = 'off';
        $form_country['country']['country_id']['unique'] = 'off';

        $form_country['country']['name']['name'] = 'name';
        $form_country['country']['name']['title'] = Multilanguage::_('L_TEXT_TITLE');
        $form_country['country']['name']['value'] = '';
        $form_country['country']['name']['length'] = 40;
        $form_country['country']['name']['type'] = 'safe_string';
        $form_country['country']['name']['required'] = 'on';
        $form_country['country']['name']['unique'] = 'off';

        $form_country['country']['url']['name'] = 'url';
        $form_country['country']['url']['title'] = 'ALIAS';
        $form_country['country']['url']['value'] = '';
        $form_country['country']['url']['length'] = 40;
        $form_country['country']['url']['type'] = 'safe_string';
        $form_country['country']['url']['required'] = 'off';
        $form_country['country']['url']['unique'] = 'off';

        $form_country['country']['description']['name'] = 'description';
        $form_country['country']['description']['title'] = 'DESCRIPTION';
        $form_country['country']['description']['value'] = '';
        $form_country['country']['description']['length'] = 40;
        $form_country['country']['description']['type'] = 'textarea';
        $form_country['country']['description']['required'] = 'off';
        $form_country['country']['description']['unique'] = 'off';
        $form_country['country']['description']['rows'] = '10';
        $form_country['country']['description']['cols'] = '40';


        $form_country['country']['meta_title']['name'] = 'meta_title';
        $form_country['country']['meta_title']['title'] = 'META TITLE';
        $form_country['country']['meta_title']['value'] = '';
        $form_country['country']['meta_title']['length'] = 40;
        $form_country['country']['meta_title']['type'] = 'safe_string';
        $form_country['country']['meta_title']['required'] = 'off';
        $form_country['country']['meta_title']['unique'] = 'off';
        $form_country['country']['meta_title']['tab'] = 'Мета теги';

        $form_country['country']['meta_description']['name'] = 'meta_description';
        $form_country['country']['meta_description']['title'] = 'META DESCRIPTION';
        $form_country['country']['meta_description']['value'] = '';
        $form_country['country']['meta_description']['length'] = 40;
        $form_country['country']['meta_description']['type'] = 'textarea';
        $form_country['country']['meta_description']['required'] = 'off';
        $form_country['country']['meta_description']['unique'] = 'off';
        $form_country['country']['meta_description']['tab'] = 'Мета теги';

        $form_country['country']['meta_keywords']['name'] = 'meta_keywords';
        $form_country['country']['meta_keywords']['title'] = 'META KEYWORDS';
        $form_country['country']['meta_keywords']['value'] = '';
        $form_country['country']['meta_keywords']['length'] = 40;
        $form_country['country']['meta_keywords']['type'] = 'safe_string';
        $form_country['country']['meta_keywords']['required'] = 'off';
        $form_country['country']['meta_keywords']['unique'] = 'off';
        $form_country['country']['meta_keywords']['rows'] = '10';
        $form_country['country']['meta_keywords']['cols'] = '40';
        $form_country['country']['meta_keywords']['tab'] = 'Мета теги';

        $form_data = array();
        $table_name = 'country';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data[$table_name])) {
                $form_data = array();
                $form_data = $form_country;
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $form_country;
            //$form_data = $this->_get_big_city_kvartira_model2($ajax);
        }

        return $form_data;
    }

    function get_ipoteka_model($ajax = false)
    {
        $form_data = array();
        $table_name = 'ipoteka';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data)) {
                $form_data = array();
                $form_data = $this->_get_ipoteka_model($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $this->_get_ipoteka_model($ajax);
        }
        return $form_data;
    }

    /**
     * Get ipoteka model
     * @param boolean $ajax mode
     * @return array
     */
    function _get_ipoteka_model($ajax = false)
    {
        $form_data = array();

        $form_data['ipoteka']['id']['name'] = 'id';
        $form_data['ipoteka']['id']['title'] = Multilanguage::_('L_TEXT_IDENTIFIER');
        $form_data['ipoteka']['id']['value'] = 0;
        $form_data['ipoteka']['id']['length'] = 40;
        $form_data['ipoteka']['id']['type'] = 'primary_key';
        $form_data['ipoteka']['id']['required'] = 'off';
        $form_data['ipoteka']['id']['unique'] = 'off';

        $form_data['ipoteka']['fio']['name'] = 'fio';
        $form_data['ipoteka']['fio']['title'] = 'Имя';
        $form_data['ipoteka']['fio']['value'] = '';
        $form_data['ipoteka']['fio']['length'] = 40;
        $form_data['ipoteka']['fio']['type'] = 'safe_string';
        $form_data['ipoteka']['fio']['required'] = 'on';
        $form_data['ipoteka']['fio']['unique'] = 'off';

        $form_data['ipoteka']['phone']['name'] = 'phone';
        $form_data['ipoteka']['phone']['title'] = 'Номер телефона';
        $form_data['ipoteka']['phone']['value'] = '';
        $form_data['ipoteka']['phone']['length'] = 40;
        $form_data['ipoteka']['phone']['type'] = 'safe_string';
        $form_data['ipoteka']['phone']['required'] = 'on';
        $form_data['ipoteka']['phone']['unique'] = 'off';

        $form_data['ipoteka']['email']['name'] = 'email';
        $form_data['ipoteka']['email']['title'] = 'Эл.почта';
        $form_data['ipoteka']['email']['value'] = '';
        $form_data['ipoteka']['email']['length'] = 40;
        $form_data['ipoteka']['email']['type'] = 'safe_string';
        $form_data['ipoteka']['email']['required'] = 'on';
        $form_data['ipoteka']['email']['unique'] = 'off';

        $form_data['ipoteka']['city_id']['name'] = 'city_id';
        $form_data['ipoteka']['city_id']['primary_key_name'] = 'city_id';
        $form_data['ipoteka']['city_id']['primary_key_table'] = 'city';
        $form_data['ipoteka']['city_id']['title'] = 'Я живу в';
        $form_data['ipoteka']['city_id']['value_string'] = '';
        $form_data['ipoteka']['city_id']['value'] = 0;
        $form_data['ipoteka']['city_id']['length'] = 40;
        $form_data['ipoteka']['city_id']['type'] = 'select_by_query';
        $form_data['ipoteka']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_data['ipoteka']['city_id']['value_name'] = 'name';
        $form_data['ipoteka']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_data['ipoteka']['city_id']['value_default'] = 0;
        $form_data['ipoteka']['city_id']['required'] = 'off';
        $form_data['ipoteka']['city_id']['unique'] = 'off';

        $form_data['ipoteka']['workage']['name'] = 'workage';
        $form_data['ipoteka']['workage']['title'] = 'Мой стаж на последнем месте';
        $form_data['ipoteka']['workage']['value'] = '';
        $form_data['ipoteka']['workage']['length'] = 40;
        $form_data['ipoteka']['workage']['type'] = 'select_box';
        $form_data['ipoteka']['workage']['select_data'] = array('выбрать' => 'выбрать', 'менее 3х мес.' => 'менее 3х мес.', 'более 3х мес.' => 'более 3х мес.', 'более года' => 'более года');
        $form_data['ipoteka']['workage']['required'] = 'off';
        $form_data['ipoteka']['workage']['unique'] = 'off';

        $form_data['ipoteka']['age']['name'] = 'age';
        $form_data['ipoteka']['age']['title'] = 'Мой возраст (лет)';
        $form_data['ipoteka']['age']['value'] = '';
        $form_data['ipoteka']['age']['length'] = 40;
        $form_data['ipoteka']['age']['type'] = 'safe_string';
        $form_data['ipoteka']['age']['required'] = 'on';
        $form_data['ipoteka']['age']['unique'] = 'off';

        $form_data['ipoteka']['kredit']['name'] = 'kredit';
        $form_data['ipoteka']['kredit']['title'] = 'Мне нужен кредит на покупку';
        $form_data['ipoteka']['kredit']['value'] = '';
        $form_data['ipoteka']['kredit']['length'] = 40;
        $form_data['ipoteka']['kredit']['type'] = 'select_box';
        $form_data['ipoteka']['kredit']['select_data'] = array('выберите тип' => 'выберите тип', 'квартиры' => 'квартиры', 'доли в новостройке' => 'доли в новостройке', 'частного дома' => 'частного дома', 'дачи' => 'дачи', 'участка земли' => 'участка земли');
        $form_data['ipoteka']['kredit']['required'] = 'off';
        $form_data['ipoteka']['kredit']['unique'] = 'off';

        $form_data['ipoteka']['cost']['name'] = 'cost';
        $form_data['ipoteka']['cost']['title'] = 'Стоимостью';
        $form_data['ipoteka']['cost']['value'] = '';
        $form_data['ipoteka']['cost']['length'] = 40;
        $form_data['ipoteka']['cost']['type'] = 'safe_string';
        $form_data['ipoteka']['cost']['required'] = 'on';
        $form_data['ipoteka']['cost']['unique'] = 'off';

        $form_data['ipoteka']['dohod']['name'] = 'dohod';
        $form_data['ipoteka']['dohod']['title'] = 'Подтверждение доходов';
        $form_data['ipoteka']['dohod']['value'] = '';
        $form_data['ipoteka']['dohod']['length'] = 40;
        $form_data['ipoteka']['dohod']['type'] = 'select_box';
        $form_data['ipoteka']['dohod']['select_data'] = array('выбрать' => 'выбрать', '2-НДФЛ' => '2-НДФЛ', 'справка банка' => 'справка банка');
        $form_data['ipoteka']['dohod']['required'] = 'off';
        $form_data['ipoteka']['dohod']['unique'] = 'off';

        $form_data['ipoteka']['dohod_per_month']['name'] = 'dohod_per_month';
        $form_data['ipoteka']['dohod_per_month']['title'] = 'Общий месячный доход';
        $form_data['ipoteka']['dohod_per_month']['value'] = '';
        $form_data['ipoteka']['dohod_per_month']['length'] = 40;
        $form_data['ipoteka']['dohod_per_month']['type'] = 'safe_string';
        $form_data['ipoteka']['dohod_per_month']['required'] = 'off';
        $form_data['ipoteka']['dohod_per_month']['unique'] = 'off';

        $form_data['ipoteka']['vznos']['name'] = 'vznos';
        $form_data['ipoteka']['vznos']['title'] = 'Первоначальный взнос';
        $form_data['ipoteka']['vznos']['value'] = '';
        $form_data['ipoteka']['vznos']['length'] = 40;
        $form_data['ipoteka']['vznos']['type'] = 'safe_string';
        $form_data['ipoteka']['vznos']['required'] = 'off';
        $form_data['ipoteka']['vznos']['unique'] = 'off';

        $form_data['ipoteka']['captcha']['name'] = 'captcha';
        $form_data['ipoteka']['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
        $form_data['ipoteka']['captcha']['value'] = '';
        $form_data['ipoteka']['captcha']['length'] = 40;
        $form_data['ipoteka']['captcha']['type'] = 'captcha';
        $form_data['ipoteka']['captcha']['required'] = 'on';
        $form_data['ipoteka']['captcha']['unique'] = 'off';

        //$item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'];

        return $form_data;
    }

    /**
     * Get kvartira model
     * @param boolean $ajax mode
     * @return array
     */
    function get_kvartira_model($ajax = false, $ignore_user_group = false)
    {
        $form_data = array();
        $table_name = 'data';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name, $ignore_user_group);


            if (empty($form_data)) {
                $form_data = array();
                $form_data = $this->_get_kvartira_model($ajax);
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name, $ignore_user_group);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $this->_get_kvartira_model($ajax);
            //$form_data = $this->_get_big_city_kvartira_model2($ajax);
        }
        //echo '<pre>';
        //print_r($form_data);
        //echo '</pre>';


        return $form_data;
    }

    function _get_kvartira_model($ajax = false)
    {
        $form_data = array();

        $form_data['data']['id']['name'] = 'id';
        $form_data['data']['id']['title'] = 'Идентификатор';
        $form_data['data']['id']['value'] = 0;
        $form_data['data']['id']['length'] = 40;
        $form_data['data']['id']['type'] = 'primary_key';
        $form_data['data']['id']['required'] = 'off';
        $form_data['data']['id']['unique'] = 'off';

        $form_data['data']['user_id']['name'] = 'user_id';
        $form_data['data']['user_id']['title'] = 'Владелец';
        $form_data['data']['user_id']['primary_key_name'] = 'user_id';
        $form_data['data']['user_id']['primary_key_table'] = 'user';
        $form_data['data']['user_id']['value_string'] = '';
        $form_data['data']['user_id']['value'] = 0;
        $form_data['data']['user_id']['length'] = 40;
        $form_data['data']['user_id']['type'] = 'select_by_query';
        $form_data['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by fio';
        $form_data['data']['user_id']['value_name'] = 'fio';
        $form_data['data']['user_id']['title_default'] = Multilanguage::_('L_CHOOSE_USER');
        $form_data['data']['user_id']['value_default'] = 0;
        $form_data['data']['user_id']['required'] = 'off';
        $form_data['data']['user_id']['unique'] = 'off';


        $form_data['data']['date_added']['name'] = 'date_added';
        $form_data['data']['date_added']['title'] = 'Дата подачи';
        $form_data['data']['date_added']['value'] = 'now';
        $form_data['data']['date_added']['length'] = 40;
        $form_data['data']['date_added']['type'] = 'dtdatetime';
        $form_data['data']['date_added']['required'] = 'off';
        $form_data['data']['date_added']['unique'] = 'off';

        $form_data['data']['active']['name'] = 'active';
        $form_data['data']['active']['title'] = 'Публиковать на сайте';
        $form_data['data']['active']['value'] = 1;
        $form_data['data']['active']['length'] = 40;
        $form_data['data']['active']['type'] = 'checkbox';
        $form_data['data']['active']['required'] = 'off';
        $form_data['data']['active']['unique'] = 'off';

        $form_data['data']['hot']['name'] = 'hot';
        $form_data['data']['hot']['title'] = 'Спецразмещение';
        $form_data['data']['hot']['value'] = 0;
        $form_data['data']['hot']['length'] = 40;
        $form_data['data']['hot']['type'] = 'checkbox';
        $form_data['data']['hot']['required'] = 'off';
        $form_data['data']['hot']['unique'] = 'off';


        if ($this->getConfigValue('apps.realtypro.show_contact.enable') == 1) {
            $form_data['data']['show_contact']['name'] = 'show_contact';
            $form_data['data']['show_contact']['title'] = 'Показывать контактные данные владельца';
            $form_data['data']['show_contact']['value'] = 0;
            $form_data['data']['show_contact']['length'] = 40;
            $form_data['data']['show_contact']['type'] = 'checkbox';
            $form_data['data']['show_contact']['required'] = 'off';
            $form_data['data']['show_contact']['unique'] = 'off';
        }


        if ($this->getConfigValue('apps.company.best')) {
            $form_data['data']['best']['name'] = 'best';
            $form_data['data']['best']['title'] = 'Лучшее предложение';
            $form_data['data']['best']['value'] = 0;
            $form_data['data']['best']['length'] = 40;
            $form_data['data']['best']['type'] = 'checkbox';
            $form_data['data']['best']['required'] = 'off';
            $form_data['data']['best']['unique'] = 'off';
        }

        $form_data['data']['topic_id']['name'] = 'topic_id';
        $form_data['data']['topic_id']['title'] = 'Тип';
        $form_data['data']['topic_id']['value_string'] = '';
        $form_data['data']['topic_id']['value'] = 0;
        $form_data['data']['topic_id']['length'] = 40;
        $form_data['data']['topic_id']['type'] = 'select_box_structure';
        $form_data['data']['topic_id']['required'] = 'on';
        $form_data['data']['topic_id']['unique'] = 'off';

        $form_data['data']['address']['name'] = 'address';
        $form_data['data']['address']['title'] = 'Адрес';
        $form_data['data']['address']['value'] = '';
        $form_data['data']['address']['length'] = 40;
        $form_data['data']['address']['type'] = 'safe_string';
        $form_data['data']['address']['required'] = 'off';
        $form_data['data']['address']['unique'] = 'off';
        $form_data['data']['address']['dbtype'] = 'notable';
        $form_data['data']['address']['parameters']['dadata'] = 1;
        $form_data['data']['address']['hint'] = 'Укажите адрес объекта, можно вводить город, улицу, номер дома через пробел. Затем выберите из предложенных вариантов правильный адрес.';


        if ($this->getConfigValue('country_in_form')) {
            $form_data['data']['country_id']['name'] = 'country_id';
            $form_data['data']['country_id']['primary_key_name'] = 'country_id';
            $form_data['data']['country_id']['primary_key_table'] = 'country';
            $form_data['data']['country_id']['title'] = 'Страна';
            $form_data['data']['country_id']['value_string'] = '';
            $form_data['data']['country_id']['value'] = 0;
            $form_data['data']['country_id']['length'] = 40;
            $form_data['data']['country_id']['type'] = 'select_by_query';
            $form_data['data']['country_id']['query'] = 'select * from ' . DB_PREFIX . '_country order by name';
            $form_data['data']['country_id']['value_name'] = 'name';
            $form_data['data']['country_id']['title_default'] = Multilanguage::_('L_CHOOSE_COUNTRY');
            $form_data['data']['country_id']['value_default'] = 0;
            $form_data['data']['country_id']['required'] = 'off';
            $form_data['data']['country_id']['unique'] = 'off';
            if ($ajax) {
                $form_data['data']['country_id']['onchange'] = '';

                if ($this->getConfigValue('apps.realty.ajax_region_refresh')) {
                    $form_data['data']['country_id']['onchange'] .= ' update_child_list(\'region_id\', this); ';
                }

                if ($this->getConfigValue('apps.realty.ajax_city_refresh')) {
                    $form_data['data']['country_id']['onchange'] .= ' set_empty(\'city_id\', this); ';
                }

                if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                    $form_data['data']['country_id']['onchange'] .= ' set_empty(\'district_id\', this); ';
                }

                if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                    $form_data['data']['country_id']['onchange'] .= ' set_empty(\'metro_id\', this); ';
                }

                if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                    $form_data['data']['country_id']['onchange'] .= ' set_empty(\'street_id\', this); ';
                }
            }
        }

        //if ($this->getConfigValue('region_in_form')) {
        $form_data['data']['region_id']['name'] = 'region_id';
        $form_data['data']['region_id']['primary_key_name'] = 'region_id';
        $form_data['data']['region_id']['primary_key_table'] = 'region';
        $form_data['data']['region_id']['title'] = Multilanguage::_('L_REGION');
        $form_data['data']['region_id']['value_string'] = '';
        $form_data['data']['region_id']['value'] = 0;
        $form_data['data']['region_id']['length'] = 40;
        $form_data['data']['region_id']['type'] = 'select_by_query';
        $form_data['data']['region_id']['query'] = 'select * from ' . DB_PREFIX . '_region order by name';

        if (intval($this->getRequestValue('country_id')) != 0/* and $this->getRequestValue('country_id') != '' */) {
            $form_data['data']['region_id']['query'] = 'select * from ' . DB_PREFIX . '_region where country_id=' . intval($this->getRequestValue('country_id')) . ' order by name';
        }

        $form_data['data']['region_id']['value_name'] = 'name';
        $form_data['data']['region_id']['title_default'] = Multilanguage::_('L_CHOOSE_REGION');
        $form_data['data']['region_id']['value_default'] = 0;
        $form_data['data']['region_id']['required'] = 'off';
        $form_data['data']['region_id']['unique'] = 'off';
        $form_data['data']['region_id']['parameters']['autocomplete'] = 1;

        if ($ajax) {
            if ($this->getConfigValue('apps.realty.ajax_city_refresh')) {
                $form_data['data']['region_id']['onchange'] .= ' update_child_list(\'city_id\', this); ';
            }

            if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                $form_data['data']['region_id']['onchange'] .= ' set_empty(\'district_id\', this); ';
            }

            if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                $form_data['data']['region_id']['onchange'] .= ' set_empty(\'metro_id\', this); ';
            }

            if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                $form_data['data']['region_id']['onchange'] .= ' set_empty(\'street_id\', this); ';
            }
        }
        //}
        //if ( $this->getConfigValue('city_in_form') ) {
        $form_data['data']['city_id']['name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_table'] = 'city';
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_data['data']['city_id']['value_string'] = '';
        $form_data['data']['city_id']['value'] = 0;
        $form_data['data']['city_id']['length'] = 40;
        $form_data['data']['city_id']['type'] = 'select_by_query';
        $form_data['data']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        if (intval($this->getRequestValue('region_id')) != 0/* and $this->getRequestValue('region_id') != '' */) {
            $form_data['data']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city where region_id=' . intval($this->getRequestValue('region_id')) . ' order by name';
        }
        $form_data['data']['city_id']['value_name'] = 'name';
        if ($this->getConfigValue('theme') == 'kgs') {
            $form_data['data']['city_id']['title_default'] = 'выбрать массив';
        } else {
            $form_data['data']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        }
        $form_data['data']['city_id']['value_default'] = 0;
        $form_data['data']['city_id']['required'] = 'on';
        $form_data['data']['city_id']['unique'] = 'off';
        $form_data['data']['city_id']['parameters']['autocomplete'] = 1;
        $form_data['data']['city_id']['parameters']['disable_autocomplete_on_search'] = 1;

        if ($ajax) {
            if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                $form_data['data']['city_id']['onchange'] .= ' update_child_list(\'metro_id\', this); ';
            }
            if ($this->getConfigValue('link_street_to_city')) {
                if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                    $form_data['data']['city_id']['onchange'] .= ' update_child_list(\'district_id\', this); ';
                }
                if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                    $form_data['data']['city_id']['onchange'] .= ' update_child_list(\'street_id\', this); ';
                }
            } else {
                if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                    $form_data['data']['city_id']['onchange'] .= ' update_child_list(\'district_id\', this); ';
                }
                if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                    $form_data['data']['city_id']['onchange'] .= ' set_empty(\'street_id\', this); ';
                }
            }
        }
        //}
        //if ( $this->getConfigValue('metro_in_form') ) {
        $form_data['data']['metro_id']['name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_table'] = 'metro';
        $form_data['data']['metro_id']['title'] = 'Метро';
        $form_data['data']['metro_id']['value_string'] = '';
        $form_data['data']['metro_id']['value'] = 0;
        $form_data['data']['metro_id']['length'] = 40;
        $form_data['data']['metro_id']['type'] = 'select_by_query';
        $form_data['data']['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro order by name';
        if (intval($this->getRequestValue('city_id')) != 0/* and $this->getRequestValue('city_id') != '' */) {
            $form_data['data']['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro where city_id=' . intval($this->getRequestValue('city_id')) . ' order by name';
        }

        $form_data['data']['metro_id']['value_name'] = 'name';
        $form_data['data']['metro_id']['title_default'] = 'выбрать метро';
        $form_data['data']['metro_id']['value_default'] = 0;
        $form_data['data']['metro_id']['required'] = 'off';
        $form_data['data']['metro_id']['unique'] = 'off';
        $form_data['data']['metro_id']['parameters']['autocomplete'] = 1;
        $form_data['data']['metro_id']['parameters']['disable_autocomplete_on_search'] = 1;


        //}
        //if ( $this->getConfigValue('district_in_form') ) {
        $form_data['data']['district_id']['name'] = 'district_id';
        $form_data['data']['district_id']['primary_key_name'] = 'id';
        $form_data['data']['district_id']['primary_key_table'] = 'district';
        $form_data['data']['district_id']['title'] = Multilanguage::_('L_DISTRICT');
        $form_data['data']['district_id']['value_string'] = '';
        $form_data['data']['district_id']['value'] = 0;
        $form_data['data']['district_id']['length'] = 40;
        $form_data['data']['district_id']['type'] = 'select_by_query';
        $form_data['data']['district_id']['query'] = 'select * from ' . DB_PREFIX . '_district order by name';
        if (intval($this->getRequestValue('city_id')) != 0/* and $this->getRequestValue('city_id') != '' */) {
            $form_data['data']['district_id']['query'] = 'select * from ' . DB_PREFIX . '_district where city_id=' . intval($this->getRequestValue('city_id')) . ' order by name';
        }
        $form_data['data']['district_id']['value_name'] = 'name';
        $form_data['data']['district_id']['title_default'] = Multilanguage::_('L_CHOOSE_DISTRICT');
        $form_data['data']['district_id']['value_default'] = 0;
        $form_data['data']['district_id']['required'] = 'off';
        $form_data['data']['district_id']['unique'] = 'off';
        $form_data['data']['district_id']['parameters']['autocomplete'] = 1;
        $form_data['data']['district_id']['parameters']['disable_autocomplete_on_search'] = 1;


        if ($ajax) {
            if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                if ($this->getConfigValue('link_street_to_city')) {

                } else {
                    $form_data['data']['district_id']['onchange'] .= ' update_child_list(\'street_id\', this); ';
                }
            }
        }
        //}
        //if ( $this->getConfigValue('street_in_form') ) {
        $form_data['data']['street_id']['name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_table'] = 'street';
        $form_data['data']['street_id']['title'] = Multilanguage::_('L_STREET');
        $form_data['data']['street_id']['value_string'] = '';
        $form_data['data']['street_id']['value'] = 0;
        $form_data['data']['street_id']['length'] = 40;
        $form_data['data']['street_id']['type'] = 'select_by_query';
        $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street order by name';
        if (intval($this->getRequestValue('district_id')) != 0/* and $this->getRequestValue('district_id') != '' */) {
            $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street where district_id=' . intval($this->getRequestValue('district_id')) . ' order by name';
        }
        if ($this->getConfigValue('link_street_to_city')) {
            if (intval($this->getRequestValue('city_id')) != 0/* and $this->getRequestValue('city_id') != '' */) {
                $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street where city_id=' . intval($this->getRequestValue('city_id')) . ' order by name';
            }
        }

        $form_data['data']['street_id']['value_name'] = 'name';
        $form_data['data']['street_id']['title_default'] = Multilanguage::_('L_CHOOSE_STREET');
        $form_data['data']['street_id']['value_default'] = 0;
        $form_data['data']['street_id']['required'] = 'on';
        $form_data['data']['street_id']['unique'] = 'off';
        $form_data['data']['street_id']['parameters']['autocomplete'] = 1;
        $form_data['data']['street_id']['parameters']['disable_autocomplete_on_search'] = 1;


        $form_data['data']['number']['name'] = 'number';
        $form_data['data']['number']['title'] = 'Номер дома';
        $form_data['data']['number']['value'] = '';
        $form_data['data']['number']['length'] = 40;
        $form_data['data']['number']['type'] = 'safe_string';
        $form_data['data']['number']['required'] = 'off';
        $form_data['data']['number']['unique'] = 'off';

        $form_data['data']['price']['name'] = 'price';
        $form_data['data']['price']['title'] = 'Цена';
        $form_data['data']['price']['value'] = '';
        $form_data['data']['price']['length'] = 40;
        $form_data['data']['price']['type'] = 'price';
        if ($this->getConfigValue('theme') == 'albostar') {
            $form_data['data']['price']['required'] = 'on';
        } else {
            $form_data['data']['price']['required'] = 'on';
        }
        $form_data['data']['price']['unique'] = 'off';

        if ($this->getConfigValue('currency_enable')) {
            $form_data['data']['currency_id']['name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_table'] = 'currency';
            $form_data['data']['currency_id']['title'] = 'Валюта';
            $form_data['data']['currency_id']['value_string'] = '';
            $form_data['data']['currency_id']['value'] = 0;
            $form_data['data']['currency_id']['length'] = 40;
            $form_data['data']['currency_id']['type'] = 'select_by_query';
            $form_data['data']['currency_id']['query'] = 'select * from ' . DB_PREFIX . '_currency WHERE is_active=1 ORDER BY sort_order ASC, code ASC, currency_id ASC';
            $form_data['data']['currency_id']['value_name'] = 'name';
            $form_data['data']['currency_id']['title_default'] = '';
            $form_data['data']['currency_id']['value_default'] = 0;
            $form_data['data']['currency_id']['required'] = 'off';
            $form_data['data']['currency_id']['unique'] = 'off';
        }

        $form_data['data']['room_count']['name'] = 'room_count';
        $form_data['data']['room_count']['title'] = 'Кол.во комнат';
        $form_data['data']['room_count']['value'] = '';
        $form_data['data']['room_count']['length'] = 40;
        $form_data['data']['room_count']['type'] = 'safe_string';
        $form_data['data']['room_count']['required'] = 'off';
        $form_data['data']['room_count']['unique'] = 'off';

        $form_data['data']['floor']['name'] = 'floor';
        $form_data['data']['floor']['title'] = 'Этаж';
        $form_data['data']['floor']['value'] = '';
        $form_data['data']['floor']['length'] = 40;
        $form_data['data']['floor']['type'] = 'safe_string';
        $form_data['data']['floor']['required'] = 'off';
        $form_data['data']['floor']['unique'] = 'off';

        $form_data['data']['floor_count']['name'] = 'floor_count';
        $form_data['data']['floor_count']['title'] = 'Этажность';
        $form_data['data']['floor_count']['value'] = '';
        $form_data['data']['floor_count']['length'] = 40;
        $form_data['data']['floor_count']['type'] = 'safe_string';
        $form_data['data']['floor_count']['required'] = 'off';
        $form_data['data']['floor_count']['unique'] = 'off';


        $form_data['data']['walls']['name'] = 'walls';
        $form_data['data']['walls']['title'] = 'Материал стен';
        $form_data['data']['walls']['value'] = '';
        $form_data['data']['walls']['length'] = 40;
        $form_data['data']['walls']['type'] = 'safe_string';
        $form_data['data']['walls']['required'] = 'off';
        $form_data['data']['walls']['unique'] = 'off';

        if ($this->getConfigValue('apps.plan.enable')) {

            $form_data['data']['planning']['name'] = 'planning';
            $form_data['data']['planning']['primary_key_name'] = 'plan_id';
            $form_data['data']['planning']['primary_key_table'] = 'plan';
            $form_data['data']['planning']['title'] = 'Планировка';
            $form_data['data']['planning']['value_string'] = '';
            $form_data['data']['planning']['value'] = 0;
            $form_data['data']['planning']['length'] = 40;
            $form_data['data']['planning']['type'] = 'select_by_query';
            $form_data['data']['planning']['query'] = 'select * from ' . DB_PREFIX . '_plan order by name';
            $form_data['data']['planning']['value_name'] = 'name';
            $form_data['data']['planning']['title_default'] = 'выбрать планировку';
            $form_data['data']['planning']['value_default'] = 0;
            $form_data['data']['planning']['required'] = 'off';
            $form_data['data']['planning']['unique'] = 'off';
        } else {
            $form_data['data']['planning']['name'] = 'planning';
            $form_data['data']['planning']['title'] = 'Планировка';
            $form_data['data']['planning']['value'] = '';
            $form_data['data']['planning']['length'] = 40;
            $form_data['data']['planning']['type'] = 'safe_string';
            $form_data['data']['planning']['required'] = 'off';
            $form_data['data']['planning']['unique'] = 'off';
        }

        if ($this->getConfigValue('apps.balcony.enable')) {
            $form_data['data']['balcony']['name'] = 'balcony';
            $form_data['data']['balcony']['primary_key_name'] = 'balcony_id';
            $form_data['data']['balcony']['primary_key_table'] = 'balcony';
            $form_data['data']['balcony']['title'] = 'Балкон';
            $form_data['data']['balcony']['value_string'] = '';
            $form_data['data']['balcony']['value'] = 0;
            $form_data['data']['balcony']['length'] = 40;
            $form_data['data']['balcony']['type'] = 'select_by_query';
            $form_data['data']['balcony']['query'] = 'select * from ' . DB_PREFIX . '_balcony order by name';
            $form_data['data']['balcony']['value_name'] = 'name';
            $form_data['data']['balcony']['title_default'] = 'выбрать балкон';
            $form_data['data']['balcony']['value_default'] = 0;
            $form_data['data']['balcony']['required'] = 'off';
            $form_data['data']['balcony']['unique'] = 'off';
        } else {
            $form_data['data']['balcony']['name'] = 'balcony';
            $form_data['data']['balcony']['title'] = 'Балкон';
            $form_data['data']['balcony']['value'] = '';
            $form_data['data']['balcony']['length'] = 40;
            $form_data['data']['balcony']['type'] = 'safe_string';
            $form_data['data']['balcony']['required'] = 'off';
            $form_data['data']['balcony']['unique'] = 'off';
        }
        /*
          $form_data['data']['date_added']['name'] = 'date_added';
          $form_data['data']['date_added']['title'] = 'Дата подачи';
          $form_data['data']['date_added']['value'] = time();
          $form_data['data']['date_added']['length'] = 40;
          $form_data['data']['date_added']['type'] = 'safe_string';
          $form_data['data']['date_added']['required'] = 'off';
          $form_data['data']['date_added']['unique'] = 'off';
         */

        $form_data['data']['square_all']['name'] = 'square_all';
        $form_data['data']['square_all']['title'] = 'Площадь общая';
        $form_data['data']['square_all']['value'] = '';
        $form_data['data']['square_all']['length'] = 40;
        $form_data['data']['square_all']['type'] = 'safe_string';
        $form_data['data']['square_all']['required'] = 'off';
        $form_data['data']['square_all']['unique'] = 'off';

        $form_data['data']['square_live']['name'] = 'square_live';
        $form_data['data']['square_live']['title'] = 'Площадь жилая';
        $form_data['data']['square_live']['value'] = '';
        $form_data['data']['square_live']['length'] = 40;
        $form_data['data']['square_live']['type'] = 'safe_string';
        $form_data['data']['square_live']['required'] = 'off';
        $form_data['data']['square_live']['unique'] = 'off';

        $form_data['data']['square_kitchen']['name'] = 'square_kitchen';
        $form_data['data']['square_kitchen']['title'] = 'Площадь кухни';
        $form_data['data']['square_kitchen']['value'] = '';
        $form_data['data']['square_kitchen']['length'] = 40;
        $form_data['data']['square_kitchen']['type'] = 'safe_string';
        $form_data['data']['square_kitchen']['required'] = 'off';
        $form_data['data']['square_kitchen']['unique'] = 'off';

        $form_data['data']['land_area']['name'] = 'land_area';
        $form_data['data']['land_area']['title'] = 'Площадь участка';
        $form_data['data']['land_area']['value'] = '';
        $form_data['data']['land_area']['length'] = 40;
        $form_data['data']['land_area']['type'] = 'safe_string';
        $form_data['data']['land_area']['required'] = 'off';
        $form_data['data']['land_area']['unique'] = 'off';
        $form_data['data']['land_area']['active_in_topic'] = '5,50,51,52,53,54';


        if ($this->getConfigValue('theme') == 'albostar') {
            $form_data['data']['square_land']['name'] = 'square_land';
            $form_data['data']['square_land']['title'] = 'Площадь участка';
            $form_data['data']['square_land']['value'] = '';
            $form_data['data']['square_land']['length'] = 40;
            $form_data['data']['square_land']['type'] = 'safe_string';
            $form_data['data']['square_land']['required'] = 'off';
            $form_data['data']['square_land']['unique'] = 'off';
        }


        if ($this->getConfigValue('apps.sanuzel.enable')) {

            $form_data['data']['bathroom']['name'] = 'bathroom';
            $form_data['data']['bathroom']['primary_key_name'] = 'sanuzel_id';
            $form_data['data']['bathroom']['primary_key_table'] = 'sanuzel';
            $form_data['data']['bathroom']['title'] = 'Сан. узел';
            $form_data['data']['bathroom']['value_string'] = '';
            $form_data['data']['bathroom']['value'] = 0;
            $form_data['data']['bathroom']['length'] = 40;
            $form_data['data']['bathroom']['type'] = 'select_by_query';
            $form_data['data']['bathroom']['query'] = 'select * from ' . DB_PREFIX . '_sanuzel order by name';
            $form_data['data']['bathroom']['value_name'] = 'name';
            $form_data['data']['bathroom']['title_default'] = 'выбрать сан. узел';
            $form_data['data']['bathroom']['value_default'] = 0;
            $form_data['data']['bathroom']['required'] = 'off';
            $form_data['data']['bathroom']['unique'] = 'off';
        } else {
            $form_data['data']['bathroom']['name'] = 'bathroom';
            $form_data['data']['bathroom']['title'] = 'Сан. узел';
            $form_data['data']['bathroom']['value'] = '';
            $form_data['data']['bathroom']['length'] = 40;
            $form_data['data']['bathroom']['type'] = 'safe_string';
            $form_data['data']['bathroom']['required'] = 'off';
            $form_data['data']['bathroom']['unique'] = 'off';
        }

        $form_data['data']['plate']['name'] = 'plate';
        $form_data['data']['plate']['title'] = 'Плита';
        $form_data['data']['plate']['value'] = '';
        $form_data['data']['plate']['length'] = 40;
        $form_data['data']['plate']['type'] = 'select_box';
        $form_data['data']['plate']['select_data'] = array('0' => 'нет', '1' => 'газ', '2' => 'электро');
        $form_data['data']['plate']['required'] = 'off';
        $form_data['data']['plate']['unique'] = 'off';

        if ($this->getConfigValue('theme') != 'albostar') {
            $form_data['data']['is_telephone']['name'] = 'is_telephone';
            $form_data['data']['is_telephone']['title'] = 'Телефон';
            $form_data['data']['is_telephone']['value'] = 0;
            $form_data['data']['is_telephone']['length'] = 40;
            $form_data['data']['is_telephone']['type'] = 'checkbox';
            $form_data['data']['is_telephone']['required'] = 'off';
            $form_data['data']['is_telephone']['unique'] = 'off';


            $form_data['data']['furniture']['name'] = 'furniture';
            $form_data['data']['furniture']['title'] = 'Мебель';
            $form_data['data']['furniture']['value'] = 0;
            $form_data['data']['furniture']['length'] = 40;
            $form_data['data']['furniture']['type'] = 'checkbox';
            $form_data['data']['furniture']['required'] = 'off';
            $form_data['data']['furniture']['unique'] = 'off';
        }

        $form_data['data']['text']['name'] = 'text';
        $form_data['data']['text']['title'] = 'Описание';
        $form_data['data']['text']['value'] = '';
        $form_data['data']['text']['length'] = 40;
        $form_data['data']['text']['type'] = 'textarea';
        $form_data['data']['text']['required'] = 'off';
        $form_data['data']['text']['unique'] = 'off';
        $form_data['data']['text']['rows'] = '10';
        $form_data['data']['text']['cols'] = '40';

        //$item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'];

        $form_data['data']['image']['name'] = 'image';
        $form_data['data']['image']['table_name'] = 'data';
        $form_data['data']['image']['primary_key'] = 'id';
        $form_data['data']['image']['primary_key_value'] = 0;
        $form_data['data']['image']['action'] = 'data';
        $form_data['data']['image']['title'] = 'Фотографии ';
        $form_data['data']['image']['value'] = '';
        $form_data['data']['image']['type'] = 'uploads';
        $form_data['data']['image']['required'] = 'off';
        $form_data['data']['image']['unique'] = 'off';

        //if ( $this->getConfigValue('apps.realtypro.youtube') ) {
        $form_data['data']['youtube']['name'] = 'youtube';
        $form_data['data']['youtube']['title'] = 'Видео';
        $form_data['data']['youtube']['value'] = '';
        $form_data['data']['youtube']['length'] = 40;
        $form_data['data']['youtube']['type'] = 'youtube';
        $form_data['data']['youtube']['required'] = 'off';
        $form_data['data']['youtube']['unique'] = 'off';
        //}

        $form_data['data']['fio']['name'] = 'fio';
        $form_data['data']['fio']['title'] = 'Ваше имя';
        $form_data['data']['fio']['value'] = '';
        $form_data['data']['fio']['length'] = 40;
        $form_data['data']['fio']['type'] = 'safe_string';
        $form_data['data']['fio']['required'] = 'off';
        $form_data['data']['fio']['unique'] = 'off';

        $form_data['data']['email']['name'] = 'email';
        $form_data['data']['email']['title'] = 'E-mail';
        $form_data['data']['email']['value'] = '';
        $form_data['data']['email']['length'] = 40;
        $form_data['data']['email']['type'] = 'safe_string';
        $form_data['data']['email']['required'] = 'off';
        $form_data['data']['email']['unique'] = 'off';

        $form_data['data']['phone']['name'] = 'phone';
        $form_data['data']['phone']['title'] = 'Ваш телефон';
        $form_data['data']['phone']['value'] = '';
        $form_data['data']['phone']['length'] = 40;
        $form_data['data']['phone']['type'] = 'mobilephone';
        $form_data['data']['phone']['required'] = 'off';
        $form_data['data']['phone']['unique'] = 'off';


        if ($this->getConfigValue('allow_callme_timelimits')) {
            $form_data['data']['can_call_start']['name'] = 'can_call_start';
            $form_data['data']['can_call_start']['title'] = 'Самое раннее время для звонка мне (HH:MM)';
            $form_data['data']['can_call_start']['value'] = '';
            $form_data['data']['can_call_start']['length'] = 40;
            $form_data['data']['can_call_start']['type'] = 'select_box';
            $form_data['data']['can_call_start']['select_data'] = array('не указано', '6:00', '7:00', '8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00');
            $form_data['data']['can_call_start']['required'] = 'off';
            $form_data['data']['can_call_start']['unique'] = 'off';

            $form_data['data']['can_call_end']['name'] = 'can_call_end';
            $form_data['data']['can_call_end']['title'] = 'Самое позднее время для звонка мне (HH:MM)';
            $form_data['data']['can_call_end']['value'] = '';
            $form_data['data']['can_call_end']['length'] = 40;
            $form_data['data']['can_call_end']['type'] = 'select_box';
            $form_data['data']['can_call_end']['select_data'] = array('не указано', '6:00', '7:00', '8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00');
            $form_data['data']['can_call_end']['required'] = 'off';
            $form_data['data']['can_call_end']['unique'] = 'off';
        }

        /*
          if ( $this->getConfigValue('apps.fasteditor.enable') ) {
          $form_data['data']['tmp_password']['name'] = 'tmp_password';
          $form_data['data']['tmp_password']['title'] = 'tmp_password';
          $form_data['data']['tmp_password']['value'] = '';
          $form_data['data']['tmp_password']['length'] = 40;
          $form_data['data']['tmp_password']['type'] = 'hidden';
          $form_data['data']['tmp_password']['required'] = 'off';
          $form_data['data']['tmp_password']['unique'] = 'off';
          }
         */

        $form_data['data']['meta_title']['name'] = 'meta_title';
        $form_data['data']['meta_title']['title'] = 'Meta title';
        $form_data['data']['meta_title']['value'] = '';
        $form_data['data']['meta_title']['type'] = 'safe_string';
        $form_data['data']['meta_title']['required'] = 'off';
        $form_data['data']['meta_title']['unique'] = 'off';
        $form_data['data']['meta_title']['tab'] = 'Мета-теги';

        $form_data['data']['meta_keywords']['name'] = 'meta_keywords';
        $form_data['data']['meta_keywords']['title'] = 'Meta keywords';
        $form_data['data']['meta_keywords']['value'] = '';
        $form_data['data']['meta_keywords']['type'] = 'textarea';
        $form_data['data']['meta_keywords']['required'] = 'off';
        $form_data['data']['meta_keywords']['unique'] = 'off';
        $form_data['data']['meta_keywords']['tab'] = 'Мета-теги';
        $form_data['data']['meta_keywords']['rows'] = '5';
        $form_data['data']['meta_keywords']['cols'] = '40';

        $form_data['data']['meta_description']['name'] = 'meta_description';
        $form_data['data']['meta_description']['title'] = 'Meta description';
        $form_data['data']['meta_description']['value'] = '';
        $form_data['data']['meta_description']['type'] = 'textarea';
        $form_data['data']['meta_description']['required'] = 'off';
        $form_data['data']['meta_description']['unique'] = 'off';
        $form_data['data']['meta_description']['tab'] = 'Мета-теги';
        $form_data['data']['meta_description']['rows'] = '8';
        $form_data['data']['meta_description']['cols'] = '40';

        $form_data['data']['geo']['name'] = 'geo';
        $form_data['data']['geo']['title'] = 'Координаты';
        $form_data['data']['geo']['value'] = '';
        $form_data['data']['geo']['type'] = 'geodata';
        $form_data['data']['geo']['required'] = 'off';
        $form_data['data']['geo']['unique'] = 'off';
        $form_data['data']['geo']['tab'] = 'Координаты';

        $form_data['data']['view_count']['name'] = 'view_count';
        $form_data['data']['view_count']['title'] = 'Количество просмотров';
        $form_data['data']['view_count']['value'] = '';
        $form_data['data']['view_count']['length'] = 40;
        $form_data['data']['view_count']['type'] = 'hidden';
        $form_data['data']['view_count']['required'] = 'off';
        $form_data['data']['view_count']['unique'] = 'off';

        return $form_data;
    }

    function _get_big_city_kvartira_model($ajax = false)
    {
        $form_data = array();

        $form_data['data']['id']['name'] = 'id';
        $form_data['data']['id']['title'] = 'Идентификатор';
        $form_data['data']['id']['value'] = 0;
        $form_data['data']['id']['length'] = 40;
        $form_data['data']['id']['type'] = 'primary_key';
        $form_data['data']['id']['required'] = 'off';
        $form_data['data']['id']['unique'] = 'off';

        $form_data['data']['user_id']['name'] = 'user_id';
        $form_data['data']['user_id']['title'] = 'Идентификатор пользователя';
        $form_data['data']['user_id']['value'] = 0;
        $form_data['data']['user_id']['length'] = 40;
        $form_data['data']['user_id']['type'] = 'select_by_query';
        $form_data['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by login';
        $form_data['data']['user_id']['value_name'] = 'login';
        $form_data['data']['user_id']['primary_key_name'] = 'user_id';
        $form_data['data']['user_id']['primary_key_table'] = 'user';
        $form_data['data']['user_id']['title_default'] = 'выбрать пользователя';
        $form_data['data']['user_id']['value_default'] = 0;
        $form_data['data']['user_id']['required'] = 'off';
        $form_data['data']['user_id']['unique'] = 'off';

        $form_data['data']['date_added']['name'] = 'date_added';
        $form_data['data']['date_added']['title'] = 'Дата подачи';
        $form_data['data']['date_added']['value'] = '';
        $form_data['data']['date_added']['length'] = 40;
        $form_data['data']['date_added']['type'] = 'hidden';
        $form_data['data']['date_added']['required'] = 'off';
        $form_data['data']['date_added']['unique'] = 'off';

        $form_data['data']['active']['name'] = 'active';
        $form_data['data']['active']['title'] = 'Публиковать на сайте';
        $form_data['data']['active']['value'] = 0;
        $form_data['data']['active']['length'] = 40;
        $form_data['data']['active']['type'] = 'checkbox';
        $form_data['data']['active']['required'] = 'off';
        $form_data['data']['active']['unique'] = 'off';

        $form_data['data']['hot']['name'] = 'hot';
        $form_data['data']['hot']['title'] = 'Спецразмещение';
        $form_data['data']['hot']['value'] = 0;
        $form_data['data']['hot']['length'] = 40;
        $form_data['data']['hot']['type'] = 'checkbox';
        $form_data['data']['hot']['required'] = 'off';
        $form_data['data']['hot']['unique'] = 'off';

        $form_data['data']['optype']['name'] = 'optype';
        $form_data['data']['optype']['title'] = 'Тип операции';
        $form_data['data']['optype']['value'] = 0;
        $form_data['data']['optype']['length'] = 40;
        $form_data['data']['optype']['type'] = 'select_box';
        $form_data['data']['optype']['select_data'] = array('0' => 'сдам', '1' => 'продам');
        $form_data['data']['optype']['required'] = 'off';
        $form_data['data']['optype']['unique'] = 'off';

        $form_data['data']['topic_id']['name'] = 'topic_id';
        $form_data['data']['topic_id']['title'] = 'Категория';
        $form_data['data']['topic_id']['value_string'] = '';
        $form_data['data']['topic_id']['value'] = 0;
        $form_data['data']['topic_id']['length'] = 40;
        $form_data['data']['topic_id']['type'] = 'select_box_structure';
        $form_data['data']['topic_id']['required'] = 'on';
        $form_data['data']['topic_id']['unique'] = 'off';

        $form_data['data']['metro_id']['name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_table'] = 'metro';
        $form_data['data']['metro_id']['title'] = 'Метро';
        $form_data['data']['metro_id']['value_string'] = '';
        $form_data['data']['metro_id']['value'] = 0;
        $form_data['data']['metro_id']['length'] = 40;
        $form_data['data']['metro_id']['type'] = 'select_by_query';
        $form_data['data']['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro order by name';
        $form_data['data']['metro_id']['value_name'] = 'name';
        $form_data['data']['metro_id']['title_default'] = 'выбрать метро';
        $form_data['data']['metro_id']['value_default'] = 0;
        $form_data['data']['metro_id']['required'] = 'off';
        $form_data['data']['metro_id']['unique'] = 'off';

        $form_data['data']['metro_time_onfoot']['name'] = 'metro_time_onfoot';
        $form_data['data']['metro_time_onfoot']['title'] = 'Минут до метро пешком';
        $form_data['data']['metro_time_onfoot']['value'] = '';
        $form_data['data']['metro_time_onfoot']['length'] = 40;
        $form_data['data']['metro_time_onfoot']['type'] = 'safe_string';
        $form_data['data']['metro_time_onfoot']['required'] = 'off';
        $form_data['data']['metro_time_onfoot']['unique'] = 'off';

        $form_data['data']['metro_time_oncar']['name'] = 'metro_time_oncar';
        $form_data['data']['metro_time_oncar']['title'] = 'Минут до метро транспортом';
        $form_data['data']['metro_time_oncar']['value'] = '';
        $form_data['data']['metro_time_oncar']['length'] = 40;
        $form_data['data']['metro_time_oncar']['type'] = 'safe_string';
        $form_data['data']['metro_time_oncar']['required'] = 'off';
        $form_data['data']['metro_time_oncar']['unique'] = 'off';

        /* $form_data['data']['is_city']['name'] = 'is_city';
          $form_data['data']['is_city']['title'] = 'Москва';
          $form_data['data']['is_city']['hint'] = 'если не указано, то Подмосковье';
          $form_data['data']['is_city']['value'] = 1;
          $form_data['data']['is_city']['type'] = 'checkbox';
          $form_data['data']['is_city']['required'] = 'off';
          $form_data['data']['is_city']['unique'] = 'off'; */

        $form_data['data']['city_id']['name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_table'] = 'city';
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_data['data']['city_id']['value_string'] = '';
        $form_data['data']['city_id']['value'] = 0;
        $form_data['data']['city_id']['length'] = 40;
        $form_data['data']['city_id']['type'] = 'select_by_query';
        $form_data['data']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_data['data']['city_id']['value_name'] = 'name';
        $form_data['data']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_data['data']['city_id']['value_default'] = 0;
        $form_data['data']['city_id']['required'] = 'off';
        $form_data['data']['city_id']['unique'] = 'off';

        $form_data['data']['street_id']['name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_table'] = 'street';
        $form_data['data']['street_id']['title'] = Multilanguage::_('L_STREET');
        $form_data['data']['street_id']['value_string'] = '';
        $form_data['data']['street_id']['value'] = 0;
        $form_data['data']['street_id']['length'] = 40;
        $form_data['data']['street_id']['type'] = 'select_by_query';
        $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street order by name';
        $form_data['data']['street_id']['value_name'] = 'name';
        $form_data['data']['street_id']['title_default'] = Multilanguage::_('L_CHOOSE_STREET');
        $form_data['data']['street_id']['value_default'] = 0;
        $form_data['data']['street_id']['required'] = 'off';
        $form_data['data']['street_id']['unique'] = 'off';

        if ($this->getConfigValue('user_add_street_enable')) {
            $form_data['data']['new_street']['name'] = 'new_street';
            $form_data['data']['new_street']['title'] = 'Новая улица';
            $form_data['data']['new_street']['value'] = '';
            $form_data['data']['new_street']['length'] = 40;
            $form_data['data']['new_street']['type'] = 'auto_add_value';
            $form_data['data']['new_street']['dbtype'] = 'notable';
            $form_data['data']['new_street']['value_table'] = 'street';
            $form_data['data']['new_street']['value_primary_key'] = 'street_id';
            $form_data['data']['new_street']['value_field'] = 'name';
            $form_data['data']['new_street']['assign_to'] = 'street_id';
            $form_data['data']['new_street']['required'] = 'off';
            $form_data['data']['new_street']['unique'] = 'off';
        }

        $form_data['data']['number']['name'] = 'number';
        $form_data['data']['number']['title'] = 'Номер дома';
        $form_data['data']['number']['value'] = '';
        $form_data['data']['number']['length'] = 40;
        $form_data['data']['number']['type'] = 'safe_string';
        $form_data['data']['number']['required'] = 'off';
        $form_data['data']['number']['unique'] = 'off';

        $form_data['data']['housing_number']['name'] = 'housing_number';
        $form_data['data']['housing_number']['title'] = 'Номер корпуса';
        $form_data['data']['housing_number']['value'] = '';
        $form_data['data']['housing_number']['length'] = 40;
        $form_data['data']['housing_number']['type'] = 'safe_string';
        $form_data['data']['housing_number']['required'] = 'off';
        $form_data['data']['housing_number']['unique'] = 'off';

        $form_data['data']['price']['name'] = 'price';
        $form_data['data']['price']['title'] = 'Цена';
        $form_data['data']['price']['value'] = '';
        $form_data['data']['price']['length'] = 40;
        $form_data['data']['price']['type'] = 'price';
        $form_data['data']['price']['required'] = 'off';
        $form_data['data']['price']['unique'] = 'off';

        if ($this->getConfigValue('currency_enable')) {
            $form_data['data']['currency_id']['name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_table'] = 'currency';
            $form_data['data']['currency_id']['title'] = 'Валюта';
            $form_data['data']['currency_id']['value_string'] = '';
            $form_data['data']['currency_id']['value'] = 0;
            $form_data['data']['currency_id']['length'] = 40;
            $form_data['data']['currency_id']['type'] = 'select_by_query';
            $form_data['data']['currency_id']['query'] = 'select * from ' . DB_PREFIX . '_currency WHERE is_active=1 ORDER BY sort_order ASC, code ASC, currency_id ASC';
            $form_data['data']['currency_id']['value_name'] = 'name';
            $form_data['data']['currency_id']['title_default'] = '';
            $form_data['data']['currency_id']['value_default'] = 0;
            $form_data['data']['currency_id']['required'] = 'off';
            $form_data['data']['currency_id']['unique'] = 'off';
        }

        $form_data['data']['rent_term']['name'] = 'rent_term';
        $form_data['data']['rent_term']['title'] = 'Срок аренды';
        $form_data['data']['rent_term']['value'] = 0;
        $form_data['data']['rent_term']['length'] = 40;
        $form_data['data']['rent_term']['type'] = 'select_box';
        $form_data['data']['rent_term']['select_data'] = array('0' => 'длинный', '1' => 'короткий');
        $form_data['data']['rent_term']['required'] = 'off';
        $form_data['data']['rent_term']['unique'] = 'off';

        $form_data['data']['room_count']['name'] = 'room_count';
        $form_data['data']['room_count']['title'] = 'Кол.во комнат';
        $form_data['data']['room_count']['value'] = '';
        $form_data['data']['room_count']['length'] = 40;
        $form_data['data']['room_count']['type'] = 'safe_string';
        $form_data['data']['room_count']['required'] = 'off';
        $form_data['data']['room_count']['unique'] = 'off';

        $form_data['data']['floor']['name'] = 'floor';
        $form_data['data']['floor']['title'] = 'Этаж';
        $form_data['data']['floor']['value'] = '';
        $form_data['data']['floor']['length'] = 40;
        $form_data['data']['floor']['type'] = 'safe_string';
        $form_data['data']['floor']['required'] = 'off';
        $form_data['data']['floor']['unique'] = 'off';

        $form_data['data']['floor_count']['name'] = 'floor_count';
        $form_data['data']['floor_count']['title'] = 'Этажность';
        $form_data['data']['floor_count']['value'] = '';
        $form_data['data']['floor_count']['length'] = 40;
        $form_data['data']['floor_count']['type'] = 'safe_string';
        $form_data['data']['floor_count']['required'] = 'off';
        $form_data['data']['floor_count']['unique'] = 'off';

        $form_data['data']['refrigerator']['name'] = 'refrigerator';
        $form_data['data']['refrigerator']['title'] = 'Холодильник';
        $form_data['data']['refrigerator']['value'] = 0;
        $form_data['data']['refrigerator']['length'] = 40;
        $form_data['data']['refrigerator']['type'] = 'select_box';
        $form_data['data']['refrigerator']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['refrigerator']['required'] = 'off';
        $form_data['data']['refrigerator']['unique'] = 'off';

        $form_data['data']['tvset']['name'] = 'tvset';
        $form_data['data']['tvset']['title'] = 'Телевизор';
        $form_data['data']['tvset']['value'] = 0;
        $form_data['data']['tvset']['length'] = 40;
        $form_data['data']['tvset']['type'] = 'select_box';
        $form_data['data']['tvset']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['tvset']['required'] = 'off';
        $form_data['data']['tvset']['unique'] = 'off';

        $form_data['data']['washer']['name'] = 'washer';
        $form_data['data']['washer']['title'] = 'Cтиральная машина';
        $form_data['data']['washer']['value'] = 0;
        $form_data['data']['washer']['length'] = 40;
        $form_data['data']['washer']['type'] = 'select_box';
        $form_data['data']['washer']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['washer']['required'] = 'off';
        $form_data['data']['washer']['unique'] = 'off';

        $form_data['data']['furniture_kitchen']['name'] = 'furniture_kitchen';
        $form_data['data']['furniture_kitchen']['title'] = 'Мебель на кухне';
        $form_data['data']['furniture_kitchen']['value'] = 0;
        $form_data['data']['furniture_kitchen']['length'] = 40;
        $form_data['data']['furniture_kitchen']['type'] = 'select_box';
        $form_data['data']['furniture_kitchen']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_kitchen']['required'] = 'off';
        $form_data['data']['furniture_kitchen']['unique'] = 'off';

        $form_data['data']['furniture_room']['name'] = 'furniture_room';
        $form_data['data']['furniture_room']['title'] = 'Мебель в комнате';
        $form_data['data']['furniture_room']['value'] = 0;
        $form_data['data']['furniture_room']['length'] = 40;
        $form_data['data']['furniture_room']['type'] = 'select_box';
        $form_data['data']['furniture_room']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_room']['required'] = 'off';
        $form_data['data']['furniture_room']['unique'] = 'off';

        $form_data['data']['balcony']['name'] = 'balcony';
        $form_data['data']['balcony']['title'] = 'Балкон';
        $form_data['data']['balcony']['value'] = 0;
        $form_data['data']['balcony']['length'] = 40;
        $form_data['data']['balcony']['type'] = 'select_box';
        $form_data['data']['balcony']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['balcony']['required'] = 'off';
        $form_data['data']['balcony']['unique'] = 'off';

        $form_data['data']['is_telephone']['name'] = 'is_telephone';
        $form_data['data']['is_telephone']['title'] = 'Телефон';
        $form_data['data']['is_telephone']['value'] = 0;
        $form_data['data']['is_telephone']['length'] = 40;
        $form_data['data']['is_telephone']['type'] = 'select_box';
        $form_data['data']['is_telephone']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['is_telephone']['required'] = 'off';
        $form_data['data']['is_telephone']['unique'] = 'off';

        $form_data['data']['plate']['name'] = 'plate';
        $form_data['data']['plate']['title'] = 'Плита';
        $form_data['data']['plate']['value'] = 0;
        $form_data['data']['plate']['length'] = 40;
        $form_data['data']['plate']['type'] = 'select_box';
        $form_data['data']['plate']['select_data'] = array('0' => 'не указано', '1' => 'газ', '2' => 'электро');
        $form_data['data']['plate']['required'] = 'off';
        $form_data['data']['plate']['unique'] = 'off';

        $form_data['data']['square_all']['name'] = 'square_all';
        $form_data['data']['square_all']['title'] = 'Площадь общая';
        $form_data['data']['square_all']['value'] = '';
        $form_data['data']['square_all']['length'] = 40;
        $form_data['data']['square_all']['type'] = 'safe_string';
        $form_data['data']['square_all']['required'] = 'off';
        $form_data['data']['square_all']['unique'] = 'off';

        $form_data['data']['square_live']['name'] = 'square_live';
        $form_data['data']['square_live']['title'] = 'Площадь жилая';
        $form_data['data']['square_live']['value'] = '';
        $form_data['data']['square_live']['length'] = 40;
        $form_data['data']['square_live']['type'] = 'safe_string';
        $form_data['data']['square_live']['required'] = 'off';
        $form_data['data']['square_live']['unique'] = 'off';

        $form_data['data']['square_kitchen']['name'] = 'square_kitchen';
        $form_data['data']['square_kitchen']['title'] = 'Площадь кухни';
        $form_data['data']['square_kitchen']['value'] = '';
        $form_data['data']['square_kitchen']['length'] = 40;
        $form_data['data']['square_kitchen']['type'] = 'safe_string';
        $form_data['data']['square_kitchen']['required'] = 'off';
        $form_data['data']['square_kitchen']['unique'] = 'off';


        $form_data['data']['contact_phone_1']['name'] = 'contact_phone_1';
        $form_data['data']['contact_phone_1']['title'] = 'Телефон1';
        $form_data['data']['contact_phone_1']['value'] = '';
        $form_data['data']['contact_phone_1']['length'] = 40;
        $form_data['data']['contact_phone_1']['type'] = 'safe_string';
        $form_data['data']['contact_phone_1']['required'] = 'off';
        $form_data['data']['contact_phone_1']['unique'] = 'off';

        $form_data['data']['contact_phone_2']['name'] = 'contact_phone_2';
        $form_data['data']['contact_phone_2']['title'] = 'Телефон2';
        $form_data['data']['contact_phone_2']['value'] = '';
        $form_data['data']['contact_phone_2']['length'] = 40;
        $form_data['data']['contact_phone_2']['type'] = 'safe_string';
        $form_data['data']['contact_phone_2']['required'] = 'off';
        $form_data['data']['contact_phone_2']['unique'] = 'off';

        $form_data['data']['text']['name'] = 'text';
        $form_data['data']['text']['title'] = 'Описание';
        $form_data['data']['text']['value'] = '';
        $form_data['data']['text']['length'] = 40;
        $form_data['data']['text']['type'] = 'textarea';
        $form_data['data']['text']['required'] = 'off';
        $form_data['data']['text']['unique'] = 'off';
        $form_data['data']['text']['rows'] = '10';
        $form_data['data']['text']['cols'] = '40';

        $form_data['data']['renter_slavic']['name'] = 'renter_slavic';
        $form_data['data']['renter_slavic']['title'] = 'Cлавян';
        $form_data['data']['renter_slavic']['value'] = 0;
        $form_data['data']['renter_slavic']['type'] = 'checkbox';
        $form_data['data']['renter_slavic']['required'] = 'off';
        $form_data['data']['renter_slavic']['unique'] = 'off';
        $form_data['data']['renter_slavic']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_decent']['name'] = 'renter_decent';
        $form_data['data']['renter_decent']['title'] = 'Всех приличных';
        $form_data['data']['renter_decent']['value'] = 0;
        $form_data['data']['renter_decent']['type'] = 'checkbox';
        $form_data['data']['renter_decent']['required'] = 'off';
        $form_data['data']['renter_decent']['unique'] = 'off';
        $form_data['data']['renter_decent']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_rfcitisen']['name'] = 'renter_rfcitisen';
        $form_data['data']['renter_rfcitisen']['title'] = 'Граждан  РФ';
        $form_data['data']['renter_rfcitisen']['value'] = 0;
        $form_data['data']['renter_rfcitisen']['type'] = 'checkbox';
        $form_data['data']['renter_rfcitisen']['required'] = 'off';
        $form_data['data']['renter_rfcitisen']['unique'] = 'off';
        $form_data['data']['renter_rfcitisen']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_family']['name'] = 'renter_family';
        $form_data['data']['renter_family']['title'] = 'Сем. пару';
        $form_data['data']['renter_family']['value'] = 0;
        $form_data['data']['renter_family']['type'] = 'checkbox';
        $form_data['data']['renter_family']['required'] = 'off';
        $form_data['data']['renter_family']['unique'] = 'off';
        $form_data['data']['renter_family']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_onegirl']['name'] = 'renter_onegirl';
        $form_data['data']['renter_onegirl']['title'] = 'Одну девушку';
        $form_data['data']['renter_onegirl']['value'] = 0;
        $form_data['data']['renter_onegirl']['type'] = 'checkbox';
        $form_data['data']['renter_onegirl']['required'] = 'off';
        $form_data['data']['renter_onegirl']['unique'] = 'off';
        $form_data['data']['renter_onegirl']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_twogirl']['name'] = 'renter_twogirl';
        $form_data['data']['renter_twogirl']['title'] = 'Двух девушек';
        $form_data['data']['renter_twogirl']['value'] = 0;
        $form_data['data']['renter_twogirl']['type'] = 'checkbox';
        $form_data['data']['renter_twogirl']['required'] = 'off';
        $form_data['data']['renter_twogirl']['unique'] = 'off';
        $form_data['data']['renter_twogirl']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_oneman']['name'] = 'renter_oneman';
        $form_data['data']['renter_oneman']['title'] = 'Одного мужчину';
        $form_data['data']['renter_oneman']['value'] = 0;
        $form_data['data']['renter_oneman']['type'] = 'checkbox';
        $form_data['data']['renter_oneman']['required'] = 'off';
        $form_data['data']['renter_oneman']['unique'] = 'off';
        $form_data['data']['renter_oneman']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_twomen']['name'] = 'renter_twomen';
        $form_data['data']['renter_twomen']['title'] = 'Двух мужчин';
        $form_data['data']['renter_twomen']['value'] = 0;
        $form_data['data']['renter_twomen']['type'] = 'checkbox';
        $form_data['data']['renter_twomen']['required'] = 'off';
        $form_data['data']['renter_twomen']['unique'] = 'off';
        $form_data['data']['renter_twomen']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_with_children']['name'] = 'renter_with_children';
        $form_data['data']['renter_with_children']['title'] = 'Можно с детьми';
        $form_data['data']['renter_with_children']['value'] = 0;
        $form_data['data']['renter_with_children']['type'] = 'checkbox';
        $form_data['data']['renter_with_children']['required'] = 'off';
        $form_data['data']['renter_with_children']['unique'] = 'off';
        $form_data['data']['renter_with_children']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_with_animals']['name'] = 'renter_with_animals';
        $form_data['data']['renter_with_animals']['title'] = 'Можно с животными';
        $form_data['data']['renter_with_animals']['value'] = 0;
        $form_data['data']['renter_with_animals']['type'] = 'checkbox';
        $form_data['data']['renter_with_animals']['required'] = 'off';
        $form_data['data']['renter_with_animals']['unique'] = 'off';
        $form_data['data']['renter_with_animals']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_another']['name'] = 'renter_another';
        $form_data['data']['renter_another']['title'] = 'Другие требования';
        $form_data['data']['renter_another']['value'] = 0;
        $form_data['data']['renter_another']['type'] = 'checkbox';
        $form_data['data']['renter_another']['required'] = 'off';
        $form_data['data']['renter_another']['unique'] = 'off';
        $form_data['data']['renter_another']['tab'] = 'Требования к соискателю';


        $form_data['data']['image']['name'] = 'image';
        $form_data['data']['image']['table_name'] = 'data';
        $form_data['data']['image']['primary_key'] = 'id';
        $form_data['data']['image']['primary_key_value'] = 0;
        $form_data['data']['image']['action'] = 'data';
        $form_data['data']['image']['title'] = 'Фотографии ';
        $form_data['data']['image']['value'] = '';
        $form_data['data']['image']['length'] = 40;
        $form_data['data']['image']['type'] = 'uploadify_image';
        $form_data['data']['image']['required'] = 'off';
        $form_data['data']['image']['unique'] = 'off';

        if ($this->getConfigValue('apps.realtypro.youtube')) {
            $form_data['data']['youtube']['name'] = 'youtube';
            $form_data['data']['youtube']['title'] = 'Видео';
            $form_data['data']['youtube']['value'] = '';
            $form_data['data']['youtube']['length'] = 40;
            $form_data['data']['youtube']['type'] = 'youtube';
            $form_data['data']['youtube']['required'] = 'off';
            $form_data['data']['youtube']['unique'] = 'off';
        }

        $form_data['data']['view_count']['name'] = 'view_count';
        $form_data['data']['view_count']['title'] = 'Количество просмотров';
        $form_data['data']['view_count']['value'] = '';
        $form_data['data']['view_count']['length'] = 40;
        $form_data['data']['view_count']['type'] = 'hidden';
        $form_data['data']['view_count']['required'] = 'off';
        $form_data['data']['view_count']['unique'] = 'off';

        $form_data['data']['whoyuaare']['name'] = 'whoyuaare';
        $form_data['data']['whoyuaare']['title'] = 'Кто вы';
        $form_data['data']['whoyuaare']['value'] = 0;
        $form_data['data']['whoyuaare']['length'] = 40;
        $form_data['data']['whoyuaare']['type'] = 'select_box';
        $form_data['data']['whoyuaare']['select_data'] = array('0' => 'не указано', '1' => 'собственник', '2' => 'агентство', '3' => 'частный риелтор');
        $form_data['data']['whoyuaare']['required'] = 'off';
        $form_data['data']['whoyuaare']['unique'] = 'off';

        $form_data['data']['fio']['name'] = 'fio';
        $form_data['data']['fio']['title'] = 'Ваше имя';
        $form_data['data']['fio']['value'] = '';
        $form_data['data']['fio']['length'] = 40;
        $form_data['data']['fio']['type'] = 'safe_string';
        $form_data['data']['fio']['required'] = 'on';
        $form_data['data']['fio']['unique'] = 'off';

        $form_data['data']['email']['name'] = 'email';
        $form_data['data']['email']['title'] = 'E-mail';
        $form_data['data']['email']['value'] = '';
        $form_data['data']['email']['length'] = 40;
        $form_data['data']['email']['type'] = 'email';
        $form_data['data']['email']['required'] = 'off';
        $form_data['data']['email']['unique'] = 'off';

        $form_data['data']['phone']['name'] = 'phone';
        $form_data['data']['phone']['title'] = 'Ваш телефон (мобильный)<br />Формат ввода <b>8**********</b>';
        $form_data['data']['phone']['value'] = '';
        $form_data['data']['phone']['length'] = 40;
        $form_data['data']['phone']['type'] = 'mobilephone';
        $form_data['data']['phone']['required'] = 'on';
        $form_data['data']['phone']['unique'] = 'off';

        $form_data['data']['agency_cooperation']['name'] = 'agency_cooperation';
        $form_data['data']['agency_cooperation']['title'] = 'Готовы ли вы сотрудничать с агентствами';
        $form_data['data']['agency_cooperation']['value'] = 1;
        $form_data['data']['agency_cooperation']['type'] = 'checkbox';
        $form_data['data']['agency_cooperation']['required'] = 'off';
        $form_data['data']['agency_cooperation']['unique'] = 'off';

        return $form_data;
    }

    function _get_big_city_kvartira_model2($ajax = false)
    {
        $form_data = array();

        $form_data['data']['id']['name'] = 'id';
        $form_data['data']['id']['title'] = 'Идентификатор';
        $form_data['data']['id']['value'] = 0;
        $form_data['data']['id']['length'] = 40;
        $form_data['data']['id']['type'] = 'primary_key';
        $form_data['data']['id']['required'] = 'off';
        $form_data['data']['id']['unique'] = 'off';

        $form_data['data']['user_id']['name'] = 'user_id';
        $form_data['data']['user_id']['title'] = 'Идентификатор пользователя';
        $form_data['data']['user_id']['value'] = 0;
        $form_data['data']['user_id']['length'] = 40;
        $form_data['data']['user_id']['type'] = 'select_by_query';
        $form_data['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by login';
        $form_data['data']['user_id']['value_name'] = 'login';
        $form_data['data']['user_id']['primary_key_name'] = 'user_id';
        $form_data['data']['user_id']['primary_key_table'] = 'user';
        $form_data['data']['user_id']['title_default'] = 'выбрать пользователя';
        $form_data['data']['user_id']['value_default'] = 0;
        $form_data['data']['user_id']['required'] = 'off';
        $form_data['data']['user_id']['unique'] = 'off';

        $form_data['data']['date_added']['name'] = 'date_added';
        $form_data['data']['date_added']['title'] = 'Дата подачи';
        $form_data['data']['date_added']['value'] = '';
        $form_data['data']['date_added']['length'] = 40;
        $form_data['data']['date_added']['type'] = 'hidden';
        $form_data['data']['date_added']['required'] = 'off';
        $form_data['data']['date_added']['unique'] = 'off';

        $form_data['data']['active']['name'] = 'active';
        $form_data['data']['active']['title'] = 'Публиковать на сайте';
        $form_data['data']['active']['value'] = 0;
        $form_data['data']['active']['length'] = 40;
        $form_data['data']['active']['type'] = 'checkbox';
        $form_data['data']['active']['required'] = 'off';
        $form_data['data']['active']['unique'] = 'off';

        $form_data['data']['hot']['name'] = 'hot';
        $form_data['data']['hot']['title'] = 'Спецразмещение';
        $form_data['data']['hot']['value'] = 0;
        $form_data['data']['hot']['length'] = 40;
        $form_data['data']['hot']['type'] = 'checkbox';
        $form_data['data']['hot']['required'] = 'off';
        $form_data['data']['hot']['unique'] = 'off';

        $form_data['data']['optype']['name'] = 'optype';
        $form_data['data']['optype']['title'] = 'Тип операции';
        $form_data['data']['optype']['value'] = 0;
        $form_data['data']['optype']['length'] = 40;
        $form_data['data']['optype']['type'] = 'select_box';
        $form_data['data']['optype']['select_data'] = array('0' => 'сдам', '1' => 'продам');
        $form_data['data']['optype']['required'] = 'off';
        $form_data['data']['optype']['unique'] = 'off';

        $form_data['data']['topic_id']['name'] = 'topic_id';
        $form_data['data']['topic_id']['title'] = 'Категория';
        $form_data['data']['topic_id']['value_string'] = '';
        $form_data['data']['topic_id']['value'] = 0;
        $form_data['data']['topic_id']['length'] = 40;
        $form_data['data']['topic_id']['type'] = 'select_box_structure';
        $form_data['data']['topic_id']['required'] = 'on';
        $form_data['data']['topic_id']['unique'] = 'off';

        $form_data['data']['metro_id']['name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_table'] = 'metro';
        $form_data['data']['metro_id']['title'] = 'Метро';
        $form_data['data']['metro_id']['value_string'] = '';
        $form_data['data']['metro_id']['value'] = 0;
        $form_data['data']['metro_id']['length'] = 40;
        $form_data['data']['metro_id']['type'] = 'select_by_query';
        $form_data['data']['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro order by name';
        $form_data['data']['metro_id']['value_name'] = 'name';
        $form_data['data']['metro_id']['title_default'] = 'выбрать метро';
        $form_data['data']['metro_id']['value_default'] = 0;
        $form_data['data']['metro_id']['required'] = 'off';
        $form_data['data']['metro_id']['unique'] = 'off';

        /* $form_data['data']['metro_time_onfoot']['name'] = 'metro_time_onfoot';
          $form_data['data']['metro_time_onfoot']['title'] = 'Минут до метро пешком';
          $form_data['data']['metro_time_onfoot']['value'] = '';
          $form_data['data']['metro_time_onfoot']['length'] = 40;
          $form_data['data']['metro_time_onfoot']['type'] = 'safe_string';
          $form_data['data']['metro_time_onfoot']['required'] = 'off';
          $form_data['data']['metro_time_onfoot']['unique'] = 'off';

          $form_data['data']['metro_time_oncar']['name'] = 'metro_time_oncar';
          $form_data['data']['metro_time_oncar']['title'] = 'Минут до метро транспортом';
          $form_data['data']['metro_time_oncar']['value'] = '';
          $form_data['data']['metro_time_oncar']['length'] = 40;
          $form_data['data']['metro_time_oncar']['type'] = 'safe_string';
          $form_data['data']['metro_time_oncar']['required'] = 'off';
          $form_data['data']['metro_time_oncar']['unique'] = 'off'; */

        /* $form_data['data']['is_city']['name'] = 'is_city';
          $form_data['data']['is_city']['title'] = 'Москва';
          $form_data['data']['is_city']['hint'] = 'если не указано, то Подмосковье';
          $form_data['data']['is_city']['value'] = 1;
          $form_data['data']['is_city']['type'] = 'checkbox';
          $form_data['data']['is_city']['required'] = 'off';
          $form_data['data']['is_city']['unique'] = 'off'; */

        $form_data['data']['city_id']['name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_table'] = 'city';
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_data['data']['city_id']['value_string'] = '';
        $form_data['data']['city_id']['value'] = 0;
        $form_data['data']['city_id']['length'] = 40;
        $form_data['data']['city_id']['type'] = 'select_by_query';
        $form_data['data']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_data['data']['city_id']['value_name'] = 'name';
        $form_data['data']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_data['data']['city_id']['value_default'] = 0;
        $form_data['data']['city_id']['required'] = 'off';
        $form_data['data']['city_id']['unique'] = 'off';

        $form_data['data']['street_id']['name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_table'] = 'street';
        $form_data['data']['street_id']['title'] = Multilanguage::_('L_STREET');
        $form_data['data']['street_id']['value_string'] = '';
        $form_data['data']['street_id']['value'] = 0;
        $form_data['data']['street_id']['length'] = 40;
        $form_data['data']['street_id']['type'] = 'select_by_query';
        $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street order by name';
        $form_data['data']['street_id']['value_name'] = 'name';
        $form_data['data']['street_id']['title_default'] = Multilanguage::_('L_CHOOSE_STREET');
        $form_data['data']['street_id']['value_default'] = 0;
        $form_data['data']['street_id']['required'] = 'off';
        $form_data['data']['street_id']['unique'] = 'off';

        if ($this->getConfigValue('user_add_street_enable')) {
            $form_data['data']['new_street']['name'] = 'new_street';
            $form_data['data']['new_street']['title'] = 'Новая улица';
            $form_data['data']['new_street']['value'] = '';
            $form_data['data']['new_street']['length'] = 40;
            $form_data['data']['new_street']['type'] = 'auto_add_value';
            $form_data['data']['new_street']['dbtype'] = 'notable';
            $form_data['data']['new_street']['value_table'] = 'street';
            $form_data['data']['new_street']['value_primary_key'] = 'street_id';
            $form_data['data']['new_street']['value_field'] = 'name';
            $form_data['data']['new_street']['assign_to'] = 'street_id';
            $form_data['data']['new_street']['required'] = 'off';
            $form_data['data']['new_street']['unique'] = 'off';
        }

        $form_data['data']['number']['name'] = 'number';
        $form_data['data']['number']['title'] = 'Номер дома';
        $form_data['data']['number']['value'] = '';
        $form_data['data']['number']['length'] = 40;
        $form_data['data']['number']['type'] = 'safe_string';
        $form_data['data']['number']['required'] = 'off';
        $form_data['data']['number']['unique'] = 'off';

        /* $form_data['data']['housing_number']['name'] = 'housing_number';
          $form_data['data']['housing_number']['title'] = 'Номер корпуса';
          $form_data['data']['housing_number']['value'] = '';
          $form_data['data']['housing_number']['length'] = 40;
          $form_data['data']['housing_number']['type'] = 'safe_string';
          $form_data['data']['housing_number']['required'] = 'off';
          $form_data['data']['housing_number']['unique'] = 'off'; */

        $form_data['data']['price']['name'] = 'price';
        $form_data['data']['price']['title'] = 'Цена';
        $form_data['data']['price']['value'] = '';
        $form_data['data']['price']['length'] = 40;
        $form_data['data']['price']['type'] = 'price';
        $form_data['data']['price']['required'] = 'off';
        $form_data['data']['price']['unique'] = 'off';

        if ($this->getConfigValue('currency_enable')) {
            $form_data['data']['currency_id']['name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_table'] = 'currency';
            $form_data['data']['currency_id']['title'] = 'Валюта';
            $form_data['data']['currency_id']['value_string'] = '';
            $form_data['data']['currency_id']['value'] = 0;
            $form_data['data']['currency_id']['length'] = 40;
            $form_data['data']['currency_id']['type'] = 'select_by_query';
            $form_data['data']['currency_id']['query'] = 'select * from ' . DB_PREFIX . '_currency WHERE is_active=1 ORDER BY sort_order ASC, code ASC, currency_id ASC';
            $form_data['data']['currency_id']['value_name'] = 'name';
            $form_data['data']['currency_id']['title_default'] = '';
            $form_data['data']['currency_id']['value_default'] = 0;
            $form_data['data']['currency_id']['required'] = 'off';
            $form_data['data']['currency_id']['unique'] = 'off';
        }

        $form_data['data']['aim']['name'] = 'aim';
        $form_data['data']['aim']['title'] = 'Цель';
        $form_data['data']['aim']['value'] = 0;
        $form_data['data']['aim']['length'] = 40;
        $form_data['data']['aim']['type'] = 'select_box';
        $form_data['data']['aim']['select_data'] = array('0' => 'не указанно', '1' => 'Инвестиция', '2' => 'Для отдыха', '3' => 'Для ПМЖ');
        $form_data['data']['aim']['required'] = 'off';
        $form_data['data']['aim']['unique'] = 'off';

        $form_data['data']['room_count']['name'] = 'room_count';
        $form_data['data']['room_count']['title'] = 'Кол.во комнат';
        $form_data['data']['room_count']['value'] = '';
        $form_data['data']['room_count']['length'] = 40;
        $form_data['data']['room_count']['type'] = 'safe_string';
        $form_data['data']['room_count']['required'] = 'off';
        $form_data['data']['room_count']['unique'] = 'off';

        $form_data['data']['floor']['name'] = 'floor';
        $form_data['data']['floor']['title'] = 'Этаж';
        $form_data['data']['floor']['value'] = '';
        $form_data['data']['floor']['length'] = 40;
        $form_data['data']['floor']['type'] = 'safe_string';
        $form_data['data']['floor']['required'] = 'off';
        $form_data['data']['floor']['unique'] = 'off';

        $form_data['data']['floor_count']['name'] = 'floor_count';
        $form_data['data']['floor_count']['title'] = 'Этажность';
        $form_data['data']['floor_count']['value'] = '';
        $form_data['data']['floor_count']['length'] = 40;
        $form_data['data']['floor_count']['type'] = 'safe_string';
        $form_data['data']['floor_count']['required'] = 'off';
        $form_data['data']['floor_count']['unique'] = 'off';

        $form_data['data']['refrigerator']['name'] = 'refrigerator';
        $form_data['data']['refrigerator']['title'] = 'Холодильник';
        $form_data['data']['refrigerator']['value'] = 0;
        $form_data['data']['refrigerator']['length'] = 40;
        $form_data['data']['refrigerator']['type'] = 'select_box';
        $form_data['data']['refrigerator']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['refrigerator']['required'] = 'off';
        $form_data['data']['refrigerator']['unique'] = 'off';

        $form_data['data']['tvset']['name'] = 'tvset';
        $form_data['data']['tvset']['title'] = 'Телевизор';
        $form_data['data']['tvset']['value'] = 0;
        $form_data['data']['tvset']['length'] = 40;
        $form_data['data']['tvset']['type'] = 'select_box';
        $form_data['data']['tvset']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['tvset']['required'] = 'off';
        $form_data['data']['tvset']['unique'] = 'off';

        $form_data['data']['washer']['name'] = 'washer';
        $form_data['data']['washer']['title'] = 'Cтиральная машина';
        $form_data['data']['washer']['value'] = 0;
        $form_data['data']['washer']['length'] = 40;
        $form_data['data']['washer']['type'] = 'select_box';
        $form_data['data']['washer']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['washer']['required'] = 'off';
        $form_data['data']['washer']['unique'] = 'off';

        $form_data['data']['furniture_kitchen']['name'] = 'furniture_kitchen';
        $form_data['data']['furniture_kitchen']['title'] = 'Мебель на кухне';
        $form_data['data']['furniture_kitchen']['value'] = 0;
        $form_data['data']['furniture_kitchen']['length'] = 40;
        $form_data['data']['furniture_kitchen']['type'] = 'select_box';
        $form_data['data']['furniture_kitchen']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_kitchen']['required'] = 'off';
        $form_data['data']['furniture_kitchen']['unique'] = 'off';

        $form_data['data']['furniture_room']['name'] = 'furniture_room';
        $form_data['data']['furniture_room']['title'] = 'Мебель в комнате';
        $form_data['data']['furniture_room']['value'] = 0;
        $form_data['data']['furniture_room']['length'] = 40;
        $form_data['data']['furniture_room']['type'] = 'select_box';
        $form_data['data']['furniture_room']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_room']['required'] = 'off';
        $form_data['data']['furniture_room']['unique'] = 'off';

        $form_data['data']['balcony']['name'] = 'balcony';
        $form_data['data']['balcony']['title'] = 'Балкон';
        $form_data['data']['balcony']['value'] = 0;
        $form_data['data']['balcony']['length'] = 40;
        $form_data['data']['balcony']['type'] = 'select_box';
        $form_data['data']['balcony']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['balcony']['required'] = 'off';
        $form_data['data']['balcony']['unique'] = 'off';

        $form_data['data']['is_telephone']['name'] = 'is_telephone';
        $form_data['data']['is_telephone']['title'] = 'Телефон';
        $form_data['data']['is_telephone']['value'] = 0;
        $form_data['data']['is_telephone']['length'] = 40;
        $form_data['data']['is_telephone']['type'] = 'select_box';
        $form_data['data']['is_telephone']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['is_telephone']['required'] = 'off';
        $form_data['data']['is_telephone']['unique'] = 'off';

        $form_data['data']['plate']['name'] = 'plate';
        $form_data['data']['plate']['title'] = 'Плита';
        $form_data['data']['plate']['value'] = 0;
        $form_data['data']['plate']['length'] = 40;
        $form_data['data']['plate']['type'] = 'select_box';
        $form_data['data']['plate']['select_data'] = array('0' => 'не указано', '1' => 'газ', '2' => 'электро');
        $form_data['data']['plate']['required'] = 'off';
        $form_data['data']['plate']['unique'] = 'off';

        $form_data['data']['square_all']['name'] = 'square_all';
        $form_data['data']['square_all']['title'] = 'Площадь общая';
        $form_data['data']['square_all']['value'] = '';
        $form_data['data']['square_all']['length'] = 40;
        $form_data['data']['square_all']['type'] = 'safe_string';
        $form_data['data']['square_all']['required'] = 'off';
        $form_data['data']['square_all']['unique'] = 'off';

        $form_data['data']['square_live']['name'] = 'square_live';
        $form_data['data']['square_live']['title'] = 'Площадь жилая';
        $form_data['data']['square_live']['value'] = '';
        $form_data['data']['square_live']['length'] = 40;
        $form_data['data']['square_live']['type'] = 'safe_string';
        $form_data['data']['square_live']['required'] = 'off';
        $form_data['data']['square_live']['unique'] = 'off';

        $form_data['data']['square_kitchen']['name'] = 'square_kitchen';
        $form_data['data']['square_kitchen']['title'] = 'Площадь кухни';
        $form_data['data']['square_kitchen']['value'] = '';
        $form_data['data']['square_kitchen']['length'] = 40;
        $form_data['data']['square_kitchen']['type'] = 'safe_string';
        $form_data['data']['square_kitchen']['required'] = 'off';
        $form_data['data']['square_kitchen']['unique'] = 'off';

        $form_data['data']['object_type']['name'] = 'object_type';
        $form_data['data']['object_type']['title'] = 'Тип объекта';
        $form_data['data']['object_type']['value'] = 0;
        $form_data['data']['object_type']['length'] = 40;
        $form_data['data']['object_type']['type'] = 'select_box';
        $form_data['data']['object_type']['select_data'] = array('0' => 'не указано', '1' => 'Апартаменты', '2' => 'Дом', '3' => 'Вилла');
        $form_data['data']['object_type']['required'] = 'off';
        $form_data['data']['object_type']['unique'] = 'off';

        $form_data['data']['object_state']['name'] = 'object_state';
        $form_data['data']['object_state']['title'] = 'Состояние';
        $form_data['data']['object_state']['value'] = 0;
        $form_data['data']['object_state']['length'] = 40;
        $form_data['data']['object_state']['type'] = 'select_box';
        $form_data['data']['object_state']['select_data'] = array('0' => 'не указано', '1' => 'Готовый объект', '2' => 'Строится', '3' => 'Вторичный рынок');
        $form_data['data']['object_state']['required'] = 'off';
        $form_data['data']['object_state']['unique'] = 'off';

        $form_data['data']['object_destination']['name'] = 'object_destination';
        $form_data['data']['object_destination']['title'] = 'Раcположение';
        $form_data['data']['object_destination']['value'] = 0;
        $form_data['data']['object_destination']['length'] = 40;
        $form_data['data']['object_destination']['type'] = 'select_box';
        $form_data['data']['object_destination']['select_data'] = array('0' => 'не указано', '1' => 'Центральная часть', '2' => 'У моря', '3' => 'В горах');
        $form_data['data']['object_destination']['required'] = 'off';
        $form_data['data']['object_destination']['unique'] = 'off';

        $form_data['data']['infra_greenzone']['name'] = 'infra_greenzone';
        $form_data['data']['infra_greenzone']['title'] = 'Зеленая зона';
        $form_data['data']['infra_greenzone']['value'] = 0;
        $form_data['data']['infra_greenzone']['type'] = 'checkbox';
        $form_data['data']['infra_greenzone']['required'] = 'off';
        $form_data['data']['infra_greenzone']['unique'] = 'off';
        $form_data['data']['infra_greenzone']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_sea']['name'] = 'infra_sea';
        $form_data['data']['infra_sea']['title'] = 'Море';
        $form_data['data']['infra_sea']['value'] = 0;
        $form_data['data']['infra_sea']['type'] = 'checkbox';
        $form_data['data']['infra_sea']['required'] = 'off';
        $form_data['data']['infra_sea']['unique'] = 'off';
        $form_data['data']['infra_sea']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_sport']['name'] = 'infra_sport';
        $form_data['data']['infra_sport']['title'] = 'Спорт';
        $form_data['data']['infra_sport']['value'] = 0;
        $form_data['data']['infra_sport']['type'] = 'checkbox';
        $form_data['data']['infra_sport']['required'] = 'off';
        $form_data['data']['infra_sport']['unique'] = 'off';
        $form_data['data']['infra_sport']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_clinic']['name'] = 'infra_clinic';
        $form_data['data']['infra_clinic']['title'] = 'Больница';
        $form_data['data']['infra_clinic']['value'] = 0;
        $form_data['data']['infra_clinic']['type'] = 'checkbox';
        $form_data['data']['infra_clinic']['required'] = 'off';
        $form_data['data']['infra_clinic']['unique'] = 'off';
        $form_data['data']['infra_clinic']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_terminal']['name'] = 'infra_terminal';
        $form_data['data']['infra_terminal']['title'] = 'Вокзал';
        $form_data['data']['infra_terminal']['value'] = 0;
        $form_data['data']['infra_terminal']['type'] = 'checkbox';
        $form_data['data']['infra_terminal']['required'] = 'off';
        $form_data['data']['infra_terminal']['unique'] = 'off';
        $form_data['data']['infra_terminal']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_airport']['name'] = 'infra_airport';
        $form_data['data']['infra_airport']['title'] = 'Аэропорт';
        $form_data['data']['infra_airport']['value'] = 0;
        $form_data['data']['infra_airport']['type'] = 'checkbox';
        $form_data['data']['infra_airport']['required'] = 'off';
        $form_data['data']['infra_airport']['unique'] = 'off';
        $form_data['data']['infra_airport']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_bank']['name'] = 'infra_bank';
        $form_data['data']['infra_bank']['title'] = 'Банки';
        $form_data['data']['infra_bank']['value'] = 0;
        $form_data['data']['infra_bank']['type'] = 'checkbox';
        $form_data['data']['infra_bank']['required'] = 'off';
        $form_data['data']['infra_bank']['unique'] = 'off';
        $form_data['data']['infra_bank']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_restaurant']['name'] = 'infra_restaurant';
        $form_data['data']['infra_restaurant']['title'] = 'Рестораны';
        $form_data['data']['infra_restaurant']['value'] = 0;
        $form_data['data']['infra_restaurant']['type'] = 'checkbox';
        $form_data['data']['infra_restaurant']['required'] = 'off';
        $form_data['data']['infra_restaurant']['unique'] = 'off';
        $form_data['data']['infra_restaurant']['tab'] = 'Инфраструктура поблизости';


        $form_data['data']['contact_phone_1']['name'] = 'contact_phone_1';
        $form_data['data']['contact_phone_1']['title'] = 'Телефон1';
        $form_data['data']['contact_phone_1']['value'] = '';
        $form_data['data']['contact_phone_1']['length'] = 40;
        $form_data['data']['contact_phone_1']['type'] = 'safe_string';
        $form_data['data']['contact_phone_1']['required'] = 'off';
        $form_data['data']['contact_phone_1']['unique'] = 'off';

        $form_data['data']['contact_phone_2']['name'] = 'contact_phone_2';
        $form_data['data']['contact_phone_2']['title'] = 'Телефон2';
        $form_data['data']['contact_phone_2']['value'] = '';
        $form_data['data']['contact_phone_2']['length'] = 40;
        $form_data['data']['contact_phone_2']['type'] = 'safe_string';
        $form_data['data']['contact_phone_2']['required'] = 'off';
        $form_data['data']['contact_phone_2']['unique'] = 'off';

        $form_data['data']['text']['name'] = 'text';
        $form_data['data']['text']['title'] = 'Описание';
        $form_data['data']['text']['value'] = '';
        $form_data['data']['text']['length'] = 40;
        $form_data['data']['text']['type'] = 'textarea';
        $form_data['data']['text']['required'] = 'off';
        $form_data['data']['text']['unique'] = 'off';
        $form_data['data']['text']['rows'] = '10';
        $form_data['data']['text']['cols'] = '40';


        $form_data['data']['image']['name'] = 'image';
        $form_data['data']['image']['table_name'] = 'data';
        $form_data['data']['image']['primary_key'] = 'id';
        $form_data['data']['image']['primary_key_value'] = 0;
        $form_data['data']['image']['action'] = 'data';
        $form_data['data']['image']['title'] = 'Фотографии ';
        $form_data['data']['image']['value'] = '';
        $form_data['data']['image']['length'] = 40;
        $form_data['data']['image']['type'] = 'uploadify_image';
        $form_data['data']['image']['required'] = 'off';
        $form_data['data']['image']['unique'] = 'off';

        if ($this->getConfigValue('apps.realtypro.youtube')) {
            $form_data['data']['youtube']['name'] = 'youtube';
            $form_data['data']['youtube']['title'] = 'Видео';
            $form_data['data']['youtube']['value'] = '';
            $form_data['data']['youtube']['length'] = 40;
            $form_data['data']['youtube']['type'] = 'safe_string';
            $form_data['data']['youtube']['required'] = 'off';
            $form_data['data']['youtube']['unique'] = 'off';
        }

        $form_data['data']['view_count']['name'] = 'view_count';
        $form_data['data']['view_count']['title'] = 'Количество просмотров';
        $form_data['data']['view_count']['value'] = '';
        $form_data['data']['view_count']['length'] = 40;
        $form_data['data']['view_count']['type'] = 'hidden';
        $form_data['data']['view_count']['required'] = 'off';
        $form_data['data']['view_count']['unique'] = 'off';

        $form_data['data']['whoyuaare']['name'] = 'whoyuaare';
        $form_data['data']['whoyuaare']['title'] = 'Кто вы';
        $form_data['data']['whoyuaare']['value'] = 0;
        $form_data['data']['whoyuaare']['length'] = 40;
        $form_data['data']['whoyuaare']['type'] = 'select_box';
        $form_data['data']['whoyuaare']['select_data'] = array('0' => 'не указано', '1' => 'собственник', '2' => 'агентство', '3' => 'частный риелтор');
        $form_data['data']['whoyuaare']['required'] = 'off';
        $form_data['data']['whoyuaare']['unique'] = 'off';

        $form_data['data']['fio']['name'] = 'fio';
        $form_data['data']['fio']['title'] = 'Ваше имя';
        $form_data['data']['fio']['value'] = '';
        $form_data['data']['fio']['length'] = 40;
        $form_data['data']['fio']['type'] = 'safe_string';
        $form_data['data']['fio']['required'] = 'off';
        $form_data['data']['fio']['unique'] = 'off';

        $form_data['data']['email']['name'] = 'email';
        $form_data['data']['email']['title'] = 'E-mail';
        $form_data['data']['email']['value'] = '';
        $form_data['data']['email']['length'] = 40;
        $form_data['data']['email']['type'] = 'email';
        $form_data['data']['email']['required'] = 'off';
        $form_data['data']['email']['unique'] = 'off';

        $form_data['data']['phone']['name'] = 'phone';
        $form_data['data']['phone']['title'] = 'Ваш телефон (мобильный)<br />Формат ввода <b>8**********</b>';
        $form_data['data']['phone']['value'] = '';
        $form_data['data']['phone']['length'] = 40;
        $form_data['data']['phone']['type'] = 'mobilephone';
        $form_data['data']['phone']['required'] = 'off';
        $form_data['data']['phone']['unique'] = 'off';


        return $form_data;
    }

    function define_kgs_titles($form_data)
    {
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_TEXT_ARRAY');
        return $form_data;
    }

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

    private function validateLevelRequired($value, $level_required)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $SM = new Structure_Manager();
        $category_structure = $SM->loadCategoryStructure();
        if ( $category_structure['catalog'][$value]['parent_id'] == 0 and $level_required > 0 ) {
            return false;
        }
        return true;
    }

}

?>
