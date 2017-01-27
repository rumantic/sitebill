{foreach from=$list_items item=list_item}
<tr>
{foreach from=$used_fields item=used_field}
	<td>{$list_item[$used_field]}</td>
{/foreach}
<td>
		<a href="{$estate_folder}/admin?action={$action}&do=edit&{$primary_key}={$list_item[$primary_key]}" class="btn btn-info btn-mini" target="_blank"><i class="icon-white icon-pencil"></i></a>
		<a onclick="{literal}if ( confirm('Действительно хотите удалить запись?') ) {return true;} else {return false;}{/literal}" href="{$estate_folder}/admin?action={$action}&do=delete&{$primary_key}={$list_item[$primary_key]}" class="btn btn-danger btn-mini delete_me" data-id="{$list_item[$primary_key]}"><i class="icon-white icon-remove"></i></a>
	</td>
</tr>
{/foreach}