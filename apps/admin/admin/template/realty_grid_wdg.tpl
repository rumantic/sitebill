{literal}
<script type="text/javascript">
$(document).ready(function(){

	$('.go_up').click(function(){
		var id=$(this).attr('alt');
		var tr=$(this).parents('tr').eq(0);
		$.getJSON(estate_folder+'/js/ajax.php?action=go_up&id='+id,{},function(data){
			if(data.response.body!=''){
				tr.find('td').eq(1).html(data.response.body);
				tr.parents('table').eq(0).find('tr.row3').eq(0).before(tr);
			}
		});
	});


	$('#search_toggle').click(function(){
		$('#search_form_block').toggle();
        $('#srch_date_from').datepicker({dateFormat:'yy-mm-dd'});
        $('#srch_date_to').datepicker({dateFormat:'yy-mm-dd'});
		
	});
	$('#reset').click(function(){
		$(this).parents('form').eq(0).find('input[type=text]').each(function(){
			this.value='';
		});
		$(this).parents('form').submit();
	});
	
	
	$('#grid_control_panel select[name=cp_optype]').change(function(){
		
		var operation=$(this).val();
		if(operation!=''){
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: {action: 'get_form_element',element:operation},
				dataType: 'html',
				success: function(html){
					$('#grid_control_panel_content').html(html);
					$('#grid_control_panel button#run').show();
				}
			});
		}
	});
	/*
	$('#grid_control_panel button#run').click(function(){
		
		var cp=$('#grid_control_panel');
		var action=$(this).attr('alt');
		var operation=cp.find('select[name=cp_optype]').val();
		
		if(operation!=''){
			var field=null;
			if(cp.find('#grid_control_panel_content select').length!=0){
				var field=cp.find('#grid_control_panel_content select');
			}else if(cp.find('#grid_control_panel_content input').length!=0){
				var field=cp.find('#grid_control_panel_content input');
				if(field.attr('type')=='checkbox' && field.is(':checked')){
					field.val('1');
				}
				
			}
			if(field!==null){
				var cat_id=field.val();
			}
			var checked=[];
			$('.grid_check_one:checked').each(function(){
				checked.push(this.value);
			});
			if(checked.length>0){
				window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=change_param&new_param_value='+cat_id+'&param_name='+operation+'&ids='+checked.join(','));
			}
			
		}
		return false;
	});
	*/
	$('.batch_update').click(function(){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push($(this).val());
		});
		window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=batch_update&batch_ids='+ids.join(','));
	});
	
	$('.duplicate').click(function(){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push($(this).val());
		});
		window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=duplicate&ids='+ids.join(','));
	});
	$('.tooltipe_block').popover({trigger: 'hover'});
	
});
	
</script>
{/literal}
<div class="navbar">
  <div class="navbar-inner">
    <div class="container">

    <div class="nav pull-right">
		
{if $admin ne ''}
<div align="right"><a href="#search" id="search_toggle" class="btn btn-info"><i class="icon-white icon-search"></i> {$L_ADVSEARCH}</a></div>

<div id="search_form_block" {if $smarty.request.submit_search_form_block eq ''}style="display:none;"{/if} class="spacer-top">
<form action="?action=data method="get">
<table>
<tr><td>{$L_WORD}</td><td> <input type="text" name="srch_word" value="{$smarty.request.srch_word}" /></td></tr>
<tr><td>{$L_PHONE}</td><td> <input type="text" name="srch_phone" value="{$smarty.request.srch_phone}" /></td></tr>
<tr><td>{$L_ID}</td><td> <input type="text" name="srch_id" value="{$smarty.request.srch_id}" /></td></tr>
{if $show_uniq_id}
	<tr><td>UNIQ_ID</td><td> <input type="text" name="uniq_id" value="{$smarty.request.uniq_id}" /></td></tr>
{/if}
<tr><td>{$L_DATE} {$L_FROM}</td><td> <input type="text" name="srch_date_from" id="srch_date_from" value="{$smarty.request.srch_date_from}" /></td></tr>
<tr><td>{$L_DATE} {$L_TO}</td><td> <input type="text" name="srch_date_to" id="srch_date_to" value="{$smarty.request.srch_date_to}" /></td></tr>
<tr><td></td><td align="right">
<input type="submit" name="submit_search_form_block" value="{$L_GO_FIND}" class="btn btn-primary" />
<input type="button" id="reset" value="{$L_RESET}" class="btn btn-warning" /></td></tr>
</table>
</form>
</div>

</div>


{/if}
		
</div>
</div>
</div>

<table class="table table-hover" cellspacing="2" cellpadding="2">
	<tr  class="row_head">
		<td class="row_title"><input type="checkbox" class="grid_check_all" /></td>
	{foreach from=$grid_data_columns item=grid_data_column}	
		<td class="row_title">{$grid_data_column}</td>
	{/foreach}
	{if $admin !=''}
		<td class="row_title"></td>
	{/if}	
	</tr>
	{section name=i loop=$grid_items}
	<tr valign="top"{if $grid_items[i].hot.value}class="row3hot"{else}class="row3"{/if}>
	<td><input type="checkbox" class="grid_check_one" value="{$grid_items[i].id.value}" /></td>
	{foreach from=$grid_data_columns item=grid_data_column}	
		{if $grid_items[i][$grid_data_column].type=='uploadify_image'}
		<td>{$grid_items[i][$grid_data_column].image_array|count}</td>
		{else}
		<td>{$grid_items[i][$grid_data_column].value_string}</td>
		{/if}
	{/foreach}
	{if $admin !=''}
			<td nowrap>
		{if isset($show_up_icon) && $show_up_icon}
		
		<a class="btn btn-warning go_up" alt="{$grid_items[i].id.value}" href="#grow_up"><i class="icon-white icon-circle-arrow-up"></i></a>
		{/if}
			
			<a href="{$estate_folder_control}?do=edit&id={$grid_items[i].id.value}" class="btn btn-info"><i class="icon-white icon-pencil"></i></a>
			<a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_items[i].id.value}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a>
			
			</td>
			{/if}
	</tr>
	{/section}
	<tr>
		<td colspan="{$grid_data_columns|count}">
		<button alt="data" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> {$L_DELETE_CHECKED}</button>
		<button alt="data" class="batch_update btn btn-inverse"><i class="icon-white icon-th"></i> Пакетная обработка <sup>(beta)</sup></button> 
		<button alt="data" class="duplicate btn btn-inverse"><i class="icon-white icon-th"></i> Дублировать <sup>(beta)</sup></button>
		</td>
	</tr>

	{if $pager != ''}
	<tr>
		<td colspan="{$grid_data_columns|count}" class="pager"><div align="center">{$pager}</div></td>
	</tr>
	{/if}
</table>