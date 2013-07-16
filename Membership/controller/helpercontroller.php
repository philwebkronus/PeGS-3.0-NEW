<?php

/*Description: Set up session for reward card item to be redeem.
 * Author: aqdepliyan
 * Date Created: 07/09/2013 02:37PM
 */
include '../sessionmanager.php';

if((isset($_POST['rewarditemid']) && isset($_POST['productname']) &&  isset($_POST['partnername']) && isset($_POST['rewardofferid']) && isset($_POST['points']) && isset($_POST['iscoupon']) && isset($_POST['playerpoints']) && $_POST['iscoupon'] != '') &&
        ($_POST['rewarditemid'] != '' || $_POST['productname'] != '' || $_POST['partnername'] != '' ||  $_POST['rewardofferid'] != '' || $_POST['points'] != '' || $_POST['iscoupon'] != '')){
    
    $_SESSION['RewardItemsInfo']['RewardItemID'] = $_POST['rewarditemid'];
    $_SESSION['RewardItemsInfo']['ProductName'] = $_POST['productname'];
    $_SESSION['RewardItemsInfo']['PartnerName'] = $_POST['partnername'];
    $_SESSION['RewardItemsInfo']['RewardOfferID'] = $_POST['rewardofferid'];
    $_SESSION['RewardItemsInfo']['Points'] = $_POST['points'];
    $_SESSION['RewardItemsInfo']['IsCoupon'] = $_POST['iscoupon'];
    $_SESSION['RewardItemsInfo']['PlayerPoints'] = $_POST['playerpoints'];
    $result = true;
} else {
    $result =  false;
}
echo $result; exit;
?>
