<div class="container">
    <div id="main">
        <div class="row">
            <div class="span9">
                {if $is_account}
                    <div class="account">
                        {if $breadcrumbs != ''}
                            <div id="breadcrumbs">{$breadcrumbs}</div>
                        {/if}   
                        <div class="clear"></div>

                        {if $main_file_tpl != ''}
                            <h1 class="page-header">{$title}</h1>
                            {include file="$main_file_tpl"}
                        {else}
                            <h1 class="page-header">{$title}</h1>
                            {$main}
                        {/if}
                    </div>
                {else}	


                    {if $breadcrumbs != ''}
                        <div id="breadcrumbs">{$breadcrumbs}</div>
                    {/if}


                    {if $main_file_tpl != ''}
                        <h1 class="page-header">{$title}</h1>
                        {include file="$main_file_tpl"}
                    {else}
                        <h1 class="page-header">{$title}</h1>
                        {$main}
                    {/if}

                {/if}
                {include file="top_special.tpl"}
            </div>

            <div class="sidebar span3">
                {include file="agents_list.tpl"}
                {include file='right_special.tpl'}
            </div>
        </div>
        {include file="news_list_column.tpl"}	
    </div>
</div>