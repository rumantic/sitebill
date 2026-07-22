<?php

namespace system\lib\frontend\grid\traits;

trait TransformGridData
{
    function transformGridData($ra, $_collect_user_info = false)
    {

        $uselangs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $uselangs = true;
            $postfix = $this->getLangPostfix($this->getCurrentLang());
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new \Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new \Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $chains = $Structure_Manager->createCatalogChains();


        $params = array();

        $_model = $data_model->get_kvartira_model(false, true);

        $sbf = array();

        $fields = trim($this->getConfigValue('core.listing.select_query_fields'));
        if (!empty($fields)) {
            $fields_parts = explode("\n", $fields);
            foreach ($fields_parts as $fp) {
                list($f, $n) = explode('=', trim($fp));
                if (trim($f) != '') {
                    $sbf[trim($f)] = trim($f);
                    if (trim($n) != '') {
                        $sbf[trim($f)] = trim($n);
                    }
                }
            }
        }


        $grid_geodata = array();

        $billing = false;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $billing = true;
        }


        // еще жесть
        foreach ($ra as $item_id => $item_array) {



            if (isset($item_array['geo_lat']) && isset($item_array['geo_lng']) && $item_array['geo_lat'] != '' && $item_array['geo_lng'] != '') {
                $grid_geodata[] = array(
                    'lat' => $item_array['geo_lat'],
                    'lng' => $item_array['geo_lng'],
                    'id' => $item_array['id']
                );
            }
            if (isset($_model['data']['country_id']) && $item_array['country_id'] > 0) {
                $parameters = $_model['data']['country_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['country'] = $data_model->get_string_value_by_id('country', 'country_id', $fname, $item_array['country_id'], true);
            }

            if (isset($_model['data']['region_id']) && $item_array['region_id'] > 0) {
                $parameters = $_model['data']['region_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['region'] = $data_model->get_string_value_by_id('region', 'region_id', $fname, $item_array['region_id'], true);
            }
            if (isset($_model['data']['district_id']) && $item_array['district_id'] > 0) {
                $parameters = $_model['data']['district_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['district'] = $data_model->get_string_value_by_id('district', 'id', $fname, $item_array['district_id'], true);
            }
            if (isset($_model['data']['street_id']) && $item_array['street_id'] > 0) {
                $parameters = $_model['data']['street_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['street'] = $data_model->get_string_value_by_id('street', 'street_id', $fname, $item_array['street_id'], true);
            }
            if (isset($_model['data']['city_id']) && $item_array['city_id'] > 0) {

                $parameters = $_model['data']['city_id']['parameters'];

                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['city'] = $data_model->get_string_value_by_id('city', 'city_id', $fname, $item_array['city_id'], true);
            }
            if (isset($_model['data']['metro_id']) && $item_array['metro_id'] > 0) {
                $parameters = $_model['data']['metro_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['metro'] = $data_model->get_string_value_by_id('metro', 'metro_id', $fname, $item_array['metro_id'], true);
            }
            /*if (isset($_model['data']['optype']) && $_model['data']['optype']['type'] == 'select_by_query' && $item_array['optype'] > 0) {
                $parameters = $_model['data']['optype']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['optype_sh'] = $data_model->get_string_value_by_id('optype', 'optype_id', $fname, $item_array['optype'], true);
            }*/
            if ($item_array['user_id'] > 0) {
                if ($_collect_user_info) {
                    $fields_str = trim($this->getConfigValue('core.listing.add_user_info_fields'));
                    if ($fields_str == '') {
                        $fields = '`phone`, `login`, `fio`';
                        $fields = array();
                    } else {
                        $fields = explode(',', $fields_str);
                        $fields = array_map(function ($it) {
                            if (trim($it) !== '') {
                                return trim($it);
                            }
                        }, $fields);
                    }
                    if (empty($fields)) {
                        $fields = array('phone', 'login', 'fio');
                    }
                    $DBC = \DBC::getInstance();
                    if (!isset($collected[$item_array['user_id']])) {
                        $stmt = $DBC->query('SELECT `' . implode('`,`', $fields) . '` FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1', array($item_array['user_id']));
                        if ($stmt) {
                            $ar = $DBC->fetch($stmt);
                            $collected[$item_array['user_id']] = $ar;
                        }
                    }
                    $ra[$item_id]['_user_info'] = $collected[$item_array['user_id']];
                }


                $ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'fio', $item_array['user_id'], true);
                if ($ra[$item_id]['user'] == '') {
                    $ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'login', $item_array['user_id'], true);
                }
            }
            if ($item_array['currency_id'] > 0) {
                $ra[$item_id]['currency'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'code', $item_array['currency_id'], true);
            }

            foreach ($_model['data'] as $k => $v) {
                if ($v['type'] == 'select_box') {
                    $ra[$item_id]['_' . $k . '_'] = @$ra[$item_id][$k];
                    if (@isset($_model['data'][$k]['select_data'][$ra[$item_id][$k]])) {
                        $ra[$item_id][$k] = $_model['data'][$k]['select_data'][$ra[$item_id][$k]];
                    } else {
                        $ra[$item_id][$k] = '';
                    }
                }
            }


            if (!empty($sbf)) {
                //print_r($sbf);
                $tmp_cache = array();
                foreach ($sbf as $kn => $vn) {
                    if (isset($_model['data'][$kn]) && $_model['data'][$kn]['type'] == 'select_by_query') {
                        if ($item_array[$kn] > 0) {

                            if ($kn == $vn) {
                                $vn = '_' . $kn . '_';
                            }
                            $parameters = $_model['data'][$kn]['parameters'];
                            $fname = $_model['data'][$kn]['value_name'];
                            if (isset($tmp_cache[$kn][$item_array[$kn]])) {
                                $txt = $tmp_cache[$kn][$item_array[$kn]];
                            } else {
                                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                                    $fname .= $postfix;
                                }
                                $txt = $data_model->get_string_value_by_id($_model['data'][$kn]['primary_key_table'], $_model['data'][$kn]['primary_key_name'], $fname, $item_array[$kn], true);
                            }

                            $ra[$item_id][$vn] = $txt;
                        } else {
                            $ra[$item_id][$vn] = '';
                        }
                    }
                }
            }


            if ($uselangs) {
                foreach ($_model['data'] as $k => $v) {
                    if (($v['type'] == 'safe_string' || $v['type'] == 'textarea' || $v['type'] == 'textarea_editor') && isset($item_array[$k . $postfix]) && $item_array[$k . $postfix] != '') {
                        $ra[$item_id][$k] = $item_array[$k . $postfix];
                    }
                }
            }


            //$select_what[]=DB_PREFIX.'_topic.name AS type_sh';

            $params['topic_id'] = $item_array['topic_id'];

            $ra[$item_id]['path'] = $this->get_category_breadcrumbs_string($params, $category_structure);
            $ra[$item_id]['_chain'] = null;
            if (isset($chains['ar'][$item_array['topic_id']])) {
                $ra[$item_id]['_chain'] = $chains['ar'][$item_array['topic_id']];
            }


            $ra[$item_id]['date'] = date('d.m', strtotime($ra[$item_id]['date_added']));
            $ra[$item_id]['_posted_days'] = ceil((time() - strtotime($ra[$item_id]['date_added'])) / 86400);
            $ra[$item_id]['datetime'] = date('d.m H:i', strtotime($ra[$item_id]['date_added']));
            if ( !$this->getConfigValue('template.franch.grid.full_text_description') ) {
                $ra[$item_id]['text'] = strip_tags($ra[$item_id]['text']);
            }

            /*
              $image_array = $data_model->get_image_array ( 'data', 'data', 'id', $item_array['id'], 1 );
              if ( count($image_array) > 0 ) {
              $ra[$item_id]['img'] = $image_array;
              }

             */
            $ra[$item_id]['topic_info'] = null;
            if (isset($category_structure['catalog'][$ra[$item_id]['topic_id']])) {
                $ra[$item_id]['topic_info'] = $category_structure['catalog'][$ra[$item_id]['topic_id']];
            }


            if ($uselangs) {
                $fname = 'name' . $postfix;
                $ra[$item_id]['topic_info'][$fname] = @$ra[$item_id]['topic_info'][$fname];
            }
            $ra[$item_id]['type_sh'] = $ra[$item_id]['topic_info']['name'];

            $ra[$item_id]['href'] = @$this->getRealtyHREF($ra[$item_id]['id'], false, array('topic_id' => $ra[$item_id]['topic_id'], 'alias' => $ra[$item_id]['translit_alias']));

            if ($billing) {
                if (isset($item_array['premium_status_end']) && isset($_model['data']['premium_status_end']) && $ra[$item_id]['premium_status_end'] > time()) {
                    $ra[$item_id]['premium_status'] = 1;
                }
                if (isset($item_array['vip_status_end']) && isset($_model['data']['vip_status_end']) && $ra[$item_id]['vip_status_end'] > time()) {
                    $ra[$item_id]['vip_status'] = 1;
                }
                if (isset($item_array['bold_status_end']) && isset($_model['data']['bold_status_end']) && $ra[$item_id]['bold_status_end'] > time()) {
                    $ra[$item_id]['bold_status'] = 1;
                }
            }
        }

        foreach ($ra as $item) {
            $_ids[] = $item['id'];
        }

        $hasMultipleFields = array();
        $hasUploadify = false;
        $hasUploads = false;
        $uploads_element = '';

        // Упрощение модели по требованию
        $_model['data'] = $this->modelReducer($_model['data']);
        foreach ($_model['data'] as $k => $v) {
            if (isset($v['type']) && $v['type'] == 'uploadify_image') {
                $hasUploadify = true;
            } elseif (isset($v['type']) && $v['type'] == 'uploads') {
                $hasUploads = true;
                $uploads_element = $v['name'];
                break;
            }
        }

        foreach ($_model['data'] as $k => $v) {
            if (isset($v['type']) && $v['type'] == 'select_by_query_multi') {
                $hasMultipleFields[] = $k;
            }
        }


        if (!empty($hasMultipleFields)) {
            $elements_keys = array();
            $data = array();
            $DBC = \DBC::getInstance();
            $query = 'SELECT `primary_id`, `field_name`, `field_value` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(',', array_fill(0, count($hasMultipleFields), '?')) . ') AND `primary_id` IN (' . implode(',', array_fill(0, count($_ids), '?')) . ')';
            $query_params[] = 'data';
            //$query_params=$_ids;
            $query_params = array_merge($query_params, $hasMultipleFields, $_ids);
            $stmt = $DBC->query($query, $query_params);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $elements_keys[$ar['field_name']][$ar['field_value']] = '';
                    $data[$ar['primary_id']][$ar['field_name']][] = $ar['field_value'];
                }
            }
            //print_r($elements_keys);
            if (!empty($elements_keys)) {
                foreach ($elements_keys as $key => $ikeys) {
                    $name = $_model['data'][$key]['value_name'];
                    $pk = $_model['data'][$key]['primary_key_name'];
                    $query = 'SELECT `' . $pk . '`, `' . $name . '` FROM ' . DB_PREFIX . '_' . $_model['data'][$key]['primary_key_table'] . ' WHERE `' . $_model['data'][$key]['primary_key_name'] . '` IN (' . implode(',', array_keys($ikeys)) . ')';
                    $stmt = $DBC->query($query);
                    echo $DBC->getLastError();
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $elements_keys[$key][$ar[$pk]] = $ar[$name];
                        }
                    }
                }

                // вот жестокий цикл для оптимизации
                foreach ($ra as $item_id => $item_array) {
                    if (isset($data[$item_array['id']])) {
                        foreach ($data[$item_array['id']] as $ek => $v) {
                            if (!is_array($ra[$item_id][$ek])) {
                                $ra[$item_id][$ek] = array();
                            }
                            $ra[$item_id][$ek][0] = $v;
                            foreach ($v as $_v) {
                                $ra[$item_id][$ek][1][$_v] = $elements_keys[$ek][$_v];
                            }
                        }
                    }
                }
            }
        }

        if ($hasUploadify) {
            $key = 'id';
            if (count($_ids) > 0) {
                $query = 'SELECT li.' . $key . ' , i.* FROM ' . DB_PREFIX . '_data_image li LEFT JOIN ' . IMAGE_TABLE . ' i USING(image_id) WHERE li.' . $key . ' IN (' . implode(', ', $_ids) . ') ORDER BY li.sort_order ASC';
                $DBC = \DBC::getInstance();
                $stmt = $DBC->query($query);
                $images = array();
                if ($stmt) {
                    $iurl = $this->storage_dir;
                    while ($ar = $DBC->fetch($stmt)) {
                        $ar['img_preview'] = $iurl . $ar['preview'];
                        $ar['img_normal'] = $iurl . $ar['normal'];
                        $images[$ar[$key]][] = $ar;
                    }
                }
                foreach ($ra as $k => $item) {
                    if (isset($images[$item['id']])) {
                        $ra[$k]['img'] = $images[$item['id']];
                    }
                }
            }
        } elseif ($hasUploads) {
            //try to get uploadify images first
            //$old_uploadify_images = $this->get_uploadify_images($_ids);
            foreach ($ra as $k => $item) {
                //echo 'uploads_element = '.$uploads_element.'<br>';
                //echo '<pre>';
                //print_r($item);
                //echo '</pre>';

                if ($item[$uploads_element] == '') {
                    if (isset($old_uploadify_images[$ra[$k]['id']])) {
                        $ra[$k]['img'] = $old_uploadify_images[$ra[$k]['id']];
                    }

                    if (isset($item['image_cache'])) {
                        $ra[$k]['image_cache'] = unserialize($item['image_cache']);
                        if (is_array($ra[$k]['image_cache']) and count($ra[$k]['image_cache']) > 0) {
                            $i = 0;
                            foreach ($ra[$k]['image_cache'] as $cache_item) {
                                if ($this->getConfigValue('apps.sharder.mirroring.enable')) {
                                    $cache_item =
                                        str_replace($this->getConfigValue('apps.sharder.mirroring.find'), $this->getConfigValue('apps.sharder.mirroring.replace'), $cache_item);
                                }

                                $ra[$k]['img'][$i] = array();
                                $ra[$k]['img'][$i]['preview'] = $cache_item;
                                $ra[$k]['img'][$i]['normal'] = $cache_item;
                                $ra[$k]['img'][$i]['remote'] = 'true';

                                if (!@is_array($ra[$k]['image']) and $ra[$k]['image'] == '') {
                                    $ra[$k]['image'] = array();
                                }
                                if (!@is_array($ra[$k]['image'][$i])) {
                                    $ra[$k]['image'][$i] = array();
                                    $ra[$k]['image'][$i]['preview'] = $cache_item;
                                    $ra[$k]['image'][$i]['normal'] = $cache_item;
                                    $ra[$k]['image'][$i]['remote'] = 'true';
                                }

                                $i++;
                            }
                        }
                    }

                    /* else{
                      $ra[$k]['img']='';
                      } */
                } else {
                    $ims = unserialize($item[$uploads_element]);

                    if (is_array($ims) && count($ims) == 0) {
                        unset($ra[$k]['img']);
                        //$ra[$k]['img']='';
                    } else {
                        $ims = $data_model->sharder_mirror($ims, true);
                        $ra[$k]['img'] = $ims;
                    }
                }
            }
        }

        if ($hasUploads) {
            foreach ($ra as $e => $item) {
                foreach ($_model['data'] as $k => $v) {
                    if (isset($v['type']) && $v['type'] == 'uploads') {
                        if (is_scalar($ra[$e][$k])) {
                            $ra[$e][$k] = unserialize($ra[$e][$k]);
                            $ra[$e][$k] = $data_model->sharder_mirror($ra[$e][$k], true);
                        }
                    } elseif (isset($ra[$e]['image_cache']) && $v['type'] == 'uploads') {
                        $ra[$e]['image_cache'] = unserialize($ra[$e]['image_cache']);
                    }
                }
            }
        }

        return $ra;
    }
}
