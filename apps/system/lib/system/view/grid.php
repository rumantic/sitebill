<?php
/**
 * Construct grid
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Common_Grid extends Sitebill {
    /**
     * Array with list of grid items
     * @var array
     */
    protected $grid_items = array();

    /**
     * Array with list of grid controls (edit/delete/structure/manual)
     * @var array
     */
    protected $grid_controls = array();
    
    /**
     * 
     * @var string
     */
    protected $grid_query;
    protected $action;
    protected $table_name;
    protected $conditions=array();
    
    /**
     * Grid object
     * @var object
     */
    protected $grid_object;
    
    protected $pager_params=array();
    
    function __construct ( $grid_object ) {
        $this->SiteBill();
        $this->grid_object = $grid_object;        
    }

    /**
     * Add grid item
     * @param string $name
     * @return void
     */
    function add_grid_item ( $name ) {
        array_push($this->grid_items, $name);
    }
    
    /**
     * Set action name
     */
    function set_action ( $action = '' ) {
    	$this->action = $action;
    }
    
    /**
     * Get action name
     */
    function get_action () {
    	return $this->action;
    }
    
    
    /**
     * Set table name
     */
    function set_table_name ( $table_name = '' ) {
    	$this->table_name = $table_name;
    }
    
    /**
     * Get table name
     */
    function get_table_name ( ) {
    	return $this->table_name;
    }
    
    
    
    /**
     * Add grid control
     * @param string $name
     * @return void
     */
    function add_grid_control ( $name ) {
        array_push($this->grid_controls, $name);
    }
    
    function set_grid_url ( $url ) {
    	$this->grid_url = $url;
    }
    
    /**
     * Set SQL-query for load records 
     * @param string $query
     * @return void
     */
    function set_grid_query ( $query ) {
    	//echo $query.'<br>';
        $this->grid_query = $query;
    }
    
    function set_grid_table ( $table ) {
    	$this->grid_table = $table;
    }
    
    function set_conditions($conditions){
    	$this->conditions = $conditions;
    }
    
    function construct_grid ( $control_params = false, $disable_mass_delete = false ) {
    	if($this->grid_query!=''){
    		return $this->_construct_grid($control_params, $disable_mass_delete);
    	}else{
    		
    		$pager_params=array();
    		$sort_params=array();
    		
    		$pager_params=$this->pager_params;
    		foreach($pager_params as $key=>$value){
    			if($key!='per_page'){
    				if($key=='page'){
    					//$sort_params[]='page=1';
    				}else{
    					$sort_params[]=$key.'='.$value;
    				}
    			}
    		}
    		$sort_params[]='page=1';
    		
    		
    		$sortby=$this->getRequestValue('_sortby');
    		if($sortby==''){
    			$sortby=$this->grid_object->primary_key;
    		}
    		$sortdir=$this->getRequestValue('_sortdir');
    		if($sortdir==''){
    			$sortdir='DESC';
    		}
    		$pager_params['_sortby']=$sortby;
    		$pager_params['_sortdir']=$sortdir;
    		
    		$where=array();
    		if(!empty($this->conditions)){
    			foreach($this->conditions as $key=>$value){
    				$where[]='(`'.$key.'`=\''.$value.'\')';
    				$sort_params[]=$key.'='.$value;
    			}
    			$pager_params=array_merge($pager_params, $this->conditions);
    		}
    		//$sort_params=array_merge($sort_params, $this->conditions);
    		$query='SELECT * FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '').' ORDER BY '.$sortby.' '.$sortdir.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    		
    		$this->db->exec($query);
    		if($this->db->num_rows()==0 && $this->current_page!=1){
    			$this->current_page=1;
    			$query='SELECT * FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '').' ORDER BY '.$sortby.' '.$sortdir.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    			$this->db->exec($query);
    		}
    		if ( !$this->db->success ) {
    			echo $this->db->error;
    			return false;
    		}
    		//$this->db->exec($this->grid_query);
    		while ( $this->db->fetch_assoc() ) {
    			$ra[$this->db->row[$this->grid_object->primary_key]] = $this->db->row;
    		}
    		
    		if(count($ra)>0){
    			$rs = '<table border="0" width="99%" class="table table-hover common-grid">';
    			$rs .= '<thead>';
    			$rs .= '<tr>';
    			if ( !$disable_mass_delete ) {
    				$rs .= '<th class="row_title" width="1%"><input type="checkbox" class="grid_check_all" /></td>';
    			}
    			//echo $sortby;
    			$sort_url=$this->grid_url;
    			if($sort_url==''){
    				$sort_url=SITEBILL_MAIN_URL.'/admin/index.php';
    			}
    			
    			foreach ( $this->grid_items as $item_id => $item_name ) {
    		
    				$rs .= '<th class="row_title"';
    				if ( $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key' ) {
    					$rs .= 'width="1%"';
    				}
    				//$sortby=$item_name;
    				//print_r($this->pager_params);
    				if($sortby==$item_name){
    					if(strtolower($sortdir)=='asc'){
    						$sortdirn='desc';
    						$sorted='common-grid-sorted-asc';
    					}else{
    						$sortdirn='asc';
    						$sorted='common-grid-sorted-desc';
    					}
    				}else{
    					$sortdirn='asc';
    					$sorted='';
    				}
    				$_sort=$sort_params;
    		
    				$_sort[]='_sortby='.$item_name;
    				$_sort[]='_sortdir='.$sortdirn;
    				$s='<a class="common-grid-sortable '.$sorted.'" href="'.$sort_url.'?'.implode('&', $_sort).'">'.$this->grid_object->data_model[$this->grid_object->table_name][$item_name]['title'].'</a>';
    		
    				$rs .= '>'.$s.'</th>';
    			}
    			if ( count($this->grid_controls) > 0 ) {
    				$rs .= '<th class="row_title" width="1%"></th>';
    			}
    			$rs .= '</tr>';
    			$rs .= '</thead>';
    			$rs .= '<tbody>';
    			
    			foreach ( $ra as $primary_key_value => $item_array ) {
    				$row_data = $this->grid_object->load_by_id($primary_key_value);
    				$rs .= '<tr class="row3">';
    				$grid_counter = 0;
    				if ( !$disable_mass_delete ) {
    					$grid_counter = 1;
    					$rs .= '<td><input type="checkbox" class="grid_check_one" value="'.$primary_key_value.'" /></td>';
    				}
    				
    				foreach ( $this->grid_items as $item_id => $item_name ) {
    					$grid_counter++;
    		
    					if($row_data[$item_name]['type']=='select_by_query'){
    						$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    					}elseif($row_data[$item_name]['type']=='structure'){
    						$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    					}elseif ($row_data[$item_name]['type']=='date') {
    						$rs .= '<td  >'.$row_data[$item_name]['value_string'].'</td>';
    					}elseif ($row_data[$item_name]['type']=='select_box') {
    						$rs .= '<td  >'.$row_data[$item_name]['select_data'][$row_data[$item_name]['value']].'</td>';
    					}elseif($row_data[$item_name]['type']=='checkbox'){
    						$rs .= '<td>'.($row_data[$item_name]['value']==1 ? '<img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/radio_yes.png">' : '<img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/radio_no.png">').'</td>';
    					} else {
    						if(is_array($row_data[$item_name]['value'])){
    							$rs .= '<td>'.implode(';',$row_data[$item_name]['value']).'</td>';
    						}else{
    							$rs .= '<td>'.$row_data[$item_name]['value'].'</td>';
    						}
    						 
    					}
    					 
    				}
    				if ( count($this->grid_controls) > 0 ) {
    					$rs .= '<td nowrap>';
    					 
    					foreach ( $this->grid_controls as $control_id => $control_name ) {
    						if ( $control_name == 'view' ) {
    							if ( !empty($control_params['view']) ) {
    								$control_params_edit_string = $control_params['view'];
    							}
    							$rs .= ' <a href="?action='.$this->grid_object->action.'&do=view&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_edit_string.'" class="btn btn-info"><i class="icon-white icon-info-sign"></i></a> ';
    						}
    						if ( $control_name == 'edit' ) {
    							if ( !empty($control_params['edit']) ) {
    								$control_params_edit_string = $control_params['edit'];
    							}
    							$rs .= ' <a href="?action='.$this->grid_object->action.'&do=edit&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_edit_string.'" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
    						}
    						if ( $control_name == 'delete' ) {
    							if ( !empty($control_params['delete']) ) {
    								$control_params_delete_string = $control_params['delete'];
    							}
    		
    							$rs .= ' <a href="?action='.$this->grid_object->action.'&do=delete&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_delete_string.'" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
    						}
    					}
    					$rs .= '</td>';
    				}
    				 
    				$rs .= '</tr>';
    			}
    			if ( !$disable_mass_delete ) {
    				$rs .= '<tr><td colspan="'.(count($this->grid_controls)+$grid_counter).'"><button alt="'.$this->grid_object->action.'" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> '.Multilanguage::_('L_DELETE_CHECKED').'</button></td></tr>';
    			}
    			$rs .= '<tr><td colspan="'.(count($this->grid_controls)+$grid_counter).'" class="pager"><div align="center">';
    			
    			$query='SELECT COUNT('.$this->grid_object->primary_key.') AS _cnt FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '');
    			
    			$this->db->exec($query);
    			$this->db->fetch_assoc();
    			
    			$total=$this->db->row['_cnt'];
    			//echo $this->get_page_links_list($this->current_page, $total, $this->per_page, $this->pager_params);
    			$rs.=$this->get_page_links_list($this->current_page, $total, $this->per_page, $pager_params);
    			
    			$rs.='</div></td></tr>';
    			$rs .= '</tbody>';
    		
    			$rs .= '</table>';
    		}else{
    			$rs.='<br><br>Записей не найдено';
    		}
    		
    		return $rs;
    	}
    	
    }
    
    private function _construct_grid($control_params = false, $disable_mass_delete = false){
    	$ra = array();
    	$query=$this->grid_query.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    	$this->db->exec($query);
    	if($this->db->num_rows()==0 && $this->current_page!=1){
    		$this->current_page=1;
    		$query=$this->grid_query.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    		$this->db->exec($query);
    	}
    	if ( !$this->db->success ) {
    		echo $this->db->error;
    		return false;
    	}
    	//$this->db->exec($this->grid_query);
    	while ( $this->db->fetch_assoc() ) {
    		$ra[$this->db->row[$this->grid_object->primary_key]] = $this->db->row;
    	}
    	if(count($ra)>0){
    		$rs = '<table border="0" width="99%" class="table table-hover">';
    		$rs .= '<thead>';
    		$rs .= '<tr>';
    		if ( !$disable_mass_delete ) {
    			$rs .= '<th class="row_title" width="1%"><input type="checkbox" class="grid_check_all" /></td>';
    		}
    		foreach ( $this->grid_items as $item_id => $item_name ) {
    			
    			$rs .= '<th class="row_title"';
    			if ( $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key' ) {
    				$rs .= 'width="1%"';
    			}
    			$rs .= '>'.$this->grid_object->data_model[$this->grid_object->table_name][$item_name]['title'].'</th>';
    		}
    		if ( count($this->grid_controls) > 0 ) {
    			$rs .= '<th class="row_title" width="1%"></th>';
    		}
    		$rs .= '</tr>';
    		$rs .= '</thead>';
    		$rs .= '<tbody>';
    		foreach ( $ra as $primary_key_value => $item_array ) {
    			$row_data = $this->grid_object->load_by_id($primary_key_value);
    			$rs .= '<tr class="row3">';
    			if ( !$disable_mass_delete ) {
    				$rs .= '<td><input type="checkbox" class="grid_check_one" value="'.$primary_key_value.'" /></td>';
    			}
    			$grid_counter = 0;
    			foreach ( $this->grid_items as $item_id => $item_name ) {
    				$grid_counter++;
    	
    				if($row_data[$item_name]['type']=='select_by_query'){
    					$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    				}elseif($row_data[$item_name]['type']=='structure'){
    					$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    				}elseif ($row_data[$item_name]['type']=='date') {
    					$rs .= '<td  >'.$row_data[$item_name]['value_string'].'</td>';
    				}elseif ($row_data[$item_name]['type']=='select_box') {
    					$rs .= '<td  >'.$row_data[$item_name]['select_data'][$row_data[$item_name]['value']].'</td>';
    				}elseif($row_data[$item_name]['type']=='checkbox'){
    					$rs .= '<td>'.($row_data[$item_name]['value']==1 ? '<img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/radio_yes.png">' : '<img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/radio_no.png">').'</td>';
    				} else {
    					if(is_array($row_data[$item_name]['value'])){
    						$rs .= '<td>'.implode(';',$row_data[$item_name]['value']).'</td>';
    					}else{
    						$rs .= '<td>'.$row_data[$item_name]['value'].'</td>';
    					}
    					 
    				}
    				 
    			}
    			if ( count($this->grid_controls) > 0 ) {
    				$rs .= '<td nowrap>';
    				 
    				foreach ( $this->grid_controls as $control_id => $control_name ) {
    					if ( $control_name == 'view' ) {
    						if ( !empty($control_params['view']) ) {
    							$control_params_edit_string = $control_params['view'];
    						}
    						$rs .= ' <a href="?action='.$this->grid_object->action.'&do=view&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_edit_string.'" class="btn btn-info"><i class="icon-white icon-info-sign"></i></a> ';
    					}
    					if ( $control_name == 'edit' ) {
    						if ( !empty($control_params['edit']) ) {
    							$control_params_edit_string = $control_params['edit'];
    						}
    						$rs .= ' <a href="?action='.$this->grid_object->action.'&do=edit&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_edit_string.'" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
    					}
    					if ( $control_name == 'delete' ) {
    						if ( !empty($control_params['delete']) ) {
    							$control_params_delete_string = $control_params['delete'];
    						}
    	
    						$rs .= ' <a href="?action='.$this->grid_object->action.'&do=delete&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_delete_string.'" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
    					}
    				}
    				$rs .= '</td>';
    			}
    			 
    			$rs .= '</tr>';
    		}
    		if ( !$disable_mass_delete ) {
    			$rs .= '<tr><td colspan="'.(count($this->grid_controls)+$grid_counter).'"><button alt="'.$this->grid_object->action.'" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> '.Multilanguage::_('L_DELETE_CHECKED').'</button></td></tr>';
    		}
    		$rs .= '<tr><td colspan="'.(count($this->grid_controls)+$grid_counter).'" class="pager"><div align="center">'.$this->getPager().'</div></td></tr>';
    	
    		$rs .= '</tbody>';
    	
    		$rs .= '</table>';
    	}else{
    		$rs.='<br><br>Записей не найдено';
    	}
    	return $rs;
    }
    
    function construct_grid_array () {
    	$ra = array();
    
    	$query=$this->grid_query.' LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page;
    	$this->db->exec($query);
    
    	while ( $this->db->fetch_assoc() ) {
    		$ra[$this->db->row[$this->grid_object->primary_key]] = $this->db->row;
    	}
    
    	$ret=array();
    	//
    	foreach ( $ra as $primary_key_value => $item_array ) {
    		$row_data = $this->grid_object->load_by_id($primary_key_value);
    		//
    		$data=array();
    		foreach ( $this->grid_items as $item_id => $item_name ) {
    			//echo 'type = '.$row_data[$item_name]['type'].', name = '.$row_data[$item_name]['name'].'<br>';
    			if($row_data[$item_name]['type']=='select_by_query'){
    				$data[$item_name]['value']=$row_data[$item_name]['value'];
    				$data[$item_name]['value_string']=$row_data[$item_name]['value_string'];
    			}elseif ($row_data[$item_name]['type']=='date') {
    				$data[$item_name]= $row_data[$item_name]['value'];
    				//$data[$item_name]=date('d.m.Y', $row_data[$item_name]['value']);
    			}elseif ($row_data[$item_name]['type']=='uploadify_image') {
    				$data['image_array'] = $this->get_image_array($this->get_action(), $this->get_table_name(), $this->grid_object->primary_key, (int)$primary_key_value);
    				//$data[$item_name]=date('d.m.Y', $row_data[$item_name]['value']);
    			}elseif ($row_data[$item_name]['type']=='select_box') {
    				$data[$item_name]=$row_data[$item_name]['select_data'][$row_data[$item_name]['value']];
    			} elseif ($row_data[$item_name]['type']=='geodata'){
    				$data[$item_name]=implode(',',$row_data[$item_name]['value']);
    			}else {
    				$data[$item_name]=$row_data[$item_name]['value'];
    			}
    			 
    
    		}
    		$ret[]=$data;
    	}
    	return $ret;
    }
    
    
    function getPager(){
    	$this->db->exec($this->grid_query);
    	$total=$this->db->num_rows();
    	$ret=$this->get_page_links_list($this->current_page, $total, $this->per_page, $this->pager_params);
    	return $ret;
    }
    
    
    
    function setPagerParams($params=array()){
    	if(isset($params['per_page']) AND ($params['per_page']!=0)){
    		$this->per_page=(int)$params['per_page'];
    	}else{
    		$this->per_page=10;
    	}
    	
    	if(isset($params['page']) AND ($params['page']!=0)){
    		$this->current_page=(int)$params['page'];
    	}else{
    		$this->current_page=1;
    	}
    	
    	unset($params['per_page']);
    	unset($params['page']);
    	
    	$this->pager_params=$params;
    	
    }
    
	
    
} 
?>
