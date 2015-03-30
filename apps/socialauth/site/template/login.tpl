<link rel="stylesheet" href="{$estate_folder}/apps/socialauth/site/template/css/style.css">
<div class="socialauth-panel">
    <noindex>
    {if $fb_login_enable==1}<a class="auth icon facebook popup" href=""></a>{/if}
    {if $vk_login_enable==1}<a class="auth icon vkontakte popup" href=""></a>{/if}
    {if $ok_login_enable==1}<a class="auth icon odnoklassniki popup" href=""></a>{/if}
    {if $tw_login_enable==1}<a class="auth icon twitter popup" href=""></a>{/if}
    {if $gl_login_enable==1}<a class="auth icon google popup" href=""></a>{/if}
    </noindex>
</div>

{literal}
<script>

$(document).ready(function(){
	$('.socialauth-panel a.auth').click(function(e){
		document.cookie = "back_url=" + window.location.href + ";expires=Mon, 01-Jan-2001 00:00:00 GMT";
		document.cookie = "back_url=" + window.location.href + ";path=/;expires=Mon, 01-Jan-2029 00:00:00 GMT";
		e.preventDefault();
	});
	$('.socialauth-panel a.vkontakte').click(function(e){
		e.preventDefault();
		window.location.replace(estate_folder+'/socialauth/login/vkontakte/');
	});
	$('.socialauth-panel a.facebook').click(function(e){
		e.preventDefault();
		window.location.replace(estate_folder+'/socialauth/login/facebook/');
	});
	$('.socialauth-panel a.odnoklassniki').click(function(e){
		e.preventDefault();
		window.location.replace(estate_folder+'/socialauth/login/odnoklassniki/');
	});
	$('.socialauth-panel a.google').click(function(e){
		e.preventDefault();
		window.location.replace(estate_folder+'/socialauth/login/google/');
	});
	$('.socialauth-panel a.twitter').click(function(e){
		e.preventDefault();
		window.location.replace(estate_folder+'/socialauth/login/twitter/');
	});
});
</script>
<style>

</style>
{/literal}