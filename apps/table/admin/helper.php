<?php

use system\lib\system\cache\RedisCache;

/**
 * Admin table helper
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Admin_Table_Helper extends SiteBill {

    private static $model_storage;

    function create_int($item) {
        $rs = "`{$item['name']}` int(10) unsigned NOT NULL DEFAULT '0'";
        return $rs;
    }

    function reset_model_storage () {
        self::$model_storage = array();
    }

    function create_datetime($item) {
        $rs = "`{$item['name']}` DATETIME NOT NULL";
        return $rs;
    }

    function create_tlocation($item) {
        $ret = array();
        $ret[] = "`country_id` int(10) unsigned NOT NULL DEFAULT '0'";
        $ret[] = "`region_id` int(10) unsigned NOT NULL DEFAULT '0'";
        $ret[] = "`city_id` int(10) unsigned NOT NULL DEFAULT '0'";
        $ret[] = "`district_id` int(10) unsigned NOT NULL DEFAULT '0'";
        $ret[] = "`street_id` int(10) unsigned NOT NULL DEFAULT '0'";
        return $ret;
    }

    function create_geodata_lat($item) {
        $rs = "`" . $item['name'] . "_lat` decimal(9,6) NULL";
        return $rs;
    }

    function create_geodata_lng($item) {
        $rs = "`" . $item['name'] . "_lng` decimal(9,6) NULL";
        return $rs;
    }

    function create_varchar($item) {
        $rs = "`{$item['name']}` varchar(255) NULL DEFAULT '{$item['value']}'";
        return $rs;
    }

    function create_uploads($item) {
        $rs = "`{$item['name']}` LONGTEXT NULL DEFAULT ''";
        return $rs;
    }

    function create_docuploads($item) {
        $rs = "`{$item['name']}` LONGTEXT NULL DEFAULT ''";
        return $rs;
    }

    function create_image($item) {


        $q = 'CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . '_' . $item['table_name'] . '_image` (
		`' . $item['table_name'] . '_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`' . $item['primary_key'] . '` int(11) NOT NULL DEFAULT 0,
		`image_id` int(11) NOT NULL DEFAULT 0,
		`sort_order` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`' . $item['table_name'] . '_image_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=' . DB_ENCODING . ' AUTO_INCREMENT=1 ;';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($q);
    }

    function create_select_by_query_multi($item) {
        $q = 'CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . '_multiple_field` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `table_name` varchar(50) COLLATE ' . DB_ENCODING . '_unicode_ci NOT NULL,
		  `field_name` varchar(50) COLLATE ' . DB_ENCODING . '_unicode_ci NOT NULL,
		  `primary_id` int(11) NOT NULL,
		  `field_value` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `main_index` (`table_name`,`field_name`,`primary_id`,`field_value`)
		) ENGINE=MyISAM  DEFAULT CHARSET=' . DB_ENCODING . ' COLLATE=' . DB_ENCODING . '_unicode_ci;';

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($q);
    }

    function create_primary_key($item) {
        $rs = "`{$item['name']}` int(11)  NOT NULL AUTO_INCREMENT, PRIMARY KEY (`{$item['name']}`) ";
        return $rs;
    }

    function create_text($item) {
        $rs = "`{$item['name']}` text";
        return $rs;
    }

    function add_ajax($form_data) {
        //return $form_data;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/table/local_helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/table/local_helper.php';
            $LH = new Local_Table_Helper();
            return $LH->add_ajax($form_data);
        }
        //return $form_data;
        $append_system_ajax = true;
        if (1 == $this->getConfigValue('apps.realty.off_system_ajax')) {
            $append_system_ajax = false;
        }
        if ( is_array($form_data) ) {
            $table_key = current(array_keys($form_data));
        }

        //$form_data['data']['date_added']['value'] = date('Y-m-d H:i:s',time());

        if ($table_key == 'data' && $this->getConfigValue('currency_enable') && !isset($form_data[$table_key]['currency_id'])) {
            $form_data[$table_key]['currency_id']['name'] = 'currency_id';
            $form_data[$table_key]['currency_id']['primary_key_name'] = 'currency_id';
            $form_data[$table_key]['currency_id']['primary_key_table'] = 'currency';
            $form_data[$table_key]['currency_id']['title'] = 'Валюта';
            $form_data[$table_key]['currency_id']['value_string'] = '';
            $form_data[$table_key]['currency_id']['value'] = 0;
            $form_data[$table_key]['currency_id']['length'] = 40;
            $form_data[$table_key]['currency_id']['type'] = 'select_by_query';
            $form_data[$table_key]['currency_id']['query'] = 'select * from ' . DB_PREFIX . '_currency WHERE is_active=1 ORDER BY sort_order ASC, code ASC, currency_id ASC';
            $form_data[$table_key]['currency_id']['value_name'] = 'name';
            $form_data[$table_key]['currency_id']['title_default'] = '';
            $form_data[$table_key]['currency_id']['value_default'] = 0;
            $form_data[$table_key]['currency_id']['required'] = 'off';
            $form_data[$table_key]['currency_id']['unique'] = 'off';
        }

        if (!$this->getConfigValue('ajax_form_in_admin')) {
            return $form_data;
        }

        if (!$append_system_ajax) {
            return $form_data;
        }






        //country

        if ($this->getConfigValue('country_in_form') && isset($form_data[$table_key]['country_id'])) {
            if ($form_data[$table_key]['country_id']['title_default'] == '') {
                $form_data[$table_key]['country_id']['title_default'] = Multilanguage::_('L_CHOOSE_COUNTRY');
            }
            if (!isset($form_data[$table_key]['country_id']['onchange'])) {
                $form_data[$table_key]['country_id']['onchange'] = '';
            }

            $form_data[$table_key]['country_id']['onchange'] = '';
            if ($this->getConfigValue('apps.realty.ajax_region_refresh')) {
                $form_data[$table_key]['country_id']['onchange'] .= ' update_child_list(\'region_id\',this); ';
                $form_data[$table_key]['country_id']['ajax_options']['update_child_list'][] = 'region_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_city_refresh')) {
                $form_data[$table_key]['country_id']['onchange'] .= ' set_empty(\'city_id\',this); ';
                $form_data[$table_key]['country_id']['ajax_options']['set_empty'][] = 'city_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                $form_data[$table_key]['country_id']['onchange'] .= ' set_empty(\'district_id\',this); ';
                $form_data[$table_key]['country_id']['ajax_options']['set_empty'][] = 'district_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                $form_data[$table_key]['country_id']['onchange'] .= ' set_empty(\'metro_id\',this); ';
                $form_data[$table_key]['country_id']['ajax_options']['set_empty'][] = 'metro_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                $form_data[$table_key]['country_id']['onchange'] .= ' set_empty(\'street_id\',this); ';
                $form_data[$table_key]['country_id']['ajax_options']['set_empty'][] = 'street_id';
            }
        }


        //region

        if ($this->getConfigValue('region_in_form') && isset($form_data[$table_key]['region_id'])) {
            if (!isset($form_data[$table_key]['region_id']['onchange'])) {
                $form_data[$table_key]['region_id']['onchange'] = '';
            }

            if ($form_data[$table_key]['region_id']['title_default'] == '') {
                $form_data[$table_key]['region_id']['title_default'] = Multilanguage::_('L_CHOOSE_REGION');
            }

            if (!isset($form_data[$table_key]['region_id']['onchange'])) {
                $form_data[$table_key]['region_id']['onchange'] = '';
            }

            if ($this->getConfigValue('apps.realty.ajax_city_refresh')) {
                $form_data[$table_key]['region_id']['onchange'] .= ' update_child_list(\'city_id\',this); ';
                $form_data[$table_key]['region_id']['ajax_options']['update_child_list'][] = 'city_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                $form_data[$table_key]['region_id']['onchange'] .= ' set_empty(\'district_id\',this); ';
                $form_data[$table_key]['region_id']['ajax_options']['set_empty'][] = 'district_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                $form_data[$table_key]['region_id']['onchange'] .= ' set_empty(\'metro_id\',this); ';
                $form_data[$table_key]['region_id']['ajax_options']['set_empty'][] = 'metro_id';
            }
            if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                $form_data[$table_key]['region_id']['onchange'] .= ' set_empty(\'street_id\',this); ';
                $form_data[$table_key]['region_id']['ajax_options']['set_empty'][] = 'street_id';
            }
            if (intval($this->getRequestValue('country_id')) != 0/* and $this->getRequestValue('country_id') != '' */) {
                $form_data[$table_key]['region_id']['query'] = 'select * from ' . DB_PREFIX . '_region where country_id=' . intval($this->getRequestValue('country_id')) . ' order by name';
            }
        }



        //city
        if ($this->getConfigValue('city_in_form') && isset($form_data[$table_key]['city_id'])) {
            if ($form_data[$table_key]['city_id']['title_default'] == '') {
                $form_data[$table_key]['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
            }

            if (!isset($form_data[$table_key]['city_id']['onchange'])) {
                $form_data[$table_key]['city_id']['onchange'] = '';
            }

            if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                if (1 == $this->getConfigValue('link_metro_to_district')) {
                    $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list_without_district(\'metro_id\',this); ';
                } else {
                    $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list(\'metro_id\',this); ';
                }
            }
            if ($this->getConfigValue('link_street_to_city')) {
                if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                    $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list(\'district_id\',this); ';
                    $form_data[$table_key]['city_id']['ajax_options']['update_child_list'][] = 'district_id';
                }
                if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                    $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list(\'street_id\',this); ';
                    $form_data[$table_key]['city_id']['ajax_options']['update_child_list'][] = 'street_id';
                }
            } else {
                if ($this->getConfigValue('apps.realty.ajax_district_refresh')) {
                    $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list(\'district_id\',this); ';
                    $form_data[$table_key]['city_id']['ajax_options']['update_child_list'][] = 'district_id';
                }
                if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                    $form_data[$table_key]['city_id']['onchange'] .= ' set_empty(\'street_id\',this); ';
                    $form_data[$table_key]['city_id']['ajax_options']['set_empty'][] = 'street_id';
                }
            }
            if (intval($this->getRequestValue('region_id')) != 0/* and $this->getRequestValue('region_id') != '' */) {
                $form_data[$table_key]['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city where region_id=' . intval($this->getRequestValue('region_id')) . ' order by name';
            }
        }

        if ($this->getConfigValue('metro_in_form') && isset($form_data[$table_key]['metro_id'])) {
            if ($form_data[$table_key]['metro_id']['title_default'] == '') {
                $form_data[$table_key]['metro_id']['title_default'] = Multilanguage::_('L_CHOOSE_METRO');
            }

            if (intval($this->getRequestValue('city_id')) != 0/* && $this->getRequestValue('city_id') != '' */ && intval($this->getRequestValue('district_id')) != 0/* && $this->getRequestValue('district_id') != '' */ && 1 == $this->getConfigValue('link_metro_to_district')) {
                $form_data[$table_key]['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro where city_id=' . intval($this->getRequestValue('city_id')) . ' AND district_id=' . intval($this->getRequestValue('district_id')) . ' order by name';
            } elseif (1 != $this->getConfigValue('apps.realty.ajax_metro_refresh')) {

            } elseif (intval($this->getRequestValue('city_id')) != 0/* and $this->getRequestValue('city_id') != '' */) {
                $form_data[$table_key]['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro where city_id=' . intval($this->getRequestValue('city_id')) . ' order by name';
            }
        }


        //district
        if ($this->getConfigValue('district_in_form') && isset($form_data[$table_key]['district_id'])) {
            if ($form_data[$table_key]['district_id']['title_default'] == '') {
                $form_data[$table_key]['district_id']['title_default'] = Multilanguage::_('L_CHOOSE_DISTRICT');
            }
            if (!isset($form_data[$table_key]['district_id']['onchange'])) {
                $form_data[$table_key]['district_id']['onchange'] = '';
            }


            if (1 == $this->getConfigValue('link_metro_to_district')) {
                if ($this->getConfigValue('apps.realty.ajax_metro_refresh')) {
                    $form_data[$table_key]['district_id']['onchange'] .= ' update_child_list(\'metro_id\',this); ';
                    $form_data[$table_key]['district_id']['ajax_options']['update_child_list'][] = 'metro_id';
                }

                /* if ( $this->getConfigValue('apps.realty.ajax_district_refresh') ) {
                  $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list(\'district_id\',this); ';
                  $form_data[$table_key]['city_id']['ajax_options']['update_child_list'][] = 'district_id';
                  }
                  if ( $this->getConfigValue('apps.realty.ajax_street_refresh') ) {
                  $form_data[$table_key]['city_id']['onchange'] .= ' update_child_list(\'street_id\',this); ';
                  $form_data[$table_key]['city_id']['ajax_options']['update_child_list'][] = 'street_id';
                  } */
            }



            if ($this->getConfigValue('apps.realty.ajax_street_refresh')) {
                if ($this->getConfigValue('link_street_to_city')) {

                } else {
                    $form_data[$table_key]['district_id']['onchange'] .= ' update_child_list(\'street_id\',this); ';
                    $form_data[$table_key]['district_id']['ajax_options']['update_child_list'][] = 'street_id';
                }
            }
            if (intval($this->getRequestValue('city_id')) != 0/* and $this->getRequestValue('city_id') != '' */) {
                $form_data[$table_key]['district_id']['query'] = 'select * from ' . DB_PREFIX . '_district where city_id=' . intval($this->getRequestValue('city_id')) . ' order by name';
            }
        }

        //street
        if ($this->getConfigValue('street_in_form') && isset($form_data[$table_key]['street_id'])) {
            if ($form_data[$table_key]['street_id']['title_default'] == '') {
                $form_data[$table_key]['street_id']['title_default'] = Multilanguage::_('L_CHOOSE_STREET');
            }

            if (intval($this->getRequestValue('district_id')) != 0/* and $this->getRequestValue('district_id') != '' */) {
                $form_data[$table_key]['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street where district_id=' . intval($this->getRequestValue('district_id')) . ' order by name';
            }
            if ($this->getConfigValue('link_street_to_city')) {
                if (intval($this->getRequestValue('city_id')) != 0/* and $this->getRequestValue('city_id') != '' */) {
                    $form_data[$table_key]['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street where city_id=' . intval($this->getRequestValue('city_id')) . ' order by name';
                }
            }
        }

        return $form_data;
    }

    function load_model($table_name, $ignore_user_group = false, $ignore_activity = false) {
        //echo $table_name.'<br>';
        //var_dump($ignore_user_group);
        $group_id = 0;
        $anonimouse_group = intval($this->getConfigValue('user_anonimouse_group_id'));
        //$anonimouse_group=5;
        /* if($anonimouse_group==0){
          $anonimouse_group=-1;
          } */

        if (isset($_SESSION['user_id_value']) && intval($_SESSION['user_id_value']) > 0) {
            $user_id = intval($_SESSION['user_id_value']);
        } elseif (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0) {
            $user_id = intval($_SESSION['user_id']);
        }

        if (!$ignore_user_group && isset($user_id)) {
            $group_id = $_SESSION['current_user_group_id'];
            /* if(isset(self::$Heaps['user'][$user_id])){
              $group_id=self::$Heaps['user'][$user_id]['group_id'];
              }else{

              //$group_id=$_SESSION['current_user_group_id'];
              $q="SELECT group_id FROM ".DB_PREFIX."_user WHERE user_id=?";
              $DBC=DBC::getInstance();
              $stmt=$DBC->query($q, array($user_id));
              if($stmt){
              $ar=$DBC->fetch($stmt);
              $group_id=(int)$ar['group_id'];
              self::$Heaps['user'][$user_id]['group_id'] = $group_id;
              }
              } */
        } elseif (!$ignore_user_group && (!isset($user_id) || $user_id == 0)) {
            $group_id = $anonimouse_group;
        }/* elseif(!$ignore_user_group && !isset($user_id)){
          $group_id=5;
          } *//* elseif(!$ignore_user_group && (!isset($user_id) || $user_id==0)){
          $group_id=$anonimouse_group;
          } */

        /* if(!isset($user_id)){

          } */

        $input_table_name = $table_name;


        $model_name = $table_name . '_' . ($ignore_user_group ? '1' : '0') . '_' . ($ignore_activity ? '1' : '0');

        if ($group_id != 0) {
            $model_name .= '_' . $group_id;
        }

        $current_lang = $this->getCurrentLang();

        /**
         * TODO
         * Возможное дополнение метки модели в кеше признаком языка
         */
        //$model_name .= '_' . $current_lang;

        $input_model_name = $model_name;

        $redis_cache = unserialize(RedisCache::get('load_model_'.$model_name));
        if ( is_array($redis_cache) and count($redis_cache) > 0 ) {
            self::$model_storage[$model_name] = $redis_cache;
            return self::$model_storage[$model_name];
        }


        /*if($table_name == 'complex'){
            echo '<!--'.$current_lang.'-->';
        }*/

        if (!isset(self::$model_storage[$model_name]) || empty(self::$model_storage[$model_name])) {
            $model_data = array();
            $DBC = DBC::getInstance();

            $query = "SELECT c.*, t.table_id, t.name as table_name FROM " . DB_PREFIX . "_columns c, " . DB_PREFIX . "_table t  WHERE t.table_id=c.table_id " . ($ignore_activity ? '' : ' AND active=1') . " ORDER BY c.table_id, c.sort_order";

            $stmt = $DBC->query($query);

            if (!$stmt) {
                $model_data[$table_name] = array();
                return $model_data;
            }
            while ($ar = $DBC->fetch($stmt)) {
                $table_name = $ar['table_name'];
                $model_name = $table_name . '_' . ($ignore_user_group ? '1' : '0') . '_' . ($ignore_activity ? '1' : '0');
                if ($group_id != 0) {
                    $model_name .= '_' . $group_id;
                }

                /**
                * TODO
                * Возможное дополнение метки модели в кеше признаком языка
                */
                //$model_name .= '_' . $current_lang;
                if (!$ignore_user_group) {
                    if ($ar['type'] == 'captcha') {
                        if ($ar['group_id'] != '0' && $ar['group_id'] != '') {
                            $t = array();
                            $t = explode(',', $ar['group_id']);
                            //$t[]=0;
                            if ($group_id != 0 && !in_array($group_id, $t)) {
                                continue;
                            }
                        }
                    } else {
                        if ($ar['group_id'] != '0' && $ar['group_id'] != '') {
                            $t = array();
                            $t = explode(',', $ar['group_id']);
                            if (!in_array($group_id, $t)) {
                                continue;
                            }
                        }
                    }
                }

                $lang_prefix = $this->getLangPostfix($current_lang);

                self::$model_storage[$model_name][$table_name][$ar['name']]['name'] = $ar['name'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['title'] = $ar['title'];
                if ($ar['title' . $lang_prefix] != '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['title'] = $ar['title' . $lang_prefix];
                }

                /* if(0===strpos(self::$model_storage[$model_name][$table_name][$ar['name']]['title'], '#')){
                  $nm=substr(self::$model_storage[$model_name][$table_name][$ar['name']]['title'], 1);
                  self::$model_storage[$model_name][$table_name][$ar['name']]['title']=Multilanguage::_($nm, 'system');
                  }else{
                  if($ar['title'.$lang_prefix]!=''){
                  self::$model_storage[$model_name][$table_name][$ar['name']]['title'] = $ar['title'.$lang_prefix];
                  }
                  } */

                if ( $ar['type'] == 'geodata' ) {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['value'] = array('lat' => '', 'lng' => '');
                } else {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['value'] = $ar['value'];
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['type'] = $ar['type'];

                self::$model_storage[$model_name][$table_name][$ar['name']]['primary_key_name'] = $ar['primary_key_name'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['primary_key_table'] = $ar['primary_key_table'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['value_string'] = $ar['value_string'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['query'] = $ar['query'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['value_name'] = $ar['value_name'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['title_default'] = $ar['title_default'];
                if ($ar['title_default' . $lang_prefix] != '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['title_default'] = $ar['title_default' . $lang_prefix];
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['value_default'] = $ar['value_default'];

                self::$model_storage[$model_name][$table_name][$ar['name']]['value_table'] = $ar['value_table'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['value_primary_key'] = $ar['value_primary_key'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['value_field'] = $ar['value_field'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['assign_to'] = $ar['assign_to'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['dbtype'] = $ar['dbtype'];
                //self::$model_storage[$model_name][$table_name][$ar['name']]['select_data'] = ($ar['select_data']!='' ? unserialize($ar['select_data']) : array());
                if ($ar['select_data'] != '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['select_data'] = $this->unserializeSelectData($ar['select_data']);
                    if ($ar['select_data' . $lang_prefix] != '') {
                        self::$model_storage[$model_name][$table_name][$ar['name']]['select_data'] = $this->unserializeSelectData($ar['select_data' . $lang_prefix]);
                    }
                    $select_data_indexed = array();
                    $select_data_reverse = array();
                    foreach ( self::$model_storage[$model_name][$table_name][$ar['name']]['select_data'] as $key_s => $value_s ) {
                        array_push($select_data_indexed, array('id'=>$key_s, 'value' => $value_s));
                        $select_data_reverse[$value_s] = $key_s;
                    }
                    self::$model_storage[$model_name][$table_name][$ar['name']]['select_data_indexed'] = $select_data_indexed;
                    self::$model_storage[$model_name][$table_name][$ar['name']]['select_data_reverse'] = $select_data_reverse;
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['table_name'] = $ar['table_name'];
                if ((self::$model_storage[$model_name][$table_name][$ar['name']]['type'] == 'uploads' || self::$model_storage[$model_name][$table_name][$ar['name']]['type'] == 'docuploads') && self::$model_storage[$model_name][$table_name][$ar['name']]['table_name'] == '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['table_name'] = $table_name;
                } elseif (self::$model_storage[$model_name][$table_name][$ar['name']]['type'] == 'select_by_query' && self::$model_storage[$model_name][$table_name][$ar['name']]['table_name'] == '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['table_name'] = $table_name;
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['primary_key'] = $ar['primary_key'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['primary_key_value'] = $ar['primary_key_value'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['action'] = $ar['action'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['tab'] = $ar['tab'];
                if ($ar['tab' . $lang_prefix] != '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['tab'] = $ar['tab' . $lang_prefix];
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['hint'] = $ar['hint'];
                if ($ar['hint' . $lang_prefix] != '') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['hint'] = $ar['hint' . $lang_prefix];
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['active_in_topic'] = $ar['active_in_topic'];
                if (1 === intval($this->getConfigValue('apps.table.additional_filtering'))) {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['active_in_optype'] = $ar['active_in_optype'];
                }

                self::$model_storage[$model_name][$table_name][$ar['name']]['group_id'] = $ar['group_id'];
                if ( $ar['group_id'] ) {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['group_id_array'] = explode(',', $ar['group_id']);
                } else {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['group_id_array'] = [];
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['entity'] = $ar['entity'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['combo'] = $ar['combo'];
                if ($ar['parameters'] != '' && $ar['parameters'] != '0') {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['parameters'] = unserialize($ar['parameters']);
                }

                if ($ar['required']) {
                    $required = 'on';
                } else {
                    $required = 'off';
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['required'] = $required;
                self::$model_storage[$model_name][$table_name][$ar['name']]['required_boolean'] = $ar['required'];

                if ($ar['unique']) {
                    $unique = 'on';
                } else {
                    $unique = 'off';
                }
                self::$model_storage[$model_name][$table_name][$ar['name']]['unique'] = $unique;
                self::$model_storage[$model_name][$table_name][$ar['name']]['columns_id'] = $ar['columns_id'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['table_id'] = $ar['table_id'];
                self::$model_storage[$model_name][$table_name][$ar['name']]['active'] = $ar['active'];

                if ( $api_parameters = $this->parse_api_parameters(self::$model_storage[$model_name][$table_name][$ar['name']]) ) {
                    self::$model_storage[$model_name][$table_name][$ar['name']]['api'] = $api_parameters;
                }


                //$model_data[$table_name][$ar['name']]['is_ml'] = $ar['is_ml'];
            }
            /*
              if(!empty($model_data)){
              self::$model_storage[$model_name]=$model_data;
              }
             */
        } else {

            $model_data = self::$model_storage[$model_name];
        }

        RedisCache::set('load_model_'.$input_model_name, serialize(self::$model_storage[$input_model_name]));

        return self::$model_storage[$input_model_name];
    }

    function parse_api_parameters ( $model_item ) {
        if (isset($model_item['parameters']['api_version']) && $model_item['parameters']['api_version'] != '') {
            return new api\types\method(
                $model_item['parameters']['api_version'],
                $model_item['parameters']['api_name'],
                $model_item['parameters']['api_method'],
                $model_item['parameters']['api_params']
            );
        }
        return false;
    }


    function unserializeSelectData($str) {
        $ret = array();
        $matches = array();
        preg_match_all('/\{[^\}]+\}/', $str, $matches);
        if (count($matches) > 0) {
            foreach ($matches[0] as $v) {
                $v = str_replace(array('{', '}'), '', $v);
                $d = explode('~~', $v);
                $ret[trim($d[0])] = trim($d[1]);
            }
        }
        return $ret;
    }
    function serializeSelectData($select_data_array) {
        if ( !is_array($select_data_array) ) {
            return '';
        }
        $rs = '';
        foreach ( $select_data_array as $key => $value) {
            $rs .= '{'.$key.'~~'.$value.'}';
        }
        return $rs;
    }

    function update_table($table_name) {
        $table_model = $this->load_model($table_name, false, true);
        return $this->alter_table($table_name, $table_model[$table_name]);
    }

    function alter_table ( $table_name, $model ) {
        $ra = $this->columns_define_generator($model);

        $DBC = DBC::getInstance();
        $rs = '';
        foreach ($ra as $item_id => $item) {
            $query = 'ALTER TABLE ' . DB_PREFIX . '_' . $table_name . ' ADD COLUMN ' . $item;
            $DBC->query($query);
            $last_error = $DBC->getLastError();
            if (!preg_match('/already/', $last_error) and ! preg_match('/PRIMARY/', $last_error)) {
                $rs .= $query.'<br>';
                $rs .= $last_error . '<br>';
            }
        }
        $rs .= 'Таблица ' . $table_name . ' обновлена успешно';
        return $rs;
    }

    function clear_table($table_name) {
        if ($table_name != '') {
            $table_model = $this->load_model($table_name, false, true);
            $DBC = DBC::getInstance();
            $stmt = $DBC->query('SHOW COLUMNS FROM `' . DB_PREFIX . '_' . $table_name . '`');
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $columns[$ar['Field']] = $ar['Field'];
                }
            }

            foreach ($table_model[$table_name] as $model_column) {
                if ($model_column['type'] == 'tlocation') {
                    $real_columns[] = 'country_id';
                    $real_columns[] = 'region_id';
                    $real_columns[] = 'city_id';
                    $real_columns[] = 'district_id';
                    $real_columns[] = 'street_id';
                } elseif ($model_column['type'] == 'geodata') {
                    $real_columns[] = 'geo_lat';
                    $real_columns[] = 'geo_lng';
                } else {
                    $real_columns[] = $model_column['name'];
                }
            }

            $diff_columns = array_diff($columns, $real_columns);
            $query = 'ALTER TABLE';
            $drop = array();
            if (count($diff_columns) > 0) {
                foreach ($diff_columns as $diff_column) {
                    $drop[] = 'DROP `' . $diff_column . '`';
                }
                $query .= ' ' . implode(', ', $drop);
                return $query;
            } else {
                return 'Таблица уже оптимизирована';
            }
        } else {
            return 'Таблица не найдена';
        }
    }

    function columns_define_generator($table_model) {
        $ra = array();
        if (empty($table_model)) {
            return $ra;
        }
        foreach ($table_model as $item_id => $item_array) {
            switch ($item_array['type']) {
                case 'primary_key':
                    $ra[] = $this->create_primary_key($item_array);
                    break;
                case 'price':
                    $ra[] = $this->create_int($item_array);
                    break;
                case 'uploads':
                    $ra[] = $this->create_uploads($item_array);
                    break;
                case 'docuploads':
                    $ra[] = $this->create_docuploads($item_array);
                    break;
                case 'select_box':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'email':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'mobilephone':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'select_by_query':
                    $ra[] = $this->create_int($item_array);
                    break;
                case 'client_id':
                    $ra[] = $this->create_int($item_array);
                    break;
                case 'select_by_query_multi':
                    $ra[] = $this->create_select_by_query_multi($item_array);
                    break;

                case 'select_by_query_multiple':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'select_box_structure':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'structure':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'select_box_structure_simple_multiple':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'shop_select_box_structure':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'geodata':
                    $ra[] = $this->create_geodata_lat($item_array);
                    $ra[] = $this->create_geodata_lng($item_array);
                    break;

                case 'service_type_select_box_structure': {
                        $ra[] = $this->create_int($item_array);
                    }
                    break;
                /*
                  case 'uploader':
                  $ra[] = $this->get_uploader_row($item_array);
                  break;

                  case 'pluploader':
                  $ra[] = $this->get_pluploader_row($item_array);
                  break;
                 */
                case 'uploadify_image':
                    $this->create_image($item_array);
                    break;

                case 'uploadify_file':
                    //$ra[] = $this->create_image($item_array);
                    break;
                case 'captcha':
                    //$ra[] = $this->create_image($item_array);
                    break;
                case 'separator':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'checkbox':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'textarea':
                    $ra[] = $this->create_text($item_array);
                    break;
                case 'parameter':
                case 'textarea_editor':
                    $ra[] = $this->create_text($item_array);
                    break;

                case 'grade':
                    $ra[] = $this->create_int($item_array);
                    break;

                case 'date':
                    $ra[] = $this->create_int($item_array);
                    break;
                case 'datetime':
                    $ra[] = $this->create_datetime($item_array);
                    break;
                case 'dtdatetime':
                    $ra[] = $this->create_datetime($item_array);
                    break;
                case 'dtdate':
                    $ra[] = $this->create_datetime($item_array);
                    break;
                case 'dttime':
                    $ra[] = $this->create_datetime($item_array);
                    break;
                case 'auto_add_value':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'injector':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'safe_string':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'compose':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'gadres':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'password':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'photo':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'avatar':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'hidden':
                    $ra[] = $this->create_varchar($item_array);
                    break;

                case 'values_list':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'youtube':
                    $ra[] = $this->create_varchar($item_array);
                    break;
                case 'tlocation':
                    $tlocation_columns = $this->create_tlocation($item_array);
                    if (!empty($tlocation_columns)) {
                        foreach ($tlocation_columns as $tlc) {
                            $ra[] = $tlc;
                        }
                    }
                    break;
            }
        }
        foreach ($ra as $k => $r) {
            if ($r == '') {
                unset($ra[$k]);
            }
        }
        return $ra;
    }

    function create_table($table_name) {
        $table_model = $this->load_model($table_name);
        return $this->create_table_from_model($table_name, $table_model);
    }

    function create_table_from_model ( $table_name, $table_model ) {
        $ra = $this->columns_define_generator($table_model[$table_name]);
        $create_table_query = 'CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . '_' . $table_name . '` (' . implode(' , ', $ra) . ') ENGINE=MyISAM DEFAULT CHARSET=' . DB_ENCODING . ' ;';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($create_table_query);
        if ($this->check_table_exist($table_name)) {
            $rs = 'Таблица ' . $table_name . ' создана успешно';
        } else {
            $rs = 'Ошибка при создании таблицы ' . $table_name;
        }
        return $rs;
    }

    function check_table_exist($table_name) {
        $exists_table = array('table', 'columns', 'table_searchform', 'table_frontgrid');
        if (in_array($table_name, $exists_table)) {
            return true;
        }

        $query = 'SHOW TABLES LIKE ?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array(DB_PREFIX . '_' . $table_name));
        if (!$stmt) {
            return false;
        }
        return true;
        //var_dump($stmt);

        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $table_name . ' LIMIT 1';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            return true;
        } else {
            return false;
        }
    }

}
