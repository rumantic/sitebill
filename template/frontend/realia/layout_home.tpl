{literal}
    <script>
        function InitImageSlider() {
            $('.iosSlider').iosSlider({
                desktopClickDrag: true,
                snapToChildren: true,
                infiniteSlider: true,
                navSlideSelector: '.slider .navigation li',
                onSlideComplete: function (args) {
                    if (!args.slideChanged)
                        return false;

                    $(args.sliderObject).find('.slider-info').attr('style', '');

                    $(args.currentSlideObject).find('.slider-info').animate({
                        left: '15px',
                        opacity: '.9'
                    }, 'easeOutQuint');
                },
                onSliderLoaded: function (args) {
                    $(args.sliderObject).find('.slider-info').attr('style', '');

                    $(args.currentSlideObject).find('.slider-info').animate({
                        left: '15px',
                        opacity: '.9'
                    }, 'easeOutQuint');
                },
                onSlideChange: function (args) {
                    $('.slider .navigation li').removeClass('active');
                    $('.slider .navigation li:eq(' + (args.currentSlideNumber - 1) + ')').addClass('active');
                },
                autoSlide: true,
                scrollbar: true,
                scrollbarContainer: '.sliderContainer .scrollbarContainer',
                scrollbarMargin: '0',
                scrollbarBorderRadius: '0',
                keyboardControls: true
            });
        }
        $(document).ready(function () {
            InitImageSlider();
        });

    </script>
{/literal}

{literal}
    <style>
        .carousel ul li .image {
            height: 180px;
            overflow-y: hidden;
        }

    </style>
    <script>
        $(document).ready(function () {
            if ($('.carousel .content ul').length > 0) {
                $('.carousel .content ul').carouFredSel({
                    scroll: {
                        items: 1
                    },
                    auto: false,
                    next: {
                        button: '.carousel .content .carousel-next',
                        key: 'right'
                    },
                    prev: {
                        button: '.carousel .content .carousel-prev',
                        key: 'left'
                    }
                });
            }
        });

    </script>
{/literal}

{if $homepage_type=='carousel'}
    <div class="carousel-wrapper">
        <div class="carousel">

            <div class="content">
                <h2 class="page-header">{$L_TABS_SPECIAL}</h2>

                <a class="carousel-prev" href="#">{_e t="Previous"}</a>
                <a class="carousel-next" href="#">{_e t="Next"}</a>
                <ul>
                    {section name=i loop=$special_items2}
                        <li>
                            <div class="image">
                                <a href="{$special_items2[i].href}"></a>
                                {if $special_items2[i].img[0].preview != ''}
                                    <img src="{mediaincpath data=$special_items2[i].img[0] type='preview'}">
                                {else}
                                    <img src="{$estate_folder}/img/no_foto.png" class="previewi">
                                {/if}

                            </div><!-- /.image -->
                            <div class="title">
                                <h3>
                                    {if isset($smarty.session.favorites)}
                                        {if in_array($special_items2[i].id,$smarty.session.favorites)}
                                            <a class="fav-rem" alt="{$special_items2[i].id}" title="{$L_DELETEFROMFAVORITES}" href="#remove_from_favorites"></a>
                                        {else}
                                            <a class="fav-add" alt="{$special_items2[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
                                        {/if}
                                    {else}
                                        <a class="fav-add" alt="{$special_items2[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
                                    {/if}
                                    <a href="{$grid_items[i].href}">
                                        {if	$special_items2[i].city ne ''} {$special_items2[i].city}{if
						$special_items2[i].street ne ''}, {$special_items2[i].street}{if
						$special_items2[i].number ne ''}, {$special_items2[i].number}{/if}{/if}
                                        {else} {if $special_items2[i].street ne ''} {$special_items2[i].street}{if
						$special_items2[i].number ne ''}, {$special_items2[i].number}{/if} {/if}
                                            {/if}
                                        </a></h3>
                                </div><!-- /.title -->
                                <div class="location">{$special_items2[i].type_sh}</div><!-- /.location-->
                                {if $special_items2[i].price_discount > 0}
                                    <div class="price">
                                        {$special_items2[i].price_discount|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}
                                        <div class="price_discount_top">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                                    </div>
                                {else}
                                    <div class="price">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                                {/if}
                                <div class="area">
                                    <span class="key">{_e t="Площадь"}:</span>
                                    <span class="value"><span class="value">{$special_items2[i].square_all}/{$special_items2[i].square_live}/{$special_items2[i].square_kitchen}</span></span>
                                </div><!-- /.area -->
                                <div class="bathrooms"><div class="inner">3</div></div><!-- /.bathrooms -->
                                <div class="bedrooms"><div class="inner">3</div></div><!-- /.bedrooms -->
                            </li>
                            {/section}
                            </ul>
                        </div>
                        <!-- /.content -->
                    </div>
                    <!-- /.carousel -->
                </div><!-- /.carousel-wrapper -->
                {/if}
                    <div class="container">
                        <div id="main">
                            {if $homepage_type=='slider'}
                                <div class="row">
                                    <div class="span9">
                                        {if $special_items2|count>0}
                                            <div class="row">
                                                <div class="span9">

                                                    <div class="slider-wrapper">
                                                        <div class="slider">
                                                            <div class="slider-inner">

                                                                <div class="images">
                                                                    <div class="iosSlider">
                                                                        <div class="slider-content">
                                                                            {section name=i loop=$special_items2}
                                                                                <div class="slide">
                                                                                    {if $special_items2[i].img[0].preview != ''}
                                                                                        <img src="{mediaincpath data=$special_items2[i].img[0]}" width="870" />
                                                                                    {else}
                                                                                        <img src="{$estate_folder}/img/no_foto.png" />
                                                                                    {/if}


                                                                                    <div class="slider-info">

                                                                                        {if $special_items2[i].price_discount > 0}
                                                                                            <div class="price">
                                                                                                <h2>
                                                                                                    {$special_items2[i].price_discount|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}
                                                                                                    <div class="price_discount_slider">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                                                                                                </h2>
                                                                                                <a href="{$special_items2[i].href}">{$L_MORE}</a>
                                                                                            </div><!-- /.price -->
                                                                                        {else}

                                                                                            <div class="price"><h2>{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</h2>
                                                                                                <a href="{$special_items2[i].href}">{$L_MORE}</a>
                                                                                            </div>
                                                                                        {/if}
                                                                                        <h2><a href="{$special_items2[i].href}">{$special_items2[i].type_sh} / {$special_items2[i].city}, {$special_items2[i].street}</a></h2>
                                                                                        <div class="slider-param">{$L_SQUARE} м<sup>2</sup>:<span class="value">{$special_items2[i].square_all}/{$special_items2[i].square_live}/{$special_items2[i].square_kitchen}</span></div>
                                                                                        <div class="slider-param">{$L_FLOOR}:<span class="value">{$special_items2[i].floor}/{$special_items2[i].floor_count}</span></div>
                                                                                    </div><!-- /.slider-info -->
                                                                                </div><!-- /.slide -->
                                                                            {/section}
                                                                        </div><!-- /.slider-content -->
                                                                    </div><!-- .iosSlider -->

                                                                    <ul class="navigation">
                                                                        {section name=i loop=$special_items2}
                                                                            <li{if $smarty.section.i.iteration==1} class="active"{/if}><a>{$smarty.section.i.iteration}</a></li>
                                                                                {/section}
                                                                    </ul>
                                                                </div>


                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        {/if}
                                        <div class="row">
                                            <div class="span9">
                                                {include file='featured_properties.tpl'}
                                            </div>

                                        </div>

                                    </div>
                                    <div class="sidebar span3">


                                        {include file='search_form.tpl'}
                                        {include file="agents_list.tpl"}
                                        <div class="hidden-tablet">
                                            {include file='right_special.tpl'}
                                        </div>
                                    </div>
                                </div>
                            {elseif  $homepage_type=='search' }

                                <div class="row">
                                    <div class="span9">
                                        <div class="row">
                                            <div class="span9">
                                                {include file='advance_search_form.tpl'}
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="span9">
                                                {include file='grid_and_pager.tpl'}
                                            </div>

                                        </div>

                                    </div>
                                    <div class="sidebar span3">
                                        {include file="agents_list.tpl"}
                                        <div class="hidden-tablet">
                                            {include file='right_special.tpl'}
                                        </div>
                                    </div>
                                </div>

                            {else}
                                <div class="row">
                                    <div class="span9">
                                        {include file='featured_properties.tpl'}
                                    </div>
                                    <div class="sidebar span3">


                                        {include file="agents_list.tpl"}
                                        <div class="hidden-tablet">
                                            {include file='right_special.tpl'}
                                        </div>
                                    </div>
                                </div>

                            {/if}


                            {include file="news_list_column.tpl"}
                        </div>
                    </div>