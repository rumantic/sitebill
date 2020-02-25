{if $item_array.parameters.lined|intval == 1}
    {assign var="fields" value=$item_array.parameters.fields|explode:"|"}
    {literal}
    <script type="text/javascript">
        $(document).ready(function() {
            $( "#{/literal}{$id}{literal}" ).SitebillLineEditor({fields: ["{/literal}{$fields|implode:","}{literal}"]});
        });
    </script>
    {/literal}
{/if}
<textarea id="{$id}" class="{$classes.textarea}" name="{$item_array.name}" rows="{$item_array.rows}" cols="{$item_array.cols}"{if $item_array.parameters.styles != ''} style="{$item_array.parameters.styles}"{/if}>{$item_array.value|escape}</textarea>