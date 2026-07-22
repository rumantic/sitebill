<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.SITE_ENCODING}">
	{if $loadnanoapi}
	<link href="http://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
	{/if}
	<link rel="stylesheet" href="{$estate_folder}/apps/system/js/bootstrap/css/bootstrap.min.css" media="screen">
	<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
	<script type="text/javascript" src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>

	<script type="text/javascript" src="{$estate_folder}/apps/system/js/jqueryui/jquery-ui.js"></script>
    <!-- <script src="{$estate_folder}/js/jquery.js"></script> -->
	{if $loadnanoapi}
	<script src="http://www.sitebill.ru/js/nanoapi.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
	{/if}
	<!--  <link rel=stylesheet type="text/css" href="{$estate_folder}/css/style.css"> -->
	<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
	{literal}
	<script>
	var estate_folder='{/literal}{$estate_folder}{literal}';
	</script>
	<style>
		#adminloginform {
			width: 350px;
		}
		.alert {
			display: none;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function(){
			var h=$('#adminloginform').height();
			var w=$('#adminloginform').width();
			var c=SitebillCore.getDialogPositionCoords(w, h);
			$('#adminloginform').css({'margin-top':c[1]+'px', 'margin-left':c[0]+'px'});
			
			$('[name=captcha]').addClass('span12');
		});
	</script>
	<style>
		.a1 {
			display: flex;
			min-height: 100vh;
			align-items: center;
			justify-content: center;
		}
		.a2 {
			width: 100vw;
			max-width: 28rem;
		}
		.a3 {
			-webkit-backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
			backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
			padding: 2rem;
			background-color: hsla(0,0%,100%,.5);
			border-radius: 16px;
			border: 1px solid #eee;
			box-shadow: 0 0 #0000,0 0 #0000,0 25px 50px -12px rgba(0,0,0,.25);
		}

		.a3 legend {
			font-size: 1.5rem;
			line-height: 2rem;
			font-weight: 700;
			text-align: center;
		}

		.a3 [type=text], .a3 [type=password] {
			appearance: none;
			background-color: #fff;
			border-color: #6b7280;
			border-radius: 0;
			border-width: 1px;
			font-size: 1rem;
			line-height: 1.5rem;
			padding: 0.5rem 0.75rem;
			height: auto;
			width: 100%;
			border-radius: 0.5rem;
			--tw-border-opacity: 1;
			border-color: rgb(209 213 219/var(--tw-border-opacity));
		}

		.a3 .btn {
			display: block;
			width: 100%;
		}
	</style>
	{/literal}
</head>
<body>
	<div class="a1">
		<div class="a2">
			<div class="a3">
				<legend>{$L_AUTHORIZATION}{if isset($ntext) && $ntext!=''}<br />{$ntext}{/if}</legend>
				{$formbody}
			</div>
		</div>
	</div>
	{*
	<div class="content">
<div class="row-fluid">
	<div id="adminloginform">
		<div class="well">
	        <legend>{$L_AUTHORIZATION}{if isset($ntext) && $ntext!=''}<br />{$ntext}{/if}</legend>
	        {$formbody}
	        {if 1==0}<form method="POST" action="">
	        	<div class="alert alert-error">
	            	<a class="close" data-dismiss="alert" href="#">x</a>Incorrect Username or Password!
				</div>      
	            <input class="span12" placeholder="{$L_AUTH_LOGIN}" type="text" name="username">
	            <input class="span12" placeholder="{$L_AUTH_PASSWORD}" type="password" name="password"> 
	            <label class="checkbox">
	                <input type="checkbox" name="remember" value="1"> Remember Me
	            </label>
	            <button class="btn-info btn" type="submit">{$L_AUTH_ENTER}</button>      
	        </form>{/if}
		</div>
	</div>
</div>
</div>
	*}
</body>
</html>