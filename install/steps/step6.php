<img src="https://www.sitebill.ru/logo_install?source=step6" width="1" height="1">
<?php
if (@$_POST['ready']) {
    echo 'Установка конфигурационного файла...';
    fillIncFile($db_inc_file);
    echo 'OK<br/>';

    echo 'Создание таблиц в базе данных...';
    installTables();
    echo 'OK<br/>';

    $pdo = getPDO();
    $query = "INSERT INTO re_user (login, password, pass, active, group_id, fio, reg_date, email, notify) VALUES ('" . $_SESSION['admin_login'] . "','" . md5($_SESSION['admin_pass']) . "','admin',1,1, 'Administrator', '" . date('Y-m-d H:i:s') . ".', '" . $_SESSION['order_email_acceptor'] . "', 1)";
    $r = $pdo->exec($query);
    if ($r) {
        $progressbar_value = 80;
        $steps[$step]['result'] = 1;
        $_SESSION['created_user'] = $admin_login;

        //return true;
        //$text.='<p>Администратор создан</p>';
        //$text.='<p>Логин: '.$admin_login.'</p>';
        //$text.='<p>Пароль: '.$admin_pass.'</p>';
        //$text.=getSettingsForm(5);
        //$text.=getBackButton(4);
    } else {
        //$this->set_error_message('Ошибка при создании пользователя - возможно пользователь уже создан, или вы пытаетесь установить скрипт на уже установленной базе. Вам необходимо запустить установку еще раз, но перед этим удалите все таблицы в базе данны, удалите файл /inc/db.inc.php');
        //return false;
    }

    $progressbar_value = 100;
    $steps[$step]['result'] = 1;
    $folder = trim(str_replace(array('/', '\\'), '', $_SESSION['distrib_folder']));
    fillSettingsFile($settings_file, array('estate_folder' => $folder));
    /////////////////////////////////////
    $settings = parse_ini_file(__DIR__ . '/../../settings.ini.php', true);
    if (isset($settings['Settings']['estate_folder'])AND ( $settings['Settings']['estate_folder'] != '')) {
        $folder = '/' . $settings['Settings']['estate_folder'];
    } else {
        $folder = '';
    }

    $sitebill_document_uri = '';
    $sitebill_document_root = $_SERVER['DOCUMENT_ROOT'] . $folder;
    define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
    //define('SITEBILL_MAIN_URL', $folder);
    require_once(__DIR__ . '/../../third/smarty/Smarty.class.php');
    require_once(__DIR__ . "/../../inc/db.inc.php");
    define('DB_PREFIX', $__db_prefix);



    //checkConnection($_SESSION['db']['db_host'],$_SESSION['db']['db_name'],$_SESSION['db']['db_user'],$_SESSION['db']['db_pass']);
    //$query="INSERT INTO ".DB_PREFIX."_config (config_key, value, title) VALUES ('license_key','".$_SESSION['license_key']."','Лицензионный ключ')";
    //$answer=mysql_query($query);
    /* $answer=$pdo->exec($query);
      if ( !$answer ) {
      echo 'Ошибка подключения к базе, повторите процесс установки снова';
      exit;
      } */
    $pdo = getPDO();


    $query = "INSERT INTO " . DB_PREFIX . "_config (config_key, value, title) VALUES ('license_key','" . $_SESSION['license_key'] . "','Лицензионный ключ')";
    $answer = $pdo->exec($query);
    if (!$answer) {
        echo 'Ошибка подключения к базе, повторите процесс установки снова';
        exit;
    }




    require_once(__DIR__ . '/../../apps/system/lib/db/MySQL.php');
    require_once(__DIR__ . '/../../apps/system/lib/sitebill.php');
    require_once(__DIR__ . '/../../apps/system/lib/admin/object_manager.php');
    require_once(__DIR__ . '/../../apps/config/admin/admin.php');
    require_once(__DIR__ . '/../../apps/system/lib/system/install/install.php');
    require_once(__DIR__ . '/../../apps/system/lib/system/multilanguage/multilanguage.class.php');

    Multilanguage::start('frontend', $_SESSION['_lang']);


    class install_config extends config_admin {

        function __construct() {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/db/mysql_db_emulator.php';
            $this->db = new Mysql_DB_Emulator();

            $this->table_name = 'config';
            $this->action = 'config';
            //$this->app_title = Multilanguage::_('L_SETTINGS');
            $this->primary_key = 'id';
            //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/config_model.php');
            //$this->data_model_object=new Config_Model();
            //$this->data_model=$this->data_model_object->get_model();
            //$this->install();
            //$this->install_hidden_config();
            $this->check_config_structure();
        }

    }

    $smarty = new Smarty;

    echo 'Инициализация конфигурационных параметров...';
    $install_config = new install_config();
    echo 'OK<br/>';
    //$install_config->check_config_structure();
    //exit;

    $sitebill = new SiteBill();



    $smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/template/frontend/agency';
    $smarty->cache_dir = SITEBILL_DOCUMENT_ROOT . '/cache/smarty';
    $smarty->compile_dir = SITEBILL_DOCUMENT_ROOT . '/cache/compile';

    $DBC = DBC::getInstance();


    /*
      $query="INSERT INTO ".DB_PREFIX."_config (config_key, value, title) VALUES ('license_key','".$_SESSION['license_key']."','Лицензионный ключ')";
      //$answer=mysql_query($query);
      $stmt=$DBC->query($query);
      var_dump($stmt);
      if ( !$stmt ) {
      echo 'Ошибка подключения к базе, повторите процесс установки снова';
      exit;
      }
     */

    //$config_admin->db->exec('update '.DB_PREFIX."_config set value='0' where config_key='show_cattree_left'");

    $query = 'update ' . DB_PREFIX . "_config set value='0' where config_key='show_cattree_left'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }

    $install_manager = new Install_Manager();
    //insert license key
    echo 'Установка начальных данных<br>';
    $install_manager->install_default_data($folder);



    $query = "update " . DB_PREFIX . "_config set value = '" . $_SESSION['order_email_acceptor'] . "' where config_key='order_email_acceptor'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */
    /*
      $query="update ".DB_PREFIX."_user set email = '".$_REQUEST['order_email_acceptor']."' where login='admin'";
      $stmt=$DBC->query($query);
      if(!$stmt){
      echo 'ERROR ON INSTALL ('.$query.')';
      }
     */
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */


    $query = "update " . DB_PREFIX . "_config set value = '" . $_SESSION['site_title'] . "' where config_key='site_title'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */

    //install apps.seo
    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/seo/admin/admin.php');
    $seo_admin = new seo_admin();
    $query = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.seo.level_enable'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */
    $query = "update " . DB_PREFIX . "_config set value = '0' where config_key='apps.seo.html_prefix_enable'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */
    $seo_admin->update_structure();

    //install apps.geodata
    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php');
    $geodata_admin = new geodata_admin();
    $query = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.geodata.enable'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */

    //installl apps.mailbox
    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/mailbox/admin/admin.php');
    $mailbox_admin = new mailbox_admin();
    $query = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.mailbox.enable'";
    $stmt = $DBC->query($query);
    if (!$stmt) {
        //echo 'ERROR ON INSTALL ('.$query.')';
    }
    /* $install_manager->db->exec($query);
      if ( $install_manager->db->error ) {
      echo $install_manager->db->error;
      } */


    /*
     * Ups
     *
     */

    echo 'Инициализация приложений...';
    require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
    require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
    $apps_processor = new Apps_Processor();
    $apps_array = $apps_processor->load_apps_array();
    unset($apps_array['system']);
    require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/update.php');
    $system_update = new system_update();
    $system_update->setRequestValue('secret_key', 'install&license_key=' . $_SESSION['license_key'] . '&server_name=' . $_SERVER['SERVER_NAME']);
    $system_update->main();

    foreach ($apps_array as $app_name => $apps_info) {
        if ($apps_info['update']) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $app_name . '/update.php');
            $update_class_name = $app_name . '_update';
            $update_app_class = new $update_class_name;
            $update_app_class->main();
        }
    }
    $query_a = array();

    $query_a[] = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.geodata.enable'";
    $query_a[] = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.getrent.enable'";
    $query_a[] = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.seo.level_enable'";
    $query_a[] = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.mailbox.enable'";
    $query_a[] = "update " . DB_PREFIX . "_config set value = '1' where config_key='apps.dashboard.enable'";
    $query_a[] = "update " . DB_PREFIX . "_config set value = 'realia' where config_key='theme'";
    foreach ($query_a as $query_b) {
        $stmt = $DBC->query($query_b, array(), $success);
        if (!$stmt) {
            echo $DBC->getLastError() . '<br>';
        }
    }
    //запустим форму conctactus, чтобы инициировать модель
    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/contactus.php');
    $contactus_form = new contactus_Form();
    $contactus_form->main();





    echo 'OK<br/>';

    echo '<br/>';
    ///////////////////////////////////////
    $text .= '<div class="page-header" align="center"><h1>Поздравляем! Установка завершена!</h1></div>';
    $text .= '<p><a href="' . $folder . '/" class="btn btn-primary">Перейти на сайт</a></p>';
    $text .= '<p><a href="' . $folder . '/admin/" class="btn btn-success">Перейти в панель управления</a></p>';
    $text .= '<p>Чтобы быстрее освоиться с функциями движка зайдите на <a href="http://www.youtube.com/user/DMn1c" target="_blank">наш канал в youtube</a></p>';

    echo $text;
    $_SESSION = array();
    unset($_SESSION);
} else {
    ?>

    <div class="page-header" align="center"><h1>Все готово для установки<br/> Нажмите кнопку Установить</h1></div>
    <form method="post" class="form-horizontal" role="form">

        <div class="space-4"></div>


        <div class="wizard-actions">

            <input type="hidden" name="step" id="step" value="6" />
            <input type="hidden" name="ready" value="1" />

            <button class="btn btn-prev" id="prev">
                <i class="ace-icon fa fa-arrow-left"></i>
                Назад
            </button>

            <input type="submit" name="submit" value="Установить" class="btn btn-success" />
        </div>




    </form>


    <?php
}
?>
