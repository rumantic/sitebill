<?php
class socialauth_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }
    
    function main () {
    	
    	$users=array();
    	$DBC=DBC::getInstance();
    	$query='SELECT `user_id`, `login`, `tw_id`, `vk_id`, `ok_id`, `gl_id`, `fb_id` FROM '.DB_PREFIX.'_user';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			if(preg_match('/^(ok|fb|vk|gl|tw)[\d][\d][\d](\d+)$/', $ar['login'])){
    				if($ar['tw_id']=='' && $ar['vk_id']=='' && $ar['ok_id']=='' && $ar['gl_id']=='' && $ar['fb_id']==''){
    					$users[]=$ar;
    				}
    				
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
        
        $rs .= 'Производим обновление пользователей на новую систему авторизации<br />';
        
        $DBC=DBC::getInstance();
        $query='SELECT user_id, login, email, tw_id, vk_id, ok_id, gl_id, fb_id FROM re_user';
        $stmt=$DBC->query($query);
         
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		if(preg_match('/^(tw|ok|gl|fb|vk)([0-9]+)$/', $ar['login'], $matches)){
        			$ar['_t']=$matches[1];
        			if($ar[$matches[1].'_id']==''){
        				$ar['_i']=$matches[2];
        				$ret[]=$ar;
        			}
        
        				
        		}elseif(preg_match('/^(tw|ok|gl|fb|vk)([0-9]+)@(tw|ok|gl|fb|vk)/', $ar['email'])){
        			$ar['_t']=$matches[1];
        			if($ar[$matches[1].'_id']==''){
        				$ar['_i']=$matches[2];
        				$ret[]=$ar;
        			}
        
        		}
        	}
        }
         
        if(!empty($ret)){
        	$count=0;
        	foreach ($ret as $ar){
        		$query='UPDATE re_user SET `'.$ar['_t'].'_id`=? WHERE user_id=?';
        		$stmt=$DBC->query($query, array($ar['_i'], $ar['user_id']));
        		$count+=1;
        	}
        	$rs .= 'Обновлено '.$count.' пользователей<br />';
        }else{
        	$rs .= 'Нет пользователей нуждающихся в обновлении<br />';
        }
        
        return $rs;
    }
}
