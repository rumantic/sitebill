{if $apps_upper_enable}
<div class="clear"><h1>Приветствуем, {$fio}! </h1></div>
{if !$upps_left && $packs_left==0}
<p>Вы исчерпали лимит поднятий за неделю.</p>
{else}
	
<p>У вас осталось {if $upps_left != ''}{$upps_left}{else}0{/if} подъема(ов) объявлений.</p>
{/if}
<p>В дополнительных пакетах: {$packs_left}</p>
<p>Количество подъемов обновляется раз в неделю.</p>

<a id="buy_ups" href="javascript:void(0);">Покупка пакета поднятий</a>

{literal}
<style>

#buy_ups_window {
padding: 10px;
background-color: #eee;
position: absolute;
display: none;
}


#buy_ups_window #options {
display: block;
}
#buy_ups_window #payment {
display: none;
}

</style>

<script type="text/javascript">
$(document).ready(function(){
	
	
	$("#buy_ups").click(function(e){
        var ups_price={/literal}{$ups_price};{literal}
		mvwindow=$("#buy_ups_window");
		mvwindow.css({'left':(e.pageX+20)+'px','top':(e.pageY+20)+'px'}).toggle();
		var days=mvwindow.find("[name=count]");
		var sum=mvwindow.find("#calc_price");
		var closer=mvwindow.find("#close");
		sum.text(ups_price);
		
		var submit=mvwindow.find("[type=submit]");
		days.keyup(function(){
			var v=Number($(this).val());
			if(isNaN(v)){
				v=1;
				$(this).val(1);
			}else if(v==0){
				v=1;
				$(this).val(1);
			}
			sum.text(v*ups_price);
		});
		
		closer.click(function(){
			$("#buy_ups_window").hide();
		});
			
		submit.click(function(){
			var v=days.val();
			var v=v.replace(/\D/g,'');
			var d=+v;
			if(d>0){
				$.ajax({
					url: estate_folder+'/js/ajax.php',
					dataType: 'html',
					type: 'post',
					data: {action:'make_special_payment',days:d,per_day:ups_price,payment_type:'buy_ups'},
					success: function(html){
						if(html!='error'){
							mvwindow.find('#options').html(html);
							setTimeout(function(){
								$('#buy_ups').remove();
								mvwindow.hide();
								mvwindow.remove();
							},3000);
						}
					},
					error: function(){
						
					}
				});
			}
		});
	});
	
	
});
</script>
{/literal}

<div id="buy_ups_window">
	<div id="options">
		<input type="hidden" value="{$id}" name="realty_id" />
		<table>
			<tr>
				<td>Количество поднятий</td>
				<td><input type="text" value="1" name="count" /></td>
			</tr>
			<tr>
				<td>Цена</td>
				<td><span id="calc_price"></span></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Оплатить" /></td>
			</tr>
		</table>
		<button id="close">Отменить</button>
	</div>
	
</div>
{/if}
<p align="left">
Если вы хотите сдать или продать свою недвижимость,<br>то внимательно заполняйте все поля формы.<br> Чем более полную информацию вы укажете, тем быстрее будет найден клиент
</p>