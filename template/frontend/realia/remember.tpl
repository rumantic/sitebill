{if $apps_billing=='on'}
<script>
var pvb_packs_info={$pvb_packs_info_js};
</script>
{literal}
<script>
function refreshUData(){
	$.ajax({
		url: estate_folder+'/apps/billing/js/ajax.php',
		dataType: 'json',
		type: 'post',
		data: {action: 'refresh_status'},
		success: function(json){
			if(typeof json == 'object'){
				$('.free_prem').text(json.free.prem);
				$('.own_prem').text(json.own.prem);
				$('.free_vip').text(json.free.vip);
				$('.own_vip').text(json.own.vip);
				$('.free_bold').text(json.free.bold);
				$('.own_bold').text(json.own.bold);
			}
		}
	});
}
$(document).ready(function(){
	var makeWindow=$('#makeSpec');
	var okButton=makeWindow.find('button.ok');
	var useOwnButton=makeWindow.find('button.use_own');
	
	makeWindow.find('[name=days]').blur(function(){
		var v=parseInt($(this).val(), 10);
		var days=$('#makeVipModalWindow').find('[name=days]');
		if(isNaN(v)){
			v=1;
			$(this).val(1);
		}else if(v==0){
			v=1;
			$(this).val(1);
		}else if(v<0){
			v=-1*v;
			$(this).val(v);
		}
		
		var per_day_price=makeWindow.find('[name=per_day_price]').val();
		makeWindow.find(".calc_price").text(v*per_day_price);
	});
	
	makeWindow.find('form').submit(function(){
		return false;
	});
	
	okButton.click(function(){
		var realty_id=parseInt(makeWindow.find('[name=realty_id]').val(), 10);
		var per_day_price=makeWindow.find('[name=per_day_price]').val();
		var days=parseInt(makeWindow.find('[name=days]').val(), 10);
		var type=makeWindow.find('[name=type]').val();
		if(isNaN(realty_id) || isNaN(days) || type==''){
			return;
		}
		okButton.prop('disabled', true);
		useOwnButton.prop('disabled', true);
		if(days>0){
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				dataType: 'html',
				type: 'post',
				data: {action:'make_special_payment', days: days, per_day: per_day_price, realty_id: realty_id, payment_type: type},
				success: function(html){
					if(html!='error'){
						makeWindow.find('.answer').html(html).show();
						makeWindow.find('form').hide();
						$('a[alt='+realty_id+']').each(function(){
							if($(this).hasClass('make_spec') && $(this).data('type')==type){
								$(this).hide();
							}
						});
						setTimeout(function(){
							okButton.prop('disabled', false);
							useOwnButton.prop('disabled', false);
							makeWindow.modal('hide');
						},2000);
					}else{
						okButton.prop('disabled', false);
						useOwnButton.prop('disabled', false);
					}
				},
				error: function(){
					okButton.prop('disabled', false);
					useOwnButton.prop('disabled', false);
				}
			});
		}
		return false;
	});
	
	useOwnButton.click(function(e){
		e.preventDefault();
		var realty_id=parseInt(makeWindow.find('[name=realty_id]').val(), 10);
		var type=makeWindow.find('[name=type]').val();
		
		if(isNaN(realty_id) || type==''){
			return;
		}
		useOwnButton.attr('disabled', true);
		okButton.prop('disabled', true);
		var _type=type;
		if(_type=='premium'){
			_type='prem'
		}
		$.ajax({
			url: estate_folder+'/apps/billing/js/ajax.php',
			dataType: 'json',
			type: 'post',
			data: {action: 'set_status', use_own: 1, realty_id: realty_id, payment_type: _type},
			success: function(json){
				if(json.status==1){
					refreshUData();
					console.log(pvb_packs_info);
					$('a[alt='+realty_id+']').each(function(){
						if($(this).hasClass('make_spec') && $(this).data('type')==type){
							$(this).hide();
						}
					});
				}
				makeWindow.find('.answer').html(json.msg).show();
				makeWindow.find('form').hide();
				setTimeout(function(){
					useOwnButton.prop('disabled', false);
					okButton.prop('disabled', false);
					makeWindow.modal('hide');
				},2000);
			},
			error: function(){
				useOwnButton.prop('disabled', false);
				okButton.prop('disabled', false);
			}
		});
	});
	
	$('.make_spec').click(function(e){
		e.preventDefault();
		var runbutton=$(this);
		var type=runbutton.data('type');
		var realty_id=parseInt(runbutton.attr('alt'), 10);
		if(type=='' || isNaN(realty_id)){
			return;
		}
		makeWindow.find('#makeSpecModalLabel .spec_title').hide();
		makeWindow.find('.use_own').hide();
		if(typeof pvb_packs_info != 'undefined'){
			if(type=='vip' && typeof pvb_packs_info.total.vip != 'undefined' && pvb_packs_info.total.vip>0){
				makeWindow.find('.use_own').show();
			}else if(type=='premium' && typeof pvb_packs_info.total.prem != 'undefined' && pvb_packs_info.total.prem>0){
				makeWindow.find('.use_own').show();
			}else if(type=='bold' && typeof pvb_packs_info.total.bold != 'undefined' && pvb_packs_info.total.bold>0){
				makeWindow.find('.use_own').show();
			}
		}
		var per_day_price=0;
		switch(type){
			case 'vip' : {
				per_day_price=makeWindow.find('#pdp_vip').val();
				makeWindow.find('#makeSpecModalLabel .spec_title_vip').show();
				break;
			}
			case 'premium' : {
				per_day_price=makeWindow.find('#pdp_premium').val();
				makeWindow.find('#makeSpecModalLabel .spec_title_premium').show();
				break;
			}
			case 'bold' : {
				per_day_price=makeWindow.find('#pdp_bold').val();
				makeWindow.find('#makeSpecModalLabel .spec_title_bold').show();
				break;
			}
		}
		
		makeWindow.find('[name=per_day_price]').val(per_day_price);
		makeWindow.find('[name=type]').val(type);
		
		makeWindow.find('.answer').html('').hide();
		makeWindow.find('form').show();
		
		makeWindow.find('[name=realty_id]').val(realty_id);
		var days=makeWindow.find('[name=days]');
		days.val(1);
		days.trigger('blur');
		makeWindow.modal();
	});
	
	$('#makeVipModalWindow').find('form').submit(function(){
		return false;
	});
});
</script>
{/literal}
	{if $pvb_buy_own==1}
	{literal}
	<script>
	$(document).ready(function(){
		$('.buyprefs').click(function(e){
			e.preventDefault();
			if(confirm('Действительно хотите купить этот набор? Средства будут списаны с вашего счета.')){
				var _this=$(this);
				var type=_this.data('type');
				_this.prop('disabled', true);
				var f=_this.parents('form').eq(0);
				var cnt=parseInt(f.find('input[type=text]').val(), 10);
				if(isNaN(cnt) || cnt<1){
					cnt=1;
					f.find('input[type=text]').val(cnt)
				}
				
				$.ajax({
					url: estate_folder+'/apps/billing/js/ajax.php',
					dataType: 'json',
					type: 'post',
					data: {action: 'buy_packs', count: cnt, payment_type: type},
					success: function(json){
						if(json.status==1){
							f.prepend($('<div class="alert alert-success">'+json.msg+'</div>'));
							refreshUData();
						}else{
							f.prepend($('<div class="alert alert-error">'+json.msg+'</div>'));
						}
						setTimeout(function(){
							f.find('.alert').remove();
							_this.prop('disabled', false);
						}, 2000);
					}
				});
			}
		});
	});
	</script>
	{/literal}
	{/if}
{/if}
{if $apps_billing=='on' && $apps_upper_enable}
<div class="property-detail">
    {if !$upps_left && $packs_left==0}
	<p>Вы исчерпали лимит поднятий за неделю.</p>
	{else}
	<p> На этой неделе у вас осталось {if $upps_left != ''}{$upps_left}{else}0{/if} подъема(ов) объявлений.</p>
	{/if}
	<p>Дополнительных подъемов: {$packs_left}</p>
	
	
	<a class="btn btn-small buy_ups_modal" alt="{$grid_items[i].id}">Купить дополнительные подъемы</a>
	
	<br>
	
	<script>
	{literal}
	$(document).ready(function(){
		$('#buyUpsModalWindow').find('form').submit(function(){
			return false;
		});
		
		$('#buyUpsModalWindow').find('[name=count]').blur(function(){
			var v=Number($(this).val());
			var count=$('#buyUpsModalWindow').find('[name=count]');
			if(isNaN(v)){
				v=1;
				count.val(1);
			}else if(v==0){
				v=1;
				count.val(1);
			}else if(v<0){
				v=-1*v;
				count.val(v);
			}
			var per_day_price=$('#buyUpsModalWindow').find('[name=ups_price]').val();
			$('#buyUpsModalWindow').find(".calc_price").text(v*per_day_price);
		});
		
		$('#buyUpsModalWindow').find('button.ok').click(function(){
			var ModalWindow=$('#buyUpsModalWindow');
			
			var _this=$(this);
			_this.hide();
			var per_day_price=ModalWindow.find('[name=ups_price]').val();
			var count=ModalWindow.find('[name=count]');
			
			var v=count.val();
			var v=v.replace(/D/g,'');
			var d=+v;
			if(d>0){
				$.ajax({
					url: estate_folder+'/js/ajax.php',
					dataType: 'html',
					type: 'post',
					data: {action:'make_special_payment', days:d, per_day:per_day_price, payment_type:'buy_ups'},
					success: function(html){
						if(html!='error'){
							ModalWindow.find('.answer').html(html).show();
							ModalWindow.find('form').hide();
							
							setTimeout(function(){
								ModalWindow.modal('hide');
							},2000);
						}
					},
					error: function(){
						
					}
				});
			}
			return false;
		});
		
		$(".buy_ups_modal").click(function(){
			var ModalWindow=$('#buyUpsModalWindow');
			ModalWindow.find('.answer').html('').hide();
			ModalWindow.find('form').show();
			var per_day_price=ModalWindow.find('[name=ups_price]').val();
			var count=ModalWindow.find('[name=count]');
			count.val(1);
			var sum=ModalWindow.find(".calc_price");
			sum.text(per_day_price);
			ModalWindow.modal();
		});
		
		
	});
	
	{/literal}
	</script>
				
					
</div>

<div class="modal fade" class="buyUps" id="buyUpsModalWindow" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog">
	<div class="modal-content">
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
	<h3 id="myModalLabel">Покупка поднятий</h3>
  </div>
  <div class="modal-body">
	<form class="form-horizontal">
		<input type="hidden" value="{$ups_price}" name="ups_price" />
		  <div class="control-group">
			<label class="control-label">Количество поднятий</label>
			<div class="controls">
			  <input type="text" value="1" name="count" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Цена</label>
			<div class="controls">
			  <span class="calc_price"></span>
			</div>
		  </div>

	</form>
	<div class="answer" style="display: none;"></div>
  </div>
  <div class="modal-footer">
	<button class="btn ok">ОК</button>
	<button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
  </div>
</div>
</div>
</div>
{/if}
{if $apps_billing=='on'}
	{if $pvb_buy_own==1}
	<div class="property-detail">
	<table class="table">
		<tr>
			<td></td>
			<td>тариф</td>
			<td>собственные</td>
			<td></td>
		</tr>
		<tr>
			<td>Премиум</td>
			<td><span class="free_prem">{intval($pvb_packs_info.free.prem)}</span></td>
			<td><span class="own_prem">{intval($pvb_packs_info.own.prem)}</span></td>
			<td>
			<form>
			<input type="text" value="10"> 
			<button class="btn btn-small buyprefs" data-type="prem">Купить</button>
			</form>
			</td>
		</tr>
		<tr>
			<td>VIP</td>
			<td><span class="free_vip">{intval($pvb_packs_info.free.vip)}</span></td>
			<td><span class="own_vip">{intval($pvb_packs_info.own.vip)}</span></td>
			<td> 
			<form>
			<input type="text" value="10"> 
			<button class="btn btn-small buyprefs" data-type="vip">Купить</button>
			</form>
			</td>
		</tr>
		<tr>
			<td>Выделенные</td>
			<td><span class="free_bold">{intval($pvb_packs_info.free.bold)}</span></td>
			<td><span class="own_bold">{intval($pvb_packs_info.own.bold)}</span></td>
			<td> 
			<form>
			<input type="text" value="10"> 
			<button class="btn btn-small buyprefs" data-type="bold">Купить</button>
			</form>
			</td>
		</tr>
		
	</table>
	</div>
	{else}
	<div class="property-detail">
	<p>Премиум: <span class="free_prem">{intval($pvb_packs_info.free.prem)}</span></p>
	<p>VIP: <span class="free_vip">{intval($pvb_packs_info.free.vip)}</span></p>
	<p>Выделенные: <span class="free_bold">{intval($pvb_packs_info.free.bold)}</span></p>
	</div>
	{/if}
{/if}