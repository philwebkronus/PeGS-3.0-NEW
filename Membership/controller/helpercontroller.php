<?php

/**
 * @Description: Set up session for reward card item to be redeem.
 * @Author: aqdepliyan
 * @DateCreated: 07/09/2013 02:37PM
 */
include '../sessionmanager.php';

if((isset($_POST['rewarditemid']) && isset($_POST['productname']) &&  isset($_POST['partnername'])  && isset($_POST['points']) && isset($_POST['rewardid']) &&  isset($_POST['learnmoreimage']) &&  isset($_POST['ecouponimage']) && $_POST['rewardid'] != '') &&
        ($_POST['rewarditemid'] != '' || $_POST['productname'] != '' || $_POST['partnername'] != '' || $_POST['points'] != '' || $_POST['rewardid'] != '' || $_POST['learnmoreimage'] != '' || $_POST['ecouponimage'] != '' )){
    
    $_SESSION['RewardItemsInfo']['RewardItemID'] = $_POST['rewarditemid'];
    $_SESSION['RewardItemsInfo']['ProductName'] = $_POST['productname'];
    $_SESSION['RewardItemsInfo']['PartnerName'] = $_POST['partnername'];
    $_SESSION['RewardItemsInfo']['Points'] = $_POST['points'];
    $_SESSION['RewardItemsInfo']['RewardID'] = $_POST['rewardid'];
    $_SESSION['RewardItemsInfo']['LearnMoreImage'] = $_POST['learnmoreimage'];
    $_SESSION['RewardItemsInfo']['eCouponImage'] = $_POST['ecouponimage'];
    $result = true;
} else {
    $result =  false;
}
echo $result; exit;
?>
