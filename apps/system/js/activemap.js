var ActiveMap = {
    //Vars
    containerId: '',
    map: null, //map
    clusterer: null,
    mapEngine: null,
    isMapInDrawMode: false,
    /*withListing: false,
    withForm: false,*/
    
    forms: [],
    listing: null,
    refreshListingWithPolyOnly: false,
    listingpager: [],
    sorts: [],
    countnotify: null,
    
    pagerDecorator: null,
    
    canvas: null,
    ctx2d: null,
    container: null, //activemap block container
    provider: null, //map provider: goole|yandex
    mapContainer: null, //$element map
    canvasContainer: null, //$element canvas
    controlsContainer: null, //$element controls block
    controlsDraw: null, //$element draw button
    controlsClear: null, //$element clear button
    mapContainerTopOffest: 0, //activemap block container top offset from page top
    mapContainerLeftOffest: 0, //activemap block container left offset from page left
    markers: [], //markers on map array
    drawedPolygone: null, //object of drawed on map polygon
    currentPolygoneCoordinates: [], //array of polygon coordinates [[lat, lng], [lat, lng], [lat, lng], ...]
    catchEvents: false,
    searchParamsCollector: null,
    itemsViewBlock: null,
    itemsViewBlockTpl: null,
    options: {
        polygonOptions: {
            fillColor: '#8080ff',
            fillOpacity: 0.7,
            strokeColor: '#0000ff',
            strokeOpacity: 0.7,
            strokeWidth: 2
        },
        canvasOptions: {
            strokeStyle: '#0000ff',
            lineWidth: 2,
            opacity: 0.7
        }
    },
    mapdefaults: {
        center: {
            lat: null,
            lng: null
        },
        zoom: null
    },
    //отключение реакции на изменение карты
    nomapbehaviors: false,
    mapEngine: null,
    //Interface
    init: function(containerId, provider, options){
        
        this.provider = provider;
        this.containerId = containerId;
        
        
        if(typeof options == 'object'){
            /*if(typeof options.withListing != 'undefined'){
                this.withListing = true;
            }*/
            /*if(typeof options.withForm != 'undefined'){
                this.withForm = true;
            }*/
            
            if(typeof options.refreshListingWithPolyOnly != 'undefined'){
                this.refreshListingWithPolyOnly = options.refreshListingWithPolyOnly;
            }
            
            if(typeof options.listing != 'undefined'){
                this.listing = options.listing;
            }
            
            if(typeof options.listingpager != 'undefined'){
                this.listingpager = options.listingpager;
            }
            
            if(typeof options.form != 'undefined'){
                this.forms = options.form;
            }
            
            if(typeof options.sorts != 'undefined'){
                this.sorts = options.sorts;
            }
            
            if(typeof options.countnotify != 'undefined'){
                this.countnotify = options.countnotify;
            }
            
            if(typeof options.pagerDecorator == 'function'){
                this.pagerDecorator = options.pagerDecorator;
            }
            
            if(typeof options.nomapbehaviors != 'undefined'){
                this.nomapbehaviors = options.nomapbehaviors;
            }
            /*if(typeof options.reload != 'undefined'){
                this.reload = 1;
            }*/
        }
        
        this.initEngine();
        
        
        
        this.container = $('#' + this.containerId);
        this.mapContainer = $('#ActiveMap');
        this.canvasContainer = $('#ActiveMapCanvas');
        
        this.controlsContainer = $('.ActiveMapControls');
        
        this.controlsDraw = this.controlsContainer.find('#ActiveMapControls-Draw');
        this.controlsClear = this.controlsContainer.find('#ActiveMapControls-Clear');
        
        this.itemsViewBlock = $('#' + this.containerId + ' .ActiveMapListBlock');
        this.itemsViewBlockTpl = $('#' + this.containerId + ' .ActiveMapListBlock .ActiveMapListBlock-tpl').clone();
        
       
        this.itemsViewBlock.find('.ActiveMapListBlock-closer').click(function(){
            _this.clearItemsViewBlock();
        });
        
        this.mapdefaults.center.lat = this.mapContainer.data('center-lat');
        this.mapdefaults.center.lng = this.mapContainer.data('center-lng');
        this.mapdefaults.zoom = this.mapContainer.data('zoom');        
        
        this.mapEngine().buildMap();
       
        this.initMapStartParameters();
                    
        var _this = this;
        this.controlsDraw.click(function(){
            _this.clearItemsViewBlock();
            _this.initDrawMode(_this);
        });            
        this.controlsClear.click(function(){
            _this.clearItemsViewBlock();
            _this.clearDrawedPolygon();
        });
        
        if(this.listing === null && $('#ActiveMapListing').length > 0){
            this.listing = $('#ActiveMapListing');
        }
        
        
        /*if(this.form.length > 0){
            this.form.submit(function(e){
                e.preventDefault();
                search_params.page = 1;
                _this.reloadMapData();
                _this.reloadListingData();
            });
        }*/
        
        if(this.forms.length > 0){
            for(var i in this.forms){
                this.forms[i].submit(function(e){
                    e.preventDefault();
                    search_params.page = 1;
                    if(!_this.nomapbehaviors){
                        _this.reloadMapData();
                    }else{
                        _this.reloadMapData(true);
                    }
                    
                    _this.reloadListingData();
                });
            }
            
        }
        
        if(this.sorts.length > 0){
            
            for(var i in this.sorts){
                if(this.sorts[i].prop('tagName').toLowerCase() == 'select'){
                    this.sorts[i].change(function(e){
                        e.preventDefault();
                        var s = $(this).find('option:selected');
                        
                        search_params.order = s.data('order');
                        search_params.asc = s.data('sort');
                        search_params.page = 1;
                        _this.reloadListingData();
                    });
                }else{                    
                    this.sorts[i].find('a').click(function(e){
                        
                        e.preventDefault();
                        var s = $(this);
                        $(this).parents().eq(0).find('a').removeClass('active');
                        $(this).addClass('active');
                        
                        search_params.order = s.data('order');
                        search_params.asc = s.data('sort');
                        search_params.page = 1;
                        _this.reloadListingData();
                    });
                }
            }
        }
    },
    /*hasListing: function(){
       return this.withListing; 
    },*/
    getSearchParams: function(){
        var sparams = {};
        
        var activeform = null;
        if(this.forms !== null && this.forms.length > 0){
            for(var i in this.forms){
                if(this.forms[i].is(':visible')){
                    activeform = this.forms[i];
                }
            }
        }
        
        if(activeform !== null){                
            var a = activeform.serializeArray();
            $.each(a, function() {
                var name=this.name.replace('[]','');
                if (sparams[name]) {
                    if (!sparams[name].push) {
                        sparams[name] = [sparams[name]];
                    }
                    sparams[name].push(this.value || '');
                } else {
                    sparams[name] = this.value || '';
                }
            });
        }
        sparams = $.extend({}, search_params, sparams);
		return sparams;
    },
    clearItemsViewBlock: function(){
        this.itemsViewBlock.find('.ActiveMapListBlock-items-item').remove();
        this.itemsViewBlock.hide();
    },
    decorateItem: function(data){
        var _this = this;
        var block = this.itemsViewBlockTpl.clone();
        var img_url;
        block.removeClass('ActiveMapListBlock-tpl');
        
        block.find('.ActiveMapListBlock-item-link').attr('href', data.href);
        block.find('.ActiveMapListBlock-item-title').text(data.type_sh);
        block.find('.ActiveMapListBlock-item-price').text(data.price);
        block.find('.ActiveMapListBlock-item-address').text(data.city);
        if(typeof data.image != 'undefined' && typeof data.image !== false && data.image.length > 0){
            if (data.image[0].remote === 'true') {
                img_url = data.image[0].preview;
            } else {
                img_url = estate_folder+'/img/data/'+data.image[0].preview;
            }

            block.find('.ActiveMapListBlock-item-image img').attr('src', img_url);
        } else if (data.image_cache !== null && typeof data.image_cache != 'undefined' && typeof data.image_cache !== false && data.image_cache.length > 0) {
            block.find('.ActiveMapListBlock-item-image img').attr('src', data.image_cache[0]);
        }
        
        return block;
    },
    showItems: function(ids){
        this.clearItemsViewBlock();
        var _this = this;
        $.ajax({
            url: estate_folder+'/js/ajax.php',
            data: {action: 'map_search_items', ids: ids},
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json.length > 0){
                    for(var i in json){
                        _this.itemsViewBlock.find('.ActiveMapListBlock-items-root').append(_this.decorateItem(json[i]));
                    }
                    _this.itemsViewBlock.show();
                }
                
            }
        });
    },
    listingPagerDecorate: function(json){
        if(this.pagerDecorator){
            return this.pagerDecorator(json);
        }
        var _this = this;
        var pgw = $('<div class="pagination-main"></div>');
        var pg = $('<ul class="pagination"></ul>');

        var currentpage = json.paging.current_page;
        var maxshowedpages = 11;
        if(currentpage == 0){
            currentpage = 1;
        }
                                
        if(maxshowedpages > json.paging.total_pages){
            var left = 1;
            var right = json.paging.total_pages;
        }else{
            var sideshowedpages = (maxshowedpages - 1)/2;
            var left = currentpage - sideshowedpages;
            var right = currentpage + sideshowedpages;
            if(left < 1){

                right = maxshowedpages;
                left = 1;
            }
            if(right > json.paging.total_pages){
                right = json.paging.total_pages;
            }
        }
                                
        var startpage = maxshowedpages - currentpage;


        for(var i in json.paging.pages){
            if(json.paging.pages[i].text >= left && json.paging.pages[i].text <= right){
                if(json.paging.pages[i].current == 1 || json.paging.pages[i].text == currentpage){
                    var x = $('<li class="active"><a href="#">'+json.paging.pages[i].text+'</a></li>');
                }else{
                    var x = $('<li><a href="#">'+json.paging.pages[i].text+'</a></li>');
                }
                x.click(function(e){
                    e.preventDefault();
                    search_params.page = $(this).text();
                    _this.reloadListingData();
                });
                pg.append(x);
            }
        }
        pgw.append(pg);
        return pgw;
    },
    reloadListingData: function(all){
        if(this.listing === null){
            return;
        }
        /*if(!this.hasListing()){
            return;
        }*/
        var Listing = this.listing;
        
        Listing.append($('<div class="shadeloader"></div>'));
        
        //var ListingPager = $('#ActiveMapListingPager');
        
        var ListingPager = this.listingpager;
        $.each(ListingPager, function(){
            this.html('');
        });
        
                
        
        if(all !== true){
            all = false;
        }
        var me = this.mapEngine();
        var bounds = me.getMapBounds();
        
        
        var params={};
        params = search_params;
        params = this.getSearchParams();
        
        search_params.page = 1;
        
        params.action = 'map_search_listing';
        
        var data = {};
        data.action = 'map_search_listing';
        data.params = params;
        if(!all && this.refreshListingWithPolyOnly && this.currentPolygoneCoordinates.length == 0){
			data.all = 1;
			params.all = 1;
		}else if(!all){
			
			data.polylineString = this.currentPolygoneCoordinates;
			data.bounds = bounds;
			
			params.polylineString = this.currentPolygoneCoordinates;
			params.bounds = bounds;
		}else{
			data.all = 1;
			params.all = 1;
		}
        
        var _this = this;
        $.ajax({
            url: estate_folder+'/js/ajax.php',
            data: params,
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json){
                    if(typeof json == 'object' && json.status == 1){
                        if(json.msg != ''){
                            //console.log(json.msg);
                        }
                        
                        if(typeof json.listing != 'undefined'){
                            Listing.html(json.listing);
                        }else{
                            Listing.html('');
                        }
                        Listing.find('.shadeloader').remove();
                        
                        if(_this.countnotify){
                            _this.countnotify.text(json.total);
                        }
                        
                        if( typeof json.paging.pages != 'undefined'){
                            if(json.paging.total_pages > 1){
                                var pgw = _this.listingPagerDecorate(json)
                                
                                $.each(ListingPager, function(){
                                    var pp = pgw.clone(true);
                                    this.append(pp);
                                });
                                pgw.remove();
                                //ListingPager.append(pgw);
                            }else{
                                
                            }
                        }
                    }else{
                        //alert(json);
                    }
                }else{

                }
                
            }
        });
    },
    reloadMapData: function(all){
        
        if(all !== true){
            all = false;
        }
        var me = this.mapEngine();
        var bounds = me.getMapBounds();
        
        
        var params={};
        params = search_params;
        params = this.getSearchParams();
        
        var data = {};
        data.action = 'map_search';
        params.action = 'map_search';
        data.params = params;
        if(!all){
            data.polylineString = this.currentPolygoneCoordinates;
            data.bounds = bounds;
            params.polylineString = this.currentPolygoneCoordinates;
            params.bounds = bounds;
        }else{
            data.all = 1;
            params.all = 1;
        }
        
        var _this = this;
        $.ajax({
            url: estate_folder+'/js/ajax.php',
            data: params,
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json){
                    if(typeof json == 'object' && json.status == 1){
                        if(json.msg != ''){
                            //console.log(json.msg);
                        }
                        var ms=[];
                                //.//console.log(1);
                        me.clearMap();
                        for(var i in json.data){
                            var lat=json.data[i].geo_lat;
                            var lng=json.data[i].geo_lng;
                            var ind=String(lat+'_'+lng);
                            if(lat!==null && lng!==null){
                                if(typeof ms[ind] === 'undefined'){
                                    ms[ind]={};
                                    ms[ind].lat = lat;
                                    ms[ind].lng = lng;
                                    ms[ind].propertyIds = [];
                                }
                                ms[ind].propertyIds.push(json.data[i].id);
                            }
                        }
                        if(Object.keys(ms).length==0){
                            return;
                        }   
                        
                        me.drawMarkers(ms, all);
                        _this.catchEvents = true;
                    }else{
                        //alert(json);
                    }
                }else{

                }
                
            }
        });
    },
    clearDrawedPolygon: function(){        
        this.mapEngine().clearDrawedPolygon();
    },
    initMapStartParameters: function(){        
        this.mapContainerTopOffest = this.mapContainer.offset().top;
        this.mapContainerLeftOffest = this.mapContainer.offset().left;
    },
    initDrawMode: function(p){
        this.isMapInDrawMode = true;
        
        this.controlsContainer.addClass('draw');
        this.clearDrawedPolygon();
        
        var line = [];
            
        this.controlsDraw.prop('disabled', true);
        this.controlsDraw.addClass('active');
        var _this = this;

        this.drawLineOverMap().then(function(coordinates) {
            var line = [];

            for(var i in coordinates){
                line.push({
                    x: coordinates[i][0],
                    y: coordinates[i][1]
                });
            }

            line = simplify(line, 1);
            coordinates = [];
            for(var i in line){
                coordinates.push([
                    line[i].x,
                    line[i].y
                ]);
            }

            var bounds = _this.mapEngine().getMapBounds();                

            var canvas = _this.canvas;
            coordinates = coordinates.map(function(x) {
                return [
                    Number((bounds[0][0] + (1 - x[1] / canvas.height) * (bounds[1][0] - bounds[0][0])).toFixed(6)),
                    Number((bounds[0][1] + x[0] / canvas.width * (bounds[1][1] - bounds[0][1])).toFixed(6)),
                ];
            });                

            var polygon = _this.mapEngine().drawPolygon(coordinates);

            _this.controlsDraw.prop('disabled', false);
            _this.controlsDraw.removeClass('active');

            _this.drawedPolygone = polygon;
            _this.currentPolygoneCoordinates = coordinates;
            _this.reloadMapData();
            _this.reloadListingData();
            _this.isMapInDrawMode = false;
            _this.controlsContainer.removeClass('draw');
        });
            
    },
    buildCanvas: function(width, height){
        var canvas = document.querySelector('#ActiveMapCanvas');
        var ctx2d = canvas.getContext('2d');
        
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        canvas.width = width;
        canvas.height = height;
        
        ctx2d.strokeStyle = this.options.canvasOptions.strokeStyle;
        ctx2d.lineWidth = this.options.canvasOptions.lineWidth;
        canvas.style.opacity = this.options.canvasOptions.opacity;

        ctx2d.clearRect(0, 0, canvas.width, canvas.height);

        canvas.style.display = 'block';
        this.canvas = canvas;
        this.ctx2d = ctx2d;
    },
    drawLineOverMap: function() {
        
        var drawing = false;
        var coordinates = [];  

        // Задаем размеры канвасу как у карты.
        var rect = this.mapEngine().getMapRectDimension();
        this.buildCanvas(rect.width, rect.height);
        var canvas = this.canvas
        var ctx2d = this.ctx2d;
        var _this = this;        
        
        canvas.onmousedown = function(e) {
            // При нажатии мыши запоминаем, что мы начали рисовать и координаты.
            var mapTopOffset = _this.mapContainer.offset().top;
            drawing = true;
            var x = e.pageX - _this.mapContainerLeftOffest - e.target.offsetLeft;
            var y = e.pageY - mapTopOffset - e.target.offsetTop;
            coordinates.push([x, y]);   
        };
        
        //var el = document.getElementsByTagName("canvas")[0];
        
        
        /*var handleStart = function(e) {
            e.preventDefault();
            drawing = true;
            var touch = e.changedTouches[0];
            var x = touch.pageX - _this.mapContainerLeftOffest - touch.target.offsetLeft;
            var y = touch.pageY - _this.mapContainerTopOffest - touch.target.offsetTop;
            coordinates.push([x, y]);  
            console.log("touchstart.");
        };*/
        
        /*var handleEnd = function(e) {
            e.preventDefault();
            coordinates.push([e.offsetX, e.offsetY]);
                canvas.style.display = 'none';
                drawing = false;
                resolve(coordinates);
            console.log("handleEnd.");
        };*/
        
        /*var handleCancel = function(evt) {
            evt.preventDefault();
            console.log("handleCancel.");
        };*/
        
        /*var handleMove = function(e) {
            e.preventDefault();
            console.log(e);
            if (drawing) {
                var last = coordinates[coordinates.length - 1];
                ctx2d.beginPath();
                ctx2d.moveTo(last[0], last[1]);
                
                var touch = e.changedTouches[0];
                
                ctx2d.lineTo(touch.pageX - _this.mapContainerLeftOffest, touch.pageY - _this.mapContainerTopOffest);
                ctx2d.stroke();
                var x = touch.pageX - _this.mapContainerLeftOffest - touch.target.offsetLeft;
                var y = touch.pageY - _this.mapContainerTopOffest - touch.target.offsetTop;
                coordinates.push([x, y]);
            }
            console.log("handleMove.");
        };*/
        
        /*canvas.addEventListener("touchstart", handleStart, false);
        
        canvas.addEventListener("touchcancel", handleCancel, false);
        canvas.addEventListener("touchmove", handleMove, false);
        console.log("initialized.");*/

        canvas.onmousemove = function(e) {
            // При движении мыши запоминаем координаты и рисуем линию.
            if (drawing) {
                var mapTopOffset = _this.mapContainer.offset().top;
                var last = coordinates[coordinates.length - 1];
                ctx2d.beginPath();
                ctx2d.moveTo(last[0], last[1]);
                ctx2d.lineTo(e.pageX - _this.mapContainerLeftOffest, e.pageY - mapTopOffset);
                ctx2d.stroke();
                var x = e.pageX - _this.mapContainerLeftOffest - e.target.offsetLeft;
                var y = e.pageY - mapTopOffset - e.target.offsetTop;
                coordinates.push([x, y]);
            }
        };
        
        canvas.ontouchstart = function(e) {
            e.preventDefault();
            drawing = true;
            var touch = e.changedTouches[0];
            var x = touch.pageX - _this.mapContainerLeftOffest - touch.target.offsetLeft;
            var y = touch.pageY - _this.mapContainerTopOffest - touch.target.offsetTop;
            coordinates.push([x, y]);
            console.log("touchstart.");
        };


        canvas.ontouchend = function(e) {
			$(canvas).mouseup();
        };

        canvas.ontouchcancel = function(evt) {
            evt.preventDefault();
            console.log("handleCancel.");
        };

        canvas.ontouchmove = function(e) {
            e.preventDefault();
            if (drawing) {
                var last = coordinates[coordinates.length - 1];
                ctx2d.beginPath();
                ctx2d.moveTo(last[0], last[1]);

                var touch = e.changedTouches[0];

                ctx2d.lineTo(touch.pageX - _this.mapContainerLeftOffest, touch.pageY - _this.mapContainerTopOffest);
                ctx2d.stroke();
                var x = touch.pageX - _this.mapContainerLeftOffest - touch.target.offsetLeft;
                var y = touch.pageY - _this.mapContainerTopOffest - touch.target.offsetTop;
                coordinates.push([x, y]);
            }
            console.log("handleMove.");
        };
        

        return new Promise(function(resolve) {
            canvas.onmouseup = function(e) {
                //coordinates.push([e.offsetX, e.offsetY]);
                canvas.style.display = 'none';
                drawing = false;
                resolve(coordinates);
            };
            /*canvas.touchend = function(e) {
                coordinates.push([e.offsetX, e.offsetY]);
                canvas.style.display = 'none';
                drawing = false;
                resolve(coordinates);
            };*/
        });
    },
    initEngine: function(){        
        if(this.provider == 'google'){
            this.mapEngine = this.initMapEngineGoogle();
        }
        if(this.provider == 'yandex'){
            this.mapEngine = this.initMapEngineYandex();
        }
        if(this.provider == 'leaflet_osm'){
            this.mapEngine = this.initMapEngineLeafletOSM();
        }
    },
    initMapEngineLeafletOSM: function(){
        var _this = this;
        return function() {
            return {
                parent: _this,
                drawPolygon: function(coordinates){
                    var gCoords = [];
                    for(var ic in coordinates){
                        gCoords.push([coordinates[ic][0], coordinates[ic][1]]);
                    }
                    
                    var pOptions = {
                        strokeColor: this.parent.options.polygonOptions.strokeColor,
                        strokeOpacity: this.parent.options.polygonOptions.strokeOpacity,
                        strokeWeight: this.parent.options.polygonOptions.strokeWidth,
                        fillColor: this.parent.options.polygonOptions.fillColor,
                        fillOpacity: this.parent.options.polygonOptions.fillOpacity
                    }
                    
                    var polygon = L.polygon(gCoords, pOptions);
    
                    polygon.addTo(this.parent.map);

                    var paths = polygon.getLatLngs();
                    var bounds = L.latLngBounds();
                    paths.forEach(function(path) {
                        bounds.extend(path);
                    });            
                
                    this.parent.map.fitBounds(bounds);
                    return polygon;
                },
                getMapRectDimension: function(){
                    var dim = this.parent.map.getSize();
                    return {width: dim.x, height: dim.y};
                },
                drawMarkers: function(markers, centered){
                    if(centered !== true){
                        centered = false;
                    }
                    
                    if(centered){
                        var bounds = L.latLngBounds();
                    }
                    
                    var m = this.parent.map;
                    var p = this.parent;
                    
                    if(p.clusterer !== null && p.markers.length > 0){
                        p.clusterer.removeLayers(p.markers);
                    }
                    
                    for(var i in markers){
                        
                        var latlng = [markers[i].lat, markers[i].lng];
                        
                        var marker = L.marker(latlng, {ids: markers[i].propertyIds})/*.addTo(this.parent.map)*/;
                        marker.options.ids = markers[i].propertyIds;
                        
                        if(centered){
                            bounds.extend(latlng);
                        }
                        
                        marker.on('click', function() {
                            p.showItems(this.options.ids);
                        });
                        this.parent.markers.push(marker);
                        
                    }
                    
                    this.parent.clusterer.addLayers(this.parent.markers);
                    
                    if(centered){
                        this.parent.map.fitBounds(bounds);
                    }
                    
                },
                clearMap: function(){
                    if(this.parent.markers.length > 0){
                        this.parent.clusterer.removeLayers(this.parent.markers);
                        this.parent.markers = [];
                    }
                },
                getMapBounds: function(){
                    var _bounds = this.parent.map.getBounds();
                    return [[_bounds.getSouthWest().lat, _bounds.getSouthWest().lng], [_bounds.getNorthEast().lat, _bounds.getNorthEast().lng]];
                },
                buildMap: function(){
                    var m = this.parent.mapContainer;
                    var centerlat = this.parent.mapdefaults.center.lat;
                    var centerlng = this.parent.mapdefaults.center.lng;
                    var zoom = this.parent.mapdefaults.zoom;
                    var latlng = [centerlat, centerlng];
                    
                    this.parent.map = L.map(document.getElementById("ActiveMap")).setView(latlng, zoom);
                    this.parent.map.addLayer(new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'}));
                    this.parent.clusterer = L.markerClusterGroup();
                    
                    var parent = this.parent;
                    var _map = this.parent.map;
                    
                    this.parent.map.on('load', function(ev) {
                        console.log('load');
                        parent.reloadMapData(true);
                        parent.reloadListingData(true);
                        this.parent.map.off('load', function(){});
                    });
                    if(!parent.nomapbehaviors){
                        this.parent.map.on('moveend', function(ev) {
                            if(parent.currentPolygoneCoordinates.length == 0 && !parent.isMapInDrawMode && parent.catchEvents){
                                parent.reloadMapData();
                                parent.reloadListingData();
                            }
                            if(!parent.catchEvents){
                                parent.catchEvents = true;
                            }
                        });
                        /*this.parent.map.on('zoomend', function(ev) {
                            console.log('zoomend');

                            if(parent.currentPolygoneCoordinates.length == 0 && !parent.isMapInDrawMode && parent.catchEvents){
                                parent.reloadMapData();
                                parent.reloadListingData();
                            }
                            if(!parent.catchEvents){
                                parent.catchEvents = true;
                            }
                        });*/
                    }
                    this.parent.map.addLayer(this.parent.clusterer);
                },
                clearDrawedPolygon(){
                    if (this.parent.drawedPolygone !== null) {
                        this.parent.drawedPolygone.remove();
                        this.parent.drawedPolygone = null;
                    }
                    this.parent.currentPolygoneCoordinates = [];
                }
            };
            
        }
    },
    initMapEngineGoogle: function(){
        var _this = this;
        return function() {
            return {
                parent: _this,
                drawPolygon: function(coordinates){
                    var gCoords = [];
                    for(var ic in coordinates){
                        gCoords.push({lat: coordinates[ic][0], lng: coordinates[ic][1]});
                    }    
    
                    var polygon = new google.maps.Polygon({
                        paths: gCoords,
                        strokeColor: this.parent.options.polygonOptions.strokeColor,
                        strokeOpacity: this.parent.options.polygonOptions.strokeOpacity,
                        strokeWeight: this.parent.options.polygonOptions.strokeWidth,
                        fillColor: this.parent.options.polygonOptions.fillColor,
                        fillOpacity: this.parent.options.polygonOptions.fillOpacity
                    });
                    polygon.setMap(this.parent.map);

                    var paths = polygon.getPaths();
                    var bounds = new google.maps.LatLngBounds();
                    paths.forEach(function(path) {
                        var ar = path.getArray();
                        for(var i = 0, l = ar.length;i < l; i++) {
                            bounds.extend(ar[i]);
                        }
                    });            
                
                    this.parent.map.fitBounds(bounds);
                    return polygon;
                },
                getMapRectDimension: function(){
                    return this.parent.map.getDiv().getBoundingClientRect();
                },
                drawMarkers: function(markers, centered){
                    if(centered !== true){
                        centered = false;
                    }
                    
                    if(centered){
                        var bounds = new google.maps.LatLngBounds();
                    }
                    
                    var m = this.parent.map;
                    var p = this.parent;
                    
                    if(p.clusterer !== null){
                        p.clusterer.clearMarkers(); 
                    }
                    
                    for(var i in markers){
                        
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(markers[i].lat, markers[i].lng),
                            map: this.parent.map,
                            icon: '',
                            ids: markers[i].propertyIds
                        });
                        if(centered){
                            bounds.extend(new google.maps.LatLng(markers[i].lat, markers[i].lng));
                        }
                        
                        marker.addListener('click', function() {
                            p.showItems(this.ids);
                        });
                        this.parent.markers.push(marker);
                    }
                    
                    this.parent.clusterer.addMarkers(this.parent.markers);
                    
                    if(centered){
                        this.parent.map.fitBounds(bounds);
                    }
                    
                },
                clearMap: function(){
                    
                    if(this.parent.markers.length > 0){
                        this.parent.markers.map(function(m){m.setMap(null)});
                        this.parent.markers = [];
                        this.parent.clusterer.clearMarkers();
                    }
                },
                getMapBounds: function(){
                    
                    var _bounds = this.parent.map.getBounds();
                    return [[_bounds.getSouthWest().lat(), _bounds.getSouthWest().lng()], [_bounds.getNorthEast().lat(), _bounds.getNorthEast().lng()]];
                },
                buildMap: function(){
                    var m = this.parent.mapContainer;
                    var centerlat = this.parent.mapdefaults.center.lat;
                    var centerlng = this.parent.mapdefaults.center.lng;
                    var zoom = this.parent.mapdefaults.zoom;
                    var latlng = new google.maps.LatLng(centerlat, centerlng);
                    var mapOptions = {
                        zoom: zoom,
                        center: latlng,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    };
                    if(window.innerWidth >= 600){
                        mapOptions.scrollwheel = true;
                    }
                    this.parent.map = new google.maps.Map(document.getElementById("ActiveMap"), mapOptions);      
                    var parent = this.parent;
                    var _map = this.parent.map;
                    google.maps.event.addListener(this.parent.map, 'tilesloaded', function(evt) {

                        parent.reloadMapData(true);
                        parent.reloadListingData(true);
                        

                        
                        google.maps.event.clearListeners(_map, 'tilesloaded');
                    });
                    if(!parent.nomapbehaviors){
                        google.maps.event.addListener(_map, 'dragend', function(evt) {
                            if(parent.currentPolygoneCoordinates.length == 0 && !parent.isMapInDrawMode && parent.catchEvents){
                                parent.reloadMapData();
                                parent.reloadListingData();
                            }
                        });
                        google.maps.event.addListener(_map, 'zoom_changed', function(evt) {
                            if(parent.currentPolygoneCoordinates.length == 0 && !parent.isMapInDrawMode && parent.catchEvents){
                                parent.reloadMapData();
                                parent.reloadListingData();
                            }
                            if(!parent.catchEvents){
                                parent.catchEvents = true;
                            }
                        });
                    }
                    
                    this.parent.clusterer = new MarkerClusterer(this.parent.map, [], {gridSize: 50, maxZoom: 15, imagePath: estate_folder+'/apps/third/google/markerclusterer/images/m'});
                    /*google.maps.event.addListener(this.parent.clusterer, 'clusterclick', function(evt) {
                        return;
                    });*/
                },
                clearDrawedPolygon(){
                    //console.log(this.parent);
                    if (this.parent.drawedPolygone !== null) {
                        this.parent.drawedPolygone.setMap(null);
                        this.parent.drawedPolygone = null;
                    }
                    this.parent.currentPolygoneCoordinates = [];
                }
            };
            
        }
    },
    initMapEngineYandex: function(){
        var _this = this;
        return function() {
            return {
                parent: _this,
                drawPolygon: function(coordinates){
                    var polygon = new ymaps.Polygon([coordinates], {}, this.parent.options.polygonOptions);
                    this.parent.map.geoObjects.add(polygon);
                    this.parent.map.setBounds(polygon.geometry.getBounds());
                    return polygon;
                },
                getMapRectDimension: function(){
                    return this.parent.map.container.getParentElement().getBoundingClientRect();
                },
                drawMarkers: function(markers, centered){
                    if(centered !== true){
                        centered = false;
                    }
                    var p = this.parent;
                    
                    if(this.parent.clusterer !== null){
                        this.parent.clusterer.removeAll();
                    }
                
                    for(var i in markers){

                        if(markers[i].lat >= -180 && markers[i].lat <= 180 && markers[i].lng >= -90 && markers[i].lng <= 90){
                            //Проверка на качество координат
                        }


                        var baloonContent={
                            iconContent: ''
                        }
                        var placemark = new ymaps.Placemark(
                            [markers[i].lat, markers[i].lng],
                            baloonContent,
                            {
                                hasBalloon: false,
                                draggable: false,
                                hideIconOnBalloonOpen: true,
                                iconLayout: 'default#image',
                                ids: markers[i].propertyIds
                            }
                        );
                
                        placemark.events.add('click', function (e) {
                            p.showItems(e.get('target').options.get('ids'));
                        });
                        this.parent.map.geoObjects.add(placemark);        
						this.parent.markers.push(placemark);
                        
                        
					}
                    
                    this.parent.clusterer.add(this.parent.markers);
                    this.parent.map.geoObjects.add(this.parent.clusterer);
                
                    if(centered){
                        this.parent.map.setBounds(this.parent.map.geoObjects.getBounds());
                    }
                    
                },
                clearMap: function(){
                    if(this.parent.markers.length > 0){
                        var _thismap = this.parent.map;
                        this.parent.markers.map(function(m){_thismap.geoObjects.remove(m);});
                        this.parent.markers = [];
                        this.parent.clusterer.removeAll();
                    }
                },
                getMapBounds: function(){
                    return this.parent.map.getBounds();
                },
                buildMap: function(){
                    var m = this.parent.mapContainer;
                    var centerlat = this.parent.mapdefaults.center.lat;
                    var centerlng = this.parent.mapdefaults.center.lng;
                    var zoom = this.parent.mapdefaults.zoom;
                    this.parent.map = new ymaps.Map('ActiveMap', { center: [centerlat, centerlng], zoom: zoom });
                    if(window.innerWidth < 600){
                        this.parent.map.behaviors.disable('drag');
                    }
                    
                    var _map = this.parent.map;
                    var parent = this.parent;
                    
                    this.parent.clusterer = new ymaps.Clusterer({
                        preset: 'islands#invertedVioletClusterIcons',
                        groupByCoordinates: false,
                        clusterDisableClickZoom: false,
                        clusterHideIconOnBalloonOpen: false,
                        geoObjectHideIconOnBalloonOpen: false
                    });
                    
                    parent.reloadMapData(true);
                    parent.reloadListingData(true);

                    if(!parent.nomapbehaviors){
                        this.parent.map.events.add('boundschange', function (e) {
                            if(parent.currentPolygoneCoordinates.length == 0 && !parent.isMapInDrawMode && parent.catchEvents){
                                parent.reloadMapData();
                                parent.reloadListingData();
                            }
                        });
                        this.parent.map.events.add('zoom_changed', function (e) {
                            if(parent.currentPolygoneCoordinates.length == 0 && !parent.isMapInDrawMode && parent.catchEvents){
                                parent.reloadMapData();
                                parent.reloadListingData();
                            }
                        });
                    }
                    
                },
                clearDrawedPolygon(){
                    //console.log(this.parent);
                    if (this.parent.drawedPolygone !== null) {
                        this.parent.map.geoObjects.remove(this.parent.drawedPolygone)
                        this.parent.drawedPolygone = null;
                    }
                    this.parent.currentPolygoneCoordinates = [];
                }
            };
            
        }
    }    
}
