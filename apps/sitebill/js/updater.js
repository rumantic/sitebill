$(document).ready(function(){
    run_command('update', 'cp1251', '', '');
	
		if ( update_info_json_string ) {
	        jQuery('#updater_wrapper').html('');
	        ui=JSON.parse(update_info_json_string);
	        //console.log(ui);
	        jQuery.ajax({
	        	url: 'https://www.sitebill.ru/apps/update/js/ajax.php',
	        	async: false,
	        	type: 'get',
	        	dataType: 'jsonp',
	        	data: {lk: ui.license_key, host: ui.host, encoding: ui.encoding, apps: ui.apps},
	        	success: function(json){
	        		var r = jQuery.parseJSON(json.body);
			        jQuery('#updater_wrapper').html('');
			        jQuery('#updater_wrapper').append(r.main);
			        $('#updater_wrapper').fadeIn('slow', function() {
			            // Animation complete.
			        });
	        	}
	        });
	     }
});