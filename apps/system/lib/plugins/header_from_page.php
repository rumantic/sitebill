<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Header from page plugins
 * Get content from page body and put it into header
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class header_from_page extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Main
     */
    function main () {
        global $smarty;
        $page = array();
        /*
        $page = $this->getPageByURI('header1');
        $smarty->assign('header_code1', $page['body']);

        $page = array();
        $page = $this->getPageByURI('header2');
        $smarty->assign('header_code2', $page['body']);

        $page = array();
        $page = $this->getPageByURI('header3');
        $smarty->assign('header_code3', $page['body']);

        $page = array();
        $page = $this->getPageByURI('header4');
        $smarty->assign('header_code4', $page['body']);

        $page = array();
        $page = $this->getPageByURI('header5');
        $smarty->assign('header_code5', $page['body']);

        $page = array();
        $page = $this->getPageByURI('header6');
        $smarty->assign('header_code6', $page['body']);

        $page = array();
        $page = $this->getPageByURI('left1');
        $smarty->assign('left1', $page['body']);

        $page = array();
        $page = $this->getPageByURI('footer1');
        $smarty->assign('footer_code1', $page['body']);

        $page = array();
        $page = $this->getPageByURI('footer2');
        $smarty->assign('footer_code2', $page['body']);
        */

        return true;
    }
}
?>
