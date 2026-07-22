$(document).on('ready', function () {
    $("#save").click(function () {
        var data = {}, estate_folder = '';
        data.action = 'save';
        data.theme = $('#config_form').find('select[name="theme"]').val();
        data.homepagetype = $('#config_form').find('select[name="homepagetype"]').val();
        $('input[type=text]').each(function(){
            data[this.name] = $(this).val();
        })
        send_data(data);
    });

    $("#switch_off_dashboard").click(function () {
        var data = {}, estate_folder = '';
        data["action"] = 'save';
        data["apps.dashboard.enable"] = 0;
        send_data(data);
    });

    //apps.dashboard.enable

    function send_data (data) {
        $.ajax({
            url: estate_folder + "/apps/dashboard/js/ajax.php",
            type: "POST",
            data: data
        }).done(function (data) {
            var response = JSON.parse(data);

            if (response.result == "success") {
                parent.location.reload();
            }
        });
    }
});
