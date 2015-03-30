/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by Ressol (ressol@gmail.com). */
jQuery(function($){
	$.datepicker.regional['zh-TW'] = {
		closeText: 'й—њй–‰',
		prevText: '&#x3c;дёЉжњ€',
		nextText: 'дё‹жњ€&#x3e;',
		currentText: 'д»Ље¤©',
		monthNames: ['дёЂжњ€','дєЊжњ€','дё‰жњ€','е››жњ€','дє”жњ€','е…­жњ€',
		'дёѓжњ€','е…«жњ€','д№ќжњ€','еЌЃжњ€','еЌЃдёЂжњ€','еЌЃдєЊжњ€'],
		monthNamesShort: ['дёЂ','дєЊ','дё‰','е››','дє”','е…­',
		'дёѓ','е…«','д№ќ','еЌЃ','еЌЃдёЂ','еЌЃдєЊ'],
		dayNames: ['ж�џжњџж—Ґ','ж�џжњџдёЂ','ж�џжњџдєЊ','ж�џжњџдё‰','ж�џжњџе››','ж�џжњџдє”','ж�џжњџе…­'],
		dayNamesShort: ['е‘Ёж—Ґ','е‘ЁдёЂ','е‘ЁдєЊ','е‘Ёдё‰','е‘Ёе››','е‘Ёдє”','е‘Ёе…­'],
		dayNamesMin: ['ж—Ґ','дёЂ','дєЊ','дё‰','е››','дє”','е…­'],
		weekHeader: 'е‘Ё',
		dateFormat: 'yy/mm/dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'е№ґ'};
	$.datepicker.setDefaults($.datepicker.regional['zh-TW']);
});
