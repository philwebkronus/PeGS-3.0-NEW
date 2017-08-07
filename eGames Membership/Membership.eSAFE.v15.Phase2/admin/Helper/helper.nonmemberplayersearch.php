<?php

/**
 *@Description: Player Search for member and non member players.
 *@Author: Renz Tiratira
 *@DateCreated: 07/04/2017 10:00
 */

require_once("../../init.inc.php");

App::LoadCore("Validation.class.php");

App::LoadModuleClass("Membership", "OthersMemberInfo");

$_OthersMemberInfo = new OthersMemberInfo();

$txtSearch = $_POST['txtSearch'];
$validate = new Validation();
$searchValue = $txtSearch;

$response = null;
if(isset($_POST["pager"]))
{
    $pager = $_POST["pager"];
    if($pager == "GetPlayerInfo" )
    {
        if(isset($_POST["txtSearch"]))
        {
            
            if($validate->validateAlphaSpaceDashAndDot($searchValue))
            {
                $result = $_OthersMemberInfo->GetNonMemberName($searchValue);
                if(count($result) > 0)
                {
                    $i=0;
                    $response->records = count($result);
                    foreach($result as $r)
                    {
                        $row = $r;
                        $response->rows[$i]["id"] = $row["OthersMemberInfoID"];
                        $response->rows[$i]["cell"] = array($row["FirstName"], $row["PlayerNumber"], $row['BirthDate'], $row["MobileNumber"]);
                        $i++;
                    }
                }
                else
                {
                    $i = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "No Record found";
                    $response->msg = $msg;
                }
            }
        }
        $count = $response->records;
        if($count >0)
        {
            $response = $count;
        }
        else
        {
            $recordinfo = array( array('RecordCount'  => $response->records, 'ErrorMsg'  => $response->msg) );
            $response = $recordinfo;
        }
        echo $response;
    }
    elseif($pager == "GetPlayerInfoGrid")
    {
        if(isset($_POST["txtSearch"]))
        {
            $page = $_POST['page'];
            $limit = $_POST['rows'];
    
            if($validate->validateAlphaSpaceDashAndDot($searchValue))
            {
                $result = $_OthersMemberInfo->GetNonMemberName($searchValue);
                
                $total_pages = ceil(count($result)/$limit);
                if($page > $total_pages)
                {
                    $page = $total_pages;
                }
                
                $start = $limit * $page - $limit;
                
                if(count($result) > 0)
                {
                    $i=0;
                    $response->page = intval($page);
                    $response->total = $total_pages;
                    $response->records = count($result);
                    foreach($result as $r)
                    {
                        $row = $r;
                        $response->rows[$i]["id"] = $row["OthersMemberInfoID"];
                        $response->rows[$i]["cell"] = array($row['FirstName'], $row['PlayerNumber'], $row['BirthDate'], $row['MobileNumber']);
                        $i++;
                    }   
                }
                else
                {
                    $i = 0;
                    $response->page = 0;
                    $response->total = 0;
                    $response->records = 0;
                    $msg = "No Record found";
                    $response->msg = $msg;
                }
            }   
            echo json_encode($response);
            exit;
        }
   }
}
?>
