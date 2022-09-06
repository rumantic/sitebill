<?php
use Illuminate\Database\Capsule\Manager as Capsule;

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * User class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_user extends API_Common {
    /**
     * @var Permission
     */
    protected $permission;

    function __construct()
    {
        parent::__construct();
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $this->permission = new Permission();
    }

    public function _get_column () {
        $primary_key_value = $this->request()->get('primary_key_value');

        $query_result = Capsule::table('user')
            ->selectRaw(
                'mobile'
            )
            ->where('user_id', '=', $primary_key_value)
            ->first();
        if ( $query_result->mobile != '') {
            $result = '<a href="https://wa.me/'.$query_result->mobile.'" target="_blank">'.str_replace('+','', $query_result->mobile).'</a>';
            $response = new API_Response('success', 'get column success', $result);
        } else {
            $response = new API_Response('error', 'get column failed', $primary_key_value);
        }
        return $this->json_string($response->get());
    }

}
