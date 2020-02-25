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
    function Template ($init = null ) {
		global $sitebill_document_uri;
		$this->assert('_document_uri',$sitebill_document_uri);
    }

    /**
    * Set main template file name
    * Return true if set name success and false if else
    * @param: string $template_name - template name
    * @return: boolean
    */
    /*function set_name ( $template_name ) {
        if ( $this->template_name == "" ) {
            $this->template_name = $template_name;
            return true;
        } else {
            return false;
        }
    }*/

    /**
    * Assert template set
    * @param string - set variable name
    * @param string - value
    * @return boolean
    */
    function assert ( $set, $value ) {
        global $smarty;
        $this->item[$set] = $value;
        $smarty->assign($set, $value);
        return true;
    }
    
    /*function get_val ( $set ) {
    	return $this->item[$set];
    }*/

    /**
     * Assert template set
     * @param string - set variable name
     * @param string - value
     * @return boolean
     */
    function assign ( $set, $value ) {
        global $smarty;
        //echo '<b>set = '.$set.'</b><br>';
        
        $this->item[$set] = $value;
        //if ( $set == 'grid_items' ) {
        	//echo '<pre>';
        	//print_r($this->item);
        	//echo '</pre>';
        //}
        
        $smarty->assign($set, $value);
        return true;
    }
    
    function fetch ($template) {
        global $smarty;
        return $smarty->fetch($template);
    }
    
    /**
    * Function to load template from table TEMPLATE
    * @param: $init - initialize object, $event_code - EVENT CODE for identify template name
    * @returns: $string - name of template file with path from ROOT DIR
    */
    /*function get_name ($init, $event_code) {
        $this->template_dir = $init->action;
        $this->event_code = $event_code;
        if ( $this->template_name != "" ) {
            return $this->template_name;
        } else {
            return '/system/normal.tpl';
        }
    }*/

    /**
    * Function load template
    * @param: $template - file name with full path
    * @return: $FileStringContent - string with full file content
    */
    /*function load_template($template){
        return implode("",file($template));
    }*/

    /**
    * Set template string from file
    * get content from specified file
    * @param string $template_file
    * @return void
    */
    /*function setTemplateFile ( $template_file ) {
        $this->templateString = $this->load_template($template_file);
    }*/

    /**
    * Set template string
    * @param string $templateString - string template content
    * @return void
    */
    /*function setTemplateString ( $templateString ) {
        $this->templateString = $templateString;
    }*/

    /**
    * Render string
    * get template string and output string with fill template sets
    * @param string $string - template string
    * @return string
    * @return false - if render failed
    */
    /*function renderString ( $string ) {
        if ( !is_array($this->item) ) {
            return false;
        }
        //remplace template set
        foreach ( $this->item as $itemKey => $itemValue ) {
            $string = str_replace( '{'.$itemKey.'}', $itemValue, $string );
        }
        return $string;
    }*/

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