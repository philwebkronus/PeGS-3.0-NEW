<?php

/*
 * @Date Sep 10, 2013
 * @Author jshernandez
 */

class TicketTransactionsMonitoringController extends VMSBaseIdentity {

    public $dateFrom;
    public $dateTo;
    public $advanceFilter = false;

    public function actionIndex() {
        $_AccountSessions = new SessionModel();
        $_TicketTransactionsMonitoring = new TicketTransactionsMonitoringForm();
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
            $ticketTotalCount = 0;
            $ticketActiveCount = 0;
            $ticketDepositReloadCount = 0;
            $ticketsEncashedCount = 0;
            $ticketVoidCount = 0;
            $ticketTotalAmount = 0;
            $ticketActiveAmount = 0;
            $ticketDepositReloadAmount = 0;
            $ticketsEncashedTotal = 0;
            $ticketVoidAmount = 0;
            $totalAmount = 0;

            if (isset($_POST['TicketTransactionsMonitoringForm'])) {
                $_TicketTransactionsMonitoring->attributes = $_POST['TicketTransactionsMonitoringForm'];
                $data = $_TicketTransactionsMonitoring->attributes;
                if (isset($data['ticketcode']) && ($data['ticketcode'] == '' || empty($data['ticketcode']))) {
                    Yii::app()->session['dateFrom'] = $data['dateFrom'] . " " . Yii::app()->params->cutofftimestart;
                    Yii::app()->session['dateTo'] = $data['dateTo'] . " " . Yii::app()->params->cutofftimestart;
                    Yii::app()->session['site'] = $data['site'];
                    Yii::app()->session['vouchertype'] = $data['vouchertype'];
                    Yii::app()->session['status'] = $data['status'];
                    Yii::app()->session['ticketcode'] = $data['ticketcode'];
                } else {
                    Yii::app()->session['dateFrom'] = "";
                    Yii::app()->session['dateTo'] = "";
                    Yii::app()->session['site'] = "";
                    Yii::app()->session['vouchertype'] = "";
                    Yii::app()->session['status'] = "";
                    Yii::app()->session['ticketcode'] = $data['ticketcode'];
                }

                $dateFrom = Yii::app()->session['dateFrom'];
                $dateTo = Yii::app()->session['dateTo'];
                $site = Yii::app()->session['site'];
                $vouchertype = Yii::app()->session['vouchertype'];
                $status = Yii::app()->session['status'];
                $ticketcode = Yii::app()->session['ticketcode'];
                $rawData = array();

                if ($ticketcode == '' || empty($ticketcode)) {
                    if ($status == 'All' || $status == '') {
                        if ($site == 'All' || $site == '') {
                            $ticketarr = $_Tickets->getAllTicketTransactionList($dateFrom, $dateTo);
                        } else {
                            $ticketarr = $_Tickets->getTicketTransactionListBySite($site, $dateFrom, $dateTo);
                        }
                    } else {
                        if ($site == 'All' || $site == '') {
                            $ticketarr = $_Tickets->getAllTicketTransactionListWithStatus($status, $dateFrom, $dateTo);
                        } else {
                            $ticketarr = $_Tickets->getTicketTransactionListBySiteWithStatus($status, $site, $dateFrom, $dateTo);
                        }
                    }
                } else {
                    $ticketarr = $_Tickets->getTicketTransactionByTicketCode($ticketcode);
                }
                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $sitecode = str_replace(Yii::app()->params['sitePrefix'], "", $value['SiteCode']);
                        $ticketvouchertypeid = $value['VoucherTypeID'];
                        $ticketvouchercode = $value['VoucherCode'];
                        $ticketstatus = $value['Status'];
                        $ticketterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($ticketterminalid);
                        $ticketterminalname = $terminalname[0]['TerminalName'];
                        $ticketamount = $value['Amount'];
                        $ticketTotalAmount = $ticketTotalAmount + $ticketamount;
                        $ticketdatecreated = $value['DateCreated'];
                        $ticketdateexpiry = $value['ValidToDate'];
                        $ticketsource = 'EGM';
                        $ticketiscreditable = $value['IsCreditable'];
                        $totalAmount = $value['TotalAmount'];

                        $ticketTotalCount = $ticketTotalCount + 1;
                        $dateNow = date("Y-m-d H:i:s");
                        if ($ticketdateexpiry < $dateNow) {
                            if ($ticketstatus == 2) {
                                $ticketstatus = 'Expired';
                                $ticketVoidCount = $ticketVoidCount + 1;
                                $ticketVoidAmount = $ticketVoidAmount + $ticketamount;
                                $dateupdated = $value['DateCreated'];
                            } else if ($ticketstatus == 3) {
                                $ticketstatus = 'Expired';
                                $ticketDepositReloadCount = $ticketDepositReloadCount + 1;
                                $ticketDepositReloadAmount = $ticketDepositReloadAmount + $ticketamount;
                                $dateupdated = $value['DateUpdated'];
                            } else if ($ticketstatus == 4) {
                                $ticketstatus = 'Expired';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['DateEncashed'];
                            } else if ($ticketstatus == 7) {
                                $ticketstatus = 'Expired';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['ValidToDate'];
                            } else {
                                $ticketstatus = '';
                                $dateupdated = $value['DateUpdated'];
                            }
                        } else {
                            if ($ticketstatus == 2) {
                                $ticketstatus = 'Cancelled';
                                $ticketVoidCount = $ticketVoidCount + 1;
                                $ticketVoidAmount = $ticketVoidAmount + $ticketamount;
                                $dateupdated = $value['DateCreated'];
                            } else if ($ticketstatus == 3) {
                                $ticketstatus = 'Used';
                                $ticketDepositReloadCount = $ticketDepositReloadCount + 1;
                                $ticketDepositReloadAmount = $ticketDepositReloadAmount + $ticketamount;
                                $dateupdated = $value['DateUpdated'];
                            } else if ($ticketstatus == 4) {
                                $ticketstatus = 'Encashed';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['DateEncashed'];
                            } else if ($ticketstatus == 7) {
                                $ticketstatus = 'Expired';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['ValidToDate'];
                            } else {
                                $ticketstatus = '';
                                $dateupdated = $value['DateUpdated'];
                            }
                        }
                        $vticketdatecreated = new DateTime($ticketdatecreated);
                        $zticketdatecreated = date(date_format($vticketdatecreated, 'Y-m-d H:i:s'));
                        $vticketdateexpiry = new DateTime($ticketdateexpiry);
                        $zticketdateexpiry = date(date_format($vticketdateexpiry, 'Y-m-d'));
                        $vdateupdated = new DateTime($dateupdated);
                        $zdateupdated = date(date_format($vdateupdated, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'SiteCode' => $sitecode,
                            'TerminalName' => str_replace("TERMINAL", $sitecode, $ticketterminalname),
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $zticketdatecreated,
                            'DateExpired' => $zticketdateexpiry,
                            'Source' => $ticketsource,
                            'IsCreditable' => $ticketiscreditable,
                            'DateUpdated' => $zdateupdated,
                            'TotalAmount' => number_format(doubleval($totalAmount), 2)
                        );
                        array_push($rawData, $record);
                    }
                }
            } else {
                $dateFrom = Yii::app()->session['dateFrom'];
                $dateTo = Yii::app()->session['dateTo'];
                $site = Yii::app()->session['site'];
                $vouchertype = Yii::app()->session['vouchertype'];
                $status = Yii::app()->session['status'];
                $ticketcode = Yii::app()->session['ticketcode'];
                $rawData = array();
                if ($ticketcode == '' || empty($ticketcode)) {
                    if ($status == 'All' || $status == '') {
                        if ($site == 'All' || $site == '') {
                            $ticketarr = $_Tickets->getAllTicketTransactionList($dateFrom, $dateTo);
                        } else {
                            $ticketarr = $_Tickets->getTicketTransactionListBySite($site, $dateFrom, $dateTo);
                        }
                    } else {
                        if ($site == 'All' || $site == '') {
                            $ticketarr = $_Tickets->getAllTicketTransactionListWithStatus($status, $dateFrom, $dateTo);
                        } else {
                            $ticketarr = $_Tickets->getTicketTransactionListBySiteWithStatus($status, $site, $dateFrom, $dateTo);
                        }
                    }
                } else {
                    $ticketarr = $_Tickets->getTicketTransactionByTicketCode($ticketcode);
                }

                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $sitecode = str_replace(Yii::app()->params['sitePrefix'], "", $value['SiteCode']);
                        $ticketvouchertypeid = $value['VoucherTypeID'];
                        $ticketvouchercode = $value['VoucherCode'];
                        $ticketstatus = $value['Status'];
                        $ticketterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($ticketterminalid);
                        $ticketterminalname = $terminalname[0]['TerminalName'];
                        $ticketamount = $value['Amount'];
                        $ticketTotalAmount = $ticketTotalAmount + $ticketamount;
                        $ticketdatecreated = $value['DateCreated'];
                        $ticketdateexpiry = $value['ValidToDate'];
                        $dateupdated = $value['DateUpdated'];
                        $ticketsource = 'EGM';
                        $ticketiscreditable = $value['IsCreditable'];
                        $ticketTotalCount = $ticketTotalCount + 1;
                        $totalAmount = $value['TotalAmount'];
                        $dateNow = date("Y-m-d H:i:s");
                        if ($ticketdateexpiry < $dateNow) {
                            if ($ticketstatus == 2) {
                                $ticketstatus = 'Expired';
                                $ticketVoidCount = $ticketVoidCount + 1;
                                $ticketVoidAmount = $ticketVoidAmount + $ticketamount;
                                $dateupdated = $value['DateCreated'];
                            } else if ($ticketstatus == 3) {
                                $ticketstatus = 'Expired';
                                $ticketDepositReloadCount = $ticketDepositReloadCount + 1;
                                $ticketDepositReloadAmount = $ticketDepositReloadAmount + $ticketamount;
                                $dateupdated = $value['DateUpdated'];
                            } else if ($ticketstatus == 4) {
                                $ticketstatus = 'Expired';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['DateEncashed'];
                            } else if ($ticketstatus == 7) {
                                $ticketstatus = 'Expired';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['ValidToDate'];
                            } else {
                                $ticketstatus = '';
                                $dateupdated = $value['DateUpdated'];
                            }
                        } else {
                            if ($ticketstatus == 2) {
                                $ticketstatus = 'Cancelled';
                                $ticketVoidCount = $ticketVoidCount + 1;
                                $ticketVoidAmount = $ticketVoidAmount + $ticketamount;
                                $dateupdated = $value['DateCreated'];
                            } else if ($ticketstatus == 3) {
                                $ticketstatus = 'Used';
                                $ticketDepositReloadCount = $ticketDepositReloadCount + 1;
                                $ticketDepositReloadAmount = $ticketDepositReloadAmount + $ticketamount;
                                $dateupdated = $value['DateUpdated'];
                            } else if ($ticketstatus == 4) {
                                $ticketstatus = 'Encashed';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['DateEncashed'];
                            } else if ($ticketstatus == 7) {
                                $ticketstatus = 'Expired';
                                $ticketsEncashedCount = $ticketsEncashedCount + 1;
                                $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                                $dateupdated = $value['ValidToDate'];
                            } else {
                                $ticketstatus = '';
                                $dateupdated = $value['DateUpdated'];
                            }
                        }
                        $vticketdatecreated = new DateTime($ticketdatecreated);
                        $zticketdatecreated = date(date_format($vticketdatecreated, 'Y-m-d H:i:s'));
                        $vticketdateexpiry = new DateTime($ticketdateexpiry);
                        $zticketdateexpiry = date(date_format($vticketdateexpiry, 'Y-m-d '));
                        $vdateupdated = new DateTime($dateupdated);
                        $zdateupdated = date(date_format($vdateupdated, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'SiteCode' => $sitecode,
                            'TerminalName' => str_replace("TERMINAL", $sitecode, $ticketterminalname),
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $zticketdatecreated,
                            'DateExpired' => $zticketdateexpiry,
                            'Source' => $ticketsource,
                            'IsCreditable' => $ticketiscreditable,
                            'DateUpdated' => $zdateupdated,
                            'TotalAmount' => number_format(doubleval($totalAmount), 2)
                        );

                        array_push($rawData, $record);
                    }
                }
            }

            Yii::app()->session['rawData'] = $rawData;
            $display = 'block';
            Yii::app()->session['display'] = $display;
            Yii::app()->session['PrintedTicketsCount'] = $ticketTotalCount;
            Yii::app()->session['ActiveTicketsCount'] = $ticketActiveCount;
            Yii::app()->session['DepositReloadTicketsCount'] = $ticketDepositReloadCount;
            Yii::app()->session['TotalAmount'] = $totalAmount;
            Yii::app()->session['EncashedTicketsCount'] = $ticketsEncashedCount;
            Yii::app()->session['VoidTicketsCount'] = $ticketVoidCount;
            Yii::app()->session['PrintedTicketsTotal'] = number_format($ticketTotalAmount, 2);
            Yii::app()->session['ActiveTicketsTotal'] = number_format($ticketActiveAmount, 2);
            Yii::app()->session['DepositReloadTicketsTotal'] = number_format($ticketDepositReloadAmount, 2);
            Yii::app()->session['EncashedTicketsTotal'] = number_format($ticketsEncashedTotal, 2);
            Yii::app()->session['VoidTicketsTotal'] = number_format($ticketVoidAmount, 2);
            $this->render('index', array('model' => $_TicketTransactionsMonitoring));
        }
    }

    public function actionTicketMonitoringDataTable($rawData) {
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
                $this->renderPartial('ticketTransactions', $params);
            } else {
                $this->renderPartial('ticketTransactions', $params);
            }
        }
    }

    //Export To Excel
    public function actionExportToExcelTicket() {
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
            $dateFrom = Yii::app()->session['dateFrom'];
            $dateTo = Yii::app()->session['dateTo'];
            //code to download the data of report in the excel format
            $fn = "Ticket_Transactions_Monitoring.xls";
            //create the instance of the exportexcel format
            $excel_obj = new ExportExcel("$fn");
            //setting the values of the headers and data of the excel file
            //and these values comes from the other file which file shows the data

            $table = "<table>";
            $table .= "<tr><td></td></tr><tr><td width='100px;'>Site/PEGS Code</td><td width='140px;'>Terminal Name</td><td width='150px;'>Ticket Code</td><td width='180px;'>Date and Time Printed</td><td width='180px;'>Amount</td><td width='100px;'>Status</td><td width='180px;'>Expiration Date</td><td width='180px;'>Date and Time Processed</td></tr>";

            foreach ($data as $vview) {
                $dateExpiry = $vview['DateExpired'];
                $expirationDate = date('Y-m-d', strtotime($dateExpiry));
                $table .= "<tr><td>'" . str_replace(Yii::app()->params['sitePrefix'], '', $vview['SiteCode']) . "</td><td>'" . $vview['TerminalName'] . "</td><td>'" . $vview['VoucherCode'] . "</td><td>" . $vview['DateCreated'] . "</td><td align='right'>'" . number_format($vview['Amount'], 2, ".", ",") . "</td><td>" . $vview['Status'] . "</td><td>" . $expirationDate . "</td><td>" . $vview['DateUpdated'] . "</td></tr>";
            }
            $table .= "</table>";
            $excel_obj->toHTML($table);
        }
    }

    public function actionCoupon() {
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
                        if ($couponstatus == 3) {
                            $couponstatus = 'Used';
                        } else {
                            $couponstatus = '';
                        }

                        $vcoupondateupdated = new DateTime($coupondateupdated);
                        $zcoupondateupdated = date(date_format($vcoupondateupdated, 'Y-m-d H:i:s'));
                        $vcoupondateexpiry = new DateTime($coupondateexpiry);
                        $zcoupondateexpiry = date(date_format($vcoupondateexpiry, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $couponterminalname,
                            'VoucherType' => $couponvouchertypeid,
                            'VoucherCode' => $couponvouchercode,
                            'Status' => $couponstatus,
                            'Amount' => $couponamount,
                            'DateCreated' => $zcoupondateupdated,
                            'DateExpired' => $zcoupondateexpiry,
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

                        $vticketdatecreated = new DateTime($ticketdatecreated);
                        $zticketdatecreated = date(date_format($vticketdatecreated, 'Y-m-d H:i:s'));
                        $vticketdateexpiry = new DateTime($ticketdateexpiry);
                        $zticketdateexpiry = date(date_format($vticketdateexpiry, 'Y-m-d H:i:s'));
                        if ($ticketstatus == 3) {
                            $ticketstatus = 'Used';
                        } else {
                            $ticketstatus = '';
                        }
                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => str_replace("TERMINAL", $sitecode, $ticketterminalname),
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $zticketdatecreated,
                            'DateExpired' => $zticketdateexpiry,
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
                        if ($couponstatus == 3) {
                            $couponstatus = 'Used';
                        } else {
                            $couponstatus = '';
                        }

                        $vcoupondateupdated = new DateTime($coupondateupdated);
                        $zcoupondateupdated = date(date_format($vcoupondateupdated, 'Y-m-d H:i:s'));
                        $vcoupondateexpiry = new DateTime($coupondateexpiry);
                        $zcoupondateexpiry = date(date_format($vcoupondateexpiry, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $couponterminalname,
                            'VoucherType' => $couponvouchertypeid,
                            'VoucherCode' => $couponvouchercode,
                            'Status' => $couponstatus,
                            'Amount' => $couponamount,
                            'DateCreated' => $zcoupondateupdated, //pass date wherein coupon has used
                            'DateExpired' => $zcoupondateexpiry,
                            'Source' => $couponsource,
                            'IsCreditable' => $couponiscreditable,
                        );

                        array_push($rawData, $record);
                    }
                }
                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $sitecode = $value['SiteCode'];
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
                        if ($ticketstatus == 3) {
                            $ticketstatus = 'Used';
                        } else {
                            $ticketstatus = '';
                        }

                        $vticketdatecreated = new DateTime($ticketdatecreated);
                        $zticketdatecreated = date(date_format($vticketdatecreated, 'Y-m-d H:i:s'));
                        $vticketdateexpiry = new DateTime($ticketdateexpiry);
                        $zticketdateexpiry = date(date_format($vticketdateexpiry, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => str_replace("TERMINAL", $sitecode, $ticketterminalname),
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $zticketdatecreated,
                            'DateExpired' => $zticketdateexpiry,
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
            $this->render('coupon', array('model' => $_TransPerCutOff));
        }
    }

    public function actionTicket() {
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
            $ticketTotalCount = 0;
            $ticketActiveCount = 0;
            $ticketDepositReloadCount = 0;
            $ticketsEncashedCount = 0;
            $ticketVoidCount = 0;
            $ticketTotalAmount = 0;
            $ticketActiveAmount = 0;
            $ticketDepositReloadAmount = 0;
            $ticketsEncashedTotal = 0;
            $ticketVoidAmount = 0;
            $totalAmount = 0;
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
                    $ticketarr = $_Tickets->getAllUsedTicketListTrans($transdate);
                } elseif ($site != 'All' && $vouchertype == 'All') {
                    $couponarr = $_Coupons->getUsedCouponListBySite($site, $transdate);
                    $ticketarr = $_Tickets->getUsedTicketListBySite($site, $transdate);
                } elseif ($site == 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getAllUsedTicketListTrans($transdate);
                } elseif ($site == 'All' && $vouchertype == 2) {
                    $couponarr = $_Coupons->getAllUsedCouponList($transdate);
                } elseif ($site != 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getUsedTicketListBySiteTrans($site, $transdate);
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
                        if ($couponstatus == 3) {
                            $couponstatus = 'Used';
                        } else {
                            $couponstatus = '';
                        }

                        $vcoupondateupdated = new DateTime($coupondateupdated);
                        $zcoupondateupdated = date(date_format($vcoupondateupdated, 'Y-m-d H:i:s'));
                        $vcoupondateexpiry = new DateTime($coupondateexpiry);
                        $zcoupondateexpiry = date(date_format($vcoupondateexpiry, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $couponterminalname,
                            'VoucherType' => $couponvouchertypeid,
                            'VoucherCode' => $couponvouchercode,
                            'Status' => $couponstatus,
                            'Amount' => $couponamount,
                            'DateCreated' => $zcoupondateupdated,
                            'DateExpired' => $zcoupondateexpiry,
                            'Source' => $couponsource,
                            'IsCreditable' => $couponiscreditable,
                        );

                        array_push($rawData, $record);
                    }
                }

                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $sitecode = str_replace(Yii::app()->params['sitePrefix'], "", $value['SiteCode']);
                        $ticketvouchertypeid = $value['VoucherTypeID'];
                        $ticketvouchercode = $value['VoucherCode'];
                        $ticketstatus = $value['Status'];
                        $ticketterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($ticketterminalid);
                        $ticketterminalname = $terminalname[0]['TerminalName'];
                        $ticketamount = $value['Amount'];
                        $ticketTotalAmount = $ticketTotalAmount + $ticketamount;
                        $ticketdatecreated = $value['DateCreated'];
                        $ticketdateexpiry = $value['ValidToDate'];
                        $ticketsource = 'EGM';
                        $ticketiscreditable = $value['IsCreditable'];
                        $totalAmount = $value['TotalAmount'];

                        $ticketTotalCount = $ticketTotalCount + 1;

                        if ($ticketstatus == 1) {
                            $ticketstatus = 'Active';
                            $ticketActiveCount = $ticketActiveCount + 1;
                            $ticketActiveAmount = $ticketActiveAmount + $ticketamount;
                            $dateupdated = $value['DateCreated'];
                        } else if ($ticketstatus == 2) {
                            $ticketstatus = 'Active';
                            $ticketVoidCount = $ticketVoidCount + 1;
                            $ticketVoidAmount = $ticketVoidAmount + $ticketamount;
                            $dateupdated = $value['DateCreated'];
                        } else if ($ticketstatus == 3) {
                            $ticketstatus = 'Used';
                            $ticketDepositReloadCount = $ticketDepositReloadCount + 1;
                            $ticketDepositReloadAmount = $ticketDepositReloadAmount + $ticketamount;
                            $dateupdated = $value['DateUpdated'];
                        } else if ($ticketstatus == 4) {
                            $ticketstatus = 'Encashed';
                            $ticketsEncashedCount = $ticketsEncashedCount + 1;
                            $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                            $dateupdated = $value['DateEncashed'];
                        } else {
                            $ticketstatus = '';
                            $dateupdated = $value['DateUpdated'];
                        }

                        $vticketdatecreated = new DateTime($ticketdatecreated);
                        $zticketdatecreated = date(date_format($vticketdatecreated, 'Y-m-d H:i:s'));
                        $vticketdateexpiry = new DateTime($ticketdateexpiry);
                        $zticketdateexpiry = date(date_format($vticketdateexpiry, 'Y-m-d'));
                        $vdateupdated = new DateTime($dateupdated);
                        $zdateupdated = date(date_format($vdateupdated, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'SiteCode' => $sitecode,
                            'TerminalName' => str_replace("TERMINAL", $sitecode, $ticketterminalname),
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $zticketdatecreated,
                            'DateExpired' => $zticketdateexpiry,
                            'Source' => $ticketsource,
                            'IsCreditable' => $ticketiscreditable,
                            'DateUpdated' => $zdateupdated,
                            'TotalAmount' => number_format(doubleval($totalAmount), 2)
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
                    $ticketarr = $_Tickets->getAllUsedTicketListTrans($transdate);
                } elseif ($site != 'All' && $vouchertype == 'All') {
                    $couponarr = $_Coupons->getUsedCouponListBySite($site, $transdate);
                    $ticketarr = $_Tickets->getUsedTicketListBySiteTrans($site, $transdate);
                } elseif ($site == 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getAllUsedTicketListTrans($transdate);
                } elseif ($site == 'All' && $vouchertype == 2) {
                    $couponarr = $_Coupons->getAllUsedCouponList($transdate);
                } elseif ($site != 'All' && $vouchertype == 1) {
                    $ticketarr = $_Tickets->getUsedTicketListBySiteTrans($site, $transdate);
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
                        if ($couponstatus == 3) {
                            $couponstatus = 'Used';
                        } else {
                            $couponstatus = '';
                        }

                        $vcoupondateupdated = new DateTime($coupondateupdated);
                        $zcoupondateupdated = date(date_format($vcoupondateupdated, 'Y-m-d H:i:s'));
                        $vcoupondateexpiry = new DateTime($coupondateexpiry);
                        $zcoupondateexpiry = date(date_format($vcoupondateexpiry, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'TerminalName' => $couponterminalname,
                            'VoucherType' => $couponvouchertypeid,
                            'VoucherCode' => $couponvouchercode,
                            'Status' => $couponstatus,
                            'Amount' => $couponamount,
                            'DateCreated' => $zcoupondateupdated, //pass date wherein coupon has used
                            'DateExpired' => $zcoupondateexpiry,
                            'Source' => $couponsource,
                            'IsCreditable' => $couponiscreditable,
                        );

                        array_push($rawData, $record);
                    }
                }

                if (!empty($ticketarr)) {
                    foreach ($ticketarr as $value) {
                        $sitename = $value['SiteName'];
                        $sitecode = str_replace(Yii::app()->params['sitePrefix'], "", $value['SiteCode']);
                        $ticketvouchertypeid = $value['VoucherTypeID'];
                        $ticketvouchercode = $value['VoucherCode'];
                        $ticketstatus = $value['Status'];
                        $ticketterminalid = $value['TerminalID'];
                        $terminalname = $_Terminals->getTerminalNamesUsingTerminalID($ticketterminalid);
                        $ticketterminalname = $terminalname[0]['TerminalName'];
                        $ticketamount = $value['Amount'];
                        $ticketTotalAmount = $ticketTotalAmount + $ticketamount;
                        $ticketdatecreated = $value['DateCreated'];
                        $ticketdateexpiry = $value['ValidToDate'];
                        $dateupdated = $value['DateUpdated'];
                        $ticketsource = 'EGM';
                        $ticketiscreditable = $value['IsCreditable'];
                        $ticketTotalCount = $ticketTotalCount + 1;
                        $totalAmount = $value['TotalAmount'];

                        if ($ticketstatus == 1) {
                            $ticketstatus = 'Active';
                            $ticketActiveCount = $ticketActiveCount + 1;
                            $ticketActiveAmount = $ticketActiveAmount + $ticketamount;
                            $dateupdated = $value['DateCreated'];
                        } else if ($ticketstatus == 2) {
                            $ticketstatus = 'Active';
                            $ticketVoidCount = $ticketVoidCount + 1;
                            $ticketVoidAmount = $ticketVoidAmount + $ticketamount;
                            $dateupdated = $value['DateCreated'];
                        } else if ($ticketstatus == 3) {
                            $ticketstatus = 'Used';
                            $ticketDepositReloadCount = $ticketDepositReloadCount + 1;
                            $ticketDepositReloadAmount = $ticketDepositReloadAmount + $ticketamount;
                            $dateupdated = $value['DateUpdated'];
                        } else if ($ticketstatus == 4) {
                            $ticketstatus = 'Encashed';
                            $ticketsEncashedCount = $ticketsEncashedCount + 1;
                            $ticketsEncashedTotal = $ticketsEncashedTotal + $ticketamount;
                            $dateupdated = $value['DateEncashed'];
                        } else {
                            $ticketstatus = '';
                            $dateupdated = $value['DateUpdated'];
                        }

                        $vticketdatecreated = new DateTime($ticketdatecreated);
                        $zticketdatecreated = date(date_format($vticketdatecreated, 'Y-m-d H:i:s'));
                        $vticketdateexpiry = new DateTime($ticketdateexpiry);
                        $zticketdateexpiry = date(date_format($vticketdateexpiry, 'Y-m-d'));
                        $vdateupdated = new DateTime($dateupdated);
                        $zdateupdated = date(date_format($vdateupdated, 'Y-m-d H:i:s'));

                        $record = array(
                            'SiteName' => $sitename,
                            'SiteCode' => $sitecode,
                            'TerminalName' => str_replace("TERMINAL", $sitecode, $ticketterminalname),
                            'VoucherType' => $ticketvouchertypeid,
                            'VoucherCode' => $ticketvouchercode,
                            'Status' => $ticketstatus,
                            'Amount' => $ticketamount,
                            'DateCreated' => $zticketdatecreated,
                            'DateExpired' => $zticketdateexpiry,
                            'Source' => $ticketsource,
                            'IsCreditable' => $ticketiscreditable,
                            'DateUpdated' => $zdateupdated,
                            'TotalAmount' => number_format(doubleval($totalAmount), 2)
                        );

                        array_push($rawData, $record);
                    }
                }
            }

            Yii::app()->session['rawData'] = $rawData;
            $display = 'block';
            Yii::app()->session['display'] = $display;
            Yii::app()->session['PrintedTicketsCount'] = $ticketTotalCount;
            Yii::app()->session['ActiveTicketsCount'] = $ticketActiveCount;
            Yii::app()->session['DepositReloadTicketsCount'] = $ticketDepositReloadCount;
            Yii::app()->session['TotalAmount'] = $totalAmount;
            Yii::app()->session['EncashedTicketsCount'] = $ticketsEncashedCount;
            Yii::app()->session['VoidTicketsCount'] = $ticketVoidCount;
            Yii::app()->session['PrintedTicketsTotal'] = number_format($ticketTotalAmount, 2);
            Yii::app()->session['ActiveTicketsTotal'] = number_format($ticketActiveAmount, 2);
            Yii::app()->session['DepositReloadTicketsTotal'] = number_format($ticketDepositReloadAmount, 2);
            Yii::app()->session['EncashedTicketsTotal'] = number_format($ticketsEncashedTotal, 2);
            Yii::app()->session['VoidTicketsTotal'] = number_format($ticketVoidAmount, 2);
            $this->render('ticket', array('model' => $_TransPerCutOff));
        }
    }
    public function actionCheckDateRange()
    {
        $dateFrom   = $_POST['dateFrom'];
        $dateTo     = $_POST['dateTo'];
        
        $dateDiff = round(abs(strtotime($dateFrom) - strtotime($dateTo)) / 86000);
       
        echo $dateDiff;
    }
}

?>
