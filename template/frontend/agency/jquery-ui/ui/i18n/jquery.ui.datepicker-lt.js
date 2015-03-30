/* Lithuanian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* @author Arturas Paleicikas <arturas@avalon.lt> */
jQuery(function($){
	$.datepicker.regional['lt'] = {
		closeText: 'UЕѕdaryti',
		prevText: '&#x3c;Atgal',
		nextText: 'Pirmyn&#x3e;',
		currentText: 'Е iandien',
		monthNames: ['Sausis','Vasaris','Kovas','Balandis','GeguЕѕД—','BirЕѕelis',
		'Liepa','RugpjЕ«tis','RugsД—jis','Spalis','Lapkritis','Gruodis'],
		monthNamesShort: ['Sau','Vas','Kov','Bal','Geg','Bir',
		'Lie','Rugp','Rugs','Spa','Lap','Gru'],
		dayNames: ['sekmadienis','pirmadienis','antradienis','treДЌiadienis','ketvirtadienis','penktadienis','ЕЎeЕЎtadienis'],
		dayNamesShort: ['sek','pir','ant','tre','ket','pen','ЕЎeЕЎ'],
		dayNamesMin: ['Se','Pr','An','Tr','Ke','Pe','Е e'],
		weekHeader: 'Wk',
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['lt']);
});