<div class="row-fluid">
	<div class="span7">
		<ol id="topic_tree" class="dd-list"></ol>
	</div>
	<div class="span5">
		<h5>Вспомогательные функции</h5>
	</div>
</div>

<div class="modal fade" id="structure_control_informer" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
		<h3 class="title"></h3>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
	</div>
</div>

<div class="modal fade" id="structure_clear_informer" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
		<h3 class="title">Очистка структуры</h3>
	</div>
	<div class="modal-body">
		<input type="hidden" name="topic_id" value="">
		<div class="row-fluid">
			<div class="span12">
				<h1>Я хочу:</h1>
			</div>
			<!-- <div class="span6">
				<h1>Входящие подпункты и объявления:</h1>
			</div> -->
		</div>
		<div class="row-fluid">
			<div class="span12">
				<form class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Удалить этот пункт</label>
						<div class="controls">
							<input type="radio" name="clear_option" value="delete_current" checked="checked">
						</div>
					</div>
				  
				  <div class="control-group">
				    <label class="control-label">Удалить всю ветку</label>
				    <div class="controls">
				      <input type="radio" name="clear_option" value="delete_branch">
				    </div>
				  </div>
				  
				  <div class="control-group">
				    <label class="control-label">Очистить входящие пункты</label>
				    <div class="controls">
				      <input type="radio" name="clear_option" value="delete_incoming">
				    </div>
				  </div>
				</form>
				<p>Объявление, входящие в удаляемые пункты структуры, будут перенесены на ближайший доступный верхний уровень структуры.</p>
			</div>
		</div>		
	</div>
	<div class="modal-footer">
		<button class="btn runaction">Выполнить</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
	</div>
</div>
<script>
var structure_grid_allow_drag={$structure_grid_allow_drag};
var use_topic_publish_status={$use_topic_publish_status};
</script>
{literal}
<script>
$(document).ready(function(){

	// включение перетягивания строк списка
    if(structure_grid_allow_drag==1){
		$(".dd-list").sortable({
			handle: ".dd-move",
			connectWith: '.dd-list',
			stop: function(e, ui){
				Structure_Control.saveSort(ui);
			}
		});
	}
	
	// загрузка дерева
	Structure_Control.load_tree();

	// разворот ветки
	$(document).on('click', 'button[data-action=expand]', function(e){
		var parent=$(this).parents('.dd-item').eq(0);
		var child_list=$(this).nextAll('.dd-list');
		if(child_list.length>0){
			child_list.show();
		}else{
			var id=parent.attr('data-id');
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: {action: 'topic_source', id:id},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json.length>0){
						var tree=$('<ol class="dd-list" style=""></ol>')
						for(var i in json){
							var liel=Structure_Control.format_element(json[i]);
							tree.append(liel);
						}
						if(structure_grid_allow_drag==1){
							tree.sortable({
								handle: ".dd-move",
								connectWith: '.dd-list',
								stop: function(e, ui){
									Structure_Control.saveSort(ui);
								}
							});
						}
						parent.append(tree);
					}
				}
			});
		}
		$(this).attr('data-action', 'collapse');
		e.preventDefault();
	});


	$(document).on('click', 'button[data-action=collapse]', function(e){
		var child_list=$(this).nextAll('.dd-list');
		if(child_list.length>0){
			child_list.hide();
			$(this).attr('data-action', 'expand');
		}
		e.preventDefault();
	});

	// удаление
	$(document).on('click', '.structure_control_delete_function', function(e){
		if ( confirm('Действительно хотите удалить запись?') ) {
			var parent = $(this).parents('.dd-item').eq(0);
			var id = parent.attr('data-id');
			$.ajax({
				url: estate_folder + '/js/ajax.php',
				data: {action: 'topic_delete', id: id},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json.status == 1){
						parent.remove();
					}else{
						var txt='<span class="label label-important">Удаление категории невозможно</span><hr />' + json.message;
						Structure_Control.showInformer('Удаление категории', txt);
					}
				}
			});
		}
		e.preventDefault();
	});

	// очистка
	$(document).on('click', '.structure_control_clear_function', function(e){
		var parent = $(this).parents('.dd-item').eq(0);
		var id = parent.attr('data-id');
		$('#structure_clear_informer [name=topic_id]').val(id);
		$('#structure_clear_informer').modal('show');
		e.preventDefault();
	});

	// смена статуса
	if(use_topic_publish_status){
		$(document).on('click', '.structure_control_publish_function', function(e){
			e.preventDefault();
			var parent = $(this).parents('.dd-item').eq(0);
			var id = parent.data('id');
			var status = (0 == parent.attr('data-status') ? 1 : 0);
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: {action: 'topic_publish', id: id, status: status},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json.status == 1){
						parent.attr('data-status', json.newstatus);
						if(json.newstatus == 1){
							parent.find('.dd-handle').removeClass('btn-warning');
						}else{
							parent.find('.dd-handle').addClass('btn-warning');
						}
					}else{
						console.log(json.message);
					}
				}
			});
		});
	}
	
	$('#structure_clear_informer .runaction').click(function(e){
		var informer = $(this).parents('#structure_clear_informer').eq(0);
		var clear_option = informer.find('[name=clear_option]:checked').val();
		var clear_advs = informer.find('[name=clear_advs]:checked').val();
		var id = informer.find('[name=topic_id]').val();
		if(clear_option != '' && clear_advs != '' && id != ''){
			$.ajax({
				url: estate_folder + '/js/ajax.php',
				data: {action: 'topic_delete', id: id, clear_option: clear_option, clear_advs: clear_advs},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json.status == 1){
						informer.modal('hide');
						Structure_Control.load_tree();
					}else{
						var txt='<span class="label label-important">Удаление категории невозможно</span><hr />'+json.message;
						Structure_Control.showInformer('Удаление категории', txt);
					}
				}
			});
		}
		e.preventDefault();
	});
});

Structure_Control={
	load_tree: function(){
		$('#topic_tree').html('');
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			type: 'post',
			data: {action: 'topic_source'},
			dataType: 'json',
			success: function(json){
				if(json.length>0){
					var tree=$('#topic_tree');
					for(var i in json){
						var liel=Structure_Control.format_element(json[i]);
						tree.append(liel);
					}
				}
			}
		});
	},
	format_element: function(json){
		var liel=$('<li class="dd-item" data-id="'+json.id+'" data-status="'+json.published+'"></li>');
		var buttons_block=$('<div class="pull-right action-buttons"></div>');

		if(use_topic_publish_status){
			var a=$('<a class="red structure_control_publish_function" href="'+estate_folder+'/admin/index.php?action=structure&do=publish&id='+json.id+'"></a>');
			a.append($('<i class="icon-off bigger-130"></i>'));
			buttons_block.append(a);
		}

        
		var a=$('<a class="red structure_control_clear_function" href="'+estate_folder+'/admin/index.php?action=structure&do=delete&id='+json.id+'"></a>');
		a.append($('<i class="icon-eraser bigger-130"></i>'));
		buttons_block.append(a);
		
		
        var a=$('<a class="blue structure_control_edit_function" href="'+estate_folder+'/admin/index.php?action=structure&do=edit&id='+json.id+'"></a>');
		a.append($('<i class="icon-pencil bigger-130"></i>'));
		buttons_block.append(a);
        
		var a=$('<a class="green structure_control_new_function" href="'+estate_folder+'/admin/index.php?action=structure&do=new&parent_id='+json.id+'"></a>');
		a.append($('<i class="icon-plus bigger-130"></i>'));
		buttons_block.append(a);
        
		var a=$('<a class="red structure_control_delete_function" href="'+estate_folder+'/admin/index.php?action=structure&do=delete&id='+json.id+'"></a>');
		a.append($('<i class="icon-trash bigger-130"></i>'));
		buttons_block.append(a);
        
		var tmp_element_html;
		tmp_element_html = '<div class="dd-handle ';
		if ( json.published == '0' ) {
			tmp_element_html += ' btn-warning  no-hover ';
		}
		tmp_element_html += '"></div>';
        
		var ddh=$(tmp_element_html);
		ddh.text(json.text+' [ID:'+json.id+'] ['+json.url+'] ['+json.order+']');
		ddh.append(buttons_block);
        
        var bt=$('<div class="dd-move"><i class="icon-move bigger-130"></i></div>');
        liel.append(bt);
        
		if(json.state=='closed'){
			var bt=$('<button data-action="expand" type="button" style="display: block;">Expand</button>');
			liel.append(bt);
		}
		liel.append(ddh);
		return liel;
	},
	showInformer: function(title, text){
		var informer=$('#structure_control_informer');
		informer.find('.title').html(title);
		informer.find('.modal-body').html(text);
		informer.modal('show');
	},
	saveSort: function(ui){
		var item_collection=ui.item;
		if(item_collection.length==0){
			return;
		}
		var item=$(item_collection[0]);
		var parent_list_element=item.parents('.dd-list').eq(0);
		var super_parent=parent_list_element.parents('.dd-item').eq(0);
		var parent_id=0;
		
		if(super_parent.length!=0){
			parent_id=parseInt(super_parent.attr('data-id'));
		}
		var neighbours=[];
		parent_list_element.children('.dd-item').each(function(i, el){
			neighbours.push(parseInt($(el).attr('data-id')));
		});
		
		if(neighbours.length>0){
			$.ajax({
                type: 'post',
                dataType: 'json',
				url: estate_folder+'/js/ajax.php',
				data: {action: 'save_topic_sort', parent_topic_id: parent_id, child_topics: neighbours},
                success: function(json){
                    
                }
			});
		}
	}
}
</script>
{/literal}