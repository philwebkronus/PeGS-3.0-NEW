<?php
/*
* Created by : Lea Tuazon
* Date Created : June 3, 2011
* Modified By: Edson L. Perez
*/

include __DIR__ . "/../sys/class/TerminalManagement.class.php";
include __DIR__ . '/../sys/core/init.php';
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

$oterminal = new TerminalManagement($_DBConnectionString[0]);
$connected = $oterminal->open();
$nopage = 0;
if ($connected) 
{
    /********** SESSION CHECKING **************/
    $isexist = $oterminal->checksession($aid);
    if ($isexist == 0) 
    {
        session_destroy();
        $msg = "Not Connected";
        $oterminal->close();
        if ($oterminal->isAjaxRequest()) 
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }
    $isexistsession = $oterminal->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) 
    {
        session_destroy();
        $msg = "Not Connected";
        $oterminal->close();
        header("Location: login.php?mess=" . $msg);
    }
    /******** END SESSION CHECKING *************/
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $oterminal->getDate();

    //pagination functionality starts here
    if (isset($_POST['paginate'])) 
    {
        $vpagination = $_POST['paginate'];
        switch ($vpagination) 
        {
            case 'TerminalsPage':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $rsiteID = $_POST['cmbsitename'];
                $resultcount = array();
                $resultcount = $oterminal->countterminals($rsiteID);
                $count = $resultcount['count'];

                //this is for computing the limit
                if ($count > 0) 
                {
                    $total_pages = ceil($count / $limit);
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
                $limit = (int) $limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if ($count > 0) 
                {
                    $result = $oterminal->viewterminalspage($rsiteID, $start, $limit);
                } 
                else 
                {
                    $result = 0;
                }
                
                if ($result > 0) 
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    foreach ($result as $vview) 
                    {
                        $rterminalID = $vview['TerminalID'];
                        if ($vview['Status'] == 1) 
                        {
                            $vstatus = "Active";
                        } 
                        else 
                        {
                            $vstatus = "Inactive";
                        }
                        $rsitecode = $oterminal->getsitecode($rsiteID); //get the sitecode first
                        $vcode = substr($vview['TerminalCode'], strlen($rsitecode['SiteCode'])); //removes the "icsa-[SiteCode]"

                        $responce->rows[$i]['id'] = $vview['TerminalName'];
                        $responce->rows[$i]['cell'] = array($vview['TerminalName'], $vcode, $vstatus, "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessTerminalMgmt.php?termid=$rterminalID'+'&page1='+'ViewTerminal';\"/>");
                        $i++;
                    }
                } 
                else 
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "Terminal Management: No returned result";
                    $responce->msg = $msg;
                }
                echo json_encode($responce);
                unset($result);
                $oterminal->close();
                exit;
                break;
                
            case 'ViewServiceAccount':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid

                $resultcount = array();
                $resultcount = $oterminal->countserviceterminals();
                $count = $resultcount['count'];

                if ($count > 0) 
                {
                    $total_pages = ceil($count / $limit);
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
                $limit = (int) $limit;
                $result = $oterminal->viewservicespage($start, $limit);

                if (count($result) > 0) 
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;

                    foreach ($result as $vview) 
                    {
                        $rservtermid = $vview['ServiceTerminalID'];
                        if ($vview['Status'] == 1) 
                        {
                            $vstatus = "Active";
                        } 
                        else 
                        {
                            $vstatus = "Inactive";
                        }
                        $responce->rows[$i]['id'] = $vview['ServiceTerminalID'];
                        $responce->rows[$i]['cell'] = array($vview['ServiceTerminalID'], $vview['ServiceTerminalAccount'], $vview['Username'], $vstatus, "<input type=\"button\" value=\"Change Status\" onclick=\"window.location.href='process/ProcessTerminalMgmt.php?stermid=$rservtermid'+'&updtermpage='+'UpdateSTerminalStatus';\"/>");
                        $i++;
                    }
                } 
                else 
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "Service Terminal Management: No returned result";
                    $responce->msg = $msg;
                }
                echo json_encode($responce);
                unset($result);
                $oterminal->close();
                exit;
                break;
                
            case 'ViewAgentAccount':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid

                $resultcount = array();
                $resultcount = $oterminal->countterminalagents();
                $count = $resultcount['count'];

                if ($count > 0) 
                {
                    $total_pages = ceil($count / $limit);
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
                $limit = (int) $limit;
                $result = $oterminal->viewagentspage($start, $limit);

                if (count($result) > 0) 
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;

                    foreach ($result as $vview) 
                    {
                        $ragentid = $vview['ServiceAgentID'];
                        $responce->rows[$i]['id'] = $vview['ServiceAgentID'];
                        $responce->rows[$i]['cell'] = array($vview['ServiceAgentID'], $vview['Username'], "<input type=\"button\" value=\"Edit Agent\" onclick=\"window.location.href='process/ProcessTerminalMgmt.php?agentid=$ragentid'+'&updagentpage='+'UpdateAgent';\"/>");
                        $i++;
                    }
                } 
                else 
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    $msg = "Service Agent: No returned result";
                    $responce->msg = $msg;
                }
                echo json_encode($responce);
                unset($result);
                $oterminal->close();
                exit;
                break;

            case 'GetTerminalType':
                $vsiteID = $_POST['cmbsitename'];
                $vterminalID = $_POST['cmbterminals'];
                $rresults = $oterminal->viewterminaltype($vterminalID);
                $rterminals = array();
                foreach ($rresults as $row) 
                {
                    $rterminalType = $row['TerminalType'];
                    //store the new created array, to populate into comboboxes
                    $newvalue = array("TerminalType" => $rterminalType);
                    array_push($rterminals, $newvalue);
                }
                echo json_encode($rterminals);
                unset($rterminals);
                unset($rresults);
                $oterminal->close();
                exit;
                break;

            case 'UpdateTerminalType':
                $vterminalID = $_POST['cmbterminals'];
                $oldterminalType = $_POST['oldterminaltype'];
                $terminalType = $_POST['terminaltype'];
                $msg = "";

                if ($terminalType == $oldterminalType) 
                {
                    $msg = 'Update Terminal Classification: Terminal Type did not change';
                } 
                else 
                {
                    $terminalcode = $oterminal->getterminalCode($vterminalID);
                    $izVIP = preg_match('/VIP/', $terminalcode);
                    if ($izVIP) 
                    {
                        $tterminalID2 = $oterminal->getTerminalIDz($terminalcode);
                    } 
                    else 
                    {
                        $terminalcode = $terminalcode . 'VIP';
                        $tterminalID2 = $oterminal->getTerminalIDz($terminalcode);
                    }
                    $count = $oterminal->checkTerminalSessions2($vterminalID, $tterminalID2);

                    //check number of sessions in a certain site
                    if ($count > 0) 
                    {
                        $msg = 'Failed to update terminal classification, There is an existing session for this terminal.';
                    } 
                    else 
                    {
                        $terminaldetails = $oterminal->viewterminals($vterminalID);

                        foreach ($terminaldetails as $row) 
                        {
                            $rterminalcode = $row['TerminalCode'];
                        }
                        
                        if (strpos($rterminalcode, 'VIP') == true) 
                        {
                            $vip = 1;
                            $terminalCode = str_replace('VIP', '', $rterminalcode);
                            $terminalidresult = $oterminal->viewterminalsbyTerminalCode($terminalCode);
                            if (empty($terminalidresult)) 
                            {
                                $vterminalID2 = 0;
                            } 
                            else 
                            {
                                foreach ($terminalidresult as $value) 
                                {
                                    $terminalID2 = $value['TerminalID'];
                                }
                            }
                            $vterminalID2 = $terminalID2;
                        } 
                        else 
                        {
                            $vip = 0;
                            $terminalCode = $rterminalcode . 'vip';
                            $terminalidresult = $oterminal->viewterminalsbyTerminalCode($terminalCode);
                            if (empty($terminalidresult)) 
                            {
                                $terminalID2 = 0;
                            } 
                            else 
                            {
                                foreach ($terminalidresult as $value) 
                                {
                                    $terminalID2 = $value['TerminalID'];
                                }
                            }
                            $vterminalID2 = $terminalID2;
                        }
                        $updateresult = $oterminal->updateterminaltype($terminalType, $vterminalID, $vterminalID2);

                        if ($updateresult > 0) 
                        {
                            $msg = 'Update Terminal Classification: Update Successful';
                            $vtransdetails = "TerminalID " . $vterminalID . " and TerminalID " . $vterminalID2 . " Change Terminal Type to " . $terminalType;
                            $vauditfuncID = 77;
                            $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                        } 
                        else 
                        {
                            $msg = 'Update Terminal Classification: Failed to Update Terminal Type';
                        }
                    }
                }
                echo json_encode($msg);
                unset($rterminals);
                unset($rresults);
                $oterminal->close();
                exit;
                break;
        }
    }

    if (isset($_POST['page'])) 
    {
        $vpage = $_POST['page'];
        switch ($vpage) 
        {
            case 'TerminalCreation':
                //validate if all are isset($_POST[])
                if (isset($_POST['txttermcode']) && isset($_POST['cmbsitename'])) 
                {
                    if (isset($_POST['txttermname'])) 
                    {
                        $vTerminalName = strtoupper($_POST['txttermname']);
                    } 
                    else 
                    {
                        $vTerminalName = strtoupper("Terminal" . $_POST['txttermcode']);
                    }
                    $vTerminalCode = $terminalcode . $_POST['txtcode'] . $_POST['txttermcode'];
                    $vSiteID = $_POST['cmbsitename'];
                    $vDateCreated = $vdate;
                    // session account id
                    $vCreatedByAID = $aid;
                    $vStatus = 1; //create as active terminal
                    //create terminal account
                    $resultid = $oterminal->createterminalaccount($vTerminalName, $vTerminalCode, $vSiteID, $vDateCreated, $vCreatedByAID, $vStatus, 0);
                    if ($resultid > 0) 
                    {
                        //insert into audit trail (regular)
                        $vtransdetails = "Site ID " . $vSiteID . ", TerminalID " . $resultid;
                        $vauditfuncID = 29;
                        $vdateupdated = $vdate;
                        $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);

                        //creation of vip terminals starts here:
                        $vnamevip = strtoupper($vTerminalName . "vip");
                        $vcodevip = strtoupper($vTerminalCode . "vip");
                        $resultvip = $oterminal->createterminalaccount($vnamevip, $vcodevip, $vSiteID, $vDateCreated, $vCreatedByAID, $vStatus, 1);
                        if ($resultvip > 0) 
                        {
                            $msg = "Terminal Creation : successfully created";
                            //insert into audit trail (vip)
                            $vdateupdated = $vdate;
                            $vtransdetailsvip = "Site ID " . $vSiteID . ", TerminalID " . $resultvip;
                            $vauditfuncID = 29;
                            $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetailsvip, $vdateupdated, $vipaddress, $vauditfuncID);

                            //send email alert
                            $vtitle = "Added Terminals";
                            $arraid = array($aid);
                            $raid = $oterminal->getfullname($arraid); //get full name of an account
                            $dateformat = date("Y-m-d h:i:s A", strtotime($vdateupdated)); //formats date on 12 hr cycle AM / PM 
                            $rsites = $oterminal->getsitename($vSiteID);
                            
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
                            $vmessage = "
                                <html>
                                    <head>
                                        <title>$vtitle</title>
                                    </head>
                                    <body>
                                        <br/><br/>
                                        $vtitle
                                        <br/><br/>
                                        Regular : Site ID $vSiteID = $vsitename / $vposaccno
                                        <br/>
                                        &nbsp;&nbsp; Terminal ID = $resultid
                                        <br/><br/>
                                        VIP : Site ID $vSiteID = $vsitename / $vposaccno
                                        <br/>
                                        &nbsp;&nbsp; Terminal ID = $resultvip
                                        <br /><br />
                                        Updated Date : $dateformat
                                        <br/><br/>
                                        Updated By : " . $vupdatedby . "
                                        <br/><br/>                            
                                    </body>
                                </html>";
                            $oterminal->emailalerts($vtitle, $grouppegs, $vmessage);
                            unset($rsites);
                        } 
                        else 
                        {
                            $msg = "Terminal Creation(VIP) : error in creating terminal account";
                        }
                    } 
                    else 
                    {
                        $msg = "Terminal Creation : error in creating terminal account";
                    }
                } 
                else 
                {
                    $msg = "Terminal Creation: Invalid fields.";
                }
                $nopage = 1;
                //redirect to site view page with corresponding popup message
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../terminalcreation.php");
                break;
                
            // update terminal details in terminal table
            case 'TerminalUpdateDetails':
                //validate if all are isset($_POST[])
                if (isset($_POST['terminalID']) && isset($_POST['txttermcode']) && isset($_POST['cmbsitename']) && isset($_POST['oldsiteID'])) 
                {
                    $vTerminalID = $_POST['terminalID'];
                    $vTerminalName = trim($_POST['txttermname']);
                    $vTerminalCode = $terminalcode . $_POST['txtcode'] . $_POST['txttermcode']; //(icsa-)-sitecode-terminalno
                    $vSiteID = $_POST['cmbsitename'];
                    $visVIP = $_POST['optvip'];
                    $voldSiteID = $_POST['oldsiteID']; //need to get old site id
                    $resultid = $oterminal->updateterminalaccount($vTerminalName, $vTerminalCode, $vSiteID, $vTerminalID);

                    if ($resultid > 0) 
                    {
                        $msg = "Terminal Update: Details successfully updated";
                        $arrnewdetails = array($vTerminalName, $vTerminalCode);
                        $vnewdetails = implode(",", $arrnewdetails);
                        $vtransdetails = "old details " . $_POST['txtolddetails'] . "new details " . $vnewdetails;
                        $vauditfuncID = 30;
                        $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    } 
                    else 
                    {
                        $msg = "Terminal Update: Terminal account details unchanged";
                    }
                } 
                else 
                {
                    $msg = "Terminal Update: Invalid fields.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../terminalview.php");
                break;
                
            //update status (Regular and VIP Terminal) in terminal table
            case 'TerminalUpdateStatus':
                if (isset($_POST['txttermcode']) && isset($_POST['optstatus'])) 
                {
                    $vsiteID = $_POST['txtsiteID'];
                    $vterminalcode = $_POST['txttermcode'];
                    //check if vip terminal was chosen
                    if (strstr($vterminalcode, "VIP") == true) 
                    {
                        //then remove vip word
                        $vterminalcode = substr($vterminalcode, 0, strrpos($vterminalcode, "VIP"));
                    }
                    $rterminalID = $oterminal->getterminalID($vterminalcode, $vsiteID);
                    $terminals = array();
                    foreach ($rterminalID as $row) 
                    {
                        $terminalID = $row['TerminalID'];
                        array_push($terminals, $terminalID);
                    }
                    $vStatus = $_POST['optstatus'];
                    $resultid = $oterminal->updateterminalstatus($terminals, $vStatus);
                    if ($resultid > 0) 
                    {
                        $msg = "Terminal Update : Status updated";
                        $vtransdetails = "terminalcode " . $vterminalcode . ",old status " . $_POST['txtoldstat'] . ",new status " . $vStatus;
                        $vauditfuncID = 31;
                        $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else 
                    {
                        if ($resultid == -1)
                            $msg = "Terminal Update : Terminal Status unchanged due to active session.";
                        else
                            $msg = "Terminal Update : Terminal Status unchanged.";
                    }
                }
                else 
                {
                    $msg = "Terminal Update Status: Invalid fields.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../terminalview.php");
                break;
                
            // assigning of services to each terminal
            case 'ServiceAssignment':
                if ((isset($_POST['cmbterminals'])) && (isset($_POST['cmbservices']))) 
                {
                    $vsiteID = $_POST['cmbsitename'];
                    $vTerminalID = $_POST['cmbterminals'];
                    $vlogin = $_POST['txttermcode'];
                    $serverId = $_POST['cmbservices'];
                    $vprovidername = $_POST['txtprovider'];
                    $vservicegrpid = $_POST['txtservicegrp'];

                    $usermode = $oterminal->getServiceUserMode($serverId);
                    $siteclassid = $oterminal->selectsiteclassification($vsiteID);

                    //check if Site is for e-Bingo
                    if ((int) $siteclassid['SiteClassificationID'] == 3) 
                    {
                        //check if casino is e-Bingo
                        if ((int) $usermode != 2) 
                        {
                            $servicename = $oterminal->viewterminalservices(0, $serverId);
                            $msg = "Terminal Service Assignment : Cannot Map " . $servicename[0]['ServiceName'] . " to an e-Bingo site";
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
                            $servicename = $oterminal->viewterminalservices(0, $serverId);
                            $msg = "Terminal Service Assignment : Cannot Map " . $servicename[0]['ServiceName'] . " to a non e-Bingo site";
                        } 
                        else 
                        {
                            $nebingo = true;
                        }
                    }

                    //it depends on the condition if site is e-Bingo, Platinum or Hybrid
                    if ($nebingo) 
                    {
                        $servicegroupname = $oterminal->getServiceGrpNameById($serverId);
                        $vprovidername = $servicegroupname;
                        $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                        $_CasinoGamingPlayerAPIUB = new CasinoGamingCAPIUB();
                        $url = $_ServiceAPI[$serverId - 1];
                        $certpath = RTGCerts_DIR . $serverId . '/cert.pem';
                        $keypath = RTGCerts_DIR . $serverId . '/key.pem';
                        $_RealtimeGamingCashierAPI = new RealtimeGamingCashierAPI($url, $certpath, $keypath, '');
                        $login = $vlogin;
                        $country = 'PH';
                        $casinoID = 1;
                        $fname = 'ICSA';
                        $lname = substr($login, strlen($terminalcode));
                        $email = strtolower($lname) . '@yopmail.com';
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
                        $alias = '';
                        $sex = '';
                        $fax = '';
                        $occupation = '';
                        $capisecretkey = '';
                        $accountID = $aid;
                        $roldsite = $oterminal->chkoldsite($vsiteID);
                        $vgenpwdid = 0;

                        //check if this a existing site and Status is active and use
                        if (isset($roldsite['GeneratedPasswordBatchID']) && $roldsite['GeneratedPasswordBatchID'] > 0) 
                        {
                            $vgenpwdid = $roldsite['GeneratedPasswordBatchID'];
                            $isoldsite = 1;
                        } 
                        else 
                        {
                            $rpwdbatch = $oterminal->chkpwdbatch();
                            $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                            $isoldsite = 0;
                        }

                        if ($vgenpwdid > 0) 
                        {
                            $isassigned = 0;
                            $apisuccess = 0;

                            //check password and encrypted password
                            $vretrievepwd = $oterminal->getgeneratedpassword($vgenpwdid, $vservicegrpid);
                            $vgenpassword = $vretrievepwd['PlainPassword'];
                            $vgenhashed = $vretrievepwd['EncryptedPassword'];

                            $password = $vgenpassword; //casino password
                            switch (true) 
                            {
                                case strstr($vprovidername, "EB"):
                                    $url = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $currency = '';
                                    $hashedPassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';                                    
                                    
                                    if (strstr($vlogin, "VIP") == true) 
                                    {
                                        $isVIP = 1;
                                    } 
                                    else 
                                    {
                                        $isVIP = 0;
                                    }
                                    $login = '';
                                    $password = '';
                                    $vaccountExist = 0;
                                    break;
                                    
                                case strstr($vprovidername, "HAB"):
                                    $url = $_ServiceAPI[$serverId-1];
                                    $capiusername = $_HABbrandID;
                                    $capipassword = $_HABapiKey;
                                    $currency = '';
                                    $hashedPassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';                                    
                                    
                                    if (strstr($vlogin, "VIP") == true) 
                                    {
                                        $isVIP = 1;
                                    } 
                                    else 
                                    {
                                        $isVIP = 0;
                                    }
                                    $servicePwdResult = $oterminal->getTerminalServicePassword($vTerminalID, $serverId);
                                    $login = $vlogin;
                                    $password = $servicePwdResult['ServicePassword'];
                                    if ($password == "")  //No mapping found yet for this Service Provider, use default password
                                    {
                                        $password = $vgenpassword;
                                    }
                                    $vaccountExistCount = $_CasinoGamingPlayerAPI->validateHabCasinoAccount($url, $capiusername, $capipassword, $login, $password);
                                    $vaccountExist = $vaccountExistCount['Count'];
                                    break;
                                    
                                case strstr($vprovidername, "RTG2"):
                                    $url = $_PlayerAPI[$serverId - 1];
                                    $cashierurl = $_ServiceAPI[$serverId - 1];
                                    $hashedpass = strtoupper(sha1($password));
                                    $hashedPassword = $hashedpass;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';
                                    if (strstr($vlogin, "VIP") == true) 
                                    {
                                        $isVIP = 1;
                                    } 
                                    else 
                                    {
                                        $isVIP = 0;
                                    }
                                    break;

                                case strstr($vprovidername, "RTG"):
                                    $url = $_PlayerAPI[$serverId - 1];
                                    $cashierurl = $_ServiceAPI[$serverId - 1];
                                    $hashedpass = sha1($password);
                                    $hashedPassword = $hashedpass;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';
                                    if (strstr($vlogin, "VIP") == true) 
                                    {
                                        $isVIP = 1;
                                    } 
                                    else 
                                    {
                                        $isVIP = 0;
                                    }
                                    $PID = $_RealtimeGamingCashierAPI->GetPIDFromLogin($login);
                                    $vaccountExist = count($PID['GetPIDFromLoginResult']);
                                    break;
                                // COMMENT OUT CCT 07/03/2018 BEGIN
                                /*    
                                case strstr($vprovidername, "v15"):
                                    $hashedpass = sha1($password);
                                    $hashedPassword = $hashedpass;
                                    $aid = 0;
                                    $currency = '';
                                    $capiusername = '';
                                    $capipassword = '';
                                    $capiplayername = '';
                                    $capiserverID = '';
                                    if (strstr($vlogin, "VIP") == true) 
                                    {
                                        $isVIP = 1;
                                    } 
                                    else 
                                    {
                                        $isVIP = 0;
                                    }
                                    break;
                                */
                                // COMMENT OUT CCT 07/03/2018 END
                                default:
                                    echo 'Invalid Casino Name.';
                                    break;
                            }

                            //check if terminalid and serviceID
                            $vctrstatus = $oterminal->checkterminalifexist($vTerminalID, $serverId);

                            //is terminal and service exist
                            if ($vctrstatus) 
                            {
                                $vcurrentpwd = $vctrstatus['ServicePassword'];
                                $vcurrenthashed = $vctrstatus['HashedServicePassword'];
                                $isassigned = 1; //aleady recorded in kronus
                            } 
                            else 
                            {
                                $isassigned = 0; //not assigned in kronus
                            }

                            //call CasinoAPI creation (RTG)
                            if ($usermode == 1) 
                            {
                                $vapiResult = array('IsSucceed' => true);
                            }
                            // CCT EDITED 06/29/2018 BEGIN
                            //if (($usermode == 0) || ($usermode == 4))
                            elseif (($usermode == 0) || ($usermode == 3) || ($usermode == 4))
                            // CCT EDITED 01/24/2018 END
                            {
                                if ($vaccountExist<=0)
                                {
                                    $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, $serverId, $url, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $isVIP, $usermode);
                                    if($vapiResult == NULL) 
                                    { // proceeed if certificate does not match
                                        $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, $serverId, $url, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $isVIP);
                                    }
                                }
                                else
                                {
                                    //bypass trapping for casino account creation proceed to change terminal password
                                    $vapiResult['IsSucceed']=false; 
                                    $vapiResult['ErrorCode']=200;
                                }
                            }
                            elseif ($usermode == 2)
                            {
                                $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, $serverId, $url, $login, $password, $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, $capipassword, $capiplayername, $capiserverID, $isVIP, $usermode);
                            }
                            // CCT EDITED 06/29/2018 END

                            //API returns successfull creation
                            if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) 
                            {
                                $apisuccess = 1;

                                // CCT EDITED 06/29/2018 BEGIN
                                //if ($usermode == 0 || $usermode == 2 || $usermode == 4) 
                                if ($usermode == 0 || $usermode == 2  || $usermode == 3 || $usermode == 4) 
                                // CCT EDITED 01/24/2018 END
                                {
                                    if ($vprovidername == 'RTG2') 
                                    {
                                        if (strstr($vlogin, "VIP") == true) 
                                        {
                                            $pid = $vapiResult['PID'];
                                            $playerClassID = 2;
                                            $_CasinoGamingPlayerAPI->ChangePlayerClassification($vprovidername, $url, $pid, $playerClassID, $userID, $serverId);
                                        }
                                    }
                                }
                            } 
                            else 
                            {
                                //if account does not created in casino's RTG check the errorcode is exists
                                if ($vapiResult['ErrorCode'] == 5 || $vapiResult['ErrorCode'] == 1 || $vapiResult['ErrorCode'] == 3 || 
                                    $vapiResult['ErrorID'] == 5 || $vapiResult['ErrorID'] == 1 || $vapiResult['ErrorID'] == 3 || $vapiResult['ErrorCode'] == 200 ) 
                                {
                                    //if provider is RTG, then
                                    if (strstr($vprovidername, "RTG") == true) 
                                    {
                                        //Call API to get Account Info
//                                      if($usermode == 0) 
//                                      {
//                                          $vapiResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $serverId, $cashierurl, $password, $vprovidername, $usermode);
//                                          if($vapiResult == NULL) 
//                                          { // proceeed if certificate does not match
//                                              $vapiResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $serverId, $cashierurl, $password, $vprovidername);
//                                          }
//                                      }
//                                      if($usermode == 2) 
//                                      {
//                                            $vapiResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $serverId, $cashierurl, $password, $vprovidername, $usermode);
//                                      }
                                        $terminalID = $oterminal->getTerminalIDz($login);
                                        if ($terminalID != false)
                                        {
                                            $vapiResult = $oterminal->getTerminalServicePassword($terminalID, $serverId);
                                            //check if exists in RTG
                                            if (isset($vapiResult['ServicePassword']) && $vapiResult['ServicePassword'] <> null) 
                                            {
                                                $vrtgoldpwd = $vapiResult['ServicePassword'];
                                                //Call API Change Password
                                                if ($usermode == 1) 
                                                {
                                                    $vapiResult = array('IsSucceed' => true);
                                                }
                                                // CCT EDITED 06/29/2018 BEGIN
                                                //if (($usermode == 0) || ($usermode == 4))
                                                elseif (($usermode == 0) || ($usermode == 3) || ($usermode == 4))
                                                // CCT EDITED 01/24/2018 END
                                                {
                                                    $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, $serverId, $url, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                    if($vapiResult == NULL) 
                                                    { // proceeed if certificate does not match
                                                        $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, $serverId, $url, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                                    }
                                                }
                                                elseif ($usermode == 2) 
                                                {
                                                    $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, $serverId, $url, $casinoID, $login, $vrtgoldpwd, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                }
                                                
                                                if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                    $apisuccess = 1;
                                                else
                                                    $apisuccess = 0; 
                                            } 
                                            else
                                            {
                                                // Account is Created in RTG BackEnd but not existing in terminalservices
                                                $vapiResult = array('IsSucceed' => true);
                                                $apisuccess = 1;
                                            }
                                        }   
                                        else
                                        {
                                            $msg = "Terminal Service Assignment : Create Player Full";
                                            $oterminal->close();
                                            $_SESSION['mess'] = $msg;
                                            header("Location: ../serviceassignment.php");
                                            break;
                                        }
                                    }
                                    else if (strstr($vprovidername, "HAB") == true)
                                    {                                    
                                        $vaccountExist = '';

                                        //Call API to verify if account is already existing in Habanero
                                        $vapiResult = $_CasinoGamingPlayerAPI->validateHabCasinoAccount($url, $capiusername, $capipassword, $login, $password);
                                        
                                        //Verify if API Call was successful
                                        if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) 
                                        {
                                            // Check if Password does not match, hence exists returns error
                                            if ($vapiResult['Count'] == 0 && $vapiResult['ErrorCode'] == 2)
                                            {
                                                if (strstr($vapiResult['ErrorMessage'] , "Password does not match") == true)
                                                {
                                                    //Update Password
                                                    $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, $serverId, $url, $casinoID, $login, $password, $password, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode);
                                                }
                                            }
                                        }
                                    }
    
                                    //verify if API for change password (RTG) is successfull
                                    if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                        $apisuccess = 1;
                                    else
                                        $apisuccess = 0;
                                }
                                else 
                                {
                                    $apisuccess = 0;
                                }
                            }
                            
                            //Check if every Casino API Call was successfull
                            if ($apisuccess == 1) 
                            {
                                $isterminalupdated = 0;
                                $resultid = 0;
                                if ($isassigned == 0) 
                                {
                                    $resultid = $oterminal->assignservices($vTerminalID, $serverId, 1, 1, $vgenpassword, $vgenhashed);
                                    if ($resultid > 0)
                                        $msg = "Terminal Service Assignment : Service successfully created";
                                    else
                                        $msg = "Terminal Service Assignment : Error in creating service terminal account";
                                }
                                else 
                                {
                                    $vstatus = 1;
                                    $isterminalupdated = $oterminal->updateterminalservicestatus($vstatus, $vTerminalID, $serverId, $vgenpassword, $vgenhashed);
                                    //check if terminal successfully updated
                                    if ($isterminalupdated > 0)
                                        $msg = "Terminal Service Assignment: Provider was successfully re-assigned";
                                    else
                                        $msg = "Terminal Service Assignment : Error in creating service terminal account";
                                }

                                //update generatedpasswordbatch if site is new and records successfully inserted
                                if (($isterminalupdated > 0 || $resultid > 0) && ($isoldsite == 0)) 
                                {
                                    $updbatchpwd = $oterminal->updateGenPwdBatch($vsiteID, $vgenpwdid);
                                    if (!$updbatchpwd)
                                        $msg = "Terminal Service Assignment:  Records unchanged in generatedpasswordbatch";
                                }

                                $vtransdetails = "terminalcode " . $vlogin . ",casino server " . $vprovidername;
                                $vauditfuncID = 33;
                                $oterminal->logtoaudit($new_sessionid, $accountID, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                            }
                            else
                            {
                                $msg = "API Error: " . $vapiResult['ErrorMessage'];
                            }
                        }
                    }
                }
                else 
                {
                    $msg = "Terminal Service Assignment: Invalid fields.";
                }
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../serviceassignment.php");
                break;
            
            //Update if active or inactive services
            case 'ServiceUpdate':
                if (isset($_POST['txttermid']) && isset($_POST['optstatus']) && isset($_POST['txtserviceid'])) 
                {
                    $vTerminalID = $_POST['txttermid'];
                    $vStatus = $_POST['optstatus'];
                    $vServiceID = $_POST['txtserviceid'];
                    $resultid = $oterminal->updateterminalservicestatus($vStatus, $vTerminalID, $vServiceID);
                    if ($resultid > 0) 
                    {
                        $msg = "Service Terminal Update : Service terminal account was successfully updated";
                        $vtransdetails = "Service Terminal Updated-terminal" . $vTerminalID . " msg-" . $msg;
                        $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
                    } 
                    else 
                    {
                        $msg = "Service Terminal Update : Service terminal account unchanged.";
                    }
                } 
                else 
                {
                    $msg = "ServiceUpdate: Invalid fields.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../terminalservices.php");
                break;

            //create agents
            case 'ServiceAgentCreation':
                if (isset($_POST['txtusername']) && isset($_POST['cmbsitename'])) 
                {
                    $vsiteID = $_POST['cmbsitename'];
                    $vUserName = $_POST['txtusername'];
                    $vPassword = $_POST['txtpassword'];
                    $vStatus = 1;
                    $ragentexist = $oterminal->checkagentexist($vUserName);
                    if ($ragentexist['ctragent'] > 0) 
                    {
                        $msg = "Service Agent: Username already exists";
                    } 
                    else 
                    {
                        $ragentid = $oterminal->createserviceagents($vUserName, $vPassword, $vStatus, $vsiteID);
                        if ($ragentid > 0) 
                        {
                            $msg = "Service Agent : Service agent created";
                            $vtransdetails = "Agent ID " . $ragentid;
                            $vauditfuncID = 35;
                            $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        } 
                        else 
                        {
                            $msg = "Service Agent : Error in creating service agents";
                        }
                    }
                } 
                else 
                {
                    $msg = "ServiceAgentCreation: Invalid fields.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../serviceagentcreation.php");
                break;
                
            //account creation for each serviceterminal
            case 'ServiceTerminalCreation':
                if (isset($_POST['txtusername']) && isset($_POST['cmbagents'])) 
                {
                    $vServiceTerminalAccount = $_POST['txtusername'];
                    $vPassword = $_POST['txtpassword'];
                    $vStatus = 1;
                    $vServiceAgentID = $_POST['cmbagents'];

                    $rsterminalexist = $oterminal->checkocifexist($vServiceTerminalAccount);

                    if ($rsterminalexist['ctroc'] > 0) 
                    {
                        $msg = "Service Terminal Creation : Username exists";
                    } 
                    else 
                    {
                        $rocid = $oterminal->createserviceterminal($vServiceTerminalAccount, $vPassword, $vStatus, $vServiceAgentID);
                        if ($rocid > 0) 
                        {
                            $msg = "Service Terminal Creation : Service terminal created";
                            $vtransdetails = "oc account id " . $rocid;
                            $vauditfuncID = 37;
                            $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        } 
                        else 
                        {
                            $msg = "Service Terminal Creation : Error in creating service terminals";
                        }
                    }
                } 
                else 
                {
                    $msg = "ServiceTerminalCreation: Invalid fields.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../serviceterminalcreation.php");
                break;
                
            //for MG only
            case 'TerminalMapping':
                if (isset($_POST['cmbterminals']) && (isset($_POST['cmbserviceterms']))) 
                {
                    $vTerminalID = $_POST['cmbterminals'];
                    $vServiceTerminalID = $_POST['cmbserviceterms'];
                    $vserviceID = $_POST['txtservice'];
                    $resultid = Array();
                    $resultid = $oterminal->checkterminalifmapped($vTerminalID, $vServiceTerminalID);
                    if ($resultid['count'] == 0) 
                    {
                        $rexist = $oterminal->terminalmapping($vTerminalID, $vServiceTerminalID, $vserviceID);
                        if ($rexist > 0) 
                        {
                            $msg = "Terminal Mapping: Terminal mapped";
                            $vtransdetails = "Terminal code " . $_POST['txttermcode'] . ",oc id " . $vServiceTerminalID;
                            $vauditfuncID = 32;
                            $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        } 
                        else 
                        {
                            $msg = "Terminal Mapping: Error in mapping terminal";
                        }
                    } 
                    else 
                    {
                        $msg = "Terminal Mapping: Terminal already mapped";
                    }
                } 
                else 
                {
                    $msg = "Terminal Mapping: Invalid fields.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../terminalmapping.php");
                break;
                
            case 'TerminalServices':
                $vterminalID = $_POST['cmbterminals'];
                $rserviceName = $oterminal->viewterminalservices($vterminalID, 0);
                echo json_encode($rserviceName);
                unset($rserviceName);
                $oterminal->close();
                exit;
                break;
            
            case 'TerminalViews':
                $vsiteID = $_POST['cmbsitename'];
                $vterminalID = $_POST['cmbterminals'];
                $rsitecode = $oterminal->getsitecode($vsiteID); //get the sitecode first
                $rresults = $oterminal->viewterminals($vterminalID);
                $rterminals = array();
                foreach ($rresults as $row) 
                {
                    $rterminalName = $row['TerminalName'];
                    $rterminalCode = $row['TerminalCode'];
                    $rterminalID = $row['TerminalID'];
                    $rterminalStatus = $row['Status'];

                    //removes the "icsa-[SiteCode]"
                    $rterminalCode = substr($rterminalCode, strlen($rsitecode['SiteCode']));

                    //store the new created array, to populate into comboboxes
                    $newvalue = array("TerminalName" => $rterminalName, "TerminalCode" => $rterminalCode,
                    "Status" => $rterminalStatus, "TerminalID" => $rterminalID);
                    array_push($rterminals, $newvalue);
                }
                echo json_encode($rterminals);
                unset($rterminals);
                unset($rresults);
                $oterminal->close();
                exit;
                break;
                
            case 'ServiceAgentUpdate':
                //update either agent user name or password
                $vusername = $_POST['txtusername'];
                $vpassword = $_POST['txtpassword'];
                $vsiteID = $_POST['cmbsitename'];
                $vagentid = $_POST['agentid'];
                $agentupdate = $oterminal->agentupdate($vusername, $vpassword, $vagentid, $vsiteID);
                if ($agentupdate > 0) 
                {
                    $msg = "ServiceAgentUpdate : Service Agent profile successfully updated.";
                    $arrnewdetails = array($vsiteID, $vusername, $vpassword);
                    $newdetails = implode(",", $arrnewdetails);
                    $vtransdetails = "old details " . $_POST['txtolddetails'] . " ;new details " . $newdetails;
                    $vauditfuncID = 36;
                    $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    unset($arrnewdetails);
                } 
                else 
                {
                    $msg = "ServiceAgentUpdate : Service Agent profile unchanged.";
                }
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../serviceagentview.php");
                break;
                
            case 'ServiceTerminalUpdate':
                //update service terminal status only
                $vserviceterminalid = $_POST['txtstermid'];
                $vserviceterminalstatus = $_POST['optstatus'];
                $serviceterninalupdate = $oterminal->servicetermstatupd($vserviceterminalstatus, $vserviceterminalid);
                if ($serviceterninalupdate > 0) 
                {
                    $vtransdetails = "oc account id " . $vserviceterminalid . ",old status " . $_POST['txtoldstat'] . ",new status " . $vserviceterminalstatus;
                    $vauditfuncID = 38;
                    $oterminal->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    $msg = "ServiceTerminalUpdate : ServiceTerminal status successfully updated.";
                } 
                else 
                {
                    $msg = "ServiceTerminalUpdate : ServiceTerminal status unchanged.";
                }
                $nopage = 1;
                $oterminal->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../serviceterminalview.php");
                break;
                
            case 'ViewServiceAccount':
                $vSTerminalID = $_POST['cmbusername'];
                $rSTStatus = $oterminal->editserviceterminals($vSTerminalID);
                echo json_encode($rSTStatus);
                unset($rSTStatus);
                $oterminal->close();
                exit;
                break;
            
            case 'ViewAgentAccount':
                $vsiteID = $_POST['cmbsitename'];
                $rresult = array();
                $rresult = $oterminal->viewagentbysite($vsiteID);
                if (count($rresult) > 0) 
                {
                    $ragentID = $rresult[0]['ServiceAgentID'];
                    $ragent = $oterminal->editagent($ragentID);
                    echo json_encode($ragent);
                } 
                else 
                {
                    echo "No agent found";
                }
                unset($rresult);
                $oterminal->close();
                exit;
                break;

            case 'PostSiteCode':
                $rsiteID = $_POST['cmbsitename'];
                $rsitecode = $oterminal->getsitecode($rsiteID);
                $isterminalcode = strstr($rsitecode['SiteCode'], $terminalcode);

                //search first if "icsa-" code was found on the sitecode
                if ($isterminalcode == false) 
                {
                    $vcode->sitecode = $rsitecode['SiteCode'];
                } 
                else 
                {
                //removes the "icsa-" code
                    $vcode->sitecode = substr($rsitecode['SiteCode'], strlen($terminalcode));
                }
                echo json_encode($vcode);
                $oterminal->close();
                exit;
                break;

            case 'GetTerminalCode':
                $vterminalID = $_POST['cmbterminals'];
                $vsiteID = $_POST['cmbsitename'];
                $rterminal = $oterminal->viewterminals($vterminalID);
                $rsites = $oterminal->getpasscode($vsiteID);

                foreach ($rterminal as $results) 
                {
                    $vterminalCode->terminalcode = $results['TerminalCode'];
                }
                $vpasscode = $rsites['PassCode'];
                $vterminalCode->passcode = $vpasscode;
                echo json_encode($vterminalCode);
                unset($rterminal);
                $oterminal->close();
                exit;
                break;
                
            case 'DisplayAgents':
                $vsiteID = $_POST['cmbsitename'];
                $rresult = array();
                $rresult = $oterminal->viewagentbysite($vsiteID);
                if (count($rresult) > 0) 
                {
                    echo json_encode($rresult);
                } 
                else 
                {
                    echo "No Agent Found";
                }
                unset($rresult);
                $oterminal->close();
                exit;
                break;
                
            case 'GetServiceGroup':
                $vserviceid = $_POST['serviceid'];
                $rservicegrp = $oterminal->viewterminalservices(0, $vserviceid);
                echo json_encode($rservicegrp);
                $oterminal->close();
                exit;
                break;
            
            default:
                if ($nopage == 0) 
                {
                    $oterminal->close();
                }
        }
    }
    //page request from terminalview.php
    elseif (isset($_GET['page1']) == 'ViewTerminal') 
    {
        $vterminalID = $_GET['termid'];
        $rterminaldetails = $oterminal->viewterminals($vterminalID);
        $vterminalupdate = array();
        foreach ($rterminaldetails as $val) 
        {
            $rterminalID = $val['TerminalID'];
            $rname = $val['TerminalName'];
            $rterminalCode = $val['TerminalCode'];
            $rsiteID = $val['SiteID'];

            $rsitecode = $oterminal->getsitecode($rsiteID); //get the sitecode first
            $rterminalCode = substr($rterminalCode, strlen($rsitecode['SiteCode'])); //remove the "icsa-[SiteCode]"

            $arrnew = array('TerminalID' => $rterminalID, 'TerminalName' => $rname, "TerminalCode" => $rterminalCode, 'SiteID' => $rsiteID);
            array_push($vterminalupdate, $arrnew);
        }

        $_SESSION['updterms'] = $vterminalupdate;
        unset($vterminalupdate);
        unset($arrnew);
        unset($rterminaldetails);
        $oterminal->close();
        header("Location: ../terminaledit.php");
    }
    //page request from accountedit.php
    elseif (isset($_GET['terminalstatus']) == 'TerminalUpdate') 
    {
        $vterminalID = $_GET['termid'];
        $rterminaldetails = $oterminal->viewterminals($vterminalID);
        $_SESSION['updterms'] = $rterminaldetails;
        $oterminal->close();
        header("Location: ../terminalstatusupdate.php");
    }
    //page request from accountedit.php
    elseif (isset($_GET['servicepage']) == 'ServiceUpdate') 
    {
        $vterminalID = $_GET['termid'];
        $_SESSION['termid'] = $vterminalID;
        $vserviceID = $_GET['service'];
        $_SESSION['serviceid'] = $vserviceID;
        $rterminalstatus = $oterminal->viewterminalservices($vterminalID, $vserviceID);
        $_SESSION['updstatus'] = $rterminalstatus;
        $oterminal->close();
        header("Location: ../servicestatusupdate.php");
    }
    //page request from serviceterminalview.php
    elseif (isset($_GET['updtermpage'])) 
    {
        //to post status to serviceterminaledit.php
        $vSTerminalID = $_GET['stermid'];
        $rSTStatus = $oterminal->editserviceterminals($vSTerminalID);
        $_SESSION['ststatus'] = $rSTStatus;
        $_SESSION['stermid'] = $vSTerminalID;
        $oterminal->close();
        header("Location: ../serviceterminaledit.php");
    }
    //page request from serviceagentview.php
    elseif (isset($_GET['updagentpage'])) 
    {
        //to post info to serviceagentedit.php
        $ragentID = $_GET['agentid'];
        $rresult = $oterminal->editagent($ragentID);
        $_SESSION['viewagent'] = $rresult;
        $_SESSION['agentid'] = $ragentID;
        $oterminal->close();
        header("Location: ../serviceagentedit.php");
    } 
    elseif (isset($_POST['sendSiteID1'])) 
    {
        //to post data to terminals combo box (serviceassignment.php)
        $vsiteID = $_POST['sendSiteID1'];
        $rsitecode = $oterminal->getsitecode($vsiteID); //get the sitecode first
        $rresult = array();
        $rresult = $oterminal->selectterminals($vsiteID);

        $terminals = array();
        foreach ($rresult as $row) 
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
        unset($rresult);
        unset($terminals);
        $oterminal->close();
        exit;
    }
    elseif (isset($_POST['cmbterminal'])) 
    {
        $vterminalID = $_POST['cmbterminal'];
        $rresult = array();
        $rresult = $oterminal->getterminalname($vterminalID);
        if (count($rresult) > 0) 
        {
            $vterminalName->TerminalName = $rresult['TerminalName'];
        } 
        else 
        {
            $vterminalName->TerminalName = "";
        }
        echo json_encode($vterminalName);
        unset($rresult);
        $oterminal->close();
        exit;
    } 
    elseif (isset($_POST['cmbsitename'])) 
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $oterminal->getsitename($vsiteID);

        foreach ($rresult as $row) 
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
        }
        
        if (count($rresult) > 0) 
        {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->POSAccNo = $rposaccno;
        } 
        else 
        {
            $vsiteName->SiteName = "";
            $vsiteName->POSAccNo = "";
        }

        /* For displaying of terminal code */
        $rsitecode = $oterminal->getsitecode($vsiteID); //get the sitecode first
        //$rterminalCode = $oterminal->getlastID($vsiteID);  //generate the last terminal ID
        $rterminalCode = $oterminal->getlastID($vsiteID, $rsitecode);  //modified by lbt
        //search first if the sitecode was found in the terminal code
        if (strstr($rterminalCode['tc'], $rsitecode['SiteCode']) == false) 
        {
            //remove all the letters from terminal code
            $rnoterminal = ereg_replace("[^0-9]", "", $rterminalCode['tc']); //remove all letters from this terminalcode
        } 
        else 
        {
            //remove the "icsa-[SiteCode]"
            $rnoterminal = substr($rterminalCode['tc'], strlen($rsitecode['SiteCode']));
        }

        $ctrterminal = (int) $rnoterminal + 1; //add + 1

        if ($ctrterminal < 10) 
        {
            $vsiteName->TerminalID = str_pad($ctrterminal, 2, "0", STR_PAD_LEFT); //if terminal no. is less than 10 pad to 0
        } 
        else 
        {
            $vsiteName->TerminalID = $ctrterminal;
        }
        echo json_encode($vsiteName);
        unset($rresult);
        $oterminal->close();
        exit;
    } 
    else 
    {
        //for viewing of terminals --> terminalview.php
        $rterminals = array();
        $rterminals = $oterminal->getallterminals();
        $_SESSION['terminals'] = $rterminals;
        unset($rterminals);
        //for services --> RTG
        $rserviceAll = array();
        $rserviceAll = $oterminal->getallservices();
        $_SESSION['getservices'] = $rserviceAll;
        unset($rserviceAll);
            
        //for service agents --> views/serviceagentview.php;
        $ragents = array();
        $ragents = $oterminal->viewterminalagents();
        $_SESSION['agents'] = $ragents;
        unset($ragents);
        
        //for service terminals --> views/serviceterminalview.php
        $rserviceTerminals = array();
        $rserviceTerminals = $oterminal->viewserviceterminals();
        $_SESSION['serviceterminals'] = $rserviceTerminals;
        unset($rserviceTerminals);
        
        //for terminal mapping, show only the unassigned oc accounts
        $ocaccounts = array();
        $ocaccounts = $oterminal->octerminalassigned();
        $octerminals = array();
        foreach ($ocaccounts as $row) 
        {
            if ($row['TerminalID'] == "") 
            {
                $rserviceterminal = $row['ServiceTerminalAccount'];
                $rserviceterminalID = $row['ServiceTerminalID'];
                $ocunassigned = array('ServiceTerminalID' => $rserviceterminalID, 'ServiceTerminalAccount' => $rserviceterminal);
                array_push($octerminals, $ocunassigned);
            }
        }
        $_SESSION['assignedoc'] = $octerminals;
        //for site listing, every terminals
        $_SESSION['siteids'] = $oterminal->getallsiteswithid();
        unset($ocaccounts);
    }
} 
else 
{
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>