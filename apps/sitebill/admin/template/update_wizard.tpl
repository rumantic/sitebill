{literal}
<script type="text/javascript">
    function update_app(apps_array, total, current_index, secret_key) {
	var percent, data = {}, first_app = {};
	data.action = 'ajax_update_app';
	data.secret_key = secret_key;
	first_app = apps_array.shift();

	percent = Math.round(100 * current_index / total);
	//$('#bar').attr('width',percent + '%');
	$('#bar').width(percent + '%');
	$('#progress').attr('data-percent', percent + '%');

	data.app_name = first_app["name"];
	jQuery("#legend").html('Обновление ' + data.app_name);

	$.ajax({
	    url: "/apps/sitebill/js/ajax.php",
	    type: "POST",
	    data: data
	}).done(function (data) {
	    var result = JSON.parse(data);
	    if (result.error) {
		jQuery("#legend").html('Ошибка при обновлении: ' + result.error);
	    } else {
		if (apps_array.length > 0) {
		    update_app(apps_array, total, ++current_index, secret_key);
		} else {
		    $('#update_progress').hide();
		    $('#update_header').hide();
		    $('#update_complete').show();
		}
	    }
	    /*        
	     if (result.finish == 1) {
	     return;
	     } else {
	     return;
	     }
	     */
	});
    }

    $(document).on('ready', function () {
	$("#start_update").click(function () {

	    //get apps for update from remote server sitebill.ru
	    if (update_info_json_string) {
		ui = JSON.parse(update_info_json_string);
		//console.log(ui);
		jQuery.ajax({
		    url: 'https://www.sitebill.ru/apps/update/js/ajax.php',
		    async: false,
		    type: 'get',
		    dataType: 'jsonp',
		    data: {lk: ui.license_key, action: 'simple_json', host: ui.host, encoding: ui.encoding, apps: ui.apps},
		    success: function (json) {
			var r = jQuery.parseJSON(json.response);
			console.log(r.apps_array);
			if (r.apps_for_update > 0) {
			    $('#update_progress').show();
			    var js_arr = jQuery.map(r.apps_array, function (el) {
				return el
			    });
			    update_app(js_arr, js_arr.length, 1, ui.secret_key);
			} else {
			    $('#update_header').hide();
			    $('#update_not_needed').show();
			}
		    }
		});
	    }
	});
    });
</script>
{/literal}
<div id="update_header">
    <div class="alert alert-warning">
	<button type="button" class="close" data-dismiss="alert">
	    <i class="icon-remove"></i>
	</button>
	<strong>Внимание!</strong>
	Перед запуском обновлений настоятельно рекомендуется сделать резервную копию базы данных и файлов сайта.
	<br>
    </div>

    <a href="#" class="btn" id="start_update">Начать обновление</a>
    <br>
    <br>
</div>
<div id="update_not_needed" style="display: none;">
    <div class="alert alert-info">
	<p>
	    У вас уже установлены все свежие обновления.										
	</p>
    </div>
</div>


<div id="update_complete" style="display: none;">
    <div class="alert alert-block alert-success">
	<p>
	    <strong>
		<i class="icon-ok"></i>
		Завершено!
	    </strong>
	    Все приложения обновлены успешно. Узнать что обновилось можно на <a href="http://www.etown.ru/s/forum/17-%D0%BE%D0%B1%D0%BD%D0%BE%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F/" target="_blank">форуме</a>.
	</p>
    </div>
</div>

<div id="update_progress" style="display: none;">
    <div class="progress" data-percent="0%" id="progress">
	<div class="bar" style="width:0%;" id="bar"></div>
    </div>
    <div id="legend"></div>
</div>