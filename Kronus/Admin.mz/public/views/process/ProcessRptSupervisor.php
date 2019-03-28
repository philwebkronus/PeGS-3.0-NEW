<?php
/*
* Created By: Edson L. Perez
* Date Created: October 13, 2011
*/
include __DIR__."/../sys/class/RptSupervisor.class.php";
require __DIR__.'/../sys/core/init.php';
require_once __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

function removeComma($money) 
{
    return str_replace(',', '', $money);
}

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

$orptsup = new RptSupervisor($_DBConnectionString[0]);
$connected = $orptsup->open();
$nopage = 0;

if($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $orptsup->getDate();
    /***** Session Checking *****/    
    $isexist=$orptsup->checksession($aid);
    if($isexist == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $orptsup->close();
        if($orptsup->isAjaxRequest())
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$orptsup->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $orptsup->close();
        header("Location: login.php?mess=".$msg);
    }
    /***** End Session Checking *****/   

    //checks if account was locked 
    $islocked = $orptsup->chkLoginAttempts($aid);
    if(isset($islocked['LoginAttempts']))
    {
        $loginattempts = $islocked['LoginAttempts'];
        if($loginattempts >= 3)
        {
            $orptsup->deletesession($aid);
            session_destroy();
            $msg = "Not Connected";
            $orptsup->close();
            header("Location: login.php?mess=".$msg);
            exit;
        }
    }

    $vcutofftime = $cutoff_time; //cutoff time set for report (web.config.php)
    //for JQGRID pagination
    if(isset ($_POST['paginate']))
    {
        $vpage = $_POST['paginate'];
        switch($vpage)
        {
            //for grid display only
            case 'GrossHold':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                $vdatefrom = $_POST['strDate'];
                //                $vdateto = $_POST['endDate'];
                //                $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdateto)))." ".$vcutofftime;
                $dateTo = date('Y-m-d',strtotime(date("Y-m-d", strtotime($vdatefrom)) .$gaddeddate))." ".$vcutofftime; 
                $dateFrom = $vdatefrom." ".$vcutofftime;
                $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
                $vsiteID = $rsiteID['SiteID'];

                //$rsitecashier = $orptsup->getsitecashier($vsiteID);
                $result = $orptsup->viewgrosshold($dateFrom, $dateTo, $vsiteID);
                //*************************** OLD COMPUTATION (V1)***************************************
                if (strtotime($vdatefrom) < strtotime($deploymentDate)) 
                {
                    $ctrtst = 0;
                    if(count($result) > 0)
                    {
                        $supdetails = array();
                        foreach ($result as $value) 
                        {                     
                            if(isset($supdetails[$value['CreatedByAID']]))
                            { //if a record for cashier already set
                                $supdetails[$value['CreatedByAID']]['Deposits'] += (float)$value['Deposits']; //SUM(D.tdtl) + SUM(D.ewt)
                                $supdetails[$value['CreatedByAID']]['Reloads'] += (float)$value['Reloads'];
                                $supdetails[$value['CreatedByAID']]['Redemptions'] += (float)$value['Redemptions'];
                                $supdetails[$value['CreatedByAID']]['LoadCash'] += (float)$value['LoadCash'];
                                $supdetails[$value['CreatedByAID']]['EncashedTickets'] += (float)$value['EncashedTickets'];
                                $supdetails[$value['CreatedByAID']]['RedemptionCashier'] += (float)$value['RedemptionCashier'];
                                $supdetails[$value['CreatedByAID']]['RedemptionGenesis'] += (float)$value['RedemptionGenesis'];
                                $supdetails[$value['CreatedByAID']]['EwalletRedemption'] += (float)$value['EwalletRedemption'];
                                $supdetails[$value['CreatedByAID']]['EwalletDeposits'] += (float)$value['EwalletDeposits'];
                            }
                            else
                            {
                                $supdetails[$value['CreatedByAID']] = array('CreatedByAID'=>$value['CreatedByAID'],
                                'Name'=>$value['Name'],
                                'Deposits'=>$value['Deposits'],
                                'Reloads'=>$value['Reloads'],
                                'Redemptions'=>$value['Redemptions'],
                                'LoadCash'=>$value['LoadCash'],
                                'EncashedTickets'=>$value['EncashedTickets'],
                                'RedemptionCashier'=>$value['RedemptionCashier'],
                                'RedemptionGenesis'=>$value['RedemptionGenesis'],
                                'EwalletRedemption'=>$value['EwalletRedemption'], 
                                'EwalletDeposits'=>$value['EwalletDeposits']); 
                            }
                            $ctrtst++;
                        }

                        $count = count($supdetails);
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
                            $start = $limit * $page - $limit;           
                        }
                        
                        if($page == 0)
                        {
                            $start = 0;
                        }
                        else
                        {
                            $start = $limit * $page - $limit;   
                        }

                        $limit = (int)$limit;
                        $trans_details = $orptsup->paginatetransaction($supdetails, $start, $limit);

                        $arrdepositamt = array();
                        $arrreloadamt = array();
                        $arrwithdrawamt = array();
                        $arrgrossholdamt = array();

                        $i = 0;
                        $response = new stdClass();
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;        

                        foreach($trans_details as $vview)
                        {
                            $vAID = $vview['CreatedByAID'];
                            $depositamt = (float)$vview['Deposits'] + (float)$vview['EwalletDeposits'];
                            $reloadamt = (float)$vview['Reloads'];
                            $withdrawamt = (float)$vview['Redemptions'] + (float)$vview['RedemptionGenesis'] + $vview['EwalletRedemption'];
                            $loadcash = (float)$vview['LoadCash'];
                            $encashtickets = (float)$vview['EncashedTickets'];
                            $cashierredemption = (float)$vview['RedemptionCashier'];
                            $ewalletredemption = (float)$vview['EwalletRedemption'];
                            $grossholdamt = $depositamt + $reloadamt - $withdrawamt;
                            //$cashonhand = (($loadcash - $cashierredemption) - $ewalletredemption) - $encashtickets;
                            $cashonhand = (($depositamt + $reloadamt) - $cashierredemption - $ewalletredemption) - $encashtickets;
                            $response->rows[$i]['id']= $vAID;
                            $response->rows[$i]['cell'] = array($vview['Name'], number_format($depositamt, 2), 
                            number_format($reloadamt, 2), number_format($withdrawamt,2), 
                            number_format($cashonhand, 2));
                            $i++;
                            //store the 3 transaction types in an array
                            array_push($arrdepositamt, $depositamt);
                            array_push($arrreloadamt, $reloadamt);
                            array_push($arrwithdrawamt, $withdrawamt);
                            array_push($arrgrossholdamt, $cashonhand);
                        }

                        // Get the sum of all  transaction types
                        $totaldeposit = array_sum($arrdepositamt); 
                        $totalreload = array_sum($arrreloadamt); 
                        $totalwithdraw = array_sum($arrwithdrawamt);
                        $cashonhand = array_sum($arrgrossholdamt);

                        unset($arrdepositamt, $arrreloadamt, $arrwithdrawamt, $arrgrossholdamt, $trans_details);

                        //session variable to store transaction types in an array; to used on ajax call later on this program
                        $_SESSION['total'] = array("TotalDeposit" => $totaldeposit, 
                            "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw, "CashOnHand" => $cashonhand);
                    }
                    else
                    {
                        $i = 0;
                        $response = new stdClass();
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "Gross Hold: No Results Found";
                        $response->msg = $msg;
                    }
                    echo json_encode($response);
                    $orptsup->close();
                    exit;
                }
                //*************************** NEW COMPUTATION (V2)***************************************
                else 
                {
                    $ctrtst = 0;
                    if(count($result) > 0)
                    {
                        $supdetails = array();
                        foreach ($result as $value) 
                        {                     
                            if(isset($supdetails[$value['CreatedByAID']]))
                            { //if a record for cashier already set 
                                $supdetails[$value['CreatedByAID']]['GenesisDeposits'] += (float)$value['GenesisDeposits'];
                                $supdetails[$value['CreatedByAID']]['GenesisReloads'] += (float)$value['GenesisReloads'];
                                $supdetails[$value['CreatedByAID']]['Deposits'] += (float)$value['Deposits']; //SUM(D.tdtl) + SUM(D.ewt)
                                $supdetails[$value['CreatedByAID']]['Reloads'] += (float)$value['Reloads'];
                                $supdetails[$value['CreatedByAID']]['Redemptions'] += (float)$value['Redemptions'];
                                $supdetails[$value['CreatedByAID']]['LoadCash'] += (float)$value['LoadCash'];
                                $supdetails[$value['CreatedByAID']]['EncashedTicketsV2'] += (float)$value['EncashedTicketsV2'];
                                $supdetails[$value['CreatedByAID']]['RedemptionCashier'] += (float)$value['RedemptionCashier'];
                                $supdetails[$value['CreatedByAID']]['RedemptionGenesis'] += (float)$value['RedemptionGenesis'];
                                $supdetails[$value['CreatedByAID']]['EwalletRedemption'] += (float)$value['EwalletRedemption'];
                                $supdetails[$value['CreatedByAID']]['EwalletDeposits'] += (float)$value['EwalletDeposits'];
                            }
                            else
                            {
                                $supdetails[$value['CreatedByAID']] = array('CreatedByAID'=>$value['CreatedByAID'], 
                                'GenesisDeposits' => $value['GenesisDeposits'], 
                                'GenesisReloads' => $value['GenesisReloads'], 
                                'Name'=>$value['Name'],
                                'Deposits'=>$value['Deposits'],
                                'Reloads'=>$value['Reloads'],
                                'Redemptions'=>$value['Redemptions'],
                                'LoadCash'=>$value['LoadCash'],
                                'EncashedTicketsV2'=>$value['EncashedTicketsV2'],
                                'RedemptionCashier'=>$value['RedemptionCashier'],
                                'RedemptionGenesis'=>$value['RedemptionGenesis'], 
                                'EwalletRedemption'=>$value['EwalletRedemption'], 
                                'EwalletDeposits'=>$value['EwalletDeposits']); 
                            }
                            $ctrtst++;
                        }

                        $count = count($supdetails);
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
                            $start = $limit * $page - $limit;           
                        }
                        
                        if($page == 0)
                        {
                            $start = 0;
                        }
                        else
                        {
                            $start = $limit * $page - $limit;   
                        }

                        $limit = (int)$limit;
                        $trans_details = $orptsup->paginatetransaction($supdetails, $start, $limit);

                        $arrdepositamt = array();
                        $arrreloadamt = array();
                        $arrwithdrawamt = array();
                        $arrgrossholdamt = array();

                        $i = 0;
                        $response = new stdClass();
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;        

                        foreach($trans_details as $vview)
                        {
                            $vAID = $vview['CreatedByAID'];
                            $depositamt = (float)$vview['Deposits'] + (float)$vview['EwalletDeposits'];
                            $reloadamt = (float)$vview['Reloads'];
                            $withdrawamt = (float)$vview['RedemptionCashier'] + (float)$vview['RedemptionGenesis'] + $vview['EwalletRedemption'] + (float)$vview['EncashedTicketsV2'] ;
                            $loadcash = (float)$vview['LoadCash'];
                            $encashtickets = (float)$vview['EncashedTicketsV2'];
                            $cashierredemption = (float)$vview['RedemptionCashier'];
                            $genredemption = (float)$vview['RedemptionGenesis'];
                            $ewalletredemption = (float)$vview['EwalletRedemption'];
                            $genesisticketloads = (float)$vview['GenesisDeposits'] + (float)$vview['GenesisReloads'];
                            $grossholdamt = $depositamt + $reloadamt - $withdrawamt;
                            //$cashonhand = (($loadcash - $cashierredemption) - $ewalletredemption) - $encashtickets;
                            $cashonhand = ($depositamt + $reloadamt) - ($withdrawamt);
                            $response->rows[$i]['id']= $vAID;
                            $response->rows[$i]['cell'] = array($vview['Name'], number_format($depositamt, 2), 
                            number_format($reloadamt, 2), number_format($withdrawamt,2), 
                            number_format($cashonhand, 2));
                            $i++;
                            //store the 3 transaction types in an array
                            array_push($arrdepositamt, $depositamt);
                            array_push($arrreloadamt, $reloadamt);
                            array_push($arrwithdrawamt, $withdrawamt);
                            array_push($arrgrossholdamt, $cashonhand);
                        }

                        // Get the sum of all  transaction types
                        $totaldeposit = array_sum($arrdepositamt); 
                        $totalreload = array_sum($arrreloadamt); 
                        $totalwithdraw = array_sum($arrwithdrawamt);
                        $cashonhand = array_sum($arrgrossholdamt);

                        unset($arrdepositamt, $arrreloadamt, $arrwithdrawamt, $arrgrossholdamt, $trans_details);

                        //session variable to store transaction types in an array; to used on ajax call later on this program
                        $_SESSION['total'] = array("TotalDeposit" => $totaldeposit, 
                            "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw, "CashOnHand" => $cashonhand);
                    }
                    else
                    {
                        $i = 0;
                        $response = new stdClass();
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "Gross Hold: No Results Found";
                        $response->msg = $msg;
                    }
                    echo json_encode($response);
                    $orptsup->close();
                    exit;
                }
                break;
        }
    }
    //Get totals per page, grand total
    elseif(isset($_POST['gettotal']) == "GetTotals")
    {
        $vdatefrom = $_POST['strDate'];
        //**************************** OLD COMPUTATION (V1)***************************************
        if ($vdatefrom < $deploymentDate) 
        {
            $dateTo = date('Y-m-d',strtotime(date("Y-m-d", strtotime($vdatefrom)) .$gaddeddate))." ".$vcutofftime; 
            $dateFrom = $vdatefrom." ".$vcutofftime;
            $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
            $vsiteID = $rsiteID['SiteID'];

            //$rsitecashier = $orptsup->getsitecashier($vsiteID);
            $result = $orptsup->viewgrosshold($dateFrom, $dateTo,  $vsiteID, $start=null, $limit=null);

            //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
            $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);

            if(isset($_SESSION['total']))
            {
                $arrtotal = $_SESSION['total'];
            }
            
            if(count($result) > 0)
            {
                $ctr1 = 0;
                $ctr2 = 0;
                $loadcash = '0.00';
                $loadticket = '0.00';
                $loadcoupon = '0.00';
                $printedtickets = '0.00';
                $encashedtickets = '0.00';
                $redemptioncashier = '0.00';
                $manualredemption = 0.00;
                $bancnet = '0.00';
                $ewalletwithdrawal  = '0.00';
                $ewalletgenwithdrawal = '0.00';
                $ewalletbancnet = '0.00';
                $ewalletloadcoupon = '0.00';
                $ewalletloadcash = '0.00';
                $ewalletloadticket = '0.00';
                //Sum up the total redemption made by the cashier
                //                 if(count($result) > 0){
                //                    while($ctr1 < count($result)) {
                //                         if($result[$ctr1]['RedemptionCashier'] != '0.00')
                //                             $redemptioncashier += $result[$ctr1]['RedemptionCashier'];
                //                         $ctr1++;
                //                     }
                //                }
                if(count($result2) > 0)
                {
                    while($ctr2 < count($result2))
                    {
                        if($result2[$ctr2]['LoadCash'] != '0.00')
                            $loadcash = $result2[$ctr2]['LoadCash'];
                        if($result2[$ctr2]['LoadTicket'] != '0.00')
                            $loadticket = $result2[$ctr2]['LoadTicket'];
                        if($result2[$ctr2]['LoadCoupon'] != '0.00')
                            $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                        if($result2[$ctr2]['ewalletLoadCoupon'] != '0.00')
                            $ewalletloadcoupon = $result2[$ctr2]['ewalletLoadCoupon'];
                        if($result2[$ctr2]['PrintedTickets'] != '0.00')
                            $printedtickets = $result2[$ctr2]['PrintedTickets'];
                        if($result2[$ctr2]['EncashedTicketsV2'] != '0.00')
                            $encashedtickets = $result2[$ctr2]['EncashedTicketsV2'];
                        if($result2[$ctr2]['ManualRedemption'] != '0.00')
                            $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                        if($result2[$ctr2]['Bancnet'] != '0.00')
                            $bancnet = (float)$result2[$ctr2]['Bancnet'];
                        if($result2[$ctr2]['ewalletBancnet'] != '0.00')
                            $ewalletbancnet = (float)$result2[$ctr2]['ewalletBancnet'];
                        if($result2[$ctr2]['ewalletLoadCash'] != '0.00')
                            $ewalletloadcash = $result2[$ctr2]['ewalletLoadCash'];
                        if($result2[$ctr2]['EwalletWithdrawal'] != '0.00')
                            $ewalletwithdrawal = (float)$result2[$ctr2]['EwalletWithdrawal'];
                        if($result2[$ctr2]['EwalletGenWithdrawal'] != '0.00')
                            $ewalletgenwithdrawal = (float)$result2[$ctr2]['EwalletGenWithdrawal'];
                        if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                            $redemptioncashier = (float)$result2[$ctr2]['RedemptionCashier'];
                        if($result2[$ctr2]['ewalletLoadTicket'] != '0.00')
                            $ewalletloadticket = $result2[$ctr2]['ewalletLoadTicket'];
                        $ctr2++;
                    }
                }
                /**** GET Total Summary *****/
                $granddeposit = "0.00";
                $grandreload ="0.00";
                $grandwithdraw = "0.00";

                // store the grand total of transaction types into an array 
                $arrgrand = array("GrandDeposit" => $granddeposit, "GrandReload" => $grandreload, "GrandWithdraw" => $grandwithdraw);

                //results will be fetch here:
                if((count($arrtotal) > 0) && (count($arrgrand) > 0))
                {
                    //$ewalletwithdrawals = $ewalletwithdrawal + $ewalletgenwithdrawal;
                    $regularCash  = $loadcash + $bancnet ;
                    $esafeCash = $ewalletbancnet + $ewalletloadcash;                          
                    //COH computation
                    $cashonhand = (($regularCash + $loadcoupon + $esafeCash + $ewalletloadcoupon) - ($redemptioncashier + $ewalletwithdrawal) - $encashedtickets) - $manualredemption;
                    /**** Get Total Per Page  *****/
                    $vtotal = new stdClass();
                    $vtotal->deposit = number_format($arrtotal["TotalDeposit"], 2, '.', ',');
                    $vtotal->reload = number_format($arrtotal["TotalReload"], 2, '.', ',');
                    $vtotal->withdraw = number_format($arrtotal["TotalWithdraw"], 2, '.', ',');
                    $vtotal->sales = number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',');
                    $xgrossamt = $arrtotal["TotalDeposit"] + $arrtotal["TotalReload"] - $arrtotal["TotalWithdraw"];
                    $vtotal->grosstotal = number_format($xgrossamt, 2, '.', ',');
                    /**** GET Total Page Summary ******/
                    $vtotal->granddeposit = number_format($arrgrand['GrandDeposit'], 2, '.', ',');
                    $vtotal->grandreload = number_format($arrgrand['GrandReload'], 2, '.', ',');
                    $vtotal->grandwithdraw = number_format($arrgrand["GrandWithdraw"], 2, '.', ',');
                    $vtotal->grandsales = number_format($arrgrand["GrandDeposit"] + $arrgrand["GrandReload"], 2, '.', ',');
                    $vtotal->loadcash = number_format($loadcash+$ewalletloadcash, 2, '.', ',');
                    $vtotal->loadticket = number_format($loadticket+$ewalletloadticket, 2, '.', ',');
                    $vtotal->loadcoupon = number_format($loadcoupon+$ewalletloadcoupon, 2, '.', ',');
                    $vtotal->printedtickets = number_format($printedtickets, 2, '.', ',');
                    $vtotal->encashedtickets = number_format($encashedtickets, 2, '.', ',');
                    $vtotal->bancnet = number_format($bancnet+$ewalletbancnet, 2, '.', ',');
                    // count site grosshold
                    //$vtotal->grosshold = number_format($vgrossholdamt, 2, '.', ',');
                    $vtotal->cashonhand = number_format($cashonhand, 2, '.', ',');
                    $vtotal->manualredemption = number_format($manualredemption, 2, '.', ',');

                    // count site cash on hand
                    //               $vcashonhandamt = $loadcash - $redemptioncashier - $manualredemption - $encashedtickets;
                    //               $vtotal->cashonhand = number_format($vcashonhandamt, 2, '.', ',');
                    echo json_encode($vtotal); 
                }
                else 
                {
                    echo "No Results Found";
                }
            }
            else
            {
                echo "No Results Found";
            }
        }
        //**************************** NEW COMPUTATION (V2)***************************************
        else 
        {
            $dateTo = date('Y-m-d',strtotime(date("Y-m-d", strtotime($vdatefrom)) .$gaddeddate))." ".$vcutofftime; 
            $dateFrom = $vdatefrom." ".$vcutofftime;
            $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
            $vsiteID = $rsiteID['SiteID'];

            //$rsitecashier = $orptsup->getsitecashier($vsiteID);
            $result = $orptsup->viewgrosshold($dateFrom, $dateTo,  $vsiteID, $start=null, $limit=null);

            //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
            $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);
            //var_dump($result2);exit;
            if(isset($_SESSION['total']))
            {
                $arrtotal = $_SESSION['total'];
            }
            
            if(count($result) > 0)
            {
                $ctr1 = 0;
                $ctr2 = 0;
                $loadcash = '0.00';
                $loadticket = '0.00';
                $loadcoupon = '0.00';
                $printedtickets = '0.00';
                $encashedtickets = '0.00';
                $redemptioncashier = '0.00';
                $manualredemption = 0.00;
                $bancnet = '0.00';
                $ewalletwithdrawal  = '0.00';
                $redemptiongenesis = '0.00';
                $ewalletgenwithdrawal = '0.00';
                $ewalletbancnet = '0.00';
                $ewalletloadcash = '0.00';
                $ewalletloadcoupon = '0.00';
                $ewalletloadticket = '0.00';
                $totalredemption = '0.00';
                //Sum up the total redemption made by the cashier
                if(count($result) > 0)
                {
                    while($ctr1 < count($result)) 
                    {
                        if($result[$ctr1]['RedemptionCashier'] != '0.00')
                            $redemptioncashier += $result[$ctr1]['RedemptionCashier'];
                        $ctr1++;
                    }
                }
                
                if(count($result2) > 0)
                {
                    while($ctr2 < count($result2))
                    {
                        if($result2[$ctr2]['LoadCash'] != '0.00')
                            $loadcash = $result2[$ctr2]['LoadCash'];
                        if($result2[$ctr2]['LoadTicket'] != '0.00')
                            $loadticket = $result2[$ctr2]['LoadTicket'];
                        if($result2[$ctr2]['ewalletLoadTicket'] != '0.00')
                            $ewalletloadticket = $result2[$ctr2]['ewalletLoadTicket'];
                        if($result2[$ctr2]['LoadCoupon'] != '0.00')
                            $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                        if($result2[$ctr2]['ewalletLoadCoupon'] != '0.00')
                            $ewalletloadcoupon = $result2[$ctr2]['ewalletLoadCoupon'];
                        if($result2[$ctr2]['PrintedTickets'] != '0.00')
                            $printedtickets = $result2[$ctr2]['PrintedTickets'];
                        if($result2[$ctr2]['EncashedTicketsV2'] != '0.00')
                            $encashedtickets = $result2[$ctr2]['EncashedTicketsV2'];
                        if($result2[$ctr2]['ManualRedemption'] != '0.00')
                            $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                        if($result2[$ctr2]['Bancnet'] != '0.00')
                            $bancnet = (float)$result2[$ctr2]['Bancnet'];
                        if($result2[$ctr2]['ewalletBancnet'] != '0.00')
                            $ewalletbancnet = (float)$result2[$ctr2]['ewalletBancnet'];
                        if($result2[$ctr2]['ewalletLoadCash'] != '0.00')
                            $ewalletloadcash = $result2[$ctr2]['ewalletLoadCash'];
                        if($result2[$ctr2]['EwalletWithdrawal'] != '0.00')
                            $ewalletwithdrawal = (float)$result2[$ctr2]['EwalletWithdrawal'];
                        if($result2[$ctr2]['EwalletGenWithdrawal'] != '0.00')
                            $ewalletgenwithdrawal = (float)$result2[$ctr2]['EwalletGenWithdrawal'];
                        if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                            $redemptioncashier = (float)$result2[$ctr2]['RedemptionCashier'];
                        if($result2[$ctr2]['TotalRedemption'] != '0.00')
                            $totalredemption = (float)$result2[$ctr2]['TotalRedemption'];
                        $ctr2++;
                    }
                }
                /**** GET Total Summary *****/
                $granddeposit = "0.00";
                $grandreload ="0.00";
                $grandwithdraw = "0.00";

                // store the grand total of transaction types into an array 
                $arrgrand = array("GrandDeposit" => $granddeposit, "GrandReload" => $grandreload, "GrandWithdraw" => $grandwithdraw);

                //results will be fetch here:
                if((count($arrtotal) > 0) && (count($arrgrand) > 0))
                {
                    $ewalletwithdrawals = $ewalletwithdrawal + $ewalletgenwithdrawal;
                    $regularCash  = $loadcash+$bancnet ;
                    $esafeCash = $ewalletbancnet + $ewalletloadcash; 
                    $cashonhand = (($regularCash +$loadticket+ $loadcoupon + $esafeCash + $ewalletloadticket + $ewalletloadcoupon) - ($totalredemption + $ewalletwithdrawal) - $encashedtickets ) - $manualredemption;

                    /**** Get Total Per Page  *****/
                    $vtotal = new stdClass();
                    $vtotal->deposit = number_format($arrtotal["TotalDeposit"], 2, '.', ',');
                    $vtotal->reload = number_format($arrtotal["TotalReload"], 2, '.', ',');
                    $vtotal->withdraw = number_format($manualredemption + $arrtotal["TotalWithdraw"], 2, '.', ',');
                    $vtotal->sales = number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',');
                    $xgrossamt = $arrtotal["TotalDeposit"] + $arrtotal["TotalReload"] - $arrtotal["TotalWithdraw"];
                    $vtotal->grosstotal = number_format($xgrossamt, 2, '.', ',');
                    /**** GET Total Page Summary ******/
                    $vtotal->granddeposit = number_format($arrgrand['GrandDeposit'], 2, '.', ',');
                    $vtotal->grandreload = number_format($arrgrand['GrandReload'], 2, '.', ',');
                    $vtotal->grandwithdraw = number_format($arrgrand["GrandWithdraw"], 2, '.', ',');
                    $vtotal->grandsales = number_format($arrgrand["GrandDeposit"] + $arrgrand["GrandReload"], 2, '.', ',');
                    $vtotal->loadcash = number_format($loadcash+$ewalletloadcash, 2, '.', ',');
                    $vtotal->loadticket = number_format($loadticket+$ewalletloadticket, 2, '.', ',');
                    $vtotal->loadcoupon = number_format($loadcoupon+$ewalletloadcoupon, 2, '.', ',');
                    $vtotal->printedtickets = number_format($printedtickets, 2, '.', ',');
                    $vtotal->encashedtickets = number_format($encashedtickets, 2, '.', ',');
                    $vtotal->bancnet = number_format($bancnet+$ewalletbancnet, 2, '.', ',');
                    // count site grosshold
                    //$vtotal->grosshold = number_format($vgrossholdamt, 2, '.', ',');
                    $vtotal->cashonhand = number_format($cashonhand, 2, '.', ',');
                    $vtotal->manualredemption = number_format($manualredemption, 2, '.', ',');
                    // count site cash on hand
                    //               $vcashonhandamt = $loadcash - $redemptioncashier - $manualredemption - $encashedtickets;
                    //               $vtotal->cashonhand = number_format($vcashonhandamt, 2, '.', ',');
                    echo json_encode($vtotal); 
                }
                else 
                {
                    echo "No Results Found";
                }
            }
            else
            {
                echo "No Results Found";
            } 
        }
        //       $vdateto = $_POST['endDate'];
        //       $dateFrom = $vdatefrom." ".$vcutofftime;
        //       $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdateto)))." ".$vcutofftime;
        unset($arrtotal, $arrdeposit, $arrreload, $arrdeposit, $arrgrand);
        $orptsup->close();
        exit;
    }
    /***************************** EXPORTING EXCEL STARTS HERE *******************************/
    elseif(isset($_GET['excel']))
    {
        $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
        $vfromdate = $_GET['DateFrom'];

        //       $vtodate = $_GET['DateTo']; 
        //       $dateFrom = $vfromdate." ".$vcutofftime;
        //       $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vtodate)))." ".$vcutofftime;

        $dateTo = date('Y-m-d',strtotime(date("Y-m-d", strtotime($vfromdate)) .$gaddeddate))." ".$vcutofftime; 
        $dateFrom = $vfromdate." ".$vcutofftime;
        $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
        $vsiteID = $rsiteID['SiteID'];

        //$rsitecashier = $orptsup->getsitecashier($vsiteID);

        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
        //setting the values of the headers and data of the excel file
        //and these values comes from the other file which file shows the data

        $rheaders = array('Cashier', 'Total Deposit', 'Total Reload', 'Total Withdrawal', 'Cash on Hand','');
        $result = $orptsup->viewgrosshold($dateFrom, $dateTo, $vsiteID, $start = null, $limit = null);

        //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
        $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);

        $combined = array();
        if(count($result) > 0)
        {   
            /*****************V1 Computation******************/
            if ($dateFrom < $deploymentDate) 
            {
                $supdetails = array();
                foreach ($result as $value) 
                {                     
                    if(isset($supdetails[$value['CreatedByAID']]))
                    { //if a record for cashier already set
                        $supdetails[$value['CreatedByAID']]['Deposits'] += (float)$value['Deposits']; //SUM(D.tdtl) + SUM(D.ewt)
                        $supdetails[$value['CreatedByAID']]['Reloads'] += (float)$value['Reloads'];
                        $supdetails[$value['CreatedByAID']]['Redemptions'] += (float)$value['Redemptions'];
                        $supdetails[$value['CreatedByAID']]['LoadCash'] += (float)$value['LoadCash'];
                        $supdetails[$value['CreatedByAID']]['EncashedTickets'] += (float)$value['EncashedTickets'];
                        $supdetails[$value['CreatedByAID']]['RedemptionCashier'] += (float)$value['RedemptionCashier'];
                        $supdetails[$value['CreatedByAID']]['RedemptionGenesis'] += (float)$value['RedemptionGenesis'];
                        $supdetails[$value['CreatedByAID']]['EwalletRedemption'] += (float)$value['EwalletRedemption'];
                        $supdetails[$value['CreatedByAID']]['EwalletDeposits'] += (float)$value['EwalletDeposits'];
                    }
                    else
                    {
                        $supdetails[$value['CreatedByAID']] = array('CreatedByAID'=>$value['CreatedByAID'],
                        'Name'=>$value['Name'],
                        'Deposits'=>$value['Deposits'],
                        'Reloads'=>$value['Reloads'],
                        'Redemptions'=>$value['Redemptions'],
                        'LoadCash'=>$value['LoadCash'],
                        'EncashedTickets'=>$value['EncashedTickets'],
                        'RedemptionCashier'=>$value['RedemptionCashier'],
                        'RedemptionGenesis'=>$value['RedemptionGenesis'],
                        'EwalletRedemption'=>$value['EwalletRedemption'], 
                        'EwalletDeposits'=>$value['EwalletDeposits']); 
                    }
                }

                $ctr1 = 0;
                $ctr2 = 0;
                $loadcash = '0.00';
                $loadticket = '0.00';
                $loadcoupon = '0.00';
                $printedtickets = '0.00';
                $encashedtickets = '0.00';
                $redemptioncashier = '0.00';
                $manualredemption = 0.00;
                $bancnet = '0.00';
                $ewalletwithdrawal  = '0.00';
                $redemptiongenesis = '0.00';
                $ewalletgenwithdrawal = '0.00';
                $ewalletbancnet = '0.00';
                $ewalletloadcash = '0.00';
                $ewalletloadcoupon = '0.00';
                $ewalletloadticket = '0.00';
                $totalredemption = '0.00';

                $vtotal = array();
                $vdeposit = array();
                $vreload = array();
                $vwithdraw = array();
                
                foreach($supdetails as $vview)
                {
                    $vAID = $vview['CreatedByAID'];
                    $depositamt = (float)$vview['Deposits'] + (float)$vview['EwalletDeposits'];
                    $reloadamt = (float)$vview['Reloads'];
                    $withdrawamt = (float)$vview['Redemptions'] + (float)$vview['RedemptionGenesis'] + $vview['EwalletRedemption'];
                    $loadcash = (float)$vview['LoadCash'];
                    $encashtickets = (float)$vview['EncashedTickets'];
                    $cashierredemption = (float)$vview['RedemptionCashier'];
                    $ewalletredemption = (float)$vview['EwalletRedemption'];
                    $cashonhand = (($depositamt + $reloadamt) - $cashierredemption - $ewalletredemption) - $encashtickets;

                    array_push($combined, array($vview['Name'], number_format($depositamt, 2, '.', ','), 
                    number_format($reloadamt, 2, '.', ','), number_format($withdrawamt, 2, '.', ','), 
                    number_format($cashonhand, 2, '.', ',')));

                    /**** GET Total per page, stores in an array *****/
                    array_push($vtotal, $cashonhand);
                    array_push($vdeposit, $depositamt);
                    array_push($vreload, $reloadamt);
                    array_push($vwithdraw, $withdrawamt);
                }

                //Sum up the total redemption made by the cashier
                if(count($result) > 0)
                {
                    while($ctr1 < count($result)) 
                    {
                        if($result[$ctr1]['RedemptionCashier'] != '0.00')
                            $redemptioncashier += $result[$ctr1]['RedemptionCashier'];
                        $ctr1++;
                    }
                }

                if(count($result2) > 0)
                {
                    while($ctr2 < count($result2))
                    {
                        if($result2[$ctr2]['LoadCash'] != '0.00')
                            $loadcash = $result2[$ctr2]['LoadCash'];
                        if($result2[$ctr2]['LoadTicket'] != '0.00')
                            $loadticket = $result2[$ctr2]['LoadTicket'];
                        if($result2[$ctr2]['LoadCoupon'] != '0.00')
                            $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                        if($result2[$ctr2]['ewalletLoadCoupon'] != '0.00')
                            $ewalletloadcoupon = $result2[$ctr2]['ewalletLoadCoupon'];
                        if($result2[$ctr2]['PrintedTickets'] != '0.00')
                            $printedtickets = $result2[$ctr2]['PrintedTickets'];
                        if($result2[$ctr2]['EncashedTicketsV2'] != '0.00')
                            $encashedtickets = $result2[$ctr2]['EncashedTicketsV2'];
                        if($result2[$ctr2]['ManualRedemption'] != '0.00')
                            $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                        if($result2[$ctr2]['Bancnet'] != '0.00')
                            $bancnet = (float)$result2[$ctr2]['Bancnet'];
                        if($result2[$ctr2]['ewalletBancnet'] != '0.00')
                            $ewalletbancnet = (float)$result2[$ctr2]['ewalletBancnet'];
                        if($result2[$ctr2]['ewalletLoadCash'] != '0.00')
                            $ewalletloadcash = $result2[$ctr2]['ewalletLoadCash'];
                        if($result2[$ctr2]['EwalletWithdrawal'] != '0.00')
                            $ewalletwithdrawal = (float)$result2[$ctr2]['EwalletWithdrawal'];
                        if($result2[$ctr2]['EwalletGenWithdrawal'] != '0.00')
                            $ewalletgenwithdrawal = (float)$result2[$ctr2]['EwalletGenWithdrawal'];
                        if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                            $redemptioncashier = (float)$result2[$ctr2]['RedemptionCashier'];
                        if($result2[$ctr2]['ewalletLoadTicket'] != '0.00')
                            $ewalletloadticket = $result2[$ctr2]['ewalletLoadTicket'];
                        $ctr2++;
                    }
                }
                $ewalletwithdrawals = $ewalletwithdrawal + $ewalletgenwithdrawal;
                $regularCash  = $loadcash+$bancnet ;
                $esafeCash = $ewalletbancnet + $ewalletloadcash;
                // count site cash on hand
                $vcashonhandamt = (($regularCash + $loadcoupon + $esafeCash + $ewalletloadcoupon) - ($redemptioncashier + $ewalletwithdrawal) - $encashedtickets) - $manualredemption;
                //$vcashonhandamt = ((((($loadcash + $bancnet + $loadcoupon) - $redemptioncashier) - $redemptiongenesis) - $ewalletwithdrawal) - $manualredemption) - $encashedtickets;
            }
            /*****************V2 Computation******************/
            else 
            {
                $supdetails = array();
                foreach ($result as $value) 
                {                     
                    if(isset($supdetails[$value['CreatedByAID']]))
                    { //if a record for cashier already set 
                        $supdetails[$value['CreatedByAID']]['GenesisDeposits'] += (float)$value['GenesisDeposits'];
                        $supdetails[$value['CreatedByAID']]['GenesisReloads'] += (float)$value['GenesisReloads'];
                        $supdetails[$value['CreatedByAID']]['Deposits'] += (float)$value['Deposits']; //SUM(D.tdtl) + SUM(D.ewt)
                        $supdetails[$value['CreatedByAID']]['Reloads'] += (float)$value['Reloads'];
                        $supdetails[$value['CreatedByAID']]['Redemptions'] += (float)$value['Redemptions'];
                        $supdetails[$value['CreatedByAID']]['LoadCash'] += (float)$value['LoadCash'];
                        $supdetails[$value['CreatedByAID']]['EncashedTicketsV2'] += (float)$value['EncashedTicketsV2'];
                        $supdetails[$value['CreatedByAID']]['RedemptionCashier'] += (float)$value['RedemptionCashier'];
                        $supdetails[$value['CreatedByAID']]['RedemptionGenesis'] += (float)$value['RedemptionGenesis'];
                        $supdetails[$value['CreatedByAID']]['EwalletRedemption'] += (float)$value['EwalletRedemption'];
                        $supdetails[$value['CreatedByAID']]['EwalletDeposits'] += (float)$value['EwalletDeposits'];
                    }
                    else
                    {
                        $supdetails[$value['CreatedByAID']] = array('CreatedByAID'=>$value['CreatedByAID'], 
                        'GenesisDeposits' => $value['GenesisDeposits'], 
                        'GenesisReloads' => $value['GenesisReloads'], 
                        'Name'=>$value['Name'],
                        'Deposits'=>$value['Deposits'],
                        'Reloads'=>$value['Reloads'],
                        'Redemptions'=>$value['Redemptions'],
                        'LoadCash'=>$value['LoadCash'],
                        'EncashedTicketsV2'=>$value['EncashedTicketsV2'],
                        'RedemptionCashier'=>$value['RedemptionCashier'],
                        'RedemptionGenesis'=>$value['RedemptionGenesis'], 
                        'EwalletRedemption'=>$value['EwalletRedemption'], 
                        'EwalletDeposits'=>$value['EwalletDeposits']); 
                    }
                }

                $ctr1 = 0;
                $ctr2 = 0;
                $loadcash = '0.00';
                $loadticket = '0.00';
                $loadcoupon = '0.00';
                $printedtickets = '0.00';
                $encashedtickets = '0.00';
                $redemptioncashier = '0.00';
                $manualredemption = 0.00;
                $bancnet = '0.00';
                $ewalletwithdrawal  = '0.00';
                $redemptiongenesis = '0.00';
                $ewalletgenwithdrawal = '0.00';
                $ewalletbancnet = '0.00';
                $ewalletloadcash = '0.00';
                $ewalletloadcoupon = '0.00';
                $ewalletloadticket = '0.00';
                $totalredemption = '0.00';

                $vtotal = array();
                $vdeposit = array();
                $vreload = array();
                $vwithdraw = array();
                
                foreach($supdetails as $vview)
                {
                    $vAID = $vview['CreatedByAID'];
                    $depositamt = (float)$vview['Deposits'] + (float)$vview['EwalletDeposits'];
                    $reloadamt = (float)$vview['Reloads'];
                    $withdrawamt = (float)$vview['RedemptionCashier'] + (float)$vview['RedemptionGenesis'] + $vview['EwalletRedemption'] + (float)$vview['EncashedTicketsV2'] ;
                    $loadcash = (float)$vview['LoadCash'];
                    $encashtickets = (float)$vview['EncashedTicketsV2'];
                    $cashierredemption = (float)$vview['RedemptionCashier'];
                    $genredemption = (float)$vview['RedemptionGenesis'];
                    $ewalletredemption = (float)$vview['EwalletRedemption'];
                    $genesisticketloads = (float)$vview['GenesisDeposits'] + (float)$vview['GenesisReloads'];
                    $cashonhand = ($depositamt + $reloadamt) - ($withdrawamt);
    
                    array_push($combined, array($vview['Name'], number_format($depositamt, 2, '.', ','), 
                    number_format($reloadamt, 2, '.', ','), number_format($withdrawamt, 2, '.', ','), 
                    number_format($cashonhand, 2, '.', ',')));

                    /**** GET Total per page, stores in an array *****/
                    array_push($vtotal, $cashonhand);
                    array_push($vdeposit, $depositamt);
                    array_push($vreload, $reloadamt);
                    array_push($vwithdraw, $withdrawamt);
                }

                //Sum up the total redemption made by the cashier
                //                if(count($result) > 0){
                //                   while($ctr1 < count($result)) {
                //                        if($result[$ctr1]['RedemptionCashier'] != '0.00')
                //                            $redemptioncashier += $result[$ctr1]['RedemptionCashier'];
                //                        $ctr1++;
                //                    }
                //               }

                if(count($result2) > 0)
                {
                    while($ctr2 < count($result2))
                    {
                        if($result2[$ctr2]['LoadCash'] != '0.00')
                            $loadcash = $result2[$ctr2]['LoadCash'];
                        if($result2[$ctr2]['LoadTicket'] != '0.00')
                            $loadticket = $result2[$ctr2]['LoadTicket'];
                        if($result2[$ctr2]['ewalletLoadTicket'] != '0.00')
                            $ewalletloadticket = $result2[$ctr2]['ewalletLoadTicket'];
                        if($result2[$ctr2]['LoadCoupon'] != '0.00')
                            $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                        if($result2[$ctr2]['ewalletLoadCoupon'] != '0.00')
                            $ewalletloadcoupon = $result2[$ctr2]['ewalletLoadCoupon'];
                        if($result2[$ctr2]['PrintedTickets'] != '0.00')
                            $printedtickets = $result2[$ctr2]['PrintedTickets'];
                        if($result2[$ctr2]['EncashedTicketsV2'] != '0.00')
                            $encashedtickets = $result2[$ctr2]['EncashedTicketsV2'];
                        if($result2[$ctr2]['ManualRedemption'] != '0.00')
                            $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                        if($result2[$ctr2]['Bancnet'] != '0.00')
                            $bancnet = (float)$result2[$ctr2]['Bancnet'];
                        if($result2[$ctr2]['ewalletBancnet'] != '0.00')
                            $ewalletbancnet = (float)$result2[$ctr2]['ewalletBancnet'];
                        if($result2[$ctr2]['ewalletLoadCash'] != '0.00')
                            $ewalletloadcash = $result2[$ctr2]['ewalletLoadCash'];
                        if($result2[$ctr2]['EwalletWithdrawal'] != '0.00')
                            $ewalletwithdrawal = (float)$result2[$ctr2]['EwalletWithdrawal'];
                        if($result2[$ctr2]['EwalletGenWithdrawal'] != '0.00')
                            $ewalletgenwithdrawal = (float)$result2[$ctr2]['EwalletGenWithdrawal'];
                        if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                            $redemptioncashier = (float)$result2[$ctr2]['RedemptionCashier'];
                        if($result2[$ctr2]['TotalRedemption'] != '0.00')
                            $totalredemption = (float)$result2[$ctr2]['TotalRedemption'];
                        $ctr2++;
                    }
                }
                
                // count site cash on hand
                $ewalletwithdrawals = $ewalletwithdrawal + $ewalletgenwithdrawal;
                $regularCash  = $loadcash+$bancnet ;
                $esafeCash = $ewalletbancnet + $ewalletloadcash; 
                $vcashonhandamt = (($regularCash +$loadticket+ $loadcoupon + $esafeCash + $ewalletloadticket + $ewalletloadcoupon) - ($totalredemption + $ewalletwithdrawal) - $encashedtickets ) - $manualredemption;
                //           $vcashonhandamt = ((((($loadcash + $bancnet + $loadcoupon + $loadticket) - $redemptioncashier) - $redemptiongenesis) - $ewalletwithdrawal) - $manualredemption) - $encashedtickets;
            }
            unset($supdetails);
            /*** Get the Total sum of transaction types and gross hold***/
            $totalgh = array_sum($vtotal);
            $totaldeposit = array_sum($vdeposit);
            $totalreload = array_sum($vreload);
            $totalwithdraw = array_sum($vwithdraw);

            //array variable to store an array of transaction types, GH; used on ajax call
            $arrtotal = array("CashOnHand" => $totalgh, "TotalDeposit" => $totaldeposit, 
                "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);

            //array for displaying totals on excel file
            $totals1 = array(0 => '',
                1 => '',
                2 => '',
                3 => '',
                4 => ''
                );
                //             $totals2 = array(0 => 'Summary per Page',
                //                        1 => '',
                //                        2 => 'Sales',
                //                        3 => number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ','),
                //                        4 => 'Redemption',
                //                        5 => number_format($arrtotal["TotalWithdraw"], 2, '.', ',')
                //                       );
            // CCT EDITED 12/18/2017 BEGIN
            $totals3 = array(0 => 'Grand Total',
                1 => 'Total Sales',
                2 => number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ','),
                3 => 'Total Redemption',
                4 => number_format(($arrtotal["TotalWithdraw"] + $manualredemption), 2, '.', ',')
                );
            // CCT EDITED 12/18/2017 END
                $totals4 = array(0 => '       ',
                1 => '     Cash',
                2 => number_format($loadcash+$ewalletloadcash, 2, '.', ','),
                3 => 'Manual Redemption',
                4 => number_format($manualredemption, 2, '.', ',')
                );
            $totals5 = array(0 => '       ',
                1 => '     Bancnet',
                2 => number_format($bancnet+$ewalletbancnet, 2, '.', ','),
                3 => 'Printed Tickets',
                4 => number_format($printedtickets, 2, '.', ',')
                );
            $totals6 = array(0 => '       ',
                1 => '     Tickets',
                2 => number_format($loadticket+$ewalletloadticket, 2, '.', ','),
                3 => 'Encashed Tickets',
                4 => number_format($encashedtickets, 2, '.', ',')
                );
            $totals7 = array(0 => '       ',
                1 => '     Coupons',
                2 => number_format($loadcoupon+$ewalletloadcoupon, 2, '.', ','),
                3 => 'Cash On Hand',
                4 => number_format($vcashonhandamt, 2, '.', ',')
                );
            array_push($combined, $totals1);
            //             array_push($combined, $totals2);
            array_push($combined, $totals3);
            array_push($combined, $totals4);
            array_push($combined, $totals5);
            array_push($combined, $totals6);
            array_push($combined, $totals7);
        }
        else
        {
            array_push($combined, array("No results found"));
        }

        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Gross Hold";
        $orptsup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $combined);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        unset($totals, $combined, $arrtotal, $vtotal, $vdeposit, $vreload, $vwithdraw);
        $orptsup->close();
        exit;
    }
    /***************************** EXPORTING PDF STARTS HERE *******************************/
    elseif(isset($_GET['pdf']))
    {
        $fn = $_GET['fn'].".pdf"; //this will be the filename of the pdf file
        $vfromdate = $_GET['DateFrom'];

        //       $vtodate = $_GET['DateTo']; 
        //       $dateFrom = $vfromdate." ".$vcutofftime;
        //       $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vtodate)))." ".$vcutofftime;

        $dateTo = date('Y-m-d',strtotime(date("Y-m-d", strtotime($vfromdate)) .$gaddeddate))." ".$vcutofftime; 
        $dateFrom = $vfromdate." ".$vcutofftime;
        $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
        $vsiteID = $rsiteID['SiteID'];

        $rsitecashier = $orptsup->getsitecashier($vsiteID);

        $result = $orptsup->viewgrosshold($dateFrom, $dateTo, $vsiteID, $start = null, $limit = null);

        //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
        $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);

        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold');
        $pdf->html.='<div style="text-align:center; ">As of ' . $dateFrom . ' To '.$dateTo.'</div><br/>';
        $pdf->SetFontSize(10);
        $pdf->c_tableHeader2(array(
            array('value'=>'Cashier'),
            array('value'=>'Total Deposit'),
            array('value'=>'Total Reload'),
            array('value'=>'Total Withdrawal'),
            array('value'=>'Cash on Hand'),
            array('value'=>'')));

        $combined = array();
        /*******************GROSS HOLD COMPUTATION V1*************/
        if ($dateFrom < $deploymentDate) 
        {
            if(count($result) > 0)
            {   
                $supdetails = array();
                foreach ($result as $value)  
                {                     
                    if(isset($supdetails[$value['CreatedByAID']]))
                    { //if a record for cashier already set
                        $supdetails[$value['CreatedByAID']]['Deposits'] += (float)$value['Deposits']; //SUM(D.tdtl) + SUM(D.ewt)
                        $supdetails[$value['CreatedByAID']]['Reloads'] += (float)$value['Reloads'];
                        $supdetails[$value['CreatedByAID']]['Redemptions'] += (float)$value['Redemptions'];
                        $supdetails[$value['CreatedByAID']]['LoadCash'] += (float)$value['LoadCash'];
                        $supdetails[$value['CreatedByAID']]['EncashedTickets'] += (float)$value['EncashedTickets'];
                        $supdetails[$value['CreatedByAID']]['RedemptionCashier'] += (float)$value['RedemptionCashier'];
                        $supdetails[$value['CreatedByAID']]['RedemptionGenesis'] += (float)$value['RedemptionGenesis'];
                        $supdetails[$value['CreatedByAID']]['EwalletRedemption'] += (float)$value['EwalletRedemption'];
                        $supdetails[$value['CreatedByAID']]['EwalletDeposits'] += (float)$value['EwalletDeposits'];
                    }
                    else
                    {
                        $supdetails[$value['CreatedByAID']] = array('CreatedByAID'=>$value['CreatedByAID'],
                        'Name'=>$value['Name'],
                        'Deposits'=>$value['Deposits'],
                        'Reloads'=>$value['Reloads'],
                        'Redemptions'=>$value['Redemptions'],
                        'LoadCash'=>$value['LoadCash'],
                        'EncashedTickets'=>$value['EncashedTickets'],
                        'RedemptionCashier'=>$value['RedemptionCashier'],
                        'RedemptionGenesis'=>$value['RedemptionGenesis'],
                        'EwalletRedemption'=>$value['EwalletRedemption'], 
                        'EwalletDeposits'=>$value['EwalletDeposits']); 
                    }
                }

                $vtotal = array();
                $vdeposit = array();
                $vreload = array();
                $vwithdraw = array();

                foreach($supdetails as $vview)
                {
                    $vAID = $vview['CreatedByAID'];
                    $depositamt = (float)$vview['Deposits'] + (float)$vview['EwalletDeposits'];
                    $reloadamt = (float)$vview['Reloads'];
                    $withdrawamt = (float)$vview['Redemptions'] + (float)$vview['RedemptionGenesis'] + $vview['EwalletRedemption'];
                    $loadcash = (float)$vview['LoadCash'];
                    $encashtickets = (float)$vview['EncashedTickets'];
                    $cashierredemption = (float)$vview['RedemptionCashier'];
                    $ewalletredemption = (float)$vview['EwalletRedemption'];
                    $cashonhand = (($depositamt + $reloadamt) - $cashierredemption - $ewalletredemption) - $encashtickets;
                    $combined = array($vview['Name'], '<span style="text-align: right;">'.number_format($depositamt, 2, '.', ',').'</span>', 
                        '<span style="text-align: right;">'.number_format($reloadamt, 2, '.', ',').'</span>', 
                        '<span style="text-align: right;">'.number_format($withdrawamt, 2, '.', ',').'</span>', 
                        '<span style="text-align: right;">'.number_format($cashonhand, 2, '.', ',').'</span>','');

                    $pdf->c_tableRow($combined);
                    /**** GET Total per page, stores in an array *****/
                    array_push($vtotal, $grossholdamt);
                    array_push($vdeposit, $depositamt);
                    array_push($vreload, $reloadamt);
                    array_push($vwithdraw, $withdrawamt);
                }
                unset($supdetails);

                $ctr1 = 0;
                $ctr2 = 0;
                $loadcash = '0.00';
                $loadticket = '0.00';
                $loadcoupon = '0.00';
                $printedtickets = '0.00';
                $encashedtickets = '0.00';
                $redemptioncashier = '0.00';
                $manualredemption = 0.00;
                $bancnet = '0.00';
                $ewalletwithdrawal  = '0.00';
                $redemptiongenesis = '0.00';
                $ewalletgenwithdrawal = '0.00';
                $ewalletbancnet = '0.00';
                $ewalletloadcash = '0.00';
                $ewalletloadcoupon = '0.00';
                $ewalletloadticket = '0.00';
                $totalredemption = '0.00';

                //Sum up the total redemption made by the cashier
                if(count($result) > 0)
                {
                    while($ctr1 < count($result)) 
                    {
                        if($result[$ctr1]['RedemptionCashier'] != '0.00')
                            $redemptioncashier += $result[$ctr1]['RedemptionCashier'];
                        $ctr1++;
                    }
                }

                if(count($result2) > 0)
                {
                    while($ctr2 < count($result2))
                    {
                        if($result2[$ctr2]['LoadCash'] != '0.00')
                            $loadcash = $result2[$ctr2]['LoadCash'];
                        if($result2[$ctr2]['LoadTicket'] != '0.00')
                            $loadticket = $result2[$ctr2]['LoadTicket'];
                        if($result2[$ctr2]['LoadCoupon'] != '0.00')
                            $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                        if($result2[$ctr2]['ewalletLoadCoupon'] != '0.00')
                            $ewalletloadcoupon = $result2[$ctr2]['ewalletLoadCoupon'];
                        if($result2[$ctr2]['PrintedTickets'] != '0.00')
                            $printedtickets = $result2[$ctr2]['PrintedTickets'];
                        if($result2[$ctr2]['EncashedTicketsV2'] != '0.00')
                            $encashedtickets = $result2[$ctr2]['EncashedTicketsV2'];
                        if($result2[$ctr2]['ManualRedemption'] != '0.00')
                            $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                        if($result2[$ctr2]['Bancnet'] != '0.00')
                            $bancnet = (float)$result2[$ctr2]['Bancnet'];
                        if($result2[$ctr2]['ewalletBancnet'] != '0.00')
                            $ewalletbancnet = (float)$result2[$ctr2]['ewalletBancnet'];
                        if($result2[$ctr2]['ewalletLoadCash'] != '0.00')
                            $ewalletloadcash = $result2[$ctr2]['ewalletLoadCash'];
                        if($result2[$ctr2]['EwalletWithdrawal'] != '0.00')
                            $ewalletwithdrawal = (float)$result2[$ctr2]['EwalletWithdrawal'];
                        if($result2[$ctr2]['EwalletGenWithdrawal'] != '0.00')
                            $ewalletgenwithdrawal = (float)$result2[$ctr2]['EwalletGenWithdrawal'];
                        if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                            $redemptioncashier = (float)$result2[$ctr2]['RedemptionCashier'];
                        if($result2[$ctr2]['ewalletLoadTicket'] != '0.00')
                            $ewalletloadticket = $result2[$ctr2]['ewalletLoadTicket'];
                        $ctr2++;
                    }
                }

                $ewalletwithdrawals = $ewalletwithdrawal + $ewalletgenwithdrawal;
                $regularCash  = $loadcash+$bancnet ;
                $esafeCash = $ewalletbancnet + $ewalletloadcash;
                // count site cash on hand
                $vcashonhandamt = (($regularCash + $loadcoupon + $esafeCash + $ewalletloadcoupon) - ($redemptioncashier + $ewalletwithdrawal) - $encashedtickets) - $manualredemption;
                //$vcashonhandamt = ((((($loadcash + $bancnet + $loadcoupon) - $redemptioncashier) - $redemptiongenesis) - $ewalletwithdrawal) - $manualredemption) - $encashedtickets;

                /*** Get the Total sum of transaction types and gross hold***/
                $totalgh = array_sum($vtotal);
                $totaldeposit = array_sum($vdeposit);
                $totalreload = array_sum($vreload);
                $totalwithdraw = array_sum($vwithdraw);

                //array variable to store an array of transaction types, GH; used on ajax call
                $arrtotal = array("CashOnHand" => $totalgh, "TotalDeposit" => $totaldeposit, "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);

                //array for displaying totals on excel file
                $totals1 = array(0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => ''
                    );
                    //             $totals2 = array(0 => 'Summary per Page',
                    //                        1 => '',
                    //                        2 => 'Sales',
                    //                        3 => '<span style="text-align: right;">'.number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',').'</span>',
                    //                        4 => 'Redemption',
                    //                        5 => '<span style="text-align: right;">'.number_format($arrtotal["TotalWithdraw"], 2, '.', ',').'</span>'
                    //                       );
                $totals3 = array(0 => 'Grand Total',
                    1 => '',
                    2 => 'Total Sales',
                    3 => '<span style="text-align: right;">'.number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',').'</span>',
                    4 => 'Total Redemption',
                    5 => '<span style="text-align: right;">'.number_format($arrtotal["TotalWithdraw"] + $manualredemption, 2, '.', ',').'</span>'
                    );
                $totals4 = array(0 => '       ',
                    1 => '',
                    2 => '     Cash',
                    3 =>'<span style="text-align: right;">'. number_format($loadcash+$ewalletloadcash, 2, '.', ',').'</span>',
                    4 => 'Manual Redemption',
                    5 => '<span style="text-align: right;">'.number_format($manualredemption, 2, '.', ',').'</span>'
                    );
                $totals5 = array(0 => '       ',
                    1 => '',
                    2 => '     Bancnet',
                    3 => '<span style="text-align: right;">'.number_format($bancnet+$ewalletbancnet, 2, '.', ',').'</span>',
                    4 => 'Printed Tickets',
                    5 => '<span style="text-align: right;">'.number_format($printedtickets, 2, '.', ',').'</span>'
                    );
                $totals6 = array(0 => '       ',
                    1 => '     ',
                    2 => '     Tickets',
                    3 => '<span style="text-align: right;">'.number_format($loadticket+$ewalletloadticket, 2, '.', ',').'</span>',
                    4 => 'Encashed Tickets',
                    5 => '<span style="text-align: right;">'.number_format($encashedtickets, 2, '.', ',').'</span>'
                    );
                $totals7 = array(0 => '       ',
                    1 => '     ',
                    2 => '     Coupons',
                    3 => '<span style="text-align: right;">'.number_format($loadcoupon + $ewalletloadcoupon, 2, '.', ',').'</span>',
                    4 => 'Cash On Hand',
                    5 => '<span style="text-align: right;">'.number_format($vcashonhandamt, 2, '.', ',').'</span>'
                    );

                //array_push($combined, $totals); 
                $pdf->c_tableRow($totals1);
                //           $pdf->c_tableRow($totals2);
                $pdf->c_tableRow($totals3);
                $pdf->c_tableRow($totals4);
                $pdf->c_tableRow($totals5);
                $pdf->c_tableRow($totals6);
                $pdf->c_tableRow($totals7);
            }
            else
            {
                $pdf->html.='<div style="text-align:center;">No Results Found</div>';
            }  
        }
        /*******************GROSS HOLD COMPUTATION V2*************/
        else 
        {
            if(count($result) > 0)
            {   
                $supdetails = array();
                foreach ($result as $value) 
                {                     
                    if(isset($supdetails[$value['CreatedByAID']]))
                    { //if a record for cashier already set 
                        $supdetails[$value['CreatedByAID']]['GenesisDeposits'] += (float)$value['GenesisDeposits'];
                        $supdetails[$value['CreatedByAID']]['GenesisReloads'] += (float)$value['GenesisReloads'];
                        $supdetails[$value['CreatedByAID']]['Deposits'] += (float)$value['Deposits']; //SUM(D.tdtl) + SUM(D.ewt)
                        $supdetails[$value['CreatedByAID']]['Reloads'] += (float)$value['Reloads'];
                        $supdetails[$value['CreatedByAID']]['Redemptions'] += (float)$value['Redemptions'];
                        $supdetails[$value['CreatedByAID']]['LoadCash'] += (float)$value['LoadCash'];
                        $supdetails[$value['CreatedByAID']]['EncashedTicketsV2'] += (float)$value['EncashedTicketsV2'];
                        $supdetails[$value['CreatedByAID']]['RedemptionCashier'] += (float)$value['RedemptionCashier'];
                        $supdetails[$value['CreatedByAID']]['RedemptionGenesis'] += (float)$value['RedemptionGenesis'];
                        $supdetails[$value['CreatedByAID']]['EwalletRedemption'] += (float)$value['EwalletRedemption'];
                        $supdetails[$value['CreatedByAID']]['EwalletDeposits'] += (float)$value['EwalletDeposits'];
                    }
                    else
                    {
                        $supdetails[$value['CreatedByAID']] = array('CreatedByAID'=>$value['CreatedByAID'], 
                        'GenesisDeposits' => $value['GenesisDeposits'], 
                        'GenesisReloads' => $value['GenesisReloads'], 
                        'Name'=>$value['Name'],
                        'Deposits'=>$value['Deposits'],
                        'Reloads'=>$value['Reloads'],
                        'Redemptions'=>$value['Redemptions'],
                        'LoadCash'=>$value['LoadCash'],
                        'EncashedTicketsV2'=>$value['EncashedTicketsV2'],
                        'RedemptionCashier'=>$value['RedemptionCashier'],
                        'RedemptionGenesis'=>$value['RedemptionGenesis'], 
                        'EwalletRedemption'=>$value['EwalletRedemption'], 
                        'EwalletDeposits'=>$value['EwalletDeposits']); 
                    }
                }

                $vtotal = array();
                $vdeposit = array();
                $vreload = array();
                $vwithdraw = array();

                foreach($supdetails as $vview)
                {
                    $vAID = $vview['CreatedByAID'];
                    $depositamt = (float)$vview['Deposits'] + (float)$vview['EwalletDeposits'];
                    $reloadamt = (float)$vview['Reloads'];
                    $withdrawamt = (float)$vview['RedemptionCashier'] + (float)$vview['RedemptionGenesis'] + $vview['EwalletRedemption'] + (float)$vview['EncashedTicketsV2'] ;
                    $loadcash = (float)$vview['LoadCash'];
                    $encashtickets = (float)$vview['EncashedTicketsV2'];
                    $cashierredemption = (float)$vview['RedemptionCashier'];
                    $genredemption = (float)$vview['RedemptionGenesis'];
                    $ewalletredemption = (float)$vview['EwalletRedemption'];
                    $genesisticketloads = (float)$vview['GenesisDeposits'] + (float)$vview['GenesisReloads'];
                    $cashonhand = ($depositamt + $reloadamt) - ($withdrawamt);
                    $combined = array($vview['Name'], '<span style="text-align: right;">'.number_format($depositamt, 2, '.', ',').'</span>', 
                        '<span style="text-align: right;">'.number_format($reloadamt, 2, '.', ',').'</span>', 
                        '<span style="text-align: right;">'.number_format($withdrawamt, 2, '.', ',').'</span>', 
                        '<span style="text-align: right;">'.number_format($cashonhand, 2, '.', ',').'</span>','');
                    $pdf->c_tableRow($combined);

                    /**** GET Total per page, stores in an array *****/
                    array_push($vtotal, $cashonhand);
                    array_push($vdeposit, $depositamt);
                    array_push($vreload, $reloadamt);
                    array_push($vwithdraw, $withdrawamt);
                }

                unset($supdetails);

                $ctr1 = 0;
                $ctr2 = 0;
                $loadcash = '0.00';
                $loadticket = '0.00';
                $loadcoupon = '0.00';
                $printedtickets = '0.00';
                $encashedtickets = '0.00';
                $redemptioncashier = '0.00';
                $manualredemption = 0.00;
                $bancnet = '0.00';
                $ewalletwithdrawal  = '0.00';
                $redemptiongenesis = '0.00';
                $ewalletgenwithdrawal = '0.00';
                $ewalletbancnet = '0.00';
                $ewalletloadcash = '0.00';
                $ewalletloadcoupon = '0.00';
                $ewalletloadticket = '0.00';
                $totalredemption = '0.00';

                //Sum up the total redemption made by the cashier
                if(count($result) > 0)
                {
                    while($ctr1 < count($result)) 
                    {
                        if($result[$ctr1]['RedemptionCashier'] != '0.00')
                            $redemptioncashier += $result[$ctr1]['RedemptionCashier'];
                        $ctr1++;
                    }
                }

                if(count($result2) > 0)
                {
                    while($ctr2 < count($result2))
                    {
                        if($result2[$ctr2]['LoadCash'] != '0.00')
                            $loadcash = $result2[$ctr2]['LoadCash'];
                        if($result2[$ctr2]['LoadTicket'] != '0.00')
                            $loadticket = $result2[$ctr2]['LoadTicket'];
                        if($result2[$ctr2]['ewalletLoadTicket'] != '0.00')
                            $ewalletloadticket = $result2[$ctr2]['ewalletLoadTicket'];
                        if($result2[$ctr2]['LoadCoupon'] != '0.00')
                            $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                        if($result2[$ctr2]['ewalletLoadCoupon'] != '0.00')
                            $ewalletloadcoupon = $result2[$ctr2]['ewalletLoadCoupon'];
                        if($result2[$ctr2]['PrintedTickets'] != '0.00')
                            $printedtickets = $result2[$ctr2]['PrintedTickets'];
                        if($result2[$ctr2]['EncashedTicketsV2'] != '0.00')
                            $encashedtickets = $result2[$ctr2]['EncashedTicketsV2'];
                        if($result2[$ctr2]['ManualRedemption'] != '0.00')
                            $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                        if($result2[$ctr2]['Bancnet'] != '0.00')
                            $bancnet = (float)$result2[$ctr2]['Bancnet'];
                        if($result2[$ctr2]['ewalletBancnet'] != '0.00')
                            $ewalletbancnet = (float)$result2[$ctr2]['ewalletBancnet'];
                        if($result2[$ctr2]['ewalletLoadCash'] != '0.00')
                            $ewalletloadcash = $result2[$ctr2]['ewalletLoadCash'];
                        if($result2[$ctr2]['EwalletWithdrawal'] != '0.00')
                            $ewalletwithdrawal = (float)$result2[$ctr2]['EwalletWithdrawal'];
                        if($result2[$ctr2]['EwalletGenWithdrawal'] != '0.00')
                            $ewalletgenwithdrawal = (float)$result2[$ctr2]['EwalletGenWithdrawal'];
                        if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                            $redemptioncashier = (float)$result2[$ctr2]['RedemptionCashier'];
                        if($result2[$ctr2]['TotalRedemption'] != '0.00')
                            $totalredemption = (float)$result2[$ctr2]['TotalRedemption'];
                        $ctr2++;
                    }
                }

                // count site cash on hand
                $ewalletwithdrawals = $ewalletwithdrawal + $ewalletgenwithdrawal;
                $regularCash  = $loadcash+$bancnet ;
                $esafeCash = $ewalletbancnet + $ewalletloadcash; 
                $vcashonhandamt = (($regularCash +$loadticket+ $loadcoupon + $esafeCash + $ewalletloadticket + $ewalletloadcoupon) - ($totalredemption + $ewalletwithdrawal) - $encashedtickets ) - $manualredemption;
                //$vcashonhandamt = ((((($loadcash + $bancnet + $loadcoupon + $loadticket) - $redemptioncashier) - $redemptiongenesis) - $ewalletwithdrawal) - $manualredemption) - $encashedtickets;

                /*** Get the Total sum of transaction types and gross hold***/
                $totalgh = array_sum($vtotal);
                $totaldeposit = array_sum($vdeposit);
                $totalreload = array_sum($vreload);
                $totalwithdraw = array_sum($vwithdraw);

                //array variable to store an array of transaction types, GH; used on ajax call
                $arrtotal = array("CashOnHand" => $totalgh, "TotalDeposit" => $totaldeposit, 
                    "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);

                //array for displaying totals on excel file
                $totals1 = array(0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => ''
                    );
                    //             $totals2 = array(0 => 'Summary per Page',
                    //                        1 => '',
                    //                        2 => 'Sales',
                    //                        3 => '<span style="text-align: right;">'.number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',').'</span>',
                    //                        4 => 'Redemption',
                    //                        5 => '<span style="text-align: right;">'.number_format($arrtotal["TotalWithdraw"], 2, '.', ',').'</span>'
                    //
                    //                                            );
                // CCT EDITED 12/18/2017 BEGIN
                $totals3 = array(0 => 'Grand Total',
                    1 => '',
                    2 => 'Total Sales',
                    3 => '<span style="text-align: right;">'.number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',').'</span>',
                    4 => 'Total Redemption',
                    5 => '<span style="text-align: right;">'.number_format(($arrtotal["TotalWithdraw"] + $manualredemption), 2, '.', ',').'</span>'
                    );
               // CCT EDITED 12/18/2017 END
                $totals4 = array(0 => '       ',
                    1 => '',
                    2 => '     Cash',
                    3 =>'<span style="text-align: right;">'. number_format($loadcash+$ewalletloadcash, 2, '.', ',').'</span>',
                    4 => 'Manual Redemption',
                    5 => '<span style="text-align: right;">'.number_format($manualredemption, 2, '.', ',').'</span>'
                    );
                $totals5 = array(0 => '       ',
                    1 => '',
                    2 => '     Bancnet',
                    3 => '<span style="text-align: right;">'.number_format($bancnet+$ewalletbancnet, 2, '.', ',').'</span>',
                    4 => 'Printed Tickets',
                    5 => '<span style="text-align: right;">'.number_format($printedtickets, 2, '.', ',').'</span>'
                    );
                $totals6 = array(0 => '       ',
                    1 => '     ',
                    2 => '     Tickets',
                    3 => '<span style="text-align: right;">'.number_format($loadticket+$ewalletloadticket, 2, '.', ',').'</span>',
                    4 => 'Encashed Tickets',
                    5 => '<span style="text-align: right;">'.number_format($encashedtickets, 2, '.', ',').'</span>'
                    );
                $totals7 = array(0 => '       ',
                    1 => '     ',
                    2 => '     Coupons',
                    3 => '<span style="text-align: right;">'.number_format($loadcoupon+$ewalletloadcoupon, 2, '.', ',').'</span>',
                    4 => 'Cash On Hand',
                    5 => '<span style="text-align: right;">'.number_format($vcashonhandamt, 2, '.', ',').'</span>'
                    );

                //array_push($combined, $totals); 
                $pdf->c_tableRow($totals1);
                //           $pdf->c_tableRow($totals2);
                $pdf->c_tableRow($totals3);
                $pdf->c_tableRow($totals4);
                $pdf->c_tableRow($totals5);
                $pdf->c_tableRow($totals6);
                $pdf->c_tableRow($totals7);
            }
            else
            {
                $pdf->html.='<div style="text-align:center;">No Results Found</div>';
            }
        }
        $pdf->c_tableEnd();
        unset($totals,$arrtotal, $vtotal, $vdeposit, $vreload, $vwithdraw);
        $vauditfuncID = 40; //export to pdf
        $vtransdetails = "Gross Hold";
        $orptsup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $pdf->c_generatePDF($fn); 
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>