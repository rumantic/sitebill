$(document).on('ready', function () {
    $( "#save" ).click(function() {
        var data = {}, estate_folder='';
	data.action = 'save';
        data.theme = $('#config_form').find('select[name="theme"]').val();
        data.homepagetype = $('#config_form').find('select[name="homepagetype"]').val();

        $.ajax({
            url : estate_folder + "/apps/dashboard/js/ajax.php",
            type : "POST",
            data: data
	}).done(function(data) {
            var response = JSON.parse(data);
                
            if ( response.result == "success" ) {
                console.log(response);
                parent.location.reload();
            }
        });
    });
});
