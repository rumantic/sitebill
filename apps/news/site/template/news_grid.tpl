<link rel="stylesheet" href="{$estate_folder}/apps/news/site/template/css/style.css">
{if $description!=''}
<div class="news-description">
{$description}
</div>
{/if}
<div id="news" class="archive">
	{section name=i loop=$news}
	<div class="news">
        <div class="date">{$news[i].date}</div>
        <div class="title"><a href="{$news[i].href}">{$news[i].title}</a></div>
        
		{if $news[i].prev_img neq ''}
		<div class="image_news"><a href="{$news[i].href}"><img src="{$estate_folder}{$news[i].prev_img}" border="0" alt="{$news[i].title}" /></a></div>
		
		{/if}
		<div class="anons">{$news[i].anons}</div>
		{*$news[i]._news_topic_id_.name*}
	</div>
	<div class="clear"></div>
	{/section}
	
</div>
{$news_pager}