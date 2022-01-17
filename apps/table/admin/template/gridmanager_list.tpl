<form class="form-horizontal" method="post" action="{$estate_folder}/admin/?action=table&section=gridmanager">
	<div class="dd dd-draghandle">
		<ol class="dd-list" id="grid_list2">
			{foreach from=$fields key=fkey item=field}
			<li class="dd-item dd2-item {if $field.checked == 1}checked-item{/if}" data-id="{$field.id}">
				<div class="dd-handle dd2-handle">
					<i class="normal-icon icon-move pink bigger-130"></i>
					<i class="drag-icon icon-move bigger-125"></i>
				</div>
				<div class="dd2-content">
					{$field.title} ({$fkey})
					<div class="pull-right">
						<label>
							<input name="field[{$fkey}]" type="checkbox" class="ace control-activity"{if $field.checked == 1} checked="checked"{/if}>
							<span class="lbl"> </span>
						</label>
					</div>
				</div>
			</li>
			{/foreach}
		</ol>
	</div>
	<input type="hidden" name="action" value="table">
	<input type="hidden" name="section" value="gridmanager">
	<div class="control-group">
		<div class="controls">
			<button type="submit" name="submit" class="btn">Сохранить</button>
		</div>
	</div>
</form>

{literal}
<script>
$(document).ready(function(){
	$('#grid_list2 .control-activity').change(function () {
		var parent = $(this).parents('.dd-item').eq(0);
		parent.toggleClass('checked-item');

		let state = 0;
		if(parent.hasClass('checked-item')){
			state = 1;
		}
		// Отправка сохранения состояния
	});

	$('#grid_list2').sortable({
		handle: ".dd-handle",
		/*stop: function (event, ui) {
			var parent = $(ui.item).parents('.dd-list').eq(0);

			if (parent.length == 1) {
				var childs = parent.find('.dd-item');
				if (childs.length > 0) {
					var ids = [];
					childs.each(function () {
						if($(this).hasClass('checked-item')){
							ids.push($(this).data('id'));
						}
					});
					var data = {};
					data.action = 'gridmanager_reorder_columns';
					data.ids = ids;
					data._app='table';
					$.ajax({
						url: estate_folder + '/js/ajax.php',
						type: 'POST',
						dataType: 'text',
						data: data,
						success: function (data) {
							//alert('Сортировка сохранена');
						}
					});
					console.log(ids);
				}

			}
		}*/
	}).disableSelection();
});
</script>

	<style>
		.dd-item .dd2-content {
			color: #ababab;
		}
		.dd-item.checked-item .dd2-content {
			color: #7c9eb2;
		}

	</style>

{/literal}