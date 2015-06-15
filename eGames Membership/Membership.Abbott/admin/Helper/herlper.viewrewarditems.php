<?php

/*
 * Description: Fetching and encoding data into JSON array to be displayed in JQGRID for list of Reward Items.
 *@Author: gvjagolino
 * Date Created: 07-02-2013 05:00 PM
 */

//Attach and Initialize framework
require_once("../../init.inc.php");

    
//Load Modules to be use.
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Rewards", "RewardItems");
App::LoadModuleClass("Loyalty", "RewardItemDetails");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$rewarditems = new RewardItems();
$response = null;

    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $direction = $_POST['sord']; // get the direction
    
    
    $rcount = $rewarditems->countAllRewardItems(); 
    
    $count = $rcount[0]['count'];

    if($count > 0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
    {
        $page = $total_pages;
    }
    $start = $limit * $page - $limit;
    $limit = (int)$limit;

        $result = $rewarditems->getAllRewardItems();
       
        if(count($result[0]) > 0)
        {
            
             $i = 0;
             $responce->page = $page;
             $responce->total = $total_pages;
             $responce->records = $count;                    
             foreach($result as $vview)
             {                  
                $rewarditemid = $vview['RewardItemID'];

                switch($vview['Status'])
                {
                    case 1: $vstatus = 'Active';    break;
                    case 2: $vstatus = 'InActive';break;
                    default: $vstatus = 'Inactive'; break;
                }
                
                switch($vview['IsCoupon'])
                {
                    case 1: $iscoupon = 'Coupon';  break;
                    case 0: $iscoupon = 'Item';break;
                    default: $iscoupon = 'Coupon'; break;
                }
                $ritemid = $vview['RewardItemID'];
                        
                $responce->rows[$i]['id']=$vview['RewardItemID'];
                $responce->rows[$i]['cell']=array($vview['RewardItemName'],$vview['RewardItemDescription'],$vview['RewardItemPrice'],$vview['RewardItemCount'],
                    $vview['RewardItemCode'] ,$vview['AvailableItemCount'],$iscoupon,$vstatus, "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='controller/editrewarditemscontroller.php?rewarditemid=$rewarditemid'+'&page='+'ViewService';\"/>");
                
                $i++;
             }
        }
        else
        {
             $i = 0;
             $responce->page = $page;
             $responce->total = $total_pages;
             $responce->records = $count;
             $msg = "No returned result";
             $responce->msg = $msg;
        }


echo json_encode($responce);
exit;



?>
