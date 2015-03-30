/* Tajiki (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Abdurahmon Saidov (saidovab@gmail.com). */
jQuery(function($){
	$.datepicker.regional['tj'] = {
		closeText: 'Р�РґРѕРјР°',
		prevText: '&#x3c;ТљР°С„Рѕ',
		nextText: 'РџРµС€&#x3e;',
		currentText: 'Р�РјСЂУЇР·',
		monthNames: ['РЇРЅРІР°СЂ','Р¤РµРІСЂР°Р»','РњР°СЂС‚','РђРїСЂРµР»','РњР°Р№','Р�СЋРЅ',
		'Р�СЋР»','РђРІРіСѓСЃС‚','РЎРµРЅС‚СЏР±СЂ','РћРєС‚СЏР±СЂ','РќРѕСЏР±СЂ','Р”РµРєР°Р±СЂ'],
		monthNamesShort: ['РЇРЅРІ','Р¤РµРІ','РњР°СЂ','РђРїСЂ','РњР°Р№','Р�СЋРЅ',
		'Р�СЋР»','РђРІРі','РЎРµРЅ','РћРєС‚','РќРѕСЏ','Р”РµРє'],
		dayNames: ['СЏРєС€Р°РЅР±Рµ','РґСѓС€Р°РЅР±Рµ','СЃРµС€Р°РЅР±Рµ','С‡РѕСЂС€Р°РЅР±Рµ','РїР°РЅТ·С€Р°РЅР±Рµ','Т·СѓРјСЉР°','С€Р°РЅР±Рµ'],
		dayNamesShort: ['СЏРєС€','РґСѓС€','СЃРµС€','С‡РѕСЂ','РїР°РЅ','Т·СѓРј','С€Р°РЅ'],
		dayNamesMin: ['РЇРє','Р”С€','РЎС€','Р§С€','РџС€','Т¶Рј','РЁРЅ'],
		weekHeader: 'РҐС„',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['tj']);
});