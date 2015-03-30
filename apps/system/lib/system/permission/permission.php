<?php
/**
 * Permission manager
 * Load matrix of the permission and check access
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru   
 */
class Permission extends Sitebill {
    private $group_users;
    private $structure;
    private $nobody_group_id;
    
    /**
     * Constructor
     */
    function __construct() {
        $this->Sitebill();
        $this->load();
        $this->nobody_group_id = 999; 
    }
    
    /**
     * Load  
     */
    function load () {
        
        $this->structure = array();
        $this->group_users = array();
        
        //create hash for each groups
        $query = "select dna.*, c.name as component_name, f.name as function_name from ".DB_PREFIX."_dna dna, ".DB_PREFIX."_group g, ".DB_PREFIX."_component c, ".DB_PREFIX."_function f where dna.group_id=g.group_id and dna.component_id=c.component_id and dna.function_id=f.function_id";
        //echo $query;
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $this->structure[$this->db->row['group_id']][$this->db->row['component_name']][$this->db->row['function_name']] =  1;
        }
        
        //load group-users matrix
        $query = "select user_id, group_id  from ".DB_PREFIX."_user";
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $this->group_users[$this->db->row['user_id']] = $this->db->row['group_id'];
        }
        //echo $this->getSessionUserId().'<br>';
        //echo '<pre>';
        //print_r($this->group_users);
        //echo '</pre>';
        
        return true;
    }
    
    /**
     * Get access value for component.function
     * @param int $user_id
     * @param string $component_name
     * @param string $function_name
     * @return boolean
     */
    function get_access ( $user_id, $component_name, $function_name ) {
        $group_id = $this->group_users[$user_id];
        
        if ( $group_id == '' ) {
            $group_id = $this->nobody_group_id;
        }
        //echo 'group_id = '.$group_id.'<br>';
        if ( isset($this->structure[$group_id][$component_name][$function_name]) && $this->structure[$group_id][$component_name][$function_name] == 1 ) {
        	//echo 'true!<br>';
            return true;
        }
        return false;
    }
}
?>
