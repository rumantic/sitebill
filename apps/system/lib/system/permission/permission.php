<?php
/**
 * Permission manager
 * Load matrix of the permission and check access
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru   
 */
class Permission extends Sitebill {
    private static $group_users = array();
    private static $structure = array();
    private $nobody_group_id;
    
    /**
     * Constructor
     */
    function __construct() {
        $this->Sitebill();
        if ( empty(self::$structure) or empty(self::$group_users) ) {
        	$this->load();
        }
        $this->nobody_group_id = 999; 
    }
    
    /**
     * Load  
     */
    function load () {
    	$DBC=DBC::getInstance();
        
        //create hash for each groups
        $query = "SELECT dna.*, 
        		c.name AS component_name, 
        		f.name AS function_name, 
        		g.system_name AS group_name 
        		FROM ".DB_PREFIX."_dna dna, 
        		".DB_PREFIX."_group g, 
        		".DB_PREFIX."_component c, 
        		".DB_PREFIX."_function f 
        		WHERE dna.group_id=g.group_id AND dna.component_id=c.component_id AND dna.function_id=f.function_id";
        //echo $query;
        $stmt=$DBC->query($query);
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		self::$structure[$ar['group_id']][$ar['component_name']][$ar['function_name']] =  1;
        		self::$structure[$ar['group_id']]['group_name'] =  $ar['group_name'];
        	}
        }
        
        //load group-users matrix
        $query = "select user_id, group_id  from ".DB_PREFIX."_user";
        $stmt=$DBC->query($query);
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		self::$group_users[$ar['user_id']] = $ar['group_id'];
        	}
        }
        return true;
    }
    
    function clear_menu_array( $menu_array, $user_id ) {
    	foreach ($menu_array as $node => $node_array ) {
    		if ( is_array($node_array['childs']) ) {
    			foreach ($node_array['childs'] as $action => $app_info ) {
    				//echo 'action = '.$action.'<br>';
    				//echo 'user_id = '.$user_id.'<br>';
    				if ( !$this->get_access($user_id, $action, 'access') ) {
    					unset($menu_array[$node]['childs'][$action]);
    				}
    			}
    			if ( count($menu_array[$node]['childs']) == 0 ) {
    				unset($menu_array[$node]);
    			}
    		} else {
    			if ( !$this->get_access($user_id, $node, 'access') ) {
    				unset($menu_array[$node]);
    			}
    		}
    	}
    	/*
    	echo '<pre>';
    	print_r($menu_array);
    	echo '</pre>';
    	*/
    	 
    	return $menu_array;
    }
    
    function init_static_apps () {
    	$static_apps = array();
    	$static_apps['data']['title'] = 'Объявления';
    	$static_apps['country']['title'] = 'Справочник стран';
    	$static_apps['region']['title'] = 'Справочник регионов';
    	$static_apps['city']['title'] = 'Справочник городов';
    	$static_apps['district']['title'] = 'Справочник районов';
    	$static_apps['metro']['title'] = 'Справочник метро';
    	$static_apps['street']['title'] = 'Справочник улиц';
    	$static_apps['menu']['title'] = 'Редактор меню';
    	$static_apps['user']['title'] = 'Менеджер пользователей';
    	$static_apps['structure']['title'] = 'Структура разделов';
    	$static_apps['group']['title'] = 'Менеджер групп пользователей';
    	$static_apps['component']['title'] = 'Менеджер компонент';
    	$static_apps['function']['title'] = 'Менеджер функций';
    	 
    	return $static_apps;
    }
    
    function init_components () {
    	$DBC=DBC::getInstance();
    
    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
    	$apps_processor = new Apps_Processor();
    	
    	$apps_menu = array_merge($this->init_static_apps(), $apps_processor->load_apps_menu());
    	
    
    	//Добавляем в функцию действие execute
    	$query = "select function_id from ".DB_PREFIX."_function where name=?";
    	$stmt=$DBC->query($query, array('access'));
    	if (!$stmt) {
    		$query = "insert into ".DB_PREFIX."_function (name, description) values (?, ?)";
    		$stmt=$DBC->query($query, array('access', 'Доступ'));
    	}
    		 
    
    	foreach ( $apps_menu as $action => $app_info ) {
    		$query = "select component_id from ".DB_PREFIX."_component where name=?";
    		$stmt=$DBC->query($query, array($action));
    		if ( !$stmt ) {
    			$query = "insert into ".DB_PREFIX."_component (name, title) values (?, ?)";
    			$stmt=$DBC->query($query, array($action, $app_info['title']));
    		}
    		
    		$this->add_permission($action, 'access');
    	}
    
    
    	//echo '<pre>';
    	//print_r($apps_menu);
    	//echo '</pre>';
    
    }
    
    function add_component ( $name, $title ) {
    	$DBC=DBC::getInstance();
    	$query = "select component_id from ".DB_PREFIX."_component where name=?";
    	$stmt=$DBC->query($query, array($name));
    	if ( !$stmt ) {
    		$query = "insert into ".DB_PREFIX."_component (name, title) values (?, ?)";
    		$stmt=$DBC->query($query, array($name, $title));
    	}
    }
    
    function add_permission ( $action, $do ) {
    	$DBC=DBC::getInstance();
    	$component_id = 0;
    	$function_id = 0;
    
    	//Получим ID компонента
    	$query = "select component_id from ".DB_PREFIX."_component where name=?";
    	$stmt=$DBC->query($query, array($action));
    	if ($stmt) {
    		$ar=$DBC->fetch($stmt);
    		$component_id = intval($ar['component_id']);
    	}
    
    	//Получим ID функции
    	$query = "select function_id from ".DB_PREFIX."_function where name=?";
    	$stmt=$DBC->query($query, array($do));
    	if ($stmt) {
    		$ar=$DBC->fetch($stmt);
    		$function_id = intval($ar['function_id']);
    	}
    
    	if ( $component_id > 0 and $function_id > 0 ) {
    		$query = "select component_function_id from ".DB_PREFIX."_component_function where component_id=? and function_id=?";
    		$stmt=$DBC->query($query, array($component_id, $function_id));
    		if ( !$stmt ) {
    			$query = "insert into ".DB_PREFIX."_component_function (component_id, function_id) values (?, ?)";
    			$stmt=$DBC->query($query, array($component_id, $function_id));
    		}
    	}
    }
    
    
    /**
     * Get access value for component.function
     * @param int $user_id
     * @param string $component_name
     * @param string $function_name
     * @return boolean
     */
    function get_access ( $user_id, $component_name, $function_name ) {
        $group_id = self::$group_users[$user_id];
        
        if ( $group_id == '' ) {
            $group_id = $this->nobody_group_id;
        }
        if ( self::$structure[$group_id]['group_name'] == 'admin' ) {
        	return true;
        	
        }
        //echo 'group_id = '.$group_id.'<br>';
        if ( isset(self::$structure[$group_id][$component_name][$function_name]) && self::$structure[$group_id][$component_name][$function_name] == 1 ) {
        	//echo 'true!<br>';
            return true;
        }
        return false;
    }
}