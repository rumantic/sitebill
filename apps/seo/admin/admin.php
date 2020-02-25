<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * SEO admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class seo_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        $this->action = 'seo';
        $this->app_title = 'SEO-Оптимизация';

        $this->SiteBill();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.seo.html_prefix_enable')) {
            $config_admin->addParamToConfig('apps.seo.html_prefix_enable', '1', 'Включить .html постфиксы в конце URL объявлений', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.level_enable')) {
            $config_admin->addParamToConfig('apps.seo.level_enable', '0', 'Включить SEO-режим с многоуровневым URL для каталогов. Прежде чем включить этот режим, внимательно ознакомьтесь с <a href="http://www.sitebill.ru/seo-level.html" target="_blank">инструкцией</a>', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.data_alias_enable')) {
            $config_admin->addParamToConfig('apps.seo.data_alias_enable', '0', 'Включить SEO-режим с расширенными ссылками объявлений.', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.allow_custom_realty_aliases')) {
            $config_admin->addParamToConfig('apps.seo.allow_custom_realty_aliases', '0', 'Разрешить установку нестандартных алиасов', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.allow_custom_realty_aliase_fields')) {
            $config_admin->addParamToConfig('apps.seo.allow_custom_realty_aliase_fields', '', 'Набор полей нестандартных алиасов');
        }

        if (!$config_admin->check_config_item('apps.seo.country_info_in_realty_view')) {
            $config_admin->addParamToConfig('apps.seo.country_info_in_realty_view', '0', 'Добавить информацию о стране в карточку объекта', 1);
        }
        if (!$config_admin->check_config_item('apps.seo.region_info_in_realty_view')) {
            $config_admin->addParamToConfig('apps.seo.region_info_in_realty_view', '0', 'Добавить информацию о регионе в карточку объекта', 1);
        }
        if (!$config_admin->check_config_item('apps.seo.city_info_in_realty_view')) {
            $config_admin->addParamToConfig('apps.seo.city_info_in_realty_view', '0', 'Добавить информацию о городе в карточку объекта', 1);
        }


        if (!$config_admin->check_config_item('apps.seo.no_country_url')) {
            $config_admin->addParamToConfig('apps.seo.no_country_url', 0, 'Не перехватывать алиасы стран', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.no_region_url')) {
            $config_admin->addParamToConfig('apps.seo.no_region_url', 0, 'Не перехватывать алиасы регионов', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.no_city_url')) {
            $config_admin->addParamToConfig('apps.seo.no_city_url', 0, 'Не перехватывать алиасы городов', 1);
        }
        
        if (!$config_admin->check_config_item('apps.seo.no_district_url')) {
            $config_admin->addParamToConfig('apps.seo.no_district_url', 1, 'Не перехватывать алиасы районов', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.no_metro_url')) {
            $config_admin->addParamToConfig('apps.seo.no_metro_url', 1, 'Не перехватывать алиасы станций метро', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.no_trailing_slashes')) {
            $config_admin->addParamToConfig('apps.seo.no_trailing_slashes', 0, 'Не использовать концевые слеши', 1);
        }

        if (!$config_admin->check_config_item('apps.seo.realty_alias')) {
            $config_admin->addParamToConfig('apps.seo.realty_alias', 'realty', 'Подстановочная часть стандартного алиаса объявления');
        }
        
        $config_admin->addParamToConfig('apps.seo.city_title_postfix', '', 'Текст после заголовка на странице города');
        

        if ( !$config_admin->check_config_item('apps.seo.user_alias') ) {
            $config_admin->addParamToConfig('apps.seo.user_alias', 'user', 'Подстановочная часть стандартного алиаса пользователя');
        }

        if ( !$config_admin->check_config_item('apps.seo.user_html_end') ) {
            $config_admin->addParamToConfig('apps.seo.user_html_end', 1, 'Включить .html постфиксы в конце URL пользователя', 1);
        }

        if ( !$config_admin->check_config_item('apps.seo.user_slash_divider') ) {
            $config_admin->addParamToConfig('apps.seo.user_slash_divider', 0, 'Использовать разделитель-слеш после подстановочной части URL пользователя', 1);
        }
    }

    function main() {
        if ($this->getRequestValue('do') == 'update') {
            $rs = $this->update_structure();
        } elseif ($this->getRequestValue('do') == 'update_data') {
            $rs = $this->update_data($this->getRequestValue('force'));
            //return $rs;
        } else {
            $rs = '<a href="?action=seo&do=update" class="btn btn-primary">Обновить структуру каталогов</a> ';
            $rs .= '<a href="?action=seo&do=update_data" class="btn btn-primary">Обновить алиасы объявлений (если алиасы не заданы)</a>';
            $rs .= ' <a href="?action=seo&do=update_data&force=true" class="btn btn-danger">Обновить алиасы объявлений (у всех объявлений, даже если алиасы уже указаны)</a>';
        }

        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;

        return $rs_new;
    }

    function update_data($force = false) {
        $ids = array();
        $existing_aliases = array();

        $data = array();
        $query = "SELECT id, translit_alias FROM " . DB_PREFIX . "_data";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array(), $success);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $data[] = $ar['id'];
                if ($ar['translit_alias'] != '') {
                    if ($force == 'true') {
                        $ids[] = $ar['id'];
                    }
                    $existing_aliases[$ar['translit_alias']] = $ar['translit_alias'];
                } else {
                    $ids[] = $ar['id'];
                }
            }
        }
        if (!$success) {
            $rs = $DBC->getLastError();
            return $rs;
        }

        //print_r($existing_aliases);
        //print_r($ids);
        if (!empty($ids)) {

            $DBC = DBC::getInstance();

            $realty_data = array();
            $values_cache = array();


            if (1 == $this->getConfigValue('apps.seo.allow_custom_realty_aliases')) {
                if ('' != $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')) {
                    $alias_fields = explode(',', $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields'));
                } else {
                    $alias_fields = array('city_id', 'street_id', 'number');
                }
            } else {
                $alias_fields = array('city_id', 'street_id', 'number');
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data_shared = $data_model->get_kvartira_model(false, true);
            $form_data_shared = $form_data_shared['data'];

            foreach ($alias_fields as $v) {
                $key = trim($v);
                if (isset($form_data_shared[$key])) {
                    if ($form_data_shared[$key]['type'] == 'select_by_query') {
                        $query = 'SELECT `' . $form_data_shared[$key]['primary_key_name'] . '`, `' . $form_data_shared[$key]['value_name'] . '` FROM ' . DB_PREFIX . '_' . $form_data_shared[$key]['primary_key_table'];
                        $stmt = $DBC->query($query);
                        if ($stmt) {
                            while ($ar = $DBC->fetch($stmt)) {
                                $values_cache[$key][$ar[$form_data_shared[$key]['primary_key_name']]] = $ar[$form_data_shared[$key]['value_name']];
                            }
                        }
                    } elseif ($form_data_shared[$key]['type'] == 'select_box') {
                        $sd = $form_data_shared[$key]['select_data'];
                        unset($sd[0]);
                        $values_cache[$key] = $sd;
                    } elseif ($form_data_shared[$key]['type'] == 'select_box_structure') {
                        $query = 'SELECT `id`, `name` FROM ' . DB_PREFIX . '_topic';
                        $stmt = $DBC->query($query);
                        if ($stmt) {
                            while ($ar = $DBC->fetch($stmt)) {
                                $values_cache[$key][$ar['id']] = $ar['name'];
                            }
                        }
                        //print_r($form_data_shared[$key]);
                    }
                    /* if(($form_data_shared[$key]['type']=='select_box_structure' || $form_data_shared[$key]['type']=='select_by_query' || $form_data_shared[$key]['type']=='select_box') && $form_data_shared[trim($v)]['value_string']!='' ){
                      $values[]=$form_data_shared[trim($v)]['value_string'];
                      }elseif($form_data_shared[trim($v)]['value']!=''){
                      $values[]=$form_data_shared[trim($v)]['value'];
                      } */
                }
            }
            //print_r($values_cache);
            //print_r($alias_fields);
            $aliases = array();

            $query = 'SELECT id, `' . implode('`,`', $alias_fields) . '` FROM ' . DB_PREFIX . '_data WHERE id IN (' . implode(',', $ids) . ')';
            $stmt = $DBC->query($query, array(), $success);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    foreach ($alias_fields as $v) {
                        $key = trim($v);
                        if (isset($values_cache[$key])) {
                            $ar['alias_parts'][] = $values_cache[$key][$ar[$key]];
                        } else {
                            $ar['alias_parts'][] = $ar[$key];
                        }
                    }
                    if (!empty($ar['alias_parts'])) {
                        foreach ($ar['alias_parts'] as $k => $v) {
                            $ar['alias_parts'][$k] = $this->transliteMe($v);
                        }
                        //$ar['_alias']=implode('-', $ar['alias_parts']);
                        $aliases[$ar['id']] = implode('-', $ar['alias_parts']);
                    }
                    //$realty_data[$ar['id']]=$ar;
                }
            } else {
                $rs = $DBC->getLastError();
                return $rs;
            }

            if (empty($aliases)) {
                $rs = 'Алиасы не заданы';
                return $rs;
            }

            $reversed_aliases = array();
            foreach ($aliases as $k => $v) {
                $reversed_aliases[$v][] = $k;
            }

            foreach ($reversed_aliases as $al => $keys) {
                if (isset($existing_aliases[$al])) {
                    $new_alias_name = $al . '-1';
                } else {
                    $new_alias_name = $al;
                }
                if (count($keys) > 1) {
                    $i = 1;
                    foreach ($keys as $k => $v) {
                        $aliases[$v] = $new_alias_name . '-' . $i;
                        $i++;
                    }
                } else {
                    $aliases[$keys[0]] = $new_alias_name;
                }
            }
            //print_r($aliases);

            $query = 'UPDATE ' . DB_PREFIX . '_data SET `translit_alias`=? WHERE id=?';
            foreach ($aliases as $k => $v) {
                $rs .= 'ID = ' . $k . ', translit_alias = <a href="' . $this->getServerFullUrl() . '/realty' . $k . '.html" target="_blank">' . $v . '</a><br>';
                $stmt = $DBC->query($query, array($v, $k), $success);
                if (!$success) {
                    $rs .= '<p class="alert alert-danger">' . $DBC->getLastError() . '</p>';
                }
            }
            return $rs;
            /* foreach($fields_for_alias as $v){
              $key=trim($v);
              if(isset($form_data_shared[$key])){
              if(($form_data_shared[$key]['type']=='select_box_structure' || $form_data_shared[$key]['type']=='select_by_query' || $form_data_shared[$key]['type']=='select_box') && $form_data_shared[trim($v)]['value_string']!='' ){
              $values[]=$form_data_shared[trim($v)]['value_string'];
              }elseif($form_data_shared[trim($v)]['value']!=''){
              $values[]=$form_data_shared[trim($v)]['value'];
              }
              }
              } */
        }

        /* if(!empty($data)){
          foreach($data as $d){
          $this->saveTranslitAlias($d);
          }
          } */

        $rs .= "Все алиасы уже установлены<br>";
        return $rs;
    }

    function update_structure() {
        $ra = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_topic';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar;
            }
        }

        if (empty($ra)) {
            return 'Категории не найдены';
        }

        $query = 'UPDATE ' . DB_PREFIX . '_topic SET url=? WHERE id=?';
        foreach ($ra as $item_id => $item) {
            if (empty($item['url'])) {
                $url = $this->transliteMe($item['name']);
                $stmt = $DBC->query($query, array($url, $item['id']));
                if ($stmt) {
                    $rs .= 'Категория ' . $item['name'] . ' успешно обновлена, установлен SEO-тег = ' . $url . '<br>';
                } else {
                    $rs .= 'Ошибка при обновлении категории ' . $item['name'] . '<br>';
                }
            }
        }
        if (empty($rs)) {
            $rs = 'Все URL уже установлены. Если вы хотите обновить структуру, то удалите URL для категории, либо очистите все URL';
        }
        return $rs;
    }

}
