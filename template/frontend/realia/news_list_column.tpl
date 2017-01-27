{if $news_list_column|count>0}
<div class="features">
	<h2 class="page-header">{$LT_NEWS}</h2>
	<div class="row">
	{foreach name=nlc from=$news_list_column item=news_list_column_item}
		<div class="item span4">
            <div class="row">
                <div class="icon span1">
		    <a href="{$news_list_column_item.href}">
		{if $news_list_column_item.img_preview != ''}
                    <img src="{$estate_folder}{$news_list_column_item.img_preview}" alt="">
		{else}
                    <img src="{$estate_folder}/template/frontend/realia/img/icons/features-pencil.png" alt="">
		{/if}
		    </a>
                </div>

                <div class="text span3">
                    <h3>{$news_list_column_item.title}</h3>
                    <p>{$news_list_column_item.anons|strip_tags|truncate:200}</p>
                    <a href="{$news_list_column_item.href}">Читать</a>
                </div>
            </div>
        </div>
        {if $smarty.foreach.nlc.iteration%3==0}
        </div>
        <div class="row">
        {/if}
	{/foreach}
	</div>
</div>
{/if}