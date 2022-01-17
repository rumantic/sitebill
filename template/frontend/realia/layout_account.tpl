<div class="container">
    <div id="main">
        {if $is_account}
            <div class="account">
                {if $breadcrumbs != ''}
                    <div id="breadcrumbs">{$breadcrumbs}</div>
                {/if}   
                <div class="clear"></div>

                {$main}
            </div>
        {else}			

            {if $breadcrumbs != ''}
                <div id="breadcrumbs">{$breadcrumbs}</div>
            {/if}	
            {if $main_file_tpl != ''}
                <div class="clear"></div>
                {include file="$main_file_tpl"}
            {else}
                {$main}
            {/if}

        {/if}
    </div>
</div>