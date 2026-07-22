<!-- Object-only mode (iframe) -->
<div class="min-h-screen bg-slate-50">
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
        {$content}
    </div>
</div>
