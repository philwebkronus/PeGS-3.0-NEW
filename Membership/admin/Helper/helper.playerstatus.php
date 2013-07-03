<?php

/*
 * Description: Fetching and encoding data into JSON array to be displayed in JQGRID for unban/ban player module.
 *@Author: aqdepliyan
 * Date Created: 06-28-2013 02:03 PM
 */

//Attach and Initialize framework
require_once("../../init.inc.php");

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
    $page = $_POST['page'];
    $limit = $_POST['rows'];
    
    if($validate->validateAlphaSpaceDashAndDot($searchValue)){
        $result =  $_MemberInfo->getMemberInfoByName($searchValue);
        $count = count($result);
        $_SESSION['CardData']['CardNumber'] = '';
        $_SESSION['CardData']['Name'] = $searchValue;
       if($count == 1) {
               $MID = $result[0]['MID'];
               $cardInfo = $_MemberCards->getMemberCardInfoByMID($MID);
               if(isset($cardInfo[0])){
                   $bhstatus = $cardInfo[0]['Status'] == 1 ? "0":"1";
                   $remarks = $_BanningHistory->getRemarks($MID, $bhstatus);
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
                        $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                        "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                        "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >";
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
                       $remarks = $_BanningHistory->getRemarks($MID, $bhstatus);
                       if(isset($remarks[0]['Remarks']) && $remarks[0]['Remarks'] != ''){
                           $data[0]['Remarks'] = $remarks[0]['Remarks'];
                       } else {
                           $data[0]['Remarks'] = '';
                       }
                       $data[0]['MID'] = $MID;
                       $data[0]['FullName'] = $result[$ctr1]['LastName'].', '.$result[$ctr1]['FirstName'];
                       $data[0]['ID'] = $result[$ctr1]['IdentificationName'].' - '.$result[$ctr1]['IdentificationNumber'];
                       $bdate = new DateTime($result[$ctr1]['Birthdate']);
                       $data[0]['Birthdate'] = $bdate->format('m/d/Y');
                       $statusvalue = $data[0]['Status'] == 1  ?  "Active" : ($data[0]['Status'] == 5 ? "Banned": "");
                       $data[0]['StatusValue'] = $statusvalue;
                       $memInfo[$ctr2] = $data[0];
                       $ctr2++;
                   }
                   $ctr1++;
               }while($ctr1 != $count);
               
               $total_pages = ceil(count($memInfo)/$limit);
                if ($page > $total_pages) {
                    $page = $total_pages;
                }

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
                        $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                        "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                        "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >";
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
    } elseif (preg_match ("/^[A-Za-z0-9]+$/", $searchValue)) {
        $membercards = $_MemberCards->getMemberCardInfoByCardNumber($searchValue);
        $count = count($membercards);
        $_SESSION['CardData']['Name'] = '';
        $_SESSION['CardData']['CardNumber'] = $searchValue;

        if($count > 0){
                $MID = $membercards[0]['MID'];
                $result = $_MemberInfo->getMemberInfoByMID($MID);
                $CardNumber = $_SESSION['CardData']['CardNumber'];

                $bhstatus = $result[0]['Status'] == 1 ? "0":"1";
                $remarks = $_BanningHistory->getRemarks($MID, $bhstatus);
                if(isset($remarks[0])){
                    $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                } else {
                    $memInfo[0]['Remarks'] =  '';
                }
                $memInfo[0]['MID'] =  $MID;
                $memInfo[0]['CardNumber'] = $CardNumber;
                $memInfo[0]['FullName'] = $result[0]['LastName'].', '.$result[0]['FirstName'];
                $memInfo[0]['ID'] = $result[0]['IdentificationName'].' - '.$result[0]['IdentificationNumber'];
                $bdate = new DateTime($result[0]['Birthdate']);
                $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                $memInfo[0]['Status'] = $result[0]['Status'];
                $statusvalue = $result[0]['Status'] == 1  ?  "Active" : ($result[0]['Status'] == 5 ? "Banned": "");
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
                        $row['Status'] = "<input type='button' value='$statval' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' MID='$m' Status='$stat' ".
                                                        "  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                        "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >";
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

echo json_encode($response);
unset($memInfo);
exit;

?>
