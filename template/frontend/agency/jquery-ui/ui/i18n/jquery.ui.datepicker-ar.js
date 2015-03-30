/* Arabic Translation for jQuery UI date picker plugin. */
/* Khaled Alhourani -- me@khaledalhourani.com */
/* NOTE: monthNames are the original months names and they are the Arabic names, not the new months name ЩЃШЁШ±Ш§ЩЉШ± - ЩЉЩ†Ш§ЩЉШ± and there isn't any Arabic roots for these months */
jQuery(function($){
	$.datepicker.regional['ar'] = {
		closeText: 'ШҐШєЩ„Ш§Щ‚',
		prevText: '&#x3c;Ш§Щ„ШіШ§ШЁЩ‚',
		nextText: 'Ш§Щ„ШЄШ§Щ„ЩЉ&#x3e;',
		currentText: 'Ш§Щ„ЩЉЩ€Щ…',
		monthNames: ['ЩѓШ§Щ†Щ€Щ† Ш§Щ„Ш«Ш§Щ†ЩЉ', 'ШґШЁШ§Ш·', 'ШўШ°Ш§Ш±', 'Щ†ЩЉШіШ§Щ†', 'Щ…Ш§ЩЉЩ€', 'Ш­ШІЩЉШ±Ш§Щ†',
		'ШЄЩ…Щ€ШІ', 'ШўШЁ', 'ШЈЩЉЩ„Щ€Щ„',	'ШЄШґШ±ЩЉЩ† Ш§Щ„ШЈЩ€Щ„', 'ШЄШґШ±ЩЉЩ† Ш§Щ„Ш«Ш§Щ†ЩЉ', 'ЩѓШ§Щ†Щ€Щ† Ш§Щ„ШЈЩ€Щ„'],
		monthNamesShort: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
		dayNames: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesShort: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesMin: ['Ш­', 'Щ†', 'Ш«', 'Ш±', 'Ш®', 'Ш¬', 'Ші'],
		weekHeader: 'ШЈШіШЁЩ€Ш№',
		dateFormat: 'dd/mm/yy',
		firstDay: 6,
  		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ar']);
});