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