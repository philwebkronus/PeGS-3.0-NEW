<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../init.inc.php");
//Load modules to be use.
App::LoadModuleClass('Membership', 'BanningHistory');
App::LoadModuleClass('Membership', 'Members');
App::LoadModuleClass('Membership', 'MemberInfo');

//Initialize Modules
$_Members = new Members();
$_BanningHistory = new BanningHistory();
$_MemberInfo = new MemberInfo();

//if membership card link was clicked, card info will display
if(isset($_POST['MemberCardID']) && $_POST['MemberCardID'] != ''){

    $MemCardID = $_POST['MemberCardID'];
    $AccountBannedHistory = $_BanningHistory->getBanningHistoryUsingMemCardID($MemCardID);
    $count = count($AccountBannedHistory);
    $page = $_POST['page'];
    $limit = $_POST['rows'];
    
    if(isset($AccountBannedHistory) &&count($AccountBannedHistory) > 0)
    {
            $total_pages = ceil(count($AccountBannedHistory)/$limit);
            if ($page > $total_pages) {
                $page = $total_pages;
            }

            $response->page = $page;
            $response->total = $total_pages;
            $response->records = count($AccountBannedHistory);    
            $itr = 0;
            $ctr = 0;
            $ctr2 = 0;
            do {
                $MID = $AccountBannedHistory[0]['MID'];
                $meminfo = $_MemberInfo->getDateVerifiedByMID($MID);
                if(isset($meminfo[$itr]['DateVerified']) && $meminfo[$itr]['DateVerified'] != ''){
                    $dv = new DateTime($meminfo[$itr]['DateVerified']);
                    $dateVerified = $dv->format('m/d/Y');
                } else {
                    $dateVerified = '';
                }
                if($ctr == 0) {
                    $status = 'Active';
                    $DateCreated = $dateVerified;
                    $remarks = 'Account verified through email';
                }else{
                $status = $AccountBannedHistory[$ctr2]['Status'] == 1 ? "Banned":"Active";
                if(isset($AccountBannedHistory[$ctr2]['DateCreated']) && $AccountBannedHistory[$ctr2]['DateCreated'] != ''){
                    $dCreated = new DateTime($AccountBannedHistory[$ctr2]['DateCreated']);
                    $DateCreated = $dCreated->format('m/d/Y');
                } else {
                    $DateCreated = '';
                }
                $remarks = $AccountBannedHistory[$ctr2]['Remarks'];
                $ctr2++;
                }
                $response->rows[$itr]['id'] = $AccountBannedHistory[0]['MemberCardID'];
                $response->rows[$itr]['cell'] = array(
                                                $status, $DateCreated, $remarks
                );
                $itr++;
                $ctr++;
            }while($ctr2 != $count);
    } else {
        $itr = 0;
        $response->page = 0;
        $response->total = 0;
        $response->records = 0;
        $msg = "No Banned History";
        $response->msg = $msg;
    }
    
} else {
        
        $response = null;
        
        //on page load, display all banned cards
        $bannedAccounts  = $_Members->getAllBannedAccountsInfo();
        $count = count($bannedAccounts);
        $page = $_POST['page'];
        $limit = $_POST['rows'];
        
         if(isset($bannedAccounts) &&count($bannedAccounts) > 0)
        {
                //Get Last Date Banned.
                $itr = 0;
                do{
                    $MID = $bannedAccounts[$itr]['MID'];
                    $maxDate = $_BanningHistory->getMaxBannedDate($MID);
                    $lastBannedDate = new DateTime($maxDate);
                    $bannedAccounts[$itr]['ActionDate'] = $lastBannedDate->format('m-d-Y');
                    $itr++;
                }while($itr != $count);

                $total_pages = ceil(count($bannedAccounts)/$limit);
                if ($page > $total_pages) {
                    $page = $total_pages;
                }
                
                $response->page = $page;
                $response->total = $total_pages;
                $response->records = count($bannedAccounts);    
                $itr = 0;
                do {
                    $CardNo = $bannedAccounts[$itr]['CardNumber'];
                    $Age = $bannedAccounts[$itr]['Age'];
                    $Gender = $bannedAccounts[$itr]['Gender'] == 1 ? "Male":"Female" ;
                    $Nationality = $bannedAccounts[$itr]['Nationality'];
                    $MemCardID = $bannedAccounts[$itr]['MemberCardID'];
                    if(isset($bannedAccounts[$itr]['DateCreated']) && $bannedAccounts[$itr]['DateCreated'] != ''){
                        $dCreated = new DateTime($bannedAccounts[$itr]['DateCreated']);
                        $DateCreated = $dCreated->format('m-d-Y');
                    } else {
                        $DateCreated = '';
                    }
                    $ActionDate = $bannedAccounts[$itr]['ActionDate'];
                    $bannedAccounts[$itr]['CardNumber'] = "<input type='button' value='$CardNo' class='statuslink' MemberCardID='$MemCardID' CardNumber='$CardNo' Age='$Age' Gender='$Gender' ".
                                                    "Nationality='$Nationality'  style='{ overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                                    "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; }' >";
                    $response->rows[$itr]['id'] = $bannedAccounts[$itr]['MID'];
                    $response->rows[$itr]['cell'] = array(
                                                    $bannedAccounts[$itr]['CardNumber'], $DateCreated, $ActionDate
                    );
                    $itr++;
                }while($itr != $count);
        } else {
            $itr = 0;
            $response->page = 0;
            $response->total = 0;
            $response->records = 0;
            $msg = "No Banned Account";
            $response->msg = $msg;
        }
}

echo json_encode($response);
unset($response);
exit;

?>
