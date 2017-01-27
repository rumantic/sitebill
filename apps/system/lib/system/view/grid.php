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
    protected $controls_params=array();
    
    /**
     * Grid object
     * @var object
     */
    protected $grid_object;
    
    protected $pager_params=array();
    /*
     * Идентификатор пользователя для которого запущен рендеринг грида
     * По-умолчанию false - т.е. для любого, проверки прав доступа не выполняется
     */
    protected $render_user_id=false;
    
    function __construct ( $grid_object ) {
        $this->SiteBill();
        $this->grid_object = $grid_object;     
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$this->data_model_controller = new Data_Model();
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
    
    function add_control_param ( $name, $value ) {
    	$this->controls_params[$name]=$value;
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
    
    function extended_items () {
    	//echo $this->get_action();
    	$this->template->assign('action', $this->get_action());
    	return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/template/grid/extended_items_row.tpl');
    	//return 'extended items';
    }
    
    function get_grid_query () {
    	return $this->grid_query;
    }
    
    function construct_query () {
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
		
        $tagged_params = $this->add_tags_params();
        $where = $this->add_tagged_parms_to_where($where, $tagged_params, $this->grid_object->table_name);
		
    	//$sort_params=array_merge($sort_params, $this->conditions);
    	$query='SELECT * FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '').' ORDER BY '.$sortby.' '.$sortdir.' ';
	//echo $query;
    	$this->set_grid_query($query);
    }
    
    function parse_id_values_from_model ( $column_name, $column_values, $data_model ) {
    	if ( $data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_by_query' ) {
    		foreach ( $column_values as $idx => $value ) {
    			$val=$this->data_model_controller->get_value_id_by_name($data_model[$this->grid_object->table_name][$column_name]['primary_key_table'], $data_model[$this->grid_object->table_name][$column_name]['value_name'], $data_model[$this->grid_object->table_name][$column_name]['primary_key_name'], $value);
    			
    			if(0!=(int)$val){
    				$column_values[$idx] = $val;
    			}else{
    				unset($column_values[$idx]);
    			}
    			
    		}
    	} elseif ( $data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_box' and count($column_values) > 0 ) {
    		$select_data = array_flip($data_model[$this->grid_object->table_name][$column_name]['select_data']);
    		$ra = array();
    		foreach ( $column_values as $idx => $value ) {
    			if ( $select_data[$value] ) {
    				$ra[] = $select_data[$value];
    			}
    		}
    		return $ra;
    	} elseif ( $data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_box_structure' and count($column_values) > 0 ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure=new Structure_Manager();
    		$x=$Structure->createCatalogChains();
    		$categoryChain=$x['txt'];
    		$categoryChainRev=array_flip($categoryChain);
    		foreach ( $column_values as $idx => $value ) {
    			$value_array = explode(' / ', $value);
    			$var = implode("|", $value_array);
    			$var = mb_strtolower($var);
    			if(isset($categoryChainRev[$var])) {
    				$column_values[$idx] = $categoryChainRev[$var];  
    			} else {
    				unset($column_values[$idx]);
    			}    			
    		}
    	}
    	return $column_values;
    }
    
    
    function add_tags_params ( $params = array() ) {
    	
    	if ( is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array']) ) {
    		foreach ( $_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'] as $column_name => $column_values ) {
    			$column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->grid_object->data_model); 
    			if ( isset($params[$column_name]) and !is_array($params[$column_name]) ) {
    				if ( $params[$column_name] != 0 ) {
    					array_push($column_values, $params[$column_name]);
    				}
    				$params[$column_name] = $column_values;
    			} elseif (isset($params[$column_name]) and is_array($params[$column_name]) ) {
    				$params[$column_name] = array_merge($params[$column_name], $column_values);
    				
    			} elseif ( is_array($column_values) ) {
    				$params[$column_name] = $column_values;
    			}
    		}
    	}
    	return $params;
    }
    
    function add_tagged_parms_to_where($where_array, $tagged_params, $table_name) {
    	foreach ( $tagged_params as $column_name => $column_values ) {
    		if ( is_array($column_values) && count($column_values)>0 ) {
    			//$column_values=array_filter($column_values, function($a){if($a!=''){return $a;}});
    			if(!empty($column_values)){
			    $type = $this->grid_object->data_model[$table_name][$column_name]['type'];
    				if(isset($column_values['min']) || isset($column_values['max'])){
    					if(isset($column_values['min'])){
    						$where_array[]="(".DB_PREFIX."_".$table_name.".".$column_name." >= '".$column_values['min']."')";
    					}
    					if(isset($column_values['max'])){
    						$where_array[]="(".DB_PREFIX."_".$table_name.".".$column_name." <= '".$column_values['max']."')";
    					}
				} elseif  ($type == 'client_id') {
				    $where_fio_phone_array = array();
				    foreach ( $column_values as $fio_phone ) {
					list($fio, $phone) = explode(',',$fio_phone);
					$fio = trim($fio);
					$phone = trim($phone);
					$where_fio_phone_array[] = " client_id in (select client_id from ".DB_PREFIX."_client where fio='$fio' and phone='$phone') ";
				    }
				    
    					$where_array[]= implode(' or ', $where_fio_phone_array);
    				}else{
    					$where_array[]="(".DB_PREFIX."_".$table_name.".".$column_name." IN ('".implode('\',\'', $column_values)."'))";
    				}
    				
    			}
    			
    		}
    	}
    	return $where_array;
    }
    
    function set_render_user_id( $user_id ) {
        $this->render_user_id = $user_id;
    }
    
    function get_render_user_id() {
        return $this->render_user_id;
    }
    
    
    
    function construct_grid ( $control_params = false, $disable_mass_delete = false ) {
    	if($this->grid_query!=''){
    		return $this->_construct_grid($control_params, $disable_mass_delete);
    	}else{
    		$DBC=DBC::getInstance();
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
                //unset($_SESSION['tags_array']);
                //unset($_SESSION['model_tags']);
                //echo '<pre>';
                //print_r($_SESSION);
                //print_r($this->conditions);
                //echo '</pre>';
                
    		if(!empty($this->conditions)){
    			foreach($this->conditions as $key=>$value){
			    if ( is_array($value) ) {
    				$where[]='(`'.$key.'` in ('.implode(', ',$value).'))';
			    } else {
    				$where[]='(`'.$key.'`=\''.$value.'\')';
    				$sort_params[]=$key.'='.$value;
			    }
    			}
    			$pager_params=array_merge($pager_params, $this->conditions);
    		}
                $tagged_params = $this->add_tags_params();
                $where = $this->add_tagged_parms_to_where($where, $tagged_params, $this->grid_object->table_name);

                //echo '<pre>';
                //print_r($where);
                //print_r($_SESSION);
                //print_r($this->conditions);
                //echo '</pre>';
                
    		//$sort_params=array_merge($sort_params, $this->conditions);
    		$query_no_limit = 'SELECT * FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '').' ORDER BY '.$sortby.' '.$sortdir.' '; 
    		$query='SELECT * FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '').' ORDER BY '.$sortby.' '.$sortdir.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    		$this->set_grid_query($query_no_limit);
    		$stmt=$DBC->query($query);
    		if(!$stmt && $this->current_page!=1){
    			$this->current_page=1;
    			$query='SELECT * FROM '.DB_PREFIX.'_'.$this->grid_object->table_name.(!empty($where) ? ' WHERE '.implode('AND', $where) : '').' ORDER BY '.$sortby.' '.$sortdir.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    			$stmt=$DBC->query($query);
    		}
    		$empty_data = false;
    		if(!$stmt){
    			//return false;
				$empty_data = true;
    		}
    		
			if ( !$empty_data ) {
	    		while($ar=$DBC->fetch($stmt)){
					$ra[$ar[$this->grid_object->primary_key]] = $ar;
				}
			} else {
				$ra = array();
			}
			
			//Отсюда начинаем формировать таблицу со всеми подключаемыми плагинами
			$rs = '';
			
			if (is_array($this->grid_controls) ) {
			    if ( in_array('memorylist', $this->grid_controls)  ) {
				$rs .= $this->get_memory_header();
			    }
			}
    		
    			$rs .= '
<link rel="stylesheet" href="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template1/assets/css/colorbox.css" />
<script src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template1/assets/js/jquery.colorbox-min.js"></script>
					
<script type="text/javascript">
var fast_previews=[];
var column_values_for_tags = [];
var datastr={};
function setColorboxWrapper(id){
	var $overflow = \'\';
	var colorbox_params = {
		rel: \'colorbox\'+id,
		reposition:true,
		scalePhotos:true,
		scrolling:false,
		previous:\'<i class="ace-icon fa fa-arrow-left"></i>\',
		next:\'<i class="ace-icon fa fa-arrow-right"></i>\',
		close:\'&times;\',
		current:\'{current} of {total}\',
		maxWidth:\'100%\',
		maxHeight:\'100%\',
		onOpen:function(){
			$overflow = document.body.style.overflow;
			document.body.style.overflow = \'hidden\';
		},
		onClosed:function(){
			document.body.style.overflow = $overflow;
		},
		onComplete:function(){
			$.colorbox.resize();
		}
	};

	$(\'.ace-thumbnails [data-rel="colorbox\'+id+\'"]\').colorbox(colorbox_params);
}

$(document).ready(function(){
	
	$(\'.colorboxed\').each(function(item){
		setColorboxWrapper($(this).data(\'cbxid\'));
	});
		
		$(\'.ranged-tags\').each(function(e){
			var _this=$(this);
			var name=_this.data(\'field\');
			_this.find(\'.ranged-tags-title\').click(function(e){
				e.preventDefault();
				_this.find(\'.ranged-tags-params\').fadeToggle();
			});
			_this.find(\'.cancel\').click(function(e){
				e.preventDefault();
				_this.find(\'.ranged-tags-params\').fadeToggle();
			});
			var min=null;
			var max=null;
			var txt=\'не задано\';
			
			_this.find(\'input\').each(function(e){
				var iname=$(this).attr(\'name\');
				var val=$(this).val();
				var tag_array = {};
				
				
				var reg=/(.*)\[(.*)\]/;
				var matches=$(this).attr(\'name\').match(reg);
				if(typeof datastr[name] != \'undefined\'){
					tag_array=datastr[name];
				}
				if(val!=\'\'){
					tag_array[matches[2]]=val;
				}else{
					delete tag_array[matches[2]];
				}
				datastr[name] = tag_array;
				if(iname==name+\'[min]\' && val!=\'\'){
					min=val;
				}
				if(iname==name+\'[max]\' && val!=\'\'){
					max=val;
				}
				
				
				
			});
			
			if(min !== null && max !== null){
				var txt=min+\' - \'+max;
			}else if(min !== null){
				var txt=\'от \'+min;
			}else if(max !== null){
				var txt=\'до \'+max;
			}
			_this.find(\'.ranged-tags-title\').html(txt);
					
			_this.find(\'.apply\').click(function(e){
				e.preventDefault();
				var tag_array = {};
				var reg=/(.*)\[(.*)\]/;
				if(typeof datastr[name] != \'undefined\'){
					tag_array=datastr[name];
				}
				_this.find(\'input\').each(function(){
					var val=$(this).val();
					var matches=$(this).attr(\'name\').match(reg);
					if(typeof datastr[name] != \'undefined\'){
						tag_array=datastr[name];
					}
					if(val!=\'\'){
						tag_array[matches[2]]=val;
					}else{
						delete tag_array[matches[2]];
					}
					
					datastr[name] = tag_array;
				});
				$.ajax({url: \''.SITEBILL_MAIN_URL.'/js/ajax.php?action=get_tags&do=set&model_name='.$this->grid_object->table_name.'&tags_array=\'+JSON.stringify(datastr)}).done(function(result_items){location.reload();});
			});
			
			_this.find(\'.clear\').click(function(e){
				e.preventDefault();
				if(typeof datastr[name] != \'undefined\'){
					tag_array=datastr[name];
					delete datastr[name];
				}
				$.ajax({url: \''.SITEBILL_MAIN_URL.'/js/ajax.php?action=get_tags&do=set&model_name='.$this->grid_object->table_name.'&tags_array=\'+JSON.stringify(datastr)}).done(function(result_items){location.reload();});
			});
			
		});
});


</script>                            
                                <table class="table table-striped table-hover dataTable">';
    			$rs .= '<thead>';
    			$rs .= '<tr>';
    			if ( !$disable_mass_delete ) {
    				$rs .= '<th width="1%"><input type="checkbox" class="grid_check_all" /></td>';
    			}
    			//echo $sortby;
    			$sort_url=$this->grid_url;
    			if($sort_url==''){
    				$sort_url=SITEBILL_MAIN_URL.'/admin/index.php';
    			}
    			
    			foreach($this->grid_items as $item_id => $item_name){
    				if(!isset($this->grid_object->data_model[$this->grid_object->table_name][$item_name])){
    					unset($this->grid_items[$item_id]);
    				}
    			}
    			
    			foreach ( $this->grid_items as $item_id => $item_name ) {
    				
    					$rs .= '<th ';
    					if ( $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key' ) {
    						$rs .= 'width="1%"';
    					}
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
    					
    					if($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type']=='price'){
    						$tags_input = '<div class="ranged-tags" data-field="'.$item_name.'">
    						<div class="ranged-tags-title"></div>
    						<div class="ranged-tags-params" style="display: none;">
    						<input name="'.$item_name.'[min]" type="text" class="tagged_input" value="'.$_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]['min'].'">
    						<input name="'.$item_name.'[max]" type="text" class="tagged_input" value="'.$_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]['max'].'">
    						<a href="#" class="btn btn-danger clear" title="очистить фильтр"><i class="icon-remove"></i></a>
    						<a href="#" class="btn btn-success apply" title="применить фильтр"><i class="icon-ok"></i></a>
    						<a href="#" class="btn cancel" title="скрыть окно фильтра"><i class="icon-off"></i></a>
    						</div>
    						</div>';
    						$rs .= '>'.$s.$tags_input.'</th>';
    					}else{
    						$tags_input = '
			<div class="inline-tags">
				<input type="text" name="'.$item_name.'" id="'.$item_name.'" class="input-tag tagged" value="" placeholder="..." />
			</div>
                                        ';
    						$tags_input .= "
			<script type=\"text/javascript\">
			$(document).ready(function(){
				var tag_input = $('#".$item_name."');
				var tag_array = [];
				try{
				   tag_input.tag({
				      placeholder: tag_input.attr('placeholder'),
				      source: function(query, process) {
				    	  column_name = tag_input.attr('name');
							$.ajax({
								url: estate_folder+'/js/ajax.php?action=get_tags&column_name='+column_name+'&model_name=".$this->grid_object->table_name."'
				        	}).done(function(result_items){
								process(result_items);
							});
						}
				   });
					var tag_obj = tag_input.data('tag');";
    							
    							
    						if ( is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]) ) {
    							foreach ( $_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name] as $tag_item ) {
    								$tags_input .= 'tag_obj.add("'.$tag_item.'");
                                            tag_array.push("'.$tag_item.'");
                                            datastr["'.$item_name.'"] = tag_array;';
    									
    							}
    						}
    						$tags_input .= "
				}
				catch(e) {
				   //display a textarea for old IE, because it doesn't support this plugin or another one I tried!
				   tag_input.after('<textarea id=\"'+tag_input.attr('id')+'\" name=\"'+tag_input.attr('name')+'\" rows=\"3\">'+tag_input.val()+'</textarea>').remove();
				}
				tag_input.on('added', function (e, value) {
					tag_array.push(value);
			   		datastr[$(this).attr('name')] = tag_array;
			        $.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&model_name=".$this->grid_object->table_name."&do=set&tags_array='+JSON.stringify(datastr)})
			        .done(function(result_items){
			        	location.reload();
			           //process(result_items);
			        });
				})
				tag_input.on('removed', function (e, value) {
			   		var item_index = datastr[$(this).attr('name')].indexOf(value);
			   		datastr[$(this).attr('name')].splice(item_index, 1);
			        $.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&model_name=".$this->grid_object->table_name."&do=set&tags_array='+JSON.stringify(datastr)})
			        .done(function(result_items){
			        	location.reload();
			           //process(result_items);
			        });
				})
    						
    	
			});
    						
			</script>
    	
    	
                                        ";
    							
    						$rs .= '>'.$s.$tags_input.'</th>';
    					}
    					

    					}
    			
    				
    			if ( count($this->grid_controls) > 0 ) {
    				$rs .= '<th  width="1%"></th>';
    			}
    			$rs .= '</tr>';
    			$rs .= '</thead>';
    			$rs .= '<tbody>';
	    		if(count($ra)>0){
    			foreach ( $ra as $primary_key_value => $item_array ) {
    				$row_data = $this->grid_object->load_by_id($primary_key_value);
    				
    				$rs .= '<tr >';
    				$grid_counter = 0;
    				if ( !$disable_mass_delete ) {
    					$grid_counter = 1;
    					$rs .= '<td><input type="checkbox" class="grid_check_one" value="'.$primary_key_value.'" /></td>';
    				}
    				
    				foreach ( $this->grid_items as $item_id => $item_name ) {
    					$grid_counter++;
    					
    					/*if($row_data[$item_name]['name']=='name'){
    						$a=' class="editable_name_field" data-key="'.$this->grid_object->primary_key.'" data-fid="'.$primary_key_value.'" data-tbl="'.$this->grid_object->action.'"';
    					}else{
    						$a='';
    					}*/
						$a='';
    		
    					if($row_data[$item_name]['type']=='select_by_query'){
    						$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    					}elseif($row_data[$item_name]['type']=='structure'){
    						$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    					}elseif($row_data[$item_name]['type']=='select_box_structure'){
    						$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';
    					}elseif ($row_data[$item_name]['type']=='date') {
    						$rs .= '<td  >'.$row_data[$item_name]['value_string'].'</td>';
                                        }elseif($row_data[$item_name]['type']=='client_id'){
    						$rs .= '<td>'.$row_data[$item_name]['value_string'].'</td>';                                                
    					}elseif ($row_data[$item_name]['type']=='select_box') {
    						$rs .= '<td  >'.$row_data[$item_name]['select_data'][$row_data[$item_name]['value']].'</td>';
    					}elseif($row_data[$item_name]['type']=='photo'){
							if ( $row_data[$item_name]['value'] != '' ) {
	    						$rs .= '<td><img width="100" src="/img/data/user/'.$row_data[$item_name]['value'].'"></td>';
							} else {
	    						$rs .= '<td></td>';
							}
    					}elseif($row_data[$item_name]['type']=='checkbox'){
    						$rs .= '<td>'.($row_data[$item_name]['value']==1 ? '<img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/radio_yes.png">' : '<img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/radio_no.png">').'</td>';
    					}elseif($row_data[$item_name]['type']=='uploads'){
							$rs .= '<td>';
							if(count($row_data[$item_name]['value'])>0 and is_array($row_data[$item_name]['value'])){
								$rs .= '<ul class="ace-thumbnails clearfix">';
								$counter = 0;
								foreach($row_data[$item_name]['value'] as $vv){
									if ( $counter == 0 ) {
										$rs .= '<li><img src="'.SITEBILL_MAIN_URL.'/img/data/'.$vv['preview'].'" style="width: 40px; height: 40px;">
					<div class="tags">
						<span class="label-holder">
							<span class="label label-info">'.count($row_data[$item_name]['value']).'</span>
						</span>
					</div>
					<div class="tools tools-top">
						<a href="'.SITEBILL_MAIN_URL.'/img/data/'.$vv['normal'].'"  data-rel="colorbox'.$primary_key_value.$item_name.'" class="colorboxed" data-cbxid="'.$primary_key_value.$item_name.'">
							<i class="ace-icon fa fa-search-plus"></i>
						</a>
					</div>
											
											</li>
';
									} else {
										$rs .= '
				<li style="display: none;">
					<a href="'.SITEBILL_MAIN_URL.'/img/data/'.$vv['normal'].'"  data-rel="colorbox'.$primary_key_value.$item_name.'"><img src="'.SITEBILL_MAIN_URL.'/img/data/'.$vv['preview'].'" width="50" /></a>
				</li>
											
											';
										
									}
									$counter++;

									
								}
								$rs .= '</ul>';
							}
    						$rs .= '</td>';
    					}else {
    						if(is_array($row_data[$item_name]['value'])){
    							$rs .= '<td>'.implode(';',$row_data[$item_name]['value']).'</td>';
    						}else{
    							$rs .= '<td'.$a.'>'.$row_data[$item_name]['value'].'</td>';
    						}
    						 
    					}
    					 
    				}
    				
    				if ( count($this->grid_controls) > 0 ) {
    					$rs .= '<td nowrap>';
    					foreach ( $this->grid_controls as $control_id => $control_name ) {
                                            if(is_array($control_name)){
                                                $check_control_name = $control_name['name'];
                                            } else {
                                                $check_control_name = $control_name;
                                            }
					    if ( $control_name == 'memorylist' ) {
					        $rs .= $this->compile_memory_control($primary_key_value);
					    }
					    
                                            if ( !$this->check_access($this->grid_object->action, $this->get_render_user_id(), $check_control_name, $this->grid_object->primary_key, $primary_key_value ) ) {
                                                continue;
                                            }
    						if ( $control_name == 'view' ) {
    							$control_params_view_string='';
    							if ( !empty($control_params['view']) ) {
    								$control_params_view_string = $control_params['view'];
    							}
    							/*if(!empty($this->controls_params)){
    								$control_params_view_string.='&'.http_build_query($this->controls_params);
    							}*/
    							$rs .= ' <a href="?action='.$this->grid_object->action.'&do=view&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_view_string.'" class="btn btn-info"><i class="icon-white icon-info-sign"></i></a> ';
    						}elseif ( $control_name == 'edit') {
    							$control_params_edit_string='';
    							if ( !empty($control_params['edit']) ) {
    								$control_params_edit_string = $control_params['edit'];
    							}
    							/*if(!empty($this->controls_params)){
    								$control_params_edit_string.='&'.http_build_query($this->controls_params);
    							}*/
    							$rs .= ' <a href="?action='.$this->grid_object->action.'&do=edit&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_edit_string.'" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
    						}elseif ( $control_name == 'delete' ) {
    							$control_params_delete_string='';
    							if ( !empty($control_params['delete']) ) {
    								$control_params_delete_string = $control_params['delete'];
    							}
    							/*if(!empty($this->controls_params)){
    								$control_params_delete_string.='&'.http_build_query($this->controls_params);
    							}*/
    							$rs .= ' <a href="?action='.$this->grid_object->action.'&do=delete&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_delete_string.'" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
    						}else{
    							$control_params_oth_string='';
    							
    							/*if(!empty($this->controls_params)){
    								$control_params_oth_string.='&'.http_build_query($this->controls_params);
    							}*/
    							if(is_array($control_name)){
    								$rs .= ' <a href="?action='.$this->grid_object->action.'&do='.$control_name['name'].'&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_oth_string.'" class="btn '.($control_name['btnclass']!='' ? $control_name['btnclass'] : 'btn btn-warning').'"><i class="icon-white '.($control_name['btnicon']!='' ? $control_name['btnicon'] : 'icon-tasks').'"></i>'.($control_name['btntext']!='' ? ' '.$control_name['btntext'] : '').'</a> ';
    							}else{
    								$rs .= ' <a href="?action='.$this->grid_object->action.'&do='.$control_name.'&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_oth_string.'" class="btn btn-warning"><i class="icon-white icon-tasks"></i></a> ';
    							}
    							
    						}
    						if ( is_array( $control_name ) ) {
    							foreach ( $control_name as $custom_control_name => $custom_control_string ) {
    								$custom_code = str_replace('{primary_key_value}', $primary_key_value, $custom_control_string);
    								$rs .= $custom_code.' ';
    							}
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
    			$stmt=$DBC->query($query);
    			if($stmt){
    				$ar=$DBC->fetch($stmt);
    				$total=$ar['_cnt'];
    			}else{
    				$total=0;
    			}
    			$rs.=$this->get_page_links_list($this->current_page, $total, $this->per_page, $pager_params);
    			
    			$rs.='</div></td></tr>';
					
				} else {
					$rs .= '<tr>';
	    			$rs.='<td colspan="'.(count($this->grid_controls)+count($this->grid_items)).'"><p align="center" class="alert">Ничего не найдено</p></td>';
					$rs .= '</tr>';
					
				}
    			
    			$rs .= '</tbody>';
    		
    			$rs .= '</table>';
    			$rs .= '';
    		
    		return $rs;
    	}
    	
    }
    
    private function compile_memory_control( $id ) {
	$this->template->assign('id', $id);
	return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/admin/template/memorylist_item_control.tpl');
    }
    
    private function get_memory_header () {
	require_once SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/admin/memory_list.php';
	$ML=new Memory_List();
	$memory_lists=$ML->getUserMemoryLists($this->get_render_user_id());
	foreach($memory_lists as $ml){
		if(isset($ml['items']) && count($ml['items'])>0){
			foreach($ml['items'] as $item){
				$items_in_memory[$item['id']][]=$ml;
			}
		}
	}
	$this->template->assign('items_in_memory', $items_in_memory);
	return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/admin/template/memorylist_header.tpl');
    }
    
    private function _construct_grid($control_params = false, $disable_mass_delete = false){
    	$ra = array();
    	$query=$this->grid_query.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    	
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if(!$stmt && $this->current_page!=1){
    		$this->current_page=1;
    		$query=$this->grid_query.' '.(isset($this->per_page) ? 'LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page : '');
    		$stmt=$DBC->query($query);
    	}
    	
    	if(!$stmt){
    		return false;
    	}
    	
    	while($ar=$DBC->fetch($stmt)){
    		$ra[$ar[$this->grid_object->primary_key]] = $ar;
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
    					if(is_array($control_name)){
    						$rs .= ' <a href="?action='.$this->grid_object->action.'&do='.$control_name['name'].'&'.$this->grid_object->primary_key.'='.$primary_key_value.$control_params_oth_string.'" class="btn '.($control_name['btnclass']!='' ? $control_name['btnclass'] : 'btn btn-warning').'"><i class="icon-white '.($control_name['btnicon']!='' ? $control_name['btnicon'] : 'icon-tasks').'"></i>'.($control_name['btntext']!='' ? ' '.$control_name['btntext'] : '').'</a> ';
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
    	$DBC=DBC::getInstance();
    	
    	$query=$this->grid_query.' LIMIT '.(($this->current_page-1)*$this->per_page).', '.$this->per_page;
    	$stmt=$DBC->query($query);
    	
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ra[$ar[$this->grid_object->primary_key]] = $ar;
    		}
    	}
    
    	$ret=array();
    	if(!empty($ra)){
    		foreach ( $ra as $primary_key_value => $item_array ) {
    			$row_data = $this->grid_object->load_by_id($primary_key_value);
    			$data=array();
    			foreach ( $this->grid_items as $item_id => $item_name ) {
    				if($row_data[$item_name]['type']=='select_by_query'){
    					$data[$item_name]['value']=$row_data[$item_name]['value'];
    					$data[$item_name]['value_string']=$row_data[$item_name]['value_string'];
    				}elseif ($row_data[$item_name]['type']=='date') {
    					$data[$item_name]= $row_data[$item_name]['value'];
    				}elseif ($row_data[$item_name]['type']=='uploadify_image') {
    					$data['image_array'] = $this->get_image_array($this->get_action(), $this->get_table_name(), $this->grid_object->primary_key, (int)$primary_key_value);
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
    	}
    	
    	return $ret;
    }
    
    function getPager(){
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($this->grid_query);
    	$total=0;
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$total++;
    		}
    	}
    	
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