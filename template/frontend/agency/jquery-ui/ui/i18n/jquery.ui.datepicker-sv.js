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
