{if $blogRecords|count>0}
{foreach from=$blogRecords item=blogRecord}
	<h2><a href="{$blogRecord.href}">{$blogRecord.title}</a></h2>
	<p>{$blogRecord.body}</p>
{/foreach}

{if $blog_pager_array.pages|count>1}
{foreach from=$blog_pager_array.pages item=pager_page}
	{if $pager_page.current==1}
		{assign var=__curpagenr value=$pager_page.text}
	{/if}
{/foreach}
{if $__curpagenr-3<=2}
	{assign var=__startnr value=1}
	{assign var=__leftsep value=0}
{else}
	{assign var=__startnr value=$__curpagenr-3}
	{assign var=__leftsep value=1}
{/if}

{if $__curpagenr+4>=$blog_pager_array.pages|count}
	{assign var=__endnr value=$blog_pager_array.pages|count}
	{assign var=__rightsep value=0}
{else}
	{assign var=__endnr value=$__curpagenr+3}
	{assign var=__rightsep value=1}
{/if}


<div class="pagination">
	<ul>
		<li><a href="{$blog_pager_array.ppn.href}">&lsaquo;</a></li>
		{if $__leftsep==1}
		<li><a href="{$blog_pager_array.pages[1].href}">{$blog_pager_array.pages[1].text}</a></li>
		<li class="disabled"><a href="javascript:void(0);">...</a></li>
		{/if}
		{foreach from=$blog_pager_array.pages item=pager_page}
		{if $pager_page.text>=$__startnr && $pager_page.text<=$__endnr}
		<li{if $pager_page.current==1} class="active"{/if}><a href="{$pager_page.href}">{$pager_page.text}</a></li>
		{/if}
		{/foreach}
		{if $__rightsep==1}
		<li class="disabled"><a href="javascript:void(0);">...</a></li>
		<li><a href="{$blog_pager_array.pages[$blog_pager_array.pages|count].href}">{$blog_pager_array.pages[$blog_pager_array.pages|count].text}</a></li>
		{/if}
		<li><a href="{$blog_pager_array.npn.href}">&rsaquo;</a></li>
	</ul>
</div>
{/if}
{/if}