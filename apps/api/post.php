<?php
$_GET['_lang'] = 'en';
include ('api_header.php');

if ( !SConfig::getConfigValueStatic('apps.api.post_enable') ) {
    echo 'post form disabled';
    exit;
}
if ( SConfig::getConfigValueStatic('apps.api.post_key') == '' or  SConfig::getConfigValueStatic('apps.api.post_key') != $_REQUEST['post_key']) {
    echo 'wrong post key';
    exit;
}
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager_post.php');

$data_manager_post = new Data_Manager_Post();
$data_manager_post->set_default_form_action('/apps/api/post.php');
echo $data_manager_post->main();
