<?php
/**
 * Banks Controller
 * @author Mark Kenneth Esguerra
 * @date Febraury 18, 2015
 */
require __DIR__.'/../sys/core/init.php';
include __DIR__."/../sys/class/TopUp.class.php";

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
else 
{
    $new_sessionid = '';
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$topup = new TopUp($_DBConnectionString[0]);
$connected = $topup->open();
$nopage = 0;
if ($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $topup->getDate();    
/*********************SESSION CHECKING ************************/
    $isexist=$topup->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $topup->close();
      if($topup->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
    }
    $isexistsession =$topup->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $topup->close();
      header("Location: login.php?mess=".$msg);
    }
/**********************END SESSION CHECKING *******************/
    if (isset($_POST['sitepage']))
    {
        $vpage = $_POST['sitepage'];
        switch ($vpage)
        {
            case "CheckInputValidity": 
                $process = $_POST['process'];
                switch ($process)
                {
                    case "AddBankInputs": 
                        $bankcode = trim($_POST['bankcode']);
                        $bankname = trim($_POST['bankname']);
                        if ($bankcode != "" && $bankname != "")
                        {
                            $isExist = $topup->checkIfBankCodeExist($bankcode);
                            if ($isExist['Count'] == 0)
                            {
                                $result = array('ErrorCode' => 0, 
                                                'Message' => "Are you sure you want to add $bankname?");
                            }
                            else
                            {
                                $result = array('ErrorCode' => 1, 
                                                'Message' => 'Bank code already in used.');
                            }
                        }
                        else
                        {
                            $result = array('ErrorCode' => 1, 
                                            'Message' => 'Please fill out all fields.');
                        }
                        echo json_encode($result);
                        break;
                    case "UpdateBankInputs":
                        $bankID         = $_POST['txtbankid'];
                        $bankcode       = trim($_POST['txtbankcode']);
                        $bankname       = trim($_POST['txtbankname']);
                        if ($bankcode != "" && $bankname != "")
                        {
                            $isExist = $topup->checkIfBankCodeExist($bankcode, $bankID);
                            if ($isExist['Count'] == 0)
                            {
                                $result = array('ErrorCode' => 0, 
                                                'Message' => "Are you sure you want to change the details of ");
                            }
                            else
                            {
                                $result = array('ErrorCode' => 1, 
                                                'Message' => 'Bank code already in used.');
                            }
                        }
                        else
                        {
                            $result = array('ErrorCode' => 1, 
                                            'Message' => 'Please fill out all fields.');
                        }
                        echo json_encode($result);
                        break;
                }
                break;
            case "AddBank":
                $bankcode       = trim($_POST['bankcode']);
                $bankname       = trim($_POST['bankname']);
                $isaccredited   = trim($_POST['isaccredited']);
                //check if fields are blanks
                if ($bankcode != "" && $bankname != "" && $isaccredited != "")
                {
                    //check if bankcode already exist
                    $isExist = $topup->checkIfBankCodeExist($bankcode);
                    if ($isExist['Count'] == 0)
                    {
                        $status = 1;
                        $response = $topup->insertBank($bankcode, $bankname, $isaccredited, $status);
                        $isaccredited = ($isaccredited == 1) ? "YES" : "NO";
                        if ($response['ErrorCode'] == 0)
                            $vtransdetails = $response['Message'].", bankcode: ".$bankcode.", bankname: ". $bankname. ", isaccredited: ".$isaccredited;
                            $vauditfuncID = 94;
                            $topup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else
                    {
                        $response = array('ErrorCode' => 1, 
                                          'Message' => 'Bank code already in used.');
                    }
                }
                else
                {
                    $response = array('ErrorCode' => 1, 
                                      'Message' => 'Please fill out all fields.');
                }
                $topup->close();
                echo json_encode($response);
                break;
            case "ViewBanks":
                $page       = $_POST['page']; // get the requested page
                $limit      = $_POST['rows']; // get how many rows we want to have into the grid
                $direction  = $_POST['sord'];
                
                if($_POST['sidx'] != "")
                {
                  $sort = $_POST['sidx'];
                }
                else
                {
                  $sort = "SiteID";
                }
       
                $count = $topup->getAllBanks(true);
                $count = $count['Count'];
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
                $results = $topup->getAllBanks();
                
                if (count($results) > 0)
                {
                    $i = 0;
                    $responce->page = $page;
                    $responce->total = $total_pages;
                    $responce->records = $count;
                    foreach($results as $row) {
                        
                        $responce->rows[$i]['id']=$row['BankID'];
                        //topup module: if account will be suspended         {
                        $responce->rows[$i]['cell']=array($row['BankCode'], 
                                                          $row['BankName'], 
                                                          ($row['IsAccredited'] == 1) ? "YES" : "NO", 
                                                          ($row['Status'] == 1) ? "Active" : "Inactive", 
                                                          "<input type=\"button\" id=\"btnupdate\" name=\"btnupdate\" value=\"Update\" BankID=".$row['BankID'].">");
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
                unset($results);
                $topup->close();
                exit;
                break;
            case "SetBankUpdate":
                $hdnBankID = $_POST['bankid'];
                //get bank detail
                $details = $topup->getBankDetails($hdnBankID);
                $checked = ($details['IsAccredited'] == 1) ? "checked" : "";
                $_SESSION['bank']['bank_id'] = $hdnBankID;
                $_SESSION['bank']['is_accredited'] = $checked;
                $_SESSION['bank']['bank_code'] = $details['BankCode'];
                $_SESSION['bank']['bank_name'] = $details['BankName'];
                $topup->close();
                header('location: ../updatebank.php');
                break;
            case "UpdateBank":
                $bankID         = $_POST['txtbankid'];
                $bankcode       = trim($_POST['txtbankcode']);
                $bankname       = trim($_POST['txtbankname']);
                $isaccredited   = $_POST['isaccredited'];
                //check if blank
                if ($bankcode != "" && $bankname != "")
                {
                    //check if bankcode already exist
                    $isExist = $topup->checkIfBankCodeExist($bankcode, $bankID);
                    if ($isExist['Count'] == 0)
                    {
                        $result = $topup->updateBankDetails($bankID, $bankcode, $bankname, $isaccredited);
                        if ($result['ErrorCode'] == 0)
                            $vtransdetails = $result['Message']." BankID: ".$bankID."";
                            $vauditfuncID = 95;
                            $topup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else
                    {
                        $result = array('ErrorCode' => 1, 
                                        'Message' => 'Bank code already in used.');
                    }
                }
                else
                {
                    $result = array('ErrorCode' => 1, 
                                    'Message' => 'Please fill out all fields.');
                }
                $topup->close();
                echo json_encode($result);
                break;
            case "GetBankStatus": 
                $bankID = trim($_POST['bankID']);
                if ($bankID > 0)
                {
                    //get status
                    $status = $topup->getBankStatus($bankID);
                    switch ($status['Status']) 
                    {
                        case 1 : $stringstat = "Active";
                            $option = array('O_Status' => 0, 'O_StringStat' => 'Inactive');
                            break;
                        case 0 : $stringstat = "Inactive";
                            $option = array('O_Status' => 1, 'O_StringStat' => 'Active');
                            break;
                        default : $stringstat = "undefined";
                            $option = array();
                            break;
                    }
                    $result = array('Success' => 1, 'Status' => $status, 'StringStat' => $stringstat, 'Option' => $option);
                }
                else
                {
                    $result = array('Success' => 0);
                }
                $topup->close();
                echo json_encode($result);
                break;
            case "EditBankStatus":
                $bankID = $_POST['bankID'];
                $status = $_POST['status'];
                
                if ($bankID != "")
                {
                    if ($status != "")
                    {
                        $response = $topup->updateBankStatus($bankID, $status);
                        $status = ($status == 1) ? "Active" : "Inactive";
                        if ($response['ErrorCode'] == 0)
                            $vtransdetails = $response['Message']." bankID: ".$bankID.", status: ".$status;
                            $vauditfuncID = 96;
                            $topup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else
                    {
                        $response = array('ErrorCode' => 1, 
                                          'Message' => 'Please select status.');
                    }
                }
                else
                {
                    $response = array('ErrorCode' => 1, 
                                      'Message' => 'Please select bank.');
                }
                $topup->close();
                echo json_encode($response);
                break;
            default: 
                $msg = "Not Connected";    
                header("Location: login.php?mess=".$msg);
                break;
        }
    }
    if (isset($_POST['loadbanknames']))
    {
        
        $option = "<option value=''>-Please Select-</option>";
        $banknames = $topup->getbanknames();
        foreach ($banknames as $bank)
        {
            $option .= "<option value=".$bank['BankID'].">".$bank['BankName']."</option>";
        }
        echo $option;
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
