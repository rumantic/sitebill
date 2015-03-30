/* Ukrainian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Maxim Drogobitskiy (maxdao@gmail.com). */
/* Corrected by Igor Milla (igor.fsp.milla@gmail.com). */
jQuery(function($){
	$.datepicker.regional['uk'] = {
		closeText: 'Р—Р°РєСЂРёС‚Рё',
		prevText: '&#x3c;',
		nextText: '&#x3e;',
		currentText: 'РЎСЊРѕРіРѕРґРЅС–',
		monthNames: ['РЎС–С‡РµРЅСЊ','Р›СЋС‚РёР№','Р‘РµСЂРµР·РµРЅСЊ','РљРІС–С‚РµРЅСЊ','РўСЂР°РІРµРЅСЊ','Р§РµСЂРІРµРЅСЊ',
		'Р›РёРїРµРЅСЊ','РЎРµСЂРїРµРЅСЊ','Р’РµСЂРµСЃРµРЅСЊ','Р–РѕРІС‚РµРЅСЊ','Р›РёСЃС‚РѕРїР°Рґ','Р“СЂСѓРґРµРЅСЊ'],
		monthNamesShort: ['РЎС–С‡','Р›СЋС‚','Р‘РµСЂ','РљРІС–','РўСЂР°','Р§РµСЂ',
		'Р›РёРї','РЎРµСЂ','Р’РµСЂ','Р–РѕРІ','Р›РёСЃ','Р“СЂСѓ'],
		dayNames: ['РЅРµРґС–Р»СЏ','РїРѕРЅРµРґС–Р»РѕРє','РІС–РІС‚РѕСЂРѕРє','СЃРµСЂРµРґР°','С‡РµС‚РІРµСЂ','РївЂ™СЏС‚РЅРёС†СЏ','СЃСѓР±РѕС‚Р°'],
		dayNamesShort: ['РЅРµРґ','РїРЅРґ','РІС–РІ','СЃСЂРґ','С‡С‚РІ','РїС‚РЅ','СЃР±С‚'],
		dayNamesMin: ['РќРґ','РџРЅ','Р’С‚','РЎСЂ','Р§С‚','РџС‚','РЎР±'],
		weekHeader: 'РўРёР¶',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['uk']);
});