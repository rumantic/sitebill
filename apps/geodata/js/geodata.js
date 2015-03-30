(function($){
	jQuery.fn.Geodata = function(options){
		var _defaults = {width: 350, height: 350};
		
		var options = $.extend(true, _defaults, options);
		
		
		
		var PositionEditor={
			init: function(map){
				this.map=map;
				this.settedMarker=null;
			},
			initMarker: function(x,y,id){
				if(x!='' && y!=''){
					var latlng = new google.maps.LatLng(new Number(x),new Number(y));
					this.createPositionMarker(latlng,id);
					this.map.setCenter(latlng);
				}else{
					if(this.settedMarker!=null){
						this.settedMarker.setMap(null);
						this.settedMarker=null;
					}
				}
				
			},

			createPositionMarker: function(latlng, id){
					
				var lat=new String(latlng.lat());
				var lng=new String(latlng.lng());
				var lat_parts=lat.split('.');
				if(lat_parts[1]!==undefined && lat_parts[1].length>6){
					lat=lat_parts[0]+'.'+lat_parts[1].substring(0,6);
				}
				var lng_parts=lng.split('.');
				if(lng_parts[1]!==undefined && lng_parts[1].length>6){
					lng=lng_parts[0]+'.'+lng_parts[1].substring(0,6);
				}
				
				if(this.settedMarker!=null){
					this.settedMarker.setMap(null);
					this.settedMarker=null;
				}
				
				var marker = new google.maps.Marker({
					position: latlng, 
					map: this.map,
					draggable: false,
					title:latlng.lat()+' '+latlng.lng()
				});
				this.settedMarker=marker;
				var ret=[];
				ret.push(lat);
				ret.push(lng);
				return ret;
			}
		}
		var PositionEditorYandex={
			init: function(map){
				this.map=map;
				this.settedMarker=null;
			},
			initMarker: function(x, y, id){
				if(x!='' && y!=''){
					//var latlng = new google.maps.LatLng(new Number(x),new Number(y));
					var latlng=new Array(new Number(x),new Number(y));
					this.createPositionMarker(latlng, id);
					this.map.setCenter(latlng);
				}else{
					if(this.settedMarker!=null){
						this.settedMarker.setMap(null);
						this.settedMarker=null;
					}
				}
				
			},

			createPositionMarker: function(latlng, id){
					
				var lat=new String(latlng[0]);
				var lng=new String(latlng[1]);
				var lat_parts=lat.split('.');
				if(lat_parts[1]!==undefined && lat_parts[1].length>6){
					lat=lat_parts[0]+'.'+lat_parts[1].substring(0,6);
				}
				var lng_parts=lng.split('.');
				if(lng_parts[1]!==undefined && lng_parts[1].length>6){
					lng=lng_parts[0]+'.'+lng_parts[1].substring(0,6);
				}
				
				if(this.settedMarker!=null){
					this.map.geoObjects.remove(this.settedMarker);
					//this.settedMarker.remove()
					//this.settedMarker.setMap(null);
					this.settedMarker=null;
				}
				var myPlacemark = new ymaps.Placemark(
					latlng,
					{
						iconContent: '',
					},
					{
						draggable: false,
			        }
			    );
				this.map.geoObjects.add(myPlacemark);
				
				
				this.settedMarker=myPlacemark;
				var ret=[];
				ret.push(lat);
				ret.push(lng);
				return ret;
			}
		}
		var GDC=$(this);
		var map_center_string=GDC.attr('coords');
		var map_zoom=GDC.attr('zoom');
		
		if(map_zoom=='' || map_zoom=='0'){
			map_zoom=Number(10);
		}else{
			map_zoom=Number(map_zoom);
		}
		
		if(map_center_string!=''){
			var c=map_center_string.split(',');
		}else{
			var c=[55.751849,37.622681];
		}
		
		//console.log(c);
		var late=GDC.find('[geodata=lat]');
		var lnge=GDC.find('[geodata=lng]');
		if(options.map_type!='google'){
			options.map_type='yandex';
		}
		if(options.map_type=='google'){
			if(late && lnge){
				var map=initializeGoogleLocationsMap(map_id);
				
				var PE=PositionEditor.init(map);
				google.maps.event.addDomListener(map, 'click', function(event) {
					var geo_c=PositionEditor.createPositionMarker(event.latLng, 0);
					late.val(geo_c[0]);
					lnge.val(geo_c[1]);
				});
				var lat=late.val();
				var lng=lnge.val();
				if(lat!='' && lng!=''){
					PositionEditor.initMarker(lat, lng, 0);
				}
				
				/*late.change(function(){
					var lng=lnge.val();
					var lat=late.val();
					if(lat!='' && lng!=''){
						runMapChange(lat, lng);
					}
				});
				
				lnge.change(function(){
					var lng=lnge.val();
					var lat=late.val();
					if(lat!='' && lng!=''){
						runMapChange(lat, lng);
					}
				});*/
			}
		}else{
			if(late && lnge){
				var map_id='map_'+CryptoJS.MD5((new Date()).toString()+'_'+(Math.floor(Math.random() * (999 - 100 + 1)) + 100));
				ymaps.ready(function(){
					var map=initializeYandexLocationsMap(map_id);
					var PE=PositionEditorYandex.init(map);
					map.events.add('click', function (e) {
						var geo_c=PositionEditorYandex.createPositionMarker(e.get('coordPosition'), 0);
						late.val(geo_c[0]);
						lnge.val(geo_c[1]);
					});
					var lat=late.val();
					var lng=lnge.val();
					if(lat!='' && lng!=''){
						PositionEditorYandex.initMarker(lat, lng, 0);
					}
					
					
					/*late.change(function(){
						
						var lng=lnge.val();
						var lat=late.val();
						if(lat!='' && lng!=''){
							runMapChange(map, lat, lng);
							PositionEditor.initMarker(lat, lng, 0);
						}
					});
					
					lnge.change(function(){
						
						var lng=lnge.val();
						var lat=late.val();
						if(lat!='' && lng!=''){
							runMapChange(map, lat, lng);
							PositionEditor.initMarker(lat, lng, 0);
						}
					});*/
				});
				
			}
		}
		
		/*function runMapChange(map, lat, lng){
			if(options.map_type=='google'){
				//map.setCenter(new google.maps.LatLng(lat, lng));
				map.panTo(new google.maps.LatLng(lat, lng));
			}else{
				//map.setCenter(new Array(lat, lng));
				map.panTo(new Array(lat, lng));
			}
			//
		}*/
		
		function initializeYandexLocationsMap(map_id){
			var behaviors=[];
			behaviors.push("drag");
			behaviors.push("dblClickZoom");
			behaviors.push("scrollZoom");
			
			var latlng=new Array(new Number(c[0]),new Number(c[1]));
			var m=$('<div id="'+map_id+'" style="width:'+options.width+'px; height:'+options.height+'px"></div>')
			m.appendTo($('body')).css({'position':'absolute','left':'-1000px','display':'block'});
			
			var map = new ymaps.Map(document.getElementById(map_id), {
				zoom: map_zoom,
				center: latlng,
				behaviors: behaviors,
				type : 'yandex#publicMap'
				});
			map.controls.add(new ymaps.control.TypeSelector(['yandex#map', 'yandex#publicMap', 'yandex#satellite', 'yandex#hybrid']));
			map.controls.add('scaleLine');
			map.controls.add('zoomControl', { top: 75, left: 5 });
			m.css({'position':'relative','left':'0'}).appendTo(GDC);
			return map;	
		}
		
		function initializeGoogleLocationsMap(map_id){
			var latlng = new google.maps.LatLng(new Number(c[0]),new Number(c[1]));
			var myOptions = {
				zoom: map_zoom,
				center: latlng,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			
			var m=$('<div id="'+map_id+'" style="width:'+options.width+'px; height:'+options.height+'px"></div>')
			m.appendTo($('body')).css({'position':'absolute','left':'-1000px','display':'block'});
			var map = new google.maps.Map(document.getElementById(map_id), myOptions);
			
			google.maps.event.addDomListener(map, 'tilesloaded', function(event) {
				m.css({'position':'relative','left':'0'}).appendTo(GDC);
			});
			
			return map;
		}
	};
})(jQuery);
