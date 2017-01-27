{if $apps_comment_on==1}
<div class="row-fluid">
	<div class="span12">
	<h3>Комментарии</h3>
		<div class="app_comments_list_container">
		
			{foreach from=$app_comment_comments item=comment}
			<p class="pull-right">{$comment.comment_date}</p>
	<p>{$comment.fio}  {$comment.comment_text}</p>
	{/foreach}
		</div>
	
	
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
	<h3>Добавить комментарий</h3>
	 {$app_comment_form}
	</div>
</div>
	
{/if}