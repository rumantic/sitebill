<form method="post" class="remind_recovery_form" action="{$recovery_href}">
	<div class="form-group">
		<label for="login">{$TYPE_RECOVERY_CODE}</label>
		<input type="text" name="recovery_code" id="recovery_code" placeholder="">
	</div>
	<input type="submit" name="submit" value="{$SEND_RECOVERYCODE}">
</form>