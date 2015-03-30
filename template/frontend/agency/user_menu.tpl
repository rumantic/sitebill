<div class="btn-group">
<a class="btn btn-success btn-small dropdown-toggle" data-toggle="dropdown" href="#">
    {$fio}
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">
    <!-- <li><a href="{$estate_folder}/account/profile/">{$L_HELLO}, {$fio}!</a></li> -->
	<li><a href="{$estate_folder}/account/data/?do=new">{$L_ADD_ADV}</a></li>
	<li><a href="{$estate_folder}/account/profile/" >{$L_MY_PROFILE}</a></li>
	<li><a href="{$estate_folder}/account/data/" >{$L_MY_ADVS}</a></li>
	{if $realtylogv2_on==1}<li><a href="{$estate_folder}/{$realtylogv2_namespace}/trash/" >Корзина ({$trash_count})</a></li>{/if}
	{if $mailbox_panel ne ''}<li><a href="{$estate_folder}/mailbox/" >{$mailbox_panel}</a></li>{/if}
	{if $mysearch_panel ne ''}<li><a href="{$estate_folder}/mysearch/" >{$mysearch_panel}</a></li>{/if}
	{if $app_company_namespace ne ''}<li><a href="{$estate_folder}/{$app_company_namespace}/my/" >Мои компании</a></li>{/if}
	<li><a href="{$estate_folder}/account/balance/" >{$L_MY_BALANCE} ({$ballance} {$L_RUR_SHORT})</a></li>
	<li><a href="{$estate_folder}/logout/" class="logout">{$L_LOGOUT_BUTTON}</a></li>
  </ul>
</div>