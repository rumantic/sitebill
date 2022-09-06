<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class dropzone_model_adapter extends Data_Manager_Export
{

    function __construct($table_name, $action, $primary_key, $data_model)
    {
        parent::__construct();
        $this->user_mode = $user_mode;
        $this->table_name = $table_name;
        $this->action = $action;
        $this->primary_key = $primary_key;
        $this->data_model = $data_model;
        $this->model = new Data_Model();
    }

    function get_primary_key()
    {
        return $this->primary_key;
    }

    function init_request_from_xls($assoc_array, $data)
    {

        $model_array = $this->get_model(true);
        $tlocation_data = array(
            'country_id' => '',
            'district_id' => '',
            'region_id' => '',
            'city_id' => '',
            'street_id' => ''
        );

        if(isset($data['country_id'])){
            $tlocation_data['country_id'] = $data['country_id'];
        }
        if(isset($data['district_id'])){
            $tlocation_data['district_id'] = $data['district_id'];
        }
        if(isset($data['region_id'])){
            $tlocation_data['region_id'] = $data['region_id'];
        }
        if(isset($data['city_id'])){
            $tlocation_data['city_id'] = $data['city_id'];
        }
        if(isset($data['street_id'])){
            $tlocation_data['street_id'] = $data['street_id'];
        }

        //Проверим, есть ли в assoc_array маппинг для полей
        foreach ($tlocation_data as $key_tlocation => $data_array) {
            if (isset($assoc_array[$key_tlocation]) && $assoc_array[$key_tlocation] != $key_tlocation) {
                $tlocation_data[$key_tlocation] = $data[$assoc_array[$key_tlocation]];
            }
        }

        unset($tlocation_data[$this->get_primary_key()]);

        $tld = $this->createTLocationData($tlocation_data);
        foreach ($tld as $kk => $vv) {
            if ($kk != $this->get_primary_key()) {
                $this->setRequestValue($kk, $vv);
            }
        }

        foreach ($assoc_array as $key => $value) {
            if (in_array($key, array('country_id', 'district_id', 'region_id', 'city_id', 'street_id'))) {
                continue;
                //break;
            }
            if ($model_array[$key]['type'] == 'select_by_query') {
                $id = $this->get_value_id_by_name($model_array[$key]['primary_key_table'], $model_array[$key]['value_name'], $model_array[$key]['primary_key_name'], $data[$value]);
                if (empty($id)) {
                    $id = $this->add_value($model_array[$key]['primary_key_table'], $model_array[$key]['value_name'], $model_array[$key]['primary_key_name'], $data[$value]);
                }
                $this->setRequestValue($key, $id);
            } elseif ($model_array[$key]['type'] == 'structure_chain' || $model_array[$key]['type'] == 'select_box_structure') {
                $chain = $data[$value];
                if (empty($chain)) {
                    $chain = $this->category_not_defined_title;
                }
                $chain = mb_strtolower($chain, SITE_ENCODING);
                $x = $this->getCatalogChains();
                $catalogChain = $x['txt'];
                $catalogChainRev = array_flip($catalogChain);
                if (isset($catalogChainRev[$chain])) {
                    $this->setRequestValue($key, $catalogChainRev[$chain]);
                } else {
                    $this->setRequestValue($key, $this->createTopicPoints($chain));
                }
            } elseif ($model_array[$key]['type'] == 'select_box') {
                if (!empty($model_array[$key]['select_data'])) {
                    foreach ($model_array[$key]['select_data'] as $k => $v) {
                        if ($v == $data[$value]) {
                            $this->setRequestValue($key, $k);
                            break;
                        }
                    }
                }
            } elseif ($model_array[$key]['type'] == 'select_by_query_multi') {
                $this->setRequestValue($key, $this->get_multi_ids_from_string($data[$value], $model_array[$key]));
            } elseif ($model_array[$key]['type'] == 'geodata') {
                $geodata_name = $model_array[$key]['name'];
                $geodata = array();
                $geodata = explode(',', $data[$value]);
                if (count($geodata) > 1) {
                    if (preg_match('/^(-?)([0-9]?)([0-9])((\.?)(\d*)?)$/', trim($geodata[0]))) {
                        $lat = trim($geodata[0]);
                    } else {
                        $lat = '';
                    }
                    if (preg_match('/^(-?)([0-9]?)([0-9]?)([0-9])((\.?)(\d*)?)$/', trim($geodata[1]))) {
                        $lng = trim($geodata[1]);
                    } else {
                        $lng = '';
                    }
                    if ($lat != '' && $lng != '') {
                        $this->setRequestValue($key, array('lat' => $lat, 'lng' => $lng));
                    } else {
                        $this->setRequestValue($key, array('lat' => '', 'lng' => ''));
                    }
                } else {
                    $this->setRequestValue($key, array('lat' => '', 'lng' => ''));
                }

                //continue;
            } elseif ($model_array[$key]['type'] == 'tlocation') {

                /* if ( $model_array[$key]['type'] == 'tlocation' ) {
                  $model_array[$key]['value']['country_id'] = $this->getRequestValue('country_id');
                  $model_array[$key]['value']['region_id'] = $this->getRequestValue('region_id');
                  $model_array[$key]['value']['city_id'] = $this->getRequestValue('city_id');
                  $model_array[$key]['value']['district_id'] = $this->getRequestValue('district_id');
                  $model_array[$key]['value']['street_id'] = $this->getRequestValue('street_id');
                  continue;
                  } */
            } elseif ($model_array[$key]['type'] == 'checkbox') {
                if ($data[$value] == 1) {
                    $this->setRequestValue($key, 1);
                } else {
                    unset($_POST[$key]);
                    unset($_GET[$key]);
                }
            } else {

                $this->setRequestValue($key, $data[$value]);
            }
        }
    }

    function get_multi_ids_from_string ($string, $model_item) {
        $items = explode(',', $string);
        $result = array();
        if ( is_array($items) and count($items) > 0 ) {
            foreach ( $items as $string_value ) {
                $string_value = trim($string_value);
                $result[] = $this->get_value_id_by_name(
                    $model_item['primary_key_table'],
                    $model_item['value_name'],
                    $model_item['primary_key_name'],
                    $string_value
                );
            }
        }
        return $result;
    }

}
