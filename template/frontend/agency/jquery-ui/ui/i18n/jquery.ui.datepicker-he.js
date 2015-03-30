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
