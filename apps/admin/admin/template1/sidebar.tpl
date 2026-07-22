<div id="sidebar" class="sidebar responsive" data-sidebar="true" data-sidebar-scroll="true" data-sidebar-hover="true">
    <ul class="nav nav-list">

        {if isset($interface.sidebar) && !empty($interface.sidebar)}

            {foreach from=$interface.sidebar key=menukey item=menuitem}
                {if isset($menuitem.incfile)}
                    {include file=$menuitem.incfile}
                {else}
                    <li {if $menuitem.active}class="active open"{/if}>
                        {if isset($menuitem.childs)}
                            <a href="#" class="dropdown-toggle">
                                <i class="{$menuitem.icon}"></i>
                                <span class="menu-text"> {$menuitem.title}</span>
                                <b class="arrow icon-angle-down"></b>
                            </a>
                        {else}
                            <a href="{$menuitem.href}">
                                <i class="{$menuitem.icon}"></i> <span class="menu-text"> {$menuitem.title}</span>
                            </a>
                        {/if}
                        {if isset($menuitem.childs) && is_array($menuitem.childs)}
                            <ul class="submenu">

                                {foreach from=$menuitem.childs item=ama}
                                    <li {if $ama.active}class="active"{/if}>
                                        <a href="{$ama.href}">{$ama.title}{if isset($ama.count_key) && $ama.count_key != ''} (<span class="sb-tab-count" data-count-key="{$ama.count_key}">…</span>){/if}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        {/if}

                    </li>
                {/if}
            {/foreach}

        {/if}


        {if isset($interface.recentapps) && !empty($interface.recentapps) && is_array($interface.recentapps) && is_array($interface.recentapps.childs)}
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-x fa-desktop"></i>
                    <span class="menu-text">{$interface.recentapps.title}</span>
                    <b class="arrow icon-angle-down"></b>
                </a>
                <ul class="submenu">
                    {foreach name=i from=$interface.recentapps.childs item=recentapp}
                        {if $smarty.foreach.i.iteration < 10}
                            <li>{$recentapp}</li>
                        {/if}
                    {/foreach}
                </ul>
            </li>
        {/if}

        {*
        {if $admin_menua.structure && $data_category_tree != ''}
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-folder-close"></i>
                    <span class="menu-text"> {_e t="Категории"} </span>
                    <b class="arrow icon-angle-down"></b>
                </a>
                <div class="submenu">
                    <div class=" nolinedotted">{$data_category_tree}</div>
                </div>
            </li>
        {/if}
        *}
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

    {literal}
    <script type="text/javascript">
    (function () {
        var url = '{/literal}{$estate_folder}{literal}/apps/api/rest.php?action=data&do=counts';

        // Заполняет один спан из кэша
        function fillSpan(span, counts) {
            var key = span.getAttribute('data-count-key');
            if (key && typeof counts[key] !== 'undefined') {
                span.textContent = counts[key];
            } else {
                var li = span.closest ? span.closest('li') : span.parentNode;
                if (li) li.style.display = 'none';
            }
        }

        // Глобальный кэш — заполняется после загрузки данных
        window.sbTabCounts = null;

        // Заполняет все текущие непроставленные спаны
        window.sbApplyCounts = function (counts) {
            document.querySelectorAll('.sb-tab-count[data-count-key]').forEach(function (span) {
                fillSpan(span, counts);
            });
        };

        // MutationObserver — ловит спаны, добавленные Angular позже
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

        // Загружаем данные один раз
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

</div>
