<?php
/*
 * Created By: Edson L. Perez
 * Date Created: September 16, 2011
 * Purpose: Process for operator reports
 */

include __DIR__."/../sys/class/RptOperator.class.php";
include __DIR__."/../sys/class/LoyaltyUBWrapper.class.php";
require __DIR__.'/../sys/core/init.php';
require_once __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

// ADDED CCT 02/21/2018 BEGIN
function multidimensional_search($parents, $searched) 
{

  if (empty($searched) || empty($parents)) 
  { 
    return false; 
  } 

  foreach ($parents as $key => $value) 
  { 
        $exists = true; 
        foreach ($searched as $skey => $svalue) 
        { 
           $exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue); 
        } 
        if($exists)
        { 
            return $key; 
        } 
  } 
  return false; 
} 
// ADDED CCT 02/21/2018 END

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

$orptoptr = new RptOperator($_DBConnectionString[0]);
$orptoptr2 = new RptOperator($_DBConnectionString[5]);
$loyalty= new LoyaltyUBWrapper();
$connected = $orptoptr->open();
$nopage = 0;
if($connected)
{    
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $orptoptr->getDate();
    /****************** Session Checking ********************/    
    $isexist=$orptoptr->checksession($aid);
    if($isexist == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $orptoptr->close();
        if($orptoptr->isAjaxRequest())
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=".$msg);
    }   
    
    $isexistsession =$orptoptr->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $orptoptr->close();
        header("Location: login.php?mess=".$msg);
    }
    /***************** End Session Checking ******************/   

    //checks if account was locked 
    $islocked = $orptoptr->chkLoginAttempts($aid);
    if(isset($islocked['LoginAttempts']))
    {
        $loginattempts = $islocked['LoginAttempts'];
        if($loginattempts >= 3)
        {
            $orptoptr->deletesession($aid);
            session_destroy();
            $msg = "Not Connected";
            $orptoptr->close();
            header("Location: login.php?mess=".$msg);
            exit;
        }
    }

    $rsitesowned = array();
    $rsitesowned = $orptoptr->viewsitebyowner($aid); //get all sites owned by operator
    $_SESSION['pegsowned'] = $rsitesowned;

    $sitelist = array();
    $sitelist = $orptoptr->getallsites();
    $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection

    /****************** SITE TRANSACTIONS *********************/
    $vcutofftime = $cutoff_time; //cutoff time set for report (web.config.php)

    //for JQGRID pagination
    if(isset($_POST['paginate']))
    {
        $vpage = $_POST['paginate'];
        switch($vpage)
        {
            // CCT ADDED 02/21/2018 BEGIN
            case "RptGrossHoldPerProvider":
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                $datefrom = $_POST['date'];
                $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
                
                //get site operator's sites
                $aid = $_SESSION['accID'];
                $siteIDs = $orptoptr->getSiteByAID($aid);
                
                //get service ids with transaction for said date
                //EDITED CCT 07/11/2018 BEGIN
                //$serviceGrpIDs = array(1, 6, 7);
                $serviceGrpIDs = array(1, 4, 6, 7);
                //EDITED CCT 07/11/2018 END
                $serviceIDs = $orptoptr->getServiceIDwithTransactions($serviceGrpIDs, $siteIDs, $datefrom, $dateto);
                
                // Prepare array structure
                $siteIDarray = array();
                if (count($siteIDs) > 0) 
                {
                    foreach ($siteIDs as $siteID) 
                    {
                        $siteIDarray [$siteID['SiteID']] =
                            array('SiteID' => $siteID['SiteID'], 'SiteCode' => trim(str_replace("ICSA-", "", $siteID['SiteCode'])),);
                            
                        if (count($serviceIDs) > 0)
                        {
                            $serviceIDarray ['ServiceInfo'] = array();                        
                            foreach ($serviceIDs as $serviceID)
                            { 
                                $tempserviceIDarray [$serviceID['ServiceID']] = array();
                                $tempserviceIDarray = 
                                    array(
                                        'ServiceID'     => $serviceID['ServiceID'],
                                        'ServiceName'   => $serviceID['ServiceName'],
                                        'UserMode'      => $serviceID['UserMode'],
                                        'TB_DR'         => 0,
                                        'TB_W'          => 0,
                                        'TB_MR'         => 0,
                                        'TB_GH'         => 0,
                                        'UB_GH'         => 0,
                                        'SubTotal_GH'   => 0
                                    );
                                $resultarr = array_push($serviceIDarray ['ServiceInfo'], $tempserviceIDarray);
                            }
                            $siteIDarray[$siteID['SiteID']] = array_merge($siteIDarray[$siteID['SiteID']], $serviceIDarray);                            
                        }
                    }
                }
                
                // Create additional total line entry
                $siteIDarray ['Total'] = array('SiteID' => 'Total', 'SiteCode' => 'Total',);
   
                if (count($serviceIDs) > 0)
                {
                    $serviceIDarray ['ServiceInfo'] = array();                        
                    foreach ($serviceIDs as $serviceID)
                    { 
                        $tempserviceIDarray [$serviceID['ServiceID']] = array();
                        $tempserviceIDarray = 

                            array(
                                'ServiceID'     => $serviceID['ServiceID'],
                                'ServiceName'   => $serviceID['ServiceName'],
                                'UserMode'      => $serviceID['UserMode'],
                                'TB_DR'         => 0,
                                'TB_W'          => 0,
                                'TB_MR'         => 0,
                                'TB_GH'         => 0,
                                'UB_GH'         => 0,
                                'SubTotal_GH'   => 0
                            );
                        $resultarr = array_push($serviceIDarray ['ServiceInfo'], $tempserviceIDarray);
                    }
                    $siteIDarray ['Total'] = array_merge($siteIDarray ['Total'], $serviceIDarray);                            
                }                

                // Populate array with contents                
                if (count($siteIDs) > 0) 
                {
                    foreach ($siteIDs as $siteID) 
                    {
                        if (count($serviceIDs) > 0)
                        {    
                            foreach ($serviceIDs as $serviceID)
                            {    
                                //get D, R, W and MR of respective serviceID

                                // Check User Mode if TB or UB 
                                $arrSiteIDKeyResult = multidimensional_search($siteIDarray, array('SiteID' => $siteID['SiteID']));
                                $arrServiceIDKeyResult = multidimensional_search($siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'], array('ServiceID' => $serviceID['ServiceID']));
                                $arrUserModeResult = $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UserMode'];
                                
                                $ub_info = 0;
                                $ub_grosshold = 0;
                                $tb_deposit_reload = 0;
                                $tb_withdraw = 0;
                                $tb_mr = 0;
                                $tb_grosshold = 0;

                                if ($arrUserModeResult == 1) // UB
                                {
                                    // for account based (Abbott)
                                    $ub_info        = $orptoptr->getGrossHoldeSAFE($siteID['SiteID'], $datefrom, $dateto);
                                    $ub_grosshold   = $ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal'];
                                    $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH'] = $ub_grosshold;
                                }
                                else // TB
                                {    
                                    // for terminal based (Sapphire/Topaz, Habanero, e-Bingo)
                                    $tb_deposit_reload  = $orptoptr->getGrossHoldTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], "DR", $datefrom, $dateto);
                                    $tb_withdraw        = $orptoptr->getGrossHoldTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], "W", $datefrom, $dateto);
                                    $tb_mr              = $orptoptr->getManualRedemptionTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], $datefrom, $dateto);
                                    $tb_grosshold       = $tb_deposit_reload['Amount'] - ($tb_withdraw['Amount'] + $tb_mr['Amount']);
                                    $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_DR'] = $tb_deposit_reload;
                                    $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_W'] = $tb_withdraw;
                                    $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_MR'] = $tb_mr;
                                    $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'] = $tb_grosshold;
                                }
                                $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['SubTotal_GH'] = 
                                        $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH'] + 
                                        $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'];

                                // for totals
                                $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH']+= 
                                        ($ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal']);
                                $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_DR'] += $tb_deposit_reload['Amount'];
                                $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_W'] += $tb_withdraw['Amount'];
                                $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_MR'] += $tb_mr['Amount'];
                                $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'] += 
                                        ($tb_deposit_reload['Amount'] - $tb_withdraw['Amount'] - $tb_mr['Amount']);
                                $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['SubTotal_GH'] += 
                                        ($ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal'] +
                                        $tb_deposit_reload['Amount'] - $tb_withdraw['Amount'] - $tb_mr['Amount']);                                  
                            }
                        }
                    }

                    $count = 0;
                    $count = count($siteIDarray);
                    if ($count > 0) 
                    {
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
                        //paginate array
                        $trans_details2 = $orptoptr->paginatetransaction($siteIDarray, $start, $limit);
                        $response = new stdClass();
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count; 

                        //display to jqgrid
                        $gh1 = 0;
                        $gh2 = 0;
                        $gh3 = 0;
                        //ADDED CCT 07/11/2018 BEGIN
                        $gh4 = 0;
                        $gh5 = 0;
                        //ADDED CCT 07/11/2018 END
                        $j = 0;                        
                        foreach($trans_details2 as $vview2)
                        {
                            $response->rows[$j]['id']=$vview2['SiteCode'];
                            foreach($vview2['ServiceInfo'] as $servview2)
                            {
                                if ($servview2['ServiceID'] == 22) // RTG
                                {
                                    $gh1 = $servview2['SubTotal_GH'];
                                }
                                elseif ($servview2['ServiceID'] == 25) // Habanero
                                {
                                    $gh2 = $servview2['SubTotal_GH'];
                                }
                                elseif ($servview2['ServiceID'] == 26) // e-Bingo
                                {
                                    $gh3 = $servview2['SubTotal_GH'];
                                }
                                //ADDED CCT 07/11/2018 BEGIN
                                elseif ($servview2['ServiceID'] == 28) // RTG UB
                                {
                                    $gh4 = $servview2['SubTotal_GH'];
                                }
                                elseif ($servview2['ServiceID'] == 29) // Habanero UB
                                {
                                    $gh5 = $servview2['SubTotal_GH'];
                                }
                                //ADDED CCT 07/11/2018 END
                            }
                            
                            if ($vview2['SiteCode'] == 'Total')
                            {
                                // EDITED CCT 07/11/2018 BEGIN
                                //$response->rows[$j]['cell']=array('<b>'.$vview2['SiteCode'].'</b>', '<b>'.number_format($gh1, 2).'</b>', 
                                //                '<b>'.number_format($gh2, 2).'</b>', '<b>'.number_format($gh3, 2).'</b>', 
                                //                '<b>'.number_format($gh1 + $gh2 + $gh3, 2).'</b>');
                                $response->rows[$j]['cell']=array('<b>'.$vview2['SiteCode'].'</b>', '<b>'.number_format($gh1 + $gh4, 2).'</b>', 
                                                '<b>'.number_format($gh2 + $gh5, 2).'</b>', '<b>'.number_format($gh3, 2).'</b>', 
                                                '<b>'.number_format($gh1 + $gh2 + $gh3 + $gh4 + $gh5, 2).'</b>');
                                // EDITED CCT 07/11/2018 END
                            }
                            else
                            {
                                // EDITED CCT 07/11/2018 BEGIN
                                //$response->rows[$j]['cell']=array($vview2['SiteCode'], number_format($gh1, 2), number_format($gh2, 2), 
                                //                number_format($gh3, 2), number_format($gh1 + $gh2 + $gh3, 2));
                                $response->rows[$j]['cell']=array($vview2['SiteCode'], number_format($gh1 + $gh4, 2), number_format($gh2 + $gh5, 2), 
                                                number_format($gh3, 2), number_format($gh1 + $gh2 + $gh3 + $gh4 + $gh5, 2));
                                // EDITED CCT 07/11/2018 END
                            }
                            $j++;
                        }
                    }
                    else 
                    {
                        $j = 0;
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        $msg = "Gross Hold: No returned result";
                        $response->msg = $msg;
                    }
                    echo json_encode($response);
                    $orptoptr->close();
                    exit;
                }
                else 
                {
                    $arr_r = array('ErrorCode' => 1, 'Message' => 'The operator has no site.');
                }
                break;
            // CCT ADDED 02/21/2018 END
                
            case 'DailySiteTransaction':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                $vdatefrom = $_POST['rptDate'];
                $vdateto = date ( 'Y-m-d' , strtotime ($gaddeddate , strtotime($vdatefrom)));
                $vsiteID = $_POST['cmbsitename'];
                $dateFrom = $vdatefrom." ".$vcutofftime;
                $dateTo = $vdateto." ".$vcutofftime;
                $direction = $_POST['sord'];

                $arrsiteID = array(); 
                if($vsiteID == 0)
                {
                    foreach($rsitesowned as $row)
                    {
                        $vsiteID = $row['SiteID'];
                        array_push($arrsiteID, $vsiteID);
                    }   
                }
                else
                {
                    $arrsiteID = array($vsiteID);
                }

                $result = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID);

                if(count($result) > 0)
                {   
                    $totaldeposit = 0;
                    $totalreload = 0;
                    $totalwithdraw = 0;

                    $optrdetails = array();
                    foreach($result as $value) 
                    {
                        $rsitecode = $orptoptr->getsitecode($value['SiteID']);
                        if(!isset($optrdetails[$value['TransactionSummaryID']])) 
                        {
                            $optrdetails[$value['TransactionSummaryID']] = 
                                    array(
                                        'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                        'DateStarted'=>$value['DateStarted'],
                                        'DateEnded'=>$value['DateEnded'],
                                        'DateCreated'=>$value['DateCreated'],
                                        'TerminalID'=>$value['TerminalID'],
                                        'SiteID'=>$value['SiteID'],
                                        'SiteCode'=>$rsitecode['SiteCode'],
                                        'TerminalCode'=>$value['TerminalCode'],
                                        'Withdrawal'=>'0.00',
                                        'Deposit'=>'0.00',
                                        'Reload'=>'0.00'
                                    ); 
                        }
                        $trans = array();
                        switch ($value['TransactionType']) 
                        {
                            case 'W':
                                $trans = array('Withdrawal'=>$value['amount']);
                                break;
                            case 'D':
                                $trans = array('Deposit'=>$value['amount']);
                                break;
                            case 'R':
                                $trans = array('Reload'=>$value['amount']);
                                break;
                        }
                        $optrdetails[$value['TransactionSummaryID']] = array_merge($optrdetails[$value['TransactionSummaryID']], $trans);
                    }

                    $count = 0;
                    $count = count($optrdetails);
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

                    //paginate array
                    $trans_details = $orptoptr->paginatetransaction($optrdetails, $start, $limit);
                    $arrdepositamt = array();
                    $arrreloadamt = array();
                    $arrwithdrawamt = array();
                    $i = 0;
                    $response = new stdClass();
                    $response->page = $page;
                    $response->total = $total_pages;
                    $response->records = $count; 
                    //display to jqgrid
                    foreach($trans_details as $vview)
                    {
                        $rterminalCode = $vview['TerminalCode'];
                        //search first if the sitecode was found in the terminal code
                        if(strstr($rterminalCode, $vview['SiteCode']) == false)
                        {
                            //remove all the letters from terminal code
                            $rterminalCode = ereg_replace("[^0-9]", "", $rterminalCode);
                        }
                        else
                        {
                            //remove the "icsa-[SiteCode]"
                            $rterminalCode = substr($rterminalCode, strlen($vview['SiteCode']));
                        }

                        $vdeposit = $vview['Deposit'];
                        $vreload = $vview['Reload'];
                        $vwithdraw = $vview['Withdrawal'];
                        $response->rows[$i]['id']=$vview['TransactionSummaryID'];
                        $response->rows[$i]['cell']=array($vview['SiteCode'], $rterminalCode, 
                        number_format($vdeposit, 2), number_format($vreload, 2), number_format($vwithdraw, 2),
                        $vview['DateStarted'], $vview['DateEnded']);
                        $i++;

                        //store the 3 transaction types in an array
                        array_push($arrdepositamt, $vdeposit);
                        array_push($arrreloadamt, $vreload);
                        array_push($arrwithdrawamt, $vwithdraw);

                        $_SESSION['siteid1'] = $_POST['cmbsitename'];
                    }

                    // Get the sum of all  transaction types
                    $totaldeposit = array_sum($arrdepositamt); 
                    $totalreload = array_sum($arrreloadamt); 
                    $totalwithdraw = array_sum($arrwithdrawamt);

                    unset($arrdepositamt, $arrreloadamt, $arrwithdrawamt, $optrdetails, $trans_details);
                    //session variable to store transaction types in an array; to used on ajax call later on this program
                    $_SESSION['total'] = array("TotalDeposit" => $totaldeposit, 
                    "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);
                }
                else
                {
                    $i = 0;
                    $response = new stdClass();
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Site Transaction: No Results Found";
                    $response->msg = $msg;
                    unset($_SESSION['total']);
                }

                echo json_encode($response);

                unset($arrsiteID);
                unset($arrdeposit);
                unset($arrreload);
                unset($arrwithdraw);
                $orptoptr->close();
                exit;
                break;
                
            case 'DailySiteTransaction2':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                $vdatefrom = $_POST['rptDate'];
                $vdateto = date ( 'Y-m-d' , strtotime ($gaddeddate , strtotime($vdatefrom)));
                $vsiteID = $_POST['cmbsitename'];
                $dateFrom = $vdatefrom." ".$vcutofftime;
                $dateTo = $vdateto." ".$vcutofftime;
                $direction = $_POST['sord'];

                $arrsiteID = array(); 
                if($vsiteID == 0)
                {
                    foreach($rsitesowned as $row)
                    {
                        $vsiteID = $row['SiteID'];
                        array_push($arrsiteID, $vsiteID);
                    }   
                }
                else
                {
                    $arrsiteID = array($vsiteID);
                }

                $result2 = $orptoptr->viewewtransactionperday($dateFrom, $dateTo, $arrsiteID);

                if(count($result2) > 0)
                {
                    $totalewloads = 0;
                    $totalewwithdrawals = 0;

                    $optrdetails2 = array();
                    foreach($result2 as $value2) 
                    {                
                        $rsitecode = $orptoptr->getsitecode($value2['SiteID']);
                        if(!isset($optrdetails2[$value2['SiteID']])) 
                        {
                            $optrdetails2[$value2['EwalletTransID']] = 
                                    array(
                                        'EwalletTransID'=>$value2['EwalletTransID'],
                                        'SiteID'=>$value2['SiteID'],
                                        'SiteCode'=>$rsitecode['SiteCode'],
                                        'LoyaltyCardNumber'=>$value2['LoyaltyCardNumber'],
                                        'StartDate'=>$value2['StartDate'],
                                        'EndDate'=>$value2['EndDate'],
                                        'EWLoads'=>$value2['EWLoads'],
                                        'EWWithdrawals'=>$value2['EWWithdrawals']
                                    ); 
                        }
                        $trans2 = array();
                        switch ($value2['TransType']) 
                        {
                            case 'W':
                                $trans2 = array('EWWithdrawals'=>$value2['EWWithdrawals']);
                                break;
                            case 'D':
                            $trans2 = array('EWLoads'=>$value2['EWLoads']);
                            break;
                        }
                        $optrdetails2[$value2['EwalletTransID']] = array_merge($optrdetails2[$value2['EwalletTransID']], $trans2);
                    }

                    $count = 0;
                    $count = count($optrdetails2);
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

                    //paginate array
                    $trans_details2 = $orptoptr->paginatetransaction($optrdetails2, $start, $limit);

                    $arrewloadsamt = array();
                    $arrewwithdrawalsamt = array();
                    $j = 0;
                    $response = new stdClass();
                    $response->page = $page;
                    $response->total = $total_pages;
                    $response->records = $count; 
                    //display to jqgrid
                    foreach($trans_details2 as $vview2)
                    {
                        $vewloads = $vview2['EWLoads'];
                        $vewwithdrawals = $vview2['EWWithdrawals'];
                        $response->rows[$j]['id']=$vview2['EwalletTransID'];
                        $response->rows[$j]['cell']=array($vview2['SiteCode'], $vview2['LoyaltyCardNumber'],
                        number_format($vewloads, 2), number_format($vewwithdrawals, 2),
                        $vview2['StartDate'], $vview2['EndDate']);
                        $j++;
                        //                    //store the 2 transaction types in an array
                        array_push($arrewloadsamt, $vewloads);
                        array_push($arrewwithdrawalsamt, $vewwithdrawals);
                    }

                    $_SESSION['siteid2'] = $_POST['cmbsitename'];

                    $arrwithdraw = array();
                    $arrreload = array();
                    $arrdeposit = array();
                    $result = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID);
                    $ctr2 = 0;
                    while($ctr2 < count($result))
                    {
                        $trans = array();
                        switch ($result[$ctr2]['TransactionType']) 
                        {
                            case 'W':
                                $trans = array('Withdrawal'=>$result[$ctr2]['amount']);
                                array_push($arrwithdraw, $trans);
                                break;
                            case 'D':
                                $trans = array('Deposit'=>$result[$ctr2]['amount']);
                                array_push($arrdeposit, $trans);
                                break;
                            case 'R':
                                $trans = array('Reload'=>$result[$ctr2]['amount']);
                                array_push($arrreload, $trans);
                                break;
                        }
                        $ctr2++;
                    }

                    //                 // Get the sum of all  transaction types
                    $sales = array_sum($arrdeposit)+array_sum($arrreload)+array_sum($arrewloadsamt);
                    $redemption = array_sum($arrwithdraw)+array_sum($arrewwithdrawalsamt);
                    $ticketencashment = $orptoptr->getTotalTicketEncashment($dateFrom, $dateTo, $arrsiteID);
                    $cashonhand = $sales-$redemption-$ticketencashment;

                    unset($arrewloadsamt, $arrewwithdrawalsamt, $optrdetails2, $trans_details2);
                    //session variable to store transaction types in an array; to used on ajax call later on this program
                    $_SESSION['total2'] = array("Sales" => $sales, 
                    "Redemption" => $redemption, "TicketEncashment" => $ticketencashment, "CashOnHand" => $cashonhand);
                }
                else
                {
                    $j = 0;
                    $response = new stdClass();
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg2 = "Site Transaction: No Results Found";
                    $response->msg = $msg2;
                    unset($_SESSION['total2']);                 
                }

                echo json_encode($response);

                unset($arrsiteID);
                unset($arrewloadsamt);
                unset($arrewwithdrawalsamt);
                $orptoptr->close();
                exit;
                break;
                
            case 'BCFPerSite':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $search = $_POST['_search'];
                $direction = $_POST['sord'];
                if(isset ($_POST['searchField']) && isset($_POST['searchString']))
                {
                    $searchField = $_POST['searchField'];
                    $searchString = $_POST['searchString'];
                    echo $searchField;
                    echo $searchString;
                }  

                //for sorting
                if($_POST['sidx'] != "")
                {
                    $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SiteID";
                }

                $rctrsite = $orptoptr->countbcfpersite($rsitesowned);
                $count = $rctrsite['ctrbcf'];

                //this is for computing the limit
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

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                    $result = $orptoptr->viewbcfpersite($rsitesowned, $start, $limit);
                }
                else
                {
                    $result = 0; 
                }

                if($result > 0)
                {
                    $i = 0;
                    $response->page = $page;
                    $response->total = $total_pages;
                    $response->records = $count;
                    $arrbalance = array();
                    $totalbalance = 0;

                    foreach($result as $vview) 
                    {
                        $rsiteID = $vview['SiteID'];
                        if($vview['TopUpType'] == 0)
                        {
                            $topup = "Fixed";
                        }
                        else
                        {
                            $topup = "Variable";
                        }
                        if($vview['PickUpTag'] == 0)
                        {
                            $pickup = "Provincial";
                        }
                        else
                        {
                            $pickup = "Metro Manila";
                        }

                        $isterminalcode = strstr($vview['SiteCode'], $terminalcode);
                        if($isterminalcode == false)
                        {
                            $vcode= $vview['SiteCode'];
                        }
                        else
                        {
                            $vcode = substr($vview['SiteCode'], strlen($terminalcode));
                        }

                        $response->rows[$i]['id'] = $rsiteID;
                        $response->rows[$i]['cell']=array($vview['POS'], $vcode, $vview['SiteName'],  
                        $vview['LastTransactionDate'], $topup, 
                        $pickup, number_format($vview['MinBalance'], 2, '.', ','), 
                        number_format($vview['MaxBalance'], 2, '.', ','), 
                        number_format($vview['Balance'],2,'.',','));
                        $i++;

                        array_push($arrbalance, $vview['Balance']);
                    }
                    $totalbalance = array_sum($arrbalance);

                    $_SESSION['total'] = array("TotalBalance" => $totalbalance);
                    unset($arrbalance);
                }
                else
                {
                    $i = 0;
                    unset($_SESSION['total']);
                    $response->page = $page;
                    $response->total = $total_pages;
                    $response->records = $count;
                    $msg = "BCF Per Site: No returned result";
                    $response->msg = $msg;
                }
                echo json_encode($response);
                $orptoptr->close();
                exit;
                break;

            case "RptGrossHold":
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction

                $datefrom = $_POST['date'];
                $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
                //get site operator's sites
                $aid = $_SESSION['accID'];
                $siteIDs = $orptoptr->getSiteByAID($aid);
                //get site grossholds
                $result = array();
                $total = 0;
                $subtotal = 0;
                $costello_total = 0;
                $abbott_total = 0;
                if (count($siteIDs) > 0) 
                {
                    foreach ($siteIDs as $siteID) 
                    {
                        //get D, R, W and MR of respective casinos
                        $siteCode = $orptoptr->getsitecode($siteID['SiteID']);
                        //for Costello
                        // EDITED CCT 02/08/2018 BEGIN
                        //$serviceID_COSTELLO       = array(1);
                        //$serviceID_COSTELLO       = array(1, 6); // Added Habanero
                        // EDITED 07/11/2018 BEGIN
                        $serviceID_COSTELLO       = array(1, 4, 6); // Added RTG UB
                        // EDITED 07/11/2018 END
                        // EDITED CCT 02/08/2018 END
                        $deposit_reload_COSTELLO  = $orptoptr->getGrossHoldTB($siteID['SiteID'], $serviceID_COSTELLO, "DR", $datefrom, $dateto);
                        $withdraw_COSTELLO        = $orptoptr->getGrossHoldTB($siteID['SiteID'], $serviceID_COSTELLO, "W", $datefrom, $dateto);
                        $mr_COSTELLO              = $orptoptr->getManualRedemptionTrans($siteID['SiteID'], $serviceID_COSTELLO, $datefrom, $dateto);
                        $grosshold_COSTELLO       = $deposit_reload_COSTELLO['Amount'] - ($withdraw_COSTELLO['Amount'] + $mr_COSTELLO['Amount']);
                        //for abbott
                        $loads = $orptoptr->getGrossHoldeSAFE($siteID['SiteID'], $datefrom, $dateto);

                        $gh = $loads['StartBalance']+$loads['WalletReloads'] - $loads['EndBalance'] - $loads['GenesisWithdrawal'];
                        // EDITED CCT 02/06/2018 BEGIN
                        //$subtotal = $grosshold_MG + $grosshold_COSTELLO + $gh;
                        $subtotal = $grosshold_COSTELLO + $gh;
                        // EDITED CCT 02/06/2018 END
                        $results[] = 
                            array(
                                'SubTotal' => number_format($subtotal, 2, ".", ","), 
                                'SiteCode' => trim(str_replace("ICSA-", "", $siteCode['SiteCode']))
                            );
                        $total += $subtotal;
                        $abbott_total += $loads['GrossHold'];
                    }

                    $count = 0;
                    $count = count($results);
                    if ($count > 0) 
                    {
                        $results[] = array('SiteCode' => '<b>Total</b>', 
                        'SubTotal' => "<b>".number_format($total, 2, ".", ",")."</b>");
                        $count=$count+1;
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
                        //paginate array
                        $trans_details2 = $orptoptr->paginatetransaction($results, $start, $limit);
                        $arrewloadsamt = array();
                        $arrewwithdrawalsamt = array();
                        $j = 0;
                        $response = new stdClass();
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count; 
                        //display to jqgrid
                        foreach($trans_details2 as $vview2)
                        {
                            $response->rows[$j]['id']=$vview2['SiteCode'];
                            $response->rows[$j]['cell']=array($vview2['SiteCode'], $vview2['SubTotal']);
                            $j++;
                        }
                    }
                    else 
                    {
                        $j = 0;
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        $msg = "Gross Hold: No returned result";
                        $response->msg = $msg;
                    }

                    echo json_encode($response);
                    $orptoptr->close();
                    exit;
                }
                else 
                {
                    $arr_r = array('ErrorCode' => 1, 'Message' => 'The operator has no site.');
                }
                break;
                
            //disable or halt process if backtracks to less or equal to set date 
            case "CheckGrossHoldDate":
                $date = $_POST['date'];
                if (strtotime($date) > strtotime($deploymentDate)) 
                {
                    echo 0;
                }
                else 
                {
                    echo 1;
                }
                break;
        }
    }
    elseif(isset($_POST['page2']))
    {
        $vpage2 = $_POST['page2'];
        switch($vpage2)
        {
            //for operator standalone terminal monitoring; pass siteid and sitecode to cashier terminal monitoring
            case 'StandaloneMonitoring':
                $vsiteID = $_POST['siteid'];
                $vsitecode = $terminalcode.$_POST['sitecode'];
                $vpath = $orptoptr->getpath($_SESSION['acctype'], 14); //pass menuID 14(Terminal Monitoring)
                $vrealpath = $vpath."&siteid=".$vsiteID."&sitecode=".$vsitecode;
                echo $vrealpath;
                exit;
                break;
        }
    }
    //on ajax call from rptsitetransaction.php
    elseif(isset($_POST['gettotal']) == "GetTotals")
    {
        $grandsales = 0.00;
        $grandredemption = 0.00;
        $grandticketencashment = 0.00;
        $grandcashonhand = 0.00;
        $arrewloads = array();
        $arrewwithdrawals = array();
        $arrdeposit = array();
        $arrwithdraw = array();
        $arrreload = array();

        $vdatefrom = $_POST['rptDate'];
        $vdateto = date ( 'Y-m-d' , strtotime ($gaddeddate, strtotime($vdatefrom)));
        $vsiteID = $_POST['cmbsitename'];
        $dateFrom = $vdatefrom." ".$vcutofftime;
        $dateTo = $vdateto." ".$vcutofftime;

        $arrsiteID = array(); 
        if($vsiteID == 0)
        {
            foreach($rsitesowned as $row)
            {
                $vsiteID = $row['SiteID'];
                array_push($arrsiteID, $vsiteID);
            }   
        }
        else
        {
            $arrsiteID = array($vsiteID);
        }

        $result2 = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID);

        if($result2) 
        {
            $ctr2 = 0;
            while($ctr2 < count($result2))
            {
                $trans = array();
                switch ($result2[$ctr2]['TransactionType']) 
                {
                    case 'W':
                        $trans = array('Withdrawal'=>(float)$result2[$ctr2]['amount']);
                        array_push($arrwithdraw, $trans);
                        break;
                    case 'D':
                        $trans = array('Deposit'=>(float)$result2[$ctr2]['amount']);
                        array_push($arrdeposit, $trans);
                        break;
                    case 'R':
                        $trans = array('Reload'=>(float)$result2[$ctr2]['amount']);
                        array_push($arrreload, $trans);
                        break;
                }
                $ctr2++;
            }
        } 
        else 
        {
            $grandsum = 0.00;
        }

        //used this method to get the grand total of all tranction types
        $result = $orptoptr->viewewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit=null, $sort = "StartDate", $direction = "DESC");
        if($result) 
        {
            $ctr1 = 0;
            while($ctr1 < count($result))
            {
                switch($result[$ctr1]['TransType'])
                {
                    case 'D' :
                        array_push($arrewloads, (float)$result[$ctr1]['EWLoads']);
                        break;
                    case 'W':
                        array_push($arrewwithdrawals, (float)$result[$ctr1]['EWWithdrawals']);
                        break;
                }
                $ctr1++;
            }
        }
        else 
        {
            $grandsumew = 0.00;
        }

        if($result && $result2)
        {
            if($arrdeposit)
            {
                foreach($arrdeposit as $arrdepositsingle) 
                {
                    $arrdepositsum[] = (float)$arrdepositsingle['Deposit'];
                }
            }
            else 
            {
                $arrdepositsum = array('Deposit' => 0.00);
            }

            if($arrreload) 
            {
                foreach($arrreload as $arrreloadsingle) 
                {
                    $arrreloadsum[] = (float)$arrreloadsingle['Reload'];
                }
            }
            else 
            {
                $arrreloadsum = array('Reload' => 0.00);
            }

            if($arrwithdraw) 
            {
                foreach($arrwithdraw as $arrwithdrawsingle) 
                {
                    $arrwithdrawsum[] = (float)$arrwithdrawsingle['Withdrawal'];
                }
            }
            else 
            {
                $arrwithdrawsum = array('Withdrawal' => 0.00);
            }

            if($arrewloads) 
            {
                $arrewloadssum = array_sum($arrewloads);
            }
            else 
            {
                $arrewloadssum = 0.00;
            }

            if($arrewwithdrawals) 
            {
                $arrewwithdrawalssum = array_sum($arrewwithdrawals);
            }
            else 
            {
                $arrewwithdrawalssum = 0.00;
            }

            $grandsales = $arrewloadssum+array_sum($arrdepositsum)+array_sum($arrreloadsum);
            $grandredemption = $arrewwithdrawalssum+array_sum($arrwithdrawsum);
        }
        else if($result && !$result2) 
        {
            if($arrewloads) 
            {
                $arrewloadssum = array_sum($arrewloads);
            }
            else 
            {
                $arrewloadssum = 0.00;
            }

            if($arrewwithdrawals) 
            {
                $arrewwithdrawalssum = array_sum($arrewwithdrawals);
            }
            else 
            {
                $arrewwithdrawalssum = 0.00;
            }

            $grandsales = $arrewloadssum+$grandsum;
            $grandredemption = $arrewwithdrawalssum+$grandsum;
        }
        else if(!$result && $result2) 
        {
            if($arrdeposit)
            {
                foreach($arrdeposit as $arrdepositsingle) 
                {
                    $arrdepositsum[] = (float)$arrdepositsingle['Deposit'];
                }
            }
            else 
            {
                $arrdepositsum = array('Deposit' => 0.00);
            }

            if($arrreload) 
            {
                foreach($arrreload as $arrreloadsingle) 
                {
                    $arrreloadsum[] = (float)$arrreloadsingle['Reload'];
                }
            }
            else 
            {
                $arrreloadsum = array('Reload' => 0.00);
            }

            if($arrwithdraw) 
            {
                foreach($arrwithdraw as $arrwithdrawsingle) 
                {
                    $arrwithdrawsum[] = (float)$arrwithdrawsingle['Withdrawal'];
                }
            }
            else 
            {
                $arrwithdrawsum = array('Withdrawal' => 0.00);
            }

            $grandsales = $grandsumew+array_sum($arrdepositsum)+array_sum($arrreloadsum);
            $grandredemption = $grandsumew+array_sum($arrwithdrawsum);
        }
        else 
        {
            $grandsales = 0.00;
            $grandredemption = 0.00;
        }

        //Compute for total Cash On Hand of the sites under the current operator
        $cohdata = $orptoptr->getCashOnHandDetails($dateFrom, $dateTo, $arrsiteID);
        $esafeCash = $cohdata['TotalCashLoadEwallet']; 
        if ($dateFrom < $deploymentDate) 
        {
            $grandticketencashment = $orptoptr->getEncashedTicketsV15($arrsiteID, $dateFrom, $dateTo);
            $ewalletWithdraw = $cohdata['TotalGenesisRedemption'] + $cohdata['TotalEwalletRedemption'];
            $grandcashonhand = (($cohdata['TotalCashLoad'] + $cohdata['TotalCouponLoad']+ $esafeCash + $cohdata['TotalEwalletCoupon'])-($cohdata['TotalCashRedemption']+$ewalletWithdraw)-$grandticketencashment)-$cohdata['TotalMR'];
        }
        else 
        {
            $grandticketencashment = $orptoptr->getEncashedTicketsV15($arrsiteID, $dateFrom, $dateTo);
            $ewalletWithdraw = $cohdata['TotalGenesisRedemption'] + $cohdata['TotalEwalletRedemption'];
            $grandcashonhand = (($cohdata['TotalCashLoad'] + $cohdata['TotalTicketLoadGenesis'] + $cohdata['TotalCouponLoad'] + $cohdata['TotalCashLoadEwallet'] + $cohdata['TotalEsafeTicketLoadGenesis'] + $cohdata['TotalEwalletCoupon'])-($cohdata['TotalRedemption'] + $ewalletWithdraw)-$grandticketencashment)- $cohdata['TotalMR'];
            $grandredemption += $grandticketencashment;
        }

        $grandredemption += $cohdata['TotalMR'];
        // store the grand total of transaction types into an array 
        $arrgrand = array("GrandSales" => $grandsales, "GrandRedemption" => $grandredemption,
        "GrandTicketEncashment" => $grandticketencashment, "GrandCashOnHand" => $grandcashonhand);

        //results will be fetch here:
        /**** Get Total Per Page  *****/
        $vtotal = new stdClass();
        /**** GET Total Page Summary ******/
        $vtotal->grandredemption = number_format($arrgrand['GrandRedemption'], 2, '.', ',');
        $vtotal->grandticketencashment = number_format($arrgrand['GrandTicketEncashment'], 2, '.', ',');
        $vtotal->grandcashonhand = number_format($arrgrand["GrandCashOnHand"], 2, '.', ',');
        $vtotal->grandsales = number_format($arrgrand["GrandSales"], 2, '.', ',');
        $vtotal->grandMR = number_format($cohdata['TotalMR'], 2, '.', ',');

        echo json_encode($vtotal); 

        unset($arrgrand);
        $orptoptr->close();
        exit;
    }
    
    /**
    *Added on July 3, 2012
    * For Active Session And Terminal Balance Per Site
    */
    else if(isset($_POST["ActiveSession"])) 
    {
        $siteID = strip_tags($_POST["siteID"]);
        $actionType = $_POST["ActiveSessionAction"];
        $data = "";

        switch($actionType) 
        {
            case "sessioncount":
                $data = $orptoptr->getActiveSessionCount($siteID, $cardnumber = '');
                break;

            case "sessioncountter":
                $usermode = 0;
                $data1 = $orptoptr->getActiveSessionCountMod($cardnumber = '', $usermode, $siteID);
                $usermode = 2;
                $data2 = $orptoptr->getActiveSessionCountMod($cardnumber = '', $usermode, $siteID);
                $usermode = 4;
                $data3 = $orptoptr->getActiveSessionCountMod($cardnumber = '', $usermode, $siteID);
                $data = $data1 + $data2 + $data3;
                break;

            case "sessioncountub":
                $usermode = 1;
                // CCT EDITED 07/11/2018 BEGIN
                //$data = $orptoptr->getActiveSessionCountMod($cardnumber = '', $usermode, $siteID);
                $data1 = $orptoptr->getActiveSessionCountMod($cardnumber = '', $usermode, $siteID);
                // CCT ADDED 07/11/2018 BEGIN
                $usermode = 3;
                $data2 = $orptoptr->getActiveSessionCountMod($cardnumber = '', $usermode, $siteID);
                // CCT ADDED 07/11/2018 END
                $data = $data1 + $data2;
                // CCT EDITED 07/11/2018 END
                break;

            case "sessioncount1":
                $cardnumber = $_POST["txtcardnumber"];
                $data = $orptoptr->getActiveSessionCount($siteID = '', $cardnumber);
                break;

            case "sessioncountter1":
                $cardnumber = $_POST["txtcardnumber"];
                $usermode = 0;
                $data1 = $orptoptr->getActiveSessionCountMod($cardnumber, $usermode, $siteID = '');
                $usermode = 2;
                $data2 = $orptoptr->getActiveSessionCountMod($cardnumber, $usermode, $siteID = '');
                $usermode = 4;
                $data3 = $orptoptr->getActiveSessionCountMod($cardnumber, $usermode, $siteID = '');
                $data = $data1 + $data2 + $data3;
                break;

            case "sessioncountub1":
                $cardnumber = $_POST["txtcardnumber"];
                $usermode = 1;
                // EDITED CCT 07/11/2018 BEGIN
                //$data = $orptoptr->getActiveSessionCountMod($cardnumber, $usermode, $siteID = '');
                $data1 = $orptoptr->getActiveSessionCountMod($cardnumber, $usermode, $siteID = '');
                // EDITED CCT 07/11/2018 END
                // ADDED CCT 07/11/2018 BEGIN
                $usermode = 3;
                $data2 = $orptoptr->getActiveSessionCountMod($cardnumber, $usermode, $siteID = '');
                $data = $data1 + $data2;
                // ADDED CCT 07/11/2018 END
                break;

            case "sessionrecord": 
                $data = $orptoptr->getActiveSessionPlayingBalance($cardinfo, $siteID, $_ServiceAPI, 
                    $_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey,
                    $_HABbrandID, $_HABapiKey);
                break;
            
            case "pagcorsessionrecord":
                $orptoptr2->open(); 
                $data = $orptoptr->getPagcorActiveSessionPlayingBalance($cardinfo, $siteID, $_ServiceAPI, 
                    $_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey, 
                    $_HABbrandID, $_HABapiKey);
                break;
            
            //for user based report
            case "sessionrecordub":
                $cardnumber = $_POST["txtcardnumber"];
                $data = $orptoptr->getActiveSessionPlayingBalanceub($cardinfo, $cardnumber, $_SESSION['ServiceUserName'], $_ServiceAPI, 
                    $_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey, 
                    $_HABbrandID, $_HABapiKey);
                break;

            //for user based report
            case "pagcorsessionrecordub":
                $orptoptr2->open(); 
                $cardnumber = $_POST["txtcardnumber"];
                $data = $orptoptr->getPagcorActiveSessionPlayingBalanceub($cardinfo, $cardnumber, $_SESSION['ServiceUserName'], $_ServiceAPI, 
                    $_CAPIUsername, $_CAPIPassword, $_CAPIPlayerName, $_MicrogamingCurrency, $_ptcasinoname,$_PlayerAPI,$_ptsecretkey, 
                    $_HABbrandID, $_HABapiKey);
                break;
        }

        echo $data;
        unset($data);
    }
    //get membersihp card details
    else if(isset($_POST["pageub"]) == 'GetLoyaltyCard') 
    {
        $cardnumber = $_POST['txtcardnumber'];

        if(strlen($cardnumber) > 0) 
        {
            $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
            $obj_result = json_decode($loyaltyResult);
            $statuscode = $obj_result->CardInfo->StatusCode;

            if(!is_null($statuscode) ||$statuscode == '')
            {
                if($statuscode == 1 || $statuscode == 5 || $statuscode == 9)
                {
                    $casinoarray_count = count($obj_result->CardInfo->CasinoArray);
                    if($casinoarray_count != 0)
                    {
                        for($ctr = 0; $ctr < $casinoarray_count;$ctr++) 
                        {
                            $servicename = $orptoptr->getServiceName($obj_result->CardInfo->CasinoArray[$ctr]->ServiceID);
                            $casinoinfo[$ctr] = 
                                array(
                                    'UserName'  => $obj_result->CardInfo->MemberName,
                                    'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                    'Email'  => $obj_result->CardInfo->Email,
                                    'Birthdate' => $obj_result->CardInfo->Birthdate,
                                    'Casino' => $servicename,
                                    'CardNumber' => $obj_result->CardInfo->CardNumber,
                                    'Login' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                                    'StatusCode' => $obj_result->CardInfo->StatusCode,
                                );
                            $_SESSION['ServiceUserName'] = $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername;
                            $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                        }
                        echo json_encode($casinoinfo);
                    }
                    else
                    {
                        // EDITED CCT 02/20/2018 BEGIN
                        // Revised output when there is no Account-Based record for the player
                        //$services = "Active Session and Terminal Balance: Casino is empty";
                        //echo "$services";
                        $casinoinfo [0] = array('UserName' => $obj_result->CardInfo->MemberName,
                                    'MobileNumber' => $obj_result->CardInfo->MobileNumber,
                                    'Email' => $obj_result->CardInfo->Email,
                                    'Birthdate' => $obj_result->CardInfo->Birthdate,
                                    'Casino' => '',
                                    'Login' => '',
                                    'CardNumber' => $cardnumber,
                                    'StatusCode' => $obj_result->CardInfo->StatusCode,);
                        $_SESSION['ServiceUserName'] = '';
                        $_SESSION['MID'] = $obj_result->CardInfo->MemberID;  
                        echo json_encode($casinoinfo);
                        // EDITED CCT 02/20/2018 END                        
                    }
                }
                else
                {  
                    //check membership card status
                    $statusmsg = $orptoptr->membershipcardStatus($statuscode);
                    $services = "Active Session and Terminal Balance: ".$statusmsg;
                    echo "$services";
                }                        
            }
            else
            {
                $statuscode = 100;
                //check membership card status
                $statusmsg = $orptoptr->membershipcardStatus($statuscode);
                $services = "Active Session and Terminal Balance: ".$statusmsg;
                echo "$services";
            }
        }
        else 
        {
            echo "Active Session and Terminal Balance: Invalid input detected.";
        }
    }
    /***************************** EXPORTING EXCEL STARTS HERE *******************************/
    // CCT ADDED 02/23/2018 BEGIN
    else if(isset($_GET['excel4']) == "grossholdexcelperprovider") 
    {
        $datefrom = $_GET['date'];
        $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
        $fn = "Gross_Hold_per_Provider_".date('Y_m_d').".xls";
        
        //setting up excel
        $excel_obj = new ExportExcel("$fn");
        $rptheader = array('Gross Hold per Provider Report from '.$datefrom.' to '.$dateto,"" );
        $array_headers = array('Site / PEGS Code', 'RTG', 'Habanero', 'e-Bingo', 'Total Gross Hold');  
        $completeexcelvalues = array();
        array_push($completeexcelvalues,array('','','','','')); // for Space between Header
        array_push($completeexcelvalues, $array_headers);

        //get site operator's sites
        $aid = $_SESSION['accID'];
        $siteIDs = $orptoptr->getSiteByAID($aid);

        //get service ids with transaction for said date
        //EDITED CCT 07/11/2018 BEGIN
        //$serviceGrpIDs = array(1, 6, 7);
        $serviceGrpIDs = array(1, 4, 6, 7);
        //EDITED CCT 07/11/2018 END
        $serviceIDs = $orptoptr->getServiceIDwithTransactions($serviceGrpIDs, $siteIDs, $datefrom, $dateto);

        // Prepare array structure
        $siteIDarray = array();
        if (count($siteIDs) > 0) 
        {
            foreach ($siteIDs as $siteID) 
            {
                $siteIDarray [$siteID['SiteID']] =
                    array('SiteID' => $siteID['SiteID'], 'SiteCode' => trim(str_replace("ICSA-", "", $siteID['SiteCode'])),);

                if (count($serviceIDs) > 0)
                {
                    $serviceIDarray ['ServiceInfo'] = array();                        
                    foreach ($serviceIDs as $serviceID)
                    { 
                        $tempserviceIDarray [$serviceID['ServiceID']] = array();
                        $tempserviceIDarray = 
                            array(
                                'ServiceID'     => $serviceID['ServiceID'],
                                'ServiceName'   => $serviceID['ServiceName'],
                                'UserMode'      => $serviceID['UserMode'],
                                'TB_DR'         => 0,
                                'TB_W'          => 0,
                                'TB_MR'         => 0,
                                'TB_GH'         => 0,
                                'UB_GH'         => 0,
                                'SubTotal_GH'   => 0
                            );
                        $resultarr = array_push($serviceIDarray ['ServiceInfo'], $tempserviceIDarray);
                    }
                    $siteIDarray[$siteID['SiteID']] = array_merge($siteIDarray[$siteID['SiteID']], $serviceIDarray);                            
                }
            }
        }

        // Create additional total line entry
        $siteIDarray ['Total'] = array('SiteID' => 'Total', 'SiteCode' => 'Total',);

        if (count($serviceIDs) > 0)
        {
            $serviceIDarray ['ServiceInfo'] = array();                        
            foreach ($serviceIDs as $serviceID)
            { 
                $tempserviceIDarray [$serviceID['ServiceID']] = array();
                $tempserviceIDarray = 

                    array(
                        'ServiceID'     => $serviceID['ServiceID'],
                        'ServiceName'   => $serviceID['ServiceName'],
                        'UserMode'      => $serviceID['UserMode'],
                        'TB_DR'         => 0,
                        'TB_W'          => 0,
                        'TB_MR'         => 0,
                        'TB_GH'         => 0,
                        'UB_GH'         => 0,
                        'SubTotal_GH'   => 0
                    );
                $resultarr = array_push($serviceIDarray ['ServiceInfo'], $tempserviceIDarray);
            }
            $siteIDarray ['Total'] = array_merge($siteIDarray ['Total'], $serviceIDarray);                            
        }                

        // Populate array with contents                
        if (count($siteIDs) > 0) 
        {
            foreach ($siteIDs as $siteID) 
            {
                if (count($serviceIDs) > 0)
                {    
                    foreach ($serviceIDs as $serviceID)
                    {    
                        //get D, R, W and MR of respective serviceID

                        // Check User Mode if TB or UB 
                        $arrSiteIDKeyResult = multidimensional_search($siteIDarray, array('SiteID' => $siteID['SiteID']));
                        $arrServiceIDKeyResult = multidimensional_search($siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'], array('ServiceID' => $serviceID['ServiceID']));
                        $arrUserModeResult = $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UserMode'];

                        $ub_info = 0;
                        $ub_grosshold = 0;
                        $tb_deposit_reload = 0;
                        $tb_withdraw = 0;
                        $tb_mr = 0;
                        $tb_grosshold = 0;

                        if ($arrUserModeResult == 1) // UB
                        {
                            // for account based (Abbott)
                            $ub_info        = $orptoptr->getGrossHoldeSAFE($siteID['SiteID'], $datefrom, $dateto);
                            $ub_grosshold   = $ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal'];
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH'] = $ub_grosshold;
                        }
                        else // TB
                        {    
                            // for terminal based (Sapphire/Topaz, Habanero, e-Bingo)
                            $tb_deposit_reload  = $orptoptr->getGrossHoldTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], "DR", $datefrom, $dateto);
                            $tb_withdraw        = $orptoptr->getGrossHoldTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], "W", $datefrom, $dateto);
                            $tb_mr              = $orptoptr->getManualRedemptionTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], $datefrom, $dateto);
                            $tb_grosshold       = $tb_deposit_reload['Amount'] - ($tb_withdraw['Amount'] + $tb_mr['Amount']);
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_DR'] = $tb_deposit_reload;
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_W'] = $tb_withdraw;
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_MR'] = $tb_mr;
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'] = $tb_grosshold;
                        }
                        $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['SubTotal_GH'] = 
                                $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH'] + 
                                $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'];

                        // for totals
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH']+= 
                                ($ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal']);
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_DR'] += $tb_deposit_reload['Amount'];
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_W'] += $tb_withdraw['Amount'];
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_MR'] += $tb_mr['Amount'];
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'] += 
                                ($tb_deposit_reload['Amount'] - $tb_withdraw['Amount'] - $tb_mr['Amount']);
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['SubTotal_GH'] += 
                                ($ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal'] +
                                $tb_deposit_reload['Amount'] - $tb_withdraw['Amount'] - $tb_mr['Amount']);                                  
                    }
                }
            }

            // Prepare array for excel file contents
            $gh1 = 0;
            $gh2 = 0;
            $gh3 = 0;
            // ADDED CCT 07/11/2018 BEGIN
            $gh4 = 0;
            $gh5 = 0;
            // ADDED CCT 07/11/2018 END
            $rrecord = array();
            
            foreach($siteIDarray as $sites)
            {
                foreach($sites['ServiceInfo'] as $serviceprov)
                {
                    if ($serviceprov['ServiceID'] == 22) // RTG
                    {
                        $gh1 = $serviceprov['SubTotal_GH'];
                    }
                    elseif ($serviceprov['ServiceID'] == 25) // Habanero
                    {
                        $gh2 = $serviceprov['SubTotal_GH'];
                    }
                    elseif ($serviceprov['ServiceID'] == 26) // e-Bingo
                    {
                        $gh3 = $serviceprov['SubTotal_GH'];
                    }
                    // ADDED CCT 07/11/2018 BEGIN
                    elseif ($serviceprov['ServiceID'] == 28) // RTG UB
                    {
                        $gh4 = $serviceprov['SubTotal_GH'];
                    }
                    elseif ($serviceprov['ServiceID'] == 29) // Habanero UB
                    {
                        $gh5 = $serviceprov['SubTotal_GH'];
                    }
                    // ADDED CCT 07/11/2018 END
                }
                
                $rrecord = array(   0 => $sites['SiteCode'], 
                                    // EDITED CCT 07/11/2018 BEGIN
                                    //1 => number_format($gh1, 2),
                                    //2 => number_format($gh2, 2),
                                    1 => number_format($gh1 + $gh4, 2),
                                    2 => number_format($gh2 + $gh5, 2),
                                    3 => number_format($gh3, 2),
                                    //4 => number_format($gh1 + $gh2 + $gh3, 2),
                                    4 => number_format($gh1 + $gh2 + $gh3 + $gh4 + $gh5, 2),
                                    // EDITED CCT 07/11/2018 END
                                );

                array_push($completeexcelvalues, $rrecord); 
            }
        }
        else 
        {
            $rrecord = array(0 => 'The operator has no site. No records found');
        }

        $excel_obj->setHeadersAndValues($rptheader, $completeexcelvalues);
        $excel_obj->GenerateExcelFile();
        unset($completeexcelvalues);
    }    
    // CCT ADDED 02/23/2018 END
    elseif(isset($_GET['excel2']) == "e-walletsitetrans")
    {
        $fn = $_GET['fn'] . ".xls";
        $vdatefrom = $_GET['date'];
        $vdateto = date('Y-m-d', strtotime($gaddeddate, strtotime($vdatefrom)));
        $dateFrom = $vdatefrom . " " . $vcutofftime;
        $dateTo = $vdateto . " " . $vcutofftime;
        $vsiteID = $_SESSION['siteid1'];
        $arrsiteID = array();

        if ($vsiteID == 0) 
        {
            foreach ($rsitesowned as $row) 
            {
                $vsiteID = $row['SiteID'];
                array_push($arrsiteID, $vsiteID);
            }
        } 
        else 
        {
            $arrsiteID = array($vsiteID);
        }
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");

        //Headers for non e-SAFE transactions
        $rheaders = array( 'Site / PEGS Code', 'Terminal Code', 'Deposit', 'Reload', 'Redemption', 'Date Started', 'Date Ended');
        $completeexcelvalues = array();

        $arrdeposit = array();
        $arrreload = array();
        $arrwithdraw = array();
        $result = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit = null, $sort = null, $direction = null);

        if (count($result) > 0) 
        {
            $optrdetails = array();
            foreach ($result as $value) 
            {
                $rsitecode = $orptoptr->getsitecode($value['SiteID']); //get the sitecode first

                if (!isset($optrdetails[$value['TransactionSummaryID']])) 
                {
                    $optrdetails[$value['TransactionSummaryID']] = 
                            array(
                                'TransactionSummaryID' => $value['TransactionSummaryID'],
                                'DateStarted' => $value['DateStarted'],
                                'DateEnded' => $value['DateEnded'],
                                'DateCreated' => $value['DateCreated'],
                                'TerminalID' => $value['TerminalID'],
                                'SiteID' => $value['SiteID'],
                                'SiteCode' => $rsitecode['SiteCode'],
                                'TerminalCode' => $value['TerminalCode'],
                                'Withdrawal' => '0.00',
                                'Deposit' => '0.00',
                                'Reload' => '0.00'
                                );
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $trans = array('Withdrawal' => $value['amount']);
                        break;
                    case 'D':
                        $trans = array('Deposit' => $value['amount']);
                        break;
                    case 'R':
                        $trans = array('Reload' => $value['amount']);
                        break;
                }
                $optrdetails[$value['TransactionSummaryID']] = array_merge($optrdetails[$value['TransactionSummaryID']], $trans);
            }

            $granddeposit = 0;
            $grandreload = 0;
            $grandwithdraw = 0;
            $arrdeposit = array();
            $arrreload = array();
            $arrwithdraw = array();

            foreach ($optrdetails as $vview) 
            {
                $rterminalCode = $vview['TerminalCode'];
                //search first if the sitecode was found in the terminal code
                if (strstr($rterminalCode, $vview['SiteCode']) == false) 
                {
                    //remove all the letters from terminal code
                    $rterminalCode = ereg_replace("[^0-9]", "", $rterminalCode);
                } 
                else 
                {
                    //remove the "icsa-[SiteCode]"
                    $rterminalCode = substr($rterminalCode, strlen($vview['SiteCode']));
                }
                $vdeposit = $vview['Deposit'];
                $vreload = $vview['Reload'];
                $vwithdraw = $vview['Withdrawal'];
                $excelvalues = array(//0 => $vview['TransactionSummaryID'],
                                0 => $vview['SiteCode'],
                                1 => $rterminalCode,
                                2 => number_format($vdeposit, 2, '.', ','),
                                3 => number_format($vreload, 2, '.', ','),
                                4 => number_format($vwithdraw, 2, '.', ','),
                                5 => $vview['DateStarted'],
                                6 => $vview['DateEnded']
                                );
                array_push($completeexcelvalues, $excelvalues); //push the values for site transactions per day
                array_push($arrdeposit, $vdeposit);
                array_push($arrreload, $vreload);
                array_push($arrwithdraw, $vwithdraw);
            }
        }

        /**
        * For e-SAFE transaction
        */
        $arrspace = array('', '', '', '', '', '', '', '');
        array_push($completeexcelvalues, $arrspace);

        //Headers for e-SAFE transactions
        $xheaders = array('Site / PEGS Code', 'Card Number', 'e-SAFE Loads', 'e-SAFE Withdrawals', 'Date Started', 'Date Ended', '', '');
        array_push($completeexcelvalues, $xheaders);

        $arrewloads = array();
        $arrewwithdrawals = array();

        $result1 = $orptoptr->viewewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit = null, $sort = null, $direction = null);

        if (count($result1) > 0) 
        {
            $optrdetails2 = array();
            foreach ($result1 as $value2) 
            {
                $rsitecode = $orptoptr->getsitecode($value2['SiteID']);
                if (!isset($optrdetails2[$value2['EwalletTransID']])) 
                {
                    $optrdetails2[$value2['EwalletTransID']] = 
                            array(
                                'EwalletTransID' => $value2['EwalletTransID'],
                                'SiteID' => $value2['SiteID'],
                                'SiteCode' => $rsitecode['SiteCode'],
                                'LoyaltyCardNumber' => $value2['LoyaltyCardNumber'],
                                'StartDate' => $value2['StartDate'],
                                'EndDate' => $value2['EndDate'],
                                'EWLoads' => $value2['EWLoads'],
                                'EWWithdrawals' => $value2['EWWithdrawals']
                            );
                }
                $trans2 = array();
                switch ($value2['TransType']) 
                {
                    case 'W':
                        $trans2 = array('EWWithdrawals' => $value2['EWWithdrawals']);
                        break;
                    case 'D':
                        $trans2 = array('EWLoads' => $value2['EWLoads']);
                        break;
                }
                $optrdetails2[$value2['EwalletTransID']] = array_merge($optrdetails2[$value2['EwalletTransID']], $trans2);
            }

            $grandsales = 0;
            $grandredemption = 0;
            $grandticketencashment = 0;
            $grandcashonhand = 0;
            $ewalletWithdraw = 0;
            $arrewloads = array();
            $arrewwithdrawals = array();

            foreach ($optrdetails2 as $vview2) 
            {
                $vewloads = $vview2['EWLoads'];
                $vewwithdrawals = $vview2['EWWithdrawals'];
                $excelvalues = array(
                            0 => $vview2['SiteCode'],
                            1 => $vview2['LoyaltyCardNumber'],
                            2 => number_format($vewloads, 2, '.', ','),
                            3 => number_format($vewwithdrawals, 2, '.', ','),
                            4 => $vview2['StartDate'],
                            5 => $vview2['EndDate']
                        );
                array_push($completeexcelvalues, $excelvalues); //push the values for site transactions per day
                array_push($arrewloads, $vewloads);
                array_push($arrewwithdrawals, $vewwithdrawals);
            }

            $arrdeposit = array();
            $arrwithdraw = array();
            $arrreload = array();

            $result2 = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID);

            if ($result2) 
            {
                $ctr2 = 0;
                while ($ctr2 < count($result2)) 
                {
                    $trans = array();
                    switch ($result2[$ctr2]['TransactionType']) 
                    {
                        case 'W':
                            $trans = array('Withdrawal' => (float) $result2[$ctr2]['amount']);
                            array_push($arrwithdraw, $trans);
                            break;
                        case 'D':
                            $trans = array('Deposit' => (float) $result2[$ctr2]['amount']);
                            array_push($arrdeposit, $trans);
                            break;
                        case 'R':
                            $trans = array('Reload' => (float) $result2[$ctr2]['amount']);
                            array_push($arrreload, $trans);
                            break;
                    }
                    $ctr2++;
                }
            } 
            else 
            {
                $grandsum = 0.00;
            }
            $grandsumew = 0.00;

            if ($result1 && $result2) 
            {

                if ($arrdeposit) 
                {
                    foreach ($arrdeposit as $arrdepositsingle) 
                    {
                        $arrdepositsum[] = (float) $arrdepositsingle['Deposit'];
                    }
                } 
                else 
                {
                    $arrdepositsum = array('Deposit' => 0.00);
                }

                if ($arrreload) 
                {
                    foreach ($arrreload as $arrreloadsingle) 
                    {
                        $arrreloadsum[] = (float) $arrreloadsingle['Reload'];
                    }
                } 
                else 
                {
                    $arrreloadsum = array('Reload' => 0.00);
                }

                if ($arrwithdraw) 
                {
                    foreach ($arrwithdraw as $arrwithdrawsingle) 
                    {
                        $arrwithdrawsum[] = (float) $arrwithdrawsingle['Withdrawal'];
                    }
                } 
                else 
                {
                    $arrwithdrawsum = array('Withdrawal' => 0.00);
                }

                if ($arrewloads) 
                {
                    $arrewloadssum = array_sum($arrewloads);
                } 
                else 
                {
                    $arrewloadssum = 0.00;
                }

                if ($arrewwithdrawals) 
                {
                    $arrewwithdrawalssum = array_sum($arrewwithdrawals);
                } 
                else 
                {
                    $arrewwithdrawalssum = 0.00;
                }

                $grandsales = $arrewloadssum + array_sum($arrdepositsum) + array_sum($arrreloadsum);
                $grandredemption = $arrewwithdrawalssum + array_sum($arrwithdrawsum);
            }
            else if ($result1 && !$result2) 
            {
                if ($arrewloads) 
                {
                    $arrewloadssum = array_sum($arrewloads);
                } 
                else 
                {
                    $arrewloadssum = 0.00;
                }

                if ($arrewwithdrawals) 
                {
                    $arrewwithdrawalssum = array_sum($arrewwithdrawals);
                } 
                else 
                {
                    $arrewwithdrawalssum = 0.00;
                }

                $grandsales = $arrewloadssum + $grandsum;
                $grandredemption = $arrewwithdrawalssum + $grandsum;
            }
            else if (!$result1 && $result2) 
            {
                if ($arrdeposit) 
                {
                    foreach ($arrdeposit as $arrdepositsingle) 
                    {
                        $arrdepositsum[] = (float) $arrdepositsingle['Deposit'];
                    }
                } 
                else 
                {
                    $arrdepositsum = array('Deposit' => 0.00);
                }

                if ($arrreload) 
                {
                    foreach ($arrreload as $arrreloadsingle) 
                    {
                        $arrreloadsum[] = (float) $arrreloadsingle['Reload'];
                    }
                } 
                else 
                {
                    $arrreloadsum = array('Reload' => 0.00);
                }

                if ($arrwithdraw) 
                {
                    foreach ($arrwithdraw as $arrwithdrawsingle) 
                    {
                        $arrwithdrawsum[] = (float) $arrwithdrawsingle['Withdrawal'];
                    }
                } 
                else 
                {
                    $arrwithdrawsum = array('Withdrawal' => 0.00);
                }
                $grandsales = $grandsumew + array_sum($arrdepositsum) + array_sum($arrreloadsum);
                $grandredemption = $grandsumew + array_sum($arrwithdrawsum);
            } 
            else 
            {
                $grandsales = 0.00;
                $grandredemption = 0.00;
            }
            $cohdata = $orptoptr->getCashOnHandDetails($dateFrom, $dateTo, $arrsiteID);
            $esafeCash = $cohdata['TotalCashLoadEwallet']; 
            if ($dateFrom < $deploymentDate) 
            {
                $grandticketencashment = $orptoptr->getEncashedTicketsV15($arrsiteID, $dateFrom, $dateTo);
                $ewalletWithdraw = $cohdata['TotalGenesisRedemption'] + $cohdata['TotalEwalletRedemption'];
                $grandcashonhand = (($cohdata['TotalCashLoad'] + $cohdata['TotalCouponLoad']+ $esafeCash + $cohdata['TotalEwalletCoupon'])-($cohdata['TotalCashRedemption']+$ewalletWithdraw)-$grandticketencashment)-$cohdata['TotalMR'];
            }
            else 
            {
                $grandticketencashment = $orptoptr->getEncashedTicketsV15($arrsiteID, $dateFrom, $dateTo);
                $ewalletWithdraw = $cohdata['TotalGenesisRedemption'] + $cohdata['TotalEwalletRedemption'];
                $grandcashonhand = (($cohdata['TotalCashLoad'] + $cohdata['TotalTicketLoadGenesis'] + $cohdata['TotalCouponLoad'] + $cohdata['TotalCashLoadEwallet'] + $cohdata['TotalEsafeTicketLoadGenesis'] + $cohdata['TotalEwalletCoupon'])-($cohdata['TotalRedemption'] + $ewalletWithdraw)-$grandticketencashment)- $cohdata['TotalMR'];
                $grandredemption += $grandticketencashment;
            }

            $grandredemption += $cohdata['TotalMR'];
            // store the grand total of transaction types into an array 
            $arrgrand = array("GrandSales" => $grandsales, "GrandRedemption" => $grandredemption,
                        "GrandTicketEncashment" => $grandticketencashment, "GrandCashOnHand" => $grandcashonhand);
            $arrspace = array('', '', '', '', '', '', '', '');
            array_push($completeexcelvalues, $arrspace);

            //array for displaying total sales on excel file
            $totalsales = array(
                    0 => 'Sales',
                    1 => number_format($grandsales, 2, '.', ',')
                );
            array_push($completeexcelvalues, $totalsales); //push the total sales for the site transaction
            //array for displaying total redeemed on excel file
            $totalredeem = array(
                    0 => 'Redemption',
                    1 => number_format($grandredemption, 2, '.', ',')
                );
            array_push($completeexcelvalues, $totalredeem); //push the total withdraw for the site transaction
            //array for displaying total ticket encashment on excel file
            $totalticketencashment = array(
                    0 => 'Ticket Encashments',
                    1 => number_format($grandticketencashment, 2, '.', ',')
                );
            array_push($completeexcelvalues, $totalticketencashment);
            //array for displaying total cash on hand on excel file
            $totalcashonhand = array(
                    0 => 'Cash on Hand',
                    1 => number_format($grandcashonhand, 2, '.', ',')
                );
            array_push($completeexcelvalues, $totalcashonhand);
        }

        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Site Transactions";
        $orptoptr->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        unset($rheaders);
        unset($completeexcelvalues, $optrdetails);
        unset($arrsiteID);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        $orptoptr->close();
    }
    elseif(isset($_GET['excel']) == "sitetrans")
    {
        $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
        $vdatefrom = $_GET['date'];
        $vdateto = date ( 'Y-m-d' , strtotime ($gaddeddate, strtotime($vdatefrom)));

        $dateFrom = $vdatefrom." ".$vcutofftime;
        $dateTo = $vdateto." ".$vcutofftime;

        $vsiteID = $_SESSION['siteid1'];

        //checks if siteID was selected all;
        $arrsiteID = array(); 
        if($vsiteID == 0)
        {
            foreach($rsitesowned as $row)
            {
                $vsiteID = $row['SiteID'];
                array_push($arrsiteID, $vsiteID);
            }   
        }
        else
        {
            $arrsiteID = array($vsiteID);
        }
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
        //setting the values of the headers and data of the excel file
        //and these values comes from the other file which file shows the data
        $rheaders = array('Transaction Summary ID', 'Site Code', 'Terminal Code', 'Deposit','Reload','Redemption','Date Started','Date Ended');
        $completeexcelvalues = array();

        $arrdeposit = array();
        $arrreload = array();
        $arrwithdraw = array();
        $result = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit = null, $sort = null, $direction = null);

        if(count($result) > 0)
        {                
            $rsitecode = $orptoptr->getrptsitecode($arrsiteID); //get the sitecode first
            $optrdetails = array();
            foreach($result as $value) 
            {                
                if(!isset($optrdetails[$value['TransactionSummaryID']])) 
                {
                    $optrdetails[$value['TransactionSummaryID']] = 
                            array(
                                'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                'DateStarted'=>$value['DateStarted'],
                                'DateEnded'=>$value['DateEnded'],
                                'DateCreated'=>$value['DateCreated'],
                                'TerminalID'=>$value['TerminalID'],
                                'SiteID'=>$value['SiteID'],
                                'TerminalCode'=>$value['TerminalCode'],
                                'Withdrawal'=>'0.00',
                                'Deposit'=>'0.00',
                                'Reload'=>'0.00'
                            ); 
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $trans = array('Withdrawal'=>$value['amount']);
                        break;
                    case 'D':
                        $trans = array('Deposit'=>$value['amount']);
                        break;
                    case 'R':
                        $trans = array('Reload'=>$value['amount']);
                        break;
                }
                $optrdetails[$value['TransactionSummaryID']] = array_merge($optrdetails[$value['TransactionSummaryID']], $trans);
            }

            $granddeposit = 0;
            $grandreload = 0;
            $grandwithdraw = 0;
            $arrdeposit = array();
            $arrreload = array();
            $arrwithdraw = array();

            foreach($optrdetails as $vview)
            {    
                $rterminalCode = $vview['TerminalCode'];
                //search first if the sitecode was found in the terminal code
                if(strstr($rterminalCode, $rsitecode['SiteCode']) == false)
                {
                    //remove all the letters from terminal code
                    $rterminalCode = ereg_replace("[^0-9]", "", $rterminalCode);
                }
                else
                {
                    //remove the "icsa-[SiteCode]"
                    $rterminalCode = substr($rterminalCode, strlen($rsitecode['SiteCode']));
                }
                $vdeposit = $vview['Deposit'];
                $vreload = $vview['Reload'];
                $vwithdraw = $vview['Withdrawal'];
                $excelvalues = array(
                        0 => $vview['TransactionSummaryID'],
                        1 => $rsitecode['SiteCode'],
                        2 => $rterminalCode,
                        3 => number_format($vdeposit, 2, '.', ','), 
                        4 => number_format($vreload, 2, '.', ','), 
                        5 => number_format($vwithdraw, 2, '.', ','), 
                        6 => $vview['DateStarted'],
                        7 => $vview['DateEnded']
                    );
                array_push($completeexcelvalues,$excelvalues); //push the values for site transactions per day
                array_push($arrdeposit, $vdeposit);
                array_push($arrreload, $vreload);
                array_push($arrwithdraw, $vwithdraw);
            }
        }

        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Site Transactions";
        $orptoptr->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        unset($rheaders);
        unset($completeexcelvalues, $optrdetails);
        unset($arrsiteID);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        $orptoptr->close();
    }
    /***************************** EXPORTING PDF STARTS HERE *******************************/
    elseif(isset($_GET['pdf']) == "sitetrans" )
    {
        $granddeposit = 0;
        $grandreload = 0;
        $grandwithdraw = 0;
        $arrdeposit = array();
        $arrreload = array();
        $arrwithdraw = array(); 
        $vdatefrom = $_GET['date'];
        $vdateto = date ( 'Y-m-d' , strtotime ($gaddeddate, strtotime($vdatefrom)));
        $dateFrom = $vdatefrom." ".$vcutofftime;
        $dateTo = $vdateto." ".$vcutofftime;
        $vsiteID = $_SESSION['siteid1'];

        //checks if siteID was selected all;
        $arrsiteID = array(); 
        if($vsiteID == 0)
        {
            foreach($rsitesowned as $row)
            {
                $vsiteID = $row['SiteID'];
                array_push($arrsiteID, $vsiteID);
            }   
        }
        else
        {
            $arrsiteID = array($vsiteID);
        }

        /**** set this configuration for exporting large quantity of records into pdf; *****/
        /**** prevents program from exceeding ,max execution time *****/
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '120');

        $result = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit = null, $sort = null, $direction = null);
        $pdf = CTCPDF::c_getInstance(); //call method of tcpdf
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Site Transaction Per Day'); //filename
        $pdf->html.='<div style="text-align:center;">As of ' . $dateFrom .' To '.$dateTo.'</div>';
        $pdf->SetFontSize(10);
        $pdf->c_tableHeader(array('Transaction Summary ID', 'Site / PEGS Code', 'Terminal Code', 'Deposit','Reload','Redemption','Date Started','Date Ended'));
        $rsitecode = $orptoptr->getrptsitecode($arrsiteID); //get the sitecode first
        if(count($result) > 0)
        {
            $optrdetails = array();
            foreach($result as $value) 
            {                
                if(!isset($optrdetails[$value['TransactionSummaryID']])) 
                {
                    $optrdetails[$value['TransactionSummaryID']] = array(
                            'TransactionSummaryID'=>$value['TransactionSummaryID'],
                            'DateStarted'=>$value['DateStarted'],
                            'DateEnded'=>$value['DateEnded'],
                            'DateCreated'=>$value['DateCreated'],
                            'TerminalID'=>$value['TerminalID'],
                            'SiteID'=>$value['SiteID'],
                            'TerminalCode'=>$value['TerminalCode'],
                            'Withdrawal'=>'0.00',
                            'Deposit'=>'0.00',
                            'Reload'=>'0.00'
                        ); 
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $trans = array('Withdrawal'=>$value['amount']);
                        break;
                    case 'D':
                        $trans = array('Deposit'=>$value['amount']);
                        break;
                    case 'R':
                        $trans = array('Reload'=>$value['amount']);
                        break;
                }
                $optrdetails[$value['TransactionSummaryID']] = array_merge($optrdetails[$value['TransactionSummaryID']], $trans);
            }

            $arrdeposit2 = array();
            $arrreload2 = array();
            $arrwithdraw2 = array();
            foreach($optrdetails as $vview)
            {
                $vterminalCode = $vview['TerminalCode'];
                //remove the "icsa-[SiteCode]"
                $rterminalCode = substr($vterminalCode, strlen($rsitecode['SiteCode']));
                $vdeposit = $vview['Deposit'];
                $vreload = $vview['Reload'];
                $vwithdraw = $vview['Withdrawal'];

                //push the values for site transactions per day
                $pdf->c_tableRow(array(
                        0 => $vview['TransactionSummaryID'],
                        1 => $rsitecode['SiteCode'],
                        2 => $rterminalCode,
                        3 => number_format($vdeposit, 2, '.', ','), 
                        4 => number_format($vreload, 2, '.', ','), 
                        5 => number_format($vwithdraw, 2, '.', ','), 
                        6 => $vview['DateStarted'],
                        7 => $vview['DateEnded']
                    ));

                array_push($arrdeposit2, $vdeposit);
                array_push($arrreload2, $vreload);
                array_push($arrwithdraw2, $vwithdraw);            
            }
        }
        else
        {
            $pdf->html.= '<div style="text-align: center;">';
            $pdf->html.= ' No Results Found';
            $pdf->html.= '</div>';
        }
        $pdf->c_tableEnd();
        $vauditfuncID = 40; //export to pdf
        $vtransdetails = "Site Transactions";
        $orptoptr->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        unset($arrsiteID, $optrdetails);
        $pdf->c_generatePDF('SiteTransactionPerDay.pdf'); 
        $orptoptr->close();
    }
    elseif(isset($_GET['pdf2']) == "e-walletsitetrans" )
    {
        $granddeposit = 0;
        $grandreload = 0;
        $grandwithdraw = 0;
        $ewalletWithdraw = 0;
        $arrdeposit = array();
        $arrreload = array();
        $arrwithdraw = array();
        $vdatefrom = $_GET['date'];
        $vdateto = date('Y-m-d', strtotime($gaddeddate, strtotime($vdatefrom)));

        $dateFrom = $vdatefrom . " " . $vcutofftime;
        $dateTo = $vdateto . " " . $vcutofftime;

        $vsiteID = $_SESSION['siteid1'];

        //checks if siteID was selected all
        $arrsiteID = array();
        if ($vsiteID == 0) 
        {
            foreach ($rsitesowned as $row) 
            {
                $vsiteID = $row['SiteID'];
                array_push($arrsiteID, $vsiteID);
            }
        } 
        else 
        {
            $arrsiteID = array($vsiteID);
        }

        /*         * ** set this configuration for exporting large quantity of records into pdf; **** */
        /*         * ** prevents program from exceeding ,max execution time **** */
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '120');

        $result = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit = null, $sort = null, $direction = null);
        $pdf = CTCPDF::c_getInstance(); //call method of tcpdf
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Site Transaction Per Day'); //filename
        $pdf->html.='<div style="text-align:center;">As of ' . $dateFrom . ' To ' . $dateTo . '</div>';
        $pdf->SetFontSize(9);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code','width' => '100px'),
                array('value'=>'Terminal Code','width' => '100px'),
                array('value'=>'Deposit'),
                array('value'=>'Reload'),
                array('value'=>'Redemption'),
                array('value'=>'Date Started','width' => '130px'),
                array('value'=>'Date Ended','width' => '130px')
        ));

        if (count($result) > 0) 
        {
            $optrdetails = array();
            foreach ($result as $value) 
            {
                $rsitecode = $orptoptr->getsitecode($value['SiteID']);
                if (!isset($optrdetails[$value['TransactionSummaryID']])) 
                {
                    $optrdetails[$value['TransactionSummaryID']] = array(
                        'TransactionSummaryID' => $value['TransactionSummaryID'],
                        'DateStarted' => $value['DateStarted'],
                        'DateEnded' => $value['DateEnded'],
                        'DateCreated' => $value['DateCreated'],
                        'TerminalID' => $value['TerminalID'],
                        'SiteID' => $value['SiteID'],
                        'SiteCode' => $rsitecode['SiteCode'],
                        'TerminalCode' => $value['TerminalCode'],
                        'Withdrawal' => '0.00',
                        'Deposit' => '0.00',
                        'Reload' => '0.00'
                        );
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $trans = array('Withdrawal' => $value['amount']);
                        break;
                    case 'D':
                        $trans = array('Deposit' => $value['amount']);
                        break;
                    case 'R':
                        $trans = array('Reload' => $value['amount']);
                        break;
                }
                $optrdetails[$value['TransactionSummaryID']] = array_merge($optrdetails[$value['TransactionSummaryID']], $trans);
            }

            $arrdeposit2 = array();
            $arrreload2 = array();
            $arrwithdraw2 = array();
            foreach ($optrdetails as $vview) 
            {
                $vterminalCode = $vview['TerminalCode'];
                //remove the "icsa-[SiteCode]"
                $rterminalCode = substr($vterminalCode, strlen($vview['SiteCode']));
                $vdeposit = $vview['Deposit'];
                $vreload = $vview['Reload'];
                $vwithdraw = $vview['Withdrawal'];

                //push the values for site transactions per day
                $pdf->c_tableRow2(array(
                        array('value'=>$vview['SiteCode'], 'width' => '100px'),
                        array('value'=>$rterminalCode,'width' => '100px'),
                        array('value'=>number_format($vdeposit,2),'align'=>'right'),
                        array('value'=>number_format($vreload,2),'align'=>'right'),
                        array('value'=>number_format($vwithdraw,2),'align'=>'right'),
                        array('value'=>$vview['DateStarted'], 'width' => '130px'),
                        array('value'=>$vview['DateEnded'], 'width' => '130px')
                    ));

                array_push($arrdeposit2, $vdeposit);
                array_push($arrreload2, $vreload);
                array_push($arrwithdraw2, $vwithdraw);
            }
        } 
        else 
        {
            $pdf->html.= '<div style="text-align: center; border: 1px solid black;">';
            $pdf->html.= ' No Results Found';
            $pdf->html.= '</div>';
        }
        $pdf->c_tableEnd();

        /**
        * For e-SAFE transactions
        */
        $result1 = $orptoptr->viewewtransactionperday($dateFrom, $dateTo, $arrsiteID, $start = null, $limit = null, $sort = null, $direction = null);
        $pdf->html.='<div style="text-align:center;"></div>';
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Card Number'),
                array('value'=>'e-SAFE Loads'),
                array('value'=>'e-SAFE Withdrawals'),
                array('value'=>'Date Started'),
                array('value'=>'Date Ended')
        ));

        if (count($result1) > 0) 
        {
            $optrdetails2 = array();
            foreach ($result1 as $value2) 
            {
                $rsitecode = $orptoptr->getsitecode($value2['SiteID']);
                if (!isset($optrdetails2[$value2['EwalletTransID']])) 
                {
                    $optrdetails2[$value2['EwalletTransID']] = array(
                        'EwalletTransID' => $value2['EwalletTransID'],
                        'SiteID' => $value2['SiteID'],
                        'SiteCode' => $rsitecode['SiteCode'],
                        'LoyaltyCardNumber' => $value2['LoyaltyCardNumber'],
                        'EWLoads' => $value2['EWLoads'],
                        'EWWithdrawals' => $value2['EWWithdrawals'],
                        'StartDate' => $value2['StartDate'],
                        'EndDate' => $value2['EndDate']
                    );
                }
                $trans2 = array();
                switch ($value2['TransType']) 
                {
                    case 'W':
                        $trans2 = array('EWWithdrawals' => $value2['EWWithdrawals']);
                        break;
                    case 'D':
                        $trans2 = array('EWLoads' => $value2['EWLoads']);
                        break;
                }
                $optrdetails2[$value2['EwalletTransID']] = array_merge($optrdetails2[$value2['EwalletTransID']], $trans2);
            }

            $arrewloads = array();
            $arrewwithdrawals = array();

            foreach ($optrdetails2 as $vview2) 
            {
        //              //remove the "icsa-[SiteCode]"
                $vewloads = $vview2['EWLoads'];
                $vewwithdrawals = $vview2['EWWithdrawals'];

                //push the values for e-SAFE site transactions per day
                $pdf->c_tableRow2(array(
                        array('value'=>$vview2['SiteCode']),
                        array('value'=>$vview2['LoyaltyCardNumber']),
                        array('value'=>number_format($vewloads, 2, '.', ','),'align'=>'right'),
                        array('value'=>number_format($vewwithdrawals, 2, '.', ','),'align'=>'right'),
                        array('value'=>$vview2['StartDate']),
                        array('value'=>$vview2['EndDate'])
                    ));

                array_push($arrewloads, $vewloads);
                array_push($arrewwithdrawals, $vewwithdrawals);
            }

            $result2 = $orptoptr->viewtransactionperday($dateFrom, $dateTo, $arrsiteID);

            if ($result2) 
            {
                $ctr2 = 0;
                while ($ctr2 < count($result2)) 
                {
                    $trans = array();
                    switch ($result2[$ctr2]['TransactionType']) 
                    {
                        case 'W':
                            $trans = array('Withdrawal' => (float) $result2[$ctr2]['amount']);
                            array_push($arrwithdraw, $trans);
                            break;
                        case 'D':
                            $trans = array('Deposit' => (float) $result2[$ctr2]['amount']);
                            array_push($arrdeposit, $trans);
                            break;
                        case 'R':
                            $trans = array('Reload' => (float) $result2[$ctr2]['amount']);
                            array_push($arrreload, $trans);
                            break;
                    }
                    $ctr2++;
                }
            } 
            else 
            {
                $grandsum = 0.00;
            }

            $grandsumew = 0.00;


            if ($result1 && $result2) 
            {
                if ($arrdeposit) 
                {
                    foreach ($arrdeposit as $arrdepositsingle) 
                    {
                        $arrdepositsum[] = (float) $arrdepositsingle['Deposit'];
                    }
                } 
                else 
                {
                    $arrdepositsum = array('Deposit' => 0.00);
                }

                if ($arrreload) 
                {
                    foreach ($arrreload as $arrreloadsingle) 
                    {
                        $arrreloadsum[] = (float) $arrreloadsingle['Reload'];
                    }
                } 
                else 
                {
                    $arrreloadsum = array('Reload' => 0.00);
                }

                if ($arrwithdraw) 
                {
                    foreach ($arrwithdraw as $arrwithdrawsingle) 
                    {
                        $arrwithdrawsum[] = (float) $arrwithdrawsingle['Withdrawal'];
                    }
                } 
                else 
                {
                    $arrwithdrawsum = array('Withdrawal' => 0.00);
                }

                if ($arrewloads) 
                {
                    $arrewloadssum = array_sum($arrewloads);
                } 
                else 
                {
                    $arrewloadssum = 0.00;
                }

                if ($arrewwithdrawals) 
                {
                    $arrewwithdrawalssum = array_sum($arrewwithdrawals);
                } 
                else 
                {
                    $arrewwithdrawalssum = 0.00;
                }

                $grandsales = $arrewloadssum + array_sum($arrdepositsum) + array_sum($arrreloadsum);
                $grandredemption = $arrewwithdrawalssum + array_sum($arrwithdrawsum);
            } 
            else if ($result1 && !$result2) 
            {
                if ($arrewloads) 
                {
                    $arrewloadssum = array_sum($arrewloads);
                } 
                else 
                {
                    $arrewloadssum = 0.00;
                }

                if ($arrewwithdrawals) 
                {
                    $arrewwithdrawalssum = array_sum($arrewwithdrawals);
                } 
                else 
                {
                    $arrewwithdrawalssum = 0.00;
                }

                $grandsales = $arrewloadssum + $grandsum;
                $grandredemption = $arrewwithdrawalssum + $grandsum;
            } 
            else if (!$result1 && $result2) 
            {
                if ($arrdeposit) 
                {
                    foreach ($arrdeposit as $arrdepositsingle) 
                    {
                        $arrdepositsum[] = (float) $arrdepositsingle['Deposit'];
                    }
                } 
                else 
                {
                    $arrdepositsum = array('Deposit' => 0.00);
                }

                if ($arrreload) 
                {
                    foreach ($arrreload as $arrreloadsingle) 
                    {
                        $arrreloadsum[] = (float) $arrreloadsingle['Reload'];
                    }
                } 
                else 
                {
                    $arrreloadsum = array('Reload' => 0.00);
                }

                if ($arrwithdraw) 
                {
                    foreach ($arrwithdraw as $arrwithdrawsingle) 
                    {
                        $arrwithdrawsum[] = (float) $arrwithdrawsingle['Withdrawal'];
                    }
                } 
                else 
                {
                    $arrwithdrawsum = array('Withdrawal' => 0.00);
                }

                $grandsales = $grandsumew + array_sum($arrdepositsum) + array_sum($arrreloadsum);
                $grandredemption = $grandsumew + array_sum($arrwithdrawsum);
            } 
            else 
            {
                $grandsales = 0.00;
                $grandredemption = 0.00;
            }

            //Compute for total Cash On Hand of the sites under the current operator
            $cohdata = $orptoptr->getCashOnHandDetails($dateFrom, $dateTo, $arrsiteID);
            $esafeCash = $cohdata['TotalCashLoadEwallet']; 
            if ($dateFrom < $deploymentDate) 
            {
                $grandticketencashment = $orptoptr->getEncashedTicketsV15($arrsiteID, $dateFrom, $dateTo);
                $ewalletWithdraw = $cohdata['TotalGenesisRedemption'] + $cohdata['TotalEwalletRedemption'];
                $grandcashonhand = (($cohdata['TotalCashLoad'] + $cohdata['TotalCouponLoad']+ $esafeCash + $cohdata['TotalEwalletCoupon'])-($cohdata['TotalCashRedemption']+$ewalletWithdraw)-$grandticketencashment)-$cohdata['TotalMR'];
            }
            else 
            {
                $grandticketencashment = $orptoptr->getEncashedTicketsV15($arrsiteID, $dateFrom, $dateTo);
                $ewalletWithdraw = $cohdata['TotalGenesisRedemption'] + $cohdata['TotalEwalletRedemption'];
                $grandcashonhand = (($cohdata['TotalCashLoad'] + $cohdata['TotalTicketLoadGenesis'] + $cohdata['TotalCouponLoad'] + $cohdata['TotalCashLoadEwallet'] + $cohdata['TotalEsafeTicketLoadGenesis'] + $cohdata['TotalEwalletCoupon'])-($cohdata['TotalRedemption'] + $ewalletWithdraw)-$grandticketencashment)- $cohdata['TotalMR'];
                $grandredemption += $grandticketencashment;
            }
            $grandredemption += $cohdata['TotalMR'];
            // store the grand total of transaction types into an array 
            $arrgrand = array("GrandSales" => $grandsales, "GrandRedemption" => $grandredemption,
            "GrandTicketEncashment" => $grandticketencashment, "GrandCashOnHand" => $grandcashonhand);
            $pdf->c_tableEnd();
            $pdf->html .= '<div style="text-align: center; ">';
            $pdf->html .= '</div>';
            $pdf->html .= '<style type="text/css">table{border:solid 1px #000;border-collapse:collapse;} td{border:solid 1px #000;}</style><table><thead><tr>';
            $pdf->html .='<th colspan="2" style="text-align:center; width: 260px;"><b>Grand Total</b></th>';
            $pdf->html .='</tr></thead>';

            $pdf->c_tableRow2(array(
                    array('value'=>'Sales', 'width'=>'130px'),
                    array('value'=>number_format($grandsales, 2, '.', ','), 'width'=>'130px', 'align' => 'right')
            ));
            $pdf->c_tableRow2(array(
                    array('value'=>'Redemption', 'width'=>'130px'),
                    array('value'=>number_format($grandredemption, 2, '.', ','), 'width'=>'130px', 'align' => 'right')
            ));
            $pdf->c_tableRow2(array(
                    array('value'=>'Ticket Encashment', 'width'=>'130px'),
                    array('value'=>number_format($grandticketencashment, 2, '.', ','), 'width'=>'130px', 'align' => 'right')
            ));
            $pdf->c_tableRow2(array(
                    array('value'=>'Cash on Hand', 'width'=>'130px'),
                    array('value'=>number_format($grandcashonhand, 2, '.', ','), 'width'=>'130px', 'align' => 'right')
            ));
        } 
        else 
        {
            $pdf->html.= '<div style="text-align: center; border: 1px solid black;">';
            $pdf->html.= ' No Results Found';
            $pdf->html.= '</div>';
        }

        $pdf->c_tableEnd();
        $vauditfuncID = 40; //export to pdf
        $vtransdetails = "Site Transactions";
        $orptoptr->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        unset($arrsiteID, $optrdetails, $optrdetails2);
        $pdf->c_generatePDF('SiteTransactionPerDay.pdf');
        $orptoptr->close();
    }
    //for displaying site name on label
    elseif(isset($_POST['cmbsitename']))
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $orptoptr->getsitename($vsiteID);
        foreach($rresult as $row)
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
        }

        $vsiteName = new stdClass();

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
        $orptoptr->close();
        exit;
    }
    //on ajax call from rptbcfpersite.php
    elseif(isset($_POST['gettotalbcf']) == "GetBCFTotals")
    {
        $arrtotal = 0;
        $granddeposit = 0;
        $grandreload = 0;
        $grandwithdraw = 0;
        $arrbalance = array();

        if(isset($_SESSION['total']))
        {
            $arrtotal = $_SESSION['total'];
        }

        //used this method to get the grand total of all tranction types
        $result = $orptoptr->viewbcfpersite($rsitesowned, $start=null, $limit=null);
        foreach($result as $vview)
        {                     
            //store into an arrays
            array_push($arrbalance, $vview['Balance']);
        }

        /**** GET Total Summary *****/
        $grandbalance = array_sum($arrbalance);

        // store the grand total of transaction types into an array 
        $arrgrand = array("GrandBalance" => $grandbalance);

        //results will be fetch here:
        if((count($arrtotal) > 0) && (count($arrgrand) > 0))
        {
            /**** Get Total Per Page  *****/
            $vtotal->summary = number_format($arrtotal["TotalBalance"], 2, '.', ',');
            /**** GET Total Page Summary ******/
            $vtotal->grandsum = number_format($arrgrand['GrandBalance'], 2, '.', ',');
            echo json_encode($vtotal); 
        }
        else 
        {
            echo "No Results Found";
        }
        unset($arrbalance);
        $orptoptr->close();
        exit;
    }
    elseif(isset($_GET['excel1']) == "bcfpersite")
    {
        $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
        //setting the values of the headers and data of the excel file
        //and these values comes from the other file which file shows the data
        $rheaders = array('Site / PEGS Code','Site / PEGS Name', 'Last Transaction Date', 'Top-up Type', 'Pick-up Tag','Minimum Balance','Maximum Balance','Balance');
        $completeexcelvalues = array();
        $result = $orptoptr->viewbcfpersite($rsitesowned, $start = null, $limit = null);
        if(count($result) > 0)
        {
            $arrbalance = array();
            foreach($result as $vview) 
            {
                $rsiteID = $vview['SiteID'];
                if($vview['TopUpType'] == 0)
                {
                    $topup = "Fixed";
                }
                else
                {
                    $topup = "Variable";
                }
                
                if($vview['PickUpTag'] == 0)
                {
                    $pickup = "Provincial";
                }
                else
                {
                    $pickup = "Metro Manila";
                }

                $isterminalcode = strstr($vview['SiteCode'], $terminalcode);
                if($isterminalcode == false)
                {
                    $vcode= $vview['SiteCode'];
                }
                else
                {
                    $vcode = substr($vview['SiteCode'], strlen($terminalcode));
                }

                $excelvalues = array(
                        0 => $vcode,
                        1 => $vview['SiteName'],
                        2 => $vview['LastTransactionDate'], 
                        3 => $topup, 
                        4 => $pickup, 
                        5 => number_format($vview['MinBalance'], 2, '.', ','), 
                        6 => number_format($vview['MaxBalance'], 2, '.', ','),
                        7 => number_format($vview['Balance'],2,'.',',')
                    );
                array_push($completeexcelvalues, $excelvalues); //push the values for site transactions per day
                array_push($arrbalance, $vview['Balance']);
            }
            $totalbalance = array_sum($arrbalance);
            $arrtotal = array(
                    0 => " ",
                    1 => "",
                    2 => "",
                    3 => "",
                    4 => "",
                    5 => "",
                    6 => "TotalBalance",
                    7 => number_format($totalbalance, 2, '.',',')
            );
            array_push($completeexcelvalues, $arrtotal);
            unset($arrbalance);
        }
        else
        {
            unset($_SESSION['total']);
            $msg = "BCF Per Site: No returned result";
            array_push($completeexcelvalues, $msg);
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "BCF Per Site";
        $orptoptr->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        unset($completeexcelvalues);
    }
    elseif(isset($_GET['pdf1']) == "bcfpersite")
        {
        //setting the values of the headers and data of the excel file
        //and these values comes from the other file which file shows the data
        $pdf = CTCPDF::c_getInstance(); //call method of tcpdf
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('BCF Per Site'); //filename
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
        date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(10);
        $pdf->c_tableHeader(array('Site / PEGS Code','Site / PEGS Name', 'Last Transaction Date', 'Top-up Type', 'Pick-up Tag','Minimum Balance','Maximum Balance','Balance'));
        $result = $orptoptr->viewbcfpersite($rsitesowned, $start = null, $limit = null);
        if(count($result) > 0)
        {
            $arrbalance = array();
            foreach($result as $vview) 
            {
                $rsiteID = $vview['SiteID'];
                if($vview['TopUpType'] == 0)
                {
                    $topup = "Fixed";
                }
                else
                {
                    $topup = "Variable";
                }
                if($vview['PickUpTag'] == 0)
                {
                    $pickup = "Provincial";
                }
                else
                {
                    $pickup = "Metro Manila";
                }

                $isterminalcode = strstr($vview['SiteCode'], $terminalcode);
                if($isterminalcode == false)
                {
                    $vcode= $vview['SiteCode'];
                }
                else
                {
                    $vcode = substr($vview['SiteCode'], strlen($terminalcode));
                }

                $pdf->c_tableRow(array(
                        0 => $vcode,
                        1 => $vview['SiteName'],
                        2 => $vview['LastTransactionDate'], 
                        3 => $topup, 
                        4 => $pickup, 
                        5 => number_format($vview['MinBalance'], 2, '.', ','), 
                        6 => number_format($vview['MaxBalance'], 2, '.', ','),
                        7 => number_format($vview['Balance'],2,'.',',')
                    ));
                array_push($arrbalance, $vview['Balance']);
            }
            $totalbalance = array_sum($arrbalance);
            $pdf->c_tableRow(array(
                        0 => '',
                        1 => '',
                        2 => '', 
                        3 => '', 
                        4 => '', 
                        5 => '', 
                        6 => 'Total Balance',
                        7 => number_format($totalbalance,2,'.',',')
                    ));
            unset($arrbalance);
        }
        else
        {
            unset($_SESSION['total']);
            $msg = "BCF Per Site: No returned result";
            $pdf->html.= '<div style="text-align: right;">';
            $pdf->html.= $msg;
            $pdf->html.= '</div>';
        }

        $vauditfuncID = 40; //export to pdf
        $vtransdetails = "BCF Per Site";
        $orptoptr->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $pdf->c_generatePDF('BCF Per Site.pdf'); 
    }
    else if(isset($_GET['excel3']) == "grossholdexcel") 
    {
        $datefrom = $_GET['date'];
        $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));

        $fn = "Gross_Hold_".date('Y_m_d').".xls";
        //setting up excel
        $excel_obj = new ExportExcel("$fn");
        $rptheader = array('Gross Hold report from '.$datefrom.' to '.$dateto,"" );
        $array_headers = array('Site', 'Gross Hold');  
        $completeexcelvalues = array();
        array_push($completeexcelvalues,array('','')); // for Space between Header
        array_push($completeexcelvalues, $array_headers);
        //get site operator's sites
        $aid = $_SESSION['accID'];
        $siteIDs = $orptoptr->getSiteByAID($aid);
        //get site grossholds
        $result = array();
        $total = 0;
        $subtotal = 0;
        // $MG_total = 0;  // Comment Out CCT 02/06/2018
        $costello_total = 0;
        $abbott_total = 0;
        if (count($siteIDs) > 0) 
        {
            foreach ($siteIDs as $siteID) 
            {
                //get D, R, W and MR of respective casinos
                $siteCode = $orptoptr->getsitecode($siteID['SiteID']);
                //for Costello
                //EDITED CCT 02/08/2018 BEGIN
                //$serviceID_COSTELLO       = array(1); //service group id
                // EDITED CCT 07/11/2018 BEGIN
                //$serviceID_COSTELLO       = array(1, 6); // Added Habanero
                $serviceID_COSTELLO       = array(1, 4, 6); // Added RTG UB
                // EDITED CCT 07/11/2018 END
                //EDITED CCT 02/08/2018 END
                $deposit_reload_COSTELLO  = $orptoptr->getGrossHoldTB($siteID['SiteID'], $serviceID_COSTELLO, "DR", $datefrom, $dateto);
                $withdraw_COSTELLO        = $orptoptr->getGrossHoldTB($siteID['SiteID'], $serviceID_COSTELLO, "W", $datefrom, $dateto);
                $mr_COSTELLO              = $orptoptr->getManualRedemptionTrans($siteID['SiteID'], $serviceID_COSTELLO, $datefrom, $dateto);
                $grosshold_COSTELLO       = $deposit_reload_COSTELLO['Amount'] - ($withdraw_COSTELLO['Amount'] + $mr_COSTELLO['Amount']);
                //for abbott
                $loads = $orptoptr->getGrossHoldeSAFE($siteID['SiteID'], $datefrom, $dateto);
                //appending results
                $gh = $loads['StartBalance']+$loads['WalletReloads'] - $loads['EndBalance'] - $loads['GenesisWithdrawal'];
                // EDITED CCT 02/06/2018 BEGIN
                //$subtotal += ($grosshold_MG + $grosshold_COSTELLO + $gh);
                $subtotal += ($grosshold_COSTELLO + $gh);
                // EDITED CCT 02/06/2018 END
                $arr_grosshold[] = array( 'SiteCode' => $siteCode['SiteCode'], 
                'SubTotal' => $subtotal);
                $total += $subtotal;
                $subtotal = 0; //reset to 0 once assigned
            }

            for ($i = 0; $i < count($arr_grosshold); $i++) 
            {
                $rrecord = array(   0 => trim(str_replace("ICSA-", "", $arr_grosshold[$i]['SiteCode'])), 
                                    1 => number_format($arr_grosshold[$i]['SubTotal'], 2, ".", ","));
                array_push($completeexcelvalues, $rrecord); 
            }
            
            $rrecordtotal = array(  0 => 'Total', 
                                    1 => number_format($total, 2, ".", ","));
            array_push($completeexcelvalues, $rrecordtotal); 
        }
        else 
        {
            $rrecord = array(0 => 'The operator has no site. No records found');
        }

        $excel_obj->setHeadersAndValues($rptheader, $completeexcelvalues);
        //        $excel_obj->setHeadersAndValues($array_headers, $completeexcelvalues);
        $excel_obj->GenerateExcelFile();
        unset($completeexcelvalues);
     }
     // CCT ADDED 02/23/2018 BEGIN
     else if(isset($_GET['pdf4']) == "grossholdpdfperprovider") 
     {
        $datefrom = $_GET['date'];
        $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));

        $pdf = CTCPDF::c_getInstance(); //call method of tcpdf
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold per Provider'); //filename
        $pdf->html.='<div style="text-align:center;">From '.$datefrom.' to '.$dateto.' </div>';
        $pdf->SetFontSize(10);
        $pdf->c_tableHeader2(array(
                                array('value'=>'Site / PEGS Code'),
                                array('value'=>'RTG'),
                                array('value'=>'Habanero'),
                                array('value'=>'e-Bingo'),
                                array('value'=>'Total Gross Hold'),
                            ));
        //get site operator's sites
        $aid = $_SESSION['accID'];
        $siteIDs = $orptoptr->getSiteByAID($aid);

        //get service ids with transaction for said date
        //EDITED CCT 07/11/2018 BEGIN
        //$serviceGrpIDs = array(1, 6, 7);
        $serviceGrpIDs = array(1, 4, 6, 7);
        //EDITED CCT 07/11/2018 END
        $serviceIDs = $orptoptr->getServiceIDwithTransactions($serviceGrpIDs, $siteIDs, $datefrom, $dateto);

        // Prepare array structure
        $siteIDarray = array();
        if (count($siteIDs) > 0) 
        {
            foreach ($siteIDs as $siteID) 
            {
                $siteIDarray [$siteID['SiteID']] =
                    array('SiteID' => $siteID['SiteID'], 'SiteCode' => trim(str_replace("ICSA-", "", $siteID['SiteCode'])),);

                if (count($serviceIDs) > 0)
                {
                    $serviceIDarray ['ServiceInfo'] = array();                        
                    foreach ($serviceIDs as $serviceID)
                    { 
                        $tempserviceIDarray [$serviceID['ServiceID']] = array();
                        $tempserviceIDarray = 
                            array(
                                'ServiceID'     => $serviceID['ServiceID'],
                                'ServiceName'   => $serviceID['ServiceName'],
                                'UserMode'      => $serviceID['UserMode'],
                                'TB_DR'         => 0,
                                'TB_W'          => 0,
                                'TB_MR'         => 0,
                                'TB_GH'         => 0,
                                'UB_GH'         => 0,
                                'SubTotal_GH'   => 0
                            );
                        $resultarr = array_push($serviceIDarray ['ServiceInfo'], $tempserviceIDarray);
                    }
                    $siteIDarray[$siteID['SiteID']] = array_merge($siteIDarray[$siteID['SiteID']], $serviceIDarray);                            
                }
            }
        }

        // Create additional total line entry
        $siteIDarray ['Total'] = array('SiteID' => 'Total', 'SiteCode' => 'Total',);

        if (count($serviceIDs) > 0)
        {
            $serviceIDarray ['ServiceInfo'] = array();                        
            foreach ($serviceIDs as $serviceID)
            { 
                $tempserviceIDarray [$serviceID['ServiceID']] = array();
                $tempserviceIDarray = 

                    array(
                        'ServiceID'     => $serviceID['ServiceID'],
                        'ServiceName'   => $serviceID['ServiceName'],
                        'UserMode'      => $serviceID['UserMode'],
                        'TB_DR'         => 0,
                        'TB_W'          => 0,
                        'TB_MR'         => 0,
                        'TB_GH'         => 0,
                        'UB_GH'         => 0,
                        'SubTotal_GH'   => 0
                    );
                $resultarr = array_push($serviceIDarray ['ServiceInfo'], $tempserviceIDarray);
            }
            $siteIDarray ['Total'] = array_merge($siteIDarray ['Total'], $serviceIDarray);                            
        }                

        // Populate array with contents                
        if (count($siteIDs) > 0) 
        {
            foreach ($siteIDs as $siteID) 
            {
                if (count($serviceIDs) > 0)
                {    
                    foreach ($serviceIDs as $serviceID)
                    {    
                        //get D, R, W and MR of respective serviceID

                        // Check User Mode if TB or UB 
                        $arrSiteIDKeyResult = multidimensional_search($siteIDarray, array('SiteID' => $siteID['SiteID']));
                        $arrServiceIDKeyResult = multidimensional_search($siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'], array('ServiceID' => $serviceID['ServiceID']));
                        $arrUserModeResult = $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UserMode'];

                        $ub_info = 0;
                        $ub_grosshold = 0;
                        $tb_deposit_reload = 0;
                        $tb_withdraw = 0;
                        $tb_mr = 0;
                        $tb_grosshold = 0;

                        if ($arrUserModeResult == 1) // UB
                        {
                            // for account based (Abbott)
                            $ub_info        = $orptoptr->getGrossHoldeSAFE($siteID['SiteID'], $datefrom, $dateto);
                            $ub_grosshold   = $ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal'];
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH'] = $ub_grosshold;
                        }
                        else // TB
                        {    
                            // for terminal based (Sapphire/Topaz, Habanero, e-Bingo)
                            $tb_deposit_reload  = $orptoptr->getGrossHoldTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], "DR", $datefrom, $dateto);
                            $tb_withdraw        = $orptoptr->getGrossHoldTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], "W", $datefrom, $dateto);
                            $tb_mr              = $orptoptr->getManualRedemptionTBPerProvider($siteID['SiteID'], $serviceID['ServiceID'], $datefrom, $dateto);
                            $tb_grosshold       = $tb_deposit_reload['Amount'] - ($tb_withdraw['Amount'] + $tb_mr['Amount']);
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_DR'] = $tb_deposit_reload;
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_W'] = $tb_withdraw;
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_MR'] = $tb_mr;
                            $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'] = $tb_grosshold;
                        }
                        $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['SubTotal_GH'] = 
                                $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH'] + 
                                $siteIDarray [$arrSiteIDKeyResult] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'];

                        // for totals
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['UB_GH']+= 
                                ($ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal']);
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_DR'] += $tb_deposit_reload['Amount'];
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_W'] += $tb_withdraw['Amount'];
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_MR'] += $tb_mr['Amount'];
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['TB_GH'] += 
                                ($tb_deposit_reload['Amount'] - $tb_withdraw['Amount'] - $tb_mr['Amount']);
                        $siteIDarray ['Total'] ['ServiceInfo'] [$arrServiceIDKeyResult] ['SubTotal_GH'] += 
                                ($ub_info['StartBalance'] + $ub_info['WalletReloads'] - $ub_info['EndBalance'] - $ub_info['GenesisWithdrawal'] +
                                $tb_deposit_reload['Amount'] - $tb_withdraw['Amount'] - $tb_mr['Amount']);                                  
                    }
                }
            }

            // Prepare array for pdf file contents
            $gh1 = 0;
            $gh2 = 0;
            $gh3 = 0;
            // ADDED CCT 07/11/2018 BEGIN
            $gh4 = 0;
            $gh5 = 0;
            // ADDED CCT 07/11/2018 END
            $i = 0;
            
            foreach($siteIDarray as $sites)
            {
                foreach($sites['ServiceInfo'] as $serviceprov)
                {
                    if ($serviceprov['ServiceID'] == 22) // RTG
                    {
                        $gh1 = $serviceprov['SubTotal_GH'];
                    }
                    elseif ($serviceprov['ServiceID'] == 25) // Habanero
                    {
                        $gh2 = $serviceprov['SubTotal_GH'];
                    }
                    elseif ($serviceprov['ServiceID'] == 26) // e-Bingo
                    {
                        $gh3 = $serviceprov['SubTotal_GH'];
                    }
                    // ADDED CCT 07/11/2018 BEGIN
                    elseif ($serviceprov['ServiceID'] == 28) // RTG UB
                    {
                        $gh4 = $serviceprov['SubTotal_GH'];
                    }
                    elseif ($serviceprov['ServiceID'] == 29) // Habanero UB
                    {
                        $gh5 = $serviceprov['SubTotal_GH'];
                    }
                    // ADDED CCT 07/11/2018 END
                }

                $arr_grosshold[] = array(
                                        'SiteCode'      => $sites['SiteCode'], 
                                        // EDITED CCT 07/11/2018 BEGIN
                                        //'RTG'           => number_format($gh1, 2), 
                                        //'Habanero'      => number_format($gh2, 2), 
                                        'RTG'           => number_format($gh1 + $gh4, 2), 
                                        'Habanero'      => number_format($gh2 + $gh5, 2), 
                                        'e-Bingo'       => number_format($gh3, 2),                     
                                        //'TotalGrossHold' => number_format($gh1 + $gh2 + $gh3, 2),
                                        'TotalGrossHold' => number_format($gh1 + $gh2 + $gh3 + $gh4 + $gh5, 2),
                                        // EDITED CCT 07/11/2018 END
                                        );
            }

            for ($i = 0; $i < count($arr_grosshold); $i++) 
            {
                if ($arr_grosshold[$i]['SiteCode'] == "Total")
                {    
                    $pdf->c_tableRow2(
                                        array(
                                                array('value' => '<b>'.$arr_grosshold[$i]['SiteCode'].'</b>'),
                                                array('value' => '<b>'.$arr_grosshold[$i]['RTG'].'</b>','align'=>'right'),
                                                array('value' => '<b>'.$arr_grosshold[$i]['Habanero'].'</b>','align'=>'right'),
                                                array('value' => '<b>'.$arr_grosshold[$i]['e-Bingo'].'</b>','align'=>'right'),
                                                array('value' => '<b>'.$arr_grosshold[$i]['TotalGrossHold'].'</b>','align'=>'right'),
                                        )
                                    );
                }
                else 
                {
                    $pdf->c_tableRow2(
                                        array(
                                                array('value' => $arr_grosshold[$i]['SiteCode']),
                                                array('value' => $arr_grosshold[$i]['RTG'],'align'=>'right'),
                                                array('value' => $arr_grosshold[$i]['Habanero'],'align'=>'right'),
                                                array('value' => $arr_grosshold[$i]['e-Bingo'],'align'=>'right'),
                                                array('value' => $arr_grosshold[$i]['TotalGrossHold'],'align'=>'right'),
                                        )
                                    );
                }
            } 

            $pdf->c_tableEnd();
            $pdf->c_generatePDF("Gross_Hold_Per_Provider_".date('Y_m_d').".pdf");
        }
     }    
     // CCT ADDED 02/23/2018 END
     else if(isset($_GET['pdf3']) == "grossholdpdf") 
     {
        $datefrom = $_GET['date'];
        $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));

        $pdf = CTCPDF::c_getInstance(); //call method of tcpdf
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold'); //filename
        $pdf->html.='<div style="text-align:center;">From '.$datefrom.' to '.$dateto.' </div>';
        $pdf->SetFontSize(10);
        $pdf->c_tableHeader2(array(
                                array('value'=>'Site'),
                                array('value'=>'Gross Hold'),
                            ));
        //get site operator's sites
        $aid = $_SESSION['accID'];
        $siteIDs = $orptoptr->getSiteByAID($aid);
        //get site grossholds
        $result = array();
        $total = 0;
        $subtotal = 0;
        // $MG_total = 0; // Comment Out CCT 02/06/2018 BEGIN
        $costello_total = 0;
        $abbott_total = 0;
        if (count($siteIDs) > 0) 
        {
            foreach ($siteIDs as $siteID) 
            {
                //get D, R, W and MR of respective casinos
                $siteCode = $orptoptr->getsitecode($siteID['SiteID']);
                //for Costello
                // EDITED CCT 02/08/2018 BEGIN
                //$serviceID_COSTELLO       = array(1); //service group id
                //$serviceID_COSTELLO       = array(1, 6); // Added Habanero
                // EDITED 07/11/2018 BEGIN
                $serviceID_COSTELLO       = array(1, 4 , 6); // Added RTG UB
                // EDITED 07/11/2018 END
                // EDITED CCT 02/08/2018 END
                $deposit_reload_COSTELLO  = $orptoptr->getGrossHoldTB($siteID['SiteID'], $serviceID_COSTELLO, "DR", $datefrom, $dateto);
                $withdraw_COSTELLO        = $orptoptr->getGrossHoldTB($siteID['SiteID'], $serviceID_COSTELLO, "W", $datefrom, $dateto);
                $mr_COSTELLO              = $orptoptr->getManualRedemptionTrans($siteID['SiteID'], $serviceID_COSTELLO, $datefrom, $dateto);
                $grosshold_COSTELLO       = $deposit_reload_COSTELLO['Amount'] - ($withdraw_COSTELLO['Amount'] + $mr_COSTELLO['Amount']);
                //for abbott
                $loads = $orptoptr->getGrossHoldeSAFE($siteID['SiteID'], $datefrom, $dateto);

                //appending results
                $gh = $loads['StartBalance']+$loads['WalletReloads'] - $loads['EndBalance'] - $loads['GenesisWithdrawal'];
                // EDITED CCT 02/06/2018 BEGIN
                //$subtotal += ($grosshold_MG + $grosshold_COSTELLO + $gh);
                $subtotal += ($grosshold_COSTELLO + $gh);
                // EDITED CCT 02/06/2018 END
                $arr_grosshold[] = array( 'SiteCode' => str_replace("ICSA-", "", $siteCode['SiteCode']), 'SubTotal' => $subtotal);

                $total += $subtotal;
                $subtotal = 0; //reset to 0
            }
            for ($i = 0; $i < count($arr_grosshold); $i++) 
            {
                $pdf->c_tableRow2(array(array('value' => str_replace("ICSA-", "", $arr_grosshold[$i]['SiteCode'])),
                array('value' => number_format($arr_grosshold[$i]['SubTotal'], 2, ".", ","))));
            } 
            $pdf->c_tableRow2(array(array('value' => '<b>Total</b>'),
            array('value' => '<b>'.number_format($total, 2, ".", ",").'</b>','align'=>'right')));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF("Gross_Hold_".date('Y_m_d').".pdf");
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>