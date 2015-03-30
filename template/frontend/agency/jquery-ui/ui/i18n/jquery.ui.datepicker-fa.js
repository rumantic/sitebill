/* Persian (Farsi) Translation for the jQuery UI date picker plugin. */
/* Javad Mowlanezhad -- jmowla@gmail.com */
/* Jalali calendar should supported soon! (Its implemented but I have to test it) */
jQuery(function($) {
	$.datepicker.regional['fa'] = {
		closeText: 'ШЁШіШЄЩ†',
		prevText: '&#x3C;Щ‚ШЁЩ„ЫЊ',
		nextText: 'ШЁШ№ШЇЫЊ&#x3E;',
		currentText: 'Ш§Щ…Ш±Щ€ШІ',
		monthNames: [
			'ЩЃШ±Щ€Ш±ШЇЩЉЩ†',
			'Ш§Ш±ШЇЩЉШЁЩ‡ШґШЄ',
			'Ш®Ш±ШЇШ§ШЇ',
			'ШЄЩЉШ±',
			'Щ…Ш±ШЇШ§ШЇ',
			'ШґЩ‡Ш±ЩЉЩ€Ш±',
			'Щ…Щ‡Ш±',
			'ШўШЁШ§Щ†',
			'ШўШ°Ш±',
			'ШЇЫЊ',
			'ШЁЩ‡Щ…Щ†',
			'Ш§ШіЩЃЩ†ШЇ'
		],
		monthNamesShort: ['1','2','3','4','5','6','7','8','9','10','11','12'],
		dayNames: [
			'ЩЉЪ©ШґЩ†ШЁЩ‡',
			'ШЇЩ€ШґЩ†ШЁЩ‡',
			'ШіЩ‡вЂЊШґЩ†ШЁЩ‡',
			'Ъ†Щ‡Ш§Ш±ШґЩ†ШЁЩ‡',
			'ЩѕЩ†Ш¬ШґЩ†ШЁЩ‡',
			'Ш¬Щ…Ш№Щ‡',
			'ШґЩ†ШЁЩ‡'
		],
		dayNamesShort: [
			'ЫЊ',
			'ШЇ',
			'Ші',
			'Ъ†',
			'Щѕ',
			'Ш¬', 
			'Шґ'
		],
		dayNamesMin: [
			'ЫЊ',
			'ШЇ',
			'Ші',
			'Ъ†',
			'Щѕ',
			'Ш¬', 
			'Шґ'
		],
		weekHeader: 'Щ‡ЩЃ',
		dateFormat: 'yy/mm/dd',
		firstDay: 6,
		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fa']);
});