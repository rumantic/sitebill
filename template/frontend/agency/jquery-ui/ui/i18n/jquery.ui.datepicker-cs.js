/* Czech initialisation for the jQuery UI date picker plugin. */
/* Written by Tomas Muller (tomas@tomas-muller.net). */
jQuery(function($){
	$.datepicker.regional['cs'] = {
		closeText: 'ZavЕ™Г­t',
		prevText: '&#x3c;DЕ™Г­ve',
		nextText: 'PozdД›ji&#x3e;',
		currentText: 'NynГ­',
		monthNames: ['leden','Гєnor','bЕ™ezen','duben','kvД›ten','ДЌerven',
        'ДЌervenec','srpen','zГЎЕ™Г­','Е™Г­jen','listopad','prosinec'],
		monthNamesShort: ['led','Гєno','bЕ™e','dub','kvД›','ДЌer',
		'ДЌvc','srp','zГЎЕ™','Е™Г­j','lis','pro'],
		dayNames: ['nedД›le', 'pondД›lГ­', 'ГєterГЅ', 'stЕ™eda', 'ДЌtvrtek', 'pГЎtek', 'sobota'],
		dayNamesShort: ['ne', 'po', 'Гєt', 'st', 'ДЌt', 'pГЎ', 'so'],
		dayNamesMin: ['ne','po','Гєt','st','ДЌt','pГЎ','so'],
		weekHeader: 'TГЅd',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['cs']);
});
