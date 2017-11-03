<ul class="pagination">
{if $pager_settings.show_end_links==1}
	{if $paging.current_page==1}
	<li><span class="pagenav">&laquo;&laquo;</span></li>
	{else}
	<li><a rel="nofollow" href="{$paging.fpn.href}" data-page="1" class="pagenav">&laquo;&laquo;</a></li>
	{/if}
{/if}
{if $pager_settings.show_prev_links==1}
	{if $paging.current_page==1}
	<li><span class="pagenav">&laquo;</span></li>
	{else}
	<li><a rel="nofollow" href="{$paging.ppn.href}" data-page="{$paging.ppn.go_page}" class="pagenav">&laquo;</a></li>
	{/if}
{/if}
{if $pager_settings.draw_all_pages==1}
	{foreach from=$paging.pages item=page}
		{if $page.current==1}
		<li><span class="pagenav">{$page.text}</span></li>
		{else}
		<li><a rel="nofollow" href="{$page.href}" data-page="{$page.text}" class="pagenav">{$page.text}</a></li>
		{/if}
	{/foreach}
{else}
	{if $pager_settings.left_prefix==1}
	<li>...</li>
	{/if}
	{foreach from=$paging.pages item=page}		
		{if $page.text>=$pager_settings.start && $page.text<=$pager_settings.end}
			{if $page.current==1}
			<li><span class="pagenav">{$page.text}</span></li>
			{else}
			<li><a rel="nofollow" href="{$page.href}" data-page="{$page.text}" class="pagenav">{$page.text}</a></li>
			{/if}
		{/if}		
	{/foreach}
	{if $pager_settings.right_prefix==1}
	<li>...</li>
	{/if}
{/if}
{if $pager_settings.show_prev_links==1}
	{if $paging.current_page==$paging.total_pages}
	<li><span class="pagenav">&raquo;</span></li>
	{else}
	<li><a rel="nofollow" href="{$paging.npn.href}" data-page="{$paging.npn.go_page}" class="pagenav">&raquo;</a></li>
	{/if}
{/if}
{if $pager_settings.show_end_links==1}
	{if $paging.current_page==$paging.total_pages}
	<li><span class="pagenav">&raquo;&raquo;</span></li>
	{else}
	<li><a rel="nofollow" href="{$paging.lpn.href}" data-page="{$paging.total_pages}" class="pagenav">&raquo;&raquo;</a></li>
	{/if}
{/if}
</ul>