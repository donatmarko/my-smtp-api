<?php

/**
 * send_email.php
 *
 * Simple SMTP relay API for my projects where I can't use SMTP directly due to server/firewall limitations.
 *
 * @author     Donat Marko
 * @copyright  2020 Donatus
 * @license    GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config-inc.php';
require_once 'vendor/autoload.php';
require_once 'classes/common.php';
require_once 'classes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 'on');

$sql = new mysqli(SQL_SERVER, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE);
$db = new DB($sql);


/**
 * Unifies the variously formatted to, cc, bcc objects.
 * Possible forms:
 * 		* "test@example.com"
 * 		* ["test@example.com", "test2@example.com"]
 * 		* {"name": "test", "email": "test@example.com"}
 * 		* {"email": "test@example.com"}
 * 		* [{"email": "test@example.com"}, {"email": "test2@example.com"}]
 * 		* [{"name": "test", "email": "test@example.com"}, {"name": "test2", "email": "test2@example.com"}]
 * @param DB database instance
 * @return string JSON formatted output
 */
function get_recipient_objects($data)
{
	$list = [];
	
	if (is_array($data) && !Common::IsAssociative($data))
	{
		foreach ($data as $elem)
		{
			if (is_string($elem))
			{
				$list[] = [
					'name' => $elem,
					'email' => $elem
				];
			}
			else if (!property_exists($elem, 'name'))
			{
				$list[] = [
					'name' => $elem->email,
					'email' => $elem->email
				];
			}
			else
			{
				$list[] = [
					'name' => $elem->name,
					'email' => $elem->email
				];
			}
		}
	}
	else if (is_string($data))
	{
		$list[] = [
			'name' => $data,
			'email' => $data
		];
	}
	else if (!property_exists($data, 'name'))
	{
		$list[] = [
			'name' => $data->email,
			'email' => $data->email
		];
	}
	else
	{
		$list[] = [
			'name' => $data->name,
			'email' => $data->email
		];
	}
	
	return $list;
}


/**
 * The API itself which does the rest of the job.
 * @param DB database instance
 * @return string JSON formatted output
 */
function send_email($db)
{
    // don't do anything without the presence of HTTP_USER_AGENT
    if (!array_key_exists('HTTP_USER_AGENT', $_SERVER))
        return ['error_code' => 4, 'error_text' => 'invalid header'];
    
	// retrieve API key data from database
	$apikeydata = $db->GetAPIkeyData($_POST['apikey']);

	// API key not exist
	if (!$apikeydata)
		return ['error_code' => 1, 'error_text' => 'wrong API key'];

	// IP restricted API key
	if (!empty($apikeydata->ip) && !preg_match($apikeydata->ip, $_SERVER['REMOTE_ADDR']))
		return ['error_code' => 403, 'error_text' => 'forbidden'];
	
	// missing mail in request
	if (!isset($_POST['mail']))
		return ['error_code' => 2, 'error_text' => 'mail missing'];
	
	$mail = json_decode($_POST['mail']);
	$subject = isset($mail->subject) ? $mail->subject : '';
	$body = isset($mail->body) ? $mail->body : '';
	$isHTML = isset($mail->isHTML) ? $mail->isHTML == true : false;
	$altBody = isset($mail->altBody) ? $mail->altBody : '';
	$charset = isset($mail->charset) ? $mail->charset : 'UTF-8';
	$from = isset($mail->from) ? $mail->from : null;
	$tos = isset($mail->to) ? $mail->to : null;
	$ccs = isset($mail->cc) ? $mail->cc : null;
	$bccs = isset($mail->bcc) ? $mail->bcc : null;

	$cfg_smtp = new PHPMailer(true);	
	try
	{        
		$cfg_smtp->SMTPDebug = 0;
		$cfg_smtp->isSMTP();                    
		$cfg_smtp->Host = SMTP_SERVER;
		$cfg_smtp->SMTPAuth = SMTP_AUTH;
		$cfg_smtp->Username = SMTP_USERNAME;
		$cfg_smtp->Password = SMTP_PASSWORD;
		$cfg_smtp->SMTPSecure = SMTP_SECURE;
		$cfg_smtp->Port = SMTP_PORT;
		$cfg_smtp->CharSet = $charset;


		// missing sender
		if ($from === null)
			return ['error_code' => 4, 'error_text' => 'sender is missing'];
		$from = get_recipient_objects($from)[0];
		$cfg_smtp->setFrom($from['email'], $from['name']);
	
	
		// missing recepient
		if ($tos === null)
			return ['error_code' => 3, 'error_text' => 'recipient is missing'];
		foreach (get_recipient_objects($tos) as $to)
		{
			$cfg_smtp->addAddress($to['email'], $to['name']);
		}


		// adding Carbon Copy
		if ($ccs !== null)
		{
			foreach (get_recipient_objects($ccs) as $cc)
			{
				$cfg_smtp->addCC($cc['email'], $cc['name']);
			}
		}


		// adding Blind Carbon Copy
		if ($bccs !== null)
		{
			foreach (get_recipient_objects($bccs) as $bcc)
			{
				$cfg_smtp->addBCC($bcc['email'], $bcc['name']);
			}
		}
		
	
		$cfg_smtp->isHTML($isHTML);
		$cfg_smtp->Subject = $subject;
		$cfg_smtp->Body    = $body;            
		$cfg_smtp->AltBody = $isHTML ? $altBody : $body;
		$cfg_smtp->send();
		return ['error_code' => 0, 'error_text' => 'success'];
	}
	catch (Exception $e)
	{
		// error in SMTP process
		return ['error_code' => 99, 'error_text' => $cfg_smtp->ErrorInfo];
	}
}

header('Content-Type: application/json');
if (!empty($_POST) && isset($_POST['apikey']) && isset($_POST['mail']))
{
    $result = json_encode(send_email($db));
    $db->Log($_POST['apikey'], $_POST['mail'], $result);
    echo $result;
}

$sql->close();

?>