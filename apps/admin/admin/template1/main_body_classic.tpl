<div class="navbar" id="navbar">
    {literal}
        <script type="text/javascript">
            try {
                ace.settings.check('navbar', 'fixed')
            } catch (e) {
            }
        </script>
    {/literal}
    <div class="navbar-inner">
        <div class="container-fluid">
            <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
                <span class="sr-only">Toggle sidebar</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="brand">
                <div class="dragon"></div>
                <div class="ttl">CMS Sitebill</div>
            </div>

            {include file='top_nav_notify.tpl'}


            {if $smarty.const.DEVMODE==1}

                {if $admin_menua.apps.childs}

                    <div class="modal custom_modal hide fade" id="myModalAPP">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h3>{$L_ADMIN_MENU_APPLICATIONS}</h3>
                        </div>
                        <div class="modal-body">
                            <ul>
                                {assign var=fletter value=''}
                                {foreach from=$admin_menua.apps.childs item=ama}
                                {if $fletter==''}
                                {assign var=fletter value=$ama.title|mb_substr:0:1|strtoupper}
                                <li class="letter">
                                    {$fletter}
                                </li>
                                {else}
                                {if $fletter ne $ama.title|mb_substr:0:1|strtoupper}
                                {assign var=fletter value=$ama.title|mb_substr:0:1|strtoupper}
                            </ul>
                            <ul>
                                <li class="letter">
                                    {$fletter}
                                </li>
                                {/if}
                                {/if}
                                <li>
                                    <a {if isset($ama.childs) && $ama.childs|count>0}data-toggle="dropdown"  class="dropdown-toggle" href="{$ama.href}" data-target="#"{else}href="{$ama.href}"{/if}>{$ama.title}</a>
                                </li>
                                {/foreach}
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn" data-dismiss="modal">{$L_CLOSE}</a>
                        </div>
                    </div>
                {/if}
            {/if}
            <div class="pull-right">
                <a href="{$MAIN_URL}/apps/admin/" target="_blank" class="btn btn-small btn-warning"><i class="icon-dashboard"></i> Новая админка</a>
                <a href="{$MAIN_URL}/" target="_blank" class="btn btn-small btn-primary"><i class="icon-eye-open"></i> {$L_SITE}</a>


                {if $admin_menua.apps.childs}

                    {if $smarty.const.DEVMODE==1}
                        <a href="#myModalAPP" role="button" class="btn" data-toggle="modal">{$L_ADMIN_MENU_APPLICATIONS}</a>
                    {else}
                        <div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-info dropdown-toggle">
                                {$L_ADMIN_MENU_APPLICATIONS}
                                <i class="icon-angle-down icon-on-right"></i>
                            </button>

                            <ul class="dropdown-menu">
                                {foreach from=$admin_menua.apps.childs item=ama}
                                    <li>
                                        <a {if isset($ama.childs) && $ama.childs|count>0}data-toggle="dropdown"  class="dropdown-toggle" href="{$ama.href}" data-target="#"{else}href="{$ama.href}"{/if}>{$ama.title}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                {/if}
                {if isset($custom_admin_entity_menu) && $custom_admin_entity_menu|count>0}
                    <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-info dropdown-toggle">
                            {$L_ADMIN_MENU_ADDITIONAL_APPLICATIONS}
                            <i class="icon-angle-down icon-on-right"></i>
                        </button>

                        <ul class="dropdown-menu">
                            {foreach from=$custom_admin_entity_menu item=custom_admin_entity}
                                <li>
                                    <a href="{$custom_admin_entity.href}">{$custom_admin_entity.entity_title}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-info dropdown-toggle">
                        <i class="icon-globe icon-on-right"></i>
                    </button>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="{$MAIN_URL}/admin/?_lang=ru"><img src="{$MAIN_URL}/apps/admin/admin/template/img/flag_ru.gif" alt="Русский" title="Русский"/> Русский</a>
                        </li>
                        <li>
                            <a href="{$MAIN_URL}/admin/?_lang=en"><img src="{$MAIN_URL}/apps/admin/admin/template/img/flag_en.png" alt="English" title="English"/> English</a>
                        </li>

                    </ul>
                </div>

                <div class="btn-group">
                    <button data-toggle="dropdown" class="btn btn-info dropdown-toggle">
                        <i class="icon-question-sign icon-on-right"></i>
                    </button>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="http://wiki.sitebill.ru/" target="_blank"><i class="icon-white icon-book"></i> База знаний</a>
                        </li>

                        <li>
                            <a href="https://www.sitebill.ru/s/" target="_blank"><i class="icon-white icon-comment"></i> Форум</a>
                        </li>

                        <li>
                            <a href="http://www.youtube.com/user/DMn1c" target="_blank"><i class="icon-white icon-film"></i> Видео-уроки</a>
                        </li>

                        <li>
                            <a href="http://www.sitebill.ru/" target="_blank"><i class="icon-white icon-heart"></i> Наш сайт</a>
                        </li>

                        <li>
                            <a href="https://play.google.com/store/apps/details?id=ru.sitebill.mobilecms" target="_blank"><i class="icon-white icon-camera"></i> Мобильное приложение</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div><!-- /.navbar-inner -->
</div>

<div class="main-container container-fluid">
    {include file='sidebar.tpl'}
    <div class="main-content">
        <div class="breadcrumbs" id="breadcrumbs">

            <ul class="breadcrumb">
                {foreach from=$breadcrumbs_array item=crumb name=bread}
                    {if $smarty.foreach.bread.first}<i class="icon-home home-icon"></i>{/if}
                    <li {if $smarty.foreach.bread.last}class="active"{/if}><a href="{$crumb.href}">{$crumb.title}</a>{if !$smarty.foreach.bread.last} <span class="divider"><i class="icon-angle-right arrow-icon"></i></span>{/if}</li>
                {/foreach}
            </ul>
            <!-- div class="pull-right">{if $help_link!=''}{$help_link}{/if}</div-->
        </div>

        <div class="page-content">
            {$content}
        </div>

    </div>
</div>
{$messenger_widget}
<a href="#" class="scrollup">{$LT_SCROLLUP}</a>
