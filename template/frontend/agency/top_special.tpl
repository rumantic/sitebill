<div id="top_special">
<div id="es">
<h1>{$L_SPECIAL_OFFERS}</h1>
</div>
<div class="clr"></div>
{section name=i loop=$special_items}
<div id="item">
{if $special_items[i].img[0].preview != ''}
<div id="item_img"><a href="{$estate_folder}/realty{$special_items[i].id}.html"><img src="{$estate_folder}/img/data/{$special_items[i].img[0].preview}" border="0" /></a></div>
{else}
<div id="item_img"><a href="{$estate_folder}/realty{$special_items[i].id}.html"><img src="{$estate_folder}/img/no_photo.png" border="0" /></a></div>
{/if}
<a href="{$estate_folder}/realty{$special_items[i].id}.html">{$special_items[i].path}</a><br>

</div>
{/section}
</div>
