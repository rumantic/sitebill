{literal}
<style>
.disabled td {
color: rgb(201, 201, 201);
font-style: italic;
}
</style>
<script>
$(document).ready(function(){
	$('.edit_total').change(function(){
		var group_id=$(this).attr('alt');
		if($(this).is(':checked')){
			$('.rules_table input[type=checkbox].edit_'+group_id).prop('checked', true);
		}else{
			$('.rules_table input[type=checkbox].edit_'+group_id).prop('checked', false);
		}
	});
	
	$('.view_total').change(function(){
		var group_id=$(this).attr('alt');
		if($(this).is(':checked')){
			$('.rules_table input[type=checkbox].view_'+group_id).prop('checked', true);
		}else{
			$('.rules_table input[type=checkbox].view_'+group_id).prop('checked', false);
		}
	});
	$('.save_rules').click(function(){
		var rules=[];
		$('.rules_table .rule').each(function(){
			var _this=$(this);
			var column_id=_this.attr('data-columnsid');
			var group_id=$(this).attr('data-groupid');
			var rule_type=_this.attr('data-ruletype');
			var name=_this.attr('name');
			//console.log(group_id);
			
			if(_this.is(':checked')){
				//var value=1;
				rules.push(name+'=1');
			}else{
				//var value=0;
				rules.push(name+'=0');
			}
			
			
		});
		
		var params=rules.join('&')
		$.ajax({
			url: estate_folder+'/apps/table/js/ajax.php?action=save_group_rules',
			type: 'post',
			data: params
		});
	});
	
});
</script>
{/literal}
<table class="table rules_table">
<thead>
	<tr>
		<th>ID</th>
		<th>Название</th>
		<th>Системное</th>
		<th>Тип</th>
		{foreach from=$groups item=group}
		<th colspan="2">{$group.name}</th>
		{/foreach}
	</tr>
	<tr>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		{foreach from=$groups item=group}
		<th><input type="checkbox" alt="{$group.group_id}" class="edit_total" /> Edit</th>
		<th><input type="checkbox" alt="{$group.group_id}" class="view_total" /> View</th>
		{/foreach}
	</tr>
</thead>
{foreach from=$columns item=column}
<tr{if $column.active==0} class="disabled"{/if}>
	<td>{$column.columns_id}</td>
	<td>{$column.title}</td>
	<td>{$column.name}</td>
	<td>{$column.type}</td>
	{foreach from=$groups item=group}
	<td><input type="checkbox" data-groupid="{$group.group_id}" data-columnsid="{$column.columns_id}" data-ruletype="edit" class="edit_{$group.group_id} rule" name="rule[edit][{$column.columns_id}][{$group.group_id}]" value=""{if isset($rules.edit[$column.columns_id][$group.group_id]) && $rules.edit[$column.columns_id][$group.group_id]==1} checked="checked"{/if} /></td>
	<td><input type="checkbox" data-groupid="{$group.group_id}" data-columnsid="{$column.columns_id}" data-ruletype="view" class="view_{$group.group_id} rule" name="rule[view][{$column.columns_id}][{$group.group_id}]" value=""{if isset($rules.view[$column.columns_id][$group.group_id]) && $rules.view[$column.columns_id][$group.group_id]==1} checked="checked"{/if} /></td>
	{/foreach}
	
</tr>
{/foreach}
</table>
<button class="save_rules">SAVE</button>