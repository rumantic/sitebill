<script type="text/javascript" src="{$estate_folder}/apps/system/js/realtymap.js"></script>
<link rel="stylesheet" href="{$estate_folder}/template/frontend/{$current_theme_name}/css/map.css" type="text/css">


{literal}
    <script>

        $(document).ready(function () {
            if (typeof google == 'object') {
                var latlng = new google.maps.LatLng(42.561426, 27.63471);
                var myOptions = {
                    zoom: 14,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById("activemap"), myOptions);

                var drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.MARKER,
                    drawingControl: true,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: [
                            google.maps.drawing.OverlayType.POLYGON/*,
                             google.maps.drawing.OverlayType.RECTANGLE*/
                        ]
                    },
                    markerOptions: {
                        icon: 'images/beachflag.png'
                    },
                    circleOptions: {
                        /* fillColor: '#ffff00',*/
                        fillOpacity: 1,
                        strokeWeight: 5,
                        clickable: false,
                        editable: true,
                        zIndex: 1
                    },
                    polygonOptions: {
                        fillOpacity: 0.1,
                    }
                });

                var polygone = null;
                var markers_collection = [];

                google.maps.event.addListener(drawingManager, 'polygoncomplete', function (polygon) {
                    if (null != polygone) {
                        polygone.setMap(null);
                    }

                    if (markers_collection.length > 0) {
                        for (var j in markers_collection) {
                            markers_collection[j].setMap(null);
                        }
                    }

                    var bounds = new google.maps.LatLngBounds();
                    polygone = polygon;
                    var val = (polygon.getPath().getArray());

                    var NW = {};
                    var SE = {};
                    var lat = 0;
                    for (var i in val) {
                        bounds.extend(val[i]);
                    }
                    map.fitBounds(bounds);

                    //var diapasones=[];
                    /*
                     if(bounds.getSouthWest().lng()>0 && bounds.getNorthEast().lng()<0){
                     console.log('2 diapasones'); 
                     console.log('FROM '+bounds.getSouthWest().lat()+' TO '+bounds.getNorthEast().lat()); 
                     console.log('FROM '+bounds.getSouthWest().lng()+' TO 180'); 
                     console.log('AND ');
                     console.log('FROM '+bounds.getSouthWest().lat()+' TO '+bounds.getNorthEast().lat()); 
                     console.log('FROM -180 TO '+bounds.getNorthEast().lng()); 
                     }else{
                     console.log('1 diapasone'); 
                     console.log('FROM '+bounds.getSouthWest().lat()+' TO '+bounds.getNorthEast().lat()); 
                     console.log('FROM '+bounds.getSouthWest().lng()+' TO '+bounds.getNorthEast().lng()); 
                     }*/



                    var lat1 = bounds.getNorthEast().lat();
                    var lat2 = bounds.getSouthWest().lat();
                    var lng1 = bounds.getNorthEast().lng();
                    var lng2 = bounds.getSouthWest().lng();

                    //  var 
                    var geocoords = getNormalCoords(lat2) + ',' + getNormalCoords(lng2) + ':' + getNormalCoords(lat1) + ',' + getNormalCoords(lng1);
                    $('form.partial [name=geocoords]').val(geocoords);
                    var form = $('form.partial');
                    var params = SitebillCore.serializeFormJSON(form);


                    $.ajax({
                        url: estate_folder + '/js/ajax.php',
                        data: {action: 'collect_data', params: params},
                        type: 'post',
                        dataType: 'json',
                        success: function (json) {
                            if (json) {


                                for (var i in json) {
                                    var latlng = new google.maps.LatLng(json[i].lat, json[i].lng);
                                    var marker = new google.maps.Marker({
                                        position: latlng,
                                        map: map,
                                        draggable: true,
                                    });
                                    markers_collection.push(marker);
                                }
                                RM.reinit(json);
                            } else {
                                RM.reinit([]);
                            }
                        }
                    });
                    /*if(lat1<lat2){
                     var lat_min=lat1;
                     var lat_max=lat2;
                     }else{
                     var lat_min=lat2;
                     var lat_max=lat1;
                     }
                     if(lng1<lng2){
                     var lng_min=lng1;
                     var lng_max=lng2;
                     }else{
                     var lng_min=lng2;
                     var lng_max=lng1;
                     }*/
                    //console.log(getNormalCoords(lat_min)+':'+getNormalCoords(lat_max));
                    //console.log(getNormalCoords(lng_min)+':'+getNormalCoords(lng_max));
                });

                drawingManager.setMap(map);
            }
        });

        function getNormalCoords(k) {
            var z = new String(k);
            var z_parts = z.split('.');
            if (z_parts[1] !== undefined && z_parts[1].length > 6) {
                z = z_parts[0] + '.' + z_parts[1].substring(0, 6);
            }
            return z;
        }

    </script>
{/literal}







<div class="container">

    <div id="main" class="searchonmap">
        <div class="row-fluid">
            <div class="span12">
                <div id="activemap" style="width: 100%; height: 500px;"></div>
            </div>

        </div>





        <div class="row-fluid">
            <div class="span12">

            </div>

        </div>






        <div id="main" class="searchonmap">
            <div class="row-fluid">
                <div class="span12">
                    <div class="bigmap" style="border: 1px solid #e6e6e6; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; width: 100%; height: 800px;">
                        <div id="searchonmapmap" style="width: 100%; height: 100%;"></div>
                    </div>
                    <form class="partial fluidform">
                        <input type="text" name="geocoords" value="" />
                        {$country_list}
                        {$region_list}
                        {$city_list}

                        <table border="0" cellpadding="2" cellspacing="0">
                            <tr>
                                <td colspan="4">{$structure_box}</td>
                            </tr>
                            <tr>
                                <td>{$L_FLOOR} {$L_FROM}</td>
                                <td><div class="select_box_td"><input type="text" name="floor_min" value="{if (isset($smarty.request.floor_min) && $smarty.request.floor_min!=0)}{$smarty.request.floor_min}{/if}" /></div></td>
                                <td>{$L_TO}</td>
                                <td><div class="select_box_td"><input type="text" name="floor_max" value="{if (isset($smarty.request.floor_max) && $smarty.request.floor_max!=0)}{$smarty.request.floor_max}{/if}" /></div></td>
                            </tr>
                            <tr>
                                <td>{$L_FLOORS} {$L_FROM}</td>
                                <td><div class="select_box_td"><input type="text" name="floor_count_min" value="{if (isset($smarty.request.floor_count_min) && $smarty.request.floor_count_min!=0)}{$smarty.request.floor_count_min}{/if}" /></div></td>
                                <td>{$L_TO}</td>
                                <td><div class="select_box_td"><input type="text" name="floor_count_max" value="{if (isset($smarty.request.floor_count_max) && $smarty.request.floor_count_max!=0)}{$smarty.request.floor_count_max}{/if}" /></div></td>
                            </tr>
                            <tr>
                                <td>{$L_SQUARE_SHORT} {$L_FROM}</td>
                                <td><div class="select_box_td"><input type="text" name="square_min" value="{if (isset($smarty.request.square_min) && $smarty.request.square_min!=0)}{$smarty.request.square_min}{/if}" /></div></td>
                                <td>{$L_TO}</td>
                                <td><div class="select_box_td"><input type="text" name="square_max" value="{if (isset($smarty.request.square_max) && $smarty.request.square_max!=0)}{$smarty.request.square_max}{/if}" /></div></td>						
                            </tr>
                            <tr>
                                <td>{$L_PRICE} {$L_FROM}</td>
                                <td><div class="select_box_td"><input type="text" class="price_from price_field" name="price_min" value="{if isset($price_min)}{$price_min|number_format:0:'':' '}{else}{/if}"/></div></td>
                                <td>{$L_TO}</td>
                                <td><div class="select_box_td"><input type="text" class="price_for price_field" name="price" value="{if isset($price) && $price!=0}{$price|number_format:0:'':' '}{else}{/if}"/></div></td>
                            </tr>

                            <tr>
                                <td class="slider_block" colspan="4"><div class="slider"></div></td>
                            </tr>

                        </table>

                    </form>
                </div>

            </div>


        </div>
    </div>

    {literal}
        <style>
            #searchonmapmap img {
                max-width: none;
            }
            .partial.fluidform {

                /* top: 10px; */
                /* left: 10px; */
                width: 100%;
                background-color: #eee;
                /* padding: 10px; */
                border: 1px solid gray;
                -webkit-box-shadow: 7px 7px 5px 0px rgba(50, 50, 50, 0.75);
                -moz-box-shadow: 7px 7px 5px 0px rgba(50, 50, 50, 0.75);
                box-shadow: 7px 7px 5px 0px rgba(50, 50, 50, 0.75);
            }
            /*.searchonmap {
                    position: relative;
            }
            .searchonmap #searchonmapform {
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 500px;
            background-color: wheat;
            }*/
        </style>
    {/literal}
    <script>
    var map_type = '{$map_type}';
    </script>
    {literal}
        <script>
            var RM = null;
            $(document).ready(function () {

                RM = new RealtyMap();
                RM.initJSON('searchonmapmap', [], map_type, {scrollZoom: true, minimap: false, defaultZoom: 14, ajax: true});
                LiveSearch.runRefresh();

                $('form.partial input').change(function () {
                    LiveSearch.runRefresh();
                });

                $(document).on('change', 'form.partial select', function () {
                    LiveSearch.runRefresh();
                });

            });



            var LiveSearch = {
                collectRequestParams: function () {
                    var form = $('form.partial');
                    return SitebillCore.serializeFormJSON(form);
                },

                stripSlashes: function (str) {
                    str = str.replace(/\\'/g, '\'');
                    str = str.replace(/\\"/g, '"');
                    str = str.replace(/\\0/g, '\0');
                    str = str.replace(/\\\\/g, '\\');
                    return str;
                },
                runRefresh: function (mode) {

                    var params = this.collectRequestParams();



                    var mode = mode || '';

                    $.ajax({
                        url: estate_folder + '/js/ajax.php',
                        data: {action: 'collect_data', params: params},
                        type: 'post',
                        dataType: 'json',
                        success: function (json) {
                            if (json) {
                                RM.reinit(json);
                            } else {
                                RM.reinit([]);
                            }
                        }
                    });
                }

            };

        </script>
    {/literal}