<?php

/*
 * @Date Sep 10, 2013
 * @Author jr
 */

class TransactionpercutoffController extends VMSBaseIdentity {

    public $dateFrom;
    public $dateTo;
    public $advanceFilter = false;

    public function actionIndex() {
        $_AccountSessions = new SessionModel();
        $_TransPerCutOff = new TransactionpercutoffForm();
        $_Coupons = new CouponModel();
        $_Tickets = new TicketModel();
        $_Terminals = new TerminalsModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }

        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);

        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } else {
            $display = 'none';
            Yii::app()->session['display'] = $display;
            $rawData = array();
            Yii::app()->session['rawData'] = $rawData;
            $display = 'block';
            if (isset($_POST['TransactionpercutoffForm'])) {
                $_TransPerCutOff->attributes = $_POST['TransactionpercutoffForm'];
                $data = $_TransPerCutOff->attributes;
                Yii::app()->session['transactiondate'] = $data['transactiondate'] . " " . Yii::app()->params->cutofftimestart;
                Yii::app()->session['site'] = $data['site'];
                Yii::app()->session['vouchertype'] = $data['vouchertype'];
                Yii::app()->session['status'] = $data['status'];

                $transdate = Yii::app()->session['transactiondate'];
                $site = Yii::app()->session['site'];
                $vouchertype = Yii::app()->session['vouchertype'];
                $status = Yii::app()->session['status'];
                $rawData = array();
                if ($site == 'All' && $vouchertype == 'All') {
                    $couponarr = $_Coupons->getAllUsedCouponList($transdate);
                    $ticketarr = $_Tickets->getAllUsedTicketList($transdate);
                } elseif ($site != 'All' && $vouchertype == 'All') {
                    $couponarr = $_Coupons->getUsedCouponListBySite($site, $transdate);
                    $ticketarr = $_Tickets->getUsedTicketListBySite($site, $transdate);
                } elseif ($site == 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getAllUsedTicketList($transdate);
                } elseif ($site == 'All' && $vouchertype == 2) {
                    $couponarr = $_Coupons->getAllUsedCouponList($transdate);
                } elseif ($site != 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getUsedTicketListBySite($site, $transdate);
                } elseif ($site != 'All' && $vouchertype == 2) {
                    $couponarr = $_Coupons->getUsedCouponListBySite($site, $transdate);
                }

                if (!empty($couponarr)) {
                    foreach ($couponarr as $value) {
                        $sitename = $value['SiteName'];
                        $couponvouchertypeid = $value['VoucherTypeID'];
                        $couponvouchercode = $value['VoucherCode'];
                        $couponstatus = $value['Status'];
                        $couponterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($couponterminalid);
                        $couponterminalname = $terminalname[0]['TerminalName'];
                        $couponamount = $value['Amount'];
                        $coupondatecreated = $value['DateCreated'];
                        $coupondateexpiry = $value['ValidToDate'];
                        $coupondateupdated = $value['DateUpdated'];
                        $couponiscreditable = $value['IsCreditable'];
                        $couponsource = "Cashier";
                        if($couponstatus == 3){
                            $couponstatus = 'Used';
                        } else {
                            $couponstatus = '';
                        }
                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $couponterminalname,
                            'VoucherType' => $couponvouchertypeid,
                            'VoucherCode' => $couponvouchercode,
                            'Status' => $couponstatus,
                            'Amount' => $couponamount,
                            'DateCreated' => $coupondateupdated,
                            'DateExpired' => $coupondateexpiry,
                            'Source' => $couponsource,
                            'IsCreditable' => $couponiscreditable,
                        );

                        array_push($rawData, $record);
                    }
                }
                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $ticketvouchertypeid = $value['VoucherTypeID'];
                        $ticketvouchercode = $value['VoucherCode'];
                        $ticketstatus = $value['Status'];
                        $ticketterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($ticketterminalid);
                        $ticketterminalname = $terminalname[0]['TerminalName'];
                        $ticketamount = $value['Amount'];
                        $ticketdatecreated = $value['DateCreated'];
                        $ticketdateexpiry = $value['ValidToDate'];
                        $ticketsource = 'EGM';
                        $ticketiscreditable = $value['IsCreditable'];
                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $ticketterminalname,
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $ticketdatecreated,
                            'DateExpired' => $ticketdateexpiry,
                            'Source' => $ticketsource,
                            'IsCreditable' => $ticketiscreditable,
                        );
                        array_push($rawData, $record);
                    }
                }
            } else {
                $transdate = Yii::app()->session['transactiondate'];
                $site = Yii::app()->session['site'];
                $vouchertype = Yii::app()->session['vouchertype'];
                $status = Yii::app()->session['status'];
                $rawData = array();
                if ($site == 'All' && $vouchertype == 'All') {
                    $couponarr = $_Coupons->getAllUsedCouponList($transdate);
                    $ticketarr = $_Tickets->getAllUsedTicketList($transdate);
                } elseif ($site != 'All' && $vouchertype == 'All') {
                    $couponarr = $_Coupons->getUsedCouponListBySite($site, $transdate);
                    $ticketarr = $_Tickets->getUsedTicketListBySite($site, $transdate);
                } elseif ($site == 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getAllUsedTicketList($transdate);
                } elseif ($site == 'All' && $vouchertype == 2) {
                   $couponarr = $_Coupons->getAllUsedCouponList($transdate);
                } elseif ($site != 'All' && $vouchertype == 1) {
                   $ticketarr = $_Tickets->getUsedTicketListBySite($site, $transdate);
                } elseif ($site != 'All' && $vouchertype == 2) {
                   $couponarr = $_Coupons->getUsedCouponListBySite($site, $transdate);
                }

                if (!empty($couponarr)) {
                    foreach ($couponarr as $value) {
                        $sitename = $value['SiteName'];
                        $couponvouchertypeid = $value['VoucherTypeID'];
                        $couponvouchercode = $value['VoucherCode'];
                        $couponstatus = $value['Status'];
                        $couponterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($couponterminalid);
                        $couponterminalname = $terminalname[0]['TerminalName'];
                        $couponamount = $value['Amount'];
                        $coupondatecreated = $value['DateCreated'];
                        $coupondateupdated = $value['DateUpdated'];
                        $coupondateexpiry = $value['ValidToDate'];
                        $couponsource = 'Cashier';
                        $couponiscreditable = $value['IsCreditable'];
                        if($couponstatus == 3){
                            $couponstatus = 'Used';
                        } else {
                            $couponstatus = '';
                        }
                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $couponterminalname,
                            'VoucherType' => $couponvouchertypeid,
                            'VoucherCode' => $couponvouchercode,
                            'Status' => $couponstatus,
                            'Amount' => $couponamount,
                            'DateCreated' => $coupondateupdated, //pass date wherein coupon has used
                            'DateExpired' => $coupondateexpiry,
                            'Source' => $couponsource,
                            'IsCreditable' => $couponiscreditable,
                        );

                        array_push($rawData, $record);
                    }
                }
                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $ticketvouchertypeid = $value['VoucherTypeID'];
                        $ticketvouchercode = $value['VoucherCode'];
                        $ticketstatus = $value['Status'];
                        $ticketterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($ticketterminalid);
                        $ticketterminalname = $terminalname[0]['TerminalName'];
                        $ticketamount = $value['Amount'];
                        $ticketdatecreated = $value['DateCreated'];
                        $ticketdateexpiry = $value['ValidToDate'];
                        $ticketsource = 'EGM';
                        $ticketiscreditable = $value['IsCreditable'];
                        if($ticketstatus == 3){
                            $ticketstatus = 'Used';
                        } else {
                            $ticketstatus = '';
                        }
                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $ticketterminalname,
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $ticketdatecreated,
                            'DateExpired' => $ticketdateexpiry,
                            'Source' => $ticketsource,
                            'IsCreditable' => $ticketiscreditable,
                        );

                        array_push($rawData, $record);
                    }
                }
            }
            Yii::app()->session['rawData'] = $rawData;
            $display = 'block';
            Yii::app()->session['display'] = $display;
            $this->render('index', array('model' => $_TransPerCutOff));
        }
    }

    public function actionTransactionPerCutOffDataTable($rawData) {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }

        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } else {
                $arrayDataProvider = new CArrayDataProvider($rawData, array(
                    'keyField' => false,
                    'pagination' => array(
                    'pageSize' => 10,
                ),
                ));
            $params = array(
                'arrayDataProvider' => $arrayDataProvider,
            );
            
            if (!isset($_GET['ajax'])) {
                $this->renderPartial('transactionpercutoff', $params);
            } else {
                $this->renderPartial('transactionpercutoff', $params);
            }
        }
    }

    public function actionExportToCSV() {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }

        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);

        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } else {
            Yii::import('ext.ECSVExport');

            $rawData = Yii::app()->session['rawData'];

            $filename = "Transaction_Per_Cut_Off_" . Date('Y_m_d');

            $csv = new ECSVExport($rawData);
            $csv->toCSV($filename);

            $content = file_get_contents($filename);

            Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
            exit();
        }
    }

    //Export To Excel
    public function actionExportToExcel() {
        $_AccountSessions = new SessionModel();
        $model = new TransactionpercutoffForm();
        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }

        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);

        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } else {
            $data = Yii::app()->session['rawData'];
            //code to download the data of report in the excel format
            include_once("protected/extensions/ExportToExcel.php");
            $transdate = Yii::app()->session['transactiondate'];
            $transdate = explode(' ', $transdate);
            //code to download the data of report in the excel format
            $fn = "TransactionPerCutOff_".$transdate[0].".xls";
            //create the instance of the exportexcel format
            $excel_obj = new ExportExcel("$fn");
            //setting the values of the headers and data of the excel file
            //and these values comes from the other file which file shows the data
            $header = array('Voucher Type', 'Code ', 'Site', 'Terminal', 'Amount', 'Transaction Date', 'Date Expired', 'Source', 'Is Creditable', 'Status');
            $completeexcelvalues = array();

            foreach ($data as $vview) {
                $excelvalues = array($vview['VoucherType'] == 1 ? "Ticket" : "Coupon", $vview['VoucherCode'], $vview['SiteName'], $vview['TerminalName'], $vview['Amount'], $vview['DateCreated'], $vview['DateExpired'], $vview['Source'], $vview['IsCreditable'] == 1 ? "Creditable" : "Not Creditable", $vview['Status']);
                array_push($completeexcelvalues, $excelvalues);
            }

            $excel_obj->setHeadersAndValues($header, $completeexcelvalues);
            //now generate the excel file with the data and headers set
            $excel_obj->GenerateExcelFile();
        }
    }

}

?>
