/* Luxembourgish initialisation for the jQuery UI date picker plugin. */
/* Written by Michel Weimerskirch <michel@weimerskirch.net> */
jQuery(function($){
	$.datepicker.regional['lb'] = {
		closeText: 'FГ¤erdeg',
		prevText: 'ZrГ©ck',
		nextText: 'Weider',
		currentText: 'Haut',
		monthNames: ['Januar','Februar','MГ¤erz','AbrГ«ll','Mee','Juni',
		'Juli','August','September','Oktober','November','Dezember'],
		monthNamesShort: ['Jan', 'Feb', 'MГ¤e', 'Abr', 'Mee', 'Jun',
		'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
		dayNames: ['Sonndeg', 'MГ©indeg', 'DГ«nschdeg', 'MГ«ttwoch', 'Donneschdeg', 'Freideg', 'Samschdeg'],
		dayNamesShort: ['Son', 'MГ©i', 'DГ«n', 'MГ«t', 'Don', 'Fre', 'Sam'],
		dayNamesMin: ['So','MГ©','DГ«','MГ«','Do','Fr','Sa'],
		weekHeader: 'W',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['lb']);
});
