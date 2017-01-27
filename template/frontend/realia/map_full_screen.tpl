{include file="header.tpl"}
{literal}
<script>
$(document).ready(function(){
    $('#_lang').change(function(){
        $(this).parents('form').eq(0).submit();
        //window.location.replace(estate_folder+'/?_lang='+$(this).val());
    });
});

</script>
{/literal}
<body>

<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>
<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>
{literal}
<style>
.realty_on_map {
    font-size: 10px;
    height: 100px;
    overflow: hidden;
}
.realty_on_map img {
    float: left;
    margin-right: 5px;
}
</style>
{/literal}
<script type="text/javascript">
var d = '{$data}'; 
var objects=JSON.parse(d);

//var x_d='{$x_data}';
//var x_data=JSON.parse(x_d);
//console.log(x_data);
</script>
    {literal}
    <script type="text/javascript">
    function initialize() {
        var latlng = new google.maps.LatLng(49.886672,23.937149);
        var myOptions = {
          zoom: 16,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("YMapsID"), myOptions);
        
        var infowindow = new google.maps.InfoWindow({  
          content: 'Hello, world!' 
        });
        
        var bounds=new google.maps.LatLngBounds();
        //var markers=[];
        
        var infowindow = new google.maps.InfoWindow({  
          content: '' 
        });
        
        
        
        for(var o=0; o<objects.length; o++){
            var html=objects[o].html;
            
            
            var latlng=new google.maps.LatLng(Number(objects[o].geo_lat),Number(objects[o].geo_lng));
            bounds.extend(latlng);
            var marker = new google.maps.Marker({
                icon: {url: '/template/frontend/agency/img/home_small.png'},
                position: latlng, 
                map: map,
                title: objects[o].title
            });
            //markers.push(marker);
            makeInfoWin(marker, infowindow, html);
            
        }
        
        var boundsCenter=bounds.getCenter();
        map.setCenter(boundsCenter);
        map.fitBounds(bounds);
        
        
        
        
    };
    
    function makeInfoWin(marker, infowindow, data) {
        //var infowindow = new google.maps.InfoWindow({ content: data });
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(data);
            infowindow.open(marker.getMap(),marker);
        });  
    }
      
    jQuery(document).ready(function(){
        initialize();
    });
    {/literal}
    </script>




<div class="bigmap">
<div id="YMapsID" style="border: 1px solid #e6e6e6; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; width: 100%; height: 100%;"></div>
</div>
</body>
</html>