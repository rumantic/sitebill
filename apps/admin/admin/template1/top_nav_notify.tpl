<ul class="nav ace-nav pull-right">

    {if $interface.admin_admin3}
        <li class="orange">
            <a href="{$interface.admin_admin3.href}" target="_blank" class="btn-warning"><i class="{$interface.admin_admin3.icon}"></i> {$interface.admin_admin3.title}</a>
        </li>
    {/if}


    {if $interface.admin_site}
        <li class="green">
            <a href="{$interface.admin_site.href}" target="_blank"><i class="{$interface.admin_site.icon}"></i> {$interface.admin_site.title}</a>
        </li>
    {/if}

    {if $interface.apps}
        <li>
            {if $smarty.const.DEVMODE==1}
                <a href="#myModalAPP" role="button" class="btn" data-toggle="modal">{$L_ADMIN_MENU_APPLICATIONS}</a>
            {else}
                <a href="#" data-toggle="dropdown" class="btn-info dropdown-toggle">
                    {$L_ADMIN_MENU_APPLICATIONS}
                    <i class="icon-angle-down icon-on-right"></i>
                </a>

                <ul class="dropdown-menu" style="max-height: 90vh; overflow: auto;">
                    {foreach from=$interface.apps.childs item=ama}
                        <li>
                           <a {if isset($ama.childs) && $ama.childs|count>0}data-toggle="dropdown"  class="dropdown-toggle" href="{$ama.href}" data-target="#"{else}href="{$ama.href}"{/if}>{$ama.title}</a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
        </li>
    {/if}
    {if isset($interface.custom_entity_menu) && $interface.custom_entity_menu|count>0}
        <li>
            <a href="#" data-toggle="dropdown" class="btn-info dropdown-toggle">
                {$L_ADMIN_MENU_ADDITIONAL_APPLICATIONS}
                <i class="icon-angle-down icon-on-right"></i>
            </a>

            <ul class="dropdown-menu">
                {foreach from=$interface.custom_entity_menu item=custom_admin_entity}
                    <li>
                        <a href="{$custom_admin_entity.href}">{$custom_admin_entity.entity_title}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}

    {if isset($interface.langswitcher)}
        <li>
            <a href="#" data-toggle="dropdown" class="btn-info dropdown-toggle">
                <i class="icon-globe icon-on-right"></i> {$interface.langswitcher.current}
                <i class="icon-angle-down icon-on-right"></i>
            </a>

            <ul class="dropdown-menu">
                {foreach from=$interface.langswitcher.variants item=variant}
                    <li{if $variant.active == 1} class="active"{/if}><a href="{$variant.href}">{$variant.name}</a></li>
                {/foreach}
            </ul>
        </li>
    {/if}

    {if $smarty.const.BRANDING!=1}
        {if $interface.knowlegebase && !empty($interface.knowlegebase)}
            <li class="purple">
                <a href="#" data-toggle="dropdown" class="dropdown-toggle">
                    <i class="icon-question-sign icon-on-right"></i>
                </a>

                <ul class="dropdown-menu">
                    {foreach from=$interface.knowlegebase item=item}
                    <li>
                        <a href="{$item.href}"{if $item.hreftarget != ''} target="_blank"{/if}><i class="icon-white{if $item.icon != ''} {$item.icon}{/if}"></i> {$item.title}</a>
                    </li>
                    {/foreach}
                </ul>
            </li>
        {/if}
    {/if}


    {if $admin_template_allowed|@count > 1}
    <li>
        <a href="#" data-toggle="dropdown" class="dropdown-toggle">
            <i class="icon-magic"></i>
            <i class="icon-angle-down icon-on-right"></i>
        </a>
        <ul class="dropdown-menu dropdown-caret dropdown-closer">
            {foreach from=$admin_template_allowed item=tpl_name}
                <li{if $tpl_name == $admin_template} class="active"{/if}>
                    <a href="?admin_template={$tpl_name}">{$tpl_name}</a>
                </li>
            {/foreach}
        </ul>
    </li>
    {/if}


    <li class="light-blue">
        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
            {if $current_user_info.imgfile.value != ''}
            <img class="nav-user-photo" src="{$estate_folder}/img/data/user/{$current_user_info.imgfile.value}"  />
            {else}
            <img class="nav-user-photo" src="{$assets_folder}/assets/avatars/avatar2.png"  />
            {/if}

            <span class="user-info">
                <small>Привет,</small>
                {if $current_user_info.fio.value != ''}{$current_user_info.fio.value}{else}{$current_user_info.login.value}{/if}
            </span>

            <i class="icon-caret-down"></i>
        </a>

        <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
            <li>
                <a href="{$estate_folder}/admin/?action=user&do=edit&user_id={$current_user_info.user_id.value}">
                    <i class="icon-user"></i>
                    {$L_MY_PROFILE}
                </a>
            </li>

            <li class="divider"></li>

            <li>
                <a href="?action=logout">
                    <i class="icon-off"></i>
                    {$L_LOGOUT_BUTTON}
                </a>
            </li>
        </ul>
    </li>
</ul>
