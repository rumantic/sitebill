<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class API_Request {

    private $input_data;

    function __construct() {
        $this->input_data = json_decode(file_get_contents('php://input'), true);
    }

    function get($name) {
        if ($_REQUEST[$name] != '') {
            return $_REQUEST[$name];
        } elseif ($this->input_data[$name] != '') {
            return $this->input_data[$name];
        }
        return NULL;
    }
    
    function dump () {
        //return file_get_contents('php://input');
        return $this->input_data;
    }
}
