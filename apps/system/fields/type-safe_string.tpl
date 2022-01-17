{if isset($item_array['parameters']['dadata'])}
{if $item_array['parameters']['dadata']==1}
<link rel='stylesheet prefetch' href='https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/css/suggestions.min.css'>
<script src='https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/js/jquery.suggestions.min.js'></script>

{assign var="root_dir" value=$smarty.const.SITEBILL_DOCUMENT_ROOT}
{assign var="dadatascript" value="/template/frontend/$theme/js/dadata/dadata.js"}
{assign var="dadatascriptvile" value="$root_dir/template/frontend/$theme/js/dadata/dadata.js"}
{if file_exists($dadatascriptvile)}
<script src='{$smarty.const.SITEBILL_MAIN_URL}/template/frontend/{$theme}/js/dadata/dadata.js?t={$smarty.now}'></script>
{else}
<script src='{$smarty.const.SITEBILL_MAIN_URL}/apps/system/js/dadata/dadata.js?t={$smarty.now}'></script>
{/if}
{/if}

<script type="text/javascript">
$(document).ready(function () {literal}{{/literal}
    $("#{$id}").suggestions({literal}{{/literal}
        token: "f26c98c6b12d1deb3c1ea1205db88e5cf6e652a0",
        type: "ADDRESS",
        onSelect: showSelected
    {literal}}{/literal});
{literal}}{/literal});
</script>

{/if}
<input id="{$id}" placeholder="{if isset($item_array['placeholder'])}{$item_array['placeholder']}{else}{$item_array['title']}{/if}" type="text" class="{$classes['input']}" name="{$item_array['name']}" value="{$item_array['value']|escape}"{if isset($item_array['parameters']['styles']) and $item_array['parameters']['styles']>''} styles="{$item_array['parameters']['styles']}"{/if}{if isset($item_array['parameters']['onclick']) and $item_array['parameters']['onclick']>''} onclick="{$item_array['parameters']['onclick']}"{/if}{if isset($item_array['parameters']['onchange']) and $item_array['parameters']['onchange']>''} onchange="{$item_array['parameters']['onchange']}"{/if}/>
