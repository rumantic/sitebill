/* Kazakh (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Dmitriy Karasyov (dmitriy.karasyov@gmail.com). */
jQuery(function($){
	$.datepicker.regional['kk'] = {
		closeText: 'Р–Р°Р±Сѓ',
		prevText: '&#x3c;РђР»РґС‹ТЈТ“С‹',
		nextText: 'РљРµР»РµСЃС–&#x3e;',
		currentText: 'Р‘ТЇРіС–РЅ',
		monthNames: ['ТљР°ТЈС‚Р°СЂ','РђТ›РїР°РЅ','РќР°СѓСЂС‹Р·','РЎУ™СѓС–СЂ','РњР°РјС‹СЂ','РњР°СѓСЃС‹Рј',
		'РЁС–Р»РґРµ','РўР°РјС‹Р·','ТљС‹СЂРєТЇР№РµРє','ТљР°Р·Р°РЅ','ТљР°СЂР°С€Р°','Р–РµР»С‚РѕТ›СЃР°РЅ'],
		monthNamesShort: ['ТљР°ТЈ','РђТ›Рї','РќР°Сѓ','РЎУ™Сѓ','РњР°Рј','РњР°Сѓ',
		'РЁС–Р»','РўР°Рј','ТљС‹СЂ','ТљР°Р·','ТљР°СЂ','Р–РµР»'],
		dayNames: ['Р–РµРєСЃРµРЅР±С–','Р”ТЇР№СЃРµРЅР±С–','РЎРµР№СЃРµРЅР±С–','РЎУ™СЂСЃРµРЅР±С–','Р‘РµР№СЃРµРЅР±С–','Р–Т±РјР°','РЎРµРЅР±С–'],
		dayNamesShort: ['Р¶РєСЃ','РґСЃРЅ','СЃСЃРЅ','СЃСЂСЃ','Р±СЃРЅ','Р¶РјР°','СЃРЅР±'],
		dayNamesMin: ['Р–Рє','Р”СЃ','РЎСЃ','РЎСЂ','Р‘СЃ','Р–Рј','РЎРЅ'],
		weekHeader: 'РќРµ',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['kk']);
});
