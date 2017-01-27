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
                                                <a href="{$estate_folder}/" title="Главная">
                                                    <img src="{$estate_folder}/template/frontend/{$current_theme_name}/img/logo.png" alt="Home">
                                                </a>
                                            </div><!-- /.logo -->

                                            <div class="site-name">
                                                <a href="/" title="Главная" class="brand">Realia</a>
                                            </div><!-- /.site-name -->

                                            <div class="site-slogan">
                                                <span>CMS Sitebill<br>шаблон</span>
                                            </div><!-- /.site-slogan -->
                                        </div><!-- /.logo-wrapper -->

                                        <div class="info">
                                            <div class="site-email">
                                                {if $apps_contact_email != ''}
                                                    <a href="mailto:{$apps_contact_email}">{$apps_contact_email}</a>
                                                {else}
                                                    <a href="mailto:dkondin@gmail.com">dkondin@gmail.com</a>
                                                {/if}
                                            </div><!-- /.site-email -->

                                            <div class="site-phone">
                                                {if $apps_contact_phone != ''}
                                                    <span><a href="tel:{$apps_contact_phone}">{$apps_contact_phone}</a></span>
                                                {else}
                                                    <span><a href="tel:8 800 250-99-31">8 800 250-99-31</a></span>
                                                {/if}
                                            </div><!-- /.site-phone -->
                                        </div><!-- /.info -->

{if $smarty.session.user_id eq ''}
                                        <a class="btn btn-primary btn-large list-your-property arrow-right" href="{$estate_folder}/add/">{$L_ADD_ADV}</a>
{else}
                                        <a class="btn btn-primary btn-large list-your-property arrow-right" href="{$estate_folder}/account/data/?do=new">{$L_ADD_ADV}</a>
{/if}
                                    </div><!-- /.row -->
                                </div><!-- /.navbar-inner -->
                            </div><!-- /.navbar -->
                        </div><!-- /.container -->
                    </div><!-- /#header-inner -->
                </div><!-- /#header -->
            </div><!-- /#header-wrapper -->
