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
