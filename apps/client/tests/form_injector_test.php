<?php
require_once ('../../system/bootstrap.php');
if ( !defined('TEST_ENABLED') ) {
    echo 'test disabled';
    exit;
}
echo 'form_injector_test<br>';
$form_injector = new \client\admin\Form_Injection();
$item['value'] = 123;
echo $form_injector->get_client_info($item);
