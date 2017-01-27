<?php
/**
 * Menu structure manager
 */
class Menu_Structure_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function Menu_Structure_Manager() {
        $this->Sitebill();
        $this->table_name = 'menu_structure';
        $this->action = 'menu_structure';
        $this->primary_key = 'menu_structure_id';
        $this->grid_key = 'name';
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $this->get_menu_structure_model();
    }
    
    /**
     * Get menu_structure model
     * @param
     * @return
     */
    function get_menu_structure_model () {
		$form_menu_structure = array();
		$languages = Multilanguage::availableLanguages();
		
		
		$form_menu_structure['menu_structure']['menu_structure_id']['name'] = 'menu_structure_id';
		$form_menu_structure['menu_structure']['menu_structure_id']['title'] = 'Идентификатор';
		$form_menu_structure['menu_structure']['menu_structure_id']['value'] = 0;
		$form_menu_structure['menu_structure']['menu_structure_id']['length'] = 40;
		$form_menu_structure['menu_structure']['menu_structure_id']['type'] = 'primary_key';
		$form_menu_structure['menu_structure']['menu_structure_id']['required'] = 'off';
		$form_menu_structure['menu_structure']['menu_structure_id']['unique'] = 'off';
		
		$form_menu_structure['menu_structure']['menu_id']['name'] = 'menu_id';
		$form_menu_structure['menu_structure']['menu_id']['title'] = Multilanguage::_('L_ID');
		$form_menu_structure['menu_structure']['menu_id']['value'] = '';
		$form_menu_structure['menu_structure']['menu_id']['length'] = 40;
		$form_menu_structure['menu_structure']['menu_id']['type'] = 'hidden';
		$form_menu_structure['menu_structure']['menu_id']['required'] = 'on';
		$form_menu_structure['menu_structure']['menu_id']['unique'] = 'off';
		
		$form_menu_structure['menu_structure']['name']['name'] = 'name';
		$form_menu_structure['menu_structure']['name']['title'] = Multilanguage::_('SUBPUNKT_NAME','system');
		$form_menu_structure['menu_structure']['name']['value'] = '';
		$form_menu_structure['menu_structure']['name']['length'] = 40;
		$form_menu_structure['menu_structure']['name']['type'] = 'safe_string';
		$form_menu_structure['menu_structure']['name']['required'] = 'on';
		$form_menu_structure['menu_structure']['name']['unique'] = 'off';
		if(1==$this->getConfigValue('apps.language.use_langs')){
			foreach ( $languages as $language_id => $language_title ) {
				$form_menu_structure['menu_structure']['name_'.$language_id]['name'] = 'name_'.$language_id;
				$form_menu_structure['menu_structure']['name_'.$language_id]['title'] = Multilanguage::_('SUBPUNKT_NAME','system').'('.$language_id.')';
				$form_menu_structure['menu_structure']['name_'.$language_id]['value'] = '';
				$form_menu_structure['menu_structure']['name_'.$language_id]['length'] = 40;
				$form_menu_structure['menu_structure']['name_'.$language_id]['type'] = 'safe_string';
				$form_menu_structure['menu_structure']['name_'.$language_id]['required'] = 'off';
				$form_menu_structure['menu_structure']['name_'.$language_id]['unique'] = 'off';
			}
		}
		
		$form_menu_structure['menu_structure']['url']['name'] = 'url';
		$form_menu_structure['menu_structure']['url']['title'] = Multilanguage::_('URL_NAME','system');
		$form_menu_structure['menu_structure']['url']['value'] = '';
		$form_menu_structure['menu_structure']['url']['length'] = 40;
		$form_menu_structure['menu_structure']['url']['type'] = 'safe_string';
		$form_menu_structure['menu_structure']['url']['required'] = 'off';
		$form_menu_structure['menu_structure']['url']['unique'] = 'off';
		
		$form_menu_structure['menu_structure']['sort_order']['name'] = 'sort_order';
		$form_menu_structure['menu_structure']['sort_order']['title'] = Multilanguage::_('SORT_ORDER','system');
		$form_menu_structure['menu_structure']['sort_order']['value'] = '';
		$form_menu_structure['menu_structure']['sort_order']['length'] = 40;
		$form_menu_structure['menu_structure']['sort_order']['type'] = 'safe_string';
		$form_menu_structure['menu_structure']['sort_order']['required'] = 'off';
		$form_menu_structure['menu_structure']['sort_order']['unique'] = 'off';
		
		return $form_menu_structure;
    }
    
    /**
     * Grid
     * @param void
     * @return string
     */
    function grid ( $menu_id ) {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
    	$common_grid = new Common_Grid($this);
    
    
    	$common_grid->add_grid_item($this->primary_key);
    	$common_grid->add_grid_item('name');
    	$common_grid->add_grid_item('url');
    	$common_grid->add_grid_item('sort_order');
    	 
    	$common_grid->add_grid_control('edit');
    	$common_grid->add_grid_control('delete');
    
    	$common_grid->setPagerParams(array('action'=>'menu','do'=>'structure','menu_id'=>$menu_id,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
    
    	$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." where menu_id=$menu_id order by sort_order");
    	$rs = $common_grid->construct_grid(array('edit' =>'&action=menu&do=structure&subdo=edit&menu_id='.$menu_id, 'delete' =>'&action=menu&do=structure&subdo=delete&menu_id='.$menu_id), true);
    	return $rs;
    }
    
    
    /**
     * Grid
     * @param int $menu_id menu id
     * @return string
     */
    function grid1 ( $menu_id ) {
        global $_SESSION;
        $DBC=DBC::getInstance();
        
	    
        
        $query = "select * from ".DB_PREFIX."_".$this->table_name." where menu_id=$menu_id order by '".$this->grid_key."'";
        $stmt=$DBC->query($query);

        $rs = '<div align="left"><table border="0" width="20%"><tr>';
        $rs .= '<td ><b>'.Multilanguage::_('L_TEXT_TITLE').'</b></td>';
        $rs .= '<td ><b>URL</b></td>';
        $rs .= '<td></td>';
        $rs .= '</tr>';
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$j++;
	            if ( ceil($j/2) > floor($j/2)  ) {
	                $row_class = "row1";
	            } else {
	                $j = 0;
	                $row_class = "row2";
	            }
	            $rs .= '<tr>';
	            $rs .= '<td class="'.$row_class.'" nowrap width="99%">'.$ar[$this->grid_key];
	            $rs .= '</td>';
	            $rs .= '<td class="'.$row_class.'" nowrap ">'.$ar['url'].'</td>';
	            
	            $rs .= '<td width="10%" nowrap style="vertical-align: top;">
	            <a href="?action=menu&do=structure&menu_id='.$menu_id.'&subdo=edit&'.$this->primary_key.'='.$ar[$this->primary_key].'"><img src="'.SITEBILL_MAIN_URL.'/img/edit.gif" border="0"></a> 
	            <a href="?action=menu&do=structure&menu_id='.$menu_id.'&subdo=delete&'.$this->primary_key.'='.$ar[$this->primary_key].'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"><img src="'.SITEBILL_MAIN_URL.'/img/delete.gif"></a>
	            </td>';
	            $rs .= '</tr>';
        	}
        }
        $rs .= '</table></div>';
        return $rs;
    }
}