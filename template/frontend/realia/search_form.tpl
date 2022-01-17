<h2><editable id="search_title_edit" data-file="search_form.tpl">{$LT_SEARCH}</editable></h2>
<script type="text/javascript" src="{$estate_folder}/js/autoNumeric-1.7.5.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/mycombobox_ac.js"></script>
<script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/search_form.js"></script>
{literal}
    <script>
    var max_price ={/literal}{if $max_price ne ''}{$max_price}{else}0{/if}{literal};
        var price_from = Number({/literal}{if isset($price_min)}{$price_min}{else}0{/if}{literal});
            var price_for = Number({/literal}{if (isset($price)) && ($price ne '')}{$price}{else}{$max_price}{/if}{literal});
    </script>
{/literal}





{include file='standart_search_form.tpl'}
