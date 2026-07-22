<!-- Top navigation right section (Tailwind version) -->
<div class="flex items-center space-x-2">
    {if $interface.admin_admin3}
        <a href="{$interface.admin_admin3.href}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-sm rounded hover:bg-amber-600 transition-colors">
            <i class="{$interface.admin_admin3.icon} mr-1"></i> {$interface.admin_admin3.title}
        </a>
    {/if}

    {if $interface.admin_site}
        <a href="{$interface.admin_site.href}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors">
            <i class="{$interface.admin_site.icon} mr-1"></i> {$interface.admin_site.title}
        </a>
    {/if}

    {if $interface.apps}
        <div class="relative" id="tw-apps-dropdown">
            <button onclick="this.parentElement.querySelector('.tw-dropdown-menu').classList.toggle('hidden')" class="inline-flex items-center px-3 py-1.5 bg-accent text-white text-sm rounded-lg hover:bg-accent-700 transition-colors">
                {$L_ADMIN_MENU_APPLICATIONS}
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div class="tw-dropdown-menu hidden absolute right-0 mt-1 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 max-h-[80vh] overflow-auto z-50">
                <div class="py-1">
                    {foreach from=$interface.apps.childs item=ama}
                        <a href="{$ama.href}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{$ama.title}</a>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if isset($interface.custom_entity_menu) && $interface.custom_entity_menu|count>0}
        <div class="relative" id="tw-custom-dropdown">
            <button onclick="this.parentElement.querySelector('.tw-dropdown-menu').classList.toggle('hidden')" class="inline-flex items-center px-3 py-1.5 bg-accent text-white text-sm rounded-lg hover:bg-accent-700 transition-colors">
                {$L_ADMIN_MENU_ADDITIONAL_APPLICATIONS}
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div class="tw-dropdown-menu hidden absolute right-0 mt-1 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    {foreach from=$interface.custom_entity_menu item=custom_admin_entity}
                        <a href="{$custom_admin_entity.href}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{$custom_admin_entity.entity_title}</a>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if isset($interface.langswitcher)}
        <div class="relative" id="tw-lang-dropdown">
            <button onclick="this.parentElement.querySelector('.tw-dropdown-menu').classList.toggle('hidden')" class="inline-flex items-center px-3 py-1.5 text-gray-600 text-sm rounded border border-gray-300 hover:bg-gray-50 transition-colors">
                <i class="icon-globe mr-1"></i> {$interface.langswitcher.current}
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <div class="tw-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    {foreach from=$interface.langswitcher.variants item=variant}
                        <a href="{$variant.href}" class="block px-4 py-2 text-sm {if $variant.active == 1}text-accent-700 font-medium bg-accent-50{else}text-gray-700 hover:bg-gray-100{/if}">{$variant.name}</a>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if $smarty.const.BRANDING!=1}
        {if $interface.knowlegebase && !empty($interface.knowlegebase)}
            <div class="relative" id="tw-help-dropdown">
                <button onclick="this.parentElement.querySelector('.tw-dropdown-menu').classList.toggle('hidden')" class="inline-flex items-center justify-center w-8 h-8 text-purple-600 rounded-full hover:bg-purple-50 transition-colors">
                    <i class="icon-question-sign"></i>
                </button>
                <div class="tw-dropdown-menu hidden absolute right-0 mt-1 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        {foreach from=$interface.knowlegebase item=item}
                            <a href="{$item.href}"{if $item.hreftarget != ''} target="_blank"{/if} class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {if $item.icon != ''}<i class="{$item.icon} mr-1"></i>{/if} {$item.title}
                            </a>
                        {/foreach}
                    </div>
                </div>
            </div>
        {/if}
    {/if}

    <!-- Template switcher -->
    {if $admin_template_allowed|@count > 1}
    <div class="relative" id="tw-template-dropdown">
        <button onclick="this.parentElement.querySelector('.tw-dropdown-menu').classList.toggle('hidden')" class="inline-flex items-center px-2 py-1.5 text-gray-500 text-sm rounded hover:bg-gray-100 transition-colors" title="Шаблон: {$admin_template}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
        </button>
        <div class="tw-dropdown-menu hidden absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
            <div class="py-1">
                {foreach from=$admin_template_allowed item=tpl_name}
                    <a href="?admin_template={$tpl_name}" class="block px-4 py-2 text-sm {if $tpl_name == $admin_template}text-accent-700 font-medium bg-accent-50{else}text-gray-700 hover:bg-gray-100{/if}">
                        {$tpl_name}
                    </a>
                {/foreach}
            </div>
        </div>
    </div>
    {/if}

    <!-- User menu -->
    <div class="relative" id="tw-user-dropdown">
        <button onclick="this.parentElement.querySelector('.tw-dropdown-menu').classList.toggle('hidden')" class="flex items-center space-x-2 px-2 py-1 rounded hover:bg-gray-100 transition-colors">
            {if $current_user_info.imgfile.value != ''}
                <img class="w-8 h-8 rounded-full object-cover" src="{$estate_folder}/img/data/user/{$current_user_info.imgfile.value}" alt="" />
            {else}
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-accent to-indigo-500 flex items-center justify-center text-white text-sm font-medium">
                    {if $current_user_info.fio.value != ''}{$current_user_info.fio.value|mb_substr:0:1|strtoupper}{else}U{/if}
                </div>
            {/if}
            <div class="hidden sm:block text-left">
                <div class="text-xs text-gray-500">Привет,</div>
                <div class="text-sm text-gray-800 font-medium truncate max-w-[120px]">
                    {if $current_user_info.fio.value != ''}{$current_user_info.fio.value}{else}{$current_user_info.login.value}{/if}
                </div>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
        <div class="tw-dropdown-menu hidden absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
            <div class="py-1">
                <a href="{$ADMIN_BASE}?action=user&do=edit&id={$smarty.session.user_id}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="icon-cog mr-1"></i> {$L_SETTINGS}
                </a>
                <a href="{$ADMIN_BASE}?action=logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <i class="icon-off mr-1"></i> {$L_LOGOUT_BUTTON}
                </a>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    document.querySelectorAll('.tw-dropdown-menu').forEach(function(menu) {
        if (!menu.parentElement.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
});
</script>
{/literal}
