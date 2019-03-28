<?php

include __DIR__ . "/../sys/class/CSManagement.class.php";

require __DIR__ . '/../sys/core/init.php';
require_once __DIR__ . '/../sys/class/CTCPDF.php';
require_once __DIR__ . "/../sys/class/class.export_excel.php";

$aid = 0;
if (isset($_SESSION['sessionID'])) {
    $new_sessionid = $_SESSION['sessionID'];
} else {
    $new_sessionid = '';
}
if (isset($_SESSION['accID'])) {
    $aid = $_SESSION['accID'];
}


$mswrpt = new CSManagement($_DBConnectionString[0]);
$mswrpt2 = new CSManagement($_DBConnectionString[5]);
$mswrpt3 = new CSManagement($_DBConnectionString[6]);

$connected = $mswrpt->open();
$connected2 = $mswrpt2->open();
$connected3 = $mswrpt3->open();

$nopage = 0;
if ($connected) {
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $mswrpt->getDate();
    /*     * **************** Session Checking ******************* */
    $isexist = $mswrpt->checksession($aid);
    if ($isexist == 0) {
        session_destroy();
        $msg = "Not Connected";
        $mswrpt->close();
        if ($mswrpt->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }
    $isexistsession = $mswrpt->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) {
        session_destroy();
        $msg = "Not Connected";
        $mswrpt->close();
        header("Location: login.php?mess=" . $msg);
    }
    /*     * *************** End Session Checking ***************** */

    //checks if account was locked 
    $islocked = $mswrpt->chkLoginAttempts($aid);
    if (isset($islocked['LoginAttempts'])) {
        $loginattempts = $islocked['LoginAttempts'];
        if ($loginattempts >= 3) {
            $mswrpt->deletesession($aid);
            session_destroy();
            $msg = "Not Connected";
            $mswrpt->close();
            header("Location: login.php?mess=" . $msg);
            exit;
        }
    }
    //for JQGRID pagination
    if (isset($_POST['paginate'])) {
        $vpage = $_POST['paginate'];
        switch ($vpage) {
            //display Bet Reference Details
            case "GetBetReferenceDetails":

                $betrefid = strtoupper(trim($_POST['txtbetrefid']));
                $serviceids = '';
                //validate if card number field was empty
                if (strlen($betrefid) > 0) {
                    $chkBetRedID = $mswrpt3->chkbetrefid($betrefid);
                    $MIDdetails = $mswrpt2->getCardNumberUsingMID($chkBetRedID['MID']);

                    $arr = array(
                        'MID' => $chkBetRedID['MID'],
                        'ServiceUsername' => $MIDdetails['ServiceUsername'],
                        'CardNumber' => $MIDdetails['CardNumber'],
                    );

                    echo json_encode($arr);
                }
                $mswrpt2->close();
                $mswrpt3->close();
                exit;
                break;
            case "GetBetPayoutResetlement":
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                $betrefid = strtoupper(trim($_POST['txtbetrefid']));

                //validate if card number field was empty
                if (strlen($betrefid) > 0) {
                    $chkBetRedID = $mswrpt3->countbetsdetails($betrefid);

                    $count = 0;
                    $count = $chkBetRedID[0]['count'];
                    //this is for computing the limit
                    if ($count > 0) {
                        $total_pages = ceil($count / $limit);
                    } else {
                        $total_pages = 0;
                    }

                    if ($page > $total_pages) {
                        $page = $total_pages;
                        $start = $limit * $page - $limit;
                    }

                    if ($page == 0) {
                        $start = 0;
                    } else {
                        $start = $limit * $page - $limit;
                    }

                    $limit = (int) $limit;

                    //this is for proper rendering of results, if count is 0 $result is also must be 0
                    if ($count > 0) {
                        $result = $mswrpt3->getbetsdetails($betrefid, $start, $limit);
                    } else {
                        $result = 0;
                    }

                    if ($result > 0) {
                        $i = 0;
                        $response = new stdClass();
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        //display to jqgrid
                        foreach ($result as $vview) {

                            //TransactionType
                            switch ($vview['TransTypeID']) {
                                case '1':
                                    $vview['TransTypeID'] = 'Bet';
                                    $vview['LastTransUpdate'] = '--';
                                    break;
                                case '2':
                                    $vview['TransTypeID'] = 'Payout';
                                    $vview['LastTransUpdate'] = '--';
                                    break;
                                case '4':
                                    $vview['TransTypeID'] = 'ResettlePayout';
                                    break;
                            }
                            //ResettlementType
                            switch ($vview['ResettleType']) {
                                case '1':
                                    $vview['ResettleType'] = 'Deposit';
                                    break;
                                case '2':
                                    $vview['ResettleType'] = 'Withdraw';
                                    break;
                                default :
                                    $vview['ResettleType'] = 'N/A';
                                    break;
                            }
                            //Status
                            switch ($vview['Status']) {
                                case '0':
                                    $vview['Status'] = 'Pending';
                                    break;
                                case '1':
                                    $vview['Status'] = 'Validation of Bet';
                                    break;
                                case '2':
                                    $vview['Status'] = 'Successful';
                                    break;
                                case '3':
                                    $vview['Status'] = 'Failed RTG';
                                    break;
                                case '4':
                                    $vview['Status'] = 'Failed : Bet Does Not Exists in MSW';
                                    break;
                                case '5':
                                    $vview['Status'] = 'Successful RTG';
                                    break;
                                case '6':
                                    $vview['Status'] = 'Fulfillment Approved';
                                    break;
                                case '7':
                                    $vview['Status'] = 'Fulfillment Denied';
                                    break;
                                case '8':
                                    $vview['Status'] = 'Error Validating Bet';
                                    break;
                            }
                            $response->rows[$i]['id'] = $vview['WagerTransDetailsID'];
                            $response->rows[$i]['cell'] = array($vview['BetSlipID'], number_format($vview['Amount'], 2), $vview['TransactionNo'], $vview['TransDate'],
                                $vview['LastTransUpdate'], $vview['Status'], $vview['Option1'], $vview['TransTypeID'], $vview['ResettleType']
                            );
                            $i++;
                        }
                    } else {
                        $i = 0;
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        $msg = "SIte Listing: No returned result";
                        $response->msg = $msg;
                    }
                    echo json_encode($response);
                }
                $mswrpt3->close();
                break;
            case "GetRecreditDetails":
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                $betrefid = strtoupper(trim($_POST['txtbetrefid']));

                //validate if card number field was empty
                if (strlen($betrefid) > 0) {
                    $chkBetRedID = $mswrpt3->countrecreditdetails($betrefid);

                    $count = 0;
                    $count = $chkBetRedID[0]['count'];
                    //this is for computing the limit
                    if ($count > 0) {
                        $total_pages = ceil($count / $limit);
                    } else {
                        $total_pages = 0;
                    }

                    if ($page > $total_pages) {
                        $page = $total_pages;
                        $start = $limit * $page - $limit;
                    }

                    if ($page == 0) {
                        $start = 0;
                    } else {
                        $start = $limit * $page - $limit;
                    }

                    $limit = (int) $limit;

                    //this is for proper rendering of results, if count is 0 $result is also must be 0
                    if ($count > 0) {
                        $result = $mswrpt3->getrecreditdetails($betrefid, $start, $limit);
                    } else {
                        $result = 0;
                    }

                    if ($result > 0) {
                        $i = 0;
                        $response = new stdClass();
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        //display to jqgrid
                        foreach ($result as $vview) {

                            //Status
                            switch ($vview['Status']) {
                                case '0':
                                    $vview['Status'] = 'Pending';
                                    break;
                                case '1':
                                    $vview['Status'] = 'Successful';
                                    break;
                                case '2':
                                    $vview['Status'] = 'Failed';
                                    break;
                                case '3':
                                    $vview['Status'] = 'Fulfilled Approved';
                                    break;
                                case '4':
                                    $vview['Status'] = 'Fulfilled Denied';
                                    break;
                            }
                            $response->rows[$i]['id'] = $vview['RecreditTransDetailsID'];
                            $response->rows[$i]['cell'] = array($vview['BetSlipID'], number_format($vview['Amount'], 2), $vview['TransactionNo'], $vview['TransDate'],
                                $vview['LastTransUpdate'], $vview['Status'], $vview['Option1']
                            );
                            $i++;
                        }
                    } else {
                        $i = 0;
                        $response->page = $page;
                        $response->total = $total_pages;
                        $response->records = $count;
                        $msg = "SIte Listing: No returned result";
                        $response->msg = $msg;
                    }
                    echo json_encode($response);
                }
                $mswrpt3->close();
                break;
        }
    } elseif (isset($_GET['excel']) && $_GET['excel'] == "BetMSWTransactions") {

        $betrefid = $_GET['BetRefID'];
        $fn = "BetPayoutResettlementMSW_" . strtoupper(trim(strtoupper(trim($betrefid)))) . ".xls"; //this will be the filename of the excel file
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
        //setting the values of the headers and data of the excel file
        //and these values comes from the other file which file shows the data
        $rheaders = array('BetSlip ID', 'Amount', 'Transaction No', 'Transaction Date', 'Date Last Updated', 'Status', 'Tracking ID', 'Transaction Type', 'Resettlement Type');
        $completeexcelvalues = array();
        $direction = $_GET['sord'];
        $result = $mswrpt3->getbetsdetails(strtoupper(trim($betrefid)),null,null);
        if (count($result) > 0) {
            foreach ($result as $vview) {
                //TransactionType
                switch ($vview['TransTypeID']) {
                    case '1':
                        $vview['TransTypeID'] = 'Bet';
                        $vview['LastTransUpdate'] = '--';
                        break;
                    case '2':
                        $vview['TransTypeID'] = 'Payout';
                        $vview['LastTransUpdate'] = '--';
                        break;
                    case '4':
                        $vview['TransTypeID'] = 'ResettlePayout';
                        break;
                }
                //ResettlementType
                switch ($vview['ResettleType']) {
                    case '1':
                        $vview['ResettleType'] = 'Deposit';
                        break;
                    case '2':
                        $vview['ResettleType'] = 'Withdraw';
                        break;
                    default :
                        $vview['ResettleType'] = 'N/A';
                        break;
                }
                //Status
                switch ($vview['Status']) {
                    case '0':
                        $vview['Status'] = 'Pending';
                        break;
                    case '1':
                        $vview['Status'] = 'Validation of Bet';
                        break;
                    case '2':
                        $vview['Status'] = 'Successful';
                        break;
                    case '3':
                        $vview['Status'] = 'Failed RTG';
                        break;
                    case '4':
                        $vview['Status'] = 'Failed : Bet Does Not Exists in MSW';
                        break;
                    case '5':
                        $vview['Status'] = 'Successful RTG';
                        break;
                    case '6':
                        $vview['Status'] = 'Fulfillment Approved';
                        break;
                    case '7':
                        $vview['Status'] = 'Fulfillment Denied';
                        break;
                    case '8':
                        $vview['Status'] = 'Error Validating Bet';
                        break;
                }
                $rtransdetails = $vview['TransactionNo'];
                $excelvalues = array(
                    0 => $vview['BetSlipID'],
                    1 => number_format((float) $vview['Amount'], 2, '.', ''),
                    2 => $vview['TransactionNo'],
                    3 => $vview['TransDate'],
                    4 => $vview['LastTransUpdate'],
                    5 => $vview['Status'],
                    6 => $vview['Option1'],
                    7 => $vview['TransTypeID'],
                    8 => $vview['ResettleType']
                );
                array_push($completeexcelvalues, $excelvalues);
            }
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Export to Excel";
        $mswrpt->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
    } elseif (isset($_GET['pdf']) && $_GET['pdf'] == 'BetMSWTransactions') {

        $completePDFArray = array();

        $betrefid = $_GET['BetRefID'];
        $queries = $mswrpt3->getbetsdetails(strtoupper(trim($betrefid)),null,null);

        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('MSW Bets, Payouts & Resettlement Transactions');
        $pdf->html.='<div style="text-align:center;margin-top : -20px;">Bet Reference ID : ' . strtoupper(trim($betrefid)) . '</div>';
        $pdf->html.='<br><br>';
        $pdf->SetFontSize(6);
        $pdf->c_tableHeader2(array(
            array('value' => 'BetSlip ID', 'width' => '60px'),
            array('value' => 'Amount', 'width' => '60px'),
            array('value' => 'Transaction No'),
            array('value' => 'Transaction Date', 'width' => '100px'),
            array('value' => 'Date Last Updated', 'width' => '100px'),
            array('value' => 'Status', 'width' => '100px'),
            array('value' => 'Tracking ID'),
            array('value' => 'Transaction Type'),
            array('value' => 'Resettlement Type')
        ));
        if (count($queries) > 0) {
            foreach ($queries as $row) {
                //TransactionType
                switch ($row['TransTypeID']) {
                    case '1':
                        $row['TransTypeID'] = 'Bet';
                        $row['LastTransUpdate'] = '--';
                        break;
                    case '2':
                        $row['TransTypeID'] = 'Payout';
                        $row['LastTransUpdate'] = '--';
                        break;
                    case '4':
                        $row['TransTypeID'] = 'ResettlePayout';
                        break;
                }
                //ResettlementType
                switch ($row['ResettleType']) {
                    case '1':
                        $row['ResettleType'] = 'Deposit';
                        break;
                    case '2':
                        $row['ResettleType'] = 'Withdraw';
                        break;
                    default :
                        $row['ResettleType'] = 'N/A';
                        break;
                }
                //Status
                switch ($row['Status']) {
                    case '0':
                        $vview['Status'] = 'Pending';
                        break;
                    case '1':
                        $row['Status'] = 'Validation of Bet';
                        break;
                    case '2':
                        $row['Status'] = 'Successful';
                        break;
                    case '3':
                        $row['Status'] = 'Failed RTG';
                        break;
                    case '4':
                        $row['Status'] = 'Failed : Bet Does Not Exists in MSW';
                        break;
                    case '5':
                        $row['Status'] = 'Successful RTG';
                        break;
                    case '6':
                        $row['Status'] = 'Fulfillment Approved';
                        break;
                    case '7':
                        $row['Status'] = 'Fulfillment Denied';
                        break;
                    case '8':
                        $row['Status'] = 'Error Validating Bet';
                        break;
                }
                $rtransdetails = $row['TransactionNo'];
                $pdf->c_tableRow2(array(
                    array('value' => $row['BetSlipID'], 'align' => 'center', 'width' => '60px'),
                    array('value' => number_format($row['Amount'], 2, '.', ','), 'align' => 'right', 'width' => '60px'),
                    array('value' => $row['TransactionNo'], 'align' => 'center'),
                    array('value' => $row['TransDate'], 'align' => 'center', 'width' => '100px'),
                    array('value' => $row['LastTransUpdate'], 'align' => 'center', 'width' => '100px'),
                    array('value' => $row['Status'], 'align' => 'center', 'width' => '100px'),
                    array('value' => $row['Option1'], 'align' => 'center'),
                    array('value' => $row['TransTypeID'], 'align' => 'center'),
                    array('value' => $row['ResettleType'], 'align' => 'center')
                ));
            }
        } else {
            $pdf->html.='<div style="text-align:center;">No Results Found</div>';
        }

        $pdf->c_tableEnd();
        $vauditfuncID = 40; //export to pdf
        $vtransdetails = "Export to PDF";
        $mswrpt->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $pdf->c_generatePDF("BetPayoutResettlementMSW_" . strtoupper(trim($betrefid)) . '.pdf');
    } elseif (isset($_GET['excel']) && $_GET['excel'] == "RecreditMSWTransactions") {

        $betrefid = $_GET['BetRefID'];
        $fn = "RecreditMSW_" . strtoupper(trim($betrefid)) . ".xls"; //this will be the filename of the excel file
        //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
        //setting the values of the headers and data of the excel file
        //and these values comes from the other file which file shows the data
        $rheaders = array('BetSlip ID', 'Amount', 'Transaction No', 'Transaction Date', 'Date Last Updated', 'Status', 'Tracking ID');
        $completeexcelvalues = array();
        $direction = $_GET['sord'];
        $result = $mswrpt3->getrecreditdetails(strtoupper(trim($betrefid)),null,null);


        if (count($result) > 0) {
            foreach ($result as $vview) {
                //Status
                switch ($vview['Status']) {
                    case '0':
                        $vview['Status'] = 'Pending';
                        break;
                    case '1':
                        $vview['Status'] = 'Successful';
                        break;
                    case '2':
                        $vview['Status'] = 'Failed';
                        break;
                    case '3':
                        $vview['Status'] = 'Fulfilled Approved';
                        break;
                    case '4':
                        $vview['Status'] = 'Fulfilled Denied';
                        break;
                }
                $rtransdetails = $vview['TransactionNo'];
                $excelvalues = array(
                    0 => $vview['BetSlipID'],
                    1 => number_format((float) $vview['Amount'], 2, '.', ','),
                    2 => $vview['TransactionNo'],
                    3 => $vview['TransDate'],
                    4 => $vview['LastTransUpdate'],
                    5 => $vview['Status'],
                    6 => $vview['Option1']
                );
                array_push($completeexcelvalues, $excelvalues);
            }
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Export to Excel";
        $mswrpt->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
    } elseif (isset($_GET['pdf']) && $_GET['pdf'] == "RecreditMSWTransactions") {

        $completePDFArray = array();

        $betrefid = $_GET['BetRefID'];
        $queries = $mswrpt3->getrecreditdetails(strtoupper(trim($betrefid)),null,null);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('MSW Re-Credit Transactions');
        $pdf->html.='<div style="text-align:center;margin-top : -20px;">Bet Reference ID : ' . strtoupper(trim($betrefid)) . '</div>';
        $pdf->html.='<br><br>';
        $pdf->SetFontSize(6);
        $pdf->c_tableHeader2(array(
            array('value' => 'BetSlip ID'),
            array('value' => 'Amount'),
            array('value' => 'Transaction No'),
            array('value' => 'Transaction Date', 'width' => '100px'),
            array('value' => 'Date Last Updated', 'width' => '100px'),
            array('value' => 'Status', 'width' => '100px'),
            array('value' => 'Tracking ID')
        ));
        if (count($queries) > 0) {
            foreach ($queries as $row) {
                //Status
                switch ($row['Status']) {
                    case '0':
                        $row['Status'] = 'Pending';
                        break;
                    case '1':
                        $row['Status'] = 'Successful';
                        break;
                    case '2':
                        $row['Status'] = 'Failed';
                        break;
                    case '3':
                        $row['Status'] = 'Fulfilled Approved';
                        break;
                    case '4':
                        $row['Status'] = 'Fulfilled Denied';
                        break;
                }
                $rtransdetails = $row['TransactionNo'];
                $pdf->c_tableRow2(array(
                    array('value' => $row['BetSlipID'], 'align' => 'center'),
                    array('value' => number_format($row['Amount'], 2, '.', ','), 'align' => 'right'),
                    array('value' => $row['TransactionNo'], 'align' => 'center'),
                    array('value' => $row['TransDate'], 'align' => 'center', 'width' => '100px'),
                    array('value' => $row['LastTransUpdate'], 'align' => 'center', 'width' => '100px'),
                    array('value' => $row['Status'], 'align' => 'center', 'width' => '100px'),
                    array('value' => $row['Option1'], 'align' => 'center')
                ));
            }
        } else {
            $pdf->html.='<div style="text-align:center;">No Results Found</div>';
        }

        $pdf->c_tableEnd();
        $vauditfuncID = 40; //export to pdf
        $vtransdetails = "Export to PDF";
        $mswrpt->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $pdf->c_generatePDF("RecreditMSW_" . strtoupper(trim($betrefid)) . '.pdf');
    }
} else {
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>