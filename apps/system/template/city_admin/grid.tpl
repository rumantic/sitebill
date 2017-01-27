<div>
	<form id="filter">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="sort" value="{$start_sort}">
	<input type="hidden" name="sort_asc" value="desc">
	<div class="row-fluid">
		<div class="span4">
			<div class="control-group">
				<div class="controls span12">
					<label>Название</label>
					<input type="text" name="name">
				</div>
				<div class="controls span12">
					<label>
						<input type="radio" name="_compare" value="any" checked="checked">
						<span class="lbl"></span>Любое совпадение
					</label>
				</div>
				<div class="controls span12">
					<label>
						<input type="radio" name="_compare" value="first">
						<span class="lbl"></span>Только в начале
					</label>
				</div>
				
			</div>
		</div>
	
		<div class="span4">
			<div class="control-group">
				<div class="controls span12">
					<label>Регион</label>
					<input type="text" name="region_id">
				</div>
				
			</div>
		</div>
		<div class="span4">
			<div class="control-group">
				<div class="controls span12">
					<label>На страницу</label>
					<input type="text" name="per_page" value="10">
				</div>
				
			</div>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<a href="#" id="refreshS" class="btn btn-block">Искать</a>
		</div>
		
	</div>
	</form>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
			{foreach from=$list_heading item=list_heading_name key=list_heading_key}
				<th><a class="sort" data-sp="{$list_heading_key}" href="#">{$list_heading_name}</a></th>
			{/foreach}
				<th></th>
			</tr>
		</thead>

		<tbody id="dataset">
			
		</tbody>
	</table>
	<div id="pager" class="dataTables_paginate paging_bootstrap pagination"></div>
</div>
</div>
{literal}
<script>
PSearch={};
PSearch.last_call={};

PSearch.load=function(html){
	$('#dataset').html(html);
	$('#dataset').css({'opacity': 1});
};
PSearch.loadPager=function(html){
	$('#pager').html(html);
};
PSearch.froze=function(){
	$('#dataset').css({'opacity': 0.5});
};
PSearch.search=function(){
	PSearch.froze();
	var data={};
	data=SitebillCore.serializeFormJSON($('#filter'));
	data.action='{/literal}{$dataload_action}{literal}';
	
	
	PSearch.last_call=data;
	$.ajax({
		url: '{/literal}{$datarequest_url}{literal}',
		dataType: 'json',
		type: 'post',
		data: data,
		success: function(json){
			if(typeof json.html != 'undefined'){
				var html=json.html;
				html = html.replace(/\\'/g, '\'');
				html = html.replace(/\\"/g, '"');
		        html = html.replace(/\\0/g, '\0');
		        html = html.replace(/\\\\/g, '\\');
				PSearch.load(html);
				PSearch.loadPager(json.pager);
			}
			
		}
	});
};
$(document).ready(function(){
	$('#refreshS').click(function(e){
		$('#filter [name=page]').val(1);
		e.preventDefault();
		PSearch.search();
	});
	$('#refreshS').trigger('click');
	$('.sort').click(function(e){
		e.preventDefault();
		$('.sort').not($(this)).removeClass('common-grid-sorted-asc');
		$('.sort').not($(this)).removeClass('common-grid-sorted-desc');
		var sdir='';
		if(!$(this).hasClass('common-grid-sorted-desc') && !$(this).hasClass('common-grid-sorted-asc')){
			sdir='asc';
			$(this).addClass('common-grid-sorted-asc');
		}else if($(this).hasClass('common-grid-sorted-asc')){
			sdir='desc';
			$(this).removeClass('common-grid-sorted-asc');
			$(this).addClass('common-grid-sorted-desc');
		}else if($(this).hasClass('common-grid-sorted-desc')){
			sdir='asc';
			$(this).removeClass('common-grid-sorted-desc');
			$(this).addClass('common-grid-sorted-asc');
		}
		$('#filter [name=sort]').val($(this).data('sp'));
		$('#filter [name=sort_asc]').val(sdir);
		/*if(sdir=='asc'){
			$(this).removeClass('data-asc', 'desc');
			$(this).attr('data-asc', 'desc');
		}else{
			$(this).attr('data-asc', 'asc');
		}*/
		$('#filter [name=page]').val(1);
		PSearch.search();
	});
	
	
	
	$(document).on('click', '#pager a', function(e){
		e.preventDefault();
		var txt=$('#filter [name=page]').val($(this).data('page'));
		PSearch.search();
	});
	
	/*$(document).on('click', '.delete_me', function(e){
		e.preventDefault();
		if(confirm('Действительно хотите удалить запись?')){
			var _this=$(this);
			var id=_this.data('id');
			$.ajax({
				url: estate_folder+'/apps/predefinedlinks/js/ajax.php',
				dataType: 'json',
				type: 'post',
				data: {action: 'delete', id: id},
				success: function(json){
					if(json.status==1){
						_this.parents('tr').eq(0).remove();
					}else{
						alert(json.msg);
					}
				}
			});
		}
	})*/
});
</script>
{/literal}