<div class="socialauth-panel">
    <noindex>
    {if $fb_login_enable==1}<a class="auth icon facebook popup" href=""></a>{/if}
    {if $vk_login_enable==1}<a class="auth icon vkontakte popup" href=""></a>{/if}
    {if $ok_login_enable==1}<a class="auth icon odnoklassniki popup" href=""></a>{/if}
    {if $tw_login_enable==1}<a class="auth icon twitter popup" href=""></a>{/if}
    {if $gl_login_enable==1}<a class="auth icon google popup" href=""></a>{/if}
    {if $tg_login_enable==1}<script async src="https://telegram.org/js/telegram-widget.js?2" data-telegram-login="{$tg_bot_name}" data-size="medium" data-userpic="true" data-auth-url="{$tg_url_back}" data-request-access="write"></script>{/if}
    </noindex>
</div>