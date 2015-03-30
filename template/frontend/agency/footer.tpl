    <div id="footer">
         {if $show_demo_banners != 1}
            {literal}
            Место для счетчиков
            {/literal}
        {/if}
                        
        {if $show_demo_banners == 1}
            {include file='sitebill_footer.tpl.html'}
        {/if}
    </div>
    <div class="sitebill">
        <span>Сделано на <a href="http://www.sitebill.ru" target="_blank">CMS SiteBill</a></span>
    </div>