{literal}
<style>
#app_comment_panel {
padding: 20px;
margin: 10px;
border: 1px solid #eee;
}

#app_comment_panel #app_comments_list {
border-bottom: 1px solid #eee;
margin-top: 30px;
}

#app_comment_form textarea {
width: 620px;
height: 75px;
}
</style>
{/literal}

<div id="app_comment_panel">
	<script type="text/javascript" src="{$estate_folder}/apps/comment/js/comment_manager.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		Comment_Manager.run();
	});
	
	</script>
	
	<div id="app_comments_list">
		<h3>Комментарии</h3>
		<div class="app_comments_list_container">
			{include file=$app_comment_list}
		</div>
	</div>
	{$app_comment_form}
</div>