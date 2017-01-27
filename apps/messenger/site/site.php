<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Messenger fronend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class messenger_site extends messenger_admin {

    function frontend() {
	if (!$this->getConfigValue('apps.messenger.frontend_enable')) {
	    return false;
	}

	$REQUESTURIPATH = Sitebill::getClearRequestURI();

	if (preg_match('/^messenger(\/(.*)?)?$/', $REQUESTURIPATH)) {
	    $user_id = $this->getSessionUserId();
	    if ( $user_id > 0 ) {
		$user_info = $this->get_user_jabber_info($user_id);
		$this->template->assign('messenger_frontend', 'true');
		if (in_array($this->getRequestValue('do'), array('edit', 'edit_done', 'new', 'new_done'))) {
		    $this->template->assign('messenger_body', $this->config_action($user_id));
		    $this->set_apps_template('messenger', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl');
		} else {
		    $this->template->assign('user_info', $user_info);
		    $this->template->assign('messenger_body', $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/messenger/admin/template/chatroom.tpl'));
		    $this->set_apps_template('messenger', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl');
		}
		
		return true;
	    }
	}
	return false;
    }

}
