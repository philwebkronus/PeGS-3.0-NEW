
<?php

/*
 * @description: Batch Terminal Creation for habanero
 * @date Created January 04, 2018 14:51 PM <pre />
 * @author Jonathan Concepcion <jvconcepcion@philweb.com.ph> & Ronald Patrick Santos <rjsantos@philweb.com.ph>
 */
ini_set('display_errors', true);
ini_set('log_errors', true);

include 'PDOhandler.php';
include 'HabaneroPlayerAPI.class.php';
include 'recordlogs.php';

try {
    $title = "\n\n[START -----------------------------------------------------------]";
    $message = "";
    InsertLogs($title, $message);

    $PDO = new PDOhandler; //get pdo db connection

    try {
        $HabaneroAPIWrapper = new HabaneroPlayerAPI; //call habanero api

        try {
            $checkTerminalServicesIfHasTopaz = $PDO->checkTerminalServicesIfHasTopaz(); //Check if Terminal has 22 serviceID

            if ($checkTerminalServicesIfHasTopaz !== false && count($checkTerminalServicesIfHasTopaz) > 0) {

                foreach ($checkTerminalServicesIfHasTopaz as $rowTerminals) {
                    $TerminalID = $rowTerminals['TerminalID'];

                    try {
                        $getTerminalDetails = $PDO->getTerminalDetails($TerminalID);
                        if (count($getTerminalDetails) > 0) {
                            $TerminalCode = $getTerminalDetails[0]['TerminalCode'];
                            $isVIP = $getTerminalDetails[0]['isVIP'];
                            $siteID = $getTerminalDetails[0]['SiteID'];

                            //if True
                            if ($checkTerminalServicesIfHasTopaz) {
                                try {
                                    $checkTerminalServicesIfHasHabanero = $PDO->checkTerminalServicesIfHasHabanero($TerminalID); //Check if Terminal has 25 serviceID
                                    //if False
                                    if (!$checkTerminalServicesIfHasHabanero) {
                                        try {
                                            //Get the GeneratedPasswordBatchID of the Site
                                            $getGeneratedPasswordBatchID = $PDO->getGeneratedPasswordBatchID($siteID);
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
                                                                InsertLogs($title, $message);
                                                            }
                                                        } catch (Exception $ex) {
                                                            $title = "[Error | UpdateNewGeneratedPasswordBatchID:]";
                                                            $message = $ex->getMessage();
                                                            InsertLogs($title, $message);
                                                        }
                                                    } else {
                                                        $title = "[Error | GetNewGeneratedPasswordBatchID:]";
                                                        $message = $TerminalCode . " | Can't get New Generated Password Batch ID!";
                                                        InsertLogs($title, $message);
                                                    }
                                                } catch (Exception $ex) {
                                                    $title = "[Error | GetNewGeneratedPasswordBatchID:]";
                                                    $message = $TerminalCode . " | Can' t get New Generated Password Batch ID!";
                                                    InsertLogs($title, $message);
                                                }
                                            } else {
                                                $GeneratedPasswordBatchID = $getGeneratedPasswordBatchID['GeneratedPasswordBatchID'];
                                            }


                                            if (!empty($GeneratedPasswordBatchID)) {

                                                try {
                                                    //Get Password in GeneratedPasswordPool
                                                    $getGeneratedPasswordPool = $PDO->getGeneratedPasswordPool($GeneratedPasswordBatchID);

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
                                                            $message = $TerminalCode . " | Successful Created Account to Habanero!";
                                                            InsertLogs($title, $message);

                                                            //Check if player created
                                                            $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                                                            if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {
                                                                $title = "[Success | QueryPlayer:]";
                                                                $message = $TerminalCode . " | Successful Query Player!";
                                                                InsertLogs($title, $message);

                                                                try {

                                                                    //$InsertTerminalServices = true;
                                                                    $InsertTerminalServices = $PDO->InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword);

                                                                    if ($InsertTerminalServices) {
                                                                        $title = "[Success | InsertTerminalServices:]";
                                                                        $message = $TerminalCode . " | Successful Inserted to Terminal Services!";
                                                                        InsertLogs($title, $message);
                                                                    } else {
                                                                        $title = "[Error | InsertTerminalServices:]";
                                                                        $message = $TerminalCode . " | Fail to Insert Terminal Services!";
                                                                        InsertLogs($title, $message);
                                                                    }
                                                                } catch (Exception $ex) {
                                                                    $title = "[Error | InsertTerminalServices:]";
                                                                    $message = $ex->getMessage();
                                                                    InsertLogs($title, $message);
                                                                }
                                                            } else {
                                                                $title = "[Error | QueryPlayer:]";
                                                                $message = $TerminalCode . " | Player account not found!";
                                                                InsertLogs($title, $message);
                                                            }
                                                        } elseif ($CreatePlayerHabanero['createplayermethodResult']['PlayerCreated'] == false) {
                                                            $title = "[Error | CreatePlayerHabanero:]";
                                                            $message = $TerminalCode . " | Already Created on Habanero backend!";
                                                            InsertLogs($title, $message);

                                                            try {
                                                                //Update Player to Habanero
                                                                $UpdatePlayerPassword = $HabaneroAPIWrapper->UpdatePlayerPassword($Username, $Password);
                                                                if ($UpdatePlayerPassword['updatepasswordmethodResult']['Success'] == true) {
                                                                    $title = "[Success | UpdatePlayerPassword:]";
                                                                    $message = $TerminalCode . " | Successful Update Player Password!";
                                                                    InsertLogs($title, $message);

                                                                    //Check if player created
                                                                    $QueryPlayer = $HabaneroAPIWrapper->QueryPlayer($Username, $Password);

                                                                    if ($QueryPlayer['queryplayermethodResult']['Found'] == true) {
                                                                        $title = "[Success | QueryPlayer:]";
                                                                        $message = $TerminalCode . " | Successful Query Player!";
                                                                        InsertLogs($title, $message);

                                                                        try {
                                                                            //$InsertTerminalServices = true;
                                                                            $InsertTerminalServices = $PDO->InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword);

                                                                            if ($InsertTerminalServices) {
                                                                                $title = "[Success | InsertTerminalServices:]";
                                                                                $message = $TerminalCode . " | Successful Inserted to Terminal Services!";
                                                                                InsertLogs($title, $message);
                                                                            } else {
                                                                                $title = "[Error | InsertTerminalServices:]";
                                                                                $message = $TerminalCode . " | Fail to Insert Terminal Services!";
                                                                                InsertLogs($title, $message);
                                                                            }
                                                                        } catch (Exception $ex) {
                                                                            $title = "[Error | InsertTerminalServices:]";
                                                                            $message = $ex->getMessage();
                                                                            InsertLogs($title, $message);
                                                                        }
                                                                    } else {

                                                                        $title = "[Error | QueryPlayer:]";
                                                                        $message = $TerminalCode . " | Player account not found!";
                                                                        InsertLogs($title, $message);
                                                                    }
                                                                } else {
                                                                    $title = "[Error | UpdatePlayerPassword:]";
                                                                    $message = $TerminalCode . " | Can't Update Player Password!";
                                                                    InsertLogs($title, $message);
                                                                }
                                                            } catch (Exception $ex) {
                                                                $title = "[Error | UpdatePlayerPassword:]";
                                                                $message = $ex->getMessage();
                                                                InsertLogs($title, $message);
                                                            }
                                                        } else {
                                                            $title = "[Error | CreatePlayerHabanero:]";
                                                            $message = $CreatePlayerHabanero[' createplayermethodResult'][' Message'];
                                                            InsertLogs($title, $message);
                                                        }
                                                    } else {
                                                        $title = "[Error | getGeneratedPasswordPool:]";
                                                        $message = $TerminalCode . " | Can' t get Generated Password!";
                                                        InsertLogs($title, $message);
                                                    }
                                                } catch (Exception $f) {
                                                    $title = "[Error | GetGeneratedPasswordPool:]";
                                                    $message = $f->getMessage();
                                                    InsertLogs($title, $message);
                                                }
                                            } else {
                                                $title = "[Error | GetGeneratedPasswordBatchID:]";
                                                $message = $TerminalCode . " | Can't get Generated Password Batch ID!";
                                                InsertLogs($title, $message);
                                            }
                                        } catch (Exception $e) {
                                            $title = "[Error | GetGeneratedPasswordBatchID:]";
                                            $message = $e->getMessage();
                                            InsertLogs($title, $message);
                                        }
                                    } else {
                                        $title = "[Error | CheckTerminalServicesIfHasHabanero:]";
                                        $message = $TerminalCode . " | Terminal Already have account to Habanero!";
                                        InsertLogs($title, $message);
                                    }
                                } catch (Exception $d) {
                                    $title = "[Error | CheckTerminalServicesIfHasHabanero:]";
                                    $message = $d->getMessage();
                                    InsertLogs($title, $message);
                                }
                            } else {
                                $title = "[Error | CheckTerminalServicesIfHasTopaz:]";
                                $message = $TerminalCode . " | Terminal has no account on Topaz!";
                                InsertLogs($title, $message);
                            }
                        }
                    } catch (Exception $ex) {
                        $title = "[Error | GetTerminalDetails:]";
                        $message = $a->getMessage();
                        InsertLogs($title, $message);
                    }
                }
                $title = "[END -----------------------------------------------------------]";
                $message = "\n\n";
                InsertLogs($title, $message);
            }
        } catch (Exception $ex) {
            $title = "[Error | CheckTerminalServicesIfHasTopaz:]";
            $message = $ex->getMessage();
            InsertLogs($title, $message);
        }
    } catch (Exception $he) {
        $title = "[Error | API Error:]";
        $message = $he->getMessage();
        InsertLogs($title, $message);
        echo $title;
    }
} catch (PDOException $pe) {
    $title = "[Error | Database Error:]";
    $message = $pe->getMessage();
    InsertLogs($title, $message);
    echo $title;
    die();
}
?>

