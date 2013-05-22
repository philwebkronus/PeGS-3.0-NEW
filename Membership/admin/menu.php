<?php

/*
 * @author : owliber
 * @date : 2013-05-20
 */
require_once("../init.inc.php");
include('sessionmanager.php');

App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'CardTransactions');
App::LoadModuleClass('Kronus', 'Sites');

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Sites = new Sites();

if(isset($_SESSION['CardInfo']['MID']))
{
    $points = $_MemberCards->getMemberCardInfo( $_SESSION['CardInfo']['MID'] );
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
<div id="menu_container">
    <div id="cardinfo"><br />
        Card Number <span style="font-size:22px;"><?php echo $CardNumber; ?></span>
        <table>
            <tr>
                <td>Current Points</td>
                <td align="right"><?php echo number_format($currentPoints, 0); ?></td>
                <td>Lifetime Points</td>
                <td align="right"><?php echo number_format($lifetimePoints, 0); ?></td>
            </tr>
            <tr>
                <td>Bonus Points</td>
                <td align="right"><?php echo number_format($bonusPoints, 0); ?></td>
                <td>Redeemed Points</td>
                <td align="right"><?php echo number_format($redeemedPoints, 0); ?></td>
            </tr>
            <tr>
                <td>Last Played Site</td>
                <td align="right"><?php echo $siteName; ?></td>
                <td>Last Played Date</td>
                <td align="right"><?php echo $transDate; ?></td>
            </tr>
        </table>
    </div>
    <ul id="menu">
          <li><a href="index.php">Player Profile</a></li>
          <li><a href="redemption.php">Redemption</a></li>
          <li><a href="transactionhistory.php">Transaction History</a></li>
          <li><a href="logout.php">Logout</a></li>
    </ul>
</div>