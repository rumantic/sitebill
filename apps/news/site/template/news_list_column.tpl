<div class="news_column">
<h1>Новости</h1>
{section name=i loop=$news_list_column}
<div class="news_item{if $smarty.section.i.iteration%2==0} odd{/if}">

<p>
	<time>{$news_list_column[i].date}</time>
	<a href="{$news_list_column[i].href}">{$news_list_column[i].title}</a>
</p>
{if $news_list_column[i].img_preview != ''}
<img src="{$news_list_column[i].img_preview}" />
{/if}
<div class="anons">{$news_list_column[i].anons|strip_tags|substr:0:300} <a href="{$news_list_column[i].href}">подробнее</a></div>
</div>

{/section}
<a href="{$estate_folder}/rss/"><img src="{$estate_folder}/template/frontend/agency/img/rss.gif" border="0"/></a>

</div>