<?php

ini_set('display_errors', true);
ini_set('log_errors', true);

include '../PDOhandler.php';
include 'HabaneroPlayerAPI.php';
include '../config/config.php';

session_start();
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

$HabaneroAPIWrapper = new HabaneroPlayerAPI($urlHabanero, $BrandID, $APIkey);



if (isset($_GET['Count'])) {
    $serviceID = 29;
    //$serviceID = 22;
    $Count = (int) trim($_GET['Count']);

    if ($Count <= $max_terminal_process_creation) {

        $GetAccountsDetails = $PDO->getHabaneroAccountsForCreation($Count);

        if (count($GetAccountsDetails) > 0) {
            $counterTN = 0;
            $SuccessCounterTN = 0;
            $ErrorCounterTN = 0;
            $AlreadyCounterTN = 0;


            foreach ($GetAccountsDetails as $GetAccounts) {

                $PlayerIP = getenv("REMOTE_ADDR");
                $UserAgent = $_SERVER['HTTP_USER_AGENT'];
                $MID = $GetAccounts['MID'];
                $Username = $GetAccounts['ServiceUsername'];
                $Password = $GetAccounts['ServicePassword'];
                $PlayerRank = $GetAccounts['isVIP'];


                if ($counterTN == 0) {
                    $startTime = microtime(true);
                    $title = "[StartTime]";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . $startTime;
                    $PDO->InsertLogsHabaneroUserbased($title, $errormessage);
                }

                if ($counterTN != 0) {
                    $checkEvery10 = $counterTN % $timoutPer;
                    if ($checkEvery10 == 0) {
                        $title = "[Pause]";
                        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . microtime(true);
                        $PDO->InsertLogsHabaneroUserbased($title, $errormessage);
                        //sleep for 5 seconds
                        sleep(5);
                    }
                }

                //Create Player to Habanero
                $CreatePlayerHabanero = $HabaneroAPIWrapper->CreatePlayer($PlayerIP, $UserAgent, $Username, $Password, $PlayerRank);

                if ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == true) {
                    $title = "[Success | CreatePlayerHabanero:]";
                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Successful Created Account to Habanero.";
                    $PDO->InsertLogsHabaneroUserbased($title, $message);

                    //Check if player created
                    $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                    if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {

                        $title = "[Success | QueryPlayer:]";
                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Successful Query Player.";

                        $PDO->InsertLogsHabaneroUserbased($title, $message);

                        $updateMemberServices = $PDO->updateOptionID($MID);
                        if ($updateMemberServices) {
                            $title = "[Success | UpdateMemberServices:]";
                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Successful Update Memberservices";
                            $PDO->InsertLogsHabaneroUserbased($title, $message);
                            $SuccessCounterTN = $SuccessCounterTN + 1;
                        } else {
                            $title = "[Error | UpdateMemberServices:]";
                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Error Update Memberservices.";
                            $PDO->InsertLogsHabaneroUserbased($title, $message);
                            $ErrorCounterTN = $ErrorCounterTN + 1;
                        }
                    } else {
                        $title = "[Error | QueryPlayer:]";
                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Player account not found.";
                        $PDO->InsertLogsHabaneroUserbased($title, $message);
                        $ErrorCounterTN = $ErrorCounterTN + 1;
                    }
                } elseif ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == false) {
                    $title = "[Error | CreatePlayerHabanero:]";
                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Already Created on Habanero backend.";
                    $PDO->InsertLogsHabaneroUserbased($title, $message);

                    $updateMemberServices = $PDO->updateOptionID($MID);
                    if ($updateMemberServices) {
                        $title = "[Success | UpdateMemberServices:]";
                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Successful Update Memberservices";
                        $PDO->InsertLogsHabaneroUserbased($title, $message);
                    } else {
                        $title = "[Error | UpdateMemberServices:]";
                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | Error Update Memberservices.";
                        $PDO->InsertLogsHabaneroUserbased($title, $message);
                    }

                    $AlreadyCounterTN = $AlreadyCounterTN + 1;
                } else {
                    $title = "[Error | CreatePlayerHabanero:]";
                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | Login : " . $Username . " | MID : " . $MID . " | ServiceID : " . $serviceID . " | " . $CreatePlayerHabanero[' createplayermethodResult'][' Message'];
                    $PDO->InsertLogsHabaneroUserbased($title, $message);
                    $ErrorCounterTN = $ErrorCounterTN + 1;
                }


                $counterTN = $counterTN + 1;
            }

            $elapsed = (microtime(true) - $startTime);
            $endTime = microtime(true);


            echo
            " <br>Result: <br><br>Total # of Records Created : " . $SuccessCounterTN .
            " <br>Total # of Records Error : " . $ErrorCounterTN .
            " <br>Total # of Records Already Exists : " . $AlreadyCounterTN . "<br>" .
            " <br> Start Time : " . $startTime . " seconds" .
            " <br>End Time : " . $endTime . " seconds" .
            " <br><br> Elapsed time is: " . $elapsed . " seconds";

            $title = "[RecordsCreated]";
            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . $SuccessCounterTN;
            $PDO->InsertLogsHabaneroUserbased($title, $errormessage);

            $title = "[RecordsError]";
            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . $ErrorCounterTN;
            $PDO->InsertLogsHabaneroUserbased($title, $errormessage);

            $title = "[RecordsExists]";
            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . $AlreadyCounterTN;
            $PDO->InsertLogsHabaneroUserbased($title, $errormessage);

            $title = "[EndTime]";
            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . $endTime;
            $PDO->InsertLogsHabaneroUserbased($title, $errormessage);

            $title = "[ElapsedTime]";
            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | " . $elapsed;
            $PDO->InsertLogsHabaneroUserbased($title, $errormessage);
        } else {
            echo
            " <br>Result: <br><br>No Records found! ";
        }
    }
}
