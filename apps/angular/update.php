<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/angular/admin/admin.php');

class angular_update extends angular_admin
{
    /**
     * Construct
     */
    function __construct()
    {
        $this->sitebill();
    }

    function pre_update () {
        $dist_files_all = $this->load_dist_files_list();
        $dist_files = $dist_files_all['dist_files'];
        return $this->delete_dist_files($dist_files);
    }

    function main($secret_key = '')
    {
        $dist_files_all = $this->load_dist_files_list();
        $dist_files = $dist_files_all['dist_files'];
        return $this->link_dist_files_to_root($dist_files);
    }
}
