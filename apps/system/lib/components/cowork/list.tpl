{if $list|count>0}
<table class="table">
    {foreach from=$list item=item}
    <tr>
        <td>[{$item.user_id.value}] {$item.login.value}</td>
        <td>{$item.parent_user_id.value_string} [{$item.parent_user_id.value}]</td>
        <td><a href="{$estate_folder}/admin/?action=cowork&do=off&user_id={$item.user_id.value}" class="btn">Отключить наставника</a></td>
        <td><a href="{$estate_folder}/admin/?action=cowork&do=off_full&user_id={$item.user_id.value}" class="btn">Отключить наставника от Стажера и от всех его объектов</a></td>
    </tr>
    {/foreach}
</table>  
{/if}            