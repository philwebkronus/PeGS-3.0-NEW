<?php
/*
  * Created By: Edson L. Perez
 * Date Created: July 6, 2011
 * Purpose: Process For cs, unlock accounts(operator), viewing of redemptions
 */

include __DIR__."/../sys/class/CSManagement.class.php";
include __DIR__."/../sys/class/LoyaltyUBWrapper.class.php";
include __DIR__."/../sys/class/PcwsWrapper.class.php";
require_once __DIR__.'/../sys/core/init.php';
require_once __DIR__.'/../sys/class/helper/common.class.php';
include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
else 
{
    $new_sessionid = '';
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$ocs= new CSManagement($_DBConnectionString[0]);
$oas = new CSManagement($_DBConnectionString[0]);
$ocs2 = new CSManagement($_DBConnectionString[4]);
$loyalty= new LoyaltyUBWrapper();
$CasinoGamingCAPI = new CasinoGamingCAPI();
$connected = $ocs->open();
$connected2 = $ocs2->open();

if($connected && $connected2)
{
    $vdate = $ocs->getDate();
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $servername = $_SERVER['HTTP_HOST'];
    /********** SESSION CHECKING **********/   
    $isexist=$ocs->checksession($aid);
    if($isexist == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $ocs->close();
        if($ocs->isAjaxRequest())
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=".$msg);
    }   
    
    $isexistsession =$ocs->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $ocs->close();
        if($ocs->isAjaxRequest())
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=".$msg);
    }
    /********** END SESSION CHECKING **********/ 

    //get all services 
    $rserviceall = array();
    $rserviceall = $ocs->getallservices("ServiceName");
    $_SESSION['serviceall'] = $rserviceall;

    //get all sites
    $sitelist = array();
    $sitelist = $ocs->getallsites();
    $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection

    //for services --> RTG Servers only
    $rservice = array();
    $rservice = $ocs->getallservices("ServiceName");
    $rservices = array();
    foreach($rservice as $row)
    {
        $rserverID = $row['ServiceID'];
        $rservername = $row['ServiceName'];

        if(strstr($rservername, "RTG"))
        {
            $newarr = array('ServiceID' => $rserverID, 'ServiceName' => $rservername);
            array_push($rservices, $newarr);   
        }
    }
    $_SESSION['getservices'] = $rservices; //session variable for RTG Servers selection

    if(!isset($_POST['paginate'])) 
    {
        if(isset ($_POST['page']))
        {
            $vpage = $_POST['page'];
            switch($vpage)
            {
                //page submit from resetpin.php
                case 'ResetPin':
                    if(isset($_POST['cardno']))
                    {
                        if(!empty($_POST['cardno']))
                        {
                            $chkcard = $ocs2->chkcardnumber($_POST['cardno']);
                            if($chkcard['count'] > 0)
                            {
                                if($chkcard['Status'] == 1 || $chkcard['Status'] == 5)
                                {
                                    $rstpin = new PcwsWrapper($Pcws['systemusername'],$Pcws['systemcode']);
                                    $result = $rstpin->resetPin($Pcws[strtolower($vpage)],$_POST['cardno']);
                                    $result_array = json_decode(json_encode($result), TRUE);
                                    if(isset($result_array['System Access Authentication']))
                                    {
                                        if($result['System Access Authentication']['ErrorCode'] == 1)
                                        {
                                            $msg = $result['System Access Authentication']['TransactionMessage'];
                                        }
                                    }   
                                    else
                                    {
                                        if($result['changePin']['ErrorCode'] == 0)
                                        {
                                            $vauditfuncID = 88;
                                            $vtransdetails = "Card Number ".$_POST['cardno'];
                                            $ocs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                                        }
                                        $msg = array();
                                        $msg = $result;
                                        //                                        $msg = $result['changePin']['TransactionMessage'];
                                        //                                        $msg = "New PIN: " . $result['changePin']['NewPIN'];
                                    }    
                                }
                                else
                                {
                                    $msg = "Reset Player PIN : Player Not Active";  
                                }
                            }
                            else
                            {
                                $msg = "Reset Player PIN : Invalid Card Number";  
                            }
                        }
                        else 
                        {
                            $msg = "Reset Player PIN : Card Number Not Set";
                        }
                    }
                    else 
                    {
                        $msg = "Reset Player PIN : Card Number Not Set";
                    }
                    $ocs2->close();
                    echo json_encode($msg);
                    break;

                case 'ManualRedemption':
                    $siteID = $_POST['cmbsite'];
                    $login = $_POST['terminalcode'];
                    $serverId = $_POST['cmbservices'];
                    $provider = $_POST['txtservices'];
                    $vterminalID = $_POST['cmbterminal'];

                    if(isset($_POST['chkbalance']))
                    {
                        $vbalance = $_POST['chkbalance'];
                    }
                    else
                    {
                        $vbalance = null;
                    }
                    
                    $vamount = 0;
                    if(isset($_POST['txtamount']))
                            $vamount = $_POST['txtamount'];
                    //to check if the sname of provider matches on the posted data, and redirect to its respective process
                    $sRTG = preg_match('/RTG/', $provider);
                    if($sRTG == 0)
                    {
                        // Comment Out CCT 02/06/2018 BEGIN
                        //$sMG = preg_match('/MG/', $provider);
                        //if($sMG == 0)
                        //{
                            //$sPT = preg_match('/PT/', $provider);
                            //if($sPT == 0)
                            //{
                        // Comment Out CCT 02/06/2018 END    
                                // COMMENT OUT CCT 11/27/2017 BEGIN
                                //echo 'Invalid Casino.';
                                // COMMENT OUT CCt 11/27/2017 END
                                // ADDED CCT 11/27/2017 BEGIN
                                $sHAB = preg_match('/Habanero/', $provider);
                                if($sHAB == 0)
                                {
                                    // COMMENT OUT CCT 01/22/2018 BEGIN
                                    //echo 'Invalid Casino.';
                                    // COMMENT OUT CCT 01/22/2018 END
                                    // ADDED CCT 01/22/2018 BEGIN
                                    $sEB = preg_match('/e-Bingo/', $provider);
                                    if($sEB == 0)
                                    {
                                        echo 'Invalid Casino.';
                                    } 
                                    else 
                                    {
                                        echo 'No manual redemption for e-Bingo.';
                                        exit;
                                    }
                                    // ADDED CCT 01/22/2018 END
                                } 
                                else 
                                {
                                    $_SESSION['site'] = $siteID;
                                    $_SESSION['terminal'] = $vterminalID;
                                    $_SESSION['serverid'] = $serverId;
                                    $_SESSION['chkbalance'] = $vbalance;
                                    $_SESSION['txtamount'] = $vamount;
                                    $_SESSION['login'] = $login;
                                    $redirect = "ProcessHabanero_CAPI.php";
                                }
                                // ADDED CCT 11/27/2017 END
                        // Comment Out CCT 02/06/2018 BEGIN
                            //} 
                            //else 
                            //{
                            //$_SESSION['site'] = $siteID;
                            //$_SESSION['terminal'] = $vterminalID;
                            //$_SESSION['serverid'] = $serverId;
                            //$_SESSION['chkbalance'] = $vbalance;
                            //$_SESSION['txtamount'] = $vamount;
                            //$_SESSION['login'] = $login;
                            //$redirect = "ProcessPT_CAPI.php";
                            //}
                        //}
                        //else
                        //{
                        //    $_SESSION['site'] = $siteID;
                        //    $_SESSION['terminal'] = $vterminalID;
                        //    $_SESSION['serverid'] = $serverId;
                        //    $_SESSION['chkbalance'] = $vbalance;
                        //    $_SESSION['txtamount'] = $vamount;
                        //    $_SESSION['login'] = $login;
                        //    $redirect = "ProcessMG_CAPI.php";
                        //}
                        // Comment Out CCT 02/06/2018 END
                    }
                    //pass session variables to ProcessRTG.php
                    else
                    {
                        $_SESSION['site'] = $siteID;
                        $_SESSION['terminal'] = $vterminalID;
                        $_SESSION['login'] = $login;
                        $_SESSION['serverid'] = $serverId;
                        $_SESSION['chkbalance'] = $vbalance;
                        //$_SESSION['Withdraw'] = $vwithdraw;
                        $_SESSION['txtamount'] = $vamount;
                        $redirect = "ProcessRTG_CAPI.php";
                    }
                    $ocs->close();
                    header("Location: $redirect");
                    break;

                //display card info
                case "GetLoyaltyCard":
                    $cardnumber = $_POST['txtcardnumber'];
                    $serviceids = '';
                    //validate if card number field was empty
                    if(strlen($cardnumber) > 0) 
                    {
                        $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
                        $obj_result = json_decode($loyaltyResult);
                        $statuscode = $obj_result->CardInfo->StatusCode;

                        if(!is_null($statuscode) ||$statuscode == '')
                        {
                            //allows active membership and temp card
                            if($statuscode == 1 || $statuscode == 5 || $statuscode == 9)
                            {
                                $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

                                //if casino array from getcardinfo APi returns multiple casino
                                if($casinoarray_count != 0)
                                {
                                    for($ctr = 0; $ctr < $casinoarray_count;$ctr++) 
                                    {   
                                        if($ctr > 0)
                                        {
                                            $serviceids .= ', '.$obj_result->CardInfo->CasinoArray[$ctr]->ServiceID;
                                        }
                                        else
                                        {
                                            $serviceids .= $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID;
                                        }
                                    }
                                    $casinoinfo = array(
                                            array(
                                                'UserName'  => $obj_result->CardInfo->MemberName,
                                                'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                                'Email'  => $obj_result->CardInfo->Email,
                                                'Birthdate' => $obj_result->CardInfo->Birthdate,
                                                'Casino' => $serviceids,
                                                'CardNumber' => $obj_result->CardInfo->CardNumber,
                                                'StatusCode' => $obj_result->CardInfo->StatusCode,
                                            ),
                                        );
                                    $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                                    $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                                    echo json_encode($casinoinfo);
                                }
                                else
                                {
                                    // EDITED CCT 02/20/2018 BEGIN
                                    // Revised output when there is no Account-Based record for the player
                                    //$services = "User Based Redemption: Casino is empty";
                                    //echo "$services";
                                    $casinoinfo = array(
                                                    array('UserName' => $obj_result->CardInfo->MemberName,
                                                        'MobileNumber' => $obj_result->CardInfo->MobileNumber,
                                                        'Email' => $obj_result->CardInfo->Email,
                                                        'Birthdate' => $obj_result->CardInfo->Birthdate,
                                                        'Casino' => '',
                                                        'CardNumber' => $obj_result->CardInfo->CardNumber,
                                                        'StatusCode' => $obj_result->CardInfo->StatusCode,
                                                        ),
                                                );
                                    $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                                    $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                                    echo json_encode($casinoinfo);                                      
                                    // EDITED CCT 02/20/2018 END
                                }    
                            } 
                            else 
                            {
                                $statusmsg = $ocs->membershipcardStatus($statuscode);
                                $services = "User Based Redemption: $statusmsg";
                                echo "$services";
                            }
                        }
                        else
                        {
                            $statuscode = 100;
                            $statusmsg = $ocs->membershipcardStatus($statuscode);
                            $services = "User Based Redemption: $statusmsg";
                            echo "$services";
                        }
                    } 
                    else 
                        echo "User Based Redemption: Invalid input detected.";
                    $ocs->close();
                    exit;
                    break;
                    
                //Get Casino Array using membsership card
                case "GetCasino":
                    $casino = $_SESSION['CasinoArray'];   
                    $casinoarray_count = count($casino);

                    $casinos = array();
                    $service = array();
                    if($casinoarray_count != 0)
                    {
                        for($ctr = 0; $ctr < $casinoarray_count;$ctr++) 
                        {
                            $casinos[$ctr] = array(
                                array(
                                    'ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                    'ServicePassword' => $casino[$ctr]->ServicePassword,
                                    'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                    'ServiceID' => $casino[$ctr]->ServiceID,
                                    'UserMode' => $casino[$ctr]->UserMode,
                                    'isVIP' => $casino[$ctr]->isVIP,
                                    'Status' => $casino[$ctr]->Status 
                                )
                            );

                            //loop through array output to get casino array from membership card
                            foreach ($casinos[$ctr] as $value) 
                            {
                                $rserviceuname = $value['ServiceUsername'];
                                $rservicepassword = $value['ServicePassword'];
                                $rserviceid = $value['ServiceID'];
                                $rusermode = $value['UserMode'];
                                $risvip = $value['isVIP'];
                                $hashedpassword = $value['HashedServicePassword'];
                                $rstatus = $value['Status'];

                                //get casino service name
                                $servicename = $ocs->getCasinoName($rserviceid);
                                $servicegrpname = $ocs->getServiceGrpName($rserviceid);

                                foreach ($servicename as $service2) 
                                {
                                    $serviceName = $service2['ServiceName'];
                                    $serviceStatus = $service2['Status'];

                                    //check if status of casino is Active
                                    if($serviceStatus != 1 || $rstatus != 1)
                                    {
                                        $balance = 'User Based Redemption: InActive Casino';

                                        $casino2 = array(
                                            "UserName"  => "$rserviceuname",
                                            "Password"  => "$rservicepassword",
                                            "ServiceName"  => $serviceName,
                                            "ServiceID"  => $rserviceid,    
                                            "UserMode" => "$rusermode",
                                            "IsVIP" => "$risvip",
                                            "Status" => "$rstatus",
                                            "Balance" => "$balance",
                                        );
                                        array_push($service, $casino2); 
                                    }
                                    else
                                    {
                                        switch (true)
                                        {
//                                            case strstr($servicegrpname, "HAB"): //if provider is Habanero, then   ADDED CCT 11/24/2017
//                                                $url = $_ServiceAPI[$rserviceid-1];
//                                                $capiusername = $_HABbrandID;
//                                                $capipassword = $_HABapiKey;
//                                                $capiplayername = '';
//                                                $capiserverID = '';
//                                                $usermode = '';
//                                                $password = '';
//                                                $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $rserviceid, $url, $rserviceuname, 
//                                                $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode, $password); 
//                                                break;                                         
                                            case strstr($servicegrpname, "RTG2"): //if provider is RTG2, then
                                                $url = $_ServiceAPI[$rserviceid-1];
                                                $capiusername = $_CAPIUsername;
                                                $capipassword = $_CAPIPassword;
                                                $capiplayername = $_CAPIPlayerName;
                                                $capiserverID = '';
                                                $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID); 
                                                break;    
                                            case strstr($servicegrpname, "RTG"): //if provider is RTG, then
                                                $url = $_ServiceAPI[$rserviceid-1];
                                                $capiusername = $_CAPIUsername;
                                                $capipassword = $_CAPIPassword;
                                                $capiplayername = $_CAPIPlayerName;
                                                $capiserverID = '';
                                                $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID); 
                                                break;
                                            // Comment Out CCT 02/06/2018 BEGIN
                                            //case strstr($servicegrpname, "MG"): //if provider is MG, then
                                            //    $_MGCredentials = $_ServiceAPI[$rserviceid-1];
                                            //    list($mgurl, $mgserverID) =  $_MGCredentials;
                                            //    $url = $mgurl;
                                            //    $capiusername = $_CAPIUsername;
                                            //    $capipassword = $_CAPIPassword;
                                            //    $capiplayername = $_CAPIPlayerName;
                                            //    $capiserverID = $mgserverID;
                                            //    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID); 
                                            //    break;
                                            //case strstr($servicegrpname, "PT"): //if provider is PT, then
                                            //    $url = $_ServiceAPI[$rserviceid-1];
                                            //    $capiusername = $_ptcasinoname;
                                            //    $capipassword = $_ptsecretkey;
                                            //    $capiplayername = $_CAPIPlayerName;
                                            //    $capiserverID = '';
                                            //    $balance = $CasinoGamingCAPI->getBalance($servicegrpname, $rserviceid, $url, $rserviceuname, $capiusername, $capipassword, $capiplayername, $capiserverID); 
                                            //    break;
                                            // Comment Out CCT 02/06/2018 END
                                            default :
                                                echo "Error: Invalid Casino Provider";
                                                break;
                                            }   

                                        //convert current balance to number format
                                        if(is_string($balance['Balance'])) 
                                        {
                                            $balance = number_format((double)$balance,2, '.', ',');
                                        }  
                                        else 
                                        {
                                            $balance = number_format($balance,2, '.', ',');
                                        }

                                        //convert status code to status name
                                        switch ($serviceStatus)
                                        {
                                            case 1: $serviceStatus = "Active";
                                                break;
                                            case 0: $serviceStatus = "InActive";
                                        }  

                                        $casino2 = array(
                                            "UserName"  => "$rserviceuname",
                                            "Password"  => "$rservicepassword",
                                            "ServiceName"  => $serviceName,
                                            "ServiceID"  => $rserviceid,    
                                            "UserMode" => "$rusermode",
                                            "IsVIP" => "$risvip",
                                            "Status" => "$rstatus",
                                            "Balance" => "$balance",
                                        );
                                        array_push($service, $casino2);  
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        // EDITED CCT 02/20/2018 BEGIN
                        // Revised output when there is no Account-Based record for the player
                        //$service =  "User Based Redemption: Invalid Card Number";
                        $casino2 = array(
                                        "UserName"  => '',
                                        "Password"  => '',
                                        "ServiceName"  => '',
                                        "ServiceID"  => '',    
                                        "UserMode" => '',
                                        "IsVIP" => '',
                                        "Status" => '',
                                        "Balance" => '',
                                    );
                        array_push($service, $casino2);  //
                        // EDITED CCT 02/20/2018 END
                    }

                    echo json_encode($service);
                    unset($casino);
                    unset($casino2);            
                    $ocs->close();
                    exit;
                    break;

                case 'GetPasskey':
                    $vcashierID = $_POST['cmbcashier'];
                    $vresult = $ocs->getcashierpasskey($vcashierID);

                    $arrcashier = array();
                    foreach($vresult as $row)
                    {
                        $arrnew = array("Passkey" => $row['Passkey'], 'DateIssued' => $row['DatePasskeyIssued'], 
                                    'DateExpired' => $row['DatePasskeyExpires'], 'SiteCode' => $_POST['cmbsite'], 
                                    'POS' => $_POST['posaccountno'], 'Email' => $row['Email']);
                        array_push($arrcashier, $arrnew);
                    }
                    echo json_encode($arrcashier);
                    unset($arrcashier);
                    $ocs->close();
                    exit;
                    break;
                    
                case 'PasskeyNotification':
                    $vemail = preg_replace("/[0-9]+$/", "", $_POST['txtemail']);
                    $to = $vemail;               
                    $subject = 'PEGS Station Manager Cashier New Passkey';
                    $message = "
                        <html>
                            <head>
                                <title>$subject</title>
                            </head>
                            <body>
                                <br/><br/>
                                New Passkey: ".$_POST['txtpasskey']."
                                <br/><br/>
                                Issued On: ".$_POST['dateissued']."
                                <br/><br/>
                                Expires On: ".$_POST['dateexpires']."
                                <br/><br/>
                                System generated email on ".date("l dS \of F Y h:i:s A")."
                                <br/><br/>  
                                This email and any attachments are confidential and may also be
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
                            </body>
                        </html>";
                    $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
                    $sentEmail = mail($to, $subject, $message, $headers);   
                    if($sentEmail == 1)
                    {
                        $msg = "Success on sending passkey";
                        //insert into audit trail
                        $vtransdetails = "New Passkey ".$_POST['txtpasskey']." ,Date Issued ".$_POST['dateissued']." ,Date Expires ".$_POST['dateexpires'];
                        $vauditfuncID = 49;
                        $ocs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else
                    {
                        $msg = "Error: Message not send";
                    }
                    $_SESSION['mess'] = $msg;
                    $ocs->close();
                    header("Location: ../csviewpasskey.php");
                    break;
            }
        }
        //this will populate the combobox for terminals (manualredemption.php)
        elseif(isset($_POST['sendSiteID2']))
        {
            $vsiteID = $_POST['sendSiteID2'];
            $rsitecode = $ocs->getsitecode($vsiteID); //get the sitecode first
            $rresult = array();
            $rresult = $ocs->viewterminals($vsiteID);

            $terminals = array();
            foreach($rresult as $row)
            {
                $rterminalID = $row['TerminalID'];
                $rorigcode = $row['TerminalCode'];
                $sitecode = $terminalcode;

                //remove the "icsa-[SiteCode]"
                $rterminalCode = substr($row['TerminalCode'], strlen($rsitecode['SiteCode']));

                //create a new array to populate the combobox
                $newvalue = array("TerminalID" => $rterminalID, "TerminalCode" => $rterminalCode, "TerminalOrigCode" => $rorigcode);
                array_push($terminals, $newvalue);
            }
            echo json_encode($terminals);
            unset($rresult);
            unset($terminals);
            $ocs->close();
            exit;
        }
        //this will get all servers from a particular TerminalID
        elseif(isset($_POST['sendTerminalID'])) 
        {
            $vterminalID = $_POST['sendTerminalID'];
            $rservices = array();
            $rservices = $ocs->viewservices($vterminalID); //get all services
            echo json_encode($rservices);
            unset($rservices);
            $ocs->close();
            exit;
        }
        elseif(isset ($_POST['cmbterminal']))
        {
            $vterminalID = $_POST['cmbterminal'];
            $rresult = array();
            $rresult = $ocs->getterminalvalues($vterminalID);
            foreach($rresult as $row)
            {
                $vterminals->TerminalName = $row['TerminalName'];
                $vterminals->TerminalCode = $row['TerminalCode']; 
            }
            echo json_encode($vterminals);
            unset($rresult);
            $ocs->close();
            exit;
        }
        elseif(isset ($_POST['cmbservices']))
        {
            $vterminalID = $_POST['cmbservices'];
            $rresult = array();
            $rresult = $ocs->getTransSummary($vterminalID);
            foreach($rresult as $row)
            {
                $vloyaltycard->summaryID = $row['summaryID'];
                $vloyaltycard->loyaltyCard = $row['loyaltyCard']; 
            }
            echo json_encode($vloyaltycard);
            unset($rresult);
            $ocs->close();
            exit;
        }
        //for displaying site name on label
        elseif(isset($_POST['cmbsitename']))
        {
            $vsiteID = $_POST['cmbsitename'];
            $rresult = array();
            $rresult = $ocs->getsitename($vsiteID);
            foreach($rresult as $row)
            {
                $rsitename = $row['SiteName'];
                $rposaccno = $row['POS'];
            }

            if(count($rresult) > 0)
            {
                $vsiteName->SiteName = $rsitename;
                $vsiteName->POSAccNo = $rposaccno;
            }
            else
            {
                $vsiteName->SiteName = "";
                $vsiteName->POSAccNo = "";
            }
            echo json_encode($vsiteName);
            unset($rresult);
            $ocs->close();
            exit;
        }
        elseif(isset($_POST['cashiersiteID']))
        {
            $vcashiersiteID = $_POST['cashiersiteID'];
            $vresults = array();
            $vresults = $ocs->getcashierpersite($vcashiersiteID);       
            echo json_encode($vresults);        
            unset($vresults);
            $ocs->close();
            exit;
        }
        elseif(isset ($_GET['cmbterminal']))
        {
            $connected = $ocs->open();
            $vterminalID = $_GET['cmbterminal'];
            $rresult = array();
            $rresult = $ocs->getterminalname($vterminalID);
            $vterminalName->TerminalName = $rresult['TerminalName'];
            echo json_encode($vterminalName);
            unset($rresult);
            $ocs->close();
            exit;
        }
        //this was used in transaction tracking
        else if(isset($_POST['sendSiteID']))
        {
            $connect = $ocs->open();

            $vsiteID = $_POST['sendSiteID'];
            if($vsiteID <> "-1")
            {
                $rsitecode = $ocs->getsitecode($vsiteID); //get the sitecode first
                $vresults = array();
                //get all terminals
                $vresults = $ocs->viewterminals($vsiteID);
                if(count($vresults) > 0)
                {
                    $terminals = array();
                    foreach($vresults as $row)
                    {
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
                }
                else
                {
                    echo "No Terminal Assigned";
                }
                unset($vresults);
            }
            else
            {
                echo "No Terminal Assigned";
            }
            unset($vresults);
            $ocs->close();
            exit;
        }
    }
    else if(isset($_POST['paginate'])) //INCLUSION FROM AS
    {
        $connected = $ocs->open();
        $vpaginate = $_POST['paginate'];
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $direction = $_POST['sord']; // get the direction
        $theProviders = $ocs->getallservicesecitytrack("ServiceName"); //Added on June 13, 2012

        switch($vpaginate)
        {
            //page post for transaction tracking
            case 'ViewSupport':
                if(isset ($_POST['cmbsite']) && isset ($_POST['cmbterminal']) 
                    && isset ($_POST['txtDate1']) && isset ($_POST['txtDate2']) 
                    && isset($_POST['cmbstatus']))
                {
                    $vSiteID = $_POST['cmbsite'];
                    $vTerminalID = $_POST['cmbterminal'];
                    $vdate1 = $_POST['txtDate1'];
                    $vdate2 = $_POST['txtDate2'];
                    $vFrom = $vdate1;
                    $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
                    $vtransstatus = $_POST['cmbstatus'];
                    $vtranstype = $_POST['cmbtranstype'];

                    /** Store status to an array **/
                    $arrstasssstus = array();
                    if($vtransstatus == 1)
                    {
                        $arrstatus = array($vtransstatus, '3');
                    }
                    elseif($vtransstatus == 2)
                    {
                        $arrstatus = array($vtransstatus, '4');
                    }
                    else
                    {
                        $arrstatus = array($vtransstatus);
                    }

                    $rcount = $ocs->counttransactiondetails($vSiteID,$vTerminalID,$arrstatus, $vtranstype, $vFrom,$vTo); 

                    $count = $rcount['count'];

                    if($count > 0 ) 
                    {
                        $total_pages = ceil($count/$limit);
                    } 
                    else 
                    {
                        $total_pages = 0;
                    }
                    if ($page > $total_pages)
                    {
                        $page = $total_pages;
                    }
                    $start = $limit * $page - $limit;
                    $limit = (int)$limit;   
                    $result = $ocs->selecttransactiondetails($vSiteID,$vTerminalID,$arrstatus, $vtranstype, $vFrom,$vTo, $start, $limit);  

                    if(count($result) > 0)
                    {
                        $i = 0;
                        $responce->page = $page;
                        $responce->total = $total_pages;
                        $responce->records = $count;                    
                        foreach($result as $vview)
                        {                     
                            switch( $vview['Status'])
                            {
                                case 0: $vstatus = 'Pending';break;
                                case 1: $vstatus = 'Successful';    break;
                                case 2: $vstatus = 'Failed';break;
                                case 3: $vstatus = 'Timed Out';break;
                                case 4: $vstatus = 'Transaction Approved (Late)'; break;   
                                case 5: $vstatus = 'Transaction Denied (Late)';  break;
                                default: $vstatus = 'All'; break;
                            } 
                            switch($vview['TransactionType'])
                            {
                                case 'D': $vtranstype = 'Deposit';break;
                                case 'W': $vtranstype = 'Withdrawal';break;
                                case 'R': $vtranstype = 'Reload';break;
                                case 'RD': $vtranstype = 'Redeposit';break;
                            }               
                            $responce->rows[$i]['id']=$vview['TransactionDetailsID'];
                            $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'],$vview['TerminalID'],$vtranstype,$vview['ServiceTransactionID'], number_format($vview['Amount'],2),$vview['DateCreated'],$vstatus, $vview['UserName']);
                            $i++;
                        }
                    }
                    else
                    {
                        $i = 0;
                        $responce->page = $page;
                        $responce->total = $total_pages;
                        $responce->records = $count;
                        $msg = "Application Support: No returned result";
                        $responce->msg = $msg;
                    }

                    echo json_encode($responce);
                    unset($result);
                    $ocs->close();
                    exit;
                }
                break;

            //page post for E-city transaction details tracking
            case 'LPTransactionDetails':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
                //$vdate2 = $_POST['txtDate2'];
                $vFrom = $vdate1;
                $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate1)))." ".$cutoff_time;
                $vsummaryID = $_POST['summaryID'];

                //for sorting
                if($_POST['sidx'] != "")
                {
                    $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionReferenceID"; //default sort name for transactiondetails
                }

                $rcount = $ocs->counttransdetails($vSiteID,$vTerminalID, $vFrom, $vTo, $vsummaryID); 
                $count = $rcount['ctrtdetails'];

                if($count > 0 ) 
                {
                    $total_pages = ceil($count/$limit);
                } 
                else 
                {
                    $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                    $page = $total_pages;
                }
                $start = $limit * $page - $limit;
                $start < 0 ? $start = 0 : $start = $start; //Added on June 14, 2012 to Handle negative starting page
                $limit = (int)$limit;   
                $result = $ocs->gettransactiondetails($vSiteID, $vTerminalID, $vFrom, $vTo, $vsummaryID, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;                    
                    foreach($result as $vview)
                    {                     
                        switch( $vview['Status'])
                        {
                            case 0: $vstatus = 'Pending';break;
                            case 1: $vstatus = 'Successful';    break;
                            case 2: $vstatus = 'Failed';break;
                            case 3: $vstatus = 'Timed Out';break;
                            case 4: $vstatus = 'Transaction Approved (Late)'; break;   
                            case 5: $vstatus = 'Transaction Denied (Late)';  break;
                            default: $vstatus = 'All'; break;
                        } 

                        switch($vview['TransactionType'])
                        {
                            case 'D': $vtranstype = 'Deposit';break;
                            case 'W': $vtranstype = 'Withdrawal';break;
                            case 'R': $vtranstype = 'Reload';break;
                            case 'RD': $vtranstype = 'Redeposit';break;
                        }

                        /**
                        *Added as of June 13, 2012
                        * @author Marx Lenin Topico 
                        */
                        if(array_key_exists($vview['ServiceID'], $theProviders)) 
                        {
                            $serviceID = $theProviders[$vview['ServiceID']]["ServiceName"];
                        }
                        $responce->rows[$i]['id']=$vview['TransactionReferenceID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'],$vview['TransactionSummaryID'],$vview['SiteID'], $vview['TerminalID'],$vtranstype,$serviceID, number_format($vview['Amount'],2),$vview['DateCreated'],$vview['Name'], $vstatus);
                        $i++;
                    }
                }
                else
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "E-City Tracking: No returned result";
                    $responce->msg = $msg;
                }

                echo json_encode($responce);
                unset($result);
                $ocs->close();
                exit;
                break;

            //page post for transaction summary
            case 'LPTransactionSummary':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
                //$vdate2 = $_POST['txtDate2'];
                $vFrom = $vdate1;
                $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate1)))." ".$cutoff_time;
                //for sorting
                if($_POST['sidx'] != "")
                {
                    $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionsSummaryID"; //default sort name for transaction summary grid
                }
                $rcount = $ocs->counttranssummary($vSiteID,$vTerminalID, $vFrom, $vTo); 
                $count = $rcount['ctrtsum'];
                if($count > 0 ) 
                {
                    $total_pages = ceil($count/$limit);
                } 
                else 
                {
                    $total_pages = 0;
                }
                if ($page > $total_pages)
                {
                    $page = $total_pages;
                }
                $start = $limit * $page - $limit;
                $start < 0 ? $start = 0 : $start = $start; //Added on June 14, 2012 to Handle negative starting page
                $limit = (int)$limit;   

                $result = $ocs->gettransactionsummary($vSiteID, $vTerminalID, $vFrom, $vTo, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;                    
                    foreach($result as $vview)
                    {        
                        //get terminal number in code
                        $sitecode = $vview['SiteCode']; 
                        unset($vview['SiteCode']);
                        $terminal = preg_split("/$sitecode/", $vview['TerminalCode']);
                        $responce->rows[$i]['id']=$vview['TransactionsSummaryID'];
                        $responce->rows[$i]['cell']=array($vview['POSAccountNo'], 
                        $terminal[1],  
                        number_format($vview['Deposit'], 2), 
                        number_format($vview['Reload'],2), 
                        number_format($vview['Withdrawal'], 2), 
                        $vview['DateStarted'], 
                        $vview['DateEnded'], 
                        $vview['Name']);
                        $i++;
                    }
                }
                else
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "E-City Tracking: No returned result";
                    $responce->msg = $msg;
                }
                echo json_encode($responce);
                unset($result);
                $ocs->close();
                exit;
                break;

            //page post for transaction request logs
            case 'LPTransactionLogs':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
                //$vdate2 = $_POST['txtDate2'];
                $vFrom = $vdate1;
                $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate1)));
                $vsummaryID = $_POST['summaryID'];

                //for sorting
                if($_POST['sidx'] != "")
                {
                    $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionRequestLogLPID"; //default sort name for transaction logs(E-City) grid
                }

                $rcount = $ocs->counttranslogslp($vSiteID,$vTerminalID, $vFrom, $vTo, $vsummaryID); 
                $count = $rcount['ctrlogs'];

                if($count > 0 ) 
                {
                    $total_pages = ceil($count/$limit);
                } 
                else 
                {
                    $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                    $page = $total_pages;
                }
                $start = $limit * $page - $limit;
                $start < 0 ? $start = 0 : $start = $start; //Added on June 14, 2012 to Handle negative starting
                $limit = (int)$limit;   

                $result = $ocs->gettranslogslp($vSiteID, $vTerminalID, $vFrom, $vTo, $vsummaryID, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;                    
                    foreach($result as $vview)
                    {
                        switch( $vview['Status'])
                        {
                            case 0: $vstatus = 'Pending';break;
                            case 1: $vstatus = 'Successful';    break;
                            case 2: $vstatus = 'Failed';break;
                            case 3: $vstatus = 'Fulfilled Approved';break;
                            case 4: $vstatus = 'Fulfilled Denied'; break;                               
                            default: $vstatus = 'All'; break;
                        } 

                        switch($vview['TransactionType'])
                        {
                            case 'D': $vtranstype = 'Deposit Transfer';break;
                            case 'W': $vtranstype = 'Withdrawal Transfer';break;
                            case 'R': $vtranstype = 'Reload';break;
                            case 'RD': $vtranstype = 'Redeposit';break;
                        }

                        /**
                        *Added as of June 13, 2012
                        * @author Marx Lenin Topico 
                        */
                        if(array_key_exists($vview['ServiceID'], $theProviders)) 
                        {
                            $serviceID = $theProviders[$vview['ServiceID']]["ServiceName"];
                        }

                        $vsthistoryID = $vview['ServiceTransferHistoryID'];
                        $responce->rows[$i]['id']=$vview['TransactionRequestLogLPID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'], 
                        $vview['SiteID'], 
                        $vview['TerminalID'],
                        $vtranstype, 
                        $vview['ServiceTransactionID'], 
                        $vview['ServiceStatus'], number_format($vview['Amount'], 2), $serviceID, 
                        $vview['StartDate'], $vview['EndDate'], $vstatus);
                        $i++;
                    }
                }
                else
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "E-City Tracking: No returned result";
                    $responce->msg = $msg;
                }
                echo json_encode($responce);
                unset($result);
                $ocs->close();
                exit;
                break;

            case 'ViewMachineInfo':
                //for sorting
                if($_POST['sidx'] != "")
                {
                    $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "CashierMachineInfoId_PK"; //default sort name for transaction logs(E-City) grid
                }
                $vsiteID = $_POST['siteid'];
                $rcount = $oas->countcashiermachineinfo($vsiteID);
                $count = $rcount['ctrmachine'];
                if($count > 0 ) 
                {
                    $total_pages = ceil($count/$limit);
                } 
                else 
                {
                    $total_pages = 0;
                }
                if ($page > $total_pages)
                {
                    $page = $total_pages;
                }
                $start = $limit * $page - $limit;
                $limit = (int)$limit;   
                $result = $oas->getcashiermachineinfo($start, $limit, $vsiteID);
                if(count($result) > 0)
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;                    
                    foreach($result as $vview)
                    {
                        $cshmacID = $vview['CashierMachineInfoId_PK'];
                        $sitecode = substr($vview['SiteCode'], strlen($terminalcode));
                        $responce->rows[$i]['id']=$cshmacID;
                        $responce->rows[$i]['cell']=array($sitecode,$vview['ComputerName'], $vview['CPU_Id'], 
                        $vview['BIOS_SerialNumber'], $vview['MAC_Address'], 
                        $vview['Motherboard_SerialNumber'], $vview['OS_Id'], $vview['IPAddress'], 
                            "<input type=\"button\" value=\"Disable\" onclick=\"window.location.href='process/ProcessAppSupport.php?cshmacid=$cshmacID'+'&disable='+'DisableTerminal';\"/>");
                        $i++;
                    }
                }
                else
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "Disabling of cashier Terminal: No returned result";
                    $responce->msg = $msg;
                }

                echo json_encode($responce);
                unset($result);
                $oas->close();
                exit;
                break;
        }
    }
    else
    {
        //get all sites
        $_SESSION['sites'] = $ocs->getsites(); //session variable to get all site
    }
    $ocs->close();
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>