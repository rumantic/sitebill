<?php
if ( $_REQUEST['key'] != 'j38xkshwnvx' ) {
    die('wrong key');
}
require_once ('../system/bootstrap.php');
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php');
$geodata_admin = new geodata_admin();

echo $geodata_admin->parse();

