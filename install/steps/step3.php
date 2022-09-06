<?php
$php_check = checkPHPVersion($minimal_php_version_text);
$gd_check = checkGDVersion($minimal_gd_version_text);
$libxml_check = getLibXmlStatus();
$catalog_errors = array();
$libraries_errors = array();
$catalog_errors = check_catalogs_and_permissions();
//$libraries_check=checkNeededLibraries($libraries_errors);
$answer = array();
$error = '';
$modules = get_loaded_extensions();
$answer[0]['text'] = 'Версия PHP не ниже ' . $minimal_php_version_text;
if ($php_check === FALSE) {
    $answer[0]['error'] = 1;
    $error = 1;
}
$answer[1]['text'] = 'Версия библиотеки GD не ниже ' . $minimal_gd_version_text;
if ($gd_check === FALSE) {
    $answer[1]['error'] = 1;
    $error = 1;
}

$answer[2]['text'] = 'Необходимо наличие модуля PDO';
if (!in_array('PDO', $modules) || !in_array('pdo_mysql', $modules)) {
    $answer[2]['error'] = 1;
    $error = 1;
}

$answer[3]['text'] = 'Необходимо наличие модуля mbstring';
if (!in_array('mbstring', $modules)) {
    $answer[3]['error'] = 1;
    $error = 1;
}

$answer[4]['text'] = 'Необходимо наличие модуля iconv';
if (!in_array('iconv', $modules)) {
    $answer[4]['error'] = 1;
    $error = 1;
}

$answer[5]['text'] = 'Поддержка библиотеки libxml';
if ($libxml_check === FALSE) {
    $answer[5]['error'] = 1;
    $error = 1;
}

$answer[6]['text'] = 'Необходимо наличие модуля curl';
if (!in_array('curl', $modules)) {
    $answer[6]['error'] = 1;
    $error = 1;
}
/*
  $answer[7]['text']='Необходимо наличие модуля mod_rewrite';
  if(!in_array('mod_rewrite', apache_get_modules())){
  $answer[7]['error'] = 1;
  $error=1;
  }
 */


if (@$catalog_check === FALSE) {
    $error = 1;
}

$answer = array_merge($answer, $catalog_errors);
$message_array = getResultMessage($answer);
?>
<div class="page-header" align="center">
    <h1>Результат проверки совместимости CMS &laquo;Sitebill&raquo; c вашим хостингом</h1>
</div>

<div class="row">
    <div class="col-xs-2">
    </div>

    <div class="col-xs-9 col-lg-7">
        <span>
            <?php echo $message_array['html']; ?>

        </span>
    </div>

</div>


<form method="post">
    <div class="wizard-actions">
        <button class="btn btn-prev" id="prev">
            <i class="ace-icon fa fa-arrow-left"></i>
            Назад
        </button>

        <?php
        if (!$message_array['error']) {
            ?>
            <input type="hidden" id="step" name="step" value="3" />

            <button  class="btn btn-success btn-next" />
            Далее <i class="ace-icon fa fa-arrow-right icon-on-right"></i>
            </button>

            <?php
        } else {
            ?>
            <input type="hidden" id="step_retry" name="step" value="2" />

            <button type="submit" name="submit" class="btn btn-warning" /><i class="ace-icon fa fa-refresh"></i> Повторить проверку</button>


            <?php
        }
        ?>
    </div>
</form>

<?php

function getResultMessage($message) {
    $ret = '';
    $error = false;
    if (!empty($message)) {
        $ret .= '<ul class="list-unstyled list-striped  pricing-table-header">';

        foreach ($message as $m) {
            if (@$m['error']) {
                $error = true;
                $ret .= '<li class="text-danger">';
            } else {
                $ret .= '<li class="text-success">';
            }
            $ret .= '';
            $ret .= $m['text'];
            $ret .= '<div class="pull-right" style="padding-right: 5px;">';
            if (@$m['error']) {
                $ret .= '<i class="ace-icon fa fa-times bigger-110 red"></i>';
            } else {
                $ret .= '<i class="ace-icon fa fa-check bigger-110 green"></i>';
            }
            $ret .= '</div>';
            $ret .= '</li>';
        }
        $ret .= '</ul>';
    }
    $ra['html'] = $ret;
    $ra['error'] = $error;

    return $ra;
}

function check_catalogs_and_permissions() {
    $error_folder_stack = array();
    $no_error = TRUE;
    $check_folders = array('/cache/compile', '/cache/upl', '/img/data', '/img/data/user', '/inc', '/settings.ini.php');

    $dir_name = dirname(__FILE__);
    $j = 0;
    foreach ($check_folders as $folder) {
        if (!is_writable($dir_name . '/../..' . $folder)) {
            $error_folder_stack[$j]['text'] = $folder . ' нет прав на запись! (проверьте права доступа)';
            $error_folder_stack[$j]['error'] = 1;
            $no_error = FALSE;
        } else {
            $error_folder_stack[$j]['text'] = $folder . ' доступен на запись';
        }
        $j++;
    }
    if (!is_file($dir_name . '/../../.htaccess')) {
        $error_folder_stack[$j]['text'] = 'Файл .htaccess не найден в корне сайта. Загрузите его из дистрибутива.';
        $error_folder_stack[$j]['error'] = 1;
        $no_error = FALSE;
    }
    return $error_folder_stack;
}
?>
<img src="https://www.sitebill.ru/logo_install?source=step3" width="1" height="1">
