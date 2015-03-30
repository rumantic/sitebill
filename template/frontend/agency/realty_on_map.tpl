<div class="realty_on_map">
{if isset($realty.img)}
<img src="{$estate_folder}/img/data/{$realty.img[0].preview}" width="50" align="left" hspace="5" vspace="5" />
{/if}
<a href="{$realty.href}" target="_blank">{if $realty.city ne ''}
	{$realty.city},  
{/if}
{if $realty.street ne ''}{$realty.street}{if $realty.number ne ''}, {$realty.number} {/if}{/if}
{if $realty.square_all!='' && $realty.square_all!=0}{$realty.square_all}{else}-{/if}/{if $realty.square_kitchen!='' && $realty.square_kitchen!=0}{$realty.square_kitchen}{else}-{/if}/{if $realty.square_live!='' && $realty.square_live!=0}{$realty.square_live}{else}-{/if}, 
<br>
<b>{$realty.price|number_format:0:",":" "}</b>
</a>
</div>