<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/mailbox/admin/admin.php');

class mailbox_update extends mailbox_admin
{

    function main($secret_key = '')
    {
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_data ADD COLUMN blacklist_checked tinyint(1) not null default 0";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_data ADD COLUMN complaint_id int(11) not null default 0";

        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        $DBC=DBC::getInstance();

        foreach ( $query_data as $query ) {
            $success=false;
            $stmt=$DBC->query($query, array(), $rows, $success);
            if ( !$success ) {
                $rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
            }
        }

        $rs .= 'Обновление конфигурации<br>';
        return $rs;
    }

}
