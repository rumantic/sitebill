<?php
/**
 * KrascapMapTrait — Map display methods extracted from SiteBill_Krascap.
 *
 * Methods: map, map2, map_search
 */
trait KrascapMapTrait
{
    function map($only_data = false)
    {
        $data = array();

        if ($this->getConfigValue('apps.geodata.enable') != 1) {
            $this->template->assert('_geo_data_hide', 1);
            return json_encode($data);
        }


        $params['id'] = $this->getRequestValue('id');
        $params['topic_id'] = $this->getRequestValue('topic_id');
        $params['order'] = $this->getRequestValue('order');
        $params['region_id'] = $this->getRequestValue('region_id');
        $params['city_id'] = $this->getRequestValue('city_id');
        $params['district_id'] = $this->getRequestValue('district_id');
        $params['metro_id'] = $this->getRequestValue('metro_id');
        $params['street_id'] = $this->getRequestValue('street_id');
        $params['page'] = $this->getRequestValue('page');
        $params['spec'] = $this->getRequestValue('spec');
        $params['owner'] = (int)$this->getRequestValue('owner');
        $params['asc'] = $this->getRequestValue('asc');
        if (NULL != $this->getRequestValue('user_id')) {
            $params['user_id'] = $this->getRequestValue('user_id');
        }

        $params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
        $this->template->assert('price', $params['price']);

        $params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
        $this->template->assert('price_min', $params['price_min']);

        $params['house_number'] = $this->getRequestValue('house_number');
        $this->template->assert('house_number', $params['house_number']);

        $params['onlyspecial'] = $this->getRequestValue('onlyspecial');
        $this->template->assert('onlyspecial', $params['onlyspecial']);

        $params['floor_min'] = (int)$this->getRequestValue('floor_min');
        $params['floor_max'] = (int)$this->getRequestValue('floor_max');

        $params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
        $params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');

        $params['square_min'] = (int)$this->getRequestValue('square_min');
        $params['square_max'] = (int)$this->getRequestValue('square_max');

        $params['is_phone'] = (int)$this->getRequestValue('is_phone');
        $params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
        $params['has_photo'] = (int)$this->getRequestValue('has_photo');
        $params['is_internet'] = (int)$this->getRequestValue('is_internet');

        $params['room_count'] = $this->getRequestValue('room_count');
        $params['optype'] = $this->getRequestValue('optype');
        $params['extended_search'] = $this->getRequestValue('extended_search');
        $params['search'] = $this->getRequestValue('search');
        //$params['no_portions'] = 1;
        $params['per_page'] = 1000;
        $params['no_premium_filtering'] = 1;
        $params['has_geo'] = 1;


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/GridConstructorFactory.php';
        $grid_constructor = GridConstructorFactory::create();
        $odata = array();
        $odata = $grid_constructor->get_sitebill_adv_ext($params);

        global $smarty;

        foreach ($odata as $k => $d) {
            $data[$k]['currency_name'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
            $data[$k]['city'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
            $data[$k]['street'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
            if ((int)$d['price'] != 0) {
                $gdata[$k]['price'] = number_format($d['price'], 0, '.', ' ');
            } else {
                $gdata[$k]['price'] = $d['price'];
            }
            $data[$k]['type_sh'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
            $data[$k]['title'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city'] . ' ' . $d['street'] . (($d['number'] != '' && $d['number'] != 0) ? ', ' . $d['number'] : '') . ' (' . $gdata[$k]['price'] . ')');
            $smarty->assign('realty', $d);
            $html = $smarty->fetch('realty_on_map.tpl');
            $html = str_replace("\r\n", ' ', $html);
            $html = str_replace("\n", ' ', $html);
            $html = str_replace("\t", ' ', $html);
            $html = addslashes($html);
            $data[$k]['html'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
            $data[$k]['geo_lat'] = $d['geo_lat'];
            $data[$k]['geo_lng'] = $d['geo_lng'];
            $data[$k]['href'] = $d['href'];
            $data[$k]['parent_category_url'] = $d['parent_category_url'];
            $data[$k]['id'] = $d['id'];
        }

        if ($only_data) {
            return json_encode($data);
        }

        $this->template->assert('_geo_data', json_encode($data));

        $this->template->assert('main_file_tpl', 'map.tpl');
        return true;
    }

    function map2($only_data = false)
    {

        global $smarty;


        $params['id'] = $this->getRequestValue('id');
        $params['topic_id'] = $this->getRequestValue('topic_id');
        $params['order'] = $this->getRequestValue('order');
        $params['region_id'] = $this->getRequestValue('region_id');
        $params['city_id'] = $this->getRequestValue('city_id');
        $params['district_id'] = $this->getRequestValue('district_id');
        $params['metro_id'] = $this->getRequestValue('metro_id');
        $params['street_id'] = $this->getRequestValue('street_id');
        $params['page'] = $this->getRequestValue('page');
        $params['spec'] = $this->getRequestValue('spec');
        $params['owner'] = (int)$this->getRequestValue('owner');
        $params['asc'] = $this->getRequestValue('asc');
        if (NULL != $this->getRequestValue('user_id')) {
            $params['user_id'] = $this->getRequestValue('user_id');
        }

        $params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
        $this->template->assert('price', $params['price']);

        $params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
        $this->template->assert('price_min', $params['price_min']);

        $params['house_number'] = $this->getRequestValue('house_number');
        $this->template->assert('house_number', $params['house_number']);

        $params['onlyspecial'] = $this->getRequestValue('onlyspecial');
        $this->template->assert('onlyspecial', $params['onlyspecial']);

        $params['floor_min'] = (int)$this->getRequestValue('floor_min');
        $params['floor_max'] = (int)$this->getRequestValue('floor_max');

        $params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
        $params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');

        $params['square_min'] = (int)$this->getRequestValue('square_min');
        $params['square_max'] = (int)$this->getRequestValue('square_max');

        $params['is_phone'] = (int)$this->getRequestValue('is_phone');
        $params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
        $params['has_photo'] = (int)$this->getRequestValue('has_photo');
        $params['is_internet'] = (int)$this->getRequestValue('is_internet');

        $params['room_count'] = $this->getRequestValue('room_count');
        $params['optype'] = $this->getRequestValue('optype');
        $params['extended_search'] = $this->getRequestValue('extended_search');
        $params['search'] = $this->getRequestValue('search');
        $params['no_portions'] = 1;
        $params['no_premium_filtering'] = 1;
        $params['has_geo'] = 1;


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/GridConstructorFactory.php';
        $grid_constructor = GridConstructorFactory::create();
        $data = $grid_constructor->get_sitebill_adv_ext($params);

        global $smarty;

        foreach ($data as $k => $d) {
            $data[$k]['currency_name'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
            $data[$k]['city'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
            $data[$k]['street'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
            $data[$k]['price'] = number_format($d['price'], 0, '.', ' ');
            $data[$k]['type_sh'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
            $data[$k]['title'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city'] . ' ' . $d['street'] . (($d['number'] != '' && $d['number'] != 0) ? ', ' . $d['number'] : '') . ' (' . $data[$k]['price'] . ')');
            $smarty->assign('realty', $d);
            $html = $smarty->fetch('realty_on_map.tpl');
            $html = str_replace("\r\n", ' ', $html);
            $html = str_replace("\n", ' ', $html);
            $html = str_replace("\t", ' ', $html);
            $html = addslashes($html);
            $data[$k]['html'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
        }
        if ($only_data) {
            return json_encode($data);
        }

        $this->template->assert('data', json_encode($data));

        return $smarty->fetch('map.tpl');
        return true;
    }

    function map_search()
    {
        global $smarty;
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/map_search.tpl');
        return 'map_search';
    }
}
