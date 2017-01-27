{$ajax_functions}
<script type="text/javascript">
{literal}
$(function(){
  var topPos = $('.complex_right').offset().top;
  
   $(window).scroll(function() {
		var scrollTop = $(window).scrollTop();

		var sidebar = $(".complex_right");
		var offset = sidebar.offset();
		var left = offset.left;
		var top = offset.top;
		var height = sidebar.height();
		var width = sidebar.width();

		var maincontent = $(".complex_main");
		var main_offset = maincontent.offset();
		var main_height = maincontent.height()+main_offset.top;

		var left_width = $('.complex_left').width();
		var left_height = $('.complex_left').height();

	if (height < left_height) {

		if ( (scrollTop >= main_offset.top) && (scrollTop <= main_height-height) ){
			$('.complex_right').removeAttr('style');
			$('.complex_right').css({
				'left':left+'px',
				'position':'fixed',
				'top':"0px",
				'width':width+"px",
				'margin-left': 0
			});
		} else if ( (scrollTop > main_height-height) ) {

			$('.complex_right').removeAttr('style');

			$('.complex_right').css({
				position:'absolute',
				left: left_width+'px',
				bottom: '15px'
			});

		} else { $('.complex_right').removeAttr('style'); }
	}
   });
 });
{/literal}
	var objects=[];
</script>
{literal}
<style>
.fixed {
	position: fixed;
	top: 0;
	bottom: 0;
}
.complex_single {
margin-bottom: 20px;
background-color: white;
border-bottom: 1px solid #FFF;
padding: 10px 0;
}
.complex_info {
padding: 10px;
clear: both;
}
.complex_header {
position: relative;
padding-top: 5px;

}
.complex_header a {
font-family: 'PT Sans Narrow', sans-serif;
font-size: 22px;
color: #FFFFFF;
}
.complex_footer {
padding: 10px;
}
.complex_image {
width: 140px;
margin-left: 10px;
float: left;
}
.complex_title {
display: block;
padding: 20px 0;
padding-left: 200px;
margin-top: 10px;
background-color: #336699;
color: white;
}
.complex_title a:hover {
text-decoration: none;
}
.property-filter {
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
display: inline-block;
zoom: 1;
background-color: #0581b5;
display: block;
padding: 20px;
width: 100%;
margin-bottom: 20px;
color: white;
margin-top: 10px;
}
.property-filter td {
padding: 10px 10px 0 10px;
}
.cnameonmaphint {
  padding: 10px;
}
.cnameonmaphint a {
  color: #70af1a;
  font-family: 'PT Sans Narrow', sans-serif;
  font-size: 22px;
}
.cnameonmaphint a:hover {
  text-decoration: none;
}
</style>
{/literal}

<div class="actn">
<p>На нашем сайте вы сможете подобрать жилой комплекс, в котором вырастут ваши дети и сбудутся самые заветные мечты. Фотографии, информация – все что поможет принять решение есть у нас. Просто пролистайте несколько страниц.</p>
</div>



<div class="row-fluid complex_main" style="position: relative;">
	<div class="span6 complex_left">
		{foreach from=$grid_data.grid_array item=complex}
		
			
			
			{if $complex.geo_lat!='' && $complex.geo_lng!=''}
			{literal}
			<script type="text/javascript">
			var obj={};
			obj.geo_lat={/literal}{$complex.geo_lat}{literal};
			obj.geo_lng={/literal}{$complex.geo_lng}{literal};
			obj.html='{/literal}<div class="cnameonmaphint"><a href="{$complex.href}">{$complex.name}</a></div>{literal}';
			obj.objectID={/literal}{$complex.complex_id}{literal};
			objects.push(obj);
			{/literal}
			</script>
			{/if}
			<div class="complex_single" id="objectID_{$complex.complex_id}">
				<div class="complex_header">
					<div class="complex_image">
					
						<a href="{$complex.href}">
							{if isset($complex.image[0])}
								<img class="img-polaroid" src="{$estate_folder}/img/data/{$complex.image[0].preview}" alt="{$complex.name}" title="{$complex.name}">
							{else}
								<img class="img-polaroid" src="{$estate_folder}/template/frontend/{$current_theme_name}/img/no_foto_complex.png" alt="{$complex.name}" title="{$complex.name}">
							{/if}
						</a>
					</div>
					<div class="complex_title">
						<a href="{$complex.href}">{$complex.name}</a>
						
						{assign var=x value=array()}
						{if $complex.city_id.value_string!=''}
						{append var=x value=$complex.city_id.value_string}
						{/if}
						{if $complex.district_id.value_string!=''}
						{append var=x value=$complex.district_id.value_string|cat:' район'}
						{/if}
						{if $complex.street_id.value_string!=''}
						{append var=x value=$complex.street_id.value_string|cat:' ул.'}
						{/if}
						{if $complex.metro_id.value_string!=''}
						{append var=x value=$complex.metro_id.value_string}
						{/if}
						{if $x|count>0}<br />{$x|implode:', '}{/if}
					</div>
					

				</div>
				<div class="complex_info">
					
					<table class="table table-condensed">
						<tr>
							<td class="parameter">Законность</td>
							<td class="value">{$complex.lexx}</td>
						</tr>
						<tr>
							<td class="parameter">Срок сдачи</td>
							<td class="value">{$complex.deadline}</td>
						</tr>
						<tr>
							<td class="parameter">Тип дома</td>
							<td class="value">{$complex.tip_construct}</td>
						</tr>
						<tr>
							<td class="parameter">Отделка</td>
							<td class="value">{$complex.decoration}</td>
						</tr>
						<tr>
							<td class="parameter">Цена за м<sup>2</sup> от</td>
							<td class="value">{if $complex.price_pm_from!=0}{number_format($complex.price_pm_from, 0, ',', ' ')}{/if}</td>
						</tr>
					</table>
				</div>
				{if $complex._data|count>0}
				<div class="complex_warp">
					<div class="wrap_data">
						<table class="table table-condensed">
							<thead>
								<tr>
									<th>Квартира</th>
									<th>Этаж</th>
									<th>Площадь, м<sup>2</sup></th>
									<th>Цена,  руб.</th>
								</tr>
							</thead>
							<tbody>
							
							{assign var=total_count value=0}
							{foreach from=$complex._data item=apt key=apt_key}
								{assign var=total_count value=$total_count+$apt._cnt}
								{if $apt_key>0}
								<tr>
									<td class="align-left name">
										<a href="{$estate_folder}/{$apps_complex_alias}/{$complex.url}#{$apt_key}rc">{$apt_key}-комнатная ({$apt._cnt})</a>
									</td>
									<td class="square">
										{if $apt._min_floor==$apt._max_floor}
											{$apt._min_floor}
										{else}
											{$apt._min_floor}-{$apt._max_floor}
										{/if}
									</td>
									<td class="square">
											<b>
											{if $apt._min_square_all==$apt._max_square_all}
												{$apt._min_square_all}
											{else}
												{$apt._min_square_all}-{$apt._max_square_all}
											{/if}
											</b>
											<span class="slash">/</span>
											<span>
											{if $apt._min_square_live==$apt._max_square_live}
												{$apt._min_square_live}
											{else}
												{$apt._min_square_live}-{$apt._max_square_live}
											{/if}
											</span>
											<span class="slash">/</span>
											<span>
											{if $apt._min_square_kitchen==$apt._max_square_kitchen}
												{$apt._min_square_kitchen}
											{else}
												{$apt._min_square_kitchen}-{$apt._max_square_kitchen}
											{/if}
											</span>
									</td>
									<td class="align-left nowrap price">
										<span>
										<span style="white-space:nowrap">
										{if $apt._min_price==$apt._max_price}
											{$apt._min_price}
										{else}
											{$apt._min_price}-{$apt._max_price}
										{/if}
										</span>
										</span>
									</td>
								</tr>
								{/if}
							  {/foreach}

							</tbody>
						</table>
						<div class="complex_footer">
						<p>количество предложений - <a href="{$complex.href}">{$total_count}</a></p>
						</div>
					</div>
			  </div>
			  {/if}
		</div>
			{/foreach}
			{if $grid_data.pager != ''}
			<tr>
				<td colspan="4" class="pager"><div align="center">{$grid_data.pager}</div></td>
			</tr>
			 {/if}
	</div>
	<div class="span6 complex_right">
		<div class="bigmap" style="width: 100%;  height: 600px;">
			<div id="YMapsID" style="border: 1px solid #e6e6e6; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; width: 100%; height: 100%;"></div>
		</div>
	</div>
</div>
 
<script type="text/javascript">

</script>
	{literal}
	<script type="text/javascript">
	var map=null;
	var markers=[];
	
	var marker_1=estate_folder + '/template/frontend/{/literal}{$current_theme_name}{literal}/img/mapmarker_teal.png';
	var marker_2=estate_folder + '/template/frontend/{/literal}{$current_theme_name}{literal}/img/mapmarker_tealhard.png';
	function initialize() {
		var latlng = new google.maps.LatLng({/literal}{$apps_geodata_new_map_center}{literal});
		var myOptions = {
		  zoom: 14,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("YMapsID"), myOptions);

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
					icon: {url: marker_1},
					position: latlng,
					map: map,
					title: objects[o].title,
					objectID: objects[o].objectID,
				});
				markers.push(marker);
				makeInfoWin(marker, infowindow, html);

			}
			
			if(objects.length>1){
				var boundsCenter=bounds.getCenter();
				map.setCenter(boundsCenter);
				map.fitBounds(bounds);
			}else{
				map.setCenter(latlng);
			}

			
		}
	};

	function makeInfoWin(marker, infowindow, data) {
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.setContent(data);
			infowindow.open(marker.getMap(), marker);
			higlightComplex(marker.objectID);
		});
	}

	function higlightComplex(id){
		$('.complex_single').removeClass('lighted');
		$('#objectID_'+id).addClass('lighted');
	}

	jQuery(document).ready(function(){
		initialize();
		$('.complex_single').hover(
			function(){
				var id=$(this).attr('id').replace('objectID_', '');
				for(var i in markers){
					if(markers[i].objectID==id){
						markers[i].setIcon(marker_2);
						map.panTo(markers[i].position);
						break;
					}
				}
			},
			function(){
				var id=$(this).attr('id').replace('objectID_', '');
				for(var i in markers){
					if(markers[i].objectID==id){
						markers[i].setIcon(marker_1);
						break;
					}
				}
			}
		);
	});
	{/literal}
	</script>


    {if $grid_data.pager != ''}
	<div align="center" class="pager">{$grid_data.pager}</div>
    {/if}
