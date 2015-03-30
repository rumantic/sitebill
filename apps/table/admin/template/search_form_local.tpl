<script type="text/javascript" src="/apps/system/js/refresher.functions.js"></script>
<div class="local_search_form" style="margin: 10px;">
<form method="get" action="{$estate_folder}/index.php">
{*$selected_columns|print_r*}
  <fieldset>
  <div class="row-fluid">
    {foreach name=fe1 from=$selected_columns item=sc}
    {if $sc.type=='hidden'}
    	{$sc.html}
    {else}
	    <div class="span4">
			<label>{$sc.title}</label>{$sc.html}
		</div>
		
		{if $smarty.foreach.fe1.iteration%3==0}
		</div><div class="row-fluid">
		{/if}
	{/if}
	{/foreach}
   </div>
    <div class="row-fluid"><a href="#" class="btn reset_form">Очистить</a> <button type="submit" class="btn btn-primary pull-right">{$L_GOSEARCH}</button></div>
  </fieldset>
</form>
</div>