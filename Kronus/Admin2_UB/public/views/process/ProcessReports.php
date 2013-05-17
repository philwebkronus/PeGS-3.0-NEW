<?php
/*
 * Created By: Arlene Salazar
 * Purpose: Controller for Reports
 * Created On: June 2,2011
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
    $cutoff_time = "6:00:00";
    $type = $_GET['type'];
    
    if($type == 'opt')
    {
        header("Location: ../views/opt.php");
    } else if($type == 'bc') {
        header("Location: ../views/BettingCredit.php");
    }
    else if($type == 'sup')
    {
        header("Location: ../views/sup.php");
    }
    else if($type == 'ops')
    {
        header("Location: ../views/operations.php");
    }
    else if($type == 'bcf')
    {
        //$_SESSION['accID'] = 2;                                                 /*Disable when integrated*/
        /*Query result for all BCF*/
        $completequerysample = array();
        $completeExcelValuesArray = array();
        $completePagingArray = array();
        $completetotalarray = array();
        $total_balance = 0;
        $total_minbalance = 0;
        $total_maxbalance = 0;
        $total_page_balance = 0;
        $total_page_minbalance = 0;
        $total_page_maxbalance = 0;
        $limit = 10;
        if(!isset($_GET['page']))
            $start = 0;
        else
            $start = (($_GET['page'] - 1) * $limit);

        $sites_array = array();
        $allSites = $conn->GetAllSiteIds();
        $conn->prepare($allSites);
        $conn->bindParam(1, $aid);
        $conn->execute();
        $row_count = $conn->rowCount();

        $sites = $conn->GetSiteIds($start,$limit);
        $conn->prepare($sites);
        $conn->bindParam(1, $aid);
        $conn->execute();
        //echo $conn->rowCount();
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                array_push($sites_array,$row->SiteID);
            }
        }
        if(count($sites_array) > 0)
        {
            for($ctr = 0 ; $ctr < count($sites_array) ; $ctr++)
            {
                $bcfReport = $conn->BCFPaging();
                $conn->prepare($bcfReport);
                $conn->bindParam(1, $sites_array[$ctr]);
                $conn->execute();

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

                        $total_page_balance = ($total_page_balance + $row['Balance']);
                        $total_page_minbalance = ($total_page_minbalance + $row['MinBalance']);
                        $total_page_maxbalance = ($total_page_maxbalance + $row['MaxBalance']);

                        $resultarray = array(
                                            'LastTransactionDate' => $row['LastTransactionDate'],
                                            'TopUpType' => $topup,
                                            'PickUpTag' => $pickup,
                                            'Balance' => number_format($row['Balance'], 2 , '.' , ','),
                                            'MinBalance' => number_format($row['MinBalance'], 2 , '.' , ','),
                                            'MaxBalance' => number_format($row['MaxBalance'], 2 , '.' , ','),
                                            'SiteName' => $row['SiteName']
                                         );
                        array_push($completequerysample,$resultarray);
                        $totalexcel_array = array(
                            0 => $row['SiteName'],
                            1 => $row['LastTransactionDate'],
                            2 => $topup,
                            3 => $pickup,
                            4 => $row['Balance'],
                            5 => $row['MinBalance'],
                            6 => $row['MaxBalance']
                        );
                        array_push($completeExcelValuesArray,$totalexcel_array);
                    }
                }

                /*Total(Min,Max,Balance) Results for BCF*/
                $total = $conn->GetMinMaxBalTotal();
                $conn->prepare($total);
                $conn->bindParam(1, $sites_array[$ctr]);
                $conn->execute();

                if( $conn->rowCount() > 0 )
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $total_balance = ($total_balance + $row->bal);
                        $total_minbalance = ($total_minbalance + $row->minBal);
                        $total_maxbalance = ($total_maxbalance + $row->maxBal);
                    }
                }
            }
        }

        $total_array = array(
            'minBal' => number_format($total_minbalance, 2 , '.' , ','),
            'bal' => number_format($total_balance, 2 , '.' , ','),
            'maxBal' => number_format($total_maxbalance, 2 , '.' , ',')
        );
        $totalexcel_array = array(
            0 => 'Total',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => '',
            6 => $total_balance
        );
        //print_r($completequerysample);
        array_push($completeExcelValuesArray,$totalexcel_array);
        $a = array();
        krsort($completequerysample,SORT_NUMERIC);
        $_SESSION['bcf_total_page_balance'] = number_format($total_page_balance, 2 , '.' , ',');
        $_SESSION['bcf_total_page_minbalance'] = number_format($total_page_minbalance, 2 , '.' , ',');
        $_SESSION['bcf_total_page_maxbalance'] = number_format($total_page_maxbalance, 2 , '.' , ',');
        $_SESSION['bcfResults'] = $completequerysample;
        $_SESSION['bcfTotalResult'] = $total_array;
        $_SESSION['bcf_pagingarray'] = $completePagingArray;
        $_SESSION['bcf_page'] = $_GET['page'];
        $_SESSION['bcf_total_page_count'] = ceil(($row_count/$limit));
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $_SESSION['report_header']=array('Site','Last Transaction Date','Auto TopUp Type','Pick Up Tag','Minimum Balance','Maximum Balance','Balance');
        header("Location: ../views/BcfReport.php");
        $conn->close();
    }
    else if($type == 'st')
    {
        $stcompletearray = array();
        $completeExcelValuesArray = array();
        /*Query for Site Transaction Report*/
        $streport = $conn->GetSiteTrans();
        $conn->executeQuery($streport);
        $_SESSION['stTotal'] = $conn->rowCount();
        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $starray = array(
                                'TransactionReferenceID' => $row->TransactionReferenceID ,
                                'TransactionSummaryID' => $row->TransactionSummaryID ,
                                'termname' => $row->termname ,
                                'TransactionType' => $row->TransactionType ,
                                'ServiceID' => $row->ServiceID ,
                                'Status' => $row->Status ,
                                'Amount' => $row->Amount
                );
                array_push($stcompletearray,$starray);
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
                $excel_array = array(
                                0 => $row->TransactionReferenceID ,
                                2 => $row->termname ,
                                3 => $sType ,
                                4 => $row->ServiceID ,
                                5 => $row->Status ,
                                6 => $row->Amount
                );
                array_push($completeExcelValuesArray,$excel_array);
            }
            $_SESSION['stReport'] = $stcompletearray;
        }
        else
        {

        }
        /*Query for Site Transaction Report Total*/
        $sttotalcomplete = array();
        $stTotal = $conn->GetStTotal();
        $conn->executeQuery($stTotal);
        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $sttotal = array(
                                'total' => $row->total
                );
                array_push($sttotalcomplete,$sttotal);
                $excel_array = array(
                                0 => 'Total' ,
                                1 => '' ,
                                2 => '' ,
                                3 => '' ,
                                4 => '' ,
                                5 => '' ,
                                6 => $row->total
                );
            }
            $_SESSION['stReportTotal'] = $sttotalcomplete;
        }
        else
        {

        }
        $conn->close();
        $_SESSION['bcfResults'] = $stcompletearray;
        $_SESSION['report_values'] = $completeExcelValuesArray;
        header("Location: ../views/STReport.php");
    }
    else if($type == 'stp')
    {
        $completeExcelValuesArray = array();
        $completePagingArray = array();
        $limit = 10;
        $total_page_total = 0;
	$overall_total = 0;
        
        if(!isset($_GET['date']))
            $date = date("Y-m-d");
        else
            $date = $_GET['date'];
        $enddate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($date)));

	$all_sites_array = array();
	$completeResultArray = array();
	$all_sites = $conn->GetAllSiteIds();
	$conn->prepare($all_sites);
	$conn->bindParam(1,$aid);
	$conn->execute();
	if($conn->rowCount() > 0)
	{
		while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
		{
			array_push($all_sites_array,$row->SiteID);
		}
	}
	
        /*Query for Site Transaction*/
	if(count($all_sites_array) > 0)
	{
		for($ctr = 0 ; $ctr < count($all_sites_array) ; $ctr++)
		{
			$stpNoLimit = $conn->SiteTransPerDayPagingNoLimit();
			$conn->prepare($stpNoLimit);
			$conn->bindParam(1, $date . " " . $cutoff_time);
			$conn->bindParam(2, $enddate . " " . $cutoff_time);
			$conn->bindParam(3, $all_sites_array[$ctr]);
			$conn->execute();		
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
				$excel_array = array(
				                0 => $row->TransactionReferenceID,
				                1 => $row->termname,
				                2 => $pType,
				                3 => $row->servname,
				                4 => $stat,
				                5 => $row->Amount
				);
				array_push($completeExcelValuesArray,$excel_array);
				$result_array = array(
				                'TransactionDetailsID' => $row->TransactionDetailsID,
				                'TransactionReferenceID' => $row->TransactionReferenceID,
				                'TransactionSummaryID' => $row->TransactionSummaryID,
				                'termname' => $row->termname,
				                'pType' => $pType,
				                'servname' => $row->servname,
				                'stat' => $stat,
				                'Amount' => number_format($row->Amount, 2 , '.' , ',')
				);
				array_push($completeResultArray,$result_array);
			    }
			}

			/*Query of total for Site Remittance with specific date*/
			$total = $conn->SumSiteTransPerDay();
			$conn->prepare($total);
			$conn->bindParam(1, $date . " " . $cutoff_time);
			$conn->bindParam(2, $enddate . " " . $cutoff_time);
			$conn->bindParam(3, $all_sites_array[$ctr]);
			$conn->execute();
			while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
			{
			    $overall_total = ($overall_total + $row->total);

			}
			/*$stp = $conn->SiteTransPerDayPaging($start,$limit,'td.TransactionType','ASC');
			$conn->prepare($stp);
			$conn->bindParam(1, $date . " " . $cutoff_time);
			$conn->bindParam(2, $enddate . " " . $cutoff_time);
			$conn->execute();
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
				$total_page_total = ($total_page_total + $row->Amount);
				$paging_array = array(
				                'TransactionDetailsID' => $row->TransactionDetailsID,
				                'TransactionReferenceID' => $row->TransactionReferenceID,
				                'TransactionSummaryID' => $row->TransactionSummaryID,
				                'termname' => $row->termname,
				                'pType' => $pType,
				                'servname' => $row->servname,
				                'stat' => $stat,
				                'Amount' => number_format($row->Amount, 2 , '.' , ',')
				);
				array_push($completePagingArray,$paging_array);
			    }
			}*/
			
		}
	}
	else
	{

	}
        $conn->close();
	if(!isset($_GET['page']))
            $start_index = 0;
        else
            $start_index = (($_GET['page'] - 1) * $limit);
	
	$end_index = ($start_index + ($limit - 1));
	if($end_index > count($completeResultArray))
		$end_index = (count($completeResultArray) - 1);
	else
		$end_index = $end_index;

	if(count($completeResultArray) > 0)
	{
		for($i = $start_index ; $i <= $end_index ; $i++)
		{
			$result_array = array(
					'TransactionDetailsID' => $completeResultArray[$i]['TransactionDetailsID'],
				        'TransactionReferenceID' => $completeResultArray[$i]['TransactionReferenceID'],
				        'TransactionSummaryID' => $completeResultArray[$i]['TransactionSummaryID'],
				        'termname' => $completeResultArray[$i]['termname'],
				        'pType' => $completeResultArray[$i]['pType'],
				        'servname' => $completeResultArray[$i]['servname'],
				        'stat' => $completeResultArray[$i]['stat'],
				        'Amount' => number_format($completeResultArray[$i]['Amount'], 2 , '.' , ',')
			);
			$total_page_total = ($total_page_total + $completeResultArray[$i]['Amount']);
			array_push($completePagingArray,$result_array);
		} 
	}
	else
	{
		
	}
	$excel_array = array(
			0 => 'Total',
			1 => '',
			2 => '',
			3 => '',
			4 => '',
			5 => $overall_total
	);
	array_push($completeExcelValuesArray,$excel_array);
        //print_r($completePagingArray);
	$total_count = count($completeResultArray);
	$_SESSION['stptotal'] = number_format($overall_total, 2 , '.' , ',');
	$_SESSION['stp_total_page_count'] = ceil(count($completeResultArray)/$limit);
        $_SESSION['stp_total_page_total'] = number_format($total_page_total, 2 , '.' , ',');
        $_SESSION['stp_coverage'] = "Coverage: " . $date . " 6:00 AM to " . $enddate . " 6:00 AM";
        $_SESSION['sitetrans'] = $completePagingArray;
        $_SESSION['stp_page'] = $_GET['page'];
        $_SESSION['stp_date'] = $_GET['date'];
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $_SESSION['report_header']=array('Transaction Reference ID','Transaction Summary ID','Terminal','Transaction Type','Service ID','Status','Amount');
        header("Location: ../views/STReportPerDay.php");
    }
    else if($type == 'ajaxstp')
    {
        $rptDate = $_GET['rptDate'];
        $completeExcelValuesArray = array();
        /*Query for Site Remittance*/
        $stp = $conn->GetSiteTransPerDay();
        $conn->prepare($stp);
        $conn->bindParam(1, $rptDate , "6:00:00");
        $conn->execute();
        $_SESSION['stpTotal'] = $conn->rowCount();
        $result_string = "";
        $result_string .= "<table border='1'>";
        $result_string .= "<tr><td colspan='8'>Report for $rptDate</td></tr>";
        $result_string .= "<tr>
                                <td>Transaction Reference ID</td>
                                <td>Transaction Summary ID</td>
                                <td>Terminal</td>
                                <td>Transaction Type</td>
                                <td>Service ID</td>
                                <td>Status</td>
                                <td>Amount</td>
                           </tr>";

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $result_string .= "<tr>";
                $result_string .= "<td>" . $row->TransactionReferenceID . "</td>";
                $result_string .= "<td>" . $row->TransactionSummaryID . "</td>";
                $result_string .= "<td>" . $row->termname . "</td>";
                if($row->TransactionType == "D")
                {
                    $result_string .= "<td>Deposit</td>";
                    $pType= "Deposit";
                }
                else if($row->TransactionType == "R")
                {
                    $result_string .= "<td>Reload</td>";
                    $pType= "Reload";
                }
                else
                {
                    $result_string .= "<td>Withdrawal</td>";
                    $pType= "Withdrawal";
                }
                $result_string .= "<td>" . $row->servname . "</td>";
                if($row->Status == 0)
                {
                    $result_string .= "<td>Pending</td>";
                    $stat = "Pending";
                }
                else if($row->Status == 1)
                {
                    $result_string .= "<td>Successfull</td>";
                    $stat = "Successfull";
                }
                else if($row->Status == 2)
                {
                    $result_string .= "<td>Failed</td>";
                    $stat = "Failed";
                }
                else if($row->Status == 3)
                {
                    $result_string .= "<td>Timed Out</td>";
                    $stat = "Timed Out";
                }
                else if($row->Status == 4)
                {
                    $result_string .= "<td>Transaction Approved</td>";
                    $stat = "Transaction Approved";
                }
                else
                {
                    $result_string .= "<td>Transaction Denied</td>";
                    $stat = "Transaction Denied";
                }
                $result_string .= "<td>" . $row->Amount . "</td>";
                $result_string .= "</tr>";
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
        }
        else
        {
            $result_string .= "<tr><td colspan='7'>No Records Found</td></tr>";
        }
        /*Query of total for Site Remittance*/
        $total = $conn->SumSiteTransPerDay();
        $conn->prepare($total);
        $conn->bindParam(1, $rptDate);
        $conn->execute();
        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
        {
            if($row->total == "")
            {
                $result_string .= "<tr><td colspan='6'>Total</td>0<td></td></tr>";
            }
            else
            {
                $result_string .= "<tr><td colspan='6'>Total</td><td>$row->total</td></tr>";
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
        echo $result_string;
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $conn->close();
    }
    else if($type == 'sr')
    {
        $limit = 10;
	
	$all_sites_array = array();
	$completeResultArray = array();
	$completeExcelValuesArray = array();
	$sr_total = 0;
	$total_page_amount = 0;
	$all_sites = $conn->GetAllSiteIds();
	$conn->prepare($all_sites);
	$conn->bindParam(1,$aid);
	$conn->execute();
	if($conn->rowCount() > 0)
	{
		while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
		{
			array_push($all_sites_array,$row->SiteID);
		}
	}

	if(count($all_sites_array) > 0)
	{
             for($i = 0 ; $i < count($all_sites_array) ; $i++)
	     {
		$srCompleteResult = array();
		/*Query for retrieving Site Remittance Report*/
		$siteRemit = $conn->GetSiteRemit();
		$conn->prepare($siteRemit);
		$conn->bindParam(1,$all_sites_array[$i]);
		$conn->execute();
		$row_count = $conn->rowCount();
		if( $conn->rowCount() > 0 )
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
		        $excelarray = array(
		                        0 => $row->siteName,
		                        1 => $row->bankname,
		                        2 => $row->Branch,
		                        3 => $row->BankTransactionID,
		                        4 => $row->BankTransactionDate,
		                        5 => $row->ChequeNumber,
		                        6 => $row->DateCreated,
		                        7 => $row->Amount
		        );
		        array_push($completeExcelValuesArray,$excelarray);
			$srResult = array(
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
		                        'Amount' => $row->Amount,
		                        'DateCreated' => $row->DateCreated
		        );
		        array_push($completeResultArray,$srResult);
		    }
		}
		/*$sr = $conn->SiteRemitPaging($start,$limit);
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

		        $total_page_amount = ($total_page_amount + $row->Amount);
		        $srResult = array(
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
		                        'Amount' => number_format($row->Amount, 2 , '.' , ','),
		                        'DateCreated' => $row->DateCreated
		        );
		        array_push($srCompleteResult,$srResult);
		    }
		}*/
		//print_r($srCompleteResult);
		
		//print_r($srCompleteResult);
		$srCompleteTotal = array();
		/*Query for retrievinf total of Site Remittance Report*/
		$siteRemitTotal = $conn->SumSiteRemit();
		$conn->prepare($siteRemitTotal);
		$conn->bindParam(1,$all_sites_array[$i]);
		$conn->execute();
		if( $conn->rowCount() > 0 )
		{
		    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
		    {
			$sr_total = ($sr_total + $row->total);
		    }
		    
		}
	     }
	}

	if(!isset($_GET['page']))
            $start_index = 0;
        else
            $start_index = (($_GET['page'] - 1) * $limit);
	
	$end_index = ($start_index + ($limit - 1));
	if($end_index > count($completeResultArray))
		$end_index = (count($completeResultArray) - 1);
	else
		$end_index = $end_index;

	if(count($completeResultArray) > 0)
	{
		for($i = $start_index ; $i <= $end_index ; $i++)
		{
			echo $completeResultArray[$i]['Amount'];
			$result_array = array(
					'RemittanceType' => $completeResultArray[$i]['RemittanceType'],
				        'BankName' => $completeResultArray[$i]['BankName'],
				        'Branch' => $completeResultArray[$i]['Branch'],
				        'BankTransactionID' => $completeResultArray[$i]['BankTransactionID'],
				        'BankTransactionDate' => $completeResultArray[$i]['BankTransactionDate'],
				        'ChequeNumber' => $completeResultArray[$i]['ChequeNumber'],
				        'Particulars' => $completeResultArray[$i]['Particulars'],
				        'Status' => $completeResultArray[$i]['Status'],
					'username' => $completeResultArray[$i]['username'],
				        'siteName' => $completeResultArray[$i]['siteName'],
				        'Amount' => number_format($completeResultArray[$i]['Amount'], 2 , '.' , ','),
				        'DateCreated' => $completeResultArray[$i]['DateCreated'],		
			);
			$total_page_amount = ($total_page_amount + $completeResultArray[$i]['Amount']);
			array_push($srCompleteResult,$result_array);
		} 
	}
	else
	{
		
	}
	$excel_array = array(
                        0 => 'Total',
                        1 => '',
                        2 => '',
                        3 => '',
                        4 => '',
                        5 => '',
                        6 => '',
                        7 => $sr_total
        );
        array_push($completeExcelValuesArray,$excel_array);
	//print_r($completeExcelValuesArray);
	$_SESSION['arlene'] = $aid;
	$_SESSION['siteRemit'] = $srCompleteResult;
	$_SESSION['siteRemitTotal'] = number_format($sr_total, 2 , '.' , ',');
	$_SESSION['page'] = $_GET['page'];
        $_SESSION['total_page_count'] = ceil(count($completeResultArray)/$limit);
        $_SESSION['total_page_amount'] = number_format($total_page_amount, 2 , '.' , ',');
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $_SESSION['report_header']=array('Site','Bank Name','Branch','ID','Deposit Date','Cheque Number','Date/Time','Amount');
        header("Location: ../views/SiteRemit.php");
        $conn->close();
    }
    else if($type == 'gh')
    {
        $total_deposit = 0;
        $total_reload = 0;
        $total_withdraw = 0;
        $total_grosshold = 0;
        $total_page_deposit = 0;
        $total_page_reload = 0;
        $total_page_withdraw = 0;
        $total_page_grosshold = 0;
        $total_page_amount = 0;

        //$_SESSION['accID'] = 1;								/*Disable when integrated*/
        if(!isset($_GET['strdate']))
            $date = date("Y-m-d");
        else
            $date = $_GET['strdate'];

        if(!isset($_GET['enddate']))
            $enddate = date("Y-m-d");
        else
            $enddate = $_GET['enddate'];
        $page = $_GET['page'];
        $strdate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($enddate)));
        $limit = 10;
        //echo $strdate;
        if($page == 0)
            $start = 0;
        else
            $start = (($page - 1) * $limit);
        $_SESSION['strdate'] = $date;
        $_SESSION['enddate'] = $enddate;
        $_SESSION['page'] = $page;
        $_SESSION['report_coverage'] = "Coverage: $date $cutoff_time to $strdate $cutoff_time";
        
        $site = $conn->GetSiteID();
        $conn->prepare($site);
        $conn->bindParam(1, $aid);
        $conn->execute();
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $siteid = $row->SiteID;
            }
        }
        else
        {
            $siteid = '';
        }
        
        $terminals_array = array();
        $terminals = $conn->GetSupUniqueTerminalID($start, $limit);
        $conn->prepare($terminals);
        $conn->bindParam(1, $siteid);
        $conn->bindParam(2, $date . " " . $cutoff_time);
        $conn->bindParam(3, $strdate . " " . $cutoff_time);
        $conn->execute();

        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                array_push($terminals_array,$row->TerminalID);
            }
        }

        $allGrossHoldArray = array();
        
        if(count($terminals_array) > 0)
        {

            for($ctr = 0 ; $ctr < count($terminals_array) ; $ctr++)
            {
                $termname = $conn->GetTerminalName();
                $conn->prepare($termname);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $termname_array = array('TerminalName' => $row->TerminalName);
                    }
                }

                $deposit = $conn->GetSupDepositWithdrawalReload();
                $conn->prepare($deposit);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->bindParam(2, 'D');
                $conn->bindParam(3, $siteid);
                $conn->bindParam(4, $date . " " . $cutoff_time);
                $conn->bindParam(5, $strdate . " " . $cutoff_time);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $total_page_deposit = ($total_page_deposit + $row->total);
                        $deposit = $row->total;
                        $deposit_array = array('Deposit' => number_format($row->total, 2 , '.' , ','));
                    }
                }

                $reload = $conn->GetSupDepositWithdrawalReload();
                $conn->prepare($reload);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->bindParam(2, 'R');
                $conn->bindParam(3, $siteid);
                $conn->bindParam(4, $date . " " . $cutoff_time);
                $conn->bindParam(5, $strdate . " " . $cutoff_time);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $total_page_reload = ($total_page_reload + $row->total);
                        $reload = $row->total;
                        $reload_array = array('Reload' => number_format($row->total, 2 , '.' , ','));
                    }
                }

                $withdraw = $conn->GetSupDepositWithdrawalReload();
                $conn->prepare($withdraw);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->bindParam(2, 'W');
                $conn->bindParam(3, $siteid);
                $conn->bindParam(4, $date . " " . $cutoff_time);
                $conn->bindParam(5, $strdate . " " . $cutoff_time);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $total_page_withdraw = ($total_page_withdraw + $row->total);
                        $withdraw = $row->total;
                        $withdraw_array = array('Withdraw' => number_format($row->total, 2 , '.' , ','));
                    }
                }
                $gross_hold = (($deposit + $reload) - $withdraw);
                $total_page_grosshold = ($total_page_grosshold + $gross_hold);
                $grosshold_array = array('GrossHold' => number_format($gross_hold, 2 , '.' , ','));
                $gh = array_merge($termname_array,$deposit_array,$reload_array,$withdraw_array,$grosshold_array);
                //print_r($gh);
                array_push($allGrossHoldArray,$gh);
            }
        }
        
        $_SESSION['completePagingArray'] = $allGrossHoldArray;

        /*Query for Excel Report*/
        $excelterminals_array = array();
        $terminals = $conn->GetAllSupUniqueTerminalID();
        $conn->prepare($terminals);
        $conn->bindParam(1, $siteid);
        $conn->bindParam(2, $date . " " . $cutoff_time);
        $conn->bindParam(3, $strdate . " " . $cutoff_time);
        $conn->execute();

        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                array_push($excelterminals_array,$row->TerminalID);
            }
        }
        $excelGrossHoldArray = array();
        if(count($excelterminals_array) > 0)
        {
            for($ctr = 0 ; $ctr < count($terminals_array) ; $ctr++)
            {
                $termname = $conn->GetTerminalName();
                $conn->prepare($termname);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $termname_array = array('TerminalName' => $row->TerminalName);
                    }
                }

                $deposit = $conn->GetSupDepositWithdrawalReload();
                $conn->prepare($deposit);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->bindParam(2, 'D');
                $conn->bindParam(3, $siteid);
                $conn->bindParam(4, $date . " " . $cutoff_time);
                $conn->bindParam(5, $strdate . " " . $cutoff_time);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $deposit = $row->total;
                        $deposit_array = array('Deposit' => $row->total);
                        $total_deposit = ($total_deposit + $row->total);
                    }
                }

                $reload = $conn->GetSupDepositWithdrawalReload();
                $conn->prepare($reload);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->bindParam(2, 'R');
                $conn->bindParam(3, $siteid);
                $conn->bindParam(4, $date . " " . $cutoff_time);
                $conn->bindParam(5, $strdate . " " . $cutoff_time);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $reload = $row->total;
                        $reload_array = array('Reload' => $row->total);
                        $total_reload = ($total_reload + $row->total);
                    }
                }

                $withdraw = $conn->GetSupDepositWithdrawalReload();
                $conn->prepare($withdraw);
                $conn->bindParam(1, $terminals_array[$ctr]);
                $conn->bindParam(2, 'W');
                $conn->bindParam(3, $siteid);
                $conn->bindParam(4, $date . " " . $cutoff_time);
                $conn->bindParam(5, $strdate . " " . $cutoff_time);
                $conn->execute();
                if($conn->rowCount() > 0)
                {
                    while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                    {
                        $withdraw = $row->total;
                        $withdraw_array = array('Withdraw' => $row->total);
                        $total_withdraw = ($total_withdraw + $row->total);
                    }
                }
                $gross_hold = (($deposit + $reload) - $withdraw);
                $grosshold_array = array('GrossHold' => $gross_hold);
                $total_grosshold = ($total_grosshold + $gross_hold);
                $gh = array_merge($termname_array,$deposit_array,$reload_array,$withdraw_array,$grosshold_array);
                array_push($excelGrossHoldArray,$gh);
            }
        }
        $completeExcelArray = array();
        if(count($excelGrossHoldArray) > 0)
        {
            for($i = 0 ; $i < count($excelGrossHoldArray) ; $i++)
            {
                $excel_array = array(
                        0 => $excelGrossHoldArray[$i]['TerminalName'],
                        1 => $excelGrossHoldArray[$i]['Deposit'],
                        2 => $excelGrossHoldArray[$i]['Reload'],
                        3 => $excelGrossHoldArray[$i]['Withdraw'],
                        4 => $excelGrossHoldArray[$i]['GrossHold']
                );
                array_push($completeExcelArray,$excel_array);
            }
        }
        $totals = array(
            0 => 'Total',
            1 => number_format($total_deposit, 2 , '.' , ','),
            2 => number_format($total_reload, 2 , '.' , ','),
            3 => number_format($total_withdraw, 2 , '.' , ','),
            4 => number_format($total_grosshold, 2 , '.' , ','),
        );

        $excel_totals = array(
            0 => 'Total',
            1 => $total_deposit,
            2 => $total_reload,
            3 => $total_withdraw,
            4 => $total_grosshold,
        );
        array_push($completeExcelArray,$excel_totals);
        $_SESSION['total_page_count'] = ceil((count($excelterminals_array)/$limit));
        $_SESSION['total_page_deposit'] = number_format($total_page_deposit, 2 , '.' , ',');
        $_SESSION['total_page_reload'] = number_format($total_page_reload, 2 , '.' , ',');
        $_SESSION['total_page_withdraw'] = number_format($total_page_withdraw, 2 , '.' , ',');
        $_SESSION['total_page_grosshold'] = number_format($total_page_grosshold, 2 , '.' , ',');
        $_SESSION['completeTotalArray'] = $totals;
        $_SESSION['report_header']=array('Terminal','Deposit','Reload','Withdrawal','Gross Hold');
        $_SESSION['report_values'] = $completeExcelArray;
        header("Location: ../views/GrossHold.php");
        $conn->close();
    }
    else if($type == 'ajaxgh')
    {
        $completeExcelValuesArray = array();
        $result_string = "";
        $strdate = date ( 'Y-m-j' , strtotime ('-1 day' , strtotime($_GET['strdate'])));
        $enddate = $_GET['enddate'];
        $siteID = $_GET['siteid'];
        $trmlID = $_GET['termid'];
        $strDate = "$strdate 6:00:00";
        $endDate = "$enddate 6:00:00";
        if($siteID == 0)                                                        /*Checks if the sitename selected is ALL*/
        {
            $ghQuery = $conn->GetAllGrossHold();
            $conn->executeQuery($ghQuery);
        }
        else
        {
            $ghQuery = $conn->GetGrossHold();
            $conn->prepare($ghQuery);
            $conn->bindParam(1, $siteID);
            $conn->bindParam(2, $trmlID);
            $conn->bindParam(3, $strDate);
            $conn->bindParam(4, $endDate);
            $conn->execute();
        }

        $result_string .= "<table border=1>";
        $result_string .= "<tr>
                                <td>Transaction Reference ID</td>
                                <td>Transaction Type</td>
                                <td>Service ID</td>
                                <td>Created By</td>
                                <td>Site Name</td>
                                <td>Status</td>
                                <td>Amount</td>
                           </tr>";

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $result_string .= "<tr>";
                $result_string .= "<td>$row->TransactionReferenceID</td>";
                if($row->TransactionType == "D")
                {
                    $result_string .= "<td>Deposit</td>";
                    $dType = "Deposit";
                }
                else if($row->TransactionType == "R")
                {
                    $result_string .= "<td>Reload</td>";
                    $dType = "Reload";
                }
                else
                {
                    $result_string .= "<td>Withdrawal</td>";
                    $dType = "Withdrawal";
                }
                $result_string .= "<td>$row->ServiceID</td>";
                $result_string .= "<td>$row->username</td>";
                $result_string .= "<td>$row->sitename</td>";
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
                $result_string .= "<td>$stat</td>";
                $result_string .= "<td>$row->Amount</td>";
                $result_string .= "</tr>";
                $excel_array = array(
                                0 => $row->TransactionReferenceID,
                                1 => $dType,
                                2 => $row->ServiceID,
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
            $result_string .= "<tr><td colspan='7'>No Record Found</td></tr>";
        }

        if($siteID == 0)                                                        /*Checks if the sitename selected is ALL*/
        {
            $ghSumQuery = $conn->SumAllGrossHold();
            $conn->executeQuery($ghSumQuery);
        }
        else
        {
            $ghSumQuery = $conn->SumGrossHold();
            $conn->prepare($ghSumQuery);
            $conn->bindParam(1, $siteID);
            $conn->bindParam(2, $trmlID);
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
                    $result_string .= "<tr>";
                    $result_string .= "<td colspan='6'>Total</td>";
                    $result_string .= "<td>$row->total</td>";
                    $result_string .= "</tr>";
                }
                else
                {
                    $result_string .= "<tr>";
                    $result_string .= "<td colspan='6'>Total</td>";
                    $result_string .= "<td>0</td>";
                    $result_string .= "</tr>";
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
        $_SESSION['report_values'] = $completeExcelValuesArray;
        echo $result_string;
        $conn->close();
    }
    else if($type == 'sl')
    {
        /*Query for retrieving all sites*/
        $siteList = array();
        $sites = $conn->GetSiteList();
        $conn->executeQuery($sites);
        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $site = array(
                            'id' => $row->SiteID,
                            'name' => $row->SiteName
                );
                array_push($siteList,$site);
            }
            $_SESSION['siteList'] = $siteList;
        }

        header("Location: ../views/operations.php");
        $conn->close();
    }
    else if($type == 'ajaxsl')
    {
        $completeExcelValuesArray = array();
        $result_string = "";
        $slSiteID = $_GET['slsiteid'];
        /*Site & Site Details*/
        if( $slSiteID == 0 )
        {
            $siteDtls = $conn->GetAllSiteDetails();
            $conn->executeQuery($siteDtls);
        }
        else
        {
            $siteDtls = $conn->GetSiteDetails();
            $conn->prepare($siteDtls);
            $conn->bindParam(1, $slSiteID);
            $conn->execute();
        }

        $result_string .= "<table border=1>";
        $result_string .= "<tr>
                    <td>Site Name</td>
                    <td>Site Code</td>
                    <td>Status</td>
                    <td>Site Description</td>
                    <td>Site Alias</td>
                    <td>Site Island</td>
                    <td>Site Region</td>
                    <td>Site Province</td>
                    <td>Site City</td>
                    <td>Site Barangay</td>
                    <td>Site Address</td>
              </tr>";
        if( $conn->rowCount() > 0 )
        {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                        $result_string .= "<tr>";
                        $result_string .= "<td>$row->SiteName</td>";
                        $result_string .= "<td>$row->SiteCode</td>";
                        #Status : 0 - Pending; 1 - Active; 2 - Suspended; 3 - Deactivated;
                        if($row->Status == 0)
                        {
                                $result_string .= "<td>Pending</td>";
                                $sType = "Pending";
                        }
                        else if($row->Status == 1)
                        {
                                $result_string .= "<td>Active</td>";
                                $sType = "Active";
                        }
                        else if($row->Status == 2)
                        {
                                $result_string .= "<td>Suspended</td>";
                                $sType = "Suspended";
                        }
                        else
                        {
                                $result_string .= "<td>Deactivated</td>";
                                $sType = "Deactivated";
                        }
                        $result_string .= "<td>$row->SiteDescription</td>";
                        $result_string .= "<td>$row->SiteAlias</td>";
                        $result_string .= "<td>$row->IslandName</td>";
                        $result_string .= "<td>$row->RegionName</td>";
                        $result_string .= "<td>$row->ProvinceName</td>";
                        $result_string .= "<td>$row->CityName</td>";
                        $result_string .= "<td>$row->BarangayName</td>";
                        $result_string .= "<td>$row->SiteAddress</td>";
                        $result_string .= "</tr>";
                        $excel_array = array(
                                        0 => $row->SiteName,
                                        1 => $row->SiteCode,
                                        2 => $sType,
                                        3 => $row->SiteDescription,
                                        4 => $row->SiteAlias,
                                        5 => $row->IslandName,
                                        6 => $row->RegionName,
                                        7 => $row->ProvinceName,
                                        8 => $row->CityName,
                                        9 => $row->BarangayName,
                                        10 => $row->SiteAddress,
                        );
                        array_push($completeExcelValuesArray,$excel_array);
                }
        }
        else
        {
                $result_string .= "<tr><td colspan='10'>No Record Found</td></tr>";
        }
        $result_string .= "</table>";

        /*Site & Terminals*/
        if( $slSiteID == 0 )
        {
            $siteTrml = $conn->GetAllSiteTerminals();
            $conn->executeQuery($siteTrml);
        }
        else
        {
            $siteTrml = $conn->GetSiteTerminals();
            $conn->prepare($siteTrml);
            $conn->bindParam(1, $slSiteID);
            $conn->execute();
        }
        $result_string .= "<table border=1>";
        $result_string .= "<tr>
                                <td>Terminal Name</td>
                                <td>Terminal Code</td>
                                <td>Status</td>
                           </tr>";
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
                        10 => '',
        );
        array_push($completeExcelValuesArray,$excel_array);
        if( $conn->rowCount() > 0 )
        {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                        $result_string .= "<tr>";
                        $result_string .= "<td>$row->TerminalName</td>";
                        $result_string .= "<td>$row->TerminalCode</td>";
                        //Status : 0 - Pending; 1 - Active; 2 - Disabled; 3 - Terminated'
                        if($row->Status == 0)
                        {
                                $result_string .= "<td>Pending</td>";
                                $tType = "Pending";
                        }
                        else if($row->Status == 1)
                        {
                                $result_string .= "<td>Active</td>";
                                $tType = "Active";
                        }
                        else if($row->Status == 2)
                        {
                                $result_string .= "<td>Disabled</td>";
                                $tType = "Disabled";
                        }
                        else
                        {
                                $result_string .= "<td>Terminated</td>";
                        }
                        $result_string .= "</tr>";
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
                                        10 => '',
                        );
                        array_push($completeExcelValuesArray,$excel_array);
                }
        }
        else
        {
                $result_string .= "<tr><td colspan='3'>No Record Found</td></tr>";
        }
        $result_string .= "</table>";

        /*Site and Accounts*/
        if( $slSiteID == 0 )
        {
            $siteAcct = $conn->GetAllSiteAccounts();
            $conn->executeQuery($siteAcct);
        }
        else
        {
            $siteAcct = $conn->GetSiteAccounts();
            $conn->prepare($siteAcct);
            $conn->bindParam(1, $slSiteID);
            $conn->execute();
        }
        $result_string .= "<table border=1>";
        $result_string .= "<tr>
                                <td>Access</td>
                                <td>Address</td>
                                <td>Email</td>
                                <td>Landline</td>
                                <td>MobileNumber</td>
                           </tr>";
        $excel_array = array(
                        0 => 'Access',
                        1 => 'Address',
                        2 => 'Email',
                        3 => 'Landline',
                        4 => 'MobileNumber',
                        5 => '',
                        6 => '',
                        7 => '',
                        8 => '',
                        9 => '',
                        10 => '',
        );
        array_push($completeExcelValuesArray,$excel_array);
        if($conn->rowCount() > 0)
        {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                        $result_string .= "<tr>";
                        $result_string .= "<td>$row->name</td>";
                        $result_string .= "<td>$row->Address</td>";
                        $result_string .= "<td>$row->Email</td>";
                        $result_string .= "<td>$row->Landline</td>";
                        $result_string .= "<td>$row->MobileNumber</td>";
                        $result_string .= "</tr>";
                        $excel_array = array(
                                        0 => $row->name,
                                        1 => $row->Address,
                                        2 => $row->Email,
                                        3 => $row->Landline,
                                        4 => $row->MobileNumber,
                                        5 => '',
                                        6 => '',
                                        7 => '',
                                        8 => '',
                                        9 => '',
                                        10 => '',
                        );
                        array_push($completeExcelValuesArray,$excel_array);
                }
        }
        else
        {
                $result_string .= "<tr><td colspan='5'>No Record Found</td></tr>";
        }
        $result_string .= "</table>";
        echo $result_string;
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $conn->close();
    }
    else if($type == 'csh')
    {
        $completeExcelValuesArray = array();
        $transSummCompleteArray = array();
        $limit = 10;
        if(!isset($_GET['page']))
            $start = 0;
        else
            $start = (($_GET['page'] - 1) * $limit);

        if(!isset($_GET['date']))
            $date = date("Y-m-d");
        else
            $date = $_GET['date'];
        $enddate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($date)));

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
        $_SESSION['total_page_count'] = ceil($total_row_count/$limit);

        $csh = $conn->GetTransSummaryPaging($start,$limit);
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
                                'Deposit' => number_format($row->Deposit, 2 , '.' , ','),
                                'Reload' => number_format($row->Reload, 2 , '.' , ','),
                                'Withdrawal' => number_format($row->Withdrawal, 2 , '.' , ','),
                                'DateStarted' => $row->DateStarted,
                                //'DateStarted' => strtotime($row->DateStarted),
                                'DateEnded' => $dateEnded,
                                'TerminalName' => $row->TerminalName,
                                'GrossHold' => number_format($gh, 2 , '.' , ',')
                );
                array_push($transSummCompleteArray,$transsumm);
                $ghTotalComputation = $ghTotalComputation + $gh;
            }
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
                $totalDeposit = number_format($row->totalDeposit, 2 , '.' , ',');
                $totalReload = number_format($row->totalReload, 2 , '.' , ',');
                $totalWithdrawal = number_format($row->totalWithdrawal, 2 , '.' , ',');
                $totalGrossHold = number_format((($row->totalDeposit + $row->totalReload) - $row->totalWithdrawal), 2 , '.' , ',');
            }
        }
        //print_r($transSummCompleteArray);
        $_SESSION['cshResults'] = $transSummCompleteArray;
        $_SESSION['report_values'] = $completeExcelValuesArray;
        $_SESSION['report_header']=array('Login','Time In','Time Out','Deposit','Reload','Redemption','Gross Hold');
        $_SESSION['coverage'] = "Coverage $date $cutoff_time to $enddate $cutoff_time";
        $_SESSION['page'] = $_GET['page'];
        $_SESSION['date'] = $_GET['date'];
        $_SESSION['totalDeposit'] = $totalDeposit;
        $_SESSION['totalReload'] = $totalReload;
        $_SESSION['totalWithdrawal'] = $totalWithdrawal;
        $_SESSION['totalGrossHold'] = $totalGrossHold;
        $conn->close();
        header("Location: ../views/cashier.php");
    }
    else if($type == 'atplain')
    {
	$completePagingArray = array();
	$completeExcelArray = array();
	$limit = 10;
	if(!isset($_GET['page']))
		$start = 0;
	else
		$start = (($_GET['page'] - 1) * $limit);

	if(!isset($_GET['date']))
		$date = date("Y-m-d");
		
	else
		$date = $_GET['date'];

	$_SESSION['date'] = $date;
	$_SESSION['at_page'] = $_GET['page'];
        $accounttype = $_SESSION['acctype'];
        $accountId = $_SESSION['accID'];
	
        if($accounttype == 1)
        {
            $adttrail = $conn->GetAdminAuditTrail($start, $limit);
            $conn->prepare($adttrail);
            $conn->bindParam(1, $date);
            $conn->execute();
            if($conn->rowCount() > 0)
            {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                    $at = array(
                            'ID' => $row->ID,
                            'username' => $row->username,
                            'TransDetails' => $row->TransDetails,
                            'TransDateTime' => $row->TransDateTime,
                            'RemoteIP' => $row->RemoteIP,
                            'AID' => $row->AID,
                            'accounttype' => $row->accounttype
                    );
                    array_push($completePagingArray,$at);
                }
            }
        }
        else if(($accounttype == 2) || ($accounttype == 8))
        {
            $sites_array = array();
            $aid_array = array();
            $siteaccts = $conn->GetAllSiteIDForAuditTrail();
            $conn->prepare($siteaccts);
            $conn->bindParam(1, $accountId);
            $conn->execute();
            if($conn->rowCount() > 0)
            {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                    array_push($sites_array,$row->SiteID);
                }
            }
            else
            {
                $sites_array = "";
            }

            $allAuditTrail = array();
            if(count($sites_array) > 0)
            {
                for($ctr = 0 ; $ctr<count($sites_array) ; $ctr++)
                {
                    $aid = $conn->GetAllAIDForAuditTrail();
                    $conn->prepare($aid);
                    $conn->bindParam(1, $sites_array[$ctr]);
                    $conn->execute();
                    if($conn->rowCount() > 0)
                    {
                        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                        {
                            if(in_array($row->AID,$aid_array))
                            {

                            }
                            else
                            {
                                array_push($aid_array,$row->AID);
                            }
                        }
                    }
                    else
                    {
                        array_push($aid_array,$row->AID);
                    }
                }
                
                $rows = 0;
                if(count($aid_array) > 0)
                {
                    for($ctr = 0 ; $ctr < count($aid_array) ; $ctr++)
                    {
                        $audit_trails = $conn->GetSelectedAuditTrail();
                        $conn->prepare($audit_trails);
                        $conn->bindParam(1, $date);
                        $conn->bindParam(2, $aid_array[$ctr]);
                        $conn->execute();
                        if($conn->rowCount() > 0)
                        {
                            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                            {
                                $at = array(
                                        'ID' => $row->ID,
                                        'username' => $row->username,
                                        'TransDetails' => $row->TransDetails,
                                        'TransDateTime' => $row->TransDateTime,
                                        'RemoteIP' => $row->RemoteIP,
                                        'AID' => $row->AID,
                                        'accounttype' => $row->accounttype
                                );
                                array_push($allAuditTrail,$at);
                            }
                        }
                        
                        $totals = $conn->GetAllSelectedAuditTrail();
                        $conn->prepare($totals);
                        $conn->bindParam(1, $date);
                        $conn->bindParam(2, $aid_array[$ctr]);
                        $conn->execute();
                        while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                        {
                            $rows = ($rows + $row->total_rows);
                        }
                        
                    }
                    $total_row = $rows;
                }
            }
            else
            {
                $total_row = 0;
            }
            
            if(($start + $limit) > count($allAuditTrail))
            {
                $end = count($allAuditTrail);
            }
            else
            {
                $end = ($start + $limit);
            }
            for($ctr = $start ; $ctr < $end ; $ctr++)
            {
                $audit = array(
                        'ID' => $allAuditTrail[$ctr]['ID'],
                        'username' => $allAuditTrail[$ctr]['username'],
                        'TransDetails' => $allAuditTrail[$ctr]['TransDetails'],
                        'TransDateTime' => $allAuditTrail[$ctr]['TransDateTime'],
                        'RemoteIP' => $allAuditTrail[$ctr]['RemoteIP'],
                        'AID' => $allAuditTrail[$ctr]['AID'],
                        'accounttype' => $allAuditTrail[$ctr]['accounttype']
                );
                array_push($completePagingArray,$audit);
            }
            
        }
        else if($accounttype == 4)
        {
            $adttrail = $conn->GetAllCashierAuditTrail();
            $conn->prepare($adttrail);
            $conn->bindParam(1, $date);
            $conn->bindParam(2, $accountId);
            $conn->execute();

            if($conn->rowCount() > 0)
            {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                    $at = array(
                            'ID' => $row->ID,
                            'username' => $row->username,
                            'TransDetails' => $row->TransDetails,
                            'TransDateTime' => $row->TransDateTime,
                            'RemoteIP' => $row->RemoteIP,
                            'AID' => $row->AID,
                            'accounttype' => $row->accounttype
                    );
                    array_push($completePagingArray,$at);
                }
            }
        }
        else
        {
            
            $adttrail = $conn->GetAuditTrail($start, $limit);
            $conn->prepare($adttrail);
            $conn->bindParam(1, $date);
            $conn->bindParam(2, $accounttype);
            $conn->execute();
            
            if($conn->rowCount() > 0)
            {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                    $at = array(
                            'ID' => $row->ID,
                            'username' => $row->username,
                            'TransDetails' => $row->TransDetails,
                            'TransDateTime' => $row->TransDateTime,
                            'RemoteIP' => $row->RemoteIP,
                            'AID' => $row->AID,
                            'accounttype' => $row->accounttype
                    );
                    array_push($completePagingArray,$at);
                }
            }
        }
        

        if($accounttype == 1)
        {
            $adttrailtotalrow = $conn->GetCountAllAuditTrail();
            $conn->prepare($adttrailtotalrow);
            $conn->bindParam(1, $date);
            $conn->execute();
        }
        else if(($accounttype == 2) || ($accounttype == 8))
        {
            
        }
        else if($accounttype == 4)
        {
            $adttrailtotalrow = $conn->GetCountCashierAuditTrail();
            $conn->prepare($adttrailtotalrow);
            $conn->bindParam(1, $date);
            $conn->bindParam(2, $accountId);
            $conn->execute();
        }
        else
        {
            $adttrailtotalrow = $conn->GetCountAuditTrail();
            $conn->prepare($adttrailtotalrow);
            $conn->bindParam(1, $date);
            $conn->bindParam(2, $accounttype);
            $conn->execute();
        }
        
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $total_row = $row->total_row;
            }
        }

        if($accounttype == 1)
        {
            $adtrailexcel = $conn->GetAllAdminAuditTrail();
            $conn->prepare($adtrailexcel);
            $conn->bindParam(1, $date);
            $conn->execute();
        }
        else if(($accounttype == 2) || ($accounttype == 8))
        {
            for($ctr = 0 ; $ctr < count($allAuditTrail) ; $ctr++)
            {
                $excel_array = array(
                        0 => $allAuditTrail[$ctr]['username'],
                        1 => $allAuditTrail[$ctr]['TransDateTime'],
                        2 => $allAuditTrail[$ctr]['TransDetails'],
                        3 => $allAuditTrail[$ctr]['RemoteIP']
                );
                array_push($completeExcelArray,$excel_array);
            }
        }
        else if($accounttype == 4)
        {
            $adtrailexcel = $conn->GetAllCashierAuditTrail();
            $conn->prepare($adtrailexcel);
            $conn->bindParam(1, $date);
            $conn->bindParam(2, $accountId);
            $conn->execute();
        }
        else
        {
            $adtrailexcel = $conn->GetAllAuditTrail();
            $conn->prepare($adtrailexcel);
            $conn->bindParam(1, $date);
            $conn->bindParam(2, $accounttype);
            $conn->execute();
        }
        if(($accounttype == 2) || ($accounttype == 8))
        {

        }
        else
        {
            if($conn->rowCount() > 0)
            {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                    $excel_array = array(
                            0 => $row->username,
                            1 => $row->TransDateTime,
                            2 => $row->TransDetails,
                            3 => $row->RemoteIP
                    );
                    array_push($completeExcelArray,$excel_array);
                }
            }
        }
        //print_r($allAuditTrail);
	$_SESSION['total_page_count'] = ceil($total_row/$limit);
        $_SESSION['report_header'] = array('UserName','TransactionDate','TransactionDetails','RemoteIPAddress');
        $_SESSION['report_values'] = $completeExcelArray;
	$_SESSION['complete_audit_trail'] = $completePagingArray;
	//print_r($completePagingArray);
	header("Location: ../views/AuditTrailPlain.php");
	$conn->close();
    }
    else
    {
        echo "You cannot access this page";
    }
}
else
{
    echo "Error";
}
?>
