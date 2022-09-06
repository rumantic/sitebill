<?php

/**
 * Mailer class
 */
class Mailer
{

    private $to;
    private $subject = "Order list";
    private $message = '';
    private $mailheaders;
    private $robot_email;
    var $parameters = "-f%1\$s";

    /**
     * Constructor
     * @param $to
     * @param $from
     */
    public function __construct()
    {
        $this->mailheaders = "MIME-Version: 1.0\r\nContent-type: text/html; charset=" . SITE_ENCODING . "\r\nFrom:%2\$s\r\n";
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $this->config_admin = new config_admin();
    }

    /**
     * Send
     * @param string $msg
     * @return void
     */
    public function send($msg)
    {
        //$this->message.=implode("<br>\n",$msg);
        $this->message = $msg;

        $headers = sprintf($this->mailheaders, $this->to, $this->robot_email);
        if (1 == $sitebill->getConfigValue('disable_mail_additionals')) {
            $result = $this->secure_mail($this->to, $from, $this->subject, $this->message, $headers);
            //mail($this->to, $this->subject, $this->message, $headers);
        } else {
            $result = $this->secure_mail($this->to, $from, $this->subject, $this->message, $headers);
            //mail($this->to, $this->subject, $this->message, $headers, sprintf($this->parameters, $robot_email));
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
    function send_simple($to, $from, $subject, $msg)
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
        $sitebill = new SiteBill();
        if ($sitebill->getConfigValue('system_email') != '') {
            $from = $sitebill->getConfigValue('system_email');
        }
        $robot_email = $from;

        $message = $msg;

        if (is_array($to)) {
            foreach ($to as $_to) {
                $sitebill->writeLog(__METHOD__ . ', to = ' . $_to);
                $headers = sprintf($this->mailheaders, $_to, $robot_email);
                if (1 == $sitebill->getConfigValue('disable_mail_additionals')) {
                    $result = $this->secure_mail($_to, $from, $subject, $message, $headers);
                    //$result = mail($_to, $subject, $message, $headers);
                } else {
                    //$result = mail($_to, $subject, $message, $headers, sprintf($this->parameters, $robot_email));
                    $result = $this->secure_mail($_to, $from, $subject, $message, $headers);
                }
            }
        } else {
            $headers = sprintf($this->mailheaders, $to, $robot_email);
            if (1 == $sitebill->getConfigValue('disable_mail_additionals')) {
                //$result = mail($to, $subject, $message, $headers);
                $result = $this->secure_mail($to, $from, $subject, $message, $headers);
            } else {
                //$result = mail($to, $subject, $message, $headers, sprintf($this->parameters, $robot_email));
                $result = $this->secure_mail($to, $from, $subject, $message, $headers);
            }
        }


        if ($result) {
            return true;
        } else {
            return false;
            //echo 'Отправка почты в данный момент невозможна, попробуйте позже';
        }
    }

    function secure_mail($to, $from, $subject, $message, $headers = '', $parameters = '')
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/mailer/class.phpmailer.php');
        $mail = new PHPMailer;
        $mail->CharSet = SITE_ENCODING;
        if ($this->config_admin->getConfigValue('system_email_robot') != '') {
            //echo $this->config_admin->getConfigValue('system_email_robot');
            $mail->setFrom($from, $this->config_admin->getConfigValue('system_email_robot'));
        } else {
            $mail->setFrom($from);
        }
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->msgHTML($message);
        //echo 'send very simple';

        //send the message, check for errors
        if (!$mail->send()) {
            //echo "Mailer Error: " . $mail->ErrorInfo;
            $this->config_admin->writeLog(__METHOD__ . ', ' . "Mailer Error: " . $mail->ErrorInfo);
            //echo 'Отправка почты в данный момент невозможна, попробуйте позже';
            return false;
        }
        return true;
    }

    /**
     * Send simple
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $msg
     * @param mixed
     */
    function send_very_simple($to, $from, $subject, $msg)
    {
        $robot_email = $from;

        //$message = $this->add_styles().$msg;

        $headers = sprintf($this->mailheaders, $to, $robot_email);
        //$headers= "MIME-Version: 1.0\r\n";
        //$headers .= "Content-type: text/html; charset=windows-1251\r\n";
        //mail('kondin@etown.ru', $subject, $msg, $headers);
        $this->secure_mail($to, $from, $subject, $msg, $headers);

        /*
        if (mail($to, $subject, $msg, $headers)) {
            //if ( mail($to, $subject, $msg) ) {
        } else {
            echo 'Отправка почты в данный момент невозможна, попробуйте позже';
        }
         */
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
    function send_smtp($to, $from, $subject, $msg, $number = 1)
    {
        if (!is_array($to) && $to == '') {
            return;
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
        $sitebill = new SiteBill();

        $from = $sitebill->getConfigValue('smtp' . $number . '_from');
        $host = $sitebill->getConfigValue('smtp' . $number . '_server');
        $username = $sitebill->getConfigValue('smtp' . $number . '_login');
        $password = $sitebill->getConfigValue('smtp' . $number . '_password');
        $port = $sitebill->getConfigValue('smtp' . $number . '_port');

        //echo "from = $from, host = $host, username = $username, password = $password, port = $port, to = $to<br>";


        date_default_timezone_set('America/Toronto');

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/mailer/class.smtp.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/mailer/class.phpmailer.php');
        //include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

        $mail = new PHPMailer();

        $body = $msg;
        //$body             = preg_replace("/[\]/",'',$body);
        //$from = 'rumantic.coder@yandex.ru';
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = $host; // SMTP server
        if ($sitebill->getConfigValue('use_smtp_ssl')) {
            $mail->SMTPSecure = "ssl";
        }

        if ( defined('SMTP_DEBUG') and SMTP_DEBUG == true ) {
            echo '<pre>';
            print_r($to);
            echo '</pre>';
            $mail->SMTPDebug = 1;             // enables SMTP debug information (for testing)
        } else {
            $mail->SMTPDebug = 0;             // enables SMTP debug information (for testing)
        }
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true;          // enable SMTP authentication
        $mail->Host = $host; // sets the SMTP server
        $mail->Port = $port;            // set the SMTP port for the GMAIL server
        $mail->Username = $username; // SMTP account username
        $mail->Password = $password;    // SMTP account password
        $mail->CharSet = SITE_ENCODING;
        $mail->SetFrom($from, empty($sitebill->getConfigValue('system_email_robot')) ? "Sitebill" : $sitebill->getConfigValue('system_email_robot'));

        //$mail->AddReplyTo("kondin@etown.ru","First Last");

        $mail->Subject = $subject;

        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

        $mail->MsgHTML($body);

        //$address = "kondin@etown.ru";
        //$address = "ctrlaltdel@mail.ru";

        if (!is_array($to)) {
            if ( defined('SMTP_DEBUG') and SMTP_DEBUG == true ) {
                echo 'send to 1 address: '.$to.'<br>';
                $sitebill->writeLog('send to 1 address: '.$to);
            }
            $mail->AddAddress($to);
        } elseif (is_array($to) && count($to) > 0) {
            if ( defined('SMTP_DEBUG') and SMTP_DEBUG == true ) {
                echo 'send to array addresses: ';
                echo '<pre>';
                print_r($to);
                echo '</pre>';
            }

            foreach ($to as $k => $_to) {
                $sitebill->writeLog('send to array '.$k.' = '.$_to);
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

        if (!$mail->Send()) {
            return false;
        } else {
            return true;
        }
    }

    function add_styles()
    {
        $rs = '';
        return $rs;
    }

}
