{assign var="controls_js" value=$SITEBILL_DOCUMENT_ROOT|cat:'/apps/admin/admin/template1/controls_js.tpl'}

{include file=$controls_js}

{assign var="local_controls_js" value=$SITEBILL_DOCUMENT_ROOT|cat:'/template/frontend/local/admin/data/controls_js.tpl'}
{if file_exists($local_controls_js)}
    {include file="$local_controls_js"}
{/if}


{$legacy_rs}
