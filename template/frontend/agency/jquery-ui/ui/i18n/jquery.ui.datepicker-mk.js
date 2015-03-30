/* Macedonian i18n for the jQuery UI date picker plugin. */
/* Written by Stojce Slavkovski. */
jQuery(function($){
	$.datepicker.regional['mk'] = {
		closeText: 'Р—Р°С‚РІРѕСЂРё',
		prevText: '&#x3C;',
		nextText: '&#x3E;',
		currentText: 'Р”РµРЅРµСЃ',
		monthNames: ['Р€Р°РЅСѓР°СЂРё','Р¤РµРІСЂСѓР°СЂРё','РњР°СЂС‚','РђРїСЂРёР»','РњР°С�','Р€СѓРЅРё',
		'Р€СѓР»Рё','РђРІРіСѓСЃС‚','РЎРµРїС‚РµРјРІСЂРё','РћРєС‚РѕРјРІСЂРё','РќРѕРµРјРІСЂРё','Р”РµРєРµРјРІСЂРё'],
		monthNamesShort: ['Р€Р°РЅ','Р¤РµРІ','РњР°СЂ','РђРїСЂ','РњР°С�','Р€СѓРЅ',
		'Р€СѓР»','РђРІРі','РЎРµРї','РћРєС‚','РќРѕРµ','Р”РµРє'],
		dayNames: ['РќРµРґРµР»Р°','РџРѕРЅРµРґРµР»РЅРёРє','Р’С‚РѕСЂРЅРёРє','РЎСЂРµРґР°','Р§РµС‚РІСЂС‚РѕРє','РџРµС‚РѕРє','РЎР°Р±РѕС‚Р°'],
		dayNamesShort: ['РќРµРґ','РџРѕРЅ','Р’С‚Рѕ','РЎСЂРµ','Р§РµС‚','РџРµС‚','РЎР°Р±'],
		dayNamesMin: ['РќРµ','РџРѕ','Р’С‚','РЎСЂ','Р§Рµ','РџРµ','РЎР°'],
		weekHeader: 'РЎРµРґ',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['mk']);
});
