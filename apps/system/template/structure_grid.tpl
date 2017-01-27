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
			<!--<div class="span6">
				<form class="form-horizontal">
				  <div class="control-group">
				    <label class="control-label">Удалить</label>
				    <div class="controls">
				      <input type="radio" name="clear_advs" value="delete_all">
				    </div>
				  </div>
				  <div class="control-group">
				    <label class="control-label">Перенести на уровень выше</label>
				    <div class="controls">
				      <input type="radio" name="clear_advs" checked="checked" value="move_up">
				    </div>
				  </div>
				  
				</form>
			</div>  -->
		</div>
		
		
	</div>
	<div class="modal-footer">
		<button class="btn runaction">Выполнить</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
	</div>
</div>
<script>
var structure_grid_allow_drag={$structure_grid_allow_drag};
</script>
{literal}
<script>
$(document).ready(function(){
	if(structure_grid_allow_drag==1){
		$(".dd-list").sortable({
			handle: ".dd-handle",
			connectWith: '.dd-list',
			stop: function(e, ui){
				Structure_Control.saveSort(ui);
			}
		});
	}
	
	
	Structure_Control.load_tree();
	/*
	$.ajax({
		url: estate_folder+'/js/ajax.php?action=topic_source',
		type: 'post',
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
	});*/
	
	$(document).on('click', 'button[data-action=expand]', function(e){
		var parent=$(this).parents('.dd-item').eq(0);
		var child_list=$(this).nextAll('.dd-list');
		if(child_list.length>0){
			child_list.show();
		}else{
			var id=parent.attr('data-id');
			$.ajax({
				url: estate_folder+'/js/ajax.php?action=topic_source',
				data: {id:id},
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
								handle: ".dd-handle",
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
	
	$(document).on('click', '.structure_control_delete_function', function(e){
		if ( confirm('Действительно хотите удалить запись?') ) {
			var parent=$(this).parents('.dd-item').eq(0);
			var id=parent.attr('data-id');
			$.ajax({
				url: estate_folder+'/js/ajax.php?action=topic_delete',
				data: {id:id},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json.status=='ok'){
						parent.remove();
					}else{
						var txt='<span class="label label-important">Удаление категории невозможно</span><hr />'+json.message;
						Structure_Control.showInformer('Удаление категории', txt);
					}
				}
			});
		}
		e.preventDefault();
	});
	
	$(document).on('click', '.structure_control_clear_function', function(e){
		var parent=$(this).parents('.dd-item').eq(0);
		var id=parent.attr('data-id');
		$('#structure_clear_informer [name=topic_id]').val(id);
		$('#structure_clear_informer').modal('show');
		e.preventDefault();
	});
	
	$('#structure_clear_informer .runaction').click(function(e){
		var informer=$(this).parents('#structure_clear_informer').eq(0);
		var clear_option=informer.find('[name=clear_option]:checked').val();
		var clear_advs=informer.find('[name=clear_advs]:checked').val();
		var id=informer.find('[name=topic_id]').val();
		console.log(clear_option);
		console.log(clear_advs);
		console.log(id);
		if(clear_option!='' && clear_advs!='' && id!=''){
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: {action: 'topic_delete', id: id, clear_option: clear_option, clear_advs: clear_advs},
				type: 'post',
				dataType: 'json',
				success: function(json){
					if(json.status=='ok'){
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
	/*$('#topic_tree').tree({
		onClick: function(node){
			//document.location.href='/admin/index.php?action=company&topic_id='+node.id;
		},
		dnd: true,
		url: "{/literal}{$estate_folder}{literal}/js/ajax.php?action=topic_source",
		onDrop: function(target, source, point){
			var id=source.id;
			var elt=$('#topic_tree').find('[node-id='+id+']').parents('li').eq(0);
			var parentul=elt.parents('ul').eq(0);
			var parent_id=parentul.prev('div.tree-node').eq(0).attr('node-id');
			var ids=[];
			
			if(parent_id === undefined){
				parent_id=0;
				
				$('#topic_tree > li > div.tree-node').each(function(){
					ids.push($(this).attr('node-id'));
				});
				console.log(ids);
			}else{
				var es=$('#topic_tree').find('[node-id='+parent_id+']').parents('li').eq(0);
				es.find('ul > li > div.tree-node').each(function(){
					ids.push($(this).attr('node-id'));
				});
			}
			
			if(ids.length>0){
				$.ajax({
					url: '{/literal}{$estate_folder}{literal}/js/ajax.php?action=save_topic_sort',
					data: {parent_topic_id: parent_id, child_topics: ids.join(',')}
				});
			}
		},
		onLoadSuccess: function(){
			$('#topic_tree .tree-node').each(function(){
				var nodeid=$(this).attr('node-id');
				if($(this).find('span.controls').length==0){
					var span=$('<span class="controls"></span>');
					span.append('<a href="?action=structure&do=new&parent_id='+nodeid+'" class="btn btn-info btn-mini"><i class="icon-white icon-plus"></i></a>');
					span.append('&nbsp;');
					span.append('<a href="?action=structure&do=edit&id='+nodeid+'" class="btn btn-info btn-mini"><i class="icon-white icon-pencil"></i></a>');
					span.append('&nbsp;');
					span.append('<a href="?action=structure&do=delete&id='+nodeid+'" onclick="if ( confirm(\'Действительно хотите удалить категорию?\') ) {return true;} else {return false;}" class="btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>');
					$(this).append(span);
				}
			});
		}
	});*/
});

Structure_Control={
	load_tree: function(){
		$('#topic_tree').html('');
		$.ajax({
			url: estate_folder+'/js/ajax.php?action=topic_source',
			type: 'post',
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
		var liel=$('<li class="dd-item" data-id="'+json.id+'"></li>');
		var buttons_block=$('<div class="pull-right action-buttons"></div>');
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
			tmp_element_html += ' btn-warning ';
		}
		tmp_element_html += '"></div>';
		var ddh=$(tmp_element_html);
		ddh.text(json.text+' [ID:'+json.id+'] ['+json.url+']');
		ddh.append(buttons_block);
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
				url: estate_folder+'/js/ajax.php?action=save_topic_sort',
				data: {parent_topic_id: parent_id, child_topics: neighbours.join(',')}
			});
		}
	}
}
</script>
{/literal}