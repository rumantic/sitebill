<?php
/**
 * ViewMapTrait — Geo/map functionality for Kvartira_View.
 *
 * Manages: geocodeField, getRealtyOnMap, get_city_info, get_region_info, get_country_info.
 */
trait ViewMapTrait
{
    function geocodeField($item, &$form_data_shared)
    {
        if ($item['value']['lat'] == '' && $item['value']['lng'] == '' && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            if (method_exists('geodata_admin', 'geocode_address')) {
                $address_array = array();
                if (isset($form_data_shared['country_id']) && $form_data_shared['country_id']['value_string'] != '') {
                    $address_array[] = $form_data_shared['country_id']['value_string'];
                }
                if (isset($form_data_shared['region_id']) && $form_data_shared['region_id']['value_string'] != '') {
                    $address_array[] = $form_data_shared['region_id']['value_string'];
                }
                if (isset($form_data_shared['city_id']) && $form_data_shared['city_id']['value_string'] != '') {
                    $address_array[] = $form_data_shared['city_id']['value_string'];
                    if (isset($form_data_shared['street_id']) && $form_data_shared['street_id']['value_string'] != '') {
                        $address_array[] = $form_data_shared['street_id']['value_string'];
                        if (isset($form_data_shared['number']) && $form_data_shared['number']['value'] != '') {
                            $address_array[] = $form_data_shared['number']['value'];
                        }
                    }
                }
                if (version_compare(PHP_VERSION, '8.0.0', '<')) {
                    $data = geodata_admin::geocode_address(implode(', ', $address_array));
                    if ($data && $data['lat'] != '' && $data['lng'] != '') {
                        $form_data_shared[$item['name']]['value']['lat'] = $data['lat'];
                        $form_data_shared[$item['name']]['value']['lng'] = $data['lng'];
                        $DBC = DBC::getInstance();
                        $query = 'UPDATE ' . DB_PREFIX . '_data SET `' . $item['name'] . '_lat`=?, `' . $item['name'] . '_lng`=? WHERE id=?';
                        $stmt = $DBC->query($query, array($data['lat'], $data['lng'], $form_data_shared['id']['value']));
                    }
                }
            }
        }
    }

    private function get_city_info($city_id)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/city/city_manager.php');
        $city_manager = new City_Manager();
        return $city_manager->load_by_id($city_id);
    }

    private function get_region_info($region_id)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/region/region_manager.php');
        $region_manager = new Region_Manager();
        return $region_manager->load_by_id($region_id);
    }

    private function get_country_info($country_id)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/country/country_manager.php');
        $country_manager = new Country_Manager();
        return $country_manager->load_by_id($country_id);
    }

    protected function getRealtyOnMap($form_data)
    {

        $gdata = array();
        $geoobjects_collection = array();
        $gd = array();

        foreach ($form_data as $key => $value) {
            if ($key == 'city_id') {
                $gd['city'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $value['value_string']);
            } elseif ($key == 'street_id') {
                $gd['street'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $value['value_string']);
            } elseif ($key == 'price') {
                $gd['price'] = $value['value'];
            } elseif ($key == 'topic_id') {
                $gd['type_sh'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $value['value_string']);
            } else {
                $gd[$key] = $value['value'];
            }
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_on_map.tpl')) {
            global $smarty;
            $smarty->assign('realty', $gd);
            $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_on_map.tpl');
            $html = str_replace("\r\n", ' ', $html);
            $html = str_replace("\n", ' ', $html);
            $html = str_replace("\t", ' ', $html);
            $html = addslashes($html);
        } else {
            $html = '';
        }
        //echo $html;
        $gd['html'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
        $gd['href'] = '#';

        if (isset($form_data['geo']) && $form_data['geo']['value']['lat'] != '' && $form_data['geo']['value']['lng'] != '') {
            $gd['geo_lat'] = $form_data['geo']['value']['lat'];
            $gd['geo_lng'] = $form_data['geo']['value']['lng'];

            $gc = $gd['geo_lat'] . '_' . $gd['geo_lng'];

            if (!isset($geoobjects_collection[$gc]['html'])) {
                $geoobjects_collection[$gc]['html'] = '';
            }

            $geoobjects_collection[$gc]['html'] .= $gd['html'];

            if (!isset($geoobjects_collection[$gc]['count'])) {
                $geoobjects_collection[$gc]['count'] = 0;
            }
            $geoobjects_collection[$gc]['count'] += 1;
            $geoobjects_collection[$gc]['lat'] = $gd['geo_lat'];
            $geoobjects_collection[$gc]['lng'] = $gd['geo_lng'];
        }
        return $geoobjects_collection;
    }
}
