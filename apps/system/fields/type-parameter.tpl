{literal}
<script type="text/javascript">
    $(document).ready(function(){
	$(document).on("click", ".paramsrow a", function(){$(this).parents(".paramsrow").eq(0).remove();return false;});
	$("#add_column_params").click(function(){
	    var pr=$(this).parents("#paramsblock").eq(0).find(".paramsrow:last").clone();
	    $(this).before(pr);
            return false;
        });
    });
</script>
<div id="paramsblock">
{/literal}
	{if $item_array['value']|count>0}
	    {foreach from=$item_array['value'] key=pk item=pv}
		{if $pk>'' &&  $pk!='0'}
                <div class="paramsrow">
	            <input type="text" name="parameters[name][]" value="{$pk}" />=<input type="text" name="parameters[value][]" value="{$pv}" />
                    <a href="javascript:void(0);">x</a>
                </div>
		{/if}
	    {/foreach}
	{/if}

{literal}
<div class="paramsrow">
    <input type="text" name="parameters[name][]" value="" />=<input type="text" name="parameters[value][]" value="" />
    <a href="javascript:void(0);">x</a>
</div>
<button id="add_column_params">Add</button></div>
{/literal}