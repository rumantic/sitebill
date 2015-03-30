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

}