<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * User company manager tool. Manager users for this COMPANY_ID
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class User_Company_Manager extends User_Object_Manager {
    private $user_company_id = 0;
    
    /**
     * Frontend main
     */
    function frontend_main () {
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php') ) {
		    
		    $user_array = $this->load_by_id($this->getSessionUserId());
		    
		    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/group/group_manager.php');
		    $group_manager = new Group_Manager();
		    $group_array = $group_manager->load_by_system_name('realtor');
		    
            
		    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php');
            $company_admin = new company_admin();
            $company_array = $company_admin->load_by_id($user_array['company_id']['value']);
            $this->set_user_company_id($user_array['company_id']['value']);
            //echo '<pre>';
            //print_r($company_array);
            //echo '</pre>';
            
		    //set default value of the COMPANY_ID for all realtors
            $this->data_model[$this->table_name]['company_id']['type'] = 'hidden';
            $this->data_model[$this->table_name]['company_id']['value'] = $company_array['company_id']['value'];
            
		    //set default value of the GROUP_ID for all realtors
            $this->data_model[$this->table_name]['group_id']['type'] = 'hidden';
            $this->data_model[$this->table_name]['group_id']['value'] = $group_array['group_id']['value'];
            
            
        }
        return $this->main();
    }
    
    private function set_user_company_id ($company_id) {
        $this->user_company_id = $company_id;    
    }
    
    private function get_user_company_id () {
        return $this->user_company_id;    
    }
    
    
    /**
     * Grid
     * @param void
     * @return string
     */
    function grid () {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        
        
        $common_grid->add_grid_item('user_id');
        $common_grid->add_grid_item('login');
        $common_grid->add_grid_item('fio');
        $common_grid->add_grid_item('email');
        
        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
        
        $common_grid->setPagerParams(array('page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page'),'action'=>$this->action));
        
        $common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." where company_id=".$this->get_user_company_id()." ");
        $rs = $common_grid->construct_grid();
        
        return $rs;
        

        /*
        global $_SESSION;
        global $__db_prefix;
        
        $query = "select * from ".DB_PREFIX."_".$this->table_name." where company_id=".$this->get_user_company_id()." order by user_id asc";
        $this->db->exec($query);

        $rs = '<div align="left"><table border="0" width="20%">';
        $rs .= '<td ><b>ФИО</b></td>';
        $rs .= '<td></td>';
        $rs .= '</tr>';
        while ( $this->db->fetch_assoc() ) {
            $j++;
            if ( ceil($j/2) > floor($j/2)  ) {
                $row_class = "row1";
            } else {
                $j = 0;
                $row_class = "row2";
            }
            $rs .= '<tr>';
            $rs .= '<td class="'.$row_class.'" nowrap width="99%">'.$this->db->row['fio'].'</td>';
            $rs .= '<td width="10%" nowrap><a href="?action='.$this->action.'&do=edit&'.$this->primary_key.'='.$this->db->row[$this->primary_key].'"><img src="'.SITEBILL_MAIN_URL.'/img/edit.gif" border="0"></a> <a href="?action='.$this->action.'&do=delete&'.$this->primary_key.'='.$this->db->row[$this->primary_key].'" onclick="if ( confirm(\'Уверены что хотите удалить?\') ) {return true;} else {return false;}"><img src="'.SITEBILL_MAIN_URL.'/img/delete.gif" border="0"></a></td>';
            $rs .= '</tr>';
        }
        $rs .= '</table></div>';
        return $rs;
        */
    }
    
}
?>
