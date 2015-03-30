<!-- 
{literal}
<style>
.cianexported {
color: green;
}
</style>
{/literal}
<div class="row-fluid">
<div class="btn-group">{if isset($smarty.request.srch_export_cian) && ($smarty.request.srch_export_cian=='on' || $smarty.request.srch_export_cian=='1')}
<a class="btn" href="{$estate_folder}/account/data/">Все</a> 
<a class="btn btn-info" href="{$estate_folder}/account/data/?srch_export_cian=on"><i class="icon-white icon-ok"></i> Только выгружаемые в ЦИАН</a>
{else}
<a class="btn btn-info" href="{$estate_folder}/account/data/"><i class="icon-white icon-ok"></i> Все</a> 
<a class="btn" href="{$estate_folder}/account/data/?srch_export_cian=on">Только выгружаемые в ЦИАН</a>
{/if}
</div>
</div>
 -->
<table class="content_main table">
    <tr  class="row_head">
        {if $admin !=''}
        <td class="row_title"></td>
        {/if}
    
        <td>{$L_DATE}</td>
        <td>{$L_ID}</td>
        <td>{$L_PHOTO}</td>
        <td>{$L_TYPE}&nbsp;<a href="{$url}&order=type&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=type&asc=desc">&uarr;</a></td>
        <td>{$L_CITY}&nbsp;<a href="{$url}&order=city&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=city&asc=desc">&uarr;</a></td>
        <td>{$L_DISTRICT}&nbsp;<a href="{$url}&order=district&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=district&asc=desc">&uarr;</a></td>
        <td>{$L_STREET}&nbsp;<a href="{$url}&order=street&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=street&asc=desc">&uarr;</a></td>
        <td>{$L_PRICE}&nbsp;<a href="{$url}&order=price&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=price&asc=desc">&uarr;</a></td>
        <td>{$L_FLOOR}</td>
        <td>{$L_SQUARE} м<sup>2</sup></td>
    </tr>
    {section name=i loop=$grid_items}

    <tr valign="top" class="row3{if isset($grid_items[i].export_cian) && $grid_items[i].export_cian==1} cianexported{/if}" {if $grid_items[i].active == 0}style="color: #ff5a5a;"{/if}>
        {if $admin !=''}
        <td nowrap>
        <a href="{$estate_folder_control}?do=edit&id={$grid_items[i].id}"><img src="{$estate_folder}/img/edit.gif" border="0" width="16" height="16" /></a>
        <a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_items[i].id}"><img src="{$estate_folder}/img/delete.gif" border="0" width="16" height="16" /></a>
        
        </td>
        {/if}
        
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}><b><a href="{$grid_items[i].href}">{$grid_items[i].date}</a></b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}><b><a href="{$grid_items[i].href}">{$grid_items[i].id}</a></b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if} align="center">
        {if $grid_items[i].img != '' } 
        <a href="{$grid_items[i].href}"><img src="{$estate_folder}/img/data/{$grid_items[i].img[0].preview}" width="50"></a> 
        <!-- img src="{$estate_folder}/img/hasphoto.jpg" border="0" width="16" height="14" /--> 
        {/if}
        </td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}><b>{$grid_items[i].type_sh}</b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].city}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].district}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].street}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if} nowrap><b>{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if} {if $grid_items[i].currency != 'RUR'}({$grid_items[i].price_ue} {$L_RUR_SHORT}){/if}</b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].floor}/{$grid_items[i].floor_count}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].square_all}/{$grid_items[i].square_live}/{$grid_items[i].square_kitchen}</td>
        
    </tr>
    {if $apps_billing=='on'}
    <tr>
	    <td colspan="8">
	    
	    
	    
	    	{if $grid_items[i].vip_status_end > $now}
				<span class="vb"><i class="icon-star icon-black"></i> VIP до {$grid_items[i].vip_status_end|date_format:"%d.%m.%Y %H:%M"}</span>
			{else}
				<a class="btn btn-small make_vip_modal" alt="{$grid_items[i].id}">Сделать VIP</a>
			{/if}
	    
	    	{if $grid_items[i].premium_status_end > $now}
				<span class="vb"><i class="icon-star icon-black"></i> Premium до {$grid_items[i].premium_status_end|date_format:"%d.%m.%Y %H:%M"}</span>
			{else}
				<a class="btn btn-small make_premium_modal" alt="{$grid_items[i].id}">Сделать premium</a>
			{/if}
	    
	    
	    	{if $grid_items[i].bold_status_end > $now}
				<span class="vb"><i class="icon-star icon-black"></i> Выделено до {$grid_items[i].bold_status_end|date_format:"%d.%m.%Y %H:%M"}</span>
			{else}
				<a class="btn btn-small make_bold_modal" alt="{$grid_items[i].id}">Выделить объявление</a>
			{/if}
	    	{if $apps_upper_enable==1 && ($upps_left!=0 || $packs_left!=0)}
	    	<a class="btn btn-small go_up" href="{$estate_folder}/upper/realty{$grid_items[i].id}/">Поднять</a>
	    	{/if}
	    	
	    </td>
    </tr>
    {/if}
    {/section}

    {if $pager != ''}
    <tr>
        <td colspan="9" class="pager">{$pager}</td>
    </tr>
    {/if}
</table>

{if $apps_billing=='on'}
{literal}



<script type="text/javascript">
$(document).ready(function(){
	
	$('#makeVipModalWindow').find('form').submit(function(){
		return false;
	});
	
	$('#makeVipModalWindow').find('[name=days]').blur(function(){
		var v=Number($(this).val());
		var days=$('#makeVipModalWindow').find('[name=days]');
		if(isNaN(v)){
			v=1;
			days.val(1);
		}else if(v==0){
			v=1;
			days.val(1);
		}else if(v<0){
			v=-1*v;
			days.val(v);
		}
		var per_day_price=$('#makeVipModalWindow').find('[name=per_day_price]').val();
		$('#makeVipModalWindow').find(".calc_price").text(v*per_day_price);
	});
	
	$('#makeVipModalWindow').find('button.ok').click(function(){
		var makeVipModalWindow=$('#makeVipModalWindow');
		var realty_id=makeVipModalWindow.find('[name=realty_id]').val();
		var _this=$(this);
		_this.attr('disabled', true);
		
		var per_day_price=makeVipModalWindow.find('[name=per_day_price]').val();
		var days=makeVipModalWindow.find('[name=days]');
		
		var v=days.val();
		var v=v.replace(/D/g,'');
		var d=+v;
		if(d>0){
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				dataType: 'html',
				type: 'post',
				data: {action:'make_special_payment', days:d, per_day:per_day_price, realty_id:realty_id, payment_type:'vip'},
				success: function(html){
					if(html!='error'){
						makeVipModalWindow.find('.answer').html(html).show();
						makeVipModalWindow.find('form').hide();
						$('a[alt='+realty_id+']').each(function(){
							if($(this).hasClass('make_vip_modal')){
								$(this).hide();
							}
						});
						setTimeout(function(){
							_this.attr('disabled', false);
							makeVipModalWindow.modal('hide');
							
						},2000);
					}else{
						_this.attr('disabled', false);
					}
				},
				error: function(){
					_this.attr('disabled', false);
				}
			});
		}
		return false;
	});
	
	$(".make_vip_modal").click(function(){
		var makeVipModalWindow=$('#makeVipModalWindow');
		makeVipModalWindow.find('.answer').html('').hide();
		makeVipModalWindow.find('form').show();
		var per_day_price=makeVipModalWindow.find('[name=per_day_price]').val();
		var realty_id=$(this).attr('alt');
		
		makeVipModalWindow.find('[name=realty_id]').val(realty_id);
		var days=makeVipModalWindow.find('[name=days]');
		days.val(1);
		
		var sum=makeVipModalWindow.find(".calc_price");
		sum.text(per_day_price);
		
		makeVipModalWindow.modal();
	});
	
	
	$('#makePremiumModalWindow').find('form').submit(function(){
		return false;
	});
	
	$('#makePremiumModalWindow').find('[name=days]').blur(function(){
		var v=Number($(this).val());
		var days=$('#makePremiumModalWindow').find('[name=days]');
		if(isNaN(v)){
			v=1;
			days.val(1);
		}else if(v==0){
			v=1;
			days.val(1);
		}else if(v<0){
			v=-1*v;
			days.val(v);
		}
		var per_day_price=$('#makePremiumModalWindow').find('[name=per_day_price]').val();
		$('#makePremiumModalWindow').find(".calc_price").text(v*per_day_price);
	});
	
	$('#makePremiumModalWindow').find('button.ok').click(function(){
		var makePremiumModalWindow=$('#makePremiumModalWindow');
		var realty_id=makePremiumModalWindow.find('[name=realty_id]').val();
		var _this=$(this);
		_this.attr('disabled', true);
		var per_day_price=makePremiumModalWindow.find('[name=per_day_price]').val();
		var days=makePremiumModalWindow.find('[name=days]');
		
		var v=days.val();
		var v=v.replace(/D/g,'');
		var d=+v;
		if(d>0){
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				dataType: 'html',
				type: 'post',
				data: {action:'make_special_payment', days:d, per_day:per_day_price, realty_id:realty_id, payment_type:'premium'},
				success: function(html){
					if(html!='error'){
						makePremiumModalWindow.find('.answer').html(html).show();
						makePremiumModalWindow.find('form').hide();
						$('a[alt='+realty_id+']').each(function(){
							if($(this).hasClass('make_premium_modal')){
								$(this).hide();
							}
						});
						setTimeout(function(){
							/*_this.parents('td').eq(0).find('.vb').eq(0).show();
							_this.remove();*/
							_this.attr('disabled', false);
							makePremiumModalWindow.modal('hide');
						},2000);
					}else{
						_this.attr('disabled', false);
					}
				},
				error: function(){
					_this.attr('disabled', false);
				}
			});
		}
		return false;
	});
	
	$(".make_premium_modal").click(function(){
		var makePremiumModalWindow=$('#makePremiumModalWindow');
		makePremiumModalWindow.find('.answer').html('').hide();
		makePremiumModalWindow.find('form').show();
		var per_day_price=makePremiumModalWindow.find('[name=per_day_price]').val();
		var realty_id=$(this).attr('alt');
		
		makePremiumModalWindow.find('[name=realty_id]').val(realty_id);
		var days=makePremiumModalWindow.find('[name=days]');
		days.val(1);
		
		var sum=makePremiumModalWindow.find(".calc_price");
		sum.text(per_day_price);
		
		makePremiumModalWindow.modal();
	});
	
	
	$('#makeBoldModalWindow').find('form').submit(function(){
		return false;
	});
	
	$('#makeBoldModalWindow').find('[name=days]').blur(function(){
		var v=Number($(this).val());
		var days=$('#makeBoldModalWindow').find('[name=days]');
		if(isNaN(v)){
			v=1;
			days.val(1);
		}else if(v==0){
			v=1;
			days.val(1);
		}else if(v<0){
			v=-1*v;
			days.val(v);
		}
		var per_day_price=$('#makeBoldModalWindow').find('[name=per_day_price]').val();
		$('#makeBoldModalWindow').find(".calc_price").text(v*per_day_price);
	});
	
	$('#makeBoldModalWindow').find('button.ok').click(function(){
		var makeBoldModalWindow=$('#makeBoldModalWindow');
		var realty_id=makeBoldModalWindow.find('[name=realty_id]').val();
		var _this=$(this);
		_this.attr('disabled', true);
		var per_day_price=makeBoldModalWindow.find('[name=per_day_price]').val();
		var days=makeBoldModalWindow.find('[name=days]');
		
		var v=days.val();
		var v=v.replace(/D/g,'');
		var d=+v;
		if(d>0){
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				dataType: 'html',
				type: 'post',
				data: {action:'make_special_payment', days:d, per_day:per_day_price, realty_id:realty_id, payment_type:'bold'},
				success: function(html){
					if(html!='error'){
						makeBoldModalWindow.find('.answer').html(html).show();
						makeBoldModalWindow.find('form').hide();
						$('a[alt='+realty_id+']').each(function(){
							if($(this).hasClass('make_bold_modal')){
								$(this).hide();
							}
						});
						setTimeout(function(){
							_this.attr('disabled', false);
							makeBoldModalWindow.modal('hide');
						},2000);
					}else{
						_this.attr('disabled', false);
					}
				},
				error: function(){
					_this.attr('disabled', false);
				}
			});
		}
		return false;
	});
	
	$(".make_bold_modal").click(function(){
		var makeBoldModalWindow=$('#makeBoldModalWindow');
		makeBoldModalWindow.find('.answer').html('').hide();
		makeBoldModalWindow.find('form').show();
		var per_day_price=makeBoldModalWindow.find('[name=per_day_price]').val();
		var realty_id=$(this).attr('alt');
		
		makeBoldModalWindow.find('[name=realty_id]').val(realty_id);
		var days=makeBoldModalWindow.find('[name=days]');
		days.val(1);
		
		var sum=makeBoldModalWindow.find(".calc_price");
		sum.text(per_day_price);
		
		makeBoldModalWindow.modal();
	});
});

</script>

<style>

#rounded-corner img {
	max-width: none;
}

.make_vip_window, .make_premium_window, .make_bold_window {
padding: 10px;
/*width: 200px;*/
position: absolute;
display: none;
background-color: white;
border: 1px solid #CFCFCF;
border-radius: 5px 5px 5px 5px;
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
float: right;
margin: 3px 0 10px 3px;
padding-bottom: 10px;
z-index: 1000;
}





.make_vip_window .options {

display: block;

}


</style>

{/literal}

<div class="modal fade" class="makeVip" id="makeVipModalWindow" tabindex="-1" role="dialog" aria-labelledby="prettyRegisterOk" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="myModalLabel">Установка статуса VIP</h3>
  </div>
  <div class="modal-body">
  	<form class="form-horizontal">
  		<input type="hidden" value="" name="realty_id" />
  		<input type="hidden" value="{$per_day_price}" name="per_day_price" />
		  <div class="control-group">
		    <label class="control-label">Дней</label>
		    <div class="controls">
		      <input type="text" value="1" name="days" />
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

<div class="modal fade" class="makePremium" id="makePremiumModalWindow" tabindex="-1" role="dialog" aria-labelledby="prettyRegisterOk" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="myModalLabel">Установка статуса Премиум</h3>
  </div>
  <div class="modal-body">
  	<form class="form-horizontal">
  		<input type="hidden" value="" name="realty_id" />
  		<input type="hidden" value="{$per_day_price_premium}" name="per_day_price" />
		  <div class="control-group">
		    <label class="control-label">Дней</label>
		    <div class="controls">
		      <input type="text" value="1" name="days" />
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

<div class="modal fade" class="makeBold" id="makeBoldModalWindow" tabindex="-1" role="dialog" aria-labelledby="prettyRegisterOk" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
    <h3 id="myModalLabel">Установка статуса Выделено</h3>
  </div>
  <div class="modal-body">
  	<form class="form-horizontal">
  	"Выделенное объявление"- это услуга позволяет выделить цветом Ваше объявление среди общего списка объявлений
			Данная услуга платная и предоставляется в соответствии с тарифами
  		<input type="hidden" value="" name="realty_id" />
  		<input type="hidden" value="{$per_day_price_bold}" name="per_day_price" />
		  <div class="control-group">
		    <label class="control-label">Дней</label>
		    <div class="controls">
		      <input type="text" value="1" name="days" />
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
{/if}