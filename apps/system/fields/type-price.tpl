{if defined($smarty.const.NO_DYNAMIC_INCS) && $smarty.const.NO_DYNAMIC_INCS != 1}
{else}
<script src="{$estate_folder}/apps/system/js/autoNumeric.js"></script>
{/if}
{literal}<script type="text/javascript">$(document).ready(function() {$("#{/literal}{$id}{literal}").autoNumeric({aSep: ' ', vMax: '999999999999', vMin: '0'});});</script>{/literal}
<input type="text" id="{$id}" class="price_field form-control" name="{$item_array.name}" value="{$item_array.value}" />
