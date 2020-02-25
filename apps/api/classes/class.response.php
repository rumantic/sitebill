<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class API_Response {
    private $state; //success, error
    private $message; // Response text (description)
    private $data; // Response data (array or string)
    function __construct( $state = '', $message = '', $data = '' ) {
        $this->set_state($state);
        $this->set_message($message);
        $this->set_data($data);
    }
    
    function get () {
        return array('state' => $this->get_state(), 'message' => $this->get_message(), 'data' => $this->get_data());
    }
    
    function set_state ( $state ) {
        $this->state = $state;
    }

    function get_state () {
        return $this->state;
    }
    
    function set_message ( $message ) {
        $this->message = $message;
    }
    
    function get_message () {
        return $this->message;
    }
    
    function set_data ( $data ) {
        $this->data = $data;
    }
    
    function get_data () {
        return $this->data;
    }
    
}