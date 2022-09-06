<?php
session_start();
require_once('../../system/bootstrap.php');

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php');
$table_admin = new table_admin();
echo $table_admin->ajax();
?>
