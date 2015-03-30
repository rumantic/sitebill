<?php
class Sitebill_User {
	
	
	private static $instance=NULL;
	private $id=0;
	private $login='';
	private $name='';
	private $group_id=0;
	private $group_name='';
	private $group_system_name='';
	//private $default_group_system_name='_guest';
	private $session_key='';
	
	public static function getInstance(){
		if(self::$instance==NULL){
			self::$instance=new self();
		}
		return self::$instance;
	}

	public function isAuthorised(){
		return $this->id==0 ? false : true;
	}
	
	public function setSessionKey($session_key){
		$this->session_key==$session_key;
	}
	
	public function getUserId(){
		return $this->id;
	}
	
	public function getUserName(){
		return $this->name;
	}
	
	public function getUserLogin(){
		return $this->login;
	}
	
	public function getUserGroupName(){
		return $this->group_name;
	}
	
	public function getUserGroupId(){
		return $this->group_id;
	}
	
	public function getUserGroupSystemName(){
		return $this->group_system_name;
	}
	
	public function isAdmin(){
		return $this->group_system_name=='administrator';
	}
	
	public function initUser($userid){
		$DBC=DBC::getInstance();
		$query='SELECT u.user_id, u.fio AS name, u.login, g.name AS group_name, u.group_id, g.system_name AS group_system_name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.user_id=? LIMIT 1';
		$stmt=$DBC->query($query, array($userid));
		$userdata=array();
		if($stmt){
			$userdata=$DBC->fetch($stmt);
		}
		$this->initUserObjectByData($userdata);
	}
	
	
	private function initUserObjectByData($userdata){
		$this->name=$userdata['name'];
		$this->group_id=(int)$userdata['group_id'];
		$this->group_name=$userdata['group_name'];
		$this->login=$userdata['login'];
		$this->id=(int)$userdata['user_id'];
		$this->group_system_name=$userdata['group_system_name'];
		$this->saveCurrentState();
	}
	
	

	private function saveCurrentState(){
		$_SESSION['Sitebill_User']=array();
		$_SESSION['Sitebill_User']['name']=$this->name;
		$_SESSION['Sitebill_User']['group_id']=$this->group_id;
		$_SESSION['Sitebill_User']['group_name']=$this->group_name;
		$_SESSION['Sitebill_User']['login']=$this->login;
		$_SESSION['Sitebill_User']['id']=$this->id;
		$_SESSION['Sitebill_User']['group_system_name']=$this->group_system_name;
		$_SESSION['Sitebill_User']['session_key']=$this->session_key;
	}
	
	private function loadCurrentState(){
		if(isset($_SESSION['Sitebill_User'])){
			$userdata=$_SESSION['Sitebill_User'];
			$this->name=$userdata['name'];
			$this->group_id=(int)$userdata['group_id'];
			$this->group_name=$userdata['group_name'];
			$this->login=$userdata['login'];
			$this->id=(int)$userdata['id'];
			$this->group_system_name=$userdata['group_system_name'];
		}
	}
	
	public function logoutUser(){
		$_SESSION['Sitebill_User']=array();
		$this->id=0;
		$this->name='';
		$this->group_id=0;
		$this->group_name='';
		$this->login='';
		$this->group_system_name='';
		$this->session_key='';
		$this->saveCurrentState();
	}
	
	private function __construct(){
		$this->loadCurrentState();
	}
	
	function __destruct(){
		$this->saveCurrentState();
	}
	
	private function __clone(){
	
	}
}