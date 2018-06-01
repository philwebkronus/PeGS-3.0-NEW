<?php

ini_set('display_errors', false);
ini_set('log_errors', false);
date_default_timezone_set("Asia/Taipei");

include 'PDOhandler.php';
include 'config/config.php';
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

//Email Group
$EmailGroup = array('incidentresponse@philweb.com.ph, servicemgt@philweb.com.ph, jsvalladores@philweb.com.ph, racalope@philweb.com.ph');
//$EmailGroup = array('racalope@philweb.com.ph');

// Logs : Starting Cron
$title = "[CRON START :]";
$message = "---------------------------------------------------------";
$PDO->InsertLogs($title, $message);



// Mail Starting Cron
$MailSubject = '[START] Habanero Player Points Update Cron';
$MailMessage = 'This is to inform you that habanero points cron will now start.';

sendMail($MailSubject, $MailMessage, $PDO, $EmailGroup);


// Mail On-going Cron
//$MailSubject = 'On-going : Habanero Player Points Update Cron';
//$MailMessage = 'This is to inform you that habanero points cron is now on-going.';

//sendMail($MailSubject, $MailMessage, $PDO, $EmailGroup);


// Update Query
$UpdatePlayerPointsData = $PDO->UpdatePlayerPointsData($divisibleBy);
if ($UpdatePlayerPointsData) {

    // Logs Update Success
    $title = "[UPDATE PLAYER POINTS:]";
    $message = "Success to update player points";
    $PDO->InsertLogs($title, $message);

    // Mail Update Success 
    $MailSubject = '[END : OK] Habanero Player Points Update Cron';
    $MailMessage = 'This is to inform you that habanero points cron has success.';

    sendMail($MailSubject, $MailMessage, $PDO, $EmailGroup);
} else {
    // Logs Update Fail
    $title = "[UPDATE PLAYER POINTS:]";
    $message = "Failed to update player points";
    $PDO->InsertLogs($title, $message);


    // Mail Update Fail
    $MailSubject = '[END : FAILED] Habanero Player Points Update Cron';
    $MailMessage = 'This is to inform you that habanero points cron has failed.';

    sendMail($MailSubject, $MailMessage, $PDO, $EmailGroup);
}

// Logs : Starting Cron
$title = "[CRON END :]";
$message = "---------------------------------------------------------";
$PDO->InsertLogs($title, $message);

function sendMail($subject, $MailMessage, $PDO, $EmailGroup) {

    $headers = 'From: IT Development - POS Support <itdevpossupport@philweb.com.ph>' . "\r\n" .
            'X-Mailer: PHP/' . phpversion() . "\r\n" .
            'Content-type: text/html; charset=iso-8859-1';

    $message = '        <html>
                           <head>
                                   <title>Habanero Player Points Update Cron</title>
                           </head>
                           <body>
                           <h4><p>Hi,</p>
                           <br>
                           <span>' . $MailMessage . '</span>
                           <br><br>
                           <h4><p>Thank you!</p></h4>
                           <i>This email and any attachments are confidential and may also be
                                privileged.  If you are not the addressee, do not disclose, copy,
                                circulate or in any other way use or rely on the information contained
                                in this email or any attachments.  If received in error, notify the
                                sender immediately and delete this email and any attachments from your
                                system.  Any opinions expressed in this message do not necessarily
                                represent the official positions of PhilWeb Corporation. Emails cannot
                                be guaranteed to be secure or error free as the message and any
                                attachments could be intercepted, corrupted, lost, delayed, incomplete
                                or amended.  PhilWeb Corporation and its subsidiaries do not accept
                                liability for damage caused by this email or any attachments and may
                                monitor email traffic.
                                </i>
                            </body>
                         </html>';

    $vcount = 0;
    while ($vcount < count($EmailGroup)) {
        $to = $EmailGroup[$vcount];
        $isSuccess = mail($to, $subject, $message, $headers);

        //Validate if email was successfully sent.  
        if (!$isSuccess) {
            $errorMessage = error_get_last();
            if (is_array($errorMessage)) {
                $message = $errorMessage['message'];
            } else {
                $message = "Can\'t Send Email!";
            }
            $title = "[SMTP ERROR:]";
            $PDO->InsertLogs($title, $message);
        } else {
            $message = "Email succesfully sent!";
            $title = "[SMTP SUCCESSFUL:]";
            $PDO->InsertLogs($title, $message);
        }
        $vcount = $vcount + 1;
    }
}
?>

