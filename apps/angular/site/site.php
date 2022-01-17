<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Angular site
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class angular_site extends angular_admin {
    function frontend() {
        $REQUESTURIPATH=Sitebill::getClearRequestURI();

        $search_file = basename($REQUESTURIPATH);

        $dist_files_all = $this->load_dist_files_list();
        $dist_files = $dist_files_all['dist_files'];


        if (in_array($search_file, $dist_files)) {
            /*
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /apps/angular/dist/".$REQUESTURIPATH);
            */
            if ( preg_match('/\.css/', $search_file) ) {
                $content_type = 'text/css';
            } else {
                $content_type = 'application/javascript';
            }

            header("Content-Type: $content_type");
            header("Content-length: ".filesize($this->dir.'/'.$search_file));
            header("Accept-Ranges: bytes");
            header("Cache-Control: max-age=604800, public");
            header("Pragma: cache");
            //header('Content-Disposition: attachment; filename="'.basename($REQUESTURIPATH).'"');
            echo file_get_contents($this->dir.'/'.$search_file);
            exit;

        }
    }
}
