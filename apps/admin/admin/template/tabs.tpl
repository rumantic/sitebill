<link rel="stylesheet" href="{$MAIN_URL}/template/backend/admin/css/menu.css">
<div class="menu">
<ul>
{section name=i loop=$tabs}
<li class="page_item {if $tabs[i].current==1}current_page_item{/if}"><a href="{$tabs[i].url}">{$tabs[i].title}</a></li>
{/section}
</ul>
</div>