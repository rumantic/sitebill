<?php
require_once ('../../system/bootstrap.php');
if ( !TEST_ENABLED ) {
    echo 'test disabled';
    exit;
}
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php');
$geodata_admin = new geodata_admin();
geodata_admin::geocode_address_by_google('Москва, Мира, 10', $geodata_admin);
if ( $geodata_admin->getError() ) {
    echo $geodata_admin->getError().'<br>';
}
geodata_admin::geocode_address('Москва, Мира, 10', $geodata_admin);
if ( $geodata_admin->getError() ) {
    echo $geodata_admin->getError().'<br>';
}
