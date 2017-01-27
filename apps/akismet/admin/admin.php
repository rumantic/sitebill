<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Akismet admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class akismet_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    function main () {
	return 'akismet';
    }
    
    
    function akismet_check($check_text) {
	if ( $check_text == '' ) {
	    return false;
	}
	$key = $this->getConfigValue('apps.akismet.key');
	if ( $_SERVER['HTTPS'] == 'on' ) {
	    $proto = 'https://';
	} else {
	    $proto = 'http://';
	}
	$data = array('blog' => $proto.$_SERVER['HTTP_HOST'],
	    'user_ip' => $_SERVER['REMOTE_ADDR'],
	    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
	    'referrer' => '',
	    'permalink' => '',
	    'comment_type' => 'comment',
	    'comment_author' => '',
	    'comment_author_email' => '',
	    'comment_author_url' => '',
	    'comment_content' => $check_text
	);
	
	
	$request = 'blog=' . urlencode($data['blog']) .
		'&user_ip=' . urlencode($data['user_ip']) .
		'&user_agent=' . urlencode($data['user_agent']) .
		'&referrer=' . urlencode($data['referrer']) .
		'&permalink=' . urlencode($data['permalink']) .
		'&comment_type=' . urlencode($data['comment_type']) .
		'&comment_author=' . urlencode($data['comment_author']) .
		'&comment_author_email=' . urlencode($data['comment_author_email']) .
		'&comment_author_url=' . urlencode($data['comment_author_url']) .
		'&comment_content=' . urlencode($data['comment_content']);
	$host = $http_host = $key . '.rest.akismet.com';
	$path = '/1.1/comment-check';
	$port = 443;
	$akismet_ua = "Sitebill/3.3 | Akismet/3.1.7";
	$content_length = strlen($request);
	$http_request = "POST $path HTTP/1.0\r\n";
	$http_request .= "Host: $host\r\n";
	$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$http_request .= "Content-Length: {$content_length}\r\n";
	$http_request .= "User-Agent: {$akismet_ua}\r\n";
	$http_request .= "\r\n";
	$http_request .= $request;
	$response = '';
	if (false != ( $fs = @fsockopen('ssl://' . $http_host, $port, $errno, $errstr, 10) )) {

	    fwrite($fs, $http_request);

	    while (!feof($fs))
		$response .= fgets($fs, 1160); // One TCP-IP packet
	    fclose($fs);

	    $response = explode("\r\n\r\n", $response, 2);
	}
	
	//echo '<pre>';
	//print_r($response);
	//echo '</pre>';
	//exit;
	$this->clearError();
	if ('true' == $response[1]) {
	    $this->riseError('В тексте содержиться СПАМ');
	    return true;
	} elseif ('invalid' == $response[1] or $key == '') {
	    $this->riseError('Ключ apps.akismet.key указан неверно');
	    return true;
	} else {
	    return false;
	}
    }
    
}