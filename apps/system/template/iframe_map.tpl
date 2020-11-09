<html>
<head>
    
<script type="text/javascript">
var estate_folder = '{$estate_folder}';
var map_type='{$map_type}';
//var loc_objects={$iframe_grid_data};
</script>
{literal}
<script type="text/javascript">
var options={scrollZoom: {/literal}{if $scroll_zoom}true{else}false{/if}{literal}, minimap: false, defaultZoom: 4, use_clusters: true};
</script>
{/literal}
<link rel="stylesheet" href="{$estate_folder}/apps/geodata/css/map.css" />
<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
<script src="{$estate_folder}/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
<base target="_parent" />
{if $map_type=='yandex'}
<script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
{elseif $map_type=='leaflet_osm'}
<link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/js/leaflet/leaflet.css" />
<link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/js/leaflet/MarkerCluster.css" />
<link rel="stylesheet" type="text/css" href="{$estate_folder}/apps/system/js/leaflet/MarkerCluster.Default.css" />

<script type="text/javascript" src="{$estate_folder}/apps/system/js/leaflet/leaflet.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/leaflet/leaflet.markercluster.js"></script>   
{else}
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3{if $g_api_key ne ''}&key={$g_api_key}{/if}"></script>
<script type="text/javascript" src="{$estate_folder}/apps/third/google/markerclusterer/markerclusterer.js"></script>
{/if}
<script type="text/javascript" src="{$estate_folder}/js/estate.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>

{literal}
    <script>
    var search_params = {};
    </script>
{/literal}
{if isset($iframe_grid_params)}
<script>
    search_params = {$iframe_grid_params};
</script>
{/if}
<script src="{$estate_folder}/apps/system/js/simplify/simplify.js"></script>
<script src="{$estate_folder}/apps/system/js/activemap.js"></script>
<body style="margin:0;">

<div id="ActiveMapContainer" class="ActiveMapContainer" style="position: relative;"> 

    
    
	<div class="e-map-draw-search ActiveMapControls">
        <div id="ActiveMapControls-Draw" class="b-btn m-green pull-left hidden-print ">
            <i class="fa fa-pencil"></i>
                <span id="DrawModeButtonTextDrawSearch">{_e t="Обвести"}</span>
        </div>
        <div id="ActiveMapControls-Clear" class="b-btn m-red pull-left hidden-print ">
            <i class="fa fa-times"></i>
                <span id="DrawModeButtonTextClear">{_e t="Очистить"}</span>
        </div>
    </div>
    <div class="ActiveMapListBlock">
        <div class="ActiveMapListBlock-wrapper">
            <div class="ActiveMapListBlock-closer">x</div>
            <div class="ActiveMapListBlock-content">
                <div class="ActiveMapListBlock-items-root">
                    <div class="ActiveMapListBlock-items-item ActiveMapListBlock-tpl">
                        <div class="ActiveMapListBlock-item-root-do">
                            <a class="ActiveMapListBlock-item-link" target="_blank" href="">
                                <div class="ActiveMapListBlock-item-image"><img src="{$estate_folder}/img/no_foto.jpg"></div>
                                <div class="ActiveMapListBlock-item-description">
                                    <h3 class="ActiveMapListBlock-item-title"></h3>
                                    <div class="ActiveMapListBlock-item-price"></div>
                                    <div class="ActiveMapListBlock-item-address"></div>
                                    

                                </div>
                            </a>
                            
                        </div>
                    </div>
      
                    
                </div>
            </div>
        </div>
    </div>
    <div id="ActiveMap" class="" data-center-lat="55.753215" data-center-lng="37.622504" data-zoom="14"></div>
    <canvas id="ActiveMapCanvas" class="" style="position: absolute; left: 0; top: 0; display: none;"></canvas>
    
</div>
        </body>
{literal}

<style>
#ActiveMap {
    width: 100%;
    height: 100%;
    z-index: 1;
    /*min-height: 500px;*/
}
#ActiveMapCanvas {
    z-index: 3;
}
.ActiveMapControls {
    position: absolute;
    z-index: 3;
    top: 20px;
    right: 20px;
}
.ActiveMapControls .b-btn {
    border: 1px solid transparent;
    position: relative;
    display: inline-block;
    padding: 8px 22px;
    cursor: pointer;
    text-align: center;
    border-radius: 2px;
    background: white;
    width: 121px;
    border-radius: 20px;
    -webkit-box-shadow: 0 5px 15px -7px rgba(0,0,0,.5);
    box-shadow: 0 5px 15px -7px rgba(0,0,0,.5);
}
.ActiveMapControls .b-btn.active {
    border: 1px solid Gray;
}
.ActiveMapListBlock-tpl {
    display: none;
}
.ActiveMapListBlock {
    position: absolute;
    width: 340px;
    top: 15px;
    left: 15px;
    right: auto;
    font-family: Arial,'Helvetica Neue',Helvetica,sans-serif;
    font-size: 14px;
    line-height: 1;
    pointer-events: none;
    z-index: 3;
    display: none;
}
.ActiveMapListBlock-closer {
    width: 20px;
    height: 20px;
    position: absolute;
    top: 5px;
    right: 5px;
    text-align: center;
    cursor: pointer;
}
.ActiveMapListBlock-wrapper {
    background-color: #fff;
    border-radius: 3px;
    -webkit-box-shadow: 0 2px 10px rgba(0,0,0,.2);
    box-shadow: 0 2px 10px rgba(0,0,0,.2);
    pointer-events: auto;
    min-height: 0;
    max-height: 100%;
    overflow: hidden;
    padding-top: 20px;
}
.ActiveMapListBlock-content {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    min-height: 0;
    height: 100%;
}
.ActiveMapListBlock-items-root {
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 105px;
    font-family: Arial,'Helvetica Neue',Helvetica,sans-serif;
    font-size: 14px;
    line-height: 1;
}
.ActiveMapListBlock-items-item {
    margin: 15px 20px;
}
.ActiveMapListBlock-item-root-do {
    font-family: Arial,'Helvetica Neue',Helvetica,sans-serif;
    font-size: 14px;
    line-height: 1;
    background-color: #fff;
    position: relative;
}
.ActiveMapListBlock-item-link {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    text-decoration: none;
    cursor: pointer;
}
.ActiveMapListBlock-item-image {
    -ms-flex-negative: 0;
    flex-shrink: 0;
    margin-right: 12px;
    background-image: url(https://www.avito.st/s/cc/resources/215d2a4128e5.svg);
    background-size: cover;
    outline: 1px solid rgba(0,0,0,.08);
    outline-offset: -1px;
    width: 100px;
    height: 75px;
}
.ActiveMapListBlock-item-image>img {
    width: 100px;
    height: 75px;
}
.ActiveMapListBlock-item-description {
    -ms-flex-negative: 0;
    flex-shrink: 0;
    width: 188px;
}
.ActiveMapListBlock-item-address, .ActiveMapListBlock-item-date, .ActiveMapListBlock-item-price, .ActiveMapListBlock-item-title {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.ActiveMapListBlock-item-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    color: #0091d9;
}
.ActiveMapListBlock-item-address, .ActiveMapListBlock-item-date, .ActiveMapListBlock-item-price, .ActiveMapListBlock-item-stats, .ActiveMapListBlock-item-status {
    margin-top: 6px;
}
</style>
{/literal}

{if $map_type=='google'}
    <script>
    $(document).ready(function(){
        if (typeof google == 'object') {
            ActiveMap.init('ActiveMapContainer', 'google');
        }
    });
    </script>
{elseif $map_type=='yandex'}
    <script>
    $(document).ready(function(){
        ymaps.ready(['Map', 'Polygon']).then(function() {
            ActiveMap.init('ActiveMapContainer', 'yandex');
        });
    });
    </script>
{elseif $map_type=='leaflet_osm'}
    <script>
    $(document).ready(function(){
        ActiveMap.init('ActiveMapContainer', 'leaflet_osm'); 
    });
    </script>
{/if}

{if 1==0}
    <script type="text/javascript" src="{$estate_folder}/apps/system/js/realtymap.js"></script>
    {if isset($custom_center)}
    <script>
    options.custom_center = [{$custom_center}];
    options.adopt_bounds = false;
    </script>
    {/if}
    {if isset($defaultZoom)}
    <script>
    options.defaultZoom = Number({$defaultZoom});
    </script>
    {/if}
    {if isset($smarty.get.clusterGridSize)}
    <script>
    options.gridSize = Number({$smarty.get.clusterGridSize});
    </script>
    {/if}
    {literal}
    <script type="text/javascript">
    $(document).ready(function(){
        var RM=new RealtyMap('2.1');
        RM.initJSON('YMapsID', loc_objects, map_type, options);
    });
    </script>
    {/literal}
    </head>
    <body style="margin:0;">
    <div class="bigmap">
    <div id="YMapsID" style="width: {$map_w}; height: {$map_h}"></div>
    </div>
    </body>
{/if}
</html>