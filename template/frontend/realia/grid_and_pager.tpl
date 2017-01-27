{if $grid_items|count==0}     
	<h1 class="page-header">Ничего не удалось найти</h1>
{else}
	<h1 class="page-header">{$title}</h1>

	{if $smarty.request.page == 1 or $smarty.request.page == '' }
	<span itemprop="description">{$description}</span>
	{/if}
	
	
	{assign var="lang_topic_name" value="name_{$smarty.session._lang}"}
	
	
	{if $smarty.session.grid_type eq 'thumbs'}
			{include file='realty_grid_thumbs.tpl'}
		{else}
			{include file='realty_grid_list.tpl'}
		{/if}
	
	
	
	
	{foreach from=$pager_array.pages item=pager_page}
		{if $pager_page.current==1}
			{assign var=__curpagenr value=$pager_page.text}
		{/if}
	{/foreach}
	
	{if $__curpagenr-3<1}
		{assign var=__startnr value=1}
		{assign var=__leftsep value=0}
	{else}
		{assign var=__startnr value=$__curpagenr-3}
		{assign var=__leftsep value=1}
	{/if}
	
	{if $__curpagenr+3>$pager_array.pages|count}
		{assign var=__endnr value=$pager_array.pages|count}
		{assign var=__rightsep value=0}
	{else}
		{assign var=__endnr value=$__curpagenr+3}
		{assign var=__rightsep value=1}
	{/if}
	
	{if $pager_array.pages|count>1}
	<div class="pagination pagination-centered">
	<ul>
		<li><a href="{$pager_array.ppn.href}">&lsaquo;</a></li>
		{if $__leftsep==1}
		<li><a href="{$pager_array.pages[1].href}">{$pager_array.pages[1].text}</a></li>
		<li><a href="javascript:void(0);" class="selected">...</a></li>
		{/if}
		{foreach from=$pager_array.pages item=pager_page}
		{if $pager_page.text>=$__startnr && $pager_page.text<=$__endnr}
		<li{if $pager_page.current==1} class="active"{/if}><a href="{$pager_page.href}">{$pager_page.text}</a></li>
		{/if}
		{/foreach}
		{if $__rightsep==1}
		<li><a href="javascript:void(0);" class="selected">...</a></li>
		<li><a href="{$pager_array.pages[$pager_array.pages|count].href}">{$pager_array.pages[$pager_array.pages|count].text}</a></li>
		{/if}
		<li><a href="{$pager_array.npn.href}">&rsaquo;</a></li>
	</ul>
	</div>
	{/if}
	
{/if}
		            
