<div id="sidebar" class="sidebar                  responsive" data-sidebar="true" data-sidebar-scroll="true" data-sidebar-hover="true">            
    <ul class="nav nav-list">
        <li>
            <a href="{$estate_folder}/admin/"><i class="icon-home"></i> <span class="menu-text">{$L_HOME}</span></a>
        </li>

        {if $admin_menua.data}

            {assign var="local_data_menu" value="`$smarty.const.SITEBILL_DOCUMENT_ROOT`/apps/admin/admin/template1/local_data_menu.tpl"}
            {if $local_data_menu|file_exists}
                {include file='local_data_menu.tpl'}
            {else}
                <li {if $admin_menua.data.active}class="active open"{/if}>
                    <a href="{$estate_folder}/admin/?action=data">
                        <i class="icon-book"></i> <span class="menu-text">{$L_ADMIN_MENU_AUTOADVERTS}</span>
                    </a>
                    <!-- 
                    {if $admin_menua.datamain.childs.data.childs|count>0}
                    <ul class="submenu">
                        {foreach from=$admin_menua.datamain.childs.data.childs item=ama}
                          <li {if $ama.active}class="active"{/if}>
                          <a href="{$ama.href}">{$ama.title}</a>
                          </li>
                        {/foreach}
                        </ul>
                    {/if} 
                    -->


                    {if 1==0}
                        <!-- ul class="submenu">
                        
                          <li {if $ama.active}class="active"{/if}>
                          <a href="{$estate_folder}/admin/?action=data">Актуальные</a>
                          </li>

                          <li {if $ama.active}class="active"{/if}>
                          <a href="{$estate_folder}/admin/?action=data">На прозвон</a>
                          </li>

                          <li {if $ama.active}class="active"{/if}>
                          <a href="{$estate_folder}/admin/?action=data">Не дозвонились</a>
                          </li>

                          <li {if $ama.active}class="active"{/if}>
                          <a href="{$estate_folder}/admin/?action=data">Модерация</a>
                          </li>

                          <li {if $ama.active}class="active"{/if}>
                          <a href="{$estate_folder}/admin/?action=data">Архив</a>
                          </li>
                          
                        </ul-->
                    {/if}
                </li>
            {/if}
        {/if}
        {if $admin_menua.client}
            <li {if $admin_menua.client.active}class="active open"{/if}><a href="{$estate_folder}/admin/?action=client"><i class="icon- ace-icon fa fa-heart bigger-125"></i> <span class="menu-text">{if $L_CLIENT_MENU != ''}{$L_CLIENT_MENU}{else}Клиенты{/if}</span></a></li>
            {/if}


        {if $admin_menua.references}
            <li {if $admin_menua.references.active}class="active open"{/if}>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-globe"></i>
                    <span class="menu-text"> {$L_ADMIN_MENU_REFERENCES} </span>
                    <b class="arrow icon-angle-down"></b>
                </a>

                <ul class="submenu">

                    {foreach from=$admin_menua.references.childs item=ama}
                        <li {if $ama.active}class="active"{/if}>
                            <a href="{$ama.href}">{$ama.title}</a>
                        </li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if $admin_menua.components}
            <li {if $admin_menua.components.active}class="active open"{/if}>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-legal"></i>
                    <span class="menu-text"> Компоненты </span>
                    <b class="arrow icon-angle-down"></b>
                </a>

                <ul class="submenu">

                    {foreach from=$admin_menua.components.childs item=ama}
                        <li  {if $ama.active}class="active"{/if}>
                            <a href="{$ama.href}">{$ama.title}</a>
                        </li>
                    {/foreach}
                </ul>
            </li>
        {/if}
        {if $admin_menua.content}
            <li {if $admin_menua.content.active}class="active open"{/if}>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-coffee"></i>
                    <span class="menu-text"> {if $L_CONTENT_MENU != ''}{$L_CONTENT_MENU}{else}Content{/if} </span>
                    <b class="arrow icon-angle-down"></b>
                </a>

                <ul class="submenu">

                    {foreach from=$admin_menua.content.childs item=ama}
                        <li  {if $ama.active}class="active"{/if}>
                            <a href="{$ama.href}">{$ama.title}</a>
                        </li>
                    {/foreach}
                </ul>
            </li>
        {/if}



        {if $admin_menua.apps.childs.config}
            <li {if $admin_menua.config.active}class="active open"{/if}>
                <a href="{$estate_folder}/admin/?action=config"><i class="icon-cog"></i> <span class="menu-text">{$L_ADMIN_MENU_SETTINGS}</span></a>
            </li>
        {/if}
        {if $admin_menua.sitebill}
            <li {if $admin_menua.sitebill.active}class="active open"{/if}>
                <a href="{$estate_folder}/admin/?action=sitebill"><i class="icon-refresh"></i> 
                    <span class="menu-text">{$L_ADMIN_MENU_UPDATES}</span>
                </a>
            </li>
        {/if}
        {if $admin_menua.user}
            <li {if $admin_menua.user.active}class="active open"{/if}><a href="{$estate_folder}/admin/?action=user"><i class="icon-user"></i> <span class="menu-text">{$L_USER_MENU}</span></a></li>
                {/if}

        {if $admin_menua.structure}
            <li {if $admin_menua.structure.active}class="active open"{/if}>
                <a href="{$estate_folder}/admin/?action=structure"><i class="icon-th-list"></i> <span class="menu-text">{$L_ADMIN_MENU_STRUCTURE}</span></a></li>
                {/if}
        {if $admin_menua.table}
        <li {if $admin_menua.table.active}class="active open"{/if}><a href="{$estate_folder}/admin/?action=table">
                <i class="icon-edit"></i> 
                <span class="menu-text">{if $L_TABLE_MENU != ''}{$L_TABLE_MENU}{else}Form editor{/if}</span></a></li>
        {/if}
                {if $admin_menua.access}
            <li {if $admin_menua.access.active}class="active open"{/if}>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-group"></i>
                    <span class="menu-text"> {$L_ADMIN_MENU_ACCESS} </span>
                    <b class="arrow icon-angle-down"></b>
                </a>

                <ul class="submenu">

                    {foreach from=$admin_menua.access.childs item=ama}
                        <li  {if $ama.active}class="active"{/if}>
                            <a href="{$ama.href}">{$ama.title}</a>
                        </li>
                    {/foreach}
                </ul>
            </li>
        {/if}

        <li>
            <a href="#" class="dropdown-toggle">
                <i class="icon-desktop"></i>
                <span class="menu-text"> Недавние </span>

                <b class="arrow icon-angle-down"></b>
            </a>

            <ul class="submenu">
                {section name=le  max=10  loop=$smarty.session.recently_apps}
                    <li>{$smarty.session.recently_apps[le]}</li>
                    {/section}
            </ul>
        </li>

        {if $data_category_tree != ''}
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-folder-close"></i>
                    <span class="menu-text"> Категории </span>

                    <b class="arrow icon-angle-down"></b>
                </a>

                <div class="submenu">
                    <div class=" nolinedotted">{$data_category_tree}</div>
                </div>
            </li>
        {/if}

        <li>
            <a href="https://play.google.com/store/apps/details?id=ru.sitebill.mobilecms" target="_blank">
                <i class="icon-camera"></i>
                <span class="menu-text">Мобильное фото</span></a>
        </li>


    </ul>


    <div class="sidebar-collapse" id="sidebar-collapse">
        <i class="icon-double-angle-left" data-icon1="icon-double-angle-left" data-icon2="icon-double-angle-right"></i>
    </div>

    {literal}
        <script type="text/javascript">
            try {
                ace.settings.check('sidebar', 'collapsed')
            } catch (e) {
            }
        </script>
    {/literal}

</div>
