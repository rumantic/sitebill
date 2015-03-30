<?php

class Debugger {

	private static $exceptions = array ();

	private static $queries = array ();
	
	private static $queries_ext = array ();

	static public function appendQuery($sql) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
			array_push ( self::$queries, $sql );
		}
	}
	
	static public function appendQueryExt($sql, $time, $trace='') {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
			self::$queries_ext[]=array(
				'q'=>$sql,
				't'=>$time,
				'tr'=>$trace
			);
		}
	}

	static public function appendException($e) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
			array_push ( self::$exceptions, $e->getMessage () );
		}
	}

	static public function formatedMessages() {
		$total = count(self::$queries);
		$message = '';

		if ($total) {
			$message .= '<p>';
			$message .= implode('</p><p>', self::$queries);
			$message .= '</p>';
		}

		if (count(self::$exceptions)) {
			$message .= '<p>';
			$message .= implode('</p><p>', self::$exceptions);
			$message .= '</p>';
		}

		$message .= "<p>Total queries: $total</p>";
		$message .= '<p>Memory usage: ' . (memory_get_usage( true ) / 1024  / 1024) . ' MB</p>';
		return $message;
	}
	
	static public function formatedMessagesExt() {
		$total = count(self::$queries_ext);
		$message = '';
		$total_time=0;
		$message .= '<style>
		#pofiler {
		width: 600px;
		font-size: 12px;
		position: absolute;
		background-color: white;
		padding: 10px;
		border: 1px solid silver;
		z-index: 99999;
				top: 0;
				right: 0;
		}
				#pofiler-inner {
		display:none;
		}
				
				.pofiler-query {
margin: 10px 0;
border: 1px solid #ddd;
border-left: 0;
border-right: 0;
}
				.pofiler-query-query {
font-weight: bold;
}
				.pofiler-query-time {
color: rgb(94, 94, 219);
}
				.trace {
font-size: 11px;
}
				';
		$message .= '</style>';
		$message .= '<script>';
		$message .= '$(document).ready(function(){$("#pofiler h1").click(function(){$("#pofiler-inner").toggle()});});';
		$message .= '</script>';
		$message .= '<div id="pofiler">';
		$message .= '<h1>Pofiler</h1>';
		$message .= '<div id="pofiler-inner">';
		$message .= '<h2>Queries</h2>';
		if ($total) {
			foreach(self::$queries_ext as $q){
				$message .= '<div class="pofiler-query">';
				$message .= '<div class="pofiler-query-query">'.$q['q'].'</div><div class="pofiler-query-time">['.$q['t'].' sec]</div>';
				$message .= '<div class="trace">'.$q['tr'].'</div>';
				$message .= '</div>';
				$total_time+=$q['t'];
			}
		}
		$message .= '<h2>Exeptions</h2>';
		if (count(self::$exceptions)) {
			$message .= '<p class="pofiler-exeption">';
			$message .= implode('</p><p>', self::$exceptions);
			$message .= '</p>';
		}
	
		$message .= "<p>Total queries: $total</p>";
		$message .= "<p>Total time: $total_time</p>";
		$message .= '<p>Memory usage: ' . (memory_get_usage( true ) / 1024  / 1024) . ' MB</p>';
		$message .= '</div>';
		$message .= '</div>';
		return $message;
	}
}