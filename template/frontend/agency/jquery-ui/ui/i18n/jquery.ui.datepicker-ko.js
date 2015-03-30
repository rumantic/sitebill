/* Korean initialisation for the jQuery calendar extension. */
/* Written by DaeKwon Kang (ncrash.dk@gmail.com), Edited by Genie. */
jQuery(function($){
	$.datepicker.regional['ko'] = {
		closeText: 'л‹«кё°',
		prevText: 'мќґм „л‹¬',
		nextText: 'л‹¤мќЊл‹¬',
		currentText: 'м�¤лЉ�',
		monthNames: ['1м›”','2м›”','3м›”','4м›”','5м›”','6м›”',
		'7м›”','8м›”','9м›”','10м›”','11м›”','12м›”'],
		monthNamesShort: ['1м›”','2м›”','3м›”','4м›”','5м›”','6м›”',
		'7м›”','8м›”','9м›”','10м›”','11м›”','12м›”'],
		dayNames: ['мќјмљ”мќј','м›”мљ”мќј','н™”мљ”мќј','м€�мљ”мќј','лЄ©мљ”мќј','кё€мљ”мќј','н† мљ”мќј'],
		dayNamesShort: ['мќј','м›”','н™”','м€�','лЄ©','кё€','н† '],
		dayNamesMin: ['мќј','м›”','н™”','м€�','лЄ©','кё€','н† '],
		weekHeader: 'Wk',
		dateFormat: 'yy-mm-dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'л…„'};
	$.datepicker.setDefaults($.datepicker.regional['ko']);
});