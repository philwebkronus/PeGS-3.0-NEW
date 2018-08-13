<?php

ini_set('display_errors', true);
ini_set('log_errors', true);

include '../PDOhandler.php';
include 'HabaneroPlayerAPI.php';
include '../config/config.php';

session_start();
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

$HabaneroAPIWrapper = new HabaneroPlayerAPI($urlHabanero, $BrandID, $APIkey);



if (isset($_GET['TerminalIDs'])) {
    $serviceID = 25;
    //$serviceID = 22;
    $LongCode = trim($_GET['TerminalCodes']);
    $terminalName = explode(',', $LongCode);
    $SiteCode = trim($_GET['SiteCode']);
    $SiteID = trim($_GET['SiteID']);

    if (count($terminalName) <= $max_terminal_process_creation) {

        $counterTN = 0;

        foreach ($_GET['TerminalIDs'] as $terminalID) {

            if ($counterTN == 0) {
                $startTime = microtime(true);
                $title = "[StartTime]";
                $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $startTime;
                $PDO->InsertLogsHabanero($title, $errormessage);
            }

            if ($counterTN != 0) {
                $checkEvery10 = $counterTN % 10;
                if ($checkEvery10 == 0) {
                    $title = "[Pause]";
                    $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . microtime(true);
                    $PDO->InsertLogsHabanero($title, $errormessage);
                    //sleep for 5 seconds
                    sleep(5);
                }
            }

            if (strstr($terminalName[$counterTN], "VIP") == true) {
                $isVIP = 1;
            } else {
                $isVIP = 0;
            }

            $login = 'ICSA-' . trim($terminalName[$counterTN]);

            if (!empty($terminalName[$counterTN])) {

                $TerminalID = $terminalID;
                $TerminalCode = $login;
                $siteID = $SiteID;

                try {
                    $checkTerminalServicesIfHasHabanero = $PDO->checkTerminalServicesIfHasHabanero($TerminalID); //Check if Terminal has 25 serviceID
                    //if False
                    if (!$checkTerminalServicesIfHasHabanero) {
                        try {
                            //Get the GeneratedPasswordBatchID of the Site
                            $getGeneratedPasswordBatchID = $PDO->getHabaneroGeneratedPasswordBatchID($siteID);

                            if (!$getGeneratedPasswordBatchID) {
                                try {
                                    $getNewGeneratedPasswordBatchID = $PDO->getNewGeneratedPasswordBatchID();
                                    if ($getNewGeneratedPasswordBatchID) {
                                        $NewGeneratedPasswordBatchID = $getNewGeneratedPasswordBatchID['GeneratedPasswordBatchID'];
                                        $GeneratedPasswordBatchID = $NewGeneratedPasswordBatchID;
                                        try {
                                            $UpdateNewGeneratedPasswordBatchID = $PDO->UpdateNewGeneratedPasswordBatchID($siteID, $NewGeneratedPasswordBatchID);
                                            if ($UpdateNewGeneratedPasswordBatchID == 1) {
                                                $title = "[Success | UpdateNewGeneratedPasswordBatchID:]";
                                                $message = $siteID . " | Successful Update New Generated Password Batch ID!";
                                                $PDO->InsertLogsHabanero($title, $message);
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Terminal Already have account to Habanero!.";
                                            }
                                        } catch (Exception $ex) {
                                            $title = "[Error | UpdateNewGeneratedPasswordBatchID:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                            $PDO->InsertLogsHabanero($title, $message);
                                        }
                                    } else {
                                        $title = "[Error | GetNewGeneratedPasswordBatchID:]";
                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't get New Generated Password Batch ID.";
                                        $PDO->InsertLogsHabanero($title, $message);
                                    }
                                } catch (Exception $ex) {
                                    $title = "[Error | GetNewGeneratedPasswordBatchID:]";
                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                    $PDO->InsertLogsHabanero($title, $message);
                                }
                            } else {
                                $GeneratedPasswordBatchID = $getGeneratedPasswordBatchID['GeneratedPasswordBatchID'];
                            }


                            if (!empty($GeneratedPasswordBatchID)) {

                                try {
                                    //Get Password in GeneratedPasswordPool
                                    $getGeneratedPasswordPool = $PDO->getHabaneroGeneratedPasswordPool($GeneratedPasswordBatchID);

                                    if ($getGeneratedPasswordPool) {

                                        $PlainPassword = $getGeneratedPasswordPool['PlainPassword'];
                                        $HashedPasswword = $getGeneratedPasswordPool['EncryptedPassword'];

                                        $PlayerIP = getenv("REMOTE_ADDR");
                                        $UserAgent = $_SERVER['HTTP_USER_AGENT'];
                                        $Username = $TerminalCode;
                                        $Password = $PlainPassword;
                                        $PlayerRank = $isVIP;

                                        //Create Player to Habanero
                                        $CreatePlayerHabanero = $HabaneroAPIWrapper->CreatePlayer($PlayerIP, $UserAgent, $Username, $Password, $PlayerRank);

                                        if ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == true) {
                                            $title = "[Success | CreatePlayerHabanero:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Created Account to Habanero.";
                                            $PDO->InsertLogsHabanero($title, $message);

                                            //Check if player created
                                            $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                                            if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {
                                                $title = "[Success | QueryPlayer:]";
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Query Player.";
                                                $PDO->InsertLogsHabanero($title, $message);

                                                try {

                                                    //$InsertTerminalServices = true;
                                                    $InsertTerminalServices = $PDO->InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword);

                                                    if ($InsertTerminalServices) {
                                                        $title = "[Success | InsertTerminalServices:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Inserted to Terminal Services.";
                                                        $PDO->InsertLogsHabanero($title, $message);
                                                    } else {
                                                        $title = "[Error | InsertTerminalServices:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Fail to Insert Terminal Services.";
                                                        $PDO->InsertLogsHabanero($title, $message);
                                                    }
                                                } catch (Exception $ex) {
                                                    $title = "[Error | InsertTerminalServices:]";
                                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                                    $PDO->InsertLogsHabanero($title, $message);
                                                }
                                            } else {
                                                $title = "[Error | QueryPlayer:]";
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Player account not found.";
                                                $PDO->InsertLogsHabanero($title, $message);
                                            }
                                        } elseif ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == false) {
                                            $title = "[Error | CreatePlayerHabanero:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Already Created on Habanero backend.";
                                            $PDO->InsertLogsHabanero($title, $message);

                                            try {
                                                //Update Player to Habanero
                                                $UpdatePlayerPassword = $HabaneroAPIWrapper->UpdatePlayerPassword($Username, $Password);
                                                if ($UpdatePlayerPassword['updatepasswordmethodResult']['Success'] == true) {
                                                    $title = "[Success | UpdatePlayerPassword:]";
                                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Update Player Password.";
                                                    $PDO->InsertLogsHabanero($title, $message);

                                                    //Check if player created
                                                    $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                                                    if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {
                                                        $title = "[Success | QueryPlayer:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Query Player.";
                                                        $PDO->InsertLogsHabanero($title, $message);

                                                        try {
                                                            //$InsertTerminalServices = true;
                                                            $InsertTerminalServices = $PDO->InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword);

                                                            if ($InsertTerminalServices) {
                                                                $title = "[Success | InsertTerminalServices:]";
                                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successfuly Inserted to Terminal Services.";
                                                                $PDO->InsertLogsHabanero($title, $message);
                                                            } else {
                                                                $title = "[Error | InsertTerminalServices:]";
                                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Fail to Insert Terminal Services.";
                                                                $PDO->InsertLogsHabanero($title, $message);
                                                            }
                                                        } catch (Exception $ex) {
                                                            $title = "[Error | InsertTerminalServices:]";
                                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                                            $PDO->InsertLogsHabanero($title, $message);
                                                        }
                                                    } else {

                                                        $title = "[Error | QueryPlayer:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Player account not found.";
                                                        $PDO->InsertLogsHabanero($title, $message);
                                                    }
                                                } else {
                                                    $title = "[Error | UpdatePlayerPassword:]";
                                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't Update Player Password.";
                                                    $PDO->InsertLogsHabanero($title, $message);
                                                }
                                            } catch (Exception $ex) {
                                                $title = "[Error | UpdatePlayerPassword:]";
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                                $PDO->InsertLogsHabanero($title, $message);
                                            }
                                        } else {
                                            $title = "[Error | CreatePlayerHabanero:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $CreatePlayerHabanero[' createplayermethodResult'][' Message'];
                                            $PDO->InsertLogsHabanero($title, $message);
                                        }
                                    } else {
                                        $title = "[Error | getGeneratedPasswordPool:]";
                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can' t get Generated Password.";
                                        $PDO->InsertLogsHabanero($title, $message);
                                    }
                                } catch (Exception $f) {
                                    $title = "[Error | GetGeneratedPasswordPool:]";
                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $f->getMessage();
                                    $PDO->InsertLogsHabanero($title, $message);
                                }
                            } else {
                                $title = "[Error | GetGeneratedPasswordBatchID:]";
                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't get Generated Password Batch ID!.";
                                $PDO->InsertLogsHabanero($title, $message);
                            }
                        } catch (Exception $e) {
                            $title = "[Error | GetGeneratedPasswordBatchID:]";

                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $e->getMessage();
                            $PDO->InsertLogsHabanero($title, $message);
                        }
                    } else {
                        $title = "[Error | CheckTerminalServicesIfHasHabanero:]";
                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Terminal Already have account to Habanero!.";
                        $PDO->InsertLogsHabanero($title, $message);
                    }
                } catch (Exception $d) {
                    $title = "[Error | CheckTerminalServicesIfHasHabanero:]";
                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $d->getMessage();
                    $PDO->InsertLogsHabanero($title, $message);
                }
            }

            /* VIP */
            $TerminalCode = $login . "VIP";
            $getTerminalID = $PDO->getTerminalID($TerminalCode);
            $TerminalID = $getTerminalID['TerminalID'];
            $siteID = $SiteID;

            if (!empty($getTerminalID)) {

                try {
                    $checkTerminalServicesIfHasHabanero = $PDO->checkTerminalServicesIfHasHabanero($TerminalID); //Check if Terminal has 25 serviceID
                    //if False
                    if (!$checkTerminalServicesIfHasHabanero) {
                        try {
                            //Get the GeneratedPasswordBatchID of the Site
                            $getGeneratedPasswordBatchID = $PDO->getHabaneroGeneratedPasswordBatchID($siteID);

                            if (!$getGeneratedPasswordBatchID) {
                                try {
                                    $getNewGeneratedPasswordBatchID = $PDO->getNewGeneratedPasswordBatchID();
                                    if ($getNewGeneratedPasswordBatchID) {
                                        $NewGeneratedPasswordBatchID = $getNewGeneratedPasswordBatchID['GeneratedPasswordBatchID'];
                                        $GeneratedPasswordBatchID = $NewGeneratedPasswordBatchID;
                                        try {
                                            $UpdateNewGeneratedPasswordBatchID = $PDO->UpdateNewGeneratedPasswordBatchID($siteID, $NewGeneratedPasswordBatchID);
                                            if ($UpdateNewGeneratedPasswordBatchID == 1) {
                                                $title = "[Success | UpdateNewGeneratedPasswordBatchID:]";
                                                $message = $siteID . " | Successful Update New Generated Password Batch ID!";
                                                $PDO->InsertLogsHabanero($title, $message);
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Terminal Already have account to Habanero!.";
                                            }
                                        } catch (Exception $ex) {
                                            $title = "[Error | UpdateNewGeneratedPasswordBatchID:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                            $PDO->InsertLogsHabanero($title, $message);
                                        }
                                    } else {
                                        $title = "[Error | GetNewGeneratedPasswordBatchID:]";
                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't get New Generated Password Batch ID.";
                                        $PDO->InsertLogsHabanero($title, $message);
                                    }
                                } catch (Exception $ex) {
                                    $title = "[Error | GetNewGeneratedPasswordBatchID:]";
                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                    $PDO->InsertLogsHabanero($title, $message);
                                }
                            } else {
                                $GeneratedPasswordBatchID = $getGeneratedPasswordBatchID['GeneratedPasswordBatchID'];
                            }


                            if (!empty($GeneratedPasswordBatchID)) {

                                try {
                                    //Get Password in GeneratedPasswordPool
                                    $getGeneratedPasswordPool = $PDO->getHabaneroGeneratedPasswordPool($GeneratedPasswordBatchID);

                                    if ($getGeneratedPasswordPool) {

                                        $PlainPassword = $getGeneratedPasswordPool['PlainPassword'];
                                        $HashedPasswword = $getGeneratedPasswordPool['EncryptedPassword'];

                                        $PlayerIP = getenv("REMOTE_ADDR");
                                        $UserAgent = $_SERVER['HTTP_USER_AGENT'];
                                        $Username = $TerminalCode;
                                        $Password = $PlainPassword;
                                        $PlayerRank = $isVIP;

                                        //Create Player to Habanero
                                        $CreatePlayerHabanero = $HabaneroAPIWrapper->CreatePlayer($PlayerIP, $UserAgent, $Username, $Password, $PlayerRank);

                                        if ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == true) {
                                            $title = "[Success | CreatePlayerHabanero:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Created Account to Habanero.";
                                            $PDO->InsertLogsHabanero($title, $message);

                                            //Check if player created
                                            $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                                            if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {
                                                $title = "[Success | QueryPlayer:]";
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Query Player.";
                                                $PDO->InsertLogsHabanero($title, $message);

                                                try {

                                                    //$InsertTerminalServices = true;
                                                    $InsertTerminalServices = $PDO->InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword);

                                                    if ($InsertTerminalServices) {
                                                        $title = "[Success | InsertTerminalServices:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Inserted to Terminal Services.";
                                                        $PDO->InsertLogsHabanero($title, $message);
                                                    } else {
                                                        $title = "[Error | InsertTerminalServices:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Fail to Insert Terminal Services.";
                                                        $PDO->InsertLogsHabanero($title, $message);
                                                    }
                                                } catch (Exception $ex) {
                                                    $title = "[Error | InsertTerminalServices:]";
                                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                                    $PDO->InsertLogsHabanero($title, $message);
                                                }
                                            } else {
                                                $title = "[Error | QueryPlayer:]";
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Player account not found.";
                                                $PDO->InsertLogsHabanero($title, $message);
                                            }
                                        } elseif ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == false) {
                                            $title = "[Error | CreatePlayerHabanero:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Already Created on Habanero backend.";
                                            $PDO->InsertLogsHabanero($title, $message);

                                            try {
                                                //Update Player to Habanero
                                                $UpdatePlayerPassword = $HabaneroAPIWrapper->UpdatePlayerPassword($Username, $Password);
                                                if ($UpdatePlayerPassword['updatepasswordmethodResult']['Success'] == true) {
                                                    $title = "[Success | UpdatePlayerPassword:]";
                                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Update Player Password.";
                                                    $PDO->InsertLogsHabanero($title, $message);

                                                    //Check if player created
                                                    $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                                                    if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {
                                                        $title = "[Success | QueryPlayer:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successful Query Player.";
                                                        $PDO->InsertLogsHabanero($title, $message);

                                                        try {
                                                            //$InsertTerminalServices = true;
                                                            $InsertTerminalServices = $PDO->InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword);

                                                            if ($InsertTerminalServices) {
                                                                $title = "[Success | InsertTerminalServices:]";
                                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Successfuly Inserted to Terminal Services.";
                                                                $PDO->InsertLogsHabanero($title, $message);
                                                            } else {
                                                                $title = "[Error | InsertTerminalServices:]";
                                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Fail to Insert Terminal Services.";
                                                                $PDO->InsertLogsHabanero($title, $message);
                                                            }
                                                        } catch (Exception $ex) {
                                                            $title = "[Error | InsertTerminalServices:]";
                                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                                            $PDO->InsertLogsHabanero($title, $message);
                                                        }
                                                    } else {

                                                        $title = "[Error | QueryPlayer:]";
                                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Player account not found.";
                                                        $PDO->InsertLogsHabanero($title, $message);
                                                    }
                                                } else {
                                                    $title = "[Error | UpdatePlayerPassword:]";
                                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't Update Player Password.";
                                                    $PDO->InsertLogsHabanero($title, $message);
                                                }
                                            } catch (Exception $ex) {
                                                $title = "[Error | UpdatePlayerPassword:]";
                                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $ex->getMessage();
                                                $PDO->InsertLogsHabanero($title, $message);
                                            }
                                        } else {
                                            $title = "[Error | CreatePlayerHabanero:]";
                                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $CreatePlayerHabanero[' createplayermethodResult'][' Message'];
                                            $PDO->InsertLogsHabanero($title, $message);
                                        }
                                    } else {
                                        $title = "[Error | getGeneratedPasswordPool:]";
                                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can' t get Generated Password.";
                                        $PDO->InsertLogsHabanero($title, $message);
                                    }
                                } catch (Exception $f) {
                                    $title = "[Error | GetGeneratedPasswordPool:]";
                                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $f->getMessage();
                                    $PDO->InsertLogsHabanero($title, $message);
                                }
                            } else {
                                $title = "[Error | GetGeneratedPasswordBatchID:]";
                                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Can't get Generated Password Batch ID.";
                                $PDO->InsertLogsHabanero($title, $message);
                            }
                        } catch (Exception $e) {
                            $title = "[Error | GetGeneratedPasswordBatchID:]";

                            $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $e->getMessage();
                            $PDO->InsertLogsHabanero($title, $message);
                        }
                    } else {
                        $title = "[Error | CheckTerminalServicesIfHasHabanero:]";
                        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | Terminal Already have account to Habanero.";
                        $PDO->InsertLogsHabanero($title, $message);
                    }
                } catch (Exception $d) {
                    $title = "[Error | CheckTerminalServicesIfHasHabanero:]";
                    $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | " . $d->getMessage();
                    $PDO->InsertLogsHabanero($title, $message);
                }
            } else {
                $title = "[Error | GetVIPTerminalID:]";
                $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | TerminalID : " . $TerminalID . " | TerminalCode : " . $TerminalCode . " | SiteCode : " . $SiteCode . " | ServiceID : " . $serviceID . " | No VIP Terminal Created.";
                $PDO->InsertLogsHabanero($title, $message);
            }







            $counterTN = $counterTN + 1;
        }

        $elapsed = (microtime(true) - $startTime);
        $endTime = microtime(true);

        echo
        " <br>Result: <br><br> Start Time : " . $startTime . " seconds" .
        " <br>End Time : " . $endTime . " seconds" .
        " <br><br> Elapsed time is: " . $elapsed . " seconds";

        $title = "[EndTime]";
        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $endTime;
        $PDO->InsertLogsHabanero($title, $errormessage);

        $title = "[ElapsedTime]";
        $message = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SESSION['IP_Adddress'] . " | SiteCode : " . $SiteCode . " | " . $elapsed;
        $PDO->InsertLogsHabanero($title, $errormessage);
    }
}