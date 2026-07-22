{if !isset($disable_td_wrap)}
    <td class="max-w-[200px] leading-10">
{/if}

    <button data-id="{$grid_item.id.value}" class="fast_preview inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-400 hover:text-accent-700 hover:bg-accent-50 transition-colors" title="Просмотр">
        <i class="icon-eye-open"></i>
    </button>

    {if $data_adv_share_access_can_view_all and $grid_item.user_id.value != $data_adv_share_access_user_id}
    {else}
        {if isset($show_up_icon) && $show_up_icon}
            <a class="go_up inline-flex items-center justify-center w-8 h-8 rounded-md text-amber-500 hover:text-amber-600 hover:bg-amber-50 transition-colors" alt="{$grid_item.id.value}" href="#grow_up" title="Поднять">
                <i class="icon-circle-arrow-up"></i>
            </a>
        {/if}

        <a href="{$estate_folder_control}?action=data&do=edit&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-400 hover:text-accent-700 hover:bg-accent-50 transition-colors" title="Редактировать">
            <i class="icon-pencil"></i>
        </a>

        {if intval($grid_item.archived.value)==1}
            <a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?action=data&do=delete_final&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-rose-600 hover:bg-rose-50 transition-colors" title="Удалить окончательно">
                <i class="icon-remove"></i>
            </a>
            <a href="{$estate_folder_control}?action=data&do=restore&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-emerald-600 hover:bg-emerald-50 transition-colors" title="Восстановить">
                <i class="icon-ok"></i>
            </a>
        {else}
            <a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?action=data&{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-rose-600 hover:bg-rose-50 transition-colors" title="Удалить">
                <i class="icon-remove"></i>
            </a>
        {/if}

        <a title="{if $grid_item.active.value == 1}{_e t="выключить"}{else}{_e t="включить"}{/if}" data-id="{$grid_item.id.value}" data-active="{$grid_item.active.value}" class="active_toggle inline-flex items-center justify-center w-8 h-8 rounded-md transition-colors {if $grid_item.active.value == 1}text-emerald-600 hover:bg-emerald-50{else}text-rose-600 hover:bg-rose-50{/if}">
            <i class="icon-off"></i>
        </a>

        {if {getConfig key='apps.mailbox.use_complaint_mode'} eq 1}
            <a title="{_e t="Пожаловаться"}" data-id="{$grid_item.id.value}" data-complaint-id="1" class="mailbox_complaint_send_complaint inline-flex items-center justify-center w-8 h-8 rounded-md transition-colors {if $grid_item.complaint_id.value > 0}text-pink-500 hover:bg-pink-50{else}text-slate-400 hover:bg-slate-100{/if}">
                <i class="fa-exclamation-circle"></i>
            </a>
        {/if}

        {if {getConfig key='apps.reservation.enable'} == 1}
            <a class="inline-flex items-center justify-center w-8 h-8 rounded-md text-amber-500 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Цены бронирования" href="/account/reservation/my/{$grid_item.id.value}/" target="_blank">
                <i class="fa-calendar"></i>
            </a>
        {/if}

        <div class="clearfix"></div>

        {if isset($grid_item.status_id)}
            <div class="flex items-center space-x-1 mt-1">
                {if intval($grid_item.status_id.value)===1}
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=2&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-violet-500 hover:text-violet-600 hover:bg-violet-50 transition-colors" title="На прозвон">
                        <i class="icon-refresh"></i>
                    </a>
                {elseif intval($grid_item.status_id.value)===2}
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=1&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-emerald-600 hover:bg-emerald-50 transition-colors" title="Дозвонились">
                        <i class="glyphicon glyphicon-phone-alt"></i>
                    </a>
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=3&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-pink-500 hover:bg-pink-50 transition-colors" title="Не дозвонились">
                        <i class="icon-phone"></i>
                    </a>
                {elseif intval($grid_item.status_id.value)===3}
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=1&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-emerald-600 hover:bg-emerald-50 transition-colors" title="Дозвонились">
                        <i class="glyphicon glyphicon-phone-alt"></i>
                    </a>
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=2&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-violet-500 hover:text-violet-600 hover:bg-violet-50 transition-colors" title="На прозвон">
                        <i class="icon-refresh"></i>
                    </a>
                {else}
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=3&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-pink-500 hover:bg-pink-50 transition-colors" title="Не дозвонились">
                        <i class="icon-phone"></i>
                    </a>
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=1&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-emerald-600 hover:bg-emerald-50 transition-colors" title="Дозвонились">
                        <i class="glyphicon glyphicon-phone-alt"></i>
                    </a>
                    <a href="{$estate_folder_control}?action=data&do=set_status&status_id={$smarty.request.status_id}&page={$smarty.request.page}&set_status_id=2&id={$grid_item.id.value}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-violet-500 hover:text-violet-600 hover:bg-violet-50 transition-colors" title="На прозвон">
                        <i class="icon-refresh"></i>
                    </a>
                {/if}
            </div>
        {/if}
    {/if}

    {if $billing_controls_tpl != ''}
        {include file=$billing_controls_tpl item=$grid_item}
    {/if}
    {assign var="local_controls" value=$SITEBILL_DOCUMENT_ROOT|cat:'/template/frontend/local/admin/data/controls.tpl'}
    {if file_exists($local_controls)}
        {include file="$local_controls"}
    {/if}
    {$grid_item._memo}

{if !isset($disable_td_wrap)}
</td>
{/if}
