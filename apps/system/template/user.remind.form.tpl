<form method="post" class="remind_form" action="{$remind_href}">
	<div class="form-group">
		<label for="login">
		{if $email_as_login==1}
			{_e t="Укажите Ваш E-mail"}
		{else}
			{_e t="Укажите Ваш логин или E-mail"}
		{/if}
		</label>
		<input type="text" name="login" id="login" placeholder="">
	</div>
	{if {getConfig key='apps.sms.allow_sms_register'} eq 1}
	<div class="form-group">
		<label for="mobile">
			{_e t="Или ваш телефон"}
		</label>
		<input type="text" name="mobile" id="mobile" placeholder="">
	</div>
	{/if}
	<input type="submit" name="submit" value="{_e t="Восстановить пароль"}">
</form>
