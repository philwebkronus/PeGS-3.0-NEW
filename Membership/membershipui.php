<?php
require_once("init.inc.php");

App::LoadModuleClass("Loyalty","Rewards");
App::LoadControl("Button");


$_Rewards = new Rewards();
$fproc = new FormsProcessor();
//
//$viewmore = new Button ("viewmore","viewmore", "View More");
//$fproc ->AddControl($viewmore);

$arrRewards = $_Rewards->getRewardItems();
//$arrRewardItems = $arrRewards[0];

app::pr($arrRewards);
?>
