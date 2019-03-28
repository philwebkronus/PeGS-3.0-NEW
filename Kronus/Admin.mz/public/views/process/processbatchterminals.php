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
if (isset($_SESSION['sessionID'])) 
{
    $new_sessionid = $_SESSION['sessionID'];
} 
else 
{
    $new_sessionid = '';
}

if (isset($_SESSION['accID'])) 
{
    $aid = $_SESSION['accID'];
}

$obatch = new BatchTerminalMgmt($_DBConnectionString[0]);
$connected = $obatch->open();
$nopage = 0;
if ($connected) 
{
    /*********************SESSION CHECKING ************************/
    $isexist = $obatch->checksession($aid);
    if ($isexist == 0) 
    {
        session_destroy();
        $msg = "Not Connected";
        $obatch->close();
        if ($obatch->isAjaxRequest()) 
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }
    $isexistsession = $obatch->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) 
    {
        session_destroy();
        $msg = "Not Connected";
        $obatch->close();
        header("Location: login.php?mess=" . $msg);
    }
    /**********************END SESSION CHECKING *******************/
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $obatch->getDate();

    if (isset($_POST['page'])) 
    {
        $vpage = $_POST['page'];
        switch ($vpage) 
        {
            case 'BatchTerminalCreation':
                if (isset($_POST['cmbsitename']) && isset($_POST['cmbterminals'])
                   && (isset($_POST['optserver']) || isset($_POST['optserver1']) || isset($_POST['optserver2']) || isset($_POST['optserver3'])) || isset($_POST['optserver4']))
                {
                    $vsiteID = $_POST['cmbsitename'];
                    $vterminals = $_POST['cmbterminals']; // no. of terminals that will be created
                    $vsitecode = $_POST['txtsitecode'];
                    $vlastterminal = (int) $_POST['txtlastterm'];

                    //get Server ID
                    if (isset($_POST['optserver'])) 
                    {
                        $arrserverID = $_POST['optserver'];
                    }
                    $vCreatedByAID = $aid; // session account id
                    $vStatus = 1;
                    $vterminalno = $vlastterminal + 1; //add + 1
                    //set all default values for the casinos
                    $country = 'PH';
                    $casinoID = 1;
                    $fname = 'ICSA';
                    $email = '';
                    $dayphone = '3385599';
                    $evephone = '3385599';
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
                    if (isset($roldsite['GeneratedPasswordBatchID']) && $roldsite['GeneratedPasswordBatchID'] > 0) 
                    {
                        $vgenpwdid = $roldsite['GeneratedPasswordBatchID'];
                        $isoldsite = 1;
                    } 
                    else 
                    {
                        $rpwdbatch = $obatch->chkpwdbatch();
                        $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                        $isoldsite = 0;
                    }

                    //check if generatedpasswordbatch returns an ID
                    if ($vgenpwdid > 0) 
                    {
                        $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                        $_CasinoGamingPlayerAPIUB = new CasinoGamingCAPIUB();
                        $isassigned = 0;
                        $isapisuccess = 0;
                        $rtgUsedServer = 0;
                        $habUsedServer = 0;
                        $ebUsedServer = 0; 
                        
                        //loop through number of selected terminals to be assigned in its
                        //chosen casino provider
                        for ($ctrterminal = 1; $ctrterminal <= $vterminals; $ctrterminal++) 
                        {
                            if ($vterminalno < 10)
                                $vstartcode = str_pad($vterminalno, 2, "0", STR_PAD_LEFT); //if terminal no. is less than 10 pad to 0
                            else
                                $vstartcode = $vterminalno;

                            if (isset($arrserverID) && isset($arrserverID[$ctrterminal])) 
                            {
                                $servers = explode(':', $arrserverID[$ctrterminal]);

                                //-----------------  explode radiobox of RTG ----------------------- //
                                $rtggrpid = $servers[2];
                                $rtgvserviceID = $servers[1];
                                $rtgvprovider = $servers[0];
                                $usermode = $obatch->getServiceUserMode($rtgvserviceID);
                                $servicegroupname = $obatch->getServiceGrpNameById($rtgvserviceID);
                                $rtgvprovider = $servicegroupname;

                                //-----------------  radiobox of Habanero ----------------------- //
                                $habgrpid = $servers[2];
                                $habvserviceID = $servers[1];
                                $usermode = $obatch->getServiceUserMode($habvserviceID);
                                $servicegroupname = $obatch->getServiceGrpNameById($habvserviceID);
                                $habvprovider = $servicegroupname;

                                //-----------------  radiobox of e-Bingo ----------------------- //
                                $ebgrpid = $servers[2];
                                $ebvserviceID = $servers[1];
                                $usermode = $obatch->getServiceUserMode($ebvserviceID);
                                $servicegroupname = $obatch->getServiceGrpNameById($ebvserviceID);
                                $ebvprovider = $servicegroupname;
                            }

                            $lname = $vsitecode . $vstartcode;
                            $alias = $vsitecode . $vstartcode;
                            $email = strtolower($lname) . '@yopmail.com';
                            $siteclassid = $obatch->selectsiteclassification($vsiteID);
                            //check if Site is for e-Bingo
                            if ((int) $siteclassid['SiteClassificationID'] == 3) 
                            {
                                //check if casino is e-Bingo
                                if ((int) $usermode != 2) 
                                {
                                    $servicename = $obatch->viewterminalservices(0, $vserviceID);
                                    $errmsg = "Cannot Map " . $servicename[0]['ServiceName'] . " to an e-Bingo site";
                                    $isapisuccess = 0;
                                    $nebingo = false;
                                    break;
                                } 
                                else 
                                {
                                    $nebingo = true;
                                }
                            } 
                            else 
                            {
                                if ((int) $usermode == 2) 
                                {
                                    $servicename = $obatch->viewterminalservices(0, $vserviceID);
                                    $errmsg = "Cannot Map " . $servicename[0]['ServiceName'] . " to a non e-Bingo site";
                                    $isapisuccess = 0;
                                    $nebingo = false;
                                    break;
                                } 
                                else 
                                {
                                    $nebingo = true;
                                }
                            }

                            //it depends on the condition if site is e-Bingo, Platinum or Hybrid
                            if ($nebingo) 
                            {
                                //Check if assigned casino provider is Habanero
                                if (isset($habvprovider) && $habvprovider == "HAB") 
                                {
                                    $habUsedServer = 1;
                                    //get password and encrypted password for Habanero
                                    $vretrievepwd = $obatch->getgeneratedpassword($vgenpwdid, $habgrpid);
                                    $vgenpassword = $vretrievepwd['PlainPassword'];
                                    $vgenhashed = $vretrievepwd['EncryptedPassword'];
                                    $password = $vgenpassword; //casino password
                                    //sets creation of regular terminal account in Habanero
                                    $vterminalName = "TERMINAL" . $vstartcode;
                                    $vterminalCode = $terminalcode . $vsitecode . $vstartcode; //(icsa-) + sitecode + terminalno
                                    $visVIP = 0;
                                    $login = $vterminalCode;
                                    $regterminal = $vterminalCode;
                                    $haburl = $_ServiceAPI[$habvserviceID-1];
                                    $hashedPassword = $password;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = $_HABbrandID;
                                    $capipassword = $_HABapiKey;
                                    $capiplayername = '';
                                    $capiserverID = '';

                                    // EDITED 07/03/2018 BEGIN
                                    // if ($usermode == 0) 
                                    if (($usermode == 0) || ($usermode == 3))
                                    // EDITED 07/03/2018 END
                                    {
                                        //Creates regular terminal account in Habanero
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($habvprovider, $habvserviceID, $haburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP, $usermode);

                                        if ($vplayerResult == NULL) 
                                        { // proceeed if certificate does not match
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($habvprovider, $habvserviceID, $haburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP);
                                        }

                                        if ($vplayerResult['IsSucceed'] != 1) // not successful
                                        {
                                            //Call API to verify if account is already existing in Habanero
                                            $vapiResult = $_CasinoGamingPlayerAPI->validateHabCasinoAccount($haburl, $capiusername, $capipassword, $login, $password);

                                            //Verify if API Call was successful
                                            if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) 
                                            {
                                                // Check if Password does not match, hence exists returns error
                                                if ($vapiResult['Count'] == 0 && $vapiResult['ErrorCode'] == 2)
                                                {
                                                    if (strstr($vapiResult['ErrorMessage'] , "Password does not match") == true)
                                                    {
                                                        //Update Password
                                                        $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($habvprovider, $habvserviceID, $haburl, $habvserviceID, $login, $password, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                        {
                                                            $isapisuccess = 1;
                                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $habvserviceID, 1, $password, $password);
                                                            array_push($arrterminalID, $isrecorded);
                                                            $vplayerResult['IsSucceed'] = true;
                                                            $vplayerResult['Added'] = true;
                                                        }
                                                        else 
                                                        {
                                                            $isapisuccess = 0;
                                                            $errmsg = "Error in Changing Terminal Password";
                                                            $vplayerResult = array('IsSucceed' => false, 'Added' => false, 'ErrorCode' => 2);
                                                        }
                                                    }
                                                }
                                            }                                            
                                        }
                                        else // successful
                                        {
                                            $isapisuccess = 1;
                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $habvserviceID, 1, $password, $password);
                                            array_push($arrterminalID, $isrecorded);
                                            $vplayerResult['IsSucceed'] = true;
                                            $vplayerResult['Added'] = true;
                                            //LOG creation of Habanero Accounts
                                            $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $habvserviceID);
                                        }

                                        //check if regular terminal account was successfully created in Habanero
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                        {
                                            /*************************** CREATE VIP ***********************************/
                                            $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                            $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                            $visVIP = 1;
                                            $login = $vterminalCode;
                                            $vipterminal = $vterminalCode;

                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($habvprovider, $habvserviceID, $haburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP, $usermode);
                                            if ($vplayerResult == NULL) 
                                            { // proceeed if certificate does not match
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($habvprovider, $habvserviceID, $haburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP);
                                            }
                                            
                                            if ($vplayerResult['IsSucceed'] != 1) // not successful
                                            {
                                                //Call API to verify if account is already existing in Habanero
                                                $vapiResult = $_CasinoGamingPlayerAPI->validateHabCasinoAccount($haburl, $capiusername, $capipassword, $login, $password);

                                                //Verify if API Call was successful
                                                if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) 
                                                {
                                                    // Check if Password does not match, hence exists returns error
                                                    if ($vapiResult['Count'] == 0 && $vapiResult['ErrorCode'] == 2)
                                                    {
                                                        if (strstr($vapiResult['ErrorMessage'] , "Password does not match") == true)
                                                        {
                                                            //Update Password
                                                            $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($habvprovider, $habvserviceID, $haburl, $habvserviceID, $login, $password, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                            if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                            {
                                                                $isapisuccess = 1;
                                                                $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $habvserviceID, 1, $password, $password);
                                                                array_push($arrterminalID, $isrecorded);
                                                                $vplayerResult['IsSucceed'] = true;
                                                                $vplayerResult['Added'] = true;
                                                            }
                                                            else 
                                                            {
                                                                $isapisuccess = 0;
                                                                $errmsg = "Error in Changing Terminal Password";
                                                                $vplayerResult = array('IsSucceed' => false, 'Added' => false, 'ErrorCode' => 2);
                                                            }
                                                        }
                                                    }
                                                }                                                
                                            }
                                            else // successful
                                            {
                                                $isapisuccess = 1;
                                                $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $habvserviceID, 1, $password, $password);
                                                array_push($arrterminalID, $isrecorded);
                                                $vplayerResult['IsSucceed'] = true;
                                                $vplayerResult['Added'] = true;                                                
                                                //LOG creation of Habanero Accounts
                                                $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $habvserviceID);
                                            }
                                        } 
                                    }
                                }

                                //Check if assigned casino provider is e-Bingo
                                if (isset($ebvprovider) && $ebvprovider == "EB") 
                                {
                                    $ebUsedServer = 1;
                                    //set password and encrypted password for e-Bingo
                                    //$vretrievepwd = $obatch->getgeneratedpassword($vgenpwdid, $habgrpid);
                                    $vgenpassword = '';
                                    $vgenhashed = '';;
                                    $password = '';
                                    //sets creation of regular terminal account in Habanero
                                    $vterminalName = "TERMINAL" . $vstartcode;
                                    $vterminalCode = $terminalcode . $vsitecode . $vstartcode; //(icsa-) + sitecode + terminalno
                                    $visVIP = 0;
                                    $login = $vterminalCode;
                                    $regterminal = $vterminalCode;
                                    $eburl = $_ServiceAPI[$ebvserviceID-1];
                                    $hashedPassword = $password;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';
                                    
                                    // EDITED 07/03/2018 BEGIN
                                    //if (($usermode == 0) || ($usermode == 4))
                                    if (($usermode == 0) || ($usermode == 3) || ($usermode == 4))
                                    // EDITED 07/03/2018 END
                                    {
                                        //Creates regular terminal account in e-Bingo
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($ebvprovider, $ebvserviceID, $eburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP, $usermode);

                                        if ($vplayerResult == NULL) 
                                        { // proceeed if certificate does not match
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($ebvprovider, $ebvserviceID, $eburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP);
                                        }

                                        $isapisuccess = 1;
                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $ebvserviceID, 1, $password, $password);
                                        array_push($arrterminalID, $isrecorded);
                                        $vplayerResult['IsSucceed'] = true;
                                        $vplayerResult['Added'] = true;
                                        //LOG creation of e-Bingo Accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $ebvserviceID);
                                        
                                        //check if regular terminal account was successfully created in e-Bingo
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                        {
                                            /*************************** CREATE VIP ***********************************/
                                            $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                            $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                            $visVIP = 1;
                                            $login = $vterminalCode;
                                            $vipterminal = $vterminalCode;

                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($ebvprovider, $ebvserviceID, $eburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP, $usermode);
                                            if ($vplayerResult == NULL) 
                                            { // proceeed if certificate does not match
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($ebvprovider, $ebvserviceID, $eburl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $visVIP);
                                            }
                                            
                                            $isapisuccess = 1;
                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $ebvserviceID, 1, $password, $password);
                                            array_push($arrterminalID, $isrecorded);
                                            $vplayerResult['IsSucceed'] = true;
                                            $vplayerResult['Added'] = true;                                                
                                            //LOG creation of e-Bingo Accounts
                                            $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $ebvserviceID);
                                        } 
                                    }
                                }

                                //Check if assigned casino provider is RTG
                                if (isset($rtgvprovider) && $rtgvprovider == "RTG") 
                                {
                                    $rtgUsedServer = 1;
                                    $certpath = RTGCerts_DIR . $rtgvserviceID . '/cert.pem';
                                    $keypath = RTGCerts_DIR . $rtgvserviceID . '/key.pem';
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

                                    $_RealtimeGamingCashierAPI = new RealtimeGamingCashierAPI($cashierurl, $certpath, $keypath, '');
                                    
                                    // EDITED CCT 07/03/2018 BEGIN
                                    if ($usermode == 1) 
                                    {
                                        $vplayerResult = array('IsSucceed' => true);
                                    }

                                    //if ($usermode == 0) 
                                    else if (($usermode == 0) || ($usermode == 3))
                                    {
                                        $PID = $_RealtimeGamingCashierAPI->GetPIDFromLogin($login);
                                        if (count($PID['GetPIDFromLoginResult'])<=0)
                                        {
                                            //Creates regular terminal account in RTG
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                            if ($vplayerResult == NULL) 
                                            { // proceeed if certificate does not match
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0);
                                            }
                                        }
                                        else
                                        {                                           
                                            //Call API to get Account Info
                            //              if ($usermode == 0) {
                                            //$vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider, $usermode);
                                            $terminalID = $obatch->getTerminalIDbyTerminalCode($login);
                                            if ($terminalID['TerminalID'] != false)
                                            {
                                                $vplayerResult = $obatch->getServicePasssword($terminalID, $vserviceID);
                            //                                                if ($vplayerResult == NULL) { // proceeed if certificate does not match
                            //                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl,$rtgvprovider, $password);
                            //                                                }
                            //                                            }
                            //                                            if ($usermode == 2) {
                                                //$vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider, $usermode);
                            //                                            }

                                                //check if exists in RTG
                                                if (isset($vplayerResult['ServicePassword']) && $vplayerResult['ServicePassword'] <> null) 
                                                {
                                                    $vrtgoldpwd = $vplayerResult['ServicePassword'];

                                                    if ($usermode == 1) 
                                                    {
                                                        $vplayerResult = array('IsSucceed' => true);
                                                    }

                                                    //if ($usermode == 0) 
                                                    else if (($usermode == 0) || ($usermode == 3))
                                                    {
                                                        //Call API Change Password
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        if ($vplayerResult == NULL) 
                                                        { // proceeed if certificate does not match
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                        }
                                                    }

                                                    //if ($usermode == 2) 
                                                    else if ($usermode == 2) 
                                                    {
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    }

                                                    //verify if API for change password (RTG) is successfull
                                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                                    {
                                                        $isapisuccess = 1;
                                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                        array_push($arrterminalID, $isrecorded);
                                                    } 
                                                    else 
                                                    {
                                                        $isapisuccess = 0;
                                                        $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                } 
                                                else 
                                                {
                                                    $isapisuccess = 0;
                                                    $errmsg = "Error in Changing Terminal Password";
                                                }
                                                $vplayerResult['IsSucceed'] == true;
                                                $vplayerResult['Added'] == true;
                                            } 
                                            else 
                                            {
                                                $vplayerResult = array('IsSucceed' => false, 'Added' => false, 'ErrorCode' => 5);
                                            } 
                                        }
                                    }

                                    //if ($usermode == 2) 
                                    else if ($usermode == 2) 
                                    {
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                    }
                                    // EDITED CCT 07/03/2018 END

                                    //check if regular terminal account was successfully created in RTG
                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                    {
                                        $isapisuccess = 1;
                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                        array_push($arrterminalID, $isrecorded);
                                        /*************************** CREATE VIP ***********************************/
                                        $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                        $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                        $visVIP = 1;
                                        $login = $vterminalCode;
                                        $vipterminal = $vterminalCode;

                                        if ($usermode == 1) 
                                        {
                                            $vplayerResult = array('IsSucceed' => true);
                                        }

                                        //if ($usermode == 0) 
                                        else if (($usermode == 0) || ($usermode == 3))
                                        {
                                            $PID = $_RealtimeGamingCashierAPI->GetPIDFromLogin($login);
                                            if (count($PID['GetPIDFromLoginResult'])<=0)
                                            {
                                                //creates vip terminal account in RTG
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                                if ($vplayerResult == NULL) 
                                                { // proceeed if certificate does not match
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1);
                                                }
                                            }
                                            else
                                            {
                                                $vplayerResult['IsSucceed']=false; 
                                                $vplayerResult['ErrorCode']= 10;
                                            }
                                        }

                                        //if ($usermode == 2) 
                                        else if ($usermode == 2) 
                                        {
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                        }

                                        //check if vip terminal account was successfully created in RTG
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                        {
                                            $isapisuccess = 1;
                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                            array_push($arrterminalID, $isrecorded);
                                        } 
                                        else 
                                        {
                                            //if account does not created in casino's RTG, check the errorcode is exists
                                            if ($vplayerResult['ErrorCode'] == 5 || $vplayerResult['ErrorID'] == 5 || $vplayerResult['ErrorCode']==10) 
                                            {
                                                //Call API to get Account Info
                            //                                                if ($usermode == 0) {
                            //                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider, $usermode);
                            //                                                    if ($vplayerResult == NULL) { // proceeed if certificate does not match
                            //                                                        $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider);
                            //                                                    }
                            //                                                }
                            //                                                if ($usermode == 2) {
                            //                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider, $usermode);
                            //                                                }
                                                $terminalID = $obatch->getTerminalIDbyTerminalCode($login);
                                                if ($terminalID['TerminalID'] != false)
                                                {
                                                    $vplayerResult = $obatch->getServicePasssword($terminalID, $vserviceID);
                                                    //check if exists in RTG
                                                    if (isset($vplayerResult['AccountInfo']['password']) && $vplayerResult['AccountInfo']['password'] <> null) 
                                                    {
                                                        $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                        if ($usermode == 1) 
                                                        {
                                                            $vplayerResult = array('IsSucceed' => true);
                                                        }

                                                        //if ($usermode == 0) 
                                                        else if (($usermode == 0) || ($usermode == 3))
                                                        {
                                                            //Call API Change Password
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                            if ($vplayerResult == NULL) 
                                                            { // proceeed if certificate does not match
                                                                $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                            }
                                                        }

                                                        //if ($usermode == 2) 
                                                        else if ($usermode == 2) 
                                                        {
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        }

                                                        //verify if API for change password (RTG) is successfull
                                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                                        {
                                                            $isapisuccess = 1;
                                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                            array_push($arrterminalID, $isrecorded);
                                                        } 
                                                        else 
                                                        {
                                                            $isapisuccess = 0;
                                                            $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                        }
                                                    } 
                                                    else 
                                                    {
                                                        $isapisuccess = 0;
                                                        $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                }
                                                else 
                                                {
                                                    $isapisuccess = 0;
                                                    $errmsg = "Create Player Full";
                                                }
                                            } 
                                            else 
                                            {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        }

                                        //LOG creation of RTG Accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                    } 
                                    else 
                                    {
                                        //check if terminal account was existing in RTG, (ErrorCode must be 5)
                                        if (isset($vplayerResult['ErrorCode']) && $vplayerResult['ErrorCode'] == 5 ||
                                            isset($vplayerResult['ErrorID']) && $vplayerResult['ErrorID'] == 5) 
                                        {
                                            //Call API to get Account Info
                            //                                            if ($usermode == 0) {
                            //                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider, $usermode);
                            //                                                if ($vplayerResult == NULL) { // proceeed if certificate does not match
                            //                                                 $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider);
                            //                                                }
                            //                                            }
                            //                                            if ($usermode == 2) {
                            //                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $rtgvprovider, $usermode);
                            //                                            }
                                            $terminalID = $obatch->getTerminalIDbyTerminalCode($login);
                                            if ($terminalID['TerminalID'] != false)
                                            {
                                                $vplayerResult = $obatch->getServicePasssword($terminalID['TerminalID'], $vserviceID);
                                                //check if exists in RTG
                                                if (isset($vplayerResult) && $vplayerResult <> null) 
                                                {
                                                    $vrtgoldpwd = $vplayerResult;

                                                    if ($usermode == 1) 
                                                    {
                                                        $vplayerResult = array('IsSucceed' => true);
                                                    }

                                                    //if ($usermode == 0) 
                                                    else if (($usermode == 0) || ($usermode == 3))
                                                    {
                                                        //Call API Change Password
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        if ($vplayerResult == NULL) 
                                                        { // proceeed if certificate does not match
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                        }
                                                    }

                                                    //if ($usermode == 2) 
                                                    else if ($usermode == 2) 
                                                    {
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    }

                                                    //verify if API for change password (RTG) is successfull
                                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                                    {
                                                        $isapisuccess = 1;
                                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                        array_push($arrterminalID, $isrecorded);
                                                    } 
                                                    else 
                                                    {
                                                        $isapisuccess = 0;
                                                        $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                } 
                                                else 
                                                {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } 
                                            else 
                                            {
                                                $isapisuccess = 0;
                                                $errmsg = "Create Player Full";
                                            }
                                        } 
                                        else 
                                        {
                                            $isapisuccess = 0;
                                            $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                        }
                                    }

                                    //LOG creation of RTG Accounts
                                    $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                }

                                //Check if assigned casino provider is RTG
                                if (isset($rtgvprovider) && $rtgvprovider == "RTG2") 
                                {
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

                                    if ($usermode == 1) 
                                    {
                                        $vplayerResult = array('IsSucceed' => true);
                                    }

                                    //if ($usermode == 0) 
                                    else if (($usermode == 0) || ($usermode == 3))
                                    {
                                        //Creates regular terminal account in RTG
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                        if ($vplayerResult == NULL) 
                                        { // proceeed if certificate does not match
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0);
                                        }
                                    }

                                    //if ($usermode == 2) 
                                    else if ($usermode == 2) 
                                    {
                                        $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 0, $usermode);
                                    }

                                    //check if regular terminal account was successfully created in RTG
                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                    {
                                        $isapisuccess = 1;
                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                        array_push($arrterminalID, $isrecorded);
                                        /*************************** CREATE VIP ***********************************/
                                        $vterminalName = "TERMINAL" . $vstartcode . "VIP";
                                        $vterminalCode = $terminalcode . $vsitecode . $vstartcode . "VIP"; //(icsa-) + sitecode + terminalno
                                        $visVIP = 1;
                                        $login = $vterminalCode;
                                        $vipterminal = $vterminalCode;

                                        if ($usermode == 1) 
                                        {
                                            $vplayerResult = array('IsSucceed' => true);
                                        }

                                        //if ($usermode == 0 || $usermode == 2) 
                                        else if (($usermode == 0 || $usermode == 3))
                                        {
                                            //creates vip terminal account in RTG
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                            if ($vplayerResult == NULL) 
                                            { // proceeed if certificate does not match
                                                $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1);
                                            }
                                        }

                                        //if ($usermode == 2) 
                                        else if ($usermode == 2) 
                                        {
                                            $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($rtgvprovider, $rtgvserviceID, $rtgurl, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, 1, $usermode);
                                        }

                                        //check if vip terminal account was successfully created in RTG
                                        if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                        {
                                            $isapisuccess = 1;

                                            //if ($usermode == 0 || $usermode == 2) 
                                            if ($usermode == 0 || $usermode == 2 || $usermode == 3) 
                                            {
                                                $pid = $vplayerResult['PID'];
                                                $playerClassID = 2;
                                                $_CasinoGamingPlayerAPI->ChangePlayerClassification($rtgvprovider, $url, $pid, $playerClassID, $userID, $rtgvserviceID);
                                            }

                                            $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                            array_push($arrterminalID, $isrecorded);
                                        } 
                                        else 
                                        {
                                            //if account does not created in casino's RTG, check the errorcode is exists
                                            if ($vplayerResult['ErrorCode'] == 5 || $vplayerResult['ErrorID'] == 5) 
                                            {
                                                //Call API to get Account Info
                                                //if ($usermode == 0) 
                                                if (($usermode == 0) || ($usermode == 3))
                                                {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                    if ($vplayerResult == NULL) 
                                                    { // proceeed if certificate does not match
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password);
                                                    }
                                                }
                                                
                                                //if ($usermode == 2) 
                                                else if ($usermode == 2) 
                                                {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                }

                                                //check if exists in RTG
                                                if (isset($vplayerResult['AccountInfo']['password']) && $vplayerResult['AccountInfo']['password'] <> null) 
                                                {
                                                    $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                    if ($usermode == 1) 
                                                    {
                                                        $vplayerResult = array('IsSucceed' => true);
                                                    }

                                                    //if ($usermode == 0) 
                                                    else if (($usermode == 0) || ($usermode == 3))
                                                    {
                                                        //Call API Change Password
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        if ($vplayerResult == NULL) 
                                                        { // proceeed if certificate does not match
                                                            $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                        }
                                                    }

                                                    //if ($usermode == 2) 
                                                    else if ($usermode == 2) 
                                                    {
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    }

                                                    //verify if API for change password (RTG) is successfull
                                                    if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                                    {
                                                        $isapisuccess = 1;
                                                        $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                        array_push($arrterminalID, $isrecorded);
                                                    } 
                                                    else 
                                                    {
                                                        $isapisuccess = 0;
                                                        $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                    }
                                                } 
                                                else 
                                                {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } 
                                            else 
                                            {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        }

                                        //LOG creation of RTG Accounts
                                        $logterminals = $obatch->logbatchterminals($vsiteID, $vipterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                    } 
                                    else 
                                    {
                                        //check if terminal account was existing in RTG, (ErrorCode must be 5)
                                        if (isset($vplayerResult['ErrorCode']) && $vplayerResult['ErrorCode'] == 5 ||
                                            isset($vplayerResult['ErrorID']) && $vplayerResult['ErrorID'] == 5) 
                                        {
                                            //Call API to get Account Info
                                            //if ($usermode == 0) 
                                            if (($usermode == 0) || ($usermode == 3))
                                            {
                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                                if ($vplayerResult == NULL) 
                                                { // proceeed if certificate does not match
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password);
                                                }
                                            }
                                            
                                            //if ($usermode == 2) 
                                            else if ($usermode == 2) 
                                            {
                                                $vplayerResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $rtgvserviceID, $cashierurl, $password, $usermode);
                                            }

                                            //check if exists in RTG
                                            if (isset($vplayerResult['AccountInfo']['password']) && $vplayerResult['AccountInfo']['password'] <> null) 
                                            {
                                                $vrtgoldpwd = $vplayerResult['AccountInfo']['password'];

                                                if ($usermode == 1) 
                                                {
                                                    $vplayerResult = array('IsSucceed' => true);
                                                }

                                                //if ($usermode == 0 || $usermode == 2) 
                                                else if ($usermode == 0 || $usermode == 3) 
                                                {
                                                    //Call API Change Password
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    if ($vplayerResult == NULL) 
                                                    { // proceeed if certificate does not match
                                                        $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                    }
                                                }

                                                //if ($usermode == 2) 
                                                else if ($usermode == 2) 
                                                {
                                                    $vplayerResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($rtgvprovider, $rtgvserviceID, $rtgurl, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                }

                                                //verify if API for change password (RTG) is successfull
                                                if (isset($vplayerResult['IsSucceed']) && $vplayerResult['IsSucceed'] == true) 
                                                {
                                                    $isapisuccess = 1;
                                                    $isrecorded = $obatch->createbatchterminals($isapisuccess, $vterminalName, $vterminalCode, $vsiteID, 1, $vCreatedByAID, $visVIP, $rtgvserviceID, 1, $vgenpassword, $vgenhashed);
                                                    array_push($arrterminalID, $isrecorded);
                                                } 
                                                else 
                                                {
                                                    $isapisuccess = 0;
                                                    $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                                }
                                            } 
                                            else 
                                            {
                                                $isapisuccess = 0;
                                                $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                            }
                                        } 
                                        else 
                                        {
                                            $isapisuccess = 0;
                                            $errmsg = "RTG " . $vplayerResult['ErrorMessage'];
                                        }
                                    }

                                    //LOG creation of RTG Accounts
                                    $logterminals = $obatch->logbatchterminals($vsiteID, $regterminal, $isapisuccess, $vCreatedByAID, $rtgvserviceID);
                                }

                                $vterminalno++;
                                unset($vserviceID, $rtgvserviceID, $servers, $rtgservers, $vprovider, $rtgvprovider);
                            } 
                            else 
                            {
                                break;
                            }
                        }

                        //Verify if every API Call was successful
                        if ($isapisuccess > 0) 
                        {
                            //Verify if every insert in DB was successful
                            if ($isrecorded > 0) 
                            {
                                $msg = "Batch Terminal Creation: Success in creating the terminal accounts.";

                                //if new site, upddate its status as active in generatedpasswordbatch table
                                if ($isoldsite == 0) 
                                {
                                    $updbatchpwd = $obatch->updateGenPwdBatch($vsiteID, $vgenpwdid);
                                    if (!$updbatchpwd)
                                        $msg = "Batch Terminal Creation: Records unchanged in generatedpasswordbatch";
                                }

                                //insert into audit trail
                                $vdateupdated = $vdate;
                                $vtransdetails = "Site Code " . $vsitecode . " no. of terminals " . $vterminals . " servers ";

                                //sets condition to properly logged in audit trail
                                if ($habUsedServer == 1)
                                {
                                    $vtransdetails = $vtransdetails . ' HAB';
                                }

                                if ($ebUsedServer == 1)
                                {
                                    if (($habUsedServer == 1) || ($rtgUsedServer == 1))
                                    {
                                        $vtransdetails = $vtransdetails . ', EB';
                                    }
                                    else
                                    {
                                        $vtransdetails = $vtransdetails . ' EB';
                                    }    
                                }

                                if ($rtgUsedServer == 1)
                                {
                                    if (($habUsedServer == 1) || ($ebUsedServer == 1))
                                    {    
                                        $vtransdetails = $vtransdetails . ', RTG';
                                    }
                                    else
                                    {
                                        $vtransdetails = $vtransdetails . ' RTG';                                        
                                    }
                                }

                                $vauditfuncID = 34;
                                $obatch->logtoaudit($new_sessionid, $vCreatedByAID, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);

                                //send email alert
                                $vtitle = "Added Terminals";
                                $arraid = array($vCreatedByAID);

                                $raid = $obatch->getfullname($arraid); //get full name of an account
                                $dateformat = date("Y-m-d h:i:s A", strtotime($vdateupdated)); //formats date on 12 hr cycle AM / PM
                                $rsites = $obatch->getsitename($vsiteID);
                                foreach ($rsites as $val) 
                                {
                                    $vsitename = $val['SiteName'];
                                    $vposaccno = $val['POS'];
                                }
                                $ctr = 0;
                                while ($ctr < count($raid)) 
                                {
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
                //for services --> RTG
                $rserviceAll = array();
                $rresult = $obatch->getallservices();
                //$rserviceAll = array_splice($rresult, 2); 
                foreach ($rresult as $result) 
                {
                    if (substr($result['ServiceName'], 0, 2)) 
                    {
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
    elseif (isset($_POST['cmbsitename'])) 
    {
        $vsiteID = $_POST['cmbsitename'];
        /***** get the site name *****/
        $rresult = array();
        $rresult = $obatch->getsitename($vsiteID);
        foreach ($rresult as $row) 
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
        }
        if (count($rresult) > 0) 
        {
            $rrecord->SiteName = $rsitename;
            $rrecord->POSAccNo = $rposaccno;
        } 
        else 
        {
            $rrecord->SiteName = "";
            $rrecord->POSAccNo = "";
        }

        /***** get the site code, which will be append to terminal code *****/
        $rsitecode = $obatch->getsitecode($vsiteID);
        $isterminalcode = strstr($rsitecode['SiteCode'], $terminalcode);
        if ($isterminalcode == false) 
        {
            $rrecord->sitecode = $rsitecode['SiteCode'];
        } 
        else 
        {
            $rrecord->sitecode = substr($rsitecode['SiteCode'], strlen($terminalcode));
        }

        /***** get the pass code, which will be used as a password to RTG Terminal Cretion *****/
        $rsites = $obatch->getpasscode($vsiteID);
        $vpasscode = $rsites['PassCode'];
        $rrecord->passcode = $vpasscode;

        /***** get the last terminal code of a particular site *****/
        $rcodelen = strlen($rsitecode['SiteCode']) + 1;
        $rterminalCode = $obatch->getlastID($vsiteID, $rcodelen); //generate the last terminal ID
        $rnoterminal = ereg_replace("[^0-9]", "", $rterminalCode['tc']); //remove all letters from this terminalcode
        $rrecord->lastterminal = $rnoterminal;

        echo json_encode($rrecord);
        unset($rresult);
        $obatch->close();
        exit;
    } 
    else 
    {
        //for site listing, every terminals
        $sitewitid = array();
        $_SESSION['siteids'] = $obatch->getallsiteswithid();
    }
}
else 
{
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>