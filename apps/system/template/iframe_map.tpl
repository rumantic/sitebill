<html>
<head>
<script type="text/javascript">
var estate_folder = '{$estate_folder}';
var map_type='{$map_type}';
var loc_objects={$iframe_grid_data};
</script>
<link rel="stylesheet" href="{$estate_folder}/apps/geodata/css/map.css" />
<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
<script src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
<base target="_parent" />
{if $map_type=='yandex'}
<script type="text/javascript" src="http://api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
{else}
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?v=3"></script>
<script type="text/javascript" src="{$estate_folder}/apps/third/google/markerclusterer/markerclusterer.js"></script>
{/if}
<script type="text/javascript" src="{$estate_folder}/js/estate.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/realtymap.js"></script>
{literal}
<script type="text/javascript">
$(document).ready(function(){
	var RM=new RealtyMap('2.1');
	RM.initJSON('YMapsID', loc_objects, map_type, {scrollZoom: false, minimap: false, defaultZoom: 4, use_clusters: true});
});
</script>
{/literal}
</head>
<body style="margin:0;">
<div class="bigmap">
<div id="YMapsID" style="width: {$map_w}; height: {$map_h}"></div>
</div>
</body>
</html>