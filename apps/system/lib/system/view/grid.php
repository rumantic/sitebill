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
     * Массив объектов для рендеринга вывода элемента
     */
    protected $grid_items_render_objects = array();

    /**
     * Array with list of grid controls (edit/delete/structure/manual)
     * @var array
     */
    protected $grid_controls = array();
    
    /*
     * Enable howing button of batch list update
     */
    protected $batchUpdate = false;
    protected $batchActivate = false;
    protected $batchUpdateUrl = '';
    protected $massDeleteUrl = '';
    
    /**
     * 
     * @var string
     */
    protected $grid_query;
    protected $action;
    protected $table_name;
    protected $conditions = array();
    protected $conditions_sql = array();
    protected $conditions_left_join = array();
    protected $controls_params = array();

    /**
     * Grid object
     * @var object
     */
    protected $grid_object;
    protected $pager_params = array();
    /*
     * Идентификатор пользователя для которого запущен рендеринг грида
     * По-умолчанию false - т.е. для любого, проверки прав доступа не выполняется
     */
    protected $render_user_id = false;
    
    protected $total_count = 0;
            
    function __construct($grid_object) {
        $this->SiteBill();
        $this->grid_object = $grid_object;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $this->data_model_controller = new Data_Model();
    }
    
    function setBatchUpdateUrl($batch_update_url){
        $this->batchUpdateUrl = $batch_update_url;
    }
    
    function setMAssDeleteUrl($mass_delete_url){
        $this->massDeleteUrl = $mass_delete_url;
    }

    /**
     * Add grid item
     * @param string $name
     * @return void
     */
    function add_grid_item($name, $item_render_object = false) {
        array_push($this->grid_items, $name);
        if ( $item_render_object != false ) {
            $this->grid_items_render_objects[$name] = $item_render_object;
        }
    }

    /**
     * Set action name
     */
    function set_action($action = '') {
        $this->action = $action;
    }

    /**
     * Get action name
     */
    function get_action() {
        return $this->action;
    }

    /**
     * Set table name
     */
    function set_table_name($table_name = '') {
        $this->table_name = $table_name;
    }

    /**
     * Get table name
     */
    function get_table_name() {
        return $this->table_name;
    }
    
    public function enableBatchUpdate(){
        $this->batchUpdate = true;
    }
    
    public function enableMassDelete(){
        $this->massDelete = true;
    }
    
    public function enableBatchActivate(){
        $this->batchActivate = true;
    }

    /**
     * Add grid control
     * @param string $name
     * @return void
     */
    function add_grid_control($name) {
        array_push($this->grid_controls, $name);
    }

    function add_control_param($name, $value) {
        $this->controls_params[$name] = $value;
    }

    function set_grid_url($url) {
        $this->grid_url = $url;
    }

    /**
     * Set SQL-query for load records 
     * @param string $query
     * @return void
     */
    function set_grid_query($query) {
        //echo $query.'<br>';
        $this->grid_query = $query;
    }

    function set_grid_table($table) {
        $this->grid_table = $table;
    }

    function set_conditions($conditions) {
        $this->conditions = $conditions;
    }
    
    function set_conditions_sql($conditions) {
        $this->conditions_sql = $conditions;
    }

    function set_conditions_left_join($conditions) {
        $this->conditions_left_join = $conditions;
    }


    function extended_items() {
        //echo $this->get_action();
        $this->template->assign('action', $this->get_action());
        $this->template->assign('total_count', $this->get_total_count());
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/grid/extended_items_row.tpl');
        //return 'extended items';
    }

    function get_grid_query() {
        return $this->grid_query;
    }

    function construct_query() {
        $pager_params = $this->pager_params;
        foreach ($pager_params as $key => $value) {
            if ($key != 'per_page') {
                if ($key == 'page') {
                    //$sort_params[]='page=1';
                } else {
                    $sort_params[] = $key . '=' . $value;
                }
            }
        }
        $sort_params[] = 'page=1';


        $sortby = $this->getRequestValue('_sortby');
        if ($sortby == '') {
            $sortby = $this->grid_object->primary_key;
        }
        $sortdir = $this->getRequestValue('_sortdir');
        if ($sortdir == '') {
            $sortdir = 'DESC';
        }
        $pager_params['_sortby'] = $sortby;
        $pager_params['_sortdir'] = $sortdir;

        $where = array();
        if (!empty($this->conditions)) {
            foreach ($this->conditions as $key => $value) {
                $where[] = '(`' . $key . '`=\'' . $value . '\')';
                $sort_params[] = $key . '=' . $value;
            }
            $pager_params = array_merge($pager_params, $this->conditions);
        }

        $tagged_params = $this->add_tags_params();
        $where = $this->add_tagged_parms_to_where($where, $tagged_params, $this->grid_object->table_name);

        //$sort_params=array_merge($sort_params, $this->conditions);
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . (!empty($where) ? ' WHERE ' . implode('AND', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ';
        //echo $query;
        $this->set_grid_query($query);
    }

    function parse_id_values_from_model($column_name, $column_values, $data_model) {
        if ($data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_by_query') {
            foreach ($column_values as $idx => $value) {
                $val = $this->data_model_controller->get_value_id_by_name($data_model[$this->grid_object->table_name][$column_name]['primary_key_table'], $data_model[$this->grid_object->table_name][$column_name]['value_name'], $data_model[$this->grid_object->table_name][$column_name]['primary_key_name'], $value);

                if (0 != (int) $val) {
                    $column_values[$idx] = $val;
                } else {
                    unset($column_values[$idx]);
                }
            }
        } elseif ($data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_box' and count($column_values) > 0) {
            $select_data = array_flip($data_model[$this->grid_object->table_name][$column_name]['select_data']);
            $ra = array();
            foreach ($column_values as $idx => $value) {
                if ($select_data[$value]) {
                    $ra[] = $select_data[$value];
                }
            }
            return $ra;
        } elseif ($data_model[$this->grid_object->table_name][$column_name]['type'] == 'select_box_structure' and count($column_values) > 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure = new Structure_Manager();
            $x = $Structure->createCatalogChains();
            $categoryChain = $x['txt'];
            $categoryChainRev = array_flip($categoryChain);
            foreach ($column_values as $idx => $value) {
                $value_array = explode(' / ', $value);
                $var = implode("|", $value_array);
                $var = mb_strtolower($var);
                if (isset($categoryChainRev[$var])) {
                    $column_values[$idx] = $categoryChainRev[$var];
                } else {
                    unset($column_values[$idx]);
                }
            }
        }
        return $column_values;
    }

    function add_tags_params($params = array()) {

        if (isset($_SESSION['model_tags']) && is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'])) {
            foreach ($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'] as $column_name => $column_values) {
                $column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->grid_object->data_model);
                if (isset($params[$column_name]) and ! is_array($params[$column_name])) {
                    if ($params[$column_name] != 0) {
                        array_push($column_values, $params[$column_name]);
                    }
                    $params[$column_name] = $column_values;
                } elseif (isset($params[$column_name]) and is_array($params[$column_name])) {
                    $params[$column_name] = array_merge($params[$column_name], $column_values);
                } elseif (is_array($column_values)) {
                    $params[$column_name] = $column_values;
                }
            }
        }
        return $params;
    }
    
    function set_total_count($total_count) {
        $this->total_count = $total_count;
    }
    
    function get_total_count() {
        return $this->total_count;
    }

    function add_tagged_parms_to_where($where_array, $tagged_params, $table_name) {
        foreach ($tagged_params as $column_name => $column_values) {
            if (is_array($column_values) && count($column_values) > 0) {
                //$column_values=array_filter($column_values, function($a){if($a!=''){return $a;}});
                if (!empty($column_values)) {
                    $type = $this->grid_object->data_model[$table_name][$column_name]['type'];
                    if (isset($column_values['min']) || isset($column_values['max'])) {
                        if (isset($column_values['min'])) {
                            $where_array[] = "(" . DB_PREFIX . "_" . $table_name . "." . $column_name . " >= '" . $column_values['min'] . "')";
                        }
                        if (isset($column_values['max'])) {
                            $where_array[] = "(" . DB_PREFIX . "_" . $table_name . "." . $column_name . " <= '" . $column_values['max'] . "')";
                        }
                    } elseif ($type == 'client_id') {
                        $where_fio_phone_array = array();
                        foreach ($column_values as $fio_phone) {
                            list($fio, $phone) = explode(',', $fio_phone);
                            $fio = trim($fio);
                            $phone = trim($phone);
                            $where_fio_phone_array[] = " client_id in (select client_id from " . DB_PREFIX . "_client where fio='$fio' and phone='$phone') ";
                        }

                        $where_array[] = implode(' or ', $where_fio_phone_array);
                    } else {
                        $where_array[] = "(" . DB_PREFIX . "_" . $table_name . "." . $column_name . " IN ('" . implode('\',\'', $column_values) . "'))";
                    }
                }
            }
        }
        return $where_array;
    }

    function set_render_user_id($user_id) {
        $this->render_user_id = $user_id;
    }

    function get_render_user_id() {
        return $this->render_user_id;
    }

    function construct_grid($control_params = false, $disable_mass_delete = false) {
       
        //Регистрируем hook для обработки элементов грида при выводе
        if (function_exists('BeforePrintGridItem')) {
            $BeforePrintGridItem = true;
        } else {
            $BeforePrintGridItem = false;
        }

        if ($this->grid_query != '') {

            return $this->_construct_grid($control_params, $disable_mass_delete);
        } else {
            
            /*$_props=array();
            if(isset($_GET['_props']) && is_array($_GET['_props']) && !empty($_GET['_props'])){
                $_props=$_GET['_props'];
            }
            
            print_r($_props);
            
            foreach ($_props as $column_name => $column_values) {
                $column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->get_model());
                if (isset($params[$column_name]) and ! is_array($params[$column_name])) {
                    if ($params[$column_name] != 0) {
                        array_push($column_values, $params[$column_name]);
                    }
                    $params[$column_name] = $column_values;
                } elseif (isset($params[$column_name]) and is_array($params[$column_name])) {
                    $params[$column_name] = array_merge($params[$column_name], $column_values);
                } elseif (is_array($column_values)) {
                    $params[$column_name] = $column_values;
                }
            }*/
        
            
            $DBC = DBC::getInstance();
            $pager_params = array();
            $sort_params = array();

            $pager_params = $this->pager_params;
            foreach ($pager_params as $key => $value) {
                if ($key != 'per_page') {
                    if ($key == 'page') {
                        //$sort_params[]='page=1';
                    } else {
                        $sort_params[] = $key . '=' . $value;
                    }
                }
            }

            $table_and_prefix = DB_PREFIX.'_'.$this->grid_object->table_name;

            $sort_params[] = 'page=1';
            if ($this->getRequestValue('_sortby') == '') {
                $sortby = $this->grid_object->primary_key;
            } else {
                $sortby = $this->getRequestValue('_sortby');
            }
            $sortdir = $this->getRequestValue('_sortdir');
            if ($sortdir == '') {
                $sortdir = 'DESC';
            }
            $pager_params['_sortby'] = $sortby;
            $pager_params['_sortdir'] = $sortdir;
            $sortby = $table_and_prefix.'.'.$sortby;

            $where = array();
            //unset($_SESSION['tags_array']);
            //unset($_SESSION['model_tags']);
            //echo '<pre>';
            //print_r($_SESSION);
            //print_r($this->conditions);
            //echo '</pre>';

            if (!empty($this->conditions)) {
                foreach ($this->conditions as $key => $value) {
                    if(is_numeric($key)){
                        $sub=array();
                        foreach($value as $k=>$subv){
                            if (is_array($subv)) {
                                $sub[] = '(`' . $table_and_prefix . '`.`' . $k . '` IN (' . implode(', ', $subv) . '))';
                            } else {
                                $sub[] = '(`' . $table_and_prefix . '`.`' . $k . '`=\'' . $subv . '\')';
                                //$sort_params[] = $k . '=' . $subv;
                            }
                        }
                        $where[] = '(' . implode(' OR ', $sub) . ')';
                    }else{
                        if (is_array($value)) {
                            $where[] = '(`' . $table_and_prefix . '`.`' . $key . '` in (\'' . implode('\', \'', $value) . '\'))';
                        } else {
                            $where[] = '(`' . $table_and_prefix . '`.`' . $key . '`=\'' . $value . '\')';
                            $sort_params[] = $table_and_prefix.'.'.$key . '=' . $value;
                        }
                    }
                    
                }
                $pager_params = array_merge($pager_params, $this->conditions);
            }
            if (!empty($this->conditions_sql)) {
                foreach ($this->conditions_sql as $sql_condition) {
                    $where[] = $sql_condition;
                }
            }
            $left_join_tables = ' ';
            if (!empty($this->conditions_left_join)) {
                $left_join_tables = implode(' ', $this->conditions_left_join['tables']);
            }

            $tagged_params = $this->add_tags_params();
            $where = $this->add_tagged_parms_to_where($where, $tagged_params, $this->grid_object->table_name);

            //echo '<pre>';
            //print_r($where);
            //print_r($_SESSION);
            //print_r($this->conditions);
            //echo '</pre>';
            //$sort_params=array_merge($sort_params, $this->conditions);
            
            //$this->per_page=3;
            
            $query_no_limit = 'SELECT `'. DB_PREFIX . '_' . $this->grid_object->table_name. '`.* FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ';
            $query_no_limit_total_count = 'SELECT count(`'. DB_PREFIX . '_' . $this->grid_object->table_name. '`' .'.`'.$this->grid_object->primary_key.'`) as total FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '');
            $query =          'SELECT `'. DB_PREFIX . '_' . $this->grid_object->table_name. '`.* FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . $left_join_tables . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $sortby . ' ' . $sortdir . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');
            //$this->writeLog($query);
            //$this->writeLog($query_no_limit);
            //$this->writeLog($query_no_limit_total_count);
            $stmt = $DBC->query($query_no_limit_total_count);
            $total_count = 0;
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $total_count = $ar['total'];
            }
            $this->set_total_count($total_count);
           
            $this->set_grid_query($query_no_limit);
            //echo $total_count;
            $stmt = $DBC->query($query);
            if (!$stmt && $this->current_page != 1) {
                $this->current_page = 1;
                $query = 'SELECT `'. DB_PREFIX . '_' . $this->grid_object->table_name. '`.* FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name .$left_join_tables . (!empty($where) ? ' WHERE ' . implode('AND', $where) : '') .' ORDER BY ' . $sortby . ' ' . $sortdir . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');
                $stmt = $DBC->query($query);
            }
            $empty_data = false;
            if (!$stmt) {
                //return false;
                $empty_data = true;
            }

            if (!$empty_data) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ra[$ar[$this->grid_object->primary_key]] = $ar;
                }
            } else {
                $ra = array();
            }
            
            /*echo '<pre>';
            print_r($ra);*/
            
            if(!empty($ra) && $this->grid_object->table_name=='data'){
                if(1==intval($this->getConfigValue('use_topic_actual_days'))){
                    //$fe=reset($ra);
                    /*$actual_dates=array();
                    $actual_topics=array();*/
                    
                    //$topic_actuals=array();
                    $DBC=DBC::getInstance();
                    /*if(!isset($fe['date_added'])){
                        $query='SELECT id, date_added FROM '.DB_PREFIX.'_data WHERE id IN ('.implode(',', array_keys($ra)).')';
                        $stmt=$DBC->query($query);
                        if($stmt){
                            while($ar=$DBC->fetch($stmt)){
                                $actual_dates[$ar['id']]=$ar['date_added'];
                            }
                        }
                        
                    }else{
                        foreach($ra as $k=>$v){
                            $actual_dates[$k]=$v['date_added'];
                        }
                    }
                    
                    if(!isset($fe['topic_id'])){
                        $query='SELECT id, topic_id FROM '.DB_PREFIX.'_data WHERE id IN ('.implode(',', array_keys($ra)).')';
                        $stmt=$DBC->query($query);
                        if($stmt){
                            while($ar=$DBC->fetch($stmt)){
                                $actual_topics[$ar['id']]=$ar['topic_id'];
                            }
                        }
                        
                    }else{
                        foreach($ra as $k=>$v){
                            $actual_topics[$k]=$v['topic_id'];
                        }
                    }*/

                    $topic_actuals=array();
                    
                    $query='SELECT id, actual_days FROM '.DB_PREFIX.'_topic';
                    $stmt=$DBC->query($query);
                    if($stmt){
                        while($ar=$DBC->fetch($stmt)){
                            $topic_actuals[$ar['id']]=$ar['actual_days'];
                        }
                    }
                    foreach($ra as $k=>$v){
                        $actual_adv_days=floor((time()-strtotime($v['date_added']))/(24*3600));
                        if(isset($topic_actuals[$v['topic_id']]) && intval($topic_actuals[$v['topic_id']])>0 && $actual_adv_days>$topic_actuals[$v['topic_id']]){
                            $ra[$k]['_classes']='actuality_expired';
                        }

                    }
                }
            }
            

            //Отсюда начинаем формировать таблицу со всеми подключаемыми плагинами
            $rs = '';

            if (is_array($this->grid_controls)) {
                if (in_array('memorylist', $this->grid_controls)) {
                    $rs .= $this->get_memory_header();
                }
            }

            $rs .= '
<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/css/colorbox.css" />
<script src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/js/jquery.colorbox-min.js"></script>
					
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

    $(\'.mass_delete\').click(function(){
		var ids=[];
		var url=$(this).data(\'url\');
		$(this).parents(\'table\').eq(0).find(\'input.grid_check_one:checked\').each(function(){
			ids.push($(this).val());
		});
        
		window.location.replace(url+\'?do=mass_delete&ids=\'+ids.join(\',\'));
	});
    
    $(\'.mass_action\').click(function(){
		var ids=[];
		var url=$(this).data(\'url\');
        var a=$(this).data(\'action\');
		$(this).parents(\'table\').eq(0).find(\'input.grid_check_one:checked\').each(function(){
			ids.push($(this).val());
		});
        if(ids.length>0){
            window.location.replace(\'/account/data/?do=mass_action&action_name=\'+a+\'&ids=\'+ids.join(\',\'));
        }else{
            return false;
        }
    });
	
	$(\'.colorboxed\').each(function(item){
		setColorboxWrapper($(this).data(\'cbxid\'));
	});
    
    $(\'.tags-clear\').click(function(e){
        e.preventDefault();
        $.ajax({url: \'' . SITEBILL_MAIN_URL . '/js/ajax.php?action=get_tags&do=clear&model_name=' . $this->grid_object->table_name . '\'}).done(function(){location.reload();});
    });
    
            $(\'.fast_preview\').click(function () {
                var id = $(this).data(\'id\');
                if (fast_previews[id] === undefined) {
                    $.ajax({
                        url: estate_folder + \'/js/ajax.php?action=fast_preview_public&id=\' + id,
                        dataType: \'html\',
                        success: function (html) {
                            fast_previews[id] = html;
                            $(\'#fast_preview_modal\').find(\'.modal-body\').html(html);
                            $(\'#fast_preview_modal\').find(\'.newwin\').attr(\'href\', estate_folder + \'/realty\' + id);
                            $(\'#fast_preview_modal\').modal(\'show\');
                        }
                    });
                } else {
                    $(\'#fast_preview_modal\').find(\'.modal-body\').html(fast_previews[id]);
                    $(\'#fast_preview_modal\').find(\'.newwin\').attr(\'href\', estate_folder + \'/realty\' + id);
                    $(\'#fast_preview_modal\').modal(\'show\');
                }
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
			var txt=\''._e('не задано').'\';
			
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
				$.ajax({url: \'' . SITEBILL_MAIN_URL . '/js/ajax.php?action=get_tags&do=set&model_name=' . $this->grid_object->table_name . '&tags_array=\'+JSON.stringify(datastr)}).done(function(result_items){location.reload();});
			});
			
			_this.find(\'.clear\').click(function(e){
				e.preventDefault();
				if(typeof datastr[name] != \'undefined\'){
					tag_array=datastr[name];
					delete datastr[name];
				}
				$.ajax({url: \'' . SITEBILL_MAIN_URL . '/js/ajax.php?action=get_tags&do=set&model_name=' . $this->grid_object->table_name . '&tags_array=\'+JSON.stringify(datastr)}).done(function(result_items){location.reload();});
			});
			
		});
});


</script>';
if($this->batchUpdate){
    $rs .= '<script>$(document).ready(function(){
      $(\'.batch_update\').click(function () {
        var ids = [];
        var action = $(this).attr(\'alt\');
        $(this).parents(\'table\').eq(0).find(\'input.grid_check_one:checked\').each(function () {
            ids.push($(this).val());
        });
        if(ids.length>0){
            window.location.replace(\''.$this->batchUpdateUrl.'?action=\' + action + \'&do=batch_update&batch_ids=\' + ids.join(\',\'));
        }else{
            return false;
        }
        });  
    });</script>';
}


$rs .= '<div class="modal fade" id="fast_preview_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>'._e('Быстрый просмотр').' <a target="_blank" class="btn btn-success newwin" href="#">'._e('открыть в новом окне').'</a></h3>
    </div>
    <div class="modal-body"></div>
    <div class="modal-footer"></div>
</div>
</div>
</div>

                                <table class="table table-striped table-hover dataTable">';
            $rs .= '<thead>';
            $rs .= '<tr>';
            if (!$disable_mass_delete) {
                $rs .= '<th><input type="checkbox" class="grid_check_all" /></td>';
            }
            //echo $sortby;
            $sort_url = $this->grid_url;
            if ($sort_url == '') {
                $sort_url = SITEBILL_MAIN_URL . '/admin/index.php';
            }

            foreach ($this->grid_items as $item_id => $item_name) {
                if (!isset($this->grid_object->data_model[$this->grid_object->table_name][$item_name])) {
                    unset($this->grid_items[$item_id]);
                }
            }


            foreach ($this->grid_items as $item_id => $item_name) {
                
                $rs .= '<th ';
                if ($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key') {
                    $rs .= 'width="1%"';
                }
                if ($sortby == $item_name) {
                    if (strtolower($sortdir) == 'asc') {
                        $sortdirn = 'desc';
                        $sorted = 'common-grid-sorted-asc';
                    } else {
                        $sortdirn = 'asc';
                        $sorted = 'common-grid-sorted-desc';
                    }
                } else {
                    $sortdirn = 'asc';
                    $sorted = '';
                }
                $_sort = $sort_params;

                $_sort[] = '_sortby=' . $item_name;
                $_sort[] = '_sortdir=' . $sortdirn;
                $s = '<a class="common-grid-sortable ' . $sorted . '" href="' . $sort_url . '?' . implode('&', $_sort) . '">' . $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['title'] . '</a>';

                if ($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'price') {
                    $tags_input = '<div class="ranged-tags" data-field="' . $item_name . '">
    						<div class="ranged-tags-title"></div>
    						<div class="ranged-tags-params" style="display: none;">
    						<input name="' . $item_name . '[min]" type="text" class="tagged_input" value="' . $_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]['min'] . '">
    						<input name="' . $item_name . '[max]" type="text" class="tagged_input" value="' . $_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name]['max'] . '">
    						<a href="#" class="btn btn-danger clear" title="очистить фильтр"><i class="icon-remove"></i></a>
    						<a href="#" class="btn btn-success apply" title="применить фильтр"><i class="icon-ok"></i></a>
    						<a href="#" class="btn cancel" title="скрыть окно фильтра"><i class="icon-off"></i></a>
    						</div>
    						</div>';
                    $rs .= '>' . $s . $tags_input . '</th>';
                } else {
                    $tags_input = '
                        <div class="inline-tags">
                            <input type="text" name="' . $item_name . '" id="' . $item_name . '" class="input-tag tagged" value="" placeholder="..." />
                        </div>';
                    $tags_input .= "
			<script type=\"text/javascript\">
			$(document).ready(function(){
				var tag_input = $('#" . $item_name . "');
				var tag_array = [];
				try{
				   tag_input.tag({
				      placeholder: tag_input.attr('placeholder'),
				      source: function(query, process) {
				    	  column_name = tag_input.attr('name');
							$.ajax({
								url: estate_folder+'/js/ajax.php?action=get_tags&column_name='+column_name+'&model_name=" . $this->grid_object->table_name . "'
				        	}).done(function(result_items){
								process(result_items);
							});
						}
				   });
					var tag_obj = tag_input.data('tag');";


                    if (isset($_SESSION['model_tags']) && is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name])) {
                        foreach ($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name] as $tag_item) {
                            $tags_input .= 'tag_obj.add("' . $tag_item . '");
                                            tag_array.push("' . $tag_item . '");
                                            datastr["' . $item_name . '"] = tag_array;';
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
			        $.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&model_name=" . $this->grid_object->table_name . "&do=set&tags_array='+JSON.stringify(datastr)})
			        .done(function(result_items){
			        	location.reload();
			           //process(result_items);
			        });
				})
				tag_input.on('removed', function (e, value) {
			   		var item_index = datastr[$(this).attr('name')].indexOf(value);
			   		datastr[$(this).attr('name')].splice(item_index, 1);
			        $.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&model_name=" . $this->grid_object->table_name . "&do=set&tags_array='+JSON.stringify(datastr)})
			        .done(function(result_items){
			        	location.reload();
			           //process(result_items);
			        });
				})
    						
    	
			});
    						
			</script>
    	
    	
                                        ";
                    
                    /*$tags_input = '
                        <div class="inline-tags1">
                            <input type="text" name="' . $item_name . '" id="' . $item_name . '" class="input-tag tagged" value="" />
                        </div>';*/

                    $rs .= '>' . $s . $tags_input . '</th>';
                }
            }


            if (count($this->grid_controls) > 0) {
                $rs .= '<th  width="1%"><a class="tags-clear" href="">'._e('Очистить').'</a></th>';
            }
            $rs .= '</tr>';
            $rs .= '</thead>';
            $rs .= '<tbody>';
           
            if (count($ra) > 0) {
                
                
                
                foreach ($ra as $primary_key_value => $item_array) {
                    $ids[]=$primary_key_value;
                }
                
                $row_datas=$this->grid_object->load_by_id($ids);
                 
                //echo count($row_datas);
                
                
                $checked_accesses=array();
              //var_dump($row_data);
            //exit();
                foreach ($ra as $primary_key_value => $item_array) {
                    //$row_data = $this->grid_object->load_by_id($primary_key_value);
                    $row_data = $row_datas[$primary_key_value];
                    if ($BeforePrintGridItem) {
                        $row_data = BeforePrintGridItem($row_data, $control_params);
                    }
                    $has_access = $this->check_access($this->grid_object->action, $this->get_render_user_id(), $check_control_name, $this->grid_object->primary_key, $primary_key_value);
                    
                    $rs .= '<tr class="'.((isset($item_array['active']) && $item_array['active']==0) ? 'notactive danger alert-danger' : '').((isset($item_array['_classes']) && $item_array['_classes']!='') ? ' '.$item_array['_classes'] : '').'">';
                    $grid_counter = 0;
                    if (!$disable_mass_delete) {
                        $grid_counter = 1;
                        $rs .= '<td><input type="checkbox" class="grid_check_one" value="' . $primary_key_value . '" /></td>';
                    }

                    foreach ($this->grid_items as $item_id => $item_name) {
                        $grid_counter++;
                        
                        if ( isset($row_data[$item_name]['parameters']['only_owner_access']) && $row_data[$item_name]['parameters']['only_owner_access'] == 1 ) {
                            if ( !$has_access and $_SESSION['current_user_group_name'] != 'admin') {
                                $row_data[$item_name]['value'] = _e('скрыто');
                                $row_data[$item_name]['value_string'] = _e('скрыто');
                            }
                            //echo '<pre>';
                            //print_r($row_data[$item_name]);
                            //print_r($this->grid_object->data_model[$this->grid_object->table_name]);
                            //echo '</pre>';
                        }
                        

                        /* if($row_data[$item_name]['name']=='name'){
                          $a=' class="editable_name_field" data-key="'.$this->grid_object->primary_key.'" data-fid="'.$primary_key_value.'" data-tbl="'.$this->grid_object->action.'"';
                          }else{
                          $a='';
                          } */
                        $a = '';

                        if ($row_data[$item_name]['type'] == 'select_by_query') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'structure') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'select_box_structure') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'date') {
                            $rs .= '<td  >' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'client_id') {
                            $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'select_box') {
                            $rs .= '<td  >' . $row_data[$item_name]['select_data'][$row_data[$item_name]['value']] . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'photo') {
                            if ($row_data[$item_name]['value'] != '') {
                                $rs .= '<td><img width="100" src="/img/data/user/' . $row_data[$item_name]['value'] . '"></td>';
                            } else {
                                $rs .= '<td></td>';
                            }
                        } elseif ($row_data[$item_name]['type'] == 'checkbox') {
                            $rs .= '<td>' . ($row_data[$item_name]['value'] == 1 ? '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_yes.png">' : '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_no.png">') . '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'uploads') {
                            $rs .= '<td>';
                            if (count($row_data[$item_name]['value']) > 0 and is_array($row_data[$item_name]['value'])) {
                                $rs .= '<ul class="ace-thumbnails clearfix">';
                                $counter = 0;
                                //echo '<pre>';
                                //print_r($row_data[$item_name]['value']);
                                //echo '</pre>';

                                foreach ($row_data[$item_name]['value'] as $vv) {
                                    if ( $vv['remote'] == 'true' ) {
                                        $preview_url = $vv['preview'];
                                        $normal_url = $vv['normal'];
                                    } else {
                                        $preview_url = SITEBILL_MAIN_URL . '/img/data/' . $vv['preview'];
                                        //echo $preview_url.'<br>';
                                        $normal_url = SITEBILL_MAIN_URL . '/img/data/' .$vv['normal'];
                                    }
                                    if ($counter == 0) {
                                        $rs .= '<li><img src="' . $preview_url . '" style="width: 40px; height: 40px;">
					<div class="tags">
						<span class="label-holder">
							<span class="label label-info">' . count($row_data[$item_name]['value']) . '</span>
						</span>
					</div>
					<div class="tools tools-top">
						<a href="' . $normal_url . '"  data-rel="colorbox' . $primary_key_value . $item_name . '" class="colorboxed" data-cbxid="' . $primary_key_value . $item_name . '">
							<i class="ace-icon fa fa-search-plus"></i>
						</a>
					</div>
											
											</li>
';
                                    } else {
                                        $rs .= '
				<li style="display: none;">
					<a href="' . $normal_url . '"  data-rel="colorbox' . $primary_key_value . $item_name . '"><img src="' . $preview_url . '" width="50" /></a>
				</li>
											
											';
                                    }
                                    $counter++;
                                }
                                $rs .= '</ul>';
                            }
                            $rs .= '</td>';
                        } elseif ($row_data[$item_name]['type'] == 'docuploads') {
                            $rs .= '<td>' . count($row_data[$item_name]['value']) . '</td>';
                        } else {
                            if (is_array($row_data[$item_name]['value'])) {
                                $rs .= '<td>' . implode(';', $row_data[$item_name]['value']) . '</td>';
                            } else {
                                $rs .= '<td' . $a . '>' . $row_data[$item_name]['value'] . '</td>';
                            }
                        }
                    }

                    if (count($this->grid_controls) > 0) {
                        $rs .= '<td nowrap class="account-grid-controls '.((isset($item_array['active']) && $item_array['active']==0) ? 'notactive danger alert-danger' : '').'">';
                        foreach ($this->grid_controls as $control_id => $control_name) {
                            if (is_array($control_name)) {
                                $check_control_name = $control_name['name'];
                            } else {
                                $check_control_name = $control_name;
                            }
                            if ($control_name == 'memorylist') {
                                $rs .= $this->compile_memory_control($primary_key_value);
                                continue;
                            } elseif ($control_name == 'fast_preview') {
                                $rs .= ' <button data-id="'.$primary_key_value.'" class="fast_preview btn btn-danger btn-mini"><i class="icon-white icon-eye-open"></i></button> ';
                                continue;
                            }

                            if (!$has_access) {
                                continue;
                            }
                            if ($control_name == 'view') {
                                $control_params_view_string = '';
                                if (!empty($control_params['view'])) {
                                    $control_params_view_string = $control_params['view'];
                                }
                                /* if(!empty($this->controls_params)){
                                  $control_params_view_string.='&'.http_build_query($this->controls_params);
                                  } */
                                $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=view&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_view_string . '" class="btn btn-info"><i class="icon-white icon-info-sign"></i></a> ';
                            } elseif ($control_name == 'edit') {
                                $control_params_edit_string = '';
                                if (!empty($control_params['edit'])) {
                                    $control_params_edit_string = $control_params['edit'];
                                }
                                /* if(!empty($this->controls_params)){
                                  $control_params_edit_string.='&'.http_build_query($this->controls_params);
                                  } */
                                $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=edit&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_edit_string . '" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
                            } elseif ($control_name == 'delete') {
                                $control_params_delete_string = '';
                                if (!empty($control_params['delete'])) {
                                    $control_params_delete_string = $control_params['delete'];
                                }
                                /* if(!empty($this->controls_params)){
                                  $control_params_delete_string.='&'.http_build_query($this->controls_params);
                                  } */
                                $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=delete&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_delete_string . '" onclick="if ( confirm(\''._e('Действительно хотите удалить запись?').'\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
                            } else {
                                $control_params_oth_string = '';

                                /* if(!empty($this->controls_params)){
                                  $control_params_oth_string.='&'.http_build_query($this->controls_params);
                                  } */
                                if (is_array($control_name)) {
                                    $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $control_name['name'] . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="btn ' . ($control_name['btnclass'] != '' ? $control_name['btnclass'] : 'btn-warning') . '"><i class="icon-white ' . ($control_name['btnicon'] != '' ? $control_name['btnicon'] : 'icon-tasks') . '"></i>' . ($control_name['btntext'] != '' ? ' ' . $control_name['btntext'] : '') . '</a> ';
                                } else {
                                    $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $control_name . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="btn btn-warning"><i class="icon-white icon-tasks"></i></a> ';
                                }
                            }
                            /*if (is_array($control_name)) {
                                foreach ($control_name as $custom_control_name => $custom_control_string) {
                                    $custom_code = str_replace('{primary_key_value}', $primary_key_value, $custom_control_string);
                                    $rs .= $custom_code . ' ';
                                }
                            }*/
                        }
                        $rs .= '</td>';
                    }
                    $rs .= '</tr>';
                    if ((ADMIN_MODE!=1 && $this->grid_object->table_name == 'complex' && intval($this->getConfigValue('apps.complex.use_billing'))==1) || ($this->getConfigValue('apps.billing.enable') and $this->grid_object->table_name == 'data' and $this->check_access($this->grid_object->action, $this->get_render_user_id(), 'edit', $this->grid_object->primary_key, $primary_key_value)) ) {
                        
                        $rs .= '<tr>';
                        $rs .= '<td colspan="' . (count($this->grid_controls) + $grid_counter) . '">'.$this->billing_controls($row_data).'</td>';
                        $rs .= '</tr>';
                    }
                    
                }
                
                if(!$disable_mass_delete || $this->batchUpdate || $this->batchActivate){
                    $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '">';
                    if (!$disable_mass_delete) {
                        if($this->massDeleteUrl != ''){
                            $rs .= '<button data-url="' . $this->massDeleteUrl . '" class="mass_delete btn btn-danger"><i class="icon-white icon-remove"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button> ';
                        }else{
                            $rs .= '<button alt="' . $this->grid_object->action . '" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button> ';
                        }
                    }
                    if($this->batchUpdate){
                        $rs .= '<button alt="' . $this->grid_object->action . '" class="batch_update btn btn-inverse"><i class="icon-white icon-th"></i> '._e('Пакетная обработка').'</button> ';
                    }
                    if($this->batchActivate){
                        $rs .= '<button alt="' . $this->grid_object->action . '" data-action="activate" class="mass_action btn btn-inverse">'._e('Активировать').'</button> ';
                        $rs .= '<button alt="' . $this->grid_object->action . '" data-action="deactivate" class="mass_action btn btn-inverse">'._e('Архивировать').'</button> ';
                    }
                    $rs .= '</td></tr>';
                }
                /*if (!$disable_mass_delete) {
                    $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '"><button alt="' . $this->grid_object->action . '" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button></td></tr>';
                }*/
                $query = 'SELECT COUNT(' . $this->grid_object->primary_key . ') AS _cnt FROM ' . DB_PREFIX . '_' . $this->grid_object->table_name . (!empty($where) ? ' WHERE ' . implode('AND', $where) : '');
                $stmt = $DBC->query($query);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $total = $ar['_cnt'];
                } else {
                    $total = 0;
                }
                $page_links_list=$this->get_page_links_list($this->current_page, $total, $this->per_page, $pager_params);
                if($page_links_list!=''){
                    $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '" class="pager"><div align="center">';
                    $rs .= $page_links_list;
                    $rs .= '</div></td></tr>';
                }
                
            } else {
                $rs .= '<tr>';
                $rs .= '<td colspan="' . (count($this->grid_controls) + count($this->grid_items)) . '"><p align="center" class="alert">'._e('Ничего не найдено').'</p></td>';
                $rs .= '</tr>';
            }

            $rs .= '</tbody>';

            $rs .= '</table>';
            $rs .= '';
            
            if($this->grid_object->table_name == 'complex' && intval($this->getConfigValue('apps.complex.use_billing'))==1){
                $rs .= $this->getB();      
            }
            
            

            return $rs;
        }
    }
    
    private function getB(){
        $status_cost=array();
        if($this->grid_object->table_name == 'complex'){
            $status_cost['vip']= floatval($this->getConfigValue('apps.complex.complex_vip_cost'));
            $status_cost['premium']= floatval($this->getConfigValue('apps.complex.complex_premium_cost'));
            $status_cost['bold']= floatval($this->getConfigValue('apps.complex.complex_bold_cost'));
        }
        

        $ret='';
        $ret.='<div class="modal fade" class="makeSpec" id="makeSpec" tabindex="-1" role="dialog" aria-labelledby="makeSpecOk" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="makeSpecModalLabel">
    	<span class="spec_title spec_title_premium">'.Multilanguage::_('BPREMIUM_MAKE_TIT', 'system').'</span>
    	<span class="spec_title spec_title_bold">'.Multilanguage::_('BBOLD_MAKE_TIT', 'system').'</span>
    	<span class="spec_title spec_title_vip">'.Multilanguage::_('BVIP_MAKE_TIT', 'system').'</span>
    </h3>
  </div>
  <div class="modal-body">
  	<form class="form-horizontal">
  		<input type="hidden" value="" name="realty_id" />
  		<input type="hidden" value="" name="per_day_price" />
  		<input type="hidden" value="" name="type" />
        <input type="hidden" value="'.$this->grid_object->table_name.'" name="object_name" />
            <input type="hidden" value="'.$this->grid_object->primary_key.'" name="object_key" />
  		
  		<input type="hidden" value="'.$status_cost['premium'].'" id="pdp_premium" />
  		<input type="hidden" value="'.$status_cost['vip'].'" id="pdp_vip" />
  		<input type="hidden" value="'.$status_cost['bold'].'" id="pdp_bold" />
        
  		
		  <div class="control-group">
		    <label class="control-label">'.Multilanguage::_('B_MAKE_DAYS', 'system').'</label>
		    <div class="controls">
		      <input type="text" value="1" name="days" />
		    </div>
		  </div>
		  <div class="control-group">
		    <label class="control-label">'.Multilanguage::_('$L_PRICE', 'system').'</label>
		    <div class="controls">
		      <span class="calc_price"></span>
		    </div>
		  </div>
	</form>
	<div class="answer" style="display: none;"></div>
  </div>
  <div class="modal-footer">
  	<button class="btn use_own">'.Multilanguage::_('B_MAKE_USEPACKETS', 'system').'</button>
	<button class="btn ok">'.Multilanguage::_('OK_NM', 'system').'</button>
    <button class="btn" data-dismiss="modal" aria-hidden="true">'.Multilanguage::_('CANCEL_NM', 'system').'</button>
  </div>
</div>';
        
        $ret.='<script src="'.SITEBILL_MAIN_URL.'/apps/billing/js/grid_billing.js"></script>';
        return $ret;
    }
    
    private function billing_controls ($row_data) {
        
        $DBC = DBC::getInstance();
        $row = array();
        $query = 'SELECT `vip_status_end`, `premium_status_end`, `bold_status_end` FROM ' . DB_PREFIX . '_'.$this->grid_object->table_name.' WHERE `'.$this->grid_object->primary_key.'` = ? LIMIT 1';
        $stmt = $DBC->query($query, array($row_data[$this->grid_object->primary_key]['value']));
        if ($stmt) {
            $row = $DBC->fetch($stmt);
        }
        
        
        if ( $row['vip_status_end'] > time() ) {
            $rs .= ' <span class="vb alert alert-info"><i class="icon-star icon-black"></i> '.Multilanguage::_('GB_BVIP_TO', 'system').' '.date('d.m.Y H:i', $row['vip_status_end']).'</span>';
        } else {
            $rs .= ' <a class="btn btn-small make_spec" data-type="vip" data-object="'.$this->grid_object->table_name.'" alt="'.$row_data[$this->grid_object->primary_key]['value'].'"><i class="icon-star icon-black"></i> '.Multilanguage::_('GB_BVIP_MAKE', 'system').'</a>';
        }

        if ( $row['premium_status_end'] > time() ) {
            $rs .= ' <span class="vb alert alert-info"><i class="icon-fire icon-black"></i> PREMIUM до '.date('d.m.Y H:i', $row['premium_status_end']).'</span>';
        } else {
            $rs .= ' <a class="btn btn-small make_spec" data-type="premium" data-object="'.$this->grid_object->table_name.'" alt="'.$row_data[$this->grid_object->primary_key]['value'].'"><i class="icon-fire icon-black"></i> Сделать PREMIUM</a>';
        }

        if ( $row['bold_status_end'] > time() ) {
            $rs .= ' <span class="vb alert alert-info"><i class="icon-heart icon-black"></i> '.Multilanguage::_('GB_BBOLD_TO', 'system').' '.date('d.m.Y H:i', $row['bold_status_end']).'</span>';
        } else {
            $rs .= ' <a class="btn btn-small make_spec" data-type="bold" data-object="'.$this->grid_object->table_name.'" alt="'.$row_data[$this->grid_object->primary_key]['value'].'"><i class="icon-heart icon-black"></i> '.Multilanguage::_('GB_BBOLD_MAKE', 'system').'</a>';
        }
        if ( $this->grid_object->table_name == 'data' && $_SESSION['billing']['upps_left'] > 0 or $_SESSION['billing']['packs_left'] > 0 ) {
            $rs .= '<a class="btn btn-small go_up" href="'.SITEBILL_MAIN_URL.'/upper/realty'.$row_data['id']['value'].'/"><i class="icon-arrow-up icon-black"></i> Поднять</a>';
        }
        
        return $rs;
    }

    private function compile_memory_control($id) {
        $this->template->assign('id', $id);
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/template/memorylist_item_control.tpl');
    }

    private function get_memory_header() {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
        $ML = new Memory_List();
        $memory_lists = $ML->getUserMemoryLists($this->get_render_user_id());
        foreach ($memory_lists as $ml) {
            if (isset($ml['items']) && count($ml['items']) > 0) {
                foreach ($ml['items'] as $item) {
                    $items_in_memory[$item['id']][] = $ml;
                }
            }
        }
        $this->template->assign('items_in_memory', $items_in_memory);
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/template/memorylist_header.tpl');
    }

    private function _construct_grid($control_params = false, $disable_mass_delete = false) {
        $ra = array();
        $query = $this->grid_query . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if (!$stmt && $this->current_page != 1) {
            $this->current_page = 1;
            $query = $this->grid_query . ' ' . (isset($this->per_page) ? 'LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page : '');
            $stmt = $DBC->query($query);
        }

        if (!$stmt) {
            return false;
        }

        while ($ar = $DBC->fetch($stmt)) {
            $ra[$ar[$this->grid_object->primary_key]] = $ar;
        }

        if (count($ra) > 0) {
            $rs = '<table border="0" width="99%" class="table table-hover">';
            $rs .= '<thead>';
            $rs .= '<tr>';
            if (!$disable_mass_delete) {
                $rs .= '<th class="row_title" width="1%"><input type="checkbox" class="grid_check_all" /></td>';
            }
            foreach ($this->grid_items as $item_id => $item_name) {

                $rs .= '<th class="row_title"';
                if ($this->grid_object->data_model[$this->grid_object->table_name][$item_name]['type'] == 'primary_key') {
                    $rs .= 'width="1%"';
                }
                $rs .= '>' . $this->grid_object->data_model[$this->grid_object->table_name][$item_name]['title'] . '</th>';
            }
            if (count($this->grid_controls) > 0) {
                $rs .= '<th class="row_title" width="1%"></th>';
            }
            $rs .= '</tr>';
            $rs .= '</thead>';
            $rs .= '<tbody>';
            foreach ($ra as $primary_key_value => $item_array) {
                $row_data = $this->grid_object->load_by_id($primary_key_value);
                $rs .= '<tr class="row3">';
                if (!$disable_mass_delete) {
                    $rs .= '<td><input type="checkbox" class="grid_check_one" value="' . $primary_key_value . '" /></td>';
                }
                $grid_counter = 0;
                foreach ($this->grid_items as $item_id => $item_name) {
                    $grid_counter++;
                    if ( $this->grid_items_render_objects[$item_name] ) {
                        $rs .= '<td>' .$this->grid_items_render_objects[$item_name]->fetch_template($item_name, $row_data).'</td>';
                    } elseif ($row_data[$item_name]['type'] == 'select_by_query') {
                        $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'structure') {
                        $rs .= '<td>' . $row_data[$item_name]['value_string'] . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'date') {
                        $rs .= '<td  >' . $row_data[$item_name]['value_string'] . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'select_box') {
                        $rs .= '<td  >' . $row_data[$item_name]['select_data'][$row_data[$item_name]['value']] . '</td>';
                    } elseif ($row_data[$item_name]['type'] == 'checkbox') {
                        $rs .= '<td>' . ($row_data[$item_name]['value'] == 1 ? '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_yes.png">' : '<img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/radio_no.png">') . '</td>';
                    } else {
                        if (is_array($row_data[$item_name]['value'])) {
                            $rs .= '<td>' . implode(';', $row_data[$item_name]['value']) . '</td>';
                        } else {
                            $rs .= '<td>' . $row_data[$item_name]['value'] . '</td>';
                        }
                    }
                }
                if (count($this->grid_controls) > 0) {
                    $rs .= '<td nowrap>';

                    foreach ($this->grid_controls as $control_id => $control_name) {
                        if ($control_name == 'view') {
                            if (!empty($control_params['view'])) {
                                $control_params_edit_string = $control_params['view'];
                            }
                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=view&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_edit_string . '" class="btn btn-info"><i class="icon-white icon-info-sign"></i></a> ';
                        }
                        if ($control_name == 'edit') {
                            if (!empty($control_params['edit'])) {
                                $control_params_edit_string = $control_params['edit'];
                            }
                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=edit&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_edit_string . '" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
                        }
                        if ($control_name == 'delete') {
                            if (!empty($control_params['delete'])) {
                                $control_params_delete_string = $control_params['delete'];
                            }

                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=delete&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_delete_string . '" onclick="if ( confirm(\''._e('Действительно хотите удалить запись?').'\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
                        }
                        if (is_array($control_name)) {
                            $rs .= ' <a href="?action=' . $this->grid_object->action . '&do=' . $control_name['name'] . '&' . $this->grid_object->primary_key . '=' . $primary_key_value . $control_params_oth_string . '" class="btn ' . ($control_name['btnclass'] != '' ? $control_name['btnclass'] : 'btn btn-warning') . '"><i class="icon-white ' . ($control_name['btnicon'] != '' ? $control_name['btnicon'] : 'icon-tasks') . '"></i>' . ($control_name['btntext'] != '' ? ' ' . $control_name['btntext'] : '') . '</a> ';
                        }
                    }
                    $rs .= '</td>';
                }

                $rs .= '</tr>';
            }
            if (!$disable_mass_delete) {
                $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '"><button alt="' . $this->grid_object->action . '" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> ' . Multilanguage::_('L_DELETE_CHECKED') . '</button></td></tr>';
            }
            $rs .= '<tr><td colspan="' . (count($this->grid_controls) + $grid_counter) . '" class="pager"><div align="center">' . $this->getPager() . '</div></td></tr>';

            $rs .= '</tbody>';

            $rs .= '</table>';
        } else {
            $rs .= '<br><br>'._e('Записей не найдено');
        }
        return $rs;
    }
    
    /*
     * Временная функция
     */
    function degradate_grid($grid){
        
        foreach($grid as $k=>$row_data){
            //$row_data = $this->grid_object->load_by_id($primary_key_value);
            $data = array();
            foreach ($row_data as $item_name => $v) {
                //$item_name=$item_id['name'];
                if ($row_data[$item_name]['type'] == 'select_by_query') {
                    $data[$item_name]['value'] = $row_data[$item_name]['value'];
                    $data[$item_name]['value_string'] = $row_data[$item_name]['value_string'];
                } elseif ($row_data[$item_name]['type'] == 'date') {
                    $data[$item_name] = $row_data[$item_name]['value'];
                } elseif ($row_data[$item_name]['type'] == 'uploadify_image') {
                    $data['image_array'] = $row_data[$item_name]['image_array'];
                } elseif ($row_data[$item_name]['type'] == 'select_box') {
                    if ($row_data[$item_name]['parameters']['multiselect'] == 1) {
                        $data[$item_name]['value'] = $row_data[$item_name]['value'];
                        $data[$item_name]['value_string'] = $row_data[$item_name]['value_string'];
                    } else {
                        $data[$item_name]['value'] = $row_data[$item_name]['value'];
                        $data[$item_name]['value_string'] = $row_data[$item_name]['select_data'][$row_data[$item_name]['value']];
                    }
                } elseif ($row_data[$item_name]['type'] == 'geodata') {
                    $data[$item_name] = implode(',', $row_data[$item_name]['value']);
                } else {
                    $data[$item_name] = $row_data[$item_name]['value'];
                }
            }
            $ret[$k] = $data;
        }
        return $ret;
    }

    function construct_grid_array() {
        $ra = array();
        $DBC = DBC::getInstance();

        $query = $this->grid_query . ' LIMIT ' . (($this->current_page - 1) * $this->per_page) . ', ' . $this->per_page;
        
        $stmt = $DBC->query($query);

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar[$this->grid_object->primary_key]] = $ar;
            }
        } else {
            $this->writeLog(__METHOD__.', query = '.$query.', query error = '.$DBC->getLastError());
        }

        $ret = array();
        $ids=array();
        foreach ($ra as $primary_key_value => $item_array) {
            $ids[$primary_key_value]=$primary_key_value;
        }
        if(!empty($ids)){
            $row_datas=$this->grid_object->load_by_id($ids);
        }
        
        if(!empty($row_datas)){
            foreach ($row_datas as $primary_key_value => $item_array) {
                $data = array();
                foreach ($this->grid_items as $item_id => $item_name) {
                    $data[$item_name] = $item_array[$item_name];
                }
                $ret[] = $data;
            }
        }
        //print_r($ret);
        return $ret;
        /*return $row_datas;
        if (!empty($ra)) {
            foreach ($ra as $primary_key_value => $item_array) {
                $row_data = $this->grid_object->load_by_id($primary_key_value);
                $data = array();
                foreach ($this->grid_items as $item_id => $item_name) {
                    if ($row_data[$item_name]['type'] == 'select_by_query') {
                        $data[$item_name]['value'] = $row_data[$item_name]['value'];
                        $data[$item_name]['value_string'] = $row_data[$item_name]['value_string'];
                    } elseif ($row_data[$item_name]['type'] == 'date') {
                        $data[$item_name] = $row_data[$item_name]['value'];
                    } elseif ($row_data[$item_name]['type'] == 'uploadify_image') {
                        $data['image_array'] = $this->get_image_array($this->get_action(), $this->get_table_name(), $this->grid_object->primary_key, (int) $primary_key_value);
                    } elseif ($row_data[$item_name]['type'] == 'select_box') {
                        if ($row_data[$item_name]['parameters']['multiselect'] == 1) {
                            $data[$item_name]['value'] = $row_data[$item_name]['value'];
                            $data[$item_name]['value_string'] = $row_data[$item_name]['value_string'];
                        } else {
                            $data[$item_name]['value'] = $row_data[$item_name]['value'];
                            $data[$item_name]['value_string'] = $row_data[$item_name]['select_data'][$row_data[$item_name]['value']];
                        }
                    } elseif ($row_data[$item_name]['type'] == 'geodata') {
                        $data[$item_name] = implode(',', $row_data[$item_name]['value']);
                    } else {
                        $data[$item_name] = $row_data[$item_name]['value'];
                    }
                }
                $ret[] = $data;
            }
        }

        return $ret;*/
    }

    function getPager() {
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($this->grid_query);
        $total = 0;
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $total++;
            }
        }

        $ret = $this->get_page_links_list($this->current_page, $total, $this->per_page, $this->pager_params);
        return $ret;
    }

    function setPagerParams($params = array()) {
        if (isset($params['per_page']) AND ( $params['per_page'] != 0)) {
            $this->per_page = (int) $params['per_page'];
        } else {
            $this->per_page = 10;
        }

        if (isset($params['page']) AND ( $params['page'] != 0)) {
            $this->current_page = (int) $params['page'];
        } else {
            $this->current_page = 1;
        }

        unset($params['per_page']);
        unset($params['page']);

        $this->pager_params = $params;
    }

}
