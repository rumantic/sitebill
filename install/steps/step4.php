<img src="https://www.sitebill.ru/logo_install?source=step4" width="1" height="1">
<?php
$params = array();
if (isset($_POST['db_host'])) {
    $params['db_host'] = $_POST['db_host'];
} else {
    $params['db_host'] = @$_SESSION['db']['db_host'];
}
if (@$_POST['db_port'] != '') {
    $params['db_port'] = $_POST['db_port'];
} else {
    $params['db_port'] = @$_SESSION['db']['db_port'];
}

if (isset($_POST['db_name'])) {
    $params['db_name'] = $_POST['db_name'];
} else {
    $params['db_name'] = @$_SESSION['db']['db_name'];
}


if (isset($_POST['db_user'])) {
    $params['db_user'] = $_POST['db_user'];
} else {
    $params['db_user'] = @$_SESSION['db']['db_user'];
}

if (isset($_POST['db_pass'])) {
    $params['db_pass'] = $_POST['db_pass'];
} else {
    $params['db_pass'] = @$_SESSION['db']['db_pass'];
}


echo getDBParametersForm(3, $params, $wizard);

function getDBParametersForm($step, $params = array(), $wizard) {
    if ($params['db_port'] == '') {
        $params['db_port'] = '3306';
    }
    $error_hash = $wizard->get_error_hash();
    $text = '';
    $text .= '<div class="page-header" align="center"><h1>Укажите параметры подключения к БД для CMS &laquo;Sitebill&raquo;<br/> База данных должна быть создана</h1></div>';
    //$text.='<p>Если хостинг БД вашего провайдера отличается от localhost введите его в поле "Хост".</p>';
    $text .= '<form method="post" class="form-horizontal" role="form">';
    $text .= '<input type="hidden" name="step" value="' . $step . '" />';

    if ($wizard->get_error_state()) {
        $text .= '<div class="form-group has-error">';
        $text .= '<label for="inputError" class="col-xs-12 col-sm-3 col-md-3 control-label no-padding-right"></label>';
        $text .= '<div class="help-block col-xs-12 col-sm-5 inline">' . $wizard->get_error_message() . '</div>';
        $text .= '</div>';
        $text .= '<div class="space-4"></div>';
    }


    $text .= '<div class="form-group ' . @$error_hash['db_host'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Хост </label>
			<div class="col-sm-9">
			<input type="text" name="db_host" value="' . $params['db_host'] . '" class="col-xs-10 col-sm-7" />
			</div>
			</div>';

    $text .= '<div class="form-group ' . @$error_hash['db_port'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Порт </label>
			<div class="col-sm-9">
			<input type="text" name="db_port" value="' . $params['db_port'] . '" class="col-xs-10 col-sm-7" />
			</div>
			</div>';

    $text .= '<div class="form-group ' . @$error_hash['db_name'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Название базы </label>
			<div class="col-sm-9">
			<input type="text" name="db_name" value="' . $params['db_name'] . '" class="col-xs-10 col-sm-7" />
			</div>
			</div>';

    $text .= '<div class="form-group ' . @$error_hash['db_user'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Пользователь</label>
			<div class="col-sm-9">
			<input type="text" name="db_user" value="' . $params['db_user'] . '" class="col-xs-10 col-sm-7" />
			</div>
			</div>';

    $text .= '<div class="form-group">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Пароль</label>
			<div class="col-sm-9">
			<input type="text" name="db_pass" value="' . $params['db_pass'] . '" class="col-xs-10 col-sm-7" />
			</div>
			</div>';

    $text .= '
<div class="wizard-actions">';
    $text .= '
<button class="btn btn-prev" id="prev">
												<i class="ace-icon fa fa-arrow-left"></i>
												Назад
</button>
			<input type="hidden" name="step" id="step" value="4" />
			
			';

    if (!$wizard->get_error_state()) {
        $text .= '
		
			
<button  class="btn btn-success btn-next" />
Далее <i class="ace-icon fa fa-arrow-right icon-on-right"></i>
</button>
			';
    } else {
        $text .= '<button type="submit" name="submit" class="btn btn-warning" /><i class="ace-icon fa fa-refresh"></i> Повторить проверку</button>
		
		';
    }
    $text .= '</div>';

    $text .= '</form>';
    return $text;
}
