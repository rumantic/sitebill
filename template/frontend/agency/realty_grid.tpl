<script src="{$estate_folder}/apps/system/js/json2.js" type="text/javascript"></script>

<h1>{$title}</h1>


{if $smarty.request.page == 1 or $smarty.request.page == '' }
<span itemprop="description">{$description}</span>
{/if}

<div class="row-fluid">
    <div class="span6">
        <p>{$L_FIND_TOTAL}: <b>{$_total_records}</b></p>
    </div>
    <div class="span6">
        <div class="right_p">
            <!--div class="pagenav_buttons">
                <a href="" class="backward active"></a>
                <a href="" class="forward active"></a>
            </div-->
            
            <div class="viewtype_buttons">
                <a href="{$estate_folder}/{$url}&grid_type=list" class="list_view{if $smarty.session.grid_type eq 'list'} active{/if}" rel="nofollow"></a>
                <a href="{$estate_folder}/{$url}&grid_type=thumbs" class="thumbs_view{if $smarty.session.grid_type eq 'thumbs'} active{/if}" rel="nofollow"></a>
            </div>
        </div>
    </div>
</div>
<!-- <div class="partsimporter" partsimporter-label="wqw"></div> -->
{assign var="lang_topic_name" value="name_{$smarty.session._lang}"}


{if $smarty.session.grid_type eq 'thumbs'}
            {include file='realty_grid_thumbs.tpl.html'}
{else}
<table class="content_main" cellspacing="2" cellpadding="2">
    <tr  class="row_head">
        <td width="1%" class="row_title"></td>
        <td width="1%" class="row_title">{$L_DATE}</td>
        <td width="1%" class="row_title">{$L_ID}</td>
        <td width="1%" class="row_title">{$L_PHOTO}</td>
        <td width="70" class="row_title">{$L_TYPE}&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=type&asc=asc" rel="nofollow">&darr;</a></noindex>&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=type&asc=desc" rel="nofollow">&uarr;</a></noindex></td>
        <td width=13% class="row_title">{$L_CITY}&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=city&asc=asc" rel="nofollow">&darr;</a></noindex>&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=city&asc=desc" rel="nofollow">&uarr;</a></noindex></td>
        <td width=13% class="row_title">{$L_DISTRICT}&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=district&asc=asc" rel="nofollow">&darr;</a></noindex>&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=district&asc=desc" rel="nofollow">&uarr;</a></noindex></td>
        <td width=13% class="row_title">{$L_STREET}&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=street&asc=asc" rel="nofollow">&darr;</a></noindex>&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=street&asc=desc" rel="nofollow">&uarr;</a></noindex></td>
        <td class="row_title" nowrap>{$L_PRICE}&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=price&asc=asc" rel="nofollow">&darr;</a></noindex>&nbsp;<noindex><a href="{$estate_folder}/{$url}&order=price&asc=desc" rel="nofollow">&uarr;</a></noindex></td>
        <td class="row_title">{$L_FLOOR}</td>
        <td class="row_title">{$L_SQUARE} м<sup>2</sup></td>
        {if $admin !=''}
        <td class="row_title"></td>
        {/if}
    </tr>
    {section name=i loop=$grid_items}
    
    <tr valign="top" class="row3{if $grid_items[i].bold_status==1} grid_table_bold{/if}{if $grid_items[i].premium_status==1} grid_table_premium{/if}" {if $grid_items[i].active == 0}style="color: #ff5a5a;"{/if}>
        
        <td><a name="row{$grid_items[i].id}"></a>
        {if isset($smarty.session.favorites)}
            {if in_array($grid_items[i].id,$smarty.session.favorites)}
                <a class="remove_from_favorites" alt="{$grid_items[i].id}" title="{$L_DELETEFROMFAVORITES}" href="#remove_from_favorites"></a>
            {else}
                <a class="add_to_favorites" alt="{$grid_items[i].id}" title="{$L_ADDTOFAVORITES}" href="#add_to_favorites"></a>
            {/if}
        {/if}
        </td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}><b><a href="{$grid_items[i].href}">{$grid_items[i].date}</a></b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}><b><a href="{$grid_items[i].href}">{$grid_items[i].id}{if isset($grid_items[i].uniq_id)} ({$grid_items[i].uniq_id}){/if}</a></b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if} align="center">
        {if $grid_items[i].img != '' } 
        <a href="{$grid_items[i].href}"><img src="{$estate_folder}/img/data/{$grid_items[i].img[0].preview}" width="50" class="previewi"></a> 
        <!-- img src="{$estate_folder}/img/hasphoto.jpg" border="0" width="16" height="14" /--> 
        {/if}
        </td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}><b>{if $grid_items[i].topic_info.$lang_topic_name != ''}{$grid_items[i].topic_info.$lang_topic_name}{else}{$grid_items[i].type_sh}{/if}</b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].city}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].district}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].street}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if} nowrap><b>{$grid_items[i].price|number_format:0:",":" "} {if $grid_items[i].currency_name != ''}{$grid_items[i].currency_name}{/if}</b></td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].floor}/{$grid_items[i].floor_count}</td>
        <td{if $admin == ''}  onClick="document.location='{$grid_items[i].href}'" {/if}>{$grid_items[i].square_all}/{$grid_items[i].square_live}/{$grid_items[i].square_kitchen}</td>
        {if $admin !=''}
        <td nowrap>
        <a href="{$estate_folder_control}?do=edit&id={$grid_items[i].id}"><img src="{$estate_folder}/img/edit.gif" border="0" width="16" height="16" /></a>
        <a onclick="return confirm('{$L_MESSAGE_REALLY_WANT_DELETE}');" href="{$estate_folder_control}?{if $topic_id != ''}topic_id={$topic_id}&{/if}do=delete&id={$grid_items[i].id}"><img src="{$estate_folder}/img/delete.gif" border="0" width="16" height="16" /></a>
        
        </td>
        {/if}
    </tr>
    
    {/section}

    {if $pager != ''}
    <tr>
        <td colspan="9" class="pager">{$pager}</td>
    </tr>
    {/if}
</table>

{/if}





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
			if(typeof google == 'object'){
				initialize_grid_map();
			}
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
	
	
	<div class="bigmap">
		<div id="grid_realty_map" style="border: 1px solid #e6e6e6; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; width: 100%; height: 400px;"></div>
	</div>

{/if}