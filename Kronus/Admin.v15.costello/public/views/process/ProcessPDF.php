<?php
/*
 * Created By: Arlene Salazar
 * Purpose: Controller for PDF Convertion
 * Created On: June 10,2011
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

    $rptType = $_GET['rptType'];                                                /*Holds type of report ot be exported in PDF*/
    $cutoff_time = "06:00:00";
    define	('FPDF_FONTPATH','../../sys/class/fpdf/font/');
    require	('../../sys/class/fpdf/fpdf.php');
                       /*====================CHECKING OF REPORT TYPE====================*/
    if($rptType == "bcf")                                                       /*PDF Convertion for BCF per site*/
    {
        /*Query result for all BCF (for PDF)*/
        $completequerysample = array();
        $sites_array = array();
        $accountid = $_SESSION['accID'];
        $total_balance = 0;
        $total_minbalance = 0;
        $total_maxbalance = 0;

        $allSites = $conn->GetAllSiteIds();
        $conn->prepare($allSites);
        $conn->bindParam(1, $accountid);
        $conn->execute();
        if($conn->rowCount() > 0)
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                array_push($sites_array,$row->SiteID);
            }
        }
        $pdf=new FPDF('L','mm','Legal');
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"BCF per Site",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',9);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,'Report for BCF per Site As of ' . date("Y-m-d H:i:s"),0,1,'C');

        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(55);
        $pdf->Cell(35,5,'Site',1,0,'C');
        $pdf->SetX(90);
        $pdf->Cell(50,5,'Last Transaction Date ',1,0,'C');
        $pdf->SetX(140);
        $pdf->Cell(30,5,'TopUp Type',1,0,'C');
        $pdf->SetX(170);
        $pdf->Cell(30,5,'Pick Up Tag ',1,0,'C');
        $pdf->SetX(200);
        $pdf->Cell(35,5,'Minimum Balance',1,0,'R');
        $pdf->SetX(235);
        $pdf->Cell(35,5,'Maximum Balance',1,0,'R');
        $pdf->SetX(270);
        $pdf->Cell(35,5,'Balance',1,1,'R');
	function Footer(&$pdf)
	{
	    // Go to 1.5 cm from bottom
	    $pdf->SetY(-31);
	    // Select Arial italic 8
	    $pdf->SetFont('Arial','I',8);
	    // Print centered page number
	    $pdf->Cell(0,10,'Generated On ' . date('F d,Y h:i:s A').'.',0,0,'C');
		//$pdf->Cell(35,5,'dasdasdasdsa',1,0,'C');
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

                        $pdf->SetX(55);
                        $pdf->Cell(35,5,$row['SiteName'],1,0,'C');
                        $pdf->Cell(50,5,$row['LastTransactionDate'],1,0,'C');
                        $pdf->Cell(30,5,$topup,1,0,'C');
                        $pdf->Cell(30,5,$pickup,1,0,'C');
                        $pdf->Cell(35,5,number_format($row['MinBalance'], 2 , '.' , ','),1,0,'R');
                        $pdf->Cell(35,5,number_format($row['MaxBalance'], 2 , '.' , ','),1,0,'R');
                        $pdf->Cell(35,5,number_format($row['Balance'], 2 , '.' , ','),1,1,'R');
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
        else
        {
            $pdf->SetX(55);
            $pdf->Cell(200,5,"No Record Found",1,1,'C');
        }

        $pdf->SetX(55);
        $pdf->Cell(215,5,"Total",1,0,'C');
        $pdf->Cell(35,5,number_format($total_balance, 2 , '.' , ','),1,1,'R');
	Footer(&$pdf);
        $pdf->Output('BCF per Site As of ' . date("Y-m-d h:i:s A"),'D');
        Header('Content-Type: application/pdf');
        $conn->close();
    }
    else if($rptType == "st1")                                                  /*PDF Convertion of Site Transaction with no specific date*/
    {
        /*Query for Site Transaction Report (for PDF convertion)*/
        $streport = $conn->GetSiteTrans();
        $conn->executeQuery($streport);

        $pdf=new FPDF('L','mm','Legal');
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"Site Transaction",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,'Report for Site Transaction As of ' . date("Y-m-d H:i:s"),0,1,'C');

        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(10);
        $pdf->Cell(50,5,'Transaction Reference ID',1,0,'C');
        $pdf->SetX(60);
        $pdf->Cell(50,5,'Terminal',1,0,'C');
        $pdf->SetX(110);
        $pdf->Cell(50,5,'Transaction Type',1,0,'C');
        $pdf->SetX(160);
        $pdf->Cell(50,5,'Service',1,0,'C');
        $pdf->SetX(210);
        $pdf->Cell(50,5,'Status',1,0,'C');
        $pdf->SetX(260);
        $pdf->Cell(50,5,'Amount',1,1,'C');

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $pdf->SetX(10);
                $pdf->Cell(50,5,$row->TransactionReferenceID,1,0,'C');
                $pdf->Cell(50,5,$row->termname,1,0,'C');
                if($row->TransactionType == "D")
                    $pdf->Cell(50,5,"Deposit",1,0,'C');
                else if($row->TransactionType == "R")
                    $pdf->Cell(50,5,"Reload",1,0,'C');
                else
                    $pdf->Cell(50,5,"Withdrawal",1,0,'C');
                $pdf->Cell(50,5,$row->ServiceID,1,0,'C');
                $pdf->Cell(50,5,$row->Status,1,0,'C');
                $pdf->Cell(50,5,$row->Amount,1,1,'C');
            }
        }
        else
        {
            $pdf->SetX(10);
            $pdf->Cell(300,5,"No Record Found",1,1,'C');
        }
        /*Query for Site Transaction Total (for PDF convertion)*/
        $stTotal = $conn->GetStTotal();
        $conn->executeQuery($stTotal);

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $pdf->SetX(10);
                $pdf->Cell(250,5,"Total",1,0,'C');
                $pdf->Cell(50,5,$row->total,1,1,'C');
            }
        }
        else
        {
            $pdf->SetX(10);
            $pdf->Cell(250,5,"Total",1,0,'C');
            $pdf->Cell(50,5,0,1,1,'C');
        }
        $pdf->Output('Site Transaction As of ' . date("Y-m-d H:i:s"),'D');
        Header('Content-Type: application/pdf');
        
        $conn->close();
    }
    else if($rptType == "st2")                                                  /*PDF Convertion of Site Transaction with specific date*/
    {
        $srRptDate = $_GET['date'];
        $enddate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($srRptDate)));
        /*Query for PDF Site Remittance*/
        $all_sites_array = array();
	$completeResultArray = array();
	$overall_total = 0;
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
			$conn->bindParam(1, $srRptDate . " " . $cutoff_time);
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
			$conn->bindParam(1, $srRptDate . " " . $cutoff_time);
			$conn->bindParam(2, $enddate . " " . $cutoff_time);
			$conn->bindParam(3, $all_sites_array[$ctr]);
			$conn->execute();
			while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
			{
			    $overall_total = ($overall_total + $row->total);
			}
		}
	}
	else
	{

	}

        $pdf=new FPDF('L','mm','Legal');
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"Site Transaction",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,'Report for Site Transaction for ' . $srRptDate,0,1,'C');

        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(30);
        $pdf->Cell(50,5,'Transaction Summary ID',1,0,'C');
        $pdf->SetX(80);
        $pdf->Cell(50,5,'Terminal',1,0,'C');
        $pdf->SetX(130);
        $pdf->Cell(50,5,'Transaction Type',1,0,'C');
        $pdf->SetX(180);
        $pdf->Cell(50,5,'Service',1,0,'C');
        $pdf->SetX(230);
        $pdf->Cell(50,5,'Status',1,0,'C');
        $pdf->SetX(280);
        $pdf->Cell(50,5,'Amount',1,1,'R');
	$pdf->SetFont('Arial','',9);
	function Footer(&$pdf)
	{
	    // Go to 1.5 cm from bottom
	    $pdf->SetY(-31);
	    // Select Arial italic 8
	    $pdf->SetFont('Arial','I',8);
	    // Print centered page number
	    $pdf->Cell(0,10,'Generated On ' . date('F d,Y h:i:s A').'.',0,0,'C');
		//$pdf->Cell(35,5,'dasdasdasdsa',1,0,'C');
	}
	if(count($completeResultArray) > 0)
	{
	   	for($ctr = 0 ; $ctr < /*10000*/count($completeResultArray) ; $ctr++)
		{
			$pdf->SetX(30);
		        $pdf->Cell(50,5,$completeResultArray[$ctr]['TransactionReferenceID'],1,0,'C');
		        $pdf->Cell(50,5,$completeResultArray[$ctr]['termname'],1,0,'C');
			$pdf->Cell(50,5,$completeResultArray[$ctr]['pType'],1,0,'C');
		        $pdf->Cell(50,5,$completeResultArray[$ctr]['servname'],1,0,'C');
		        $pdf->Cell(50,5,$completeResultArray[$ctr]['stat'],1,0,'C');
		        $pdf->Cell(50,5,number_format($completeResultArray[$ctr]['Amount'], 2 , '.' , ','),1,1,'R');
		}
	}
	else
	{
		$pdf->SetX(30);
            	$pdf->Cell(300,5,"No Record Found",1,1,'C');
	}
	
        $pdf->SetX(30);
        $pdf->Cell(250,5,"Total",1,0,'L');
        $pdf->Cell(50,5,number_format($overall_total, 2 , '.' , ','),1,1,'R');
        Footer(&$pdf);
        $pdf->Output('Site Transaction for ' . $srRptDate,'D');
        Header('Content-Type: application/pdf');
        
        $conn->close();
    }
    else if($rptType == "sr")
    {
	$pdf=new FPDF('L','mm','Legal');
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"Site Remittance",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,'Report for Site Remittance as of ' . date("Y-m-d H:i:s"),0,1,'C');

        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(10);
        $pdf->Cell(35,5,'Site',1,0,'C');
        $pdf->SetX(45);
        $pdf->Cell(60,5,'Bank',1,0,'C');
        $pdf->SetX(105);
        $pdf->Cell(40,5,'Branch',1,0,'C');
        $pdf->SetX(145);
        $pdf->Cell(40,5,'Bank  Transaction ID',1,0,'C');
        $pdf->SetX(185);
        $pdf->Cell(35,5,'Deposit Date',1,0,'C');
        $pdf->SetX(220);
        $pdf->Cell(40,5,'Cheque Number',1,0,'C');
        $pdf->SetX(260);
        $pdf->Cell(50,5,'Transaction Date',1,0,'C');
        $pdf->SetX(310);
        $pdf->Cell(40,5,'Amount',1,1,'R');
        $pdf->SetFont('Arial','',10);
	function Footer(&$pdf)
	{
	    // Go to 1.5 cm from bottom
	    $pdf->SetY(-31);
	    // Select Arial italic 8
	    $pdf->SetFont('Arial','I',8);
	    // Print centered page number
	    $pdf->Cell(0,10,'Generated On ' . date('F d,Y h:i:s A').'.',0,0,'C');
		//$pdf->Cell(35,5,'dasdasdasdsa',1,0,'C');
	}

	$all_sites_array = array();
	$completeResultArray = array();
	$completeExcelValuesArray = array();
	$srCompleteResult = array();
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

	if(count($completeResultArray) > 0)
	{
	    for($ctr = 0 ; $ctr < count($completeResultArray) ; $ctr++)
	    {
		$pdf->SetX(10);
                $pdf->Cell(35,5,$completeResultArray[$ctr]['siteName'],1,0,'C');
                $pdf->Cell(60,5,$completeResultArray[$ctr]['BankName'],1,0,'C');
                $pdf->Cell(40,5,$completeResultArray[$ctr]['Branch'],1,0,'C');
                $pdf->Cell(40,5,$completeResultArray[$ctr]['BankTransactionID'],1,0,'C');
                $pdf->Cell(35,5,$completeResultArray[$ctr]['BankTransactionDate'],1,0,'C');
                $pdf->Cell(40,5,$completeResultArray[$ctr]['ChequeNumber'],1,0,'C');
                $pdf->Cell(50,5,$completeResultArray[$ctr]['DateCreated'],1,0,'C');
                $pdf->Cell(40,5,number_format($completeResultArray[$ctr]['Amount'], 2 , '.' , ','),1,1,'R');
	    }
	    $pdf->SetX(10);
            $pdf->Cell(300,5,'Total',1,0,'C');
            $pdf->Cell(40,5,number_format($sr_total, 2 , '.' , ','),1,1,'R');
	}
	else
        {
            $pdf->SetX(10);
            $pdf->Cell(340,5,'No Record Found',1,1,'C');
        }
        Footer(&$pdf);
        $pdf->Output('Site Transaction for ' . date("Y-m-d h:i:s A"),'D');
        Header('Content-Type: application/pdf');
        $conn->close();
    }
    else if($rptType == "gh")
    {
        $total_deposit = 0;
        $total_reload = 0;
        $total_withdraw = 0;
        $total_grosshold = 0;
        $date = $_GET['strdate'];
        $enddate = $_GET['enddate'];
        $strdate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($enddate)));

        $coverage = "Coverage: $date $cutoff_time to $strdate $cutoff_time";

        $site = $conn->GetSiteID();
        $conn->prepare($site);
        $conn->bindParam(1, $_SESSION['accID']);
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
                array_push($allGrossHoldArray,$gh);
            }
        }
        $totals_array = array();
        $totals = array(
                'TotalDep' => $total_deposit,
                'TotalRel' => $total_reload,
                'TotalWdw' => $total_withdraw,
                'TotalGh' => $total_grosshold
        );
        array_push($totals_array,$totals);
        $pdf=new FPDF();
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"Gross Hold Report",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,$coverage,0,1,'C');

        $pdf->SetFont('Arial','B',8);
        $pdf->SetX(5);
        $pdf->Cell(40,5,'Terminal',1,0,'C');
        $pdf->SetX(45);
        $pdf->Cell(40,5,'Deposit',1,0,'R');
        $pdf->SetX(85);
        $pdf->Cell(40,5,'Reload',1,0,'R');
        $pdf->SetX(125);
        $pdf->Cell(40,5,'Withdrawal',1,0,'R');
        $pdf->SetX(165);
        $pdf->Cell(40,5,'Gross Hold',1,1,'R');
        $pdf->SetFont('Arial','',8);
	function Footer(&$pdf)
	{
	    // Go to 1.5 cm from bottom
	    $pdf->SetY(-31);
	    // Select Arial italic 8
	    $pdf->SetFont('Arial','I',8);
	    // Print centered page number
	    $pdf->Cell(0,10,'Generated On ' . date('F d,Y h:i:s A').'.',0,0,'C');
		//$pdf->Cell(35,5,'dasdasdasdsa',1,0,'C');
	}

        if(count($allGrossHoldArray) > 0)
        {
            for($ctr = 0 ; $ctr < count($allGrossHoldArray) ; $ctr++)
            {
                $pdf->SetX(5);
                $pdf->Cell(40,5,$allGrossHoldArray[$ctr]['TerminalName'],1,0,'C');
                $pdf->Cell(40,5,number_format ( $allGrossHoldArray[$ctr]['Deposit'] , 2 , '.' , ',' ),1,0,'R');
                $pdf->Cell(40,5,number_format ( $allGrossHoldArray[$ctr]['Reload'] , 2 , '.' , ',' ),1,0,'R');
                $pdf->Cell(40,5,number_format ( $allGrossHoldArray[$ctr]['Withdraw'] , 2 , '.' , ',' ),1,0,'R');
                $pdf->Cell(40,5,number_format ( $allGrossHoldArray[$ctr]['GrossHold'] , 2 , '.' , ',' ),1,1,'R');
            }
            for($ctr2 = 0 ; $ctr2 < count($totals_array) ; $ctr2++)
            {
                $pdf->SetX(5);
                $pdf->Cell(40,5,'Total',1,0,'C');
                $pdf->Cell(40,5,number_format ( $totals_array[$ctr2]['TotalDep'] , 2 , '.' , ',' ),1,0,'R');
                $pdf->Cell(40,5,number_format ( $totals_array[$ctr2]['TotalRel'] , 2 , '.' , ',' ),1,0,'R');
                $pdf->Cell(40,5,number_format ( $totals_array[$ctr2]['TotalWdw'] , 2 , '.' , ',' ),1,0,'R');
                $pdf->Cell(40,5,number_format ( $totals_array[$ctr2]['TotalGh'] , 2 , '.' , ',' ),1,1,'R');
            }
        }
        else
        {
            $pdf->SetX(5);
            $pdf->Cell(200,5,'No Record Found',1,0,'C');
        }
        Footer(&$pdf);
        $pdf->Output('Gross Hold Report for ' . $date . " to " . $enddate,'D');;
        Header('Content-Type: application/pdf');
        $conn->close();
    }
    else if($rptType == "sl")                                                   /*Converts Site Listing report to PDF*/
    {
        //echo "Arlene <br/> Salazar";
        $slSiteId = $_GET['slsiteid'];
        /*Site & Site Details*/
        if( $slSiteId == 0 )
        {
            $siteDtls = $conn->GetAllSiteDetails();
            $conn->executeQuery($siteDtls);
        }
        else
        {
            $siteDtls = $conn->GetSiteDetails();
            $conn->prepare($siteDtls);
            $conn->bindParam(1, $slSiteId);
            $conn->execute();
        }
        $pdf=new FPDF('L','mm','Legal');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"Site Listing Report",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,'Report for Site Listing',0,1,'C');

        $pdf->SetFont('Arial','B',8);
        function SiteHeader(&$pdf)
        {
                $pdf->SetX(10);
                $pdf->Cell(30,5,'Site Name',1,0,'C');
                $pdf->SetX(40);
                $pdf->Cell(30,5,'Status',1,0,'C');
                $pdf->SetX(70);
                $pdf->Cell(60,5,'Description',1,0,'C');
                $pdf->SetX(130);
                $pdf->Cell(30,5,'Alias',1,0,'C');
                $pdf->SetX(160);
                $pdf->Cell(30,5,'Island',1,0,'C');
                $pdf->SetX(190);
                $pdf->Cell(30,5,'Region',1,0,'C');
                $pdf->SetX(220);
                $pdf->Cell(30,5,'Province',1,0,'C');
                $pdf->SetX(250);
                $pdf->Cell(30,5,'City',1,0,'C');
                $pdf->SetX(280);
                $pdf->Cell(30,5,'Barangay',1,0,'C');
                $pdf->SetX(310);
                $pdf->Cell(30,5,'Address',1,1,'C');
        }
        function TerminalHeader(&$pdf)
        {
                $pdf->SetX(10);
                $pdf->Cell(115,5,'Terminal Name',1,0,'C');
                $pdf->SetX(125);
                $pdf->Cell(110,5,'Terminal Code',1,0,'C');
                $pdf->SetX(235);
                $pdf->Cell(110,5,'Status',1,1,'C');
        }
        function AccountHeader(&$pdf)
        {
                $pdf->SetX(10);
                $pdf->Cell(40,5,'Account Type',1,0,'C');
		$pdf->SetX(50);
                $pdf->Cell(40,5,'Username',1,0,'C');
                $pdf->SetX(90);
                $pdf->Cell(105,5,'Address',1,0,'C');
                $pdf->SetX(195);
                $pdf->Cell(65,5,'Email',1,0,'C');
                $pdf->SetX(260);
                $pdf->Cell(40,5,'Landline',1,0,'C');
                $pdf->SetX(300);
                $pdf->Cell(45,5,'MobileNumber',1,1,'C');
        }
	function Footer(&$pdf)
	{
	    // Go to 1.5 cm from bottom
	    $pdf->SetY(-31);
	    // Select Arial italic 8
	    $pdf->SetFont('Arial','I',8);
	    // Print centered page number
	    $pdf->Cell(0,10,'Generated On ' . date('F d,Y h:i:s A').'.',0,0,'C');
		//$pdf->Cell(35,5,'dasdasdasdsa',1,0,'C');
	}
        $pdf->SetX(10);
        $pdf->Cell(330,5,"Site Details",1,1,'C');
        SiteHeader(&$pdf);
        if( $conn->rowCount() > 0 )
        {
            $counter = 1;
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
		if($counter < 10)
		{
			$current_y = $pdf->GetY();
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->SiteName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			#Status : 0 - Pending; 1 - Active; 2 - Suspended; 3 - Deactivated;
		        if($row->Status == 0)
		        {
		            $pdf->Multicell($cell_width,11,'Pending',1,'j',false);
		        }
		        else if($row->Status == 1)
		        {
		            $pdf->Multicell($cell_width,11,'Active',1,'j',false);
		        }
		        else if($row->Status == 2)
		        {
		             $pdf->Multicell($cell_width,11,'Suspended',1,'j',false);
		        }
		        else
		        {
		            $pdf->Multicell($cell_width,11,'Deactivated',1,'j',false);
		        }
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 60;
			$pdf->Multicell($cell_width,11,$row->SiteDescription,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->SiteAlias,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->IslandName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			if(strlen($row->RegionName) > 40)
				$cell_height = 3.7;
			if(strlen($row->RegionName) > 35)
				$cell_height = 3.6;
			else if(strlen($row->RegionName) > 20)
				$cell_height = 5.5;
			else if(strlen($row->RegionName) > 10)
				$cell_height = 7;
			else
				$cell_height = 9;
			$pdf->Multicell($cell_width,$cell_height,$row->RegionName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->ProvinceName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			if(strlen($row->CityName) >= 20)
				$cell_height = 5.5;
			else if(strlen($row->SiteAddress) >= 10)
				$cell_height = 10.8;
			else
				$cell_height = 11;
			$pdf->Multicell($cell_width,$cell_height,$row->CityName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->BarangayName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$site_address = "";
			$arr = explode(' ',$row->SiteAddress);
		        for($i = 0 ; $i < count($arr) ; $i++)
		        {
		            $site_address .= $arr[$i] . "\n";
		        }
			$current_x = $pdf->GetX();
			$cell_width = 30;
			if(strlen($row->SiteAddress) > 20)
				$cell_height = 5.5;
			else if(strlen($row->SiteAddress) > 10)
				$cell_height = 10.8;
			else
				$cell_height = 11;
			$pdf->Multicell($cell_width,$cell_height,$row->SiteAddress,1,'j',false);
			$pdf->Ln(2);
		}
		else
		{
			$counter = 0;
			$pdf->AddPage();
			$current_y = $pdf->GetY();
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->SiteName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			#Status : 0 - Pending; 1 - Active; 2 - Suspended; 3 - Deactivated;
		        if($row->Status == 0)
		        {
		            $pdf->Multicell($cell_width,11,'Pending',1,'j',false);
		        }
		        else if($row->Status == 1)
		        {
		            $pdf->Multicell($cell_width,11,'Active',1,'j',false);
		        }
		        else if($row->Status == 2)
		        {
		             $pdf->Multicell($cell_width,11,'Suspended',1,'j',false);
		        }
		        else
		        {
		            $pdf->Multicell($cell_width,11,'Deactivated',1,'j',false);
		        }
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 60;
			$pdf->Multicell($cell_width,11,$row->SiteDescription,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->SiteAlias,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->IslandName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			if(strlen('ARMM (Autonomous Region in Muslim Mindanao)') > 40)
				$cell_height = 3.7;
			else if(strlen($row->RegionName) > 35)
				$cell_height = 3.6;
			else if(strlen($row->RegionName) > 20)
				$cell_height = 5.5;
			else if(strlen($row->RegionName) > 10)
				$cell_height = 7;
			else
				$cell_height = 9;
			$pdf->Multicell($cell_width,$cell_height,'ARMM (Autonomous Region in Muslim Mindanao)',1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->ProvinceName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			if(strlen($row->CityName) >= 20)
				$cell_height = 5.5;
			else if(strlen($row->SiteAddress) >= 10)
				$cell_height = 10.8;
			else
				$cell_height = 11;
			$pdf->Multicell($cell_width,$cell_height,$row->CityName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$current_x = $pdf->GetX();
			$cell_width = 30;
			$pdf->Multicell($cell_width,11,$row->BarangayName,1,'j',false);
			$pdf->SetXY($current_x + $cell_width, $current_y);
			$site_address = "";
			$arr = explode(' ',$row->SiteAddress);
		        for($i = 0 ; $i < count($arr) ; $i++)
		        {
		            $site_address .= $arr[$i];
		        }
			$current_x = $pdf->GetX();
			$cell_width = 30;
			//echo strlen($row->SiteAddress) . "<br/>";
			if(strlen($row->SiteAddress) >= 20)
				$cell_height = 5.5;
			else if(strlen($row->SiteAddress) >= 10)
				$cell_height = 10.8;
			else
				$cell_height = 11;
			$pdf->Multicell($cell_width,$cell_height,$row->SiteAddress,1,'j',false);
			$pdf->Ln(2);
		}
		$counter ++;	
	     }
        }
        else
        {
                $pdf->SetX(10);
                $pdf->Cell(330,5,"No Record Found",1,1,'C');
        }
        $pdf->Ln(10);
        /*Site & Terminals*/
        if( $slSiteId == 0 )
        {
            $siteTrml = $conn->GetAllSiteTerminals();
            $conn->executeQuery($siteTrml);
        }
        else
        {
            $siteTrml = $conn->GetSiteTerminals();
            $conn->prepare($siteTrml);
            $conn->bindParam(1, $slSiteId);
            $conn->execute();
        }

        $pdf->SetX(10);
        $pdf->Cell(335,5,"Terminals",1,1,'C');
        TerminalHeader(&$pdf);
        if( $conn->rowCount() > 0 )
        {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                        $pdf->SetX(10);
                        $pdf->Cell(115,5,$row->TerminalName,1,0,'C');
                        $pdf->Cell(110,5,$row->TerminalCode,1,0,'C');
                        //Status : 0 - Pending; 1 - Active; 2 - Disabled; 3 - Terminated'
                        if($row->Status == 0)
                        {
                                $pdf->Cell(110,5,'Pending',1,1,'C');
                        }
                        else if($row->Status == 1)
                        {
                                $pdf->Cell(110,5,'Active',1,1,'C');
                        }
                        else if($row->Status == 2)
                        {
                                $pdf->Cell(110,5,'Disabled',1,1,'C');
                        }
                        else
                        {
                                $pdf->Cell(110,5,'Terminated',1,1,'C');
                        }
                }
        }
        else
        {
                $pdf->SetX(10);
                $pdf->Cell(330,5,"No Record Found",1,1,'C');
        }
        $pdf->Ln(10);

        $pdf->SetX(10);
        $pdf->Cell(335,5,"Accounts",1,1,'C');
        AccountHeader(&$pdf);
        /*Site and Accounts*/
        if( $slSiteId == 0 )
        {
            $siteAcct = $conn->GetAllSiteAccounts();
            $conn->executeQuery($siteAcct);
        }
        else
        {
            $siteAcct = $conn->GetSiteAccounts();
            $conn->prepare($siteAcct);
            $conn->bindParam(1, $slSiteId);
            $conn->execute();
        }
        if($conn->rowCount() > 0)
        {
                while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
                {
                        $pdf->SetX(10);
                        $pdf->Cell(40,5,$row->name,1,0,'C');
			$pdf->Cell(40,5,$row->Username,1,0,'C');
                        $pdf->Cell(105,5,$row->Address,1,0,'C');
                        $pdf->Cell(65,5,$row->Email,1,0,'C');
                        $pdf->Cell(40,5,$row->Landline,1,0,'C');
                        $pdf->Cell(45,5,$row->MobileNumber,1,1,'C');
                }
        }
        else
        {
                $pdf->SetX(10);
                $pdf->Cell(335,5,"No Record Found",1,1,'C');
        }
	Footer(&$pdf);
        $pdf->Output('Site Listing','D');
        Header('Content-Type: application/pdf');
        $conn->close();
    }
    else
    {
        $date = $_GET['strdate'];
        $enddate = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($date)));
        $excelReportArray = array();
        $pdf=new FPDF('L','mm','Legal');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,5,"Transaction History Report",0,0,'C');
        $pdf->Ln(4);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(10,15);
        $pdf->Cell(0,5,'Report for Transaction History',0,1,'C');

        $pdf->SetFont('Arial','B',8);
        $pdf->SetX(10);
        $pdf->Cell(40,5,'Login',1,0,'C');
        $pdf->SetX(50);
        $pdf->Cell(60,5,'Time In',1,0,'C');
        $pdf->SetX(110);
        $pdf->Cell(60,5,'Time Out',1,0,'C');
        $pdf->SetX(170);
        $pdf->Cell(50,5,'Deposit',1,0,'C');
        $pdf->SetX(220);
        $pdf->Cell(40,5,'Reloads',1,0,'C');
        $pdf->SetX(260);
        $pdf->Cell(40,5,'Redemption',1,0,'C');
        $pdf->SetX(300);
        $pdf->Cell(40,5,'Grosshold',1,1,'C');
        $pdf->SetFont('Arial','',8);
	/*function Footer(&$pdf)
	{
	    // Go to 1.5 cm from bottom
	    $pdf->SetY(-50);
	    // Select Arial italic 8
	    $pdf->SetFont('Arial','I',8);
	    // Print centered page number
	    $pdf->Cell(0,10,'Generated On ' . date('F d,Y h:i:s A').'.',0,0,'C');
		//$pdf->Cell(35,5,'dasdasdasdsa',1,0,'C');
	}*/
        $ghTotalComputation = 0;
        $csh  = $conn->GetTransSummary();
        $conn->prepare($csh);
        $conn->bindParam(1, $date . " " . $cutoff_time);
        $conn->bindParam(2, $enddate . " " . $cutoff_time);
        $conn->execute();
        $totalrow = $conn->rowCount();
        if( $conn->rowCount() > 0 )
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
                $gh = (($row->Deposit + $row->Reload) - $row->Withdrawal);
                $pdf->SetX(10);
                $pdf->Cell(40,5,$row->TerminalName,1,0,'C');
                $pdf->Cell(60,5,$row->DateStarted,1,0,'C');
                $pdf->Cell(60,5,$dateEnded,1,0,'C');
                $pdf->Cell(50,5,number_format($row->Deposit, 2 , '.' , ','),1,0,'C');
                $pdf->Cell(40,5,number_format($row->Reload, 2 , '.' , ','),1,0,'C');
                $pdf->Cell(40,5,number_format($row->Withdrawal, 2 , '.' , ','),1,0,'C');
                $pdf->Cell(40,5,number_format($gh, 2 , '.' , ','),1,1,'C');
                $ghTotalComputation = $ghTotalComputation + $gh;
            }
        }
        else
        {
            $pdf->SetX(10);
            $pdf->Cell(330,5,"No Record Found",1,1,'C');
        }

        $totals = $conn->SumDepositReloadWithdraw();
        $conn->prepare($totals);
        $conn->bindParam(1, $date . " " . $cutoff_time);
        $conn->bindParam(2, $enddate . " " . $cutoff_time);
        $conn->execute();

        if( $conn->rowCount() > 0 )
        {
            while($row = $conn->fetchPerType(PDO::FETCH_OBJ))
            {
                $pdf->SetX(10);
                $pdf->Cell(160,5,'Total',1,0,'C');
                $pdf->Cell(50,5,number_format($row->totalDeposit, 2 , '.' , ','),1,0,'C');
                $pdf->Cell(40,5,number_format($row->totalReload, 2 , '.' , ','),1,0,'C');
                $pdf->Cell(40,5,number_format($row->totalWithdrawal, 2 , '.' , ','),1,0,'C');
                $pdf->Cell(40,5,number_format((($row->totalDeposit + $row->totalReload) - $row->totalWithdrawal), 2 , '.' , ','),1,1,'C');
            }
        }
        //Footer(&$pdf);
        $pdf->Output('Transaction History','D');
        Header('Content-Type: application/pdf');
        $conn->close();
    }
}
else
{
    echo "error";
}
?>
