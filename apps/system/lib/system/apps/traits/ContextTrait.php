<?php
namespace system\lib\system\apps\traits;

trait ContextTrait {
    /**
     * @var \Object_Manager
     */
    private $context;


    public function set_context($context)
    {
        $this->context = $context;
    }

    public function get_context()
    {
        return $this->context;
    }
}
