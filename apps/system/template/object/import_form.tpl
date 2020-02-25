<style>
.loading {
	padding: 5px;
	width: 16px;
	height: 16px;
	background-image: url({$estate_folder}/apps/system/css/images/loading.gif);
	background-position: center;
	background-repeat: no-repeat;
	display: none;
}
#uploads_result {
    width: 100%;
    overflow: scroll;
    height: 350px;
}
</style>
{if isset($uploads_item->scripts) && $uploads_item->scripts|count>0}
	{foreach from=$uploads_item->scripts item=form_element_script}
		{$form_element_script}
	{/foreach}
{/if}

{$uploads_item->collection[0].html}
<div class="col-xs-12">
<span><div id="uploads_result">
</div></span>
<div class="loading"></div>
</div>
<div class="col-xs-12" id="button_block" style="display: none;">
<button class="btn btn-success" id="ok_button"><i class="icon-white icon-ok"></i> Загрузить в базу</button> 
</div>