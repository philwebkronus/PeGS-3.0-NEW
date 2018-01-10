<?php

ini_set('display_errors', true);
ini_set('log_errors', true);

include '../PDOhandler.php';
include 'RealtimeGamingPlayerAPI.php';
include 'RealtimeGamingWCFPlayerAPI.php';
include 'HabaneroPlayerAPI.php';
include '../config/config.php';

session_start();
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);
$RTG = new RealtimeGamingPlayerAPI($urlRTG, $certFilePath, $keyFilePath, $caching);
$rtgWcfPlayerApi = new RealtimeGamingWCFPlayerAPI($urlRTG, $combiFilePath, '');
$Habanero = new HabaneroPlayerAPI($urlHabanero, $BrandID, $APIkey);

if (isset($_GET['TerminalIDs'])) {
    $serviceID = $_GET['CasinoID'];
    //$serviceID = 22;
    $LongCode = trim($_GET['TerminalCodes']);
    $terminalName = explode(',', $LongCode);
    $SiteCode = trim($_GET['SiteCode']);
    $SiteID = trim($_GET['SiteID']);
    $CasinoCode = $_GET['CasinoCode'];


    if (in_array($_GET['CasinoID'], $avilable_casino)) {
        if (count($terminalName) <= $max_terminal_process) {


            $counterTN = 0;
            foreach ($_GET['TerminalIDs'] as $terminalID) {
                if ($counterTN == 0) {
                    $startTime = microtime(true);
                    $title = "StartTime";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $startTime;
                    $PDO->InsertLogs($title, $errormessage);
                }

                if (strstr($terminalName[$counterTN], "VIP") == true) {
                    $isVIP = 1;
                } else {
                    $isVIP = 0;
                }
                //Get Service Group ID
                $getServiceGroupID = $PDO->getServiceGroupID($serviceID);
                if ($getServiceGroupID != false) {
                    $ServiceGroupID = $getServiceGroupID[0];
                } else {
                    $title = "GetGeneratedPasswordBatchID";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't Get ServiceGroupID.";
                    $PDO->InsertLogs($title, $errormessage);

                    if (($counterTN + 1) == count($terminalName)) {
                        $elapsed = (microtime(true) - $startTime);
                        $endTime = microtime(true);

                        echo
                        " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                        " <br>End Time : " . $endTime . " seconds" .
                        " <br><br> Elapsed time is: " . $elapsed . " seconds";

                        $title = "EndTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                        $PDO->InsertLogs($title, $errormessage);

                        $title = "ElapsedTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                        $PDO->InsertLogs($title, $errormessage);
                    }

                    $counterTN = $counterTN + 1;
                    continue;
                }
                //Get Generated Password Batch ID
                $getGeneratedPasswordBatchID = $PDO->getGeneratedPasswordBatchID();
                if ($getGeneratedPasswordBatchID != false) {
                    $GeneratedPasswordBatchID = $getGeneratedPasswordBatchID[0];
                } else {
                    $title = "GetGeneratedPasswordBatchID";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't Get GeneratedPasswordBatchID.";
                    $PDO->InsertLogs($title, $errormessage);

                    if (($counterTN + 1) == count($terminalName)) {
                        $elapsed = (microtime(true) - $startTime);
                        $endTime = microtime(true);

                        echo
                        " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                        " <br>End Time : " . $endTime . " seconds" .
                        " <br><br> Elapsed time is: " . $elapsed . " seconds";

                        $title = "EndTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                        $PDO->InsertLogs($title, $errormessage);

                        $title = "ElapsedTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                        $PDO->InsertLogs($title, $errormessage);
                    }

                    $counterTN = $counterTN + 1;
                    continue;
                }
                //Get Service Password
                $getServicePassword = $PDO->getServicePassword($terminalID, $serviceID);
                if ($getServicePassword != false) {
                    $OldPassword = $getServicePassword[0];
                } else {
                    $title = "GetServicePassword";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Terminal does not have " . $CasinoCode . " account created.";
                    $PDO->InsertLogs($title, $errormessage);


                    if (($counterTN + 1) == count($terminalName)) {
                        $elapsed = (microtime(true) - $startTime);
                        $endTime = microtime(true);

                        echo
                        " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                        " <br>End Time : " . $endTime . " seconds" .
                        " <br><br> Elapsed time is: " . $elapsed . " seconds";

                        $title = "EndTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                        $PDO->InsertLogs($title, $errormessage);

                        $title = "ElapsedTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                        $PDO->InsertLogs($title, $errormessage);
                    }
                    $counterTN = $counterTN + 1;
                    continue;
                }
                //Get Generated Password Pool
                $getGeneratedPasswordPool = $PDO->getGeneratedPasswordPool($ServiceGroupID);
                if ($getGeneratedPasswordPool != false) {
                    $NewPlainPassword = $getGeneratedPasswordPool['PlainPassword'];
                    $NewHashedPassword = $getGeneratedPasswordPool['EncryptedPassword'];
                } else {
                    $title = "GetGeneratedPasswordPool";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " |TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't get New Password.";
                    $PDO->InsertLogs($title, $errormessage);


                    if (($counterTN + 1) == count($terminalName)) {
                        $elapsed = (microtime(true) - $startTime);
                        $endTime = microtime(true);

                        echo
                        " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                        " <br>End Time : " . $endTime . " seconds" .
                        " <br><br> Elapsed time is: " . $elapsed . " seconds";

                        $title = "EndTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                        $PDO->InsertLogs($title, $errormessage);

                        $title = "ElapsedTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                        $PDO->InsertLogs($title, $errormessage);
                    }

                    $counterTN = $counterTN + 1;
                    continue;
                }

                $login = 'ICSA-' . trim($terminalName[$counterTN]);

                if ($serviceID == 22) {
                    //CALL ChangePlayer API to RTG
                    //$changePlayerPassword = $RTG->changePlayerPassword($serviceID, $login, $OldPassword, $NewPlainPassword);
                    $changePlayerPassword = $rtgWcfPlayerApi->changePlayerPassword($login, $OldPassword, $NewPlainPassword);
                }

                if ($serviceID == 25) {
                    //CALL UpdatePlayerPassword API to Habanero
                    $changePlayerPassword = $Habanero->UpdatePlayerPassword($login, $NewPlainPassword);
                    if ($changePlayerPassword['updatepasswordmethodResult']['Success'] == true) {
                        $changePlayerPassword['ChangePasswordResult']['ErrorCode'] = 0;
                    } else {
                        $changePlayerPassword['ChangePasswordResult']['ErrorCode'] = 1;
                    }
                }

                if (isset($changePlayerPassword['ChangePasswordResult']['ErrorCode']) && $changePlayerPassword['ChangePasswordResult']['ErrorCode'] == 0) {

                    //If succesfull Update terminalservices
                    $UpdateTerminalServices = $PDO->UpdateTerminalServices($login, $serviceID, $NewPlainPassword, $NewHashedPassword);

                    if ($UpdateTerminalServices == 1) {
                        $title = "UpdateTerminalServices";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Update Terminal Services.";
                        $PDO->InsertLogs($title, $errormessage);

                        if (($counterTN + 1) == count($terminalName)) {
                            $elapsed = (microtime(true) - $startTime);
                            $endTime = microtime(true);

                            echo
                            " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                            " <br>End Time : " . $endTime . " seconds" .
                            " <br><br> Elapsed time is: " . $elapsed . " seconds";

                            $title = "EndTime";
                            $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                            $PDO->InsertLogs($title, $errormessage);

                            $title = "ElapsedTime";
                            $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                            $PDO->InsertLogs($title, $errormessage);
                        }

                        $counterTN = $counterTN + 1;
                        continue;
                    } else {
                        $title = "UpdateTerminalServices";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Fail to Update Terminal Services.";
                        $PDO->InsertLogs($title, $errormessage);

                        if (($counterTN + 1) == count($terminalName)) {
                            $elapsed = (microtime(true) - $startTime);
                            $endTime = microtime(true);

                            echo
                            " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                            " <br>End Time : " . $endTime . " seconds" .
                            " <br><br> Elapsed time is: " . $elapsed . " seconds";

                            $title = "EndTime";
                            $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                            $PDO->InsertLogs($title, $errormessage);

                            $title = "ElapsedTime";
                            $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                            $PDO->InsertLogs($title, $errormessage);
                        }

                        $counterTN = $counterTN + 1;
                        continue;
                    }
                } else {
                    $title = "ChangePlayerPassword";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $terminalID . " | TerminalCode : " . $terminalName[$counterTN] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Fail to Change Password | " . $changePlayerPassword['ChangePasswordResult']['Message'];
                    $PDO->InsertLogs($title, $errormessage);

                    if (($counterTN + 1) == count($terminalName)) {
                        $elapsed = (microtime(true) - $startTime);
                        $endTime = microtime(true);

                        echo
                        " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
                        " <br>End Time : " . $endTime . " seconds" .
                        " <br><br> Elapsed time is: " . $elapsed . " seconds";

                        $title = "EndTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
                        $PDO->InsertLogs($title, $errormessage);

                        $title = "ElapsedTime";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
                        $PDO->InsertLogs($title, $errormessage);
                    }

                    $counterTN = $counterTN + 1;
                    continue;
                }

                $counterTN = $counterTN + 1;
            }
        } else {
            echo "<script>alert('Exceeds Maximum Terminals that can process. Please limit it to " . $max_terminal_process . " pairs of Regular/VIP terminals only!');</script>";
            $title = "ProcessChangeTerminalPassword";
            $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Exceeds Maximum Terminals that can process. Please limit it to " . $max_terminal_process . " pairs of Regular/VIP terminals only!";
            $PDO->InsertLogs($title, $errormessage);
        }
    } else {
        echo "<script>alert('Casino is not yet available in the web tool!');</script>";
        $title = "ProcessChangeTerminalPassword";
        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | Casino is not yet available in the web tool.";
        $PDO->InsertLogs($title, $errormessage);
    }
}



