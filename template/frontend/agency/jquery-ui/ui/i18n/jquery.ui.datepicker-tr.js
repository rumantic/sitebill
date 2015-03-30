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