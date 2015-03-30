/* Slovak initialisation for the jQuery UI date picker plugin. */
/* Written by Vojtech Rinik (vojto@hmm.sk). */
jQuery(function($){
	$.datepicker.regional['sk'] = {
		closeText: 'ZavrieЕҐ',
		prevText: '&#x3c;PredchГЎdzajГєci',
		nextText: 'NasledujГєci&#x3e;',
		currentText: 'Dnes',
		monthNames: ['JanuГЎr','FebruГЎr','Marec','AprГ­l','MГЎj','JГєn',
		'JГєl','August','September','OktГіber','November','December'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','MГЎj','JГєn',
		'JГєl','Aug','Sep','Okt','Nov','Dec'],
		dayNames: ['NedeДѕa','Pondelok','Utorok','Streda','Е tvrtok','Piatok','Sobota'],
		dayNamesShort: ['Ned','Pon','Uto','Str','Е tv','Pia','Sob'],
		dayNamesMin: ['Ne','Po','Ut','St','Е t','Pia','So'],
		weekHeader: 'Ty',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['sk']);
});
