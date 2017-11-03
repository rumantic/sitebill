<form method="post" class="remind_form" action="{$remind_href}">
	<div class="form-group">
		<label for="login">
		{if $email_as_login==1}
			{$TYPE_LOGIN_PASS_EMAILMODE}
		{else}
			{$TYPE_LOGIN_PASS}
		{/if}
		</label>
		<input type="text" name="login" id="login" placeholder="">
	</div>
	<input type="submit" name="submit" value="{$SEND_PASSWORD}">
</form>