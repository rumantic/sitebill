{if $realty|count>0}
	{if $view_type=='hs2'}
		<div class="gallery-3">
		<div class="container">
		
		<ul>
		{foreach from=$realty item=fdata}
		
		<li>
		<div class="brief" style="width: 200px;">
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
		<div class="prev"></div>
		<div class="next"></div>
		</div></div>
	{else}
		<div class="sInformer-slider-{$biid} sInformer-slider-hor">
		<a href="#" class="prev"></a>
		<div class="sInformer-slider-hor-container">
		
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
		
		</div>
		<a href="#" class="next"></a>
		</div>
		{literal}
		<style>
		.sInformer-slider-{/literal}{$biid}{literal}  .brief {
			height: {/literal}{$eheight}{literal}px;
			width: {/literal}{$ewidth}{literal}px;
		}
		</style>
		{/literal}
	{/if}
{/if}