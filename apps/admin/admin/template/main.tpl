<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
{if $smarty.const.SITE_ENCODING != '' }
    <meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.SITE_ENCODING}" />
{else}
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
{/if}
     <title>CMS Sitebill</title>
    <!-- <link rel="stylesheet" href="{$MAIN_URL}/css/admin.css"> --> 
   <link rel="stylesheet" href="{$MAIN_URL}/css/system.css">
    <link rel="stylesheet" href="{$MAIN_URL}/css/menu.css">
    <!-- <link rel="stylesheet" href="{$MAIN_URL}/css/form_decorator.css"> -->
    <link rel="stylesheet" href="{$MAIN_URL}/apps/admin/admin/template/css/admin.css">
    <link rel="stylesheet" href="{$MAIN_URL}/apps/system/js/bootstrap/css/bootstrap.min.css" media="screen">
    <!-- <script src="{$MAIN_URL}/js/jquery.js"></script> -->
    <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery/jquery.js"></script>
    <script src="{$MAIN_URL}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
    
	<script src="{$MAIN_URL}/apps/system/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
	<link rel="stylesheet" href="{$MAIN_URL}/apps/system/js/bootstrap-editable/css/bootstrap-editable.css" />
	<link href="http://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
	<script src="http://www.sitebill.ru/js/nanoapi.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
	<script src="{$MAIN_URL}/js/interface.js"></script>
	<script src="{$MAIN_URL}/js/estate.js"></script>
	<script type="text/javascript" src="{$MAIN_URL}/js/jquery.tablesorter.min.js"></script>
    <link href="{$MAIN_URL}/css/jquery-ui-1.8.custom.css" rel="stylesheet" type="text/css"/>
    <!-- <script type="text/javascript" src="{$MAIN_URL}/js/jquery.ui.core.js"></script>     -->
    <!-- <script type="text/javascript" src="{$MAIN_URL}/js/jquery-ui-1.8.19.custom.min.js"></script>  -->
    <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jqueryui/jquery-ui.js"></script>
    <!-- <script type="text/javascript" src="{$MAIN_URL}/js/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/jquery.ui.button.js"></script> -->
    <script type="text/javascript" src="{$MAIN_URL}/apps/system/js/mycombobox.js"></script>
    <link rel="stylesheet" href="{$MAIN_URL}/apps/system/css/jquery-ui.custom.css" />
    <link rel="stylesheet" href="{$MAIN_URL}/apps/system/css/mycombobox.css" />
          
    <!-- <script type="text/javascript" src="{$MAIN_URL}/js/jquery.ui.datepicker.js"></script> -->
    <script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU"></script>
	<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>
    
	<script>
		var estate_folder='{$estate_folder}';
		</script>
</head>
<body onload="runDialog('homescript_etown_ru'); {$onload}">
<div class="container-fluid">
<div class="row-fluid">
    <div class="span12">
        <div class="top_text"><a href="?action=logout">{$L_LOGOUT_BUTTON}</a></div> 
        <div class="top_image"><a href="?action=logout"><img src="{$MAIN_URL}/img/logout.png" border="0"></a></div>
        <div class="top_help">
            <div class="fl"><a href="{$MAIN_URL}/admin/?_lang=ru"><img src="{$MAIN_URL}/apps/admin/admin/template/img/flag_ru.gif" alt="Русский" title="Русский"/></a>
            <a href="{$MAIN_URL}/admin/?_lang=en"><img src="{$MAIN_URL}/apps/admin/admin/template/img/flag_en.png" alt="Английский" title="Английский"/></a></div>        
            <div class="fl"><a href="http://www.sitebill.ru/doc.html" target="_blank"><img src="{$MAIN_URL}/apps/admin/admin/template/img/help.png" border="0"></a></div>
        </div>
    
    </div>
</div>

<div class="row-fluid">
    <div class="span12">

    <div class="navbar">    
    <div class="navbar-inner">
        <a class="brand" href="{$estate_folder}/admin/">CMS Sitebill</a>
        
    	<ul class="nav nav-pills">
    	{assign var=a value=1}
    	{foreach from=$admin_menua item=ama}
    	<li class="dropdown">
    	<a id="drop{$a}" {if isset($ama.childs) && $ama.childs|count>0}data-toggle="dropdown"  class="dropdown-toggle" href="{$ama.href}" data-target="#"{else}href="{$ama.href}"{/if}>{$ama.title}</a>
    		{if isset($ama.childs) && $ama.childs|count>0}
    		<ul class="dropdown-menu" role="menu" aria-labelledby="drop{$a}">
    		{foreach from=$ama.childs item=cama}
    		<li><a href="{$cama.href}">{$cama.title}</a></li>
    		{/foreach}
    		</ul>
    		{/if}
    		{assign var=a value=$a+1}
    	</li>
    	{/foreach}
		</ul>

		
	</div>
	</div>	  
	</div>
</div>   
<div class="row-fluid">
<div class="span2">
<ul class="nav nav-tabs nav-stacked">
  <li><a href="{$estate_folder}/admin/"><i class="icon-home"></i> Главная</a></li>
  <li><a href="{$estate_folder}/admin/?action=config"><i class="icon-cog"></i> Настройки</a></li>
  <li><a href="{$estate_folder}/admin/?action=sitebill"><i class="icon-refresh"></i> Обновления</a></li>
  <li><a href="{$estate_folder}/admin/?action=user"><i class="icon-user"></i> Пользователи</a></li>
  <li><a href="{$estate_folder}/admin/?action=structure"><i class="icon-th-list"></i> Структура</a></li>
</ul>
{$data_category_tree}
<ul class="nav nav-tabs nav-stacked">
        {section name=le  max=10  loop=$smarty.session.recently_apps}
            <li>{$smarty.session.recently_apps[le]}</li>
        {/section}
</ul>


</div>
<div class="span10">{$content}</div>
</div>

<div class="row-fluid">
<div class="span12">
<div class="navbar">
<div class="navbar-inner">
<a href="http://www.sitebill.ru" target="_blank" class="brand">www.sitebill.ru</a>
</div>
</div>
</div>
</div>

</div>
</body>
</html>