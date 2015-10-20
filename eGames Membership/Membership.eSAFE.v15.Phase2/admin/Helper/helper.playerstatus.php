<?php

/**
 *@Description: Fetching and encoding data into JSON array to be displayed in JQGRID for unban/ban player module.
 *@Author: aqdepliyan
 *@DateCreated: 06-28-2013 02:03 PM
 */

//Attach and Initialize framework
require_once("../../init.inc.php");

//clear session for carddata.
unset($_SESSION['CardData']);

//Load Modules to be use.
App::LoadModuleClass('Membership', 'BanningHistory');
App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass("Loyalty", "MemberCards");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_MemberInfo = new MemberInfo();
$_BanningHistory = new BanningHistory();

$txtSearch = $_POST['txtSearch'];
$validate = new Validation();
$searchValue = $txtSearch;
$memInfo = null;
$response = null;

if(isset($_POST['pager'])){
    $pager = $_POST['pager'];
    if($pager == 'GetBanUnban'){
        
        if(isset($_POST['txtSearch']) && $_POST['txtSearch'] != '' ){
    
        //clear session for carddata.
        unset($_SESSION['CardData']);

        //Load Modules to be use.
        App::LoadModuleClass('Membership', 'BanningHistory');
        App::LoadModuleClass('Membership', 'MemberInfo');
        App::LoadModuleClass("Loyalty", "MemberCards");

        //Load Needed Core Class.
        App::LoadCore('Validation.class.php');

        //Initialize Modules
        $_MemberCards = new MemberCards();
        $_MemberInfo = new MemberInfo();
        $_BanningHistory = new BanningHistory();

        $txtSearch = $_POST['txtSearch'];
        $validate = new Validation();
        $searchValue = $txtSearch;
        $memInfo = null;
        $response = null;


        if($validate->validateAlphaSpaceDashAndDot($searchValue)){
            $result =  $_MemberInfo->getMemberInfoByNameSP($searchValue);
            if(!empty($result)){
                $count = count($result);
            $_SESSION['CardData']['CardNumber'] = '';
            $_SESSION['CardData']['Name'] = $searchValue;
           if($count == 1) {
                   $MID = $result[0]['MID'];
                   $cardInfo = $_MemberCards->getMemberCardInfoByMID($MID);
                   if(isset($cardInfo[0])){
                       $bhstatus = $cardInfo[0]['Status'] == 1 ? "0":"1";
                       $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                       if(isset($remarks[0]) && $remarks[0]['Remarks'] != ''){
                           $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                       } else {
                           $memInfo[0]['Remarks'] =  '';
                       }
                       $memInfo[0]['MID'] =  $MID;
                       $memInfo[0]['CardNumber'] = $cardInfo[0]['CardNumber'];
                       $memInfo[0]['FullName'] = $result[0]['FirstName'].' '.$result[0]['LastName'];
                       $memInfo[0]['ID'] = $result[0]['IdentificationName'].' - '.$result[0]['IdentificationNumber'];
                       $bdate = new DateTime($result[0]['Birthdate']);
                       $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                       $memInfo[0]['Status'] = $cardInfo[0]['Status'];
                       $statusvalue = $cardInfo[0]['Status'] == 1  ?  "Active" : ($cardInfo[0]['Status'] == 5 ? "Banned": "");
                       
                       switch($cardInfo[0]['Status'])
                        {
                            case 1: $statusvalue = 'Active';    break;
                            case 2: $statusvalue = 'Suspended';break;
                            case 3: $statusvalue = 'Locked';break;
                            case 4: $statusvalue = 'Locked';break;
                            case 5: $statusvalue = 'Banned'; break;   
                            case 6: $statusvalue = 'Terminated';  break;
                            default: $statusvalue = 'Card Not Found'; break;
                        }
                       $memInfo[0]['StatusValue'] = $statusvalue;
                       $memInfo[0]['MemberCardID'] = $cardInfo[0]['MemberCardID'];
                   }


                    if(count($memInfo) > 0){

                        $itr = 0;
                         $response->records = count($memInfo);    
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }
               } elseif ($count > 1) {
                   $ctr1 = 0;
                   $ctr2 = 0;
                   do{
                       $MID = $result[$ctr1]['MID'];
                       $data = $_MemberCards->getMemberCardInfoByMID($MID);
                       if(isset($data[0])){
                           $bhstatus = $data[0]['Status'] == 1 ? "0":"1";
                           $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                           if(isset($remarks[0]['Remarks']) && $remarks[0]['Remarks'] != ''){
                               $memInfo[$ctr2]['Remarks'] = $remarks[0]['Remarks'];
                           } else {
                               $memInfo[$ctr2]['Remarks'] = '';
                           }
                           $memInfo[$ctr2]['MID'] = $MID;
                           $memInfo[$ctr2]['CardNumber'] = $data[0]['CardNumber'];
                           $memInfo[$ctr2]['FullName'] = $result[$ctr1]['LastName'].', '.$result[$ctr1]['FirstName'];
                           $memInfo[$ctr2]['ID'] = $result[$ctr1]['IdentificationName'].' - '.$result[$ctr1]['IdentificationNumber'];
                           $bdate = new DateTime($result[$ctr1]['Birthdate']);
                           $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
                           $statusvalue = $data[0]['Status'] == 1  ?  "Active" : ($data[0]['Status'] == 5 ? "Banned": "");
                           
                           switch($data[0]['Status'])
                            {
                                case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                            }
                           $memInfo[$ctr2]['Status'] = $data[0]['Status'];
                           $memInfo[$ctr2]['StatusValue'] = $statusvalue;
                           $memInfo[$ctr2]['MemberCardID'] = $data[0]['MemberCardID'];
                           $ctr2++;
                       }
                       $ctr1++;
                   }while($ctr1 != $count);
                    if(count($memInfo) > 0){
                        
                        $itr = 0;
                        $response->records = count($memInfo);
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }

               } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Player not found";
                    $response->msg = $msg;
               }
            }
            else{
                $membercards = $_MemberCards->getMemberCardInfoByCardNumber($searchValue);
            $count = count($membercards);
            $_SESSION['CardData']['Name'] = '';
            $_SESSION['CardData']['CardNumber'] = $searchValue;
            if($count > 0){
                    $MID = $membercards[0]['MID'];
                    $forBanning = 1;
                    $result = $_MemberInfo->getMemInfoUsingSP($MID, $forBanning);
                    $CardNumber = $_SESSION['CardData']['CardNumber'];

                    $bhstatus = $result[0]['Status'] == 1 ? "0":"1";
                    $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                    if(isset($remarks[0])){
                        $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                    } else {
                        $memInfo[0]['Remarks'] =  '';
                    }
                    $memInfo[0]['MID'] =  $MID;
                    $memInfo[0]['CardNumber'] = $CardNumber;
                    $memInfo[0]['FullName'] = $result['LastName'].', '.$result['FirstName'];
                    $memInfo[0]['ID'] = $result['IdentificationName'].' - '.$result['IdentificationNumber'];
                    $bdate = new DateTime($result['Birthdate']);
                    $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[0]['Status'] = $result['Status'];
                    $statusvalue = $result['Status'] == 1  ?  "Active" : ($result['Status'] == 5 ? "Banned": "");
                   
                    switch($result[0]['Status'])
                    {
                        case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                    }
                            
                    $memInfo[0]['StatusValue'] = $statusvalue;
                    $memInfo[0]['MemberCardID'] = $membercards[0]['MemberCardID'];

                    if(count($memInfo) > 0){
                        $itr = 0;
                        $response->records = count($memInfo);    
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }
                } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Invalid Card";
                    $response->msg = $msg;
                }
            }
            
        } elseif (preg_match ("/^[A-Za-z0-9]+$/", $searchValue)) {
            $membercards = $_MemberCards->getMemberCardInfoByCardSP($searchValue);
            $count = count($membercards);
            $_SESSION['CardData']['Name'] = '';
            $_SESSION['CardData']['CardNumber'] = $searchValue;

            if($count > 0){
                    $MID = $membercards[0]['MID'];

                    $forBanning = 1;
                    $result = $_MemberInfo->getMemInfoUsingSP($MID, $forBanning);
                    $CardNumber = $_SESSION['CardData']['CardNumber'];

                    $bhstatus = $result['Status'] == 1 ? "0":"1";
                    $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                    if(isset($remarks[0])){
                        $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                    } else {
                        $memInfo[0]['Remarks'] =  '';
                    }
                    $memInfo[0]['MID'] =  $MID;
                    $memInfo[0]['CardNumber'] = $CardNumber;
                    $memInfo[0]['FullName'] = $result['LastName'].', '.$result['FirstName'];
                    $memInfo[0]['ID'] = $result['IdentificationName'].' - '.$result['IdentificationNumber'];
                    $bdate = new DateTime($result['Birthdate']);
                    $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[0]['Status'] = $result['Status'];
                    $statusvalue = $result['Status'] == 1  ?  "Active" : ($result['Status'] == 5 ? "Banned": "");
                    switch($result['Status'])
                    {
                        case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                    }
                    
                    $memInfo[0]['StatusValue'] = $statusvalue;
                    $memInfo[0]['MemberCardID'] = $membercards[0]['MemberCardID'];
                    if(count($memInfo) > 0){
                        $itr = 0;
                        $response->records = count($memInfo);    
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }
                } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Invalid Card";
                    $response->msg = $msg;
                }
        } else {
            $itr = 0;
            $response->page = 0;
            $response->total = 0;
            $response->records = 0;
            $msg = "Invalid Input";
            $response->msg = $msg;
        }
    } else {
        $itr = 0;
        $response->page = 0;
        $response->total = 0;
        $response->records = 0;
        $msg = "Needed Input is empty";
        $response->msg = $msg;
    }
    $count = $response->records;
    if($count > 0){
        $response = $count;
    }
    else{
        $recordinfo = array(
                    array(
                      'RecordCount'  => $response->records,
                      'ErrorMsg'  => $response->msg,
                    )
                      );
    
        $response = $recordinfo; 
    }   
    
    }
    elseif($pager == 'GetBanUnbanGrid'){
        
        if(isset($_POST['txtSearch']) && $_POST['txtSearch'] != '' ){
    
        $page = $_POST['page'];
        $limit = $_POST['rows'];

        if($validate->validateAlphaSpaceDashAndDot($searchValue)){
            $result =  $_MemberInfo->getMemberInfoByNameSP($searchValue);
            
            if(!empty($result)){
                $count = count($result);
            $_SESSION['CardData']['CardNumber'] = '';
            $_SESSION['CardData']['Name'] = $searchValue;
           if($count == 1) {
                   $MID = $result[0]['MID'];
                   $cardInfo = $_MemberCards->getMemberCardInfoByMID($MID);
                   if(isset($cardInfo[0])){
                       $bhstatus = $cardInfo[0]['Status'] == 1 ? "0":"1";
                       $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                       if(isset($remarks[0]) && $remarks[0]['Remarks'] != ''){
                           $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                       } else {
                           $memInfo[0]['Remarks'] =  '';
                       }
                       $memInfo[0]['MID'] =  $MID;
                       $memInfo[0]['CardNumber'] = $cardInfo[0]['CardNumber'];
                       $memInfo[0]['FullName'] = $result[0]['LastName'].', '.$result[0]['FirstName'];
                       $memInfo[0]['ID'] = $result[0]['IdentificationName'].' - '.$result[0]['IdentificationNumber'];
                       $bdate = new DateTime($result[0]['Birthdate']);
                       $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                       $memInfo[0]['Status'] = $cardInfo[0]['Status'];
                       $statusvalue = $cardInfo[0]['Status'] == 1  ?  "Active" : ($cardInfo[0]['Status'] == 5 ? "Banned": "");
                       
                       switch($cardInfo[0]['Status'])
                        {
                            case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                        }
                       $memInfo[0]['StatusValue'] = $statusvalue;
                       $memInfo[0]['MemberCardID'] = $cardInfo[0]['MemberCardID'];
                   }

                   $total_pages = ceil(count($memInfo)/$limit);
                    if ($page > $total_pages) {
                        $page = $total_pages;
                    }

                    $start = $limit * $page -$limit;

                    if(count($memInfo) > 0){

                        $itr = 0;
                        $response->page = $page;
                         $response->total = $total_pages;
                         $response->records = count($memInfo);    
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }
               } elseif ($count > 1) {
                   $ctr1 = 0;
                   $ctr2 = 0;
                   do{
                       $MID = $result[$ctr1]['MID'];
                       $data = $_MemberCards->getMemberCardInfoByMID($MID);
                       if(isset($data[0])){
                           $bhstatus = $data[0]['Status'] == 1 ? "0":"1";
                           $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                           if(isset($remarks[0]['Remarks']) && $remarks[0]['Remarks'] != ''){
                               $memInfo[$ctr2]['Remarks'] = $remarks[0]['Remarks'];
                           } else {
                               $memInfo[$ctr2]['Remarks'] = '';
                           }
                           $memInfo[$ctr2]['MID'] = $MID;
                           $memInfo[$ctr2]['CardNumber'] = $data[0]['CardNumber'];
                           $memInfo[$ctr2]['FullName'] = $result[$ctr1]['LastName'].', '.$result[$ctr1]['FirstName'];
                           $memInfo[$ctr2]['ID'] = $result[$ctr1]['IdentificationName'].' - '.$result[$ctr1]['IdentificationNumber'];
                           $bdate = new DateTime($result[$ctr1]['Birthdate']);
                           $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
                           $statusvalue = $data[0]['Status'] == 1  ?  "Active" : ($data[0]['Status'] == 5 ? "Banned": "");
                           
                           switch($data[0]['Status'])
                            {
                                case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                            }
                           $memInfo[$ctr2]['Status'] = $data[0]['Status'];
                           $memInfo[$ctr2]['StatusValue'] = $statusvalue;
                           $memInfo[$ctr2]['MemberCardID'] = $data[0]['MemberCardID'];
                           $ctr2++;
                       }
                       $ctr1++;
                   }while($ctr1 != $count);
                    if(count($memInfo) > 0){

                        $total_pages = ceil(count($memInfo)/$limit);
                        if ($page > $total_pages) {
                            $page = $total_pages;
                        }

                        $start = $limit * $page -$limit;
                    
                        $itr = 0;
                        $response->page = $page;
                        $response->records = count($memInfo);
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }

               } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Player not found";
                    $response->msg = $msg;
               }
            }
            else{
                            $membercards = $_MemberCards->getMemberCardInfoByCardNumber($searchValue);
            $count = count($membercards);
            $_SESSION['CardData']['Name'] = '';
            $_SESSION['CardData']['CardNumber'] = $searchValue;

            if($count > 0){
                    $MID = $membercards[0]['MID'];
                    $forBanning = 1;
                    $result = $_MemberInfo->getMemInfoUsingSP($MID, $forBanning);
                    $CardNumber = $_SESSION['CardData']['CardNumber'];

                    $bhstatus = $result[0]['Status'] == 1 ? "0":"1";
                    $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                    if(isset($remarks[0])){
                        $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                    } else {
                        $memInfo[0]['Remarks'] =  '';
                    }
                    $memInfo[0]['MID'] =  $MID;
                    $memInfo[0]['CardNumber'] = $CardNumber;
                    $memInfo[0]['FullName'] = $result['LastName'].', '.$result['FirstName'];
                    $memInfo[0]['ID'] = $result['IdentificationName'].' - '.$result['IdentificationNumber'];
                    $bdate = new DateTime($result['Birthdate']);
                    $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[0]['Status'] = $result['Status'];
                    $statusvalue = $result['Status'] == 1  ?  "Active" : ($result['Status'] == 5 ? "Banned": "");
                    
                    switch($result[0]['Status'])
                    {
                        case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                    }
                    $memInfo[0]['StatusValue'] = $statusvalue;
                    $memInfo[0]['MemberCardID'] = $membercards[0]['MemberCardID'];

                    if(count($memInfo) > 0){
                        $total_pages = ceil(count($memInfo)/$limit);
                        if ($page > $total_pages) {
                            $page = $total_pages;
                        }
                        $itr = 0;
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = count($memInfo);    
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }
                } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Invalid Card";
                    $response->msg = $msg;
                }
            }
            
        } elseif (preg_match ("/^[A-Za-z0-9]+$/", $searchValue)) {
            $membercards = $_MemberCards->getMemberCardInfoByCardNumber($searchValue);
            $count = count($membercards);
            $_SESSION['CardData']['Name'] = '';
            $_SESSION['CardData']['CardNumber'] = $searchValue;

            if($count > 0){
                    $MID = $membercards[0]['MID'];
                    $forBanning = 1;
                    $result = $_MemberInfo->getMemInfoUsingSP($MID, $forBanning);
                    $CardNumber = $_SESSION['CardData']['CardNumber'];
                    
                    $bhstatus = $result['Status'] == 1 ? "0":"1";
                    $remarks = $_BanningHistory->getRemarksSP($MID, $bhstatus);
                    if(isset($remarks[0])){
                        $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                    } else {
                        $memInfo[0]['Remarks'] =  '';
                    }
                    $memInfo[0]['MID'] =  $MID;
                    $memInfo[0]['CardNumber'] = $CardNumber;
                    $memInfo[0]['FullName'] = $result['LastName'].', '.$result['FirstName'];
                    $memInfo[0]['ID'] = $result['IdentificationName'].' - '.$result['IdentificationNumber'];
                    $bdate = new DateTime($result['Birthdate']);
                    $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[0]['Status'] = $result['Status'];
                    $statusvalue = $result['Status'] == 1  ?  "Active" : ($result['Status'] == 5 ? "Banned": "");
                    switch($result['Status'])
                    {
                        case 1: $statusvalue = 'Active';    break;
                                case 2: $statusvalue = 'Suspended';break;
                                case 3: $statusvalue = 'Locked';break;
                                case 4: $statusvalue = 'Locked';break;
                                case 5: $statusvalue = 'Banned'; break;   
                                case 6: $statusvalue = 'Terminated';  break;
                                default: $statusvalue = 'Card Not Found'; break;
                    }
                    $memInfo[0]['StatusValue'] = $statusvalue;
                    $memInfo[0]['MemberCardID'] = $membercards[0]['MemberCardID'];

                    if(count($memInfo) > 0){
                        $total_pages = ceil(count($memInfo)/$limit);
                        if ($page > $total_pages) {
                            $page = $total_pages;
                        }
                        $itr = 0;
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = count($memInfo);    
                        foreach ($memInfo as $value) {
                            $row = $value;
                            $MemCardID = $row['MemberCardID'];
                            $CardNo = $row['CardNumber'];
                            $m = $row['MID'];
                            $stat = $row['Status'];
                            $statval = $row['StatusValue'];
                            if($stat == 1 || $stat == 5){
                               $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                            "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >"; 
                            }
                            else {
                                $row['Status'] = $statval;
                            }
                            
                            $response->rows[$itr]['id'] = $row['MID'];
                            $response->rows[$itr]['cell'] = array(
                                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['Status'],$row['Remarks']
                            );
                            $itr++;
                        }
                    } else {
                        $itr = 0;
                        $response->page = 0;
                        $response->total = 0;
                        $response->records = 0;
                        $msg = "No Record found";
                        $response->msg = $msg;
                    }
                } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Invalid Card";
                    $response->msg = $msg;
                }
        } else {
            $itr = 0;
            $response->page = 0;
            $response->total = 0;
            $response->records = 0;
            $msg = "Invalid Input";
            $response->msg = $msg;
        }
    } else {
        $itr = 0;
        $response->page = 0;
        $response->total = 0;
        $response->records = 0;
        $msg = "Needed Input is empty";
        $response->msg = $msg;
    }
        
    }
}

echo json_encode($response);
unset($memInfo);
exit;

?>
