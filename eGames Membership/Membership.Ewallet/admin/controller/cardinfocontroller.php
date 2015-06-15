<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-04
 * Company: Philweb
 * ***************** */

/*
 * @author : owliber
 * @date : 2013-05-23
 */

require_once("../init.inc.php");
include_once('sessionmanager.php');

App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'Cards');
App::LoadModuleClass('Loyalty', 'CardTransactions');
App::LoadModuleClass('Kronus', 'Sites');

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Sites = new Sites();
$_Cards = new Cards();

if(isset($_SESSION['CardInfo']['MID']))
{
    $arrCards = $_Cards->getCardInfo($_SESSION['CardInfo']['CardNumber']);
    //App::Pr($arrCards);
    $cardinfo = $arrCards[0];
    $_SESSION['CardInfo']["CardTypeID"] = $cardinfo["CardTypeID"];
    //$points = $_MemberCards->getMemberCardInfo($_SESSION['CardInfo']['MID'] );
    $points = $_MemberCards->getActiveMemberCardInfo($_SESSION['CardInfo']['MID'] );
    $row = $points[0];
    /**
    * Loyalty Points
    */
    $currentPoints = $row['CurrentPoints'];
    $lifetimePoints = $row['LifetimePoints'];
    $bonusPoints = $row['BonusPoints'];
    $redeemedPoints = $row['RedeemedPoints'];
    
    $trans = $_CardTransactions->getLastTransaction($_SESSION['CardInfo']['CardNumber']);
    
    if(count( $trans ) > 0)
    {
        $site = $_Sites->getSite($trans[0]['SiteID']);
        $siteName = $site[0]['SiteName'];
        $transDate = date('M d, Y ',strtotime($trans[0]['TransactionDate']));

    }
    else
    {
        $siteName = "";
        $transDate = "";
    }
    
    $CardNumber = $_SESSION['CardInfo']['CardNumber'];
}
else
{
    $currentPoints = 0;
    $lifetimePoints = 0;
    $bonusPoints = 0;
    $redeemedPoints = 0;
    $CardNumber = "";
    $siteName = "";
    $transDate = "";
}
?>
