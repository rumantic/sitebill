<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Admin3 frontend (legacy)
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

class admin3_site extends admin3_admin {

    function frontend() {
        return false;
    }

}
