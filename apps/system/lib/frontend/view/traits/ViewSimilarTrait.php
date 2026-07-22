<?php
/**
 * ViewSimilarTrait — Similar objects logic for Kvartira_View.
 *
 * Manages: getSimilar, formatPhoneNumber.
 */
trait ViewSimilarTrait
{
    protected function formatPhoneNumber($num)
    {
        $parts = array();
        $matches = array();
        $num = preg_replace('/[^\d]/', '', $num);
        //echo
        if (substr($num, 0, 1) == '8' && strlen($num) == 11) {
            preg_match_all('/(\d*)(\d{3})(\d{3})(\d{2})(\d{2})$/', $num, $matches);
        } elseif (strlen($num) == 11 || strlen($num) == 10) {
            preg_match_all('/(\d*)(\d{3})(\d{3})(\d{2})(\d{2})$/', $num, $matches);
        } else {
            preg_match_all('/(\d*)(\d{2})(\d{2})$/', $num, $matches);
        }
        for ($i = 1; $i < 6; $i++) {
            if (isset($matches[$i]) && $matches[$i] !== '') {
                $parts[] = $matches[$i][0];
            }
        }
        if (count($parts) > 0) {
            return implode('-', $parts);
        } else {
            return $num;
        }
    }

    protected function getSimilar($categories, $params = array())
    {
        if (intval($this->getConfigValue('similar_items_count')) == -1) {
            return array();
        }
        $similar_items_count = (0 == (int)$this->getConfigValue('similar_items_count') ? 5 : (int)$this->getConfigValue('similar_items_count'));

        //$str='{}'
        /* $similar_str=trim($this->getConfigValue('apps.realty.similar_preg'));

          if($similar_str!=''){
          //$title_str='';


          preg_match_all('/{([^}]+)}/', $similar_str, $matches);
          //print_r($matches);

          } */


        $simvariants = array();
        $conds = array();

        /* $similar_str='{price:+3000,city_id,topic_id}{price:+3000,city_id}';
          $similar_str='{price:+3000,city_id,topic_id}{topic_id}{price:+3000,city_id,!parenttopic}{!supertopic}{!rand}';
          $similar_str='{price:+3000,city_id,topic_id}{!rand}'; */

        $similar_str = trim($this->getConfigValue('apps.realty.similar_preg'));

        if (preg_match_all('/{([^}]+)}/', $similar_str, $matches)) {
            $simvariants = $matches[1];
        }

        if (!empty($simvariants)) {

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();

            $current_topic = intval($this->realty['topic_id']['value']);

            foreach ($simvariants as $step => $stepstr) {
                $parts = explode(',', $stepstr);
                if (count($parts) > 0) {
                    foreach ($parts as $part) {
                        if ($part == '!rand') {
                            $conds[$step][] = 'rand';
                        } elseif ($part == '!supertopic') {
                            if ($current_topic != 0) {
                                $ch = $Structure_Manager->createCatalogChains();
                                if (isset($ch['ar'][$current_topic]) && count($ch['ar'][$current_topic]) == 1) {
                                    $conds[$step][] = '`topic_id`=?';
                                    $conds_val[$step][] = $current_topic;
                                } elseif (isset($ch['ar'][$current_topic])) {
                                    $pt = $ch['ar'][$current_topic][0];
                                    $childs = $Structure_Manager->get_all_childs($pt, $category_structure);
                                    $childs[] = $pt;
                                    $conds[$step][] = '`topic_id` IN (' . implode(',', $childs) . ')';
                                    //$conds_val[$step][]=$ch['ar'][$rt][0];
                                }
                            }
                        } elseif ($part == '!parenttopic') {
                            if ($current_topic != 0) {
                                $ch = $Structure_Manager->createCatalogChains();
                                if (isset($ch['ar'][$current_topic]) && count($ch['ar'][$current_topic]) > 1) {
                                    $current_parent_topic = $ch['ar'][$current_topic][count($ch['ar'][$current_topic]) - 2];
                                    $childs = $Structure_Manager->get_all_childs($current_parent_topic, $category_structure);
                                    $childs[] = $current_parent_topic;
                                    $conds[$step][] = '`topic_id` IN (' . implode(',', $childs) . ')';
                                } else {
                                    $conds[$step][] = '`topic_id`=?';
                                    $conds_val[$step][] = $current_topic;
                                }
                            }
                        } elseif ($part == '!innertopic') {
                            if ($current_topic != 0) {
                                $childs = $Structure_Manager->get_all_childs($current_topic, $category_structure);
                                $childs[] = $current_topic;

                                $conds[$step][] = '`topic_id` IN (' . implode(',', $childs) . ')';
                            }
                        } elseif (false !== strpos($part, ':')) {
                            list($key, $val) = explode(':', $part);
                            if (preg_match('/([d\+-])(\d+)(%?)/', $val, $m)) {
                                if ($m[1] == 'd' && $m[3] == '%') {
                                    $min_val = intval($this->realty[$key]['value'] - ($m[2] * $this->realty[$key]['value']) / 100);
                                    $max_val = intval($this->realty[$key]['value'] + ($m[2] * $this->realty[$key]['value']) / 100);
                                } elseif ($m[1] == 'd' && $m[3] != '%') {
                                    $min_val = intval($this->realty[$key]['value'] - $m[2]);
                                    $max_val = intval($this->realty[$key]['value'] + $m[2]);
                                } elseif ($m[1] == '+' && $m[3] == '%') {
                                    $min_val = $this->realty[$key]['value'];
                                    $max_val = intval($this->realty[$key]['value'] + ($m[2] * $this->realty[$key]['value']) / 100);
                                } elseif ($m[1] == '-' && $m[3] == '%') {
                                    $min_val = intval($this->realty[$key]['value'] - ($m[2] * $this->realty[$key]['value']) / 100);
                                    $max_val = $this->realty[$key]['value'];
                                } elseif ($m[1] == '+' && $m[3] != '%') {
                                    $min_val = $this->realty[$key]['value'];
                                    $max_val = intval($this->realty[$key]['value'] + $m[2]);
                                } elseif ($m[1] == '-' && $m[3] != '%') {
                                    $min_val = intval($this->realty[$key]['value'] - $m[2]);
                                    $max_val = $this->realty[$key]['value'];
                                }

                                $conds[$step][] = '`' . $key . '`>=?';
                                $conds_val[$step][] = $min_val;
                                $conds[$step][] = '`' . $key . '`<=?';
                                $conds_val[$step][] = $max_val;
                            } else {
                                $conds[$step][] = '`' . $part . '`=?';
                                $conds_val[$step][] = $this->realty[$part]['value'];
                            }
                        } else {
                            $conds[$step][] = '`' . $part . '`=?';
                            $conds_val[$step][] = $this->realty[$part]['value'];
                        }
                    }
                }
            }
        }

        $DBC = DBC::getInstance();
        $ret = array();

        if (!empty($conds)) {

            $ids[] = $this->realty['id']['value'];
            $last_to_select = $similar_items_count;
            foreach ($conds as $k => $v) {
                if ($last_to_select > 0) {
                    if ($v[0] == 'rand') {
                        $v = array();
                        $v[] = 'active=1';
                        if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
                            $v[] = '`archived`<>1';
                        }
                        $v[] = 'id NOT IN (' . implode(',', $ids) . ')';
                        $q = 'SELECT id FROM ' . DB_PREFIX . '_data' . (!empty($v) ? ' WHERE ' . implode(' AND ', $v) : '') . ' ORDER BY RAND() LIMIT ' . $last_to_select;
                    } else {
                        $v[] = 'active=1';
                        if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
                            $v[] = '`archived`<>1';
                        }
                        $v[] = 'id NOT IN (' . implode(',', $ids) . ')';
                        $q = 'SELECT id FROM ' . DB_PREFIX . '_data' . (!empty($v) ? ' WHERE ' . implode(' AND ', $v) : '') . ' LIMIT ' . $last_to_select;
                    }
                    $stmt = $DBC->query($q, (isset($conds_val[$k]) ? $conds_val[$k] : array()));

                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret[] = $ar['id'];
                            $ids[] = $ar['id'];
                        }
                        $last_to_select = $similar_items_count - count($ret);
                    }
                } else {
                    break;
                }
            }
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();


            $where = array();
            if (!empty($params)) {
                $ids[] = $params['id'];
                $where['active'] = 'active=1';
                if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
                    $where['archived'] = '`archived`<>1';
                }
                if ($params['street_id'] != 0) {
                    $where['street_id'] = 'street_id=' . $params['street_id'];
                }
                if ($params['topic_id'] != 0) {
                    $where['topic_id'] = 'topic_id=' . $params['topic_id'];
                }
                if ($params['city_id'] != 0) {
                    $where['city_id'] = 'city_id=' . $params['city_id'];
                }
                if ($params['district_id'] != 0) {
                    $where['district_id'] = 'district_id=' . $params['district_id'];
                }
                if ($params['id'] != 0) {
                    $where['id'] = 'id NOT IN (' . implode(',', $ids) . ')';
                }
                $q = 'SELECT id FROM ' . DB_PREFIX . '_data' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' LIMIT ' . $similar_items_count;

                $stmt = $DBC->query($q);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ret[] = $ar['id'];
                        $ids[] = $ar['id'];
                    }
                }


                if (count($ret) < $similar_items_count) {
                    $last = $similar_items_count - count($ret);
                    unset($where['district_id']);
                    unset($where['street_id']);
                    $where['id'] = 'id NOT IN (' . implode(',', $ids) . ')';
                    $q = 'SELECT id FROM ' . DB_PREFIX . '_data' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' LIMIT ' . $last;

                    $stmt = $DBC->query($q);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret[] = $ar['id'];
                            $ids[] = $ar['id'];
                        }
                    }
                }
                if (count($ret) < $similar_items_count) {
                    $last = $similar_items_count - count($ret);
                    unset($where['city_id']);
                    //unset($where['street_id']);
                    $where['id'] = 'id NOT IN (' . implode(',', $ids) . ')';
                    $q = 'SELECT id FROM ' . DB_PREFIX . '_data' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' LIMIT ' . $last;
                    $stmt = $DBC->query($q);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret[] = $ar['id'];
                            $ids[] = $ar['id'];
                        }
                    }
                }
                if (count($ret) < $similar_items_count) {
                    $last = $similar_items_count - count($ret);
                    unset($where['topic_id']);
                    $where['id'] = 'id NOT IN (' . implode(',', $ids) . ')';
                    $q = 'SELECT id FROM ' . DB_PREFIX . '_data' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' LIMIT ' . $last;
                    $stmt = $DBC->query($q);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret[] = $ar['id'];
                            $ids[] = $ar['id'];
                        }
                    }
                }
            }
        }

        $datas = array();

        if (count($ret) > 0) {
            if (1 == $this->getConfigValue('apps.realty.similar_grid')) {
                $grid_constructor = $this->_getGridConstructor();
                $dparams = array();
                $dparams['order'] = 'date_added';
                $dparams['asc'] = 'desc';
                $dparams['no_portions'] = 1;
                $dparams['page'] = 1;
                $dparams['id'] = $ret;

                $res = $grid_constructor->get_sitebill_adv_core($dparams, false, false, false);

                $datas = $res['data'];
            } else {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();

                $form_data_src = $data_model->get_kvartira_model(false, true);

                $hasTlocation = false;
                foreach ($form_data_src['data'] as $key => $val) {
                    if ($val['type'] == 'tlocation') {
                        $hasTlocation = true;
                        $tlocationElement = $key;
                        break;
                    }
                }
                $sim = $data_model->init_model_data_from_db_multi('data', 'id', $ret, $form_data_src['data'], true);
                foreach ($sim as $id => $v) {
                    $sim[$id] = $data_model->init_language_values($v);
                    $sim[$id]['href'] = $this->getRealtyHREF($id, false, array('alias' => $sim[$id]['translit_alias']['value'], 'topic_id' => $sim[$id]['topic_id']['value']));
                    if ($hasTlocation) {
                        $sim[$id]['country_id']['value_string'] = $sim[$id][$tlocationElement]['value_string']['country_id'];
                        $sim[$id]['region_id']['value_string'] = $sim[$id][$tlocationElement]['value_string']['region_id'];
                        $sim[$id]['city_id']['value_string'] = $sim[$id][$tlocationElement]['value_string']['city_id'];
                        $sim[$id]['district_id']['value_string'] = $sim[$id][$tlocationElement]['value_string']['district_id'];
                        $sim[$id]['street_id']['value_string'] = $sim[$id][$tlocationElement]['value_string']['street_id'];
                    }
                    $datas[] = $sim[$id];
                }
                unset($sim);
            }
        }

        return $datas;
    }
}
