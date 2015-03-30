/*! jQuery UI - v1.8.21 - 2012-06-05
* https://github.com/jquery/jquery-ui
* Includes: jquery.ui.datepicker-af.js, jquery.ui.datepicker-ar-DZ.js, jquery.ui.datepicker-ar.js, jquery.ui.datepicker-az.js, jquery.ui.datepicker-bg.js, jquery.ui.datepicker-bs.js, jquery.ui.datepicker-ca.js, jquery.ui.datepicker-cs.js, jquery.ui.datepicker-cy-GB.js, jquery.ui.datepicker-da.js, jquery.ui.datepicker-de.js, jquery.ui.datepicker-el.js, jquery.ui.datepicker-en-AU.js, jquery.ui.datepicker-en-GB.js, jquery.ui.datepicker-en-NZ.js, jquery.ui.datepicker-eo.js, jquery.ui.datepicker-es.js, jquery.ui.datepicker-et.js, jquery.ui.datepicker-eu.js, jquery.ui.datepicker-fa.js, jquery.ui.datepicker-fi.js, jquery.ui.datepicker-fo.js, jquery.ui.datepicker-fr-CH.js, jquery.ui.datepicker-fr.js, jquery.ui.datepicker-gl.js, jquery.ui.datepicker-he.js, jquery.ui.datepicker-hi.js, jquery.ui.datepicker-hr.js, jquery.ui.datepicker-hu.js, jquery.ui.datepicker-hy.js, jquery.ui.datepicker-id.js, jquery.ui.datepicker-is.js, jquery.ui.datepicker-it.js, jquery.ui.datepicker-ja.js, jquery.ui.datepicker-ka.js, jquery.ui.datepicker-kk.js, jquery.ui.datepicker-km.js, jquery.ui.datepicker-ko.js, jquery.ui.datepicker-lb.js, jquery.ui.datepicker-lt.js, jquery.ui.datepicker-lv.js, jquery.ui.datepicker-mk.js, jquery.ui.datepicker-ml.js, jquery.ui.datepicker-ms.js, jquery.ui.datepicker-nl-BE.js, jquery.ui.datepicker-nl.js, jquery.ui.datepicker-no.js, jquery.ui.datepicker-pl.js, jquery.ui.datepicker-pt-BR.js, jquery.ui.datepicker-pt.js, jquery.ui.datepicker-rm.js, jquery.ui.datepicker-ro.js, jquery.ui.datepicker-ru.js, jquery.ui.datepicker-sk.js, jquery.ui.datepicker-sl.js, jquery.ui.datepicker-sq.js, jquery.ui.datepicker-sr-SR.js, jquery.ui.datepicker-sr.js, jquery.ui.datepicker-sv.js, jquery.ui.datepicker-ta.js, jquery.ui.datepicker-th.js, jquery.ui.datepicker-tj.js, jquery.ui.datepicker-tr.js, jquery.ui.datepicker-uk.js, jquery.ui.datepicker-vi.js, jquery.ui.datepicker-zh-CN.js, jquery.ui.datepicker-zh-HK.js, jquery.ui.datepicker-zh-TW.js
* Copyright (c) 2012 AUTHORS.txt; Licensed MIT, GPL */

/* Afrikaans initialisation for the jQuery UI date picker plugin. */
/* Written by Renier Pretorius. */
jQuery(function($){
	$.datepicker.regional['af'] = {
		closeText: 'Selekteer',
		prevText: 'Vorige',
		nextText: 'Volgende',
		currentText: 'Vandag',
		monthNames: ['Januarie','Februarie','Maart','April','Mei','Junie',
		'Julie','Augustus','September','Oktober','November','Desember'],
		monthNamesShort: ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun',
		'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'],
		dayNames: ['Sondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrydag', 'Saterdag'],
		dayNamesShort: ['Son', 'Maa', 'Din', 'Woe', 'Don', 'Vry', 'Sat'],
		dayNamesMin: ['So','Ma','Di','Wo','Do','Vr','Sa'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['af']);
});

/* Algerian Arabic Translation for jQuery UI date picker plugin. (can be used for Tunisia)*/
/* Mohamed Cherif BOUCHELAGHEM -- cherifbouchelaghem@yahoo.fr */

jQuery(function($){
	$.datepicker.regional['ar-DZ'] = {
		closeText: 'ШҐШєЩ„Ш§Щ‚',
		prevText: '&#x3c;Ш§Щ„ШіШ§ШЁЩ‚',
		nextText: 'Ш§Щ„ШЄШ§Щ„ЩЉ&#x3e;',
		currentText: 'Ш§Щ„ЩЉЩ€Щ…',
		monthNames: ['Ш¬Ш§Щ†ЩЃЩЉ', 'ЩЃЩЉЩЃШ±ЩЉ', 'Щ…Ш§Ш±Ші', 'ШЈЩЃШ±ЩЉЩ„', 'Щ…Ш§ЩЉ', 'Ш¬Щ€Ш§Щ†',
		'Ш¬Щ€ЩЉЩ„ЩЉШ©', 'ШЈЩ€ШЄ', 'ШіШЁШЄЩ…ШЁШ±','ШЈЩѓШЄЩ€ШЁШ±', 'Щ†Щ€ЩЃЩ…ШЁШ±', 'ШЇЩЉШіЩ…ШЁШ±'],
		monthNamesShort: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
		dayNames: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesShort: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesMin: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		weekHeader: 'ШЈШіШЁЩ€Ш№',
		dateFormat: 'dd/mm/yy',
		firstDay: 6,
  		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ar-DZ']);
});

/* Arabic Translation for jQuery UI date picker plugin. */
/* Khaled Alhourani -- me@khaledalhourani.com */
/* NOTE: monthNames are the original months names and they are the Arabic names, not the new months name ЩЃШЁШ±Ш§ЩЉШ± - ЩЉЩ†Ш§ЩЉШ± and there isn't any Arabic roots for these months */
jQuery(function($){
	$.datepicker.regional['ar'] = {
		closeText: 'ШҐШєЩ„Ш§Щ‚',
		prevText: '&#x3c;Ш§Щ„ШіШ§ШЁЩ‚',
		nextText: 'Ш§Щ„ШЄШ§Щ„ЩЉ&#x3e;',
		currentText: 'Ш§Щ„ЩЉЩ€Щ…',
		monthNames: ['ЩѓШ§Щ†Щ€Щ† Ш§Щ„Ш«Ш§Щ†ЩЉ', 'ШґШЁШ§Ш·', 'ШўШ°Ш§Ш±', 'Щ†ЩЉШіШ§Щ†', 'Щ…Ш§ЩЉЩ€', 'Ш­ШІЩЉШ±Ш§Щ†',
		'ШЄЩ…Щ€ШІ', 'ШўШЁ', 'ШЈЩЉЩ„Щ€Щ„',	'ШЄШґШ±ЩЉЩ† Ш§Щ„ШЈЩ€Щ„', 'ШЄШґШ±ЩЉЩ† Ш§Щ„Ш«Ш§Щ†ЩЉ', 'ЩѓШ§Щ†Щ€Щ† Ш§Щ„ШЈЩ€Щ„'],
		monthNamesShort: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
		dayNames: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesShort: ['Ш§Щ„ШЈШ­ШЇ', 'Ш§Щ„Ш§Ш«Щ†ЩЉЩ†', 'Ш§Щ„Ш«Щ„Ш§Ш«Ш§ШЎ', 'Ш§Щ„ШЈШ±ШЁШ№Ш§ШЎ', 'Ш§Щ„Ш®Щ…ЩЉШі', 'Ш§Щ„Ш¬Щ…Ш№Ш©', 'Ш§Щ„ШіШЁШЄ'],
		dayNamesMin: ['Ш­', 'Щ†', 'Ш«', 'Ш±', 'Ш®', 'Ш¬', 'Ші'],
		weekHeader: 'ШЈШіШЁЩ€Ш№',
		dateFormat: 'dd/mm/yy',
		firstDay: 6,
  		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ar']);
});
/* Azerbaijani (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Jamil Najafov (necefov33@gmail.com). */
jQuery(function($) {
	$.datepicker.regional['az'] = {
		closeText: 'BaДџla',
		prevText: '&#x3c;Geri',
		nextText: 'Д°rЙ™li&#x3e;',
		currentText: 'BugГјn',
		monthNames: ['Yanvar','Fevral','Mart','Aprel','May','Д°yun',
		'Д°yul','Avqust','Sentyabr','Oktyabr','Noyabr','Dekabr'],
		monthNamesShort: ['Yan','Fev','Mar','Apr','May','Д°yun',
		'Д°yul','Avq','Sen','Okt','Noy','Dek'],
		dayNames: ['Bazar','Bazar ertЙ™si','Г‡Й™rЕџЙ™nbЙ™ axЕџamД±','Г‡Й™rЕџЙ™nbЙ™','CГјmЙ™ axЕџamД±','CГјmЙ™','ЕћЙ™nbЙ™'],
		dayNamesShort: ['B','Be','Г‡a','Г‡','Ca','C','Ећ'],
		dayNamesMin: ['B','B','Г‡','РЎ','Г‡','C','Ећ'],
		weekHeader: 'Hf',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['az']);
});
/* Bulgarian initialisation for the jQuery UI date picker plugin. */
/* Written by Stoyan Kyosev (http://svest.org). */
jQuery(function($){
    $.datepicker.regional['bg'] = {
        closeText: 'Р·Р°С‚РІРѕСЂРё',
        prevText: '&#x3c;РЅР°Р·Р°Рґ',
        nextText: 'РЅР°РїСЂРµРґ&#x3e;',
		nextBigText: '&#x3e;&#x3e;',
        currentText: 'РґРЅРµСЃ',
        monthNames: ['РЇРЅСѓР°СЂРё','Р¤РµРІСЂСѓР°СЂРё','РњР°СЂС‚','РђРїСЂРёР»','РњР°Р№','Р®РЅРё',
        'Р®Р»Рё','РђРІРіСѓСЃС‚','РЎРµРїС‚РµРјРІСЂРё','РћРєС‚РѕРјРІСЂРё','РќРѕРµРјРІСЂРё','Р”РµРєРµРјРІСЂРё'],
        monthNamesShort: ['РЇРЅСѓ','Р¤РµРІ','РњР°СЂ','РђРїСЂ','РњР°Р№','Р®РЅРё',
        'Р®Р»Рё','РђРІРі','РЎРµРї','РћРєС‚','РќРѕРІ','Р”РµРє'],
        dayNames: ['РќРµРґРµР»СЏ','РџРѕРЅРµРґРµР»РЅРёРє','Р’С‚РѕСЂРЅРёРє','РЎСЂСЏРґР°','Р§РµС‚РІСЉСЂС‚СЉРє','РџРµС‚СЉРє','РЎСЉР±РѕС‚Р°'],
        dayNamesShort: ['РќРµРґ','РџРѕРЅ','Р’С‚Рѕ','РЎСЂСЏ','Р§РµС‚','РџРµС‚','РЎСЉР±'],
        dayNamesMin: ['РќРµ','РџРѕ','Р’С‚','РЎСЂ','Р§Рµ','РџРµ','РЎСЉ'],
		weekHeader: 'Wk',
        dateFormat: 'dd.mm.yy',
		firstDay: 1,
        isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['bg']);
});

/* Bosnian i18n for the jQuery UI date picker plugin. */
/* Written by Kenan Konjo. */
jQuery(function($){
	$.datepicker.regional['bs'] = {
		closeText: 'Zatvori', 
		prevText: '&#x3c;', 
		nextText: '&#x3e;', 
		currentText: 'Danas', 
		monthNames: ['Januar','Februar','Mart','April','Maj','Juni',
		'Juli','August','Septembar','Oktobar','Novembar','Decembar'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
		'Jul','Aug','Sep','Okt','Nov','Dec'],
		dayNames: ['Nedelja','Ponedeljak','Utorak','Srijeda','ДЊetvrtak','Petak','Subota'],
		dayNamesShort: ['Ned','Pon','Uto','Sri','ДЊet','Pet','Sub'],
		dayNamesMin: ['Ne','Po','Ut','Sr','ДЊe','Pe','Su'],
		weekHeader: 'Wk',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['bs']);
});
/* InicialitzaciГі en catalГ  per a l'extenciГі 'calendar' per jQuery. */
/* Writers: (joan.leon@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ca'] = {
		closeText: 'Tancar',
		prevText: '&#x3c;Ant',
		nextText: 'Seg&#x3e;',
		currentText: 'Avui',
		monthNames: ['Gener','Febrer','Mar&ccedil;','Abril','Maig','Juny',
		'Juliol','Agost','Setembre','Octubre','Novembre','Desembre'],
		monthNamesShort: ['Gen','Feb','Mar','Abr','Mai','Jun',
		'Jul','Ago','Set','Oct','Nov','Des'],
		dayNames: ['Diumenge','Dilluns','Dimarts','Dimecres','Dijous','Divendres','Dissabte'],
		dayNamesShort: ['Dug','Dln','Dmt','Dmc','Djs','Dvn','Dsb'],
		dayNamesMin: ['Dg','Dl','Dt','Dc','Dj','Dv','Ds'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ca']);
});
/* Czech initialisation for the jQuery UI date picker plugin. */
/* Written by Tomas Muller (tomas@tomas-muller.net). */
jQuery(function($){
	$.datepicker.regional['cs'] = {
		closeText: 'ZavЕ™Г­t',
		prevText: '&#x3c;DЕ™Г­ve',
		nextText: 'PozdД›ji&#x3e;',
		currentText: 'NynГ­',
		monthNames: ['leden','Гєnor','bЕ™ezen','duben','kvД›ten','ДЌerven',
        'ДЌervenec','srpen','zГЎЕ™Г­','Е™Г­jen','listopad','prosinec'],
		monthNamesShort: ['led','Гєno','bЕ™e','dub','kvД›','ДЌer',
		'ДЌvc','srp','zГЎЕ™','Е™Г­j','lis','pro'],
		dayNames: ['nedД›le', 'pondД›lГ­', 'ГєterГЅ', 'stЕ™eda', 'ДЌtvrtek', 'pГЎtek', 'sobota'],
		dayNamesShort: ['ne', 'po', 'Гєt', 'st', 'ДЌt', 'pГЎ', 'so'],
		dayNamesMin: ['ne','po','Гєt','st','ДЌt','pГЎ','so'],
		weekHeader: 'TГЅd',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['cs']);
});

/* Welsh/UK initialisation for the jQuery UI date picker plugin. */
/* Written by William Griffiths. */
jQuery(function($){
	$.datepicker.regional['cy-GB'] = {
		closeText: 'Done',
		prevText: 'Prev',
		nextText: 'Next',
		currentText: 'Today',
		monthNames: ['Ionawr','Chwefror','Mawrth','Ebrill','Mai','Mehefin',
		'Gorffennaf','Awst','Medi','Hydref','Tachwedd','Rhagfyr'],
		monthNamesShort: ['Ion', 'Chw', 'Maw', 'Ebr', 'Mai', 'Meh',
		'Gor', 'Aws', 'Med', 'Hyd', 'Tac', 'Rha'],
		dayNames: ['Dydd Sul', 'Dydd Llun', 'Dydd Mawrth', 'Dydd Mercher', 'Dydd Iau', 'Dydd Gwener', 'Dydd Sadwrn'],
		dayNamesShort: ['Sul', 'Llu', 'Maw', 'Mer', 'Iau', 'Gwe', 'Sad'],
		dayNamesMin: ['Su','Ll','Ma','Me','Ia','Gw','Sa'],
		weekHeader: 'Wy',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['cy-GB']);
});
/* Danish initialisation for the jQuery UI date picker plugin. */
/* Written by Jan Christensen ( deletestuff@gmail.com). */
jQuery(function($){
    $.datepicker.regional['da'] = {
		closeText: 'Luk',
        prevText: '&#x3c;Forrige',
		nextText: 'NГ¦ste&#x3e;',
		currentText: 'Idag',
        monthNames: ['Januar','Februar','Marts','April','Maj','Juni',
        'Juli','August','September','Oktober','November','December'],
        monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
        'Jul','Aug','Sep','Okt','Nov','Dec'],
		dayNames: ['SГёndag','Mandag','Tirsdag','Onsdag','Torsdag','Fredag','LГёrdag'],
		dayNamesShort: ['SГёn','Man','Tir','Ons','Tor','Fre','LГёr'],
		dayNamesMin: ['SГё','Ma','Ti','On','To','Fr','LГё'],
		weekHeader: 'Uge',
        dateFormat: 'dd-mm-yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['da']);
});

/* German initialisation for the jQuery UI date picker plugin. */
/* Written by Milian Wolff (mail@milianw.de). */
jQuery(function($){
	$.datepicker.regional['de'] = {
		closeText: 'schlieГџen',
		prevText: '&#x3c;zurГјck',
		nextText: 'Vor&#x3e;',
		currentText: 'heute',
		monthNames: ['Januar','Februar','MГ¤rz','April','Mai','Juni',
		'Juli','August','September','Oktober','November','Dezember'],
		monthNamesShort: ['Jan','Feb','MГ¤r','Apr','Mai','Jun',
		'Jul','Aug','Sep','Okt','Nov','Dez'],
		dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
		dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		weekHeader: 'KW',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['de']);
});

/* Greek (el) initialisation for the jQuery UI date picker plugin. */
/* Written by Alex Cicovic (http://www.alexcicovic.com) */
jQuery(function($){
	$.datepicker.regional['el'] = {
		closeText: 'ОљО»ОµОЇПѓО№ОјОї',
		prevText: 'О ПЃОїО·ОіОїПЌОјОµОЅОїП‚',
		nextText: 'О•ПЂПЊОјОµОЅОїП‚',
		currentText: 'О¤ПЃО­П‡П‰ОЅ ОњО®ОЅО±П‚',
		monthNames: ['О™О±ОЅОїП…О¬ПЃО№ОїП‚','О¦ОµОІПЃОїП…О¬ПЃО№ОїП‚','ОњО¬ПЃП„О№ОїП‚','О‘ПЂПЃОЇО»О№ОїП‚','ОњО¬О№ОїП‚','О™ОїПЌОЅО№ОїП‚',
		'О™ОїПЌО»О№ОїП‚','О‘ПЌОіОїП…ПѓП„ОїП‚','ОЈОµПЂП„О­ОјОІПЃО№ОїП‚','ОџОєП„ПЋОІПЃО№ОїП‚','ОќОїО­ОјОІПЃО№ОїП‚','О”ОµОєО­ОјОІПЃО№ОїП‚'],
		monthNamesShort: ['О™О±ОЅ','О¦ОµОІ','ОњО±ПЃ','О‘ПЂПЃ','ОњО±О№','О™ОїП…ОЅ',
		'О™ОїП…О»','О‘П…Оі','ОЈОµПЂ','ОџОєП„','ОќОїОµ','О”ОµОє'],
		dayNames: ['ОљП…ПЃО№О±ОєО®','О”ОµП…П„О­ПЃО±','О¤ПЃОЇП„О·','О¤ОµП„О¬ПЃП„О·','О О­ОјПЂП„О·','О О±ПЃО±ПѓОєОµП…О®','ОЈО¬ОІОІО±П„Ої'],
		dayNamesShort: ['ОљП…ПЃ','О”ОµП…','О¤ПЃО№','О¤ОµП„','О ОµОј','О О±ПЃ','ОЈО±ОІ'],
		dayNamesMin: ['ОљП…','О”Оµ','О¤ПЃ','О¤Оµ','О Оµ','О О±','ОЈО±'],
		weekHeader: 'О•ОІОґ',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['el']);
});
/* English/Australia initialisation for the jQuery UI date picker plugin. */
/* Based on the en-GB initialisation. */
jQuery(function($){
	$.datepicker.regional['en-AU'] = {
		closeText: 'Done',
		prevText: 'Prev',
		nextText: 'Next',
		currentText: 'Today',
		monthNames: ['January','February','March','April','May','June',
		'July','August','September','October','November','December'],
		monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
		'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['en-AU']);
});

/* English/UK initialisation for the jQuery UI date picker plugin. */
/* Written by Stuart. */
jQuery(function($){
	$.datepicker.regional['en-GB'] = {
		closeText: 'Done',
		prevText: 'Prev',
		nextText: 'Next',
		currentText: 'Today',
		monthNames: ['January','February','March','April','May','June',
		'July','August','September','October','November','December'],
		monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
		'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['en-GB']);
});

/* English/New Zealand initialisation for the jQuery UI date picker plugin. */
/* Based on the en-GB initialisation. */
jQuery(function($){
	$.datepicker.regional['en-NZ'] = {
		closeText: 'Done',
		prevText: 'Prev',
		nextText: 'Next',
		currentText: 'Today',
		monthNames: ['January','February','March','April','May','June',
		'July','August','September','October','November','December'],
		monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
		'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['en-NZ']);
});

/* Esperanto initialisation for the jQuery UI date picker plugin. */
/* Written by Olivier M. (olivierweb@ifrance.com). */
jQuery(function($){
	$.datepicker.regional['eo'] = {
		closeText: 'Fermi',
		prevText: '&lt;Anta',
		nextText: 'Sekv&gt;',
		currentText: 'Nuna',
		monthNames: ['Januaro','Februaro','Marto','Aprilo','Majo','Junio',
		'Julio','AЕ­gusto','Septembro','Oktobro','Novembro','Decembro'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
		'Jul','AЕ­g','Sep','Okt','Nov','Dec'],
		dayNames: ['DimanД‰o','Lundo','Mardo','Merkredo','ДґaЕ­do','Vendredo','Sabato'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','ДґaЕ­','Ven','Sab'],
		dayNamesMin: ['Di','Lu','Ma','Me','Дґa','Ve','Sa'],
		weekHeader: 'Sb',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['eo']);
});

/* InicializaciГіn en espaГ±ol para la extensiГіn 'UI date picker' para jQuery. */
/* Traducido por Vester (xvester@gmail.com). */
jQuery(function($){
	$.datepicker.regional['es'] = {
		closeText: 'Cerrar',
		prevText: '&#x3c;Ant',
		nextText: 'Sig&#x3e;',
		currentText: 'Hoy',
		monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
		'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
		monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
		'Jul','Ago','Sep','Oct','Nov','Dic'],
		dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
		dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
		dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['es']);
});
/* Estonian initialisation for the jQuery UI date picker plugin. */
/* Written by Mart SГµmermaa (mrts.pydev at gmail com). */
jQuery(function($){
	$.datepicker.regional['et'] = {
		closeText: 'Sulge',
		prevText: 'Eelnev',
		nextText: 'JГ¤rgnev',
		currentText: 'TГ¤na',
		monthNames: ['Jaanuar','Veebruar','MГ¤rts','Aprill','Mai','Juuni',
		'Juuli','August','September','Oktoober','November','Detsember'],
		monthNamesShort: ['Jaan', 'Veebr', 'MГ¤rts', 'Apr', 'Mai', 'Juuni',
		'Juuli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dets'],
		dayNames: ['PГјhapГ¤ev', 'EsmaspГ¤ev', 'TeisipГ¤ev', 'KolmapГ¤ev', 'NeljapГ¤ev', 'Reede', 'LaupГ¤ev'],
		dayNamesShort: ['PГјhap', 'Esmasp', 'Teisip', 'Kolmap', 'Neljap', 'Reede', 'Laup'],
		dayNamesMin: ['P','E','T','K','N','R','L'],
		weekHeader: 'nГ¤d',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['et']);
}); 
/* Euskarako oinarria 'UI date picker' jquery-ko extentsioarentzat */
/* Karrikas-ek itzulia (karrikas@karrikas.com) */
jQuery(function($){
	$.datepicker.regional['eu'] = {
		closeText: 'Egina',
		prevText: '&#x3c;Aur',
		nextText: 'Hur&#x3e;',
		currentText: 'Gaur',
		monthNames: ['urtarrila','otsaila','martxoa','apirila','maiatza','ekaina',
			'uztaila','abuztua','iraila','urria','azaroa','abendua'],
		monthNamesShort: ['urt.','ots.','mar.','api.','mai.','eka.',
			'uzt.','abu.','ira.','urr.','aza.','abe.'],
		dayNames: ['igandea','astelehena','asteartea','asteazkena','osteguna','ostirala','larunbata'],
		dayNamesShort: ['ig.','al.','ar.','az.','og.','ol.','lr.'],
		dayNamesMin: ['ig','al','ar','az','og','ol','lr'],
		weekHeader: 'As',
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['eu']);
});
/* Persian (Farsi) Translation for the jQuery UI date picker plugin. */
/* Javad Mowlanezhad -- jmowla@gmail.com */
/* Jalali calendar should supported soon! (Its implemented but I have to test it) */
jQuery(function($) {
	$.datepicker.regional['fa'] = {
		closeText: 'ШЁШіШЄЩ†',
		prevText: '&#x3C;Щ‚ШЁЩ„ЫЊ',
		nextText: 'ШЁШ№ШЇЫЊ&#x3E;',
		currentText: 'Ш§Щ…Ш±Щ€ШІ',
		monthNames: [
			'ЩЃШ±Щ€Ш±ШЇЩЉЩ†',
			'Ш§Ш±ШЇЩЉШЁЩ‡ШґШЄ',
			'Ш®Ш±ШЇШ§ШЇ',
			'ШЄЩЉШ±',
			'Щ…Ш±ШЇШ§ШЇ',
			'ШґЩ‡Ш±ЩЉЩ€Ш±',
			'Щ…Щ‡Ш±',
			'ШўШЁШ§Щ†',
			'ШўШ°Ш±',
			'ШЇЫЊ',
			'ШЁЩ‡Щ…Щ†',
			'Ш§ШіЩЃЩ†ШЇ'
		],
		monthNamesShort: ['1','2','3','4','5','6','7','8','9','10','11','12'],
		dayNames: [
			'ЩЉЪ©ШґЩ†ШЁЩ‡',
			'ШЇЩ€ШґЩ†ШЁЩ‡',
			'ШіЩ‡вЂЊШґЩ†ШЁЩ‡',
			'Ъ†Щ‡Ш§Ш±ШґЩ†ШЁЩ‡',
			'ЩѕЩ†Ш¬ШґЩ†ШЁЩ‡',
			'Ш¬Щ…Ш№Щ‡',
			'ШґЩ†ШЁЩ‡'
		],
		dayNamesShort: [
			'ЫЊ',
			'ШЇ',
			'Ші',
			'Ъ†',
			'Щѕ',
			'Ш¬', 
			'Шґ'
		],
		dayNamesMin: [
			'ЫЊ',
			'ШЇ',
			'Ші',
			'Ъ†',
			'Щѕ',
			'Ш¬', 
			'Шґ'
		],
		weekHeader: 'Щ‡ЩЃ',
		dateFormat: 'yy/mm/dd',
		firstDay: 6,
		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fa']);
});
/* Finnish initialisation for the jQuery UI date picker plugin. */
/* Written by Harri KilpiГ¶ (harrikilpio@gmail.com). */
jQuery(function($){
	$.datepicker.regional['fi'] = {
		closeText: 'Sulje',
		prevText: '&#xAB;Edellinen',
		nextText: 'Seuraava&#xBB;',
		currentText: 'T&#xE4;n&#xE4;&#xE4;n',
		monthNames: ['Tammikuu','Helmikuu','Maaliskuu','Huhtikuu','Toukokuu','Kes&#xE4;kuu',
		'Hein&#xE4;kuu','Elokuu','Syyskuu','Lokakuu','Marraskuu','Joulukuu'],
		monthNamesShort: ['Tammi','Helmi','Maalis','Huhti','Touko','Kes&#xE4;',
		'Hein&#xE4;','Elo','Syys','Loka','Marras','Joulu'],
		dayNamesShort: ['Su','Ma','Ti','Ke','To','Pe','La'],
		dayNames: ['Sunnuntai','Maanantai','Tiistai','Keskiviikko','Torstai','Perjantai','Lauantai'],
		dayNamesMin: ['Su','Ma','Ti','Ke','To','Pe','La'],
		weekHeader: 'Vk',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fi']);
});

/* Faroese initialisation for the jQuery UI date picker plugin */
/* Written by Sverri Mohr Olsen, sverrimo@gmail.com */
jQuery(function($){
	$.datepicker.regional['fo'] = {
		closeText: 'Lat aftur',
		prevText: '&#x3c;Fyrra',
		nextText: 'NГ¦sta&#x3e;',
		currentText: 'ГЌ dag',
		monthNames: ['Januar','Februar','Mars','AprГ­l','Mei','Juni',
		'Juli','August','September','Oktober','November','Desember'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Mei','Jun',
		'Jul','Aug','Sep','Okt','Nov','Des'],
		dayNames: ['Sunnudagur','MГЎnadagur','TГЅsdagur','Mikudagur','HГіsdagur','FrГ­ggjadagur','Leyardagur'],
		dayNamesShort: ['Sun','MГЎn','TГЅs','Mik','HГіs','FrГ­','Ley'],
		dayNamesMin: ['Su','MГЎ','TГЅ','Mi','HГі','Fr','Le'],
		weekHeader: 'Vk',
		dateFormat: 'dd-mm-yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fo']);
});

/* Swiss-French initialisation for the jQuery UI date picker plugin. */
/* Written Martin Voelkle (martin.voelkle@e-tc.ch). */
jQuery(function($){
	$.datepicker.regional['fr-CH'] = {
		closeText: 'Fermer',
		prevText: '&#x3c;PrГ©c',
		nextText: 'Suiv&#x3e;',
		currentText: 'Courant',
		monthNames: ['Janvier','FГ©vrier','Mars','Avril','Mai','Juin',
		'Juillet','AoГ»t','Septembre','Octobre','Novembre','DГ©cembre'],
		monthNamesShort: ['Jan','FГ©v','Mar','Avr','Mai','Jun',
		'Jul','AoГ»','Sep','Oct','Nov','DГ©c'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		weekHeader: 'Sm',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fr-CH']);
});
/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood{at}iinet.com.au),
              StГ©phane Nahmani (sholby@sholby.net),
              StГ©phane Raimbault <stephane.raimbault@gmail.com> */
jQuery(function($){
	$.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: 'PrГ©cГ©dent',
		nextText: 'Suivant',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier','FГ©vrier','Mars','Avril','Mai','Juin',
		'Juillet','AoГ»t','Septembre','Octobre','Novembre','DГ©cembre'],
		monthNamesShort: ['Janv.','FГ©vr.','Mars','Avril','Mai','Juin',
		'Juil.','AoГ»t','Sept.','Oct.','Nov.','DГ©c.'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'],
		dayNamesMin: ['D','L','M','M','J','V','S'],
		weekHeader: 'Sem.',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fr']);
});

/* Galician localization for 'UI date picker' jQuery extension. */
/* Translated by Jorge Barreiro <yortx.barry@gmail.com>. */
jQuery(function($){
	$.datepicker.regional['gl'] = {
		closeText: 'Pechar',
		prevText: '&#x3c;Ant',
		nextText: 'Seg&#x3e;',
		currentText: 'Hoxe',
		monthNames: ['Xaneiro','Febreiro','Marzo','Abril','Maio','XuГ±o',
		'Xullo','Agosto','Setembro','Outubro','Novembro','Decembro'],
		monthNamesShort: ['Xan','Feb','Mar','Abr','Mai','XuГ±',
		'Xul','Ago','Set','Out','Nov','Dec'],
		dayNames: ['Domingo','Luns','Martes','M&eacute;rcores','Xoves','Venres','S&aacute;bado'],
		dayNamesShort: ['Dom','Lun','Mar','M&eacute;r','Xov','Ven','S&aacute;b'],
		dayNamesMin: ['Do','Lu','Ma','M&eacute;','Xo','Ve','S&aacute;'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['gl']);
});
/* Hebrew initialisation for the UI Datepicker extension. */
/* Written by Amir Hardon (ahardon at gmail dot com). */
jQuery(function($){
	$.datepicker.regional['he'] = {
		closeText: 'ЧЎЧ’Ч•ЧЁ',
		prevText: '&#x3c;Ч”Ч§Ч•Ч“Чќ',
		nextText: 'Ч”Ч‘Чђ&#x3e;',
		currentText: 'Ч”Ч™Ч•Чќ',
		monthNames: ['Ч™Ч Ч•ЧђЧЁ','Ч¤Ч‘ЧЁЧ•ЧђЧЁ','ЧћЧЁЧҐ','ЧђЧ¤ЧЁЧ™Чњ','ЧћЧђЧ™','Ч™Ч•Ч Ч™',
		'Ч™Ч•ЧњЧ™','ЧђЧ•Ч’Ч•ЧЎЧ�','ЧЎЧ¤Ч�ЧћЧ‘ЧЁ','ЧђЧ•Ч§Ч�Ч•Ч‘ЧЁ','Ч Ч•Ч‘ЧћЧ‘ЧЁ','Ч“Ч¦ЧћЧ‘ЧЁ'],
		monthNamesShort: ['Ч™Ч Ч•','Ч¤Ч‘ЧЁ','ЧћЧЁЧҐ','ЧђЧ¤ЧЁ','ЧћЧђЧ™','Ч™Ч•Ч Ч™',
		'Ч™Ч•ЧњЧ™','ЧђЧ•Ч’','ЧЎЧ¤Ч�','ЧђЧ•Ч§','Ч Ч•Ч‘','Ч“Ч¦Чћ'],
		dayNames: ['ЧЁЧђЧ©Ч•Чџ','Ч©Ч Ч™','Ч©ЧњЧ™Ч©Ч™','ЧЁЧ‘Ч™ЧўЧ™','Ч—ЧћЧ™Ч©Ч™','Ч©Ч™Ч©Ч™','Ч©Ч‘ЧЄ'],
		dayNamesShort: ['Чђ\'','Ч‘\'','Ч’\'','Ч“\'','Ч”\'','Ч•\'','Ч©Ч‘ЧЄ'],
		dayNamesMin: ['Чђ\'','Ч‘\'','Ч’\'','Ч“\'','Ч”\'','Ч•\'','Ч©Ч‘ЧЄ'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: true,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['he']);
});

/* Hindi initialisation for the jQuery UI date picker plugin. */
/* Written by Michael Dawart. */
jQuery(function($){
	$.datepicker.regional['hi'] = {
		closeText: 'а¤¬а¤‚а¤¦',
		prevText: 'а¤Єа¤їа¤›а¤Іа¤ѕ',
		nextText: 'а¤…а¤—а¤Іа¤ѕ',
		currentText: 'а¤†а¤њ',
		monthNames: ['а¤ња¤Ёа¤µа¤°аҐЂ ','а¤«а¤°а¤µа¤°аҐЂ','а¤®а¤ѕа¤°аҐЌа¤љ','а¤…а¤ЄаҐЌа¤°аҐ‡а¤І','а¤®а¤€','а¤њаҐ‚а¤Ё',
		'а¤њаҐ‚а¤Іа¤ѕа¤€','а¤…а¤—а¤ёаҐЌа¤¤ ','а¤ёа¤їа¤¤а¤®аҐЌа¤¬а¤°','а¤…а¤•аҐЌа¤џаҐ‚а¤¬а¤°','а¤Ёа¤µа¤®аҐЌа¤¬а¤°','а¤¦а¤їа¤ёа¤®аҐЌа¤¬а¤°'],
		monthNamesShort: ['а¤ња¤Ё', 'а¤«а¤°', 'а¤®а¤ѕа¤°аҐЌа¤љ', 'а¤…а¤ЄаҐЌа¤°аҐ‡а¤І', 'а¤®а¤€', 'а¤њаҐ‚а¤Ё',
		'а¤њаҐ‚а¤Іа¤ѕа¤€', 'а¤…а¤—', 'а¤ёа¤їа¤¤', 'а¤…а¤•аҐЌа¤џ', 'а¤Ёа¤µ', 'а¤¦а¤ї'],
		dayNames: ['а¤°а¤µа¤їа¤µа¤ѕа¤°', 'а¤ёаҐ‹а¤®а¤µа¤ѕа¤°', 'а¤®а¤‚а¤—а¤Іа¤µа¤ѕа¤°', 'а¤¬аҐЃа¤§а¤µа¤ѕа¤°', 'а¤—аҐЃа¤°аҐЃа¤µа¤ѕа¤°', 'а¤¶аҐЃа¤•аҐЌа¤°а¤µа¤ѕа¤°', 'а¤¶а¤Ёа¤їа¤µа¤ѕа¤°'],
		dayNamesShort: ['а¤°а¤µа¤ї', 'а¤ёаҐ‹а¤®', 'а¤®а¤‚а¤—а¤І', 'а¤¬аҐЃа¤§', 'а¤—аҐЃа¤°аҐЃ', 'а¤¶аҐЃа¤•аҐЌа¤°', 'а¤¶а¤Ёа¤ї'],
		dayNamesMin: ['а¤°а¤µа¤ї', 'а¤ёаҐ‹а¤®', 'а¤®а¤‚а¤—а¤І', 'а¤¬аҐЃа¤§', 'а¤—аҐЃа¤°аҐЃ', 'а¤¶аҐЃа¤•аҐЌа¤°', 'а¤¶а¤Ёа¤ї'],
		weekHeader: 'а¤№а¤«аҐЌа¤¤а¤ѕ',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['hi']);
});

/* Croatian i18n for the jQuery UI date picker plugin. */
/* Written by Vjekoslav Nesek. */
jQuery(function($){
	$.datepicker.regional['hr'] = {
		closeText: 'Zatvori',
		prevText: '&#x3c;',
		nextText: '&#x3e;',
		currentText: 'Danas',
		monthNames: ['SijeДЌanj','VeljaДЌa','OЕѕujak','Travanj','Svibanj','Lipanj',
		'Srpanj','Kolovoz','Rujan','Listopad','Studeni','Prosinac'],
		monthNamesShort: ['Sij','Velj','OЕѕu','Tra','Svi','Lip',
		'Srp','Kol','Ruj','Lis','Stu','Pro'],
		dayNames: ['Nedjelja','Ponedjeljak','Utorak','Srijeda','ДЊetvrtak','Petak','Subota'],
		dayNamesShort: ['Ned','Pon','Uto','Sri','ДЊet','Pet','Sub'],
		dayNamesMin: ['Ne','Po','Ut','Sr','ДЊe','Pe','Su'],
		weekHeader: 'Tje',
		dateFormat: 'dd.mm.yy.',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['hr']);
});
/* Hungarian initialisation for the jQuery UI date picker plugin. */
/* Written by Istvan Karaszi (jquery@spam.raszi.hu). */
jQuery(function($){
	$.datepicker.regional['hu'] = {
		closeText: 'bezГЎr',
		prevText: 'vissza',
		nextText: 'elЕ‘re',
		currentText: 'ma',
		monthNames: ['JanuГЎr', 'FebruГЎr', 'MГЎrcius', 'ГЃprilis', 'MГЎjus', 'JГєnius',
		'JГєlius', 'Augusztus', 'Szeptember', 'OktГіber', 'November', 'December'],
		monthNamesShort: ['Jan', 'Feb', 'MГЎr', 'ГЃpr', 'MГЎj', 'JГєn',
		'JГєl', 'Aug', 'Szep', 'Okt', 'Nov', 'Dec'],
		dayNames: ['VasГЎrnap', 'HГ©tfЕ‘', 'Kedd', 'Szerda', 'CsГјtГ¶rtГ¶k', 'PГ©ntek', 'Szombat'],
		dayNamesShort: ['Vas', 'HГ©t', 'Ked', 'Sze', 'CsГј', 'PГ©n', 'Szo'],
		dayNamesMin: ['V', 'H', 'K', 'Sze', 'Cs', 'P', 'Szo'],
		weekHeader: 'HГ©t',
		dateFormat: 'yy.mm.dd.',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['hu']);
});

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
/* Indonesian initialisation for the jQuery UI date picker plugin. */
/* Written by Deden Fathurahman (dedenf@gmail.com). */
jQuery(function($){
	$.datepicker.regional['id'] = {
		closeText: 'Tutup',
		prevText: '&#x3c;mundur',
		nextText: 'maju&#x3e;',
		currentText: 'hari ini',
		monthNames: ['Januari','Februari','Maret','April','Mei','Juni',
		'Juli','Agustus','September','Oktober','Nopember','Desember'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Mei','Jun',
		'Jul','Agus','Sep','Okt','Nop','Des'],
		dayNames: ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
		dayNamesShort: ['Min','Sen','Sel','Rab','kam','Jum','Sab'],
		dayNamesMin: ['Mg','Sn','Sl','Rb','Km','jm','Sb'],
		weekHeader: 'Mg',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['id']);
});
/* Icelandic initialisation for the jQuery UI date picker plugin. */
/* Written by Haukur H. Thorsson (haukur@eskill.is). */
jQuery(function($){
	$.datepicker.regional['is'] = {
		closeText: 'Loka',
		prevText: '&#x3c; Fyrri',
		nextText: 'N&aelig;sti &#x3e;',
		currentText: '&Iacute; dag',
		monthNames: ['Jan&uacute;ar','Febr&uacute;ar','Mars','Apr&iacute;l','Ma&iacute','J&uacute;n&iacute;',
		'J&uacute;l&iacute;','&Aacute;g&uacute;st','September','Okt&oacute;ber','N&oacute;vember','Desember'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Ma&iacute;','J&uacute;n',
		'J&uacute;l','&Aacute;g&uacute;','Sep','Okt','N&oacute;v','Des'],
		dayNames: ['Sunnudagur','M&aacute;nudagur','&THORN;ri&eth;judagur','Mi&eth;vikudagur','Fimmtudagur','F&ouml;studagur','Laugardagur'],
		dayNamesShort: ['Sun','M&aacute;n','&THORN;ri','Mi&eth;','Fim','F&ouml;s','Lau'],
		dayNamesMin: ['Su','M&aacute;','&THORN;r','Mi','Fi','F&ouml;','La'],
		weekHeader: 'Vika',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['is']);
});
/* Italian initialisation for the jQuery UI date picker plugin. */
/* Written by Antonello Pasella (antonello.pasella@gmail.com). */
jQuery(function($){
	$.datepicker.regional['it'] = {
		closeText: 'Chiudi',
		prevText: '&#x3c;Prec',
		nextText: 'Succ&#x3e;',
		currentText: 'Oggi',
		monthNames: ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
			'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
		monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
			'Lug','Ago','Set','Ott','Nov','Dic'],
		dayNames: ['Domenica','Luned&#236','Marted&#236','Mercoled&#236','Gioved&#236','Venerd&#236','Sabato'],
		dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
		dayNamesMin: ['Do','Lu','Ma','Me','Gi','Ve','Sa'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['it']);
});

/* Japanese initialisation for the jQuery UI date picker plugin. */
/* Written by Kentaro SATO (kentaro@ranvis.com). */
jQuery(function($){
	$.datepicker.regional['ja'] = {
		closeText: 'й–‰гЃ�г‚‹',
		prevText: '&#x3c;е‰Ќ',
		nextText: 'ж¬Ў&#x3e;',
		currentText: 'д»Љж—Ґ',
		monthNames: ['1жњ€','2жњ€','3жњ€','4жњ€','5жњ€','6жњ€',
		'7жњ€','8жњ€','9жњ€','10жњ€','11жњ€','12жњ€'],
		monthNamesShort: ['1жњ€','2жњ€','3жњ€','4жњ€','5жњ€','6жњ€',
		'7жњ€','8жњ€','9жњ€','10жњ€','11жњ€','12жњ€'],
		dayNames: ['ж—Ґж›њж—Ґ','жњ€ж›њж—Ґ','зЃ«ж›њж—Ґ','ж°ґж›њж—Ґ','жњЁж›њж—Ґ','й‡‘ж›њж—Ґ','ењџж›њж—Ґ'],
		dayNamesShort: ['ж—Ґ','жњ€','зЃ«','ж°ґ','жњЁ','й‡‘','ењџ'],
		dayNamesMin: ['ж—Ґ','жњ€','зЃ«','ж°ґ','жњЁ','й‡‘','ењџ'],
		weekHeader: 'йЂ±',
		dateFormat: 'yy/mm/dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'е№ґ'};
	$.datepicker.setDefaults($.datepicker.regional['ja']);
});
/* Georgian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Lado Lomidze (lado.lomidze@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ka'] = {
		closeText: 'бѓ“бѓђбѓ®бѓЈбѓ бѓ•бѓђ',
		prevText: '&#x3c; бѓ¬бѓ�бѓњбѓђ',
		nextText: 'бѓЁбѓ”бѓ›бѓ“бѓ”бѓ’бѓ� &#x3e;',
		currentText: 'бѓ“бѓ¦бѓ”бѓЎ',
		monthNames: ['бѓ�бѓђбѓњбѓ•бѓђбѓ бѓ�','бѓ—бѓ”бѓ‘бѓ”бѓ бѓ•бѓђбѓљбѓ�','бѓ›бѓђбѓ бѓўбѓ�','бѓђбѓћбѓ бѓ�бѓљбѓ�','бѓ›бѓђбѓ�бѓЎбѓ�','бѓ�бѓ•бѓњбѓ�бѓЎбѓ�', 'бѓ�бѓ•бѓљбѓ�бѓЎбѓ�','бѓђбѓ’бѓ•бѓ�бѓЎбѓўбѓќ','бѓЎбѓ”бѓҐбѓўбѓ”бѓ›бѓ‘бѓ”бѓ бѓ�','бѓќбѓҐбѓўбѓќбѓ›бѓ‘бѓ”бѓ бѓ�','бѓњбѓќбѓ”бѓ›бѓ‘бѓ”бѓ бѓ�','бѓ“бѓ”бѓ™бѓ”бѓ›бѓ‘бѓ”бѓ бѓ�'],
		monthNamesShort: ['бѓ�бѓђбѓњ','бѓ—бѓ”бѓ‘','бѓ›бѓђбѓ ','бѓђбѓћбѓ ','бѓ›бѓђбѓ�','бѓ�бѓ•бѓњ', 'бѓ�бѓ•бѓљ','бѓђбѓ’бѓ•','бѓЎбѓ”бѓҐ','бѓќбѓҐбѓў','бѓњбѓќбѓ”','бѓ“бѓ”бѓ™'],
		dayNames: ['бѓ™бѓ•бѓ�бѓ бѓђ','бѓќбѓ бѓЁбѓђбѓ‘бѓђбѓ—бѓ�','бѓЎбѓђбѓ›бѓЁбѓђбѓ‘бѓђбѓ—бѓ�','бѓќбѓ—бѓ®бѓЁбѓђбѓ‘бѓђбѓ—бѓ�','бѓ®бѓЈбѓ—бѓЁбѓђбѓ‘бѓђбѓ—бѓ�','бѓћбѓђбѓ бѓђбѓЎбѓ™бѓ”бѓ•бѓ�','бѓЁбѓђбѓ‘бѓђбѓ—бѓ�'],
		dayNamesShort: ['бѓ™бѓ•','бѓќбѓ бѓЁ','бѓЎбѓђбѓ›','бѓќбѓ—бѓ®','бѓ®бѓЈбѓ—','бѓћбѓђбѓ ','бѓЁбѓђбѓ‘'],
		dayNamesMin: ['бѓ™бѓ•','бѓќбѓ бѓЁ','бѓЎбѓђбѓ›','бѓќбѓ—бѓ®','бѓ®бѓЈбѓ—','бѓћбѓђбѓ ','бѓЁбѓђбѓ‘'],
		weekHeader: 'бѓ™бѓ•бѓ�бѓ бѓђ',
		dateFormat: 'dd-mm-yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ka']);
});

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

/* Khmer initialisation for the jQuery calendar extension. */
/* Written by Chandara Om (chandara.teacher@gmail.com). */
jQuery(function($){
	$.datepicker.regional['km'] = {
		closeText: 'бћ’бџ’бћњбћѕвЂ‹бћљбћЅбћ…',
		prevText: 'бћ�бћ»бћ“',
		nextText: 'бћ”бћ“бџ’бћ‘бћ¶бћ”бџ‹',
		currentText: 'бћђбџ’бћ„бџѓвЂ‹бћ“бџЃбџ‡',
		monthNames: ['бћ�бћЂбћљбћ¶','бћЂбћ»бћ�бџ’бћ—бџ€','бћ�бћёбћ“бћ¶','бћ�бџЃбћџбћ¶','бћ§бћџбћ—бћ¶','бћ�бћ·бћђбћ»бћ“бћ¶',
		'бћЂбћЂбџ’бћЂбћЉбћ¶','бћџбћёбћ бћ¶','бћЂбћ‰бџ’бћ‰бћ¶','бћЏбћ»бћ›бћ¶','бћњбћ·бћ…бџ’бћ†бћ·бћЂбћ¶','бћ’бџ’бћ“бћј'],
		monthNamesShort: ['бћ�бћЂбћљбћ¶','бћЂбћ»бћ�бџ’бћ—бџ€','бћ�бћёбћ“бћ¶','бћ�бџЃбћџбћ¶','бћ§бћџбћ—бћ¶','бћ�бћ·бћђбћ»бћ“бћ¶',
		'бћЂбћЂбџ’бћЂбћЉбћ¶','бћџбћёбћ бћ¶','бћЂбћ‰бџ’бћ‰бћ¶','бћЏбћ»бћ›бћ¶','бћњбћ·бћ…бџ’бћ†бћ·бћЂбћ¶','бћ’бџ’бћ“бћј'],
		dayNames: ['бћўбћ¶бћ‘бћ·бћЏбџ’бћ™', 'бћ…бћ“бџ’бћ‘', 'бћўбћ„бџ’бћ‚бћ¶бћљ', 'бћ–бћ»бћ’', 'бћ–бџ’бћљбћ бћџбџ’бћ”бћЏбћ·бџЌ', 'бћџбћ»бћЂбџ’бћљ', 'бћџбџ…бћљбџЌ'],
		dayNamesShort: ['бћўбћ¶', 'бћ…', 'бћў', 'бћ–бћ»', 'бћ–бџ’бћљбћ ', 'бћџбћ»', 'бћџбџ…'],
		dayNamesMin: ['бћўбћ¶', 'бћ…', 'бћў', 'бћ–бћ»', 'бћ–бџ’бћљбћ ', 'бћџбћ»', 'бћџбџ…'],
		weekHeader: 'бћџбћ”бџ’бћЉбћ¶бћ бџЌ',
		dateFormat: 'dd-mm-yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['km']);
});

/* Korean initialisation for the jQuery calendar extension. */
/* Written by DaeKwon Kang (ncrash.dk@gmail.com), Edited by Genie. */
jQuery(function($){
	$.datepicker.regional['ko'] = {
		closeText: 'л‹«кё°',
		prevText: 'мќґм „л‹¬',
		nextText: 'л‹¤мќЊл‹¬',
		currentText: 'м�¤лЉ�',
		monthNames: ['1м›”','2м›”','3м›”','4м›”','5м›”','6м›”',
		'7м›”','8м›”','9м›”','10м›”','11м›”','12м›”'],
		monthNamesShort: ['1м›”','2м›”','3м›”','4м›”','5м›”','6м›”',
		'7м›”','8м›”','9м›”','10м›”','11м›”','12м›”'],
		dayNames: ['мќјмљ”мќј','м›”мљ”мќј','н™”мљ”мќј','м€�мљ”мќј','лЄ©мљ”мќј','кё€мљ”мќј','н† мљ”мќј'],
		dayNamesShort: ['мќј','м›”','н™”','м€�','лЄ©','кё€','н† '],
		dayNamesMin: ['мќј','м›”','н™”','м€�','лЄ©','кё€','н† '],
		weekHeader: 'Wk',
		dateFormat: 'yy-mm-dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'л…„'};
	$.datepicker.setDefaults($.datepicker.regional['ko']);
});
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
/* Latvian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* @author Arturas Paleicikas <arturas.paleicikas@metasite.net> */
jQuery(function($){
	$.datepicker.regional['lv'] = {
		closeText: 'AizvД“rt',
		prevText: 'Iepr',
		nextText: 'NДЃka',
		currentText: 'Е odien',
		monthNames: ['JanvДЃris','FebruДЃris','Marts','AprД«lis','Maijs','JЕ«nijs',
		'JЕ«lijs','Augusts','Septembris','Oktobris','Novembris','Decembris'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Mai','JЕ«n',
		'JЕ«l','Aug','Sep','Okt','Nov','Dec'],
		dayNames: ['svД“tdiena','pirmdiena','otrdiena','treЕЎdiena','ceturtdiena','piektdiena','sestdiena'],
		dayNamesShort: ['svt','prm','otr','tre','ctr','pkt','sst'],
		dayNamesMin: ['Sv','Pr','Ot','Tr','Ct','Pk','Ss'],
		weekHeader: 'Nav',
		dateFormat: 'dd-mm-yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['lv']);
});
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

/* Malayalam (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Saji Nediyanchath (saji89@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ml'] = {
		closeText: 'аґ¶аґ°аґї',
		prevText: 'аґ®аµЃаґЁаµЌаґЁаґ¤аµЌаґ¤аµ†',  
		nextText: 'аґ…аґџаµЃаґ¤аµЌаґ¤аґ¤аµЌ ',
		currentText: 'аґ‡аґЁаµЌаґЁаµЌ',
		monthNames: ['аґњаґЁаµЃаґµаґ°аґї','аґ«аµ†аґ¬аµЌаґ°аµЃаґµаґ°аґї','аґ®аґѕаґ°аµЌвЂЌаґљаµЌаґљаµЌ','аґЏаґЄаµЌаґ°аґїаґІаµЌвЂЌ','аґ®аµ‡аґЇаµЌ','аґњаµ‚аґЈаµЌвЂЌ',
		'аґњаµ‚аґІаµ€','аґ†аґ—аґёаµЌаґ±аµЌаґ±аµЌ','аґёаµ†аґЄаµЌаґ±аµЌаґ±аґ‚аґ¬аґ°аµЌвЂЌ','аґ’аґ•аµЌаґџаµ‹аґ¬аґ°аµЌвЂЌ','аґЁаґµаґ‚аґ¬аґ°аµЌвЂЌ','аґЎаґїаґёаґ‚аґ¬аґ°аµЌвЂЌ'],
		monthNamesShort: ['аґњаґЁаµЃ', 'аґ«аµ†аґ¬аµЌ', 'аґ®аґѕаґ°аµЌвЂЌ', 'аґЏаґЄаµЌаґ°аґї', 'аґ®аµ‡аґЇаµЌ', 'аґњаµ‚аґЈаµЌвЂЌ',
		'аґњаµ‚аґІаґѕ', 'аґ†аґ—', 'аґёаµ†аґЄаµЌ', 'аґ’аґ•аµЌаґџаµ‹', 'аґЁаґµаґ‚', 'аґЎаґїаґё'],
		dayNames: ['аґћаґѕаґЇаґ°аµЌвЂЌ', 'аґ¤аґїаґ™аµЌаґ•аґіаµЌвЂЌ', 'аґљаµЉаґµаµЌаґµ', 'аґ¬аµЃаґ§аґЁаµЌвЂЌ', 'аґµаµЌаґЇаґѕаґґаґ‚', 'аґµаµ†аґіаµЌаґіаґї', 'аґ¶аґЁаґї'],
		dayNamesShort: ['аґћаґѕаґЇ', 'аґ¤аґїаґ™аµЌаґ•', 'аґљаµЉаґµаµЌаґµ', 'аґ¬аµЃаґ§', 'аґµаµЌаґЇаґѕаґґаґ‚', 'аґµаµ†аґіаµЌаґіаґї', 'аґ¶аґЁаґї'],
		dayNamesMin: ['аґћаґѕ','аґ¤аґї','аґљаµЉ','аґ¬аµЃ','аґµаµЌаґЇаґѕ','аґµаµ†','аґ¶'],
		weekHeader: 'аґ†',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ml']);
});

/* Malaysian initialisation for the jQuery UI date picker plugin. */
/* Written by Mohd Nawawi Mohamad Jamili (nawawi@ronggeng.net). */
jQuery(function($){
	$.datepicker.regional['ms'] = {
		closeText: 'Tutup',
		prevText: '&#x3c;Sebelum',
		nextText: 'Selepas&#x3e;',
		currentText: 'hari ini',
		monthNames: ['Januari','Februari','Mac','April','Mei','Jun',
		'Julai','Ogos','September','Oktober','November','Disember'],
		monthNamesShort: ['Jan','Feb','Mac','Apr','Mei','Jun',
		'Jul','Ogo','Sep','Okt','Nov','Dis'],
		dayNames: ['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'],
		dayNamesShort: ['Aha','Isn','Sel','Rab','kha','Jum','Sab'],
		dayNamesMin: ['Ah','Is','Se','Ra','Kh','Ju','Sa'],
		weekHeader: 'Mg',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ms']);
});
/* Dutch (Belgium) initialisation for the jQuery UI date picker plugin. */
/* David De Sloovere @DavidDeSloovere */
jQuery(function($){
	$.datepicker.regional['nl-BE'] = {
		closeText: 'Sluiten',
		prevText: 'в†ђ',
		nextText: 'в†’',
		currentText: 'Vandaag',
		monthNames: ['januari', 'februari', 'maart', 'april', 'mei', 'juni',
		'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
		monthNamesShort: ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun',
		'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
		dayNames: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
		dayNamesShort: ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'],
		dayNamesMin: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['nl-BE']);
});

/* Dutch (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Mathias Bynens <http://mathiasbynens.be/> */
jQuery(function($){
	$.datepicker.regional.nl = {
		closeText: 'Sluiten',
		prevText: 'в†ђ',
		nextText: 'в†’',
		currentText: 'Vandaag',
		monthNames: ['januari', 'februari', 'maart', 'april', 'mei', 'juni',
		'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
		monthNamesShort: ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun',
		'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
		dayNames: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
		dayNamesShort: ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'],
		dayNamesMin: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
		weekHeader: 'Wk',
		dateFormat: 'dd-mm-yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional.nl);
});
/* Norwegian initialisation for the jQuery UI date picker plugin. */
/* Written by Naimdjon Takhirov (naimdjon@gmail.com). */

jQuery(function($){
  $.datepicker.regional['no'] = {
    closeText: 'Lukk',
    prevText: '&laquo;Forrige',
    nextText: 'Neste&raquo;',
    currentText: 'I dag',
    monthNames: ['januar','februar','mars','april','mai','juni','juli','august','september','oktober','november','desember'],
    monthNamesShort: ['jan','feb','mar','apr','mai','jun','jul','aug','sep','okt','nov','des'],
    dayNamesShort: ['sГёn','man','tir','ons','tor','fre','lГёr'],
    dayNames: ['sГёndag','mandag','tirsdag','onsdag','torsdag','fredag','lГёrdag'],
    dayNamesMin: ['sГё','ma','ti','on','to','fr','lГё'],
    weekHeader: 'Uke',
    dateFormat: 'dd.mm.yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
  };
  $.datepicker.setDefaults($.datepicker.regional['no']);
});

/* Polish initialisation for the jQuery UI date picker plugin. */
/* Written by Jacek Wysocki (jacek.wysocki@gmail.com). */
jQuery(function($){
	$.datepicker.regional['pl'] = {
		closeText: 'Zamknij',
		prevText: '&#x3c;Poprzedni',
		nextText: 'NastД™pny&#x3e;',
		currentText: 'DziЕ›',
		monthNames: ['StyczeЕ„','Luty','Marzec','KwiecieЕ„','Maj','Czerwiec',
		'Lipiec','SierpieЕ„','WrzesieЕ„','PaЕєdziernik','Listopad','GrudzieЕ„'],
		monthNamesShort: ['Sty','Lu','Mar','Kw','Maj','Cze',
		'Lip','Sie','Wrz','Pa','Lis','Gru'],
		dayNames: ['Niedziela','PoniedziaЕ‚ek','Wtorek','Ељroda','Czwartek','PiД…tek','Sobota'],
		dayNamesShort: ['Nie','Pn','Wt','Ељr','Czw','Pt','So'],
		dayNamesMin: ['N','Pn','Wt','Ељr','Cz','Pt','So'],
		weekHeader: 'Tydz',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['pl']);
});

/* Brazilian initialisation for the jQuery UI date picker plugin. */
/* Written by Leonildo Costa Silva (leocsilva@gmail.com). */
jQuery(function($){
	$.datepicker.regional['pt-BR'] = {
		closeText: 'Fechar',
		prevText: '&#x3c;Anterior',
		nextText: 'Pr&oacute;ximo&#x3e;',
		currentText: 'Hoje',
		monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
		'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
		'Jul','Ago','Set','Out','Nov','Dez'],
		dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','S&aacute;bado'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
		dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['pt-BR']);
});
/* Portuguese initialisation for the jQuery UI date picker plugin. */
jQuery(function($){
	$.datepicker.regional['pt'] = {
		closeText: 'Fechar',
		prevText: '&#x3c;Anterior',
		nextText: 'Seguinte',
		currentText: 'Hoje',
		monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
		'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
		'Jul','Ago','Set','Out','Nov','Dez'],
		dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','S&aacute;bado'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
		dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
		weekHeader: 'Sem',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['pt']);
});
/* Romansh initialisation for the jQuery UI date picker plugin. */
/* Written by Yvonne Gienal (yvonne.gienal@educa.ch). */
jQuery(function($){
	$.datepicker.regional['rm'] = {
		closeText: 'Serrar',
		prevText: '&#x3c;Suandant',
		nextText: 'Precedent&#x3e;',
		currentText: 'Actual',
		monthNames: ['Schaner','Favrer','Mars','Avrigl','Matg','Zercladur', 'Fanadur','Avust','Settember','October','November','December'],
		monthNamesShort: ['Scha','Fev','Mar','Avr','Matg','Zer', 'Fan','Avu','Sett','Oct','Nov','Dec'],
		dayNames: ['Dumengia','Glindesdi','Mardi','Mesemna','Gievgia','Venderdi','Sonda'],
		dayNamesShort: ['Dum','Gli','Mar','Mes','Gie','Ven','Som'],
		dayNamesMin: ['Du','Gl','Ma','Me','Gi','Ve','So'],
		weekHeader: 'emna',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['rm']);
});

/* Romanian initialisation for the jQuery UI date picker plugin.
 *
 * Written by Edmond L. (ll_edmond@walla.com)
 * and Ionut G. Stan (ionut.g.stan@gmail.com)
 */
jQuery(function($){
	$.datepicker.regional['ro'] = {
		closeText: 'ГЋnchide',
		prevText: '&laquo; Luna precedentДѓ',
		nextText: 'Luna urmДѓtoare &raquo;',
		currentText: 'Azi',
		monthNames: ['Ianuarie','Februarie','Martie','Aprilie','Mai','Iunie',
		'Iulie','August','Septembrie','Octombrie','Noiembrie','Decembrie'],
		monthNamesShort: ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun',
		'Iul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['DuminicДѓ', 'Luni', 'MarЕЈi', 'Miercuri', 'Joi', 'Vineri', 'SГўmbДѓtДѓ'],
		dayNamesShort: ['Dum', 'Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'SГўm'],
		dayNamesMin: ['Du','Lu','Ma','Mi','Jo','Vi','SГў'],
		weekHeader: 'SДѓpt',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ro']);
});

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

/* Slovenian initialisation for the jQuery UI date picker plugin. */
/* Written by Jaka Jancar (jaka@kubje.org). */
/* c = &#x10D;, s = &#x161; z = &#x17E; C = &#x10C; S = &#x160; Z = &#x17D; */
jQuery(function($){
	$.datepicker.regional['sl'] = {
		closeText: 'Zapri',
		prevText: '&lt;Prej&#x161;nji',
		nextText: 'Naslednji&gt;',
		currentText: 'Trenutni',
		monthNames: ['Januar','Februar','Marec','April','Maj','Junij',
		'Julij','Avgust','September','Oktober','November','December'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
		'Jul','Avg','Sep','Okt','Nov','Dec'],
		dayNames: ['Nedelja','Ponedeljek','Torek','Sreda','&#x10C;etrtek','Petek','Sobota'],
		dayNamesShort: ['Ned','Pon','Tor','Sre','&#x10C;et','Pet','Sob'],
		dayNamesMin: ['Ne','Po','To','Sr','&#x10C;e','Pe','So'],
		weekHeader: 'Teden',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['sl']);
});

/* Albanian initialisation for the jQuery UI date picker plugin. */
/* Written by Flakron Bytyqi (flakron@gmail.com). */
jQuery(function($){
	$.datepicker.regional['sq'] = {
		closeText: 'mbylle',
		prevText: '&#x3c;mbrapa',
		nextText: 'PГ«rpara&#x3e;',
		currentText: 'sot',
		monthNames: ['Janar','Shkurt','Mars','Prill','Maj','Qershor',
		'Korrik','Gusht','Shtator','Tetor','NГ«ntor','Dhjetor'],
		monthNamesShort: ['Jan','Shk','Mar','Pri','Maj','Qer',
		'Kor','Gus','Sht','Tet','NГ«n','Dhj'],
		dayNames: ['E Diel','E HГ«nГ«','E MartГ«','E MГ«rkurГ«','E Enjte','E Premte','E Shtune'],
		dayNamesShort: ['Di','HГ«','Ma','MГ«','En','Pr','Sh'],
		dayNamesMin: ['Di','HГ«','Ma','MГ«','En','Pr','Sh'],
		weekHeader: 'Ja',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['sq']);
});

/* Serbian i18n for the jQuery UI date picker plugin. */
/* Written by Dejan DimiД‡. */
jQuery(function($){
	$.datepicker.regional['sr-SR'] = {
		closeText: 'Zatvori',
		prevText: '&#x3c;',
		nextText: '&#x3e;',
		currentText: 'Danas',
		monthNames: ['Januar','Februar','Mart','April','Maj','Jun',
		'Jul','Avgust','Septembar','Oktobar','Novembar','Decembar'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
		'Jul','Avg','Sep','Okt','Nov','Dec'],
		dayNames: ['Nedelja','Ponedeljak','Utorak','Sreda','ДЊetvrtak','Petak','Subota'],
		dayNamesShort: ['Ned','Pon','Uto','Sre','ДЊet','Pet','Sub'],
		dayNamesMin: ['Ne','Po','Ut','Sr','ДЊe','Pe','Su'],
		weekHeader: 'Sed',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['sr-SR']);
});

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

/* Swedish initialisation for the jQuery UI date picker plugin. */
/* Written by Anders Ekdahl ( anders@nomadiz.se). */
jQuery(function($){
    $.datepicker.regional['sv'] = {
		closeText: 'StГ¤ng',
        prevText: '&laquo;FГ¶rra',
		nextText: 'NГ¤sta&raquo;',
		currentText: 'Idag',
        monthNames: ['Januari','Februari','Mars','April','Maj','Juni',
        'Juli','Augusti','September','Oktober','November','December'],
        monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
        'Jul','Aug','Sep','Okt','Nov','Dec'],
		dayNamesShort: ['SГ¶n','MГҐn','Tis','Ons','Tor','Fre','LГ¶r'],
		dayNames: ['SГ¶ndag','MГҐndag','Tisdag','Onsdag','Torsdag','Fredag','LГ¶rdag'],
		dayNamesMin: ['SГ¶','MГҐ','Ti','On','To','Fr','LГ¶'],
		weekHeader: 'Ve',
        dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['sv']);
});

/* Tamil (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by S A Sureshkumar (saskumar@live.com). */
jQuery(function($){
	$.datepicker.regional['ta'] = {
		closeText: 'а®®аЇ‚а®џаЇЃ',
		prevText: 'а®®аЇЃа®©аЇЌа®©аЇ€а®Їа®¤аЇЃ',
		nextText: 'а®…а®џаЇЃа®¤аЇЌа®¤а®¤аЇЃ',
		currentText: 'а®‡а®©аЇЌа®±аЇЃ',
		monthNames: ['а®¤аЇ€','а®®а®ѕа®ља®ї','а®Єа®™аЇЌа®•аЇЃа®©а®ї','а®ља®їа®¤аЇЌа®¤а®їа®°аЇ€','а®µаЇ€а®•а®ѕа®ља®ї','а®†а®©а®ї',
		'а®†а®џа®ї','а®†а®µа®Ја®ї','а®ЄаЇЃа®°а®џаЇЌа®џа®ѕа®ља®ї','а®ђа®ЄаЇЌа®Єа®ља®ї','а®•а®ѕа®°аЇЌа®¤аЇЌа®¤а®їа®•аЇ€','а®®а®ѕа®°аЇЌа®•а®ґа®ї'],
		monthNamesShort: ['а®¤аЇ€','а®®а®ѕа®ља®ї','а®Єа®™аЇЌ','а®ља®їа®¤аЇЌ','а®µаЇ€а®•а®ѕ','а®†а®©а®ї',
		'а®†а®џа®ї','а®†а®µ','а®ЄаЇЃа®°','а®ђа®ЄаЇЌ','а®•а®ѕа®°аЇЌ','а®®а®ѕа®°аЇЌ'],
		dayNames: ['а®ћа®ѕа®Їа®їа®±аЇЌа®±аЇЃа®•аЇЌа®•а®їа®ґа®®аЇ€','а®¤а®їа®™аЇЌа®•а®џаЇЌа®•а®їа®ґа®®аЇ€','а®љаЇ†а®µаЇЌа®µа®ѕа®ЇаЇЌа®•аЇЌа®•а®їа®ґа®®аЇ€','а®ЄаЇЃа®¤а®©аЇЌа®•а®їа®ґа®®аЇ€','а®µа®їа®Їа®ѕа®ґа®•аЇЌа®•а®їа®ґа®®аЇ€','а®µаЇ†а®іаЇЌа®іа®їа®•аЇЌа®•а®їа®ґа®®аЇ€','а®ља®©а®їа®•аЇЌа®•а®їа®ґа®®аЇ€'],
		dayNamesShort: ['а®ћа®ѕа®Їа®їа®±аЇЃ','а®¤а®їа®™аЇЌа®•а®іаЇЌ','а®љаЇ†а®µаЇЌа®µа®ѕа®ЇаЇЌ','а®ЄаЇЃа®¤а®©аЇЌ','а®µа®їа®Їа®ѕа®ґа®©аЇЌ','а®µаЇ†а®іаЇЌа®іа®ї','а®ља®©а®ї'],
		dayNamesMin: ['а®ћа®ѕ','а®¤а®ї','а®љаЇ†','а®ЄаЇЃ','а®µа®ї','а®µаЇ†','а®љ'],
		weekHeader: 'РќРµ',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ta']);
});

/* Thai initialisation for the jQuery UI date picker plugin. */
/* Written by pipo (pipo@sixhead.com). */
jQuery(function($){
	$.datepicker.regional['th'] = {
		closeText: 'аё›аёґаё”',
		prevText: '&laquo;&nbsp;аёўа№‰аё­аё™',
		nextText: 'аё–аё±аё”а№„аё›&nbsp;&raquo;',
		currentText: 'аё§аё±аё™аё™аёµа№‰',
		monthNames: ['аёЎаёЃаёЈаёІаё„аёЎ','аёЃаёёаёЎаё аёІаёћаё±аё™аё�а№Њ','аёЎаёµаё™аёІаё„аёЎ','а№ЂаёЎаё©аёІаёўаё™','аёћаё¤аё©аё аёІаё„аёЎ','аёЎаёґаё–аёёаё™аёІаёўаё™',
		'аёЃаёЈаёЃаёЋаёІаё„аёЎ','аёЄаёґаё‡аё«аёІаё„аёЎ','аёЃаё±аё™аёўаёІаёўаё™','аё•аёёаёҐаёІаё„аёЎ','аёћаё¤аёЁаё€аёґаёЃаёІаёўаё™','аё�аё±аё™аё§аёІаё„аёЎ'],
		monthNamesShort: ['аёЎ.аё„.','аёЃ.аёћ.','аёЎаёµ.аё„.','а№ЂаёЎ.аёў.','аёћ.аё„.','аёЎаёґ.аёў.',
		'аёЃ.аё„.','аёЄ.аё„.','аёЃ.аёў.','аё•.аё„.','аёћ.аёў.','аё�.аё„.'],
		dayNames: ['аё­аёІаё—аёґаё•аёўа№Њ','аё€аё±аё™аё—аёЈа№Њ','аё­аё±аё‡аё„аёІаёЈ','аёћаёёаё�','аёћаё¤аё«аё±аёЄаёљаё”аёµ','аёЁаёёаёЃаёЈа№Њ','а№ЂаёЄаёІаёЈа№Њ'],
		dayNamesShort: ['аё­аёІ.','аё€.','аё­.','аёћ.','аёћаё¤.','аёЁ.','аёЄ.'],
		dayNamesMin: ['аё­аёІ.','аё€.','аё­.','аёћ.','аёћаё¤.','аёЁ.','аёЄ.'],
		weekHeader: 'Wk',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['th']);
});
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
/* Turkish initialisation for the jQuery UI date picker plugin. */
/* Written by Izzet Emre Erkan (kara@karalamalar.net). */
jQuery(function($){
	$.datepicker.regional['tr'] = {
		closeText: 'kapat',
		prevText: '&#x3c;geri',
		nextText: 'ileri&#x3e',
		currentText: 'bugГјn',
		monthNames: ['Ocak','Ећubat','Mart','Nisan','MayД±s','Haziran',
		'Temmuz','AДџustos','EylГјl','Ekim','KasД±m','AralД±k'],
		monthNamesShort: ['Oca','Ећub','Mar','Nis','May','Haz',
		'Tem','AДџu','Eyl','Eki','Kas','Ara'],
		dayNames: ['Pazar','Pazartesi','SalД±','Г‡arЕџamba','PerЕџembe','Cuma','Cumartesi'],
		dayNamesShort: ['Pz','Pt','Sa','Г‡a','Pe','Cu','Ct'],
		dayNamesMin: ['Pz','Pt','Sa','Г‡a','Pe','Cu','Ct'],
		weekHeader: 'Hf',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['tr']);
});
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
/* Vietnamese initialisation for the jQuery UI date picker plugin. */
/* Translated by Le Thanh Huy (lthanhhuy@cit.ctu.edu.vn). */
jQuery(function($){
	$.datepicker.regional['vi'] = {
		closeText: 'ДђГіng',
		prevText: '&#x3c;TrЖ°б»›c',
		nextText: 'Tiбєїp&#x3e;',
		currentText: 'HГґm nay',
		monthNames: ['ThГЎng Mб»™t', 'ThГЎng Hai', 'ThГЎng Ba', 'ThГЎng TЖ°', 'ThГЎng NДѓm', 'ThГЎng SГЎu',
		'ThГЎng BбєЈy', 'ThГЎng TГЎm', 'ThГЎng ChГ­n', 'ThГЎng MЖ°б»ќi', 'ThГЎng MЖ°б»ќi Mб»™t', 'ThГЎng MЖ°б»ќi Hai'],
		monthNamesShort: ['ThГЎng 1', 'ThГЎng 2', 'ThГЎng 3', 'ThГЎng 4', 'ThГЎng 5', 'ThГЎng 6',
		'ThГЎng 7', 'ThГЎng 8', 'ThГЎng 9', 'ThГЎng 10', 'ThГЎng 11', 'ThГЎng 12'],
		dayNames: ['Chб»§ Nhбє­t', 'Thб»© Hai', 'Thб»© Ba', 'Thб»© TЖ°', 'Thб»© NДѓm', 'Thб»© SГЎu', 'Thб»© BбєЈy'],
		dayNamesShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
		dayNamesMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
		weekHeader: 'Tu',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['vi']);
});

/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by Cloudream (cloudream@gmail.com). */
jQuery(function($){
	$.datepicker.regional['zh-CN'] = {
		closeText: 'е…ій—­',
		prevText: '&#x3c;дёЉжњ€',
		nextText: 'дё‹жњ€&#x3e;',
		currentText: 'д»Ље¤©',
		monthNames: ['дёЂжњ€','дєЊжњ€','дё‰жњ€','е››жњ€','дє”жњ€','е…­жњ€',
		'дёѓжњ€','е…«жњ€','д№ќжњ€','еЌЃжњ€','еЌЃдёЂжњ€','еЌЃдєЊжњ€'],
		monthNamesShort: ['дёЂ','дєЊ','дё‰','е››','дє”','е…­',
		'дёѓ','е…«','д№ќ','еЌЃ','еЌЃдёЂ','еЌЃдєЊ'],
		dayNames: ['ж�џжњџж—Ґ','ж�џжњџдёЂ','ж�џжњџдєЊ','ж�џжњџдё‰','ж�џжњџе››','ж�џжњџдє”','ж�џжњџе…­'],
		dayNamesShort: ['е‘Ёж—Ґ','е‘ЁдёЂ','е‘ЁдєЊ','е‘Ёдё‰','е‘Ёе››','е‘Ёдє”','е‘Ёе…­'],
		dayNamesMin: ['ж—Ґ','дёЂ','дєЊ','дё‰','е››','дє”','е…­'],
		weekHeader: 'е‘Ё',
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'е№ґ'};
	$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
});

/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by SCCY (samuelcychan@gmail.com). */
jQuery(function($){
	$.datepicker.regional['zh-HK'] = {
		closeText: 'й—њй–‰',
		prevText: '&#x3c;дёЉжњ€',
		nextText: 'дё‹жњ€&#x3e;',
		currentText: 'д»Ље¤©',
		monthNames: ['дёЂжњ€','дєЊжњ€','дё‰жњ€','е››жњ€','дє”жњ€','е…­жњ€',
		'дёѓжњ€','е…«жњ€','д№ќжњ€','еЌЃжњ€','еЌЃдёЂжњ€','еЌЃдєЊжњ€'],
		monthNamesShort: ['дёЂ','дєЊ','дё‰','е››','дє”','е…­',
		'дёѓ','е…«','д№ќ','еЌЃ','еЌЃдёЂ','еЌЃдєЊ'],
		dayNames: ['ж�џжњџж—Ґ','ж�џжњџдёЂ','ж�џжњџдєЊ','ж�џжњџдё‰','ж�џжњџе››','ж�џжњџдє”','ж�џжњџе…­'],
		dayNamesShort: ['е‘Ёж—Ґ','е‘ЁдёЂ','е‘ЁдєЊ','е‘Ёдё‰','е‘Ёе››','е‘Ёдє”','е‘Ёе…­'],
		dayNamesMin: ['ж—Ґ','дёЂ','дєЊ','дё‰','е››','дє”','е…­'],
		weekHeader: 'е‘Ё',
		dateFormat: 'dd-mm-yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'е№ґ'};
	$.datepicker.setDefaults($.datepicker.regional['zh-HK']);
});

/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by Ressol (ressol@gmail.com). */
jQuery(function($){
	$.datepicker.regional['zh-TW'] = {
		closeText: 'й—њй–‰',
		prevText: '&#x3c;дёЉжњ€',
		nextText: 'дё‹жњ€&#x3e;',
		currentText: 'д»Ље¤©',
		monthNames: ['дёЂжњ€','дєЊжњ€','дё‰жњ€','е››жњ€','дє”жњ€','е…­жњ€',
		'дёѓжњ€','е…«жњ€','д№ќжњ€','еЌЃжњ€','еЌЃдёЂжњ€','еЌЃдєЊжњ€'],
		monthNamesShort: ['дёЂ','дєЊ','дё‰','е››','дє”','е…­',
		'дёѓ','е…«','д№ќ','еЌЃ','еЌЃдёЂ','еЌЃдєЊ'],
		dayNames: ['ж�џжњџж—Ґ','ж�џжњџдёЂ','ж�џжњџдєЊ','ж�џжњџдё‰','ж�џжњџе››','ж�џжњџдє”','ж�џжњџе…­'],
		dayNamesShort: ['е‘Ёж—Ґ','е‘ЁдёЂ','е‘ЁдєЊ','е‘Ёдё‰','е‘Ёе››','е‘Ёдє”','е‘Ёе…­'],
		dayNamesMin: ['ж—Ґ','дёЂ','дєЊ','дё‰','е››','дє”','е…­'],
		weekHeader: 'е‘Ё',
		dateFormat: 'yy/mm/dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: 'е№ґ'};
	$.datepicker.setDefaults($.datepicker.regional['zh-TW']);
});
