<?php

use Illuminate\Http\Request;

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class API_Request {

    private $input_data;
    /**
     * @var Request
     */
    public $iRequest = null;

    function __construct($request) {
        $this->iRequest = $request;
        $this->input_data = json_decode(file_get_contents('php://input'), true);
    }

    function get($name) {
        if ($_REQUEST[$name] != '') {
            return $_REQUEST[$name];
        } elseif ($this->iRequest->get($name) != '') {
            return $this->iRequest->get($name);
        }
        return NULL;
    }

    function dump () {
        //return file_get_contents('php://input');
        return $this->input_data;
    }
}
