<link rel="stylesheet" href="{$estate_folder}/apps/news/site/template/css/style.css">
<div id="news" class="archive">
	{section name=i loop=$news}
	<div class="news">
        <div class="date">{$news[i].date}</div>
        <div class="title"><a href="{$news[i].href}">{$news[i].title}</a></div>
        
		{if $news[i].prev_img neq ''}
		<div class="image_news"><a href="{$news[i].href}"><img src="{$estate_folder}{$news[i].prev_img}" border="0" alt="{$news[i].title}" /></a></div>
		
		{/if}
		<div class="anons">{$news[i].anons}</div>
		
	</div>
	<div class="clear"></div>
	{/section}
	
</div>	
	
	
	{if isset($news_paging)}
	
		{if $news_paging.pages|count>1}
		
			{foreach from=$news_paging.pages item=pager_page}
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
				{assign var=__endnr value=$news_paging.pages|count}
				{assign var=__rightsep value=0}
			{else}
				{assign var=__endnr value=$__curpagenr+3}
				{assign var=__rightsep value=1}
			{/if}
			
			{if $news_paging.pages|count>1}
			<div class="pagination pagination-centered">
			<ul>
				<li><a href="{$news_paging.ppn.href}">&lsaquo;</a></li>
				{if $__leftsep==1}
				<li><a href="{$news_paging.pages[1].href}">{$news_paging.pages[1].text}</a></li>
				<li><a href="javascript:void(0);" class="selected">...</a></li>
				{/if}
				{foreach from=$news_paging.pages item=pager_page}
				{if $pager_page.text>=$__startnr && $pager_page.text<=$__endnr}
				<li{if $pager_page.current==1} class="active"{/if}><a href="{$pager_page.href}">{$pager_page.text}</a></li>
				{/if}
				{/foreach}
				{if $__rightsep==1}
				<li><a href="javascript:void(0);" class="selected">...</a></li>
				<li><a href="{$news_paging.pages[$news_paging.pages|count].href}">{$news_paging.pages[$news_paging.pages|count].text}</a></li>
				{/if}
				<li><a href="{$news_paging.npn.href}">&rsaquo;</a></li>
			</ul>
			</div>
			{/if}
		{/if}
	{elseif isset($pager)}
	<div class="pagination" align="center">
		<span class="pages">{$L_PAGE}:</span>
		{section name=j loop=$pager}
		
			{if $pager[j].current eq 1}
			<span class="current">{$pager[j].text}</span>
			{else}
				{if $pager[j].href neq ''}
				<a href="{$pager[j].href}">{$pager[j].text}</a>
				{else}
				<span>{$pager[j].text}</span>
				{/if}
			{/if}
		
		{/section}
	</div>
	{/if}