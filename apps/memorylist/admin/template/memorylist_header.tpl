{literal}
<script>
$(document).ready(function(){
	$('#MyWatchlistBtn').click(function(e){
		$.ajax({
			url: estate_folder+'/apps/userdata/js/ajax.php',
			data: {action: 'mywatchlist'},
			type: 'post',
			dataType: 'json',
			success: function(json){
				$('#my_watchlist .modal-body').html(json.list);
			}
		});
		
		e.preventDefault();
	});
	
	$(document).on('click', '.add_to_memory_list', function(e){
		var informer=$('#my_memorylist');
		var informer_form=$('#my_memorylist .modal-body form');
		var id=$(this).attr('data-itemid');
		informer_form.html('');
		
		var label=$('<input type="hidden" id="add_to_memory_id"></input>').val(id);
		informer_form.append(label);
		$.ajax({
			url: estate_folder+'/apps/memorylist/js/ajax.php',
			data: {action: 'memorylist', doaction: 'getlists'},
			type: 'post',
			dataType: 'json',
			success: function(json){
				if(json.length>0){
					
					var label=$('<label></label>').text('Выберите список');
					informer_form.append(label);
					var select=$('<select id="add_to_memory_listid"></select>');
					for(var i in json){
						var option=$('<option></option>');
						option.attr('value',json[i].id);
						option.text(json[i].title);
						select.append(option);
					}
					informer_form.append(select);
				}
				var label=$('<label></label>').text('или введите имя нового');
				informer_form.append(label);
				var option=$('<input type="text" id="add_to_memory_listtitle"></input>');
				informer_form.append(option);
			}
		});
		
		$('#my_memorylist').modal('show');
		e.preventDefault();
	});
	
	$('#my_memorylist #my_memorylist_add').click(function(e){
		var form=$('#my_memorylist form');
		var listid=form.find('#add_to_memory_listid').val();
		var listtitle=form.find('#add_to_memory_listtitle').val();
		var itemid=form.find('#add_to_memory_id').val();
		$.ajax({
			url: estate_folder+'/apps/memorylist/js/ajax.php',
			data: {action: 'memorylist', doaction: 'add', listid: listid, listtitle: listtitle, itemid: itemid},
			type: 'post',
			dataType: 'json',
			success: function(json){
				var mlc=$('.memorylist_controls_'+itemid);
				var btn=$('<a href="#" data-listid="'+json.memorylist_id+'" data-itemid="'+itemid+'" class="remove_from_memory_list btn btn-mini"></a>');
				btn.text(json.title+' ');
				btn.append($('<i class="icon icon-remove"></i>'));
				mlc.prepend($('<br />'));
				mlc.prepend(btn);
				$('#my_memorylist').modal('hide');
			}
		});
		e.preventDefault();
	});
	
	$(document).on('click', '.remove_from_memory_list', function(e){
		var itemid=$(this).attr('data-itemid');
		var listid=$(this).attr('data-listid');
		var _this=$(this);
		$.ajax({
			url: estate_folder+'/apps/memorylist/js/ajax.php',
			data: {action: 'memorylist', doaction: 'remove', listid: listid, itemid: itemid},
			type: 'post',
			dataType: 'json',
			success: function(){
				_this.remove();
			}
		});
		
		e.preventDefault();
	});
	
	$(document).on('click', '.ml_remove_from_memory_list', function(e){
		var itemid=$(this).attr('data-itemid');
		var listid=$(this).attr('data-listid');
		var _this=$(this);
		$.ajax({
			url: estate_folder+'/apps/memorylist/js/ajax.php',
			data: {action: 'memorylist', doaction: 'remove', listid: listid, itemid: itemid},
			type: 'post',
			dataType: 'json',
			success: function(){
				_this.parents('tr').eq(0).remove();
			}
		});
		
		e.preventDefault();
	});
});
	
	
</script>
{/literal}
<div class="modal fade" id="my_watchlist" tabindex="-1" role="dialog" aria-labelledby="my_watchlist" aria-hidden="true">
<div class="modal-dialog" role="document">
    <div class="modal-content">
    
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
		<h3 id="myModalLabel">Подключенные категории</h3>
	</div>
	<div class="modal-body">
    Тут выводим список категорий из watchlistmanager
	</div>
	<div class="modal-footer">
		<a class="btn let_me_login" href="{$estate_folder}/account/watchlist/?do=new">Новая заявка</a>
		<button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
	</div>
    </div>
</div>
</div>

<div class="modal fade" id="my_memorylist" tabindex="-1" role="dialog" aria-labelledby="my_memorylist" aria-hidden="true">
<div class="modal-dialog" role="document">
    <div class="modal-content">
    
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
		<h3 id="myModalLabel">Добавление в список</h3>
	</div>
	<div class="modal-body">
    <form>
    
    </form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" id="my_memorylist_add">Сохранить</button>
		<button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
	</div>
    </div>
</div>
</div>

