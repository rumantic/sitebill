<div class="widget-box">
	<div class="widget-header header-color-blue">
		<h5 class="bigger lighter">
			<i class="icon-table"></i> Валюты
		</h5>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<table class="table table-striped table-bordered table-hover" id="ordertable">
				<thead>
					<tr>
						<th>ID</th>
						<th>Код\Название</th>
						<th>По умолчанию (у.е.)</th>
						<th>Курс к у.е.</th>
						<th>--</th>
					</tr>
				</thead>

				<tbody>
				{foreach from=$valutes item=valute}
				<tr class="order_line">
					<td>
					{$valute.currency_id}
					</td>

					<td class="">
					<strong>{$valute.code}</strong><br>
					{$valute.name}
					</td>
					<td>
					<input type="checkbox" class="ace def_prop" value="{$valute.currency_id}"{if $valute.is_default==1} checked="checked"{/if}>
					<span class="lbl"></span>
					</td>
					<td>{$valute.course}</td>
					<td class="controlsblock">
						<a href="{$estate_folder}/admin/?action=currency&do=edit&currency_id={$valute.currency_id}" class="btn btn-mini btn-info show_order">Изменить</a>
						<a href="{$estate_folder}/admin/?action=currency&do=delete&currency_id={$valute.currency_id}" class="btn btn-mini btn-danger delete">Удалить</a>
						
					</td>
					
				</tr>
				{/foreach}

														
				</tbody>
			</table>
		</div>
	</div>
</div>

{literal}
<script>
$(document).ready(function(){
	
	$('.def_prop').click(function(e){
		e.preventDefault();
		var id=$(this).val();
		var _this=$(this);
		$.ajax({
			url: estate_folder+'/apps/currency/js/ajax.php',
			dataType: 'json',
			data: {action: 'set_default', id: id},
			type: 'post',
			success: function(json){
				if(json.status==1){
					$('.def_prop').prop('checked', false);
					_this.prop('checked', true);
				}
			}
		});
	});
});
</script>
{/literal}