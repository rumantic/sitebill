<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>TC</title>
<script type="text/javascript">
var estate_folder = '{$estate_folder}'; 
</script>
<link rel="stylesheet" href="{$estate_folder}/apps/system/js/bootstrap/css/bootstrap.min.css" media="screen">
<link rel=stylesheet type="text/css" href="{$estate_folder}/template/frontend/agency/css/bootstrap.corrections.css">
<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/jqueryui/jquery-ui.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
<link rel="stylesheet" href="{$estate_folder}/template/frontend/agency/jquery-ui/themes/base/jquery.ui.all.css">
{literal}
<style>
body {
	text-align: center;
}
#tc {
	display: inline-block;
	margin: 0 auto;
	border: 1px solid silver;
	border-radius: 5px;
}
.tcinner {
	position: relative;
	width: 600px;
	height: 300px;
	margin: 10px;
	background-image: url('{/literal}{$estate_folder}{literal}/template/frontend/agency/img/tclogo.png');
	background-position: 0 0;
	background-repeat: no-repeat;
}
.tcinner-info {
	position: absolute;
	bottom: 0;
	right: 0;
	width: 400px;
	max-height: 200px;
	overflow: hidden;
}
</style>
<script>
	$(document).ready(function(){
		var tc=$('#tc');
		var dp=SitebillCore.getDialogPositionCoords(tc.width(), tc.height());
		$('#tc').css({'margin-top':dp[1]});
	});
</script>
{/literal}
</head>
<body>
<div id="tc">
	<div class="tcinner">
		<div class="tcinner-info">
			{$LT_TEXT_CLOSED}
		</div>
	</div>
</div>
</body>
</html>