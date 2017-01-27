{assign var=x value=array()}
{foreach from=$data_set item=data_item}
{if $data_item.type eq "primary_key" or $data_item.value eq "0" or $data_item.value eq "" or $data_item.name eq "currency_id" or $data_item.name eq "export_cian" or $data_item.name eq "user_id" or $data_item.name eq "price"  or $data_item.name eq "youtube" or $data_item.type eq "hidden" or $data_item.name eq "text" or $data_item.type eq "geodata" or $data_item.name eq "meta_keywords"  or $data_item.name eq "meta_description" or $data_item.name eq "meta_title" or $data_item.type eq "uploadify_image" or $data_item.type eq "uploads" or $data_item.name eq "fio" or $data_item.name eq "phone" or $data_item.name eq "email"}
{elseif $data_item.type eq "select_by_query"}
{if $data_item.value_string!=''}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b>'|cat:$data_item.value_string}
{/if}
{elseif $data_item.type eq "select_box_structure"}
{if $data_item.value_string!=''}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b>'|cat:$data_item.value_string}
{/if}
{elseif $data_item.type eq "checkbox"}
{if $data_item.name ne 'hot' and $data_item.name ne 'active'}
{if $data_item.value eq 1}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b><input type="checkbox" checked="checked" disabled="disabled" />'}
{/if}
{/if}            
{elseif $data_item.type eq "select_box"}
{if $data_item.value_string!=''}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b>'|cat:$data_item.value_string}
{/if}
{elseif $data_item.type eq "tlocation"}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b>'|cat:$data_item.tlocation_string}
{else}
{if $data_item.value!=''}
{if is_array($data_item.value)}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b>'|cat:$data_item.value|implode:','}
{else}
{append var=x value='<b>'|cat:$data_item.title|cat:':</b>'|cat:$data_item.value}
{/if}
{/if}
{/if}
{/foreach}
{if $x|count>0}<br>
{$x|implode:', '}
{/if}