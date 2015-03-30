<?php
/**
 * Component manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Component_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function Component_Manager() {
        $this->Sitebill();
        $this->table_name = 'component';
        $this->action = 'component';
        $this->app_title = Multilanguage::_('COMPONENT_APP_NAME','system');
        $this->primary_key = 'component_id';
        $this->grid_key = 'name';
        
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/version/version.php';
        $version = new Version();
        if ( !$version->get_version_value('component.table') ) {
            $this->create_table();
            $version->set_version_value('component.table', 1);
        }

        if ( !$version->get_version_value('component_function.table') ) {
            $this->create_component_function_table();
            $version->set_version_value('component_function.table', 1);
        }
        
        $this->data_model = $this->get_component_model();
    }
    
    function create_component_function_table () {
        $query = "
CREATE TABLE `".DB_PREFIX."_component_function` (
  `component_function_id` int(11) NOT NULL AUTO_INCREMENT,
  `component_id` int(11) NOT NULL DEFAULT '0',
  `function_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`component_function_id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING." ;
        ";
        $this->db->exec($query);
    }
    
    function create_table () {
        $query = "
CREATE TABLE `".DB_PREFIX."_component` (
  `component_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`component_id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING." ;
        ";
        $this->db->exec($query);
    }
    
    /**
     * Get component model
     * @param
     * @return
     */
    function get_component_model () {
		$form_component = array();
		
		$form_component['component']['component_id']['name'] = 'component_id';
		$form_component['component']['component_id']['title'] = Multilanguage::_('L_ID');
		$form_component['component']['component_id']['value'] = 0;
		$form_component['component']['component_id']['length'] = 40;
		$form_component['component']['component_id']['type'] = 'primary_key';
		$form_component['component']['component_id']['required'] = 'off';
		$form_component['component']['component_id']['unique'] = 'off';
		
		$form_component['component']['name']['name'] = 'name';
		$form_component['component']['name']['title'] = Multilanguage::_('COMPONENT_NAME','system');
		$form_component['component']['name']['value'] = '';
		$form_component['component']['name']['length'] = 40;
		$form_component['component']['name']['type'] = 'safe_string';
		$form_component['component']['name']['required'] = 'on';
		$form_component['component']['name']['unique'] = 'off';
		
		return $form_component;
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    
		$rs = $this->getTopMenu();

		switch( $this->getRequestValue('do') ){
            case 'structure':
                $rs = $this->getStructureProcessor( $this->getRequestValue('component_id') );
                return $rs;
            break;
		    
			case 'edit_done' : {
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
			    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			        $rs = $this->get_form($form_data[$this->table_name], 'edit');
			    } else {
			        $this->edit_data($form_data[$this->table_name]);
			        if ( $this->GetErrorMessage() ) {
			        	$rs .= '<div class="error">'.$this->GetErrorMessage().'</div><br>';
			        }
			         
			        $rs .= $this->grid();
			    }
				break;
			}
		    
			case 'edit' : {
                $form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
                //echo '<pre>';
                //print_r($form_data[$this->table_name]);			    
			    $rs = $this->get_form($form_data[$this->table_name], 'edit');
				
			    break;
			}
			case 'delete' : {
		        $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
		        $rs .= $this->grid();

		        break;
			}
			
			case 'new_done' : {
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
	            //echo '<pre>';
	            //print_r($form_data['data']);
			    
			    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			        $rs = $this->get_form($form_data[$this->table_name], 'new');
			        
			    } else {
			        $this->add_data($form_data[$this->table_name]);
			        if ( $this->GetErrorMessage() ) {
			        	$rs .= '<div class="error">'.$this->GetErrorMessage().'</div><br>';
			        }
			        $rs .= $this->grid();
			    }
				break;
			}
			
			case 'new' : {
			    $rs = $this->get_form($form_data[$this->table_name]);
				break;
			}
			case 'mass_delete' : {
				$id_array=array();
				$ids=trim($this->getRequestValue('ids'));
				if($ids!=''){
					$id_array=explode(',',$ids);
				}
				$rs.=$this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
				break;
			}
			default : {
			    $rs .= $this->grid($user_id);
			}
		}
		$rs_new = $this->get_app_title_bar();
		$rs_new .= $rs;
		
		
		return $rs_new;
	}
    
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    $rs = '';
	    $rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">Добавить компонент</a>';
	    return $rs;
	}
	
    /**
     * Add function to component
     * @param int $component_id
     * @return int $function_id
     * @return void
     */
    function addFunctionToComponent ( $component_id, $function_id ) {
        $query = "insert into ".DB_PREFIX."_component_function (component_id, function_id) values ($component_id, $function_id)";
        
        //echo $query;
        $this->db->exec($query);
        if ( $this->db->error ) {
        	$this->riseError($this->db->error);
        }
    }
    
    /**
     * Get name
     * @param int $component_id
     * @return string 
     */
    function getName ( $component_id ) {
        $query = "select * from ".DB_PREFIX."_component where component_id=$component_id";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        return $this->db->row['name'];
    }
    
    /**
     * Get function list
     * @param int $component_id component ID
     * @return string
     */
    function getFunctionList ( $component_id ) {
        $query = "select a.* from ".DB_PREFIX."_component_function ma, ".DB_PREFIX."_function a where ma.component_id=$component_id and a.function_id=ma.function_id";
        $this->db->exec($query);
        $rs .= '<table border="0" width="50%">';
        $rs .= '<tr><td class="row_title" nowrap colspan="2">'.Multilanguage::_('AVIAL_FUNCTION_LIST','system').'</td></tr>';
        $rs .= '<tr>
        <td class="row_title">'.Multilanguage::_('SYSTEM_NAME','system').'</td>
        <td class="row_title">'.Multilanguage::_('DESCRIPTION','system').'</td>
        </tr>';
        while ( $this->db->fetch_assoc() ) {
            $rs .= '<tr class="row3">';
            $rs .= '<td>'.$this->db->row['name'].'</td>';
            $rs .= '<td>'.$this->db->row['description'].'</td>';
            $rs .= '</tr>'; 
        }
        $rs .= '</table>';
        return $rs;
    }
    
    /**
     * Get function select box 
     * @param
     * @return
     */
    function getFunctionSelectBox () {
        $query = "select * from ".DB_PREFIX."_function";
        $this->db->exec($query);
        $rs = '<select name="function_id">';
        while ( $this->db->fetch_assoc() ) {
            $rs .= '<option value="'.$this->db->row['function_id'].'">'.$this->db->row['description'].' | '.$this->db->row['name'].'</option>';
        }
        $rs .= '</select>';
        return $rs;
    }
    
    
    /**
     * Include function form
     * @param int $component_id component ID
     * @return string
     */
    function includeFunctionForm ($component_id) {
        $rs = '<form class="form-horizontal">';
        $rs .= '<div class="control-group">';
        $rs .= '<div class="controls">';
        $rs .= $this->getFunctionSelectBox();
        $rs .= '</div>';
        $rs .= '</div>';
        $rs .= '<div class="control-group">';
        $rs .= '<div class="controls">';
        $rs .= '<input type="submit" class="btn btn-primary" value="'.Multilanguage::_('ADD','system').'">';
        $rs .= '</div>';
        $rs .= '</div>';
        $rs .= '<input type="hidden" name="action" value="component">';
        $rs .= '<input type="hidden" name="component_id" value="'.$component_id.'">';
        $rs .= '<input type="hidden" name="do" value="structure">';
        $rs .= '<input type="hidden" name="structure_do" value="add">';
        
        $rs .= '<form>';
        return $rs;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid () {
        global $_SESSION;
        global $__db_prefix;
        
        $query = "select * from ".DB_PREFIX."_".$this->table_name." order by '".$this->grid_key."'";
        //echo $query;
        
        $this->db->exec($query);
        
        $rs = '<table class="table table-hover">';
        $rs .= '<thead>';
        $rs .= '<tr>';
        $rs .= '<th>'.Multilanguage::_('L_TEXT_TITLE').'</th>';
        $rs .= '<th></th>';
        $rs .= '</tr>';
        $rs .= '<thead>';
        $rs .= '<tbody>';
        while ( $this->db->fetch_assoc() ) {
        	$rs .= '<tr>';
        	$rs .= '<td>'.$this->db->row[$this->grid_key].'</td>';
        	$rs .= '<td>
            <a class="btn btn-info" href="?action='.$this->action.'&do=edit&'.$this->primary_key.'='.$this->db->row[$this->primary_key].'"><i class="icon-white icon-pencil"></i></a>
            <a class="btn btn-danger" href="?action='.$this->action.'&do=delete&'.$this->primary_key.'='.$this->db->row[$this->primary_key].'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>
			<a href="?action='.$this->action.'&do=structure&'.$this->primary_key.'='.$this->db->row[$this->primary_key].'" class="btn btn-info">структура</a>
            </td>';
        	$rs .= '</tr>';
        }
        $rs .= '</tbody>';
        $rs .= '</table>';

       
        return $rs;
    }
    
	
    /**
     * Get structure processor
     * @param int $component_id 
     * @return string
     */
    function getStructureProcessor ( $component_id ) {

        switch ( $this->getRequestValue('structure_do', 'default') ) {
            case 'add':
                $this->addFunctionToComponent($component_id, $this->getRequestValue('function_id'));
                if ( $this->GetErrorMessage() ) {
                	$rs .= '<div class="error">'.$this->GetErrorMessage().'</div><br>';
                }
                
            break;
        }
        
        $rs .= Multilanguage::_('COMPONENT_NAME','system').' <b>'.$this->getName($component_id).'</b>';
        
        $rs .= $this->getFunctionList( $component_id );
        $rs .= Multilanguage::_('INC_FUNCTIONS_TO_COMPONENT','system');
        $rs .= $this->includeFunctionForm($component_id);
        
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        
        return $rs_new;
    }
    
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$search_queries=array(
			Multilanguage::_('TABLE_DNA','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_dna WHERE component_id=?',
			Multilanguage::_('TABLE_COMP_FUNC','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_component_function WHERE component_id=?'
		);
		$ans=array();
		foreach($search_queries as $k=>$v){
			$query=str_replace('?', $primary_key_value, $v);
			$this->db->exec($query);
		    if ($this->db->success) {
		    	$this->db->fetch_assoc();
		    	$rs=$this->db->row['rs'];
		        if($rs!=0){
		        	$ans[]=sprintf(Multilanguage::_('MESSAGE_CANT_DELETE','system'), $k);
		        }
		    }
		}
		if(empty($ans)){
			return parent::delete_data($table_name, $primary_key, $primary_key_value);
		}else{
			$this->riseError(implode('<br />',$ans));
		}
	}
    
}
?>