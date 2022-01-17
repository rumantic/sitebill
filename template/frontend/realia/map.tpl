<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/realtymap.js"></script>

<script type="text/javascript">
    var map_type = '{$map_type}';
    var loc_objects ={$geoobjects_collection_clustered};
    /*var loc_objects={$_geo_data};
     
     for(var i in loc_objects){
     loc_objects[i].lat=loc_objects[i].geo_lat;
     loc_objects[i].lng=loc_objects[i].geo_lng;
     }*/
    {literal}

    $(document).ready(function () {
        var RM = new RealtyMap();
        //RM.init('YMapsID', loc_objects, map_type);
        //RM.initX('YMapsID', 'mapobjectslisting', map_type);
        RM.initJSON('YMapsID', loc_objects, map_type, {scrollZoom: false, minimap: false, defaultZoom: 4});
    });
    {/literal}
</script>
<div class="bigmap">
    <div id="YMapsID" style="border: 1px solid #e6e6e6; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; width: 100%; height: 800px;"></div>
</div>
{*$mapobjectslisting*}