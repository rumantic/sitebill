<?php
namespace api\aliases;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.controller.php');

class API_Laravel_Controller_alias extends Controller {
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function api(Request $request)
    {
        $api_controller = new API_Controller_alias();
        $api_controller->main();

    }
}
