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
		
		<div id="lc_full">
		
			
			
			<div id="left_wide">
			{if $breadcrumbs != ''}
					<div id="breadcrumbs">{$breadcrumbs}</div>
				{/if}	
					
				{if $main_file_tpl != ''}
				     <div class="clear"></div>
				    {include file="$main_file_tpl"}
				{else}
					{$main}
				{/if}
			</div>
			
			
			
		</div>
		
	
		{literal}
		<style>
		
    #lc_full {
  width: 998px;
  /* overflow: hidden; */
  float: left;
  width: 998px;
  float: left;
  /* margin-top: 48px; */
  border: 1px solid #CFCFCF;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  border-radius: 5px;
  padding: 10px;
  margin-bottom: 2px;
  margin-left: 3px;
  background-color: white;
}

		#left_wide {
		  width: 100%;
		}
		</style>
		{/literal}
		
		
{include file="footer.tpl"}	
</div>