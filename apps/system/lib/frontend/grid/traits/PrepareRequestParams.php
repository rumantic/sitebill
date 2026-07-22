<?php

namespace system\lib\frontend\grid\traits;

trait PrepareRequestParams
{
    protected function eat_all_request_for_paging ( $params ) {
        $all = $this->request()->all();
        return array_merge($params, $all);
    }

    protected function prepareRequestParams($params, $premium = false)
    {

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $_billing_on = true;
        } else {
            $_billing_on = false;
        }


        if (isset($params['currency_id']) && 0 != (int)$params['currency_id'] && 1 == $this->getConfigValue('currency_enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CA = new \currency_admin();
            $this->use_currency = true;
            $this->price_koefficient = $CA->getCourse((int)$params['currency_id']);
        } elseif (!isset($params['currency_id']) && 1 == $this->getConfigValue('currency_enable')) {
            $def_currency = intval($this->getConfigValue('apps.currency.default_grid_currency_id'));
            if ($def_currency != 0) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
                $CA = new \currency_admin();
                $this->use_currency = true;
                $this->price_koefficient = $CA->getCourse($def_currency);
            } else {
                $this->use_currency = false;
                $this->price_koefficient = 1;
            }
        } else {
            $this->use_currency = false;
            $this->price_koefficient = 1;
        }


        $where_array = array();
        $add_from_table = '';
        $add_select_value = '';
        $select_what = array();
        $left_joins = array();

        $where_array_prepared = array();
        $where_value_prepared = array();


        if (isset($params['order']) && $params['order'] == 'city') {
            if ($this->getConfigValue('apps.language.use_langs')) {
                $field = 'name';
                $no_ml = 0;
                if (isset($this->grid_item_data_model['city_id']['parameters']['no_ml'])) {
                    $no_ml = intval($this->grid_item_data_model['city_id']['parameters']['no_ml']);
                }
                if (0 === intval($parameters['no_ml'])) {
                    $field .= $this->getLangPostfix($this->getCurrentLang());
                }

                $select_what[] = DB_PREFIX . '_city.' . $field . ' as city';
            } else {
                $select_what[] = DB_PREFIX . '_city.name as city';
            }

            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_city ON ' . DB_PREFIX . '_city.city_id=' . DB_PREFIX . '_data.city_id';
        }

        if (isset($params['order']) && $params['order'] == 'district') {
            $select_what[] = DB_PREFIX . '_district.name as district';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_district ON ' . DB_PREFIX . '_district.id=' . DB_PREFIX . '_data.district_id';
        }

        if (isset($params['order']) && $params['order'] == 'metro') {
            $select_what[] = DB_PREFIX . '_metro.name as metro';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_metro ON ' . DB_PREFIX . '_metro.metro_id=' . DB_PREFIX . '_data.metro_id';
        }

        if (isset($params['order']) && $params['order'] == 'street' && isset($this->grid_item_data_model['street_id'])) {
            $select_what[] = DB_PREFIX . '_street.name as street';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_street ON ' . DB_PREFIX . '_street.street_id=' . DB_PREFIX . '_data.street_id';
        }


        if (isset($params['order']) && $params['order'] == 'type' && isset($this->grid_item_data_model['topic_id'])) {
            $select_what[] = DB_PREFIX . '_topic.name AS type_sh';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_topic ON ' . DB_PREFIX . '_topic.id=' . DB_PREFIX . '_data.topic_id';
        }


        //Подключать модель и проверять на наличие такого поля
        if (isset($params['srch_export_cian']) && $params['srch_export_cian'] == 1 && isset($this->grid_item_data_model['export_cian'])) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.export_cian=1)';
        } else {
            unset($params['srch_export_cian']);
        }

        if (isset($params['memorylist_id'])) {
            $mids = array();
            $DBC = \DBC::getInstance();
            $mquery = 'SELECT id FROM ' . DB_PREFIX . '_memorylist_item WHERE memorylist_id = ?';
            $stmt = $DBC->query($mquery, array($params['memorylist_id']));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $mids[] = $ar['id'];
                }
            }
            if (!empty($mids)) {
                $where_array_prepared[] = DB_PREFIX . '_data.id IN (' . implode(',', array_fill(0, count($mids), '?')) . ')';
                $where_value_prepared = array_merge($where_value_prepared, $mids);
            } else {
                $where_array_prepared[] = DB_PREFIX . '_data.id = -1';
            }
        }


        if (isset($params['favorites']) && !empty($params['favorites'])) {
            $favorites_array = $params['favorites'];
            foreach ($favorites_array as $k => $v) {
                if ((int)$v != 0) {
                    $favorites_array[$k] = (int)$v;
                } else {
                    unset($favorites_array[$k]);
                }
            }
            if (count($favorites_array) > 0) {
                $str_a = array();
                foreach ($favorites_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.id IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $favorites_array);
            }
        }

        if (isset($params['client_id']) && (int)$params['client_id'] != 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.client_id = ?)';
            $where_value_prepared[] = (int)$params['client_id'];
        } else {
            unset($params['client_id']);
        }

        if (isset($params['uniq_id']) && (int)$params['uniq_id'] != 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.uniq_id = ?)';
            $where_value_prepared[] = (int)$params['uniq_id'];
        } else {
            unset($params['uniq_id']);
        }

        if (isset($params['optype']) && is_array($params['optype'])) {
            $optypes_array = $params['optype'];
            foreach ($optypes_array as $k => $v) {
                if ((int)$v != 0) {
                    $optypes_array[$k] = (int)$v;
                } else {
                    unset($optypes_array[$k]);
                }
            }
            if (count($optypes_array) > 0) {
                $str_a = array();
                foreach ($optypes_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.optype IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $optypes_array);
            }
        } elseif (isset($params['optype']) && $params['optype'] > 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.optype = ?)';
            $where_value_prepared[] = (int)$params['optype'];
        }

        //$where_array_prepared[]='('.DB_PREFIX.'_topic.id='.DB_PREFIX.'_data.topic_id)';
        //echo '$params[\'topic_id\'] = '.$params['topic_id'].'<br>';

        if (isset($params['topic_id'])) {
            $topics = $params['topic_id'];

            if (!is_array($topics)) {
                $topics = (array)$topics;
            }
            if (!empty($topics)) {
                $list = array();
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $Structure_Manager = new \Structure_Manager();
                $category_structure = $Structure_Manager->loadCategoryStructure();
                foreach ($topics as $topic_id) {
                    if (intval($topic_id) > 0 && isset($category_structure['catalog'][$topic_id])) {
                        $childs = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                        if (!empty($childs)) {
                            $list = array_merge($list, $childs);
                        }
                        $list[] = intval($topic_id);
                    }
                }
            }

            if (!empty($list)) {
                $list = array_unique($list, SORT_NUMERIC);
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.topic_id IN (' . implode(',', array_fill(0, count($list), '?')) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $list);
            } else {
                unset($params['topic_id']);
            }
        }

        if (isset($params['wlocation']) && is_array($params['wlocation']) && !empty($params['wlocation'])) {
            $wsubquery = array();
            $wsubqueryparams = array();
            foreach ($params['wlocation'] as $wlocation) {
                $subquery = array();
                $subqueryparams = array();
                foreach ($wlocation as $k => $v) {
                    switch ($k) {
                        case 'country_id' :
                        {
                            $subquery[] = '' . DB_PREFIX . '_data.country_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'region_id' :
                        {
                            $subquery[] = '' . DB_PREFIX . '_data.region_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'city_id' :
                        {
                            $subquery[] = '' . DB_PREFIX . '_data.city_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'district_id' :
                        {
                            $subquery[] = '' . DB_PREFIX . '_data.district_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'street_id' :
                        {
                            $subquery[] = '' . DB_PREFIX . '_data.street_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'number' :
                        {
                            $subquery[] = '' . DB_PREFIX . '_data.number=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                    }
                }

                if (!empty($subquery)) {
                    $wsubquery[] = implode(' AND ', $subquery);
                    $wsubqueryparams = array_merge($wsubqueryparams, $subqueryparams);
                }

            }

            if (!empty($wsubquery)) {
                $where_array_prepared[] = '((' . implode(') OR (', $wsubquery) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $wsubqueryparams);
            }

        }

        if (isset($params['loc'])) {
            $pairs = array();
            foreach ($params['loc'] as $k => $loc) {
                $loc = urldecode($loc);
                if (preg_match('/^(\d+)\|(.*)/', $loc, $matches)) {
                    $sid = $matches[1];
                    $nid = preg_replace('/[^[a-zа-я0-9-] ]/i', '', trim($matches[2]));
                    if ($sid > 0 && $nid != '') {
                        $pairs[] = array('sid' => $sid, 'nid' => $nid);
                    }
                }
            }

            if (!empty($pairs)) {
                $q = array();
                $v = array();
                foreach ($pairs as $pair) {
                    $q[] = '(' . DB_PREFIX . '_data.street_id=? AND ' . DB_PREFIX . '_data.number=?)';
                    $v[] = $pair['sid'];
                    $v[] = $pair['nid'];
                }
                $where_array_prepared[] = '(' . implode(' OR ', $q) . ')';
                $where_value_prepared = array_merge($where_value_prepared, $v);

                unset($pairs);
                unset($q);
                unset($v);
            }

        }


        if (isset($params['country_id']) && (int)$params['country_id'] != 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.country_id=?)';
            $where_value_prepared[] = (int)$params['country_id'];
        } else {
            unset($params['country_id']);
        }

        if (isset($params['community_id'])) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/community/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/community/site/site.php';
            $CS = new \community_site();
            $ids = $CS->getCommunityUsersIds($params['community_id']);
            if (!empty($ids)) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN (' . implode(',', array_fill(0, count($ids), '?')) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $ids);
            } else {
                $where_array_prepared[] = '1=0';
            }
        }

        if (isset($params['complex_id'])) {
            if (is_array($params['complex_id'])) {
                $complex_array = $params['complex_id'];
                foreach ($complex_array as $k => $v) {
                    if ((int)$v != 0) {
                        $complex_array[$k] = (int)$v;
                    } else {
                        unset($complex_array[$k]);
                    }
                }
                if (count($complex_array) > 0) {
                    $str_a = array();
                    foreach ($complex_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.complex_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $complex_array);
                } else {
                    unset($params['complex_id']);
                }
            } else {
                if (intval($params['complex_id']) > 0) {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.`complex_id`=?)';
                    $where_value_prepared[] = intval($params['complex_id']);
                } else {
                    unset($params['complex_id']);
                }
            }
        }

        if (isset($params['complex_building_id'])) {
            if (is_array($params['complex_building_id'])) {
                $complex_array = $params['complex_building_id'];
                foreach ($complex_array as $k => $v) {
                    if ((int)$v != 0) {
                        $complex_array[$k] = (int)$v;
                    } else {
                        unset($complex_array[$k]);
                    }
                }
                if (count($complex_array) > 0) {
                    $str_a = array();
                    foreach ($complex_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.complex_building_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $complex_array);
                } else {
                    unset($params['complex_building_id']);
                }
            } else {
                if (intval($params['complex_building_id']) > 0) {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.`complex_building_id`=?)';
                    $where_value_prepared[] = intval($params['complex_building_id']);
                } else {
                    unset($params['complex_building_id']);
                }
            }
        }

        if (isset($params['id']) && is_array($params['id'])) {

            if (!empty($params['id'])) {
                $str_a = array();
                foreach ($params['id'] as $k => $_id) {
                    if ((int)$_id != 0) {
                        $str_a[] = '?';
                    } else {
                        unset($params['id'][$k]);
                    }
                }
                if (!empty($params['id'])) {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $params['id']);
                }
            } else {
                unset($params['id']);
            }
        } elseif (isset($params['id'])) {
            if ((int)$params['id'] != 0) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.id=?)';
                $where_value_prepared[] = (int)$params['id'];
            } else {
                unset($params['id']);
            }
        }

        //echo $_SESSION['user_domain_owner'];
        if (isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id'] != 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=?)';
            $where_value_prepared[] = (int)$_SESSION['user_domain_owner']['user_id'];
        } else {
            if (isset($params['user_id'])) {
                if (isset($params['coworked_ids']) && !empty($params['coworked_ids'])) {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=? OR ' . DB_PREFIX . '_data.id IN (' . implode(',', array_fill(0, count($params['coworked_ids']), '?')) . '))';
                    $where_value_prepared[] = (int)$params['user_id'];
                    $where_value_prepared = array_merge($where_value_prepared, $params['coworked_ids']);
                } elseif (isset($params['coworked_users']) && !empty($params['coworked_users'])) {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=? OR ' . DB_PREFIX . '_data.user_id IN (' . implode(',', array_fill(0, count($params['coworked_users']), '?')) . '))';
                    $where_value_prepared[] = (int)$params['user_id'];
                    $where_value_prepared = array_merge($where_value_prepared, $params['coworked_users']);
                } else {
                    if (is_array($params['user_id'])) {
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN (' . implode(',', array_fill(0, count($params['user_id']), '?')) . '))';
                        $where_value_prepared = array_merge($where_value_prepared, $params['user_id']);
                    } else {
                        if ((int)$params['user_id'] > 0) {
                            $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=?)';
                            $where_value_prepared[] = (int)$params['user_id'];
                        }

                    }

                }
            }
        }


        if (isset($params['agg_user_id'])) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN (' . implode(',', array_fill(0, count($params['agg_user_id']), '?')) . '))';
            $where_value_prepared = array_merge($where_value_prepared, array_values($params['agg_user_id']));
            unset($params['agg_user_id']);
        }


        if (isset($params['onlyspecial']) && (int)$params['onlyspecial'] > 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot=1)';
        } else {
            unset($params['onlyspecial']);
        }

        if (isset($params['dv_ipoteka']) && (int)$params['dv_ipoteka'] > 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.dv_ipoteka=1)';
        } else {
            unset($params['dv_ipoteka']);
        }

        if (isset($params['rent_period']) && (int)$params['rent_period'] > 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.rent_period=?)';
            $where_value_prepared[] = intval($params['rent_period']);
        } else {
            unset($params['rent_period']);
        }

        if (isset($params['review_video_enable']) && (int)$params['review_video_enable'] > 0) {
            $keyPartial = 'reviewvideo_';

            $or_review_video = [];
            for ( $i = 10; $i <= 41; $i++ ) {
                $or_review_video[] = DB_PREFIX . '_data.'.$keyPartial.$i.' <> '."''";
            }

            $where_array_prepared[] = '('.implode(' OR ', $or_review_video). ')';
            unset($params['review_video_enable']);

        } else {
            unset($params['review_video_enable']);
        }

        $skip_normal_price = false;

        if (isset($params['start_date']) && $params['start_date'] != '' and ($params['price_min'] > 0 or $params['price'] > 0)) {
            $skip_normal_price = true;

            $select_what[] = "re_data.price as fake_reservation_amount";

            /*
            $select_what[] = "COALESCE(re_reservation_rate_regular_period.amount, re_reservation_rate.amount) as current_reservation_amount";


            // create view re_reservation_rate_regular_period as select * from re_reservation_rate where rate_type=30;

            $left_joins[] = "\n".'LEFT JOIN ' . DB_PREFIX . '_reservation_rate_regular_period ON
            ' . DB_PREFIX . '_reservation_rate_regular_period.id=' . DB_PREFIX . '_data.id';
            //AND ' . DB_PREFIX . '_reservation_rate_regular_period.amount <= '.intval($params['price']);


            $left_joins[] = "\n".'INNER JOIN ' . DB_PREFIX . '_reservation_rate ON
            ' . DB_PREFIX . '_reservation_rate.id=' . DB_PREFIX . '_data.id
            AND ' . DB_PREFIX . '_reservation_rate.period_start = \'\' AND ' . DB_PREFIX . '_reservation_rate.rate_type = 10 AND ' . DB_PREFIX . '_reservation_rate.period_end = \'\'';
            //AND ' . DB_PREFIX . '_reservation_rate.amount <= '.intval($params['price']);



            if ( $params['price'] > 0 ) {
                //$skip_normal_price = true;
                //$where_array_prepared[] = '(' . DB_PREFIX . '_reservation_rate_regular_period.amount <= '.intval($params['price']).')';
                //$where_array_prepared[] = '(' . DB_PREFIX . '_reservation_rate.amount <= '.intval($params['price']).')';
            }



            // @todo: Это заготовка для поиска по периодам
            $where_array_prepared[] = '
            (
            (
             ' . DB_PREFIX . '_reservation_rate.period_start = \'\' AND ' . DB_PREFIX . '_reservation_rate.period_end = \'\'  AND ' . DB_PREFIX . '_reservation_rate.rate_type = 10
            ) OR
            (
             DATE_FORMAT(\''.$params['start_date'].'\', "%m-%d")
            BETWEEN ' . DB_PREFIX . '_reservation_rate_regular_period.period_start AND ' . DB_PREFIX . '_reservation_rate_regular_period.period_end
            )

            )
            ';
            //print_r($left_joins);
            */

        } else {
            //unset($params['start_date']);
        }

        if (isset($params['end_date']) && $params['end_date'] != '') {
            // $where_array_prepared[] = '(' . DB_PREFIX . '_data.dv_ipoteka=1)';
        } else {
            //unset($params['end_date']);
        }



        if (isset($params['price']) && $params['price'] != 0 && !$skip_normal_price) {

            //$price_str=preg_replace('/[^\d.,]/', '', $params['price']);
            $price_str = (int)str_replace(' ', '', $params['price']);
            if ($this->use_currency) {
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')<=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price<=?)';
                $where_value_prepared[] = $price_str;
            }

        } else {
            unset($params['price']);
        }


        if (isset($params['price_min']) && $params['price_min'] != 0 && !$skip_normal_price) {
            $price_str = (int)str_replace(' ', '', $params['price_min']);
            if ($this->use_currency) {
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')>=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price>=?)';
                $where_value_prepared[] = $price_str;
            }
        } else {
            unset($params['price_min']);
        }
        ////
        if (isset($params['price_pm']) && $params['price_pm'] != 0) {
            $price_str = (int)str_replace(' ', '', $params['price_pm']);

            if ($this->use_currency) {
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price_pm*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')<=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price_pm<=?)';
                $where_value_prepared[] = $price_str;
            }
        } else {
            unset($params['price_pm']);
        }
        if (isset($params['price_pm_min']) && $params['price_pm_min'] != 0) {
            $price_str = (int)str_replace(' ', '', $params['price_pm_min']);

            if ($this->use_currency) {
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price_pm*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')>=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price_pm>=?)';
                $where_value_prepared[] = $price_str;
            }
        } else {
            unset($params['price_pm_min']);
        }
        //////
        if (isset($params['house_number']) && $params['house_number'] != '') {
            $number = trim($params['house_number']);
            $number = preg_replace('/[^[a-zа-я0-9-] ]/i', '', $number);
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.number=?)';
            $where_value_prepared[] = $number;
        } else {
            unset($params['house_number']);
        }

        if (isset($params['region_id']) && (int)$params['region_id'] != 0) {
            if (is_array($params['region_id']) && !empty($params['region_id'])) {
                $regions_array = $params['region_id'];
                foreach ($regions_array as $k => $v) {
                    if ((int)$v != 0) {
                        $regions_array[$k] = (int)$v;
                    } else {
                        unset($regions_array[$k]);
                    }
                }
                if (count($regions_array) > 0) {
                    $str_a = array();
                    foreach ($regions_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.region_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $regions_array);
                }
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.region_id=?)';
                $where_value_prepared[] = (int)$params['region_id'];
            }
        } else {
            unset($params['region_id']);
        }

        if (isset($params['spec']) && $params['spec'] != '') {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot=1)';
        } else {
            unset($params['spec']);
        }
        if (isset($params['hot']) && $params['hot'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot = 1)';
        } elseif (isset($params['hot']) && $params['hot'] == -1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot <> 1)';
        } else {
            unset($params['hot']);
        }

        if (isset($params['district_id']) && $params['district_id'] != 0) {
            if (is_array($params['district_id']) && !empty($params['district_id'])) {
                $districts_array = $params['district_id'];
                foreach ($districts_array as $k => $v) {
                    if ((int)$v != 0) {
                        $districts_array[$k] = (int)$v;
                    } else {
                        unset($districts_array[$k]);
                    }
                }
                if (count($districts_array) > 0) {
                    $str_a = array();
                    foreach ($districts_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.district_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $districts_array);
                }
                unset($districts_array);
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.district_id=?)';
                $where_value_prepared[] = (int)$params['district_id'];
            }
        } else {
            unset($params['district_id']);
        }

        if (isset($params['city_id']) && $params['city_id'] != 0) {
            if (is_array($params['city_id']) && !empty($params['city_id'])) {
                $city_array = $params['city_id'];
                foreach ($city_array as $k => $v) {
                    if ((int)$v != 0) {
                        $city_array[$k] = (int)$v;
                    } else {
                        unset($city_array[$k]);
                    }
                }
                if (count($city_array) > 0) {
                    $str_a = array();
                    foreach ($city_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.city_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $city_array);
                }
                unset($city_array);
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.city_id=?)';
                $where_value_prepared[] = (int)$params['city_id'];
            }
        } else {
            unset($params['city_id']);
        }

        if (isset($params['metro_id']) and $params['metro_id'] != 0) {
            if (is_array($params['metro_id']) && !empty($params['metro_id'])) {
                $metro_array = $params['metro_id'];
                foreach ($metro_array as $k => $v) {
                    if ((int)$v != 0) {
                        $metro_array[$k] = (int)$v;
                    } else {
                        unset($metro_array[$k]);
                    }
                }
                if (count($metro_array) > 0) {
                    $str_a = array();
                    foreach ($metro_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.metro_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $metro_array);
                }
                unset($metro_array);
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.metro_id=?)';
                $where_value_prepared[] = (int)$params['metro_id'];
            }
        } else {
            unset($params['metro_id']);
        }

        if (isset($params['street_id']) and $params['street_id'] != 0) {
            if (is_array($params['street_id']) && !empty($params['street_id'])) {
                $street_array = $params['street_id'];
                foreach ($street_array as $k => $v) {
                    if ((int)$v != 0) {
                        $street_array[$k] = (int)$v;
                    } else {
                        unset($street_array[$k]);
                    }
                }
                if (count($street_array) > 0) {
                    $str_a = array();
                    foreach ($street_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.street_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $street_array);
                }
                unset($street_array);
            } else {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.street_id=?)';
                $where_value_prepared[] = (int)$params['street_id'];
            }
        } else {
            unset($params['street_id']);
        }


        if (isset($params['srch_phone']) && $params['srch_phone'] !== NULL && trim($params['srch_phone']) !== '') {
            $phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
            $sub_where = array();
            $where_array_prepared_sub = array();
            if ($this->getConfigValue('allow_additional_mobile_number')) {
                $sub_where[] = '(re_data.ad_mobile_phone LIKE \'%' . $phone . '%\')';

                $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.ad_mobile_phone LIKE ?)';
                $where_value_prepared[] = '%' . $phone . '%';
            }
            if ($this->getConfigValue('allow_additional_stationary_number')) {
                $sub_where[] = '(re_data.ad_stacionary_phone LIKE \'%' . $phone . '%\')';

                $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.ad_stacionary_phone LIKE ?)';
                $where_value_prepared[] = '%' . $phone . '%';
            }
            $sub_where[] = '(re_data.phone LIKE \'%' . $phone . '%\')';


            $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.phone LIKE ?)';
            $where_value_prepared[] = '%' . $phone . '%';
            $where_array_prepared[] = '(' . implode(' OR ', $where_array_prepared_sub) . ')';
        } else {
            unset($params['srch_phone']);
        }

        if (isset($params['srch_word']) and $params['srch_word'] !== NULL) {
            $sub_where = array();
            $where_array_prepared_sub = array();

            $word = htmlspecialchars($params['srch_word']);
            if ($word != '') {
                $sub_where[] = '(re_data.text LIKE \'%' . $word . '%\')';

                $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.text LIKE ?)';
                $where_value_prepared[] = '%' . $word . '%';


                $where_array_prepared[] = '(' . implode(' OR ', $where_array_prepared_sub) . ')';
            }
        } else {
            unset($params['srch_word']);
        }

        if (isset($params['room_count'])) {
            if (is_array($params['room_count']) && count($params['room_count']) > 0) {
                $sub_where = array();
                $where_array_prepared_sub = array();
                foreach ($params['room_count'] as $rq) {
                    if ($rq == 4) {
                        $sub_where[] = 'room_count>3';
                        $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.room_count>3)';
                    } elseif (0 != (int)$rq) {
                        $sub_where[] = 'room_count=' . (int)$rq;
                        $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.room_count=?)';
                        $where_value_prepared[] = (int)$rq;
                    }
                }
                if (count($sub_where) > 0) {
                    $where_array_prepared[] = '(' . implode(' OR ', $where_array_prepared_sub) . ')';
                }
            } elseif ((int)$params['room_count'] != 0) {
                $where_value_prepared[] = (int)$params['room_count'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.room_count=?)';
            } else {
                unset($params['room_count']);
            }
        }

        if (isset($params['added_in_days']) && 0 != (int)$params['added_in_days']) {
            $date_limit = time() - ((int)$params['added_in_days']) * 24 * 3600;
            $where_value_prepared[] = date('Y-m-d H:i:s', $date_limit);
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.date_added>=?)';
        } else {
            unset($params['added_in_days']);
        }


        if (isset($params['srch_date_to'])) {
            $srch_date_to = '';
            if (preg_match('/^(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)$/', $params['srch_date_to'])) {
                $srch_date_to = $params['srch_date_to'];
            } elseif (preg_match('/^(\d\d\d\d-\d\d-\d\d)$/', $params['srch_date_to'])) {
                $srch_date_to = $params['srch_date_to'];
            } else {

            }
            if ($srch_date_to != '') {
                $where_value_prepared[] = $srch_date_to;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.date_added<=?)';
            } else {
                unset($params['srch_date_to']);
            }
        }

        if (isset($params['srch_date_from'])) {
            $srch_date_from = '';
            if (preg_match('/^(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)$/', $params['srch_date_from'])) {
                $srch_date_from = $params['srch_date_from'];
            } elseif (preg_match('/^(\d\d\d\d-\d\d-\d\d)$/', $params['srch_date_from'])) {
                $srch_date_from = $params['srch_date_from'];
            } else {

            }
            if ($srch_date_from != '') {
                $where_value_prepared[] = $srch_date_from;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.date_added>=?)';
            } else {
                unset($params['srch_date_from']);
            }
        }

        $number_search_items = [
            'land_area_min',
            'land_area_max',
        ];

        foreach ($number_search_items as $number_item) {
            if (!empty($this->request()->get($number_item))) {
                $params[$number_item] = $this->request()->get($number_item);
            }
            $number_column_name = preg_replace('/_min|_max/', '', $number_item);
            if (preg_match('/_min/', $number_item)) {
                $number_operator = '>=';
            } elseif (preg_match('/_max/', $number_item)) {
                $number_operator = '<=';
            } else {
                unset($params[$number_item]);
            }

            if (isset($params[$number_item]) && (int)$params[$number_item] != 0) {
                $where_value_prepared[] = (int)$params[$number_item];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.' . $number_column_name . '*1 ' . $number_operator . ' ?)';
            } else {
                unset($params[$number_item]);
            }
        }

        if (isset($params['floor_min']) && (int)$params['floor_min'] != 0) {
            $where_value_prepared[] = (int)$params['floor_min'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 >= ?)';
        } else {
            unset($params['floor_min']);
        }

        if (isset($params['floor_max']) && (int)$params['floor_max'] != 0) {
            $where_value_prepared[] = (int)$params['floor_max'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 <= ?)';
        } else {
            unset($params['floor_max']);
        }

        if (isset($params['floor_count_min']) && (int)$params['floor_count_min'] != 0) {
            $where_value_prepared[] = (int)$params['floor_count_min'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor_count*1 >= ?)';
        } else {
            unset($params['floor_count_min']);
        }

        if (isset($params['floor_count_max']) && (int)$params['floor_count_max'] != 0) {
            $where_value_prepared[] = (int)$params['floor_count_max'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor_count*1 <= ?)';
        } else {
            unset($params['floor_count_max']);
        }


        if (isset($params['square_min']) && (int)$params['square_min'] != 0) {
            $square_min = preg_replace('/[^\d.,]/', '', $params['square_min']);
            $where_value_prepared[] = $square_min;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_all*1 >= ?)';
        } else {
            unset($params['square_min']);
        }

        if (isset($params['square_max']) && (int)$params['square_max'] != 0) {
            $square_max = preg_replace('/[^\d.,]/', '', $params['square_max']);
            $where_value_prepared[] = $square_max;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_all*1 <= ?)';
        } else {
            unset($params['square_max']);
        }


        if (isset($params['not_first_floor']) && (int)$params['not_first_floor'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 > 1)';
        } else {
            unset($params['not_first_floor']);
        }

        if (isset($params['not_last_floor']) && (int)$params['not_last_floor'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 > 0 AND ' . DB_PREFIX . '_data.floor*1 <> ' . DB_PREFIX . '_data.floor_count*1)';
        } else {
            unset($params['not_last_floor']);
        }

        if (isset($params['live_square_min']) && $params['live_square_min'] != 0 && $params['live_square_min'] !== '') {
            $square_min = preg_replace('/[^\d.,]/', '', $params['live_square_min']);
            $where_value_prepared[] = $square_min;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_live*1>= ?)';
        } else {
            unset($params['live_square_min']);
        }

        if (isset($params['live_square_max']) && $params['live_square_max'] != 0 && $params['live_square_max'] !== '') {
            $square_max = preg_replace('/[^\d.,]/', '', $params['live_square_max']);
            $where_value_prepared[] = $square_max;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_live*1<= ?)';
        } else {
            unset($params['live_square_max']);
        }


        if (isset($params['kitchen_square_min']) && $params['kitchen_square_min'] != 0 && $params['kitchen_square_min'] !== '') {
            $square_min = preg_replace('/[^\d.,]/', '', $params['kitchen_square_min']);
            $where_value_prepared[] = $square_min;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_kitchen*1>= ?)';
        } else {
            unset($params['kitchen_square_min']);
        }

        if (isset($params['kitchen_square_max']) && $params['kitchen_square_max'] != 0 && $params['kitchen_square_max'] !== '') {
            $square_max = preg_replace('/[^\d.,]/', '', $params['kitchen_square_max']);
            $where_value_prepared[] = $square_max;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_kitchen*1<= ?)';
        } else {
            unset($params['kitchen_square_max']);
        }


        if (isset($params['is_phone']) && (int)$params['is_phone'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.is_telephone=1)';
        } else {
            unset($params['is_phone']);
        }

        if (isset($params['is_internet']) && (int)$params['is_internet'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.is_internet=1)';
        } else {
            unset($params['is_internet']);
        }

        if (isset($params['is_furniture']) && (int)$params['is_furniture'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.furniture=1)';
        } else {
            unset($params['is_furniture']);
        }

        if (isset($params['owner']) && (int)$params['owner'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.whoyuaare=1)';
        } else {
            unset($params['owner']);
        }

        if (isset($params['status_id']) && isset($this->grid_item_data_model['status_id']) && intval($params['status_id']) > 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.status_id=?)';
            $where_value_prepared[] = intval($params['status_id']);
        } else {
            unset($params['status_id']);
        }

        if (isset($params['has_photo']) && (int)$params['has_photo'] == 1) {
            //print_r($_model);
            $hasUploadify = false;
            $hasUploads = false;
            $uploadsFields = array();
            foreach ($this->grid_item_data_model as $item) {
                if ($item['type'] == 'uploadify_image') {
                    $hasUploadify = true;
                    break;
                } elseif ($item['type'] == 'uploads') {
                    $hasUploads = true;
                    $uploadsFields[] = $item['name'];
                }
            }

            //print_r($uploadsFields);

            if ($hasUploadify) {
                $where_array_prepared[] = '((SELECT COUNT(*) FROM ' . DB_PREFIX . '_data_image WHERE id=' . DB_PREFIX . '_data.id)>0)';
            } elseif ($hasUploads) {
                $sub_query = array();
                foreach ($uploadsFields as $uf) {
                    $sub_query[] = DB_PREFIX . '_data.`' . $uf . '`<>\'\'';
                }
                $where_array_prepared[] = '(' . implode(' OR ', $sub_query) . ')';
            }
        } else {
            unset($params['has_photo']);
        }

        if (isset($params['infra_greenzone']) && (int)$params['infra_greenzone'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_greenzone=1)';
        } else {
            unset($params['infra_greenzone']);
        }

        if (isset($params['infra_sea']) && (int)$params['infra_sea'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_sea=1)';
        } else {
            unset($params['infra_sea']);
        }

        if (isset($params['infra_sport']) && (int)$params['infra_sport'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_sport=1)';
        } else {
            unset($params['infra_sport']);
        }

        if (isset($params['infra_clinic']) && (int)$params['infra_clinic'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_clinic=1)';
        } else {
            unset($params['infra_clinic']);
        }

        if (isset($params['infra_terminal']) && (int)$params['infra_terminal'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_terminal=1)';
        } else {
            unset($params['infra_terminal']);
        }

        if (isset($params['infra_airport']) && (int)$params['infra_airport'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_airport=1)';
        } else {
            unset($params['infra_airport']);
        }

        if (isset($params['infra_bank']) && (int)$params['infra_bank'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_bank=1)';
        } else {
            unset($params['infra_bank']);
        }

        if (isset($params['infra_restaurant']) && (int)$params['infra_restaurant'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_restaurant=1)';
        } else {
            unset($params['infra_restaurant']);
        }
        //

        if (isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state']) > 0) {
            $state_array = $params['object_state'];
            foreach ($state_array as $k => $v) {
                if ((int)$v != 0) {
                    $state_array[$k] = (int)$v;
                } else {
                    unset($state_array[$k]);
                }
            }
            if (count($state_array) > 0) {
                $str_a = array();
                foreach ($state_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.object_state IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $state_array);
            }
        } else {
            unset($params['object_state']);
        }

        if (isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type']) > 0) {
            $state_array = $params['object_type'];
            foreach ($state_array as $k => $v) {
                if ((int)$v != 0) {
                    $state_array[$k] = (int)$v;
                } else {
                    unset($state_array[$k]);
                }
            }
            if (count($state_array) > 0) {
                $str_a = array();
                foreach ($state_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.object_destination IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $state_array);
            }
        } else {
            unset($params['object_type']);
        }

        if (isset($params['aim']) && is_array($params['aim']) && count($params['aim']) > 0) {
            $state_array = $params['aim'];
            foreach ($state_array as $k => $v) {
                if ((int)$v != 0) {
                    $state_array[$k] = (int)$v;
                } else {
                    unset($state_array[$k]);
                }
            }
            if (count($state_array) > 0) {
                $str_a = array();
                foreach ($state_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.aim IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $state_array);
            }
        } else {
            unset($params['aim']);
        }

        if (isset($params['export_afy']) && (int)$params['export_afy'] == 1 && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/afyexporter/admin/admin.php')) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.export_afy=1)';
        } else {
            unset($params['export_afy']);
        }

        if (isset($params['export_cian']) && (int)$params['export_cian'] == 1 && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/cianexporter/admin/admin.php')) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.export_cian=1)';
        } else {
            unset($params['export_cian']);
        }


        if (isset($params['map_bounds'])) {
            $where_array_prepared[] = '((' . DB_PREFIX . '_data.geo_lat BETWEEN ? AND ?) AND (' . DB_PREFIX . '_data.geo_lng BETWEEN ? AND ?))';
            $where_value_prepared[] = $params['map_bounds'][0][0];
            $where_value_prepared[] = $params['map_bounds'][1][0];
            $where_value_prepared[] = $params['map_bounds'][0][1];
            $where_value_prepared[] = $params['map_bounds'][1][1];
        }
        if ($this->request()->get('polylineString') and is_scalar($this->request()->get('polylineString'))) {
            $polylineString = $this->request()->get('polylineString');
            $polylineStringPairs = $this->fromStringToPolylineStringPairs($polylineString);
            $min_max_coords = $this->coords_to_min_max($polylineStringPairs);
            $max_lat = $min_max_coords['max_lat'];
            $min_lat = $min_max_coords['min_lat'];
            $max_lng = $min_max_coords['max_lng'];
            $min_lng = $min_max_coords['min_lng'];

            $where_array_prepared[] = '( ' . DB_PREFIX . '_data.geo_lat>=? AND ' . DB_PREFIX . '_data.geo_lat<=? AND ' . DB_PREFIX . '_data.geo_lng>=? AND ' . DB_PREFIX . '_data.geo_lng<=? )';
            $where_value_prepared[] = $min_lat;
            $where_value_prepared[] = $max_lat;
            $where_value_prepared[] = $min_lng;
            $where_value_prepared[] = $max_lng;
        }


        //Сомнительно
        if (isset($params['geocoords'])) {
            if (preg_match('/([-]?[0-9]{2,3}\.[0-9]{6}),([-]?[0-9]{2,3}\.[0-9]{6}):([-]?[0-9]{2,3}\.[0-9]{6}),([-]?[0-9]{2,3}\.[0-9]{6})/', $params['geocoords'], $matches)) {
                //print_r();
                $lat_min = $matches[1];
                $lng_min = $matches[2];
                $lat_max = $matches[3];
                $lng_max = $matches[4];
                $diapasones = array();
                if ($lng_min > 0 && $lng_max < 0) {
                    $diapasones[] = array(
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lng_min' => $lng_min,
                        'lng_max' => 180
                    );
                    $diapasones[] = array(
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lng_min' => -180,
                        'lng_max' => $lng_max
                    );
                } else {
                    $diapasones[] = array(
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lng_min' => $lng_min,
                        'lng_max' => $lng_max
                    );
                }

                $where_array_prepared[] = '(' . DB_PREFIX . '_data.geo_lat IS NOT NULL AND ' . DB_PREFIX . '_data.geo_lng IS NOT NULL)';

                $subarray = array();
                foreach ($diapasones as $diapasone) {

                    $subarray[] = '(' . DB_PREFIX . '_data.geo_lat >=? AND ' . DB_PREFIX . '_data.geo_lat <= ? AND ' . DB_PREFIX . '_data.geo_lng >=? AND ' . DB_PREFIX . '_data.geo_lng <= ?)';
                    $where_value_prepared[] = $diapasone['lat_min'];
                    $where_value_prepared[] = $diapasone['lat_max'];
                    $where_value_prepared[] = $diapasone['lng_min'];
                    $where_value_prepared[] = $diapasone['lng_max'];
                }

                $where_array_prepared[] = '(' . implode(' OR ', $subarray) . ')';
            }
        } elseif (isset($params['has_geo']) && (int)$params['has_geo'] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.geo_lat IS NOT NULL AND ' . DB_PREFIX . '_data.geo_lng IS NOT NULL)';
        } else {
            unset($params['has_geo']);
        }

        if (isset($params['minbeds']) && (int)$params['minbeds'] != 0) {
            $where_value_prepared[] = (int)$params['minbeds'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.bedrooms_count>=?)';
        } else {
            unset($params['minbeds']);
        }

        if (isset($params['minbaths']) && (int)$params['minbaths'] != 0) {
            $where_value_prepared[] = (int)$params['minbaths'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.bathrooms_count>=?)';
        } else {
            unset($params['minbaths']);
        }

        if (isset($params['vip_status']) && (int)$params['vip_status'] != 0) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= ?)';
            $where_value_prepared[] = $_time;
        } else {
            unset($params['vip_status']);
        }

        if (isset($params['premium_status']) && (int)$params['premium_status'] != 0) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= ?)';
            $where_value_prepared[] = $_time;
        } else {
            unset($params['premium_status']);
        }

        if (isset($params['bold_status']) && (int)$params['bold_status'] != 0) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.bold_status_end<>0 AND ' . DB_PREFIX . '_data.bold_status_end >= ?)';
            $where_value_prepared[] = $_time;
        } else {
            unset($params['bold_status']);
        }

        if (!isset($params['admin']) || (isset($params['admin']) && $params['admin'] != 1)) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.`active`=1)';
            if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting') && isset($this->grid_item_data_model['archived'])) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`<>1)';
            }
            //echo $_SESSION['current_user_group_name'];
        } else {
            if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting') && @$params['archived'] == 1 && isset($this->grid_item_data_model['archived'])) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=1)';
            } elseif (1 == (int)$this->getConfigValue('apps.realty.use_predeleting') && isset($this->grid_item_data_model['archived'])) {

                if (defined('ADMIN_MODE') && ADMIN_MODE == 1) {
                    if (isset($params['active']) && $params['active'] == 1) {
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=0)';
                    } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=0)';
                    } else {
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=0)';
                    }
                } else {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`<>1)';
                }

            }

            if (isset($params['active']) && $params['active'] == 1) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`active`=1)';
            } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`active`=0)';
            }
        }


        if ($this->getConfigValue('apps.company.timelimit')) {
            $current_time = time();
            $add_from_table .= ' , re_user u, re_company c ';

            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id)';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_company c ON u.company_id=c.company_id';
            $where_array_prepared[] = '(c.start_date<=?)';
            $where_value_prepared[] = $current_time;
            $where_array_prepared[] = '(c.end_date >=?)';
            $where_value_prepared[] = $current_time;
        }


        if ($this->billing_mode && $premium) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_value_prepared[] = $_time;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end >= ?)';
        } elseif ($this->billing_mode && $params['vip'] == 1) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_value_prepared[] = $_time;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= ?)';
        } elseif ($this->billing_mode && $params['premium'] == 1) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_value_prepared[] = $_time;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= ?)';
        } elseif ($_billing_on && $params['admin'] == 1) {

        } elseif ($this->billing_mode) {
            if (!isset($params['no_premium_filtering']) && 1 != $this->getConfigValue('apps.billing.disable_premium_popup')) {
                $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
                $where_value_prepared[] = $_time;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end < ?)';
            }
        }

        if (isset($params['only_img']) && $params['only_img']) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.id=i.id)';
            $add_from_table .= ' , re_data_image i ';
        }

        [$params,  $where_array_prepared] = $this->prepareRequestParamsFromSconfig ($params,  $where_array_prepared);

        return array(
            'where_array' => $where_array,
            'add_from_table' => $add_from_table,
            'add_select_value' => $add_select_value,
            'params' => $params,
            'where_array_prepared' => $where_array_prepared,
            'where_value_prepared' => $where_value_prepared,
            'left_joins' => $left_joins,
            'select_what' => $select_what
        );
    }

    private function prepareRequestParamsFromSconfig ($params,  $where_array_prepared) {

        if ( \SConfig::getConfigValueStatic('searchable_params') !== null and is_array(\SConfig::getConfigValueStatic('searchable_params')) ) {
            foreach ( \SConfig::getConfigValueStatic('searchable_params') as $param ) {
                [$params, $where_array_prepared] = $this->checkbox_where_mutator($param, $params, $where_array_prepared);
            }
        }
        return [$params, $where_array_prepared];
    }



}
