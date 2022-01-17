<?php

namespace api\types;

class method
{
    public $api;
    public $name;
    public $method;
    public $params;

    public function __construct( $api, $name, $method, $params = null )
    {
        $this->api = $api;
        $this->name = $name;
        $this->method = $method;
        $this->params = $params;
    }

}
