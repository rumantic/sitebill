<?php
class socialauth_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
    	
    	$users=array();
    	$DBC=DBC::getInstance();
    	$query='SELECT `user_id`, `login` FROM '.DB_PREFIX.'_user';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			if(preg_match('/^(ok|fb|vk|gl|tw)[\d][\d][\d](\d+)$/', $ar['login'])){
    				$users[]=$ar;
    			}
    		}
    	}
    	if(!empty($users)){
    		$query='UPDATE '.DB_PREFIX.'_user SET `password`=? WHERE `user_id`=?';
    		foreach($users as $user){
    			$new_password=md5($user['login'].$this->getConfigValue('apps.socialauth.salt'));
    			$stmt=$DBC->query($query, array($new_password, $user['user_id']));
    		}
    	}
    	
        
        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        $rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
        return $rs;
    }
}