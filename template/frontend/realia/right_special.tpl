<div class="widget properties last">
    <div class="title">
        <h2>VIP</h2>
    </div><!-- /.title -->

    <div class="content">
    	{section name=i loop=$special_items2}
        <div class="property">
            <div class="image">
                <a href="{$special_items2[i].href}"></a>
                {if $special_items2[i].img[0].preview != ''}
                <img src="{$estate_folder}/img/data/{$special_items2[i].img[0].preview}" />
                {else}
                <img src="{$estate_folder}/template/frontend/realia/img/no_foto_100x74.png" />
                {/if}
            </div><!-- /.image -->

            <div class="wrapper">
                <div class="title">
                    <h3>
                    	<a href="{$special_items2[i].href}">{if $special_items2[i].topic_info.$lang_topic_name != ''}{$special_items2[i].topic_info.$lang_topic_name}{else}{$special_items2[i].type_sh}{/if}</a>
                    </h3>
                </div>
                <div class="location">{$special_items2[i].city}, {$special_items2[i].street}</div>
                {if $special_items2[i].price_discount > 0}
                <div class="price">
                {$special_items2[i].price_discount|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}
                <div class="price_discount_special">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                </div>
                {else}
                <div class="price">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                {/if}
            </div>
        </div>
		{/section}
    </div>
</div>