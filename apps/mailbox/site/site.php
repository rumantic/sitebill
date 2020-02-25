<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * mailbox fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class mailbox_site extends mailbox_admin {

    function frontend() {
        global $smarty;
        //$this->type='question';
        if (!$this->getConfigValue('apps.mailbox.enable')) {
            return false;
        }

        $REQUESTURIPATH = Sitebill::getClearRequestURI();

        if (preg_match('/^mailbox(\/(.*)?)?$/', $REQUESTURIPATH)) {
            $uid = (int) $_SESSION['user_id'];
            if ($uid == 0 or ! isset($uid)) {
                $this->set_apps_template('mailbox', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl.html');
                $this->set_apps_template('mailbox', $this->getConfigValue('theme'), 'mailbox_inc_file', 'need_login.tpl');
                return true;
            }
            if (preg_match('/^mailbox\/delete\/(\d+)(\/?)$/', $REQUESTURIPATH, $matches)) {
                $DBC = DBC::getInstance();
                $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE mailbox_id=? AND reciever_id=?';
                $stmt = $DBC->query($query, array($matches[1], $uid));
                header('location: ' . SITEBILL_MAIN_URL . '/mailbox/');
                exit();
            } else {
                $breadcrumbs[] = '<a href="/">' . Multilanguage::_('L_HOME') . '</a>';
                $breadcrumbs[] = Multilanguage::_('APP_NAME', 'mailbox');
                $this->template->assert('title', Multilanguage::_('APP_NAME', 'mailbox'));
                $this->template->assert('breadcrumbs', implode(' / ', $breadcrumbs));
                if ($uid > 0) {
                    $msgs = $this->getUserIncomingMessages($uid);
                    //print_r($msgs['messages']);
                    $smarty->assign('msgs', $msgs['messages']);
                    $this->set_apps_template('mailbox', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl.html');
                    $this->set_apps_template('mailbox', $this->getConfigValue('theme'), 'mailbox_inc_file', 'list.tpl.html');
                } else {
                    
                }
            }
            return true;
        }
        return false;
    }

    function fontend_account(){
        global $smarty;
        $REQUESTURIPATH=Sitebill::getClearRequestURI();
        $uid=(int)$_SESSION['user_id'];
        if(preg_match('/^account\/mailbox\/delete\/(\d+)(\/?)$/', $REQUESTURIPATH, $matches)){
            $DBC=DBC::getInstance();
            $query='DELETE FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE mailbox_id=? AND reciever_id=?';
            $stmt=$DBC->query($query, array($matches[1], $uid));
            header('location: '.SITEBILL_MAIN_URL.'/account/mailbox/');
            exit();
        }else{
            if($uid>0){
                $msgs=$this->getUserIncomingMessages($uid);
                if($msgs['count']>0){
                    foreach($msgs['messages'] as &$r){
                        $r['_delhref']=SITEBILL_MAIN_URL.'/account/mailbox/delete/'.$r['mailbox_id'];
                    }
                }
                $smarty->assign('msgs',$msgs['messages']);
                $tpl=$this->get_apps_template('mailbox', $this->getConfigValue('theme'), 'mailbox_inc_file', 'list.tpl.html');
                $smarty->assign('mailbox_inc_file', $tpl);
                $ctpl=$this->get_apps_template('mailbox', $this->getConfigValue('theme'), 'mailbox_inc_file', 'main.tpl.html');
                return $smarty->fetch($ctpl);
            }
        }
        return '';
    }
	
}