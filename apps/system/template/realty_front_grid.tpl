{$category_tree}
system
<table class="table table-striped table-hover grid">
<thead>
<tr>
{foreach from=$grid_header item=head_item}

<th{if $current_sort==$head_item.name} class="actsort"{/if}>{$head_item.title}{if $head_item.sortable==1} <a href="{$url}&order={$head_item.name}&asc=asc">&darr;</a> <a href="{$url}&order={$head_item.name}&asc=desc">&uarr;</a>{/if}</th>

{/foreach}

</tr>
</thead>
<tbody>
{foreach from=$grid_items item=grid_item}
	<tr>
	{foreach from=$grid_header item=head_item}
	
	<td>
	{if $head_item.linked==1}<a href="{$grid_item.href}">{/if}
	{if is_array($grid_item[$head_item.name])}
		{foreach from=$grid_item[$head_item.name] item=gi}
		<img src="{$estate_folder}{$gi.img_preview}" width="100" />
		{/foreach}
		
	{else}
		{$grid_item[$head_item.name]}
	{/if}
	
	{if $head_item.linked==1}</a>{/if}
	
	</td>
	{/foreach}
	
	</tr>
{/foreach}
</tbody>
</table>
{$pager}

{if $geodata_show_grid_map==1}
	<script type="text/javascript">
	var realty_geo_data = {$grid_geodata}; 
	</script>
	
	{if $map_type eq 'google'}
	
		{literal}
		<script type="text/javascript">
		var markers={};
		function initialize_grid_map() {
			
			if(realty_geo_data.length==0){
				$('#grid_realty_map').hide();
				return;
			}
			var latlng = new google.maps.LatLng(49.886672,23.937149);
			var myOptions = {
			  zoom: 16,
			  center: latlng,
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			var map = new google.maps.Map(document.getElementById("grid_realty_map"), myOptions);
			
			var infowindow = new google.maps.InfoWindow({  
			  content: 'Hello, world!' 
			});
			
			var bounds=new google.maps.LatLngBounds();
			var infowindow = new google.maps.InfoWindow({  
			  content: '' 
			});
			
			
			for(var o=0; o<realty_geo_data.length; o++){
				var latlng=new google.maps.LatLng(Number(realty_geo_data[o].lat),Number(realty_geo_data[o].lng));
				bounds.extend(latlng);
				var marker = new google.maps.Marker({
					icon: {url: '/template/frontend/agency/img/home_small.png'},
					position: latlng, 
					map: map,
					title: realty_geo_data[o].id
				});
				makeInfoWinForGridMap(marker, infowindow/*, realty_geo_data[o].id*/);
				markers[realty_geo_data[o].id]=marker;
				//markers.push(marker);
			}
			
			var boundsCenter=bounds.getCenter();
			map.setCenter(boundsCenter);
			map.fitBounds(bounds);
			
			
		};
		
		function makeInfoWinForGridMap(marker, infowindow, data) {
			google.maps.event.addListener(marker, 'click', function() {
				var m=marker.title;
				destination = $('a[name=row'+m+']').offset().top - 50;
				if($.browser.safari){
					$('body').animate( { scrollTop: destination }, 1100 );
				}else{
					$('body').animate( { scrollTop: destination }, 1100 );
				}
				return false;
				infowindow.setContent(data);
				infowindow.open(marker.getMap(),marker);
			});  
		}
		jQuery(document).ready(function(){
			initialize_grid_map();
		});
		</script>
		{/literal}
	
	{else}
		{literal}
		<script type="text/javascript">
		ymaps.ready(init);
	    var map;
	
	    function init(){  
	    	if(realty_geo_data.length<1){
	    		$('#grid_realty_map').hide();
	    		return;
	    	}
	    	map = new ymaps.Map('grid_realty_map',{
				zoom: 16,
				center: [23.937149,49.886672],
				behaviors: ["scrollZoom", "drag", "dblClickZoom"],
				type : 'yandex#publicMap'
				});
			map.controls.add(new ymaps.control.TypeSelector(['yandex#map', 'yandex#publicMap', 'yandex#satellite', 'yandex#hybrid']));
			map.controls.add('scaleLine');
			map.controls.add(new ymaps.control.MiniMap(
			    { type: 'yandex#satellite' },
			    { size: [90, 90] }
			));
			map.controls.add('zoomControl', { top: 75, left: 5 });
			
			var min_lat=0;
			var min_lng=0;
			var max_lat=0;
			var max_lng=0;
			for(var o=0; o<realty_geo_data.length; o++){
				if(min_lat==0){
					min_lat=Number(realty_geo_data[o].lat);
				}
				if(max_lat==0){
					max_lat=Number(realty_geo_data[o].lat);
				}
				if(min_lng==0){
					min_lng=Number(realty_geo_data[o].lng);
				}
				if(max_lng==0){
					max_lng=Number(realty_geo_data[o].lng);
				}
				
				if(Number(realty_geo_data[o].lat)<min_lat){
					min_lat=Number(realty_geo_data[o].lat);
				}
				if(Number(realty_geo_data[o].lat)>max_lat){
					max_lat=Number(realty_geo_data[o].lat);
				}
				
				if(Number(realty_geo_data[o].lng)<min_lng){
					min_lng=Number(realty_geo_data[o].lng);
				}
				if(Number(realty_geo_data[o].lng)>max_lng){
					max_lng=Number(realty_geo_data[o].lng);
				}
				//console.log(min_lat,max_lat,min_lng,max_lng);
				
				var latlng=new Array(Number(realty_geo_data[o].lat),Number(realty_geo_data[o].lng));
				var myPlacemark = new ymaps.Placemark(
						latlng,
						{
							iconContent: realty_geo_data[o].id
						},
						{
							draggable: false,
		    	            hideIconOnBalloonOpen: false,
		    	            preset: "twirl#yellowStretchyIcon"
		    	        }
		    	    );
				
				myPlacemark.events.add("click", function(e) {
		      		var object = e.get('target');
		      		var m=object.properties.get('iconContent');
		      		destination = $('a[name=row'+m+']').offset().top - 50;
					$('body').animate( { scrollTop: destination }, 1100 );
		      	});
		      	map.geoObjects.add(myPlacemark);
		    }
			
			if(min_lat==max_lat && min_lng==max_lng){
				map.setCenter(new Array(min_lat,min_lng));
			}else{
				map.setBounds([[min_lat,min_lng],[max_lat,max_lng]]);
			}
		}
		</script>
		{/literal}
	{/if}
	
	
	

{/if}