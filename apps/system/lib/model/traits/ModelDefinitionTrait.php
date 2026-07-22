<?php
/**
 * ModelDefinitionTrait — extracted from Data_Model class (model.php)
 * Auto-generated, do not edit manually.
 */
trait ModelDefinitionTrait
{
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
        $form_data['data']['address']['parameters']['dadata'] = 1;
        $form_data['data']['address']['hint'] = 'Укажите адрес объекта, можно вводить город, улицу, номер дома через пробел. Затем выберите из предложенных вариантов правильный адрес.';

        $form_data['data']['geo']['name'] = 'geo';
        $form_data['data']['geo']['title'] = 'Координаты';
        $form_data['data']['geo']['value'] = '';
        $form_data['data']['geo']['type'] = 'geodata';
        $form_data['data']['geo']['required'] = 'off';
        $form_data['data']['geo']['unique'] = 'off';
        $form_data['data']['geo']['tab'] = '';

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

        $form_data['data']['view_count']['name'] = 'view_count';
        $form_data['data']['view_count']['title'] = 'Количество просмотров';
        $form_data['data']['view_count']['value'] = '';
        $form_data['data']['view_count']['length'] = 40;
        $form_data['data']['view_count']['type'] = 'hidden';
        $form_data['data']['view_count']['required'] = 'off';
        $form_data['data']['view_count']['unique'] = 'off';

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
        }
        //echo '<pre>';
        //print_r($form_data);
        //echo '</pre>';


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
        }

        return $form_data;
    }

    function define_kgs_titles($form_data)
    {
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_TEXT_ARRAY');
        return $form_data;
    }

}
