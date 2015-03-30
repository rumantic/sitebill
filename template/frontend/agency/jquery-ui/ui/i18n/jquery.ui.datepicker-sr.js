/* Serbian i18n for the jQuery UI date picker plugin. */
/* Written by Dejan DimiД‡. */
jQuery(function($){
	$.datepicker.regional['sr'] = {
		closeText: 'Р—Р°С‚РІРѕСЂРё',
		prevText: '&#x3c;',
		nextText: '&#x3e;',
		currentText: 'Р”Р°РЅР°СЃ',
		monthNames: ['Р€Р°РЅСѓР°СЂ','Р¤РµР±СЂСѓР°СЂ','РњР°СЂС‚','РђРїСЂРёР»','РњР°С�','Р€СѓРЅ',
		'Р€СѓР»','РђРІРіСѓСЃС‚','РЎРµРїС‚РµРјР±Р°СЂ','РћРєС‚РѕР±Р°СЂ','РќРѕРІРµРјР±Р°СЂ','Р”РµС†РµРјР±Р°СЂ'],
		monthNamesShort: ['Р€Р°РЅ','Р¤РµР±','РњР°СЂ','РђРїСЂ','РњР°С�','Р€СѓРЅ',
		'Р€СѓР»','РђРІРі','РЎРµРї','РћРєС‚','РќРѕРІ','Р”РµС†'],
		dayNames: ['РќРµРґРµС™Р°','РџРѕРЅРµРґРµС™Р°Рє','РЈС‚РѕСЂР°Рє','РЎСЂРµРґР°','Р§РµС‚РІСЂС‚Р°Рє','РџРµС‚Р°Рє','РЎСѓР±РѕС‚Р°'],
		dayNamesShort: ['РќРµРґ','РџРѕРЅ','РЈС‚Рѕ','РЎСЂРµ','Р§РµС‚','РџРµС‚','РЎСѓР±'],
		dayNamesMin: ['РќРµ','РџРѕ','РЈС‚','РЎСЂ','Р§Рµ','РџРµ','РЎСѓ'],
		weekHeader: 'РЎРµРґ',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['sr']);
});
