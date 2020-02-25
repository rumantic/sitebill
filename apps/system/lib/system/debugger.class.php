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

    static public function formatedMessagesExt($html=false) {
        if (!defined('DEBUG_ENABLED') || !DEBUG_ENABLED) {
            return '';
        }
        $total = count(self::$queries_ext);
        $message = '';
        $message_h='';
        $total_time=0;
        $message_h .= '<style>
        #pofiler {
        width: 600px;
        font-size: 12px;
        '.(!$html ? 'position: absolute;' : '').'
        background-color: white;
        padding: 10px;
        border: 1px solid silver;
        z-index: 99999;
                top: 0;
                right: 0;
        }
                #pofiler-inner {
        '.(!$html ? 'display:none;' : '').'
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
        $by_table=array();
        $message_h .= '</style>';
        $message_h .= '<script>';
        $message_h .= '$(document).ready(function(){$("#pofiler h1").click(function(){$("#pofiler-inner").toggle()});});';
        $message_h .= '</script>';
        $message_h .= '<div id="pofiler">';
        $message_h .= '<h1>Pofiler</h1>';
        $message_h .= '<div id="pofiler-inner">';
        $message .= '<h2>Queries</h2>';
        if ($total) {
            
            $qw=self::$queries_ext;
            usort($qw, array('Debugger', 'sortBtTime'));
            $qw=array_slice($qw, 0, 10);
            
            
            foreach(self::$queries_ext as $q){
                $message .= '<div class="pofiler-query">';
                $message .= '<div class="pofiler-query-query">'.htmlspecialchars($q['q']).'</div><div class="pofiler-query-time">['.$q['t'].' sec]</div>';
                $message .= '<div class="trace">'.$q['tr'].'</div>';
                $message .= '</div>';
                $total_time+=$q['t'];
                if(preg_match('/re_([a-z_]*)/i', $q['q'], $matches)){
                    $by_table[$matches[1]][]=$q;
                }else{
                    $by_table['_SYSTEM_'][]=$q;
                }

            }
        }
        if(!empty($by_table)){
            $message .= '<a name="qt"></a><h2>Queries By Table</h2>';
            $message .= '<p>';
            foreach($by_table as $k=>$v){
                $message .= '<a href="#qt_'.$k.'">'.$k.' ('.count($v).')</a> | ';
            }
            $message .= '</p>';
            foreach($by_table as $k=>$v){
                $message .= '<a name="qt_'.$k.'"></a><h3>'.$k.' ('.count($v).')</h3>';
                foreach($v as $vq){
                    //$message .= '<div class="pofiler-query-query">'.$vq.'</div>';
                    $message .= '<div class="pofiler-query">';
                    $message .= '<div class="pofiler-query-query">'.htmlspecialchars($vq['q']).'</div><div class="pofiler-query-time">['.$vq['t'].' sec]</div>';
                    $message .= '<div class="trace">'.$vq['tr'].'</div>';
                    $message .= '</div>';
                }
            }
        }
        
        $message .= '<a name="qs"></a><h2>Slowest</h2>';
            foreach($qw as $vq){
                    //$message .= '<div class="pofiler-query-query">'.$vq.'</div>';
                    $message .= '<div class="pofiler-query">';
                    $message .= '<div class="pofiler-query-query">'.htmlspecialchars($vq['q']).'</div><div class="pofiler-query-time">['.$vq['t'].' sec]</div>';
                    $message .= '<div class="trace">'.$vq['tr'].'</div>';
                    $message .= '</div>';
                }
         
        $message .= '<h2>Exeptions</h2>';
        if (count(self::$exceptions)) {
            $message .= '<p class="pofiler-exeption">';
            $message .= implode('</p><p>', self::$exceptions);
            $message .= '</p>';
        }

        $m= "<p>Total queries: $total</p>";
        $m .= "<p>Total time: $total_time</p>";
        $m .= '<p>Memory usage: ' . (memory_get_usage( true ) / 1024  / 1024) . ' MB</p>';
        $m .= '<p><a href="#q">Query</a> | <a href="#qt">Query by Table</a> | <a href="#qs">Slowest</a></p>';

        $message = $message_h.$m.$message;

        $message .= '</div>';
        $message .= '</div>';
        return $message;
    }
    
    static function sortBtTime($a, $b){
        if($a['t']>$b['t']){
            return -1;
        }elseif($a['t']<$b['t']){
            return 1;
        }else{
            return 0;
        }
        
    }
}