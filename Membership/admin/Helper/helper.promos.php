<?php
/**
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 11, 2013
 */
include ('../../init.inc.php');
$modulename = "Loyalty";
App::LoadModuleClass($modulename, "Promos");
App::LoadModuleClass($modulename, "Helper");

$Promos = new Promos();
$Helper = new Helper();

$page = $_GET['page'];
$limit = $_GET['rows'];

//Get promos and its details
$countPromos = $Promos->GetNumberOfPromos();
$count = $countPromos[0]['count'];
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
//$start = $limit * $page - $limit;
//Load the audit logs to json
$loadPromos = $Promos->loadPromos();

$response->page = $page;
$response->total = $total_pages;
$response->records = $count;

if(count($loadPromos) > 0)
{
    $i = 0; 
    foreach ($loadPromos as $val)
    {
        $response->rows[$i]['id'] = $val['PromoID'];
        $id = $val['PromoID'];
        $response->rows[$i]['cell'] = array($val['Name'],
                                            $val['Description'],
                                            $val['StartDate'],
                                            $val['EndDate'],
                                            Helper::determinePromoStatus($val['Status']),
                                            "<input type='button' value='Update Details' id='updatelink' PromoID=".$id." style='overflow:visible; margin:0; padding:0; border:0; color:royalblue; background:transparent; ".
                                            "font:inherit; line-height:normal; text-decoration:underline;  cursor:pointer; -moz-user-select:text; '/>"
                                            
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
     $msg = "View Promo: No returned result";
     $response->msg = $msg;
}

echo json_encode($response);
?>
