<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
     <title>CMS</title>
    <link rel="stylesheet" href="{$MAIN_URL}/css/admin.css">
    <link rel="stylesheet" href="{$MAIN_URL}/css/system.css">
    <link rel="stylesheet" href="{$MAIN_URL}/css/menu.css">
    <script src="{$MAIN_URL}/js/jquery.js"></script>
	<link href="http://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
	<script src="http://www.sitebill.ru/js/nanoapi.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
	<script src="{$MAIN_URL}/js/interface.js"></script>
	<script src="'{$MAIN_URL}/js/estate.js"></script>
	<script type="text/javascript" src="{$MAIN_URL}/js/jquery.tablesorter.min.js"></script>
	
</head>
<body onload="runDialog('homescript_etown_ru'); {$onload}">
<div id="content">
    <div id="esh">
        <div id="ctop"><img src="/img/1x1.gif" width="916" height="6" border="0"></div>
        <div id="cmiddle">
        <div id="est">CMS</div>
        <div class="top_text"><a href="?action=logout">{$L_LOGOUT_BUTTON}</a></div> <div class="top_image"><a href="?action=logout"><img src="{$MAIN_URL}/img/logout.png" border="0"></a></div>
		</div>
        <div id="cbottom"><img src="{$MAIN_URL}/img/1x1.gif" width="916" height="7" border="0"></div>
    </div>
    <div id="menu_top_cont">
    	<ul id="menulist_root-son-of-suckerfish-horizontal" class="mainlevel-son-of-suckerfish-horizontal" >
           {$admin_menu}
      	</ul> 
    </div>

    <div id="es">
    
        <div id="ctop"><img src="{$MAIN_URL}/img/1x1.gif" width="916" height="6" border="0"></div>
        <div id="cmiddle" >
{$content}
		</div>
        <div id="cbottom"><img src="{$MAIN_URL}/img/1x1.gif" width="916" height="7" border="0"></div>
</div>
    <div id="esf">
        <div id="ctop"><img src="/img/1x1.gif" width="916" height="6" border="0"></div>
        <div id="cmiddle">
        <div id="esrt"><a href="http://www.sitebill.ru" target="_blank">www.sitebill.ru</a></div>
        
		</div>
        <div id="cbottom"><img src="{$MAIN_URL}/img/1x1.gif" width="916" height="7" border="0"></div>
    </div>

</div>
</body>
</html>