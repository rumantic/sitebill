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
}
