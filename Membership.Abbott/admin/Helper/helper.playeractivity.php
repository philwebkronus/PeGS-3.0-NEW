<?php

/*
 * Description: Fetching and encoding data into JSON array to display Member Profile;
 *              Member Activity Details be displayed in JQGRID for list of Member Activity.
 * @Author: Junjun S. Hernandez
 * Date Created: 07-02-2013 05:00 PM
 */

//Attach and Initialize framework
require_once("../../init.inc.php");


//Load Modules to be use.
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Kronus", "Sites");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_MemberInfo = new MemberInfo();
$_TransactionSummary = new TransactionSummary();
$_Sites = new Sites();
$profile = null;
$response = null;


if (isset($_POST['pager'])) {
    $vpage = $_POST['pager'];
    switch ($vpage) {
        //for Card Number dropdown
        case "ProfileData":
            if (isset($_POST['Card']) && $_POST['Card'] != '') {
                $cardnumber = $_POST['Card'];
                $MIDResult = $_MemberCards->getMIDByCard($cardnumber);
                $countMD = count($MIDResult);

                if ($countMD == 0) {
                    $profile->MID = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Membership Account Status: Invalid Card Number';
                    $profile->Msg = $msg;
                } else {
                    $MemberInfoResult = $_MemberInfo->getMemberInfoByID($MIDResult[0]['MID']);
                    if(isset($MemberInfoResult[0]['MID']) && $MemberInfoResult[0]['MID'] != '')
                    {
                    $memberinfovalue['Age'] = $MemberInfoResult[0]['Age'];
                    $memberinfovalue['Gender'] = $MemberInfoResult[0]['Gender'] == 1 ? "Male" : "Female";
                    
                    if($MemberInfoResult[0]['Status'] == 1){
                        $memberinfovalue['Status'] = 'Active';
                    }
                    else if ($MemberInfoResult[0]['Status'] == 2) {
                        $memberinfovalue['Status'] = 'Suspended';
                    }
                    else if ($MemberInfoResult[0]['Status'] == 3) {
                        $memberinfovalue['Status'] = 'Locked (Attempts)';
                    }
                    else if ($MemberInfoResult[0]['Status'] == 4) {
                        $memberinfovalue['Status'] = 'Locked (Admin)';
                    }
                    else if ($MemberInfoResult[0]['Status'] == 5) {
                        $memberinfovalue['Status'] = 'Banned';
                    }
                    else if ($MemberInfoResult[0]['Status'] == 6) {
                        $memberinfovalue['Status'] = 'Terminated';
                    }
                    $profile->MID = $MIDResult[0]['MID'];
                    $profile->Age = $memberinfovalue['Age'];
                    $profile->Gender = $memberinfovalue['Gender'];
                    $profile->Status = $memberinfovalue['Status'];
                    }
                    else{
                       $profile->MID = '';
                       $profile->Age = '';
                       $profile->Gender = '';
                       $profile->Status = '';
                       $msg = 'Membership Account Status: Invalid Card Number';
                       $profile->Msg = $msg; 
                    }
                }
                echo json_encode($profile);
            }
            
            break;

        //for Query button, get the Transaction Summary details to populate the grid
        case "ActivityReport":
            if (isset($_POST['Card']) && $_POST['Card'] != '') {
                $cardnumber = $_POST['Card'];
                $MIDResult = $_MemberCards->getMIDByCard($cardnumber);
                $startdate = $_POST['fromTransDate'] . " " . App::getParam("cutofftime");
                $enddate = $_POST['toTransDate'] . " " . App::getParam("cutofftimeend");

                $countMD = count($MIDResult);

                if ($countMD == 0) {
                    $page = $_POST['page'];
                    $limit = $_POST['rows'];
                    $response->page = $page;
                    $response->total = $countMD;
                    $response->records = $countMD;
                    echo json_encode($response);
                    exit;
                } else {
                    if ((isset($startdate) != '') AND (isset($enddate) != '')) {

                        $MID = $MIDResult[0]['MID'];
                        $TransactionResult = $_TransactionSummary->getTransSummaryByMID($MID, $startdate, $enddate);
                        $count = count($TransactionResult);
                        $page = $_POST['page'];
                        $limit = $_POST['rows'];

                        $total_pages = ceil($count / $limit);
                        if ($page > $total_pages) {
                            $page = $total_pages;
                        }

                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        if ($count > 0) {

                            $total_pages = ceil($count / $limit);
                            if ($page > $total_pages) {
                                $page = $total_pages;
                            }

                            $response->page = $page;
                            $response->total = $total_pages;
                            $response->records = count($TransactionResult);

                            $ctr = 0;
                            do {
                                $siteid = $TransactionResult[$ctr]['SiteID'];
                                $SitesResult = $_Sites->getSiteName($siteid);
                                $SiteName = $SitesResult[0]['SiteName'];
                                if ($TransactionResult[$ctr]['PlayingTime'] != '') {
                                    $pTime = $TransactionResult[$ctr]['PlayingTime'];
                                    $PlayingTime = date('H:i:s', strtotime($pTime));
                                    $startD = $TransactionResult[$ctr]['DateStarted'];
                                    $endD = $TransactionResult[$ctr]['DateEnded'];

                                    $total_time[] = $PlayingTime;
                                } else {
                                    $PlayingTime = '';
                                    $total_time[] = 0;
                                }

                                if (is_null($total_time[$ctr]) || $total_time[$ctr] == '') {
                                    $totalsecs = 0;
                                } else {

                                    list($hr, $min, $sec) = preg_split("/\:/", $total_time[$ctr]);

                                    if ($min > 0) {
                                        $minnew = $min * 60;
                                    } else {
                                        $minnew = $min;
                                    }

                                    if ($hr > 0) {
                                        $hrnew = $hr * 60 * 60;
                                    } else {
                                        $hrnew = $hr;
                                    }
                                    $totalsecs = $hrnew + $minnew + $sec;
                                }

                                if ($PlayingTime == '' || is_null($PlayingTime)) {
                                    $PlayingTime = '00:00:00';
                                }

                                $dCreated = new DateTime($TransactionResult[$ctr]['DateStarted']);
                                $thisDate = $dCreated->format('m/d/Y');

                                $deposit = $TransactionResult[$ctr]['Deposit'];
                                $reload = $TransactionResult[$ctr]['Reload'];
                                $redemption = $TransactionResult[$ctr]['Withdrawal'];
                                $response->TransactionSummaryID = $TransactionResult[$ctr]['TransactionsSummaryID'];
                                $PlayerWin = number_format($redemption - ($deposit + $reload), 2, '.', '');
                                $response->rows[$ctr]['id'] = $TransactionResult[$ctr]['TransactionsSummaryID'];
                                $response->rows[$ctr]['cell'] = array(
                                    $thisDate,
                                    $SiteName,
                                    $PlayingTime,
                                    $deposit,
                                    $reload,
                                    $redemption,
                                    $PlayerWin,
                                    $totalsecs
                                );
                                $ctr++;
                            } while ($ctr != $count);
                        }
                    }
                    echo json_encode($response);
                    exit;
                }
            }
            break;

        //Get the Transaction Details to populate the subgrid            
        case "ActivityReport2":
            if (isset($_POST['transID']) && $_POST['transID'] != '') {
                $TransactionsSummaryID = $_POST['transID'];

                $page = $_POST['page'];
                $limit = $_POST['rows'];
                $TransDetails = $_TransactionSummary->getAmountReload($TransactionsSummaryID);
                $total_pages = ceil(count($TransDetails) / $limit);
                if ($page > $total_pages) {
                    $page = $total_pages;
                }

                $count = count($TransDetails);
                
                $responce->page = $page;
                $responce->total = $total_pages;
                $responce->records = $count;
                $ctr = 0;
                
                foreach ($TransDetails as $value2) {

                    $mergedep = 0;
                    $mergerel = 0;
                    $mergewith = 0;
                    $mergeoption1 = '';
                    $trans_details1 = array();
                    $trans_details2 = array();

                    foreach ($value2 as $value) {
                        
                        $mergedep = 0;
                        $mergerel = 0;
                        $mergewith = 0;
                        $mergeoption1 = '';
                        
                        $trans_details1[$value2['TransactionType']] = array(
                            'Deposit' => $value['Deposit'],
                            'Amount' => $value['Amount'],
                            'Withdrawal' => $value['Withdrawal']
                        );
                        
                        $trans1 = array();
                        switch ($value2['TransactionType']) {
                            case 'D':
                                $mergedep = $mergedep + $value2['Deposit'];
                                $trans1 = array('Deposit' => $mergedep, 'Amount' => 0, 'Withdrawal' => 0);
                                break;
                            case 'R':
                                $mergerel = $mergerel + $value2['Amount'];
                                $trans1 = array('Deposit' => 0, 'Amount' => $mergerel, 'Withdrawal' => 0);
                                break;
                            case 'W':
                                $mergewith = $mergewith + $value2['Withdrawal'];
                                $trans1 = array('Deposit' => 0, 'Amount' => 0, 'Withdrawal' => $mergewith);
                                break;
                        }
                        $trans_details1[$value2['TransactionType']] = array_merge($trans_details1[$value2['TransactionType']], $trans1);

                        foreach ($trans_details1 as $vview) {
                            $transid = $value2['TransactionsSummaryID'];
                            $depositamt = $vview['Deposit'];
                            $reloadamt = $vview['Amount'];
                            $withdrawamt = $vview['Withdrawal'];
                        }
                        
                        $trans2 = array();
                        
                        $trans_details2[$value2['PaymentType']] = array(
                            'Option1' => $value['Option1'],
                        );
                        
                        switch ($value2['PaymentType']) {
                            case 1:
                                $mergeoption1 = '';
                                $paymentmethod = 'Cash';
                                $trans2 = array('Option1' => $mergeoption1);
                                break;
                            case 2:
                                $itr = 0;
                                $transactionreferenceid = $TransDetails[$ctr]['TransactionReferenceID'];
                                $options = $_TransactionSummary->getOptions($transactionreferenceid);
                                $countOption = count($options);
                                do{
                                    $mergeoption1 = $options[$itr]['Option1'];
                                    $paymentmethod = 'Voucher';
                                    $itr++;
                                }while($itr != $countOption);
                                break;
                        }
                    }
                    $combined = array($depositamt, $reloadamt, $withdrawamt,$paymentmethod,$mergeoption1);
                    $responce->rows[$ctr]['id'] = $transid;
                    $responce->rows[$ctr]['cell'] = $combined;
                    $ctr++;
                }

                echo json_encode($responce);
                unset($trans_details1);
                exit;
            }
            break;
    }
}
?>