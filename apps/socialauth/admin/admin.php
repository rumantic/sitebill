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
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('socialauth');
    	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
    	$config_admin = new config_admin();
    	
    	$this->action='socialauth';
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.salt') ) {
    		$config_admin->addParamToConfig('apps.socialauth.salt', substr(md5(time()), 0, 6), 'Соль для автоматических паролей регистраций через соцсети');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.default_group_id') ) {
    		$config_admin->addParamToConfig('apps.socialauth.default_group_id', 3, 'ID группы устанавливаемой новой регистрации');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.vk.enable') ) {
    		$config_admin->addParamToConfig('apps.socialauth.vk.enable', 0, 'Включить авторизацию через Вконтакте');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.fb.enable') ) {
    		$config_admin->addParamToConfig('apps.socialauth.fb.enable', 0, 'Включить авторизацию через Facebook');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.tw.enable') ) {
    		$config_admin->addParamToConfig('apps.socialauth.tw.enable', 0, 'Включить авторизацию через Twitter');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.gl.enable') ) {
    		$config_admin->addParamToConfig('apps.socialauth.gl.enable', 0, 'Включить авторизацию через Google');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.ok.enable') ) {
    		$config_admin->addParamToConfig('apps.socialauth.ok.enable', 0, 'Включить авторизацию через Одноклассники');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.vk.api_key') ) {
    		$config_admin->addParamToConfig('apps.socialauth.vk.api_key', '', 'VK API_KEY');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.vk.secret') ) {
    		$config_admin->addParamToConfig('apps.socialauth.vk.secret', '', 'VK SECRET');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.vk.redirect_url') ) {
    		$config_admin->addParamToConfig('apps.socialauth.vk.redirect_url', '', 'VK REDIRECT_URI');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.fb.client_id') ) {
    		$config_admin->addParamToConfig('apps.socialauth.fb.client_id', '', 'FB CLIENT_ID');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.fb.client_secret') ) {
    		$config_admin->addParamToConfig('apps.socialauth.fb.client_secret', '', 'FB CLIENT_SECRET');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.fb.redirect_url') ) {
    		$config_admin->addParamToConfig('apps.socialauth.fb.redirect_url', '', 'FB REDIRECT_URI');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.ok.client_id') ) {
    		$config_admin->addParamToConfig('apps.socialauth.ok.client_id', '', 'ODNOKLASSNIKI CLIENT_ID');
    	}
    	 
    	if ( !$config_admin->check_config_item('apps.socialauth.ok.public_key') ) {
    		$config_admin->addParamToConfig('apps.socialauth.ok.public_key', '', 'ODNOKLASSNIKI PUBLIC_KEY');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.ok.client_secret') ) {
    		$config_admin->addParamToConfig('apps.socialauth.ok.client_secret', '', 'ODNOKLASSNIKI CLIENT_SECRET');
    	}
    	 
    	if ( !$config_admin->check_config_item('apps.socialauth.ok.redirect_url') ) {
    		$config_admin->addParamToConfig('apps.socialauth.ok.redirect_url', '', 'ODNOKLASSNIKI REDIRECT_URI');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.gl.client_id') ) {
    		$config_admin->addParamToConfig('apps.socialauth.gl.client_id', '', 'GOOGLE CLIENT_ID');
    	}
    	 
    	if ( !$config_admin->check_config_item('apps.socialauth.gl.client_secret') ) {
    		$config_admin->addParamToConfig('apps.socialauth.gl.client_secret', '', 'GOOGLE CLIENT_SECRET');
    	}
    	 
    	if ( !$config_admin->check_config_item('apps.socialauth.gl.redirect_url') ) {
    		$config_admin->addParamToConfig('apps.socialauth.gl.redirect_url', '', 'GOOGLE REDIRECT_URI');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.tw.api_key') ) {
    		$config_admin->addParamToConfig('apps.socialauth.tw.api_key', '', 'TWITTER API_KEY');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.tw.client_secret') ) {
    		$config_admin->addParamToConfig('apps.socialauth.tw.client_secret', '', 'TWITTER CLIENT_SECRET');
    	}
    	
    	if ( !$config_admin->check_config_item('apps.socialauth.tw.redirect_url') ) {
    		$config_admin->addParamToConfig('apps.socialauth.tw.redirect_url', '', 'TWITTER REDIRECT_URI');
    	}
	}
	
	function getTopMenu () {
		$rs = '';
		$rs .= '<a href="?action='.$this->action.'&do=update_salt" class="btn btn-primary">Обновить пароли пользователей из социальных сетей с учетом соли</a> ';
		return $rs;
	}
	
	protected function _defaultAction(){
		$rs=Multilanguage::_('TEXT','socialauth');
		return $rs;
	}
	
	protected function _update_saltAction(){
		$this->updateSocialNetworkUsersPasswordsWithNewSalt();
		$rs=$this->_defaultAction();
		return $rs;
	}
	/*
	function main () {
		$rs=Multilanguage::_('TEXT','socialauth');
		return $rs;
	}*/
	
	public function _preload(){
		if ( $this->getConfigValue('apps.socialauth.vk.enable') ) {
			require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
			$VK = Vk_Logger::getInstance();
			$vk_url = $VK->getLoginUrl();
			//$this->template->assign('vk_url', $vk_url);
			$this->template->assign('vk_url', SITEBILL_MAIN_URL.'/socialauth/login/vkontakte/');
			
		}else{
			$this->template->assign('vk_url', '');
		}
		$this->template->assign('socialauth_panel', $this->getSocialAuthPanel());
	}
	
	private function getSocialAuthPanel(){
		global $smarty;
		if((int)$this->getSessionUserId()==0){
			$smarty->assign('vk_login_enable', (int)$this->getConfigValue('apps.socialauth.vk.enable'));
			$smarty->assign('ok_login_enable', (int)$this->getConfigValue('apps.socialauth.ok.enable'));
			$smarty->assign('tw_login_enable', (int)$this->getConfigValue('apps.socialauth.tw.enable'));
			$smarty->assign('gl_login_enable', (int)$this->getConfigValue('apps.socialauth.gl.enable'));
			$smarty->assign('fb_login_enable', (int)$this->getConfigValue('apps.socialauth.fb.enable'));
			
			$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/site/template/login.tpl';
			return $smarty->fetch($tpl);
		}else{
			return '';
		}
		
	}
	
	
	private function updateSocialNetworkUsersPasswordsWithNewSalt(){
		$users=array();
		$DBC=DBC::getInstance();
		$query='SELECT `user_id`, `login` FROM '.DB_PREFIX.'_user';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				if(preg_match('/^(ok|fb|vk|gl|tw)[\d][\d][\d](\d+)$/', $ar['login'])){
					$users[]=$ar;
				}
			}
		}
		if(!empty($users)){
			$query='UPDATE '.DB_PREFIX.'_user SET `password`=? WHERE `user_id`=?';
			foreach($users as $user){
				$new_password=md5($user['login'].$this->getConfigValue('apps.socialauth.salt'));
				$stmt=$DBC->query($query, array($new_password, $user['user_id']));
			}
		}
	}
}