<?php
/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-17
 * ***************** */
require_once("../init.inc.php");

App::LoadCore("JSONAPIResponse.class.php");
App::LoadModuleClass("Loyalty", "PointsTransferAPI");
App::LoadModuleClass("Loyalty", "OldCards");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadCore('ErrorLogger.php');        

$_PointsTransferAPI = new PointsTransferAPI();
$_JSONAPIResponse = new JSONAPIResponse();
$_OldCards = new OldCards();
$_MemberCards = new MemberCards();
$_Log = new AuditTrail();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

if((isset($_GET["oldnumber"]) && ctype_alnum($_GET["oldnumber"])) && 
   (isset($_GET["newnumber"]) && ctype_alnum( $_GET["newnumber"])) &&
   (isset($_GET["aid"]) && ctype_digit($_GET["aid"])))
{
    $oldcardnumber = $_GET["oldnumber"];
    $newcardnumber = $_GET["newnumber"];
    $AID = $_GET["aid"];
    
    $oldcardresult = $_OldCards->getOldCardInfo($oldcardnumber);
    $newcardresult = $_MemberCards->getMemberCardInfoByCard($newcardnumber);
    if(empty($oldcardresult))
    {
        $verifyoldcard = CardStatus::NOT_EXIST;;
    }
    elseif(empty($newcardresult))
    {
        $verifynewcard = CardStatus::NOT_EXIST;;
    }
    else
    {
        $verifyoldcard = $oldcardresult[0]["CardStatus"];
        $verifynewcard = $newcardresult[0]["Status"];
    }
    
    if ($verifyoldcard != CardStatus::OLD)
    {
        if($verifyoldcard==CardStatus::OLD)
        {
            $status = CardStatus::OLD;
            $statusmsg = "Old Loyalty Card";
        }
        elseif($verifyoldcard==CardStatus::OLD_MIGRATED)
        {                
            $status = CardStatus::OLD_MIGRATED;
            $statusmsg = "Migrated Card";
        }
        elseif($verifyoldcard==CardStatus::NOT_EXIST)
        {
            $status = CardStatus::NOT_EXIST;
            $logger->logger($logdate, $logtype, "Card Not Found[005]: ".$oldcardnumber);
            $statusmsg = "Card not found";
        }
        $_JSONAPIResponse->_sendResponse(200, json_encode(array("CardPoints"=>array("LoyaltyCardPoints"=>"", "MembershipCardPoints"=>"", "StatusCode"=>(int)$status, "StatusMsg"=>$statusmsg))));
    }
    elseif($verifynewcard != CardStatus::ACTIVE)
    {
        if($verifynewcard==CardStatus::INACTIVE)
        {
            $status = CardStatus::INACTIVE;
            $statusmsg = "Inactive Card"; 
        }
        elseif($verifynewcard==CardStatus::ACTIVE)
        {
            $status = CardStatus::ACTIVE;
            $statusmsg = "Active Card"; 
        }
        elseif($verifynewcard==CardStatus::DEACTIVATED)
        {
            $status = CardStatus::DEACTIVATED;
            $statusmsg = "Deactivated Card";
        }
        elseif($verifynewcard==CardStatus::OLD)
        {
            $status = CardStatus::OLD;
            $statusmsg = "Old Loyalty Card";
        }elseif($verifynewcard==CardStatus::NEW_MIGRATED)
        {
            $status = CardStatus::NEW_MIGRATED;
            $statusmsg = "Migrated Card";
        }
        elseif($verifynewcard==CardStatus::NOT_EXIST)
        {
            $status = CardStatus::NOT_EXIST;
            $statusmsg = "Card not found";
        }
        $_JSONAPIResponse->_sendResponse(200, json_encode(array("CardPoints"=>array("LoyaltyCardPoints"=>"", "MembershipCardPoints"=>"", "StatusCode"=>(int)$status, "StatusMsg"=>$statusmsg))));
    }
    else
    {
        $oldresult = $_OldCards->getOldCardInfo($oldcardnumber);
        $oldrow = $oldresult[0];

        $newresult = $_MemberCards->getMemberCardInfoByCard($newcardnumber);
        $newrow = $newresult[0];

        //Check if Loyalty                                     
        $isLoyalty =  App::getParam('PointSystem'); 
      
        if ($isLoyalty == 1) {

            //Add points from old loyalty card to lifetime and current points
            $newlifetimepoints = $oldrow["LifetimePoints"] + $newrow["LifetimePoints"];
            $newcurrentpoints = ($oldrow["LifetimePoints"] - $oldrow["RedeemedPoints"]) + ($newrow["LifetimePoints"] - $newrow["RedeemedPoints"]);
            $newredeemedpoints = $oldrow["RedeemedPoints"] + $newrow["RedeemedPoints"];
        } else {
            App::LoadModuleClass("Loyalty", "GetCardInfoAPI");

            $_GetCardInfoAPI = new GetCardInfoAPI();

            $compPoints = $_GetCardInfoAPI->getCompPoints($newcardnumber);

            //Add points from old loyalty card to lifetime and current points
            $newlifetimepoints = $compPoints + ($oldrow["LifetimePoints"] - $oldrow["RedeemedPoints"]);
            $newcurrentpoints = $compPoints + ($oldrow["LifetimePoints"] - $oldrow["RedeemedPoints"]);
            $tobeadded = $oldrow["LifetimePoints"] - $oldrow["RedeemedPoints"];
            if ($oldrow["RedeemedPoints"] > 0) {
                $newredeemedpoints = $oldrow["RedeemedPoints"] + $newrow["RedeemedPoints"];
            } else {
                $newredeemedpoints = 0;
            }
        }

        $datecreated = "NOW(6)";
        $oldtonew = "1";

        //Array to insert to CardPointsTransfer Table
        $arrCardPointsTransfer["MID"] = $newrow["MID"];
        $arrCardPointsTransfer["FromOldCardID"] = $oldrow["OldCardID"];
        $arrCardPointsTransfer["ToMemberCardID"] = $newrow["MemberCardID"];
        $arrCardPointsTransfer["LifetimePoints"] = $newlifetimepoints;
        $arrCardPointsTransfer["CurrentPoints"] = $newcurrentpoints;
        $arrCardPointsTransfer["RedeemedPoints"] = $newredeemedpoints;
        $arrCardPointsTransfer["BonusPoints"] = $newrow["BonusPoints"];
        $arrCardPointsTransfer["RedeemedBonusPoints"] = $newrow["RedeemedBonusPoints"];
        $arrCardPointsTransfer["DateTransferred"] = $datecreated;
        $arrCardPointsTransfer["TransferredByAID"] = $AID;
        $arrCardPointsTransfer["OldToNew"] = $oldtonew;


        
        //Array to update in CardPoints Table
        $arrCardPoints["LifeTimePoints"] = $newlifetimepoints;
        $arrCardPoints["CurrentPoints"] = $newcurrentpoints;
        $arrCardPoints["RedeemedPoints"] = $newredeemedpoints;
        $arrCardPoints["DateUpdated"] = $datecreated;
        $arrCardPoints["UpdatedByAID"] = $AID;

        $cardstatus = CardStatus::OLD_MIGRATED;
        //Array to update in OldCards Table
        $arrOldCards["CardStatus"] = $cardstatus;
        
        //Loyalty points
        if ($isLoyalty == 1) {
            // Proceed to Transfer Points Process
            $_PointsTransferAPI->ProcessCardPointsTransfer($arrCardPointsTransfer, $arrCardPoints, $arrOldCards);
        } else {
            App::LoadModuleClass("Membership", "PcwsWrapper");
            App::LoadModuleClass("Kronus", "CompPointsLogsModel");
            $comppointslogs = new CompPointsLogsModel();
            $comppoints = new PcwsWrapper();
            
            $serviceID = 18;
            $usermode = $comppointslogs->checkUserMode($serviceID);
            if ($usermode == 0) {
                //Insert to compointlogs  
                $test = $comppoints->addCompPoints($newcardnumber, 0, $serviceID, $tobeadded, 0);
            }

            $_PointsTransferAPI->ProcessCardCompPointsTransfer($arrCardPointsTransfer, $arrOldCards);
        }

        if(!App::HasError())
        {
            //Loyalty points
            if ($isLoyalty == 1) {

                $updatedresult = $_MemberCards->getMemberCardInfoByCard($newcardnumber);
                $updatedrow = $updatedresult[0];

                $loyaltyoldpoints = $oldrow["LifetimePoints"] - $oldrow["RedeemedPoints"];
                $membercardnewpoints = $updatedrow["LifetimePoints"] - $updatedrow["RedeemedPoints"];
            } else {
               $loyaltyoldpoints = $oldrow["LifetimePoints"] - $oldrow["RedeemedPoints"];
               $membercardnewpoints = $_GetCardInfoAPI->getCompPoints($newcardnumber);
            }

            $_JSONAPIResponse->_sendResponse(200, json_encode(array("CardPoints"=>array("LoyaltyCardPoints"=>$loyaltyoldpoints, "MembershipCardPoints"=>$membercardnewpoints, "StatusCode"=>(int)1, "StatusMsg"=>"Successful"))));
            $_Log->logAPI(AuditFunctions::TRANSFER_POINTS, $newrow["MID"] . ':' . $oldcardnumber .':'. $newcardnumber .':Success',$AID);
        }
        else
        {
            $_JSONAPIResponse->_sendResponse(200, json_encode(array("CardPoints"=>array("LoyaltyCardPoints"=>"", "MembershipCardPoints"=>"", "StatusCode"=>(int)0, "StatusMsg"=>"Failed"))));
            $_Log->logAPI(AuditFunctions::TRANSFER_POINTS, $newrow["MID"] . ':' . $oldcardnumber .':'. $newcardnumber .':Failed',$AID);
        }
    }
}
else
{
    $_JSONAPIResponse->_sendResponse(200, json_encode(array("CardPoints"=>array("LoyaltyCardPoints"=>"", "MembershipCardPoints"=>"", "StatusCode"=>(int)100, "StatusMsg"=>"Card not found"))));
}
?>
