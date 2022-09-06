<?php
include ('api_header.php');

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.common.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.controller.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.static_data.php');
$api_controller = new API_Controller();
$api_controller->main();
