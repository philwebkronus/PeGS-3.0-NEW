<?php

/*
 * Description: Fetching and encoding data into JSON array to be displayed in JQGRID for list of Transactions per cut-off.
 *@Author: gvjagolino
 * Date Created: 07-02-2013 05:00 PM
 */

//Attach and Initialize framework
require_once("../../init.inc.php");

    
//Load Modules to be use.
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Kronus", "TransactionSummary");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_TransactionSummary = new TransactionSummary();
$response = null;

if(isset($_POST['Sites']) && $_POST['Sites'] != ''){
    $site = $_POST['Sites'];
    $fromdate = $_POST['fromDateverified']." ".App::getParam("cutofftime");
    $todate = $_POST['toDateverified']." ".App::getParam("cutofftime");

    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $direction = $_POST['sord']; // get the direction
    
    
    $rcount = $_TransactionSummary->countTransSummary($site, $fromdate, $todate); 
    
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

        $result = $_TransactionSummary->getTransSummary($site, $fromdate, $todate);
       
        if(count($result[0]) > 0)
        {
            
             $i = 0;
             $responce->page = $page;
             $responce->total = $total_pages;
             $responce->records = $count;                    
             foreach($result as $vview)
             {                     
                $status= $_MemberCards->getStatusByCard($vview['LoyaltyCardNumber']);
                
                foreach ($status as $value) {
                    $status = $value['Status'];
                }
                switch($status)
                {
                    case 0: $vstatus = 'InActive';break;
                    case 1: $vstatus = 'Active';    break;
                    case 2: $vstatus = 'Deactivated';break;
                    case 5: $vstatus = 'Active Temporary';break;
                    case 7: $vstatus = 'New Migrated'; break;   
                    case 8: $vstatus = 'Temporary Migrated';  break;
                    case 9: $vstatus = 'Banned';  break;
                    default: $vstatus = 'Card Not Found'; break;
                } 
                $playernetwin = $vview['Deposit'] + $vview['Reload'] - $vview['Withdrawal'];
                $responce->rows[$i]['id']=$vview['TransactionsSummaryID'];
                $responce->rows[$i]['cell']=array($vview['LoyaltyCardNumber'],$vstatus,number_format($vview['Deposit'],2),
                    number_format($vview['Reload'],2),number_format($vview['Withdrawal'],2) ,number_format($playernetwin,2));
                $i++;
             }
        }
        else
        {
             $i = 0;
             $responce->page = $page;
             $responce->total = $total_pages;
             $responce->records = $count;
             $msg = "Transactions Per Cut-Off: No returned result";
             $responce->msg = $msg;
        }
}

echo json_encode($responce);
exit;

?>
