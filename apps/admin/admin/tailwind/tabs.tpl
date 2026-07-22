<div class="card !rounded-b-none border-b-0">
    <nav class="flex overflow-x-auto scroll-thin">
        {section name=i loop=$tabs}
            <a href="{$tabs[i].url}" class="tab {if $tabs[i].current==1}active{/if} relative px-4 py-3.5 text-[13.5px] font-medium whitespace-nowrap transition-colors {if $tabs[i].current==1}text-accent-700{else}text-slate-500 hover:text-slate-700{/if}">
                {$tabs[i].title}
                <span class="tab-line absolute left-3 right-3 -bottom-px h-[2px] bg-accent rounded-full"></span>
            </a>
        {/section}
    </nav>
</div>
