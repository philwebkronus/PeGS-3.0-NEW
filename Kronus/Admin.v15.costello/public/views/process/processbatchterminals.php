<?php

/**
 * Created By: Edson L. Perez
 * Created ON: September 08, 2011
 * Modified On : May 03, 2012 -- for MGCAPI Integration
 * Modified On : January 28, 2013 -- for PT Integration
 * Purpose: Process for Batch Terminal Creation
 */
include __DIR__ . "/../sys/class/BatchTerminalMgmt.class.php";
require __DIR__ . '/../sys/core/init.php';
include __DIR__ . '/../sys/class/CasinoGamingCAPI.class.php';
include __DIR__ . '/../sys/class/CasinoGamingCAPIUB.class.php';
include __DIR__ . '/../sys/class/helper.class.php';


$aid = 0;
if (isset($_SESSION['sessionID'])) {
    $new_sessionid = $_SESSION['sessionID'];
} else {
    $new_sessionid = '';
}
if (isset($_SESSION['accID'])) {
    $aid = $_SESSION['accID'];
}

$obatch = new BatchTerminalMgmt($_DBConnectionString[0]);
$connected = $obatch->open();
$nopage = 0;
if ($connected) {
    /*     * *******************SESSION CHECKING *********************** */
    $isexist = $obatch->checksession($aid);
    if ($isexist == 0) {
        session_destroy();
        $msg = "Not Connected";
        $obatch->close();
        if ($obatch->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }
    $isexistsession = $obatch->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) {
        session_destroy();
        $msg = "Not Connected";
        $obatch->close();
        header("Location: login.php?mess=" . $msg);
    }
    /*     * ********************END SESSION CHECKING ****************** */
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $obatch->getDate();

    if (isset($_POST['page'])) {
        $vpage = $_POST['page'];
        switch ($vpage) {
            case 'BatchTerminalCreation':
                if (isset($_POST['cmbsitename']) && isset($_POST['cmbterminals'])
                        && (isset($_POST['optserver']) || isset($_POST['optserver1']) || isset($_POST['optserver2']))) {
                    $vsiteID = $_POST['cmbsitename'];
                    $vterminals = $_POST['cmbterminals']; // no. of terminals that will be created
                    $vsitecode = $_POST['txtsitecode'];
                    $vlastterminal = (int) $_POST['txtlastterm'];

                    //get Server ID
                    if (isset($_POST['optserver'])) {
                        $arrserverID = $_POST['optserver'];
                    }

//                    if(isset($_POST['optserver1']))
//                    {
//                        $arrserverID1 = $_POST['optserver1'];
//                    }
//                    
//                    if(isset($_POST['optserver2']))
//                    {
//                        $arrserverID2 = $_POST['optserver2'];
//                    }                  
                    $vCreatedByAID = $aid; // session account id
                    $vStatus = 1;
                    $vterminalno = $vlastterminal + 1; //add + 1
                    //set all default values for the casinos
                    $country = 'PH';
                    $casinoID = 1;
                    $fname = 'ICSA';
                    $email = '';
                    $dayphone = '3385599';
                    $evephone = '';
                    $addr1 = 'PH';
                    $addr2 = '';
                    $city = 'PH';
                    $state = '';
                    $zip = '1232';
                    $ip = '';
                    $mac = '';
                    $userID = 0;
                    $downloadID = 0;
                    $birthdate = '1981-01-01';
                    $clientID = 1;
                    $putInAffPID = 0;
                    $calledFromCasino = 0;
                    $agentID = '';
                    $currentPosition = 0;
                    $thirdPartyPID = '';
                    $sex = '';
                    $fax = '';
                    $occupation = '';

                    $arrterminalID = array();

                    $roldsite = $obatch->chkoldsite($vsiteID);

                    $vgenpwdid = 0;

                    //check if this is a existing site and Status is active and use
                    if (isset($roldsite['GeneratedPasswordBatchID']) && $roldsite['GeneratedPasswordBatchID'] > 0) {
                        $vgenpwdid = $roldsite['GeneratedPasswordBatchID'];
                        $isoldsite = 1;
                    } else {
                        $rpwdbatch = $obatch->chkpwdbatch();
                        $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                        $isoldsite = 0;
                    }

                    //check if generatedpasswordbatch returns an ID
                    if ($vgenpwdid > 0) {
                        $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                        $_CasinoGamingPlayerAPIUB = new CasinoGamingCAPIUB();
                        $isassigned = 0;
                        $isapisuccess = 0;
                        $mgUsedServer = 0;
                        $rtgUsedServer = 0;
                        $ptUsedServer = 0;

                        //loop through number of selected terminals to be assigned in its
                        //chosen casino provider
                        for ($ctrterminal = 1; $ctrterminal <= $vterminals; $ctrterminal++) {
                            if ($vterminalno < 10)
                                $vstartcode = str_pad($vterminalno, 2, "0", STR_PAD_LEFT); //if terminal no. is less than 10 pad to 0
                            else
                                $vstartcode = $vterminalno;

                            //explode radiobox of MG
                            if (isset($arrserverID) && isset($arrserverID[$ctrterminal])) {
                                $servers = explode(':', $arrserverID[$ctrterminal]);
                                $vservicegrpid = $servers[2];
                                $vserviceID = $servers[1];
                                $vprovider = $servers[0];

                                $usermode = $obatch->getServiceUserMode($vserviceID);

                                $servicegroupname = $obatch->getServiceGrpNameById($vserviceID);

                                $vprovider = $servicegroupname;
                                //-----------------  explode radiobox of RTG ----------------------- //
                                $rtggrpid = $servers[2];
                                $rtgvserviceID = $servers[1];
                                $rtgvprovider = $servers[0];
                                $usermode = $obatch->getServiceUserMode($rtgvserviceID);

                                $servicegroupname = $obatch->getServiceGrpNameById($rtgvserviceID);

                                $rtgvprovider = $servicegroupname;
                                //-----------------  explode radiobox of PT ----------------------- //
                                $ptgrpid = $servers[2];
                                $ptvserviceID = $servers[1];
                                $ptvprovider = $servers[0];
                                $usermode = $obatch->getServiceUserMode($ptvserviceID);

                                $servicegroupname = $obatch->getServiceGrpNameById($ptvserviceID);

                                $ptvprovider = $servicegroupname;
                            }

//                            //explode radiobox of RTG
//                            if(isset($arrserverID1) && isset($arrserverID1[$ctrterminal]))
//                            {
//                                $rtgservers = explode(':',$arrserverID1[$ctrterminal]);
//                                $rtggrpid = $rtgservers[2];
//                                $rtgvserviceID = $rtgservers[1];
//                                $rtgvprovider = $rtgservers[0];
//                                
//                                $usermode = $obatch->getServiceUserMode($rtgvserviceID);
//                                
//                                $servicegroupname = $obatch->getServiceGrpNameById($rtgvserviceID);
//                                
//                                $rtgvprovider = $servicegroupname;
//                            }
//                           
//                            //explode radiobox of PT
//                            if(isset($arrserverID2) && isset($arrserverID2[$ctrterminal]))
//                            {
//                                $ptservers = explode(':',$arrserverID2[$ctrterminal]);
//                                $ptgrpid = $ptservers[2];
//                                $ptvserviceID = $ptservers[1];
//                                $ptvprovider = $ptservers[0];
//                                
//                                $usermode = $obatch->getServiceUserMode($ptvserviceID);
//                                
//                                $servicegroupname = $obatch->getServiceGrpNameById($ptvserviceID);
//                                
//                                $ptvprovider = $servicegroupname;
//                            }

                            $lname = $vsitecode . $vstartcode;
                            $alias = $vsitecode . $vstartcode;

                            $email = strtolower($lname) . '@yopmail.com';

                            $siteclassid = $obatch->selectsiteclassification($vsiteID);
                            //check if Site is for e-Bingo
                            if ((int) $siteclassid['SiteClassificationID'] == 3) {
                                //check if casino is e-Bingo
                                if ((int) $usermode != 2) {
                                    $servicename = $obatch->viewterminalservices(0, $ptvserviceID);
                                    $errmsg = "Cannot Map " . $servicename[0]['ServiceName'] . " to an e-Bingo site";
                                    $isapisuccess = 0;
                                    $nebingo = false;
                                    break;
                                } else {
                                    $nebingo = true;
                                }
                            } else {
                                if ((int) $usermode == 2) {
                                    $servicename = $obatch->viewterminalservices(0, $ptvserviceID);
                                    $errmsg = "Cannot Map " . $servicename[0]['ServiceName'] . " to a non e-Bingo site";
                                    $isapisuccess = 0;
                                    $nebingo = false;
                                    break;
                                } else {
                                    $nebingo = true;
                                }
                            }

                            //it depends on the condition if site is e-Bingo, Platinum or Hybrid
                            if ($nebingo) {
                                //check if assigned casino provider is MG
                                if (isset($vprovider) && $vprovider == "MG") {
                                    $mgUsedServer = 1;

                                    //get password and encrypted password for MG
                                    $vretrievepwd = $obatch->getgeneratedpassword($vgenpwdid, $vservicegrpid);
                                    $vgenpassword = $vretrievepwd['PlainPassword'];
                                    $vgenhashed = $vretrievepwd['EncryptedPassword'];

                                    //Start : sets regular terminal account in MG
                                    $password = $vgenpassword; //set casino password
                                    $vterminalName = "TERMINAL" . $vstartcode;
                                    $vterminalCode = $terminalcode . $vsitecode . $vstartcode; //(icsa-) + sitecode + terminalno
                                    $regterminal = $vterminalCode;
                                    $visVIP = 0;

                                    $_MGCredentials = $_PlayerAPI[$vserviceID - 1];
                                    list($mgurl, $capi_serverid) = $_MGCredentials;
                                    $url = $mgurl;
                                    $login = $vterminalCode;
                                    $hashedPassword = '';
                                    $aid = $_MicrogamingUserType;
                                    $currency = $_MicrogamingCurrency;
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = $capi_serverid;

                                    //Creates regular terminal account in MG
                                    $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovider, $vserviceID, $mgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                    //Check if regular terminal account successfully created in MG
                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                        $isapisuccess = 1;

                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $vserviceID, 1, $vgenpassword, $vgenhashed);
                                        array_push($arrterminalID, $isrecorded);

                                        //Start : sets VIP Terminal account in MG
                                        $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                        $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                        $visVIP = 1;
                                        $login = $vterminalCode;
                                        $hashedPassword = '';
                                        $aid = $_MicrogamingUserType;
                                        $currency = $_MicrogamingCurrency;
                                        $capiusername = $_CAPIUsername;
                                        $capipassword = $_CAPIPassword;
                                        $capiplayername = $_CAPIPlayerName;
                                        $capiserverID = $capi_serverid;
                                        $vipterminal = $vterminalCode;

                                        //Creates VIP terminal account in MG
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovider, $vserviceID, $mgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                        //Check if vip terminal account successfully created in MG
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                            $isapisuccess = 1;

                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $vserviceID, 1, $vgenpassword, $vgenhashed);
                                            array_push($arrterminalID, $isrecorded);
                                        } else {

                                            //check if terminal account was existing in MG, error code must be 1
                                            if ($vplayerResult['ErrorCode'] == 1) {
                                                $vaccountExist = '';

                                                //Call API to verify if account exists in MG
                                                $vplayerResult = $_CasinoGamingPlayerAPI->validateCasinoAccount($login, $vserviceID, $mgurl, $capiusername, $capipassword, $capiplayername, $capiserverID, $password);

                                                //Verify if API Call was successful
                                                if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                    $vaccountExist = $vplayerResult['AccountInfo']['UserExists'];

                                                    //check if account exists for MG Casino
                                                    if ($vaccountExist) {
                                                        //Call Reset Password API if MG
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->resetCasinoPassword($login, $password, $vserviceID, $mgurl, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                        //verify if API reset password (MG) is successfull
                                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                            $isapisuccess = 1;

                                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $vserviceID, 1, $vgenpassword, $vgenhashed);

                                                            array_push($arrterminalID, $isrecorded);
                                                        } else {
                                                            $isapisuccess = 0;
                                                            $errmsg = "MG " . $vplayerResult['ErrorMessage'];
                                                        }
                                                    }
                                                    else
                                                        $isapisuccess = 0;
                                                } else {
                                                    $isapisuccess = 0;
                                                    $errmsg = "MG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } else {
                                                $isapisuccess = 0;
                                                $errmsg = "MG " . $vplayerResult['ErrorMessage'];
                                            }
                                        }

                                        //LOG creation of MG Accounts (success / failed)
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $vserviceID);
                                    } else {

                                        //check if terminal account was existing in MG, error code must be 1
                                        if ($vplayerResult['ErrorCode'] == 1) {
                                            $vaccountExist = '';

                                            //Call API to verify if account exists in MG
                                            $vplayerResult = $_CasinoGamingPlayerAPI->validateCasinoAccount($login, $vserviceID, $mgurl, $capiusername, $capipassword, $capiplayername, $capiserverID, $password);

                                            //Verify if API Call was successful
                                            if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                $vaccountExist = $vplayerResult['AccountInfo']['UserExists'];

                                                //check if account exists for MG Casino
                                                if ($vaccountExist) {
                                                    //Call Reset Password API if MG
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->resetCasinoPassword($login, $password, $vserviceID, $mgurl, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                    //verify if API reset password (MG) is successfull
                                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                        $isapisuccess = 1;

                                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $vserviceID, 1, $vgenpassword, $vgenhashed);

                                                        array_push($arrterminalID, $isrecorded);
                                                    } else {
                                                        $isapisuccess = 0;
                                                        $errmsg = "MG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                }
                                                else
                                                    $isapisuccess = 0;
                                            }
                                            else {
                                                $isapisuccess = 0;
                                                $errmsg = "MG " . $vplayerResult['ErrorMessage'];
                                            }
                                        } else {
                                            $isapisuccess = 0;
                                            $errmsg = "MG " . $vplayerResult['ErrorMessage'];
                                        }
                                    }

                                    //LOG creation of MG Accounts
                                    $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $vserviceID);
                                }

                                //Check if assigned casino provider is RTG
                                if (isset($rtgvprovider) && $rtgvprovider == "RTG") {
                                    $rtgUsedServer = 1;

                                    //get password and encrypted password for RTG
                                    $vretrievepwd = $obatch->getgeneratedpassword($vgenpwdid, $rtggrpid);
                                    $vgenpassword = $vretrievepwd['PlainPassword'];
                                    $vgenhashed = $vretrievepwd['EncryptedPassword'];

                                    $password = $vgenpassword; //casino password
                                    //sets creation of regular terminal account in RTG
                                    $vterminalName = "TERMINAL" . $vstartcode;
                                    $vterminalCode = $terminalcode . $vsitecode . $vstartcode; //(icsa-) + sitecode + terminalno

                                    $visVIP = 0;
                                    $login = $vterminalCode;
                                    $regterminal = $vterminalCode;
                                    $rtgurl = $_PlayerAPI[$rtgvserviceID - 1];
                                    $cashierurl = $_ServiceAPI[$rtgvserviceID - 1];
                                    $hashedpass = sha1($password);
                                    $hashedPassword = $hashedpass;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';

                                    if ($usermode == 1) {
                                        $vplayerResult = array('IsSucceed' => true);
                                    }

                                    if ($usermode == 0) {
                                        //Creates regular terminal account in RTG
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                        if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0);
                                        }
                                    }

                                    if ($usermode == 2) {
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                    }


                                    //check if regular terminal account was successfully created in RTG
                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                        $isapisuccess = 1;

                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);

                                        array_push($arrterminalID, $isrecorded);

                                        /*                                         * ************************* CREATE VIP ********************************** */
                                        $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                        $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                        $visVIP = 1;
                                        $login = $vterminalCode;
                                        $vipterminal = $vterminalCode;

                                        if ($usermode == 1) {
                                            $vplayerResult = array('IsSucceed' => true);
                                        }

                                        if ($usermode == 0) {
                                            //creates vip terminal account in RTG
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                            if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1);
                                            }
                                        }

                                        if ($usermode == 2) {
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                        }

                                        //check if vip terminal account was successfully created in RTG
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                            $isapisuccess = 1;

                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);

                                            array_push($arrterminalID, $isrecorded);
                                        } else {

                                            //if account does not created in casino's RTG, check the errorcode is exists
                                            if ($vplayerResult['ErrorCode'] == 5 || $vplayerResult['ErrorID'] == 5) {

                                                //Call API to get Account Info
                                                if ($usermode == 0) {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                    if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password);
                                                    }
                                                }
                                                if ($usermode == 2) {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                }

                                                //check if exists in RTG
                                                if (isset($vplayerResult['AccountInfo']['password']) &&
                                                        $vplayerResult['AccountInfo']['password'] <> null) {
                                                    $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                    if ($usermode == 1) {
                                                        $vplayerResult = array('IsSucceed' => true);
                                                    }

                                                    if ($usermode == 0) {
                                                        //Call API Change Password
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                        }
                                                    }

                                                    if ($usermode == 2) {
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    }

                                                    //verify if API for change password (RTG) and reset password (MG) is successfull
                                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                        $isapisuccess = 1;
                                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                        array_push($arrterminalID, $isrecorded);
                                                    } else {
                                                        $isapisuccess = 0;
                                                        $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                } else {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } else {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        }

                                        //LOG creation of RTG Accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                    } else {

                                        //check if terminal account was existing in RTG, (ErrorCode must be 5)
                                        if (isset($vplayerResult['ErrorCode']) && $vplayerResult['ErrorCode'] == 5 ||
                                                isset($vplayerResult['ErrorID']) && $vplayerResult['ErrorID'] == 5) {

                                            //Call API to get Account Info
                                            if ($usermode == 0) {
                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password);
                                                }
                                            }
                                            if ($usermode == 2) {
                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                            }

                                            //check if exists in RTG
                                            if (isset($vplayerResult['AccountInfo']['password']) &&
                                                    $vplayerResult['AccountInfo']['password'] <> null) {

                                                $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                if ($usermode == 1) {
                                                    $vplayerResult = array('IsSucceed' => true);
                                                }

                                                if ($usermode == 0) {
                                                    //Call API Change Password
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                    }
                                                }

                                                if ($usermode == 2) {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                }

                                                //verify if API for change password (RTG) and reset password (MG) is successfull
                                                if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                    $isapisuccess = 1;
                                                    $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                    array_push($arrterminalID, $isrecorded);
                                                } else {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } else {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        } else {
                                            $isapisuccess = 0;
                                            $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                        }
                                    }

                                    //LOG creation of RTG Accounts
                                    $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                }


                                //Check if assigned casino provider is RTG
                                if (isset($rtgvprovider) && $rtgvprovider == "RTG2") {
                                    $rtgUsedServer = 1;

                                    //get password and encrypted password for RTG
                                    $vretrievepwd = $obatch->getgeneratedpassword($vgenpwdid, $rtggrpid);
                                    $vgenpassword = $vretrievepwd['PlainPassword'];
                                    $vgenhashed = $vretrievepwd['EncryptedPassword'];

                                    $password = $vgenpassword; //casino password
                                    //sets creation of regular terminal account in RTG
                                    $vterminalName = "TERMINAL" . $vstartcode;
                                    $vterminalCode = $terminalcode . $vsitecode . $vstartcode; //(icsa-) + sitecode + terminalno

                                    $visVIP = 0;
                                    $login = $vterminalCode;
                                    $regterminal = $vterminalCode;
                                    $rtgurl = $_PlayerAPI[$rtgvserviceID - 1];
                                    $cashierurl = $_ServiceAPI[$rtgvserviceID - 1];
                                    $hashedpass = sha1($password);
                                    $hashedPassword = $hashedpass;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';

                                    if ($usermode == 1) {
                                        $vplayerResult = array('IsSucceed' => true);
                                    }

                                    if ($usermode == 0) {
                                        //Creates regular terminal account in RTG
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                        if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0);
                                        }
                                    }

                                    if ($usermode == 2) {
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                    }


                                    //check if regular terminal account was successfully created in RTG
                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                        $isapisuccess = 1;

                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);

                                        array_push($arrterminalID, $isrecorded);

                                        /*                                         * ************************* CREATE VIP ********************************** */
                                        $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                        $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                        $visVIP = 1;
                                        $login = $vterminalCode;
                                        $vipterminal = $vterminalCode;

                                        if ($usermode == 1) {
                                            $vplayerResult = array('IsSucceed' => true);
                                        }

                                        if ($usermode == 0 || $usermode == 2) {
                                            //creates vip terminal account in RTG
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                            if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1);
                                            }
                                        }

                                        if ($usermode == 2) {
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                        }

                                        //check if vip terminal account was successfully created in RTG
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                            $isapisuccess = 1;

                                            if ($usermode == 0 || $usermode == 2) {
                                                $pid = $vplayerResult['PID'];
                                                $playerClassID = 2;
                                                $_CasinoGamingPlayerAPI->ChangePlayerClassification($rtgvprovider, $url, $pid, $playerClassID, $userID, $rtgvserviceID);
                                            }


                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);

                                            array_push($arrterminalID, $isrecorded);
                                        } else {

                                            //if account does not created in casino's RTG, check the errorcode is exists
                                            if ($vplayerResult['ErrorCode'] == 5 || $vplayerResult['ErrorID'] == 5) {

                                                //Call API to get Account Info
                                                if ($usermode == 0) {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                    if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password);
                                                    }
                                                }
                                                if ($usermode == 2) {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                }

                                                //check if exists in RTG
                                                if (isset($vplayerResult['AccountInfo']['password']) &&
                                                        $vplayerResult['AccountInfo']['password'] <> null) {
                                                    $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                    if ($usermode == 1) {
                                                        $vplayerResult = array('IsSucceed' => true);
                                                    }

                                                    if ($usermode == 0) {
                                                        //Call API Change Password
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                        }
                                                    }

                                                    if ($usermode == 2) {
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    }

                                                    //verify if API for change password (RTG) and reset password (MG) is successfull
                                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                        $isapisuccess = 1;
                                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                        array_push($arrterminalID, $isrecorded);
                                                    } else {
                                                        $isapisuccess = 0;
                                                        $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                } else {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } else {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        }

                                        //LOG creation of RTG Accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                    } else {

                                        //check if terminal account was existing in RTG, (ErrorCode must be 5)
                                        if (isset($vplayerResult['ErrorCode']) && $vplayerResult['ErrorCode'] == 5 ||
                                                isset($vplayerResult['ErrorID']) && $vplayerResult['ErrorID'] == 5) {

                                            //Call API to get Account Info
                                            if ($usermode == 0) {
                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password);
                                                }
                                            }
                                            if ($usermode == 2) {
                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                            }

                                            //check if exists in RTG
                                            if (isset($vplayerResult['AccountInfo']['password']) &&
                                                    $vplayerResult['AccountInfo']['password'] <> null) {

                                                $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                if ($usermode == 1) {
                                                    $vplayerResult = array('IsSucceed' => true);
                                                }

                                                if ($usermode == 0 || $usermode == 2) {
                                                    //Call API Change Password
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    if ($vplayerResult == NULL) { // proceeed if certificate does not match
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                    }
                                                }

                                                if ($usermode == 2) {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                }

                                                //verify if API for change password (RTG) and reset password (MG) is successfull
                                                if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                    $isapisuccess = 1;
                                                    $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                    array_push($arrterminalID, $isrecorded);
                                                } else {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } else {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        } else {
                                            $isapisuccess = 0;
                                            $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                        }
                                    }

                                    //LOG creation of RTG Accounts
                                    $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                }

                                //Check if assigned casino provider was Playtech (PT)
                                if (isset($ptvprovider) && $ptvprovider == 'PT') {
                                    $ptUsedServer = 1;

                                    //get password and encrypted password for PT
                                    $vretrievepwd = $obatch->getgeneratedpassword($vgenpwdid, $ptgrpid);
                                    $vgenpassword = $vretrievepwd['PlainPassword'];
                                    $vgenhashed = $vretrievepwd['EncryptedPassword'];

                                    $password = $vgenpassword; //casino password
                                    $email = $lname . '@yopmail.com';

                                    //replace number in the lastname with its equivalent value  in words.
                                    $number = 0;
                                    preg_match("/\d{1,}/", $lname, $number);
                                    $replace = strtoupper(helper::convert_number_to_words((int) $number[0]));
                                    $lname = preg_replace('/\d{1,}/', $replace, $lname);

                                    //sets values for creation of regular terminal account in PT
                                    $vterminalName = 'TERMINAL' . $vstartcode;
                                    $vterminalCode = $terminalcode . $vsitecode . $vstartcode;
                                    $regterminal = $vterminalCode;
                                    $visVIP = 0;
                                    $ptVIP = 1;

                                    $pturl = $_PlayerAPI[$ptvserviceID - 1];
                                    $login = $vterminalCode;
                                    $hashedPassword = $vgenhashed;
                                    $aid = '';
                                    $currency = $_ptcurrency;
                                    $capiusername = $_ptcasinoname;
                                    $capiplayername = '';
                                    $capiserverID = '';
                                    $capipassword = $_ptsecretkey;

                                    //creates regular terminal account in PT
                                    $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($ptvprovider, $ptvserviceID, $pturl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $ptVIP);

                                    //check if regular terminal account successfully created in PT
                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vterminalCode, $isapisuccess, $vCreatedByAID, $ptvserviceID);
                                        $isapisuccess = 1;
                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $ptvserviceID, 1, $vgenpassword, $vgenhashed);
                                        array_push($arrterminalID, $isrecorded);

                                        //sets values for creation of VIP terminal account in PT
                                        $vterminalName = 'TERMINAL' . $vstartcode . 'VIP';
                                        $vterminalCode = $terminalcode . $vsitecode . $vstartcode . 'VIP';
                                        $visVIP = 1;
                                        $ptVIP = 2;
                                        $login = $vterminalCode;
                                        $hashedPassword = '';
                                        $aid = '';
                                        $currency = $_ptcurrency;
                                        $capiusername = $_ptcasinoname;
                                        $capipassword = $_ptsecretkey;
                                        $capiplayername = '';
                                        $capiserverID = '';
                                        $vipterminal = $vterminalCode;

                                        $lname = $lname . 'VIP';
                                        $email = $lname . '@yopmail.com';

                                        //creates VIP terminal account in PT
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($ptvprovider, $ptvserviceID, $pturl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $ptVIP);

                                        //check if VIP terminal account successfully created in PT
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                            $isapisuccess = 1;
                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $ptvserviceID, 1, $vgenpassword, $vgenhashed);
                                            array_push($arrterminalID, $isrecorded);
                                        } else {

                                            //check if terminal account was existing in PT
                                            if ($vplayerResult['ErrorCode'] == 3) {
                                                $vaccountExist = '';
                                                $voldpw = '';

                                                //Call API change Password
                                                $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($ptvprovider, $ptvserviceID, $pturl, $casinoID, $login, $voldpw, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                //verify if API for change password(PT) is successfull
                                                if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                    $isapisuccess = 1;
                                                    $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalCode, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $ptvserviceID, 1, $vgenpassword, $vgenhashed);
                                                    array_push($arrterminalID, $isrecorded);
                                                } else {
                                                    $isapisuccess = 0;
                                                    $errmsg = 'PT ' . $vplayerResult['ErrorMessage'];
                                                }
                                            }
                                        }

                                        //LOG creation of PT Accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $ptvserviceID);
                                    } else {

                                        //check if terminal account was existing in PT
                                        if ($vplayerResult['ErrorCode'] == 3) {
                                            $vaccountExist = '';

                                            $voldpw = '';

                                            //Call API change Password
                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($ptvprovider, $ptvserviceID, $pturl, $casinoID, $login, $voldpw, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                            //verify if API for change password(PT) is successfull
                                            if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) {
                                                $isapisuccess = 1;
                                                $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalCode, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $ptvserviceID, 1, $vgenpassword, $vgenhashed);
                                                array_push($arrterminalID, $isrecorded);
                                            } else {
                                                $isapisuccess = 0;
                                                $errmsg = 'PT ' . $vplayerResult['ErrorMessage'];
                                            }
                                        }

                                        //LOG creation of PT accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $ptvserviceID);
                                    }
                                }
                                $vterminalno++;
                                unset($vserviceID, $rtgvserviceID, $ptvserviceID, $servers, $rtgservers, $ptservers, $vprovider, $rtgvprovider, $ptvprovider);
                            } else {
                                break;
                            }
                        }

                        //Verify if every API Call was successful
                        if ($isapisuccess > 0) {

                            //Verify if every insert in DB was successful
                            if ($isrecorded > 0) {
                                $msg = "Batch Terminal Creation: Success in creating the terminal accounts.";

                                //if new site, upddate its status as active in generatedpasswordbatch table
                                if ($isoldsite == 0) {
                                    $updbatchpwd = $obatch->updateGenPwdBatch($vsiteID, $vgenpwdid);
                                    if (!$updbatchpwd)
                                        $msg = "Batch Terminal Creation: Records unchanged in generatedpasswordbatch";
                                }

                                //insert into audit trail
                                $vdateupdated = $vdate;
                                $vtransdetails = "Site Code " . $vsitecode . " no. of terminals " . $vterminals . " servers ";

                                //sets condition to properly logged in audit trail
                                if ($mgUsedServer == 1)
                                    $vtransdetails = $vtransdetails . 'MG';

                                if ($rtgUsedServer == 1 && $mgUsedServer != 1)
                                    $vtransdetails = $vtransdetails . 'RTG';
                                else if ($rtgUsedServer == 1 && $mgUsedServer == 1)
                                    $vtransdetails = $vtransdetails . ", RTG";

                                if ($ptUsedServer == 1 && $rtgUsedServer != 1)
                                    $vtransdetails = $vtransdetails . 'PT';
                                else if ($ptUsedServer == 1 && $rtgUsedServer == 1)
                                    $vtransdetails = $vtransdetails . ", PT";

                                $vauditfuncID = 34;
                                $obatch->logtoaudit($new_sessionid, $vCreatedByAID, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);

                                //send email alert
                                $vtitle = "Added Terminals";
                                $arraid = array($vCreatedByAID);

                                $raid = $obatch->getfullname($arraid); //get full name of an account
                                $dateformat = date("Y-m-d h:i:s A", strtotime($vdateupdated)); //formats date on 12 hr cycle AM / PM
                                $rsites = $obatch->getsitename($vsiteID);
                                foreach ($rsites as $val) {
                                    $vsitename = $val['SiteName'];
                                    $vposaccno = $val['POS'];
                                }
                                $ctr = 0;
                                while ($ctr < count($raid)) {
                                    $vupdatedby = $raid[$ctr]['Name'];
                                    $ctr++;
                                }

                                $terminalID = implode(",", array_unique($arrterminalID));
                                $vmessage = "
                                            <html>
                                            <head>
                                            <title>$vtitle</title>
                                            </head>
                                            <body>
                                            <br/><br/>
                                            $vtitle
                                            <br/><br/>
                                            Site ID $vsiteID = $vsitename / $vposaccno
                                            <br/>
                                            Number of Terminals = $vterminals
                                            <br />
                                            Terminal ID = $terminalID
                                            <br /><br />
                                            Updated Date : $dateformat
                                            <br/><br/>
                                            Updated By : " . $vupdatedby . "
                                            <br/><br/>
                                            </body>
                                            </html>";
                                $vupdatedby = $_SESSION['uname'];
                                $obatch->emailalerts($vtitle, $grouppegs, $vmessage);
                            }
                            else
                                $msg = "Batch Terminal Creation: Error in creating the terminal ";
                        }
                        else
                            $msg = "Batch Terminal Creation: API Error: " . $errmsg;
                    }
                    else
                        $msg = "Batch Terminal Creation: No available site to get plain and encrypted password.";
                }
                else
                    $msg = "Batch Terminal Creation: Invalid fields.";


                //unset large variables, most especially arrays
                unset($vsiteID, $vterminals, $vsitecode, $vlastterminal, $arrserverID, $arrserverID1, $arrserverID2, $vCreatedByAID, $vStatus, $vterminalno, $arrterminalID, $roldsite, $vgenpwdid, $ctrterminal, $vdateupdated, $vtransdetails, $arraid, $vtitle, $raid, $dateformat, $rsites, $terminalID, $vmessage);

                $nopage = 1;
                $obatch->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../batchterminalcreation.php");
                break;

            case 'GenerateServers':
                //for services --> RTG, MG, PT
                $rserviceAll = array();
                $rresult = $obatch->getallservices();
                //$rserviceAll = array_splice($rresult, 2); //remove pt and mg
                //remove PT
                foreach ($rresult as $result) {
                    if (substr($result['ServiceName'], 0, 2)) {
                        $rserviceAll[] = $result;
                    }
                }
                echo json_encode($rserviceAll);
                unset($rserviceAll);
                $obatch->close();
                exit;
                break;
        }
    }
    //get all other infos, when this combo box was clicked
    elseif (isset($_POST['cmbsitename'])) {
        $vsiteID = $_POST['cmbsitename'];

        /*         * *** get the site name **** */
        $rresult = array();
        $rresult = $obatch->getsitename($vsiteID);
        foreach ($rresult as $row) {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
        }
        if (count($rresult) > 0) {
            $rrecord->SiteName = $rsitename;
            $rrecord->POSAccNo = $rposaccno;
        } else {
            $rrecord->SiteName = "";
            $rrecord->POSAccNo = "";
        }

        /*         * *** get the site code, which will be append to terminal code **** */
        $rsitecode = $obatch->getsitecode($vsiteID);
        $isterminalcode = strstr($rsitecode['SiteCode'], $terminalcode);
        if ($isterminalcode == false) {
            $rrecord->sitecode = $rsitecode['SiteCode'];
        } else {
            $rrecord->sitecode = substr($rsitecode['SiteCode'], strlen($terminalcode));
        }

        /*         * *** get the pass code, which will be used as a password to RTG Terminal Cretion **** */
        $rsites = $obatch->getpasscode($vsiteID);
        $vpasscode = $rsites['PassCode'];

        $rrecord->passcode = $vpasscode;

        /*         * *** get the last terminal code of a particular site **** */
        $rcodelen = strlen($rsitecode['SiteCode']) + 1;
        $rterminalCode = $obatch->getlastID($vsiteID, $rcodelen); //generate the last terminal ID


        $rnoterminal = ereg_replace("[^0-9]", "", $rterminalCode['tc']); //remove all letters from this terminalcode
        $rrecord->lastterminal = $rnoterminal;

        echo json_encode($rrecord);
        unset($rresult);
        $obatch->close();
        exit;
    } else {
        //for services --> RTG, MG, PT
// $rserviceAll = array();
// $rresult = $obatch->getallservices();
// $rserviceAll = array_splice($rresult, 2); //remove pt and mg
// $_SESSION['getservices'] = $rserviceAll;
        //for site listing, every terminals
        $sitewitid = array();
        $_SESSION['siteids'] = $obatch->getallsiteswithid();
    }
} else {
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>
