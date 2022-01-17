<h1 class="page-header">{$HOT_ITEMS}</h1>
<div class="properties-grid">
    <div class="row">
        {assign var=itcount value=1}
        {section name=i loop=$special_items2}

            <div class="property span3">
                <div class="image">
                    <div class="content">
                        <a href="{$special_items2[i].href}"></a>
                        {if $special_items2[i].img[0].preview != ''}
                            <img src="{mediaincpath data=$special_items2[i].img[0] type='preview'}" width="870" />
                        {else}
                            <img src="{$estate_folder}/img/no_foto.png" />
                        {/if}
                    </div><!-- /.content -->

                    {if $special_items2[i].price_discount > 0}
                        <div class="price">
                            {$special_items2[i].price_discount|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}
                            <div class="price_discount">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                        </div>
                    {else}
                        <div class="price">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                    {/if}

                </div><!-- /.image -->

                <div class="title">
                    <h2><a href="{$special_items2[i].href}">{$special_items2[i].city}, {$special_items2[i].street}</a></h2>
                </div><!-- /.title -->

                <div class="location">
            {if $special_items2[i].topic_info.$lang_topic_name != ''}{$special_items2[i].topic_info.$lang_topic_name}{else}{$special_items2[i].type_sh}{/if}
        </div><!-- /.location -->
        <div class="area">
            <span class="key">{$L_SQUARE}:</span><!-- /.key -->
            <span class="value">{$special_items2[i].square_all}/{$special_items2[i].square_live}/{$special_items2[i].square_kitchen}</span><!-- /.value -->
        </div><!-- /.area -->
        <div class="area">
            <span class="key">{$L_FLOOR}:</span><!-- /.key -->
            <span class="value">{$special_items2[i].floor}/{$special_items2[i].floor_count}</span><!-- /.value -->
        </div><!-- /.area -->
    </div><!-- /.property -->
    {assign var=itcount value=$itcount+1}
    {if $itcount==4}
    </div><!-- /.row -->
    <div class="row">
        {assign var=itcount value=1}
    {/if}
{/section}



</div><!-- /.row -->
</div><!-- /.properties-grid -->