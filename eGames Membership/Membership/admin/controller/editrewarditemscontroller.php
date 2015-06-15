<?php
//Attach and Initialize framework
require_once("../../init.inc.php");

    
//Load Modules to be use.
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Loyalty", "RewardItems");
App::LoadModuleClass("Loyalty", "RewardItemDetails");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$rewarditems = new RewardItems();
//redirect to edit reward item details page
if(isset($_GET['page'])=='ViewService')
   {
        $rewarditemID = $_GET['rewarditemid'];
        $_SESSION['rewarditemid'] = $rewarditemID;
        $rewarditemsdetails = array();
        $rewarditemsdetails = $rewarditems->getAllRewardItemsperItemID($rewarditemID);
        
        if(count($rewarditemsdetails) > 0)
        {
            
          $_SESSION['rewarditemdetails']= $rewarditemsdetails;
            
          unset($rewarditemsdetails);
        
            header("Location: ../editrewarditems.php"); 
        }
        else
        {
            $msg = "No Details found for this service group";
            $_SESSION['mess'] = $msg;
            header("Location: ../viewrewarditems.php"); 
        }
   }
   //redirect to edit status
   elseif(isset($_GET['statuspage']) == 'UpdateStatus')
    {
            header("Location: ../editrewarditemstatus.php");
    }
   

?>
