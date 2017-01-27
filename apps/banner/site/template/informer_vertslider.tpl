{if $realty|count>0}
<div class="sInformer-slider-{$biid} sInformer-slider-vert">
<div class="sInformer-slider-vert-container">
<a href="#" class="prev"></a>
<div class="sInformer-carousel">
<ul>
{foreach from=$realty item=fdata}

<li>
<div class="brief">
	<div class="image">
		<a href="{$fdata._href}" target="_blank"><img src="{$fdata._photofield}" alt=""></a>
	</div>
	<div class="text" style="display: block;">
		<a href="{$fdata._href}" target="_blank">{$fdata._textblock}</a>
	</div>
</div>
</li>
{/foreach}
</ul>
</div>
<a href="#" class="next"></a>
</div>
</div>
{/if}