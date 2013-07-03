<?php

/*
 * Description: Fetching and encoding data into JSON array to be displayed in JQGRID for list of banned players.
 *@Author: aqdepliyan
 * Date Created: 07-01-2013 02:27 PM
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
$validate = new Validation();
$memInfo = null;
$response = null;

if(isset($_POST['txtSearch']) && $_POST['txtSearch'] != ''){
    $txtSearch = $_POST['txtSearch'];
    $searchValue = $txtSearch;
    $page = $_POST['page'];
    $limit = $_POST['rows'];
    
    if($validate->validateAlphaSpaceDashAndDot($searchValue)){
        $_SESSION['CardData']['Name'] = $searchValue;
        $result =  $_MemberInfo->getMemberInfoByName($_SESSION['CardData']['Name']);
        $count = count($result);
        if($count == 1) {
            $MID = $result[0]['MID'];
            $cardInfo = $_MemberCards->getMemberCardInfoByMID($MID);
            if($cardInfo[0]['Status'] == 5 ){
                $remarks = $_BanningHistory->getRemarks($MID, 1);
                if(isset($remarks[0])){
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
            } else {
                $itr = 0;
                $response->page = 0;
                $response->total = 0;
                $response->records = 0;
                $msg = "Player is not banned.";
                $response->msg = $msg;
            }

        } elseif ($count > 1) {
            $ctr1 = 0;
            $ctr2 = 0;
            $ctr3 = 0;
            do{
                $MID = $result[$ctr1]['MID'];
                $data = $_MemberCards->getMemberCardInfoByMID($MID);

                if(isset($data[0]) &&$data[0]['Status'] == 5 ){
                    $remarks = $_BanningHistory->getRemarks($MID, 1);
                    if(isset($remarks[0])){
                        $data[0]['Remarks'] = $remarks[0]['Remarks'];
                    } else {
                        $data[0]['Remarks'] = '';
                    }
                    $data[0]['MID'] = $MID;
                    $data[0]['LastName'] = $result[$ctr1]['LastName'];
                    $data[0]['FirstName'] = $result[$ctr1]['FirstName'];
                    $data[0]['IdentificationName'] = $result[$ctr1]['IdentificationName'];
                    $data[0]['IdentificationNumber'] = $result[$ctr1]['IdentificationNumber'];
                    $data[0]['Birthdate'] = $result[$ctr1]['Birthdate'];
                    $cardInfo[$ctr3] = $data[0];
                    $ctr3++;
                }
                $ctr1++;
            }while($ctr1 != $count);
            if(isset($cardInfo) && count($cardInfo) != 0){
                do{
                    $memInfo[$ctr2]['Remarks'] =  $cardInfo[$ctr2]['Remarks'];
                    $memInfo[$ctr2]['MID'] =  $cardInfo[$ctr2]['MID'];
                    $memInfo[$ctr2]['FullName'] = $cardInfo[$ctr2]['LastName'].', '.$cardInfo[$ctr2]['FirstName'];
                    $memInfo[$ctr2]['ID'] = $cardInfo[$ctr2]['IdentificationName'].' - '.$cardInfo[$ctr2]['IdentificationNumber'];
                    $bdate = new DateTime($cardInfo[$ctr2]['Birthdate']);
                    $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[$ctr2]['Status'] = $cardInfo[$ctr2]['Status'];
                    $statusvalue = $cardInfo[$ctr2]['Status'] == 1  ?  "Active" : ($cardInfo[$ctr2]['Status'] == 5 ? "Banned": "");
                    $memInfo[$ctr2]['StatusValue'] = $statusvalue;
                    $memInfo[$ctr2]['CardNumber'] = $cardInfo[$ctr2]['CardNumber'];
                    $memInfo[$ctr2]['MemberCardID'] = $cardInfo[$ctr2]['MemberCardID'];
                    $ctr2++;
                }while($ctr2 != count($cardInfo));
            } else {
                $itr = 0;
                $response->page = 0;
                $response->total = 0;
                $response->records = 0;
                $msg = "Player is not banned.";
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
        $_SESSION['CardData']['CardNumber'] = $searchValue;
        $membercards = $_MemberCards->getMemberCardInfoByCardNumber($_SESSION['CardData']['CardNumber']);
        $count = count($membercards);
        if($count > 0){
                $MID = $membercards[0]['MID'];
                $result = $_MemberInfo->getMemberInfoByMID($MID);
                $CardNumber = $_SESSION['CardData']['CardNumber'];
                if(isset($result[0]) && $result[0]['Status'] == 5){
                    $remarks = $_BanningHistory->getRemarks($MID, 1);
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
                } else {
                    $itr = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "Card is not banned.";
                    $response->msg = $msg;
                }
            } else {
                $itr = 0;
                $response->page = 0;
                $response->total = 0;
                $response->records = 0;
                $msg = "Card not found";
                $response->msg = $msg;
            }
    }
    
    if(isset($memInfo) &&count($memInfo) > 0){
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
            $response->rows[$itr]['id'] = $row['MID'];
            $response->rows[$itr]['cell'] = array(
                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['StatusValue'],$row['Remarks']
            );
            $itr++;
        }
    }

} else {
    
    $page = $_POST['page'];
    $limit = $_POST['rows'];
    $memcards = $_MemberCards->getAllBannedMemberCardInfo();
    $count = count($memcards);
    
    if($count > 1){
        $ctr1 = 0;
        $ctr2 = 0;
        do{
            $MID = $memcards[$ctr1]['MID'];
            $data = $_MemberInfo->getMemberInfoByMID($MID);
            $remarks = $_BanningHistory->getRemarks($MID, 1);
            if(isset($remarks[0])){
                $data[0]['Remarks'] = $remarks[0]['Remarks'];
            } else {
                $data[0]['Remarks'] = '';
            }
            $data[0]['MID'] = $MID;
            $data[0]['MemberCardID'] = $memcards[$ctr1]['MemberCardID'];
            $data[0]['CardNumber'] = $memcards[$ctr1]['CardNumber'];
            $cardInfo[$ctr1] = $data[0];
            $ctr1++;
        }while($ctr1 != $count);

        do{
            $memInfo[$ctr2]['Remarks'] =  $cardInfo[$ctr2]['Remarks'];
            $memInfo[$ctr2]['MID'] =  $cardInfo[$ctr2]['MID'];
            $memInfo[$ctr2]['FullName'] = $cardInfo[$ctr2]['LastName'].', '.$cardInfo[$ctr2]['FirstName'];
            $memInfo[$ctr2]['ID'] = $cardInfo[$ctr2]['IdentificationName'].' - '.$cardInfo[$ctr2]['IdentificationNumber'];
            $bdate = new DateTime($cardInfo[$ctr2]['Birthdate']);
            $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
            $memInfo[$ctr2]['Status'] = $cardInfo[$ctr2]['Status'];
            $statusvalue = $cardInfo[$ctr2]['Status'] == 1  ?  "Active" : ($cardInfo[$ctr2]['Status'] == 5 ? "Banned": "");
            $memInfo[$ctr2]['StatusValue'] = $statusvalue;
            $memInfo[$ctr2]['CardNumber'] = $cardInfo[$ctr2]['CardNumber'];
            $memInfo[$ctr2]['MemberCardID'] = $cardInfo[$ctr2]['MemberCardID'];
            $ctr2++;
        }while($ctr2 != $count);

    }
    
    if(isset($memInfo) &&count($memInfo) > 0){
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
            $response->rows[$itr]['id'] = $row['MID'];
            $response->rows[$itr]['cell'] = array(
                                            $row['FullName'], $row['CardNumber'], $row['ID'],$row['Birthdate'], $row['StatusValue'],$row['Remarks']
            );
            $itr++;
        }
    }
}

echo json_encode($response);
unset($memInfo);
exit;

?>
