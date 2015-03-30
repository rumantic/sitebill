/* Algerian Arabic Translation for jQuery UI date picker plugin. (can be used for Tunisia)*/
/* Mohamed Cherif BOUCHELAGHEM -- cherifbouchelaghem@yahoo.fr */

jQuery(function($){
	$.datepicker.regional['ar-DZ'] = {
		closeText: 'ШҐШєЩ„Ш§Щ‚',
		prevText: '&#x3c;Ш§Щ„ШіШ§ШЁЩ‚',
		nextText: 'Ш§Щ„ШЄШ§Щ„ЩЉ&#x3e;',
		currentText: 'Ш§Щ„ЩЉЩ€Щ…',
		monthNames: ['Ш¬Ш§Щ†ЩЃЩЉ', 'ЩЃЩЉЩЃШ±ЩЉ', 'Щ…Ш§Ш±Ші', 'ШЈЩЃШ±ЩЉЩ„', 'Щ…Ш§ЩЉ', 'Ш¬Щ€Ш§Щ†',
		'Ш¬Щ€ЩЉЩ„ЩЉШ©', 'ШЈЩ€ШЄ', 'ШіШЁШЄЩ…ШЁШ±','ШЈЩѓШЄЩ€ШЁШ±', 'Щ†Щ€ЩЃЩ…ШЁШ±', 'ШЇЩЉШіЩ…ШЁШ±'],
		monthNamesShort: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
		dayNames: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesShort: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesMin: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		weekHeader: 'ШЈШіШЁЩ€Ш№',
		dateFormat: 'dd/mm/yy',
		firstDay: 6,
  		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ar-DZ']);
});
