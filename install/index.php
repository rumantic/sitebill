<?php
date_default_timezone_set('Europe/Moscow');
error_reporting(E_ERROR );
//error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();


if (!defined('__DIR__')) {
    define(__DIR__, dirname(__FILE__));
}
$minimal_php_version_text = '7.1';  //Minimal reqiured PHP version
$minimal_gd_version_text = '2';  //Minimal reqiured GD version
$db_inc_file = __DIR__ . '/../inc/db.inc.php';  //db connections
$settings_file = __DIR__ . '/../settings.ini.php';  //settings file
$wrong_license_key_message = "Введен неверный лицензионный ключ. Либо срок действия лицензии истек.";

if (isset($_POST['step'])) {
    $step = (int) $_POST['step'];
} else {
    if (file_exists($db_inc_file)) {
        echo 'CMS Sitebill уже установлена, если вы хотите переустановить скрипт, то удалите файл ./inc/db.inc.php, очистите базу данных от старых таблиц и запустите установку еще раз';
        exit;
    }
    $step = 0;
}

if (isset($_REQUEST['_lang'])) {
    $_SESSION['_lang'] = $_REQUEST['_lang'];
} else {
    if (!isset($_SESSION['_lang'])) {
        $_SESSION['_lang'] = 'ru';
    }
}


if (!defined('SITEBILL_DOCUMENT_ROOT')) {
    define('SITEBILL_DOCUMENT_ROOT', realpath(__DIR__ . '/..'));
}


$wizard = new wizard($db_inc_file);
//echo 'step = '.$step.'<br>';
if ($wizard->check_step( --$step)) {
    $step++;
}
//echo 'step = '.$step.'<br>';
if ( isset($include_path) ) {
    ini_set("include_path", $include_path);
}

if (isset($_SESSION['steps'])) {
    $steps = $_SESSION['steps'];
} else {
    $steps = array(
        '1' => array('title' => 'Лицензионное соглашение', 'result' => 0, 'id' => '1'),
        '2' => array('title' => 'Проверка системных требований', 'result' => 0, 'id' => '1'),
        '3' => array('title' => 'Установка параметров соединения с БД', 'result' => 0, 'id' => '2'),
        '4' => array('title' => 'Создание администратора БД', 'result' => 0, 'id' => '3'),
        '5' => array('title' => 'Установка основных конфигурационных параметров', 'result' => 0, 'id' => '4')
    );
}
/*
  $Installer=new Installer();
  $Installer->run();
 */

class wizard {

    private $error_state = false;
    private $error_message = '';
    private $db_inc_file = '';
    private $error_hash = array();

    function __construct($db_inc_file) {
        $this->db_inc_file = $db_inc_file;
    }

    function set_error_message($message) {
        $this->set_error_state();
        $this->error_message = $message;
    }

    function get_error_message() {
        return $this->error_message;
    }

    function set_error_state() {
        $this->error_state = true;
    }

    function get_error_state() {
        return $this->error_state;
    }

    function set_error_hash($key, $error_message) {
        $this->error_hash[$key] = $error_message;
    }

    function get_error_hash() {
        if (count($this->error_hash) > 0) {
            return $this->error_hash;
        }
        return false;
    }

    function check_step($step) {
        //echo 'check_step = '.$step.'<br>';
        switch ($step) {
            case '1':
                return $this->check_step1();
                break;

            case '3':
                return $this->check_step3();
                break;

            case '4':
                return $this->check_step4();
                break;

            default:
                //echo 'true<br>';
                return true;
                break;
        }
    }

    function check_step4() {
        $admin_login = $_POST['admin_login'];
        if ($admin_login == '') {
            $admin_login = @$_SESSION['admin_login'];
        }

        $admin_pass = $_POST['admin_pass'];
        if ($admin_pass == '') {
            $admin_pass = @$_SESSION['admin_pass'];
        }


        $order_email_acceptor = $_POST['order_email_acceptor'];
        if ($order_email_acceptor == '') {
            $order_email_acceptor = @$_SESSION['order_email_acceptor'];
        }


        $site_title = $_POST['site_title'];
        if ($site_title == '') {
            $site_title = @$_SESSION['site_title'];
        }


        $distrib_folder = $_POST['distrib_folder'];
        if ($distrib_folder == '') {
            $distrib_folder = @@$_SESSION['distrib_folder'];
        }

        $_SESSION['distrib_folder'] = $distrib_folder;

        if ($admin_login == '') {
            $this->set_error_hash('admin_login', 'has-error');
            $this->set_error_message('Не заполнены обязательные поля');
        } else {
            $_SESSION['admin_login'] = $admin_login;
        }

        if ($admin_pass == '') {
            $this->set_error_hash('admin_pass', 'has-error');
            $this->set_error_message('Не заполнены обязательные поля');
        } else {
            $_SESSION['admin_pass'] = $admin_pass;
        }

        if ($order_email_acceptor == '') {
            $this->set_error_hash('order_email_acceptor', 'has-error');
            $this->set_error_message('Не заполнены обязательные поля');
        } else {
            $_SESSION['order_email_acceptor'] = $order_email_acceptor;
        }

        if ($site_title == '') {
            $this->set_error_hash('site_title', 'has-error');
            $this->set_error_message('Не заполнены обязательные поля');
        } else {
            $_SESSION['site_title'] = $site_title;
        }

        if ($this->get_error_hash()) {
            return false;
        }
        return true;
    }

    function check_step3() {
        if (@$_POST['ready'] == 1) {
            return true;
        }
        $connection_status = FALSE;

        if ($_POST['db_host'] == '') {
            $this->set_error_hash('db_host', 'has-error');
        }
        if ($_POST['db_user'] == '') {
            $this->set_error_hash('db_user', 'has-error');
        }
        if ($_POST['db_name'] == '') {
            $this->set_error_hash('db_name', 'has-error');
        }
        if ($_POST['db_port'] == '') {
            $this->set_error_hash('db_port', 'has-error');
        }

        if (isset($_POST['db_user']) AND isset($_POST['db_pass']) AND isset($_POST['db_name']) AND isset($_POST['db_host'])) {

            $db_user = checkParameter($_POST['db_user']);
            $db_pass = checkParameter($_POST['db_pass']);
            $db_name = checkParameter($_POST['db_name']);
            $db_port = checkParameter($_POST['db_port']);
            $db_host = checkParameter($_POST['db_host']);

            $_SESSION['db'] = array(
                'db_host' => $db_host,
                'db_user' => $db_user,
                'db_name' => $db_name,
                'db_pass' => $db_pass,
                'db_host' => $db_host,
                'db_port' => $db_port
            );

            //if(!empty($db_host) AND !empty($db_name) AND !empty($db_user)){
            $connection_status = $this->checkConnection($db_host, $db_name, $db_user, $db_pass, $db_port);
            //}
        } else {
            $this->set_error_message('Ошибка в параметрах подключения к БД');
            return false;
        }

        if ($connection_status) {

            $pdo = getPDO();
            $query = "SHOW TABLES LIKE 're_config'";
            $r = $pdo->query($query);
            $row = $r->fetch();
            //echo '<pre>';
            //echo 'row =';
            //print_r($row);
            //echo '</pre>';

            if (is_array($row)) {
                $this->set_error_message('Похоже, что база данных <b>' . $_SESSION['db']['db_name'] . '</b> уже содержит таблицы. Удалите таблицы из базы данных и повторите попытку.');
                return false;
            }

            $progressbar_value = 60;
            @$steps[$step]['result'] = 1;
            return true;
        }

        return false;
    }

    function checkConnection($db_host, $db_name, $db_user, $db_pass, $db_port = '') {
        //echo 'checkConnection';
        if ($db_port != '') {
            $DSN = 'mysql:host=' . $db_host . ';port=' . $db_port . ';dbname=' . $db_name;
        } else {
            $DSN = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
        }

        try {
            $pdo = new PDO($DSN, $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            if ($pdo instanceof PDO) {
                return true;
            }
        } catch (PDOException $e) {
            //echo 'error '.$e->getMessage();
            $this->set_error_message('Ошибка в параметрах подключения к БД: ' . $e->getMessage());
        }
        return false;
    }

    function check_step1() {
        if (isset($_POST['license_key'])) {
            $license_key = $_POST['license_key'];
            $_SESSION['license_key'] = $license_key;
        } else {
            $license_key = $_SESSION['license_key'];
        }

        $error_message = check_license($license_key);
        if ($error_message == 'wrong_license_key_message') {
            $this->set_error_message("Введен неверный лицензионный ключ. Либо срок действия лицензии истек.");
            return false;
        }
        return true;
    }

}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>CMS &laquo;Sitebill&raquo; - Мастер установки</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!-- basic styles -->

        <link href="../apps/admin/admin/template1/assets/css/bootstrap.min.install.css" rel="stylesheet" />
        <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/font-awesome.min.css" />

        <!--[if IE 7]>
          <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/font-awesome-ie7.min.css" />
        <![endif]-->

        <!-- page specific plugin styles -->

        <!-- fonts -->

        <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/ace-fonts.css" />
        <!-- ace styles -->

        <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/ace.min.full.css" />
        <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/ace-responsive.min.css" />
        <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/ace-skins.min.css" />
        <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/styles.css" />
        <!--[if lte IE 8]>
          <link rel="stylesheet" href="../apps/admin/admin/template1/assets/css/ace-ie.min.css" />
        <![endif]-->

        <!-- inline styles related to this page -->

        <!-- ace settings handler -->

        <link rel="stylesheet" href="../apps/admin/admin/template/css/admin.css">

        <script type="text/javascript" src="../apps/system/js/jquery/jquery.js"></script>
        <script src="../apps/system/js/bootstrap/js/bootstrap.min.js"></script>

        <script src="../apps/system/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
        <link rel="stylesheet" href="../apps/system/js/bootstrap-editable/css/bootstrap-editable.css" />
        <link href="https://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
        <script src="https://www.sitebill.ru/js/nanoapi.js"></script>
        <script src="https://www.sitebill.ru/js/nanoapi_beta.js"></script>

        <script src="../apps/admin/admin/template1/assets/js/ace-extra.min.js"></script>


        <script src="../apps/admin/admin/template1/assets/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/jquery.ui.touch-punch.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/jquery.slimscroll.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/jquery.easy-pie-chart.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/jquery.sparkline.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/flot/jquery.flot.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/flot/jquery.flot.pie.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/flot/jquery.flot.resize.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/bootstrap-tag.min.js"></script>

        <!-- ace scripts -->

        <script src="../apps/admin/admin/template1/assets/js/ace-elements.min.js"></script>
        <script src="../apps/admin/admin/template1/assets/js/ace.min.js"></script>

        <link rel="stylesheet" href="../apps/admin/admin/template1/css/custom.css" />
        <style>
            .modal.fade{top: -200%;}
            .sidebar, .sidebar:before {
                width: 300px;
            }
            .main-content {
                margin-left: 300px;
            }

        </style>



        <script language="text/javascript">
            function SIF() {
                document.InternalForm.submit();
            }

            function SIF_Retry() {
                document.RetryForm.submit();
            }
        </script>

        <script type="text/javascript">
            jQuery(function ($) {
                $('[data-rel=tooltip]').tooltip({container: 'body'});
                $('[data-rel=popover]').popover({container: 'body'});

                $("#prev").click(function () {
                    var cur_step = $("#step").val();
                    cur_step -= 2;
                    $("#step").val(cur_step);

                    var step_retry = $("#step_retry").val();
                    if (step_retry) {
                        step_retry--;
                        $("#step_retry").val(step_retry);
                    }

                });

            });
        </script>

    </head>

    <body onload="runDialog('homescript_etown_ru');" class="no-skin">
        <div class="navbar navbar-default" id="navbar">
            <script type="text/javascript">
                {
                    literal}try {
                    ace.settings.check('navbar', 'fixed')
                } catch (e) {
                }{
                    /literal}
            </script>

            <div class="navbar-container" id="navbar-container">
                <div class="navbar-header pull-left">
                    <a href="#" class="navbar-brand">
                        <div class="dragon"></div>
                        <small>
                            &nbsp;Мастер установки CMS &laquo;Sitebill&raquo;
                        </small>
                    </a>
                </div>

                <div class="navbar-buttons navbar-header pull-right" role="navigation">
                    <ul class="nav ace-nav">

                        <li class="light-blue">
                            <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                                <i class="icon-question-sign icon-on-right"></i> Нужна помощь?
                                <i class="ace-icon fa fa-caret-down"></i>
                            </a>

                            <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                                <li>
                                    <a href="http://wiki.sitebill.ru/" target="_blank"><i class="icon-white icon-book"></i> База знаний</a>
                                </li>

                                <li>
                                    <a href="http://www.etown.ru/s/" target="_blank"><i class="icon-white icon-comment"></i> Форум</a>
                                </li>

                                <li>
                                    <a href="http://www.youtube.com/user/DMn1c" target="_blank"><i class="icon-white icon-film"></i> Видео-уроки</a>
                                </li>

                                <li>
                                    <a href="http://www.sitebill.ru/" target="_blank"><i class="icon-white icon-heart"></i> Наш сайт</a>
                                </li>

                                <li>
                                    <a href="https://play.google.com/store/apps/details?id=ru.sitebill.mobilecms" target="_blank"><i class="icon-white icon-camera"></i> Google play</a>
                                </li>

                            </ul>
                        </li>
                    </ul>
                </div>


            </div>
        </div>
        <?php
        $sidebar_menu[0] = array('title' => 'Лицензионное соглашение', 'icon' => ' ace-icon fa fa-home ');
        $sidebar_menu[1] = array('title' => 'Ввод лицензии', 'icon' => ' ace-icon fa fa-key ');
        $sidebar_menu[2] = array('title' => 'Проверка системных требований', 'icon' => ' fa fa-info-circle ');
        $sidebar_menu[3] = array('title' => 'Настройка подключения к БД', 'icon' => ' ace-icon fa fa-cogs ');
        $sidebar_menu[4] = array('title' => 'Основные настройки', 'icon' => ' ace-icon fa fa-user ');
        $sidebar_menu[5] = array('title' => 'Завершение установки', 'icon' => ' ace-icon fa fa-gift ');
        ?>

        <div class="main-container container">
            <div class="page-content">

                <div class="widget-box">
                    <div class="widget-header widget-header-blue widget-header-flat">
                        <h4 class="widget-title lighter"><?php echo @$sidebar_menu[$step]['title']; ?></h4>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main">
                            <div class="row">
                                <div class="col-xs-12">



                                    <?php
                                    include ('steps/progress.php');
                                    ?>


                                    <?php
                                    //echo $step;

                                    if ($step == 0) {
                                        include ('steps/step1.php');
                                    } elseif ($step == 1) {
                                        include ('steps/step2.php');
                                    } elseif ($step == 2) {
                                        include ('steps/step3.php');
                                    } elseif ($step == 3) {
                                        include ('steps/step4.php');
                                    } elseif ($step == 4) {
                                        include ('steps/step5.php');
                                    } elseif ($step == 5 or $step == 6) {
                                        include ('steps/step6.php');
                                    } else {
                                        $text .= 'Установка завершена. Не забудьте удалить папку install из корня сайта.';
                                    }

                                    $_SESSION['steps'] = $steps;
                                    ?>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div><!-- main-container -->
    </body>
</html>

<?php

function getSettingsForm($step, $error_message = '') {
    $text = '';
    $text .= '<h1>Другие настройки</h1>';
    $text .= '<form method="post">';
    $text .= '<table>';
    $text .= '<input type="hidden" name="step" value="' . $step . '" />';
    if ($_SERVER['REQUEST_URI'] != '/install/' and $_SERVER['REQUEST_URI'] != '/install/index.php') {
        $distr_folder = str_replace('index.php', '', $_SERVER['REQUEST_URI']);
        $distr_folder = str_replace('install', '', $distr_folder);
        $distr_folder = str_replace('/', '', $distr_folder);
    }
    if ($_REQUEST['site_title'] == '') {
        $_REQUEST['site_title'] = 'Агентство недвижимости';
    }
    if ($error_message != '') {
        $text .= '<tr><td colspan="2"><span class="error">' . $error_message . '</span></td></tr>';
    }


    $text .= '<tr><td>Заголовок сайта <span class="error">*</span></td><td><input type="text" size="40" name="site_title" value="' . $_REQUEST['site_title'] . '" /></td></tr>';
    $text .= '<tr><td>Email администратора (на этот адрес будут приходить заявки с сайта) <span class="error">*</span></td><td><input type="text"  size="40" name="order_email_acceptor" value="' . $_REQUEST['order_email_acceptor'] . '" /></td></tr>';

    $text .= '<tr><td>Имя папки установки (при установке не в корень сайта), если вы установили скрипт в корневой каталог, то оставьте это поле пустым. Если система сама поставила значение, то сверьте его со своим каталогом</td><td><input type="text"  size="40" name="distrib_folder" value="' . $distr_folder . '" /></td></tr>';
    $text .= '</table>';
    $text .= '<div class="controls"><input type="button" name="" value="Назад" onclick="javascript:SIF();"><input type="submit" name="submit" value="Далее" /></div>';

    $text .= '</form>';
    return $text;
}

function checkParameter($p) {
    return $p;
    //return preg_replace('/[^-A-Za-z_0-9\.]/', '', $p);
}

function installTables() {
    $new_queries = array();
    $queries = file('estate.sql');
    $query = '';
    $pdo = getPDO();
    foreach ($queries as $q) {
        if ($q != '' && 0 !== strpos($q, '#')) {
            $query = $query . ' ' . $q;
        }
    }
    $new_queries = explode(';', $query);
    //$pdo->exec('SET NAMES utf8');
    //mysql_query('SET NAMES utf8');
    if (!empty($new_queries)) {
        foreach ($new_queries as $nq) {
            if (trim($nq) != '') {
                $r = $pdo->exec($nq);
            }
        }
    }
}

function fillIncFile($inc_file) {
    $text = '';
    $text .= '<?php' . "\r\n";
    $text .= 'if(!defined(\'DB_HOST\')){' . "\r\n";
    $text .= '	define(\'DB_HOST\',\'' . $_SESSION['db']['db_host'] . '\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_PORT\')){' . "\r\n";
    $text .= '	define(\'DB_PORT\',\'' . $_SESSION['db']['db_port'] . '\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_USER\')){' . "\r\n";
    $text .= '	define(\'DB_USER\',\'' . $_SESSION['db']['db_user'] . '\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_BASE\')){' . "\r\n";
    $text .= '	define(\'DB_BASE\',\'' . $_SESSION['db']['db_name'] . '\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_PASS\')){' . "\r\n";
    $text .= '	define(\'DB_PASS\',\'' . $_SESSION['db']['db_pass'] . '\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_PREFIX\')){' . "\r\n";
    $text .= '	define(\'DB_PREFIX\',\'re\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_ENCODING\')){' . "\r\n";
    $text .= '	define(\'DB_ENCODING\',\'utf8\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'SITE_ENCODING\')){' . "\r\n";
    $text .= '	define(\'SITE_ENCODING\',\'UTF-8\');' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DEBUG_ENABLED\')){' . "\r\n";
    $text .= '	define(\'DEBUG_ENABLED\',false);' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'LOG_ENABLED\')){' . "\r\n";
    $text .= '	define(\'LOG_ENABLED\',false);' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= 'if(!defined(\'DB_DSN\')){' . "\r\n";
    $text .= '	if(DB_PORT!=\'\'){' . "\r\n";
    $text .= '		define(\'DB_DSN\',\'mysql:host=\'.DB_HOST.\';port=\'.DB_PORT.\';dbname=\'.DB_BASE);' . "\r\n";
    $text .= '	}else{' . "\r\n";
    $text .= '		define(\'DB_DSN\',\'mysql:host=\'.DB_HOST.\';dbname=\'.DB_BASE);' . "\r\n";
    $text .= '	}' . "\r\n";
    $text .= '}' . "\r\n";
    $text .= '$__server = \'' . $_SESSION['db']['db_host'] . '\';' . "\r\n";
    $text .= '$__user = \'' . $_SESSION['db']['db_user'] . '\';' . "\r\n";
    $text .= '$__password = \'' . $_SESSION['db']['db_pass'] . '\';' . "\r\n";
    $text .= '$__db = \'' . $_SESSION['db']['db_name'] . '\';' . "\r\n";
    $text .= '$__db_port = \'' . $_SESSION['db']['db_port'] . '\';' . "\r\n";
    $text .= '$__db_prefix = \'re\'; // не менять' . "\r\n";
    $text .= '$__document_root = $_SERVER[\'DOCUMENT_ROOT\'];' . "\r\n";


    /*
      $text.='if (!isset($__connection))'."\r\n";
      $text.='{'."\r\n";
      $text.='	$__connection = mysql_connect($__server.($__db_port!=\'\' ? \':\'.$__db_port : \'\'), $__user, $__password) '."\r\n";
      $text.='		or die(\'Не удалось поключиться к серверу БД (\' . mysql_error() . \')\');'."\r\n";
      $text.='	mysql_select_db($__db)'."\r\n";
      $text.='		or die (\'Не удалось подключиться к БД(\' . mysql_error() . \')\');'."\r\n";

      $text.='mysql_query("SET NAMES utf8");'."\r\n";

      $text.='}'."\r\n";
     */
    $text .= '?>';
    $f = fopen($inc_file, 'w');
    fwrite($f, $text);
    fclose($f);
}

function fillSettingsFile($file, $settings = array()) {
    $text = '';
    $text .= '[Settings]' . "\r\n";
    if (!empty($settings)) {
        foreach ($settings as $k => $s) {
            $text .= $k . '=' . $s . "\r\n";
        }
    }
    $f = fopen($file, 'w');
    fwrite($f, $text);
    fclose($f);
}

function checkNeededVersions($params = array()) {
    if (empty($params)) {
        return TRUE;
    } else {
        if (isset($params['php_need'])) {

        }
    }
}

function checkPHPVersion($minimal_php_version_text) {
    $a = explode('.', $minimal_php_version_text);
    $b = explode('.', PHP_VERSION);
    $compare_result = compareVersions($a, $b);
    if ($compare_result < 2) {
        return TRUE;
    }
    return FALSE;
}

function checkGDVersion($minimal_gd_version_text) {
    $current_gd_version = getGDVersion();
    if ($current_gd_version < 2) {
        return false;
    }
    return true;
}

function checkNeededLibraries(&$errors) {
    $error_folder_stack = array();
    $no_error = TRUE;
    $check_folders = array('/third/simple_html_dom/simple_html_dom.php');

    $dir_name = dirname(__FILE__);

    foreach ($check_folders as $folder) {
        if (!file_exists($dir_name . '/..' . $folder)) {
            $error_folder_stack[] = $folder . ' <span class="error">Отсутствует!</span>';
            $no_error = FALSE;
        } else {
            $error_folder_stack[] = $folder . ' <span class="ok">Присутствует</span>';
        }
    }
    $errors = $error_folder_stack;
    return $no_error;
}

//return 1 - first argument younger
//return 2 - second argument younger
//return 0 - equals

function compareVersions($a, $b, $l = 0) {
    if (!isset($a[$l]) AND ! isset($b[$l])) {
        return 0;
    } else {
        if ((int) $a[$l] == (int) $b[$l]) {
            return compareVersions($a, $b, $l + 1);
        } elseif ((int) $a[$l] < (int) $b[$l]) {
            return 1;
        } else {
            return 2;
        }
    }
}

function gdVersion($user_ver = 0) {
    if (!extension_loaded('gd')) {
        return;
    }
    static $gd_ver = 0;
    // Just accept the specified setting if it's 1.
    if ($user_ver == 1) {
        $gd_ver = 1;
        return 1;
    }
    // Use the static variable if function was called previously.
    if ($user_ver != 2 && $gd_ver > 0) {
        return $gd_ver;
    }
    // Use the gd_info() function if possible.
    if (function_exists('gd_info')) {
        $ver_info = gd_info();
        preg_match('/\d/', $ver_info['GD Version'], $match);
        $gd_ver = $match[0];
        return $match[0];
    }
    // If phpinfo() is disabled use a specified / fail-safe choice...
    if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
        if ($user_ver == 2) {
            $gd_ver = 2;
            return 2;
        } else {
            $gd_ver = 1;
            return 1;
        }
    }
    // ...otherwise use phpinfo().
    ob_start();
    phpinfo(8);
    $info = ob_get_contents();
    ob_end_clean();
    $info = stristr($info, 'gd version');
    preg_match('/\d/', $info, $match);
    $gd_ver = $match[0];
    return $match[0];
}

// End gdVersion()

function getGDVersion() {
    return gdVersion();
}

function callCheckConfig() {

}

function getLibXmlStatus() {
    ob_start();
    phpinfo(8);
    $phpinfo = ob_get_contents();
    ob_end_clean();
    $phpinfo = stristr($phpinfo, "libXML support ");
    $phpinfo = stristr($phpinfo, "support");
    $end = strpos($phpinfo, "</tr>");
    if ($end) {
        $phpinfo = substr($phpinfo, 0, $end);
    }
    $phpinfo = strip_tags($phpinfo);
    if (preg_match("/.*(active).*/", $phpinfo, $r)) {
        $support = TRUE;
    } else {
        $support = FALSE;
    }
    return $support;
}

function getPDO() {
    if ($_SESSION['db']['db_port'] != '') {
        $DSN = 'mysql:host=' . $_SESSION['db']['db_host'] . ';port=' . $_SESSION['db']['db_port'] . ';dbname=' . $_SESSION['db']['db_name'];
    } else {
        $DSN = 'mysql:host=' . $_SESSION['db']['db_host'] . ';dbname=' . $_SESSION['db']['db_name'];
    }
    $pdo = new PDO($DSN, $_SESSION['db']['db_user'], $_SESSION['db']['db_pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

    return $pdo;
}

function decode($key) {
    $array = explode("-", $key);
    $first = @hexdec($array[0]);
    $second = @hexdec($array[1]);
    $index = ($first + $second) / 10000;
    $sum = 0;
    if (!in_array($index, array(1, 2, 3, 4))) {
        return 0;
    }
    for ($i = 2; $i < 5; $i++) {
        $sum += @hexdec($array[$i]);
    }
    if ($sum != @hexdec($array[5])) {
        return 0;
    }
    return hexdec($array[$index]);
}

function check_license($license_key) {
    if (preg_match('/9bef-51-fde4cc-8ecaf4-7835ee76-79c29e36/i', $license_key) or preg_match('/36b6-658a-1ba3b928-16565214-78707552-aa6a808e/i', $license_key)) {
        return "wrong_license_key_message";
    }
    $ins = decode($license_key);
    $d = time() - $ins;
    if ($d > 86400 * 30) {
        return "wrong_license_key_message";
    }
    return "";
}
?>
