<?php
/**
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 8, 2013
 */
include ('../../init.inc.php');
$modulename_1 = "Membership";
$modulename_2 = "Kronus";
App::LoadModuleClass($modulename_1, "AuditTrail");
App::LoadModuleClass($modulename_2, "Accounts");

$AuditTrail = new AuditTrail();
$Accounts   = new Accounts();

$page = $_POST['page'];
$limit = $_POST['rows'];

$transactionDate = $_POST['TransactionDate'];
//Select all the accounts where account type is equal to the account type of the current user
$accounttype = $_SESSION['userinfo']['AccountTypeID'];
$selectAccounts = $Accounts->selectAccountsByAccountType($accounttype);
//load usernames in an array
for ($i = 0; count($selectAccounts) > $i; $i++)
{
    $arrAID[] = $selectAccounts[$i]['AID'];
}
//get the total number of logs
$getTotalLogs = $AuditTrail->getTotalLogs($arrAID, $transactionDate);
$count = $getTotalLogs[0]['count'];
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
$loadLogs = $AuditTrail->loadAuditLogs($arrAID, $transactionDate);

$response->page = $page;
$response->total = $total_pages;
$response->records = $count;
//Get Details and encode into JSON
if(count($loadLogs) > 0)
{
    $i = 0; 
    foreach ($loadLogs as $val)
    {
        $getUsername = $Accounts->selectUsernameByAID($val['ID']);
        $response->rows[$i]['id'] = $val['AuditTrailID'];
        $response->rows[$i]['cell'] = array($getUsername[0]['UserName'],
                                            $val['TransactionDetails'],
                                            $val['TransactionDateTime'],
                                            $val['RemoteIP']
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
