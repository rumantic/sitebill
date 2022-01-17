<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class Cloud_Provider {
    private $dev_mode = false;
    private $show_main_component = false;
    
    function enable_dev_mode () {
        $this->dev_mode = true;
    }
    
    function is_dev_mode () {
        return $this->dev_mode;
    }
    
    function show_main_component () {
        $this->show_main_component = true;
    }
    
    function hide_main_component () {
        $this->show_main_component = false;
    }
    
    function redirect ( $REQUEST_URI, $hash ) {
        if ( isset($_REQUEST['redirected']) ) {
            return false;
        }
        header('location: ' . SITEBILL_MAIN_URL . $REQUEST_URI.'&redirected=1'.$hash);
        exit;
    }
    
    
    function fetch_runner_template () {
        $this->template = new Template();
        $local_request_array = array();
        if ( $this->is_dev_mode() ) {
            $this->template->assign('dev_mode', 1);
        }
        if ( $this->show_main_component ) {
            $local_request_array['full_size_env'] = 1;
        }
        $this->template->assign('json_request', json_encode($local_request_array));
        
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/cloud/template/angular-runner.tpl');
    }
}