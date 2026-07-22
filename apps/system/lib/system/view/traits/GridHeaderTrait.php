<?php
/**
 * GridHeaderTrait — Header scripts/CSS, tag inputs, memory header for Common_Grid.
 *
 * Manages: get_pre_header, get_tags_input, vue_tags_input, classic_tags_input,
 * get_memory_header, compile_memory_control, extended_items.
 */
trait GridHeaderTrait
{
    function extended_items()
    {
        //echo $this->get_action();
        $this->template->assign('action', $this->get_action());
        $this->template->assign('total_count', $this->get_total_count());
        $this->grid_object->set_extended_items($this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/grid/extended_items_row.tpl'));
        return '';
        //return 'extended items';
    }

    function get_pre_header()
    {
        $rs = '';
        if (is_array($this->grid_controls)) {
            if (in_array('memorylist', $this->grid_controls)) {
                $rs .= $this->get_memory_header();
            }
        }

        $rs .= '
        <script src="' . SITEBILL_MAIN_URL . '/apps/api/js/legacy_api.js"></script>
        
<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/fotorama/fotorama.css"/>
<script src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/fotorama/fotorama.js"></script>
        
					
<script type="text/javascript">
var fast_previews=[];
var column_values_for_tags = [];
var datastr={};
        function setFotoramaWrapper (id) {
            let photoslider_id = \'.photoslider\' + id;
            $(photoslider_id).on(\'fotorama:fullscreenenter fotorama:fullscreenexit\', function (e, fotorama) {
                if (e.type === \'fotorama:fullscreenenter\') {
                    fotorama.setOptions({fit: \'contain\',nav: "thumbs", arrows: \'always\'});
                } else {
                    fotorama.setOptions({fit: \'cover\',nav: false, arrows: true});
                }
            }).fotorama({
                arrows: true,
                nav: false,
                allowfullscreen: true,
                width: "150px",
                ratio: "800/500",
                fit: "cover",
            });
        }

$(document).ready(function(){
    
    $(\'.colorboxed\').each(function (item) {
        setFotoramaWrapper($(this).data(\'cbxid\'));
    });

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
    
    $(\'.tags-clear\').click(function(e){
        e.preventDefault();
        $.ajax({url: \'' . SITEBILL_MAIN_URL . '/js/ajax.php?action=get_tags&do=clear&model_name=' . $this->grid_object->table_name . '\'}).done(function(){location.reload();});
    });
    
            $(\'.fast_preview\').click(function () {
                var id = $(this).data(\'id\');
                if (fast_previews[id] === undefined) {
                    $.ajax({
                        url: estate_folder + \'/js/ajax.php?action=fast_preview_public&id=\' + id,
                        dataType: \'json\',
                        success: function (html) {
                            fast_previews[id] = html;
                            $(\'#fast_preview_modal\').find(\'.modal-body\').html(html.data);
                            $(\'#fast_preview_modal\').find(\'.newwin\').attr(\'href\', html.href);
                            $(\'#fast_preview_modal\').modal(\'show\');
                        }
                    });
                } else {
                    $(\'#fast_preview_modal\').find(\'.modal-body\').html(fast_previews[id].data);
                    $(\'#fast_preview_modal\').find(\'.newwin\').attr(\'href\', fast_previews[id].href);
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
			var txt=\'' . _e('не задано') . '\';
			
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
				$.ajax({type: "POST", url: "' . SITEBILL_MAIN_URL . '/js/ajax.php?action=get_tags&do=set&model_name=' . $this->grid_object->table_name . '", data: {tags_array:datastr}}).done(function(result_items){location.reload();});
			});
			
			_this.find(\'.clear\').click(function(e){
				e.preventDefault();
				if(typeof datastr[name] != \'undefined\'){
					tag_array=datastr[name];
					delete datastr[name];
				}
				$.ajax({type: "POST", url: \'' . SITEBILL_MAIN_URL . '/js/ajax.php?action=get_tags&do=set&model_name=' . $this->grid_object->table_name . '\', data: {tags_array:datastr}}).done(function(result_items){location.reload();});
			});
			
		});
});


</script>';
        if ($this->batchUpdate) {
            $rs .= '<script>$(document).ready(function(){
      $(\'.batch_update\').click(function () {
        var ids = [];
        var action = $(this).attr(\'alt\');
        $(this).parents(\'table\').eq(0).find(\'input.grid_check_one:checked\').each(function () {
            ids.push($(this).val());
        });
        if(ids.length>0){
            window.location.replace(\'' . $this->batchUpdateUrl . '?action=\' + action + \'&do=batch_update&batch_ids=\' + ids.join(\',\'));
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
        <h3>' . _e('Быстрый просмотр') . ' <a target="_blank" class="btn btn-success newwin" href="#">' . _e('открыть в новом окне') . '</a></h3>
    </div>
    <div class="modal-body"></div>
    <div class="modal-footer"></div>
</div>
</div>
</div>';
        return $rs;
    }

    function get_tags_input($item_name)
    {
        if ($this->getConfigValue('use_vue')) {
            return $this->vue_tags_input($this->grid_object->table_name, $item_name);
        } else {
            return $this->classic_tags_input($item_name);
        }
    }

    function vue_tags_input($model_name, $item_name)
    {
        if (
            isset($_SESSION['model_tags']) &&
            isset($_SESSION['model_tags'][$model_name]) &&
            isset($_SESSION['model_tags'][$model_name]['tags_array']) &&
            isset($_SESSION['model_tags'][$model_name]['tags_array'][$item_name]) &&
            is_array($_SESSION['model_tags'][$model_name]['tags_array'][$item_name])
        ) {
            $input_tags_array = json_encode($_SESSION['model_tags'][$model_name]['tags_array'][$item_name]);
        } else {
            $input_tags_array = '';
        }

        return "<tags-input column_name='$item_name' model_name='$model_name' input_tags_array='$input_tags_array'></tags-input>";
    }

    function classic_tags_input($item_name)
    {
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
								url: estate_folder+'/js/ajax.php?action=get_tags&column_name='+column_name+'&model_name=" . $this->grid_object->table_name . "&term='+query+''
				        	}).done(function(result_items){
								process(result_items);
							});
						}
				   });
					var tag_obj = tag_input.data('tag');";


        if (
            isset($_SESSION['model_tags']) &&
            is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array']) &&
            is_array($_SESSION['model_tags'][$this->grid_object->table_name]['tags_array'][$item_name])
        ) {
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
			   		var body = {tags_array:datastr};
			        $.ajax(
			            {
			                type: 'POST',
			                url: estate_folder+'/js/ajax.php?action=get_tags&model_name=" . $this->grid_object->table_name . "&do=set',
			                data: body
			            }
			        )
			        .done(function(result_items){
			        	location.reload();
			           //process(result_items);
			        });
				})
				tag_input.on('removed', function (e, value) {
                    var val = (Array.isArray(value) ? value[0] : value);
			   		var item_index = datastr[$(this).attr('name')].indexOf(val);
			   		datastr[$(this).attr('name')].splice(item_index, 1);
			   		var body = {tags_array:datastr};
			        $.ajax(
			            {
			                type: 'POST',
			                url: estate_folder+'/js/ajax.php?action=get_tags&model_name=" . $this->grid_object->table_name . "&do=set',
			                data: body
			            }
			        )
			        .done(function(result_items){
			        	location.reload();
			           //process(result_items);
			        });
				})
    						
    	
			});
    						
			</script>
    	
    	
                                        ";
        return $tags_input;
    }

    private function compile_memory_control($id)
    {
        $this->template->assign('id', $id);
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/template/memorylist_item_control.tpl');
    }

    private function get_memory_header()
    {
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
}
