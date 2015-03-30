/* Japanese initialisation for the jQuery UI date picker plugin. */
/* Written by Kentaro SATO (kentaro@ranvis.com). */
jQuery(function($){
	$.datepicker.regional['ja'] = {
		closeText: 'й–‰гЃ�г‚‹',
		prevText: '&#x3c;е‰Ќ',
		nextText: 'ж¬Ў&#x3e;',
		currentText: 'д»Љж—Ґ',
		monthNames: ['1жњ€','2жњ€','3жњ€','4жњ€','5жњ€','6жњ€',
		'7жњ€','8жњ€','9жњ€','10жњ€','11жњ€','12жњ€'],
		monthNamesShort: ['1жњ€','2жњ€','3жњ€','4жњ€','5жњ€','6жњ€',
		'7жњ€','8жњ€','9жњ€','10жњ€','11жњ€','12жњ€'],
		dayNames: ['ж—Ґж›њж—Ґ','жњ€ж›њж—Ґ','зЃ«ж›њж—Ґ','ж°ґж›њж—Ґ','жњЁж›њж—Ґ','й‡‘ж›њж—Ґ','ењџж›њж—Ґ'],
		dayNamesShort: ['ж—Ґ','жњ€','зЃ«','ж°ґ','жњЁ','й‡‘','ењџ'],
		dayNamesMin: ['ж—Ґ','жњ€','зЃ«','ж°ґ','жњЁ','й‡‘','ењџ'],
		weekHeader: 'йЂ±',
		dateFormat: 'yy/mm/dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'е№ґ'};
	$.datepicker.setDefaults($.datepicker.regional['ja']);
});