<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>

<script type="text/javascript">
//var d = '{$_geo_data}'; 
//var objects=JSON.parse(d);
{if $_geo_data!=''}
var objects={$_geo_data};
{else}
var objects=[];
{/if}
</script>
	{literal}
	<script type="text/javascript">
	function initialize() {
		var latlng = new google.maps.LatLng({/literal}{$apps_geodata_new_map_center}{literal});
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
		
		//objects=JSON.parse(objects);
		if(objects.length>0){
			for(var o=0; o<objects.length; o++){
				var html=objects[o].html;
				html=html.replace(/\\\"/g, '"');
				
				var latlng=new google.maps.LatLng(Number(objects[o].geo_lat),Number(objects[o].geo_lng));
				bounds.extend(latlng);
				var marker = new google.maps.Marker({
					icon: {url: estate_folder + '/template/frontend/agency/img/home_small.png'},
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
		}
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
<div id="YMapsID" style="border: 1px solid #e6e6e6; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; width: 100%; height: 200px;"></div>
</div>