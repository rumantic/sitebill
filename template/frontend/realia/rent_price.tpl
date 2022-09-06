{if $item.Price_per_1_day > 0}
<div class="price"><span style="font-size: 12px;">{$data_model.Price_per_1_day.title}:</span> {$item.Price_per_1_day|number_format:0:",":" "} {if $item.currency_name != ''}{$item.currency_name}{/if}</div>
{/if}
{if $item.Price_per_7_days > 0}
<div class="price"><span style="font-size: 12px;">{$data_model.Price_per_7_days.title}:</span> {$item.Price_per_7_days|number_format:0:",":" "} {if $item.currency_name != ''}{$item.currency_name}{/if}</div>
{/if}
{if $item.Price_for_1_month > 0}
<div class="price"><span style="font-size: 12px;">{$data_model.Price_for_1_month.title}:</span> {$item.Price_for_1_month|number_format:0:",":" "} {if $item.currency_name != ''}{$item.currency_name}{/if}</div>
{/if}
{if $item.Price_from_2nd_month > 0}
<div class="price"><span style="font-size: 12px;">{$data_model.Price_from_2nd_month.title}:</span> {$item.Price_from_2nd_month|number_format:0:",":" "} {if $item.currency_name != ''}{$item.currency_name}{/if}</div>
{/if}
{if $item.Price_for_1_year_rent > 0}
<div class="price"><span style="font-size: 12px;">{$data_model.Price_for_1_year_rent.title}:</span> {$item.Price_for_1_year_rent|number_format:0:",":" "} {if $item.currency_name != ''}{$item.currency_name}{/if}</div>
{/if}
