<?php

/**
 * smtp.php
 *
 * Simple SMTP relay API for own-developed project where I can't use SMTP directly due to server limitations.
 *
 * @author     Donat Marko
 * @copyright  2018 Donatus
 * @license    GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('config-inc.php');
require 'vendor/autoload.php';
require 'classes/common.php';
require 'classes/db.php';

$sql = new mysqli($sql_server, $sql_username, $sql_password, $sql_database);
$db = new DB($sql);

/**
 * The API itself which does the rest of the job.
 * @param DB database instance
 * @return string JSON formatted output
 */
function smtpAPI($db)
{
    // don't do anything without the presence of HTTP_USER_AGENT
    if (!array_key_exists('HTTP_USER_AGENT', $_SERVER))
        return ['error_code' => 4, 'error_text' => 'invalid header'];
    else
    {
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
        else
        {
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
        }

        $cfg_smtp = new PHPMailer(true);	
        try
        {        
            $cfg_smtp->SMTPDebug = 0;
            $cfg_smtp->isSMTP();                    
            $cfg_smtp->Host = $db->Config()->smtp_host;
            $cfg_smtp->SMTPAuth = $db->Config()->smtp_auth;
            $cfg_smtp->Username = $db->Config()->smtp_username;
            $cfg_smtp->Password = $db->Config()->smtp_password;
            $cfg_smtp->SMTPSecure = $db->Config()->smtp_secure;
            $cfg_smtp->Port = $db->Config()->smtp_port;
            $cfg_smtp->CharSet = $charset;

            // set from
            if (is_string($from))
                $cfg_smtp->setFrom($from);
            else if (!property_exists($from, 'name'))
                $cfg_smtp->setFrom($from->email);
            else
                $cfg_smtp->setFrom($from->email, $from->name);
        
            // missing recepient
            if ($tos == null)
                return ['error_code' => 3, 'error_text' => 'recepient missing'];
            else
            {
                if (is_array($tos) && !Common::IsAssociative($tos))
                {
                    foreach($tos as $to)
                    {
                        if (is_string($to))
                            $cfg_smtp->addAddress($to);
                        else if (!property_exists($to, 'name'))
                            $cfg_smtp->addAddress($to->email);
                        else
                            $cfg_smtp->addAddress($to->email, $to->name);
                    }
                }
                else if (is_string($tos))
                    $cfg_smtp->addAddress($tos);
                else if (!property_exists($tos, 'name'))
                    $cfg_smtp->addAddress($tos->email);
                else
                    $cfg_smtp->addAddress($tos->email, $tos->name);
            }

            // adding Carbon Copy
            if ($ccs != null)
            {
                if (is_array($ccs) && !Common::IsAssociative($ccs))
                {
                    foreach($ccs as $cc)
                    {
                        if (is_string($cc))
                            $cfg_smtp->addCC($cc);
                        else if (!property_exists($cc, 'name'))
                            $cfg_smtp->addCC($cc->email);
                        else
                            $cfg_smtp->addCC($cc->email, $cc->name);
                    }
                }
                else if (is_string($ccs))
                    $cfg_smtp->addCC($ccs);
                else if (!property_exists($ccs, 'name'))
                    $cfg_smtp->addCC($ccs->email);
                else
                    $cfg_smtp->addCC($ccs->email, $ccs->name);
            }

            // adding Blind Carbon Copy
            if ($bccs != null)
            {
                if (is_array($bccs) && !Common::IsAssociative($bccs))
                {
                    foreach($bccs as $bcc)
                    {
                        if (is_string($bcc))
                            $cfg_smtp->addBCC($bcc);
                        else if (!property_exists($bcc, 'name'))
                            $cfg_smtp->addBCC($bcc->email);
                        else
                            $cfg_smtp->addBCC($bcc->email, $bcc->name);
                    }
                }
                else if (is_string($bccs))
                    $cfg_smtp->addBCC($bccs);
                else if (!property_exists($bccs, 'name'))
                    $cfg_smtp->addBCC($bccs->email);
                else
                    $cfg_smtp->addBCC($bccs->email, $bccs->name);
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
            return ['error_code' => 5, 'error_text' => $cfg_smtp->ErrorInfo];
        }
    }
}

header('Content-Type: application/json');
if (!empty($_POST) && isset($_POST['apikey']) && isset($_POST['mail']))
{
    $result = json_encode(smtpAPI($db));
    $db->Log($_POST['apikey'], $_POST['mail'], $result);
    echo $result;
}

$sql->close();

?>