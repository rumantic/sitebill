<li><a href="{relativeurl path="account/data?do=new"}">{$L_ADD_ADV}</a></li>
<li><a href="{relativeurl path="account/profile"}" >{$L_MY_PROFILE}</a></li>
<li><a href="{relativeurl path="account/data"}" >{$L_MY_ADVS}</a></li>
{if $realtylogv2_on==1}<li><a href="{$estate_folder}/{$realtylogv2_namespace}/trash/" >{_e t="Корзина"} ({$trash_count})</a></li>{/if}
{if $mailbox_panel ne ''}<li><a href="{relativeurl path="mailbox"}" >{$mailbox_panel}</a></li>{/if}
{if $mysearch_panel ne ''}<li><a href="{relativeurl path="mysearch"}" >{$mysearch_panel}</a></li>{/if}
{if $app_company_namespace ne ''}<li><a href="{$estate_folder}/{$app_company_namespace}/my/" >{_e t="Компания"}</a></li>{/if}
{if $apps_reservation_on==1}<li><a href="{relativeurl path="reservation/my"}" >{_e t="Бронирования"}</a></li>{/if}
{if $apps_billing == 'on'}<li><a href="{relativeurl path="billing/invoices"}" >{_e t="Счета"}</a></li>{/if}

<li><a href="{relativeurl path="account/balance"}" >{$L_MY_BALANCE} ({$ballance} {$L_RUR_SHORT})</a></li>
    {if $smarty.session.current_user_group_name eq 'admin'}
    <li><a href="{$estate_folder}/admin/" >{_e t="Админка"}</a></li>
    {/if}
