<?php
/**
 * LoginFormTrait — UI/form rendering methods for Login class
 *
 * Extracted methods:
 *   - loginForm()
 *   - wellcomePage()
 *   - getAuthMenu()
 *   - getUserMenu()
 *   - alreadyLogin()
 */
trait LoginFormTrait
{
    function alreadyLogin()
    {
        $rs = Multilanguage::_('YOU_AUTHORIZED', 'system');
        return $rs;
    }

    function getUserMenu()
    {
        $user_id = $this->getSessionUserId();
        if ($user_id > 0/* || $this->USER_isUserAuthorized() */) {
            global $smarty;
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');

            $Account = new Account;
            //$smarty->assign('fio', $this->getFio($user_id));
            $smarty->assign('fio', $_SESSION['current_user_name']);
            $smarty->assign('ballance', $Account->getAccountValue($user_id));
            $smarty->assign('total_data_count', $Account->get_user_data_count($user_id));
            $rs = $smarty->fetch('user_menu.tpl');
        } else {
            $rs = '';
        }
        return $rs;
    }

    /**
     * Get auth menu
     * @param void
     * @return string
     */
    function getAuthMenu()
    {
        global $estate_folder;
        global $smarty;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');

        $Account = new Account;

        $user_id = $this->getSessionUserId();
        if ($user_id > 0) {
            $smarty->assign('fio', $this->getFio($user_id));
            $smarty->assign('ballance', $Account->getAccountValue($user_id));
            $smarty->assign('total_data_count', $Account->get_user_data_count($user_id));
            $rs = $smarty->fetch('user_menu.tpl');
        } else {
            if ($this->getConfigValue('theme') == 'albostar') {
                $rs = '<h1>' . Multilanguage::_('L_AUTH_TITLE') . '</h1>';
                if ($this->getConfigValue('ajax_auth_form')) {
                    $rs .= $this->get_ajax_auth_form();
                } else {
                    $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL . '/login/', $this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
                }
                $rs .= '';
            } else {
                $rs = '<table border="0" cellpadding="0" cellspacing="0" align="center">
		                                        <tr>
		                                            <td class="special">
		                                            	<div id="admin_area">
		        ';
                $social_link = false;
                if ($this->getConfigValue('apps.socialauth.fb.enable')) {
                    //require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/facebook/fb_logger.php');
                    //$FB = FB_Logger::getInstance();
                    //$rs .= $FB->getLoginURL();
                }

                if ($this->getConfigValue('apps.socialauth.vk.enable')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
                    $VK = Vk_Logger::getInstance();
                    $social_link .= $VK->getLoginLink();
                }
                if ($social_link) {
                    $rs .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/apps/socialauth/css/style.css" />';
                    $rs .= '<div class="login_label">' . Multilanguage::_('LOGIN_BY', 'system') . ':</div> ' . $social_link . '<br><div class="clr"></div>';
                }

                if ($this->isDemo()) {
                    $rs .= '<div class="clr"></div>login: admin, password: admin';
                }
                if ($this->getConfigValue('ajax_auth_form')) {
                    $rs .= $this->get_ajax_auth_form();
                } else {
                    $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL . '/login/', $this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
                }
                $rs .= '</div></td></tr></table>';
            }
        }


        return $rs;
    }

    /**
     * Get wellcome page
     * @param void
     * @return string
     */
    function wellcomePage()
    {
        $back_url = $_SESSION['go_after_login'];
        unset($_SESSION['go_after_login']);
        $rs = '<h1>Добро пожаловать!</h1>';
        $rs .= 'Перейти в <a href="' . SITEBILL_MAIN_URL . '/account/data/">личный кабинет</a>';
        if (!preg_match('/login/', $back_url) && !preg_match('/logout/', $back_url)) {
            $rs .= '<script type="text/javascript">location.href="' . $back_url . '"</script>';
        }
        return $rs;
    }

    /**
     * Login form
     * @param void
     * @return string
     */
    function loginForm()
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/local_login_form.tpl')) {
            $this->template->assign('allow_register_account', $this->getConfigValue('allow_register_account'));
            $this->template->assign('allow_remind_password', $this->getConfigValue('allow_remind_password'));
            if ($this->getError() and $this->GetErrorMessage() != 'not login') {
                $this->template->assign('error_message', $this->GetErrorMessage());
            }

            return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/local_login_form.tpl');
        }

        $rs = '<table border="0" cellpadding="0" cellspacing="0" align="center" class="front_login_form_table"><tr><td class="special"><div id="admin_area" class="front_login_form"><h1>' . Multilanguage::_('L_AUTH_TITLE') . '</h1><br><div class="row-fluid">';
        if ($this->getConfigValue('ajax_auth_form')) {
            $rs .= $this->get_ajax_auth_form();
        } else {
            $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL . '/login/', $this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
        }
        $rs .= '</div></div>';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/admin/admin.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/admin/admin.php';
            $SA = new socialauth_admin();
            $panel = $SA->getSocialAuthPanel();
            if ($panel != '') {
                $rs .= '<h2>' . Multilanguage::_('L_AUTH_BYSOCIAL') . '</h2>';
                $rs .= $SA->getSocialAuthPanel();
            }
        }

        $rs .= '</td></tr></table>';
        return $rs;
    }
}
