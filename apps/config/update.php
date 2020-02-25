<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class config_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }

    function main() {

        $rs = '';

        $DBC = DBC::getInstance();
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_config ADD COLUMN `vtype` INT(11) DEFAULT 0";
        $query_data[] = "ALTER TABLE " . DB_PREFIX . "_config ADD COLUMN `public` INT(11) DEFAULT 0";
        foreach ($query_data as $query) {
            $success = false;
            $stmt = $DBC->query($query, array(), $rows, $success);
            if (!$success) {
                $rs .= Multilanguage::_('ERROR_ON_SQL_RUN', 'system') . ': ' . $query . '<br>';
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
            }
        }

        $rs .= Multilanguage::_('UPDATE_CONFIG_SUCCESS', 'system') . '<br>';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        $config_admin->check_config_structure();
        $config_admin->install_hidden_config();
        // Устанавливаем параметрам публичный доступ
        $config_admin->set_public_access('allow_register_account');
        $config_admin->set_public_access('allow_remind_password');


        return $rs;
    }

}
