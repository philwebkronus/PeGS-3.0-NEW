<?php
require_once("init.inc.php");

App::LoadModuleClass("Loyalty","Rewards");
$_Rewards = new Rewards();

if ((isset($_GET["PathID"]) && (htmlentities($_GET["PathID"])))
        && (isset($_GET["CardTypeID"]) && (htmlentities($_GET["CardTypeID"])))) {
    $CardTypeID = $_GET["CardTypeID"];
} else {
    $CardTypeID = '';
}

$PathID = $_GET["PathID"];
$arrRewards = $_Rewards->getRewardOffers($PathID,$CardTypeID);
$arr = $arrRewards[0];
$arrName = $arr['RewardItemName'];
$arrDescription = $arr['RewardDescription'];
$arrPoints = $arr['Points'];
$arrPath = $arr['ImagePath'];
$arrCode = $arr['RewardCode'];
$arrStartDate = $arr['StartDate'];
$arrEndDate = $arr['EndDate'];
$arrCardName = $arr['CardName'];

?>
<link rel="stylesheet" type="text/css" href="css/style.css">    
<div id="rewardinfo">
    <div id ="picture">
        <img src ="images/rewarditems/<?php echo $arrPath; ?>" width='200' height='150'/>
    </div>
    <div id="Info">
        <p><strong><u><center><?php echo $arrName . "/" . $arrCode; ?></center></u></strong></p>
    </div>
    <div id="Details">
        <p><strong><u>Details</u></strong></p>
        <p><strong>Required Points:</strong> <?php echo $arrPoints; ?></br>
            <strong>Validity Period:</strong> <?php echo date('F d, Y', strtotime($arrStartDate)) . " to " . date('F d, Y', strtotime($arrEndDate)); ?> </br>
            <strong>Reward Description:</strong> </br> <?php echo $arrDescription; ?></p> </br>
            <?php if (!empty($CardTypeID)) 
             {?>
                <p><i><u>Applicable For <?php echo $arrCardName; ?> Card users only.</u></i></p>            
             <?php
             }?>
    </div>
</div>