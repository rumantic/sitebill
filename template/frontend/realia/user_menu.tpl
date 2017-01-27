<li><a href="{$estate_folder}/account/data/?do=new">{$L_ADD_ADV}</a></li>
<li><a href="{$estate_folder}/account/profile/" >{$L_MY_PROFILE}</a></li>
<li><a href="{$estate_folder}/account/data/" >{$L_MY_ADVS}</a></li>
{if $realtylogv2_on==1}<li><a href="{$estate_folder}/{$realtylogv2_namespace}/trash/" >Корзина ({$trash_count})</a></li>{/if}
{if $mailbox_panel ne ''}<li><a href="{$estate_folder}/mailbox/" >{$mailbox_panel}</a></li>{/if}
{if $mysearch_panel ne ''}<li><a href="{$estate_folder}/mysearch/" >{$mysearch_panel}</a></li>{/if}
{if $app_company_namespace ne ''}<li><a href="{$estate_folder}/{$app_company_namespace}/my/" >Мои компании</a></li>{/if}
<li><a href="{$estate_folder}/account/balance/" >{$L_MY_BALANCE} ({$ballance} {$L_RUR_SHORT})</a></li>
{if $smarty.session.current_user_group_name eq 'admin'}
<li><a href="{$estate_folder}/admin/" >Админка</a></li>
{/if}
