<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * API Controller
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_Controller extends API_Common {

    /**
     * Main
     */
    function main() {
        $action = $this->request->get('action');
        $anonymous = $this->request->get('anonymous');
        $action = str_replace('/', '', $action);
        $action = str_replace('.', '', $action);
        $this->setRequestValue('do', $this->request->get('do'));
        if ( $this->getRequestValue('layer') == 'native_ajax' ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.native_ajax.php');
            $native_ajax = new Native_Ajax();
            $native_ajax->main();
            exit;
        }
        
        //$this->writeLog('<h1>action = </h1>'.$action);
        //$this->writeLog('do = '.$this->getRequestValue('do'));

        //first we need check session key for action other then oauth
        if ($action != 'oauth' and $action != 'server' and $anonymous == '') {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.oauth.php');
            $oauth = new API_oauth();
            $result = $oauth->_check_session_key();
            if ($oauth->GetErrorMessage() == 'check_session_key_failed') {
                echo $result;
                exit;
            }
        }
        if ( $action == 'init_nobody_session' ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.oauth.php');
            $oauth = new API_oauth();
            $result = $oauth->_check_session_key();
            echo $result;
            exit;
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.' . $action . '.php')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.' . $action . '.php');
            $class_name = 'API_' . $action;
            //$this->writeLog('$class_name = '.$class_name);
            
            $run_class_action = new $class_name;
            echo $run_class_action->main();
            exit;
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/' . $action . '/api/class.' . $action . '.php')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/' . $action . '/api/class.' . $action . '.php');
            $class_name = 'API_' . $action;
            //$this->writeLog('$class_name = '.$class_name);

            $run_class_action = new $class_name;
            echo $run_class_action->main();
            exit;
        } else {
            echo 'api error';
        }
    }

}
