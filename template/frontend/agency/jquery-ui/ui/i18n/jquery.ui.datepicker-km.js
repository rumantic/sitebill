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