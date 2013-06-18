<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-05
 * Company: Philweb
 * ***************** */
require_once("../init.inc.php");
include_once('sessionmanager.php');

App::LoadModuleClass('Loyalty', 'RewardItems');
App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'Cards');
App::LoadModuleClass('Loyalty', 'CardTransactions');
App::LoadModuleClass('Kronus', 'Sites');

App::LoadCore('Validation.class.php');

App::LoadControl("TextBox");
App::LoadControl("Button");

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Sites = new Sites();
$_Cards = new Cards();

$MID = "";
$currentPoints = 0;
$lifetimePoints = 0;
$bonusPoints = 0;
$redeemedPoints = 0;
$CardNumber = "";
$siteName = "";
$transDate = "";
$showcardinfo = true;
$defaultsearchvalue = "Enter Card Number or Username";


if (!isset($fproc))
{
    $fproc = new FormsProcessor();
}

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->Text = $defaultsearchvalue;
$txtSearch->AutoComplete = false;
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
            $result = $_MemberInfo->getMemberInfoByUsername($searchValue);

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
            }
        }
    }

    if ($btnClear->SubmittedValue == "Clear")
    {
        unset($_SESSION['CardInfo']);
        $txtSearch->Text = $defaultsearchvalue;
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
    $loyaltyinfo = $loyaltyinfo[0];
    $cardType = $loyaltyinfo['CardType'];
    $currentPoints = $loyaltyinfo['CurrentPoints'];
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
}
?>