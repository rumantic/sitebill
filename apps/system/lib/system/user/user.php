<?php
/**
 * User_Object
 * @author Kondin Dmitry <dmn@newod.ru>
 */
class User_Object extends SiteBill {
    /**
    * Constructor
    */
    function System_User_Object () {
        $this->SiteBill();
    }
    /**
     * Get user info string
     * @param int $user_id user ID
     * @return string
     */
    function getUserInfoString ( $user_id ) {
        $this->load($user_id);
        $rs = '<b>'.$this->getKeyValue('fio', 'value').'</b>';
        return $rs;
    }
    
    /**
     * Get login by user id
     * @param int $user_id user id
     * @return string
     */
    function getLoginByUserID ( $user_id ) {
    	$query = "select * from ".DB_PREFIX."_user where user_id=?";
        $DBC=DBC::getInstance();
        
        $stmt=$DBC->query($query, array($user_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar['login'];
        }
        return '';
    }
    
    /**
     * Get user group name
     * @param int $user_id user ID
     * @return string
     */
    function getUserGroupName ( $user_id ) {
        $query = "select g.name from ".DB_PREFIX."_group g, ".DB_PREFIX."_user su where g.group_id = su.group_id and su.user_id=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar['name'];
        }
        return '';
    }
    
    /**
     * Get shor FIO
     * @param int $user_id user ID
     * @return string
     */
    function getShortFio ($user_id) {
        $query = "select * from ".DB_PREFIX."_user where user_id=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	$this->login = $ar['login'];
        	return $ar['fio'];
        }
        return '';
    }
    
    /**
     * Get email
     * @param int $user_id user id
     * @return string
     */
    function getEmail ( $user_id ) {
        $query = "select email from ".DB_PREFIX."_user where user_id=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar['email'];
        }
        return '';
     }
    
    /**
     * Get user id by email
     * @param string $email email
     * @return mixed
	 */
    function getUserIdByEmail ( $email ) {
        $query = "select user_id from ".DB_PREFIX."_user where email=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($email));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if($ar['user_id']>0){
        		return $ar['user_id'];
        	}
        }
        return false;
     }
    
    
    /**
     * Get top list
     * @param
     * @return
     */
    function getTopList () {
        $query = "select su.*, n.time_cost from ".DB_PREFIX."_user su, ".DB_PREFIX."_note n where su.user_id=n.user_id and n.time_cost > 0";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query);
        
        $sum=array();
        $ra=array();
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$sum[$ar['user_id']] += $ar['time_cost'];
        		$ra[$ar['user_id']] = $ar;
        	}
        }
        
       
        $sum_keys = array_values($sum);
       	rsort($sum_keys);
        foreach ( $sum_keys as $item_id => $sum_value ) {
            foreach ( $sum as $user_id => $sum_item ) {
                if ( $sum_item == $sum_value ) {
                    $raa[$user_id] = $ra[$user_id];
                }    
            } 
        }
        //print_r($raa);
        //echo '</pre>';
        
        $rs .= '<b>Команда</b>';
        foreach ( $raa as $user_id => $item_array ) {
            //echo '/storage/avatar/'.$item_array['avatar'].'<br>';
            if ( $item_array['avatar'] == '' ) {
                $item_array['avatar'] = 'noavatar.jpeg';
            }
            $rs .= '<div id="es" style="width: 100%; padding: 5px;"><a href="/users/'.$item_array['login'].'/"><img src="/storage/avatar/'.$item_array['avatar'].'" hspace="5" border="0" vspace="0" width="40" align="left"></a><a href="/users/'.$item_array['login'].'/">'.$item_array['fio'].'</a></div>';
        }
        return $rs;
    }
    
	/**
     * Get user publication limit
     * @param int $user_id user id
     * @return int
	 */
    function getUserPublicationLimit ( $user_id ) {
        $query = "select publication_limit from ".DB_PREFIX."_user where user_id=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id));
        
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if($ar['publication_limit'] !=''){
        		return $ar['publication_limit'];
        	}
        }
        return $this->getConfigValue('user_publication_limit');
    }
    
	/**
     * Get user group id
     * @param int $user_id user id
     * @return int
	 */
    function getUserGroupId ( $user_id ) {
        $query = "select group_id from ".DB_PREFIX."_user where user_id=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar['group_id'];
        }
        return 0;
    }
    
    function getUser($user_id){
    	$ret=array('fio'=>'','email'=>'','phone'=>'');
    	$query = "SELECT fio, email, phone FROM ".DB_PREFIX."_user WHERE user_id=?";
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query, array($user_id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$ret['fio']=$ar['fio'];
    		$ret['email']=$ar['email'];
    		$ret['phone']=$ar['phone'];
    	}
    	return $ret;
    }
}