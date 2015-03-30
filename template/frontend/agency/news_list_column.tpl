{section name=i loop=$news_list_column}

<h3>{$news_list_column[i].title}</h3>
<span class="date">{$news_list_column[i].date}</span> 
<span class="anons"><a href="{$news_list_column[i].href}">{$news_list_column[i].anons|strip_tags|truncate:100}</a></span>


{if $news_list_column[i].img_preview_src != '' }
            <div id="ess">
            <img class="objphoto" src="{$news_list_column[i].img_preview_src}">
            </div>
{/if}
<div class="clear"></div><br>
{/section}
<a href="{$estate_folder}/rss/"><img src="{$estate_folder}/template/frontend/agency/img/rss.gif" border="0"/></a>

