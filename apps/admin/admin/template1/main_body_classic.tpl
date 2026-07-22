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
            {if $smarty.const.BRANDING==1}
                <div class="brand mybrand">
                    <img src="/template/frontend/local/mysite/resources/images/logo.png">
                </div>
            {else}
                <div class="brand">
                    <div class="dragon"></div>
                    <div class="ttl">CMS Sitebill</div>
                </div>
            {/if}

            {assign var="local_top" value=$SITEBILL_DOCUMENT_ROOT|cat:'/template/frontend/local/admin/data/top_nav_notify.tpl'}
            {if file_exists($local_top)}
                {include file="$local_top"}
            {else}
                {include file='top_nav_notify.tpl'}
            {/if}



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

        </div><!-- /.container-fluid -->
    </div><!-- /.navbar-inner -->
</div>

<div class="main-container container-fluid">
    {assign var="local_sidebar" value=$SITEBILL_DOCUMENT_ROOT|cat:'/template/frontend/local/admin/sidebar.tpl'}
    {if file_exists($local_sidebar)}
        {include file=$local_sidebar}
    {else}
        {include file='sidebar.tpl'}
    {/if}
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
            {if $search_form}
                <div class="row-fluid">
                    <div class="col-xs-12">
                        {$search_form}
                    </div>
                </div>
            {/if}
            {$content}
        </div>

    </div>
</div>
{$messenger_widget}
<a href="#" class="scrollup">{$LT_SCROLLUP}</a>
