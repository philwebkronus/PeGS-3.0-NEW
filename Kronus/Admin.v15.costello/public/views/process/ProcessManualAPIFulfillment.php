<?php

/* Created by : Gerardo V. Jagolino Jr.
 * Date Created : Apr 11, 2013
 */

include __DIR__ . "/../sys/class/ManualAPIFulfillment.class.php";
include __DIR__ . "/../sys/class/LoyaltyUBWrapper.class.php";
require __DIR__ . '/../sys/core/init.php';
include __DIR__ . '/../sys/class/CasinoGamingCAPI.class.php';
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

$maf = new ManualAPIFulfillment($_DBConnectionString[0]);
$maf2 = new ManualAPIFulfillment($_DBConnectionString[2]);
$CasinoGamingCAPI = new CasinoGamingCAPI();
$loyalty = new LoyaltyUBWrapper();
$connected = $maf->open();
$connected2 = $maf2->open();
if ($connected && $connected2) {
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $maf->getDate();
    /*     * ******** SESSION CHECKING ********* */
    $isexist = $maf->checksession($aid);
    if ($isexist == 0) {
        session_destroy();
        $msg = "Not Connected";
        $maf->close();
        if ($maf->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }

    $isexistsession = $maf->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) {
        session_destroy();
        $msg = "Not Connected";
        $maf->close();
        if ($maf->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    } else {
        //get all sites
        $sitelist = array();
        $sitelist = $maf->getallsites();
        $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection
    }
    /*     * ******** END SESSION CHECKING ********* */

    if (isset($_POST['page'])) {
        $vpage = $_POST['page'];
        switch ($vpage) {
            //Get card by transaction logs info
            case "GetLoyaltyCard":
                //get card info and add other detail to show casino information
                //validate if site dropdown box was selected
                $terminal = $_POST['cmbterm'];
                $txtusername = $_POST['txtusername'];
                $usermode = $_POST['txtusermode'];

                if ($usermode == 'terminal') {
                    //get details from pending terminal trans table
                    $trlc = $maf->getTransactionReferenceIDterminal($terminal);
                }
                if ($usermode == 'user') {
                    //get details from pending user trans table
                    $trlc = $maf->getTransactionReferenceIDuser($terminal);
                }

                foreach ($trlc as $value) {
                    $transactionrefid = $value['TransactionReferenceID'];
                    $transactionreqid = $value['TransactionRequestLogID'];
                    $cardnumber = $value['LoyaltyCardNumber'];
                    $source = $value['RequestSource'];
                }

                //
                switch ($source) {
                    case 0://cashier
                        $transreqlog = $maf->gettransactionRequestlogs($transactionreqid);
                        if (empty($transreqlog)) {
                            $newarray = null;
                        } else {
                            foreach ($transreqlog as $row) {
                                $amount = $row['Amount'];
                                $transrefid = $row['TransactionRequestLogID'];
                                $transtype = $row['TransactionType'];
                                $serviceid = $row['ServiceID'];
                                $usermode = $row['UserMode'];
                            }
                        }


                        break;

//                    case 1://launchpad
//                        $transreqlog = $maf->gettransactionRequestlogslp($transactionrefid);
//
//                        if (empty($transreqlog)) {
//                            $newarray = null;
//                        } else {
//                            foreach ($transreqlog as $row) {
//                                $amount = $row['Amount'];
//                                $transrefid = $row['TransactionReferenceID'];
//                                $transtype = $row['TransactionType'];
//                                $serviceid = $row['ServiceID'];
//                                $usermode = $row['UserMode'];
//                                $transsummaryid = $row['TransactionSummaryID'];
//                            }
//                        }
//                        break;
                }

                //check if transactionrequestlog result
                //show transaction source
                if (!empty($transreqlog)) {
                    if ($source == 0) {
                        $source = 'Cashier';
                    } else if ($source == 1) {
                        $source = 'Launchpad';
                    }

                    //show user mode
                    if ($usermode == 0 || $usermode == 2) {
                        $usermode = 'Terminal Based';
                    } else if ($usermode == 1) {
                        $usermode = 'User Based';
                    }

                    //show transtype
                    switch ($transtype) {
                        case 'D':
                            $transtype = 'Deposit';
                            break;

                        case 'R':
                            $transtype = 'Reload';
                            break;

                        case 'W':
                            $transtype = 'Withdraw';
                            break;

                        case 'RD':
                            $transtype = 'ReDeposit';
                            break;
                    }

                    $servicename = $maf->getServiceName($serviceid);
                    foreach ($servicename as $rowz) {
                        $servicename = $rowz['ServiceName'];
                    }

                    $login = $maf->getTerminalCode($terminal);
                    $login = $login['TerminalCode'];


                    $newarray = array(
                        "Casino" => "$servicename",
                        'UserName' => "$login",
                        "CardNumber" => "$cardnumber",
                        "ServiceID" => "$serviceid",
                        "TransRefID" => "$transrefid",
                        "Login" => "$txtusername",
                        "UserMode" => "$usermode",
                        "TransType" => "$transtype",
                        "Source" => "$source",
                        "Amount" => "$amount",
                    );
                }
                $_SESSION['ServiceID'] = $serviceid;
                //check if casino information is empty
                if (count($newarray) > 0) {
                    if ($newarray == null) {
                        echo "Manual Casino Fulfillment: Transaction Request Logs Empty";
                    } else {
                        echo json_encode($newarray);
                    }
                } else {
                    echo "Manual Casino Fulfillment: Transaction Request Logs Empty";
                }


                unset($newarray);
                $maf->close();
                exit;
                break;

            //Check terminalif has pending transaction
            case "CheckPending":
                //Check Pending transactions in pending user and terminal table
                if (isset($_POST['cmbsite']) && isset($_POST['cmbterm'])) {
                    $site = $_POST['cmbsite'];
                    $terminal = $_POST['cmbterm'];

                    $ptrans = $maf->checkpendingusertransactions($terminal);

                    $ptrans = $ptrans['chckpndustrans'];

                    if ($ptrans > 0) {
                        $ptrans = 'user';
                        echo json_encode($ptrans);
                    } else {
                        $ptrans = $maf->checkpendingterminaltransactions($terminal);

                        $ptrans = $ptrans['chckpndtertrans'];

                        if ($ptrans > 0) {
                            $ptrans = 'terminal';
                            echo json_encode($ptrans);
                        } else {
                            echo json_encode($ptrans);
                        }
                    }
                } else {
                    $ptrans = 'Please Select Site and Terminal';
                    echo json_encode($ptrans);
                }
                unset($ptrans);
                $maf->close();
                exit;
                break;

            //Get casino array and user info using membership card
            case "GetCardInfo":
                //check loyalty card number and card information    
                $terminal = $_POST['cmbterm'];
                $usermode = $_POST['txtusermode'];
                $site = $_POST['cmbsite'];
                $terminalCode = $maf->getTerminalCode($terminal);

                if (isset($_POST['cmbsite']) && isset($_POST['cmbterm'])) {
                    //check if usermode is terminal based
                    if ($usermode == 'terminal') {
                        $trlc = $maf->getTransactionReferenceIDterminal($terminal);
                    }
                    //check if usermode is user based
                    elseif ($usermode == 'user') {
                        $trlc = $maf->getTransactionReferenceIDuser($terminal);
                    }

                    //check if pending transaction result is empty
                    if (empty($trlc)) {
                        $loyaltyResult = 'Manual Casino Fulfillment: No Pending Transactions Found';

                        echo "$loyaltyResult";
                    } else {
                        foreach ($trlc as $value) {
                            $transactionrefid = $value['TransactionReferenceID'];
                            $cardnumber = $value['LoyaltyCardNumber'];
                            $source = $value['RequestSource'];
                            $serviceid = $value['ServiceID'];
                        }

                        $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);

                        $obj_result = json_decode($loyaltyResult);

                        $casinoarray_count = count($obj_result->CardInfo->CasinoArray);
                        $serviceids = '';
                        $serviceusername = '';
                        $casinoz = array();
                        $usermodeid = $maf->getusermode($serviceid);
                        if ($usermodeid == 2) { // if e-Bingo Terminal
                            $casinoinfo = array(
                                array(
                                    'UserName' => "",
                                    'MobileNumber' => "",
                                    'Email' => "",
                                    'Birthdate' => "",
                                    'Casino' => $serviceid,
                                    'Login' => $terminalCode['TerminalCode'],
                                    'CardNumber' => "",
                                )
                            );
                            echo json_encode($casinoinfo);
                            $_SESSION['MID'] = "";
                            $_SESSION['CasinoArray'] = "";
                            break;
                        }
                        if ($casinoarray_count != 0) {
                            for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                                $casinoz[$ctr] = array(
                                    'ServiceID' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID,
                                    'ServiceUserName' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                                );
                            }
                            $casino2 = $maf->loopAndFindCasinoService($casinoz, 'ServiceID', $serviceid);

                            if (!empty($casino2)) {
                                $casinoinfo = array(
                                    array(
                                        'UserName' => $obj_result->CardInfo->MemberName,
                                        'MobileNumber' => $obj_result->CardInfo->MobileNumber,
                                        'Email' => $obj_result->CardInfo->Email,
                                        'Birthdate' => $obj_result->CardInfo->Birthdate,
                                        'Casino' => $casino2[0]['ServiceID'],
                                        'Login' => $obj_result->CardInfo->CasinoArray[0]->ServiceUsername,
                                        'CardNumber' => $obj_result->CardInfo->CardNumber,
                                    )
                                );
                            } else {
                                $casinoinfo = array(
                                    array(
                                        'UserName' => $obj_result->CardInfo->MemberName,
                                        'MobileNumber' => $obj_result->CardInfo->MobileNumber,
                                        'Email' => $obj_result->CardInfo->Email,
                                        'Birthdate' => $obj_result->CardInfo->Birthdate,
                                        'Casino' => $serviceid,
                                        'Login' => $obj_result->CardInfo->CasinoArray[0]->ServiceUsername,
                                        'CardNumber' => $obj_result->CardInfo->CardNumber,
                                    )
                                );
                            }


                            echo json_encode($casinoinfo);
                            $_SESSION['MID'] = $obj_result->CardInfo->MID;
                            $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                        } else {
                            $services = "Manual Casino Fulfillment: Casino is empty";
                            echo "$services";
                        }
                    }
                }
                unset($loyaltyResult);
                $maf->close();
                exit;
                break;

            //verifies if the transaction was approved or denied
            case 'VerifyCasino':
                //verify casino and transaction search information
                $txtcasino = $_POST['txtcasino'];
                $serviceID = $_POST['txtserviceid'];
                $cmbterm = $_POST['cmbterm'];
                $cmbsite = $_POST['cmbsite'];
                $transtype = $_POST['txttranstype'];
                $transrefid = $_POST['txttransrefid'];
                $source = $_POST['txtsource'];

                //show transaction type
                switch ($transtype) {
                    case 'Deposit':
                        $transtype = 'D';
                        break;

                    case 'Reload':
                        $transtype = 'R';
                        break;

                    case 'Withdraw':
                        $transtype = 'W';
                        break;

                    case 'ReDeposit':
                        $transtype = 'RD';
                        break;
                }

                $tracking1 = '';
                $tracking2 = $transtype;
                $tracking3 = $cmbterm;
                $tracking4 = $cmbsite;

                $usermode = $maf->getusermode($serviceID);
                if ($usermode == 1) {
                    $casino = $_SESSION['CasinoArray'];

                    $casinoarray_count = count($casino);

                    $casinos = array();

                    if ($casinoarray_count != 0) {
                        for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                            $casinos[$ctr] =
                                    array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                        'ServicePassword' => $casino[$ctr]->ServicePassword,
                                        'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                        'ServiceID' => $casino[$ctr]->ServiceID,
                                        'UserMode' => $casino[$ctr]->UserMode,
                                        'isVIP' => $casino[$ctr]->isVIP,
                                        'Status' => $casino[$ctr]->Status
                            );

                            //get details by pick casino
                            $casino2 = $maf->loopAndFindCasinoService($casinos, 'ServiceID', $serviceID);
                        }
                    }
                    $login = $casino2[0]['ServiceUsername'];
                } else {
                    $login = $maf->getTerminalCode($cmbterm);
                    $login = $login['TerminalCode'];
                }


                $servicegroupname = $maf->getServiceGrpNameByName($txtcasino);

                //check if casino is RTG, MG or PT
                switch ($servicegroupname) {
                    case "RTG":

                        //check source if cashier or launchpad
                        if ($source == 'Cashier') {
                            $transactionid = $maf->getTransactionID($serviceID, $cmbterm);
                            $tracking1 = $transactionid['TransactionRequestLogID'];
                        } 
//                        elseif ($source == 'Launchpad') {
//                            $transactionid = $maf->getTransactionIDLP($serviceID, $cmbterm);
//                            $tracking1 = $transactionid['TransactionRequestLogLPID'];
//                            $tracking1 = "LP" . $tracking1;
//                        }

                        $url = $_ServiceAPI[$serviceID - 1];
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        if ($usermode == 2) {
                            $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4, $usermode);
                        }

                        if ($usermode == 0) {
                            $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4, $usermode);
                            if ($transSearchInfo == NULL) { // proceeed if certificate does not match
                                $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);
                            }
                        }
                        break;

                    case "RTG2":

                        //check source if cashier or launchpad
                        if ($source == 'Cashier') {
                            $transactionid = $maf->getTransactionID($serviceID, $cmbterm);
                            $tracking1 = $transactionid['TransactionRequestLogID'];
                        } 
//                        elseif ($source == 'Launchpad') {
//                            $transactionid = $maf->getTransactionIDLP($serviceID, $cmbterm);
//                            $tracking1 = $transactionid['TransactionRequestLogLPID'];
//                            $tracking1 = "LP" . $tracking1;
//                        }

                        $url = $_ServiceAPI[$serviceID - 1];
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);
                        break;
                    case "MG":

                        //check source if cashier or launchpad
                        if ($source == 'Cashier') {
                            $transactionid = $maf->getServiceTransactionID($transrefid);
                            $tracking4 = $transactionid['ServiceTransactionID'];
                        } 
//                        elseif ($source == 'Launchpad') {
//                            $transactionid = $maf->getServiceTransactionIDLP($transrefid);
//                            $tracking4 = $transactionid['ServiceTransactionID'];
//                        }

                        $_MGCredentials = $_ServiceAPI[$serviceID - 1];
                        list($mgurl, $mgserverID) = $_MGCredentials;
                        $url = $mgurl;
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = $mgserverID;

                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);

                        break;
                    case "PT":

                        //check source if cashier or launchpad
                        if ($source == 'Cashier') {
                            $transactionid = $maf->getTransactionID($serviceID, $cmbterm);
                            $tracking1 = $transactionid['TransactionRequestLogID'];
                        } 
//                        elseif ($source == 'Launchpad') {
//                            $transactionid = $maf->getTransactionIDLP($serviceID, $cmbterm);
//                            $tracking1 = $transactionid['TransactionRequestLogLPID'];
//                            $tracking1 = "LP" . $tracking1;
//                        }

                        $url = $_ServiceAPI[$serviceID - 1];
                        $capiusername = $_ptcasinoname;
                        $capipassword = $_ptsecretkey;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);

                        break;
                }

                //check if transaction is not successful
                if (isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false) {
                    $transstatus = 2;
                    $apiresult = false;
                    $transrefid = '';
                    $_SESSION['servicetransid'] = $transrefid;
                    $_SESSION['servicestatus'] = $apiresult;
                } else {
                    if (isset($transSearchInfo['TransactionInfo'])) {
                        //RTG / Magic Macau
                        if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {

                            $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                            $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                        }
                        //MG / Vibrant Vegas
                        elseif (isset($transSearchInfo['TransactionInfo']['MG'])) {
                            $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                            $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                        }
                        //PT / PlayTech
                        elseif (isset($transSearchInfo['TransactionInfo']['PT'])) {
                            $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];

                            if ($apiresult == 'missing') {
                                $transrefid = 0;
                            } else {
                                $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                            }
                        }
                    } else {
                        echo json_encode($transstatus);
                    }

                    //$apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];

                    if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                        $transstatus = 1;
                        $_SESSION['servicetransid'] = $transrefid;
                        $_SESSION['servicestatus'] = $apiresult;
                    } else {
                        $transstatus = 2;
                        $_SESSION['servicetransid'] = $transrefid;
                        $_SESSION['servicestatus'] = $apiresult;
                    }
                }


                echo json_encode($transstatus);

                unset($transstatus, $transSearchInfo, $apiresult, $transrefid);
                $maf->close();
                break;

            //proceed transaction in kronus and casino
            case 'Proceed':
                //validate data for casino fulfillment
                $txttransstatus = $_POST['txttransstatus'];
                $txtsource = $_POST['txtsource'];
                $txttranstype = $_POST['txttranstype'];
                $txttransrefid = $_POST['txttransrefid'];
                $cmbsite = $_POST['cmbsite'];
                $cmbterm = $_POST['cmbterm'];
                $amount = $_POST['txtamount'];
                $txtcardnumber = $_POST['txtcardnumber'];
                $cardnumber = $_POST['txtcardnumber'];
                $serviceid = $_POST['txtserviceid'];
                $txtcasino = $_POST['txtcasino'];
                $txtusername = $_POST['txtusername'];
                $usermode = $_POST['txtusermode'];
                $transrefid = $_SESSION['servicetransid'];
                $apiresult = $_SESSION['servicestatus'];

                $servicegroupname = $maf->getServiceGrpNameByName($txtcasino);
                $txtcasino = $servicegroupname;
                //check mode if user mode
                if ($usermode == 'user') {
                    $usermode = 1;

                    $casino = $_SESSION['CasinoArray'];

                    $casinoarray_count = count($casino);

                    $casinos = array();

                    if ($casinoarray_count != 0) {
                        for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                            $casinos[$ctr] =
                                    array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                        'ServicePassword' => $casino[$ctr]->ServicePassword,
                                        'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                        'ServiceID' => $casino[$ctr]->ServiceID,
                                        'UserMode' => $casino[$ctr]->UserMode,
                                        'isVIP' => $casino[$ctr]->isVIP,
                                        'Status' => $casino[$ctr]->Status
                            );

                            //get details by pick casino
                            $casino2 = $maf->loopAndFindCasinoService($casinos, 'ServiceID', $serviceid);
                        }
                    }
                    $ubservicepassword = $casino2[0]['ServicePassword'];
                    $txtusername = $casino2[0]['ServiceUsername'];
                    $hashedpass = $casino2[0]['HashedServicePassword'];
                } else {
                    $usermode = 0;
                    $ubservicepassword = '';
                    $hashedpass = '';
                }

                //get last session summary id using terminal
                $trans_summary_id = $maf->getLastSessSummaryID($cmbterm);
                if ($trans_summary_id == false) {
                    $trans_summary_id = null;
                }

                //get site balance for a certain site
                $bcf = $maf->getSiteBalance($cmbsite);

                //get usermode for a certain site
                $usermode = $maf->getusermode($serviceid);
                if ($usermode == 2) { // for e-Bingo only
                    $mid = $cmbterm;
                } else {
                    $mid = $_SESSION['MID'];
                }
                $transstatus = 1;
                $trans_summary_max_id = null;

                //if transaction status is Approved
                if ($txttransstatus == 1) {
                    if ($txtsource == 'Cashier') {
                        //check if terminal has a sessions
                        $sessioncount = $maf->countSessions($cmbterm);
                        $sessioncount = $sessioncount['termsess'];

                        $checkterminaltype = $maf->checkTerminaType($cmbterm);

                        if ($checkterminaltype['TerminalType'] == 1) {
                            $maf->insertEgmSession($cmbterm, $mid, $serviceid, $aid);
                        }

                        if ($sessioncount > 0) {
                            $insrttermsess = true;
                        } else {
                            $insrttermsess = $maf->insert($cmbterm, $serviceid, $amount, $trans_summary_max_id, $txtcardnumber, $mid, $usermode, $txtusername, $ubservicepassword, $hashedpass);
                        }

                        //check if Transaction type is Deposit
                        if ($txttranstype == 'Deposit') {
                            //insert in terminal sessions is successful
                            if ($insrttermsess == true) {
                                if ($usermode == 2) { // for deposit e-Bingo only
                                    $txtusername = "";
                                }
                                //insert transaction summary, transactiondetails, and terminal sessions when transaction type is Deposit
                                $trans = $maf->startTransaction($cmbsite, $cmbterm, $amount, $aid, $txttransrefid, 'D', $serviceid, $transstatus, $cardnumber, $mid);
                            } else {
                                $trans = false;
                            }

                            //check if startTransaction method fails
                            if ($trans == false) {
                                $msg = 'Manual Casino Fulfillment: Failed to Update Transactional Table';
                            } else {

                                $newbal = $bcf - $amount;
                                $maf->updateBcf($newbal, $cmbsite, 'Start session'); //update bcf 

                                $msg = 'Manual Casino Fulfillment: Transaction Successful';
                            }
                        }
                        //check if Transaction type is Reload
                        if ($txttranstype == 'Reload') {

                            $trans_summary = $maf->getTransactionSummaryDetail($cmbsite, $cmbterm);

                            $total_reload_balance = $trans_summary['Reload'] + $amount;

                            //check if casino is RTG, MG or PT
                            switch (true) {
                                case (strstr($txtcasino, "RTG2")):
                                    $url = $_ServiceAPI[$serviceid - 1];
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';
                                    $balance = $CasinoGamingCAPI->getBalance($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                    $terminal_balance = $balance;

                                    $total_terminal_balance = $terminal_balance;
                                    break;
                                case (strstr($txtcasino, "RTG")):
                                    $url = $_ServiceAPI[$serviceid - 1];
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';
                                    if ($usermode == 2) {
                                        $balance = $CasinoGamingCAPI->getBalance($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                    } 
                                    
                                    if ($usermode == 0) {
                                        $balance = $CasinoGamingCAPI->getBalance($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                        if ($balance == NULL) { // proceeed if certificate does not match
                                            $balance = $CasinoGamingCAPI->getBalance($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                        }
                                    }

                                    $terminal_balance = $balance;

                                    $total_terminal_balance = $terminal_balance;
                                    break;
                                case (strstr($txtcasino, "MG")):
                                    $_MGCredentials = $_ServiceAPI[$serviceid - 1];
                                    list($mgurl, $mgserverID) = $_MGCredentials;
                                    $url = $mgurl;
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = $mgserverID;
                                    $balance = $CasinoGamingCAPI->getBalance($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                    $terminal_balance = $balance;

                                    $total_terminal_balance = $terminal_balance;
                                    break;
                                case (strstr($txtcasino, "PT")):
                                    $url = $_ServiceAPI[$serviceid - 1];
                                    $capiusername = $_ptcasinoname;
                                    $capipassword = $_ptsecretkey;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';
                                    $balance = $CasinoGamingCAPI->getBalance($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID);


                                    $terminal_balance = $balance;

                                    $total_terminal_balance = $terminal_balance;
                                    break;
                            }

                            //insert in terminal sessions is successful
                            if ($insrttermsess == true) {
                                //insert transaction summary, transactiondetails, and terminal sessions when transaction type is Reload
                                $trans = $maf->reloadTransaction($amount, $trans_summary_id, $txttransrefid, $cmbsite, $cmbterm, 'R', $serviceid, $aid, $transstatus, $total_reload_balance, $total_terminal_balance, $cardnumber, $mid);
                            } else {
                                $trans = false;
                            }

                            if ($trans != false) {
                                if ($checkterminaltype['TerminalType'] == 1) {
                                    $stackerbatchid = $maf->getStackerBatchID($cmbterm);

                                    if (is_null($stackerbatchid)) {
                                        $updated = 1;
                                    } else {
                                        $maf2->updateStackerDetailsID($trans, $stackerbatchid);
                                        $updated = $maf2->updateSSStatus($aid, $stackerbatchid, 4);

                                        if ($updated == 0) {
                                            $updated = 1;
                                        }
                                    }
                                }
                            }

                            //check if reloadTransaction method fails
                            if ($trans == false) {
                                $msg = 'Manual Casino Fulfillment: Failed to Update Transactional Table';
                            } else {
                                $newbal = $bcf - $amount;
                                $maf->updateBcf($newbal, $cmbsite, 'Reload session');

                                $msg = 'Manual Casino Fulfillment: Transaction Successful';
                            }
                        }
                        //check if Transaction type is Withdraw
                        if ($txttranstype == 'Withdraw') {
                            if ($checkterminaltype['TerminalType'] == 1) {
                                $stackerbatchid = $maf->getStackerBatchID($cmbterm);

                                if (is_null($stackerbatchid)) {
                                    $updated = 1;
                                } else {
                                    $updated = $maf2->updateSSStatus($aid, $stackerbatchid, 5);

                                    if ($updated == 0) {
                                        $updated = 1;
                                    }
                                }
                                $maf->deleteEGMSession($cmbterm);
                            }
                            //insert in terminal sessions is successful
                            if ($insrttermsess == true) {
                                //insert transaction summary, transactiondetails, and terminal sessions when transaction type is Withdraw
                                $trans = $maf->redeemTransaction($amount, $trans_summary_id, $txttransrefid, $cmbsite, $cmbterm, 'W', $serviceid, $aid, $transstatus, $cardnumber, $mid);
                            } else {
                                $trans = false;
                            }


                            if ($trans == false) {
                                $msg = 'Manual Casino Fulfillment: Failed to Update Transactional Table';
                            } else {
                                $msg = 'Manual Casino Fulfillment: Transaction Successful';
                            }
                        }
                        //update transaction request logs status to transaction fulfilled
                        $status = '3';
                        switch (true) {
                            case (strstr($txtcasino, "MG")):
                                $apiresult = ($apiresult) ? 'true' : 'false';
                                break;
                            case (strstr($txtcasino, "PT")):
                                $apiresult = $apiresult;
                                break;
                            case (strstr($txtcasino, "RTG")):
                                $apiresult = $apiresult;
                                break;
                        }
                        $uptrans = $maf->uptransactionreqlogs($status, $txttransrefid, $transrefid, $apiresult);

                        if ($uptrans > 0) {
                            if ($checkterminaltype['TerminalType'] == 1) {
                                $egmreqid = $maf->getEgmReqID($cmbterm, $mid);

                                if (empty($egmreqid) || $egmreqid == '') {
                                    $maf->updateEgmRequestLogs($status, $transrefid, $egmreqid);
                                }
                            }

                            $zaid = $aid;
                            $zdate = $vdate;
                            $ztransdetails = 'Casino Service = ' . "$serviceid" . ' Status = ' . "$status" . ' TerminalID = ' . "$cmbterm" . ' UserMode = ' . "$usermode";
                            $zauditfunctionID = 73;
                            $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);
                        } else {
                            $msg = 'Manual Casino Fulfillment: Failed to Update Transaction Status';
                        }
                    } 
//                    elseif ($txtsource == 'Launchpad') {
//                        //for Launchpad, update transaction status
//                        if ($txttranstype == 'Withdraw') {
//                            switch ($txttranstype) {
//                                case 'Deposit':
//                                    $transtype = 'D';
//                                    break;
//
//                                case 'Reload':
//                                    $transtype = 'R';
//                                    break;
//
//                                case 'Withdraw':
//                                    $transtype = 'W';
//                                    break;
//
//                                case 'ReDeposit':
//                                    $transtype = 'RD';
//                                    break;
//                            }
//
//                            $transactionid = $maf->getTransactionIDLP($serviceid, $cmbterm);
//                            $tracking1 = $transactionid['TransactionRequestLogLPID'];
//
//                            $tracking2 = $transtype;
//                            $tracking3 = $cmbterm;
//                            $tracking4 = $cmbsite;
//
//
//                            switch (true) {
//                                case (strstr($txtcasino, "RTG2")):
//                                    $url = $_ServiceAPI[$serviceid - 1];
//                                    $capiusername = $_CAPIUsername;
//                                    $capipassword = $_CAPIPassword;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = '';
//                                    $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//
//                                    break;
//                                case (strstr($txtcasino, "RTG")):
//                                    $url = $_ServiceAPI[$serviceid - 1];
//                                    $capiusername = $_CAPIUsername;
//                                    $capipassword = $_CAPIPassword;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = '';
//                                    if ($usermode == 2) {
//                                        $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $usermode);
//                                    } 
//                                    
//                                    if ($usermode == 0) {
//                                        $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $usermode);
//                                        if ($deposit == NULL) { // proceeed if certificate does not match
//                                            $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//                                        }
//                                        
//                                    }
//
//                                    break;
//                                case (strstr($txtcasino, "MG")):
//                                    $tracking3 = $_CAPIEventID;
//
//                                    $tracking4 = $maf->insertServiceTransRef($serviceid, 2);
//                                    $tracking2 = $tracking4;
//
//
//                                    //get terminal password
//                                    if ($usermode == 0 || $usermode == 2) {
//                                        $terminal_pwd_res = $maf->getTerminalPassword($cmbterm, $serviceid);
//                                        $terminal_pwd = $terminal_pwd_res['ServicePassword'];
//                                        $tracking1 = $terminal_pwd;
//                                    } else {
//                                        $tracking1 = $ubservicepassword;
//                                    }
//
//                                    $_MGCredentials = $_ServiceAPI[$serviceid - 1];
//                                    list($mgurl, $mgserverID) = $_MGCredentials;
//                                    $url = $mgurl;
//                                    $capiusername = $_CAPIUsername;
//                                    $capipassword = $_CAPIPassword;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = $mgserverID;
//                                    $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//
//                                    break;
//                                case (strstr($txtcasino, "PT")):
//                                    $tracking3 = $_CAPIEventID;
//
//                                    $transactionid = $maf->getTransactionIDLP($serviceid, $cmbterm);
//                                    $tracking2 = $transactionid['TransactionRequestLogLPID'];
//
//                                    $transactionid = $maf->getServiceTransactionIDLP($txttransrefid);
//                                    $tracking4 = $transactionid['ServiceTransactionID'];
//
//                                    //get terminal password
//                                    if ($usermode == 0 || $usermode == 2) {
//                                        $terminal_pwd_res = $maf->getTerminalPassword($cmbterm, $serviceid);
//                                        $terminal_pwd = $terminal_pwd_res['ServicePassword'];
//                                        $tracking1 = $terminal_pwd;
//                                    } else {
//                                        $tracking1 = $ubservicepassword;
//                                    }
//
//                                    $url = $_ServiceAPI[$serviceid - 1];
//                                    $capiusername = $_ptcasinoname;
//                                    $capipassword = $_ptsecretkey;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = '';
//
//                                    //unfreeze first casino
//                                    $unfreeze = $CasinoGamingCAPI->unfreeze($txtusername, $url, $capiusername, $capipassword, 0);
//                                    //check if casino is successfuly unfreezed
//                                    if ($unfreeze['IsSucceed'] == true) {
//                                        $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//                                    } else {
//                                        $deposit['IsSucceed'] = false;
//                                    }
//                                    break;
//                            }
//
//                            //check if deposit api method fails
//                            if (isset($deposit['IsSucceed']) && $deposit['IsSucceed'] == true) {
//
//                                $transloglpid = $maf->getmaxtranslogs($transtype, $cmbterm, $serviceid);
//
//                                $data = $maf->gettranslogslpdetails($transtype, $cmbterm, $serviceid, $transloglpid);
//                                foreach ($data as $value) {
//                                    $servicetransferhisid = $value['ServiceTransferHistoryID'];
//                                    $transsummaryid = $value['TransactionSummaryID'];
//                                }
//
//                                $transreferid = $maf->udate('YmdHisu');
//                                $transtype = 'RD';
//                                $status = 0;
//
//
//
//                                if (isset($deposit['TransactionInfo'])) {
//                                    //RTG / Magic Macau
//                                    if (isset($deposit['TransactionInfo']['DepositGenericResult'])) {
//                                        $transrefid = $deposit['TransactionInfo']['DepositGenericResult']['transactionID'];
//                                        $apiresult2 = $deposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
//                                        $apierrmsg = $deposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
//                                    }
//                                    //MG / Vibrant Vegas
//                                    else if (isset($deposit['TransactionInfo'])) {
//                                        $transrefid = $deposit['TransactionInfo']['TransactionId'];
//                                        $apiresult2 = "true";
//                                        $apierrmsg = $deposit['ErrorMessage'];
//                                    }
//                                    //Rockin Reno
//                                    else if (isset($deposit['TransactionInfo']['PT'])) {
//                                        $transrefid = $deposit['TransactionInfo']['PT']['TransactionId'];
//                                        $apiresult2 = $deposit['TransactionInfo']['PT']['TransactionStatus'];
//                                        $apierrmsg = $deposit['TransactionInfo']['PT']['TransactionStatus'];
//                                    }
//                                }
//                                $statuz = 4;
//                                switch (true) {
//                                    case (strstr($txtcasino, "MG")):
//                                        $apiresult = ($apiresult) ? 'true' : 'false';
//                                        break;
//                                    case (strstr($txtcasino, "PT")):
//                                        $apiresult = $apiresult;
//                                        break;
//                                    case (strstr($txtcasino, "RTG")):
//                                        $apiresult = $apiresult;
//                                        break;
//                                }
//                                //update transactionrequestlogslp status to denied
//                                $uptrans = $maf->uptransactionreqlogslp2($statuz, $apiresult, $txttransrefid, $transrefid);
//
//                                if ($uptrans > 0) {
//                                    $paymenttype = 1;
//
//                                    switch (true) {
//                                        case (strstr($txtcasino, "MG")):
//                                            $apiresult = ($apiresult) ? 'true' : 'false';
//                                            break;
//                                        case (strstr($txtcasino, "PT")):
//                                            $apiresult = $apiresult;
//                                            break;
//                                        case (strstr($txtcasino, "RTG")):
//                                            $apiresult = $apiresult;
//                                            break;
//                                    }
//                                    //insert in transactionrequestlogslp
//                                    $inserttranslogs = $maf->insertTransactionreqlogslp($transreferid, $amount, $transtype, $cmbterm, $status, $cmbsite, $transrefid, $apiresult, $serviceid, $servicetransferhisid, $txtcardnumber, $mid, $usermode, $transsummaryid, $paymenttype);
//
//                                    //check if insert in transactionrequestlogslp is successful   
//                                    if ($inserttranslogs > 0) {
//                                        $upterminalsessions = $maf->upTerminalSessServcID($serviceid, $amount, $cmbterm);
//
//                                        //if update terminalsessions is successful
//                                        if ($upterminalsessions == true) {
//                                            $zaid = $aid;
//                                            $zdate = $vdate;
//                                            $ztransdetails = 'Casino Service = ' . "$serviceid" . ' Status = ' . "$status" . ' TerminalID = ' . "$cmbterm" . ' UserMode = ' . "$usermode";
//                                            $zauditfunctionID = 73;
//                                            $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);
//
//                                            $msg = 'Manual Casino Fulfillment: Transaction Successful';
//                                        } else {
//                                            $msg = 'Manual Casino Fulfillment: Failed to Update Terminal Sessions';
//                                        }
//                                    } else {
//                                        $msg = 'Manual Casino Fulfillment: Failed to Insert/Update Transactional Tables';
//                                    }
//                                } else {
//                                    $msg = 'Manual Casino Fulfillment: Failed to Update Transactional Tables';
//                                }
//                            } else {
//                                //check casino if RTG, MG, or PT, get api error message
//                                switch (true) {
//                                    case (strstr($txtcasino, "RTG")):
//                                        $apierrmsg = $deposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
//                                        break;
//                                    case (strstr($txtcasino, "MG")):
//                                        $apierrmsg = $deposit['ErrorMessage'];
//                                        break;
//                                    case (strstr($txtcasino, "PT")):
//                                        $apierrmsg = $deposit['TransactionInfo']['PT']['TransactionStatus'];
//                                        break;
//                                }
//                                $msg = "$apierrmsg";
//                            }
//                        } elseif ($txttranstype == 'Deposit' || $txttranstype == 'ReDeposit') {
//                            $status = 3;
//
//                            switch (true) {
//                                case (strstr($txtcasino, "MG")):
//                                    $apiresult = ($apiresult) ? 'true' : 'false';
//                                    break;
//                                case (strstr($txtcasino, "PT")):
//                                    $apiresult = $apiresult;
//                                    break;
//                                case (strstr($txtcasino, "RTG")):
//                                    $apiresult = $apiresult;
//                                    break;
//                            }
//                            $uptrans = $maf->uptransactionreqlogslp($status, $apiresult, $txttransrefid);
//
//                            //check if update transactionrequestlogslp is successfully updated
//                            if ($uptrans > 0) {
//                                $upterminalsessions = $maf->upTerminalSessServcID($serviceid, $amount, $cmbterm);
//
//                                //check if update terminalsessions is successfully updated
//                                if ($upterminalsessions == true) {
//                                    $zaid = $aid;
//                                    $zdate = $vdate;
//                                    $ztransdetails = 'Casino Service = ' . "$serviceid" . ' Status = ' . "$status" . ' TerminalID = ' . "$cmbterm" . ' UserMode = ' . "$usermode";
//                                    $zauditfunctionID = 73;
//                                    $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);
//
//                                    $msg = 'Manual Casino Fulfillment: Transaction Successful';
//                                } else {
//                                    $msg = 'Manual Casino Fulfillment: Failed to Update Terminal Sessions';
//                                }
//                            } else {
//                                $msg = 'Manual Casino Fulfillment: Failed to Update Transaction Status';
//                            }
//                        } else {
//                            $msg = 'Manual Casino Fulfillment: Invalid transaction type';
//                        }
//                    }
                }
                //if transaction status is Denied
                else {
                    //update transaction request logs status to transaction denied
                    if ($txtsource == 'Cashier') {
                        //Cashier source, tag as failed
                        $status = 4;
                        $transrefid = null;
                        switch (true) {
                            case (strstr($txtcasino, "MG")):
                                $converted_res = ($apiresult) ? 'true' : 'false';
                                break;
                            case (strstr($txtcasino, "PT")):
                                $converted_res = $apiresult;
                                break;
                            case (strstr($txtcasino, "RTG")):
                                $converted_res = $apiresult;
                                break;
                        }

                        $checkterminaltype = $maf->checkTerminaType($cmbterm);

                        $uptrans = $maf->uptransactionreqlogs($status, $txttransrefid, $transrefid, $converted_res);

                        if ($uptrans > 0) {

                            if ($checkterminaltype['TerminalType'] == 1) {
                                $egmreqid = $maf->getEgmReqID($cmbterm, $mid);

                                if (empty($egmreqid) || $egmreqid == '') {
                                    $maf->updateEgmRequestLogs($status, $transrefid, $egmreqid);
                                }
                            }

                            $zaid = $aid;
                            $zdate = $vdate;
                            $ztransdetails = 'Casino Service = ' . "$serviceid" . ' Status = ' . "$status" . ' TerminalID = ' . "$cmbterm" . ' UserMode = ' . "$usermode";
                            $zauditfunctionID = 73;
                            $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);
                            $msg = 'Manual Casino Fulfillment: Successfully Updated. Transaction Tag as Failed';
                        } else {
                            $msg = 'Manual Casino Fulfillment: Failed to Update Transaction Status';
                        }
                    } 
//                    elseif ($txtsource == 'Launchpad') {
//                        //check if Transaction type is Withdraw or W
//                        if ($txttranstype == 'Withdraw') {
//                            //launchpad source, tag as failed
//                            $status = 4;
//                            switch (true) {
//                                case (strstr($txtcasino, "MG")):
//                                    $apiresult = ($apiresult) ? 'true' : 'false';
//                                    break;
//                                case (strstr($txtcasino, "PT")):
//                                    $apiresult = $apiresult;
//                                    break;
//                                case (strstr($txtcasino, "RTG")):
//                                    $apiresult = $apiresult;
//                                    break;
//                            }
//                            $uptrans = $maf->uptransactionreqlogslp($status, $apiresult, $txttransrefid);
//
//                            //check if update transaction request logs lp is successfully updated
//                            if ($uptrans > 0) {
//                                $upterminalsessions = $maf->upTerminalSessServcID($serviceid, $amount, $cmbterm);
//
//                                if ($upterminalsessions == true) {
//                                    $zaid = $aid;
//                                    $zdate = $vdate;
//                                    $ztransdetails = 'Casino Service = ' . "$serviceid" . ' Status = ' . "$status" . ' TerminalID = ' . "$cmbterm" . ' UserMode = ' . "$usermode";
//                                    $zauditfunctionID = 73;
//                                    $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);
//
//                                    $msg = 'Manual Casino Fulfillment: Successfully Updated. Transaction Tag as Failed';
//                                } else {
//                                    $msg = 'Manual Casino Fulfillment: Failed to Update Terminal Sessions';
//                                }
//                            } else {
//                                $msg = 'Failed to Update Transaction Status';
//                            }
//                        }
//                        //check if Transaction type is Deposit / D or Redeposit / RD
//                        elseif ($txttranstype == 'Deposit' || $txttranstype == 'ReDeposit') {
//                            //check transaction type
//                            switch ($txttranstype) {
//                                case 'Deposit':
//                                    $transtype = 'D';
//                                    break;
//
//                                case 'Reload':
//                                    $transtype = 'R';
//                                    break;
//
//                                case 'Withdraw':
//                                    $transtype = 'W';
//                                    break;
//
//                                case 'ReDeposit':
//                                    $transtype = 'RD';
//                                    break;
//                            }
//
//                            $transactionid = $maf->getTransactionID($serviceid, $cmbterm);
//                            $tracking1 = $transactionid['TransactionRequestLogID'];
//
//                            $tracking2 = $transtype;
//                            $tracking3 = $cmbterm;
//                            $tracking4 = $cmbsite;
//
//                            switch (true) {
//                                //check casino if RTG, MG, or PT
//                                case (strstr($txtcasino, "RTG2")):
//
//                                    $url = $_ServiceAPI[$serviceid - 1];
//                                    $capiusername = $_CAPIUsername;
//                                    $capipassword = $_CAPIPassword;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = '';
//                                    $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//                                    break;
//                                case (strstr($txtcasino, "RTG")):
//
//                                    $url = $_ServiceAPI[$serviceid - 1];
//                                    $capiusername = $_CAPIUsername;
//                                    $capipassword = $_CAPIPassword;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = '';
//                                    if ($usermode == 2) {
//                                        $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $usermode);
//                                    } 
//                                    
//                                    if ($usermode == 0) {
//                                        $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $usermode);
//                                        if ($deposit == NULL) { // proceeed if certificate does not match
//                                            $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);  
//                                        }
//                                    }
//                                    break;
//                                case (strstr($txtcasino, "MG")):
//
//                                    $tracking3 = $_CAPIEventID;
//
//                                    $transactionid = $maf->getTransactionIDLP($serviceid, $cmbterm);
//                                    $tracking2 = $transactionid['TransactionRequestLogLPID'];
//
//                                    $tracking4 = $maf->insertServiceTransRef($serviceid, 2);
//
//                                    //get terminal password
//                                    if ($usermode == 0 || $usermode == 2) {
//                                        $terminal_pwd_res = $maf->getTerminalPassword($cmbterm, $serviceid);
//                                        $terminal_pwd = $terminal_pwd_res['ServicePassword'];
//                                        $tracking1 = $terminal_pwd;
//                                    } else {
//                                        $tracking1 = $ubservicepassword;
//                                    }
//
//                                    $_MGCredentials = $_ServiceAPI[$serviceid - 1];
//                                    list($mgurl, $mgserverID) = $_MGCredentials;
//                                    $url = $mgurl;
//                                    $capiusername = $_CAPIUsername;
//                                    $capipassword = $_CAPIPassword;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = $mgserverID;
//                                    $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//
//                                    break;
//                                case (strstr($txtcasino, "PT")):
//
//                                    $tracking3 = $_CAPIEventID;
//
//                                    $transactionid = $maf->getTransactionIDLP($serviceid, $cmbterm);
//                                    $tracking4 = $transactionid['TransactionRequestLogLPID'];
//
//                                    //get terminal password
//                                    if ($usermode == 0 || $usermode == 2) {
//                                        $terminal_pwd_res = $maf->getTerminalPassword($cmbterm, $serviceid);
//                                        $terminal_pwd = $terminal_pwd_res['ServicePassword'];
//                                        $tracking1 = $terminal_pwd;
//                                    } else {
//                                        $tracking1 = $ubservicepassword;
//                                    }
//
//                                    $url = $_ServiceAPI[$serviceid - 1];
//                                    $capiusername = $_ptcasinoname;
//                                    $capipassword = $_ptsecretkey;
//                                    $capiplayername = $_CAPIPlayerName;
//                                    $capiserverID = '';
//
//                                    //unfreeze first casino
//                                    $unfreeze = $CasinoGamingCAPI->unfreeze($txtusername, $url, $capiusername, $capipassword, 0);
//                                    if ($unfreeze['IsSucceed'] == true) {
//                                        $deposit = $CasinoGamingCAPI->Deposit($txtcasino, $serviceid, $url, $txtusername, $capiusername, $capipassword, $capiplayername, $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
//                                    } else {
//                                        $deposit['IsSucceed'] = false;
//                                    }
//                                    break;
//                            }
//
//                            //if deposit is successful 
//                            if (isset($deposit['IsSucceed']) && $deposit['IsSucceed'] == true) {
//                                $transloglpid = $maf->getmaxtranslogs($transtype, $cmbterm, $serviceid);
//
//                                $data = $maf->gettranslogslpdetails($transtype, $cmbterm, $serviceid, $transloglpid);
//                                foreach ($data as $value) {
//                                    $servicetransferhisid = $value['ServiceTransferHistoryID'];
//                                    $transsummaryid = $value['TransactionSummaryID'];
//                                }
//
//                                $transreferid = $maf->udate('YmdHisu');
//                                $transtype = 'RD';
//                                $status = 0;
//
//                                if (isset($deposit['TransactionInfo'])) {
//                                    //RTG / Magic Macau
//                                    if (isset($deposit['TransactionInfo']['DepositGenericResult'])) {
//                                        $transrefid = $deposit['TransactionInfo']['DepositGenericResult']['transactionID'];
//                                        $apiresult2 = $deposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
//                                        $apierrmsg = $deposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
//                                    }
//                                    //MG / Vibrant Vegas
//                                    else if (isset($deposit['TransactionInfo'])) {
//                                        $transrefid = $deposit['TransactionInfo']['TransactionId'];
//                                        $apiresult2 = "true";
//                                        $apierrmsg = $deposit['ErrorMessage'];
//                                    }
//                                    //Rockin Reno
//                                    else if (isset($deposit['TransactionInfo']['PT'])) {
//                                        $transrefid = $deposit['TransactionInfo']['PT']['TransactionId'];
//                                        $apiresult2 = $deposit['TransactionInfo']['PT']['TransactionStatus'];
//                                        $apierrmsg = $deposit['TransactionInfo']['PT']['TransactionStatus'];
//                                    }
//                                }
//                                $statuz = 4;
//                                switch (true) {
//                                    case (strstr($txtcasino, "MG")):
//                                        $apiresult = ($apiresult) ? 'true' : 'false';
//                                        break;
//                                    case (strstr($txtcasino, "PT")):
//                                        $apiresult = $apiresult;
//                                        break;
//                                    case (strstr($txtcasino, "RTG")):
//                                        $apiresult = $apiresult;
//                                        break;
//                                }
//                                //update transactionrequestlogslp status to denied
//                                $uptrans = $maf->uptransactionreqlogslp2($statuz, $apiresult, $txttransrefid, $transrefid);
//
//                                if ($uptrans > 0) {
//                                    $paymenttype = 1;
//
//                                    switch (true) {
//                                        case (strstr($txtcasino, "MG")):
//                                            $apiresult = ($apiresult) ? 'true' : 'false';
//                                            break;
//                                        case (strstr($txtcasino, "PT")):
//                                            $apiresult = $apiresult;
//                                            break;
//                                        case (strstr($txtcasino, "RTG")):
//                                            $apiresult = $apiresult;
//                                            break;
//                                    }
//                                    //insert in transactionrequestlogslp
//                                    $inserttranslogs = $maf->insertTransactionreqlogslp($transreferid, $amount, $transtype, $cmbterm, $status, $cmbsite, $transrefid, $apiresult2, $serviceid, $servicetransferhisid, $txtcardnumber, $mid, $usermode, $transsummaryid, $paymenttype);
//                                    //check if insert in transactionrequestlogslp is successful   
//                                    if ($inserttranslogs > 0) {
//                                        $upterminalsessions = $maf->upTerminalSessServcID($serviceid, $amount, $cmbterm);
//
//                                        if ($upterminalsessions == true) {
//                                            $zaid = $aid;
//                                            $zdate = $vdate;
//                                            $ztransdetails = 'Casino Service = ' . "$serviceid" . ' Status = ' . "$status" . ' TerminalID = ' . "$cmbterm" . ' UserMode = ' . "$usermode";
//                                            $zauditfunctionID = 73;
//                                            $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);
//
//                                            $msg = 'Manual Casino Fulfillment: Successfully Updated Tag As Success';
//                                        } else {
//                                            $msg = 'Manual Casino Fulfillment: Failed to Update Terminal Sessions';
//                                        }
//                                    } else {
//                                        $msg = 'Manual Casino Fulfillment: Failed to Insert/Update Transactional Tables';
//                                    }
//                                } else {
//                                    $msg = 'Manual Casino Fulfillment: Failed to Update Transactional Tables';
//                                }
//                            } else {
//                                //check casino if RTG, MG, or PT, get Transaction Information
//                                switch (true) {
//                                    case (strstr($txtcasino, "RTG")):
//                                        $apierrmsg = $deposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
//                                        break;
//                                    case (strstr($txtcasino, "MG")):
//                                        $apierrmsg = $deposit['ErrorMessage'];
//                                        break;
//                                    case (strstr($txtcasino, "PT")):
//                                        $apierrmsg = $deposit['ErrorMessage'];
//                                        break;
//                                }
//                                $msg = "$apierrmsg";
//                            }
//                        } else {
//                            $msg = 'Manual Casino Fulfillment: Invalid transaction type';
//                        }
//                    }
                }
                echo json_encode($msg);
                unset($uptrans, $txttranstype, $balance, $terminal_balance, $total_terminal_balance, $amount, $trans_summary_id, $bcf, $deposit, $msg);
                unset($_SESSION['servicetransid'], $_SESSION['servicestatus']);
                $maf->close();
                break;

            default :
                $msg = "Page not found";
                $_SESSION['mess'] = $msg;
                $maf->close();
                header("Location: ../blank.php");
                break;
        }
    }
    //this was used in transaction tracking
    if (isset($_POST['sendSiteIDz'])) {
        $vsiteID = $_POST['sendSiteIDz'];

        //check if selected site is valid
        if ($vsiteID <> "-1") {
            $rsitecode = $maf->getsitecode($vsiteID); //get the sitecode first
            $vresults = array();
            //get all terminals
            $vresults = $maf->selectterminals($vsiteID);
            if (count($vresults) > 0) {
                $terminals = array();
                foreach ($vresults as $row) {
                    $rterminalID = $row['TerminalID'];
                    $rterminalCode = $row['TerminalCode'];
                    $sitecode = $terminalcode;
                    //remove the "icsa-[SiteCode]"
                    $rterminalCode = substr($row['TerminalCode'], strlen($rsitecode['SiteCode']));

                    //create a new array to populate the combobox
                    $newvalue = array("TerminalID" => $rterminalID, "TerminalCode" => $rterminalCode);
                    array_push($terminals, $newvalue);
                }
                echo json_encode($terminals);
                unset($terminals);
            } else {
                echo "No Terminal Assigned";
            }
            unset($vresults);
        } else {
            echo "No Terminal Assigned";
        }
        unset($vresults);
        $maf->close();
        exit;
    } elseif (isset($_GET['cmbterminal'])) {
        $vterminalID = $_GET['cmbterminal'];
        $rresult = array();
        $rresult = $maf->getterminalname($vterminalID);
        $vterminalName->TerminalName = $rresult['TerminalName'];
        echo json_encode($vterminalName);
        unset($rresult);
        $maf->close();
        exit;
    } else {
        
    }
} else {
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>