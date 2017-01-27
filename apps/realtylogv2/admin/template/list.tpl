<form class="form-horizontal" id="findids">
	<div class="control-group">
		<label class="control-label">Искать по ID</label>

		<div class="controls">
			<input type="text" value="{$smarty.get.ids}" id="ids">
			<button class="btn btn-info btn-small" type="submit">Найти</button>
		</div>
	</div>
</form>


<div class="widget-box transparent" id="recent-box">
	<div class="widget-header">
		<h4 class="lighter smaller">
			<i class="icon-rss orange"></i>
			Логи
		</h4>
	
		<div class="widget-toolbar no-border">
			<ul class="nav nav-tabs" id="log-tab">
				<li{if $type===''} class="active"{/if}>
					<a href="{$estate_folder}/admin/?action=realtylogv2&page=1">Все</a>
				</li>
	
				<li{if $type==='delete'} class="active"{/if}>
					<a href="{$estate_folder}/admin/?action=realtylogv2&page=1&type=delete">Удаления</a>
				</li>
			</ul>
		</div>
	</div>

{if $error ne ''}
	<p class="error">{$error}</p>
{/if}

{if $success ne ''}
	<p class="text-success"><i class="ace-icon fa fa-check bigger-110 green"></i> {$success}</p>
{/if}

{if $classic_view==1}

<table class="table table-striped table-bordered table-hover dataTable" >
	<thead>
	<tr>
		<th></th>
		<th>Log ID</th>
		<th>Редактор</th>
		<th>ID</th>
		<th>Действие</th>
		<th>Дата</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
{foreach from=$data_array item=item key=log_id}
<tr class="row3"><td><input type="checkbox" class="grid_check_one" value="{$log_id}"></td>

<td>{$item.realtylog_id}</td>
<td>{$item.editor_id.value_string}</td>
<td>{$item.id}</td>
<td>{$item.action}</td>
<td>{$item.log_date}</td>
<td>
<a class="btn btn-success" title="Просмотр" target="_blank" href="{$estate_folder}/admin/?action=realtylogv2&do=view&type={$smarty.request.type}&page={$smarty.request.page}&id={$item.realtylog_id}&data_id={$item.id.value}">
			<i class="ace-icon fa fa-eye"></i>
		</a>
		<a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder}/admin/?action=realtylogv2&do=remove_log&type={$smarty.request.type}&page={$smarty.request.page}&log_id={$item.realtylog_id}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a>
</td>
</tr>
{/foreach}
</tbody>
</table>
<div class="pagination">
{$data_pager}
</div>

{else}


<table class="table table-striped table-bordered table-hover dataTable" >
	<thead>
	<tr>
		<th>ID</th>
		<th>Тип</th>
		<th>Адрес</th>
		<th>Цена</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	
	{foreach from=$data_array item=item key=log_id}
	
	<tr>
		<td>{$item.id.value}</td>
		<td>{$item.topic_id.value}
		</td>
		<td> 
		{if $item.city_id.value_string != ''}{$item.city_id.value_string}, {/if}
		{if $item.street_id.value_string != ''}{$item.street_id.value_string} {/if}
		{if $item.number.value != ''}д. {$item.number.value} {/if}
		</td>
		<td>{$item.price.value}</td>
		<td>
		{if $data_array[$log_id].action == 'delete'}
		<a class="btn btn-warning" title="Восстановить" href="{$estate_folder}/admin/?action=realtylogv2&do=restore&type={$smarty.request.type}&page={$smarty.request.page}&id={$log_id}&data_id={$item.id.value}">
			<i class="ace-icon fa fa-undo"></i>
		</a>
		{/if}
		<a class="btn btn-success" title="Просмотр" target="_blank" href="{$estate_folder}/admin/?action=realtylogv2&do=view&type={$smarty.request.type}&page={$smarty.request.page}&id={$log_id}&data_id={$item.id.value}">
			<i class="ace-icon fa fa-eye"></i>
		</a>
		<a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder}/admin/?action=realtylogv2&do=remove_log&type={$smarty.request.type}&page={$smarty.request.page}&log_id={$log_id}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a>
		</td>
	</tr>
	{/foreach}
	</tbody>
</table>

{if $pager|count>0}
<div class="pagination">
<ul>
{foreach from=$pager item=page}
<li>{$page}</li>
{/foreach}
</ul>
</div>
{/if}

{if 1==0}
<div class="widget-body">
		<div class="widget-main padding-4">
			<div class="tab-content padding-8 overflow-visible">
				<ul id="tasks" class="item-list ui-sortable">
					{foreach from=$items item=item}
						{if $item.action=='edit'}
							{assign var=markclass value='blue'}
						{elseif $item.action=='delete'}
							{assign var=markclass value='red'}
						{elseif $item.action=='new'}
							{assign var=markclass value='green'}
						{/if}
						
					
					<li class="item-{$markclass} clearfix">
						<label class="inline">
							<div class="action-buttons">
							{if $item.action=='edit'}
								<span class="{$markclass}">
									<i class="icon-pencil bigger-130"></i>
								</span>
							{elseif $item.action=='delete'}
								<span class="{$markclass}">
									<i class="icon-trash bigger-130"></i>
								</span>
							{elseif $item.action=='new'}
								<span class="{$markclass}">
									<i class="icon-flag bigger-130"></i>
								</span>
							{/if}
								<span class="vbar"></span> 
								<span class="{$markclass}"><b>{$item.id}</b></span>
								<span class="vbar"></span>
								<div class="time" style="display: inline-block;">
									<i class="icon-time"></i>
									<span class="">{$item.log_date}</span>
								</div>
							</div>
							<span class="lbl"> {$item.short_desc}</span>
						</label>
	
					
					</li>
					{/foreach}
				</ul>
				{if 1==0}<table class="table table-condenced">
					{foreach from=$items item=item}
						<tr>
						<td>{$item.id}<td>
						<td>{$item.log_date}<td>
						<td>{$item.action}<td>
						<td>{$item.short_desc}<td>
						</td>
					{/foreach}
					</table>{/if}

			</div>
		</div><!-- /widget-main -->
	</div><!-- /widget-body -->
	
	
{/if}

{/if}

</div>






{literal}
<script>
$(document).ready(function(){
	$('#findids').submit(function(e){
		e.preventDefault();
		var id=$(this).find('#ids').val();
		window.location.replace(estate_folder+'/admin/?action=realtylogv2&page=1&ids='+id);
	});
});
</script>
{/literal}