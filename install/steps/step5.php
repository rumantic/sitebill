<img src="https://www.sitebill.ru/logo_install?source=step5" width="1" height="1">
<?php
$params = array();
if (@$_POST['admin_login'] != '') {
    $params['admin_login'] = $_POST['admin_login'];
} else {
    $params['admin_login'] = @$_SESSION['admin_login'];
}

if (@$_POST['admin_pass'] != '') {
    $params['admin_pass'] = $_POST['admin_pass'];
} else {
    $params['admin_pass'] = @$_SESSION['admin_pass'];
}

if (@$_POST['order_email_acceptor'] != '') {
    $params['order_email_acceptor'] = $_POST['order_email_acceptor'];
} else {
    $params['order_email_acceptor'] = @$_SESSION['order_email_acceptor'];
}

if (@$_POST['site_title'] != '') {
    $params['site_title'] = $_POST['site_title'];
} else {
    $params['site_title'] = @$_SESSION['site_title'];
}

if (@$_POST['distrib_folder'] != '') {
    $params['distrib_folder'] = $_POST['distrib_folder'];
} else {
    $params['distrib_folder'] = @$_SESSION['distrib_folder'];
}

if ($params['distrib_folder'] == '') {
    if ($_SERVER['REQUEST_URI'] != '/install/' and $_SERVER['REQUEST_URI'] != '/install/index.php') {
        $params['distrib_folder'] = str_replace('index.php', '', $_SERVER['REQUEST_URI']);
        $params['distrib_folder'] = str_replace('install', '', $params['distrib_folder']);
        $params['distrib_folder'] = str_replace('/', '', $params['distrib_folder']);
    }
}


echo getAdminCreateForm(4, $wizard, $params);

function getAdminCreateForm($step, $wizard, $params) {
    $error_hash = $wizard->get_error_hash();
    $text = '';
    $text .= '<form method="post" class="form-horizontal" role="form">';

    if ($wizard->get_error_state()) {
        $text .= '<div class="form-group has-error">';
        $text .= '<label for="inputError" class="col-xs-12 col-sm-3 col-md-3 control-label no-padding-right"></label>';
        $text .= '<div class="help-block col-xs-12 col-sm-5 inline">' . $wizard->get_error_message() . '</div>';
        $text .= '</div>';
        $text .= '<div class="space-4"></div>';
    }

    $text .= '<div class="form-group ' . @$error_hash['admin_login'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Логин администратора </label>
			<div class="col-sm-9">
			<input type="text" name="admin_login" value="' . $params['admin_login'] . '" class="col-xs-10 col-sm-5" />
			</div>
			</div>';

    $text .= '<div class="form-group ' . @$error_hash['admin_pass'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Пароль администратора </label>
			<div class="col-sm-9">
			<input type="text" name="admin_pass" value="' . $params['admin_pass'] . '" class="col-xs-10 col-sm-5" />
			</div>
			</div>';

    $text .= '<div class="form-group ' . @$error_hash['order_email_acceptor'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Email администратора </label>
			<div class="col-sm-9">
			<input data-rel="tooltip" type="text" name="order_email_acceptor" value="' . $params['order_email_acceptor'] . '" class="col-xs-10 col-sm-5" />
			<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="left" data-content="На этот адрес будут приходить уведомления с сайта. В дальнейшем можно изменять в настройках." title="Email администратора">?</span>					
			</div>
			</div>';

    $text .= '<div class="form-group ' . @$error_hash['site_title'] . '">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Заголовок сайта </label>
			<div class="col-sm-9">
			<input data-rel="tooltip" type="text" name="site_title" value="' . $params['site_title'] . '" class="col-xs-10 col-sm-5" />
			<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="left" data-content="Будет отображаться в качестве meta title для поисковиков. В дальнейшем можно изменять в настройках." title="Заголовок сайта">?</span>
			</div>
			</div>';

    $text .= '<div class="form-group">
			<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Каталог установки </label>
			<div class="col-sm-9">
			<input data-rel="tooltip" data-placement="bottom" type="text" name="distrib_folder" value="' . $params['distrib_folder'] . '" class="col-xs-10 col-sm-5" />
			<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="left" data-content="Название каталога установки (при установке не в корень сайта), если вы установили скрипт в корневой каталог, то оставьте это поле пустым. Если система сама поставила значение, то сверьте его со своим каталогом" title="Каталог установки">?</span>
			</div>
			</div>';



    $text .= '
<div class="wizard-actions">';
    $text .= '
<button class="btn btn-prev" id="prev">
												<i class="ace-icon fa fa-arrow-left"></i>
												Назад
</button>
				<input type="hidden" name="step" id="step" value="5" />
			
			';

    if (!$wizard->get_error_state()) {
        $text .= '
<button  class="btn btn-success btn-next" />
Далее <i class="ace-icon fa fa-arrow-right icon-on-right"></i>
</button>
				
				';
    } else {
        $text .= '
				
				<button type="submit" name="submit" class="btn btn-warning" /><i class="ace-icon fa fa-refresh"></i> Повторить проверку</button>';
    }
    $text .= '</div>';


    $text .= '</form>';
    return $text;
}
