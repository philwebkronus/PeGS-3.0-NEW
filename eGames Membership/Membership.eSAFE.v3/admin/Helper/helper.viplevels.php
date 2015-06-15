<?php

/**
 * @author Joene Floresca - September 01, 2014
 * 
 */

include ('../../init.inc.php');

App::LoadModuleClass("Membership", "RefVip");

$vip_levels = new RefVip();

$getviplevels = $vip_levels->getAllColumn();

//Get Details and encode into JSON
$status = '';
if(count($getviplevels) > 0)
{
    $i = 0; 
    foreach($getviplevels as $row)
    {
        $row['Status'] == 1 ? $status = 'Active' : $status = 'Inactive';
        $response->rows[$i]['cell'] = array(
         $row['VIPLevelID'],
         $row['Name'],
         $status,   
         $row['Action'] = "<input type='image' id='editvip' src='./images/ui-icon-edit.png' title='Edit Details'  
                                                           viplevelid='".$row['VIPLevelID']."'name='".$row['Name']."' 
                                                           status='".$row['Status']."'>"  
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
     $msg = "VIP Levels: No returned result";
     $response->msg = $msg;
}

echo json_encode($response);
?>
