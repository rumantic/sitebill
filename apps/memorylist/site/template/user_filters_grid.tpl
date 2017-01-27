{literal}
<script>
$(document).ready(function(){
	$('.goPDF').click(function(e){
		e.preventDefault();
		var href=$(this).attr('href');
		if(confirm('Выгружать с полным описанием')){
			window.open(href+'&ext=1');
		}else{
			window.open(href);
		}
	});
	$('.goExcell').click(function(e){
		e.preventDefault();
		var href=$(this).attr('href');
		if(confirm('Выгружать с полным описанием')){
			window.open(href+'&ext=1');
		}else{
			window.open(href);
		}
	});
});
</script>
{/literal}	

{if $user_filters|count>0}
<table id="sample-table-1" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th class="center">
			<label>
				<input type="checkbox" class="ace">
				<span class="lbl"></span>
			</label>
		</th>
		<th>Набор</th>
		<th>Создан</th>
		<th>В наборе</th>
		<th></th>
	</tr>
</thead>
{foreach from=$user_filters item=user_filter}
	<tr>
		<td class="center">
			<input type="checkbox" class="ace">
			<span class="lbl"></span>
		</td>
		<td class="center">
			{$user_filter.title}
		</td>
		<td class="hidden-480">
			{$user_filter.created_at}
		</td>
		<td class="hidden-480">
			В списке: {$user_filter.items|count}
		</td>
		<td>
			<!--a class="btn btn-mini btn-info" href="{$etstate_folder}/memorylist/?do=showfilter&filter_id={$user_filter.memorylist_id}">
				<i class="icon-edit bigger-120"></i>
			</a-->
			<a class="btn btn-mini btn-success goPDF" target="_blank" href="{$etstate_folder}/memorylist/?do=getpdf&filter_id={$user_filter.memorylist_id}">
				<i class="icon-print bigger-120"></i> PDF
			</a>
			<!--a class="btn btn-mini btn-success goExcell" target="_blank" href="{$etstate_folder}/memorylist/?do=getexcel&filter_id={$user_filter.memorylist_id}">
				<i class="icon-file bigger-120"></i> Excell
			</a-->
			<a class="btn btn-mini btn-danger" href="{$etstate_folder}/memorylist/?do=delete&filter_id={$user_filter.memorylist_id}">
				<i class="icon-trash bigger-120">Удалить</i>
			</a>
		</td>
	</tr>
{/foreach}
</table>
{/if}