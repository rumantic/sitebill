<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/admin3/admin/admin.php');

class admin3_update extends admin3_admin
{
    /**
     * Construct
     */
    function __construct()
    {
        parent::__construct();
    }

    function main($secret_key = '')
    {
        return 'update admin3 config';
    }
}
