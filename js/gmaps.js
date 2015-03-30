
var mui = {
	colors:{
		base:"#EDF4FA",
		border:"#777",
		hotborder:"#111"
	}
}

/* Для наследования класса. Копирует прототип*/
function extend(Child, Parent) {
	var F = function() { }
	F.prototype = Parent.prototype
	Child.prototype = new F()
	Child.prototype.constructor = Child
	Child.superclass = Parent.prototype
}

/* Расширенный класс google.maps.Marker
 * Хранит информацию об участке в маркере
 *
 *	@tag 			- идентификационный номер
 *	@square			- площадь
 *
 *	@setTag(a)		- установить идентификационный номер
 *	@getTag()		- получить идентификационный номер
 *	@setSquare(a)	- установить площадь
 *	@getSquare()	- получить площадь
*/
function markerPlot(a){
	google.maps.Marker.call(this,a);
	
	this.tag = null;	
	this.setTag = function(a)
	{
		this.tag = a;
	}
	this.getTag = function(){
		return this.tag;
	}
	
	this.square = null;	
	this.setSquare = function(a)
	{
		this.square = a;
	}
	this.getSquare = function(){
		return this.square;
	}

	this.link = null;	
	this.setLink = function(a)
	{
		this.link = a;
	}
	this.getLink = function(){
		return this.link;
	}

	this.number = null;	
	this.setNumber = function(a)
	{
		this.number = a;
	}
	this.getNumber = function(){
		return this.number;
	}
	
	this.price = null;	
	this.setPrice = function(a)
	{
		this.price = a;
	}
	this.getPrice = function(){
		return this.price;
	}
	
	this.price_per_unit = null;	
	this.setPricePerUnit = function(a)
	{
		this.price_per_unit = a;
	}
	this.getPricePerUnit = function(){
		return this.price_per_unit;
	}
	
	this.function_land = null;	
	this.setFunction = function(a)
	{
		this.function_land = a;
	}
	this.getFunction = function(){
		return this.function_land;
	}
	
	
	this.location = null;	
	this.setLocation = function(a)
	{
		this.location = a;
	}
	this.getLocation = function(){
		return this.location;
	}
	
	
	
}
/* копируем стандартные поля и методы в прототип*/
extend(markerPlot,google.maps.Marker);

/* Клиентские переменные */
var map;


var boxText = document.createElement("div");
        boxText.style.cssText = "border: 1px solid black; margin-top: 8px; background: yellow; padding: 5px;";
        boxText.innerHTML = "City Hall, Sechelt<br>British Columbia<br>Canada";
var infowindow = new InfoBox({
                 content: boxText
                ,disableAutoPan: false
                ,maxWidth: 0
                ,pixelOffset: new google.maps.Size(-140, 0)
                ,zIndex: null
                ,boxStyle: {
					opacity: 1
					,width: "200px"
                 }
                ,closeBoxMargin: ""
                ,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
                ,infoBoxClearance: new google.maps.Size(1, 1)
                ,isHidden: false
                ,pane: "floatPane"
                ,enableEventPropagation: false,
				alignBottom:true
        });

/* стилизированная полилиния
 * используется только в админке при построении контура полигона
*/
function rMyPolyline(path)
{
	return new google.maps.Polyline({
	   path: path,
	   strokeColor: "#FF0000",
	   strokeOpacity: 0.6,
	   strokeWeight: 1,
	   clickable:false
	});
}

/* стилизированный полигон
*/
function rMyPoligon(path)
{
	return new google.maps.Polygon({
		paths: path,
		strokeColor: mui.colors.border,
		strokeOpacity: 0.8,
		strokeWeight: 1,
		fillColor: mui.colors.base,
		fillOpacity: 0.35,
		clickable:false
	});
}

/* маркер управления полигоном в админке
 * используется только в админке
*/
function rMyControl_circle(position)
{
	return new google.maps.Marker({
		position: position,
		map: map,
		icon:
			new google.maps.MarkerImage
			(
				'/img/crstart.png',
				new google.maps.Size(6,6),
				new google.maps.Point(0,0),
				new google.maps.Point(3,3)
			)
	});
}

/* маркер участка
 * используется только на клиенте
*/
function rMarkerPlotabs(opts)
{
	var a = new markerPlot({
		position: opts.position,
		map: map,
		icon:
			new google.maps.MarkerImage
			(
				'/img/baloon.png',
				new google.maps.Size(16,16),
				new google.maps.Point(0,0),
				new google.maps.Point(8,15)
			)
	});
	a.setTag(opts.index);
	a.setSquare(opts.square);
	a.setLink(opts.link);
	a.setNumber(opts.number);
	a.setPrice(opts.price);
	a.setPricePerUnit(opts.price_per_unit);
	a.setFunction(opts.function_land);
	a.setLocation(opts.location);
	return a;
}

/* содержимое infowindow на общей карте участков
*/
function genPlotInfo(a)
{
	return '<div class="plotInfoBln"><a href="' + a.getLink() + '" style="text-decoration: none;">' +	a.getNumber() + ' ' +
	 + a.getFunction() + ' ' + a.getSquare()+ ' Га ' + ' <b>' + a.getPrice() + '</b> р.'+
	'</a></div>'+
	'<div style="background:url(/img/gm_ballon_con.png) 140px bottom no-repeat; height:17px; position:relative; top:-1px;"></div>';
}

function initMap(map_type)
{
	/*map = new google.maps.Map(
		document.getElementById("map_canvas"),
		{			
			mapTypeId: google.maps.MapTypeId.TERRAIN
		}		
	);
	*/
	if ( map_type == 'SATELLITE' ) {
	       var myOptions = {
	               zoom: 8,
	               center: new google.maps.LatLng(55.748758, 37.6474),
	               mapTypeId: google.maps.MapTypeId.HYBRID
	       };
		
	} else {
	       var myOptions = {
	               zoom: 8,
	               center: new google.maps.LatLng(55.748758, 37.6474),
	               mapTypeId: google.maps.MapTypeId.TERRAIN
	       };
	}
       map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
}


/* 
 * принимает координаты типа [lat,lng,lat,lng,...]
 * возвращает координаты ограничивающего бокса в виде [lat,lng,lat,lng] той же ориентированности что и LatLngBounds
 *
 * при параметре fitting область карты приближается к боксу (без анимации)
*/
function fitBounds_atLatLngs(arr,fitting)
{
	var fitL = arr[0], fitR = arr[0], fitT = arr[1], fitB = arr[1], i;
	for (i=1;i<arr.length/2;i++)
	{
		if(arr[2*i] < fitL) fitL = arr[2*i];
		if(arr[2*i] > fitR) fitR = arr[2*i];
		if(arr[2*i+1] > fitT) fitT = arr[2*i+1];
		if(arr[2*i+1] < fitB) fitB = arr[2*i+1];
	}
	var a = new google.maps.LatLng(fitL,fitB),
		b = new google.maps.LatLng(fitR,fitT);
	if(fitting) map.fitBounds(	new google.maps.LatLngBounds(a,b)	);
	return[fitL,fitB,fitR,fitT];
}

/*FRONT*/
/*http://maps.gstatic.com/mapfiles/red_transparent_icons_A_J.png*/


/* функция отображения участков */
var show_plots_array = [];
function showPlots(options)
{
	
	if(typeof map != 'object') initMap(map_type);
	if (options.length)
	{
		var t = 0 , ft = [],i, item = [];
		for(i in options)
		{
			showPlot(options[i]);
			$.merge(ft,fitBounds_atLatLngs(options[i].coord));
			var marker = rMarkerPlotabs({
				position: new google.maps.LatLng(options[i].coord[0],options[i].coord[1]),
				tag:i,
				square:options[i].square,
				number:options[i].number,
				price:options[i].price,
				price_per_unit:options[i].price_per_unit,
				location:options[i].location,
				function_land:options[i].function_land,
				link:options[i].link
			});
			//marker.setTag(i);
			google.maps.event.addListener(marker,'mouseover',function(){
				infowindow.setPosition(this.getPosition());
				infowindow.setContent(genPlotInfo(this));
				infowindow.open(map);
			});
			
			//infowindow.open(map,item[i]);
			//alert(item[i]);
		}
		fitBounds_atLatLngs(ft,true);
	}else{
		showPlot(options,true);
		fitBounds_atLatLngs(options.coord,true);
	}
}

/* рисует полигон участка
*/
function showPlot(options)
{
	var path = [], i;
	for (i=0;i<options.coord.length/2;i++)
	{
		path.push( new google.maps.LatLng(options.coord[2*i],options.coord[2*i+1]) );
	}
	
	var gPlot = rMyPoligon(path);	
	gPlot.setMap(map);
	return gPlot;
}

/* ADMIN */
var eV_addPoligonPoint , eV_drawLastSegment;

function landPolygonizer(options)
{
	map = new google.maps.Map(
		document.getElementById("map_canvas"),
		{
			zoom: options.zoom,
			center: new google.maps.LatLng(options.center[0],options.center[1]),
			mapTypeId: google.maps.MapTypeId.TERRAIN,
			draggableCursor: 'crosshair'
		}		
	);

	eV_addPoligonPoint = google.maps.event.addListener(map, "click", add_poligonPoint);
}

var polygonCr = null;
var polygonCr_path = [];
var polygonCr_pt = new google.maps.LatLng(0,0);
var polygonCr_state = 0;
var polygonCr_start;

function add_poligonPoint(e){ 
	polygonCr_path.push( new google.maps.LatLng(e.latLng.lat(),e.latLng.lng()));
	
	if (polygonCr_state == 0)
	{	   
		polygonCr_pt = new google.maps.LatLng(e.latLng.lat(),e.latLng.lng());
	
		polygonCr = rMyPolyline($.merge( $.merge([],polygonCr_path),[polygonCr_pt]));
		polygonCr.setMap(map);
		
		eV_drawLastSegment = google.maps.event.addListener(map, "mousemove", function(e){
				polygonCr_pt = new google.maps.LatLng(e.latLng.lat(),e.latLng.lng());
				polygonCr.setPath(	$.merge( $.merge([],polygonCr_path),[polygonCr_pt])	);
		});
		
		polygonCr_start = rMyControl_circle(polygonCr_path[0]);
		google.maps.event.addListener(polygonCr_start, "click", close_poligonCr);
		
		polygonCr_state = 1;	
	}
}


function close_poligonCr(){
	
	if(polygonCr_path.length > 2)
	{	
		polygonCr_state = 2;
	
		google.maps.event.removeListener(eV_addPoligonPoint);
		google.maps.event.removeListener(eV_drawLastSegment);
		google.maps.event.clearListeners(polygonCr_start,"click");
		
		/*polygonCr.setPath(polygonCr_path);*/
		polygonCr.setMap();
		polygonCr_start.setMap()
		
		
		
		polygonCrd = rMyPoligon(polygonCr_path);
		polygonCrd.setMap(map);
		
		var tmp = [];
		for(i=0; i<polygonCr_path.length;i++)
		{
			tmp.push(polygonCr_path[i].lat());
			tmp.push(polygonCr_path[i].lng());
			
		}
		tmp.join(',');
		document.forms['landform'].elements['coord'].value = tmp;
		
		/*init_controls_Crd();*/
	}
}

var controlsCrd = [];
function init_controls_Crd(){
	for (i in polygonCr_path)
	{
		controlsCrd[i] = [];
		controlsCrd[i][1] = i;
		controlsCrd[i][0] = rMyControl_circle(polygonCr_path[i]);
		controlsCrd[i][0].setCursor('move');
		controlsCrd[i][0].setDraggable(true);
		google.maps.event.addListener(controlsCrd[i][0], "drag", function(){
		
		document.forms['landform'].elements['coord'].value = "";
		for (i in this){document.forms['landform'].elements['coord'].value += i+' '+this[i]+'\n';}
		
		});
	}

}
/*
update_Crd_verticles()
{



}

function remove_poligonPoint(e){

}*/