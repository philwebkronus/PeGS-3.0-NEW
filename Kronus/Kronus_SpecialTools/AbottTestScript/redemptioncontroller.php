<?php
include 'model/redemptionmodel.php';
include 'redemptionuserbased.php';

$terminalcode = $_POST['terminalcode'];
$acctid = $_POST['acctid'];
$amount = $_POST['amount'];

$redemptionmodel = new redemptionmodel('mysql:host=172.16.102.157;dbname=npos,pegsconn,pegsconnpass');
$connected = $redemptionmodel->open();


if($connected)
{
    $arrterminalid = $redemptionmodel->getTerminalID($terminalcode);
    
        $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
        $datetime = array('StartTime'=>$date);
    
    
    $terminalid = $arrterminalid['TerminalID'];
    $siteid = $arrterminalid['SiteID'];
    
    $lastsessiondetails = $redemptionmodel->getLastSessionDetails($terminalid);
    
    foreach ($lastsessiondetails as $val) {
        $casinoUsername = $val['UBServiceLogin'];
        $casinoPassword = $val['UBServicePassword'];
        $casinoHashedPwd = $val['UBHashedServicePassword'];
        $mid = $val['MID'];
        $loyaltyCardNo = $val['LoyaltyCardNumber'];
        $casinoUserMode = $val['UserMode'];
        $casinoServiceID = $val['ServiceID'];
    }
    
    $servicesdetails = $redemptionmodel->getServiceDetails($casinoServiceID);
    
    if($servicesdetails['UserMode'] == 0){
        
        $redemptionuserbased = new redemptionuserbased();
        $login_pwd  = $casinoHashedPwd;
        
        $result = $redemptionuserbased->redeem($redemptionmodel, $login_pwd, $terminalid, $siteid, 1, $casinoServiceID, $amount, 1, $acctid, $loyaltyCardNo, $terminalcode, $mid, $casinoUserMode,$casinoUsername,
                            $casinoPassword,$casinoServiceID);
        
    }
    
    
    if($servicesdetails['UserMode'] == 1){
        $redemptionuserbased = new redemptionuserbased();
        $login_pwd  = $casinoHashedPwd;
        
        $result = $redemptionuserbased->redeem($redemptionmodel, $login_pwd, $terminalid, $siteid, 1, $casinoServiceID, $amount, 1, $acctid, $loyaltyCardNo, $terminalcode, $mid, $casinoUserMode,$casinoUsername,
                            $casinoPassword,$casinoServiceID);
    }
    
    array_push($result, $datetime);
    
    $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
    $datetime2 = array('EndTime'=>$date);
    
    array_push($result, $datetime2);
    
    echo json_encode($result);
    
    
}


?>
