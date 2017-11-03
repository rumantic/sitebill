function RealtyMap(version){
	if(version===undefined){
		this.version=2;
	}else{
		this.version=version;
	}
	this.map=null;
	
	this.clusterer=null;
	this.bounds=null;
	this.type='google';
	
	this.map_element=null;
	this.locations=[];
	this.markers=[];
	//this.marker_icon=estate_folder+'/template/frontend/realia/img/marker.png';
	this.clustered_objects=[];
	this.clustered_data=[];
	this.options={
	   scrollZoom : true,
	   minimap: true,
	   marker_icon: estate_folder+'/template/frontend/agency/img/marker.png',
	   defaultZoom: 16,
	   yandexMapType: 'yandex#publicMap',
	   marker_size: [42, 57],
	   marker_offset: [-21, -57],
	   ajax: false,
	   marker_htm: '<div class="marker"><div class="marker-inner"></div></div>',
	   use_clusters: false,
	   adopt_bounds: true,
	   custom_center: []
	};
	
	this.markersVariants={
		_default: {
			icon: estate_folder+'/template/frontend/agency/img/marker.png',
			size: [42, 57],
			offset: [-21, -57]
		}
	}

	if(window.RM_Custom_Markers !== undefined){
		this.markersVariants=$.extend(true, {}, this.markersVariants, window.RM_Custom_Markers);
	}
	var self=this;
	
	this.stripslashes=function(str){
		str = str.replace(/\\'/g, '\'');
        str = str.replace(/\\"/g, '"');
        str = str.replace(/\\0/g, '\0');
        str = str.replace(/\\\\/g, '\\');
        return str;
	};
	
	this.setDefaultIconImage=function(marker_icon){
		this.markersVariants._default.icon=marker_icon;
	};
	
	this.setDefaultIconSize=function(size){
		this.markersVariants._default.size=size;
	};
	
	this.setDefaultIconOffset=function(offset){
		this.markersVariants._default.offset=offset;
	};
	
	this.makeInfoWin=function(marker, infowindow, data) {
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.setContent(data);
			infowindow.open(marker.getMap(), marker);
		});  
	}
	
	this.saveGeoCoordinates=function(coords){
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			type: 'post',
			data: {actionlat: coords[0], lng: coords[1]}
		});
	}
	
	this.clusterizeLocations=function(){
		
		for(var i in this.locations){
			if(this.locations[i].lat && this.locations[i].lat!='' && this.locations[i].lng && this.locations[i].lng!=''){
				var g_c_i=this.locations[i].lat+'_'+this.locations[i].lng;
				if(this.clustered_objects[g_c_i]==undefined){
					this.clustered_objects[g_c_i]=[];
					this.clustered_objects[g_c_i].push(this.locations[i]);
				}else{
					this.clustered_objects[g_c_i].push(this.locations[i]);
				}
			}else if(this.locations[i].address && this.locations[i].address!=''){
				
			}else{
				this.locations[i]=null;
			}
		}
		
	};
	this.refreshBounds=function(lat_lng){
		var boundsCenter=this.bounds.getCenter();
		this.map.setCenter(boundsCenter);
		this.map.fitBounds(this.bounds);
	};
	this.createMap=function(){
		if(this.type=='google'){
			this.createGoogleMap();
		}else{
	    	this.createYandexMap();
	    }
	};
	this.createSimpleMap=function(markers_array){
		if(this.type=='google'){
			this.createSimpleGoogleMap(markers_array);
		}else{
			this.createSimpleYandexMap(markers_array);
	    }
	};
	this.createSimpleYandexMap=function(markers_array){
		ymaps.ready(function(){
			self._createYandexMap();
			var count=0;
			for(var i in markers_array){
				count++;
				var llat=Number(markers_array[i].lat);
				var llng=Number(markers_array[i].lng);
				var latlng=new Array(llat, llng);
				var markerOpts={};
				if(markers_array[i].content != undefined){
					markerOpts.content=markers_array[i].content;
				}else{
					markerOpts.content='';
				}
				
				if(markers_array[i].hint != undefined){
					markerOpts.hint=markers_array[i].hint;
				}else{
					markerOpts.hint='';
				}
				/*
				if(markers_array[i].showPopup != undefined){
					markerOpts.showPopup=markers_array[i].showPopup;
				}*/
				if(markers_array[i].icon!=undefined){
					markerOpts.icon=markers_array[i].icon;
				}
				/*if(markers_array[i].marker_size!=undefined){
					markerOpts.marker_size=markers_array[i].marker_size;
				}*/
				
				var marker=self._putYandexMarker(latlng, markerOpts);
				self.markers.push(marker);
			}
			
			if(count==1){
				self.map.setCenter(latlng);
				self.map.setZoom(self.options.defaultZoom);
			}else{
				self.map.setBounds(self.map.geoObjects.getBounds());
			}
		});
	};
	this.createSimpleGoogleMap=function(markers_array){
		this._createGoogleMap();
		if(markers_array.length>1){
			this.bounds=new google.maps.LatLngBounds();
			for(var i in markers_array){
				var lat=markers_array[i].lat;
				var lng=markers_array[i].lng;
				var latlng = new google.maps.LatLng(lat, lng);
				var markerOpts={};
				if(markers_array[i].content != undefined){
					markerOpts.content=markers_array[i].content;
				}else{
					markerOpts.content='';
				}
				
				if(markers_array[i].hint != undefined){
					markerOpts.hint=markers_array[i].hint;
				}else{
					markerOpts.hint='';
				}
				/*
				if(markers_array[i].showPopup != undefined){
					markerOpts.showPopup=markers_array[i].showPopup;
				}*/
				if(markers_array[i].icon!=undefined){
					markerOpts.icon=markers_array[i].icon;
				}
				/*if(markers_array[0].marker_size!=undefined){
					markerOpts.icon=markers_array[0].marker_size;
				}*/
				
				var marker=self._putGoogleMarker(latlng, markerOpts);
				this.markers.push(marker);
				this.bounds.extend(latlng);
			}
			this.refreshBounds();
		}else{
			var lat=markers_array[0].lat;
			var lng=markers_array[0].lng;
			var latlng = new google.maps.LatLng(lat, lng);
			var markerOpts={};
			if(markers_array[0].content != undefined){
				markerOpts.content=markers_array[0].content;
			}else{
				markerOpts.content='';
			}
			
			if(markers_array[0].hint != undefined){
				markerOpts.hint=markers_array[0].hint;
			}else{
				markerOpts.hint='';
			}
			
			/*if(markers_array[0].showPopup!=undefined){
				markerOpts.showPopup=markers_array[0].showPopup;
			}*/
			if(markers_array[0].icon!=undefined){
				markerOpts.icon=markers_array[0].icon;
			}
			
			var marker=self._putGoogleMarker(latlng, markerOpts);
			this.markers.push(marker);
			this.map.setCenter(latlng);
		}
	};
	this._geocode=function(rname){
		if(this.type=='google'){
			this.geocodeGoogle(rname);
		}else{
	    	this.geocodeYandex(rname);
	    }
	};
	this.geocodeYandex=function(rname){
		ymaps.ready(function(){
			self._createYandexMap();
			var myGeocoder = ymaps.geocode(rname, {results: 1});
			myGeocoder.then(function (res) {
				if(res.geoObjects.get(0)===null){
					$(self.map_element).hide();
				}else{
					var coords=res.geoObjects.get(0).geometry.getCoordinates();
					//self.saveGeoCoordinates(coords);
					self._pushYandexMarker(coords, rname, '');
					self.map.setCenter(coords);
				}
			});
		});
	};
	this.geocodeGoogle=function(rname){
		if(typeof google == 'object'){
			self._createGoogleMap();
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({'address':rname},function(data){
				
				var lat=data[0].geometry.location.lat();
				var lng=data[0].geometry.location.lng();
				var latlng = new google.maps.LatLng(lat, lng);
				self._pushGoogleMarker(latlng, rname, '');
				self.map.setCenter(latlng);
				
			});
		}
	};
	
	this._createGoogleMap=function(){
		var _this=this;
		var mapOptions = {
	        center: new google.maps.LatLng(0, 0),
	        zoom: this.options.defaultZoom,
	        mapTypeId: google.maps.MapTypeId.ROADMAP,
	        scrollwheel: this.options.scrollZoom
	    };

		this.map = new google.maps.Map(this.map_element, mapOptions);
	};
	this._putGoogleMarker=function(latlng, markerOpts/*content, hintContent, hasInfoWindow, icon*/){
		if(markerOpts.content != undefined){
			var content=this.stripslashes(markerOpts.content);
		}else{
			var content='';
		}
		
		if(markerOpts.icon != undefined && this.markersVariants[markerOpts.icon] != undefined){
			var micon=this.markersVariants[markerOpts.icon].icon;
			var marker_offset=this.markersVariants[markerOpts.icon].offset;
			var marker_size=this.markersVariants[markerOpts.icon].size;
		}else{
			var micon=this.markersVariants._default.icon;
			var marker_offset=this.markersVariants._default.offset;
			var marker_size=this.markersVariants._default.size;
		}
		
		/*if(markerOpts.icon != undefined){
			var micon=markerOpts.icon;
		}else{
			var micon=this.options.marker_icon;
		}*/
		
		if(markerOpts.hint != undefined){
			var hintContent=markerOpts.hint;
		}else{
			var hintContent='';
		}
		
		if(markerOpts.ids != undefined){
			var ids=markerOpts.ids;
		}else{
			var ids=[];
		}
		
		
		
		
		var marker = new google.maps.Marker({
            position: latlng,
            map: this.map,
            icon: micon,
            title: hintContent,
            ids: ids
        });
		
		if(/*markerOpts.showPopup != undefined && markerOpts.showPopup == true && */content != ''){
			var infowindow = new google.maps.InfoWindow({  
			  content: ''
			});
			this.makeInfoWin(marker, infowindow, content);
		}
		return marker;
	};
	this._pushGoogleMarker=function(latlng, content, hintContent, hasInfoWindow, icon){
		var content=this.stripslashes(content);
		if(icon != undefined){
			var micon=icon;
		}else{
			var micon=this.options.marker_icon;
		}
		var marker = new google.maps.Marker({
            position: latlng,
            map: this.map,
            icon: micon,
            title: hintContent
        });
		if(hasInfoWindow){
			var content='<div class="cluster-listing scrollable">'+content+'</div>';
			var infowindow = new google.maps.InfoWindow({  
			  content: '' 
			});
			this.makeInfoWin(marker, infowindow, content);
		}
		this.map.setCenter(latlng);
		return marker;
	};
	this._createYandexMap=function(){
		
		var _this=this;
		
		var behaviors=[];
		if(_this.options.scrollZoom){
			behaviors.push("scrollZoom");
		}
		behaviors.push("drag");
		behaviors.push("dblClickZoom");
		
		if(_this.version=='2.1'){
			var controls=[];
			if(_this.options.fullscreenControl){
				controls.push('fullscreenControl');
			}
			controls.push('zoomControl');
			controls.push('typeSelector');
			_this.map = new ymaps.Map(_this.map_element, {
				zoom: _this.options.defaultZoom,
				center: [23.937149,49.886672],
				behaviors: behaviors,
				type : _this.options.yandexMapType,
				controls: controls
			});
		}else{
			_this.map = new ymaps.Map(_this.map_element, {
				zoom: _this.options.defaultZoom,
				center: [23.937149,49.886672],
				behaviors: behaviors,
				type : _this.options.yandexMapType
				});
			_this.map.controls.add(new ymaps.control.TypeSelector(['yandex#map', 'yandex#publicMap', 'yandex#satellite', 'yandex#hybrid']));
			_this.map.controls.add('scaleLine');
			if(_this.options.minimap){
				_this.map.controls.add(new ymaps.control.MiniMap(
				    { type: 'yandex#satellite' },
				    { size: [90, 90] }
				));
			}
			_this.map.controls.add('zoomControl', { top: 75, left: 5 });
		}
		
		
		
	
	
	};
	this._putYandexMarker=function(latlng, markerOpts){
		var _this=this;
		
		if(markerOpts.content != undefined){
			var content=this.stripslashes(markerOpts.content);
		}else{
			var content='';
		}
		
		
		if(markerOpts.icon != undefined && _this.markersVariants[markerOpts.icon] != undefined){
			var micon=_this.markersVariants[markerOpts.icon].icon;
			var marker_offset=_this.markersVariants[markerOpts.icon].offset;
			var marker_size=_this.markersVariants[markerOpts.icon].size;
		}else{
			var micon=_this.markersVariants._default.icon;
			var marker_offset=_this.markersVariants._default.offset;
			var marker_size=_this.markersVariants._default.size;
		}
		
		if(markerOpts.hint != undefined){
			var hintContent=markerOpts.hint;
		}else{
			var hintContent='';
		}
		
		if(/*markerOpts.showPopup != undefined && markerOpts.showPopup == true && */content != ''){
			var baloonContent={
				iconContent: '',
				balloonContentBody: content,
	            hintContent: hintContent
			}
		}else{
			var baloonContent={
				iconContent: '',
				hintContent: hintContent
			}
		}
		
		var myPlacemark = new ymaps.Placemark(
			latlng,
			baloonContent,
			{
				draggable: false,
	            hideIconOnBalloonOpen: true,
	            iconLayout: 'default#image',
	            iconImageHref: micon,
	            iconImageSize: marker_size,
	            iconImageOffset: marker_offset
	        }
	    );
		this.map.geoObjects.add(myPlacemark);
		var balloon = new ymaps.Balloon(this.map);
		return myPlacemark;
	};
	this._pushYandexMarker=function(latlng, content, hintContent, hasInfoWindow){
		//var latlng=new Array(llat, llng);
		var _this=this;
		var content=this.stripslashes(content);
		if(hasInfoWindow){
			var baloonContent={
				iconContent: '',
				balloonContentBody: '<div class="cluster-listing scrollable">'+content+'</div>',
	            hintContent: hintContent
			}
		}else{
			var baloonContent={
				iconContent: '',
				hintContent: hintContent
			}
		}
		
		var myPlacemark = new ymaps.Placemark(
			latlng,
			baloonContent,
			{
				draggable: false,
	            hideIconOnBalloonOpen: true,
	            iconImageHref: this.options.marker_icon,
	            iconImageSize: [42, 57],
	            iconImageOffset: [-21, -57]
	        }
	    );
		this.map.geoObjects.add(myPlacemark);
		var balloon = new ymaps.Balloon(this.map);
	};
	this.appendDataYandexMap=function(){
		var min_lat=0;
		var min_lng=0;
		var max_lat=0;
		var max_lng=0;
		if(this.clustered_data.length==0){
			
		}else{
			var count=0;
			for(var i in this.clustered_data){
				count++;
				var llat=Number(this.clustered_data[i].lat);
				var llng=Number(this.clustered_data[i].lng);
				var latlng=new Array(llat, llng);
				
				//var content=this.stripslashes(this.clustered_data[i].html);
				
				var markerOpts={};
				//markerOpts.content='<div class="cluster-listing scrollable">'+this.stripslashes(this.clustered_data[i].html)+'</div>';
				if(typeof this.clustered_data[i].html!='undefined' && this.clustered_data[i].html!=''){
					markerOpts.content='<div class="cluster-listing scrollable">'+this.stripslashes(this.clustered_data[i].html)+'</div>';
				}else{
					markerOpts.content='';
				}
				if(typeof this.clustered_data[i].count!='undefined'){
					markerOpts.hint='Объектов '+this.clustered_data[i].count;
				}
				if(this.clustered_data[i].icon!=undefined){
					markerOpts.icon=this.clustered_data[i].icon;
				}
				/*if(markers_array[i].marker_size!=undefined){
					markerOpts.marker_size=markers_array[i].marker_size;
				}*/
				
				var myPlacemark=self._putYandexMarker(latlng, markerOpts);
				this.markers.push(myPlacemark);
				//return;
				/*
				var marker_offset=[];
				marker_offset[0]=-1*parseInt(this.options.marker_size[0]/2);
				marker_offset[1]=-1*this.options.marker_size[1];
				
				if(this.clustered_data[i].marker !== undefined){
			    	var customMarker = ymaps.templateLayoutFactory.createClass(this.clustered_data[i].marker);
			    	var myPlacemark = new ymaps.Placemark(
							latlng,
							{
								iconContent: '',
								balloonContentBody: '<div class="cluster-listing scrollable">'+content+'</div>',
					            hintContent: 'Объектов '+this.clustered_data[i].count
							},
							{
								iconLayout: customMarker,
								draggable: false,
			    	            hideIconOnBalloonOpen: true,
			    	        }
			    	    );
			    }else{
			    	var myPlacemark = new ymaps.Placemark(
							latlng,
							{
								iconContent: '',
								balloonContentBody: '<div class="cluster-listing scrollable">'+content+'</div>',
					            hintContent: 'Объектов '+this.clustered_data[i].count
							},
							{
								//iconLayout: squareLayout,
								draggable: false,
			    	            hideIconOnBalloonOpen: true,
			    	            iconImageHref: this.options.marker_icon,
			    	            iconImageSize: this.options.marker_size,
			    	            iconImageOffset: marker_offset
			    	        }
			    	    );
			    }
			    
				this.markers.push(myPlacemark);
				this.map.geoObjects.add(myPlacemark);*/
				
				var balloon = new ymaps.Balloon(this.map);
		    }
			if(this.options.use_clusters){
				this.clusterer.add(this.markers);
				this.map.geoObjects.add(this.clusterer);
			}
			
			
			if(count==1){
				this.map.setCenter(latlng);
				this.map.setZoom(this.options.defaultZoom);
			}else if(!this.options.adopt_bounds){
				var lat_lng=this.options.custom_center;
				this.map.setCenter(lat_lng);
				this.map.setZoom(this.options.defaultZoom);
			}else{
				this.map.setBounds(this.map.geoObjects.getBounds());
			}
		}
	};
	this.createYandexMap=function(){
		var _this=this;
		if(this.clustered_data.length==0 && !this.options.ajax){
			$(this.map_element).hide();
			return;
		}
		ymaps.ready(function(){
			var behaviors=[];
			if(_this.options.scrollZoom){
				behaviors.push("scrollZoom");
			}
			behaviors.push("drag");
			behaviors.push("dblClickZoom");
						
			if(_this.version=='2.1'){
				var controls=[];
				if(_this.options.fullscreenControl){
					controls.push('fullscreenControl');
				}
				controls.push('zoomControl');
				controls.push('typeSelector');
				_this.map = new ymaps.Map(_this.map_element, {
					zoom: _this.options.defaultZoom,
					center: [23.937149,49.886672],
					behaviors: behaviors,
					type : _this.options.yandexMapType,
					controls: controls
				});
			}else{
				_this.map = new ymaps.Map(_this.map_element, {
					zoom: _this.options.defaultZoom,
					center: [23.937149,49.886672],
					behaviors: behaviors,
					type : _this.options.yandexMapType,
					
				});
				_this.map.controls.add(new ymaps.control.TypeSelector(['yandex#map', 'yandex#publicMap', 'yandex#satellite', 'yandex#hybrid']));
				_this.map.controls.add('scaleLine');
				if(_this.options.minimap){
					_this.map.controls.add(new ymaps.control.MiniMap(
					    { type: 'yandex#satellite' },
					    { size: [90, 90] }
					));
				}
				_this.map.controls.add('zoomControl', { top: 75, left: 5 });
			}
			if(_this.options.use_clusters){
				_this.clusterer = new ymaps.Clusterer({
		            preset: 'islands#invertedVioletClusterIcons',
		            groupByCoordinates: false,
		            clusterDisableClickZoom: false,
		            clusterHideIconOnBalloonOpen: false,
		            geoObjectHideIconOnBalloonOpen: false
		        });
			}
			
			
			
			_this.appendDataYandexMap();
	    	
	    	
		});
	};
	this.appendDataGoogleMap=function(){
		this.bounds=new google.maps.LatLngBounds();
		
		var infowindow = new google.maps.InfoWindow({  
		  content: '' 
		});
		
		var last_lat_lng;
		var count=0;
		
		for(var i in this.clustered_data){
			
			var llat=this.clustered_data[i].lat;
			var llng=this.clustered_data[i].lng;
			var lat_lng=new google.maps.LatLng(llat, llng);
			last_lat_lng=lat_lng;
			
			var markerOpts={};
			if(this.stripslashes(this.clustered_data[i].html)!=''){
				markerOpts.content='<div class="cluster-listing scrollable">'+this.stripslashes(this.clustered_data[i].html)+'</div>';
			}else{
				markerOpts.content='';
			}
			
			if(typeof this.clustered_data[i].ids != 'undefined'){
				markerOpts.ids=this.clustered_data[i].ids;
			}
			
			markerOpts.hint='Объектов '+this.clustered_data[i].count;
			
			/*if(markers_array[0].showPopup!=undefined){
				markerOpts.showPopup=markers_array[0].showPopup;
			}*/
			if(this.clustered_data[i].icon!=undefined){
				markerOpts.icon=this.clustered_data[i].icon;
			}
			
			var marker=self._putGoogleMarker(lat_lng, markerOpts);
			/*
			var marker = new google.maps.Marker({
	            position: lat_lng,
	            map: this.map,
	            icon: this.options.marker_icon,
	            title: 'Объектов '+this.clustered_data[i].count
	        });
			*/			
			this.markers.push(marker);
	        this.bounds.extend(lat_lng);
	        //var content='<div class="cluster-listing scrollable">'+this.stripslashes(this.clustered_data[i].html)+'</div>';
	        //this.makeInfoWin(marker, infowindow, content);
	        count++;
		}
		
		if(this.options.use_clusters){
			this.clusterer.addMarkers(this.markers);
		}
		
		
		if(count==1){
			this.map.setCenter(last_lat_lng);
		}else if(!this.options.adopt_bounds){
			var lat_lng=new google.maps.LatLng(this.options.custom_center[0], this.options.custom_center[1]);
			this.map.setCenter(lat_lng);
		}else{
			this.refreshBounds();
		}
	};
	this.clearMap=function(){
		if(this.type=='google'){
			if(this.markers.length>0){
				for(var i in this.markers){
					this.markers[i].setMap(null);
					delete this.markers[i];
				}
			}
		}else{
			if(this.markers.length>0){
				for(var i in this.markers){
					this.map.geoObjects.remove(this.markers[i]);
					delete this.markers[i];
				}
			}
		}
	};
	this.createGoogleMap=function(){
		
		if(this.clustered_data.length==0 && !this.options.ajax){
			$(this.map_element).hide();
			return;
		}
		var mapOptions = {
		        center: new google.maps.LatLng(0, 0),
		        zoom: this.options.defaultZoom,
		        mapTypeId: google.maps.MapTypeId.ROADMAP,
		        scrollwheel: this.options.scrollZoom,
		        overviewMapControl:  this.options.minimap,
		    };
		this.map = new google.maps.Map(this.map_element, mapOptions);
		if(this.options.use_clusters){
			this.clusterer = new MarkerClusterer(this.map, [], {gridSize: 50, maxZoom: 15, imagePath: estate_folder+'/apps/third/google/markerclusterer/images/m'});
		}
		this.appendDataGoogleMap();
	}
	
	var self=this;
	
	return {
		init: function(el, data, type){
			self.map_element=document.getElementById(el);
			self.type=type || 'google';
			self.locations=data || [];
			
			if(undefined == self.map_element){
		    	return false;
		    }
		    
		    if(self.locations.length==0){
		    	$(el).hide();
		    	return false;
		    }
		    
		    self.clusterizeLocations();
		    self.createMap();
		 },
		 reinit: function(data){
			 self.clearMap(); 
			 if(data.length!=0){
			 	self.clustered_data = data;
			 	if(self.type=='google'){
			 		self.appendDataGoogleMap();
				}else{
					self.appendDataYandexMap();
				}
			}
		 },
		 initJSON: function(el, datalisting, type, options){
			 
			var options = options || {};
			self.options=$.extend(true, {}, self.options, options)
			self.map_element=document.getElementById(el);
			self.type=type || 'google';
			var _this=this;
			var datalisting = datalisting;
			if(options.marker_icon !== undefined){
				self.setDefaultIconImage(options.marker_icon);
			}
			if(options.marker_size !== undefined){
				self.setDefaultIconSize(options.marker_size);
			}
			if(options.marker_offset !== undefined){
				self.setDefaultIconOffset(options.marker_offset);
			}
			self.clustered_data = datalisting || [];
			
			if(undefined == self.map_element){
				return false;
		    }
			self.createMap();
		 },
		 initGeocoded: function(el, rname, type, options){
		 	
			var options = options || {};
			self.options=$.extend(true, {}, self.options, options);
			self.map_element=document.getElementById(el);
			self.type=type || 'google';
			var _this=this;
			self._geocode(rname);
		 },
		 initSimpleMap: function(el, type, markers_array, options){
			 
			 var options = options || {}; 
			 self.options=$.extend(true, {}, self.options, options);
			 self.map_element=document.getElementById(el);
			 self.type=type || 'google';
			 var _this=this;
			 if(undefined == self.map_element){
			 	return false;
			 }
			 if(options.marker_icon !== undefined){
				self.setDefaultIconImage(options.marker_icon);
			}
			if(options.marker_size !== undefined){
				self.setDefaultIconSize(options.marker_size);
			}
			if(options.marker_offset !== undefined){
				self.setDefaultIconOffset(options.marker_offset);
			}
			 self.createSimpleMap(markers_array);
		 },
		 getMap: function(){
			 return self.map;
		 },
		 getMarkers: function(){
			 return self.markers;
		 }
	}
}