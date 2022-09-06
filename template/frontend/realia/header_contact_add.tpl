<!-- HEADER -->
<div id="header-wrapper">
    <div id="header">
        <div id="header-inner">
            <div class="container">
                <div class="navbar">
                    <div class="navbar-inner">
                        <div class="row">

                            <div class="logo-wrapper span4">
                                <a href="#nav" class="hidden-desktop" id="btn-nav">Toggle navigation</a>

                                <div class="logo">
                                    <a href="{formaturl path="/"}" title="{_e t="Главная"}">
                                        <img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/logo.png" alt="Home">
                                    </a>
                                </div><!-- /.logo -->

                                <div class="mobile-lang-switcher hidden-desktop">
                                    {include file='_langswitcher.tpl' simplemode=1}
                                </div>

                                <div class="site-name">
                                    <a class="brand"><editable id="site_logo_title_edit" data-file="header_contact_add.tpl">Realia<span contentEditable="false">&nbsp;</span></editable></a>
                                </div><!-- /.site-name -->
                                <div class="site-slogan">
                                    <span><editable id="site_slogan_edit" data-file="header_contact_add.tpl">CMS Sitebill<br>шаблон</editable></span>
                                </div><!-- /.site-slogan -->


                            </div><!-- /.logo-wrapper -->

                            <div class="info">
                                <div class="site-email">
                                    <a><editable id="info_email_edit" data-file="header_contact_add.tpl">{if $apps_contact_email != ''}{$apps_contact_email}{else}dkondin@gmail.com{/if}</editable></a>
                                </div><!-- /.site-email -->


                                <div class="site-phone">
                                    <span><a><editable id="info_phone_edit" data-file="header_contact_add.tpl">{if $apps_contact_phone != ''}{$apps_contact_phone}{else}8 800 250-99-31{/if}</editable></a></span>
                                </div><!-- /.site-phone -->
                            </div><!-- /.info -->


                            {if $smarty.session.user_id eq ''}
                                <a class="btn btn-primary btn-large list-your-property arrow-right" href="{formaturl path="add"}">{$L_ADD_ADV}</a>
                            {else}
                                <a class="btn btn-primary btn-large list-your-property arrow-right" href="{formaturl path="account/data/?do=new"}">{$L_ADD_ADV}</a>
                            {/if}
                        </div><!-- /.row -->
                    </div><!-- /.navbar-inner -->
                </div><!-- /.navbar -->
            </div><!-- /.container -->
        </div><!-- /#header-inner -->
    </div><!-- /#header -->
</div><!-- /#header-wrapper -->
