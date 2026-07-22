<!-- Top Navbar -->
<nav class="bg-white/90 backdrop-blur border-b border-slate-200/80 fixed top-0 left-0 right-0 z-30">
    <div class="flex items-center justify-between px-4 h-[60px]">
        <!-- Left: hamburger + brand -->
        <div class="flex items-center space-x-3">
            <button id="tw-menu-toggler" class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden" aria-label="Toggle sidebar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            {if $smarty.const.BRANDING==1}
                <a href="{$ADMIN_BASE}" class="flex items-center">
                    <img src="/template/frontend/local/mysite/resources/images/logo.png" class="h-8" alt="Logo">
                </a>
            {else}
                <a href="{$ADMIN_BASE}" class="flex items-center space-x-2">
                    <span class="text-lg font-bold text-gray-800">CMS Sitebill</span>
                </a>
            {/if}
        </div>

        <!-- Right: top navigation -->
        <div class="flex items-center space-x-2">
            {assign var="local_top" value=$SITEBILL_DOCUMENT_ROOT|cat:'/template/frontend/local/admin/data/top_nav_notify.tpl'}
            {if file_exists($local_top)}
                {include file="$local_top"}
            {else}
                {include file='top_nav_notify.tpl'}
            {/if}
        </div>
    </div>
</nav>

<!-- Sidebar overlay (mobile) -->
<div id="tw-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="document.getElementById('tw-sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');"></div>

<!-- Layout wrapper -->
<div class="flex pt-[60px] min-h-screen">
    <!-- Sidebar -->
    {assign var="local_sidebar" value=$SITEBILL_DOCUMENT_ROOT|cat:'/template/frontend/local/admin/sidebar.tpl'}
    {if file_exists($local_sidebar)}
        {include file=$local_sidebar}
    {else}
        {include file='sidebar.tpl'}
    {/if}

    <!-- Main content -->
    <main class="flex-1 lg:ml-64 transition-all duration-300">
        <!-- Breadcrumbs -->
        <div class="bg-white/90 backdrop-blur border-b border-slate-200/80 px-4 py-2">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    {foreach from=$breadcrumbs_array item=crumb name=bread}
                        <li class="flex items-center">
                            {if $smarty.foreach.bread.first}
                                <svg class="w-4 h-4 text-slate-400 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                            {/if}
                            {if $smarty.foreach.bread.last}
                                <span class="text-slate-500">{$crumb.title}</span>
                            {else}
                                <a href="{$crumb.href}" class="text-accent-600 hover:text-accent-800">{$crumb.title}</a>
                                <svg class="w-4 h-4 text-slate-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            {/if}
                        </li>
                    {/foreach}
                </ol>
            </nav>
        </div>

        <!-- Page content -->
        <div class="p-6 space-y-5">
            {if $search_form}
                <div class="mb-4">
                    {$search_form}
                </div>
            {/if}
            {$content}
        </div>
    </main>
</div>

{$messenger_widget}

<!-- Scroll to top button -->
<button onclick="window.scrollTo({ldelim}top:0, behavior:'smooth'{rdelim})" class="fixed bottom-6 right-6 bg-accent-600 text-white p-3 rounded-full shadow-lg hover:bg-accent-700 transition-colors hidden" id="tw-scrollup">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
</button>

{literal}
<script>
    // Sidebar toggle (mobile)
    document.getElementById('tw-menu-toggler').addEventListener('click', function() {
        var sidebar = document.getElementById('tw-sidebar');
        var overlay = document.getElementById('tw-sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    // Scroll to top visibility
    window.addEventListener('scroll', function() {
        var btn = document.getElementById('tw-scrollup');
        if (window.scrollY > 300) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    });
</script>
{/literal}
