<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Messenger backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class messenger_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
	$this->SiteBill();
	require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
	$config_admin = new config_admin();

	if (!$config_admin->check_config_item('apps.messenger.backend_enable')) {
	    $config_admin->addParamToConfig('apps.messenger.backend_enable', '1', 'Включить приложение Messenger в админке', 1);
	}

	if (!$config_admin->check_config_item('apps.messenger.frontend_enable')) {
	    $config_admin->addParamToConfig('apps.messenger.frontend_enable', '0', 'Включить приложение Messenger в личном кабинете', 1);
	}

	if (!$config_admin->check_config_item('apps.messenger.widget_enable')) {
	    $config_admin->addParamToConfig('apps.messenger.widget_enable', '0', 'Включить виджет Messenger на сайте (добавьте код <strong>{$messenger_widget}</strong> в коде шаблона перед закрывающим &lt;/body&gt;}', 1);
	}
    }
    
    public function backend_preload () {
	//$this->template->assert('params', 'admin_backend');
	//$this->template->assert('messenger_widget', $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/site/template/start_messenger_js.tpl'));
    }

    public function _preload() {
        return false;
    	if ( $this->getConfigValue('apps.messenger.widget_enable') ) {
	    if ( $this->getSessionUserId() > 0 ) {

		$this->template->assert('messenger_widget', $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/site/template/start_messenger_js.tpl'));
	    }
	}
    }

    /**
     * Используется openfire сервер https://www.igniterealtime.org/projects/openfire/
     * Для коннекта из JS используется strophe-клиент http://strophe.im/strophejs/
     * Примеры реализации чатрума взяты отсюда https://github.com/metajack/profxmpp
     * 
     * @return type
     */
    function main() {
	if ( $this->getSessionUserId() > 0 ) {
	    $this->template->assert('params', 'admin_backend');
	    $this->template->assert('messenger_widget_inner', $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/site/template/start_messenger_js.tpl'));
	}
	
	$user_id = $this->getSessionUserId();
	$user_info = $this->get_user_jabber_info($user_id);
	if (in_array($this->getRequestValue('do'), array('edit', 'edit_done', 'new', 'new_done'))) {
	    $rs = $this->config_action($user_id);
	    return $rs;
	} else {
	    $this->template->assign('user_info', $user_info);
	    return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/messenger/admin/template/chatroom.tpl');
	}
    }
    
    function ajax () {
	$user_id = $this->getSessionUserId();
	$user_info = $this->get_user_jabber_info($user_id);
	
        if ( $this->getRequestValue('action') == 'iframe' ) {
	    $this->template->assign('user_info', $user_info);
	    $this->template->assign('messenger_frontend', 'true');
	    $this->template->assign('messenger_widget', 'true');
	    $this->template->assign('assets_folder', SITEBILL_MAIN_URL.'/apps/admin/admin/template1');
	    
	    $this->template->assign('messenger_body', $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/messenger/admin/template/chatroom.tpl'));
            echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/site/template/main_messenger.tpl');
	} elseif ( $this->getRequestValue('action') == 'prebind' ) {
	    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/messenger/lib/XmppPrebind.php');
	    //echo '<pre>';
	    //print_r($user_info);
	    //echo '</pre>';
	    //exit;
	    //unset($_SESSION['xmpp_jid']);
	    //if ( !isset($_SESSION['xmpp_jid']) ) {
		$xmppPrebind = new XmppPrebind('sitebill.ru', 'https://sitebill.ru:7443/http-bind/', 'sitebillcms-'.time().rand(0, 9999), true, false);
		$xmppPrebind->connect($user_info['jabber_id'], $user_info['jabber_password']);
		//$xmppPrebind->connect('guest', 'guest');
		$xmppPrebind->auth();
		$sessionInfo = $xmppPrebind->getSessionInfo(); // array containing sid, rid and jid

		$_SESSION['xmpp_jid'] = $sessionInfo['jid'];
		$_SESSION['xmpp_sid'] = $sessionInfo['sid'];
		$_SESSION['xmpp_rid'] = $sessionInfo['rid'];
	    //} else {
		//$sessionInfo['jid'] = $_SESSION['xmpp_jid'];
		//$sessionInfo['sid'] = $_SESSION['xmpp_sid'];
		//$sessionInfo['rid'] = $_SESSION['xmpp_rid'];
	    //}
	    echo json_encode($sessionInfo);
	    exit;
	    
	    
	} else {
	    header("Content-Type: application/x-javascript");
	    echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/site/template/messenger_iframe_code.tpl');
	}
	
    }

    function config_action($user_id) {
	require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
	$data_model = new Data_Model();

	require_once(SITEBILL_DOCUMENT_ROOT . '/apps/messenger/admin/jabber_user_object.php');
	$jabber_user = new Jabber_User_Object();

	//Получим текущую информацию о пользователе
	$user_info = $this->get_user_jabber_info($user_id);
	$jabber_user->data_model[$jabber_user->table_name] = $data_model->init_model_data_from_db($jabber_user->table_name, $jabber_user->primary_key, $user_id, $jabber_user->data_model[$jabber_user->table_name]);



	//Если пользователь нажал обновить данные
	//1. Он меняет только пароль
	//2. Он меняет и логин и пароль
	//2.1 Тогда спрашиваем у сервера свободны ли эти данные на сервере. Если свободно, тогда регистрируем нового пользователя
	//2.2 Если не свободен логин, то проверяем его пароль, если он соответствует, тогда пишем что ок. Если не соответствует, тогда пишем что логин занят
	//Такой логин существует и пароль от него указан не верно
	if ($this->getRequestValue('do') == 'new' or $this->getRequestValue('do') == 'new_done') {
	    $jabber_user->data_model[$jabber_user->table_name]['jabber_password']['title'] = 'Пароль <a href="#" id="show_password">показать</a>';
	    unset($jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']);
	    unset($jabber_user->data_model[$jabber_user->table_name]['jabber_new_password_a']);
	    $rs = $this->get_config_top_menu();

	    if ($this->getRequestValue('do') == 'new_done') {
		$jabber_user->data_model[$jabber_user->table_name] = $data_model->init_model_data_from_request($jabber_user->data_model[$jabber_user->table_name]);
		$auth_response = $this->remote_auth($jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'], $jabber_user->data_model[$jabber_user->table_name]['jabber_password']['value']);
		if ($auth_response == 'success_auth') {
		    $this->update_jabber_info($user_id, $jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'], $jabber_user->data_model[$jabber_user->table_name]['jabber_password']['value']);
		    $rs .= '<div class="alert alert-block alert-success">Данные учетной записи обновлены</div>';
		    return $rs;
		} else {
		    $jabber_user->riseError('Ошибка при авторизации. Неправильно указан логин или пароль.');
		}
	    }
	    $rs .= $jabber_user->get_form($jabber_user->data_model[$jabber_user->table_name], 'new', 0, 'Сохранить');
	    return $rs;
	}

	if ($this->getRequestValue('do') == 'edit_done' or $this->getRequestValue('do') == 'edit') {
	    unset($jabber_user->data_model[$jabber_user->table_name]['jabber_password']);
	    $jabber_user->data_model[$jabber_user->table_name]['jabber_id']['title'] = 'Логин (не меняйте это поле, если хотите только поменять пароль). При создании новой учетной записи впишите новый логин.';

	    $rs = $this->get_config_top_menu();
	    if ($this->getRequestValue('do') == 'edit_done') {
		$jabber_user->data_model[$jabber_user->table_name] = $data_model->init_model_data_from_request($jabber_user->data_model[$jabber_user->table_name]);
		if (strlen($jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value']) >= 6) {
		    if ($jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'] != $user_info['jabber_id']) {
			if (!preg_match('|^[A-Z0-9_.]+$|i', $jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'])) {
			    $jabber_user->riseError('В логине допустимы только латинские буквы и цифры и символ _');
			} else {
			    if ($jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value'] == $jabber_user->data_model[$jabber_user->table_name]['jabber_new_password_a']['value']) {
				//Попробуем зарегистрировать нового пользователя
				$response = $this->reg_user($jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'], $jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value'], $user_info['fio'], $user_info['email'], $user_info['server_name']);
				if ($response == 'register_success') {
				    $this->update_jabber_info($user_id, $jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'], $jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value']);
				    $rs .= '<div class="alert alert-block alert-success">Новый пользователь зарегистрирован успешно</div>';
				    return $rs;
				} else {
				    $jabber_user->riseError('Ошибка при регистрации нового пользователя. Возможно такой пользователь уже существует.');
				}
			    } else {
				$jabber_user->riseError('Новые пароли не совпадают');
			    }
			}
		    }
		} else {
		    $jabber_user->riseError('Новый пароль меньше 6-ти символов');
		}
		$auth_response = $this->remote_auth($user_info['jabber_id'], $user_info['jabber_password']);
		if ($auth_response == 'success_auth') {
		    //Можем менять
		    if (!preg_match('|^[A-Z0-9_.]+$|i', $jabber_user->data_model[$jabber_user->table_name]['jabber_id']['value'])) {
			$jabber_user->riseError('В логине допустимы только латинские буквы и цифры и символ _');
		    } else {
			if (strlen($jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value']) >= 6) {
			    if ($jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value'] == $jabber_user->data_model[$jabber_user->table_name]['jabber_new_password_a']['value']) {
				$change_password_response = $this->change_password($user_info['jabber_id'], $user_info['jabber_password'], $jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value']);
				if ($change_password_response == 'change_password_success') {
				    $this->update_jabber_info($user_id, $user_info['jabber_id'], $jabber_user->data_model[$jabber_user->table_name]['jabber_new_password']['value']);
				    $rs .= '<div class="alert alert-block alert-success">Пароль изменен успешно</div>';
				    return $rs;
				}
			    } else {
				$jabber_user->riseError('Новые пароли не совпадают');
			    }
			} else {
			    $jabber_user->riseError('Новый пароль меньше 6-ти символов');
			}
		    }
		} else {
		    $jabber_user->riseError('Нет доступа к учетной записи. Возможно неправильно указали пароль');
		}
	    }
	    $rs .= $jabber_user->get_form($jabber_user->data_model[$jabber_user->table_name], 'edit', 0, 'Сохранить');
	    return $rs;
	}
    }

    function get_config_top_menu() {
	return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/messenger/admin/template/config_top_menu.tpl');
    }

    function reg_user($login, $password, $full_name, $email, $group_name) {
	$response = $this->getCurlContent('https://www.sitebill.ru/apps/jabber/js/ajax.php?action=register&login=' . $login . '&password=' . $password . '&full_name=' . $full_name . '&email=' . $email . '&group_name=' . $group_name);
	return $response;
    }

    function change_password($login, $password, $new_password) {
	$response = $this->getCurlContent('https://www.sitebill.ru/apps/jabber/js/ajax.php?action=change_password&login=' . $login . '&password=' . $password . '&new_password=' . $new_password);
	return $response;
    }

    function remote_auth($login, $password) {
	$response = $this->getCurlContent('https://www.sitebill.ru/apps/jabber/js/ajax.php?action=auth&login=' . $login . '&password=' . $password);
	return $response;
    }

    function getCurlContent($url, $to_file = '') {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	if (!empty($to_file)) {
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FILE, $to_file);
	}
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	$res = curl_exec($ch);
	$error = curl_error($ch);
	$errno = curl_errno($ch);
	if ($errno != 0) {
	    return false;
	}
	curl_close($ch);
	return $res;
    }

    /**
     * Получаем логин и пароль для подключения к jabber серверу
     * Если у пользователя в таблице user еще их нет, то создаются значения автоматически и сохраняются в
     * user.jabber_id, user.jabber_password
     * @param integer $user_id идентификатор пользователя
     * @return array массив с информацией о пользователе
     */
    function get_user_jabber_info($user_id) {
	$DBC = DBC::getInstance();
	$server_name = str_replace(':', '', $_SERVER['HTTP_HOST']);
	$server_name = str_replace('www.', '', $server_name);

	//Попробуем получить записи из таблицы user
	$query = 'SELECT * FROM ' . DB_PREFIX . '_user WHERE `user_id`=?';
	$stmt = $DBC->query($query, array($user_id));
	if ($stmt) {
	    $ar = $DBC->fetch($stmt);
	    if ($ar['jabber_id'] != '' and $ar['jabber_password'] != '') {
		$ar['server_name'] = $server_name;
		return $ar;
	    }
	}
	//Если инфу не получили, то создаем автоматически логин и пароль для пользователя
	//На всякий случай создадим поля в базе если их еще нет
	$query_alter = 'ALTER TABLE ' . DB_PREFIX . '_user ADD COLUMN jabber_id varchar(255)';
	$DBC->query($query_alter, array());
	$query_alter = 'ALTER TABLE ' . DB_PREFIX . '_user ADD COLUMN jabber_password varchar(255)';
	$DBC->query($query_alter, array());

	$query = 'UPDATE ' . DB_PREFIX . '_user set jabber_id=?, jabber_password=? where user_id=?';
	$jabber_id = $ar['login'] . '.' . $server_name; //хз пока как генерить ид пользователя, поэтому рандомно
	$jabber_password = $this->randomPassword();

	$stmt = $DBC->query($query, array($jabber_id, $jabber_password, $user_id));
	$ar['jabber_id'] = $jabber_id;
	$ar['jabber_password'] = $jabber_password;
	$ar['server_name'] = $server_name;
	return $ar;
    }

    function update_jabber_info($user_id, $jabber_id, $jabber_password) {
	$DBC = DBC::getInstance();
	$query = 'UPDATE ' . DB_PREFIX . '_user set jabber_id=?, jabber_password=? where user_id=?';
	$stmt = $DBC->query($query, array($jabber_id, $jabber_password, $user_id));
    }

    function randomPassword() {
	$alphabet = "abcdefghijklmnopqrstuwxyz0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < 8; $i++) {
	    $n = rand(0, $alphaLength);
	    $pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
    }

}
