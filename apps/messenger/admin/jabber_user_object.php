<?php
/**
 * Jabber user object
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Jabber_User_Object extends Object_Manager {
    /**
     * Constructor
     */
    function __construct($user_mode=false) {
        $this->SiteBill();
        $this->table_name = 'user';
        $this->primary_key = 'user_id';
        $this->action = 'messenger';
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/admin/jabber_user_model.php');
	$jabber_user_model = new Jabber_User_Model();
	$this->data_model = $jabber_user_model->get_model();
    }
}
