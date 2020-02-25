<div class="pagination pagination-centered">
<ul class="pagination">
{if $pager_settings.show_end_links==1}
	{if $paging.current_page==1}
	<li><a href="javascript:void(0);">‹‹</a></li>
	{else}
	<li><a href="{$paging.fpn.href}">‹‹</a></li>
	{/if}
{/if}
{if $pager_settings.show_prev_links==1}
	{if $paging.current_page==1}
	<li><a href="javascript:void(0);">‹</a></li>
	{else}
	<li><a href="{$paging.ppn.href}">‹</a></li>
	{/if}
{/if}
{if $pager_settings.draw_all_pages==1}
	{foreach from=$paging.pages item=page}
		{if $page.current==1}
		<li class="active"><a href="{$page.href}">{$page.text}</a></li>
		{else}
		<li><a href="{$page.href}">{$page.text}</a></li>
		{/if}
	{/foreach}
{else}
	{if $pager_settings.left_prefix==1}
	<li><a href="javascript:void(0);" class="selected">...</a></li>
	{/if}
	{foreach from=$paging.pages item=page}		
		{if $page.text>=$pager_settings.start && $page.text<=$pager_settings.end}
			{if $page.current==1}
			<li class="active"><a href="{$page.href}">{$page.text}</a></li>
			{else}
			<li><a href="{$page.href}">{$page.text}</a></li>
			{/if}
		{/if}		
	{/foreach}
	{if $pager_settings.right_prefix==1}
	<li><a href="javascript:void(0);" class="selected">...</a></li>
	{/if}
{/if}
{if $pager_settings.show_prev_links==1}
	{if $paging.current_page==$paging.total_pages}
	<li><a href="javascript:void(0);">›</a></li>
	{else}
	<li><a href="{$paging.npn.href}">›</a></li>
	{/if}
{/if}
{if $pager_settings.show_end_links==1}
	{if $paging.current_page==$paging.total_pages}
	<li><a href="javascript:void(0);">››</a></li>
	{else}
	<li><a href="{$paging.lpn.href}">››</a></li>
	{/if}
{/if}
</ul>
</div>