/* Armenian(UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Levon Zakaryan (levon.zakaryan@gmail.com)*/
jQuery(function($){
	$.datepicker.regional['hy'] = {
		closeText: 'Х“ХЎХЇХҐХ¬',
		prevText: '&#x3c;Х†ХЎХ­.',
		nextText: 'ХЂХЎХ».&#x3e;',
		currentText: 'Ф±ХµХЅЦ…ЦЂ',
		monthNames: ['ХЂХёЦ‚Х¶ХѕХЎЦЂ','Х“ХҐХїЦЂХѕХЎЦЂ','Х„ХЎЦЂХї','Ф±ХєЦЂХ«Х¬','Х„ХЎХµХ«ХЅ','ХЂХёЦ‚Х¶Х«ХЅ',
		'ХЂХёЦ‚Х¬Х«ХЅ','Х•ХЈХёХЅХїХёХЅ','ХЌХҐХєХїХҐХґХўХҐЦЂ','ХЂХёХЇХїХҐХґХўХҐЦЂ','Х†ХёХµХҐХґХўХҐЦЂ','ФґХҐХЇХїХҐХґХўХҐЦЂ'],
		monthNamesShort: ['ХЂХёЦ‚Х¶Хѕ','Х“ХҐХїЦЂ','Х„ХЎЦЂХї','Ф±ХєЦЂ','Х„ХЎХµХ«ХЅ','ХЂХёЦ‚Х¶Х«ХЅ',
		'ХЂХёЦ‚Х¬','Х•ХЈХЅ','ХЌХҐХє','ХЂХёХЇ','Х†ХёХµ','ФґХҐХЇ'],
		dayNames: ['ХЇХ«ЦЂХЎХЇХ«','ХҐХЇХёЦ‚Х·ХЎХўХ©Х«','ХҐЦЂХҐЦ„Х·ХЎХўХ©Х«','Х№ХёЦЂХҐЦ„Х·ХЎХўХ©Х«','Х°Х«Х¶ХЈХ·ХЎХўХ©Х«','ХёЦ‚ЦЂХўХЎХ©','Х·ХЎХўХЎХ©'],
		dayNamesShort: ['ХЇХ«ЦЂ','ХҐЦЂХЇ','ХҐЦЂЦ„','Х№ЦЂЦ„','Х°Х¶ХЈ','ХёЦ‚ЦЂХў','Х·ХўХ©'],
		dayNamesMin: ['ХЇХ«ЦЂ','ХҐЦЂХЇ','ХҐЦЂЦ„','Х№ЦЂЦ„','Х°Х¶ХЈ','ХёЦ‚ЦЂХў','Х·ХўХ©'],
		weekHeader: 'Х‡ФІХЏ',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['hy']);
});