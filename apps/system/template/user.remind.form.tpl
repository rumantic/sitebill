<form method="post" class="remind_form" action="{$remind_href}">
	<div class="form-group">
		<label for="login">
		{if $email_as_login==1}
			{if $TYPE_LOGIN_PASS_EMAILMODE == ''}{$TYPE_LOGIN_PASS_EMAILMODE}{else}{_e t="Укажите Ваш E-mail"}{/if}
		{else}
			{if $TYPE_LOGIN_PASS == ''}{$TYPE_LOGIN_PASS}{else}{_e t="Укажите Ваш логин или E-mail"}{/if}
		{/if}
		</label>
		<input type="text" name="login" id="login" placeholder="">
	</div>
	<input type="submit" name="submit" value="{if $SEND_PASSWORD == ''}{$SEND_PASSWORD}{else}{_e t="Отправить пароль"}{/if}">
</form>
