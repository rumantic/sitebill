{include file="remember.tpl"}
<table class="content_main table" cellspacing="2" cellpadding="2">
    <tr  class="row_head">
        <td width="1%" class="row_title">{$L_DATE}</td>
        <td width="1%" class="row_title">{$L_ID}</td>
        <td width="1%" class="row_title">{$L_PHOTO}</td>
        <td width="70" class="row_title">{$L_TYPE}&nbsp;<a href="{$url}&order=type&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=type&asc=desc">&uarr;</a></td>
        <td width=13% class="row_title">{$L_CITY}&nbsp;<a href="{$url}&order=city&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=city&asc=desc">&uarr;</a></td>
        <td width=13% class="row_title">{$L_DISTRICT}&nbsp;<a href="{$url}&order=district&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=district&asc=desc">&uarr;</a></td>
        <td width=13% class="row_title">{$L_STREET}&nbsp;<a href="{$url}&order=street&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=street&asc=desc">&uarr;</a></td>
        <td class="row_title" nowrap>{$L_PRICE}&nbsp;<a href="{$url}&order=price&asc=asc">&darr;</a>&nbsp;<a href="{$url}&order=price&asc=desc">&uarr;</a></td>
        <td class="row_title">{$L_FLOOR}</td>
        <td class="row_title">{$L_SQUARE} м<sup>2</sup></td>
        {if $admin !=''}
            <td class="row_title"></td>
        {/if}
    </tr>
    {section name=i loop=$grid_items}

        <tr valign="top" class="row3{if isset($grid_items[i].export_cian) && $grid_items[i].export_cian==1} cianexported{/if}" {if $grid_items[i].active == 0}style="color: #ff5a5a;"{/if}>

            <td><b><a href="{$grid_items[i].href}">{$grid_items[i].date}</a></b></td>
            <td><b><a href="{$grid_items[i].href}">{$grid_items[i].id}</a></b></td>
            <td align="center">
                {if $grid_items[i].img != '' }
                    <a href="{$grid_items[i].href}"><img src="{mediaincpath data=$grid_items[i].img[0] type='preview'}" width="50"></a>
                    <!-- img src="{$estate_folder}/img/hasphoto.jpg" border="0" width="16" height="14" /-->
                {/if}
            </td>
            <td><b>{$grid_items[i].type_sh}</b></td>
            <td>{$grid_items[i].city}</td>
            <td>{$grid_items[i].district}</td>
            <td>{$grid_items[i].street}</td>
            <td nowrap><b>{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if} {if $grid_items[i].currency != 'RUR'}({$grid_items[i].price_ue} {$L_RUR_SHORT}){/if}</b></td>
            <td>{$grid_items[i].floor}/{$grid_items[i].floor_count}</td>
            <td>{$grid_items[i].square_all}/{$grid_items[i].square_live}/{$grid_items[i].square_kitchen}</td>
            {if $admin !=''}
                <td nowrap>
                    <a class="btn btn-small btn-info" href="{$estate_folder_control}?do=edit&id={$grid_items[i].id}"><i class="icon-white icon-pencil"></i></a>
                    <a class="btn btn-small btn-danger" onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_items[i].id}"><i class="icon-white icon-remove"></i></a>
                </td>
            {/if}

        </tr>
        {if $apps_billing=='on' && $grid_items[i].active==1}
            <tr>
                <td colspan="11">
                    {if $grid_items[i].vip_status_end > $now}
                        <span class="vb"><i class="icon-star icon-black"></i> VIP до {$grid_items[i].vip_status_end|date_format:"%d.%m.%Y %H:%M"}</span>
                    {else}
                        <a class="btn btn-small make_spec" data-type="vip" alt="{$grid_items[i].id}">Сделать VIP</a>
                    {/if}

                    {if $grid_items[i].premium_status_end > $now}
                        <span class="vb"><i class="icon-star icon-black"></i> Premium до {$grid_items[i].premium_status_end|date_format:"%d.%m.%Y %H:%M"}</span>
                    {else}
                        <a class="btn btn-small make_spec" data-type="premium" alt="{$grid_items[i].id}">Сделать premium</a>
                    {/if}


                    {if $grid_items[i].bold_status_end > $now}
                        <span class="vb"><i class="icon-star icon-black"></i> Выделено до {$grid_items[i].bold_status_end|date_format:"%d.%m.%Y %H:%M"}</span>
                    {else}
                        <a class="btn btn-small make_spec" data-type="bold" alt="{$grid_items[i].id}">Выделить объявление</a>
                    {/if}
                    {if $upps_left!=0 || $packs_left!=0}
                        <a class="btn btn-small go_up" href="{$estate_folder}/upper/realty{$grid_items[i].id}/">Поднять</a>
                    {/if}
                </td>
            </tr>
        {/if}
    {/section}

    {if $pager != ''}
        <tr>
            <td colspan="11" class="pager">{$pager}</td>
        </tr>
    {/if}
</table>

{if $apps_billing=='on'}
    <div class="modal fade" class="makeSpec" id="makeSpec" tabindex="-1" role="dialog" aria-labelledby="makeSpecOk" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h3 id="makeSpecModalLabel">
                <span class="spec_title spec_title_premium">Установка статуса Премиум</span>
                <span class="spec_title spec_title_bold">Установка статуса Выделено</span>
                <span class="spec_title spec_title_vip">Установка статуса VIP</span>
            </h3>
        </div>
        <div class="modal-body">
            <form class="form-horizontal">
                <input type="hidden" value="" name="realty_id" />
                <input type="hidden" value="" name="per_day_price" />
                <input type="hidden" value="" name="type" />

                <input type="hidden" value="{$per_day_price_premium}" id="pdp_premium" />
                <input type="hidden" value="{$per_day_price}" id="pdp_vip" />
                <input type="hidden" value="{$per_day_price_bold}" id="pdp_bold" />

                <div class="control-group">
                    <label class="control-label">Дней</label>
                    <div class="controls">
                        <input type="text" value="1" name="days" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Цена</label>
                    <div class="controls">
                        <span class="calc_price"></span>
                    </div>
                </div>

            </form>
            <div class="answer" style="display: none;"></div>
        </div>
        <div class="modal-footer">

            <button class="btn use_own">Использовать пакетные поднятия</button>


            <button class="btn ok">ОК</button>
            <button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
        </div>
    </div>
{/if}
