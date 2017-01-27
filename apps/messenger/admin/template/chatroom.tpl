<div class="row-fluid">
    <div class="span12">
	{if $messenger_widget ne 'true'}
	    {if $messenger_frontend ne 'true'}
	    {/if}
	    <!--a href="?action=messenger&channel=bot" class="btn {if $smarty.request.channel == 'bot'}btn-success{/if}">Сообщения сайта</a-->
	    <a href="?action=messenger&do=new" class="btn btn-warning"><i class="ace-icon fa fa-cog bigger-150"></i> Настройки</a>
	{else}
	    <a href="{$estate_folder}/messenger/?action=messenger&do=new" class="btn btn-warning btn-small" target="_parent"><i class="ace-icon fa fa-cog"></i> Настройки</a>
	{/if}
	
    </div>
</div>

{$messenger_widget_inner}