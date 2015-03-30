<div id="right_special">
<h3>VIP</h3>
<div class="clr"></div>
{section name=i loop=$special_items2}
{if $smarty.section.i.index < 4}
<div id="item">
<a href="{$special_items2[i].href}">{$special_items2[i].path}</a><br>
<div id="item_img">
{if $special_items2[i].img[0].preview != ''}

<a href="{$special_items2[i].href}">
<img src="{$estate_folder}/img/data/{$special_items2[i].img[0].preview}" border="0" />
</a>

{else}

<a href="{$special_items2[i].href}">
<img src="{$estate_folder}/img/no_foto.png" border="0" />
</a>

{/if}
<span class="price">{$special_items2[i].price|number_format:0:",":" "}</span>
</div>
{$special_items2[i].text|strip_tags|truncate:200}
</div>
{/if}
{/section}
</div>
