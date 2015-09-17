<?php
/**
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: November 8, 2013
 */
include ('../../init.inc.php');
$modulename_1 = "Membership";

App::LoadModuleClass($modulename_1, "BlackLists");
App::LoadModuleClass($modulename_1, "BlackListHistory");

$blacklist = new BlackLists();
$blacklisthistory = new BlackListHistory();

$page = $_POST['page'];
$limit = $_POST['rows'];

//get the total number of blacklisted players
$blacklisted = $blacklist->getAllBlackListedSP();
$count = count($blacklisted);
if ($count > 0)
{
    $total_pages = ceil($count/$limit);
}
else
{   
    $total_pages = 0;
}
if ($page > $total_pages)
{
    $page = $total_pages;
}

$start = $limit * $page - $limit;
if($count == 0)
    $start = 0;
//load the audit logs to json

$response->page = $page;
$response->total = $total_pages;
$response->records = $count;
//Get Details and encode into JSON
if(count($blacklisted) > 0)
{
    $i = 0; 
    foreach($blacklisted as $row)
    {
        $remarks = $blacklisthistory->getRemarksSP($row['BlackListedID']);
        $response->rows[$i]['id'] = $row['BlackListedID'];
        $response->rows[$i]['cell'] = array(
                                        "<input type='button' class='statuslink' id='blacklistedhist' value='".$row['LastName']."' 
                                                BlackListedID='".$row['BlackListedID']."' title='See History' 
                                                FirstName='".$row['FirstName']."' />",
                                        $row['FirstName'],
                                        $row['BirthDate'],
                                        $row['Action'] = "<input type='image' id='editblacklisted' src='./images/ui-icon-edit.png' title='Edit Details'  
                                                           LastName='".$row['LastName']."' FirstName='".$row['FirstName']."' 
                                                           BirthDate='".$row['BirthDate']."' Remarks='".$remarks."' 
                                                           BlackListedID='".$row['BlackListedID']."'>&nbsp;<input type='image' 
                                                           id='deleteblacklisted' src='./images/ui-icon-delete.png' title='Remove from list' 
                                                           BlackListedID='".$row['BlackListedID']."' LastName='".$row['LastName']."'
                                                           FirstName='".$row['FirstName']."'>"
        );
        $i++;
    }
}
else
{
     $i = 0;
     $response->page = $page;
     $response->total = $total_pages;
     $response->records = $count;
     $msg = "Audit Trail: No returned result";
     $response->msg = $msg;
}

echo json_encode($response);
?>
