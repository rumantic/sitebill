/* Vietnamese initialisation for the jQuery UI date picker plugin. */
/* Translated by Le Thanh Huy (lthanhhuy@cit.ctu.edu.vn). */
jQuery(function($){
	$.datepicker.regional['vi'] = {
		closeText: 'ДђГіng',
		prevText: '&#x3c;TrЖ°б»›c',
		nextText: 'Tiбєїp&#x3e;',
		currentText: 'HГґm nay',
		monthNames: ['ThГЎng Mб»™t', 'ThГЎng Hai', 'ThГЎng Ba', 'ThГЎng TЖ°', 'ThГЎng NДѓm', 'ThГЎng SГЎu',
		'ThГЎng BбєЈy', 'ThГЎng TГЎm', 'ThГЎng ChГ­n', 'ThГЎng MЖ°б»ќi', 'ThГЎng MЖ°б»ќi Mб»™t', 'ThГЎng MЖ°б»ќi Hai'],
		monthNamesShort: ['ThГЎng 1', 'ThГЎng 2', 'ThГЎng 3', 'ThГЎng 4', 'ThГЎng 5', 'ThГЎng 6',
		'ThГЎng 7', 'ThГЎng 8', 'ThГЎng 9', 'ThГЎng 10', 'ThГЎng 11', 'ThГЎng 12'],
		dayNames: ['Chб»§ Nhбє­t', 'Thб»© Hai', 'Thб»© Ba', 'Thб»© TЖ°', 'Thб»© NДѓm', 'Thб»© SГЎu', 'Thб»© BбєЈy'],
		dayNamesShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
		dayNamesMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
		weekHeader: 'Tu',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['vi']);
});
