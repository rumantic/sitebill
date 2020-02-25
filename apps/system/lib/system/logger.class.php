<?php

class Logger {

	/**
	 * Log messages and send email
	 *
	 * @param string $message
	 * @param Exception $e
	 */
	public static function append($message = '', Exception $e=NULL ) {
		if (defined ( 'LOG_ENABLED' ) && LOG_ENABLED) {
			$mode = "ERROR";
			if($message!='' && $e!==NULL){
				$message = $message ? $message .= "\n" . $e->getMessage () : $e->getMessage ();
				
				$message .= "'\nFrom file " . $e->getFile () . ": " . $e->getLine () . "\nStack trace:\n" . $e->getTraceAsString ();
			}elseif($message!=''){
				$message = $message ? $message .= "\n" . $e->getMessage () : $e->getMessage ();
			}elseif($e!==NULL){
				$message = $e->getMessage ();
				$message .= "'\nFrom file " . $e->getFile () . ": " . $e->getLine () . "\nStack trace:\n" . $e->getTraceAsString ();
			}
			
				
			file_put_contents ( LOGGER_FILE, date ( "d.m.Y H:i:s" ) . " \t$mode \t$message\n", FILE_APPEND );
		}
	}

	public static function activity ($message) {
        $DBC = DBC::getInstance();
        $ip = getenv(HTTP_X_FORWARDED_FOR);
        if ($ip == '') {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $user_id = intval($_SESSION['user_id']);
        $query = 'INSERT INTO ' . DB_PREFIX . '_activitylog (`message`, `user_id`, `ipaddr`) VALUES (?, ?, ?)';
        $stmt = $DBC->query($query, array($message, $user_id, $ip));
        // echo $DBC->getLastError();

    }

    public static function emaillog ($to, $from, $subject, $body, $user_id = 0) {
	    if ( is_array($to) ) {
	        $to = implode(',', $to);
        }
	    self::activity("Send email: $to, ($subject)");
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_emails (`to`, `from`, `subject`, `message`, `user_id`) VALUES (?, ?, ?, ?, ?)';
        $stmt = $DBC->query($query, array($to, $from, $subject, $body, $user_id));
        // echo $DBC->getLastError();
    }

}
