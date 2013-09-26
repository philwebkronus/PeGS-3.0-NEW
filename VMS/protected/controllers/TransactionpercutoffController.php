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
            $display = 'none';
            Yii::app()->session['display'] = $display;

            if (isset($_POST['TransactionpercutoffForm'])) {
                $model->attributes = $_POST['TransactionpercutoffForm'];
                $data = $model->attributes;
                Yii::app()->session['transactiondate'] = $data['transactiondate'] . " " . Yii::app()->params->cutofftimestart;
                Yii::app()->session['site'] = $data['site'];
                Yii::app()->session['vouchertype'] = $data['vouchertype'];
                Yii::app()->session['status'] = $data['status'];

                $transdate = Yii::app()->session['transactiondate'];
                $site = Yii::app()->session['site'];
                $vouchertype = Yii::app()->session['vouchertype'];
                $status = Yii::app()->session['status'];

                if ($site == 'All') {
                    $site == 'All';
                } else {
                    $sitearr = $model->getTerminalNamesUsingSiteID($site);
                    if(!empty($sitearr))
                    {
                    foreach ($sitearr as $key => $trans) {
                        $vsiteID[$key] = $trans['TerminalID'];
                    }

                    $site = implode(",", $vsiteID);
                    }
                }
                $rawData = array();

                if ($vouchertype == 1) {
                    $ticketarr = $model->getTicketList($site, $transdate, 3);
                } elseif ($vouchertype == 2) {
                    $couponarr = $model->getCouponList($site, $transdate, 3);

                    if (!empty($couponarr)) {
                        foreach ($couponarr as $value) {
                            $couponvoucherid = $value['VoucherID'];
                            $couponcouchertypeid = $value['VoucherTypeID'];
                            $couponvouchercode = $value['VoucherCode'];
                            $couponstatus = $value['Status'];
                            $couponterminalid = $value['TerminalID'];
                            $couponamount = $value['Amount'];
                            $coupondatecreated = $value['DateCreated'];
                            $coupondateexpiry = $value['DateExpiry'];
                            $couponsource = $value['Source'];
                            $couponiscreditable = $value['LoyaltyCreditable'];

                            switch ($couponstatus) {
                                case 0:
                                    $couponstatus = 'InActive';
                                    break;
                                case 1:
                                    $couponstatus = 'Active';
                                    break;
                                case 2:
                                    $couponstatus = 'Void';
                                    break;
                                case 3:
                                    $couponstatus = 'Used';
                                    break;
                                case 4:
                                    $couponstatus = 'Claimed';
                                    break;
                                case 5:
                                    $couponstatus = 'Reimbursed';
                                    break;
                                case 6:
                                    $couponstatus = 'Expired';
                                    break;
                                case 7:
                                    $couponstatus = 'Cancelled';
                                    break;
                            }

                            switch ($couponsource) {
                                case 1:
                                    $couponsource = 'KAPI';
                                    break;
                                case 2:
                                    $couponsource = 'EGM';
                                    break;
                                case 3:
                                    $couponsource = 'Cashier';
                                    break;
                                case 4:
                                    $couponsource = 'VMS';
                                    break;
                            }

                            $siteid = $model->getSiteIDfromterminals($couponterminalid);

                            foreach ($siteid as $rowz) {
                                $siteid = $rowz['SiteID'];
                            }

                            $site = $model->getSiteName($siteid);

                            foreach ($site as $row) {
                                $sitename = $row['SiteName'];
                            }

                            $terminal = $model->getTerminalNamesUsingTerminalID($couponterminalid);

                            foreach ($terminal as $rowz2) {
                                $terminalname = $rowz2['TerminalName'];
                            }

                            $record = array(
                                'VoucherType' => $couponcouchertypeid,
                                'VoucherCode' => $couponvouchercode,
                                'Status' => $couponstatus,
                                'SiteName' => $sitename,
                                'TerminalName' => $terminalname,
                                'Amount' => $couponamount,
                                'DateCreated' => $coupondatecreated,
                                'DateExpired' => $coupondateexpiry,
                                'Source' => $couponsource,
                                'IsCreditable' => $couponiscreditable,
                            );

                            array_push($rawData, $record);
                        }
                    }
                } else {
                    $couponarr = $model->getCouponList($site, $transdate, 3);

                    if (!empty($ticketarr)) {
                        foreach ($ticketarr as $value2) {
                            $ticketvoucherid = $value2['VoucherID'];
                            $ticketvouchertypeid = $value2['VoucherTypeID'];
                            $ticketvouchercode = $value2['VoucherCode'];
                            $ticketstatus = $value2['Status'];
                            $ticketterminalid = $value2['TerminalID'];
                            $ticketamount = $value2['Amount'];
                            $ticketdatecreated = $value2['DateCreated'];
                            $ticketdateexpiry = $value2['DateExpiry'];
                            $ticketsource = $value2['Source'];
                            $ticketiscreditable = $value2['LoyaltyCreditable'];

                            switch ($ticketstatus) {
                                case 0:
                                    $ticketstatus = 'InActive';
                                    break;
                                case 1:
                                    $ticketstatus = 'Active';
                                    break;
                                case 2:
                                    $ticketstatus = 'Void';
                                    break;
                                case 3:
                                    $ticketstatus = 'Used';
                                    break;
                                case 4:
                                    $ticketstatus = 'Claimed';
                                    break;
                                case 5:
                                    $ticketstatus = 'Reimbursed';
                                    break;
                                case 6:
                                    $ticketstatus = 'Expired';
                                    break;
                                case 7:
                                    $ticketstatus = 'Cancelled';
                                    break;
                            }

                            switch ($ticketsource) {
                                case 1:
                                    $ticketsource = 'KAPI';
                                    break;
                                case 2:
                                    $ticketsource = 'EGM';
                                    break;
                                case 3:
                                    $ticketsource = 'Cashier';
                                    break;
                                case 4:
                                    $ticketsource = 'VMS';
                                    break;
                            }

                            $siteid = $model->getSiteIDfromterminals($ticketterminalid);

                            foreach ($siteid as $rowz) {
                                $siteid = $rowz['SiteID'];
                            }

                            $site2 = $model->getSiteName($siteid);

                            foreach ($site2 as $row2) {
                                $sitename2 = $row2['SiteName'];
                            }

                            $terminal2 = $model->getTerminalNamesUsingTerminalID($ticketterminalid);

                            foreach ($terminal2 as $rowz2) {
                                $terminalname2 = $rowz2['TerminalName'];
                            }

                            $record2 = array(
                                'VoucherType' => $ticketvouchertypeid,
                                'VoucherCode' => $ticketvouchercode,
                                'Status' => $ticketstatus,
                                'SiteName' => $sitename2,
                                'TerminalName' => $terminalname2,
                                'Amount' => $ticketamount,
                                'DateCreated' => $ticketdatecreated,
                                'DateExpired' => $ticketdateexpiry,
                                'Source' => $ticketsource,
                                'IsCreditable' => $ticketiscreditable,
                            );

                            array_push($rawData, $record2);
                        }
                    }

                    $ticketarr = $model->getTicketList($site, $transdate, 3);

                    if (!empty($couponarr)) {
                        foreach ($couponarr as $value) {
                            $couponvoucherid = $value['VoucherID'];
                            $couponcouchertypeid = $value['VoucherTypeID'];
                            $couponvouchercode = $value['VoucherCode'];
                            $couponstatus = $value['Status'];
                            $couponterminalid = $value['TerminalID'];
                            $couponamount = $value['Amount'];
                            $coupondatecreated = $value['DateCreated'];
                            $coupondateexpiry = $value['DateExpiry'];
                            $couponsource = $value['Source'];
                            $couponiscreditable = $value['LoyaltyCreditable'];

                            switch ($couponstatus) {
                                case 0:
                                    $couponstatus = 'InActive';
                                    break;
                                case 1:
                                    $couponstatus = 'Active';
                                    break;
                                case 2:
                                    $couponstatus = 'Void';
                                    break;
                                case 3:
                                    $couponstatus = 'Used';
                                    break;
                                case 4:
                                    $couponstatus = 'Claimed';
                                    break;
                                case 5:
                                    $couponstatus = 'Reimbursed';
                                    break;
                                case 6:
                                    $couponstatus = 'Expired';
                                    break;
                                case 7:
                                    $couponstatus = 'Cancelled';
                                    break;
                            }

                            switch ($couponsource) {
                                case 1:
                                    $couponsource = 'KAPI';
                                    break;
                                case 2:
                                    $couponsource = 'EGM';
                                    break;
                                case 3:
                                    $couponsource = 'Cashier';
                                    break;
                                case 4:
                                    $couponsource = 'VMS';
                                    break;
                            }

                            $siteid = $model->getSiteIDfromterminals($couponterminalid);

                            foreach ($siteid as $rowz) {
                                $siteid = $rowz['SiteID'];
                            }

                            $site = $model->getSiteName($siteid);

                            foreach ($site as $row) {
                                $sitename = $row['SiteName'];
                            }

                            $terminal = $model->getTerminalNamesUsingTerminalID($couponterminalid);

                            foreach ($terminal as $rowz2) {
                                $terminalname = $rowz2['TerminalName'];
                            }

                            $record = array(
                                'VoucherType' => $couponcouchertypeid,
                                'VoucherCode' => $couponvouchercode,
                                'Status' => $couponstatus,
                                'SiteName' => $sitename,
                                'TerminalName' => $terminalname,
                                'Amount' => $couponamount,
                                'DateCreated' => $coupondatecreated,
                                'DateExpired' => $coupondateexpiry,
                                'Source' => $couponsource,
                                'IsCreditable' => $couponiscreditable,
                            );

                            array_push($rawData, $record);
                        }
                    }

                    if (!empty($ticketarr)) {
                        foreach ($ticketarr as $value2) {
                            $ticketvoucherid = $value2['VoucherID'];
                            $ticketvouchertypeid = $value2['VoucherTypeID'];
                            $ticketvouchercode = $value2['VoucherCode'];
                            $ticketstatus = $value2['Status'];
                            $ticketterminalid = $value2['TerminalID'];
                            $ticketamount = $value2['Amount'];
                            $ticketdatecreated = $value2['DateCreated'];
                            $ticketdateexpiry = $value2['DateExpiry'];
                            $ticketsource = $value2['Source'];
                            $ticketiscreditable = $value2['LoyaltyCreditable'];

                            switch ($ticketstatus) {
                                case 0:
                                    $ticketstatus = 'InActive';
                                    break;
                                case 1:
                                    $ticketstatus = 'Active';
                                    break;
                                case 2:
                                    $ticketstatus = 'Void';
                                    break;
                                case 3:
                                    $ticketstatus = 'Used';
                                    break;
                                case 4:
                                    $ticketstatus = 'Claimed';
                                    break;
                                case 5:
                                    $ticketstatus = 'Reimbursed';
                                    break;
                                case 6:
                                    $ticketstatus = 'Expired';
                                    break;
                                case 7:
                                    $ticketstatus = 'Cancelled';
                                    break;
                            }

                            switch ($ticketsource) {
                                case 1:
                                    $ticketsource = 'KAPI';
                                    break;
                                case 2:
                                    $ticketsource = 'EGM';
                                    break;
                                case 3:
                                    $ticketsource = 'Cashier';
                                    break;
                                case 4:
                                    $ticketsource = 'VMS';
                                    break;
                            }

                            $siteid = $model->getSiteIDfromterminals($ticketterminalid);

                            foreach ($siteid as $rowz) {
                                $siteid = $rowz['SiteID'];
                            }

                            $site2 = $model->getSiteName($siteid);

                            foreach ($site2 as $row2) {
                                $sitename2 = $row2['SiteName'];
                            }

                            $terminal2 = $model->getTerminalNamesUsingTerminalID($ticketterminalid);

                            foreach ($terminal2 as $rowz2) {
                                $terminalname2 = $rowz2['TerminalName'];
                            }

                            $record2 = array(
                                'VoucherType' => $ticketvouchertypeid,
                                'VoucherCode' => $ticketvouchercode,
                                'Status' => $ticketstatus,
                                'SiteName' => $sitename2,
                                'TerminalName' => $terminalname2,
                                'Amount' => $ticketamount,
                                'DateCreated' => $ticketdatecreated,
                                'DateExpired' => $ticketdateexpiry,
                                'Source' => $ticketsource,
                                'IsCreditable' => $ticketiscreditable,
                            );

                            array_push($rawData, $record2);
                        }
                    }
                }

                Yii::app()->session['rawData'] = $rawData;

                $display = 'block';
                Yii::app()->session['display'] = $display;
            } else {
                $transdate = Yii::app()->session['transactiondate'];
                $site = Yii::app()->session['site'];
                $vouchertype = Yii::app()->session['vouchertype'];
                $status = Yii::app()->session['status'];

                if (isset($transdate) && isset($site) && isset($vouchertype) && isset($status)) {
                    if ($site == 'All') {
                        $site == 'All';
                    } else {
                        $sitearr = $model->getTerminalNamesUsingSiteID($site);

                        foreach ($sitearr as $key => $trans) {
                            $vsiteID[$key] = $trans['TerminalID'];
                        }

                        $site = implode(",", $vsiteID);
                    }
                    $rawData = array();

                    if ($vouchertype == 1) {
                        $ticketarr = $model->getTicketList($site, $transdate, $status);
                    } elseif ($vouchertype == 2) {
                        $couponarr = $model->getCouponList($site, $transdate, $status);

                        if (!empty($couponarr)) {
                            foreach ($couponarr as $value) {
                                $couponvoucherid = $value['VoucherID'];
                                $couponcouchertypeid = $value['VoucherTypeID'];
                                $couponvouchercode = $value['VoucherCode'];
                                $couponstatus = $value['Status'];
                                $couponterminalid = $value['TerminalID'];
                                $couponamount = $value['Amount'];
                                $coupondatecreated = $value['DateCreated'];
                                $coupondateexpiry = $value['DateExpiry'];
                                $couponsource = $value['Source'];
                                $couponiscreditable = $value['LoyaltyCreditable'];

                                switch ($couponstatus) {
                                    case 0:
                                        $couponstatus = 'InActive';
                                        break;
                                    case 1:
                                        $couponstatus = 'Active';
                                        break;
                                    case 2:
                                        $couponstatus = 'Void';
                                        break;
                                    case 3:
                                        $couponstatus = 'Used';
                                        break;
                                    case 4:
                                        $couponstatus = 'Claimed';
                                        break;
                                    case 5:
                                        $couponstatus = 'Reimbursed';
                                        break;
                                    case 6:
                                        $couponstatus = 'Expired';
                                        break;
                                    case 7:
                                        $couponstatus = 'Cancelled';
                                        break;
                                }

                                switch ($couponsource) {
                                    case 1:
                                        $couponsource = 'KAPI';
                                        break;
                                    case 2:
                                        $couponsource = 'EGM';
                                        break;
                                    case 3:
                                        $couponsource = 'Cashier';
                                        break;
                                    case 4:
                                        $couponsource = 'VMS';
                                        break;
                                }

                                $siteid = $model->getSiteIDfromterminals($couponterminalid);

                                foreach ($siteid as $rowz) {
                                    $siteid = $rowz['SiteID'];
                                }

                                $site = $model->getSiteName($siteid);

                                foreach ($site as $row) {
                                    $sitename = $row['SiteName'];
                                }

                                $terminal = $model->getTerminalNamesUsingTerminalID($couponterminalid);

                                foreach ($terminal as $rowz2) {
                                    $terminalname = $rowz2['TerminalName'];
                                }

                                $record = array(
                                    'VoucherType' => $couponcouchertypeid,
                                    'VoucherCode' => $couponvouchercode,
                                    'Status' => $couponstatus,
                                    'SiteName' => $sitename,
                                    'TerminalName' => $terminalname,
                                    'Amount' => $couponamount,
                                    'DateCreated' => $coupondatecreated,
                                    'DateExpired' => $coupondateexpiry,
                                    'Source' => $couponsource,
                                    'IsCreditable' => $couponiscreditable,
                                );

                                array_push($rawData, $record);
                            }
                        }
                    } else {
                        $couponarr = $model->getCouponList($site, $transdate, $status);

                        if (!empty($ticketarr)) {
                            foreach ($ticketarr as $value2) {
                                $ticketvoucherid = $value2['VoucherID'];
                                $ticketvouchertypeid = $value2['VoucherTypeID'];
                                $ticketvouchercode = $value2['VoucherCode'];
                                $ticketstatus = $value2['Status'];
                                $ticketterminalid = $value2['TerminalID'];
                                $ticketamount = $value2['Amount'];
                                $ticketdatecreated = $value2['DateCreated'];
                                $ticketdateexpiry = $value2['DateExpiry'];
                                $ticketsource = $value2['Source'];
                                $ticketiscreditable = $value2['LoyaltyCreditable'];

                                switch ($ticketstatus) {
                                    case 0:
                                        $ticketstatus = 'InActive';
                                        break;
                                    case 1:
                                        $ticketstatus = 'Active';
                                        break;
                                    case 2:
                                        $ticketstatus = 'Void';
                                        break;
                                    case 3:
                                        $ticketstatus = 'Used';
                                        break;
                                    case 4:
                                        $ticketstatus = 'Claimed';
                                        break;
                                    case 5:
                                        $ticketstatus = 'Reimbursed';
                                        break;
                                    case 6:
                                        $ticketstatus = 'Expired';
                                        break;
                                    case 7:
                                        $ticketstatus = 'Cancelled';
                                        break;
                                }

                                switch ($ticketsource) {
                                    case 1:
                                        $ticketsource = 'KAPI';
                                        break;
                                    case 2:
                                        $ticketsource = 'EGM';
                                        break;
                                    case 3:
                                        $ticketsource = 'Cashier';
                                        break;
                                    case 4:
                                        $ticketsource = 'VMS';
                                        break;
                                }

                                $siteid = $model->getSiteIDfromterminals($ticketterminalid);

                                foreach ($siteid as $rowz) {
                                    $siteid = $rowz['SiteID'];
                                }

                                $site2 = $model->getSiteName($siteid);

                                foreach ($site2 as $row2) {
                                    $sitename2 = $row2['SiteName'];
                                }

                                $terminal2 = $model->getTerminalNamesUsingTerminalID($ticketterminalid);

                                foreach ($terminal2 as $rowz2) {
                                    $terminalname2 = $rowz2['TerminalName'];
                                }

                                $record2 = array(
                                    'VoucherType' => $ticketvouchertypeid,
                                    'VoucherCode' => $ticketvouchercode,
                                    'Status' => $ticketstatus,
                                    'SiteName' => $sitename2,
                                    'TerminalName' => $terminalname2,
                                    'Amount' => $ticketamount,
                                    'DateCreated' => $ticketdatecreated,
                                    'DateExpired' => $ticketdateexpiry,
                                    'Source' => $ticketsource,
                                    'IsCreditable' => $ticketiscreditable,
                                );

                                array_push($rawData, $record2);
                            }
                        }

                        $ticketarr = $model->getTicketList($site, $transdate, 3);

                        if (!empty($couponarr)) {
                            foreach ($couponarr as $value) {
                                $couponvoucherid = $value['VoucherID'];
                                $couponcouchertypeid = $value['VoucherTypeID'];
                                $couponvouchercode = $value['VoucherCode'];
                                $couponstatus = $value['Status'];
                                $couponterminalid = $value['TerminalID'];
                                $couponamount = $value['Amount'];
                                $coupondatecreated = $value['DateCreated'];
                                $coupondateexpiry = $value['DateExpiry'];
                                $couponsource = $value['Source'];
                                $couponiscreditable = $value['LoyaltyCreditable'];

                                switch ($couponstatus) {
                                    case 0:
                                        $couponstatus = 'InActive';
                                        break;
                                    case 1:
                                        $couponstatus = 'Active';
                                        break;
                                    case 2:
                                        $couponstatus = 'Void';
                                        break;
                                    case 3:
                                        $couponstatus = 'Used';
                                        break;
                                    case 4:
                                        $couponstatus = 'Claimed';
                                        break;
                                    case 5:
                                        $couponstatus = 'Reimbursed';
                                        break;
                                    case 6:
                                        $couponstatus = 'Expired';
                                        break;
                                    case 7:
                                        $couponstatus = 'Cancelled';
                                        break;
                                }

                                switch ($couponsource) {
                                    case 1:
                                        $couponsource = 'KAPI';
                                        break;
                                    case 2:
                                        $couponsource = 'EGM';
                                        break;
                                    case 3:
                                        $couponsource = 'Cashier';
                                        break;
                                    case 4:
                                        $couponsource = 'VMS';
                                        break;
                                }

                                $siteid = $model->getSiteIDfromterminals($couponterminalid);

                                foreach ($siteid as $rowz) {
                                    $siteid = $rowz['SiteID'];
                                }

                                $site = $model->getSiteName($siteid);

                                foreach ($site as $row) {
                                    $sitename = $row['SiteName'];
                                }

                                $terminal = $model->getTerminalNamesUsingTerminalID($couponterminalid);

                                foreach ($terminal as $rowz2) {
                                    $terminalname = $rowz2['TerminalName'];
                                }

                                $record = array(
                                    'VoucherType' => $couponcouchertypeid,
                                    'VoucherCode' => $couponvouchercode,
                                    'Status' => $couponstatus,
                                    'SiteName' => $sitename,
                                    'TerminalName' => $terminalname,
                                    'Amount' => $couponamount,
                                    'DateCreated' => $coupondatecreated,
                                    'DateExpired' => $coupondateexpiry,
                                    'Source' => $couponsource,
                                    'IsCreditable' => $couponiscreditable,
                                );

                                array_push($rawData, $record);
                            }
                        }

                        if (!empty($ticketarr)) {
                            foreach ($ticketarr as $value2) {
                                $ticketvoucherid = $value2['VoucherID'];
                                $ticketvouchertypeid = $value2['VoucherTypeID'];
                                $ticketvouchercode = $value2['VoucherCode'];
                                $ticketstatus = $value2['Status'];
                                $ticketterminalid = $value2['TerminalID'];
                                $ticketamount = $value2['Amount'];
                                $ticketdatecreated = $value2['DateCreated'];
                                $ticketdateexpiry = $value2['DateExpiry'];
                                $ticketsource = $value2['Source'];
                                $ticketiscreditable = $value2['LoyaltyCreditable'];

                                $siteid = $model->getSiteIDfromterminals($ticketterminalid);

                                foreach ($siteid as $rowz) {
                                    $siteid = $rowz['SiteID'];
                                }

                                $site2 = $model->getSiteName($siteid);

                                foreach ($site2 as $row2) {
                                    $sitename2 = $row2['SiteName'];
                                }

                                $terminal2 = $model->getTerminalNamesUsingTerminalID($ticketterminalid);

                                foreach ($terminal2 as $rowz2) {
                                    $terminalname2 = $rowz2['TerminalName'];
                                }

                                $record2 = array(
                                    'VoucherType' => $ticketvouchertypeid,
                                    'VoucherCode' => $ticketvouchercode,
                                    'Status' => $ticketstatus,
                                    'SiteName' => $sitename2,
                                    'TerminalName' => $terminalname2,
                                    'Amount' => $ticketamount,
                                    'DateCreated' => $ticketdatecreated,
                                    'DateExpired' => $ticketdateexpiry,
                                    'Source' => $ticketsource,
                                    'IsCreditable' => $ticketiscreditable,
                                );

                                array_push($rawData, $record2);
                            }
                        }
                    }

                    Yii::app()->session['rawData'] = $rawData;

                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                } else {
                    $rawData = array();
                    Yii::app()->session['rawData'] = $rawData;

                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                }
            }

            $this->render('index', array('model' => $model));
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
                /* 'id'=>'siteconversion-grid',
                  'sort'=>array(
                  'attributes'=>array('DateCreated','DateExpiry','Status'),
                  'defaultOrder'=>array('DateCreated'=>true, 'DateExpiry'=>false),
                  ), */
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
            $cdate = date('Y_m_d');
            //code to download the data of report in the excel format
            $fn = "TransactionPerCutOff_".$cdate.".xls";
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
