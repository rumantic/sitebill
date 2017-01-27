{literal}
<script type="text/javascript">
var user_info_storage=[];
var column_values_for_tags = [];
var datastr={};
$(document).ready(function(){

	$('.go_up').click(function(){
		var id=$(this).attr('alt');
		var tr=$(this).parents('tr').eq(0);
		$.getJSON(estate_folder+'/js/ajax.php?action=go_up&id='+id,{},function(data){
			if(data.response.body!=''){
				tr.find('.dateh').html(data.response.body);
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
		$(this).parents('form').eq(0).find('input[type=checkbox]').each(function(){
			this.checked=false;
		});
		$(this).parents('form').submit();
	});
	
	/*$('.standart_admin_grid tr').click(function(e){
		console.log(e.target);
		var ch=$(this).find('.grid_check_one');
		if(ch.prop('checked')){
			ch.prop('checked', false);
		}else{
			ch.prop('checked', true);
		}
	});*/
	
	
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
			if(field!==null){var cat_id=field.val();}
			var checked=[];
			$('.grid_check_one:checked').each(function(){checked.push(this.value);});
			if(checked.length>0){
				window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=change_param&new_param_value='+cat_id+'&param_name='+operation+'&ids='+checked.join(','));
			}
		}
		return false;
	});
	
	$('.batch_update').click(function(){
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){ids.push($(this).val());});
		window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=batch_update&batch_ids='+ids.join(','));
	});
	
	$('.duplicate').click(function(e){
		e.preventDefault();
		var ids=[];
		var action=$(this).attr('alt');
		$(this).parents('table').eq(0).find('input.grid_check_one:checked').each(function(){
			ids.push($(this).val());
		});
		if(ids.length>0){
			if(confirm("Дублировать с картинками?")){
				window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=duplicate&duplicate_images=1&ids='+ids.join(','));
			}else{
				window.location.replace(estate_folder+'/admin/index.php?action='+action+'&do=duplicate&ids='+ids.join(','));
			}
		}
	});
	
	$('.userinfo_tooltipe_block').popover({
		content: function(){
			$('.userinfo_tooltipe_block').not($(this)).popover('hide');
			var ex=$(this);
			var id=ex.attr('data-user-id');
			var d='info '+id;
			if(id!=0){
				if(user_info_storage[id]!=undefined){
					return user_info_storage[id];
				}else{
					$.ajax({
						url: estate_folder+'/js/ajax.php',
						data: {action: 'get_user_info', user_id: id},
						type: 'post',
						dataType: 'html',
						success: function(html){
							user_info_storage[id]=html;
							ex.popover({content: html}).popover('show');	
						}
					});
				}
			}
		}
	});
	
	$(document).on('click', '.item-on', function(e){
		e.preventDefault();
		var _this=$(this);
		var id=_this.attr('alt');
		
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			data: {action: 'set_realty_status', status: 1, id: id},
			type: 'post',
			dataType: 'text',
			success: function(text){
				if(text=='OK'){
					_this.removeClass('item-on').removeClass('btn-danger').addClass('item-off').addClass('btn-success');
					_this.parents('.item').eq(0).removeClass('notactive');
				}
			}
		});
	});
	
	$(document).on('click', '.item-off', function(e){
		e.preventDefault();
		var _this=$(this);
		var id=_this.attr('alt');
		
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			data: {action: 'set_realty_status', status: 0, id: id},
			type: 'post',
			dataType: 'text',
			success: function(text){
				if(text=='OK'){
					_this.removeClass('item-off').removeClass('btn-success').addClass('item-on').addClass('btn-danger');
					_this.parents('.item').eq(0).addClass('notactive');
				}
			}
		});
	});
	$('[data-rel=tooltip]').tooltip();
});

$(document).ready(function(){
	$('.tagged').each(function(){
		var tag_input = $(this);
		var tag_array = [];
		var this_id=tag_input.attr('id')
		try{
			tag_input.tag({
				placeholder: tag_input.attr('placeholder'),
				source: function(query, process) {
					column_name = tag_input.attr('name');
					$.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&column_name='+column_name+'&model_name=data'}).done(function(result_items){process(result_items);});
				}
			});
			var tag_obj = tag_input.data('tag');
			if(typeof column_values_for_tags[this_id] != 'undefined' && column_values_for_tags[this_id].length>0){
				for(var i in column_values_for_tags[this_id]){
					tag_obj.add(column_values_for_tags[this_id][i]);
					tag_array.push(column_values_for_tags[this_id][i]);
					datastr[this_id] = tag_array;
				}
			}
		}catch(e){
			tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
		}
		tag_input.on('added', function (e, value) {
			tag_array.push(value);
	   		datastr[$(this).attr('name')] = tag_array;
	        $.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&do=set&tags_array='+JSON.stringify(datastr)}).done(function(result_items){location.reload();});
		})
		tag_input.on('removed', function (e, value) {
	   		var item_index = datastr[$(this).attr('name')].indexOf(value);
	   		datastr[$(this).attr('name')].splice(item_index, 1);
	        $.ajax({url: estate_folder+'/js/ajax.php?action=get_tags&do=set&tags_array='+JSON.stringify(datastr)}).done(function(result_items){location.reload();});
		})
	});
});
</script>
{/literal}
<div class="navbar">
	<div class="navbar-inner">
    	<div class="container">
    		<a class="brand" href="#">Найдено: {$_total_records}</a>
    		<div class="nav pull-right">
		
			{if $admin ne ''}
			<div align="right"><a href="#search" id="search_toggle" class="btn btn-info"><i class="icon-white icon-search"></i> {$L_ADVSEARCH}</a></div>
			<div id="search_form_block" {if $smarty.request.submit_search_form_block eq ''}style="display:none;"{/if} class="spacer-top">
				<form class="form-horizontal" action="?action=data" method="get">
					<div class="control-group">
						<label class="control-label">{$L_WORD}</label>
						<div class="controls">
							<input type="text" name="srch_word" value="{$smarty.request.srch_word}" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">{$L_PHONE}</label>
						<div class="controls">
							<input type="text" name="srch_phone" value="{$smarty.request.srch_phone}" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">{$L_ID}</label>
						<div class="controls">
							<input type="text" name="srch_id" value="{$smarty.request.srch_id}" />
						</div>
					</div>
					<!-- 
					<div class="control-group">
						<label class="control-label">Только экспорт в ЦИАН</label>
						<div class="controls">
							<input type="checkbox" name="srch_export_cian" {if isset($smarty.request.srch_export_cian) && ($smarty.request.srch_export_cian=='on' || $smarty.request.srch_export_cian=='1')} checked="checked"{/if} />
						</div>
					</div>
					  -->
					{if $show_uniq_id}
					<div class="control-group">
						<label class="control-label">UNIQ_ID</label>
						<div class="controls">
							<input type="text" name="uniq_id" value="{$smarty.request.uniq_id}" />
						</div>
					</div>
					{/if}
					<div class="control-group">
						<label class="control-label">{$L_DATE} {$L_FROM}</label>
						<div class="controls">
							<input type="text" name="srch_date_from" id="srch_date_from" value="{$smarty.request.srch_date_from}" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">{$L_DATE} {$L_TO}</label>
						<div class="controls">
							<input type="text" name="srch_date_to" id="srch_date_to" value="{$smarty.request.srch_date_to}" />
						</div>
					</div>
				
					<div class="control-group">
						<div class="controls">
							<input type="submit" name="submit_search_form_block" value="{$L_GO_FIND}" class="btn btn-primary" />
							<input type="button" id="reset" value="{$L_RESET}" class="btn btn-warning" /></td></tr>
						</div>
					</div>
				</form>
			</div>
		</div>
		{/if}
		</div>
	</div>
</div>
		
<table class="table table-hover standart_admin_grid" cellspacing="2" cellpadding="2">
	<tr  class="row_head">
		<td width="1%" class="row_title"><input type="checkbox" class="grid_check_all" /></td>
		<td width="1%" class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="id" id="id" class="input-tag tagged safe_string" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.id)}
				<script>
				if(column_values_for_tags['id']===undefined){
					column_values_for_tags['id']=[];
				}
				{foreach from=$smarty.session.tags_array.id item=column_value}
			   	column_values_for_tags['id'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_ID}
		</td>
		{if $show_uniq_id}
			<td width="1%" class="row_title">
				<!-- #section:plugins/input.tag-input -->
				<div class="inline-tags">
					<input type="text" name="uniq_id" id="uniq_id" class="input-tag tagged safe_string" value="" placeholder="..." />
				</div>
				{if is_array($smarty.session.tags_array.uniq_id)}
					<script>
					if(column_values_for_tags['uniq_id']===undefined){
						column_values_for_tags['uniq_id']=[];
					}
					{foreach from=$smarty.session.tags_array.uniq_id item=column_value}
				   	column_values_for_tags['uniq_id'].push('{$column_value}');
			   		{/foreach}
			   		</script>
				{/if}
				<!-- /section:plugins/input.tag-input -->
				UNIQ_ID
			</td>
		{/if}
		
		<td width="1%" class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="date_added" id="date_added" class="input-tag tagged dtdatetime" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.date_added)}
				<script>
				if(column_values_for_tags['date_added']===undefined){
					column_values_for_tags['date_added']=[];
				}
				{foreach from=$smarty.session.tags_array.date_added item=column_value}
			   	column_values_for_tags['date_added'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_DATE}
		</td>
		<td width="70" class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="topic_id" id="topic_id" class="input-tag tagged structure" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.topic_id)}
				<script>
				if(column_values_for_tags['topic_id']===undefined){
					column_values_for_tags['topic_id']=[];
				}
				{foreach from=$smarty.session.tags_array.topic_id item=column_value}
			   	column_values_for_tags['topic_id'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_TYPE}&nbsp;<a href="{$url}&order=type&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=type&asc=desc">&uarr;</a>
		</td>
		<td width=13% class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="city_id" id="city_id" class="input-tag tagged select_by_query" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.city_id)}
				<script>
				if(column_values_for_tags['city_id']===undefined){
					column_values_for_tags['city_id']=[];
				}
				{foreach from=$smarty.session.tags_array.city_id item=column_value}
			   	column_values_for_tags['city_id'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_CITY}&nbsp;<a href="{$url}&order=city&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=city&asc=desc">&uarr;</a>
		</td>
		<td width=13% class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="district_id" id="district_id" class="input-tag tagged select_by_query" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.district_id)}
				<script>
				if(column_values_for_tags['district_id']===undefined){
					column_values_for_tags['district_id']=[];
				}
				{foreach from=$smarty.session.tags_array.district_id item=column_value}
			   	column_values_for_tags['district_id'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_DISTRICT}&nbsp;<a href="{$url}&order=district&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=district&asc=desc">&uarr;</a>
		</td>
		<td width=13% class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="street_id" id="street_id" class="input-tag tagged select_by_query" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.street_id)}
				<script>
				if(column_values_for_tags['street_id']===undefined){
					column_values_for_tags['street_id']=[];
				}
				{foreach from=$smarty.session.tags_array.street_id item=column_value}
			   	column_values_for_tags['street_id'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_STREET}&nbsp;<a href="{$url}&order=street&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=street&asc=desc">&uarr;</a>
		</td>
        <td width=13% class="row_title">
        	<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="number" id="number" class="input-tag tagged safe_string" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.number)}
				<script>
				if(column_values_for_tags['number']===undefined){
					column_values_for_tags['number']=[];
				}
				{foreach from=$smarty.session.tags_array.number item=column_value}
			   	column_values_for_tags['number'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			Дом
		</td>
		<td class="row_title">
			<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="price" id="price" class="input-tag tagged price" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.price)}
				<script>
				if(column_values_for_tags['price']===undefined){
					column_values_for_tags['price']=[];
				}
				{foreach from=$smarty.session.tags_array.price item=column_value}
			   	column_values_for_tags['price'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_PRICE}&nbsp;<a href="{$url}&order=price&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=price&asc=desc">&uarr;</a>
		</td>
		{if $grid_items[0].company != ''}
		<td class="row_title">{$L_COMPANY}</td>
		{else}
        <td class="row_title">
        	<!-- #section:plugins/input.tag-input -->
			<div class="inline-tags">
				<input type="text" name="user_id" id="user_id" class="input-tag tagged select_by_query" value="" placeholder="..." />
			</div>
			{if is_array($smarty.session.tags_array.user_id)}
				<script>
				if(column_values_for_tags['user_id']===undefined){
					column_values_for_tags['user_id']=[];
				}
				{foreach from=$smarty.session.tags_array.user_id item=column_value}
			   	column_values_for_tags['user_id'].push('{$column_value}');
		   		{/foreach}
		   		</script>
			{/if}
			<!-- /section:plugins/input.tag-input -->
			{$L_USER}
		</td>
		{/if}
		{if $admin !=''}
		<td class="row_title"></td>
		{/if}
	</tr>
	{section name=i loop=$grid_items}

	<tr valign="top" class="item {if $grid_items[i].hot}row3hot{else}row3{/if}{if $grid_items[i].active == 0} notactive{/if}">
		<td>
			<input type="checkbox" class="grid_check_one" value="{$grid_items[i].id}" />
        	{if isset($grid_items[i].geo_lat) && isset($grid_items[i].geo_lng) && $grid_items[i].geo_lat!='' && $grid_items[i].geo_lng!=''}<i class="icon-globe"></i>{/if}</td>
		</td>
		<td>
			<b><a href="{$grid_items[i].href}" target="_blank">{$grid_items[i].id}</a></b> 
	        {if isset($grid_items[i].img) && $grid_items[i].img != ''} 
	        <a href="{$estate_folder}/realty{$grid_items[i].id}.html">
	        	<img src="{$estate_folder}/img/data/{$grid_items[i].img[0].preview}" width="50" class="prv">
	        </a>
	        {/if}
		
		{if $show_uniq_id}
		<td>{$grid_items[i].uniq_id}</td>
		{/if}
		
		<td><span class="dateh">{$grid_items[i].date_added}</span></td>
		<td><b>{$grid_items[i].type_sh}</b></td>
		<td>
			{if $grid_items[i].city_id!=0}
        	<a href="{$estate_folder}/admin/?action=data&city_id={$grid_items[i].city_id}" data-rel="tooltip" title="Показать все объявления для {$grid_items[i].city}"><i class="icon-white icon-filter"></i></a>
			{/if}
			{$grid_items[i].city}
		</td>
		<td>
			<span class="user-info">
			{if $grid_items[i].district_id!=0}
        	<a href="{$estate_folder}/admin/?action=data&district_id={$grid_items[i].district_id}" data-rel="tooltip" title="Показать все объявления для {$grid_items[i].district}"><i class="icon-white icon-filter"></i></a>
			{/if}
			{$grid_items[i].district}</span>
		</td>
		<td>
			<span class="user-info">
			{if $grid_items[i].street_id!=0}
        	<a href="{$estate_folder}/admin/?action=data&street_id={$grid_items[i].street_id}" data-rel="tooltip" title="Показать все объявления для {$grid_items[i].street}"><i class="icon-white icon-filter"></i></a>
			{/if}
			{$grid_items[i].street}</span>
		</td>
        <td>{$grid_items[i].number}</td>
		<td nowrap>{if $grid_items[i].price != ''}<b>{$grid_items[i].price|number_format:0:'':' '}</b>{/if}</td>
		<td>
		{if $grid_items[i].company != ''}
		{$grid_items[i].company}
		{else}
		
        	<a href="{$estate_folder}/admin/?action=data&user_id={$grid_items[i].user_id}" data-rel="tooltip" title="Показать все объявления для {$grid_items[i].user}"><i class="icon-white icon-filter"></i></a>
        	<span class="user-info2 userinfo_tooltipe_block" data-rel="popover" data-html="true" data-placement="left" data-user-id="{$grid_items[i].user_id}">{$grid_items[i].user}
        	{if isset($grid_items[i]._user_info) && isset($grid_items[i]._user_info.login)}<br />({$grid_items[i]._user_info.login}){/if}</span>
		{/if}
		</td>
		{if $admin !=''}
		<td nowrap>
			<div class="btn-group">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					Еще<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a href="#" alt="{$grid_items[i].id}" title="Активация" class="btn {if $grid_items[i].active == 0}item-on btn-danger{else}item-off btn-success{/if}"><i class="icon-white icon-off"></i> Активность</a></li>
					{if $grid_items[i].active == 1}
					<li><a title="На сайте" href="{$grid_items[i].href}" target="_blank" class="btn btn-success"><i class="icon-white icon-forward"></i> На сайте</a></li>
					{/if}
					{if isset($show_contacts_enable) && $show_contacts_enable}
							
							{if $grid_items[i].show_contact eq 0}
								<li><img src="{$estate_folder}/img/contact_delete_16x16.gif" alt="{$L_CONTACTS_ARE_HIDE}" title="{$L_CONTACTS_ARE_HIDE}" border="0" width="16" height="16" /></li>
							{else}
								<li><img src="{$estate_folder}/img/contact-new.png" alt="{$L_CONTACTS_ARE_SHOWED}" title="{$L_CONTACTS_ARE_SHOWED}" border="0" width="16" height="16" /></li>
							{/if}
						
						{/if}
						{if isset($sms_enable) && $sms_enable}
						<li><a class="btn btn-success" onclick="return confirm({literal}'{$L_MESSAGE_REALLY_WANT_SMS}'{/literal});" href="{$estate_folder_control}?do=structure&subdo=sms&id={$grid_items[i].id}"><img src="{$estate_folder}/img/sms16x16.png" alt="{$L_SENDSMS_LC}" title="{$L_SENDSMS_LC}" border="0" width="16" height="16" /> SMS</a></li>
						{/if}
						{if isset($show_up_icon) && $show_up_icon}
						
						<a class="btn btn-warning go_up" alt="{$grid_items[i].id}" href="#grow_up"><i class="icon-white icon-circle-arrow-up"></i></a>
						{/if}
				</ul>
		</div>
		<div class="btn-group">
			<a title="Редактировать" href="{$estate_folder_control}?do=edit&id={$grid_items[i].id}" class="btn btn-info"><i class="icon-white icon-pencil"></i></a></li>
			<a title="Удалить" onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_items[i].id}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a></li>
		</div>
	</td>
	{/if}
	</tr>
	{/section}
	<tr>
		<td colspan="14">
			<button alt="data" class="delete_checked btn btn-danger"><i class="icon-white icon-remove"></i> {$L_DELETE_CHECKED}</button> 
			<button alt="data" class="batch_update btn btn-inverse"><i class="icon-white icon-th"></i> Пакетная обработка</button> 
			<button alt="data" class="duplicate btn btn-inverse"><i class="icon-white icon-th"></i> Дублировать</button>
		</td>
	</tr>
	{if $pager != ''}
	<tr>
		<td colspan="14" class="pager"><div align="center">{$pager}</div></td>
	</tr>
	{/if}
</table>