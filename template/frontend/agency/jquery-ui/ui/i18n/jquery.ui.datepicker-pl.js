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
