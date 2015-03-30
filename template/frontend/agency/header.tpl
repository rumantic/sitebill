<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{if $meta_title != ''}{$meta_title}{else}{$title}{/if}</title>
<script type="text/javascript">
var estate_folder = '{$estate_folder}'; 
</script>
<meta name="description" content="{$meta_description}" /> 
<meta name="keywords" content="{$meta_keywords}" /> 
<link rel="stylesheet" href="{$estate_folder}/apps/system/js/bootstrap/css/bootstrap.min.css" media="screen">

<!-- <link rel="stylesheet" href="{$estate_folder}/apps/system/js/bootstrap/css/slider.css" media="screen"> -->
<link rel=stylesheet type="text/css" href="{$estate_folder}/template/frontend/agency/css/style.css">
<link rel=stylesheet type="text/css" href="{$estate_folder}/template/frontend/agency/css/bootstrap.corrections.css">
<link rel=stylesheet type="text/css" href="{$estate_folder}/template/frontend/agency/css/purecssmenu.css">
<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>

<script type="text/javascript" src="{$estate_folder}/apps/system/js/jqueryui/jquery-ui.js"></script>
<!-- <script type="text/javascript" src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap-slider.js"></script> -->
<script type="text/javascript" src="{$estate_folder}/js/jquery.lightbox-0.5.js"></script>
<link rel="stylesheet" type="text/css" href="{$estate_folder}/css/jquery.lightbox-0.5.css" media="screen">
<link rel="stylesheet" type="text/css" href="{$estate_folder}/css/jqueryslidemenu.css">
<link rel="stylesheet" type="text/css" href="{$estate_folder}/css/system.css">

<script type="text/javascript" src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
<script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU"></script>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>

{literal}
<!--[if lte IE 7]>
<style type="text/css">
html .jqueryslidemenu{height: 1%;} /*Holly Hack for IE7 and below*/
</style>
<![endif]-->
{/literal}
<script type="text/javascript">
var arrowimages={literal}{{/literal}down:['downarrowclass', '{$estate_folder}/img/down.gif', 23], right:['rightarrowclass', '{$estate_folder}/img/right.gif']{literal}}{/literal}
</script>
<script type="text/javascript" src="{$estate_folder}/js/jqueryslidemenu.js"></script>
<script type="text/javascript" src="{$estate_folder}/js/estate.js"></script> 
<link rel="stylesheet" href="{$estate_folder}/template/frontend/agency/jquery-ui/themes/base/jquery.ui.all.css">
<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
<script type="text/javascript">
    $(function() {
        $("#tabs-services").tabs();
    });
</script>

<script type="text/javascript" src="{$estate_folder}/template/frontend/{$current_theme_name}/js/interface.js"></script>
<!-- <script type="text/javascript" src="{$estate_folder}/apps/partsimporter/js/partsimporter.js"></script> -->

</head>

