<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.SITE_ENCODING}">
	<link href="http://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="{$estate_folder}/apps/system/js/bootstrap/css/bootstrap.min.css" media="screen">
	<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
	<script type="text/javascript" src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>

	<script type="text/javascript" src="{$estate_folder}/apps/system/js/jqueryui/jquery-ui.js"></script>
    <!-- <script src="{$estate_folder}/js/jquery.js"></script> -->
	<script src="http://www.sitebill.ru/js/nanoapi.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
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
	{/literal}
</head>
<body>
<div class="content">
<div class="row-fluid">
	<div id="adminloginform">
		<div class="well">
	        <legend>{$L_AUTHORIZATION}{if $ntext!=''}<br />{$ntext}{/if}</legend>
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
</body>
</html>