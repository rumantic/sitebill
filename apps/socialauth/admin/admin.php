<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * SocialAuth backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class socialauth_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        parent::__construct();
        Multilanguage::appendAppDictionary('socialauth');
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        $this->action = 'socialauth';

        if (!$config_admin->check_config_item('apps.socialauth.salt')) {
            $config_admin->addParamToConfig('apps.socialauth.salt', substr(md5(time()), 0, 6), 'Соль для автоматических паролей регистраций через соцсети');
        }

        if (!$config_admin->check_config_item('apps.socialauth.default_group_id')) {
            $config_admin->addParamToConfig('apps.socialauth.default_group_id', 3, 'ID группы устанавливаемой новой регистрации');
        }

        if (!$config_admin->check_config_item('apps.socialauth.vk.enable')) {
            $config_admin->addParamToConfig('apps.socialauth.vk.enable', 0, 'Включить авторизацию через Вконтакте');
        }

        if (!$config_admin->check_config_item('apps.socialauth.fb.enable')) {
            $config_admin->addParamToConfig('apps.socialauth.fb.enable', 0, 'Включить авторизацию через Facebook');
        }

        if (!$config_admin->check_config_item('apps.socialauth.tw.enable')) {
            $config_admin->addParamToConfig('apps.socialauth.tw.enable', 0, 'Включить авторизацию через Twitter');
        }

        if (!$config_admin->check_config_item('apps.socialauth.gl.enable')) {
            $config_admin->addParamToConfig('apps.socialauth.gl.enable', 0, 'Включить авторизацию через Google');
        }

        if (!$config_admin->check_config_item('apps.socialauth.tg.enable')) {
            $config_admin->addParamToConfig('apps.socialauth.tg.enable', 0, 'Включить авторизацию через Telegram', 1);
        }

        if (!$config_admin->check_config_item('apps.socialauth.tg.bot_token')) {
            $config_admin->addParamToConfig('apps.socialauth.tg.bot_token', '', 'Токен Telegram-Бота');
        }

        if (!$config_admin->check_config_item('apps.socialauth.tg.bot_name')) {
            $config_admin->addParamToConfig('apps.socialauth.tg.bot_name', '', 'Имя Telegram-Бота');
        }

        if (!$config_admin->check_config_item('apps.socialauth.ok.enable')) {
            $config_admin->addParamToConfig('apps.socialauth.ok.enable', 0, 'Включить авторизацию через Одноклассники');
        }

        if (!$config_admin->check_config_item('apps.socialauth.vk.api_key')) {
            $config_admin->addParamToConfig('apps.socialauth.vk.api_key', '', 'VK API_KEY');
        }

        if (!$config_admin->check_config_item('apps.socialauth.vk.secret')) {
            $config_admin->addParamToConfig('apps.socialauth.vk.secret', '', 'VK SECRET');
        }

        if (!$config_admin->check_config_item('apps.socialauth.vk.redirect_url')) {
            $config_admin->addParamToConfig('apps.socialauth.vk.redirect_url', '', 'VK REDIRECT_URI');
        }

        if (!$config_admin->check_config_item('apps.socialauth.fb.client_id')) {
            $config_admin->addParamToConfig('apps.socialauth.fb.client_id', '', 'FB CLIENT_ID');
        }

        if (!$config_admin->check_config_item('apps.socialauth.fb.client_secret')) {
            $config_admin->addParamToConfig('apps.socialauth.fb.client_secret', '', 'FB CLIENT_SECRET');
        }

        if (!$config_admin->check_config_item('apps.socialauth.fb.redirect_url')) {
            $config_admin->addParamToConfig('apps.socialauth.fb.redirect_url', '', 'FB REDIRECT_URI');
        }

        if (!$config_admin->check_config_item('apps.socialauth.ok.client_id')) {
            $config_admin->addParamToConfig('apps.socialauth.ok.client_id', '', 'ODNOKLASSNIKI CLIENT_ID');
        }

        if (!$config_admin->check_config_item('apps.socialauth.ok.public_key')) {
            $config_admin->addParamToConfig('apps.socialauth.ok.public_key', '', 'ODNOKLASSNIKI PUBLIC_KEY');
        }

        if (!$config_admin->check_config_item('apps.socialauth.ok.client_secret')) {
            $config_admin->addParamToConfig('apps.socialauth.ok.client_secret', '', 'ODNOKLASSNIKI CLIENT_SECRET');
        }

        if (!$config_admin->check_config_item('apps.socialauth.ok.redirect_url')) {
            $config_admin->addParamToConfig('apps.socialauth.ok.redirect_url', '', 'ODNOKLASSNIKI REDIRECT_URI');
        }

        if (!$config_admin->check_config_item('apps.socialauth.gl.client_id')) {
            $config_admin->addParamToConfig('apps.socialauth.gl.client_id', '', 'GOOGLE CLIENT_ID');
        }

        if (!$config_admin->check_config_item('apps.socialauth.gl.client_secret')) {
            $config_admin->addParamToConfig('apps.socialauth.gl.client_secret', '', 'GOOGLE CLIENT_SECRET');
        }

        if (!$config_admin->check_config_item('apps.socialauth.gl.redirect_url')) {
            $config_admin->addParamToConfig('apps.socialauth.gl.redirect_url', '', 'GOOGLE REDIRECT_URI');
        }

        if (!$config_admin->check_config_item('apps.socialauth.tw.api_key')) {
            $config_admin->addParamToConfig('apps.socialauth.tw.api_key', '', 'TWITTER API_KEY');
        }

        if (!$config_admin->check_config_item('apps.socialauth.tw.client_secret')) {
            $config_admin->addParamToConfig('apps.socialauth.tw.client_secret', '', 'TWITTER CLIENT_SECRET');
        }

        if (!$config_admin->check_config_item('apps.socialauth.tw.redirect_url')) {
            $config_admin->addParamToConfig('apps.socialauth.tw.redirect_url', '', 'TWITTER REDIRECT_URI');
        }

        /* if ( !$config_admin->check_config_item('apps.socialauth.gl.button_html') ) {
          $config_admin->addParamToConfig('apps.socialauth.gl.button_html', '', 'Код кнопки Google', 3);
          } */
    }

    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=update_salt" class="btn btn-primary">Обновить пароли пользователей из социальных сетей с учетом соли</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=check" class="btn btn-primary">Обновить данные пользователей под новую систему авторизации</a> ';
        return $rs;
    }

    protected function _defaultAction() {
        $rs = Multilanguage::_('TEXT', 'socialauth');
        return $rs;
    }

    /* protected function _checkAction(){


      $rs .= 'Производим обновление пользователей на новую систему авторизации<br />';

      $DBC=DBC::getInstance();
      $query='SELECT user_id, login, email, tw_id, vk_id, ok_id, gl_id, fb_id FROM re_user';
      $stmt=$DBC->query($query);

      if($stmt){
      while($ar=$DBC->fetch($stmt)){
      if(preg_match('/^(tw|ok|gl|fb|vk)([0-9]+)$/', $ar['login'], $matches)){
      $ar['_t']=$matches[1];
      if($ar[$matches[1].'_id']==''){
      $ar['_i']=$matches[2];
      $ret[]=$ar;
      }


      }elseif(preg_match('/^(tw|ok|gl|fb|vk)([0-9]+)@(tw|ok|gl|fb|vk)/', $ar['email'])){
      $ar['_t']=$matches[1];
      if($ar[$matches[1].'_id']==''){
      $ar['_i']=$matches[2];
      $ret[]=$ar;
      }

      }
      }
      }

      if(!empty($ret)){
      $count=0;
      foreach ($ret as $ar){
      $query='UPDATE re_user SET `'.$ar['_t'].'_id`=? WHERE user_id=?';
      $stmt=$DBC->query($query, array($ar['_i'], $ar['user_id']));
      $count+=1;
      }
      $rs .= 'Обновлено '.$count.' пользователей';
      }else{
      $rs .= 'Нет пользователей нуждающихся в обновлении';
      }
      return $rs;
      } */

    protected function _update_saltAction() {
        $this->updateSocialNetworkUsersPasswordsWithNewSalt();
        $rs = $this->_defaultAction();
        return $rs;
    }

    /*
      function main () {
      $rs=Multilanguage::_('TEXT','socialauth');
      return $rs;
      } */

    public function _preload() {
        if ($this->getConfigValue('apps.socialauth.vk.enable')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
            $VK = Vk_Logger::getInstance();
            $vk_url = $VK->getLoginUrl();
            //$this->template->assign('vk_url', $vk_url);
            $this->template->assign('vk_url', SITEBILL_MAIN_URL . '/socialauth/login/vkontakte/');
        } else {
            $this->template->assign('vk_url', '');
        }
        $this->template->assign('socialauth_panel', $this->getSocialAuthPanel());
    }

    public function getSocialAuthPanel() {
        global $smarty;
        $any_auth = (intval($this->getConfigValue('apps.socialauth.vk.enable')) || intval($this->getConfigValue('apps.socialauth.ok.enable')) || intval($this->getConfigValue('apps.socialauth.tw.enable')) || intval($this->getConfigValue('apps.socialauth.gl.enable')) || intval($this->getConfigValue('apps.socialauth.fb.enable')));
        if ((int) $this->getSessionUserId() == 0 && $any_auth) {
            $smarty->assign('vk_login_enable', (int) $this->getConfigValue('apps.socialauth.vk.enable'));
            $smarty->assign('ok_login_enable', (int) $this->getConfigValue('apps.socialauth.ok.enable'));
            $smarty->assign('tw_login_enable', (int) $this->getConfigValue('apps.socialauth.tw.enable'));
            $smarty->assign('gl_login_enable', (int) $this->getConfigValue('apps.socialauth.gl.enable'));
            $smarty->assign('fb_login_enable', (int) $this->getConfigValue('apps.socialauth.fb.enable'));
            $smarty->assign('tg_login_enable', (int) $this->getConfigValue('apps.socialauth.tg.enable'));
            $smarty->assign('tg_bot_name', $this->getConfigValue('apps.socialauth.tg.bot_name'));
            $smarty->assign('tg_url_back', $this->getServerFullUrl() . '/socialauth/login?do=login_tg');
            //$smarty->assign('gl_button_html', $this->getConfigValue('apps.socialauth.gl.button_html'));
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/site/template/login.tpl';
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/socialauth/site/template/login.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/socialauth/site/template/login.tpl';
            }
            $buttons_tpl = SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/site/template/buttons.tpl';
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/socialauth/site/template/buttons.tpl')) {
                $buttons_tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/socialauth/site/template/buttons.tpl';
            }
            $smarty->assign('buttons_tpl', $buttons_tpl);
            return $smarty->fetch($tpl);
        } else {
            return '';
        }
    }

    private function updateSocialNetworkUsersPasswordsWithNewSalt() {
        $users = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT `user_id`, `login` FROM ' . DB_PREFIX . '_user';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (preg_match('/^(ok|fb|vk|gl|tw)[\d][\d][\d](\d+)$/', $ar['login'])) {
                    $users[] = $ar;
                }
            }
        }
        if (!empty($users)) {
            $query = 'UPDATE ' . DB_PREFIX . '_user SET `password`=? WHERE `user_id`=?';
            foreach ($users as $user) {
                $new_password = md5($user['login'] . $this->getConfigValue('apps.socialauth.salt'));
                $stmt = $DBC->query($query, array($new_password, $user['user_id']));
            }
        }
    }

}
