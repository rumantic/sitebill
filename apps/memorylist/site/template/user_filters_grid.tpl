{literal}
<script>
$(document).ready(function(){
	/*$('.goPDF').click(function(e){
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
	});*/
});
</script>
{/literal}	

{if $user_filters|count>0}
<table id="sample-table-1" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>ID</th>
		<th>Набор</th>
		<th>Создан</th>
		<th>В наборе</th>
		<th></th>
	</tr>
</thead>
{foreach from=$user_filters item=user_filter}
	<tr>
		<td>
			{$user_filter.memorylist_id}
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
			{if 1==0}<a class="btn btn-mini btn-info" href="{$etstate_folder}/memorylist/?do=showfilter&filter_id={$user_filter.memorylist_id}">
				<i class="icon-edit bigger-120"></i>
			</a>{/if}
			{if $memorylist_pdf==1}
			<a class="btn btn-mini btn-success goPDF" target="_blank" href="{$etstate_folder}/memorylist/?do=getpdf&filter_id={$user_filter.memorylist_id}">
				<i class="icon-print icon-white"></i> PDF
			</a>
			{/if}
			{if $memorylist_excel==1 && 1==0}
			<a class="btn btn-mini btn-success goExcell" target="_blank" href="{$etstate_folder}/memorylist/?do=getexcel&filter_id={$user_filter.memorylist_id}">
				<i class="icon-file bigger-120"></i> Excell
			</a>
			{/if}
			<a class="btn btn-mini btn-danger" href="{$etstate_folder}/memorylist/?do=delete&filter_id={$user_filter.memorylist_id}">
				<i class="icon-trash icon-white"></i> Удалить
			</a>
		</td>
	</tr>
{/foreach}
</table>
{/if}