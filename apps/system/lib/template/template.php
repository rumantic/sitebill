<?php
/*File: template.php
 *Author: Kondin Dmitry
 *Date: 1.11.05
 *Description: Module to load and compile templates
 */
class Template {
    var $template_name; // Main screen template name
    var $templateString; // Template string
    var $item = array(); // Items array
    var $breadcrumbs=array();


    /**
    * Constructor of the class
    * @param: $init - initialize object
    * @return: nothing
    */
    function Template ($init = null, $debugbarRenderer = false ) {
		global $sitebill_document_uri;
		$this->assert('_document_uri',$sitebill_document_uri);
		$this->assign('SITEBILL_DOCUMENT_ROOT', SITEBILL_DOCUMENT_ROOT);
		if ( $debugbarRenderer ) {
            $this->assign('debugbarRenderer', $debugbarRenderer);
        }
    }

    /**
    * Assert template set
    * @param string|array $set variable name or associative array of variables
    * @param mixed $value value
    * @return boolean
    */
    function assert ( $set, $value = null ) {
        global $smarty;
        if(is_array($set)){
            foreach($set as $k => $v){
                $this->item[$k] = $v;
                SiteBill::set_template_store($k, $v);
            }
            $smarty->assign($set);
        }else{
            $this->item[$set] = $value;
            SiteBill::set_template_store($set, $value);
            $smarty->assign($set, $value);
        }        
        return true;
    }

    /**
     * DEPRECATED
     * Assert template set
     * @param string|array - variable name or associative array of variables
     * @param mixed - value
     * @return boolean
     */
    function assign ( $set, $value = null ) {
        return $this->assert($set, $value);
    }

    function fetch ($template) {
        global $smarty;
        if ( !file_exists($template) ) {
            echo 'template file not exists '.$template;
            exit;
        }
        return $smarty->fetch($template);
    }

    /**
    * Render content
    * @param void
    * @return true - if render complete success
    * @return false - if render failed
    */
    function render () {
        if ( !is_array($this->item) ) {
            return false;
        }
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ( $this->item as $itemKey => $itemValue ) {
        	if(!is_object($itemValue) && !is_array($itemValue)){
        		$this->templateString = str_replace( '{'.$itemKey.'}', $itemValue, $this->templateString );
        	}
        }
        $this->render_page = $this->templateString;
        return true;
    }

    /**
    * Convert interface to HTML
    * @param void
    * @return string - Result HTML-code
    */
    function toHTML () {
        return $this->render_page;
    }
}
?>
