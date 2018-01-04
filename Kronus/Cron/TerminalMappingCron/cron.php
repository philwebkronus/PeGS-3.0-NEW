<?php

/*
 * @description: Cron for Terminals with more than one casino/service assigned
 * @date Created December 14, 2017 16:03 PM <pre />
 * @author Jonathan Concepcion <jvconcepcion@philweb.com.ph> & Ronald Patrick Santos <rjsantos@philweb.com.ph>
 */

ini_set('display_errors', false);
//ini_set('log_errors', true);
ini_set('max_execution_time', 100); //300 seconds = 5 minutes
//set_time_limit(0);
date_default_timezone_set("Asia/Taipei");

$servername = '172.16.116.17';
$DatabaseType = 'mysql';
$port = '3307';
$dbname = 'npos';
$username = 'pegsconn';
$password = 'pegsconnpass';

$EmailGroup = array('javida@philweb.com.ph', 'jvconcepcion@philweb.com.ph', 'rjsantos@philweb.com.ph');
$DBemailGroups = array('javida@philweb.com.ph', 'jvconcepcion@philweb.com.ph', 'rjsantos@philweb.com.ph');

$headers = 'From: IT Development - POS Support <itdevpossupport@philweb.com.ph>' . "\r\n" .
        'X-Mailer: PHP/' . phpversion() . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1';

try {
    //create connection to database
    $handler = new PDO("$DatabaseType:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //set query
    $query = $handler->prepare('SELECT c.SiteName as SiteName, b.TerminalCode, GROUP_CONCAT(d.ServiceName) as TerminalService, COUNT(a.ServiceID) as MappingCount
								FROM terminalservices a
								INNER JOIN terminals b ON a.TerminalID = b.TerminalID
								INNER JOIN sites c ON b.SiteID = c.SiteID
								INNER JOIN ref_services d ON a.ServiceID = d.ServiceID
								WHERE a.Status=1 and b.Status=1 GROUP By b.TerminalID HAVING COUNT(a.ServiceID)>=2;');
    //Check if query executed
    if ($query->execute()) {
        $showAll = $query->fetchAll();

        $subject = 'Terminal Mapping Duplication Alert as of ' . date("Y-m-d H:i:s"); //Email Subject
        $message = "  <html>
                           <head>
                                   <title>e-Games Terminals with more than one services assigned</title>
                           </head>
                           <body>
                           <h4><p>Hi,</p></h4><h4><p>Kindly check the following information below:</p></h4>";

        //Will send an autoemail if all terminals are not properly mapped (terminals that have more than one casino service)
        if ($query->rowCount() > 0) {

            foreach ($showAll as $showAllData) {

                $Scode = $showAllData['SiteName'];
                $Terminals = $showAllData['TerminalCode'];
                $Mapped = $showAllData['TerminalService'];

                $message .="
		<b>Site Code:</b> $Scode<br>
		<b>Terminal:</b> $Terminals<br>
		<b>Mapped To:</b> $Mapped<br><br>";
            }

            $message .="<h4><p>Thank you!</p></h4>"
                    . "<i>This email and any attachments are confidential and may also be
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
                         </html>";
            try {
                $vcount = 0;
                while ($vcount < count($EmailGroup)) {
                    $to = $EmailGroup[$vcount];
                    $isSuccess = mail($to, $subject, $message, $headers);

                    //Validate if email was successfully sent.  
                    if (!$isSuccess) {
                        $errorMessage = error_get_last();
                        if (is_array($errorMessage)) {
                            $messageLogger = $errorMessage['message'];
                        } else {
                            $messageLogger = "Can\'t Send Email!";
                        }
                        $title = "[SMTP ERROR:]";
                        InsertLogs($title, $messageLogger);
                    } else {
                        $messageLogger = "Email succesfully sent!";
                        $title = "[SUCCESSFULL:]";
                        InsertLogs($title, $messageLogger);
                    }
                    $vcount = $vcount + 1;
                }
            } catch (EMAILException $e) {

                $messageLogger = trim($e->getMessage());
                $title = "[SMTP ERROR:]";
                InsertLogs($title, $messageLogger);
            }
        } else {
            $messageLogger = "All terminals are properly mapped.";
            $title = "[SUCCESSFULL:]";
            InsertLogs($title, $messageLogger);
        }
    }
} catch (PDOException $e) {

    //CATCH DB ERROR AND SEND EMAIL
    $subject = 'Terminal Mapping Duplication Alert as of ' . date("Y-m-d H:i:s"); //Email Subject

    $message = "  <html>
                           <head>
                                   <title><b>[CRITICAL] Database Connection Error!</b></title>
                           </head>
                           <body>
                           <h4><p>Hi,</p></h4><br><br>
                           <b>[CRITICAL] Database Connection Error!</b><br>
                           <br><br>Can't connect to Database! <br><br> "
            . $e->getMessage() .
            "<h4><p>Please check as soon as possible. Thank you!</p></h4> 
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
                         </html>";

    $errorMessage = $e->getMessage();
    $title = "[CRITICAL | DB ERROR:]";
    InsertLogs($title, $errorMessage);

    $vcount = 0;
    while ($vcount < count($DBemailGroups)) {
        $to = $DBemailGroups[$vcount];
        $test = mail($to, $subject, $message, $headers);

        //Validate if email was successfully sent.  
        if (!$test) {
            $errorMessage = error_get_last();
            if (is_array($errorMessage)) {
                $messageLogger = $errorMessage['message'];
            } else {
                $messageLogger = "Can\'t Send Email!";
            }
            $title = "[SMTP ERROR:]";
            InsertLogs($title, $messageLogger);
        }
        $vcount = $vcount + 1;
    }

    die();
}

//function for inserting logs 
function InsertLogs($title, $errormessage) {

    $rootPath = '/var/www/kronus.cron/TerminalMappingCron/Logs/';
    $logfile = $rootPath . 'Logs.txt';

    $today = date("Y-m-d H:i:s");

    if (!is_dir($rootPath)) {
        mkdir($rootPath);
    }

    $txt = "[" . $today . "]" . " " . $title . " " . $errormessage;
    if (file_exists($logfile)) {
        $fh = fopen($logfile, 'a');
        fwrite($fh, $txt . "\r\n");
    } else {
        $fh = fopen($logfile, 'w');
        fwrite($fh, $txt . "\r\n");
    }
    fclose($fh);
}

?>

