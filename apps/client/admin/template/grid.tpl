<form class="form-inline" method="get" action="{$estate_folder}/admin/">
	<input type="hidden" name="action" value="client">
	<div class="row-fluid">
		<div class="span6">
			<div class="control-group">
				<h3 class="row-fluid header smaller lighter blue">
					<span class="span7"> Тип </span>
				</h3>
				{foreach from=$order_types key=key item=order_type}
					<div class="controls span4">
						<label>
							<input type="checkbox" name="type_id[]" class="ace" value="{$key}"{if $order_type.s==1} checked="checked"{/if}>
							<span class="lbl"></span>{$order_type.n}
						</label>
					</div>
				{/foreach}
			</div>
		</div>
		<div class="span6">
			<div class="control-group">
				<h3 class="row-fluid header smaller lighter blue">
					<span class="span7"> Статус </span>
				</h3>
				{foreach from=$order_statuses key=key item=order_status}
					<div class="controls span4">
						<label>
							<input type="checkbox" name="status_id[]" class="ace" value="{$key}"{if $order_status.s==1} checked="checked"{/if}>
							<span class="lbl"></span>{$order_status.n}
						</label>
					</div>
				{/foreach}
			</div>
		</div>
	</div>
	<input type="submit" class="btn btn-inverse btn-mini" value="Фильтровать">
</form>

<div class="widget-box">
	<div class="widget-header header-color-blue">
		<h5 class="bigger lighter">
			<i class="icon-table"></i> Заявки
		</h5>
	</div>
	{if isset($orders) && is_array($orders) && !empty($orders)}
	<div class="widget-body">
		<div class="widget-main no-padding">
			<table class="table table-striped table-bordered table-hover" id="ordertable">
				<tbody>
				{foreach from=$orders item=order}
				<tr class="order_line">
					<td>
					{if $order.date|date_format:'Y-m-d' eq $smarty.now|date_format:'Y-m-d'}
					<span class="badge badge-important"><i class="icon-calendar"></i> {$order.date|date_format:'Y-m-d'}</span>
					{else}
					<i class="icon-calendar"></i> {$order.date|date_format:'Y-m-d'}
					{/if}
					<br>
					{$order.date|date_format:'H:i'}<br>
					<strong>{$order.client_id}</strong>
					{if isset($order.ip) && $order.ip!=''}
					<small>{$order.ip}</small>
					{/if}
					{if isset($order.src_page) && $order.src_page!=''}
					<small>{$order.src_page}</small>
					{/if}
					</td>
					<td class="">
					<strong>{$orders_m.type_id.select_data[$order.type_id]}</strong><br>
					{if $order.status_id=='new'}
					<span class="label label-important status_label">{$order_statuses[$order.status_id].n}</span>
					{elseif $order.status_id=='inprogress'}
					<span class="label label-warning status_label">{$order_statuses[$order.status_id].n}</span>
					{elseif $order.status_id=='complete'}
					<span class="label label-success status_label">{$order_statuses[$order.status_id].n}</span>
					{elseif $order.status_id=='cancel'}
					<span class="label status_label">{$order_statuses[$order.status_id].n}</span>
					{elseif $order.status_id=='black'}
					<span class="label label-inverse status_label">{$order_statuses[$order.status_id].n}</span>
					{else}
					<span class="label label-info status_label">{$order_statuses[$order.status_id].n}</span>
					{/if}
					<div class="btn-group set_status" data-id="{$order.client_id}">
						<button data-toggle="dropdown" class="btn btn-inverse btn-mini dropdown-toggle">
							Сменить статус
							<span class="caret"></span>
						</button>

						<ul class="dropdown-menu dropdown-inverse">
						{foreach from=$order_statuses key=key item=order_status}
							<li>
								<a href="#" data-status="{$key}">{$order_status.n}</a>
							</li>
						{/foreach}
						</ul>
					</div>
					</td>
					<td>
						{$order.fio}<br>
						{$order.email}<br>
						{$order.phone}<br>
					</td>
					<td class="controlsblock">
						<a href="#" class="btn btn-mini btn-info show_order" data-id="{$order.client_id}">Подробнее</a>
						<a href="#" class="btn btn-mini btn-info send_email" data-id="{$order.client_id}">Отправить на e-mail</a>
						<a href="#" class="btn btn-mini btn-danger delete" data-id="{$order.client_id}">Удалить</a>
						<div class="subcontrols"></div>
					</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	{/if}
</div>
{if isset($orders) && is_array($orders) && !empty($orders)}
<div id="hold" style="display: none">
{foreach from=$orders item=order}
	<div class="hold_{$order.client_id}">
		<p><strong>{$order.date|date_format:'Y-m-d'}</strong>
		{if $order.status_id=='new'}
		<span class="label label-important status_label">Новая</span>
		{elseif $order.status_id=='inprogress'}
		<span class="label label-warning status_label">Выполняется</span>
		{elseif $order.status_id=='complete'}
		<span class="label label-success status_label">Закрыта</span>
		{elseif $order.status_id=='cancel'}
		<span class="label status_label">Отменена</span>
		{elseif $order.status_id=='black'}
		<span class="label label-inverse status_label">Плохо</span>
		{else}
		<span class="label label-info status_label">Другое</span>
		{/if}<strong>{$orders_m.type_id.select_data[$order.type_id]}</strong>
		</p>
		{$order.order_text}
	</div>
{/foreach}
</div>
{/if}
<div class="modal hide fade" id="myOrderModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Просмотр заявки</h3>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer"></div>
</div>

{literal}
<script>
$(document).ready(function(){
	$('#myOrderModal').on('hidden', function () {
		$('#myOrderModal .modal-body').html('');
	});
	$('#ordertable .delete').click(function(e){
		e.preventDefault();
		if(confirm('Вы действительно хотите удалить эту заявку?')){
			var _this=$(this);
			var id=_this.data('id');
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				dataType: 'json',
				data: {action: 'delete_order', id: id, _app: 'client'},
				type: 'post',
				success: function(json){
					if(json.status==1){
						_this.parents('.order_line').eq(0).fadeOut(1000, function(){_this.parents('.order_line').eq(0).remove();});
					}
				}
			});
		}
	});
	$('#ordertable .show_order').click(function(e){
		e.preventDefault();
		var id=$(this).data('id');
		$('#myOrderModal .modal-body').html($('#hold .hold_'+id).html());
		$('#myOrderModal').modal('show');
	});
	$('#ordertable .send_email').click(function(e){
		e.preventDefault();
		var id=$(this).data('id');
		var new_win=$('<div class="send_by_email" alt="'+id+'"><p><small>Введите email или несколько вписывая каждый с новой строки</small></p><textarea name="emails"></textarea><p><small>Возможно Вы захотите добавить сопроводительное сообщение</small></p><textarea name="message"></textarea><p><small>и тему письма</small></p><input type="text" name="theme"><p><button class="btn btn-mini ok">Отправить</button> <button class="btn btn-mini not">Я передумал</button></p></div>');
		$(this).parents('.controlsblock').eq(0).find('.subcontrols').append(new_win);
	});
	$(document).on('click', '.send_by_email button.ok', function(){
		var p=$(this).parents('.send_by_email').eq(0);
		var emails=p.find('textarea[name=emails]').val();
		emails=emails.split("\n");
		var theme=p.find('input[name=theme]').val();
		var message=p.find('textarea[name=message]').val();
		var id=p.attr('alt');
		if(emails.length==1 && emails[0]==''){
			return;
		}
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			dataType: 'json',
			data: {action: 'send_by_email', id: id, emails: emails, theme: theme, message: message, _app: 'client'},
			type: 'post',
			success: function(json){
				if(json.status==1){
					p.remove();
				}
			}
		});
	});
	$(document).on('click', '.send_by_email button.not', function(){
		var p=$(this).parents('.send_by_email').eq(0);
		p.remove();
	});
	$('#ordertable .set_status a').on('click', function (e) {
		e.preventDefault();
		var new_status=$(this).data('status');
		var id=$(this).parents('.set_status').eq(0).data('id');
		var ordel_line=$(this).parents('.order_line').eq(0);
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			dataType: 'json',
			data: {action: 'set_status', id: id, status_id: new_status, _app: 'client'},
			type: 'post',
			success: function(json){
				if(json.status==1){
					var sl=ordel_line.find('.status_label');
					sl.removeClass('label-important label-warning label-success label-inverse label-info');
					var new_class='label status_label';
					if (new_status=='new'){
						sl.addClass('label-important');
					}else if(new_status=='inprogress'){
						sl.addClass('label-warning');
					}else if(new_status=='complete'){
						sl.addClass('label-success');
					}else if(new_status=='cancel'){
						//new_class+='';
					}else if(new_status=='black'){
						sl.addClass('label-inverse');
					}else{
						sl.addClass('label-info');
					}
					sl.text(json.txt);
				}
			}
		});
	});
});

function setOrderStatus(id, new_status){
	
}
</script>
{/literal}

{if isset($pager_array)}
	{foreach from=$pager_array.pages item=pager_page}
		{if $pager_page.current==1}
			{assign var=__curpagenr value=$pager_page.text}
		{/if}
	{/foreach}

	{if $__curpagenr-5<1}
		{assign var=__startnr value=1}
		{assign var=__leftsep value=0}
	{else}
		{assign var=__startnr value=$__curpagenr-3}
		{assign var=__leftsep value=1}
	{/if}

	{if $__curpagenr+5>$pager_array.pages|count}
		{assign var=__endnr value=$pager_array.pages|count}
		{assign var=__rightsep value=0}
	{else}
		{assign var=__endnr value=$__curpagenr+3}
		{assign var=__rightsep value=1}
	{/if}

	{if $pager_array.pages|count>1}
		<div class="paging_bootstrap pagination">
			<ul>
				<li><a href="{$pager_array.fpn.href}"><i class="icon-double-angle-left"></i></a></li>
				<li><a href="{$pager_array.ppn.href}"><i class="icon-angle-left"></i></a></li>
				{if $__leftsep==1}
				<li><a href="{$pager_array.pages[1].href}">{$pager_array.pages[1].text}</a></li>
				<li class="disabled"><a href="javascript:void(0);">...</a></li>
				{/if}
				{foreach from=$pager_array.pages item=pager_page}
				{if $pager_page.text>=$__startnr && $pager_page.text<=$__endnr}
				<li{if $pager_page.current==1} class="active"{/if}><a href="{$pager_page.href}">{$pager_page.text}</a></li>
				{/if}
				{/foreach}
				{if $__rightsep==1}
				<li class="disabled"><a href="javascript:void(0);">...</a></li>
				<li><a href="{$pager_array.pages[$pager_array.pages|count].href}">{$pager_array.pages[$pager_array.pages|count].text}</a></li>
				{/if}
				<li><a href="{$pager_array.npn.href}"><i class="icon-angle-right"></i></a></li>
				<li><a href="{$pager_array.lpn.href}"><i class="icon-double-angle-right"></i></a></li>
			</ul>
		</div>
	{/if}
{/if}