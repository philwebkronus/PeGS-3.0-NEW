<?php

/* Created by: Lea Tuazon
 * Date Created : June 8, 2011
 * Modified By: Edson L. Perez
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . "/../sys/class/TopUp.class.php";
include __DIR__ . "/../sys/class/LoyaltyUBWrapper.class.php";
require __DIR__ . '/../sys/core/init.php';
include_once __DIR__ . '/../sys/class/CasinoGamingCAPI.class.php';

function removeComma($money) {
    return str_replace(',', '', $money);
}

$aid = 0;
$new_sessionid = "";
if (isset($_SESSION['sessionID'])) {
    $new_sessionid = $_SESSION['sessionID'];
}

if (isset($_SESSION['accID'])) {
    $aid = $_SESSION['accID'];
}

$otopupmembership = new TopUp($_DBConnectionString[5]);
$otopup = new TopUp($_DBConnectionString[0]);
$loyalty = new LoyaltyUBWrapper();
$CasinoGamingCAPI = new CasinoGamingCAPI();
$connected = $otopup->open();
$nopage = 0;

if ($connected) {

    /*     * *************  SESSION CHECKING ************* */
    $isexist = $otopup->checksession($aid);
    if ($isexist == 0) {
        session_destroy();
        $msg = "Not Connected";
        $otopup->close();
        if ($otopup->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }

    $isexistsession = $otopup->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) {
        session_destroy();
        $msg = "Not Connected";
        $otopup->close();
        if ($otopup->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }
    /*     * ************* END SESSION CHECKING ********** */

    //checks if account was locked 
    $islocked = $otopup->chkLoginAttempts($aid);
    if (isset($islocked['LoginAttempts'])) {
        $loginattempts = $islocked['LoginAttempts'];
        if ($loginattempts >= 3) {
            if ($otopup->isAjaxRequest()) {
                header('HTTP/1.1 401 Unauthorized');
                echo "Session Expired";
                exit;
            }
            $otopup->deletesession($aid);
            session_destroy();
            $msg = "Not Connected";
            $otopup->close();
            header("Location: login.php?mess=" . $msg);
            exit;
        }
    }
    $vipaddress = $_SERVER['REMOTE_ADDR'];
    //$vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);  staging 
    $vdate = $otopup->getDate();

    //pagination starts here
    if (isset($_POST['paginate'])) {
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $rsiteID = $_POST['cmbsite'];
        $resultcount = array();
        $resultcount = $otopup->countrevdeposits($rsiteID);
        $count = $resultcount['count'];
        //this is for computing the limit
        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }

        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $start = $limit * $page - $limit;
        $limit = (int) $limit;

        //this is for proper rendering of results, if count is 0 $result is also must be 0
        if ($count > 0) {
            $result = $otopup->viewreversalpage($rsiteID, $start, $limit);
        } else {
            $result = 0;
        }

        if ($result > 0) {
            $i = 0;
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;
            foreach ($result as $vview) {
                $rremitID = $vview['SiteRemittanceID'];
                if ($vview['Status'] == 1) {
                    $vstatus = "Invalid";
                } elseif ($vview['Status'] == 2) {
                    $vstatus = "Pending";
                } else {
                    $vstatus = "Valid";
                }
                $responce->rows[$i]['id'] = $rremitID;
                $responce->rows[$i]['cell'] = array($vview['RemittanceName'], $vview['BankCode'], $vview['Branch'], number_format($vview['Amount'], 2), $vview['BankTransactionID'], $vview['BankTransactionDate'], $vview['ChequeNumber'], $vview['Particulars'], $vview['SiteID'], $vview['SiteName'], $vstatus, "<input type=\"button\" value=\"Verified\" onclick=\"window.location.href='process/ProcessTopUp.php?remitid=$rremitID'+'&remittance='+'UpdateReversal';\"/>");
                $i++;
            }
        } else {
            $i = 0;
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;
            $msg = "Topup Management: No returned result";
            $responce->msg = $msg;
        }
        echo json_encode($responce);
        unset($result);
        $otopup->close();
        exit;
    }

    if (isset($_POST['paginate2'])) {
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $rsiteID = $_POST['cmbsite'];
        $resultcount = array();
        $resultcount = $otopup->countrevdeposits2($rsiteID);
        $count = $resultcount['count'];
        //this is for computing the limit
        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }

        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $start = $limit * $page - $limit;
        $limit = (int) $limit;

        //this is for proper rendering of results, if count is 0 $result is also must be 0
        if ($count > 0) {
            $result = $otopup->viewreversalpage2($rsiteID, $start, $limit);
        } else {
            $result = 0;
        }

        if ($result > 0) {
            $i = 0;
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;
            foreach ($result as $vview) {
                $rremitID = $vview['SiteRemittanceID'];
                if ($vview['Status'] == 3) {
                    $vstatus = "Verified";
                }

                $responce->rows[$i]['id'] = $rremitID;
                $responce->rows[$i]['cell'] = array($vview['SiteRemittanceID'], $vview['RemittanceName'],
                    $vview['BankCode'], $vview['Branch'], number_format($vview['Amount'], 2), $vview['BankTransactionID'],
                    $vview['BankTransactionDate'], $vview['ChequeNumber'], $vview['Particulars'], $vview['SiteID'],
                    $vview['SiteName'], $vstatus, "<input type=\"button\" value=\"Valid\" 
                    onclick=\"window.location.href='process/ProcessTopUp.php?remitid2=$rremitID'+'&remitstat=0'+'&remittance2='+'UpdateVerifiedRemit';\"/><input type=\"button\" value=\"Invalid\" onclick=\"window.location.href='process/ProcessTopUp.php?remitid2=$rremitID'+'&remitstat=1'+'&remittance2='+'UpdateVerifiedRemit';\"/>");
                $i++;
            }
        } else {
            $i = 0;
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;
            $msg = "Topup Management: No returned result";
            $responce->msg = $msg;
        }
        echo json_encode($responce);
        unset($result);
        $otopup->close();
        exit;
    }

    if (isset($_POST['page'])) {
        $vpage = $_POST['page'];
        switch ($vpage) {
            case "GetAllBCF":
                if (isset($_POST['cmbsite']) || isset($_POST['txtposacc'])) {
                    //validate if site dropdown box was selected
                    if ($_POST['cmbsite'] > 0) {
                        $vSiteID = $_POST['cmbsite'];
                    } else {
                        //validate if pos account textfield have value
                        if (strlen($_POST['txtposacc']) > 0) {
                            //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                            if (strlen($_POST['txtposacc']) == 10) {
                                $vposaccno = $_POST['txtposacc'];
                            } else {
                                $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                            }

                            $rsite = $otopup->getidbyposacc($vposaccno);
                            //check if pos account is valid
                            if ($rsite) {
                                $vSiteID = $rsite['SiteID'];
                            } else {
                                echo "Invalid POS Account Number";
                                $otopup->close();
                                exit;
                            }
                        }
                    }

                    $rBCF = $otopup->getallbcf($vSiteID);
                    if (count($rBCF) > 0) {
                        echo json_encode($rBCF);
                    } else {
                        echo "No BCF Found for this site/pegs";
                    }
                    $otopup->close();
                    exit;
                }
                break;

            //Get Membership Card user information    
            case "GetLoyaltyCard":

                $cardnumber = $_POST['txtcardnumber'];
                if (strlen($cardnumber) > 0) {
                    $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);

                    $obj_result = json_decode($loyaltyResult);

                    $statuscode = $obj_result->CardInfo->StatusCode;

                    if (!is_null($statuscode) || $statuscode == '') {
                        if ($statuscode == 1 || $statuscode == 5 || $statuscode == 9) {
                            $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

                            if ($casinoarray_count != 0) {
                                for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                                    $casinoinfo = array(
                                        array(
                                            'UserName' => $obj_result->CardInfo->MemberName,
                                            'MobileNumber' => $obj_result->CardInfo->MobileNumber,
                                            'Email' => $obj_result->CardInfo->Email,
                                            'Birthdate' => $obj_result->CardInfo->Birthdate,
                                            'Casino' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID,
                                            'CardNumber' => $obj_result->CardInfo->CardNumber,
                                            'IsEwallet' => $obj_result->CardInfo->IsEwallet,
                                            'StatusCode' => $obj_result->CardInfo->StatusCode,
                                        ),
                                    );
                                }
                                $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                                $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                                echo json_encode($casinoinfo);
                            } else {
                                $services = "User Based Redemption: Casino is empty";
                                echo "$services";
                            }
                        } else {
                            //check membership card status
                            $statusmsg = $otopup->membershipcardStatus($statuscode);
                            $services = "User Based Redemption: " . $statusmsg;
                            echo "$services";
                        }
                    } else {
                        $statuscode = 100;
                        //check membership card status
                        $statusmsg = $otopup->membershipcardStatus($statuscode);
                        $services = "User Based Redemption: " . $statusmsg;
                        echo "$services";
                    }
                } else {
                    echo "User Based Redemption: Invalid input detected.";
                }
                $otopup->close();
                exit;
                break;

            //User Based Redemption Checking Site Existence
            case "CheckSiteID":
                $usermode = $_POST['txtusermode'];
                $loyaltycardnumber = $_POST['txtcardnumber'];
                $ubserviceID = $_POST['txtserviceid'];
                if (isset($usermode)) {
                    if ($usermode == 1 || $usermode == 3) {
                        $mid = $otopup->getMIDByUBCard($loyaltycardnumber);
                        //check if has sesssion
                        $hasEGM = $otopup->checkIfHasEGMSession($mid);
                        $hasTS = $otopup->checkIfHasTermalSession($mid);
                        //$hasTS = $otopup->checkIfHasTermalSession($mid, $ubserviceID);
                        $isESAFE = $otopup->checkIsEwallet($mid);
                        if ($isESAFE == 1) {
                            // COMMENT OUT CCT 07/23/2018 BEGIN
                            //if ($hasEGM == 0 && $hasTS == 0) 
                            //{
                            $login = $_SESSION['ServiceUserName'];
                            $transid = $otopup->getMaxTransreqlogid($loyaltycardnumber, $ubserviceID);
                            $result = array('TransCode' => 0, 'TransRequestLogID' => $transid);
                            //}
                            //else 
                            //{
                            //    $result = array('TransCode' => 1, 
                            //                    'TransMsg' => "Error: e-SAFE account with existing session is not allowed for redemption.");
                            //}
                            // COMMENT OUT CCT 07/23/2018 END
                        } else {
                            $login = $_SESSION['ServiceUserName'];
                            $transid = $otopup->getMaxTransreqlogid($loyaltycardnumber, $ubserviceID);
                            $result = array('TransCode' => 0, 'TransRequestLogID' => $transid);
                        }
                        echo json_encode($result);
                    }
                }
                break;





///TRANSWALLET


            case "TransferWallet":
                $login = $_POST['terminalcode'];
                $provider = $_POST['txtservices'];
                $vterminalID = $_POST['cmbterminal'];
                $ubserviceID = $_POST['txtserviceid'];
                $ubterminalID = $_POST['txtterminalid'];
                $vserviceBalance = $_POST['txtamount2'];
                $amountToRedeem = $_POST['hdnamtwithdraw'];
                $ticketub = $_POST['txtticketub'];
                $remarksub = $_POST['txtremarksub'];
                $loyaltycardnumber = $_POST['txtcardnumber'];
                $mid = $_POST['txtmid'];
                $usermode = $_POST['txtusermode'];
                $siteID = $_POST['cmbsite'];

                if (!isset($siteID) && $siteID == "-1") {
                    $msg = "Please select Site ID";
                }

                if (isset($usermode)) {
                    if ($usermode == 1 || $usermode == 3) {
                        $mid = $otopup->getMIDByUBCard($loyaltycardnumber);

                        //check if has sesssion
                        $hasTS = $otopup->checkIfHasTermalSession($mid);

                        $getservicename = $otopup->getCasinoName($ubserviceID);
                        $servicegrpname = $otopup->getServiceGrpName($ubserviceID);

                        $serviceName = $getservicename[0]['ServiceName'];


                        if (!$hasTS > 0) {

                            if ($serviceName == "PWW") {
                                $msg = "Cannot proceed with manual redemption. Kindly ask IR/CS to review player transactions.";
                                echo json_encode($msg);
                                exit;
                            }

                            if ($servicegrpname == "RTG" || $servicegrpname == "RTG2" || $servicegrpname == "HAB") {

                                $casino = $_SESSION['CasinoArray2'];
                                $rmid = $_SESSION['MID2'];

                                $addedPwuu = array(
                                    array('ServiceUsername' => null,
                                        'ServicePassword' => null,
                                        'HashedServicePassword' => null,
                                        'ServiceID' => 1,
                                        'UserMode' => 3,
                                        'isVIP' => null,
                                        'Status' => 1)
                                );

                                $casino = array_merge($casino, $addedPwuu);
                                $casinoarray_count = count($casino);

                                $casinos = array();
                                $balPerCasino = array();

                                if ($casinoarray_count != 0) {
                                    for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                                        if (is_array($casino[$ctr])) {
                                            $casinos[$ctr] = array(
                                                array('ServiceUsername' => $casino[$ctr]['ServiceUsername'],
                                                    'ServicePassword' => $casino[$ctr]['ServicePassword'],
                                                    'HashedServicePassword' => $casino[$ctr]['HashedServicePassword'],
                                                    'ServiceID' => $casino[$ctr]['ServiceID'],
                                                    'UserMode' => $casino[$ctr]['UserMode'],
                                                    'isVIP' => $casino[$ctr]['isVIP'],
                                                    'Status' => $casino[$ctr]['Status'],)
                                            );
                                        } else {
                                            $casinos[$ctr] = array(
                                                array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                                    'ServicePassword' => $casino[$ctr]->ServicePassword,
                                                    'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                                    'ServiceID' => $casino[$ctr]->ServiceID,
                                                    'UserMode' => $casino[$ctr]->UserMode,
                                                    'isVIP' => $casino[$ctr]->isVIP,
                                                    'Status' => $casino[$ctr]->Status)
                                            );
                                        }

                                        //loop through array output to get casino array from membership card
                                        foreach ($casinos[$ctr] as $value) {
                                            $rserviceuname = $value['ServiceUsername'];
                                            $rservicepassword = $value['ServicePassword'];
                                            $rserviceid = $value['ServiceID'];
                                            $rusermode = $value['UserMode'];
                                            $risvip = $value['isVIP'];
                                            $hashedpassword = $value['HashedServicePassword'];
                                            $rstatus = $value['Status'];

                                            $servicename = $otopup->getCasinoName($rserviceid);
                                            $servicegrp = $otopup->getServiceGrpName($rserviceid);
                                            $servicestat = $otopup->getServiceStatus($rserviceid);

                                            if ($servicestat == 1) {
                                                //loop htrough services to get if has pedning balance
                                                foreach ($servicename as $service2) {
                                                    $serviceName = $service2['ServiceName'];
                                                    $serviceStatus = $service2['Status'];
                                                    $servicegrp = $servicegrp;

                                                    switch (true) {
                                                        case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                            //call get balance method of Habanero
                                                            $url = $_ServiceAPI[$rserviceid - 1];
                                                            $capiusername = $_HABbrandID;
                                                            $capipassword = $_HABapiKey;
                                                            $capiplayername = '';
                                                            $capiserverID = '';
                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode, $rservicepassword);
                                                            if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                                if ($balance == 0) {
                                                                    $msg = "Nothing to process.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }

                                                            break;
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                            //call get balance method of RTG
                                                            $url = $_ServiceAPI[$rserviceid - 1];
                                                            $capiusername = $_CAPIUsername;
                                                            $capipassword = $_CAPIPassword;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';
                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode);
                                                            if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                                if ($balance == 0) {
                                                                    $msg = "Nothing to process.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }
                                                            break;

                                                        case strstr($serviceName, "PWW"): //if provider is PWW, then

                                                            $getTransactionSummaryID = $otopup->getTransactionSummaryID($rmid);

                                                            if ($getTransactionSummaryID['TransactionSummaryID']) {

                                                                $getMZTransactionTransferDetails = $otopup->getMZTransactionTransferDetails($getTransactionSummaryID['TransactionSummaryID']);
                                                                if (!empty($getMZTransactionTransferDetails)) {
                                                                    $balance = $getMZTransactionTransferDetails['FromAmount'];
                                                                }
                                                            } else {
                                                                $balance = 0;
                                                            }

                                                            if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                                if ($balance == 0) {
                                                                    $msg = "Nothing to process.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }

                                                            break;

                                                        default :
                                                            $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                            echo json_encode($msg);
                                                            exit;

                                                            break;
                                                    }

                                                    $balPerCasino[] = array(
                                                        "ServiceName" => $serviceName,
                                                        "Balance" => $balance
                                                    );
                                                }
                                            }
                                        }
                                    }

                                    $balanceNotZeroCount = 0;
                                    foreach ($balPerCasino as $value) {
                                        if ($getservicename[0]['ServiceName'] <> $value['ServiceName']) {
                                            if ($value['Balance'] > 0) {
                                                $balanceNotZeroCount = $balanceNotZeroCount + 1;
                                            }
                                        }
                                    }

                                    if ($balanceNotZeroCount > 0) {
                                        $msg = "Cannot proceed with manual redemption. Multiple balances found on this player. Kindly ask IR/CS to review player transactions.";
                                        echo json_encode($msg);
                                        exit;
                                    }


                                    $getUBInfo = $otopup->getUBInfo($mid, $ubserviceID);

                                    if (empty($getUBInfo)) {
                                        $msg = "Manual Redemption Error: Can't get player details";
                                        echo json_encode($msg);
                                        exit;
                                    }

                                    $login = $getUBInfo['ServiceUserName'];
                                    $password = $getUBInfo['ServicePassword'];

                                    $ubterminalID = null;
                                    $server = $otopup->getCasinoName($ubserviceID);
                                    foreach ($server as $value2) {
                                        $servername = $value2['ServiceName'];
                                        $stat = $value2['Status'];
                                        $usermode = $value2['UserMode'];
                                    }

                                    $servicegrp = $otopup->getServiceGrpName($ubserviceID);
                                    $servername = $servicegrp;

                                    //check if card is ewallet
                                    $isEwallet = $otopup->checkIsEwallet($mid);

                                    $ramount = ereg_replace(",", "", $vserviceBalance); //format number replace (,)

                                    $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                    if ($ramount > $_maxRedeem) {
                                        $balance = $ramount - $_maxRedeem;
                                        $ramount = $_maxRedeem;
                                    } else {
                                        $balance = 0;
                                    }

                                    $vsiteID = $siteID;
                                    $vterminalID = $ubterminalID;
                                    $vreportedAmt = ereg_replace(",", "", $vserviceBalance);
                                    $vactualAmt = $ramount;
                                    $vtransactionDate = $otopup->getDate();
                                    $vreqByAID = $aid;
                                    $vprocByAID = $aid;
                                    $vdateEffective = $otopup->getDate();
                                    $vstatus = 0;
                                    $vtransactionID = 0;
                                    $vremarks = $remarksub;
                                    $vticket = $ticketub;
                                    $cmbServerID = $ubserviceID;

                                    $vtransStatus = '';
                                    $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                    $transsummaryid = $transsummaryid['summaryID'];

                                    $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $ubserviceID);

                                    $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode);

                                    if ($lastmrid > 0) {
                                        switch (true) {
                                            case strstr($servername, "RTG"): //if provider is RTG, then
                                                $url = $_ServiceAPI[$ubserviceID - 1];
                                                $capiusername = $_CAPIUsername;
                                                $capipassword = $_CAPIPassword;
                                                $capiplayername = $_CAPIPlayerName;
                                                $capiserverID = '';
                                                $tracking1 = "MR" . "$lastmrid";
                                                $tracking2 = '';
                                                $tracking3 = '';
                                                $tracking4 = '';
                                                $withdraw = array();
                                                $locatorname = null;



                                                $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);

                                                break;

                                            case strstr($servername, "HAB"): //if provider is Habanero, then
                                                $url = $_ServiceAPI[$ubserviceID - 1];
                                                $capiusername = $_HABbrandID;
                                                $capipassword = $_HABapiKey;
                                                $capiplayername = $_CAPIPlayerName;
                                                $capiserverID = '';
                                                $tracking1 = "MR" . "$lastmrid";
                                                $tracking2 = '';
                                                $tracking3 = '';
                                                $tracking4 = '';
                                                $withdraw = array();
                                                $locatorname = null;


                                                $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $password);

                                                break;
                                        }


                                        switch (true) {
                                            case strstr($servername, "RTG"): //if provider is RTG, then
                                                //check if redemption was successfull, and insert information on manualredemptions and audittrail
                                                if ($withdraw['IsSucceed'] == true) {
                                                    //fetch the information when calling the RTG Withdraw Method
                                                    foreach ($withdraw as $results) {
                                                        $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                        $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                        $rwamount = $results['WithdrawGenericResult']['amount'];
                                                        $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                        $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                    }
                                                    $fmteffdate = str_replace("T", " ", $reffdate);
                                                    $vsiteID = $siteID;
                                                    $vterminalID = $ubterminalID;
                                                    $vreportedAmt = $ramount;
                                                    $vactualAmt = $rwamount;
                                                    $vtransactionDate = $otopup->getDate();
                                                    $vreqByAID = $aid;
                                                    $vprocByAID = $aid;
                                                    //$vremarks = $rremarks;
                                                    $vdateEffective = $fmteffdate;
                                                    $vstatus = 1;
                                                    $vtransactionID = $rtransactionID;
                                                    $vremarks = $remarksub;
                                                    $vticket = $ticketub;
                                                    $cmbServerID = $ubserviceID;

                                                    //check if there was no error on withdrawal
                                                    if ($riserror == "OK") {
                                                        $vtransStatus = $rremarks;
                                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                        $transsummaryid = $transsummaryid['summaryID'];
                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                        if ($issucess > 0) {
                                                            //get new balance after redemption
                                                            $balance = $CasinoGamingCAPI->getBalance($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                            $ramount = number_format($ramount, 2, ".", ",");
                                                            $balance = number_format($balance, 2, ".", ",");

                                                            //update member services
                                                            if ($otopupmembership->open()) {
                                                                $otopupmembership->updateMemberServices($balance, $mid, $ubserviceID, $issucess);
                                                            }
                                                            $otopupmembership->close();
                                                            //insert into audit trail
                                                            $vtransdetails = "transaction id " . $vtransactionID . ",amount " . $vreportedAmt;
                                                            $vauditfuncID = 7;
                                                            $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                                            $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption: Error on inserting redemption table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {
                                                        if ($riserror == "") {
                                                            $msg = $rremarks;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $status = 2;
                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                } else {
                                                    $status = 2;
                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                    $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                                break;

                                            case strstr($servername, "HAB"): //if provider is HAB, then
                                                //check if redemption was successful, and insert information on manualredemptions and audittrail
                                                if ($withdraw['IsSucceed'] == true) {
                                                    //fetch the information when calling the RTG Withdraw Method
                                                    foreach ($withdraw as $results) {
                                                        $riserror = $results['withdrawmethodResult']['Message'];
                                                        $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                        $rremarks = $results['withdrawmethodResult']['Message'];
                                                        $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                        $reffdate = $otopup->getDate();
                                                    }

                                                    $fmteffdate = str_replace("T", " ", $reffdate);
                                                    $vsiteID = $siteID;
                                                    $vterminalID = $ubterminalID;
                                                    $vreportedAmt = $ramount;
                                                    $vactualAmt = $rwamount;
                                                    $vtransactionDate = $otopup->getDate();
                                                    $vreqByAID = $aid;
                                                    $vprocByAID = $aid;
                                                    $vdateEffective = $fmteffdate;
                                                    $vstatus = 1;
                                                    $vtransactionID = $rtransactionID;
                                                    $vremarks = $remarksub;
                                                    $vticket = $ticketub;
                                                    $cmbServerID = $ubserviceID;

                                                    //check if there was no error on withdrawal
                                                    if ($riserror == "Withdrawal Success") {
                                                        $vtransStatus = $rremarks;
                                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                        $transsummaryid = $transsummaryid['summaryID'];
                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                        if ($issucess > 0) {
                                                            //get new balance after redemption
                                                            $balance = $CasinoGamingCAPI->getBalance($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, null, $password);

                                                            $ramount = number_format($ramount, 2, ".", ",");
                                                            $balance = number_format($balance, 2, ".", ",");

                                                            //update member services
                                                            if ($otopupmembership->open()) {
                                                                $otopupmembership->updateMemberServices($balance, $mid, $ubserviceID, $issucess);
                                                            }
                                                            $otopupmembership->close();
                                                            //insert into audit trail
                                                            $vtransdetails = "transaction id " . $vtransactionID . ",amount " . $vreportedAmt;
                                                            $vauditfuncID = 7;
                                                            $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                                            $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption: Error on inserting redemption table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {
                                                        if ($riserror == "") {
                                                            $msg = $rremarks;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $status = 2;
                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                } else {
                                                    $status = 2;
                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                    $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                                break;

                                            default :
                                                $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                echo json_encode($msg);
                                                exit;
                                                break;
                                        }
                                    } else {
                                        $msg = "Manual Redemption: Error on inserting redemption table.";
                                        echo json_encode($msg);
                                        exit;
                                    }
                                }
                            }
                        } else {
                            $getTerminalSessionsDetails = $otopup->getTerminalSessionsDetails($loyaltycardnumber);

                            $ActiveServiceStatus = $getTerminalSessionsDetails['ActiveServiceStatus'];
                            $TransactionSummary = $getTerminalSessionsDetails['TransactionSummaryID'];

                            $tsTerminalID = $getTerminalSessionsDetails['TerminalID'];
                            $tsServiceID = $getTerminalSessionsDetails['ServiceID'];
                            $tsAmount = $getTerminalSessionsDetails['LastBalance'];

                            $GLOBAL_OldActiveServiceStatus = $otopup->getOldActiveServiceID($tsTerminalID);

                            $getSiteIDByTerminalID = $otopup->getSiteIDByTerminalID($tsTerminalID);

                            $tsSiteID = $getSiteIDByTerminalID;

                            $MaxTransferID = $otopup->getMaxTransferID($TransactionSummary);

                            if ($ActiveServiceStatus == 1 || $ActiveServiceStatus == 8 || $ActiveServiceStatus == 9) {
                                if ($ActiveServiceStatus == 1 || $ActiveServiceStatus == 9) {
                                    $NewActiveServiceStatus = 8;
                                    $updateTerminalSessions = $otopup->updateActiveServiceStatusTW($ActiveServiceStatus, $NewActiveServiceStatus, $loyaltycardnumber);
                                    $GLOBAL_OldActiveServiceStatus = $otopup->getOldActiveServiceID($tsTerminalID);
                                }

                                if ($serviceName == "PWW") {

                                    if (empty($MaxTransferID)) { // IF NO MZTRANSACTIONTRANSFER RECORD
                                        $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                        if ($updateActiveServiceStatusRollback) {
                                            $msg = "Cannot proceed with manual redemption. Kindly ask IR/CS to review player transactions.";
                                            echo json_encode($msg);
                                            exit;
                                        } else {
                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                            echo json_encode($msg);
                                            exit;
                                        }
                                    } else { //IF HAS MZTRANSACTIONTRANSFER RECORD
                                        $getMZTransactionTransferDetails = $otopup->getMZTransactionTransferDetails($TransactionSummary);

                                        $mzSiteID = $getMZTransactionTransferDetails['SiteID'];
                                        $mzTerminalID = $getMZTransactionTransferDetails['TerminalID'];
                                        $mzToServiceID = $getMZTransactionTransferDetails['ToServiceID'];
                                        $mzFromServiceID = $getMZTransactionTransferDetails['FromServiceID'];
                                        $mzMID = $getMZTransactionTransferDetails['MID'];
                                        $MzTransferStatus = (int) $getMZTransactionTransferDetails['TransferStatus'];


                                        if ($MzTransferStatus != 8 && $MzTransferStatus != 90 && $MzTransferStatus != 93) {
                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                            if ($updateActiveServiceStatusRollback) {
                                                $msg = "Cannot proceed with manual redemption. Kindly ask IR/CS to review player transactions.";
                                                echo json_encode($msg);
                                                exit;
                                            } else {
                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        }


                                        switch ($MzTransferStatus) {
                                            case 8:

                                                if ($otopupmembership->open()) {

                                                    $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzToServiceID);
                                                }

                                                $otopupmembership->close();

                                                $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                $getservicename = $otopup->getCasinoName($mzToServiceID);
                                                $servicegrpname = $otopup->getServiceGrpName($mzToServiceID);
                                                $serviceName = $getservicename[0]['ServiceName'];

                                                switch (true) {

                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                        //call get balance method of Habanero
                                                        $url = $_ServiceAPI[$mzToServiceID - 1];
                                                        $capiusername = $_HABbrandID;
                                                        $capipassword = $_HABapiKey;
                                                        $capiplayername = '';
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                        break;

                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                        //call get balance method of RTG
                                                        $url = $_ServiceAPI[$mzToServiceID - 1];
                                                        $capiusername = $_CAPIUsername;
                                                        $capipassword = $_CAPIPassword;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        break;

                                                    default :
                                                        $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                        echo json_encode($msg);
                                                        exit;
                                                        break;
                                                }

                                                $lastbalance = $balance;

                                                $vsiteID = $mzSiteID;
                                                $vterminalID = $mzTerminalID;
                                                $vreportedAmt = 0;
                                                $vactualAmt = 0;
                                                $vtransactionDate = $otopup->getDate();
                                                $vreqByAID = $aid;
                                                $vprocByAID = $aid;
                                                $vdateEffective = $otopup->getDate();
                                                $vstatus = 1;
                                                $vtransactionID = 0;
                                                $vremarks = $remarksub;
                                                $vticket = $ticketub;
                                                $cmbServerID = $mzToServiceID;
                                                $fromServiceID = 1;

                                                $vtransStatus = '';
                                                $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                $transsummaryid = $transsummaryid['summaryID'];

                                                $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $ubserviceID);

                                                $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $MaxTransferID, $fromServiceID);

                                                if ($lastmrid > 0) {

                                                    if ($lastbalance == 0) {
                                                        $TransferStatus = 100;
                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                        if (!$updateMzTransactionTransfer) {
                                                            $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }

                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $lastbalance, $mzTerminalID);

                                                        if ($updateTerminalSessionsCredentials) {
                                                            if ($otopupmembership->open() == true) {
                                                                $otopupmembership->updateMember($mzToServiceID, $mzMID);
                                                            }
                                                            $otopupmembership->close();

                                                            $msg = "Floating balance was successfully redeemed. \n Redeemed: " . $vactualAmt . "; Remaining Balance: " . $lastbalance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {
                                                        $msg = "Floating balance was successfully redeemed. \n Redeemed: " . $vactualAmt . "; Remaining Balance: " . $lastbalance;
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed to insert redemption table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                                break;

                                            case 90:

                                                if ($otopupmembership->open()) {
                                                    $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzFromServiceID);
                                                }

                                                $otopupmembership->close();

                                                $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                $getservicename = $otopup->getCasinoName($mzFromServiceID);
                                                $servicegrpname = $otopup->getServiceGrpName($mzFromServiceID);
                                                $serviceName = $getservicename[0]['ServiceName'];

                                                switch (true) {

                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                        //call get balance method of Habanero
                                                        $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                        $capiusername = $_HABbrandID;
                                                        $capipassword = $_HABapiKey;
                                                        $capiplayername = '';
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                        break;

                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                        //call get balance method of RTG
                                                        $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                        $capiusername = $_CAPIUsername;
                                                        $capipassword = $_CAPIPassword;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        break;
                                                }


                                                $ramount = ereg_replace(",", "", $balance); //format number replace (,)

                                                $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                if ($ramount > $_maxRedeem) {
                                                    $balance = $ramount - $_maxRedeem;
                                                    $ramount = $_maxRedeem;
                                                } else {
                                                    $balance = 0;
                                                }

												if ($ramount == 0) {
													$msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
													echo json_encode($msg);
													exit;
												}

                                                $vsiteID = $mzSiteID;
                                                $vterminalID = $mzTerminalID;
                                                $vreportedAmt = ereg_replace(",", "", $balance);
                                                $vactualAmt = $ramount;
                                                $vtransactionDate = $otopup->getDate();
                                                $vreqByAID = $aid;
                                                $vprocByAID = $aid;
                                                $vdateEffective = $otopup->getDate();
                                                $vstatus = 0;
                                                $vtransactionID = 0;
                                                $vremarks = $remarksub;
                                                $vticket = $ticketub;
                                                $cmbServerID = $mzFromServiceID;
                                                $fromServiceID = 1;

                                                $vtransStatus = '';
                                                $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                $transsummaryid = $transsummaryid['summaryID'];

                                                $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $MaxTransferID, $fromServiceID);

                                                if ($lastmrid > 0) {

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                            $tracking1 = "MR" . "$lastmrid";
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';

                                                            $withdraw = array();

                                                            $locatorname = null;
                                                            //withdraw rtg casino
                                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);

                                                            break;

                                                        case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                            $tracking1 = "MR" . "$lastmrid";
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';

                                                            $withdraw = array();

                                                            $locatorname = null;
                                                            //withdraw hab casino
                                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                            break;
                                                    }

                                                    if ($withdraw['IsSucceed'] == true) {
                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                    $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                    $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                    $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                    $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                }
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['withdrawmethodResult']['Message'];
                                                                    $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                    $rremarks = $results['withdrawmethodResult']['Message'];
                                                                    $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                    $reffdate = $otopup->getDate();
                                                                }
                                                                break;
                                                        }
                                                        if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                            //SUCCESS WITHDRAWAL

                                                            $fmteffdate = $otopup->getDate();
                                                            $vtransStatus = $rremarks;
                                                            $vstatus = 1;

                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess > 0) {

																if ($balance == 0) {
																	$TransferStatus = 100;

																	$updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);

																	if ($updateMzTransactionTransfer) {

																		$updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

																		if ($updateTerminalSessionsCredentials) {
																			if ($otopupmembership->open() == true) {
																				$otopupmembership->updateMember($mzFromServiceID, $mid);
																			}
																			$otopupmembership->close();

																			$msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
																			echo json_encode($msg);
																			exit;
																		} else {
																			$msg = "Manual Redemption Error : Failed to update sessions table.";
																			echo json_encode($msg);
																			exit;
																		}
																	} else {
																		$msg = "Manual Redemption Error : Failed to update transaction transfer table.";
																		echo json_encode($msg);
																		exit;
																	}
																} else {
																	$msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
																	echo json_encode($msg);
																	exit;
																}
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            if ($riserror == "") {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $rremarks;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $riserror; //error message when calling the withdrawal result
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {

                                                        //FAILED WITHDRAWAL

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                                break;
                                                        }


                                                        if ((!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED") || (!empty($transinfo["querytransmethodResult"]) && $transinfo['querytransmethodResult']['Success'] == true)) {

                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                        $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                        $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                        $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                        $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                    }
                                                                    break;
                                                                case strstr($serviceName, "Habanero UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['querytransmethodResult']['Success'];
                                                                        $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                        $rwamount = $results['querytransmethodResult']['Amount'];
                                                                        $rremarks = "Withdrawal Success";
                                                                        $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                        $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                    }
                                                                    break;
                                                            }

                                                            if (empty($riserror) || $riserror == true) {

                                                                $fmteffdate = $otopup->getDate();
                                                                $vactualAmt = $rwamount;
                                                                $vstatus = 1;
                                                                $vtransactionID = $rtransactionID;
                                                                $vtransStatus = $rremarks;

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                if ($issucess > 0) {


                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                            break;

                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                            break;
                                                                    }

                                                                    if ($balance == 0) {
                                                                        $TransferStatus = 100;
                                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                        if (!$updateMzTransactionTransfer) {
                                                                            $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }

                                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                        if ($updateTerminalSessionsCredentials) {
                                                                            if ($otopupmembership->open() == true) {
                                                                                $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                            }
                                                                            $otopupmembership->close();

                                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {

                                                                if ($riserror == "") {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $rremarks;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $riserror; //error message when calling the withdrawal result
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }
                                                        } else {
                                                            //FAILED TRANS INFO

                                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                            if ($updateActiveServiceStatusRollback) {
                                                                $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $msg = "Manual Redemption: Error on inserting redemption table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }

                                                break;

                                            case 93:

                                                if ($otopupmembership->open()) {

                                                    $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzToServiceID);
                                                }
                                                $otopupmembership->close();

                                                $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                $getservicename = $otopup->getCasinoName($mzToServiceID);
                                                $servicegrpname = $otopup->getServiceGrpName($mzToServiceID);
                                                $serviceName = $getservicename[0]['ServiceName'];

                                                switch (true) {

                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                        $url = $_ServiceAPI[$mzToServiceID - 1];
                                                        $capiusername = $_HABbrandID;
                                                        $capipassword = $_HABapiKey;
                                                        $capiplayername = '';
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                        break;

                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                        $url = $_ServiceAPI[$mzToServiceID - 1];
                                                        $capiusername = $_CAPIUsername;
                                                        $capipassword = $_CAPIPassword;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                        break;
                                                }

                                                $ramount = ereg_replace(",", "", $balance); //format number replace (,)

                                                $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                if ($ramount > $_maxRedeem) {
                                                    $balance = $ramount - $_maxRedeem;
                                                    $ramount = $_maxRedeem;
                                                } else {
                                                    $balance = 0;
                                                }

                                                $vsiteID = $mzSiteID;
                                                $vterminalID = $mzTerminalID;
                                                $vreportedAmt = ereg_replace(",", "", $balance);
                                                $vactualAmt = $ramount;
                                                $vtransactionDate = $otopup->getDate();
                                                $vreqByAID = $aid;
                                                $vprocByAID = $aid;
                                                $vdateEffective = $otopup->getDate();
                                                $vstatus = 0;
                                                $vtransactionID = 0;
                                                $vremarks = $remarksub;
                                                $vticket = $ticketub;
                                                $cmbServerID = $mzToServiceID;
                                                $fromServiceID = 1;

                                                $vtransStatus = '';
                                                $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                $transsummaryid = $transsummaryid['summaryID'];

                                                $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $mzToServiceID);

                                                $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $MaxTransferID, $fromServiceID);

                                                if ($lastmrid > 0) {

                                                    if ($vactualAmt == 0) {

                                                        $TransferStatus = 100;

                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);

                                                        if ($updateMzTransactionTransfer) {

                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

                                                            if ($updateTerminalSessionsCredentials) {

                                                                $fmteffdate = $otopup->getDate();
                                                                $vstatus = 2;
                                                                $vtransactionID = '';
                                                                $vtransStatus = '';

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                if ($issucess) {
                                                                    if ($otopupmembership->open() == true) {
                                                                        $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                    }
                                                                    $otopupmembership->close();

                                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    }

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                            $tracking1 = "MR" . "$lastmrid";
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';
                                                            $withdraw = array();
                                                            $locatorname = null;

                                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);

                                                            break;

                                                        case strstr($serviceName, "Habanero UB"):
                                                            $tracking1 = "MR" . "$lastmrid";
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';
                                                            $withdraw = array();
                                                            $locatorname = null;

                                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                            break;

                                                        default :
                                                            $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                            echo json_encode($msg);
                                                            exit;
                                                            break;
                                                    }

                                                    if ($withdraw['IsSucceed'] == true) {
                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                    $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                    $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                    $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                    $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                }
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['withdrawmethodResult']['Message'];
                                                                    $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                    $rremarks = $results['withdrawmethodResult']['Message'];
                                                                    $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                    $reffdate = $otopup->getDate();
                                                                }
                                                                break;
                                                        }
                                                        if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                            //SUCCESS WITHDRAWAL

                                                            $fmteffdate = $otopup->getDate();
                                                            $vtransStatus = $rremarks;
                                                            $vstatus = 1;

                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess > 0) {

                                                                $ramount = number_format($vactualAmt, 2, ".", ",");
                                                                $balance = number_format($balance, 2, ".", ",");

                                                                if ($balance == 0) {
                                                                    $TransferStatus = 100;
                                                                    $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                    if (!$updateMzTransactionTransfer) {
                                                                        $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }


                                                                    $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

                                                                    if ($updateTerminalSessionsCredentials) {
                                                                        if ($otopupmembership->open() == true) {
                                                                            $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                        }
                                                                        $otopupmembership->close();

                                                                        $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            if ($riserror == "") {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $rremarks;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $riserror; //error message when calling the withdrawal result
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        //FAILED WITHDRAWAL

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                                break;
                                                        }


                                                        if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                        $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                        $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                        $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                        $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                    }
                                                                    break;
                                                                case strstr($serviceName, "Habanero UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['querytransmethodResult']['Success'];
                                                                        $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                        $rwamount = $results['querytransmethodResult']['Amount'];
                                                                        $rremarks = "Withdrawal Success";
                                                                        $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                        $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                    }
                                                                    break;
                                                            }

                                                            if ($riserror) {

                                                                $fmteffdate = $otopup->getDate();
                                                                $vactualAmt = $rwamount;
                                                                $vstatus = 1;
                                                                $vtransactionID = $rtransactionID;
                                                                $vtransStatus = $rremarks;

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                if ($issucess > 0) {


                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                            break;

                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                            break;
                                                                    }

                                                                    if ($balance == 0) {
                                                                        $TransferStatus = 100;
                                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                        if (!$updateMzTransactionTransfer) {
                                                                            $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }

                                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                        if ($updateTerminalSessionsCredentials) {
                                                                            if ($otopupmembership->open() == true) {
                                                                                $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                            }
                                                                            $otopupmembership->close();

                                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {

                                                                if ($riserror == "") {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $rremarks;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $riserror; //error message when calling the withdrawal result
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }
                                                        } else {
                                                            //FAILED TRANS INFO

                                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                            if ($updateActiveServiceStatusRollback) {
                                                                $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $msg = "Manual Redemption: Error on inserting redemption table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }

                                                break;

                                            default:
                                                $msg = "Manual Redemption Error: Invalid Active Service Status";
                                                echo json_encode($msg);
                                                exit;
                                                break;
                                        }
                                    }
                                } else if ($servicegrpname == "RTG" || $servicegrpname == "RTG2" || $servicegrpname == "HAB") {

                                    $getTerminalSessionsDetails = $otopup->getTerminalSessionsDetails($loyaltycardnumber);

                                    $ActiveServiceStatus = $getTerminalSessionsDetails['ActiveServiceStatus'];
                                    $TransactionSummary = $getTerminalSessionsDetails['TransactionSummaryID'];

                                    $tsTerminalID = $getTerminalSessionsDetails['TerminalID'];
                                    $tsServiceID = $getTerminalSessionsDetails['ServiceID'];
                                    $tsAmount = $getTerminalSessionsDetails['LastBalance'];

                                    $getSiteIDByTerminalID = $otopup->getSiteIDByTerminalID($tsTerminalID);

                                    $tsSiteID = $getSiteIDByTerminalID;

                                    if (empty($MaxTransferID)) { // IF NO MZTRANSACTIONTRANSFER RECORD
                                        if ($tsServiceID <> $ubserviceID) {

                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);

                                            if ($updateActiveServiceStatusRollback) {
                                                $msg = "No pending transaction to fulfil";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        }

                                        if ($otopupmembership->open()) {

                                            $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $tsServiceID);
                                        }

                                        $otopupmembership->close();

                                        $ramount = ereg_replace(",", "", $tsAmount); //format number replace (,)

                                        $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                        if ($ramount > $_maxRedeem) {
                                            $balance = $ramount - $_maxRedeem;
                                            $ramount = $_maxRedeem;
                                        } else {
                                            $balance = 0;
                                        }

                                        $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                        $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                        $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                        $usermode = $getPlayerCredentialsByUB['Usermode'];

                                        $getservicename = $otopup->getCasinoName($tsServiceID);
                                        $servicegrpname = $otopup->getServiceGrpName($tsServiceID);
                                        $serviceName = $getservicename[0]['ServiceName'];

                                        $vsiteID = $tsSiteID;
                                        $vterminalID = $tsTerminalID;
                                        $vreportedAmt = ereg_replace(",", "", $tsAmount);
                                        $vactualAmt = $ramount;
                                        $vtransactionDate = $otopup->getDate();
                                        $vreqByAID = $aid;
                                        $vprocByAID = $aid;
                                        $vdateEffective = $otopup->getDate();
                                        $vstatus = 0;
                                        $vtransactionID = 0;
                                        $vremarks = $remarksub;
                                        $vticket = $ticketub;
                                        $cmbServerID = $tsServiceID;

                                        $vtransStatus = '';
                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                        $transsummaryid = $transsummaryid['summaryID'];

                                        $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $tsServiceID);

                                        $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode);

                                        if ($lastmrid > 0) {

                                            switch (true) {
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                    if (is_null($trans_req_log_last_id)) {
                                                        $trans_req_log_last_id = "";
                                                    }
                                                    if (is_null($vterminalID)) {
                                                        $vterminalID = "";
                                                    }

                                                    $url = $_ServiceAPI[$tsServiceID - 1];
                                                    $capiusername = $_CAPIUsername;
                                                    $capipassword = $_CAPIPassword;
                                                    $capiplayername = $_CAPIPlayerName;
                                                    $capiserverID = '';

                                                    $tracking1 = "MR" . "$lastmrid";
                                                    $tracking2 = '';
                                                    $tracking3 = '';
                                                    $withdraw = array();
                                                    //get siteclassificationID
                                                    $siteClass = $otopup->getSiteClassByTerminal($vterminalID);

                                                    $locatorname = null;

                                                    //get new balance after redemption
                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                    if ($balance == 0) {
                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $tsServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $tsTerminalID);

                                                        if ($updateTerminalSessionsCredentials) {
                                                            $vtransStatus = '';
                                                            $vtransactionID = '';
                                                            $vstatus = 2;
                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $balance, $vtransactionID, $vdateEffective, $vtransStatus, $lastmrid);

                                                            $updateTerminalSessionsCredentials = $otopup->updateMember($tsServiceID, $mid);

                                                            $msg = "Redeemed: " . $balance . "; Remaining Balance: " . $balance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }

                                                    //withdraw rtg casino
                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);

                                                    break;

                                                case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                    if (is_null($trans_req_log_last_id)) {
                                                        $trans_req_log_last_id = "";
                                                    }
                                                    if (is_null($vterminalID)) {
                                                        $vterminalID = "";
                                                    }

                                                    $url = $_ServiceAPI[$tsServiceID - 1];
                                                    $capiusername = $_HABbrandID;
                                                    $capipassword = $_HABapiKey;
                                                    $capiplayername = $_CAPIPlayerName;
                                                    $capiserverID = '';
                                                    $tracking1 = "MR" . "$lastmrid";
                                                    $tracking2 = '';
                                                    $tracking3 = '';
                                                    $withdraw = array();
                                                    //get siteclassificationID
                                                    $siteClass = $otopup->getSiteClassByTerminal($vterminalID);

                                                    $locatorname = null;



                                                    //get new balance after redemption
                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, null, $UBServicePassword);

                                                    if ($balance == 0) {
                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $tsServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $tsTerminalID);

                                                        if ($updateTerminalSessionsCredentials) {
                                                            $vtransStatus = '';
                                                            $vtransactionID = '';
                                                            $vstatus = 2;
                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $balance, $vtransactionID, $vdateEffective, $vtransStatus, $lastmrid);

                                                            $updateTerminalSessionsCredentials = $otopup->updateMember($tsServiceID, $mid);

                                                            $msg = "Redeemed: " . $balance . "; Remaining Balance: " . $balance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }


                                                    //withdraw hab casino
                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                    break;

                                                default :
                                                    $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                    echo json_encode($msg);
                                                    exit;
                                                    break;
                                            }

                                            switch (true) {

                                                case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                    //check if redemption was successfull, and insert information on manualredemptions and audittrail
                                                    if ($withdraw['IsSucceed'] == true) {
                                                        //fetch the information when calling the RTG Withdraw Method
                                                        foreach ($withdraw as $results) {
                                                            $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                            $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                            $rwamount = $results['WithdrawGenericResult']['amount'];
                                                            $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                            $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                        }
                                                        $fmteffdate = str_replace("T", " ", $reffdate);
                                                        $vsiteID = $tsSiteID;
                                                        $vterminalID = $tsServiceID;
                                                        $vreportedAmt = $tsAmount;
                                                        $vactualAmt = $ramount;
                                                        $vtransactionDate = $otopup->getDate();
                                                        $vreqByAID = $aid;
                                                        $vprocByAID = $aid;
                                                        //$vremarks = $rremarks;
                                                        $vdateEffective = $fmteffdate;
                                                        $vstatus = 01;
                                                        $vtransactionID = $rtransactionID;
                                                        $vremarks = $remarksub;
                                                        $vticket = $ticketub;
                                                        $cmbServerID = $tsServiceID;

                                                        //check if there was no error on withdrawal
                                                        if ($riserror == "OK") {
                                                            $vtransStatus = $rremarks;
                                                            $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                            $transsummaryid = $transsummaryid['summaryID'];
                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess > 0) {

                                                                //get new balance after redemption
                                                                $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $tsServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $tsTerminalID);

                                                                if ($updateTerminalSessionsCredentials) {
                                                                    $updateTerminalSessionsCredentials = $otopup->updateMember($tsServiceID, $mid);

                                                                    $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }


                                                                $ramount = number_format($ramount, 2, ".", ",");
                                                                $balance = number_format($balance, 2, ".", ",");

                                                                //update member services
                                                                if ($otopupmembership->open()) {
                                                                    $otopupmembership->updateMemberServices($balance, $mid, $tsServiceID, $issucess);
                                                                }
                                                                $otopupmembership->close();
                                                            } else {
                                                                $msg = "Manual Redemption: Error on inserting redemption table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            if ($riserror == "") {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $rremarks;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $riserror; //error message when calling the withdrawal result
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        $status = 2;
                                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                        $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                    break;

                                                case strstr($serviceName, "Habanero UB"): //if provider is HAB, then
                                                    //check if redemption was successful, and insert information on manualredemptions and audittrail
                                                    if ($withdraw['IsSucceed'] == true) {
                                                        //fetch the information when calling the RTG Withdraw Method
                                                        foreach ($withdraw as $results) {
                                                            $riserror = $results['withdrawmethodResult']['Message'];
                                                            $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                            $rremarks = $results['withdrawmethodResult']['Message'];
                                                            $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                            $reffdate = $otopup->getDate();
                                                        }

                                                        $fmteffdate = str_replace("T", " ", $reffdate);
                                                        $vsiteID = $tsSiteID;
                                                        $vterminalID = $tsTerminalID;
                                                        $vreportedAmt = $tsAmount;
                                                        $vactualAmt = $ramount;
                                                        $vtransactionDate = $otopup->getDate();
                                                        $vreqByAID = $aid;
                                                        $vprocByAID = $aid;
                                                        $vdateEffective = $fmteffdate;
                                                        $vstatus = 1;
                                                        $vtransactionID = $rtransactionID;
                                                        $vremarks = $remarksub;
                                                        $vticket = $ticketub;
                                                        $cmbServerID = $tsServiceID;

                                                        //check if there was no error on withdrawal
                                                        if ($riserror == "Withdrawal Success") {
                                                            $vtransStatus = $rremarks;
                                                            $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                            $transsummaryid = $transsummaryid['summaryID'];
                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess > 0) {


                                                                //get new balance after redemption
                                                                $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, null, $UBServicePassword);

                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $tsServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $tsTerminalID);

                                                                if ($updateTerminalSessionsCredentials) {
                                                                    $updateTerminalSessionsCredentials = $otopup->updateMember($tsServiceID, $mid);

                                                                    //update member services
                                                                    if ($otopupmembership->open()) {
                                                                        $otopupmembership->updateMemberServices($balance, $mid, $tsServiceID, $issucess);
                                                                    }
                                                                    $otopupmembership->close();

                                                                    $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption: Error on inserting redemption table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            if ($riserror == "") {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $rremarks;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $riserror; //error message when calling the withdrawal result
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        $status = 2;
                                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                        $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                    break;

                                                default :
                                                    $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                    echo json_encode($msg);
                                                    exit;
                                                    break;
                                            }
                                        }
                                    } else { //IF HAS MZTRANSACTIONTRANSFER RECORD
                                        $getMZTransactionTransferDetails = $otopup->getMZTransactionTransferDetails($TransactionSummary);

                                        $mzSiteID = $getMZTransactionTransferDetails['SiteID'];
                                        $mzTerminalID = $getMZTransactionTransferDetails['TerminalID'];
                                        $mzToServiceID = $getMZTransactionTransferDetails['ToServiceID'];
                                        $mzFromServiceID = $getMZTransactionTransferDetails['FromServiceID'];
                                        $mzMID = $getMZTransactionTransferDetails['MID'];
                                        $MzTransferStatus = (int) $getMZTransactionTransferDetails['TransferStatus'];
                                        $mzToStatus = $getMZTransactionTransferDetails['ToStatus'];
                                        $mzFromStatus = $getMZTransactionTransferDetails['FromStatus'];
                                        $mzFromAmount = $getMZTransactionTransferDetails['FromAmount'];
                                        $mzToAmount = $getMZTransactionTransferDetails['ToAmount'];
                                        $mzTransferID = $getMZTransactionTransferDetails['TransferID'];
                                        $mzFromTransactionType = "MZ" . $getMZTransactionTransferDetails['FromTransactionType'];
                                        $mzToTransactionType = "MZ" . $getMZTransactionTransferDetails['ToTransactionType'];

                                        if ($MzTransferStatus == 8 || $MzTransferStatus == 90 || $MzTransferStatus == 93) {

                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                            if ($updateActiveServiceStatusRollback) {
                                                $msg = "Cannot proceed with manual redemption as floating balance was found for this player.";
                                                echo json_encode($msg);
                                                exit;
                                            } else {
                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        } elseif ($MzTransferStatus == 1) {

                                            if ($otopupmembership->open()) {

                                                $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzFromServiceID);
                                            }

                                            $otopupmembership->close();

                                            $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                            $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                            $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                            $usermode = $getPlayerCredentialsByUB['Usermode'];

                                            $getservicename = $otopup->getCasinoName($mzFromServiceID);
                                            $servicegrpname = $otopup->getServiceGrpName($mzFromServiceID);
                                            $serviceName = $getservicename[0]['ServiceName'];

                                            $vsiteID = $mzSiteID;
                                            $vterminalID = $mzTerminalID;
                                            $vreportedAmt = ereg_replace(",", "", 0);
                                            $vactualAmt = 0;
                                            $vtransactionDate = $otopup->getDate();
                                            $vreqByAID = $aid;
                                            $vprocByAID = $aid;
                                            $vdateEffective = $otopup->getDate();
                                            $vstatus = 1;
                                            $vtransactionID = 0;
                                            $vremarks = '';
                                            $vticket = '';
                                            $cmbServerID = $mzFromServiceID;

                                            $vtransStatus = "";
                                            $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                            $transsummaryid = $transsummaryid['summaryID'];

                                            $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzFromServiceID);

                                            if ($lastmrid > 0) {

                                                if ($vactualAmt == 0) {
                                                    $TransferStatus = 101;
                                                    $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                    if (!$updateMzTransactionTransfer) {
                                                        $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }

                                                    $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

                                                    if ($updateTerminalSessionsCredentials) {
                                                        if ($otopupmembership->open() == true) {
                                                            $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                        }

                                                        $msg = "Successful " . str_replace(array('2'), '', $servicegrpname) . " Casino Fulfillment";
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                } else {
                                                    $msg = "Successful " . str_replace(array('2'), '', $servicegrpname) . " Casino Fulfillment";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {
                                                $msg = "Manual Redemption: Error on inserting redemption table.";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        } elseif ($MzTransferStatus == 2) {



                                            if ($mzFromServiceID <> $ubserviceID) {
                                                $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                if ($updateActiveServiceStatusRollback) {
                                                    $msg = "Invalid casino to fulfil.";
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {
                                                if ($otopupmembership->open() == true) {
                                                    $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzFromServiceID);
                                                }

                                                $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                switch (true) {
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                        $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                        $capiusername = $_CAPIUsername;
                                                        $capipassword = $_CAPIPassword;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';

                                                        $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                        break;
                                                    case strstr($serviceName, "Habanero UB"):

                                                        $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                        $capiusername = $_HABbrandID;
                                                        $capipassword = $_HABapiKey;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';

                                                        $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                        break;
                                                }

                                                $ramount = ereg_replace(",", "", $getbalance); //format number replace (,)

                                                $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                if ($ramount > $_maxRedeem) {
                                                    $balance = $ramount - $_maxRedeem;
                                                    $ramount = $_maxRedeem;
                                                } else {
                                                    $balance = 0;
                                                }

                                                $fmteffdate = $otopup->getDate();
                                                $vsiteID = $mzSiteID;
                                                $vterminalID = $mzTerminalID;
                                                $vreportedAmt = ereg_replace(",", "", $getbalance);
                                                $vactualAmt = $ramount;
                                                $vtransactionDate = $otopup->getDate();
                                                $vreqByAID = $aid;
                                                $vprocByAID = $aid;
                                                $vdateEffective = $otopup->getDate();
                                                $vstatus = 0;
                                                $vtransactionID = '';
                                                $vremarks = $remarksub;
                                                $vticket = $ticketub;
                                                $cmbServerID = $mzFromServiceID;

                                                $vtransStatus = '';
                                                $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                $transsummaryid = $transsummaryid['summaryID'];

                                                $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzFromServiceID);

                                                if ($lastmrid > 0) {

                                                    if ($vactualAmt == 0) {

                                                        if ($vactualAmt == 0) {
                                                            $TransferStatus = 101;
                                                            $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                            if (!$updateMzTransactionTransfer) {
                                                                $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }

                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $mzToAmount, $mzTerminalID);

                                                        if ($updateTerminalSessionsCredentials) {

                                                            $fmteffdate = $otopup->getDate();
                                                            $vstatus = 2;
                                                            $vtransactionID = '';
                                                            $vtransStatus = '';

                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess) {
                                                                if ($otopupmembership->open() == true) {
                                                                    $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                }
                                                                $otopupmembership->close();

                                                                $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                            $locatorname = null;
                                                            $tracking1 = "MR" . "$lastmrid";
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';
                                                            $withdraw = array();

                                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                            break;

                                                        case strstr($serviceName, "Habanero UB"):
                                                            $locatorname = null;
                                                            $tracking1 = "MR" . "$lastmrid";
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';
                                                            $withdraw = array();

                                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                            break;
                                                    }

                                                    if ($withdraw['IsSucceed'] == true) {
                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                    $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                    $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                    $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                    $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                }
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['withdrawmethodResult']['Message'];
                                                                    $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                    $rremarks = $results['withdrawmethodResult']['Message'];
                                                                    $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                    $reffdate = $otopup->getDate();
                                                                }
                                                                break;
                                                        }


                                                        if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                            //SUCCESS WITHDRAWAL
                                                            $fmteffdate = $otopup->getDate();
                                                            $vtransStatus = $rremarks;
                                                            $vstatus = 1;

                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess > 0) {


                                                                $ramount = number_format($vactualAmt, 2, ".", ",");
                                                                $balance = number_format($balance, 2, ".", ",");

                                                                if ($balance == 0) {
                                                                    $TransferStatus = 101;
                                                                    $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                    if (!$updateMzTransactionTransfer) {
                                                                        $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                    $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                    if ($updateTerminalSessionsCredentials) {
                                                                        if ($otopupmembership->open() == true) {
                                                                            $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                        }
                                                                        $otopupmembership->close();

                                                                        $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        //FAILED WITHDRAWAL

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then

                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);

                                                                break;

                                                            case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then

                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                break;
                                                        }

                                                        if ((!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED") || (!empty($transinfo["querytransmethodResult"]) && $transinfo['querytransmethodResult']['Success'] == true)) {
                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                        $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                        $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                        $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                        $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                    }
                                                                    break;
                                                                case strstr($serviceName, "Habanero UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['querytransmethodResult']['Success'];
                                                                        $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                        $rwamount = $results['querytransmethodResult']['Amount'];
                                                                        $rremarks = "Withdrawal Success";
                                                                        $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                        $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                    }
                                                                    break;
                                                            }

                                                            if (empty($riserror) || $riserror == true) {
                                                                $fmteffdate = $otopup->getDate();
                                                                $vactualAmt = $rwamount;
                                                                $vstatus = 1;
                                                                $vtransactionID = $rtransactionID;
                                                                $vtransStatus = $rremarks;

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                if ($issucess > 0) {

                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                                            $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                                            $capiusername = $_CAPIUsername;
                                                                            $capipassword = $_CAPIPassword;
                                                                            $capiplayername = $_CAPIPlayerName;
                                                                            $capiserverID = '';

                                                                            $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                            break;
                                                                        case strstr($serviceName, "Habanero UB"):

                                                                            $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                                            $capiusername = $_HABbrandID;
                                                                            $capipassword = $_HABapiKey;
                                                                            $capiplayername = $_CAPIPlayerName;
                                                                            $capiserverID = '';

                                                                            $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                            break;
                                                                    }

                                                                    if ($getbalance == 0) {
                                                                        $TransferStatus = 101;
                                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                        if (!$updateMzTransactionTransfer) {
                                                                            $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }

                                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $getbalance, $mzTerminalID);

                                                                        if ($updateTerminalSessionsCredentials) {
                                                                            if ($otopupmembership->open() == true) {
                                                                                $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                            }
                                                                            $otopupmembership->close();

                                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $getbalance;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $getbalance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {

                                                                if ($riserror == "") {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $rremarks;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $riserror; //error message when calling the withdrawal result
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }
                                                        } else {
                                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                            if ($updateActiveServiceStatusRollback) {
                                                                $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $msg = "Manual Redemption: Error on inserting redemption table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            }
                                        } elseif ($MzTransferStatus == 4 || $MzTransferStatus == 5 || $MzTransferStatus == 7 || $MzTransferStatus == 9 || $MzTransferStatus == 100 || $MzTransferStatus == 101) {




                                            $getTerminalSessionsDetails = $otopup->getTerminalSessionsDetails($loyaltycardnumber);

                                            $ActiveServiceStatus = $getTerminalSessionsDetails['ActiveServiceStatus'];
                                            $TransactionSummary = $getTerminalSessionsDetails['TransactionSummaryID'];
                                            $tsTerminalID = $getTerminalSessionsDetails['TerminalID'];
                                            $tsServiceID = $getTerminalSessionsDetails['ServiceID'];

                                            $getSiteIDByTerminalID = $otopup->getSiteIDByTerminalID($tsTerminalID);
                                            $tsSiteID = $getSiteIDByTerminalID;


                                            if ($otopupmembership->open()) {

                                                $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $tsServiceID);
                                            }

                                            $otopupmembership->close();

                                            $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                            $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                            $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                            $usermode = $getPlayerCredentialsByUB['Usermode'];

                                            $getservicename = $otopup->getCasinoName($tsServiceID);
                                            $servicegrpname = $otopup->getServiceGrpName($tsServiceID);
                                            $serviceName = $getservicename[0]['ServiceName'];

                                            switch (true) {
                                                case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                    $url = $_ServiceAPI[$tsServiceID - 1];
                                                    $capiusername = $_HABbrandID;
                                                    $capipassword = $_HABapiKey;
                                                    $capiplayername = '';
                                                    $capiserverID = '';
                                                    $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                    break;
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then 
                                                    $url = $_ServiceAPI[$tsServiceID - 1];
                                                    $capiusername = $_CAPIUsername;
                                                    $capipassword = $_CAPIPassword;
                                                    $capiplayername = $_CAPIPlayerName;
                                                    $capiserverID = '';
                                                    $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    break;
                                            }

                                            $ramount = ereg_replace(",", "", $getbalance); //format number replace (,)

                                            $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                            if ($ramount > $_maxRedeem) {
                                                $balance = $ramount - $_maxRedeem;
                                                $ramount = $_maxRedeem;
                                            } else {
                                                $balance = 0;
                                            }

                                            $vsiteID = $tsSiteID;
                                            $vterminalID = $tsTerminalID;
                                            $vreportedAmt = ereg_replace(",", "", $getbalance);
                                            $vactualAmt = $ramount;
                                            $vtransactionDate = $otopup->getDate();
                                            $vreqByAID = $aid;
                                            $vprocByAID = $aid;
                                            $vdateEffective = $otopup->getDate();
                                            $vstatus = 0;
                                            $vtransactionID = 0;
                                            $vremarks = $remarksub;
                                            $vticket = $ticketub;
                                            $cmbServerID = $tsServiceID;

                                            $vtransStatus = '';
                                            $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                            $transsummaryid = $transsummaryid['summaryID'];

                                            $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $tsServiceID);

                                            $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode);

                                            if ($lastmrid > 0) {

                                                if ($vactualAmt == 0) {
                                                    $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $tsServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $tsTerminalID);

                                                    if ($updateTerminalSessionsCredentials) {
                                                        $vtransStatus = '';
                                                        $vstatus = 2;
                                                        $fmteffdate = $otopup->getDate();
                                                        $vtransactionID = '';
                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $balance, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                        $updateTerminalSessionsCredentials = $otopup->updateMember($tsServiceID, $mid);

                                                        $msg = "Redeemed: " . $balance . "; Remaining Balance: " . $balance;
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                }

                                                switch (true) {
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                        $tracking1 = "MR" . "$lastmrid";
                                                        $tracking2 = '';
                                                        $tracking3 = '';
                                                        $tracking4 = '';
                                                        $withdraw = array();
                                                        $locatorname = null;

                                                        $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                        break;

                                                    case strstr($serviceName, "Habanero UB"):
                                                        $tracking1 = "MR" . "$lastmrid";
                                                        $tracking2 = '';
                                                        $tracking3 = '';
                                                        $tracking4 = '';
                                                        $withdraw = array();
                                                        $locatorname = null;

                                                        $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $tsServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                        break;
                                                }


                                                if ($withdraw['IsSucceed'] == true) {
                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                            foreach ($withdraw as $results) {
                                                                $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                            }
                                                            break;
                                                        case strstr($serviceName, "Habanero UB"):
                                                            foreach ($withdraw as $results) {
                                                                $riserror = $results['withdrawmethodResult']['Message'];
                                                                $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                $rremarks = $results['withdrawmethodResult']['Message'];
                                                                $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                $reffdate = $otopup->getDate();
                                                            }
                                                            break;
                                                    }

                                                    if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                        //SUCCESS WITHDRAWAL

                                                        $fmteffdate = $otopup->getDate();
                                                        $vtransStatus = $rremarks;
                                                        $vstatus = 1;

                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                        if ($issucess > 0) {

                                                            $ramount = number_format($vactualAmt, 2, ".", ",");
                                                            $balance = number_format($balance, 2, ".", ",");

                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $tsServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

                                                            if ($updateTerminalSessionsCredentials) {
                                                                if ($otopupmembership->open() == true) {
                                                                    $otopupmembership->updateMember($tsServiceID, $mid);
                                                                }
                                                                $otopupmembership->close();

                                                                $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update redemption table";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {
                                                        if ($riserror == "") {
                                                            $status = 2;
                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                            $msg = $rremarks;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $status = 2;
                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                } else {
                                                    $status = 2;
                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                    $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {
                                                $msg = "Manual Redemption: Error on inserting redemption table.";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        } elseif ($MzTransferStatus == 91) {

                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                            if ($updateActiveServiceStatusRollback) {
                                                $msg = "Cannot proceed with manual redemption. Transferred amount not equal to withdrawn amount. Kindly ask IR/CS to review player transactions.";
                                                echo json_encode($msg);
                                                exit;
                                            } else {
                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        } elseif ($MzTransferStatus == 92) {

                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                            if ($updateActiveServiceStatusRollback) {
                                                $msg = "Cannot proceed with manual redemption. Re-Deposit amount not equal to withdrawn amount. Kindly ask IR/CS to review player transactions.";
                                                echo json_encode($msg);
                                                exit;
                                            } else {
                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        } elseif ($MzTransferStatus == 0) {

                                            if ($mzFromServiceID <> $ubserviceID) {
                                                $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                if ($updateActiveServiceStatusRollback) {
                                                    $msg = "Invalid casino to fulfil.";
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {
                                                if ($mzFromStatus <> 0 || $mzToStatus <> NULL) {
                                                    $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                    if ($updateActiveServiceStatusRollback) {
                                                        $msg = "An unexpected transaction was encountered. Kindly ask IR/CS to review player transactions.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                }
                                                if ($mzFromStatus == 0 && $mzToStatus == NULL) {

                                                    if ($otopupmembership->open()) {
                                                        $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzFromServiceID);
                                                    }
                                                    $otopupmembership->close();

                                                    $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                    $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                    $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                    $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                    $getservicename = $otopup->getCasinoName($mzFromServiceID);
                                                    $servicegrpname = $otopup->getServiceGrpName($mzFromServiceID);
                                                    $serviceName = $getservicename[0]['ServiceName'];

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                            $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                            $capiusername = $_CAPIUsername;
                                                            $capipassword = $_CAPIPassword;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';
                                                            $tracking1 = $mzTransferID . $mzFromTransactionType;
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';

                                                            $transinfo = array();

                                                            $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);

                                                            break;

                                                        case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                            $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                            $capiusername = $_HABbrandID;
                                                            $capipassword = $_HABapiKey;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';
                                                            $tracking1 = $mzTransferID . $mzFromTransactionType;
                                                            $tracking2 = '';
                                                            $tracking3 = '';
                                                            $tracking4 = '';

                                                            $transinfo = array();

                                                            $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                            break;

                                                        default :
                                                            $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                            echo json_encode($msg);
                                                            exit;
                                                            break;
                                                    }

                                                    if ((!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED") || (!empty($transinfo["querytransmethodResult"]) && $transinfo['querytransmethodResult']['Success'] == true)) {
                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                foreach ($transinfo as $results) {
                                                                    $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                    $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                    $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                    $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                    $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                }
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                foreach ($transinfo as $results) {
                                                                    $riserror = $results['querytransmethodResult']['Success'];
                                                                    $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                    $rwamount = $results['querytransmethodResult']['Amount'];
                                                                    $rremarks = "Withdrawal Success";
                                                                    $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                    $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                }
                                                                break;
                                                        }

                                                        if (empty($riserror) || $riserror == true) {

                                                            $vsiteID = $mzSiteID;
                                                            $vterminalID = $mzTerminalID;
                                                            $vreportedAmt = ereg_replace(",", "", 0);
                                                            $vactualAmt = 0;
                                                            $vtransactionDate = $otopup->getDate();
                                                            $vreqByAID = $aid;
                                                            $vprocByAID = $aid;
                                                            $vdateEffective = $otopup->getDate();
                                                            $vstatus = 1;
                                                            $vtransactionID = $rtransactionID;
                                                            $vremarks = $remarksub;
                                                            $vticket = $ticketub;
                                                            $cmbServerID = $mzFromServiceID;

                                                            $vtransStatus = $rremarks;
                                                            $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                            $transsummaryid = $transsummaryid['summaryID'];

                                                            $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzFromServiceID);

                                                            if ($lastmrid > 0) {
                                                                $fmteffdate = $otopup->getDate();
                                                                $vactualAmt = 0;
                                                                $vstatus = 1;
                                                                $vtransactionID = $rtransactionID;
                                                                $vtransStatus = $rremarks;

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                if ($issucess > 0) {
                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                            break;

                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                            break;
                                                                    }

                                                                    if ($balance == 0) {
                                                                        $TransferStatus = 101;
                                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                        if (!$updateMzTransactionTransfer) {
                                                                            $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                        if ($updateTerminalSessionsCredentials) {
                                                                            if ($otopupmembership->open() == true) {
                                                                                $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                            }
                                                                            $otopupmembership->close();

                                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption: Error on inserting redemption table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        // FAILED TRANSACTION INFO
                                                        if ($otopupmembership->open() == true) {

                                                            $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzFromServiceID);
                                                        }

                                                        $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                        $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                        $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                        $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                                $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                                $capiusername = $_CAPIUsername;
                                                                $capipassword = $_CAPIPassword;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';

                                                                $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):

                                                                $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                                $capiusername = $_HABbrandID;
                                                                $capipassword = $_HABapiKey;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';

                                                                $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                break;
                                                        }

                                                        $ramount = ereg_replace(",", "", $getbalance); //format number replace (,)

                                                        $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                        if ($ramount > $_maxRedeem) {
                                                            $balance = $ramount - $_maxRedeem;
                                                            $ramount = $_maxRedeem;
                                                        } else {
                                                            $balance = 0;
                                                        }

                                                        $fmteffdate = $otopup->getDate();
                                                        $vsiteID = $mzSiteID;
                                                        $vterminalID = $mzTerminalID;
                                                        $vreportedAmt = ereg_replace(",", "", $getbalance);
                                                        $vactualAmt = $ramount;
                                                        $vtransactionDate = $otopup->getDate();
                                                        $vreqByAID = $aid;
                                                        $vprocByAID = $aid;
                                                        $vdateEffective = $otopup->getDate();
                                                        $vstatus = 0;
                                                        $vtransactionID = '';
                                                        $vremarks = $remarksub;
                                                        $vticket = $ticketub;
                                                        $cmbServerID = $mzFromServiceID;

                                                        $vtransStatus = '';
                                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                        $transsummaryid = $transsummaryid['summaryID'];

                                                        $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzFromServiceID);

                                                        if ($lastmrid > 0) {

                                                            if ($vactualAmt == 0) {

                                                                if ($vactualAmt == 0) {
                                                                    $TransferStatus = 101;
                                                                    $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                    if (!$updateMzTransactionTransfer) {
                                                                        $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }

                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $mzToAmount, $mzTerminalID);

                                                                if ($updateTerminalSessionsCredentials) {

                                                                    $fmteffdate = $otopup->getDate();
                                                                    $vstatus = 2;
                                                                    $vtransactionID = '';
                                                                    $vtransStatus = '';

                                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                    if ($issucess) {
                                                                        if ($otopupmembership->open() == true) {
                                                                            $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                        }
                                                                        $otopupmembership->close();

                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }

                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    $locatorname = null;
                                                                    $tracking1 = "MR" . "$lastmrid";
                                                                    $tracking2 = '';
                                                                    $tracking3 = '';
                                                                    $tracking4 = '';
                                                                    $withdraw = array();

                                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                                    break;

                                                                case strstr($serviceName, "Habanero UB"):
                                                                    $locatorname = null;
                                                                    $tracking1 = "MR" . "$lastmrid";
                                                                    $tracking2 = '';
                                                                    $tracking3 = '';
                                                                    $tracking4 = '';
                                                                    $withdraw = array();

                                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                                    break;
                                                            }

                                                            if ($withdraw['IsSucceed'] == true) {
                                                                switch (true) {
                                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                        foreach ($withdraw as $results) {
                                                                            $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                            $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                            $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                            $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                            $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                        }
                                                                        break;
                                                                    case strstr($serviceName, "Habanero UB"):
                                                                        foreach ($withdraw as $results) {
                                                                            $riserror = $results['withdrawmethodResult']['Message'];
                                                                            $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                            $rremarks = $results['withdrawmethodResult']['Message'];
                                                                            $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                            $reffdate = $otopup->getDate();
                                                                        }
                                                                        break;
                                                                }


                                                                if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                                    //SUCCESS WITHDRAWAL
                                                                    $fmteffdate = $otopup->getDate();
                                                                    $vtransStatus = $rremarks;
                                                                    $vstatus = 1;

                                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                    if ($issucess > 0) {

                                                                        if ($balance == 0) {
                                                                            $TransferStatus = 101;
                                                                            $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                            if (!$updateMzTransactionTransfer) {
                                                                                $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }

                                                                            $ramount = number_format($vactualAmt, 2, ".", ",");
                                                                            $balance = number_format($balance, 2, ".", ",");

                                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                            if ($updateTerminalSessionsCredentials) {
                                                                                if ($otopupmembership->open() == true) {
                                                                                    $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                                }
                                                                                $otopupmembership->close();

                                                                                $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            } else {
                                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {
                                                                //FAILED WITHDRAWAL

                                                                switch (true) {
                                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then

                                                                        $transinfo = array();

                                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);

                                                                        break;

                                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then

                                                                        $transinfo = array();

                                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                        break;
                                                                }

                                                                if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            foreach ($transinfo as $results) {
                                                                                $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                                $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                                $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                                $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                                $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                            }
                                                                            break;
                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            foreach ($transinfo as $results) {
                                                                                $riserror = $results['querytransmethodResult']['Success'];
                                                                                $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                                $rwamount = $results['querytransmethodResult']['Amount'];
                                                                                $rremarks = "Withdrawal Success";
                                                                                $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                                $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                            }
                                                                            break;
                                                                    }

                                                                    if ($riserror) {
                                                                        $fmteffdate = $otopup->getDate();
                                                                        $vactualAmt = $rwamount;
                                                                        $vstatus = 1;
                                                                        $vtransactionID = $rtransactionID;
                                                                        $vtransStatus = $rremarks;

                                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                        if ($issucess > 0) {

                                                                            switch (true) {
                                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                                                    $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                                                    $capiusername = $_CAPIUsername;
                                                                                    $capipassword = $_CAPIPassword;
                                                                                    $capiplayername = $_CAPIPlayerName;
                                                                                    $capiserverID = '';

                                                                                    $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                                    break;
                                                                                case strstr($serviceName, "Habanero UB"):

                                                                                    $url = $_ServiceAPI[$mzFromServiceID - 1];
                                                                                    $capiusername = $_HABbrandID;
                                                                                    $capipassword = $_HABapiKey;
                                                                                    $capiplayername = $_CAPIPlayerName;
                                                                                    $capiserverID = '';

                                                                                    $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzFromServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                                    break;
                                                                            }

                                                                            if ($balance == 0) {
                                                                                $TransferStatus = 101;
                                                                                $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                                if (!$updateMzTransactionTransfer) {
                                                                                    $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                }

                                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzFromServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                                if ($updateTerminalSessionsCredentials) {
                                                                                    if ($otopupmembership->open() == true) {
                                                                                        $otopupmembership->updateMember($mzFromServiceID, $mid);
                                                                                    }
                                                                                    $otopupmembership->close();

                                                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                } else {
                                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                }
                                                                            } else {
                                                                                $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }
                                                                        }
                                                                    } else {

                                                                        if ($riserror == "") {
                                                                            $status = 2;
                                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                            $msg = $rremarks;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $status = 2;
                                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    }
                                                                } else {
                                                                    $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                                    if ($updateActiveServiceStatusRollback) {
                                                                        $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $msg = "Manual Redemption: Error on inserting redemption table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif ($MzTransferStatus == 3) {

                                            if ($mzToServiceID <> $ubserviceID) {
                                                $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                if ($updateActiveServiceStatusRollback) {
                                                    $msg = "Invalid casino to fulfil.";
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {
                                                if ($mzToStatus <> 0) {
                                                    $msg = "An unexpected transaction was encountered. Kindly ask IR/CS to review player transactions.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                                if ($mzToStatus == 0) {

                                                    if ($otopupmembership->open() == true) {

                                                        $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzToServiceID);
                                                    }

                                                    $otopupmembership->close();

                                                    $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                    $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                    $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                    $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                    $getservicename = $otopup->getCasinoName($mzToServiceID);
                                                    $servicegrpname = $otopup->getServiceGrpName($mzToServiceID);
                                                    $serviceName = $getservicename[0]['ServiceName'];

                                                    $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $mzToServiceID);

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then

                                                            $url = $_ServiceAPI[$mzToServiceID - 1];
                                                            $capiusername = $_CAPIUsername;
                                                            $capipassword = $_CAPIPassword;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';
                                                            $tracking1 = $mzTransferID . $mzToTransactionType;

                                                            $transinfo = array();

                                                            $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);

                                                            break;

                                                        case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then

                                                            $url = $_ServiceAPI[$mzToServiceID - 1];
                                                            $capiusername = $_HABbrandID;
                                                            $capipassword = $_HABapiKey;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';
                                                            $tracking1 = $mzTransferID . $mzToTransactionType;

                                                            $transinfo = array();

                                                            $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                            break;

                                                        default :
                                                            $msg = "Manual Redemption Error: Invalid Casino Provider";
                                                            echo json_encode($msg);
                                                            exit;
                                                            break;
                                                    }

                                                    if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                foreach ($transinfo as $results) {
                                                                    $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                    $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                    $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                    $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                    $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                }
                                                                break;

                                                            case strstr($serviceName, "Habanero UB"):
                                                                foreach ($transinfo as $results) {
                                                                    $riserror = $results['querytransmethodResult']['Success'];
                                                                    $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                    $rwamount = $results['querytransmethodResult']['Amount'];
                                                                    $rremarks = "Withdrawal Success";
                                                                    $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                    $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                }
                                                                break;
                                                        }


                                                        $ramount = ereg_replace(",", "", $rwamount); //format number replace (,)

                                                        $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                        if ($ramount > $_maxRedeem) {
                                                            $balance = $ramount - $_maxRedeem;
                                                            $ramount = $_maxRedeem;
                                                        } else {
                                                            $balance = 0;
                                                        }

                                                        $fmteffdate = $otopup->getDate();
                                                        $vsiteID = $mzSiteID;
                                                        $vterminalID = $mzTerminalID;
                                                        $vreportedAmt = ereg_replace(",", "", $rwamount);
                                                        $vactualAmt = $ramount;
                                                        $vtransactionDate = $otopup->getDate();
                                                        $vreqByAID = $aid;
                                                        $vprocByAID = $aid;
                                                        $vdateEffective = $otopup->getDate();
                                                        $vstatus = 0;
                                                        $vtransactionID = $rtransactionID;
                                                        $vremarks = $remarksub;
                                                        $vticket = $ticketub;
                                                        $cmbServerID = $mzToServiceID;

                                                        $vtransStatus = $rremarks;
                                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                        $transsummaryid = $transsummaryid['summaryID'];

                                                        $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzToServiceID);

                                                        /* Tracking Details */
                                                        $tracking1 = "MR" . "$lastmrid";
                                                        $tracking2 = "";
                                                        $tracking3 = "";

                                                        if ($lastmrid > 0) {

                                                            if ($vactualAmt == 0) {

                                                                if ($vactualAmt == 0) {
                                                                    $TransferStatus = 101;
                                                                    $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                    if (!$updateMzTransactionTransfer) {
                                                                        $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $mzToAmount, $mzTerminalID);

                                                                if ($updateTerminalSessionsCredentials) {

                                                                    $fmteffdate = $otopup->getDate();
                                                                    $vstatus = 2;
                                                                    $vtransactionID = '';
                                                                    $vtransStatus = '';

                                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                    if ($issucess) {
                                                                        if ($otopupmembership->open() == true) {
                                                                            $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                        }
                                                                        $otopupmembership->close();

                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update  redemption table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }

                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                    $capiusername = $_CAPIUsername;
                                                                    $capipassword = $_CAPIPassword;
                                                                    $capiplayername = $_CAPIPlayerName;
                                                                    $capiserverID = '';
                                                                    $locatorname = null;
                                                                    $withdraw = array();

                                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                                    break;

                                                                case strstr($serviceName, "Habanero UB"):
                                                                    $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                    $capiusername = $_HABbrandID;
                                                                    $capipassword = $_HABapiKey;
                                                                    $capiplayername = $_CAPIPlayerName;
                                                                    $capiserverID = '';
                                                                    $locatorname = null;
                                                                    $withdraw = array();

                                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                                    break;
                                                            }

                                                            if ($withdraw['IsSucceed'] == true) {
                                                                switch (true) {
                                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                        foreach ($withdraw as $results) {
                                                                            $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                            $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                            $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                            $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                            $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                        }
                                                                        break;
                                                                    case strstr($serviceName, "Habanero UB"):
                                                                        foreach ($withdraw as $results) {
                                                                            $riserror = $results['withdrawmethodResult']['Message'];
                                                                            $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                            $rremarks = $results['withdrawmethodResult']['Message'];
                                                                            $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                            $reffdate = $otopup->getDate();
                                                                        }
                                                                        break;
                                                                }


                                                                if ($riserror == "OK" || $riserror == "Withdrawal Success") {

                                                                    $fmteffdate = $otopup->getDate();
                                                                    $vtransStatus = $rremarks;
                                                                    $vstatus = 1;

                                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                    if ($issucess > 0) {

                                                                        if ($balance == 0) {
                                                                            $TransferStatus = 101;
                                                                            $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                            if (!$updateMzTransactionTransfer) {
                                                                                $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }


                                                                            $ramount = number_format($vactualAmt, 2, ".", ",");
                                                                            $balance = number_format($balance, 2, ".", ",");

                                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

                                                                            if ($updateTerminalSessionsCredentials) {
                                                                                if ($otopupmembership->open() == true) {
                                                                                    $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                                }
                                                                                $otopupmembership->close();

                                                                                $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            } else {
                                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    if ($riserror == "") {
                                                                        $status = 2;
                                                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                        $msg = $rremarks;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $status = 2;
                                                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                        $msg = $riserror; //error message when calling the withdrawal result
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {

                                                                switch (true) {
                                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                        $transinfo = array();

                                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                        break;
                                                                    case strstr($serviceName, "Habanero UB"):
                                                                        $transinfo = array();

                                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                                        break;
                                                                }


                                                                if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            foreach ($transinfo as $results) {
                                                                                $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                                $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                                $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                                $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                                $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                            }
                                                                            break;
                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            foreach ($transinfo as $results) {
                                                                                $riserror = $results['querytransmethodResult']['Success'];
                                                                                $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                                $rwamount = $results['querytransmethodResult']['Amount'];
                                                                                $rremarks = "Withdrawal Success";
                                                                                $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                                $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                            }
                                                                            break;
                                                                    }

                                                                    if ($riserror) {

                                                                        $fmteffdate = $otopup->getDate();
                                                                        $vactualAmt = $rwamount;
                                                                        $vstatus = 1;
                                                                        $vtransactionID = $rtransactionID;
                                                                        $vtransStatus = $rremarks;

                                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                        if ($issucess > 0) {

                                                                            switch (true) {
                                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                                    break;

                                                                                case strstr($serviceName, "Habanero UB"):
                                                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                                    break;
                                                                            }

                                                                            if ($balance == 0) {
                                                                                $TransferStatus = 101;
                                                                                $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                                if (!$updateMzTransactionTransfer) {
                                                                                    $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                }

                                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                                if ($updateTerminalSessionsCredentials) {
                                                                                    if ($otopupmembership->open() == true) {
                                                                                        $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                                    }
                                                                                    $otopupmembership->close();
                                                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                } else {
                                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                }
                                                                            } else {
                                                                                $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }
                                                                        }
                                                                    } else {

                                                                        if ($riserror == "") {
                                                                            $status = 2;
                                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                            $msg = $rremarks;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $status = 2;
                                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $msg = "Manual Redemption: Error on inserting redemption table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {

                                                        if ($otopupmembership->open() == true) {

                                                            $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzToServiceID);
                                                        }

                                                        $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                        $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                        $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                        $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                                $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                $capiusername = $_CAPIUsername;
                                                                $capipassword = $_CAPIPassword;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';

                                                                $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):

                                                                $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                $capiusername = $_HABbrandID;
                                                                $capipassword = $_HABapiKey;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';

                                                                $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                break;
                                                        }

                                                        $ramount = ereg_replace(",", "", $getbalance); //format number replace (,)

                                                        $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                        if ($ramount > $_maxRedeem) {
                                                            $balance = $ramount - $_maxRedeem;
                                                            $ramount = $_maxRedeem;
                                                        } else {
                                                            $balance = 0;
                                                        }

                                                        $fmteffdate = $otopup->getDate();
                                                        $vsiteID = $mzSiteID;
                                                        $vterminalID = $mzTerminalID;
                                                        $vreportedAmt = ereg_replace(",", "", $getbalance);
                                                        $vactualAmt = $ramount;
                                                        $vtransactionDate = $otopup->getDate();
                                                        $vreqByAID = $aid;
                                                        $vprocByAID = $aid;
                                                        $vdateEffective = $otopup->getDate();
                                                        $vstatus = 0;
                                                        $vtransactionID = '';
                                                        $vremarks = $remarksub;
                                                        $vticket = $ticketub;
                                                        $cmbServerID = $mzToServiceID;

                                                        $vtransStatus = '';
                                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                        $transsummaryid = $transsummaryid['summaryID'];

                                                        $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzToServiceID);

                                                        if ($lastmrid > 0) {


                                                            if ($vactualAmt == 0) {

                                                                $TransferStatus = 101;
                                                                $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                if (!$updateMzTransactionTransfer) {
                                                                    $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }

                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $mzToAmount, $mzTerminalID);

                                                                if ($updateTerminalSessionsCredentials) {

                                                                    $fmteffdate = $otopup->getDate();
                                                                    $vstatus = 2;
                                                                    $vtransactionID = '';
                                                                    $vtransStatus = '';

                                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                    if ($issucess) {
                                                                        if ($otopupmembership->open() == true) {
                                                                            $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                        }
                                                                        $otopupmembership->close();

                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }

                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    $locatorname = null;
                                                                    $tracking1 = "MR" . "$lastmrid";
                                                                    $withdraw = array();

                                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                                    break;

                                                                case strstr($serviceName, "Habanero UB"):
                                                                    $locatorname = null;
                                                                    $tracking1 = "MR" . "$lastmrid";
                                                                    $withdraw = array();

                                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                                    break;
                                                            }

                                                            if ($withdraw['IsSucceed'] == true) {
                                                                switch (true) {
                                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                        foreach ($withdraw as $results) {
                                                                            $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                            $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                            $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                            $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                            $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                        }
                                                                        break;
                                                                    case strstr($serviceName, "Habanero UB"):
                                                                        foreach ($withdraw as $results) {
                                                                            $riserror = $results['withdrawmethodResult']['Message'];
                                                                            $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                            $rremarks = $results['withdrawmethodResult']['Message'];
                                                                            $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                            $reffdate = $otopup->getDate();
                                                                        }
                                                                        break;
                                                                }


                                                                if ($riserror == "OK" || $riserror == "Withdrawal Success") {

                                                                    $fmteffdate = $otopup->getDate();
                                                                    $vtransStatus = $rremarks;
                                                                    $vstatus = 1;

                                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                    if ($issucess > 0) {

                                                                        if ($balance == 0) {
                                                                            $TransferStatus = 101;
                                                                            $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                            if (!$updateMzTransactionTransfer) {
                                                                                $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }

                                                                            $ramount = number_format($vactualAmt, 2, ".", ",");
                                                                            $balance = number_format($balance, 2, ".", ",");


                                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                            if ($updateTerminalSessionsCredentials) {
                                                                                if ($otopupmembership->open() == true) {
                                                                                    $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                                }
                                                                                $otopupmembership->close();

                                                                                $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            } else {
                                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {

                                                                switch (true) {
                                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then

                                                                        $transinfo = array();

                                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);

                                                                        break;

                                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then

                                                                        $transinfo = array();

                                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                        break;
                                                                }

                                                                if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            foreach ($transinfo as $results) {
                                                                                $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                                $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                                $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                                $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                                $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                            }
                                                                            break;
                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            foreach ($transinfo as $results) {
                                                                                $riserror = $results['querytransmethodResult']['Success'];
                                                                                $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                                $rwamount = $results['querytransmethodResult']['Amount'];
                                                                                $rremarks = "Withdrawal Success";
                                                                                $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                                $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                            }
                                                                            break;
                                                                    }

                                                                    if ($riserror) {
                                                                        $fmteffdate = $otopup->getDate();
                                                                        $vactualAmt = $rwamount;
                                                                        $vstatus = 1;
                                                                        $vtransactionID = $rtransactionID;
                                                                        $vtransStatus = $rremarks;

                                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                        if ($issucess > 0) {

                                                                            switch (true) {
                                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                                                    $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                                    $capiusername = $_CAPIUsername;
                                                                                    $capipassword = $_CAPIPassword;
                                                                                    $capiplayername = $_CAPIPlayerName;
                                                                                    $capiserverID = '';

                                                                                    $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                                    break;
                                                                                case strstr($serviceName, "Habanero UB"):

                                                                                    $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                                    $capiusername = $_HABbrandID;
                                                                                    $capipassword = $_HABapiKey;
                                                                                    $capiplayername = $_CAPIPlayerName;
                                                                                    $capiserverID = '';

                                                                                    $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                                    break;
                                                                            }

                                                                            if ($balance == 0) {
                                                                                $TransferStatus = 101;
                                                                                $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                                if (!$updateMzTransactionTransfer) {
                                                                                    $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                }


                                                                                $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                                if ($updateTerminalSessionsCredentials) {
                                                                                    if ($otopupmembership->open() == true) {
                                                                                        $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                                    }
                                                                                    $otopupmembership->close();

                                                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                } else {
                                                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                                    echo json_encode($msg);
                                                                                    exit;
                                                                                }
                                                                            } else {
                                                                                $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                                echo json_encode($msg);
                                                                                exit;
                                                                            }
                                                                        }
                                                                    } else {

                                                                        if ($riserror == "") {
                                                                            $status = 2;
                                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                            $msg = $rremarks;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $status = 2;
                                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    }
                                                                } else {
                                                                    $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                                    if ($updateActiveServiceStatusRollback) {
                                                                        $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $msg = "Manual Redemption: Error on inserting redemption table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif ($MzTransferStatus == 6) {


                                            if ($mzToServiceID <> $ubserviceID) {
                                                $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                if ($updateActiveServiceStatusRollback) {
                                                    $msg = "Invalid casino to fulfil.";
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {

                                                if ($otopupmembership->open()) {

                                                    $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzToServiceID);
                                                }

                                                $otopupmembership->close();

                                                $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                $getservicename = $otopup->getCasinoName($mzToServiceID);
                                                $servicegrpname = $otopup->getServiceGrpName($mzToServiceID);
                                                $serviceName = $getservicename[0]['ServiceName'];

                                                switch (true) {
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then

                                                        $url = $_ServiceAPI[$mzToServiceID - 1];
                                                        $capiusername = $_CAPIUsername;
                                                        $capipassword = $_CAPIPassword;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';
                                                        $tracking1 = $mzTransferID . $mzToTransactionType;

                                                        $transinfo = array();

                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);

                                                        break;

                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then

                                                        $url = $_ServiceAPI[$mzToServiceID - 1];
                                                        $capiusername = $_HABbrandID;
                                                        $capipassword = $_HABapiKey;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';
                                                        $tracking1 = $mzTransferID . $mzToTransactionType;

                                                        $transinfo = array();

                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                }


                                                if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                            foreach ($transinfo as $results) {
                                                                $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                            }
                                                            break;

                                                        case strstr($serviceName, "Habanero UB"):
                                                            foreach ($transinfo as $results) {
                                                                $riserror = $results['querytransmethodResult']['Success'];
                                                                $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                $rwamount = $results['querytransmethodResult']['Amount'];
                                                                $rremarks = "Withdrawal Success";
                                                                $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                            }
                                                            break;
                                                    }

                                                    $ramount = ereg_replace(",", "", $rwamount); //format number replace (,)

                                                    $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                    if ($ramount > $_maxRedeem) {
                                                        $balance = $ramount - $_maxRedeem;
                                                        $ramount = $_maxRedeem;
                                                    } else {
                                                        $balance = 0;
                                                    }

                                                    $fmteffdate = $otopup->getDate();
                                                    $vsiteID = $mzSiteID;
                                                    $vterminalID = $mzTerminalID;
                                                    $vreportedAmt = $rwamount;
                                                    $vactualAmt = $rwamount;
                                                    $vtransactionDate = $otopup->getDate();
                                                    $vreqByAID = $aid;
                                                    $vprocByAID = $aid;
                                                    $vdateEffective = $otopup->getDate();
                                                    $vstatus = 0;
                                                    $vtransactionID = $rtransactionID;
                                                    $vremarks = '';
                                                    $vticket = '';
                                                    $cmbServerID = $mzToServiceID;

                                                    $vtransStatus = $rremarks;
                                                    $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                    $transsummaryid = $transsummaryid['summaryID'];

                                                    $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzToServiceID);
                                                    /* Tracking Details */
                                                    $tracking1 = "MR" . "$lastmrid";
                                                    $tracking2 = "";
                                                    $tracking3 = "";

                                                    if ($lastmrid > 0) {

                                                        if ($vactualAmt == 0) {

                                                            $TransferStatus = 101;

                                                            $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                            if (!$updateMzTransactionTransfer) {
                                                                $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }

                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $mzToAmount, $mzTerminalID);

                                                            if ($updateTerminalSessionsCredentials) {

                                                                $fmteffdate = $otopup->getDate();
                                                                $vstatus = 2;
                                                                $vtransactionID = '';
                                                                $vtransStatus = '';

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                if ($issucess) {
                                                                    if ($otopupmembership->open() == true) {
                                                                        $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                    }
                                                                    $otopupmembership->close();

                                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $msg = "Manual Redemption Error : Failed to update  redemption table.";
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }


                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                $capiusername = $_CAPIUsername;
                                                                $capipassword = $_CAPIPassword;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';
                                                                $locatorname = null;
                                                                $withdraw = array();

                                                                $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                                break;

                                                            case strstr($serviceName, "Habanero UB"):
                                                                $url = $_ServiceAPI[$mzToServiceID - 1];
                                                                $capiusername = $_HABbrandID;
                                                                $capipassword = $_HABapiKey;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';
                                                                $locatorname = null;
                                                                $withdraw = array();

                                                                $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                                break;
                                                        }
                                                    }

                                                    if ($withdraw['IsSucceed'] == true) {
                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                                    $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                                    $rwamount = $results['WithdrawGenericResult']['amount'];
                                                                    $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                                    $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                                }
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                foreach ($withdraw as $results) {
                                                                    $riserror = $results['withdrawmethodResult']['Message'];
                                                                    $rwamount = abs($results['withdrawmethodResult']['Amount']);
                                                                    $rremarks = $results['withdrawmethodResult']['Message'];
                                                                    $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                                    $reffdate = $otopup->getDate();
                                                                }
                                                                break;
                                                        }

                                                        if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                            //SUCCESS WITHDRAWAL

                                                            $fmteffdate = $otopup->getDate();
                                                            $vtransStatus = $rremarks;
                                                            $vstatus = 1;

                                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                            if ($issucess > 0) {

                                                                if ($balance == 0) {
                                                                    $TransferStatus = 101;
                                                                    $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                    if (!$updateMzTransactionTransfer) {
                                                                        $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }

                                                                    $ramount = number_format($vactualAmt, 2, ".", ",");
                                                                    $balance = number_format($balance, 2, ".", ",");

                                                                    $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $vactualAmt, $mzTerminalID);

                                                                    if ($updateTerminalSessionsCredentials) {
                                                                        if ($otopupmembership->open() == true) {
                                                                            $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                        }
                                                                        $otopupmembership->close();

                                                                        $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    } else {
                                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update redemption table";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            if ($riserror == "") {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $rremarks;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $status = 2;
                                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                $msg = $riserror; //error message when calling the withdrawal result
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        //FAILED WITHDRAWAL

                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                                break;
                                                            case strstr($serviceName, "Habanero UB"):
                                                                $transinfo = array();

                                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                                break;
                                                        }


                                                        if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                        $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                        $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                        $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                        $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                                    }
                                                                    break;
                                                                case strstr($serviceName, "Habanero UB"):
                                                                    foreach ($transinfo as $results) {
                                                                        $riserror = $results['querytransmethodResult']['Success'];
                                                                        $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                        $rwamount = $results['querytransmethodResult']['Amount'];
                                                                        $rremarks = "Withdrawal Success";
                                                                        $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                        $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                                    }
                                                                    break;
                                                            }

                                                            if ($riserror) {

                                                                $fmteffdate = $otopup->getDate();
                                                                $vactualAmt = $rwamount;
                                                                $vstatus = 1;
                                                                $vtransactionID = $rtransactionID;
                                                                $vtransStatus = $rremarks;

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                                if ($issucess > 0) {


                                                                    switch (true) {
                                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                            break;

                                                                        case strstr($serviceName, "Habanero UB"):
                                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                            break;
                                                                    }

                                                                    if ($balance == 0) {
                                                                        $TransferStatus = 101;
                                                                        $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                                        if (!$updateMzTransactionTransfer) {
                                                                            $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }

                                                                        $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $balance, $mzTerminalID);

                                                                        if ($updateTerminalSessionsCredentials) {
                                                                            if ($otopupmembership->open() == true) {
                                                                                $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                            }
                                                                            $otopupmembership->close();
                                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        } else {
                                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                            echo json_encode($msg);
                                                                            exit;
                                                                        }
                                                                    } else {
                                                                        $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                        echo json_encode($msg);
                                                                        exit;
                                                                    }
                                                                }
                                                            } else {

                                                                if ($riserror == "") {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $rremarks;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                } else {
                                                                    $status = 2;
                                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                                    $msg = $riserror; //error message when calling the withdrawal result
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            }
                                                        } else {
                                                            //FAILED TRANS INFO

                                                            $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                            if ($updateActiveServiceStatusRollback) {
                                                                $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    }
                                                } else {

                                                    // FAILED TRANSACTION INFO

                                                    if ($otopupmembership->open() == true) {

                                                        $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $mzToServiceID);
                                                    }

                                                    $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                    $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                    $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                    $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):

                                                            $url = $_ServiceAPI[$mzToServiceID - 1];
                                                            $capiusername = $_CAPIUsername;
                                                            $capipassword = $_CAPIPassword;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';

                                                            $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                            break;
                                                        case strstr($serviceName, "Habanero UB"):

                                                            $url = $_ServiceAPI[$mzToServiceID - 1];
                                                            $capiusername = $_HABbrandID;
                                                            $capipassword = $_HABapiKey;
                                                            $capiplayername = $_CAPIPlayerName;
                                                            $capiserverID = '';

                                                            $getbalance = $CasinoGamingCAPI->getBalance($servicegrpname, $mzToServiceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                            break;
                                                    }

                                                    $ramount = ereg_replace(",", "", $getbalance); //format number replace (,)

                                                    $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                                    if ($ramount > $_maxRedeem) {
                                                        $balance = $ramount - $_maxRedeem;
                                                        $ramount = $_maxRedeem;
                                                    } else {
                                                        $balance = 0;
                                                    }

                                                    $fmteffdate = $otopup->getDate();
                                                    $vsiteID = $mzSiteID;
                                                    $vterminalID = $mzTerminalID;
                                                    $vreportedAmt = ereg_replace(",", "", $getbalance);
                                                    $vactualAmt = $ramount;
                                                    $vtransactionDate = $otopup->getDate();
                                                    $vreqByAID = $aid;
                                                    $vprocByAID = $aid;
                                                    $vdateEffective = $otopup->getDate();
                                                    $vstatus = 0;
                                                    $vtransactionID = '';
                                                    $vremarks = $remarksub;
                                                    $vticket = $ticketub;
                                                    $cmbServerID = $mzToServiceID;

                                                    $vtransStatus = '';
                                                    $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                                    $transsummaryid = $transsummaryid['summaryID'];

                                                    $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, $mzTransferID, $mzToServiceID);

                                                    if ($lastmrid > 0) {

                                                        if ($vactualAmt == 0) {
                                                            $TransferStatus = 101;
                                                            $updateMzTransactionTransfer = $otopup->updateMzTransactionTransfer($TransferStatus, $aid, $MaxTransferID);
                                                            if (!$updateMzTransactionTransfer) {
                                                                $msg = "Manual Redemption Error : Failed to update transactions transfer table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }

                                                            $updateTerminalSessionsCredentials = $otopup->updateTerminalSessionsCredentials(1, $mzToServiceID, $UBServiceLogin, $UBServicePassword, $UBHashedServicePassword, $mzToAmount, $mzTerminalID);

                                                            if ($updateTerminalSessionsCredentials) {

                                                                $fmteffdate = $otopup->getDate();
                                                                $vstatus = 2;
                                                                $vtransactionID = '';
                                                                $vtransStatus = '';

                                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                                if ($issucess) {
                                                                    if ($otopupmembership->open() == true) {
                                                                        $otopupmembership->updateMember($mzToServiceID, $mid);
                                                                    }
                                                                    $otopupmembership->close();

                                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                                    echo json_encode($msg);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        } else {
                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $vactualAmt;
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {
                                                        $msg = "Manual Redemption: Error on inserting redemption table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                }
                                            } // 2nd to the last else
                                        } else {
                                            $msg = "Manual redemption of player balance is currently not allowed. Please try again later.";
											echo json_encode($msg);
											exit;
                                        }
                                    }
                                } else {
                                    $msg = "Manual Redemption Error: Invalid Casino Service";
                                    echo json_encode($msg);
                                    exit;
                                }
                            } else {
                                $msg = "Manual redemption of player balance is currently not allowed. Please try again later.";
                                echo json_encode($msg);
                                exit;
                            }
                        }
                    }
                }

                break;




////END TRANSWALLET
/////// OVERRIDE




            case "ManualRedemptionOverride":
                $login = $_POST['terminalcode'];
                $provider = $_POST['txtservices'];
                $vterminalID = $_POST['cmbterminal'];
                $ubserviceID = $_POST['txtserviceid'];
                $ubterminalID = $_POST['txtterminalid'];
                $vserviceBalance = $_POST['txtamount2'];
                $amountToRedeem = $_POST['hdnamtwithdraw'];
                $ticketub = $_POST['txtticketub'];
                $remarksub = $_POST['txtremarksub'];
                $loyaltycardnumber = $_POST['txtcardnumber'];
                $mid = $_POST['txtmid'];
                $usermode = $_POST['txtusermode'];
                $siteID = $_POST['cmbsite'];

                if (!isset($siteID) && $siteID == "-1") {
                    $msg = "Please select Site ID";
                }

                if (isset($usermode)) {
                    if ($usermode == 1 || $usermode == 3) {
                        $mid = $otopup->getMIDByUBCard($loyaltycardnumber);

                        //check if has sesssion
                        $hasTS = $otopup->checkIfHasTermalSession($mid);

                        $getservicename = $otopup->getCasinoName($ubserviceID);
                        $servicegrpname = $otopup->getServiceGrpName($ubserviceID);

                        $serviceName = $getservicename[0]['ServiceName'];

                        if ($hasTS > 0) {

                            $casino = $_SESSION['CasinoArray2'];
                            $rmid = $_SESSION['MID2'];

                            $casinoarray_count = count($casino);

                            $casinos = array();
                            $balPerCasino = array();

                            if ($casinoarray_count != 0) {
                                for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                                    if (is_array($casino[$ctr])) {
                                        $casinos[$ctr] = array(
                                            array('ServiceUsername' => $casino[$ctr]['ServiceUsername'],
                                                'ServicePassword' => $casino[$ctr]['ServicePassword'],
                                                'HashedServicePassword' => $casino[$ctr]['HashedServicePassword'],
                                                'ServiceID' => $casino[$ctr]['ServiceID'],
                                                'UserMode' => $casino[$ctr]['UserMode'],
                                                'isVIP' => $casino[$ctr]['isVIP'],
                                                'Status' => $casino[$ctr]['Status'],)
                                        );
                                    } else {
                                        $casinos[$ctr] = array(
                                            array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                                'ServicePassword' => $casino[$ctr]->ServicePassword,
                                                'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                                'ServiceID' => $casino[$ctr]->ServiceID,
                                                'UserMode' => $casino[$ctr]->UserMode,
                                                'isVIP' => $casino[$ctr]->isVIP,
                                                'Status' => $casino[$ctr]->Status)
                                        );
                                    }

                                    //loop through array output to get casino array from membership card
                                    foreach ($casinos[$ctr] as $value) {
                                        $rserviceuname = $value['ServiceUsername'];
                                        $rservicepassword = $value['ServicePassword'];
                                        $rserviceid = $value['ServiceID'];
                                        $rusermode = $value['UserMode'];
                                        $risvip = $value['isVIP'];
                                        $hashedpassword = $value['HashedServicePassword'];
                                        $rstatus = $value['Status'];

                                        $servicename = $otopup->getCasinoName($rserviceid);
                                        $servicegrp = $otopup->getServiceGrpName($rserviceid);
                                        $servicestat = $otopup->getServiceStatus($rserviceid);

                                        if ($servicestat == 1) {
                                            //loop htrough services to get if has pedning balance
                                            foreach ($servicename as $service2) {
                                                $serviceName = $service2['ServiceName'];
                                                $serviceStatus = $service2['Status'];
                                                $servicegrp = $servicegrp;

                                                switch (true) {
                                                    case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                        //call get balance method of Habanero
                                                        $url = $_ServiceAPI[$rserviceid - 1];
                                                        $capiusername = $_HABbrandID;
                                                        $capipassword = $_HABapiKey;
                                                        $capiplayername = '';
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode, $rservicepassword);
                                                        if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                            if ($balance == 0) {
                                                                $msg = "Nothing to process.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }

                                                        break;
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                        //call get balance method of RTG
                                                        $url = $_ServiceAPI[$rserviceid - 1];
                                                        $capiusername = $_CAPIUsername;
                                                        $capipassword = $_CAPIPassword;
                                                        $capiplayername = $_CAPIPlayerName;
                                                        $capiserverID = '';
                                                        $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode);
                                                        if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                            if ($balance == 0) {
                                                                $msg = "Nothing to process.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                        break;
                                                }

                                                $balPerCasino[] = array(
                                                    "ServiceName" => $serviceName,
                                                    "Balance" => $balance
                                                );
                                            }
                                        }
                                    }
                                }

                                $balanceNotZeroCount = 0;
                                foreach ($balPerCasino as $value) {
                                    if ($value['Balance'] > 0) {
                                        $balanceNotZeroCount = $balanceNotZeroCount + 1;
                                    }
                                }

                                if ($balanceNotZeroCount <> 2) {
                                    $msg = "Please use User Based Manual Redemption module to redeem casino balance.";
                                    echo json_encode($msg);
                                    exit;
                                }


                                $getTerminalSessionsDetails = $otopup->getTerminalSessionsDetails($loyaltycardnumber);

                                $ActiveServiceStatus = $getTerminalSessionsDetails['ActiveServiceStatus'];
                                $TransactionSummary = $getTerminalSessionsDetails['TransactionSummaryID'];

                                $tsTerminalID = $getTerminalSessionsDetails['TerminalID'];
                                $tsServiceID = $getTerminalSessionsDetails['ServiceID'];
                                $tsAmount = $getTerminalSessionsDetails['LastBalance'];

                                $GLOBAL_OldActiveServiceStatus = $otopup->getOldActiveServiceID($tsTerminalID);

                                $getSiteIDByTerminalID = $otopup->getSiteIDByTerminalID($tsTerminalID);

                                $tsSiteID = $getSiteIDByTerminalID;

                                $MaxTransferID = $otopup->getMaxTransferID($TransactionSummary);

                                if ($ActiveServiceStatus == 1 || $ActiveServiceStatus == 8 || $ActiveServiceStatus == 9) {
                                    if ($ActiveServiceStatus == 1 || $ActiveServiceStatus == 9) {
                                        $NewActiveServiceStatus = 8;
                                        $updateTerminalSessions = $otopup->updateActiveServiceStatusTW($ActiveServiceStatus, $NewActiveServiceStatus, $loyaltycardnumber);
                                        $GLOBAL_OldActiveServiceStatus = $otopup->getOldActiveServiceID($tsTerminalID);
                                    }

                                    if ($otopupmembership->open()) {
                                        $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $ubserviceID);
                                    }
                                    $otopupmembership->close();

                                    $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                    $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                    $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                    $usermode = $getPlayerCredentialsByUB['Usermode'];


                                    $getservicename = $otopup->getCasinoName($ubserviceID);
                                    $servicegrpname = $otopup->getServiceGrpName($ubserviceID);

                                    $serviceName = $getservicename[0]['ServiceName'];

                                    switch (true) {
                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                            $url = $_ServiceAPI[$ubserviceID - 1];
                                            $capiusername = $_CAPIUsername;
                                            $capipassword = $_CAPIPassword;
                                            $capiplayername = $_CAPIPlayerName;
                                            $capiserverID = '';

                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                            break;

                                        case strstr($serviceName, "Habanero UB"):

                                            $url = $_ServiceAPI[$ubserviceID - 1];
                                            $capiusername = $_HABbrandID;
                                            $capipassword = $_HABapiKey;
                                            $capiplayername = $_CAPIPlayerName;
                                            $capiserverID = '';

                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                            break;
                                    }

                                    $totalBalance = ereg_replace(",", "", $balance);
                                    $ramount = ereg_replace(",", "", $balance); //format number replace (,)

                                    $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                    if ($ramount > $_maxRedeem) {
                                        $balance = $ramount - $_maxRedeem;
                                        $ramount = $_maxRedeem;
                                    } else {
                                        $balance = 0;
                                    }

                                    $vsiteID = $tsSiteID;
                                    $vterminalID = $tsTerminalID;
                                    $vreportedAmt = ereg_replace(",", "", $totalBalance);
                                    $vactualAmt = $ramount;
                                    $vtransactionDate = $otopup->getDate();
                                    $vreqByAID = $aid;
                                    $vprocByAID = $aid;
                                    $vdateEffective = $otopup->getDate();
                                    $vstatus = 0;
                                    $vtransactionID = '';
                                    $vremarks = $remarksub;
                                    $vticket = $ticketub;
                                    $cmbServerID = $ubserviceID;

                                    $vtransStatus = '';
                                    $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                    $transsummaryid = $transsummaryid['summaryID'];

                                    $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode, '', $ubserviceID);

                                    if ($lastmrid > 0) {
                                        if ($vactualAmt <> 0) {
                                            $tracking1 = "MR" . "$lastmrid";
                                            $tracking2 = '';
                                            $tracking3 = '';
                                            $tracking4 = '';

                                            switch (true) {
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                    $url = $_ServiceAPI[$ubserviceID - 1];
                                                    $capiusername = $_CAPIUsername;
                                                    $capipassword = $_CAPIPassword;
                                                    $capiplayername = $_CAPIPlayerName;
                                                    $capiserverID = '';
                                                    $locatorname = null;
                                                    $withdraw = array();

                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                    break;

                                                case strstr($serviceName, "Habanero UB"):
                                                    $url = $_ServiceAPI[$ubserviceID - 1];
                                                    $capiusername = $_HABbrandID;
                                                    $capipassword = $_HABapiKey;
                                                    $capiplayername = $_CAPIPlayerName;
                                                    $capiserverID = '';
                                                    $locatorname = null;
                                                    $withdraw = array();

                                                    $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                    break;
                                            }

                                            if ($withdraw['IsSucceed'] == true) {
                                                switch (true) {
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                        foreach ($withdraw as $results) {
                                                            $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                            $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                            $rwamount = $results['WithdrawGenericResult']['amount'];
                                                            $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                            $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                        }
                                                        break;
                                                    case strstr($serviceName, "Habanero UB"):
                                                        foreach ($withdraw as $results) {
                                                            $riserror = $results['withdrawmethodResult']['Message'];
                                                            $rwamount = abs($results['withdrawmethodResult'] ['Amount']);
                                                            $rremarks = $results['withdrawmethodResult']['Message'];
                                                            $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                            $reffdate = $otopup->getDate();
                                                        }
                                                        break;
                                                }

                                                if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                    //SUCCESS WITHDRAWAL

                                                    $fmteffdate = $otopup->getDate();
                                                    $vtransStatus = $rremarks;
                                                    $vstatus = 1;

                                                    $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                    if ($issucess > 0) {

                                                        $ramount = number_format($vactualAmt, 2, ".", ",");
                                                        $balance = number_format($balance, 2, ".", ",");

                                                        $updateActiveServiceStatusByTerminalID = $otopup->updateActiveServiceStatusByTerminalID(1, $tsTerminalID);

                                                        if ($updateActiveServiceStatusByTerminalID) {

                                                            $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    } else {
                                                        $msg = "Manual Redemption Error : Failed updating redemption table";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                } else {
                                                    if ($riserror == "") {
                                                        $status = 2;
                                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                        $msg = $rremarks;
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $status = 2;
                                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                        $msg = $riserror; //error message when calling the withdrawal result
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                }
                                            } else {
                                                //FAILED WITHDRAWAL

                                                switch (true) {
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                        $transinfo = array();

                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                        break;
                                                    case strstr($serviceName, "Habanero UB"):
                                                        $transinfo = array();

                                                        $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                        break;
                                                }



                                                if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                            foreach ($transinfo as $results) {
                                                                $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                                $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                                $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                                $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                                $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                            }
                                                            break;
                                                        case strstr($serviceName, "Habanero UB"):
                                                            foreach ($transinfo as $results) {
                                                                $riserror = $results['querytransmethodResult']['Success'];
                                                                $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                                $rwamount = $results['querytransmethodResult']['Amount'];
                                                                $rremarks = "Withdrawal Success";
                                                                $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                                $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                            }
                                                            break;
                                                    } if ($riserror) {

                                                        $fmteffdate = $otopup->getDate();
                                                        $vactualAmt = $rwamount;
                                                        $vstatus = 1;
                                                        $vtransactionID = $rtransactionID;
                                                        $vtransStatus = $rremarks;
                                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                        if ($issucess > 0) {


                                                            switch (true) {
                                                                case strstr($serviceName, "RTG - Sapphire V17 UB"): $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                    break;

                                                                case strstr($serviceName, "Habanero UB"):
                                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                    break;
                                                            }

                                                            $updateActiveServiceStatusByTerminalID = $otopup->updateActiveServiceStatusByTerminalID(1, $tsTerminalID);

                                                            if ($updateActiveServiceStatusByTerminalID) {
                                                                $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                                echo json_encode($msg);
                                                                exit;
                                                            } else {
                                                                $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                                echo json_encode($msg);
                                                                exit;
                                                            }
                                                        }
                                                    } else {

                                                        if ($riserror == "") {
                                                            $status = 2;
                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                            $msg = $rremarks;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $status = 2;
                                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                            $msg = $riserror; //error message when calling the withdrawal result
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                } else {
                                                    //FAILED TRANS INFO

                                                    $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                    if ($updateActiveServiceStatusRollback) {
                                                        $msg = "Failed to process manual redemption";
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $msg = "Manual Redemption: Error on inserting manual redemption";
                                        echo json_encode($msg);
                                        exit;
                                    }
                                } else {
                                    $msg = "Manual redemption of player balance is currently not allowed. Please try again later.";
                                    echo json_encode($msg);
                                    exit;
                                }
                            }
                        } else {
                            $msg = "Please use User Based Manual Redemption module to redeem casino balance.";
                            echo json_encode($msg);
                            exit;
                        }
                    }
                }

                break;










////// END OVERRIDE
///// REVERSAL




            case "ManualRedemptionReversal":
                $login = $_POST['terminalcode'];
                $provider = $_POST['txtservices'];
                $vterminalID = $_POST['cmbterminal'];
                $ubserviceID = $_POST['txtserviceid'];
                $ubterminalID = $_POST['txtterminalid'];
                $vserviceBalance = $_POST['txtamount2'];
                $amountToRedeem = $_POST['hdnamtwithdraw'];
                $ticketub = $_POST['txtticketub'];
                $remarksub = $_POST['txtremarksub'];
                $loyaltycardnumber = $_POST['txtcardnumber'];
                $mid = $_POST['txtmid'];
                $usermode = $_POST['txtusermode'];
                $siteID = $_POST['cmbsite'];

                if (!isset($siteID) && $siteID == "-1") {
                    $msg = "Please select Site ID";
                }

                if (isset($usermode)) {
                    if ($usermode == 1 || $usermode == 3) {
                        $mid = $otopup->getMIDByUBCard($loyaltycardnumber);

                        //check if has sesssion
                        $hasTS = $otopup->checkIfHasTermalSession($mid);

                        $getservicename = $otopup->getCasinoName($ubserviceID);
                        $servicegrpname = $otopup->getServiceGrpName($ubserviceID);

                        $serviceName = $getservicename[0]['ServiceName'];



                        $casino = $_SESSION['CasinoArray2'];
                        $rmid = $_SESSION['MID2'];

                        $casinoarray_count = count($casino);

                        $casinos = array();
                        $balPerCasino = array();

                        if ($casinoarray_count != 0) {
                            for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                                if (is_array($casino[$ctr])) {
                                    $casinos[$ctr] = array(
                                        array('ServiceUsername' => $casino[$ctr]['ServiceUsername'],
                                            'ServicePassword' => $casino[$ctr]['ServicePassword'],
                                            'HashedServicePassword' => $casino[$ctr]['HashedServicePassword'],
                                            'ServiceID' => $casino[$ctr]['ServiceID'],
                                            'UserMode' => $casino[$ctr]['UserMode'],
                                            'isVIP' => $casino[$ctr]['isVIP'],
                                            'Status' => $casino[$ctr]['Status'],)
                                    );
                                } else {
                                    $casinos[$ctr] = array(
                                        array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                            'ServicePassword' => $casino[$ctr]->ServicePassword,
                                            'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                            'ServiceID' => $casino[$ctr]->ServiceID,
                                            'UserMode' => $casino[$ctr]->UserMode,
                                            'isVIP' => $casino[$ctr]->isVIP,
                                            'Status' => $casino[$ctr]->Status)
                                    );
                                }

                                //loop through array output to get casino array from membership card
                                foreach ($casinos[$ctr] as $value) {
                                    $rserviceuname = $value['ServiceUsername'];
                                    $rservicepassword = $value['ServicePassword'];
                                    $rserviceid = $value['ServiceID'];
                                    $rusermode = $value['UserMode'];
                                    $risvip = $value['isVIP'];
                                    $hashedpassword = $value['HashedServicePassword'];
                                    $rstatus = $value['Status'];

                                    $servicename = $otopup->getCasinoName($rserviceid);
                                    $servicegrp = $otopup->getServiceGrpName($rserviceid);
                                    $servicestat = $otopup->getServiceStatus($rserviceid);

                                    if ($servicestat == 1) {
                                        //loop htrough services to get if has pedning balance
                                        foreach ($servicename as $service2) {
                                            $serviceName = $service2['ServiceName'];
                                            $serviceStatus = $service2['Status'];
                                            $servicegrp = $servicegrp;

                                            switch (true) {
                                                case strstr($serviceName, "Habanero UB"): //if provider is Habanero, then
                                                    //call get balance method of Habanero
                                                    $url = $_ServiceAPI[$rserviceid - 1];
                                                    $capiusername = $_HABbrandID;
                                                    $capipassword = $_HABapiKey;
                                                    $capiplayername = '';
                                                    $capiserverID = '';
                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode, $rservicepassword);
                                                    if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                        if ($balance == 0) {
                                                            $msg = "Nothing to process.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }

                                                    break;
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"): //if provider is RTG, then
                                                    //call get balance method of RTG
                                                    $url = $_ServiceAPI[$rserviceid - 1];
                                                    $capiusername = $_CAPIUsername;
                                                    $capipassword = $_CAPIPassword;
                                                    $capiplayername = $_CAPIPlayerName;
                                                    $capiserverID = '';
                                                    $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode);
                                                    if ($getservicename[0]['ServiceName'] == $serviceName) {
                                                        if ($balance == 0) {
                                                            $msg = "Nothing to process.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                    break;
                                            }

                                            $balPerCasino[] = array(
                                                "ServiceName" => $serviceName,
                                                "Balance" => $balance
                                            );
                                        }
                                    }
                                }
                            }

                            $balanceNotZeroCount = 0;
                            foreach ($balPerCasino as $value) {
                                if ($value['Balance'] > 0) {
                                    $balanceNotZeroCount = $balanceNotZeroCount + 1;
                                }
                            }

                            if ($balanceNotZeroCount <> 2) {
                                $msg = "Please use User Based Manual Redemption module to redeem casino balance.";
                                echo json_encode($msg);
                                exit;
                            }
                        }


                        if ($hasTS > 0) {

                            $getTerminalSessionsDetails = $otopup->getTerminalSessionsDetails($loyaltycardnumber);

                            $ActiveServiceStatus = $getTerminalSessionsDetails['ActiveServiceStatus'];
                            $TransactionSummary = $getTerminalSessionsDetails['TransactionSummaryID'];

                            $tsTerminalID = $getTerminalSessionsDetails['TerminalID'];
                            $tsServiceID = $getTerminalSessionsDetails['ServiceID'];
                            $tsAmount = $getTerminalSessionsDetails['LastBalance'];

                            $GLOBAL_OldActiveServiceStatus = $otopup->getOldActiveServiceID($tsTerminalID);

                            $getSiteIDByTerminalID = $otopup->getSiteIDByTerminalID($tsTerminalID);

                            $tsSiteID = $getSiteIDByTerminalID;

                            if ($ActiveServiceStatus == 1 || $ActiveServiceStatus == 8 || $ActiveServiceStatus == 9) {
                                if ($ActiveServiceStatus == 1 || $ActiveServiceStatus == 9) {
                                    $NewActiveServiceStatus = 8;
                                    $updateTerminalSessions = $otopup->updateActiveServiceStatusTW($ActiveServiceStatus, $NewActiveServiceStatus, $loyaltycardnumber);
                                    $GLOBAL_OldActiveServiceStatus = $otopup->getOldActiveServiceID($tsTerminalID);
                                }

                                if ($otopupmembership->open()) {
                                    $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $ubserviceID);
                                }
                                $otopupmembership->close();

                                $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                $usermode = $getPlayerCredentialsByUB['Usermode'];

                                $getservicename = $otopup->getCasinoName($ubserviceID);
                                $servicegrpname = $otopup->getServiceGrpName($ubserviceID);

                                $serviceName = $getservicename[0]['ServiceName'];


                                switch (true) {
                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                        $url = $_ServiceAPI[$ubserviceID - 1];
                                        $capiusername = $_CAPIUsername;
                                        $capipassword = $_CAPIPassword;
                                        $capiplayername = $_CAPIPlayerName;
                                        $capiserverID = '';

                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                        break;

                                    case strstr($serviceName, "Habanero UB"):

                                        $url = $_ServiceAPI[$ubserviceID - 1];
                                        $capiusername = $_HABbrandID;
                                        $capipassword = $_HABapiKey;
                                        $capiplayername = $_CAPIPlayerName;
                                        $capiserverID = '';

                                        $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                        break;
                                }

                                $totalBalance = ereg_replace(",", "", $balance);
                                $ramount = ereg_replace(",", "", $balance); //format number replace (,)

                                $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                                if ($ramount > $_maxRedeem) {
                                    $balance = $ramount - $_maxRedeem;
                                    $ramount = $_maxRedeem;
                                } else {
                                    $balance = 0;
                                }


                                $vsiteID = $tsSiteID;
                                $vterminalID = $tsTerminalID;
                                $vreportedAmt = ereg_replace(",", "", $totalBalance);
                                $vactualAmt = $ramount;
                                $vtransactionDate = $otopup->getDate();
                                $vreqByAID = $aid;
                                $vprocByAID = $aid;
                                $vdateEffective = $otopup->getDate();
                                $vstatus = 0;
                                $vtransactionID = '';
                                $vremarks = $remarksub;
                                $vticket = $ticketub;
                                $cmbServerID = $ubserviceID;
                                $mzTransferID = '';

                                $vtransStatus = '';
                                $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                $transsummaryid = $transsummaryid['summaryID'];

                                $lastmrid = $otopup->insertreversal($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode);

                                if ($lastmrid > 0) {

                                    if ($vactualAmt <> 0) {

                                        $tracking1 = "RCB" . "$lastmrid";
                                        $tracking2 = '';
                                        $tracking3 = '';
                                        $tracking4 = '';

                                        switch (true) {
                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                $url = $_ServiceAPI[$ubserviceID - 1];
                                                $capiusername = $_CAPIUsername;
                                                $capipassword = $_CAPIPassword;
                                                $capiplayername = $_CAPIPlayerName;
                                                $capiserverID = '';
                                                $locatorname = null;
                                                $withdraw = array();

                                                $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                                break;

                                            case strstr($serviceName, "Habanero UB"):
                                                $url = $_ServiceAPI[$ubserviceID - 1];
                                                $capiusername = $_HABbrandID;
                                                $capipassword = $_HABapiKey;
                                                $capiplayername = $_CAPIPlayerName;
                                                $capiserverID = '';
                                                $locatorname = null;
                                                $withdraw = array();

                                                $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                                break;
                                        }


                                        if ($withdraw['IsSucceed'] == true) {
                                            switch (true) {
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                    foreach ($withdraw as $results) {
                                                        $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                        $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                        $rwamount = $results['WithdrawGenericResult']['amount'];
                                                        $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                        $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                    }
                                                    break;
                                                case strstr($serviceName, "Habanero UB"):
                                                    foreach ($withdraw as $results) {
                                                        $riserror = $results['withdrawmethodResult']['Message'];
                                                        $rwamount = abs($results['withdrawmethodResult'] ['Amount']);
                                                        $rremarks = $results['withdrawmethodResult']['Message'];
                                                        $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                        $reffdate = $otopup->getDate();
                                                    }
                                                    break;
                                            }

                                            if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                                //SUCCESS WITHDRAWAL

                                                $fmteffdate = $otopup->getDate();
                                                $vtransStatus = $rremarks;
                                                $vstatus = 1;

                                                $issucess = $otopup->updateReversalSuccess($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                                if ($issucess > 0) {

                                                    $ramount = number_format($vactualAmt, 2, ".", ",");
                                                    $balance = number_format($balance, 2, ".", ",");

                                                    $updateActiveServiceStatusByTerminalID = $otopup->updateActiveServiceStatusByTerminalID(1, $tsTerminalID);

                                                    if ($updateActiveServiceStatusByTerminalID) {

                                                        $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed updating redemption table";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {
                                                if ($riserror == "") {
                                                    $status = 2;
                                                    $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                    $msg = $rremarks;
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $status = 2;
                                                    $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                    $msg = $riserror; //error message when calling the withdrawal result
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            }
                                        } else {
                                            //FAILED WITHDRAWAL

                                            switch (true) {
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                    $transinfo = array();

                                                    $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                    break;
                                                case strstr($serviceName, "Habanero UB"):
                                                    $transinfo = array();

                                                    $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                    break;
                                            }



                                            if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                                switch (true) {
                                                    case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                        foreach ($transinfo as $results) {
                                                            $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                            $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                            $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                            $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                            $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                        }
                                                        break;
                                                    case strstr($serviceName, "Habanero UB"):
                                                        foreach ($transinfo as $results) {
                                                            $riserror = $results['querytransmethodResult']['Success'];
                                                            $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                            $rwamount = $results['querytransmethodResult']['Amount'];
                                                            $rremarks = "Withdrawal Success";
                                                            $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                            $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                        }
                                                        break;
                                                } if ($riserror) {

                                                    $fmteffdate = $otopup->getDate();
                                                    $vactualAmt = $rwamount;
                                                    $vstatus = 1;
                                                    $vtransactionID = $rtransactionID;
                                                    $vtransStatus = $rremarks;
                                                    $issucess = $otopup->updateReversalSuccess($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                    if ($issucess > 0) {


                                                        switch (true) {
                                                            case strstr($serviceName, "RTG - Sapphire V17 UB"): $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                                break;

                                                            case strstr($serviceName, "Habanero UB"):
                                                                $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                                break;
                                                        }

                                                        $updateActiveServiceStatusByTerminalID = $otopup->updateActiveServiceStatusByTerminalID(1, $tsTerminalID);

                                                        if ($updateActiveServiceStatusByTerminalID) {
                                                            $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                            echo json_encode($msg);
                                                            exit;
                                                        } else {
                                                            $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                            echo json_encode($msg);
                                                            exit;
                                                        }
                                                    }
                                                } else {

                                                    if ($riserror == "") {
                                                        $status = 2;
                                                        $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                        $msg = $rremarks;
                                                        echo json_encode($msg);
                                                        exit;
                                                    } else {
                                                        $status = 2;
                                                        $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                        $msg = $riserror; //error message when calling the withdrawal result
                                                        echo json_encode($msg);
                                                        exit;
                                                    }
                                                }
                                            } else {
                                                //FAILED TRANS INFO

                                                $updateActiveServiceStatusRollback = $otopup->updateActiveServiceStatusRollback($GLOBAL_OldActiveServiceStatus, $loyaltycardnumber);
                                                if ($updateActiveServiceStatusRollback) {
                                                    $msg = "Manual Redemption Error : Failed to process manual redemption";
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $msg = "Manual Redemption Error : Failed to update sessions table.";
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $msg = "Manual Redemption: Error on inserting manual redemption";
                                    echo json_encode($msg);
                                    exit;
                                }
                            } else {
                                $msg = "Manual redemption of player balance is currently not allowed. Please try again later.";
                                echo json_encode($msg);
                                exit;
                            }
                        } else {


                            if ($otopupmembership->open()) {
                                $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($mid, $ubserviceID);
                            }
                            $otopupmembership->close();

                            $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                            $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                            $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                            $usermode = $getPlayerCredentialsByUB['Usermode'];

                            $getservicename = $otopup->getCasinoName($ubserviceID);
                            $servicegrpname = $otopup->getServiceGrpName($ubserviceID);

                            $serviceName = $getservicename[0]['ServiceName'];


                            switch (true) {
                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                    $url = $_ServiceAPI[$ubserviceID - 1];
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';

                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                    break;

                                case strstr($serviceName, "Habanero UB"):

                                    $url = $_ServiceAPI[$ubserviceID - 1];
                                    $capiusername = $_HABbrandID;
                                    $capipassword = $_HABapiKey;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';

                                    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                    break;
                            }

                            $totalBalance = ereg_replace(",", "", $balance);
                            $ramount = ereg_replace(",", "", $balance); //format number replace (,)

                            $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                            if ($ramount > $_maxRedeem) {
                                $balance = $ramount - $_maxRedeem;
                                $ramount = $_maxRedeem;
                            } else {
                                $balance = 0;
                            }


                            $vsiteID = '';
                            $vterminalID = '';
                            $vreportedAmt = ereg_replace(",", "", $totalBalance);
                            $vactualAmt = $ramount;
                            $vtransactionDate = $otopup->getDate();
                            $vreqByAID = $aid;
                            $vprocByAID = $aid;
                            $vdateEffective = $otopup->getDate();
                            $vstatus = 0;
                            $vtransactionID = '';
                            $vremarks = $remarksub;
                            $vticket = $ticketub;
                            $cmbServerID = $ubserviceID;
                            $mzTransferID = '';

                            $vtransStatus = '';
                            $transsummaryid = '';

                            $lastmrid = $otopup->insertreversal($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $loyaltycardnumber, $mid, $usermode);

                            if ($lastmrid > 0) {

                                if ($vactualAmt <> 0) {

                                    $tracking1 = "RCB" . "$lastmrid";
                                    $tracking2 = '';
                                    $tracking3 = '';
                                    $tracking4 = '';

                                    switch (true) {
                                        case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                            $url = $_ServiceAPI[$ubserviceID - 1];
                                            $capiusername = $_CAPIUsername;
                                            $capipassword = $_CAPIPassword;
                                            $capiplayername = $_CAPIPlayerName;
                                            $capiserverID = '';
                                            $locatorname = null;
                                            $withdraw = array();

                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname);
                                            break;

                                        case strstr($serviceName, "Habanero UB"):
                                            $url = $_ServiceAPI[$ubserviceID - 1];
                                            $capiusername = $_HABbrandID;
                                            $capipassword = $_HABapiKey;
                                            $capiplayername = $_CAPIPlayerName;
                                            $capiserverID = '';
                                            $locatorname = null;
                                            $withdraw = array();

                                            $withdraw = $CasinoGamingCAPI->Withdraw($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $vactualAmt, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $UBServicePassword);

                                            break;
                                    }


                                    if ($withdraw['IsSucceed'] == true) {
                                        switch (true) {
                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                foreach ($withdraw as $results) {
                                                    $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                                    $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                                    $rwamount = $results['WithdrawGenericResult']['amount'];
                                                    $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                                    $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                                }
                                                break;
                                            case strstr($serviceName, "Habanero UB"):
                                                foreach ($withdraw as $results) {
                                                    $riserror = $results['withdrawmethodResult']['Message'];
                                                    $rwamount = abs($results['withdrawmethodResult'] ['Amount']);
                                                    $rremarks = $results['withdrawmethodResult']['Message'];
                                                    $rtransactionID = $results['withdrawmethodResult']['TransactionId'];
                                                    $reffdate = $otopup->getDate();
                                                }
                                                break;
                                        }

                                        if ($riserror == "OK" || $riserror == "Withdrawal Success") {
                                            //SUCCESS WITHDRAWAL

                                            $fmteffdate = $otopup->getDate();
                                            $vtransStatus = $rremarks;
                                            $vstatus = 1;

                                            $issucess = $otopup->updateReversalSuccess($vstatus, $vactualAmt, $rtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                            if ($issucess > 0) {

                                                $ramount = number_format($vactualAmt, 2, ".", ",");
                                                $balance = number_format($balance, 2, ".", ",");


                                                $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                                echo json_encode($msg);
                                                exit;
                                            } else {
                                                $msg = "Manual Redemption Error : Failed updating redemption table";
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        } else {
                                            if ($riserror == "") {
                                                $status = 2;
                                                $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                $msg = $rremarks;
                                                echo json_encode($msg);
                                                exit;
                                            } else {
                                                $status = 2;
                                                $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                $msg = $riserror; //error message when calling the withdrawal result
                                                echo json_encode($msg);
                                                exit;
                                            }
                                        }
                                    } else {
                                        //FAILED WITHDRAWAL

                                        switch (true) {
                                            case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                $transinfo = array();

                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode);
                                                break;

                                            case strstr($serviceName, "Habanero UB"):
                                                $transinfo = array();

                                                $transinfo = $CasinoGamingCAPI->TransSearchInfo($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '', $usermode, $UBServicePassword);
                                                break;
                                        }

                                        if (!empty($transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"]) && $transinfo["TransactionInfo"]["TrackingInfoTransactionSearchResult"]["transactionStatus"] == "TRANSACTIONSTATUS_APPROVED" || $transinfo['IsSucceed'] == true) {
                                            switch (true) {
                                                case strstr($serviceName, "RTG - Sapphire V17 UB"):
                                                    foreach ($transinfo as $results) {
                                                        $riserror = $results['TrackingInfoTransactionSearchResult']['errorMsg'];
                                                        $reffdate = $results['TrackingInfoTransactionSearchResult']['effDate']; //uses ISO8601 date format
                                                        $rwamount = $results['TrackingInfoTransactionSearchResult']['amount'];
                                                        $rremarks = $results['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                                        $rtransactionID = $results['TrackingInfoTransactionSearchResult']['transactionID'];
                                                    }
                                                    break;
                                                case strstr($serviceName, "Habanero UB"):
                                                    foreach ($transinfo as $results) {
                                                        $riserror = $results['querytransmethodResult']['Success'];
                                                        $reffdate = $results['querytransmethodResult']['DtAdded'];
                                                        $rwamount = $results['querytransmethodResult']['Amount'];
                                                        $rremarks = "Withdrawal Success";
                                                        $rtransactionID = $results['querytransmethodResult']['TransactionId'];
                                                        $rwbalafter = $results['querytransmethodResult']['BalanceAfter'];
                                                    }
                                                    break;
                                            } if ($riserror) {

                                                $fmteffdate = $otopup->getDate();
                                                $vactualAmt = $rwamount;
                                                $vstatus = 1;
                                                $vtransactionID = $rtransactionID;
                                                $vtransStatus = $rremarks;
                                                $issucess = $otopup->updateReversalSuccess($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                                if ($issucess > 0) {


                                                    switch (true) {
                                                        case strstr($serviceName, "RTG - Sapphire V17 UB"): $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);

                                                            break;

                                                        case strstr($serviceName, "Habanero UB"):
                                                            $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $ubserviceID, $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $UBServicePassword);
                                                            break;
                                                    }


                                                    $msg = "Redeemed: " . $vactualAmt . "; Remaining Balance: " . $balance;
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            } else {

                                                if ($riserror == "") {
                                                    $status = 2;
                                                    $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                    $msg = $rremarks;
                                                    echo json_encode($msg);
                                                    exit;
                                                } else {
                                                    $status = 2;
                                                    $otopup->updateReversalSuccessFailed($status, $lastmrid);
                                                    $msg = $riserror; //error message when calling the withdrawal result
                                                    echo json_encode($msg);
                                                    exit;
                                                }
                                            }
                                        } else {

                                            $msg = "Manual Redemption Error : Failed to process manual redemption";
                                            echo json_encode($msg);
                                            exit;
                                        }
                                    }
                                }
                            } else {
                                $msg = "Manual Redemption: Error on inserting manual redemption";
                                echo json_encode($msg);
                                exit;
                            }
                        }
                    }
                }

                break;




///// END REVERSAL
            //User Based Redemption using Loyalty Card
            case "Withdraw":
                $ubserviceID = $_POST['txtserviceid'];
                $vserviceBalance = $_POST['txtamount2'];
                $amountToRedeem = $_POST['hdnamtwithdraw'];
                $ticketub = $_POST['txtticketub'];
                $remarksub = $_POST['txtremarksub'];
                $loyaltycardnumber = $_POST['txtcardnumber'];
                $mid = $_POST['txtmid'];
                $usermode = $_POST['txtusermode'];
                $siteID = $_POST['cmbsite'];
                //ADDED CCT 07/13/2018 BEGIN
                // Withdraw All
                $amountToRedeem = $vserviceBalance;
                //ADDED CCT 07/13/2018 END
                //ADDED CCT 01/14/2019 BEGIN
                // Check if with active session on selected provider
                $isWithActiveSession = true;
                $resActiveSession = null;
                $resActiveSession = $otopup->checkUBactivesession($mid, $ubserviceID);
                if ($resActiveSession != null) {   // With Active Session, check if activeservicestatus = 1 OR 8
                    if (($resActiveSession['ActiveServiceStatus'] == 1) || ($resActiveSession['ActiveServiceStatus'] == 8)) {
                        $resActiveSessionCount = $otopup->countUBactivesession($mid, $ubserviceID);
                        if ($resActiveSessionCount['CountServices'] > 0) {
                            $msg = "UB Card has more than 1 active session.";
                        } else {
                            if ($resActiveSession['ActiveServiceStatus'] == 1) {
                                $issucess = $otopup->updateactiveservicestatus($mid, 8, 0);
                                if ($issucess > 0) {
                                    $isWithActiveSession = false; // override
                                } else {
                                    $msg = "Error updating service status.";
                                }
                            } else {
                                $isWithActiveSession = false; // override
                            }
                        }
                    } else {
                        $msg = "Manual Redemption of player balance on this provider is currently not allowed.  Please try again later.";
                    }
                } else {
                    $isWithActiveSession = false;
                }

                if ($isWithActiveSession == false) { // No active session on selected provider, proceed with MR
                    //ADDED CCT 01/14/2019 END
                    if (isset($siteID) && $siteID != "-1") {
                        $login = $otopup->getServiceUserName($ubserviceID, $mid);
                        $ubterminalID = null;
                        $server = $otopup->getCasinoName($ubserviceID);

                        foreach ($server as $value2) {
                            $servername = $value2['ServiceName'];
                            $usermode = $value2['UserMode'];
                        }

                        $servicegrp = $otopup->getServiceGrpName($ubserviceID);
                        $servername = $servicegrp;

                        //check if card is ewallet
                        $isEwallet = $otopup->checkIsEwallet($mid);

                        if ($isEwallet == 1)
                            $ramount = ereg_replace(",", "", $amountToRedeem); //format number replace (,)
                        else
                            $ramount = ereg_replace(",", "", $vserviceBalance); //format number replace (,)

                        $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)

                        if ($ramount > $_maxRedeem) {
                            $balance = $ramount - $_maxRedeem;
                            $ramount = $_maxRedeem;
                        } else {
                            $balance = 0;
                        }

                        $vreportedAmt = $ramount;
                        $vactualAmt = 0;
                        $vtransactionDate = $otopup->getDate();
                        $vreqByAID = $aid;
                        $vprocByAID = $aid;
                        $vdateEffective = $vdate;
                        $vstatus = 0;
                        $vtransactionID = 0;
                        $vtransStatus = '';
                        $transsummaryid = null;
                        $url = $_ServiceAPI[$ubserviceID - 1];
                        $capiplayername = '';
                        $capiserverID = '';
                        $locatorname = '';
                        $lastmrid = $otopup->insertmanualredemptionub($siteID, $ubterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $remarksub, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $ticketub, $ubserviceID, $vtransStatus, $loyaltycardnumber, $mid, $usermode);

                        if ($lastmrid > 0) {
                            $tracking1 = "MR" . $lastmrid;

                            switch (true) {
                                case strstr($servername, "RTG"): //if provider is RTG, then

                                    $capiusername = '';
                                    $capipassword = '';
                                    $tracking2 = $siteID;
                                    $tracking3 = '';
                                    $withdraw = array();
                                    $tracking4 = '';
                                    $methodname = '';

                                    if ($isEwallet == 1) {
                                        if ($amountToRedeem != "") {
                                            if ($amountToRedeem <= $vserviceBalance) {
                                                $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $usermode, $locatorname);
                                            } else {
                                                $msg = "Amount to redeem should be less than or equal to Casino balance.";
                                            }
                                        } else {
                                            $msg = "Invalid amount to withdraw.";
                                        }
                                    } else {
                                        $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $usermode, $locatorname);
                                    }
                                    //check if redemption was successfull, and update information on manualredemptions and insert in audittrail
                                    if ($withdraw['IsSucceed'] == true) {
                                        //fetch the information when calling the RTG Withdraw Method
                                        foreach ($withdraw as $results) {
                                            $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                            $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                            $rwamount = $results['WithdrawGenericResult']['amount'];
                                            $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                            $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                        }
                                        $fmteffdate = str_replace("T", " ", $reffdate);
                                        $vreportedAmt = $ramount;
                                        $vactualAmt = $rwamount;
                                        $vtransactionDate = $otopup->getDate();
                                        $vstatus = 1;
                                        $vtransactionID = $rtransactionID;

                                        //check if there was no error on withdrawal
                                        if ($riserror == "OK") {
                                            $vtransStatus = $rremarks;
                                            $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);
                                            if ($issucess > 0) {
                                                //get new balance after redemption
                                                $balance = $CasinoGamingCAPI->getBalance($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID);

                                                $ramount = number_format($ramount, 2, ".", ",");
                                                $balance = number_format($balance, 2, ".", ",");

                                                //update member services
                                                if ($otopupmembership->open()) {
                                                    $otopupmembership->updateMemberServices($balance, $mid, $ubserviceID, $issucess);
                                                }
                                                $otopupmembership->close();

                                                // ADDED CCT 01/11/2019 BEGIN
                                                $issucess = $otopup->updateactiveservicestatus($mid, 1, 1);
                                                // ADDED CCT 01/11/2019 END
                                                //insert into audit trail
                                                $vtransdetails = "transaction id " . $vtransactionID . ",amount " . $vreportedAmt;
                                                $vauditfuncID = 7;
                                                $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                                $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                            } else {
                                                $msg = "Manual Redemption: Error on inserting manual redemption";
                                            }
                                        } else {
                                            if ($riserror == "") {
                                                $msg = $rremarks;
                                            } else {
                                                $status = 2;
                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                $msg = $riserror; //error message when calling the withdrawal result
                                            }
                                        }
                                    } else {
                                        $status = 2;
                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                        $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                    }
                                    break;

                                case strstr($servername, "HAB"): //if provider is Habanero, then

                                    $capiusername = $_HABbrandID;
                                    $capipassword = $_HABapiKey;
                                    $tracking2 = '';
                                    $tracking3 = '';
                                    $withdraw = array();

                                    $serviceUBResult = $otopup->getUBInfo($mid, $ubserviceID);

                                    if ($isEwallet == 1) {
                                        if ($amountToRedeem != "") {
                                            if ($amountToRedeem <= $vserviceBalance) {
                                                $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $serviceUBResult['ServicePassword']);
                                            } else {
                                                $msg = "Amount to redeem should be less than or equal to Casino balance.";
                                            }
                                        } else {
                                            $msg = "Invalid amount to withdraw.";
                                        }
                                    } else {
                                        $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '', $usermode, $locatorname, $serviceUBResult['ServicePassword']);
                                    }

                                    //check if redemption was successfull
                                    if ($withdraw['IsSucceed'] == true) {
                                        $riserror = $withdraw['TransactionInfo']['withdrawmethodResult']['Message'];
                                        $rwamount = $withdraw['TransactionInfo']['withdrawmethodResult']['Amount'];
                                        $rtransactionID = $withdraw['TransactionInfo']['withdrawmethodResult']['TransactionId'];

                                        //check if there was no error on withdrawal
                                        if ($riserror == "Withdrawal Success") {
                                            // Check if Amount < 0, Withdrawal still returns Withdrawal Success even if Previous Balance before Withdrawal is already zero
                                            if ($rwamount < 0) {
                                                $vstatus = 1;
                                                $vdateEffective = $otopup->getDate();
                                                $vtransactionDate = $vdateEffective;
                                                $rremarks = $riserror;

                                                // UPDATE MANUAL REDEMPTIONS TABLE BASED ON RETURN OF API CALL
                                                $issucess = $otopup->updateManualRedemptionub($vstatus, $rwamount * -1, $rtransactionID, $vdateEffective, $rremarks, $lastmrid);
                                                if ($issucess > 0) {
                                                    // get new balance after redemption
                                                    $rbalance = array();
                                                    $rbalance = $CasinoGamingCAPI->getBalance($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode = "", $serviceUBResult['ServicePassword']);

                                                    if ($rbalance['IsSucceed'] == true) {
                                                        if (isset($rbalance['BalanceInfo']['Balance'])) {
                                                            $habbalance = $rbalance['BalanceInfo']['Balance'];
                                                            $balance = $habbalance;
                                                        }
                                                    }
                                                    //update member services
                                                    if ($otopupmembership->open()) {
                                                        $otopupmembership->updateMemberServices($balance, $mid, $ubserviceID, $issucess);
                                                    }
                                                    $otopupmembership->close();

                                                    // ADDED CCT 01/11/2019 BEGIN
                                                    $issucess = $otopup->updateactiveservicestatus($mid, 1, 1);
                                                    // ADDED CCT 01/11/2019 END
                                                    //insert into audit trail
                                                    $rwamountnonneg = $rwamount * -1;
                                                    $rwamountnonneg = number_format($rwamountnonneg, 2, '.', ',');
                                                    $vtransdetails = "Transaction ID: " . $rtransactionID . ", Amount: " . $rwamountnonneg;
                                                    $vauditfuncID = 7;
                                                    $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                                    $msg = "Redeemed: " . $rwamountnonneg . "; Remaining Balance: " . $balance;
                                                } else {
                                                    // UPDATE MANUAL REDEMPTIONS TABLE BASED ON RETURN OF UPDATE STATEMENT
                                                    $status = 2;
                                                    $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                    $msg = "Manual Redemption: Error on updating  manual redemption record.";
                                                }
                                            } else {
                                                // UPDATE MANUAL REDEMPTIONS TABLE BASED ON RETURN OF API CALL
                                                $status = 2;
                                                $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                                $msg = "Manual Redemption: Player Balance is already zero.";
                                            }
                                        } else {
                                            $status = 2;
                                            $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                            $msg = $riserror; //error message when calling the withdrawal result                                       
                                        }
                                    } else {
                                        $status = 2;
                                        $otopup->updateManualRedemptionFailedub($status, $lastmrid);
                                        $msg = "Manual Redemption: " . $withdraw['ErrorMessage']; //error message when initially calling the Habanero API
                                    }
                                    break;

                                default :
                                    echo "Error: Invalid Casino Provider";
                                    break;
                            }
                        } else {
                            $msg = "Error: Failed to insert in Manual Redemptions Table";
                        }
                    } else {
                        $msg = "Please select Site ID";
                    }
                    //ADDED CCT 01/14/2019 BEGIN
                }
                //ADDED CCT 01/14/2019 END
                echo json_encode($msg);
                $otopup->close();
                break;

            //Get Casino Services provided by membership card    
            case "GetCasino":

                $casino = $_SESSION['CasinoArray'];
                $rmid = $_SESSION['MID'];

                $_SESSION['CasinoArray2'] = $_SESSION['CasinoArray'];
                $_SESSION['MID2'] = $_SESSION['MID'];

                $addedPwuu = array(
                    array('ServiceUsername' => null,
                        'ServicePassword' => null,
                        'HashedServicePassword' => null,
                        'ServiceID' => 1,
                        'UserMode' => 3,
                        'isVIP' => null,
                        'Status' => 1)
                );

                $casino = array_merge($casino, $addedPwuu);
                $casinoarray_count = count($casino);

                $casinos = array();
                $service = array();
                $casino2 = array();

                if ($casinoarray_count != 0) {
                    for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                        if (is_array($casino[$ctr])) {
                            $casinos[$ctr] = array(
                                array('ServiceUsername' => $casino[$ctr]['ServiceUsername'],
                                    'ServicePassword' => $casino[$ctr]['ServicePassword'],
                                    'HashedServicePassword' => $casino[$ctr]['HashedServicePassword'],
                                    'ServiceID' => $casino[$ctr]['ServiceID'],
                                    'UserMode' => $casino[$ctr]['UserMode'],
                                    'isVIP' => $casino[$ctr]['isVIP'],
                                    'Status' => $casino[$ctr]['Status'],)
                            );
                        } else {
                            $casinos[$ctr] = array(
                                array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                    'ServicePassword' => $casino[$ctr]->ServicePassword,
                                    'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                    'ServiceID' => $casino[$ctr]->ServiceID,
                                    'UserMode' => $casino[$ctr]->UserMode,
                                    'isVIP' => $casino[$ctr]->isVIP,
                                    'Status' => $casino[$ctr]->Status)
                            );
                        }


                        //loop through array output to get casino array from membership card
                        foreach ($casinos[$ctr] as $value) {
                            $rserviceuname = $value['ServiceUsername'];
                            $rservicepassword = $value['ServicePassword'];
                            $rserviceid = $value['ServiceID'];
                            $rusermode = $value['UserMode'];
                            $risvip = $value['isVIP'];
                            $hashedpassword = $value['HashedServicePassword'];
                            $rstatus = $value['Status'];

                            $servicename = $otopup->getCasinoName($rserviceid);
                            $servicegrp = $otopup->getServiceGrpName($rserviceid);
                            $servicestat = $otopup->getServiceStatus($rserviceid);

                            if ($servicestat == 1) {
                                //loop htrough services to get if has pedning balance
                                foreach ($servicename as $service2) {
                                    $serviceName = $service2['ServiceName'];
                                    $serviceStatus = $service2['Status'];
                                    $servicegrp = $servicegrp;

                                    switch (true) {
                                        // ADDED CCT 11/29/2017 BEGIN
                                        case strstr($serviceName, "Habanero"): //if provider is Habanero, then
                                            //call get balance method of Habanero
                                            $url = $_ServiceAPI[$rserviceid - 1];
                                            $capiusername = $_HABbrandID;
                                            $capipassword = $_HABapiKey;
                                            $capiplayername = '';
                                            $capiserverID = '';
                                            $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode, $rservicepassword);
                                            break;
                                        // ADDED CCT 11/29/2017 END
                                        case strstr($serviceName, "RTG"): //if provider is RTG, then
                                            //call get balance method of RTG
                                            $url = $_ServiceAPI[$rserviceid - 1];
                                            $capiusername = $_CAPIUsername;
                                            $capipassword = $_CAPIPassword;
                                            $capiplayername = $_CAPIPlayerName;
                                            $capiserverID = '';
                                            $balance = $CasinoGamingCAPI->getBalance($servicegrp, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode);
                                            break;

                                        case strstr($serviceName, "PWW"): //if provider is PWW, then
                                            //get balance to mztransactiontransfer

                                            $getTransactionSummaryID = $otopup->getTransactionSummaryID($rmid);

                                            if ($getTransactionSummaryID['TransactionSummaryID']) {

                                                $getMZTransactionTransferDetails = $otopup->getMZTransactionTransferDetails($getTransactionSummaryID['TransactionSummaryID']);
                                                if (!empty($getMZTransactionTransferDetails)) {

                                                    if ($getMZTransactionTransferDetails['TransferStatus'] == 8) {
                                                        $balance = 0;
                                                    } else if ($getMZTransactionTransferDetails['TransferStatus'] == 90) {
                                                        $Sname = $otopup->getCasinoName($getMZTransactionTransferDetails['FromServiceID']);
                                                        $Sname = $Sname[0]['ServiceName'];
                                                        $Sgrp = $otopup->getServiceGrpName($getMZTransactionTransferDetails['FromServiceID']);

                                                        if ($otopupmembership->open()) {

                                                            $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($getTransactionSummaryID['MID'], $getMZTransactionTransferDetails['FromServiceID']);
                                                        }

                                                        $otopupmembership->close();

                                                        $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                        $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                        $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                        $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                        switch (true) {
                                                            // ADDED CCT 11/29/2017 BEGIN
                                                            case strstr($Sname, "Habanero"): //if provider is Habanero, then
                                                                //call get balance method of Habanero
                                                                $url = $_ServiceAPI[$getMZTransactionTransferDetails['FromServiceID'] - 1];
                                                                $capiusername = $_HABbrandID;
                                                                $capipassword = $_HABapiKey;
                                                                $capiplayername = '';
                                                                $capiserverID = '';
                                                                $balance = $CasinoGamingCAPI->getBalance($Sgrp, $getMZTransactionTransferDetails['FromServiceID'], $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode, $UBServicePassword);
                                                                break;
                                                            // ADDED CCT 11/29/2017 END
                                                            case strstr($Sname, "RTG"): //if provider is RTG, then
                                                                //call get balance method of RTG
                                                                $url = $_ServiceAPI[$getMZTransactionTransferDetails['FromServiceID'] - 1];
                                                                $capiusername = $_CAPIUsername;
                                                                $capipassword = $_CAPIPassword;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';
                                                                $balance = $CasinoGamingCAPI->getBalance($Sgrp, $getMZTransactionTransferDetails['FromServiceID'], $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode);
                                                                break;

                                                            default :
                                                                echo "Error: Invalid Casino Provider";
                                                                break;
                                                        }
                                                    } else if ($getMZTransactionTransferDetails['TransferStatus'] == 93) {
                                                        $Sname = $otopup->getCasinoName($getMZTransactionTransferDetails['ToServiceID']);
                                                        $Sname = $Sname[0]['ServiceName'];
                                                        $Sgrp = $otopup->getServiceGrpName($getMZTransactionTransferDetails['ToServiceID']);

                                                        if ($otopupmembership->open()) {

                                                            $getPlayerCredentialsByUB = $otopupmembership->getPlayerCredentialsByUB($getTransactionSummaryID['MID'], $getMZTransactionTransferDetails['ToServiceID']);
                                                        }

                                                        $otopupmembership->close();

                                                        $UBServiceLogin = $getPlayerCredentialsByUB['ServiceUsername'];
                                                        $UBServicePassword = $getPlayerCredentialsByUB['ServicePassword'];
                                                        $UBHashedServicePassword = $getPlayerCredentialsByUB['HashedServicePassword'];
                                                        $usermode = $getPlayerCredentialsByUB['Usermode'];

                                                        switch (true) {
                                                            // ADDED CCT 11/29/2017 BEGIN
                                                            case strstr($Sname, "Habanero"): //if provider is Habanero, then
                                                                //call get balance method of Habanero
                                                                $url = $_ServiceAPI[$getMZTransactionTransferDetails['ToServiceID'] - 1];
                                                                $capiusername = $_HABbrandID;
                                                                $capipassword = $_HABapiKey;
                                                                $capiplayername = '';
                                                                $capiserverID = '';
                                                                $balance = $CasinoGamingCAPI->getBalance($Sgrp, $getMZTransactionTransferDetails['ToServiceID'], $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode, $UBServicePassword);
                                                                break;
                                                            // ADDED CCT 11/29/2017 END
                                                            case strstr($Sname, "RTG"): //if provider is RTG, then
                                                                //call get balance method of RTG
                                                                $url = $_ServiceAPI[$getMZTransactionTransferDetails['ToServiceID'] - 1];
                                                                $capiusername = $_CAPIUsername;
                                                                $capipassword = $_CAPIPassword;
                                                                $capiplayername = $_CAPIPlayerName;
                                                                $capiserverID = '';
                                                                $balance = $CasinoGamingCAPI->getBalance($Sgrp, $getMZTransactionTransferDetails['ToServiceID'], $url, $UBServiceLogin, $capiusername, $capipassword, $capiplayername, $capiserverID, $rusermode);
                                                                break;

                                                            default :
                                                                echo "Error: Invalid Casino Provider";
                                                                break;
                                                        }
                                                    } else {
                                                        $balance = 0;
                                                    }
                                                } else {
                                                    $balance = 0;
                                                }
                                            } else {
                                                $balance = 0;
                                            }


                                            break;

                                        default :
                                            echo "Error: Invalid Casino Provider";
                                            break;
                                    }

                                    $terminalid = $otopup->viewTerminalID($rserviceuname);
                                    switch ($serviceStatus) {
                                        case 1: $serviceStatus = "Active";
                                            break;
                                        case 0: $serviceStatus = "InActive";
                                    }

                                    if (is_double($balance) || is_numeric($balance)) {
                                        $balance = number_format($balance, 2, '.', '');
                                    }

                                    $getTransactionSummaryID = $otopup->getTransactionSummaryID($rmid);

                                    if ($getTransactionSummaryID['TransactionSummaryID']) {
                                        $withMZTransfer = true;
                                    } else {
                                        $withMZTransfer = false;
                                    }

                                    $casino2 = array(
                                        "UserName" => "$rserviceuname",
                                        "Password" => $rservicepassword,
                                        "HashedPassword" => "$hashedpassword",
                                        "ServiceName" => $serviceName,
                                        "ServiceID" => $rserviceid,
                                        "TerminalID" => $terminalid['TerminalID'],
                                        "UserMode" => "$rusermode",
                                        "IsVIP" => "$risvip",
                                        "MemberID" => "$rmid",
                                        "Status" => "$rstatus",
                                        "Balance" => $balance,
                                        "MZTransfer" => $withMZTransfer
                                    );
                                    $_SESSION['ServicePassword'] = $rservicepassword;
                                    $_SESSION['ServiceUserName'] = $rserviceuname;
                                }
                            }
                        }

                        if (!empty($casino2)) {
                            array_push($service, $casino2);
                        }
                    }
                }
                $cntservice = 0;

                foreach ($service as $value) {
                    if (isset($value["UserName"])) {
                        $cntservice++;
                    }
                }

                if (count($service) > 0 && $cntservice > 0) {
                    echo json_encode($service);
                } else {
                    $msg = "No User Based.";
                    echo json_encode($msg);
                }

                unset($casino, $_SESSION['CasinoArray']);
                unset($service, $casino2, $_SESSION['MID']);
                $otopup->close();
                break;



            case "PostManualTopUp":
                //check if all variables are set; all are required fields
                if (isset($_POST['txtamount']) && isset($_POST['txtminbal']) && isset($_POST['txtmaxbal']) && isset($_POST['optpick'])) {
                    //validate if site dropdown box was selected
                    if ($_POST['cmbsite'] > 0) {
                        $vSiteID = $_POST['cmbsite'];
                    } else {
                        //validate if pos account textfield have value
                        if (strlen($_POST['txtposacc']) > 0) {
                            //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                            if (strlen($_POST['txtposacc']) == 10) {
                                $vposaccno = $_POST['txtposacc'];
                            } else {
                                $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                            }

                            $rsite = $otopup->getidbyposacc($vposaccno);
                            if ($rsite) {
                                $vSiteID = $rsite['SiteID'];
                            } else {
                                $msg = "Invalid POS Account Number";
                                $otopup->close();
                                $_SESSION['mess'] = $msg;
                                header("Location: ../topupmanual.php");
                            }
                        } else {
                            $msg = "Post Manual Top-up: Invalid fields.";
                        }
                    }

                    $vAmount = removeComma($_POST['txtamount']); # inputted amount
                    $vBalance = removeComma($_POST['txtamount']);
                    $vMinBalance = removeComma($_POST['txtminbal']);
                    $vMaxBalance = removeComma($_POST['txtmaxbal']);
                    if (((float) $vAmount > 0.00) && ((float) $vMinBalance > 0.00) && ((float) $vMaxBalance > 0.00)) {
                        $vLastTransactionDate = null; # always null
                        $vLastTransactionDescription = null; #always null
                        $vTopUpType = 0;
                        $vPickUpTag = $_POST['optpick'];
                        $vPrevBalance = 0;
                        $vNewBalance = removeComma($_POST['txtamount']); # inputted amount
                        //session id
                        $vCreatedByAID = $aid;

                        $vDateCreated = $vdate;
                        $vStartBalance = 0; # result based on select bcf where siteid = 12
                        $vEndBalance = $vStartBalance + $vNewBalance; #start balance + inputted amount
                        $vToupAmount = removeComma($_POST['txtamount']); # inputted amount;
                        $vTotalTopupAmount = removeComma($_POST['txtamount']);  # inputted amount;
                        $vTopupCount = 0; # if TopUpType is fixed then TopupCount = 1 else TopupCount = 0
                        $vRemarks = "Manual TopUp";
                        $vAutoTopUpEnabled = 0; // auto top up trigger
                        $vAutoTopUpAmount = 0; //default to 0; for auto topup
                        $vStatus = 1; //0 - Pending; 1 - Successful; 2 - Failed
                        $vTopupTransactionType = 0; //0 - Manual Topup; 1 - AutoTopup
                        $postedmanualtopup = $otopup->insertsitebalance($vSiteID, $vBalance, $vMinBalance, $vMaxBalance, $vLastTransactionDate, $vLastTransactionDescription, $vTopUpType, $vPickUpTag, $vAmount, $vPrevBalance, $vNewBalance, $vCreatedByAID, $vDateCreated, $vStartBalance, $vEndBalance, $vToupAmount, $vTotalTopupAmount, $vTopupCount, $vRemarks, $vAutoTopUpEnabled, $vAutoTopUpAmount, $vTopupTransactionType, $vStatus);
                        if ($postedmanualtopup == 0) {
                            $msg = "Post Manual TopUp: Error in inserting record in sitebalance";
                        } else {
                            $msg = "Post Manual TopUp: Record inserted in sitebalance";
                            //insert into audit trail
                            $vtransdetails = "SiteCode " . $_POST['txtsitecode'] . "Amount " . $_POST['txtamount'];
                            $vauditfuncID = 17;
                            $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        }
                    } else {
                        $msg = "Post Manual Top-up: Zero input is not allowed";
                    }
                } else {
                    $msg = "Post Manual Top-up: Invalid fields.";
                }
                $nopage = 1;
                //redirect to site view page with corresponding popup message
                $otopup->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../topupmanual.php");
                break;

            case "UpdateSiteParam":
                //get variables
                $vSiteID = $_POST['txtsite'];
                $vMinBalance = removeComma($_POST['txtminbal']);
                $vMaxBalance = removeComma($_POST['txtmaxbal']);
                if (((float) $vMinBalance > 0.00) && ((float) $vMaxBalance > 0.00)) {
                    $vPickUpTag = $_POST['optpick'];
                    $vOptType = $_POST['opttype'];
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;
                    $vupatedrow = $otopup->updatesiteparam($vSiteID, $vMinBalance, $vMaxBalance, $vOptType, $vPickUpTag);
                    if ($vupatedrow > 0) {
                        $msg = "Site Balance parameters successfully updated";
                        //insert into audit trail
                        $arrnewdetails = array($vMinBalance, $vMaxBalance, $vPickUpTag, $vOptType);
                        $newdetails = implode(",", $arrnewdetails);
                        $vtransdetails = "sitecode " . $_POST['txtsitecode'] . " ;old params " . $_POST['txtolddetails'] . " ;new params " . $newdetails;
                        $vauditfuncID = 18;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    } else {
                        $msg = "Update Site Balance Parameter: Record unchanged.";
                    }
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    unset($arrnewdetails);
                    header("Location: ../topupview.php");
                } else {
                    $msg = "Update Site Balance Parameter: Zero input is not allowed";
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    header("Location:  ../topupupdateparam.php");
                }
                break;

            case "UpdateSiteBalance":
                // get variables with value
                $vSiteID = $_POST['txtsite'];
                $vAmount = removeComma($_POST['txtamount']); # inputted amount
                $vPrevBalance = removeComma($_POST['txtprevbal']);
                $vMinBalance = removeComma($_POST['txtminbal']);
                $vMaxBalance = removeComma($_POST['txtmaxbal']);
                if (((float) $vAmount > 0.00) && ((float) $vMinBalance > 0.00) && ((float) $vMaxBalance > 0.00)) {
                    $vPickUpTag = $_POST['optpick'];
                    $vOptType = $_POST['opttype'];
                    $vNewBalance = $vPrevBalance + $vAmount;
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;
                    $vRemarks = "Manual TopUp";
                    $vTopUpCount = 0; //for manual topup
                    $vTopupTransactionType = 0; //for manual topup
                    $vStatus = 1; //for manual topup

                    $vupatedrow = $otopup->updatebalance($vAmount, $vSiteID, $vPrevBalance, $vNewBalance, $vCreatedByAID, $vDateCreated, $vOptType, $vMinBalance, $vMaxBalance, $vPickUpTag, $vTopUpCount, $vStatus, $vRemarks, $vTopupTransactionType);
                    $nopage = 1;
                    if ($vupatedrow > 0) {
                        $msg = "Update Site Balance: Balance successfully updated.";
                        $vsitecode = $otopup->getsitecode($vSiteID);
                        //insert into audit trail
                        $vtransdetails = "SiteCode " . $vsitecode['SiteCode'] . "Old bcf " . $_POST['txtprevbal'] . "New bcf " . $vNewBalance;
                        $vauditfuncID = 19;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    } else {
                        $msg = "Update Site Balance: Record unchanged";
                    }
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    header("Location: ../topupview.php");
                } else {
                    $msg = "Update Site Balance : Zero input is not allowed";
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    header("Location: ../topupupdatebal.php");
                }
                break;

            case "ReversalofDeposits":
                //get variables
                $vSiteRemittanceID = $_POST['cmbsiteremit'];
                $vupatedrow = $otopup->updatesiteremittancestatus($vSiteRemittanceID, $aid, $vdate);
                if ($vupatedrow == 0) {
                    $msg = "Verify Deposits: Record unchanged";
                } else {
                    $msg = "Verify Deposits: Success in updating status in site remittance";
                    //insert into audit trail
                    $vtransdetails = "TopUp Reversal:Verify Deposits, sitremittanceid = " . $vSiteRemittanceID . " msg-" . $msg;
                    $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
                }
                $nopage = 1;
                $otopup->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../reversaldeposit.php");
                break;

            case "ReversalofDeposits2":
                //get variables
                $vSiteRemittanceID = $_POST['cmbsiteremit'];
                $vstatus = $_POST['optstatus'];
                $vupatedrow = $otopup->updateverifiedsiteremittance($vSiteRemittanceID, $vstatus, $aid, $vdate);
                if ($vupatedrow == 0) {
                    $msg = "Update Verified Deposits: Record unchanged";
                } else {
                    $msg = "Update Verified Deposits: Success in updating status in site remittance";
                    //insert into audit trail
                    $vtransdetails = "Update Verified Deposits, sitremittanceid = " . $vSiteRemittanceID . ' status =' . $vstatus . " msg-" . $msg;
                    $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
                }
                $nopage = 1;
                $otopup->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../reversaldeposit2.php");
                break;

            case "ManualTopUpReversal":
                if (isset($_POST['txtusername']) && isset($_POST['txtpassword']) && isset($_POST['cmbsite'])) {
                    //validate if site dropdown box was selected
                    if ($_POST['cmbsite'] > 0) {
                        $vSiteID = $_POST['cmbsite'];
                    } else {
                        //validate if pos account textfield have value
                        if (strlen($_POST['txtposacc']) > 0) {
                            //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                            if (strlen($_POST['txtposacc']) == 10) {
                                $vposaccno = $_POST['txtposacc'];
                            } else {
                                $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                            }

                            $rsite = $otopup->getidbyposacc($vposaccno);
                            //check if pos account is valid
                            if ($rsite) {
                                $vSiteID = $rsite['SiteID'];
                            } else {
                                $msg = "Invalid POS Account Number";
                            }
                        }
                    }
                    $vaccname = $_POST['txtusername'];
                    $vaccpass = $_POST['txtpassword'];
                    $rresult = $otopup->checkaccountdetails($vaccname, $vaccpass);
                    $isexist = $rresult['ctracc'];
                    if ($isexist > 0) {
                        $vTopUpType = 1;
                        $vCreatedByAID = $aid;
                        $vDateCreated = $vdate;
                        $vAmount = removeComma($_POST['txtamount']);

                        $rBCF = $otopup->getallbcf($vSiteID);

                        foreach ($rBCF as $rresultbcf) {
                            $vPrevBalance = $rresultbcf['Balance'];
                            $vMinBalance = $rresultbcf['MinBalance'];
                            $vMaxBalance = $rresultbcf['MaxBalance'];
                            $vTopUpType = $rresultbcf['TopUpType'];
                            $vPickUpTag = $rresultbcf['PickUpTag'];
                        }
                        if ($vAmount > 0 && $vamount <= $vPrevBalance) {
                            $vRemarks = "Manual TopUp";
                            $vTopUpCount = 0; //for manual topup
                            $vTopupTransactionType = 2; //for manual topup reversal
                            $vStatus = 1; //for manual topup
                            $vRemarks = 'Reversal of Manual TopUp';
                            //compute new balance :
                            //$vNewBalance =  $vPrevBalance + $vAmount;
                            $vNewBalance = $vPrevBalance - $vAmount;
                            $vupatedrow = $otopup->updatereversal($vAmount, $vSiteID, $vPrevBalance, $vNewBalance, $vTopUpType, $vCreatedByAID, $vDateCreated, $vTopUpType, $vMinBalance, $vMaxBalance, $vPickUpTag, $vTopUpCount, $vStatus, $vRemarks, $vTopupTransactionType);
                            if ($vupatedrow == 0) {
                                $msg = "ManualTopUpReversal: Error in manual topup reversal";
                            } else {
                                $msg = "ManualTopUpReversal: Successful manual topup reversal";
                                //insert into audit trail
                                $vtransdetails = "Site code " . $_POST['txtsitecode'] . "old amt " . $vPrevBalance . "new amt " . $vNewBalance;
                                $vauditfuncID = 20;
                                $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                            }
                            //insert into audit trail --> For authorizations
                            $vtransdetails = "ManualTopUpReversal: Authorized by: " . $vaccname;
                            $vauditfuncID = 50;
                            $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        } else {
                            $msg = "ManualTopUpReversal: Invalid amount";
                        }
                    } else {
                        $msg = "Account Details: Invalid Account";
                    }
                } else {
                    $msg = "Account Details: Invalid Fields";
                }

                $nopage = 1;
                $_SESSION['mess'] = $msg;
                //redirect to site view page with corresponding popup message
                $otopup->close();
                header("Location: ../topupreversal.php");
                break;

            case "CashOnHandAdjustment":
                //echo $_POST['cmbsitename'].$_POST['txtposacc'].$_POST['txtAmount'].$_POST['txtReason'];
                if (isset($_POST['cmbsitename']) || isset($_POST['txtposacc']) && isset($_POST['txtAmount']) && isset($_POST['txtReason'])) {
                    $reason = preg_replace('/\s\s+/', ' ', trim($_POST['txtReason']));
                    if ($_POST['cmbsitename'] > 0) {
                        $vSiteID = $_POST['cmbsitename'];
                    } else {
                        //validate if pos account textfield have value
                        if (strlen($_POST['txtposacc']) > 0) {
                            //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                            if (strlen($_POST['txtposacc']) == 10) {
                                $vposaccno = $_POST['txtposacc'];
                            } else {
                                $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                            }

                            $rsite = $otopup->getidbyposacc($vposaccno);
                            //check if pos account is valid
                            if ($rsite) {
                                $vSiteID = $rsite['SiteID'];
                            } else {
                                $msg = "Invalid POS Account Number";
                            }
                        }
                    }

                    $vaccname = $_POST['txtusername'];
                    $vaccpass = $_POST['txtpassword'];
                    $rresult = $otopup->checkaccountdetails($vaccname, $vaccpass);
                    $isexist = $rresult['ctracc'];

                    if ($isexist > 0) {
                        $vTopUpType = 1;
                        $vCreatedByAID = $aid;
                        $vDateCreated = $vdate;
                        $vAmount = removeComma($_POST['txtAmount']);
                        //$vAmount = $_POST['txtAmount'];

                        $approvedBy = $otopup->getAccountAID($vaccname, $vaccpass);
                        $retval = $otopup->insertCohAdjustment($vSiteID, $vAmount, $reason, $vCreatedByAID, $approvedBy, $vDateCreated);

                        if ($retval['ErrorCode'] == 0) {
                            $msg = "Cash on hand Adjustment has successfully added.";
                            $msg2 = "[" . $retval['LastInsertID'] . "]" . " Amount : " . $vAmount;
                            //$vtransdetails = "Cash on Hand Adjustment: ".$msg2;
                            $vauditfuncID = 101;
                            $otopup->logtoaudit($new_sessionid, $aid, $msg2, $vdate, $vipaddress, $vauditfuncID);
                        } else {
                            $msg = "An error occured while adding the new Cash on hand Adjustment.";
                        }
                    } else {
                        $msg = "Account Details: Invalid Account";
                    }
                } else {
                    $msg = "Account Details: Invalid Fields";
                }

                $nopage = 1;
                $_SESSION['mess'] = $msg;
                //redirect to site view page with corresponding popup message
                $otopup->close();
                header("Location: ../cashonhandadjustment.php");
                break;

            case "VerifiedDeposit":
                $vSiteRemittanceID = $_POST['cmbsiteremit'];
                $vupatedrow = $otopup->updatesiteremittancestatus($vSiteRemittanceID, $aid, $vdate);
                if ($vupatedrow == 0) {
                    $msg = " Verification of Deposits: Record unchanged";
                } else {
                    $msg = " Verification of Deposits: Success in updating status in site remittance";
                    //insert into audit trail
                    $vtransdetails = " Verification of Deposits:VerifiedDeposit, sitremittanceid = " . $vSiteRemittanceID . " msg-" . $msg;
                    $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
                }

                $nopage = 1;
                $otopup->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../reversaldeposit.php");
                break;

            case "PostingOfDeposit":

                if (isset($_POST['ddlRemittanceType']) && isset($_POST['txtAmount']) && isset($_POST['txtParticulars'])) {
                    //validate if site dropdown box was selected
                    if ($_POST['cmbsitename'] > 0) {
                        $vsiteID = $_POST['cmbsitename'];
                    } else {
                        //validate if pos account textfield have value
                        if (strlen($_POST['txtposacc']) > 0) {
                            //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                            if (strlen($_POST['txtposacc']) == 10) {
                                $vposaccno = $_POST['txtposacc'];
                            } else {
                                $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                            }

                            $rsite = $otopup->getidbyposacc($vposaccno);
                            //check if pos account is valid
                            if ($rsite) {
                                $vsiteID = $rsite['SiteID'];
                            } else {
                                $msg = "Invalid POS Account Number";
                                $_SESSION['mess'] = $msg;
                                $otopup->close();
                                header("Location: ../cashierdeposit.php");
                            }
                        } else {
                            $msg = "Posting of Deposits: Invalid fields.";
                        }
                    }

                    $vremittancetypeID = $_POST['ddlRemittanceType'];
                    $vamount = removeComma($_POST['txtAmount']);
                    $vparticulars = preg_replace('/\s\s+/', ' ', trim($_POST['txtParticulars']));
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;
                    $vStatus = 3; //set status to verified    
                    $vsitedate = null;

                    //if remittance type is bank
                    if ($vremittancetypeID == 1 || $vremittancetypeID == 3 || $vremittancetypeID == 4 || $vremittancetypeID == 6) {
                        if ($vremittancetypeID == 6) {
                            $vcheckno = null;
                        }

                        if ($vremittancetypeID == 1) {
                            $vcheckno = $_POST['txtChequeNo'];
                        }

                        $vbankID = $_POST['ddlBank'];
                        $vbranch = preg_replace('/\s\s+/', ' ', trim($_POST['txtBranch']));
                        $vbanktransID = $_POST['txtBankTransID'];
                        $vbanktransdate = $_POST['txtBankTransDate'];
                        $rresult = $otopup->insertdepositposting($vremittancetypeID, $vbankID, $vbranch, $vamount, $vbanktransID, $vbanktransdate, $vcheckno, $vCreatedByAID, $vparticulars, $vsiteID, $vStatus, $vDateCreated, $vsitedate);
                    }
                    //else, remittance type is walk-in
                    else {
                        $vbankID = null;
                        $vbranch = null;
                        $vbanktransID = null;
                        $vbanktransdate = null;
                        $vcheckno = null;
                        $rresult = $otopup->insertdepositposting($vremittancetypeID, $vbankID, $vbranch, $vamount, $vbanktransID, $vbanktransdate, $vcheckno, $vCreatedByAID, $vparticulars, $vsiteID, $vStatus, $vDateCreated, $vsitedate);
                    }
                    if ($rresult > 0) {
                        $msg = "Posting of Deposits: Successfully created";
                        $vtransdetails = " [" . $rresult . "] [" . $vremittancetypeID . "] Amount: " . $vamount;
                        $vauditfuncID = 104;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    } else {
                        $msg = "Posting of Deposits: Error on post";
                    }

                    $_SESSION['mess'] = $msg;
                    $otopup->close();
                    header("Location: ../cashierdeposit.php");
                } else {
                    $msg = "Posting of Deposits: Invalid fields.";
                    $_SESSION['mess'] = $msg;
                    $otopup->close();
                    header("Location: ../cashierdeposit.php");
                }
                break;

            //populate combobox with bank namesm, (postingofdeposit)
            case "GetBankName":
                $rbanknames = array();
                $rbanknames = $otopup->getbanknames();
                if (count($rbanknames) > 0) {
                    echo json_encode($rbanknames);
                } else {
                    echo "No Results Found";
                }
                unset($rbanknames);
                $otopup->close();
                exit;
                break;

            //Get operator info (display on lightbox)
            case "GetOperator":
                //validate if site dropdown box was selected
                if ($_POST['cmbsitename'] > 0) {
                    $vsiteID = $_POST['cmbsitename'];
                } else {
                    //validate if pos account textfield have value
                    if (strlen($_POST['txtposacc']) > 0) {
                        //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                        if (strlen($_POST['txtposacc']) == 10) {
                            $vposaccno = $_POST['txtposacc'];
                        } else {
                            $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                        }

                        $rsite = $otopup->getidbyposacc($vposaccno); //get site ID
                        //check if pos account is valid
                        if ($rsite) {
                            $vsiteID = $rsite['SiteID'];
                        } else {
                            echo "Invalid POS Account Number";
                            $otopup->close();
                            exit;
                        }
                    } else {
                        echo "Get Operator: Invalid fields.";
                        $otopup->close();
                        exit;
                    }
                }

                $rroperator = array();
                $rroperator = $otopup->getoperator($vsiteID);
                if (count($rroperator) > 0) {
                    echo json_encode($rroperator);
                } else {
                    echo "No operator found for this site";
                }
                unset($rroperator);
                $otopup->close();
                exit;
                break;

            case "InsertPegsConfirmation":
                if (isset($_POST['txtwho']) && isset($_POST['txtamount'])) {
                    $vsiteID = $_POST['txtsiteID'];
                    $vdatecredited = $_POST['txtdate'];
                    $vsiterep = $_POST['txtwho'];
                    $vamount = removeComma($_POST['txtamount']);
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;
                    $rconfirmationID = $otopup->insertconfirmation($vsiteID, $vdatecredited, $vsiterep, $vamount, $vCreatedByAID, $vDateCreated);
                    if ($rconfirmationID > 0) {
                        $msg = "PEGS Confirmation: Successfully confirmed";
                        $vtransdetails = "replenishment id " . $rconfirmationID .
                                ",SiteCode " . $_POST['txtsitecode'] . ",for date " . $vdate . ",amount " . $vamount;
                        $vauditfuncID = 22;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    } else {
                        $msg = "PEGS Confirmation: Error on insert";
                    }
                } else {
                    $msg = "PeGs Confirmation: Invalid Fields";
                }
                $_SESSION['mess'] = $msg;
                $otopup->close();
                header("Location: ../pegsconfirmation.php");
                break;

            case "ManualRedemption":
                $siteID = $_POST['cmbsite'];
                $login = $_POST['terminalcode'];
                $serverId = $_POST['cmbservices'];
                $provider = $_POST['txtservices'];
                $vterminalID = $_POST['cmbterminal'];
                $isredeem = 0;
                $vremarks = '';
                $vticket = '';

                if (isset($_POST['chkbalance'])) {
                    $vbalance = $_POST['chkbalance'];
                } else {
                    $vbalance = null;
                }

                if (isset($_POST['Withdraw'])) {
                    $vwithdraw = $_POST['Withdraw'];
                } else {
                    $vwithdraw = null;
                }

                $vamount = 0;
                if (isset($_POST['txtamount'])) {
                    $vamount = $_POST['txtamount'];
                }
                if (isset($_POST['txtremarks'])) {
                    $vremarks = trim($_POST['txtremarks']);
                }
                if (isset($_POST['txtticket'])) {
                    $vticket = trim($_POST['txtticket']);
                }

                //to check if the sname of provider matches on the posted data, and redirect to its respective process
                $sRTG = preg_match('/RTG/', $provider);
                if ($sRTG == 0) {
                    $sHAB = preg_match('/Habanero/', $provider);
                    if ($sHAB == 0) {
                        $sEB = preg_match('/e-Bingo/', $provider);
                        if ($sEB == 0) {
                            echo 'Invalid Casino.';
                        } else {
                            echo "No manual redemption for e-Bingo.";
                            $otopup->close();
                            exit;
                        }
                    } else {
                        $_SESSION['site'] = $siteID;
                        $_SESSION['terminal'] = $vterminalID;
                        $_SESSION['login'] = $login;
                        $_SESSION['serverid'] = $serverId;
                        $_SESSION['chkbalance'] = $vbalance;
                        $_SESSION['Withdraw'] = $vwithdraw;
                        $_SESSION['Redeem'] = $isredeem;
                        $_SESSION['txtamount'] = $vamount;
                        $_SESSION['txtremarks'] = $vremarks;
                        $_SESSION['txtticket'] = $vticket;
                        $_SESSION['txtcardnumber'] = '';
                        $redirect = "ProcessHabanero_CAPI.php";
                    }
                }
                //pass session variables to ProcessRTG.php
                else {
                    $_SESSION['site'] = $siteID;
                    $_SESSION['terminal'] = $vterminalID;
                    $_SESSION['login'] = $login;
                    $_SESSION['serverid'] = $serverId;
                    $_SESSION['chkbalance'] = $vbalance;
                    $_SESSION['Withdraw'] = $vwithdraw;
                    $_SESSION['Redeem'] = $isredeem;
                    $_SESSION['txtamount'] = $vamount;
                    $_SESSION['txtremarks'] = $vremarks;
                    $_SESSION['txtticket'] = $vticket;
                    $_SESSION['txtcardnumber'] = '';
                    $redirect = "ProcessRTG_CAPI.php";
                }
                $otopup->close();
                header("Location: $redirect");
                break;

            case "ManualRedemptionUB":
                $siteID = $_POST['cmbsite'];
                $login = $_POST['terminalcode'];
                $serverId = $_POST['cmbservices'];
                $provider = $_POST['txtservices'];
                $vterminalID = $_POST['cmbterminal'];
                $loyaltycardnumber = $_POST['txtcardnumber'];
                $mid = $_POST['txtmid'];
                $isvip = $_POST['txtisvip'];
                $isredeem = 0;
                $vremarks = '';
                $vticket = '';

//              if($isvip == 1){
//                  $login = $login."VIP";
//              }

                if (isset($_POST['chkbalance'])) {
                    $vbalance = $_POST['chkbalance'];
                } else {
                    $vbalance = null;
                }

                if (isset($_POST['Withdraw'])) {
                    $vwithdraw = $_POST['Withdraw'];
                } else {
                    $vwithdraw = null;
                }

                $vamount = 0;
                if (isset($_POST['txtamount'])) {
                    $vamount = $_POST['txtamount'];
                }
                if (isset($_POST['txtremarks'])) {
                    $vremarks = trim($_POST['txtremarks']);
                }
                if (isset($_POST['txtticket'])) {
                    $vticket = trim($_POST['txtticket']);
                }

                //to check if the sname of provider matches on the posted data, and redirect to its
                //respective process
                $sRTG = preg_match('/RTG/', $provider);
                if ($sRTG == 0) {
                    echo 'Invalid Casino.';
                }
                //pass session variables to ProcessRTG.php
                else {
                    $_SESSION['site'] = $siteID;
                    $_SESSION['terminal'] = $vterminalID;
                    $_SESSION['login'] = $login;
                    $_SESSION['serverid'] = $serverId;
                    $_SESSION['chkbalance'] = $vbalance;
                    $_SESSION['Withdraw'] = $vwithdraw;
                    $_SESSION['Redeem'] = $isredeem;
                    $_SESSION['txtamount'] = $vamount;
                    $_SESSION['txtremarks'] = $vremarks;
                    $_SESSION['txtticket'] = $vticket;
                    $_SESSION['txtcardnumber'] = $loyaltycardnumber;
                    $_SESSION['txtmid'] = $mid;
                    $redirect = "ProcessRTG_CAPI.php";
                }
                $otopup->close();
                header("Location: $redirect");
                break;

            case 'InsertReplenishment':

                if ((isset($_POST['cmbsitename']) || isset($_POST['txtposacc'])) && isset($_POST['txtamount']) && isset($_POST['cmbreplenishment'])) {
                    if ($_POST['cmbsitename'] == -1 || $_POST['cmbsitename'] == "-1") {
                        //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                        if (strlen($_POST['txtposacc']) == 10) {
                            $vposaccno = $_POST['txtposacc'];
                        } else {
                            $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                        }
                        $rsite = $otopup->getidbyposacc($vposaccno);
                        //check if pos account is valid
                        if ($rsite) {
                            $vsiteID = $rsite['SiteID'];
                        } else {
                            $msg = "Invalid POS Account Number";
                            $_SESSION['mess'] = $msg;
                            $otopup->close();
                            header("Location: ../topupreplenishment.php");
                        }
                    } else
                        $vsiteID = $_POST['cmbsitename'];

                    //$vsiteID = $_POST['txtsiteID'];
                    //$vdatecredited = $_POST['txtdate'];
                    $vamount = removeComma($_POST['txtamount']);
                    $vreplenishmenttype = $_POST['cmbreplenishment'];
                    if ($_POST['txtrefnum'] != "")
                        $vrefnum = trim($_POST['txtrefnum']);
                    else
                        $vrefnum = NULL;
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;

                    $rreplenishmentID = $otopup->insertreplenishment($vsiteID, $vreplenishmenttype, $vamount, $vrefnum, $vCreatedByAID);
                    if ($rreplenishmentID > 0) {
                        $msg = "Replenishment Platform: Successfully posted";
                        $vtransdetails = "[" . $rreplenishmentID . "] Amount: " . $vamount;
                        //",SiteCode ".$_POST['txtsitecode'].",for date ".$vdate.",amount ".$vamount;
                        $vauditfuncID = 21;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    } else {
                        $msg = "Replenishment Platform: Error on posting";
                    }
                } else {
                    $msg = "Replenishment Platform: Invalid Fields";
                }
                $_SESSION['mess'] = $msg;
                $otopup->close();
                header("Location: ../topupreplenishment.php");
                break;

            case 'TopupViewAccount':
                if (isset($_POST['txtusername']) && isset($_POST['txtpassword'])) {
                    //validate if site dropdown box was selected
                    if ($_POST['cmbsite'] > 0) {
                        $vsiteID = $_POST['cmbsite'];
                    } else {
                        //validate if pos account textfield have value
                        if (strlen($_POST['txtposacc']) > 0) {
                            //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                            if (strlen($_POST['txtposacc']) == 10) {
                                $vposaccno = $_POST['txtposacc'];
                            } else {
                                $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                            }

                            $rsite = $otopup->getidbyposacc($vposaccno);
                            //check if pos account is valid
                            if ($rsite) {
                                $vsiteID = $rsite['SiteID'];
                            } else {
                                $msg = "Invalid POS account number";
                                $_SESSION['mess'] = $msg;
                                $otopup->close();
                                header("Location: ../topupview.php");
                            }
                        }
                    }
                    $vaccname = $_POST['txtusername'];
                    $vaccpass = $_POST['txtpassword'];
                    $rresult = $otopup->checkaccountdetails($vaccname, $vaccpass);
                    $isexist = $rresult['ctracc'];
                    if ($isexist > 0) {
                        $rBCF = $otopup->getallbcf($vsiteID);
                        $_SESSION['site'] = array($vsiteID, $_POST['txtsitecode']);
                        $_SESSION['BCF'] = $rBCF;
                        $_SESSION['mess'] = $msg;

                        //insert into audit trail
                        $vtransdetails = "Update Manual Top-up: Authorized by: " . $vaccname;
                        $vauditfuncID = 50;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        $otopup->close();
                        header("Location: topupupdateparam.php");
                    } else {
                        $msg = "Account Details: Invalid Account";
                        $_SESSION['mess'] = $msg;
                        $otopup->close();
                        header("Location: ../topupview.php?mess=" . $msg);
                    }
                } else {
                    $msg = "Account Details: Invalid Fields";
                    $_SESSION['mess'] = $msg;
                    $otopup->close();
                    header("Location: ../topupview.php?mess=" . $msg);
                }
                break;
        }
    }
    //view details, page request from topupview for update details.php
    elseif (isset($_GET['page']) <> "") {
        $pageloc = $_GET['page'];

        //validate if site dropdown box was selected
        if ($_GET['siteid'] > 0) {
            $vsiteID = $_GET['siteid'];
        } else {
            //validate if pos account textfield have value
            if (strlen($_GET['txtposacc']) > 0) {
                //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                if (strlen($_GET['txtposacc']) == 10) {
                    $vposaccno = $_GET['txtposacc'];
                } else {
                    $vposaccno = str_pad($_GET['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                }

                $rsite = $otopup->getidbyposacc($vposaccno);
                //check if pos account is valid
                if ($rsite) {
                    $vsiteID = $rsite['SiteID'];
                } else {
                    $msg = "Invalid POS account number";
                    $_SESSION['mess'] = $msg;
                    $otopup->close();
                    header("Location: ../topupview.php");
                }
            }
        }
        $_SESSION['siteid'] = $vsiteID;
        $rBCF = array();
        $rBCF = $otopup->getallbcf($vsiteID);
        $_SESSION['BCF'] = $rBCF;
        $otopup->close();
        if ($pageloc == 'Edit') {
            $otopup->close();
            header("Location: ../topupupdateparam.php");
        } else {
            $otopup->close();
            header("Location: ../topupupdatebal.php");
        }
    }
    //change status from pending to verified, when updating inside grid
    elseif (isset($_GET['remittance']) == "UpdateReversal") {
        //get variables
        $vSiteRemittanceID = $_GET['remitid'];
        $vupatedrow = $otopup->updatesiteremittancestatus($vSiteRemittanceID, $aid, $vdate);
        if ($vupatedrow == 0) {
            $msg = " Verification of Deposits: Record unchanged";
        } else {
            $msg = " Verification of Deposits: Success in updating status in site remittance";
            //insert into audit trail
            $vtransdetails = "sitremittanceid = " . $vSiteRemittanceID . " msg-" . $msg;
            $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
        }
        $nopage = 1;
        $otopup->close();
        $_SESSION['mess'] = $msg;
        header("Location: ../reversaldeposit.php");
    } elseif (isset($_GET['remittance2']) == "UpdateVerifiedRemit") {
        //get variables
        $vSiteRemittanceID = $_GET['remitid2'];
        $vstatus = $_GET['remitstat'];
        $vupatedrow = $otopup->updateverifiedsiteremittance($vSiteRemittanceID, $vstatus, $aid, $vdate);
        if ($vupatedrow == 0) {
            $msg = " Update Verified Deposits: Record unchanged";
        } else {
            $msg = " Update Verified Deposits: Success in updating status in site remittance";
            //insert into audit trail
            $vtransdetails = "sitremittanceid = " . $vSiteRemittanceID . " status = " . $vstatus . " msg-" . $msg;
            $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
        }
        $nopage = 1;
        $otopup->close();
        $_SESSION['mess'] = $msg;
        header("Location: ../reversaldeposit2.php");
    }
    //views of reversal of deposit when remittance id is selected
    elseif (isset($_GET['remitpage']) == 'ViewSiteRemit') {
        $vSiteRemittanceID = $_GET['remitid'];
        $rresultreverse = $otopup->viewsiteremittance($vSiteRemittanceID);
        echo json_encode($rresultreverse);
        unset($rresultreverse);
        $otopup->close();
        exit;
    }
    //views of verified of deposit when remittance id is selected
    elseif (isset($_GET['remitpage2']) == 'ViewVerifiedSiteRemit') {
        $vSiteRemittanceID = $_GET['remitid2'];
        $rresultreverse = $otopup->viewverifiedsiteremittance($vSiteRemittanceID);
        echo json_encode($rresultreverse);
        unset($rresultreverse);
        $otopup->close();
        exit;
    }
    //views of all reversal id which populates the combo box
    elseif (isset($_POST['sendRemitID'])) {
        //to post data to terminals combo box
        $vsiteID = $_POST['sendRemitID'];
        $rtopupview = $otopup->getsiteremittanceid($vsiteID);
        echo json_encode($rtopupview);
        unset($rtopupview);
        $otopup->close();
        exit;
    }
    //views of all verified remittances to combo box
    elseif (isset($_POST['sendRemitID2'])) {
        //to post data to terminals combo box
        $vsiteID = $_POST['sendRemitID2'];
        $rtopupview = $otopup->getsiteremittanceid2($vsiteID);
        echo json_encode($rtopupview);
        unset($rtopupview);
        $otopup->close();
        exit;
    }
    //for displaying the site name
    elseif (isset($_POST['cmbsitename'])) {
        //validate if site dropdown box was selected
        if ($_POST['cmbsitename'] > 0) {
            $vsiteID = $_POST['cmbsitename'];
        } else {
            //validate if pos account textfield have value
            if (strlen($_POST['txtposacc']) > 0) {
                //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                if (strlen($_POST['txtposacc']) == 10) {
                    $vposaccno = $_POST['txtposacc'];
                } else {
                    $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                }

                $rsite = $otopup->getidbyposacc($vposaccno);
                //check if pos account is valid
                if ($rsite) {
                    $vsiteID = $rsite['SiteID'];
                } else {
                    echo "Invalid POS account number";
                    $otopup->close();
                    exit;
                }
            }
        }

        $rresult = array();
        $rresult = $otopup->getsitename($vsiteID);
        foreach ($rresult as $row) {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
            $rsitecode = $row['SiteCode'];
        }
        $vsiteName = new stdClass(); // temp solution for 'creating default object from empty value'
        if (count($rresult) > 0) {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->SiteCode = substr($rsitecode, strlen($terminalcode));
            $vsiteName->POSAccNo = $rposaccno;
        } else {
            $vsiteName->SiteName = "";
            $vsiteName->SiteCode = "";
            $vsiteName->POSAccNo = "";
        }
        echo json_encode($vsiteName);
        unset($rresult);
        $otopup->close();
        exit;
    } elseif (isset($_POST['cmbterminal'])) {
        $vterminalID = $_POST['cmbterminal'];
        $rresult = array();
        $rresult = $otopup->getterminalvalues($vterminalID);
        foreach ($rresult as $row) {
            $vterminals->TerminalName = $row['TerminalName'];
            $vterminals->TerminalCode = $row['TerminalCode'];
        }
        echo json_encode($vterminals);
        unset($rresult);
        $otopup->close();
        exit;
    }
    //get transummary and loyalty card number
    elseif (isset($_POST['cmbservices'])) {
        $vterminalID = $_POST['cmbservices'];
        $rresult = array();
        $rresult = $otopup->getTransSummary($vterminalID);
        foreach ($rresult as $row) {
            $vloyaltycard->summaryID = $row['summaryID'];
            $vloyaltycard->loyaltyCard = $row['loyaltyCard'];
        }
        echo json_encode($vloyaltycard);
        unset($rresult);
        $otopup->close();
        exit;
    } else {
//        $rremittanceid = $otopup->getsiteremittanceid();
//        $_SESSION['siteremit'] = $rremittanceid;
        $_SESSION['sites'] = $otopup->getsites(); //session variable to get all site
        $_SESSION['remittype'] = $otopup->getremittancetypes(); //session variable to get all remittance types
    }
} else {
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>
