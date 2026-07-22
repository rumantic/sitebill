<?php
session_start();
require_once('../../system/bootstrap.php');

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/dashboard/admin/admin.php');
$local_dashboard_admin = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/apps/dashboard/admin/admin.php';
if (file_exists($local_dashboard_admin) ) {
    require_once($local_dashboard_admin);
    $dashboard_admin = new local_dashboard_admin();
} else {
    $dashboard_admin = new dashboard_admin();
}
$dashboard_admin->ajax();
?>
