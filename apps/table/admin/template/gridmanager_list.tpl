<form class="form-horizontal" method="post" action="{$estate_folder}/admin/?action=table&section=gridmanager">
<div id="grid_list">
{foreach from=$fields key=fkey item=field}
<div class="control-group">
	<div class="controls">
		<label class="checkbox">
			<input type="checkbox" name="field[{$fkey}]"{if $field.checked==1} checked="checked"{/if}> {$field.title}
		</label>
	</div>
</div>
{/foreach}
	<div class="control-group">
		<div class="controls">
			<button type="submit" name="submit" class="btn">Сохранить</button>
		</div>
	</div>
</div>
</form>
{literal}
<script>
$(document).ready(function(){
	$('#grid_list').sortable();
});
</script>

{/literal}