{foreach from=$pager_array.pages item=pager_page}
    {if $pager_page.current==1}
        {assign var=__curpagenr value=$pager_page.text}
    {/if}
{/foreach}


{if $__curpagenr-4<1}
    {assign var=__startnr value=1}
    {assign var=__leftsep value=0}
{else}
    {assign var=__startnr value=$__curpagenr-3}
    {assign var=__leftsep value=1}
{/if}

{if $__curpagenr+4>$pager_array.pages|count}
    {assign var=__endnr value=$pager_array.pages|count}
    {assign var=__rightsep value=0}
{else}
    {assign var=__endnr value=$__curpagenr+3}
    {assign var=__rightsep value=1}
{/if}


{if $pager_array.pages|count>1}
    <ul>
        {if $__leftsep==1}
            <li><a href="javascript:void(0);" alt="{$pager_array.pages[1].text}">{$pager_array.pages[1].text}</a></li>
            <li><a href="javascript:void(0);" class="points">...</a></li>
            {/if}
            {foreach from=$pager_array.pages item=pager_page}
                {if $pager_page.text>=$__startnr && $pager_page.text<=$__endnr}
                <li{if $pager_page.current==1} class="active"{/if}><a href="javascript:void(0);" alt="{$pager_page.text}">{$pager_page.text}</a></li>
                {/if}


        {/foreach}
        {if $__rightsep==1}
            <li><a href="javascript:void(0);" class="points">...</a></li>
            <li><a href="javascript:void(0);" alt="{$pager_array.pages[$pager_array.pages|count].text}">{$pager_array.pages[$pager_array.pages|count].text}</a></li>
            {/if}

    </ul>
{/if}