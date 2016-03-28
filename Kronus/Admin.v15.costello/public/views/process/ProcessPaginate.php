<?php
/*
 * Created By: Arlene Salazar
 * Purpose: Controller for Pagination
 * Created On: June 14,2011
 */
include "../../sys/class/DbReport.class.php";
require '../../sys/core/init.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

//$conn = new DBReport('mysql:host=172.16.102.35;dbname=npos','nposconn','npos');
$conn = new DBReport($_DBConnectionString[0]);
$dbconn = $conn->open();
$new_sessionid = $_SESSION['sessionID'];
$aid = $_SESSION['accID'];

if($dbconn)
{
    /*************************************************************SESSION CHECKING*********************************************************************/
    $isexist=$conn->checksession($aid);
    if($isexist == 0)
    {
       session_destroy();
       $msg = "Not Connected";
      header("Location: ../views/login.php?mess=".$msg);
    }
    $isexistsession =$conn->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
       session_destroy();
       $msg = "Not Connected";
      header("Location: ../views/login.php?mess=".$msg);
    }
    /*************************************************************END OF SESSION CHECKING**************************************************************/
    
    $type =$_GET['type'];
    $start = $_GET['start'];
    $limit = $_GET['limit'];
    $order = $_GET['dir'];
    $datactr = $_GET['callback'];
    $sort = $_GET['sort'];
    $completePagingArray = array();
    $completeExcelValuesArray = array();
    $cutoff_time = "6:00:00";
    

    if($type == 'bcf')
    {
        $bcfpage = $conn->BCFPaging($start,$limit,'sb.' . $sort,$order);
        $conn->executeQuery($bcfpage);
        //echo $conn->rowCount();
        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchData())
            {
                if($row['TopUpType'] == 0)
                {
                    $topup = "Fixed";
                }
                else
                {
                    $topup = "Variable";
                }

                if($row['PickUpTag'] == 0)
                {
                    $pickup = "Provincial";
                }
                else
                {
                    $pickup = "Metro Manila";
                }
                
                $pagingarray = array(
                                    'LastTransactionDate' => $row['LastTransactionDate'],
                                    'TopUpType' => $topup,
                                    'PickUpTag' => $pickup,
                                    'Balance' => $row['Balance'],
                                    'MinBalance' => $row['MinBalance'],
                                    'MaxBalance' => $row['MaxBalance'],
                                    'SiteId' => $row['SiteID']
                                 );
                array_push($completePagingArray,$pagingarray);
                $pagingarray = NULL;
            }
            $_SESSION['bcfpagingarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['bcfpagingarray'] = "";
        }
        
        $bcf  = array(
                'totalCount' => "" . $_SESSION['bcfTotalCount'] ."",
                'topics' => $_SESSION['bcfpagingarray']
        );
        $page = json_encode($bcf);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
    }
    else if($type == 'st1')
    {
        $st = $conn->SiteTransPaging($start,$limit,'td.'.$sort,$order);
        $conn->executeQuery($st);
        //echo $conn->rowCount();
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if($row->TransactionType == "D")
                {
                    $sType = "Deposit";
                }
                else if($row->TransactionType == "R")
                {
                    $sType = "Reload";
                }
                else
                {
                    $sType = "Withdrawal";
                }
                $paging_array = array(
                                'TransactionDetailsID' => $row->TransactionDetailsID,
                                'TransactionReferenceID' => $row->TransactionReferenceID ,
                                'termname' => $row->termname ,
                                'TransactionType' => $sType ,
                                'ServiceID' => $row->ServiceID ,
                                'Status' => $row->Status ,
                                'Amount' => $row->Amount
                );
                array_push($completePagingArray,$paging_array);
            }
            $_SESSION['stpagingarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['stpagingarray'] = "";
        }
        $st  = array(
                'totalCount' => "" . $_SESSION['stTotal'] ."",
                'topics' => $_SESSION['stpagingarray']
        );

        $page = json_encode($st);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
    }
    else if($type == 'st2')
    {
        $date = $_GET['rptDate'];
        $enddate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($date)));
        //echo $enddate;
        $stpNoLimit = $conn->SiteTransPerDayPagingNoLimit();
        $conn->prepare($stpNoLimit);
        $strDate =  $date . " " . $cutoff_time;
        $endDate = $enddate . " " . $cutoff_time;
        $conn->bindParam(1, $strDate);
        $conn->bindParam(2, $endDate);
        $conn->execute();
        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
        {
            if($row->TransactionType == "D")
            {
                $pType= "Deposit";
            }
            else if($row->TransactionType == "R")
            {
                $pType= "Reload";
            }
            else
            {
                $pType= "Withdrawal";
            }

            if($row->Status == 0)
            {
                $stat = "Pending";
            }
            else if($row->Status == 1)
            {
                $stat = "Successfull";
            }
            else if($row->Status == 2)
            {
                $stat = "Failed";
            }
            else if($row->Status == 3)
            {
                $stat = "Timed Out";
            }
            else if($row->Status == 4)
            {
                $stat = "Transaction Approved";
            }
            else
            {
                $stat = "Transaction Denied";
            }
            $excel_array = array(
                            0 => $row->TransactionReferenceID,
                            1 => $row->TransactionSummaryID,
                            2 => $row->termname,
                            3 => $pType,
                            4 => $row->servname,
                            5 => $stat,
                            6 => $row->Amount
            );
            array_push($completeExcelValuesArray,$excel_array);
        }
        $totalRecords = $conn->rowCount();
        
        /*Query of total for Site Remittance with specific date*/
        $total = $conn->SumSiteTransPerDay();
        $conn->prepare($total);
        $conn->bindParam(1, $strDate);
        $conn->bindParam(2, $endDate);
        $conn->execute();
        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
        {
            $_SESSION['stptotal'] = $row->total;

            $excel_array = array(
                                0 => 'Total',
                                1 => '',
                                2 => '',
                                3 => '',
                                4 => '',
                                5 => '',
                                6 => $row->total
            );
            array_push($completeExcelValuesArray,$excel_array);

        }
        //echo $sort;
        //echo $endDate;
        $stp = $conn->SiteTransPerDayPaging($start,$limit,'td.' . $sort,$order);
        $conn->prepare($stp);
        $conn->bindParam(1, $strDate);
        $conn->bindParam(2, $endDate);
        $conn->execute();
        //echo $conn->rowCount();
        $_SESSION['stpTotal'] = $conn->rowCount();
        //echo $limit;
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if($row->TransactionType == "D")
                {
                    $pType= "Deposit";
                }
                else if($row->TransactionType == "R")
                {
                    $pType= "Reload";
                }
                else
                {
                    $pType= "Withdrawal";
                }

                if($row->Status == 0)
                {
                    $stat = "Pending";
                }
                else if($row->Status == 1)
                {
                    $stat = "Successfull";
                }
                else if($row->Status == 2)
                {
                    $stat = "Failed";
                }
                else if($row->Status == 3)
                {
                    $stat = "Timed Out";
                }
                else if($row->Status == 4)
                {
                    $stat = "Transaction Approved";
                }
                else
                {
                    $stat = "Transaction Denied";
                }
                
                $paging_array = array(
                                'TransactionDetailsID' => $row->TransactionDetailsID,
                                'TransactionReferenceID' => $row->TransactionReferenceID,
                                'TransactionSummaryID' => $row->TransactionSummaryID,
                                'termname' => $row->termname,
                                'pType' => $pType,
                                'servname' => $row->servname,
                                'stat' => $stat,
                                'Amount' => $row->Amount
                );
                //echo "a";
                array_push($completePagingArray,$paging_array);
            }
            $_SESSION['stppagingarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['stppagingarray'] = $completePagingArray;
        }
        //print_r($completePagingArray);
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $stp  = array(
                'totalCount' => "" . $totalRecords ."",
                //'totalCount' => "9",
                'topics' => $_SESSION['stppagingarray']
        );

        $page = json_encode($stp);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
    }
    else if($type == 'ajaxstp')
    {
        $_SESSION['stpDate'] = $_GET['rptDate'];
        echo $_SESSION['stpDate'];
        header('location:  ../process/ProcessPaginate.php?type=st2');
    }
    else if($type == 'sr')
    {
        $sr = $conn->SiteRemitPaging($start,$limit);
        $conn->executeQuery($sr);

        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if($row->Status == 0)
                {
                    $stat = "Valid";
                }
                else
                {
                    $stat = "Invalid";
                }
                $paging_array = array(
                                'SiteRemittanceID' => $row->SiteRemittanceID,
                                'RemittanceType' => $row->remittancename,
                                'BankName' => $row->bankname,
                                'Branch' => $row->Branch,
                                'BankTransactionID' => $row->BankTransactionID,
                                'BankTransactionDate' => $row->BankTransactionDate,
                                'ChequeNumber' => $row->ChequeNumber,
                                'Particulars' => $row->Particulars,
                                'Status' => $stat,
                                'username' => $row->username,
                                'siteName' => $row->siteName,
                                'Amount' => $row->Amount
                );
                array_push($completePagingArray,$paging_array);
            }
            $_SESSION['srpagingarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['srpagingarray'] = "";
        }
        $sr  = array(
                'totalCount' => "" . $_SESSION['srTotal'] ."",
                'topics' => $_SESSION['srpagingarray']
        );

        $page = json_encode($sr);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
    }
    else if($type == 'gh')
    {
        $startDate = $_GET['strdate'];
        $endDate = $_GET['enddate'];
        $siteId = $_GET['siteid'];
        $trmId = $_GET['termid'];
        $strdate = date ( 'Y-m-j' , strtotime ('-1 day' , strtotime($startDate)));
        $strDate = "$strdate 6:00:00";
        $endDate = "$endDate 6:00:00";
       
        if($siteId == 0)                                                        /*Checks if the sitename selected is ALL*/
        {
            $ghQuery = $conn->GetAllGrossHold();
            $conn->executeQuery($ghQuery);
            $totalRecord = $conn->rowCount();
        }
        else
        {
            $ghQuery = $conn->GetGrossHold();
            $conn->prepare($ghQuery);
            $conn->bindParam(1, $siteId);
            $conn->bindParam(2, $trmId);
            $conn->bindParam(3, $strDate);
            $conn->bindParam(4, $endDate);
            $conn->execute();
            $totalRecord = $conn->rowCount();
        }

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if($row->TransactionType == "D")
                {
                    $dType = "Deposit";
                }
                else if($row->TransactionType == "R")
                {
                    $dType = "Reload";
                }
                else
                {
                    $dType = "Withdrawal";
                }

                if($row->Status == 0)
                {
                    $stat = "Pending";
                }
                else if($row->Status == 1)
                {
                    $stat = "Successfull";
                }
                else if($row->Status == 2)
                {
                    $stat = "Failed";
                }
                else if($row->Status == 3)
                {
                    $stat = "Timed Out";
                }
                else if($row->Status == 4)
                {
                    $stat = "Transaction Approved";
                }
                else
                {
                    $stat = "Transaction Denied";
                }

                $excel_array = array(
                                0 => $row->TransactionReferenceID,
                                1 => $dType,
                                2 => $row->servname,
                                3 => $row->username,
                                4 => $row->sitename,
                                5 => $stat,
                                6 => $row->Amount
                );
                array_push($completeExcelValuesArray,$excel_array);
            }
        }
        else
        {

        }
        
        if($siteId == 0)                                                        /*Checks if the sitename selected is ALL*/
        {
            $ghSumQuery = $conn->SumAllGrossHold();
            $conn->executeQuery($ghSumQuery);
        }
        else
        {
            $ghSumQuery = $conn->SumGrossHold();
            $conn->prepare($ghSumQuery);
            $conn->bindParam(1, $siteId);
            $conn->bindParam(2, $trmId);
            $conn->bindParam(3, $strDate);
            $conn->bindParam(4, $endDate);
            $conn->execute();
        }

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if( $row->total != "")
                {
                    $_SESSION['ghtotal'] = $row->total;
                }
                else
                {
                    unset($_SESSION['ghtotal']);
                }

                $excel_array = array(
                                    0 => 'Total',
                                    1 => '',
                                    2 => '',
                                    3 => '',
                                    4 => '',
                                    5 => '',
                                    6 => $row->total
                );
                array_push($completeExcelValuesArray,$excel_array);
            }
        }
        else
        {
            //unset($_SESSION['ghtotal']);
        }
        if($siteId == 0)
        {
            $gh = $conn->AllGrossHoldPaging($start, $limit);
            $conn->executeQuery($gh);
        }
        else
        {
            $gh = $conn->GrossHoldPaging($start, $limit);
            $conn->prepare($gh);
            $conn->bindParam(1, $siteId);
            $conn->bindParam(2, $trmId);
            $conn->bindParam(3, $strDate);
            $conn->bindParam(4, $endDate);
            $conn->execute();
        }
        $totalCount = $conn->rowCount();
        //echo $conn->rowCount();
        if($conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if($row->TransactionType == "D")
                {
                    $dType = "Deposit";
                }
                else if($row->TransactionType == "R")
                {
                    $dType = "Reload";
                }
                else
                {
                    $dType = "Withdrawal";
                }
                if($row->Status == 0)
                {
                    $stat = "Pending";
                }
                else if($row->Status == 1)
                {
                    $stat = "Successfull";
                }
                else if($row->Status == 2)
                {
                    $stat = "Failed";
                }
                else if($row->Status == 3)
                {
                    $stat = "Timed Out";
                }
                else if($row->Status == 4)
                {
                    $stat = "Transaction Approved";
                }
                else
                {
                    $stat = "Transaction Denied";
                }
                $paging_array = array(
                                'TransactionDetailsID' => $row->TransactionDetailsID,
                                'TransactionReferenceID' => $row->TransactionReferenceID,
                                'dType' => $dType,
                                'servicename' => $row->servicename,
                                'username'=> $row->username,
                                'sitename' => $row->sitename,
                                'stat' => $stat,
                                'Amount' => $row->Amount
                );
                array_push($completePagingArray,$paging_array);
                
            }
            if($siteId == 0)                                                        /*Checks if the sitename selected is ALL*/
            {
                $ghSumQuery = $conn->SumAllGrossHold();
                $conn->executeQuery($ghSumQuery);
            }
            else
            {
                $ghSumQuery = $conn->SumGrossHold();
                $conn->prepare($ghSumQuery);
                $conn->bindParam(1, $siteId);
                $conn->bindParam(2, $trmId);
                $conn->bindParam(3, $strDate);
                $conn->bindParam(4, $endDate);
                $conn->execute();
            }

            if( $conn->rowCount() > 0 )
            {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                    
                }
            }
            
            $_SESSION['ghpagingarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['ghpagingarray'] = '';
        }
        

        
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $gh  = array(
                'totalCount' => "" . $totalRecord ."",
                'topics' => $_SESSION['ghpagingarray']
        );

        $page = json_encode($gh);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
    }
    else if($type == 'sldtls')
    {
        
        $completeExcelValuesArray1 = array();
        $siteID = $_GET['slsiteID'];
        //echo $siteID;
        if($siteID == 0)
        {
            $sites = $conn->GetAllSiteDetails();
            $conn->executeQuery($sites);
        }
        else
        {
            $sites = $conn->GetSiteDetails();
            $conn->prepare($sites);
            $conn->bindParam(1, $siteID);
            $conn->execute();
        }
        
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                 #Status : 0 - Pending; 1 - Active; 2 - Suspended; 3 - Deactivated;
                if($row->Status == 0)
                {
                        $sType = "Pending";
                }
                else if($row->Status == 1)
                {
                        $sType = "Active";
                }
                else if($row->Status == 2)
                {
                        $sType = "Suspended";
                }
                else
                {
                        $sType = "Deactivated";
                }
                $excel_array = array(
                                0 => $row->SiteName,
                                1 => $sType,
                                2 => $row->SiteDescription,
                                3 => $row->SiteAlias,
                                4 => $row->SiteAddress,
				5 => $row->IslandName,
                                6 => $row->ProvinceName,
                                7 => $row->CityName,
                                8 => $row->BarangayName,
				9 => $row->RegionName,
                                
                );
                array_push($completeExcelValuesArray1,$excel_array);
            }
            $_SESSION['slexcel1'] = $completeExcelValuesArray1;
        }
        else
        {
            $_SESSION['slexcel1'] = $completeExcelValuesArray1;
        }
        //$_SESSION['report_values'] = $completeExcelValuesArray1;
        
        $sitedtlstotal = $conn->rowCount();
        if($siteID == 0)
        {
            $sitedtls = $conn->GetAllSiteDetailsPaging($start,$limit);
            $conn->executeQuery($sitedtls);
        }
        else
        {
            $sitedtls = $conn->GetSiteDetailsPaging($start,$limit);
            $conn->prepare($sitedtls);
            $conn->bindParam(1, $siteID);
            $conn->execute();
        }
        //echo $conn->rowCount();
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                #Status : 0 - Pending; 1 - Active; 2 - Suspended; 3 - Deactivated;
                if($row->Status == 0)
                {
                        $sType = "Pending";
                }
                else if($row->Status == 1)
                {
                        $sType = "Active";
                }
                else if($row->Status == 2)
                {
                        $sType = "Suspended";
                }
                else
                {
                        $sType = "Deactivated";
                }
                $paging_array = array(
                                'SiteID' => $row->SiteID,
                                'SiteName' => $row->SiteName,
                                'SiteCode' => $row->SiteCode,
                                'sType' => $sType,
                                'SiteDescription' => $row->SiteDescription,
                                'SiteAlias' => $row->SiteAlias,
                                'IslandName' => $row->IslandName,
                                'RegionName' => $row->RegionName,
                                'ProvinceName' => $row->ProvinceName,
                                'CityName' => $row->CityName,
                                'BarangayName' => $row->BarangayName,
                                'SiteAddress' => $row->SiteAddress,
                );
                array_push($completePagingArray,$paging_array);
                
            }
            $_SESSION['sldtlspagingarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['sldtlspagingarray'] = "";
        }
        $stdtls  = array(
                'totalCount' => "" . $sitedtlstotal ."",
                'topics' => $_SESSION['sldtlspagingarray']
        );

        $page = json_encode($stdtls);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
        $completePagingArray = NULL;
    }
    else if($type == 'slacct')
    {
        $completeExcelValuesArray3 = array();
        $excel_array = array(
                        0 => 'Account Type',
			1 => 'Username',
                        2 => 'Address',
                        3 => 'Email',
                        4 => 'Landline',
                        5 => 'Mobile Number',
                        6 => '',
                        7 => '',
                        8 => '',
                        9 => '',
                        10 => '',
                        11 => ''
        );
        array_push($completeExcelValuesArray3,$excel_array);
        $siteID = $_GET['slsiteID'];
        if($siteID == 0)
        {
            $accounts = $conn->GetAllSiteAccounts();
            $conn->executeQuery($accounts);
        }
        else
        {
            $accounts = $conn->GetSiteAccounts();
            $conn->prepare($accounts);
            $conn->bindParam(1, $siteID);
            $conn->execute();
        }
        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
        {
            $excel_array = array(
                            0 => $row->name,
			    1 => $row->Username,
                            2 => $row->Address,
                            3 => $row->Email,
                            4 => $row->Landline,
                            5 => $row->MobileNumber,
                            6 => '',
                            7 => '',
                            8 => '',
                            9 => '',
                            10 => '',
                            11 => ''
            );
            array_push($completeExcelValuesArray3,$excel_array);
        }
        $slaccttotal = $conn->rowCount();

        if($siteID == 0)
        {
            $slaccts = $conn->GetAllSiteAccountsPaging($start, $limit);
            $conn->executeQuery($slaccts);
        }
        else
        {
            $slaccts = $conn->GetSiteAccountsPaging($start, $limit);
            $conn->prepare($slaccts);
            $conn->bindParam(1, $siteID);
            $conn->execute();
        }
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $paging_array = array(
                                'SiteID' => $row->SiteID,
                                'name' => $row->name,
                                'Address' => $row->Address,
                                'Email' => $row->Email,
                                'Landline' => $row->Landline,
                                'MobileNumber' => $row->MobileNumber,
				'Username' => $row->Username
                );
                array_push($completePagingArray,$paging_array);
            }
            $_SESSION['slacctarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['slacctarray'] = "";
        }
        $staccts  = array(
                'totalCount' => "" . $slaccttotal ."",
                'topics' => $_SESSION['slacctarray']
        );
        //print_r($_SESSION['slexcel1']);
        //echo 'a';
        $page = json_encode($staccts);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
        $_SESSION['slexcel3'] = $completeExcelValuesArray3;
        //print_r($valuesarray);
        //print_r($_SESSION['slexcel2']);
        
    }
    else if($type == 'sltrm')
    {

        $completeExcelValuesArray2 = array();
        $excel_array = array(
                        0 => 'Terminal Name',
                        1 => 'Terminal Code',
                        2 => 'Status',
                        3 => '',
                        4 => '',
                        5 => '',
                        6 => '',
                        7 => '',
                        8 => '',
                        9 => '',
                        10 => ''
        );
        array_push($completeExcelValuesArray2,$excel_array);

        $siteID = $_GET['slsiteID'];
        if($siteID == 0)
        {
            $terminals = $conn->GetAllSiteTerminals();
            $conn->executeQuery($terminals);
        }
        else
        {
            $terminals = $conn->GetSiteTerminals();
            $conn->prepare($terminals);
            $conn->bindParam(1, $siteID);
            $conn->execute();
        }
        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
        {
            //Status : 0 - Pending; 1 - Active; 2 - Disabled; 3 - Terminated'
            if($row->Status == 0)
            {
                    $tType = "Pending";
            }
            else if($row->Status == 1)
            {
                    $tType = "Active";
            }
            else if($row->Status == 2)
            {
                    $tType = "Disabled";
            }
            else
            {
                    $tType = "Terminated";
            }
            $excel_array = array(
                            0 => $row->TerminalName,
                            1 => $row->TerminalCode,
                            2 => $tType,
                            3 => '',
                            4 => '',
                            5 => '',
                            6 => '',
                            7 => '',
                            8 => '',
                            9 => '',
                            10 => ''
            );
            array_push($completeExcelValuesArray2,$excel_array);
        }
        $sltrmtotal = $conn->rowCount();
        if($siteID == 0)
        {
            $siteterminals = $conn->GetAllSiteTerminalsPaging($start, $limit);
            $conn->executeQuery($siteterminals);
        }
        else
        {
            $siteterminals = $conn->GetSiteTerminalsPaging($start, $limit);
            $conn->prepare($siteterminals);
            $conn->bindParam(1, $siteID);
            $conn->execute();
        }
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                //Status : 0 - Pending; 1 - Active; 2 - Disabled; 3 - Terminated'
                if($row->Status == 0)
                {
                        $tType = "Pending";
                }
                else if($row->Status == 1)
                {
                        $tType = "Active";
                }
                else if($row->Status == 2)
                {
                        $tType = "Disabled";
                }
                else
                {
                        $tType = "Terminated";
                }
                $paging_array = array(
                                'TerminalID' => $row->TerminalID,
                                'TerminalName' => $row->TerminalName,
                                'TerminalCode' => $row->TerminalCode,
                                'tType' => $tType
                );
                array_push($completePagingArray,$paging_array);

            }
            $_SESSION['sltrmsarray'] = $completePagingArray;
        }
        else
        {
            $_SESSION['sltrmsarray'] = $completePagingArray;
        }
        $sttrms  = array(
                'totalCount' => "" . $sltrmtotal ."",
                'topics' => $_SESSION['sltrmsarray']
        );

        $page = json_encode($sttrms);
        $_SESSION['completebcfarray'] = $datactr.'(' . $page . ');';
        $conn->close();
        echo $datactr.'(' . $page . ');';
        //$_SESSION['report_values'] = $completeExcelValuesArray2;
        $_SESSION['slexcel2'] = $completeExcelValuesArray2;
        $valuesarray = array();
        $dtl = $_SESSION['slexcel1'];
        $trms = $_SESSION['slexcel2'];
        $accts = $_SESSION['slexcel3'];
        //print_r($dtl);
        //echo count($dtl);
        //print_r($dtl);
        if(count($dtl) > 0)
        {
            for($dtls=0;$dtls<count($dtl);$dtls++)
            {
                $details = array(
                            0 => $dtl[$dtls][0],
                            1 => $dtl[$dtls][1],
                            2 => $dtl[$dtls][2],
                            3 => $dtl[$dtls][3],
                            4 => $dtl[$dtls][4],
                            5 => $dtl[$dtls][5],
                            6 => $dtl[$dtls][6],
                            7 => $dtl[$dtls][7],
                            8 => $dtl[$dtls][8],
                            9 => $dtl[$dtls][9]
                );
                array_push($valuesarray,$details);
            }
        }

        if(count($trms) > 0)
        {
            for($i=0;$i<count($trms);$i++)
            {
                $trm = array(
                            0 => $trms[$i][0],
                            1 => $trms[$i][1],
                            2 => $trms[$i][2],
                            3 => $trms[$i][3],
                            4 => $trms[$i][4],
                            5 => $trms[$i][5],
                            6 => $trms[$i][6],
                            7 => $trms[$i][7],
                            8 => $trms[$i][8],
                            9 => $trms[$i][9],
                            10 => $trms[$i][10]
                );
                array_push($valuesarray,$trm);
            }
        }

        if(count($trms) > 0)
        {
            for($i=0;$i<count($accts);$i++)
            {
                $acct = array(
                            0 => $accts[$i][0],
                            1 => $accts[$i][1],
                            2 => $accts[$i][2],
                            3 => $accts[$i][3],
                            4 => $accts[$i][4],
                            5 => $accts[$i][5],
                            6 => $accts[$i][6],
                            7 => $accts[$i][7],
                            8 => $accts[$i][8],
                            9 => $accts[$i][9],
                            10 => $accts[$i][10]
                );
                array_push($valuesarray,$acct);
            }
        }

        $_SESSION['report_values'] = $valuesarray;
        //print_r($valuesarray);
    }
    else
    {
        $date = $_GET['date'];
        $enddate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($date)));
        
        $transSummCompleteArray = array();
        $cshrtotalcount = $conn->GetTransSummary();
        $conn->prepare($cshrtotalcount);
        $conn->bindParam(1, $date . " " . $cutoff_time);
        $conn->bindParam(2, $enddate . " " . $cutoff_time);
        $conn->execute();
        $total_row_count = $conn->rowCount();
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                if($row->DateEnded == 0)
                {
                    $dateEnded = "Playing.....";
                }
                else
                {
                    $dateEnded = $row->DateEnded;
                }
                $excel_array = array(
                                0 => $row->TerminalName,
                                1 => $row->DateStarted,
                                2 => $dateEnded,
                                3 => $row->Deposit,
                                4 => $row->Reload,
                                5 => $row->Withdrawal,
                                6 => (($row->Deposit + $row->Reload) - $row->Withdrawal)
                );
                array_push($completeExcelValuesArray,$excel_array);
            }
        }
        
        $field = 'ts.' . $sort;
        if($sort == "")
            $sort = "TerminalName";
        else
            $sort = $sort;

        if($order == "")
            $order = 'ASC';
        else
            $order = $order;
        $csh = $conn->GetTransSummaryPaging($start,$limit,'tr.' . $sort,$order);
        $conn->prepare($csh);
        $conn->bindParam(1, $date . " " . $cutoff_time);
        $conn->bindParam(2, $enddate . " " . $cutoff_time);
        $conn->execute();
        //echo $conn->rowCount();
        $ghTotalComputation = 0;
        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $gh = (($row->Deposit + $row->Reload) - $row->Withdrawal);
                if($row->DateEnded == 0)
                {
                    $dateEnded = "Playing.....";
                }
                else
                {
                    $dateEnded = $row->DateEnded;
                }
                $transsumm = array(
                                'TransactionsSummaryID' => $row->TransactionsSummaryID,
                                'SiteID' => $row->SiteID,
                                'TerminalID' => $row->TerminalID,
                                'Deposit' => $row->Deposit,
                                'Reload' => $row->Reload,
                                'Withdrawal' => $row->Withdrawal,
                                'DateStarted' => $row->DateStarted,
                                //'DateStarted' => strtotime($row->DateStarted),
                                'DateEnded' => $dateEnded,
                                'TerminalName' => $row->TerminalName,
                                'GrossHold' => $gh
                );
                array_push($transSummCompleteArray,$transsumm);
                $ghTotalComputation = $ghTotalComputation + $gh;
            }
        }
        else
        {
            $_SESSION['cshResults'] = '';
        }
        

        $transSummTotalArray = array();
        $totals = $conn->SumDepositReloadWithdraw();
        $conn->prepare($totals);
        $conn->bindParam(1, $date . " " . $cutoff_time);
        $conn->bindParam(2, $enddate . " " . $cutoff_time);
        $conn->execute();

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $excel_array = array(
                                0 => 'Total',
                                1 => '',
                                2 => '',
                                3 => $row->totalDeposit,
                                4 => $row->totalReload,
                                5 => $row->totalWithdrawal,
                                6 => (($row->totalDeposit + $row->totalReload) - $row->totalWithdrawal)
                );
                array_push($completeExcelValuesArray,$excel_array);
            }
        }

        $staccts  = array(
                'totalCount' => "" . $total_row_count ."",
                'topics' => $transSummCompleteArray
        );

        $_SESSION['report_values'] = $completeExcelValuesArray;
        $page = json_encode($staccts);
        $conn->close();
        echo $datactr.'(' . $page . ');';
        //$_SESSION['report_values'] = $completeExcelValuesArray;
    }
}
else
{
    echo 'Error';
}
?>
