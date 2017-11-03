<link rel="stylesheet" href="{$estate_folder}/apps/socialauth/site/template/css/style.css">
{include file=$buttons_tpl}

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