<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Memorylist fronend - работаем со сохраненными списками пользователей
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class memorylist_site extends memorylist_admin {

    function frontend() {
	global $smarty;

	$REQUESTURIPATH = Sitebill::getClearRequestURI();

	if (preg_match('/^memorylist(\/(.*)?)?$/', $REQUESTURIPATH)) {
	    $uid = (int) $_SESSION['user_id'];
	    if ($uid == 0 or ! isset($uid)) {
		$this->go_to_login();
	    }
	    
	    require_once SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/admin/memory_list.php';
	    $ML=new Memory_List();
	    
	    $breadcrumbs[]='<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
	    $breadcrumbs[]='<a href="'.SITEBILL_MAIN_URL.'/account/">'.Multilanguage::_('L_ACCOUNT').'</a>';
	    $breadcrumbs[]='<a href="'.SITEBILL_MAIN_URL.'/account/data/">'.Multilanguage::_('L_MY_DATA').'</a>';
	    $breadcrumbs[]=Multilanguage::_('APP_NAME','memorylist');
	    $this->template->assert('title', Multilanguage::_('APP_NAME','memorylist'));
	    $this->template->assert('breadcrumbs', implode(' / ',$breadcrumbs));
	    
	    if ( $this->getRequestValue('do') == 'getpdf' ) {
		$ML->_getpdfAction();
		
	    } else {
		$this->template->assign('main',$ML->grid());
		//$this->set_apps_template('memorylist', $this->getConfigValue('theme'), 'main_file_tpl', 'mygrid.tpl');
	    }
	    
	    return true;
	}
	return false;
    }

}
