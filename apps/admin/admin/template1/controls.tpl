<td nowrap>
    <button data-id="{$grid_item.id.value}" class="fast_preview btn btn-danger btn-mini"><i class="icon-white icon-eye-open"></i></button>
    {if $data_adv_share_access_can_view_all and $grid_item.user_id.value != $data_adv_share_access_user_id}
        {*Если у нас включена опция data_adv_share_access и мы включили опцию data_adv_share_access_can_view_all и при этом
        идентификатор пользователя текущего объявления в генерации грида отличается от пользователя админки, то прячем контролы
        *}
    {else}

        {if isset($show_up_icon) && $show_up_icon}
            <a class="btn btn-warning go_up btn-mini" alt="{$grid_item.id.value}" href="#grow_up"><i class="icon-white icon-circle-arrow-up"></i></a>
        {/if}

        <a href="{$estate_folder_control}?action=data&do=edit&id={$grid_item.id.value}" class="btn btn-info btn-mini"><i class="icon-white icon-pencil"></i></a>
        {if intval($grid_item.archived.value)==1}
            <a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?action=data&do=delete_final&id={$grid_item.id.value}" class="btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
            <a href="{$estate_folder_control}?action=data&do=restore&id={$grid_item.id.value}" class="btn btn-success btn-mini"><i class="icon-white icon-ok"></i></a>
        {else}
            <a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?action=data&{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_item.id.value}" class="btn btn-danger btn-mini"><i class="icon-white icon-remove"></i></a>
        {/if}
        <a title="{if $grid_item.active.value == 1}{_e t="выключить"}{else}{_e t="включить"}{/if}" data-id="{$grid_item.id.value}" data-active="{$grid_item.active.value}" class="active_toggle btn {if $grid_item.active.value == 1}btn-success{else}btn-danger{/if} btn-mini"><i class="icon-white icon-off"></i></a>


        <div class="clearfix"></div>
        {if isset($grid_item.status_id)}


            {if intval($grid_item.status_id.value)===1}
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=2&id={$grid_item.id.value}" class="btn btn-purple btn-mini" title="На прозвон">
                    <i class="icon-refresh"></i>
                </a>
            {elseif intval($grid_item.status_id.value)===2}
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=1&id={$grid_item.id.value}" class="btn btn-success btn-mini" title="Дозвонились">
                    <i class="glyphicon glyphicon-phone-alt"></i>
                </a>
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=3&id={$grid_item.id.value}" class="btn btn-pink btn-mini" title="Не дозвонились">
                    <i class="icon-phone"></i>
                </a>
            {elseif intval($grid_item.status_id.value)===3}
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=1&id={$grid_item.id.value}" class="btn btn-success btn-mini" title="Дозвонились">
                    <i class="glyphicon glyphicon-phone-alt"></i>
                </a>
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=2&id={$grid_item.id.value}" class="btn btn-purple btn-mini" title="На прозвон">
                    <i class="icon-refresh"></i>
                </a>
            {else}
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=3&id={$grid_item.id.value}" class="btn btn-pink btn-mini" title="Не дозвонились">
                    <i class="icon-phone"></i>
                </a>
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=1&id={$grid_item.id.value}" class="btn btn-success btn-mini" title="Дозвонились">
                    <i class="glyphicon glyphicon-phone-alt"></i>
                </a>
                <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=2&id={$grid_item.id.value}" class="btn btn-purple btn-mini" title="На прозвон">
                    <i class="icon-refresh"></i>
                </a>
            {/if}
        {/if}
    {/if}
    {if $billing_controls_tpl != ''}
        {include file=$billing_controls_tpl item=$grid_item}
    {/if}

</td>
