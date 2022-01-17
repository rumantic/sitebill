<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class Dadata_Cleaner extends Data_Manager {
    function __construct() {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $this->model_object = new Data_Model();
    }

    function clean_one ( $address ) {
        echo "Start clean address: $address<br>";
        $apiKey = $this->getConfigValue('apps.dadata.apiKey');
        $secretKey = $this->getConfigValue('apps.dadata.secretKey');
        $dadata = new Dadata($apiKey, $secretKey);
        $result = $dadata->clean('address', $address);

        echo 'result = <pre>';
        print_r($result);
        echo '</pre>';
    }

    function clean() {
        if ( !$this->getConfigValue('apps.dadata.enable') ) {
            echo 'apps.dadata disabled';
            exit;
        }

        if ( $this->getRequestValue('sec') != $this->getConfigValue('apps.dadata.cron_key') ) {
            echo 'bad key';
            exit;
        }
        $DBC = DBC::getInstance();
        $apiKey = $this->getConfigValue('apps.dadata.apiKey');
        $secretKey = $this->getConfigValue('apps.dadata.secretKey');
        $dadata = new Dadata($apiKey, $secretKey);
        //$LIMIT = $this->getConfigValue('apps.excel.image_parsing_step');
        $LIMIT = $this->getConfigValue('apps.dadata.limit');
        $address_column = $this->getConfigValue('apps.dadata.address_column');

        if ( $this->getConfigValue('apps.dadata.check_street_id') ) {
            $exclude_street_sql_condition = " and street_id = 0 ";
        }


        $parsed_flag = $this->getConfigValue('apps.dadata.parsed_flag');
        //Загружаем записи для очистки
        $query = "SELECT id, ".$address_column."
				FROM " . DB_PREFIX . "_data
				WHERE address_enchanced=0 and ".$address_column." <> '' and `$parsed_flag` = 1 ".$exclude_street_sql_condition."
				ORDER BY date_added DESC
				LIMIT " . $LIMIT;
        echo $query.'<br>';
        $stmt = $DBC->query($query, $row, $success);
        if ( !$success ) {
            echo $DBC->getLastError().'<br>';
            exit;
        }
        $default_city = $this->getConfigValue('apps.dadata.default_city');
        $city_for_parsing = $default_city;

        //var_dump($stmt);
        if ($success) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[$ar['id']] = $ar;
                if ( $default_city == '' ) {
                    $data = $this->load_by_id($ar['id']);
                    $city_for_parsing = $data['city_id']['value_string'];
                }

                // Стандартизация одного значения конкретного типа
                echo "<hr>Сырой адрес: ".$city_for_parsing . ' ' . $ar[$address_column].'<br>';
                $result = $dadata->clean('address', $city_for_parsing . ' ' . $ar[$address_column]);

                if ( preg_match('/invalid/i', $result['detail']) or preg_match('/zero balance/i', $result['detail']) ) {
                    echo 'dadata api error: '.$result['detail'];
                    exit;
                }

                //echo 'clean = ' . $default_city . ' ' . $ar['parser_address'] . '<br>';
                //echo '<pre>';
                //print_r($result);
                //echo '</pre>';

                $this->clean_data($ar['id'], $result);
                $this->mark_as_parsed($ar['id']);
            }
        } else {
            //echo 'Записей для парсинга ' . count($ret) . '<br/>';
            echo $DBC->getLastError();
            exit;
        }
    }

    function mark_as_parsed ( $id ) {
        $DBC = DBC::getInstance();
        //Загружаем записи для очистки
        $query = "update " . DB_PREFIX . "_data
				SET address_enchanced=1 WHERE id=?";
        //echo $query;
        $stmt = $DBC->query($query, array($id), $success);
        if ( !$stmt ) {
            echo $DBC->getLastError().'<br>';
            exit;
        }
    }

    function clean_data($id, $clean_result) {
        $form_data = $this->load_by_id($id);


        echo 'Очистка записи id = ' . $id . '<br>';
        $form_data = $this->select_by_query_setter('street_id', 'street', $form_data, $clean_result);
        $form_data = $this->select_by_query_setter('district_id', 'city_district', $form_data, $clean_result);
        $form_data = $this->safe_string_setter('number', 'house', $form_data, $clean_result);
        if ( isset($form_data['metro_id']) ) {
            $form_data = $this->select_by_query_setter('metro_id', 'metro', $form_data, $clean_result);
        }

        $this->setRequestValue('id', $id);
        $this->edit_data($form_data, 0, $id);
        if ($this->getError()) {
            echo $this->GetErrorMessage() . '<br>';
            return false;
        }
        return true;
    }

    function select_by_query_setter ( $key_name, $dadata_key, $form_data, $clean_result ) {
        echo "<br><br>key_name = $key_name<br>";
        echo 'Старое значение: ' . $form_data[$key_name]['value_string'] . '<br>';

        if ( $key_name == 'metro_id' ) {
            $new_value = $clean_result[0][$dadata_key][0]['name'];
        } elseif ( $key_name == 'street_id' ) {
            $new_value = $clean_result[0][$dadata_key].' '.$clean_result[0]['street_type_full'];
        } else {
            $new_value = $clean_result[0][$dadata_key];
        }
        echo 'Новое значение: ' . $new_value . '<br>';


        $value_id = $this->get_id_using_autocomplete($key_name, $new_value);
        echo 'value_id = '.$value_id.'<br>';
        if ( $value_id > 0 ) {
            $form_data[$key_name]['value'] = $value_id;
        } else {
            echo '<b>Используется старое значение! Не удалось установить новое значение</b><br>';
        }
        return $form_data;
    }

    function safe_string_setter ( $key_name, $dadata_key, $form_data, $clean_result ) {
        echo "<br><br>key_name = $key_name<br>";
        echo 'Старое значение: ' . $form_data[$key_name]['value'] . '<br>';
        echo 'Новое значение: ' . $clean_result[0][$dadata_key] . '<br>';

        $form_data[$key_name]['value'] = $clean_result[0][$dadata_key];
        return $form_data;
    }

    function get_id_using_autocomplete( $key, $text ) {
        $DBC = DBC::getInstance();

        $item_array =  $this->data_model['data'][$key];
        $geoautocomplete_text_value[$key] = $text;

        if ($geoautocomplete_text_value[$key] != '') {
            $name = $this->data_model['data'][$key]['value_name'];
            $langs = Multilanguage::availableLanguages();
            if (1 === intval($this->getConfigValue('apps.language.use_langs')) && 0 === intval($parameters['no_ml'])) {
                $name .= $this->getLangPostfix($this->getCurrentLang());
            }
            $real_id = $this->model_object->get_value_id_by_name($item_array['primary_key_table'], $name, $item_array['primary_key_name'], $geoautocomplete_text_value[$key], $filters);
            if ($real_id != 0) {
                $id_value = $real_id;
            } elseif ($_no_insert) {
                $id_value = 0;
            } else {
                $query = 'INSERT INTO ' . DB_PREFIX . '_' . $item_array['primary_key_table'] . ' (`' . $item_array['value_name'] . '`) VALUES (?)';
                $stmt = $DBC->query($query, array($geoautocomplete_text_value[$key]));
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
        return $id_value;
    }

}
