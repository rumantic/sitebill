<div class="infobox">
	{if isset($realty.img)}
	<div class="image" style="max-height: 100px; overflow-y: hidden;">
		<img src="{$estate_folder}/img/data/{$realty.img[0].preview}" width="100" />
	</div>
	{/if}

	<div class="title">
		<a href="{$realty.href}">
		{if $realty.city ne ''}
			{$realty.city},
		{/if}
		{if $realty.street ne ''}{$realty.street}{if $realty.number ne ''}, {$realty.number} {/if}{/if}
		</a>
	</div>
	<div class="area">
		<span class="key">{$L_SQUARE} м<sup>2</sup>:</span>
		<span class="value">{$realty.square_all}</span>
	</div>

	{if $realty.price_discount > 0}
       <div class="price">
          {$realty.price_discount|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}
          <div class="price_discount_map">{$realty.price|number_format:0:",":" "} {if $realty.currency_name != ''}{$realty.currency_name}{/if}</div>
       </div><!-- /.price -->
       {else}
       <div class="price">{$realty.price|number_format:0:",":" "} {if $realty.currency_name != ''}{$realty.currency_name}{/if}</div>
       {/if}
	<div class="link">
		<a target="_blank" href="{$realty.href}">{$L_MORE}</a>
	</div>
</div>