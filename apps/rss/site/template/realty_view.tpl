{if $_hvdata.text.value ne ''}{$_hvdata.text.value}<br />{/if}
{foreach from=$_hvdata item=data_item}
{if $data_item.type eq "primary_key" or $data_item.value eq "0" or $data_item.value eq "" or $data_item.name eq "currency_id" or $data_item.name eq "export_cian" or $data_item.name eq "user_id" or $data_item.name eq "price"  or $data_item.name eq "youtube" or $data_item.type eq "hidden" or $data_item.name eq "text" or $data_item.type eq "geodata" or $data_item.name eq "meta_keywords"  or $data_item.name eq "meta_description" or $data_item.name eq "meta_title" or $data_item.type eq "uploadify_image" or $data_item.type eq "uploads"}
{elseif $data_item.type eq "select_by_query"}
{if $data_item.value_string!=''}
<b>{$data_item.title}:</b> {$data_item.value_string} | 
{/if}
{elseif $data_item.type eq "select_box_structure"}
{if $data_item.value_string!=''}
<b>{$data_item.title}:</b> {$data_item.value_string} | 
{/if}
{elseif $data_item.type eq "checkbox"}
{if $data_item.name ne 'hot' and $data_item.name ne 'active'}
{if $data_item.value eq 1}
<b>{$data_item.title}:</b> <input type="checkbox" checked="checked" disabled="disabled" /> | 
{/if}
{/if}            
{elseif $data_item.type eq "select_box"}
{if $data_item.value_string!=''}
<b>{$data_item.title}:</b> {$data_item.value_string} | 
{/if}
{elseif $data_item.type eq "tlocation"}
<b>{$data_item.title}:</b> {$data_item.tlocation_string} | 
{else}
{if $data_item.value!=''}
<b>{$data_item.title}:</b> {if is_array($data_item.value)}{$data_item.value|implode:','}{else}{$data_item.value}{/if} | 
{/if}
{/if}
{/foreach}