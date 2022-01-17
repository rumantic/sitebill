{assign var="lang_data_text" value="text_{$smarty.session._lang}"}

<div class="properties-rows">

    {include file='realty_grid_filter.tpl'}
    <div class="row">
        {section name=i loop=$grid_items}
            <div class="property span9{if $grid_items[i].bold_status==1} grid_list_bold{/if}{if $grid_items[i].premium_status==1} grid_list_premium{/if}{if $grid_items[i].vip_status==1} grid_list_vip{/if}">
                <div class="row">
                    <div class="image span3">
                        <div class="content">
                            <a href="{$grid_items[i].href}"></a>
                            {if $grid_items[i].img != '' }
                                <img src="{mediaincpath data=$grid_items[i].img[0] type='preview'}" class="previewi">
                            {else}
                                <img src="{$estate_folder}/template/frontend/realia/img/no_foto_270x200.png" class="previewi">
                            {/if}
                        </div><!-- /.content -->
                    </div><!-- /.image -->

                    <div class="body span6">
                        <div class="title-price row">
                            <div class="title span4">
                                <h2>
                                    {if isset($smarty.session.favorites)}
                                        {if in_array($grid_items[i].id,$smarty.session.favorites)}
                                            <a class="fav-rem" alt="{$grid_items[i].id}" title="{$L_DELETEFROMFAVORITES}" href="#remove_from_favorites"></a>
                                        {else}
                                            <a class="fav-add" alt="{$grid_items[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
                                        {/if}
                                    {else}
                                        <a class="fav-add" alt="{$grid_items[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
                                    {/if}
                                    <a href="{$grid_items[i].href}">
                                        {if $grid_items[i].city ne ''} {$grid_items[i].city}{if
                    $grid_items[i].street ne ''}, {$grid_items[i].street}{if
                    $grid_items[i].number ne ''}, {$grid_items[i].number}{/if}{/if}
                                        {else} {if $grid_items[i].street ne ''} {$grid_items[i].street}{if
                    $grid_items[i].number ne ''}, {$grid_items[i].number}{/if} {/if}
                                            {/if}
                                        </a>
                                    </h2>
                                </div><!-- /.title -->
                                {if $grid_items[i].price_discount > 0}
                                    <div class="price">
                                        {$grid_items[i].price_discount|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}
                                        <div class="price_discount_list">{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</div>
                                    </div><!-- /.price -->
                                {else}
                                    <div class="price">{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</div>
                                {/if}
                            </div><!-- /.title -->

                            <div class="location">{if $grid_items[i].topic_info.$lang_topic_name != ''}{$grid_items[i].topic_info.$lang_topic_name}{else}{$grid_items[i].type_sh}{/if}</div><!-- /.location -->
                            <p>
                                {if $grid_items[i].$lang_data_text != ''}
                                    {$grid_items[i].$lang_data_text|strip_tags|truncate:200}
                                {else}
                                    {$grid_items[i].text|strip_tags|truncate:200}
                                {/if}
                            </p>
                            <div class="area">
                                <span class="key">{$L_SQUARE} {_e t="м"}<sup>2</sup>:</span><!-- /.key -->
                                <span class="value">{$grid_items[i].square_all}/{$grid_items[i].square_live}/{$grid_items[i].square_kitchen}</span><!-- /.value -->
                            </div><!-- /.area -->
                            <div class="area">
                                <span class="key">{$L_FLOOR}:</span><!-- /.key -->
                                <span class="value">{$grid_items[i].floor}/{$grid_items[i].floor_count}</span><!-- /.value -->
                            </div><!-- /.area -->
                        </div><!-- /.body -->
                    </div><!-- /.property -->
                </div><!-- /.row -->
                {/section}
                </div>
            </div>
