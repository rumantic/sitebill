<div class="properties-grid">
    <div class="row">
        {assign var=itcount value=1}
        {section name=i loop=$grid_items}

            <div class="property span3">
                <div class="image">
                    <div class="content">
                        <a href="{$grid_items[i].href}" target="_blank"></a>
                        {if $grid_items[i].img != '' } 
                            <img src="{$estate_folder}/img/data/{$grid_items[i].img[0].preview}" class="previewi">
                        {else}
                            <img src="{$estate_folder}/template/frontend/realia/img/no_foto_270x200.png" class="previewi">
                        {/if}
                    </div>
                    <div class="price">{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</div>
                </div>

                <div class="title">
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
                        <a href="{$grid_items[i].href}" target="_blank">
                            {if $grid_items[i].city ne ''} {$grid_items[i].city}{if
                    $grid_items[i].street ne ''}, {$grid_items[i].street}{if
                    $grid_items[i].number ne ''}, {$grid_items[i].number}{/if}{/if}
                            {else} {if $grid_items[i].street ne ''} {$grid_items[i].street}{if
                    $grid_items[i].number ne ''}, {$grid_items[i].number}{/if} {/if}
                                {/if}
                            </a>
                        </h2>
                    </div>

                    <div class="location">{$grid_items[i].type_sh}</div>
                    <div class="area">
                        <span class="key">{$L_SQUARE} {_e t="м"}<sup>2</sup>:</span>
                        <span class="value">{$grid_items[i].square_all}/{$grid_items[i].square_live}/{$grid_items[i].square_kitchen}</span>
                    </div>
                    <div class="area">
                        <span class="key">{$L_FLOOR}:</span>
                        <span class="value">{$grid_items[i].floor}/{$grid_items[i].floor_count}</span>
                    </div>
                </div>
                {assign var=itcount value=$itcount+1}
                {if $itcount==4}
                </div>
                <div class="row">
                    {assign var=itcount value=1}
                {/if}
                {/section}
                </div>
            </div>