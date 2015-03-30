<div id="content">
            {include file="top_fixed_menu.tpl.html"}
	
	
			<div class="header">
            <a href="{$estate_folder}/"><img class="logo" src="{$estate_folder}/template/frontend/agency/img/{$template_vars_logo}" alt="" title=""></a>

            {if $show_demo_banners == 1}
            <div id="es">
            <a href="http://www.sitebill.ru/demo/"><img src="{$estate_folder}/template/frontend/agency/img/demo_transparent1.png" align=left width="214" height="78" border="0" alt="скачать демо-версию" title="скачать демо-версию"></a>
            </div>

            <div id="es">
            <a href="http://www.sitebill.ru/price-cms-sitebill/"><img src="{$estate_folder}/template/frontend/agency/img/buy_product.png" align=left width="280" height="78" border="0" alt="купить CMS Sitebill" title="купить CMS Sitebill"></a>
            </div>
            
            <div id="es">
            <a href="http://www.sitebill.ru/client/cart.php?gid=6"><img src="{$estate_folder}/template/frontend/agency/img/template.png" align=left width="196" height="78" border="0" alt="Шаблоны для CMS Sitebill" title="Шаблоны для CMS Sitebill"></a>
            </div>
            
            
            {/if}

            
        <div class="clear"></div>            
		{include file="slidemenu.tpl"}
		</div>
		
		<div id="lc">
		
			<div id="left1">
			{if $category_tree != ''}
				<div id="tree">
					<ul class="submenu">{$category_tree}</ul>
				</div>
				<div class="clear"></div>
			{/if}
			
            {if $is_account}
            <div class="account">
                {if $breadcrumbs != ''}
                    <div id="breadcrumbs">{$breadcrumbs}</div>
                {/if}   
                <div class="clear"></div>
            	{if $main_file_tpl != ''}
				     <div class="clear"></div>
				    {include file="$main_file_tpl"}
				{else}
					{$main}
				{/if}
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
		<div id="rc">
		    {if $right_column != ''}
			     <div class="rcont">
			     {if $show_demo_banners == 1}   
				<div id="right2">
				    <div class="vk">							
					<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?22"></script>
					
					<!-- VK Widget -->
					<div id="vk_groups"></div>
					<script type="text/javascript">
					{literal}
					VK.Widgets.Group("vk_groups", {mode: 0, width: "229", height: "190"}, 25347835);
					{/literal}
					</script>
					<br> 
					</div>
				</div>
				{/if}
                    <div id="right2">                           
						<div id="news_column">
						{include file="news_list_column.tpl"}
						</div>
					{include file="right_special.tpl"}
					
                        <div id="news_column">
            {$apps_pages_column}
                        </div>
					
                    </div>
                    
                                
				</div>
			{/if}
			{if $is_account and $category_tree_account}
                 <div class="rcont">
                    <div id="right2">                           
                    
			     {$category_tree_account}
			     </div>
           		 </div>
			{/if}
		</div>
	
		<div class="clear"></div>
		
{include file="footer.tpl"}	
</div>