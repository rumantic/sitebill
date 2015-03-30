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