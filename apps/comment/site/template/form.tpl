<form id="app_comment_form">
<table>
<input type="hidden" name="user_id" value="{$app_comment_user_id}" />
<input type="hidden" name="object_id" value="{$app_comment_object_id}" />
<input type="hidden" name="object_type" value="{$app_comment_object_type}" />
<tr>
<td class="errors" style="display: none;">Необходимо создать комментарий</td>
</tr>
<tr>
<td>Добавить комментарий</td>
</tr>
<tr>
<td><textarea name="text"></textarea></td>
</tr>
<tr>
<td><input type="submit" class="btn btn-info" id="submit" value="Добавить" /></td>

</tr>
</table>
</form>