<?php
class Sitebill_Registry {

	private static $instance=NULL;
	private $feedback=array();
	private static $handlers=array();



	private $request_values=array();
	private $request_parts=array();


	public static function getInstance(){
		if(self::$instance==NULL){
			self::$instance=new self();
		}
		return self::$instance;
	}

	public function addFeedback($key,$value){
		$this->feedback[$key]=$value;
	}

	public function getFeedback($key){
		if(isset($this->feedback[$key])){
			return $this->feedback[$key];
		}
	}


	private function __construct(){

	}

	private function __clone(){

	}

	public static function add_handler ( $handler ) {
	    self::$handlers[$handler] = $handler;
    }

    public static function get_handlers () {
	    return self::$handlers;
    }



}
