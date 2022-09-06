{if $is_underconstruction_mode==1}
{include file='main_closed.tpl'}
{else}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="{$CurrentLang}">

    {include file="header.tpl"}

    <body>
        {if $smarty.session.user_id eq ''}
            {include file="login_register.tpl"}
        {/if}

        <div id="wrapper-outer" >
            <div id="wrapper{if $is_realty_view}1{/if}">
                <div id="wrapper-inner">
                    <!-- BREADCRUMB -->
                    <div class="breadcrumb-wrapper">
                        <div class="container">
                            <div class="row">
                                <div class="span12">
                                    <ul class="breadcrumb pull-left">
                                        <li><a href="{$estate_folder}/">{$L_HOME}</a></li>
                                            {section name=i loop=$right_menu}
                                            <li><a href="{$right_menu[i].url}">{$right_menu[i].name}</a></li>
                                            {/section}
                                        <li><a href="{formaturl path="myfavorites"}">{$L_TABS_FAVORITES} (<span id="favorites_count">{if is_array($smarty.session.favorites)}{(int)$smarty.session.favorites|count}{else}0{/if}</span>)</a></li>
                                    </ul><!-- /.breadcrumb -->
                                    {*include file='city_buttons.tpl'*}

                                    <div class="account pull-right">
                                        <ul class="nav nav-pills">
                                            {if $smarty.session.user_id eq ''}
                                                <li><a href="#" data-toggle="modal" data-target="#prettyLogin">{$L_LOGIN_BUTTON}</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#prettyLogin">{$L_AUTH_REGISTRATION}</a></li>
                                                {else} {if isset($user_menu)}{$user_menu}{/if}
                                                <li><a href="{formaturl path="logout"}" >{$L_LOGOUT_BUTTON}</a></li>
                                                {/if}



                                        </ul>
                                    </div>
                                </div><!-- /.span12 -->
                            </div><!-- /.row -->
                        </div><!-- /.container -->
                    </div><!-- /.breadcrumb-wrapper -->

                    {include file='header_contact_add.tpl'}
                    <!-- NAVIGATION -->
                    <div id="navigation">
                        <div class="container">
                            <div class="navigation-wrapper">
                                <div class="navigation clearfix-normal">
                                    {$navmenu}
                                    {include file='_langswitcher.tpl'}
                                    {if $live_search_on==1}
                                        <div class="site-search">{_e t="Быстрый поиск"} {$apps_search_block}</div>
                                    {/if}
                                </div><!-- /.navigation -->
                            </div><!-- /.navigation-wrapper -->
                        </div><!-- /.container -->
                    </div><!-- /.navigation -->


                    <!-- CONTENT -->
                    <div id="content">
                        {if $REQUEST_URI=='/glavnaya1'}
                            {include file='glavnaya1.tpl'}
                        {elseif $REQUEST_URI=='/vibor-tarifa'}
                            {include file='tariff_select.tpl'}
                        {else}

                            {if $_layout != ''}
                                {include file=$_layout}
                            {else}
                                {include file='layout_basic.tpl'}
                            {/if}
                            {if $REQUESTURIPATH == '' }
                                <div class="bottom-wrapper">
                                    <div class="bottom container">
                                        <div class="bottom-inner row">
                                            <div class="item span4">
                                                <div class="address decoration"></div>
                                                <h2><a><editable id="add_title_main" data-file="main.tpl">{$LT_ADD_YOUR_OWN}</editable></a></h2>
                                                <p><editable id="add_desc_main" data-file="main.tpl">{$LT_ADD_YOUR_OWN_DESC}</editable></p>
                                                <a href="{$estate_folder}/add/" class="btn btn-primary">{$LT_VIEW_DETAILS}</a>
                                            </div><!-- /.item -->

                                            <div class="item span4">
                                                <div class="gps decoration"></div>
                                                <h2><a><editable id="rent_title_main" data-file="main.tpl">{$LT_RENT_FLAT}</editable></a></h2>
                                                <p><editable id="rent_desc_main" data-file="main.tpl">{$LT_RENT_FLAT_DESC}</editable></p>
                                                <a href="{$estate_folder}/getrent/" class="btn btn-primary">{$LT_VIEW_DETAILS}</a>
                                            </div><!-- /.item -->

                                            <div class="item span4">
                                                <div class="key decoration"></div>
                                                <h2><a><editable id="mort_title_main" data-file="main.tpl">{$LT_MORTGAGES}</editable></a></h2>
                                                <p><editable id="mort_desc_main" data-file="main.tpl">{$LT_MORTGAGES_DESC}</editable></p>
                                                <a href="{$estate_folder}/ipotekaorder/" class="btn btn-primary">{$LT_VIEW_DETAILS}</a>
                                            </div><!-- /.item -->
                                        </div><!-- /.bottom-inner -->
                                    </div><!-- /.bottom -->
                                </div><!-- /.bottom-wrapper -->

                                {include file='partners.tpl'}

                            {/if}
                        {/if}
                    </div><!-- /#content -->
                </div><!-- /#wrapper-inner -->

                {include file="footer.tpl"}
            </div><!-- /#wrapper -->
        </div><!-- /#wrapper-outer -->
    <a href="#" class="scrollup">{$LT_SCROLLUP}</a>
    {$dashboard}
    {$messenger_widget}
        {if isset($debugbarRenderer)}
            {$debugbarRenderer->render()}
        {/if}
        {include file="messenger_modals.tpl"}
        {if $smarty.session.user_id eq ''}
            <script src="{$theme_folder}/plugins/intl-tel-input/js/intlTelInput.js"></script>
            <script src="{$theme_folder}/plugins/intl-tel-input/js/isValidNumber.js"></script>
            <script src="{$theme_folder}/plugins/intl-tel-input/js/isValidMobileNumber.js"></script>
        {/if}

</body>
</html>
{/if}
