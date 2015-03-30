/* Azerbaijani (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Jamil Najafov (necefov33@gmail.com). */
jQuery(function($) {
	$.datepicker.regional['az'] = {
		closeText: 'BaДџla',
		prevText: '&#x3c;Geri',
		nextText: 'Д°rЙ™li&#x3e;',
		currentText: 'BugГјn',
		monthNames: ['Yanvar','Fevral','Mart','Aprel','May','Д°yun',
		'Д°yul','Avqust','Sentyabr','Oktyabr','Noyabr','Dekabr'],
		monthNamesShort: ['Yan','Fev','Mar','Apr','May','Д°yun',
		'Д°yul','Avq','Sen','Okt','Noy','Dek'],
		dayNames: ['Bazar','Bazar ertЙ™si','Г‡Й™rЕџЙ™nbЙ™ axЕџamД±','Г‡Й™rЕџЙ™nbЙ™','CГјmЙ™ axЕџamД±','CГјmЙ™','ЕћЙ™nbЙ™'],
		dayNamesShort: ['B','Be','Г‡a','Г‡','Ca','C','Ећ'],
		dayNamesMin: ['B','B','Г‡','РЎ','Г‡','C','Ећ'],
		weekHeader: 'Hf',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['az']);
});