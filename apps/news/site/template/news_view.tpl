<link rel="stylesheet" href="{$estate_folder}/apps/news/site/template/css/style.css">
<div id="singleNews">
	<div class="date">{$news.date.value_string}</div>
	<div class="title">{$news.title.value}</div>
	<div class="share">
	</div>

	<div class="text">
		{if $news.prev_img neq ''}
		<img src="{$news.normal_img}" width="300" alt="{$news.title.value}" class="alignleft" align="left" style="padding: 10px;" />
		{/if}
		{$news.description.value}
	</div><!-- .text -->
	<div style="clear:both;"></div>
</div><!-- #singleNews -->

{if $more_news|count>0}
<div id="news" class="archive">
    {section name=i loop=$more_news}
    <div class="news">
        <div class="date">{$more_news[i].date}</div>
        <div class="title"><a href="{$more_news[i].href}">{$more_news[i].title}</a></div>
        
        {if $more_news[i].prev_img neq ''}
        <div class="image_news"><a href="{$more_news[i].href}"><img src="{$estate_folder}{$more_news[i].prev_img}" border="0" alt="{$more_news[i].title}" /></a></div>
        
        {/if}
        <div class="anons">{$more_news[i].anons|strip_tags}</div>
        
    </div>
    <div class="clear"></div>
    {/section}
    
</div>
{/if}
{if 1==0} 
<!-- <script src="{$estate_folder}/apps/comment/js/comment_controller.js"></script> -->
{literal}
<script>
/*$(document).ready(function(){
	$('.cmnts').Comment_Controller({object_type:'news', object_id:'{/literal}{$news.news_id.value}{literal}'});
});*/
</script>
{/literal}
<div class="cmnts"></div>{/if}