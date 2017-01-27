{foreach from=$app_comment_comments item=comment}
<p class="pull-right">{$comment.comment_date}</p>
<h4>{$comment.fio}</h4>
<p>{$comment.comment_text}</p>
<hr/>
{/foreach}