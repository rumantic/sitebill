/* Russian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ru'] = {
		closeText: 'Р—Р°РєСЂС‹С‚СЊ',
		prevText: '&#x3c;РџСЂРµРґ',
		nextText: 'РЎР»РµРґ&#x3e;',
		currentText: 'РЎРµРіРѕРґРЅСЏ',
		monthNames: ['РЇРЅРІР°СЂСЊ','Р¤РµРІСЂР°Р»СЊ','РњР°СЂС‚','РђРїСЂРµР»СЊ','РњР°Р№','Р�СЋРЅСЊ',
		'Р�СЋР»СЊ','РђРІРіСѓСЃС‚','РЎРµРЅС‚СЏР±СЂСЊ','РћРєС‚СЏР±СЂСЊ','РќРѕСЏР±СЂСЊ','Р”РµРєР°Р±СЂСЊ'],
		monthNamesShort: ['РЇРЅРІ','Р¤РµРІ','РњР°СЂ','РђРїСЂ','РњР°Р№','Р�СЋРЅ',
		'Р�СЋР»','РђРІРі','РЎРµРЅ','РћРєС‚','РќРѕСЏ','Р”РµРє'],
		dayNames: ['РІРѕСЃРєСЂРµСЃРµРЅСЊРµ','РїРѕРЅРµРґРµР»СЊРЅРёРє','РІС‚РѕСЂРЅРёРє','СЃСЂРµРґР°','С‡РµС‚РІРµСЂРі','РїСЏС‚РЅРёС†Р°','СЃСѓР±Р±РѕС‚Р°'],
		dayNamesShort: ['РІСЃРє','РїРЅРґ','РІС‚СЂ','СЃСЂРґ','С‡С‚РІ','РїС‚РЅ','СЃР±С‚'],
		dayNamesMin: ['Р’СЃ','РџРЅ','Р’С‚','РЎСЂ','Р§С‚','РџС‚','РЎР±'],
		weekHeader: 'РќРµРґ',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ru']);
});