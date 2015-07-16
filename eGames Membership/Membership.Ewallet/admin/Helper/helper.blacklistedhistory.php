<?php
/**
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: November 8, 2013
 */
include ('../../init.inc.php');

App::LoadModuleClass('Membership', "BlackLists");
App::LoadModuleClass('Membership', "BlackListHistory");
App::LoadModuleClass('Kronus', "AccountDetails");

$blacklisthistory = new BlackListHistory();
$accounts = new AccountDetails();

$page = $_POST['page'];
$limit = $_POST['rows'];

$blackListedID = $_POST['blacklistedID'];
//get the total number of blacklisted players
$blacklisted = $blacklisthistory->getAllBlackListedHist($blackListedID);
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
        $response->rows[$i]['id'] = $row['BlackListHistoryID'];
        $username = $accounts->selectNameByAID($row['CreatedByAID']);
        $response->rows[$i]['cell'] = array(
                                        $row['DateCreated'],
                                        $row['CreatedByAID'] = $username[0]['Name'],
                                        $row['Remarks']
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
