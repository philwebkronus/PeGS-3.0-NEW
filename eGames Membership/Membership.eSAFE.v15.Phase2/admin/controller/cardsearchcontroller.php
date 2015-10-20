<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-05
 * Updated by: Ralph
 * Date Updated: 09-02-2015
 * Company: Philweb
 * ***************** */
require_once("../init.inc.php");
include_once('sessionmanager.php');

App::LoadModuleClass('Rewards', 'RewardItems');
App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'Cards');
App::LoadModuleClass('Loyalty', 'CardTransactions');
App::LoadModuleClass('Kronus', 'Sites');
App::LoadModuleClass('Membership', 'PcwsWrapper');
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "Members");

App::LoadCore('Validation.class.php');
App::LoadCore('ErrorLogger.php');

App::LoadControl("TextBox");
App::LoadControl("Button");

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Log = new AuditTrail();
$_Sites = new Sites();
$_Cards = new Cards();
$_MemberInfo = new MemberInfo();
$_Members = new Members();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

$MID = "";
$currentPoints = 0;
$lifetimePoints = 0;
$bonusPoints = 0;
$redeemedPoints = 0;
$CardNumber = "";
$siteName = "";
$transDate = "";
$showcardinfo = true;
$defaultsearchvalue = "Enter Card Number";
$msg = '';


if (!isset($fproc))
{
    $fproc = new FormsProcessor();
}

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->AutoComplete = false;
$txtSearch->Args = 'placeholder="Enter Card Number"';
$fproc->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;
$btnSearch->Enabled = false;
$fproc->AddControl($btnSearch);

$btnClear = new Button('btnClear', 'btnClear', 'Clear');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;
$fproc->AddControl($btnClear);

$fproc->ProcessForms();

if ($fproc->IsPostBack)
{
    if ($btnSearch->SubmittedValue == "Search" && $txtSearch->SubmittedValue != "")
    {
        unset($_SESSION["CardInfo"]);
        $validate = new Validation();
        $searchValue = $txtSearch->SubmittedValue;
        if ($validate->validateEmail($searchValue))
        {
            $result = $_MemberInfo->getMemberInfoByUsernameSP($searchValue);
            if (count($result) > 0)
            {
                $_SESSION['CardInfo']['Username'] = $searchValue;
                $MID = $result[0]['MID'];
                $cardInfo = $_MemberCards->getActiveMemberCardInfo($MID);
                $CardNumber = $cardInfo[0]['CardNumber'];
                $_SESSION['CardInfo']['CardNumber'] = $CardNumber;
                $_SESSION['CardInfo']['MID'] = $MID;
            }
            else
            {
                App::SetErrorMessage('Username not found');
                $error = "Username not found";
                $logger->logger($logdate, $logtype, $error);
            }
        }
        else
        {
            $membercards = $_MemberCards->getMemberCardInfoByCard($searchValue);
            if (count($membercards) > 0)
            {
                $MID = $membercards[0]['MID'];
                $result = $_MemberInfo->getMemberInfo($MID);
                $_SESSION['CardInfo']['CardNumber'] = $searchValue;
                $_SESSION['CardInfo']['MID'] = $MID;
                $CardNumber = $searchValue;
            }
            else
            {
                App::SetErrorMessage('Invalid card number');
                $error = "Invalid card number";
                $logger->logger($logdate, $logtype, $error);
            }
        }
    }

    if ($btnClear->SubmittedValue == "Clear")
    {
        unset($_SESSION['CardInfo']);
    }
}

if (isset($_SESSION['CardInfo']))
{

    $CardNumber = $_SESSION['CardInfo']["CardNumber"];
    $MID = $_SESSION['CardInfo']["MID"];
    $siteName = "";
    $transDate = "";
    $txtSearch->Text = $CardNumber;
    $arrCards = $_Cards->getCardInfo($CardNumber);
    $arrTransactions = $_CardTransactions->getLastTransaction($CardNumber);
    
    
    
    $cardinfo = $arrCards[0];
    unset($arrCards);
    $CardTypeID = $cardinfo["CardTypeID"];
    $_SESSION['CardInfo']["CardTypeID"] = $CardTypeID;
    $loyaltyinfo = $_MemberCards->getActiveMemberCardInfo($MID);
    if(isset($loyaltyinfo[0]) && $loyaltyinfo[0] != ""){
        $loyaltyinfo = $loyaltyinfo[0];
        $pointsystem = App::getParam('PointSystem');
        
        if($pointsystem == 1)
        {
            $currentPoints2 = $loyaltyinfo['CurrentPoints'];
        }
        else
        {
            // Add API call to PCWS for Get Current Points
            $pcws = new PcwsWrapper();
            $api = $pcws->getCompPoints($CardNumber,1);
            if($api['GetCompPoints']['ErrorCode'] == 0)
            {
                $currentPoints2 = $api['GetCompPoints']['CompBalance'];
                $_Log->logEvent(AuditFunctions::GET_COMP_POINTS, 'MID:' . $MID .'CardNumber :'.$CardNumber. ': Successful', array('ID' => $_SESSION['userinfo']['AID'], 'SessionID' => $_SESSION['userinfo']['SessionID']));

            }
            else
            {
                $currentPoints2 = 0;
                $errMsg = $api['GetCompPoints']['TransactionMessage'];
                $_Log->logEvent(AuditFunctions::GET_COMP_POINTS, 'MID:' . $MID .'CardNumber :'.$CardNumber.' Message: '.$errMsg.': Failed', array('ID' => $_SESSION['userinfo']['AID'], 'SessionID' => $_SESSION['userinfo']['SessionID']));
            }
        }
        
        $memberinfo = $_Members->getVIPLevel($MID);
        if($memberinfo['IsVIP'] == 0)
            $cardType = 'Regular';
        else
            $cardType = 'VIP';
        
        //$cardType = $loyaltyinfo['CardType'];
        //$currentPoints = $loyaltyinfo['CurrentPoints'];
        $currentPoints = $currentPoints2;
        $lifetimePoints = $loyaltyinfo['LifetimePoints'];
        $bonusPoints = $loyaltyinfo['BonusPoints'];
        $redeemedPoints = $loyaltyinfo['RedeemedPoints'];
        $loyaltyinfo['CardTypeID'] = $CardTypeID;
        
        if (count($arrTransactions) > 0)
        {
            $site = $_Sites->getSite($arrTransactions[0]['SiteID']);
            $siteName = $site[0]['SiteName'];
            $transDate = date('M d, Y ', strtotime($arrTransactions[0]['TransactionDate']));
        }

        $loyaltyinfo["LastTransactionDate"] = $transDate;
        $loyaltyinfo["LastSitePlayed"] = $siteName;
    } else {
        switch ($cardinfo["Status"]) {
            case 0:
                $msg = "Card is inactive";
                App::SetErrorMessage($msg);
                break;
            case 2:
                $msg = "Card is deactivated";
                App::SetErrorMessage($msg);
                break;
            case 8:
                $msg = "Card is migrated";
                App::SetErrorMessage($msg);
                break;
            case 9:
                $msg = "Card is banned";
                App::SetErrorMessage($msg);
                break;
        }
    }
    
}
?>
