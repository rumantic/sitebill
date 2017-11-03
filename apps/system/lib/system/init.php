<?php
/*
 * DEPRECATED CLASS
 * WILL BE REMOVED SOON
 */
/**
* @Filename: init.php
* @Author: Kondin Dmitry <dmn@newod.ru>
* @Description: Initialization class to init HTTP - variables
*/
class Init {
   /* var $init_var;
    var $db;
    var $template_dir;
    var $session_key;
    var $login;
    var $password;
    var $event_code;        //Variable to define EVENT_CODE
    var $event_message;        //Variable to define EVENT_MESSAGE
    var $GET_ARRAY = array();
    var $user_id = 0;*/

    /**
    * Constructor of a class
    */
    /*function Init () {
    }*/
    
    /**
     * Set user ID
     * @param int $user_id user ID
     * @return void
     */
    /*function setUserId ( $user_id ) {
        $this->user_id = $user_id;
    }*/

    /**
     * Get user ID
     * @param void
     * @return int $user_id user ID
     *      */
    /*function getUserId () {
        return $this->user_id;
    }*/
    
    /**
    * Initialization product page number with dividing into catalog key
    * @param int $catalog_id - catalog Id
    * @return void
    */
    /*function InitProductPageNumber ( $catalog_id ) {
        $this->product_page_number = $_POST['product_page'] != "" ? $_POST['product_page'] : $_GET['product_page'];
        $key = "catalog".$catalog_id;
        if ( $this->product_page_number == "" ) {
            $this->product_page_number = 1;
        }
    }*/

    /**
    * Initialize global array Init::GET_ARRAY
    * This array contain variables from both $_POST and $_GET
    * @param void
    * @return void
    */
	function initGlobals (){
		
	}
	
    /*function initGlobals () {
        //Init $GET_ARRAY variables from $_POST array
        foreach ( $_POST as $key => $value ) {
            if ( !isset($this->GET_ARRAY[$key]) || $this->GET_ARRAY[$key] == "" ) {
                $this->GET_ARRAY[$key] = $value;
//                print "$key = ".$this->GET_ARRAY[$key]."<br>";
            }
        }
        //Init $GET_ARRAY variables from $_GET array
        foreach ( $_GET as $key => $value ) {
            if ( $this->GET_ARRAY[$key] == "" ) {
                $this->GET_ARRAY[$key] = $value;
//                print "$key = ".$this->GET_ARRAY[$key]."<br>";
            }
        }
    }*/

    /**
    *Clean Init::GET_ARRAY array
    *Method get $define_table array with table structure and unset corresponding
    *items in Init::GET_ARRAY
    *Return true if clean success else return false
    *
    *@param array $define_table array with defined table structure
    *@return boolean
    */
    /*function CleanGetArray ( $define_table ) {
        return true;
        if ( count( $define_table ) < 1 ) {
            return false;
        }
        
        foreach ( $define_table as $key => $table_property ) {
            
            if ( $table_property['value'] != "" ) {
                unset($this->GET_ARRAY[$key]);
            }
        }
        return true;
    }*/
    /**
    *Get cookies values
    *Method get cookies values and define not defined yet values in $GET_ARRAY
    *Method return array with input environment variables
    *@param array $GET_ARRAY array with input environment variables
    *@return array
    */
    /*function GetCookies ( $GET_ARRAY ) {
        foreach ( $_SESSION as $key => $value ) {
        //Set value if not defined yet
            if ( $GET_ARRAY[$key] == "" ) {
                $GET_ARRAY[$key] = $value;
            }
        }
        return $GET_ARRAY;
    }*/

    /**
    *Method set defaults value for input array
    *Method get default value and define variables in input array if this variable not defined yet
    *@param array $GET_ARRAY array with input environment variables
    *@return array
    */
    /*function SetDefaults ( $GET_ARRAY ) {
        //Get browser language
        $browser_language=substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
        
        if ( $GET_ARRAY['language'] == "" ) {
            $GET_ARRAY['language'] = $browser_language;
        }
        //Default page number value
        if ( $GET_ARRAY['page_number'] == "" ) {
            $GET_ARRAY['page_number'] = 1;
        }
        return $GET_ARRAY;
    }*/

    /**
    * Redefine configuration file for each user
    * @param void
    * @return void
    */
    /*function userInit () {
        global $config;
        $config->module['content']['catalog_id'] = 0; // getUserCatalogId
    }*/

    /**
    *Method set cookies values
    *@param array $GET_ARRAY array with input environment variables
    *@return void
    */
    /*function SetCookies ( $GET_ARRAY ) {
        $_SESSION['language'] = $GET_ARRAY['language'];
        return;
    }*/

    /**
    *Method return data array with values from $_POST or $_GET
    *@param: array $define_table - array with define table
    *@return: array
    */
    /*function getData ( $define_table = array()) {
        if ( !is_array($this->GET_ARRAY) ) {
            $this->error = 'Input data $this->GET_ARRAY not available';
            return false;
        }
        foreach ( $define_table as $row_name => $row_array ) {
            if ( !is_null(  $this->GET_ARRAY[$row_name]) ) {
                $define_table[$row_name]['value'] = $this->GET_ARRAY[$row_name];
            }
        }
        return $define_table;
    }*/

    /**
    *Method check configuration file for necessary parameters
    *Method return stirng with message about missing parameter or empty string if all parameters are exists
    *@param void
    *@return string
    */
    /*function CheckConfig( ) {
        global $config;
        global $Message;
        //Check default language flag
        if ( $config->DefaultLanguage == "" ) {
            return $Message->MESSAGE['missing_default_language']['en'];
        }
        //Check accessible storage dir for user WWW
        if ( !is_writeable( $config->STORAGE_DIR ) ) {
            $message = sprintf( $Message->MESSAGE['storage_not_writeable'][$this->GET_ARRAY['language']],  $config->STORAGE_DIR);
            return $message;
        }
        if ( !is_int($config->CATALOG_LIMIT) and $config->CATALOG_LIMIT == 0 ) {
            $message = "Config::CATALOG_LIMIT is not defined, check configuration file";
            return $message;
        }

        if ( $config->ConfigSeparator == '' or is_null($config->ConfigSeparator) ) {
            $message = "Config::ConfigSeparator is not defined, check configuration file";
            return $message;
        }
        //If all right then return empty string
        return "";
    }*/
    /**
    * Set value of the key
    * @param string $key - key
    * @param string $value - value
    * @reuturn void
    */
   /* function setValue ( $key, $value ) {
        $this->GET_ARRAY[$key] = $value;
        switch ( $key ) {
            case 'object_id':
                $this->object_id = $value;
            break;
            case 'action':
                $this->action = $value;
            break;
        }
    }*/

    /**
    * Get value of the variable from GET_ARRAY by specified key
    * @param string - key value
    * @param string $default - default value if key not exist
    * @return string - value
    */
    /*function getValue ( $key, $default = 0 ) {
//        echo "$key = ".$this->GET_ARRAY[$key]."<br>";
        if ( is_null($this->GET_ARRAY[$key]) ) {
            return $default;
        } else{
            return $this->GET_ARRAY[$key];
        }
    }*/

    /**
    * Method get current URL and add additional string
    * @param: void
    * @return: void
    */
    /*function GetCurUri () {
    }*/
}
?>