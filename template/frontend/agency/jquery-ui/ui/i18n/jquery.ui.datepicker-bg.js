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
