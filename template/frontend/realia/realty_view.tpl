<script src="{$estate_folder}/apps/system/js/realtymap.js" type="text/javascript"></script>

{if $photo|count>0}
<link rel="stylesheet" href="{$estate_folder}/template/frontend/realia/libraries/photoswipe/photoswipe.css">
<link rel="stylesheet" href="{$estate_folder}/template/frontend/realia/libraries/photoswipe/default-skin/default-skin.css">
<script src="{$estate_folder}/template/frontend/realia/libraries/photoswipe/photoswipe.min.js"></script>
<script src="{$estate_folder}/template/frontend/realia/libraries/photoswipe/photoswipe-ui-default.min.js"></script>

<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>

<div class="hiddengal" style="position: absolute; left: -10000px;    visibility: hidden;">
    {foreach name=j from=$photo item=photoitem}
        <img src="{mediaincpath data=$photoitem}" />
    {/foreach}
</div>
{literal}
<script>
    var items = [];
    function InitPropertySlider(id, options) {
        var cc=$('#'+id);
        var prev = $('#'+id+' .preview');
        if(cc.length>0 && cc.find('.content ul').length>0){
            cc.find('.content ul').carouFredSel({
                scroll: {
                    items: 1
                },
                auto: false,
                next: {
                    button: '#'+id+' .content .carousel-next',
                    key: 'right'
                },
                prev: {
                    button: '#'+id+' .content .carousel-prev',
                    key: 'left'
                }
            });
            cc.find('.content ul li:first').addClass('active');

            cc.find('.content ul li a').click(function(e){
                e.preventDefault();

                cc.find('.content ul li').removeClass('active');
                $(this).parents('li').eq(0).addClass('active');
                prev.find('a img').attr('src', $(this).attr('href'));
                prev.find('a').attr('href', $(this).attr('href'));
                prev.find('a').attr('data-step', $(this).data('step'));
            })

        }
        if(prev.length > 0){
            prev.find('a').click(function(e){
                e.preventDefault();
                openPhotoSwipe(parseInt($(this).attr('data-step'), 10));
            });
        }
    }
    var openPhotoSwipe = function(step) {
        if(items.length==0){
            collectImgs();
        }
        var pswpElement = document.querySelectorAll('.pswp')[0];
            var options = {
            history: false,
            focus: false,
            showAnimationDuration: 0,
            hideAnimationDuration: 0,
            index: step
        };
        var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.init();
    }
    var collectImgs = function(){
        $('.hiddengal img').each(function(){
            var _this=$(this);
            items.push({src: _this.attr('src'), w: _this.width(), h: _this.height()});
        });
    }
    $(document).ready(function () {
        InitPropertySlider('cproperty_gal');
    });
</script>
{/literal}
{/if}

<script>
    var loc_objects ={$geoobjects_collection_clustered};
    var map_type = '{$map_type}';
</script>

{assign var=x value=array()}
{if $data.city_id.value_string ne ''}
    {append var=x value=$data.city_id.value_string}
{/if}

{if $data.district_id.value_string ne ''}
    {append var=x value=$data.district_id.value_string}
{/if}

{if $data.street_id.value_string ne ''}
    {append var=x value=$data.street_id.value_string}
{/if}

{if $data.number.value ne '' && $data.number.value ne '0'}
    {append var=x value=$data.number.value}
{/if}

{assign var=rname value=$x|implode:', '}

{literal}
    <script>
    var rname = '{/literal}{$rname}{literal}';
    $(document).ready(function () {
        var RM = new RealtyMap();
        //use next line instead prev line for using Yandex ver. 2.1 capabilities
        //var RM=new RealtyMap('2.1');
        if (loc_objects.length == 0) {
            RM.initGeocoded('property-map', rname, map_type, {defaultZoom: 16, yandexMapType: 'yandex#map'});
        } else {
            RM.initJSON('property-map', loc_objects, map_type, {defaultZoom: 16, yandexMapType: 'yandex#map'});
        }

    });
    </script>
{/literal}


{*lazy billing panel. apps.billing required*}
{*$fast_billing*}
{if $apps_mapplic_on==1}
    <div class="row">
        <div class="span12">
            <div class="mapplic-wrapper" data-table="data" data-key="id" data-field-name="image" data-key-value="{$data.id.value}"></div>
            {literal}
                <script>
                    $(document).ready(function () {
                        var table = $('.mapplic-wrapper').attr('data-table');
                        var key = $('.mapplic-wrapper').attr('data-key');
                        var field_name = $('.mapplic-wrapper').attr('data-field-name');
                        var key_value = $('.mapplic-wrapper').attr('data-key-value');
                        $('.mapplic-wrapper').load(
                            estate_folder + '/apps/api/rest.php?action=mapplic&do=get_panel&anonymous=1&key_value=' + key_value +
                            '&table=' + table +
                            '&key=' + key +
                            '&field_name=' + field_name
                        );
                    });
                </script>
            {/literal}
        </div>
    </div>
{/if}

<div class="row">


    <div class="span9">
        <h1 class="page-header">{$title}</h1>


        <div class="favblock">
            {if isset($smarty.session.favorites)}
                {if in_array($data.id.value, $smarty.session.favorites)}
                    <a class="fav-rem" alt="{$data.id.value}" title="{$L_DELETEFROMFAVORITES}" href="#remove_from_favorites"></a>
                {else}
                    <a class="fav-add" alt="{$data.id.value}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
                {/if}
            {else}
                <a class="fav-add" alt="{$data.id.value}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
            {/if}
            {if $apps_pdfreport_enabled==1}
                <a href="?format=pdf" class="btn btn-primary"><i class="icon-white icon-print"></i> {_e t="Печать"}</a>
            {/if}

        </div>
        {if $photo|count>0}
            <div class="carousel property" id="cproperty_gal">
                <div class="preview">
                    <a href="{mediaincpath data=$photo[0]}" data-step="0" class="lbgallery" title="Фото" >
                        <img src="{mediaincpath data=$photo[0]}" alt="">
                    </a>
                </div>
                {if $photo|count>1}
                    <div class="content">
                        <a class="carousel-prev" href="#">{_e t="Previous"}</a>
                        <a class="carousel-next" href="#">{_e t="Next"}</a>
                        <ul>
                            {foreach name=j from=$photo item=photoitem}
                                <li{if $smarty.foreach.j.iteration == 0} class="active"{/if}>
                                    <a href="{mediaincpath data=$photoitem}" data-step="{$smarty.foreach.j.index}">
                                        <img src="{mediaincpath data=$photoitem type='preview'}" />
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
            <!--a href="/imgzip/{$data.id.value}">Скачать все фото</a-->
        {/if}
    <div class="property-detail">
        <div class="noverview-holder">
            <div class="pull-left overview">
                <div class="row">
                    <div class="span3">
                        <h2>{_e t="Кратко"}</h2>
                        <table>
                            {foreach from=$hvd_tabbed item=tab key=tabname}
                                {if $tab|count>0}
                                    {foreach from=$tab item=data_item}

                                        {if $data_item.type eq "primary_key" or $data_item.value eq "0" or $data_item.value eq "" or $data_item.name eq "currency_id" or $data_item.name eq "export_cian" or $data_item.name eq "user_id" or $data_item.name eq "price"  or $data_item.name eq "youtube" or $data_item.type eq "hidden" or $data_item.name eq "text" or $data_item.type eq "geodata" or $data_item.name eq "meta_keywords"  or $data_item.name eq "meta_description" or $data_item.name eq "meta_title" or $data_item.type eq "uploads" or $data_item.name eq "text_en"}

                                        {elseif $data_item.name eq "fio"}
                                            {assign var="agent_fio" value=$data_item.value}
                                        {elseif $data_item.name eq "phone"}
                                            {assign var="agent_phone" value=$data_item.value}
                                        {elseif $data_item.name eq "email"}
                                            {assign var="agent_email" value=$data_item.value}
                                        {elseif $data_item.type eq "destination"}
                                            {if $data_item.value_string!=''}
                                                <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
                                                    {/if}
                                                {elseif $data_item.type eq "select_by_query"}
                                                    {if $data_item.value_string!=''}
                                                <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
                                                    {/if}
                                                {elseif $data_item.type eq "select_box_structure"}
                                                    {if $data_item.value_string!=''}
                                                <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
                                                    {/if}
                                                {elseif $data_item.type eq "checkbox"}
                                                    {if $data_item.name ne 'hot' and $data_item.name ne 'active'}
                                                        {if $data_item.value eq 1}
                                                    <tr><th>{$data_item.title}</th><td><input type="checkbox" checked="checked" disabled="disabled" /></td></tr>
                                                        {/if}
                                                    {/if}
                                                {elseif $data_item.type eq "select_box"}
                                                    {if $data_item.value_string!=''}
                                                <tr><th>{$data_item.title}</th><td>{$data_item.value_string}</td></tr>
                                                    {/if}
                                                {elseif $data_item.type eq "tlocation"}
                                            <tr><th>{$data_item.title}</th><td>{$data_item.tlocation_string}</td></tr>
                                                {elseif $data_item.type eq "select_by_query_multi" && is_array($data_item.value_string) && !empty($data_item.value_string)}
                                            <tr><th>{$data_item.title}</th><td>{$data_item.value_string|print_r}{', '|implode:$data_item.value_string}</td></tr>

                                        {else}
                                            {if $data_item.value!=''}
                                                {if $data_item.name eq "text"}
                                                    <tr><th>{$data_item.title}</th><td>{$data_item.value|nl2br}</td></tr>
                                                        {else}
                                                    <tr><th>{$data_item.title}</th><td>{if is_array($data_item.value) && !empty($data_item.value)}{$data_item.value|implode:','}{elseif is_array($data_item.value) && empty($data_item.value)}{else}{$data_item.value}{/if}</td></tr>
                                                        {/if}
                                                    {/if}
                                                {/if}

                                    {/foreach}

                                {/if}
                            {/foreach}

                        </table>
                    </div>
                </div>
            </div>
            {if $data.text.value != ''}
                <div class="noverview-full">{$data.text.value}</div>
            {/if}
        </div>

        {if $data.youtube.value != ''}
            <div class="noverview-holder">
                <div class="noverview-full">
                    <div align="center">
                        <iframe width="560" height="315" src="//www.youtube.com/embed/{$data.youtube.value}" frameborder="0" allowfullscreen></iframe>
                        <p>&nbsp;</p>
                    </div>
                </div>
            </div>
        {/if}

        <h2>Карта</h2>
        <div id="property-map" data-geo="{$data.geo.value.lat};{$data.geo.value.lng}"></div><!-- /#property-map -->



        {if $apps_reservation_on==1}
            <div class="res" data-id="{$data.id.value}"></div>
            {literal}
                <script>
                    $(document).ready(function () {
                        var id = $('.res').attr('data-id');
                        $('.res').load(estate_folder + '/apps/reservation/js/ajax.php?action=get_reservation_panel&id=' + id);
                    });
                </script>
            {/literal}
        {/if}
    </div>
    {if $similar_data|count>0}
        <h2>{$L_SIMILAR}</h2>

        <div class="properties-rows">
            <div class="row">
                {section name=x loop=$similar_data}
                    <div class="property span9">
                        <div class="row">
                            <div class="image span3">
                                <div class="content">
                                    <a href="{$similar_data[x].href}"></a>
                                    {if $similar_data[x].image.image_array|count ne 0}
                                        <img src="{mediaincpath data=$similar_data[x].image.image_array[0] type='preview'}" class="previewi">
                                    {else}
                                        <img src="{$estate_folder}/template/frontend/realia/img/no_foto_270x200.png" class="previewi">
                                    {/if}

                                </div>
                            </div>

                            <div class="body span6">
                                <div class="title-price row">
                                    <div class="title span4">
                                        <h2>
                                            <a href="{$similar_data[x].href}">
                                                {if $similar_data[x].city_id.value_string ne ''} {$similar_data[x].city_id.value_string}{if
                                    $similar_data[x].street_id.value_string ne ''}, {$similar_data[x].street_id.value_string}{if
                                    $similar_data[x].number.value ne ''}, {$similar_data[x].number.value}{/if}{/if}
                                                {else} {if $similar_data[x].street_id.value_string ne ''} {$similar_data[x].street_id.value_string}{if
                                    $similar_data[x].number.value ne ''}, {$similar_data[x].number.value}{/if} {/if}
                                                    {/if}
                                                </a>
                                            </h2>
                                        </div>
                                        {if $similar_data[x].price_discount.value > 0}
                                            <div class="price">
                                                {$similar_data[x].price_discount.value|number_format:0:",":" "} {if $similar_data[x].currency_id.value_string != ''}{$similar_data[x].currency_id.value_string}{/if}
                                                <div class="price_discount_list">{$similar_data[x].price.value|number_format:0:",":" "} {if $similar_data[x].currency_id.value_string != ''}{$similar_data[x].currency_id.value_string}{/if}</div>
                                            </div>
                                        {else}
                                            <div class="price">{$similar_data[x].price.value|number_format:0:",":" "} {if $similar_data[x].currency_id.value_string != ''}{$similar_data[x].currency_id.value_string}{/if}</div>
                                        {/if}

                                    </div>

                                    <div class="location">{$similar_data[x].topic_id.value_string}</div>

                                    <div class="area">
                                        <span class="key">{$L_SQUARE} {_e t="м"}<sup>2</sup>:</span>
                                        <span class="value">{$similar_data[x].square_all.value}/{$similar_data[x].square_live.value}/{$similar_data[x].square_kitchen.value}</span>
                                    </div><!-- /.area -->
                                    <div class="area">
                                        <span class="key">{$L_FLOOR}:</span>
                                        <span class="value">{$similar_data[x].floor.value}/{$similar_data[x].floor_count.value}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/section}
                        </div>
                    </div>
                    {/if}
                    </div>




                    <div class="sidebar span3">
                        {if $show_mortgage_calculator eq 'true'}
                            {include file='mortgage_calculator.tpl'}
                        {/if}
                        {if $user_data ne ''}
                            <div class="widget our-agents">
                                <div class="title">
                                    <h2><a href="{$user_data._href}">{_e t="Агент"}</a></h2>
                                </div>

                                <div class="content">
                                    <div class="agent">
                                        <div class="image">
                                            {if $user_data.imgfile.value != ''}
                                                <img src="{$estate_folder}/img/data/user/{$user_data.imgfile.value}" />
                                            {else}
                                                <img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/userplaceholder.png" />
                                            {/if}
                                        </div>
                                        <div class="name">
                                            {if isset($data.fio.value) && $data.fio.value!=''}
                                                {$data.fio.value}
                                            {else}
                                                {$user_data.fio.value}
                                            {/if}
                                        </div>
                                        <div class="phone">
                                            {if isset($data.phone.value) && $data.phone.value!=''}
                                                {$data.phone.value}
                                            {elseif $user_data.phone.value != ''}
                                                {$user_data.phone.value}
                                            {/if}
                                        </div>
                                        {if $user_data.mobile.value != ''}
                                            <div class="phone">{$user_data.mobile.value}</div><!-- /.phone -->
                                        {/if}

                                        {if isset($data.email.value) && $data.email.value!=''}
                                            <div class="email"><a href="mailto:{$data.email.value}">{$data.email.value}</a></div>
                                            {elseif $user_data.email.value != ''}
                                            <div class="email"><a href="mailto:{$user_data.email.value}">{$user_data.email.value}</a></div>
                                            {/if}
                                        <br />
                                        {if $show_upper == 'true'}
                                            <br /><span><a class="btn btn-info" href="{$estate_folder}/upper/realty{$data.id.value}"><i class="icon-white icon-chevron-up"></i> {$L_UP_AD}</a></span>
                                                {/if}
                                                {if $smarty.session.user_id!=$user_data.user_id.value && $mailbox_on==1}
                                                    {include file=$apps_mailbox_block title_data=[$data.topic_id.value_string,$data.city_id.value_string,$data.street_id.value_string] to=$user_data.user_id.value message_to_author_title=''}
                                                {/if}
                                    </div>

                                </div>
                            </div>
                        {/if}
                        {include file='right_special.tpl'}
                        <br/>
                    </div>
                </div>
