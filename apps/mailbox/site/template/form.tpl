<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
{literal}
<style>

#contact_with_author_window, #send_friend_window {
	background-color: White;
	position:absolute;
	z-index:10000;
	width:315px;
	border: 1px solid Silver;
	display: none;
	border-radius: 5px;
}

#contact_with_author_window .closer, #send_friend_window .closer {
	height: 16px;
	width: 16px;
	float: right;
	cursor: pointer;
	background-image: url('{/literal}{$estate_folder}{literal}/img/publish_x.png');
}

#contact_with_author_window label, #send_friend_window label {
	display: block;
}

#contact_with_author_window div.inner div, #send_friend_window div.inner div {
	margin: 10px;
}

#contact_with_author_window div.inner input, #contact_with_author_window div.inner textarea, #send_friend_window div.inner input, #send_friend_window div.inner textarea {
	margin: 2px 0;
}

#contact_with_author_window div.inner textarea, #send_friend_window div.inner textarea {
	/*width: 290px;*/
}

</style>
<script type="text/javascript">
function hideErrors(){
	$('#contact_with_author_window form #error_block').hide();
	$('#contact_with_author_window form #error_block_nouser').hide();
	
	$('#send_friend_window form #error_block').hide();
	$('#send_friend_window form #error_block_nouser').hide();
}
$(document).ready(function(){
	$('a#contact_with_author').click(function(e){
		var dialog=$('#contact_with_author_window');
		dialog.appendTo($('body'));
		var dialog=$('#contact_with_author_window');
		var form=$('#contact_with_author_window form');
		var offset = $(this).offset();
		hideErrors();
		$.ajax({
			url: estate_folder+'/apps/mailbox/js/ajax.php',
			data: 'action=get_logged_user_data',
			dataType: 'json',
			success: function(json){
				if(json.res!=='no_user'){
					form.find('[name=name]').val(json.fio);
					form.find('[name=phone]').val(json.phone);
					form.find('[name=email]').val(json.email);
				}
				
			}
		});
		//alert(e.pageX +', '+ e.pageY);
		var pos=SitebillCore.getDialogPositionCoords(dialog.width(),dialog.height());
			
		
		dialog.css({'top':pos[1]+'px','left':pos[0]+'px'});
		dialog.fadeIn();
		return false;
	});
	
	$('a#send_friend').click(function(){
		var form=$('#send_friend_window form');
		var dialog=$('#send_friend_window');
		dialog.appendTo($('body'));
		var dialog=$('#send_friend_window');
		form.find('[name=link]').val(window.location.href);
		hideErrors();
		$.ajax({
			url: estate_folder+'/apps/mailbox/js/ajax.php',
			data: 'action=get_logged_user_data',
			dataType: 'json',
			success: function(json){
				if(json.res!=='no_user'){
					form.find('[name=email]').val(json.email);
				}
				
			}
		});
		var pos=SitebillCore.getDialogPositionCoords(dialog.width(),dialog.height());
		dialog.css({'top':pos[1]+'px','left':pos[0]+'px'});
		dialog.fadeIn();
		return false;
	});
		
	$('#contact_with_author_window form').submit(function(){
		
		var form=$(this);
		hideErrors();
		var name=form.find('[name=name]').val();
		var phone=form.find('[name=phone]').val();
		var email=form.find('[name=email]').val();
		var message=form.find('[name=message]').val();
		var theme=form.find('[name=theme]').val();
		var to=form.find('[name=to]').val();
		var realty_id=form.find('[name=realty_id]').val();
		//console.log(name+' '+phone+' '+email+' '+message+' '+theme);
		if(name=='' || message=='' || email=='' || theme==''){
			form.find('#error_block').show();
			
		}else{
			$.ajax({
				type: 'post',
				url: estate_folder+'/apps/mailbox/js/ajax.php',
				data: {action:'send_message',name:name,message:message,theme:theme,email:email,phone:phone,reciever_id:to,realty_id:realty_id},
				dataType: 'json',
				success: function(json){
					if(json.answer=='fields_not_specified'){
						form.find('#error_block').show();
					}else if(json.answer=='no_reciever'){
						form.find('#error_block_nouser').show();
					}else{
						form.find('[name=name]').val('');
						form.find('[name=phone]').val('');
						form.find('[name=email]').val('');
						form.find('[name=message]').val('');
						
						$('#contact_with_author_window').hide();
					}
				}
			});
		}
		return false;
	});
	
	$('#send_friend_window form').submit(function(){
		
		var form=$(this);
		hideErrors();
		var email=form.find('[name=email]').val();
		var link=form.find('[name=link]').val();
		var message=form.find('[name=message]').val();
		var to=form.find('[name=to]').val();
		//var realty_id=form.find('[name=realty_id]').val();
		//console.log(name+' '+phone+' '+email+' '+message+' '+theme);
		if(message=='' || email=='' || to==''){
			form.find('#error_block').show();
			
		}else{
			$.ajax({
				type: 'post',
				url: estate_folder+'/apps/mailbox/js/ajax.php',
				data: {action:'send_friend_message',link:link,name:name,message:message,email:email,to:to},
				dataType: 'json',
				success: function(json){
					if(json.answer=='fields_not_specified'){
						form.find('#error_block').show();
					}else if(json.answer=='no_reciever'){
						form.find('#error_block_nouser').show();
					}else{
						form.find('[name=email]').val('');
						form.find('[name=message]').val('');
						form.find('[name=to]').val('');
						form.find('[name=link]').val('');
						$('#send_friend_window').hide();
					}
				}
			});
		}
		return false;
	});
	
	//console.log(uid);
	//console.log($('#contact_with_author_window').height());
	$('#contact_with_author_window').find('.closer').click(function(){
		$('#contact_with_author_window').fadeOut();
	});
	
	$('#send_friend_window').find('.closer').click(function(){
		$('#send_friend_window').fadeOut();
	});
		
		
	//});
});
</script>
{/literal}
<div class="mailbox-options">
<span><a href="#" id="contact_with_author" class="btn btn-info"><i class="icon-white icon-envelope"></i> {if $message_to_author_title != ''}{$message_to_author_title}{else}Заявка{/if}</a></span>
<span><a href="#" id="send_friend" class="btn btn-info"><i class="icon-white icon-thumbs-up"></i> {if $message_to_friend_title != ''}{$message_to_friend_title}{else}Поделиться{/if}</a></span>
</div>
<div id="contact_with_author_window" style="display: none;">
<div class="closer"></div>
<div class="inner">
	<form>
	
	<div id="error_block">Не заполнены все поля</div>
	<div id="error_block_nouser">Нет возможности отправить сообщение</div>
	<input type="hidden" name="realty_id" value="{$data.id.value}" />
	<input type="hidden" name="to" value="{$to}" />
	<div><label>Тема</label><input type="text" name="theme" value="{if isset($theme)}{$theme}{else}{', '|implode:$title_data} ID:{$data.id.value}{/if}" /></div>
	<div><label>Сообщение</label><textarea name="message"></textarea></div>
	<div><label>Имя</label><input type="text" name="name" /></div>
	<div><label>Телефон</label><input type="text" name="phone" /></div>
	<div><label>E-mail</label><input type="text" name="email" /></div>
	<div><input type="submit" value="Отправить" /></div>
	</form>
</div>
</div>
<div id="send_friend_window" style="display: none;">
<div class="closer"></div>
<div class="inner">
	<form>
	<div id="error_block">Не заполнены все поля</div>
	<div id="error_block_nouser">Нет возможности отправить сообщение</div>
	<input type="hidden" name="link" value="" />
	<div><label>Ваш E-mail</label><input type="text" name="email" /></div>
	<div><label>E-mail друга (можно несколько через запятую)</label><input type="text" name="to" /></div>
	<div><label>Сообщение</label><textarea name="message"></textarea></div>
	<div><input type="submit" value="Отправить" /></div>
	</form>
</div>
</div>