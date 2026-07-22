<?php


namespace seo\api;


use api\aliases\API_common_alias;
use system\lib\system\apps\traits\ContextTrait;
use Illuminate\Database\Capsule\Manager as Capsule;



class seo extends API_common_alias
{
    use ContextTrait;
    function _get_region_list() {
        $result = $this->select_region_list();
        if ( $result ) {
            $ret = array(
                'status' => 'ok',
                'data' => $result,
            );
        } else {
            $ret = array(
                'status' => 'error',
                'message' => 'cant get region list',
            );
        }
        return $this->json_string($ret);
    }

    function select_region_list () {
        return Capsule::table('region')
            ->orderBy('name')
            ->get();
    }

    function get_city_list( $country_id )
    {
        $city_ids = [];
        $filter = '';
        if ( $this->getConfigValue('apps.seo.add_city_list_inside_country_filter_column') != '' ) {
            $filter = " AND c.".$this->getConfigValue('apps.seo.add_city_list_inside_country_filter_column')."=1 ";
        }
        $query = "select 
        c.city_id from " . DB_PREFIX . "_city c, " . DB_PREFIX . "_region r, " . DB_PREFIX . "_country co 
        where c.region_id=r.region_id and r.country_id=co.country_id and co.country_id=? $filter ORDER BY c.name";
        $city_object = $this->init_custom_model_object('city');

        $DBC = \DBC::getInstance();
        $stmt = $DBC->query($query, [$country_id]);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $city_ids[] = $ar['city_id'];
            }
        }
        $city_list = $city_object->load_by_id($city_ids);
        return $city_list;
    }
}
