informers
<table class="table">
<thead>
<tr>
<th>#</th>
<th>Код</th>
<th>--</th>
<th>Домен</th>
<th>Активность</th>
<th></th>
</tr>
</thead>
<tbody>
{foreach from=$bi item=informer}

<tr>
<td>{$informer.biid}</td>
<td>{$informer.access_code}</td>
<td><a href="{$estate_folder}/admin/?action=banner&do=informers&subdo=code&biid={$informer.biid}">Код для вставки</a></td>
<td>{$informer.informer_parameters.domain}</td>
<td>{if $informer.is_active==1}Активен{/if}</td>
<td>
	<a href="{$estate_folder}/admin/?action=banner&do=informers&subdo=edit&biid={$informer.biid}" class="btn btn-info"><i class="icon-white icon-pencil"></i></a>
	<a href="#" class="btn btn-danger"><i class="icon-white icon-remove"></i></a>
</td>
</tr>
{/foreach}
</tbody>
</table>
