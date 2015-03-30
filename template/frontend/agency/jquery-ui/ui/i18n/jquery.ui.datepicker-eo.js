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
