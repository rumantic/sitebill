<!-- Sidebar -->
<aside id="tw-sidebar" class="fixed top-[60px] left-0 bottom-0 w-64 bg-white border-r border-slate-200/80 text-slate-600 overflow-y-auto scroll-thin z-20 transition-transform duration-300 -translate-x-full lg:translate-x-0">
    <nav class="py-3 px-3 text-[13.5px]">
        <ul class="space-y-0.5">
            {if isset($interface.sidebar) && !empty($interface.sidebar)}
                {foreach from=$interface.sidebar key=menukey item=menuitem}
                    {if isset($menuitem.incfile)}
                        {include file=$menuitem.incfile}
                    {else}
                        <li>
                            {if isset($menuitem.childs)}
                                <!-- Parent with children -->
                                <div class="nav-link {if $menuitem.active}active{/if} relative rounded-lg">
                                    <span class="nav-ind absolute left-0 top-1.5 bottom-1.5 w-[3px] rounded-full bg-accent {if !$menuitem.active}opacity-0{/if}"></span>
                                    <button class="tw-sidebar-toggle w-full flex items-center gap-3 px-3 py-2 transition-colors hover:bg-slate-50" aria-expanded="{if $menuitem.active}true{else}false{/if}">
                                        <i class="{$menuitem.icon} w-5 text-center text-slate-400"></i>
                                        <span class="{if $menuitem.active}font-medium{/if}">{$menuitem.title}</span>
                                        <svg class="tw-sidebar-arrow w-4 h-4 ml-auto text-slate-400 transition-transform {if $menuitem.active}rotate-90{/if}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                                {if isset($menuitem.childs) && is_array($menuitem.childs)}
                                    <ul class="tw-sidebar-submenu pl-7 pr-1 space-y-0.5 mb-1 mt-0.5 {if !$menuitem.active}hidden{/if}">
                                        {foreach from=$menuitem.childs item=ama}
                                            <li>
                                                <a href="{$ama.href}" class="flex items-center justify-between px-3 py-1.5 rounded-md transition-colors {if $ama.active}bg-accent-50 text-accent-700 font-medium{else}text-slate-500 hover:bg-slate-50{/if}">
                                                    <span>{$ama.title}</span>{if isset($ama.count_key) && $ama.count_key != ''}<span class="sb-tab-count text-[11px] font-mono {if $ama.active}text-accent-600/80{else}text-slate-400{/if}" data-count-key="{$ama.count_key}">…</span>{/if}
                                                </a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            {else}
                                <!-- Simple link -->
                                <a href="{$menuitem.href}" class="nav-link {if $menuitem.active}active{/if} relative flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {if $menuitem.active}font-medium{else}text-slate-600 hover:bg-slate-50{/if}">
                                    <span class="nav-ind absolute left-0 top-1.5 bottom-1.5 w-[3px] rounded-full bg-accent {if !$menuitem.active}opacity-0{/if}"></span>
                                    <i class="{$menuitem.icon} w-5 text-center text-slate-400"></i>
                                    <span>{$menuitem.title}</span>
                                </a>
                            {/if}
                        </li>
                    {/if}
                {/foreach}
            {/if}

            {if isset($interface.recentapps) && !empty($interface.recentapps) && is_array($interface.recentapps) && is_array($interface.recentapps.childs)}
                <li>
                    <button class="tw-sidebar-toggle w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors hover:bg-slate-50">
                        <i class="icon-x fa-desktop w-5 text-center text-slate-400"></i>
                        <span>{$interface.recentapps.title}</span>
                        <svg class="tw-sidebar-arrow w-4 h-4 ml-auto text-slate-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <ul class="tw-sidebar-submenu pl-7 pr-1 space-y-0.5 mb-1 mt-0.5 hidden">
                        {foreach name=i from=$interface.recentapps.childs item=recentapp}
                            {if $smarty.foreach.i.iteration < 10}
                                <li class="px-3 py-1.5 rounded-md text-slate-500 hover:bg-slate-50">{$recentapp}</li>
                            {/if}
                        {/foreach}
                    </ul>
                </li>
            {/if}
        </ul>
    </nav>

    {literal}
    <script>
    // Sidebar submenu toggle
    document.querySelectorAll('.tw-sidebar-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var submenu = this.nextElementSibling;
            if (submenu && submenu.classList.contains('tw-sidebar-submenu')) {
                submenu.classList.toggle('hidden');
                var arrow = this.querySelector('.tw-sidebar-arrow');
                if (arrow) arrow.classList.toggle('rotate-90');
                this.setAttribute('aria-expanded', submenu.classList.contains('hidden') ? 'false' : 'true');
            }
        });
    });

    // Tab counts (same logic as template1)
    (function () {
        var url = '{/literal}{$estate_folder}{literal}/apps/api/rest.php?action=data&do=counts';

        function fillSpan(span, counts) {
            var key = span.getAttribute('data-count-key');
            if (key && typeof counts[key] !== 'undefined') {
                span.textContent = counts[key];
            } else {
                var li = span.closest ? span.closest('li') : span.parentNode;
                if (li) li.style.display = 'none';
            }
        }

        window.sbTabCounts = null;

        window.sbApplyCounts = function (counts) {
            document.querySelectorAll('.sb-tab-count[data-count-key]').forEach(function (span) {
                fillSpan(span, counts);
            });
        };

        var observer = new MutationObserver(function (mutations) {
            if (!window.sbTabCounts) return;
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return;
                    var newSpans = node.querySelectorAll
                        ? node.querySelectorAll('.sb-tab-count[data-count-key]')
                        : [];
                    if (node.classList && node.classList.contains('sb-tab-count')) {
                        fillSpan(node, window.sbTabCounts);
                    }
                    newSpans.forEach(function (s) { fillSpan(s, window.sbTabCounts); });
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });

        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (!json || json.state !== 'success' || !json.message || !json.message.counts) return;
                window.sbTabCounts = json.message.counts;
                window.sbApplyCounts(window.sbTabCounts);
            })
            .catch(function () {
                document.querySelectorAll('.sb-tab-count[data-count-key]').forEach(function (span) {
                    var li = span.closest ? span.closest('li') : span.parentNode;
                    if (li) li.style.display = 'none';
                });
            });
    }());
    </script>
    {/literal}
</aside>
