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
					{*if $geodata_on_home}
		                {include file="map_on_main.tpl"}
					{/if*}
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
        	{include file="top_special.tpl"}
                {$articles_block_html}
     		{include file="news_list_column.tpl"}
	</div>
</div>