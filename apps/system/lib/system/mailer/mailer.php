<?php
/**
 * Mailer class
 */
class Mailer{
	private $to;
	private $subject = "Order list";
	private $message='';
	private $mailheaders;
	private $robot_email;
    var $parameters = "-f%1\$s";
	
    /**
     * Constructor 
     * @param $to
     * @param $from
     */	
	public function __construct(){
	    $this->mailheaders = "MIME-Version: 1.0\r\nContent-type: text/html; charset=".SITE_ENCODING."\r\nFrom:%2\$s\r\n";
	}
    
	/**
	 * Send
	 * @param string $msg
	 * @return void
	 */
	public function send($msg){
		//$this->message.=implode("<br>\n",$msg);
		$this->message = $this->add_styles().$msg;
		
		$headers = sprintf($this->mailheaders,$this->to,$this->robot_email );
		if(1==$sitebill->getConfigValue('disable_mail_additionals')){
			mail($this->to, $this->subject, $this->message, $headers);
		}else{
			mail($this->to, $this->subject, $this->message, $headers, sprintf($this->parameters, $robot_email));
		}
    }
	
	/**
	 * Send simple
	 * @param string $to
	 * @param string $from
	 * @param string $subject
	 * @param string $msg
	 * @param mixed
	 */
	function send_simple ( $to, $from, $subject, $msg ) {
		
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
		$sitebill = new SiteBill();
		if ( $sitebill->getConfigValue('system_email') != '' ) {
			$from = $sitebill->getConfigValue('system_email');
		}
        $robot_email = $from;
        
	    $message = $this->add_styles().$msg;
	    
	    if(is_array($to)){
	    	foreach($to as $_to){
	    		$headers = sprintf($this->mailheaders, $_to, $robot_email );
	    		if(1==$sitebill->getConfigValue('disable_mail_additionals')){
	    			$result=mail($_to, $subject, $message, $headers);
	    		}else{
	    			$result=mail($_to, $subject, $message, $headers, sprintf($this->parameters, $robot_email));
	    		}
	    	}
	    }else{
	    	$headers = sprintf($this->mailheaders, $to, $robot_email );
	    	if(1==$sitebill->getConfigValue('disable_mail_additionals')){
	    		$result=mail($to, $subject, $message, $headers);
	    	}else{
	    		$result=mail($to, $subject, $message, $headers, sprintf($this->parameters, $robot_email));
	    	}
	    }
	   
	   
	   
	    if ($result) {
	    	return true;
	    } else {
	    	return false;
	    	//echo 'Отправка почты в данный момент невозможна, попробуйте позже';
	    }
	    
	}
	
	/**
	 * Send simple
	 * @param string $to
	 * @param string $from
	 * @param string $subject
	 * @param string $msg
	 * @param mixed
	 */
	function send_very_simple ( $to, $from, $subject, $msg ) {
		$robot_email = $from;
	
		//$message = $this->add_styles().$msg;
	
		$headers = sprintf($this->mailheaders, $to, $robot_email );
		//$headers= "MIME-Version: 1.0\r\n";
		//$headers .= "Content-type: text/html; charset=windows-1251\r\n";		
		//mail('kondin@etown.ru', $subject, $msg, $headers);
		if ( mail($to, $subject, $msg, $headers) ) {
		//if ( mail($to, $subject, $msg) ) {
	
		} else {
			echo 'Отправка почты в данный момент невозможна, попробуйте позже';
		}
		 
	}
	
	/**
	 * Send smtp
	 * @param string $to
	 * @param string $from
	 * @param string $subject
	 * @param string $msg
	 * @param int $number
	 * @return void|boolean
	 */
	function send_smtp ( $to, $from, $subject, $msg, $number = 1 ) {
	    if ( !is_array($to) && $to == '' ) {
	        return;
	    }
	    
        
	    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
	    $sitebill = new SiteBill();
	    
	    $from = $sitebill->getConfigValue('smtp'.$number.'_from');
	    $host = $sitebill->getConfigValue('smtp'.$number.'_server');
	    $username = $sitebill->getConfigValue('smtp'.$number.'_login');
	    $password = $sitebill->getConfigValue('smtp'.$number.'_password');
	    $port = $sitebill->getConfigValue('smtp'.$number.'_port');
	    
	    //echo "from = $from, host = $host, username = $username, password = $password, port = $port, to = $to<br>";
	    
	     
	    date_default_timezone_set('America/Toronto');

        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/class.phpmailer.php');
	    //include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
		
        $mail             = new PHPMailer();
        	
        $body             = $msg;
        //$body             = preg_replace("/[\]/",'',$body);
        
        	
        //$from = 'rumantic.coder@yandex.ru';
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host       = $host; // SMTP server
        //$mail->SMTPSecure = "ssl";
        $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->Host       = $host; // sets the SMTP server
        $mail->Port       = $port;                    // set the SMTP port for the GMAIL server
        $mail->Username   = $username; // SMTP account username
        $mail->Password   = $password;        // SMTP account password
        $mail->CharSet  = SITE_ENCODING;
        $mail->SetFrom($from);
        	
        //$mail->AddReplyTo("kondin@etown.ru","First Last");
        	
        $mail->Subject    = $subject;
        	
        $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        	
        $mail->MsgHTML($body);
        	
        //$address = "kondin@etown.ru";
        //$address = "ctrlaltdel@mail.ru";
        
        if(!is_array($to)){
        	$mail->AddAddress($to);
        }elseif(is_array($to) && count($to)>0){
        	
        	foreach ($to as $k=>$_to){
        		$mail->AddAddress($_to);
        	}
        }
        
       // $mail->AddAddress($to);
        
        /*
         $address = "kondin@etown.ru";
        $mail->AddAddress($address, "John Doe");
        
        $address = "egocenter@yandex.ru";
        $mail->AddAddress($address, "John Doe");
        */
        
        //$mail->AddAttachment("images/phpmailer.gif");      // attachment
        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
        	
        if(!$mail->Send()) {
        	return false;
        } else {
        	return true;
        }
		
		
	    
	     
	}
	
	function add_styles () {
	    $rs = '
body,td {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
}

a, p, h1, h2, h3, h4, h5, h6, h7, br {
	margin: 0px;
	padding: 0px;
	font-family:Arial, Helvetica, sans-serif;
}

body {
  background-color: #ffffff;
}


.browse_summ {
	margin-left: 3ex;
	padding: 0px;
}

h1.top
{
	text-align:	left;
	color: #7cc812;
	font-size: 12pt;
	
}

.lehd
{
	text-align:	left;
	background-color:  #7cc812;
	font-weight:bold;
	color: #ffffff;
}


.cat_title
{
	color:#660000;
	text-decoration:underline;
	font-weight:bold;
}

.menu li, .bottom_menu li
{
	font-size: 10pt;
    color:#660000;
    text-decoration:underline;
    display:inline;
    padding-left: 10px;
    background-color:  #eeeeee;
}


.address
{
	padding: 3px 0px 5px 0px;
	margin: 0px;
	font-size: 10pt;
	color:#000000;
	padding-left: 10px;
}

.menu, .bottom_menu
{
	width: 100%;
	padding: 3px 0px 5px 0px;
	margin: 0px;
	background-color:  #eeeeee;
}

.list {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	width: 100%;
}

.r1 {
}

.r2 {
	background-color:  #eeffe0;
}





.date
{
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
        color: #575555;
}

h1
{
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 14px;
	font-weight: bold;
    color: #575555;
}
h2
{
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
	font-weight: bold;
    color: #575555;
}

.row1 {
    background-color:#F3F3F3;
}

.row2 {
    background-color:#F9F9F9;
}

.hot {
    background-color: #87FF95;
}

.row_title {
    background-color:#F3F3F3;
    font-weight: bold;
}

.error {
    color:#FF0606;
}
.bookmark {
    border: 1px none #999999;
    color: #996600;
}
.bookmarkmenu {
    color: #CCFF00;
}
table.bookmarks
{
    font-size: 10px;
    color: #B0B0B0;
    font-family: Verdana;
    margin: 0px;
    padding: 0px;
}

table.bookmarks a
{
    color: #999900;
    text-decoration:none;
}

.gridTable {
    margin: 0px;
    border: 0px solid gray;
    width: 100%;
}

.simpleTable {
    margin: 0px;
    border: 0px solid gray;
    padding: 0px;
}
table.bookmarks td
{
    vertical-align:middle;
    text-align:center;
    height: 22px;
    font-weight: bold;
    background-color: #F3F3F3;
    margin: 0px;
    border: 1px solid gray;
    border-bottom: 1px solid silver;
    white-space:nowrap;
    padding-left: 10px;
    padding-right:10px;
}

table.bookmarks td.top
{
    background-color: #FFFFFF;
    padding:0px;
    border: none;
    height: 2px;
}

table.bookmarks td.selected
{
    color: #505050;
    border-left: 1px solid silver;
    border-top: 1px solid silver;
    border-right: 1px solid silver;
    border-bottom: none;
    background-color: #FFFFFF;
}

table.bookmarks td.rightfiller
{
    padding: 0px;
    border: none;
    border-bottom: 1px solid silver;
    background-color: #FFFFFF;
    width:100%;
}

table.bookmarks td.leftfiller
{
    padding: 0px;
    border: none;
    border-bottom: 1px solid silver;
    background-color: #FFFFFF;
}

table.main
{
    padding: 0px;
    border: none;
    border: 1px solid silver;
    background-color: #FFFFFF;
}

#structure {

}

#structure table
{
    border-collapse: collapse;
    border: 1px solid #CCCCCC;
}

#structure table td, #structure table th
{
    font-family: Verdana;
    font-size:  12px;
    border: 1px solid #CCCCCC;
}

#calendar
{

    background: #FFFFFF;
    border: 1px solid #000000;
    text-align: justify;
    font: 11px Verdana, Helvetica, sans-serif;
    margin:0px;
    padding: 0px;
    position:absolute;
    visibility: hidden;
    left: 300px;
    top: 300px;
    z-index: 999;
}

#calendar table
{
    border-collapse: collapse;
    border: 1px solid #CCCCCC;
}

#calendar table td, #calendar table th
{
    font-family: Verdana;
    font-size:  11px;
    border: 1px solid #CCCCCC;
}


#calendar select
{
    font: 11px, Verdana;
}

#calendar #c_today
{
    border: 2px solid #FF5555;
}

#calendar #c_selected
{
    background-color:#FFFFCC;
}

#calendar #c_today_selected
{
    background-color:#FFFFCC;
    border: 2px solid #FF5555;
}
.green_bg {
    background-color: #D3F89A;
}
.red_bg {
    background-color: #F89A9A;
}
#area {
    border: 1px solid gray;
    padding: 2px;
    background-color: #FFFFCC;
    float: left;
    margin: 2px;
    width: 100%;
}
#info {
    padding: 2px;
    float: left;
}
#error_message {
    border: 1px solid gray;
    background-color: #FFCCCC;
    padding: 2px;
}
#es {
    float: left;
    height: 16px;
}
#es_full {
    float: left;
}

#es100 {
    float: left;
    width: 100%;
    background-color: #F3F3F3;
    height: 16px;
    margin: 1px;
}

#home_div {
}
#home_div .tabs {
	padding: 0px;
	overflow: hidden;
}

#home_div .tabs li {
	float: left;
	list-style: none;
	padding: 10px;
}

#home_div .tabs li.sale_div {
	background-color: #edf4fa;
}
#home_div .sale_div {
	background-color: #edf4fa;
}
#home_div .tabs li.sale_div.current {
	font-weight: bold;
}

#home_div .tabs li.rent_div {
	background-color: #fefee2;
}
#home_div .rent_div {
	background-color: #fefee2;
}
#home_div .tabs li.rent_div.current {
	font-weight: bold;
	background-color: #fefee2;
}
	    
	    ';
	    
	    return '<style>'.$rs.'</style>';
	}
}