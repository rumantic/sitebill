$(document).ready(function(){
	
	$("input.price_field").autoNumeric({aSep: ' ', vMax: '999999999999', vMin: '0'});

	
	
	
	var simple_slider=$("div.property-filter .slider");
	var simple_price_from=$("div.property-filter input.price_from");
	var simple_price_for=$("div.property-filter input.price_for");
	
    
	simple_slider.slider({
        range: true,
        min: 0,
        max: max_price,
        values: [ price_from, price_for ],
        slide: function( event, ui ) {
            if (ui.values[0]<0){
                ui.values[0] = 0;
            }
            simple_price_from.val( asMoney(ui.values[ 0 ]) );
            simple_price_for.val( asMoney(ui.values[ 1 ]) );
        }
    });
	simple_price_from.val( asMoney(simple_slider.slider( "values", 0 )) );
	simple_price_for.val( asMoney(simple_slider.slider( "values", 1 )) );
    
	simple_price_from.change(function(){
        var value1=simple_price_from.val().replace(/\D/g,'');
        var value2=simple_price_for.val().replace(/\D/g,'');
        if (value1 > max_price) { value1 = max_price; simple_price_from.val(asMoney(max_price))}
        if(parseInt(value1) > parseInt(value2)){
            value1 = value2;
            simple_price_from.val(asMoney(value1));
        }
        simple_slider.slider("values",0,value1); 
    });
	simple_price_for.change(function(){
        var value1=simple_price_from.val().replace(/\D/g,'');
        var value2=simple_price_for.val().replace(/\D/g,'');
        if (value2 > max_price) { value2 = max_price; simple_price_for.val(asMoney(max_price))}
        if(parseInt(value1) > parseInt(value2)){
             value2 = value1;
             simple_price_for.val(asMoney(value2));
        }
        simple_slider.slider("values",1,value2);
    });
	
	var extended_slider=$("div#extended_search .slider");
	var extended_price_from=$("div#extended_search input.price_from");
	var extended_price_for=$("div#extended_search input.price_for");
	
	
	
	extended_slider.slider({
        range: true,
        min: 0,
        max: max_price,
        values: [ price_from, price_for ],
        slide: function( event, ui ) {
            if (ui.values[0]<0){
                ui.values[0] = 0;
            }
            extended_price_from.val( asMoney(ui.values[ 0 ]) );
            extended_price_for.val( asMoney(ui.values[ 1 ]) );
        }
    });
	extended_price_from.val( asMoney(extended_slider.slider( "values", 0 )) );
	extended_price_for.val( asMoney(extended_slider.slider( "values", 1 )) );
    
	extended_price_from.change(function(){
        var value1=extended_price_from.val().replace(/\D/g,'');
        var value2=extended_price_for.val().replace(/\D/g,'');
        if (value1 > max_price) { value1 = max_price; extended_price_from.val(asMoney(max_price))}
        if(parseInt(value1) > parseInt(value2)){
            value1 = value2;
            extended_price_from.val(asMoney(value1));
        }
        extended_slider.slider("values",0,value1); 
    });
	extended_price_for.change(function(){
        var value1=extended_price_from.val().replace(/\D/g,'');
        var value2=extended_price_for.val().replace(/\D/g,'');
        if (value2 > max_price) { value2 = max_price; extended_price_for.val(asMoney(max_price))}
        if(parseInt(value1) > parseInt(value2)){
             value2 = value1;
             extended_price_from.val(asMoney(value2));
        }
        extended_slider.slider("values",1,value2);
    });

	$('a.search_page_toggle').click(function(){
		$('div#simple_search').slideToggle();
		$('div#extended_search').slideToggle();
		return false;
	});
});

function asMoney(number){
	return number_format(Number(number), 0, '.', ' ');
}

	function number_format (number, decimals, dec_point, thousands_sep) {
	  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	  var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec) {
		  var k = Math.pow(10, prec);
		  return '' + Math.round(n * k) / k;
		};
	  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
	  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	  if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	  }
	  if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	  }
	  return s.join(dec);
	}