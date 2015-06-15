<?php

/*
 * @author : owliber
 * @date : 2013-04-26
 */

require_once("../init.inc.php");

/*
 * Load Module Classes
 */
App::LoadModuleClass("Loyalty", "ProcessPointsAPI");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");

/*
 * Load Core for API Response
 */
App::LoadCore('JSONAPIResponse.class.php');

$_ProcessPoints = new ProcessPointsAPI();
$_JSONAPIResponse = new JSONAPIResponse();
$_Log = new AuditTrail();

if((isset($_GET['cardnumber']) && ctype_alnum($_GET['cardnumber'])) //The members' member card number
        && isset($_GET['transdate']) //The date and time of the transaction
        && (isset($_GET['paymenttype']) && ctype_digit($_GET['paymenttype'])) //The type of payment
        && (isset($_GET['transtype']) && ctype_alpha($_GET['transtype']))//The type of transaction D, R, W, RD
        && (isset($_GET['amount']) && is_numeric($_GET['amount'])) //The transaction amount
        && (isset($_GET['siteid']) && ctype_digit($_GET['siteid'])) //The Site ID of where the transaction was made
        && (isset($_GET['serviceid']) && ctype_digit($_GET['serviceid'])) //The provider ID like Kronus etc.
        && (isset($_GET['transactionid']) && ctype_digit($_GET['transactionid'])) //The transaction reference ID from the client
        && isset($_GET['terminallogin']) //The terminal login used.
        && (isset($_GET['iscreditable']) && ctype_digit($_GET['iscreditable']))        
        && isset($_GET['vouchercode']))//The voucher code if is creditable is true) //True if voucher is used
{    
    $cardnumber = trim($_GET['cardnumber']);
    $transdate = trim(htmlentities($_GET['transdate']));
    $paymenttype = trim($_GET['paymenttype']);   
    $transactiontype = trim($_GET['transtype']);
    $amount = trim($_GET['amount']);
    $siteid= trim($_GET['siteid']);
    $serviceid = trim($_GET['siteid']);
    $transactionid = trim($_GET['transactionid']);
    $terminallogin = trim($_GET['terminallogin']);
    
    (!empty($_GET['vouchercode'])) ? $vouchercode = trim($_GET['vouchercode']) : $vouchercode = "";
    
    $iscreditable = trim($_GET['iscreditable']);
    
    $APIResult = $_ProcessPoints->AddPoints( $cardnumber, $transactionid, $transdate, $paymenttype, $transactiontype, $amount, $siteid, $serviceid, $terminallogin, $iscreditable, $vouchercode );
    
    if($APIResult && !App::HasError())
    {
        $result = array("AddPoints"=>array(
                                    "StatusCode"    => 1,
                                    "StatusMsg"     => 'Proccess points is successful',
                                    )
                        );
        
        $_Log->logAPI(AuditFunctions::PROCESS_POINTS, $cardnumber.':'.$amount.':'.$transactiontype.':Success', $siteid);
    
    }
    else
    {

        $result = array("AddPoints"=>array(
                                    "StatusCode"    => 2,
                                    "StatusMsg"     => 'Proccess points has failed',
                                    )
                        );
        
        $_Log->logAPI(AuditFunctions::PROCESS_POINTS, $cardnumber.':'.$amount.':'.$transactiontype.':Failed', $siteid);
    
        
    }
    
    //Send API Response
    $_JSONAPIResponse->_sendResponse(200, json_encode($result));
    
}
else
{
    $result = array("AddPoints"=>array(
                                    "StatusCode"    => 3,
                                    "StatusMsg"     => 'Invalid parameters',
                                    )
                        );
    
    //Send API Response
    $_JSONAPIResponse->_sendResponse(200, json_encode($result));
}
?>
