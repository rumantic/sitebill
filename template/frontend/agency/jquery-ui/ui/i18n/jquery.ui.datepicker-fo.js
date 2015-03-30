/* Faroese initialisation for the jQuery UI date picker plugin */
/* Written by Sverri Mohr Olsen, sverrimo@gmail.com */
jQuery(function($){
	$.datepicker.regional['fo'] = {
		closeText: 'Lat aftur',
		prevText: '&#x3c;Fyrra',
		nextText: 'NГ¦sta&#x3e;',
		currentText: 'ГЌ dag',
		monthNames: ['Januar','Februar','Mars','AprГ­l','Mei','Juni',
		'Juli','August','September','Oktober','November','Desember'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Mei','Jun',
		'Jul','Aug','Sep','Okt','Nov','Des'],
		dayNames: ['Sunnudagur','MГЎnadagur','TГЅsdagur','Mikudagur','HГіsdagur','FrГ­ggjadagur','Leyardagur'],
		dayNamesShort: ['Sun','MГЎn','TГЅs','Mik','HГіs','FrГ­','Ley'],
		dayNamesMin: ['Su','MГЎ','TГЅ','Mi','HГі','Fr','Le'],
		weekHeader: 'Vk',
		dateFormat: 'dd-mm-yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fo']);
});
