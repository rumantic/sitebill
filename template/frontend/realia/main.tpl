<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

{include file="header.tpl"}
<body>
{if $smarty.session.user_id eq ''}
    {include file="login_register.tpl"}
{/if}


<div id="wrapper-outer" >
    <div id="wrapper">
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
                                <li><a href="{$estate_folder}/myfavorites/">{$L_TABS_FAVORITES} (<span id="favorites_count">{(int)$smarty.session.favorites|count}</span>)</a></li>
                            </ul><!-- /.breadcrumb -->

                            <div class="account pull-right">
                                <ul class="nav nav-pills">
                                	{if $smarty.session.user_id eq ''}
										<li><a href="#" data-toggle="modal" data-target="#prettyLogin">{$L_LOGIN_BUTTON}</a></li>
	                                    <li><a href="#" data-toggle="modal" data-target="#prettyLogin">{$L_AUTH_REGISTRATION}</a></li>
									{else} {if isset($user_menu)}{$user_menu}{/if}
										<li><a href="{$estate_folder}/logout/" >{$L_LOGOUT_BUTTON}</a></li>
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



                            <div class="language-switcher">
                            	{foreach item=ln from=$available_langs key=k}
									{if $smarty.session._lang eq $k}
									<div class="current"><a href="#" lang="en"><img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/flags/{$k}.png"> {$ln}</a></div><!-- /.current -->
									{/if}
								{/foreach}
                               	<div class="options">
                                    <ul>
                                    {foreach item=ln from=$available_langs key=k}
									{if $smarty.session._lang eq $k}
									{else}
									{/if}
									<li><a href="{$smarty.const.SITEBILL_MAIN_URL}/?_lang={$k}"><img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/flags/{$k}.png"></a></li>
									{/foreach}
                                    </ul>
                                </div><!-- /.options -->
                            </div><!-- /.language-switcher -->
							{if $live_search_on==1}
								<div class="site-search">Быстрый поиск{$apps_search_block}</div>
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
</body>
</html>