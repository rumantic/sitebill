<?php
/**
 * Street manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Street_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function Street_Manager() {
        $this->SiteBill();
        $this->table_name = 'street';
        $this->action = 'street';
        $this->app_title = Multilanguage::_('STREET_APP_NAME','system');
        $this->primary_key = 'street_id';
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $data_model->get_street_model();
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    /*function main () {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    
		$rs = $this->getTopMenu();

		switch( $this->getRequestValue('do') ){
			case 'edit_done' : {
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
			    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			        $rs = $this->get_form($form_data[$this->table_name], 'edit');
			    } else {
			        $this->edit_data($form_data[$this->table_name]);
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
		        if ( $this->getError() ) {
			        $rs .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').': '.$this->GetErrorMessage().'<br>';
			        $rs .= '<a href="?action='.$this->action.'">ОК</a>';
			        $rs .= '</div>';
			    } else {
		            $rs .= $this->grid();
			    }	
		        
			    
				break;
			}
			case 'export' : {
				$rs.=$this->getExportForm();
				break;
			}
			
			case 'export_done' : {
			    if ( $this->isDemo() ) {
			        $rs = $this->demo_function_disabled();
			        return $rs;
			    }
				$ret='';
				$city_id=(int)$_REQUEST['city_id'];
				if($city_id!=0){
					if($this->getConfigValue('link_street_to_city')){
						$query='SELECT s.*, d.name AS district FROM '.DB_PREFIX.'_street s LEFT JOIN '.DB_PREFIX.'_district d ON s.district_id=d.id WHERE s.city_id='.(int)$_REQUEST['city_id'].' ORDER BY s.name';
					}else{
						$query='SELECT s.*, d.name AS district FROM '.DB_PREFIX.'_street s LEFT JOIN '.DB_PREFIX.'_district d ON s.district_id=d.id WHERE s.district_id IN (SELECT id FROM '.DB_PREFIX.'_district WHERE city_id='.(int)$_REQUEST['city_id'].') ORDER BY s.name';
					}
					
				}else{
					$query='SELECT s.*, d.name AS district FROM '.DB_PREFIX.'_street s LEFT JOIN '.DB_PREFIX.'_district d ON s.district_id=d.id ORDER BY s.name';
				}
				$this->db->exec($query);
				while($this->db->fetch_assoc()){
					if(isset($_REQUEST['need_districts'])){
						$ret.=$this->db->row['name'].';'.$this->db->row['district']."\r\n";
					}else{
						$ret.=$this->db->row['name']."\r\n";
					}
					
				}
				$f=fopen(SITEBILL_DOCUMENT_ROOT.'/street_export.csv','w');
				fwrite($f,$ret);
				fclose($f);
				$rs.=Multilanguage::_('L_MESSAGE_EXPORT_DONE').'<a href="/street_export.csv">street_export.csv</a>';
				//echo $query;
				//print_r($_REQUEST);
				break;
			}
			
            case 'load_form':
                $rs = $this->getTopMenu();
                $rs .= $this->getLoadForm();
            break;
            
            case 'load_done':
                if ( $this->isDemo() ) {
                	$rs = $this->demo_function_disabled();
                	return $rs;
                }
                
                $rs = $this->getTopMenu();
                
                $csv_strings = $this->loadData();
                if ( !$csv_strings ) {
                    $rs .= $this->getLoadForm();
                } else {
                    $rs .= '<p>&nbsp;</p>';
                    $rs .= $this->importData( $csv_strings );
                    if ( $this->getError() ) {
                        $rs .= $this->getLoadForm();
                    }
                }
            break;
            
			case 'new_done' : {
        		
	            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
	            
	            if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			        $rs = $this->get_form($form_data[$this->table_name], 'new');
			        
			    } else {
			        $this->add_data($form_data[$this->table_name]);
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
		$rs_new = '<div class="apps_path">'.Multilanguage::_('L_ADMIN_MENU_APPLICATIONS').' / ';
		if ( !empty($this->app_title) ) {
			$rs_new .= '<a href="?action='.$this->action.'">'.$this->app_title.'</a>';
		} else {
			$rs_new .= '<a href="?action='.$this->action.'">'.$this->action.'</a>';
		}
		$rs_new .= '</div>';
		$rs_new .= '<div class="clear"></div>';
		$rs_new .= $rs;
		
		return $rs_new;
    }*/
    
    protected function _new_doneAction(){
    	$rs='';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
            
            if ( !$this->check_data( $form_data[$this->table_name] ) ) {
		        $rs = $this->get_form($form_data[$this->table_name], 'new');
		        
		    } else {
		        $this->add_data($form_data[$this->table_name]);
		        $rs .= $this->grid();
		    }
    	return $rs;
    }
    
    protected function _load_doneAction(){
    	$rs='';
    	if ( $this->isDemo() ) {
			$rs = $this->demo_function_disabled();
			return $rs;
		}
		$csv_strings = $this->loadData();
		if ( !$csv_strings ) {
			$rs .= $this->getLoadForm();
		} else {
			$rs .= '<p>&nbsp;</p>';
			$rs .= $this->importData( $csv_strings );
			if ( $this->getError() ) {
				$rs .= $this->getLoadForm();
			}
		}
    	return $rs;
    }
    
    protected function _load_formAction(){
    	$rs='';
    	$rs .= $this->getLoadForm();
    	return $rs;
    }
    
    protected function _export_doneAction(){
    	$rs='';
    	if ( $this->isDemo() ) {
	        $rs = $this->demo_function_disabled();
	        return $rs;
	    }
		$ret='';
		$city_id=(int)$_REQUEST['city_id'];
		if($city_id!=0){
			if($this->getConfigValue('link_street_to_city')){
				$query='SELECT s.*, d.name AS district FROM '.DB_PREFIX.'_street s LEFT JOIN '.DB_PREFIX.'_district d ON s.district_id=d.id WHERE s.city_id='.(int)$_REQUEST['city_id'].' ORDER BY s.name';
			}else{
				$query='SELECT s.*, d.name AS district FROM '.DB_PREFIX.'_street s LEFT JOIN '.DB_PREFIX.'_district d ON s.district_id=d.id WHERE s.district_id IN (SELECT id FROM '.DB_PREFIX.'_district WHERE city_id='.(int)$_REQUEST['city_id'].') ORDER BY s.name';
			}
			
		}else{
			$query='SELECT s.*, d.name AS district FROM '.DB_PREFIX.'_street s LEFT JOIN '.DB_PREFIX.'_district d ON s.district_id=d.id ORDER BY s.name';
		}
		$this->db->exec($query);
		while($this->db->fetch_assoc()){
			if(isset($_REQUEST['need_districts'])){
				$ret.=$this->db->row['name'].';'.$this->db->row['district']."\r\n";
			}else{
				$ret.=$this->db->row['name']."\r\n";
			}
			
		}
		$f=fopen(SITEBILL_DOCUMENT_ROOT.'/street_export.csv','w');
		fwrite($f,$ret);
		fclose($f);
		$rs.=Multilanguage::_('L_MESSAGE_EXPORT_DONE').'<a href="/street_export.csv">street_export.csv</a>';
		return $rs;
    }
    
    protected function _exportAction(){
    	$rs='';
    	$rs.=$this->getExportForm();
    	return $rs;
    }
    
    protected function _deleteAction(){
    	$rs='';
    	$this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
        if ( $this->getError() ) {
	        $rs .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').': '.$this->GetErrorMessage().'<br>';
	        $rs .= '<a href="?action='.$this->action.'">ОК</a>';
	        $rs .= '</div>';
	    } else {
            $rs .= $this->grid();
	    }
    	return $rs;
    }
    
    protected function _editAction(){
    	$rs='';
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	 
    	$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
        $rs = $this->get_form($form_data[$this->table_name], 'edit');
    	return $rs;
    }
    
    protected function _edit_doneAction(){
    	$rs='';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	 
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
	    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
	        $rs = $this->get_form($form_data[$this->table_name], 'edit');
	    } else {
	        $this->edit_data($form_data[$this->table_name]);
	        $rs .= $this->grid();
	    }
    	return $rs;
    }
    
    /**
     * Import data
     * @param array $csv_strings csv string
     * @return boolean
     */
    function importData ( $csv_string ) {
        $record_number = 0;
        $error_number = 0;
        /*
        if ( $this->getRequestValue('clear') == 'yes' ) {
            $this->clearItems( $this->getRequestValue('topic_id') );
        }
        */
        $i = 0;
        foreach ( $csv_string as $key => $string ) {
            if ( trim($string) == '' ) {
                continue;
            }
            $items = explode(";", $string);
            $items_count = count($items);
            //print_r($items);
            //$items[0]=preg_replace('/[^-A-ZА-Яа-яa-z_0-9\s]/', '', $items[0]);
            if(trim($items[0]) == ''){
                continue;
            }
            
            if ( $this->getConfigValue('theme') == 'svobodendom' ) {
            	//echo '$items[0] = '.$items[0].', strlen = '.strlen($items[0]).'<br>';
            	if ( strlen($items[0]) < 4 ) {
            		continue;
            	}
            }
            
            
                $added = $this->addRecordValue(trim($items[0]));
                if ( $this->getError() ) {
                    return false;
                }
                if ( $added === true ) {
                    $record_number++;
                } else {
                    $error_number++;
                }
        }
        //$rs .= "Каждая строка по $items_count элементов<br>";
        $rs = sprintf(Multilanguage::_('L_MESSAGE_SUCCESSFULLY_UPLOADED_N_STRINGS'),$record_number);
        $rs .= Multilanguage::_('L_ERROR_RECORS_SKIPING_BY_ERRORS').' '.$error_number.'<br>';
        return $rs;
    }
    
    /**
     * Check data
     * @param void
     * @return boolean
     */
    function loadData () {
        if ( !is_uploaded_file($_FILES['csv']['tmp_name']) ) {
            $this->riseError(Multilanguage::_('L_ERROR_CANT_UPLOAD_FILE'));
            return false;
        }
        $content = file_get_contents($_FILES['csv']['tmp_name']);
        $this->csv_strings = explode("\n", $content);
        
        if ( !is_array($this->csv_strings) ) {
            $this->riseError(Multilanguage::_('L_ERROR_BAD_FILE_FORMAT'));
            return false;
        }
        
        return $this->csv_strings;
    }
    
    
    /**
     * Get load form
     * @param void
     * @return string
     */
    function getLoadForm () {
        global $debug_mode;
        
        $rs .= '<form method="post" action="index.php" name="rentform" enctype="multipart/form-data">';
        $rs .= '<table border="0">';
        
        $rs .= '<tr>';
        $rs .= '<td colspan="2"><p>&nbsp;</p><h1>'.Multilanguage::_('L_TEXT_LOAD_STREET_LIST').'</h1>'.Multilanguage::_('L_TEXT_LOAD_STREET_LIST_DESC').'<br><a href="'.SITEBILL_MAIN_URL.'/img/street.txt" target="_blank">'.Multilanguage::_('L_TEXT_LOAD_FILE_EXAMPLE').'</a>.<p>Внимание! Файл должен быть в <a href="http://www.sitebill.ru/format-faila-zagruzki-ulic" target="_blank">UTF-8 кодировке</a>.</p></td>';
        $rs .= '</tr>';
        
        if ( $this->GetError() ) {
            $rs .= '<tr>';
            $rs .= '<td></td>';
            $rs .= '<td><span class="error">'.$this->GetError().'</span></td>';
            $rs .= '</tr>';
        }
        
        if($this->getConfigValue('link_street_to_city')){
        	$rs .= '<tr>';
        	$rs .= '<td class="left_column">'.Multilanguage::_('L_TEXT_CITY').':</td>';
        	$rs .= '<td>'.$this->getCitySelectBox().'</td>';
        	$rs .= '</tr>';
        } else {
        	$rs .= '<tr>';
        	$rs .= '<td class="left_column">'.Multilanguage::_('L_TEXT_DISTRICT').':</td>';
        	$rs .= '<td>'.$this->getDistrictSelectBox().'</td>';
        	$rs .= '</tr>';
        }
        
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('L_TEXT_TEXTFILE').' <span class="error">*</span>:</td>';
        $rs .= '<td><input type="file" name="csv"></td>';
        $rs .= '</tr>';
        
        
        $rs .= '<tr>';
        $rs .= '<td></td>';
        $rs .= '<input type="hidden" name="action" value="street">';
        $rs .= '<input type="hidden" name="do" value="load_done">';
        
        $rs .= '<td><input type="submit" value="'.Multilanguage::_('L_TEXT_LOAD').'"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        
        return $rs;
    }
    /**
     * Add record
     * @param void
     * @return string
     */
    function add_record_and_get_id ( $name ) {
        
        $query = "insert into ".DB_PREFIX."_street (name) values ('$name')";
        $record_id = $this->db->exec($query);
        return $record_id;
    }
    
    /**
     * Add record by value
     * @param void
     * @return string
     */
    function addRecordValue ( $value ) {
    	$value=strip_tags($value);
        if($this->getRequestValue('district_id')!=0){
        	$query = "INSERT INTO ".DB_PREFIX."_street (name, district_id) VALUES ('".$value."',".(int)$this->getRequestValue('district_id').")";
        }elseif ($this->getRequestValue('city_id')!=0){
        	$query = "INSERT INTO ".DB_PREFIX."_street (name, city_id) VALUES ('".$value."',".(int)$this->getRequestValue('city_id').")";
        }else{
        	$query = "INSERT INTO ".DB_PREFIX."_street (name) VALUES ('".$value."')";
        }
        //$query = "insert into re_street (name) values ('$value')";
        //echo "$query<br>";
        $this->db->exec($query);
        return true;
    }
    
    
    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu () {
        $rs = '<a href="?action=street&do=new" class="btn btn-primary">'.Multilanguage::_('L_TEXT_ADD_STREET').'</a>';
        $rs .= ' <a href="?action=street&do=load_form" class="btn btn-primary">'.Multilanguage::_('L_TEXT_LOAD_STREETS_FROM_FILE').'</a>';
        $rs .= ' <a href="?action=street&do=export" class="btn btn-primary">'.Multilanguage::_('L_TEXT_EXPORT').'</a>';
        $rs.=$this->getAdditionalSearchForm();
        return $rs;
    }
    
	function getAdditionalSearchForm(){
		$ret='';
		if($this->getConfigValue('link_street_to_city')){
			$query='select * from '.DB_PREFIX.'_city order by name';
			$this->db->exec($query);
			$ret.='<form method="post" action="'.SITEBILL_MAIN_URL.'/admin/">';
			$ret.='<select name="city_id">';
			$ret.='<option value="">'.Multilanguage::_('L_TEXT_ANY_CITY').'</option>';
			while($this->db->fetch_assoc()){
				if($this->getRequestValue('city_id')==$this->db->row['city_id']){
					$ret.='<option value="'.$this->db->row['city_id'].'" selected="selected">'.$this->db->row['name'].'</option>';
				}else{
					$ret.='<option value="'.$this->db->row['city_id'].'">'.$this->db->row['name'].'</option>';
				}
				
			}
			$ret.='</select>';
		}else{
			$query='select * from '.DB_PREFIX.'_district order by name';
			$this->db->exec($query);
			$ret.='<form method="post">';
			$ret.='<select name="district_id">';
			$ret.='<option value="">'.Multilanguage::_('L_TEXT_ANY_DISTRICT').'</option>';
			while($this->db->fetch_assoc()){
				if($this->getRequestValue('district_id')==$this->db->row['id']){
					$ret.='<option value="'.$this->db->row['id'].'" selected="selected">'.$this->db->row['name'].'</option>';
				}else{
					$ret.='<option value="'.$this->db->row['id'].'">'.$this->db->row['name'].'</option>';
				}
				
			}
			$ret.='</select>';
		}
		
		
		
		$ret.='<input type="hidden" name="action" value="'.$this->action.'">';
		$ret .= '<input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SELECT').'">';
		$ret.='</form>';
		return $ret;
	}
	
	function getExportForm(){
		$ret='';
		$ret.='<h2>'.Multilanguage::_('L_TEXT_EXPORT_PARAMS').'</h2>';
		$ret.='<form method="post" action="index.php">';
		$ret.='<table>';
		$ret.='<tr><td>'.Multilanguage::_('L_TEXT_CITY').'</td><td>'.$this->getCitySelectBox().'</td></tr>';
		$ret.='<tr><td>'.Multilanguage::_('L_TEXT_ADD_DISTRICTS').'?</td><td><input type="checkbox" name="need_districts"></td></tr>';
		$ret.='<tr><td colspan="2"><input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_EXPORT').'"></td></tr>';
		$ret.='<input type="hidden" name="action" value="'.$this->action.'">';
		$ret.='<input type="hidden" name="do" value="export_done">';
		$ret .= '';
		$ret.='</table>';
		$ret.='</form>';
		return $ret;
	}
	
	function getCitySelectBox(){
		$ret='';
		$query='select * from '.DB_PREFIX.'_city order by name';
		$this->db->exec($query);
		$ret.='<select name="city_id">';
		$ret.='<option value="">'.Multilanguage::_('L_TEXT_ANY_CITY').'</option>';
		while($this->db->fetch_assoc()){
			$ret.='<option value="'.$this->db->row['city_id'].'">'.$this->db->row['name'].'</option>';
		}
		$ret.='</select>';
		return $ret;
	}
	
	function getDistrictSelectBox(){
		$ret='';
		$query='select * from '.DB_PREFIX.'_district order by name';
		$this->db->exec($query);
		$ret.='<select name="district_id">';
		$ret.='<option value="">'.Multilanguage::_('L_TEXT_ANY_DISTRICT').'</option>';
		while($this->db->fetch_assoc()){
			$ret.='<option value="'.$this->db->row['id'].'">'.$this->db->row['name'].'</option>';
		}
		$ret.='</select>';
		return $ret;
	}
	
	function grid () {
		
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_grid_table($this->table_name);
        
        $common_grid->add_grid_item($this->primary_key);
        $common_grid->add_grid_item('name');
        
        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
        
        
		if($this->getRequestValue('district_id')!=0){
			$common_grid->set_conditions(array('district_id'=>(int)$this->getRequestValue('district_id')));
			$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
			//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." WHERE district_id=".(int)$this->getRequestValue('district_id')." ORDER BY ".$this->primary_key." ASC");
		}elseif($this->getRequestValue('city_id')!=0){
			$common_grid->set_conditions(array('city_id'=>(int)$this->getRequestValue('city_id')));
			$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
			//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." WHERE city_id=".(int)$this->getRequestValue('city_id')." ORDER BY ".$this->primary_key." ASC");
		}else{
			$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
			//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY ".$this->primary_key." ASC");
		}
        
        $rs = $common_grid->construct_grid();
        return $rs;
    }
    
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$search_queries=array(
			Multilanguage::_('TABLE_ADS','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE street_id=?',
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