
<script type="text/javascript" src="{$estate_folder}/js/autoNumeric-1.7.5.js"></script>
<script>
{literal}
$(document).ready(function(){
	$(document).on('change', '.property-filter select[name=region_id]', function(){
		setTimeout(function(){
			$(".property-filter select[name=city_id]").chosen({
				disable_search_threshold: 10
			});
			$(".property-filter select[name=street_id]").chosen({
				disable_search_threshold: 10
			});
		}, 500);
	});
	$(document).on('change', '.property-filter select[name=city_id]', function(){
		setTimeout(function(){
			$(".property-filter select[name=street_id]").chosen({
				disable_search_threshold: 10
			});
		}, 500);
	});
});
var max_price={/literal}{if $max_price ne ''}{$max_price}{else}0{/if}{literal};
var price_from=Number({/literal}{if isset($price_min)}{$price_min}{else}0{/if}{literal});
var price_for=Number({/literal}{if (isset($price)) && ($price ne '')}{$price}{else}{$max_price}{/if}{literal});
{/literal}
</script>
{$ajax_functions}
{foreach from=$scripts item=script}
	{*$script*}
{/foreach}
{if isset($local_search_forms) && $local_search_forms|count>0}
	<ul class="nav nav-tabs" id="search_forms_tabs">
	  <li><a href="#main_sf" data-toggle="tab">Все</a></li>
	  {foreach from=$local_search_forms key=ftname item=ftdata}
	  <li{if $ftdata.active==1} class="active"{/if}><a href="#{$ftdata.id}" data-toggle="tab">{$ftname}</a></li>
	  {/foreach}
	</ul>
{else}

{/if}
	

{if isset($local_search_forms) && $local_search_forms|count>0}
<div class="tab-content">
	<div class="tab-pane" id="main_sf">
		{include file='new_search_form.tpl'}
	</div>
	{foreach from=$local_search_forms key=ftname item=ftdata}
	<div class="tab-pane{if $ftdata.active==1} active{/if}" id="{$ftdata.id}">
		{$ftdata.body}
	</div>
	{/foreach}
</div>	
{else}
	{include file='new_search_form.tpl'}
{/if}