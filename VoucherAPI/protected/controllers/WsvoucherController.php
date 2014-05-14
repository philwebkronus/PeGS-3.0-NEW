<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WsVoucherController
 *
 * @author elperez
 */
class WsvoucherController extends Controller {

    //Source came from KAPI 

    CONST SOURCE_KAPI = 1;
    //Source came from EGM
    CONST SOURCE_EGM = 2;
    //Source came from Kronus Cashier
    CONST SOURCE_CASHIER = 3;
    CONST CASH_TYPE_BILL = 3;
    CONST VOUCHER_USED_STATUS = 3;
    CONST TICKET = 1;
    CONST COUPON = 2;
    CONST PURPOSE_PAYOUT_TICKET = 1;
    CONST PURPOSE_VOID = 2;
    CONST PURPOSE_CANCELLED = 3;
    CONST STATUS_TICKET_ACTIVE = 1;
    CONST STATUS_TICKET_VOID = 2;
    CONST STATUS_TICKET_USED = 3;
    CONST STATUS_TICKET_ENCASHMENT = 4;
    CONST STATUS_TICKET_CANCELLED = 5;
    CONST STATUS_TICKET_REIMBURSED = 6;

    /**
     * @author Edson Perez
     * @datecreated 09/12/13
     * @purpose verify whether a coupon, ticket and tracking id is valid and can be used to transact
     */
    public function actionVerify() {
        Yii::import('application.controllers.*');
        $request = $this->_readJsonRequest();

        $commonController = new CommonController();
        $voucherTypeID = CommonController::VOUCHER_TYPE_COUPON;
        $trackingid = "";
        $voucherCode = "";
        $dateCreated = "";
        $loyaltyCreditable = 0;
        $amount = 0;
        $result = array();
        $status = 0;
        $transMsg = "";

        if (isset($request['aid']) && is_numeric($request['aid']) && isset($request['source']) && is_numeric($request['source'])) {
            $AID = trim($request['aid']);
            $source = trim($request['source']);

            //will be called for verification / fulfillment of transaction
            if (isset($request['trackingid']) && ctype_alnum($request['trackingid']) &&
                    strlen($request['trackingid']) > 0) {

                $trackingid = trim($request['trackingid']);

                switch ($source) {
                    case self::SOURCE_EGM:
                        $errorCode = 2;
                        $transMsg = "Source is invalid.";
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated);
                        break;
                    case self::SOURCE_CASHIER:
                        $_couponModel = new CouponModel();

                        //verify the tracking id
                        $trackResult = $_couponModel->verifyCouponTransaction($trackingid);

                        if ((int) $trackResult['ctrtracking'] > 0) {
                            $errorCode = 0;
                            $status = 1;
                            $transMsg = "Transaction Approved";
                            $couponResult = $_couponModel->getCouponDetails($trackingid);

                            $loyaltyCreditable = $couponResult[0]['IsCreditable'];
                            $voucherCode = $couponResult[0]['CouponCode'];
                            $amount = $couponResult[0]['CouponCode'];
                            $dateCreated = $couponResult[0]['CouponCode'];
                            $expirationDate = $couponResult[0]['ValidToDate'];


                            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate, $voucherTypeID);
                        } else {
                            $errorCode = 1;
                            $transMsg = "Tracking ID not found.";
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        }
                        break;
                    case self::SOURCE_KAPI;
                        $errorCode = 2;
                        $transMsg = "Source is invalid.";
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated);
                        break;
                    default :
                        $errorCode = 2;
                        $transMsg = "Source is invalid.";
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated);
                        break;
                }
                $transMsg = "Source is invalid.";
                $details = "Verify : " . $transMsg;
                AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, $status);
            }

            //will be called for validation of coupons
            else if (isset($request['vouchercode']) && ctype_alnum($request['vouchercode']) && strlen($request['vouchercode']) > 0) {
                $voucherCode = trim($request['vouchercode']);

                switch ($source) {
                    case self::SOURCE_EGM:
                        //todo
                        break;
                    case self::SOURCE_CASHIER:
                        $result = $commonController->verifyCoupon($AID, $voucherCode, $source, $trackingid, self::COUPON);
                        $transMsg = $result['TransMsg'];
                        break;
                    case self::SOURCE_KAPI;
                        //todo
                        break;
                    default :
                        $errorCode = 2;
                        $transMsg = "Source is invalid.";
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        break;
                }
            } else {
                $transMsg = 'Invalid input parameters.';
                $errorCode = 3;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
            }
        } else {
            $transMsg = 'Invalid input parameters.';
            $errorCode = 3;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
        }

        $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher" => $result)));
    }

    /**
     * @author Edson Perez
     * @datecreated 09-16-13
     * @purpose generic api method of using whether coupon or ticket
     */
    public function actionUse() {
        $request = $this->_readJsonRequest();

        Yii::import('application.controllers.*');
        $commonController = new CommonController();

        if (isset($request["vouchercode"]) && ctype_alnum($request["vouchercode"]) && isset($request["aid"]) && is_numeric($request["aid"]) && isset($request['trackingid']) && ctype_alnum($request['trackingid']) && isset($request['terminalid']) && is_numeric($request['terminalid']) && isset($request['source']) && is_numeric($request['source']) && isset($request['mid']) && is_numeric($request['mid']) && isset($request['siteid']) && is_numeric($request['siteid'])) {
            $voucherCode = trim($request["vouchercode"]);
            $AID = trim($request["aid"]);
            $trackingID = trim($request['trackingid']);
            $terminalID = trim($request['terminalid']);
            $source = trim($request['source']);
            $MID = trim($request['mid']);
            $siteID = trim($request['siteid']);

            switch ($source) {
                case self::SOURCE_EGM:
                    //todo
                    break;
                case self::SOURCE_CASHIER:
                    $result = $commonController->useCoupon($voucherCode, $AID, $trackingID, $terminalID, $source, self::COUPON, $MID, $siteID);
                    break;
                case self::SOURCE_KAPI;
                    //todo
                    break;
                default :
                    $errorCode = 2;
                    $transMsg = "Source is invalid.";
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $result = $commonController->getUseRetMsg(2, $transMsg, $errorCode);
                    break;
            }
        } else {

            $transMsg = 'Invalid input parameters.';
            $errorCode = 3;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $result = $commonController->getUseRetMsg(2, $transMsg, $errorCode);
        }

        $this->_sendResponse(200, CJSON::encode(array("Use" => $result)));
    }

    public function actionVerifyTicket() {
        Yii::import('application.controllers.*');
        $request = $this->_readJsonRequest();

        $commonController = new CommonController();
        $_ticketModel = new TicketModel();
        $_couponModel = new CouponModel();
        $_terminalsModel = new TerminalsModel();
        $_memberCardsModel = new MemberCardsModel();
        $vtcode = "";
        $transMsg = "";
        $errorCode = "";
        $vouchertype = "";
        $trackingid = "";
        $voucherCode = "";
        $dateCreated = "";
        $expirationDate = "";
        $validFromDate = "";
        $validToDate = "";
        $source = "";
        $loyaltyCreditable = "";
        $amount = 0;
        $result = array();
        $status = 0;

        if (isset($request['Source']) && is_numeric($request['Source'])) {

            $source = trim($request['Source']);
            //check source
            switch ($source) {
                case self::SOURCE_CASHIER :
                    $transMsg = 'Ticket could not be used in cashier.';
                    $errorCode = 37;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                    break;
                case self::SOURCE_KAPI || self::SOURCE_EGM:
                    //will be called for verification / fulfillment of transaction
                    if (isset($request['TrackingID']) && ctype_alnum($request['TrackingID']) &&
                            strlen($request['TrackingID']) > 0) {

                        $trackingid = trim($request['TrackingID']);
                        $ticketData = $_ticketModel->getTicketDetailsByTrackingId($trackingid);

                        if (!empty($ticketData)) {
                            $date_now = date('Y-m-d');
                            $date1 = date($ticketData['ValidFromDate']);
                            $date2 = date($ticketData['ValidToDate']);
                            $expirationDate = $ticketData['ValidToDate'];
                            $validFromDate = strtotime($date1);
                            $validToDate = strtotime($date2);
                            $dateToday = strtotime($date_now);
                            $ticketstatus = $ticketData['Status'];
                            $amount = $ticketData['Amount'];
                            $vtcode = $ticketData['TicketCode'];
                            $dateCreated = $ticketData['DateCreated'];
                            $loyaltyCreditable = $ticketData['IsCreditable'];

                            if ($amount == '' || empty($amount)) {
                                $transMsg = 'Amount is null.';
                                $errorCode = 45;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $vtcode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                            } else {
                                $vouchertype = self::TICKET;
                                if (Yii::app()->params['expirationChecking'] == 'enabled') {
                                    $date = date('Y-m-d');
                                    $date_now = $date;

                                    if ($validToDate >= $dateToday) {
                                        list($errorCode, $transMsg) = $this->_voucherStatus($ticketstatus, $vouchertype);
                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $vtcode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                                    } else {
                                        $transMsg = 'Ticket is already expired.';
                                        $errorCode = 26;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $vtcode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                                    }
                                } else {
                                    list($errorCode, $transMsg) = $this->_voucherStatus($ticketstatus, $vouchertype);
                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $vtcode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                                }
                            }
                            $this->_sendResponse(200, CommonController::verifyTicketResponse($vtcode, $amount, $dateCreated, $vouchertype, $loyaltyCreditable, $transMsg, $errorCode));
                            exit;
                        } else {
                            $amount = 0;
                            $vouchertype = "";
                            $loyaltyCreditable = "";
                            $dateCreated = "";
                            $transMsg = 'Tracking ID not found.';
                            $errorCode = 1;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $vtcode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                        }
                    } else {
                        //will be called for validation of tickets
                        if (isset($request['VoucherTicketBarcode']) && ctype_alnum($request['VoucherTicketBarcode']) && strlen($request['VoucherTicketBarcode']) > 0 && isset($request['MembershipCardNumber']) && ctype_alnum($request['MembershipCardNumber']) && strlen($request['MembershipCardNumber']) > 0) {
                            $voucherCode = trim($request['VoucherTicketBarcode']);
                            $cardNumber = trim($request['MembershipCardNumber']);
                            if (isset($request['TerminalName']) && ctype_alnum($request['TerminalName']) &&
                                    strlen($request['TerminalName']) > 0) {
                                $terminalName = trim($request['TerminalName']);
                                if (strlen($voucherCode) >= 7) {
                                    $terminalCode = Yii::app()->params['sitePrefix'] . $terminalName;
                                    $terminalID = $_terminalsModel->getTerminalIDfromterminals($terminalCode);
                                    if (!empty($terminalID)) {
                                        $MID = $_memberCardsModel->getMIDByCardNumber($cardNumber);

                                        if (!empty($MID)) {
                                            $MID = $MID['MID'];

                                            $egmSessionsModel = new EGMSessionsModel();
                                            $isTerminalMidMatched = $egmSessionsModel->isTerminalAndMIDMatched($terminalID, $MID);
                                            if($isTerminalMidMatched == 0) {
                                                $terminalID = $_terminalsModel->getTerminalIDfromterminals($terminalCode.'vip');
                                                $isTerminalMidMatched = $egmSessionsModel->isTerminalAndMIDMatched($terminalID, $MID);
                                            }
                                            
                                            if ($isTerminalMidMatched > 0) {
                                                
                                                $siteID = $_terminalsModel->getSiteIDfromterminals($terminalID);

                                                $ticketData = $_ticketModel->getTicketDataByCode($voucherCode);
                                                if (Yii::app()->params['siteChecking'] == 'enabled') {
                                                    $ticketData = $_ticketModel->getTicketDataBySiteID($voucherCode, $siteID);
                                                    if (empty($ticketData) || $ticketData = "") {
                                                        $vouchertype = self::TICKET;
                                                        $transMsg = 'Ticket is not allowed to be used for this site.';
                                                        $errorCode = 34;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg);
                                                    }
                                                }

                                                if (Yii::app()->params['terminalChecking'] == 'enabled') {
                                                    $ticketData = $_ticketModel->getTicketDataByTerminalID($voucherCode, $terminalID);
                                                    if (empty($ticketData) || $ticketData = "") {
                                                        $vouchertype = self::TICKET;
                                                        $transMsg = 'Ticket is not allowed to be used for this terminal.';
                                                        $errorCode = 47;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg);
                                                    }
                                                }

                                                if (Yii::app()->params['cardNumberChecking'] == 'enabled') {
                                                    $ticketData = $_ticketModel->getTicketDataByMID($voucherCode, $MID);
                                                    if (empty($ticketData) || $ticketData = "") {
                                                        $vouchertype = self::TICKET;
                                                        $transMsg = 'Ticket is associated with another Membership Card Number.';
                                                        $errorCode = 46;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg);
                                                    }
                                                }

                                                if (Yii::app()->params['expirationChecking'] == 'enabled') {
                                                    $ticketData = $_ticketModel->getTicketDataNotExpired($voucherCode);
                                                    $date_now = date('Y-m-d');
                                                    $date1 = date($ticketData['ValidFromDate']);
                                                    $date2 = date($ticketData['ValidToDate']);
                                                    $dateToday = strtotime($date_now);
                                                    $ticketstatus = $ticketData['Status'];

                                                    if (($validToDate >= $dateToday) || ($ticketstatus != CommonController::VOUCHER_STATUS_EXPIRED)) {
                                                        $ticketData = $ticketData;
                                                    } else {
                                                        $ticketData = "";
                                                        $vouchertype = self::TICKET;
                                                        $transMsg = 'Ticket is already expired.';
                                                        $errorCode = 26;
                                                    }
                                                }
                                                if (!empty($ticketData)) {
                                                    $amount = $ticketData['Amount'];
                                                    $voucherCode = $ticketData['TicketCode'];
                                                    if ($amount == '' || empty($amount)) {
                                                        $transMsg = 'Amount is null.';
                                                        $errorCode = 45;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    } else {
                                                        $date_now = date('Y-m-d');
                                                        $date1 = date($ticketData['ValidFromDate']);
                                                        $date2 = date($ticketData['ValidToDate']);
                                                        $expirationDate = $ticketData['ValidToDate'];
                                                        $validFromDate = strtotime($date1);
                                                        $validToDate = strtotime($date2);
                                                        $dateToday = strtotime($date_now);
                                                        $ticketstatus = $ticketData['Status'];
                                                        $amount = $ticketData['Amount'];
                                                        $dateCreated = $ticketData['DateCreated'];
                                                        $loyaltyCreditable = $ticketData['IsCreditable'];
                                                        $vouchertype = 1;
                                                        list($errorCode, $transMsg) = $this->_voucherStatus($ticketstatus, $vouchertype);
                                                    }
                                                    $details = "VerifyTicket : " . $transMsg;
                                                    $stat = 1;
                                                    AuditLog::logAPITransactions(9, $source, $details, $voucherCode, $trackingid, $stat);
                                                    $this->_sendResponse(200, CommonController::verifyTicketResponse($voucherCode, $amount, $dateCreated, $vouchertype, $loyaltyCreditable, $transMsg, $errorCode));
                                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                                                    exit;
                                                } else {
                                                    $vouchertype = self::TICKET;
                                                    $stat = 2;
                                                    $transMsg = "Invalid ticket.";
                                                    $details = "VerifyTicket : " . $transMsg;
                                                    AuditLog::logAPITransactions(9, $source, $details, $voucherCode, $trackingid, $stat);
                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable, $expirationDate);
                                                    $this->_sendResponse(200, CommonController::verifyTicketResponse($voucherCode, $amount, $dateCreated, $vouchertype, $loyaltyCreditable, $transMsg, $errorCode));
                                                    exit;
                                                }
                                            } else {
                                                $amount = 0;
                                                $vouchertype = "";
                                                $loyaltyCreditable = "";
                                                $dateCreated = "";
                                                $transMsg = 'Terminal ID and Card Number does not match in EGM session.';
                                                $errorCode = 44;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg);
                                            }
                                        } else {
                                            $amount = 0;
                                            $vouchertype = "";
                                            $loyaltyCreditable = "";
                                            $dateCreated = "";
                                            $transMsg = 'Invalid Card Number.';
                                            $errorCode = 19;
                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg);
                                        }
                                    } else {
                                        $amount = 0;
                                        $vouchertype = "";
                                        $loyaltyCreditable = "";
                                        $dateCreated = "";
                                        $transMsg = 'Terminal does not exists.';
                                        $errorCode = 29;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                                    }
                                } else {
                                    $amount = 0;
                                    $vouchertype = "";
                                    $loyaltyCreditable = "";
                                    $dateCreated = "";
                                    $transMsg = 'Ticket does not exists.';
                                    $errorCode = 8;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                                }
                            } else {
                                $amount = 0;
                                $vouchertype = "";
                                $loyaltyCreditable = "";
                                $dateCreated = "";
                                $transMsg = 'Invalid input parameters.';
                                $errorCode = 3;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                            }
                        } else {
                            $amount = 0;
                            $vouchertype = "";
                            $loyaltyCreditable = "";
                            $dateCreated = "";
                            $transMsg = 'Invalid input parameters.';
                            $errorCode = 3;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        }
                    }
                    break;
                default :
                    $amount = 0;
                    $errorCode = 2;
                    $vouchertype = "";
                    $loyaltyCreditable = "";
                    $dateCreated = "";
                    $transMsg = "Source is invalid.";
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                    break;
            }
        } else {
            $amount = 0;
            $vouchertype = "";
            $loyaltyCreditable = "";
            $dateCreated = "";
            $transMsg = 'Invalid input parameters.';
            $errorCode = 3;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
        }

        $this->_sendResponse(200, CommonController::verifyTicketResponse($voucherCode, $amount, $dateCreated, $vouchertype, $loyaltyCreditable, $transMsg, $errorCode));
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10-22-13
     * @purpose generic api method of using whether coupon or ticket
     */
    public function actionAddTicket() {
        Yii::import('application.controllers.*');
        $commonController = new CommonController();
        $_couponModel = new CouponModel();
        $_ticketModel = new TicketModel();
        $_terminalsModel = new TerminalsModel();
        $_memberCardsModel = new MemberCardsModel();
        $_egmSessionsModel = new EGMSessionsModel();
        $request = $this->_readJsonRequest();
        $_accountsModel = new AccountsModel();
        $status = CommonController::VOUCHER_STATUS_ACTIVE;
        $validFromDate = "";
        $validToDate = "";
        $amount = "";
        $aid = "";
        $vouchertype = "";
        $voucherCode = "";
        $dateCreated = "";
        $transMsg = "";
        $errCode = "";
        $sequenceNo = "";

        if (isset($request['Source']) && is_numeric($request['Source'])) {

            $source = trim($request['Source']);
            //check source
            switch ($source) {
                case self::SOURCE_CASHIER :
                    $transMsg = 'Ticket could not be used in cashier.';
                    $errorCode = 37;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                    break;
                case self::SOURCE_KAPI || self::SOURCE_EGM :
                    $vouchertype = self::TICKET;
                    if ((isset($request['VoucherTicketBarcode']) && ctype_alnum($request['VoucherTicketBarcode']) && (strlen($request['VoucherTicketBarcode']) > 0) &&
                            isset($request['TrackingID']) && ctype_alnum($request['TrackingID']) &&
                            strlen($request['TrackingID']) > 0) && (isset($request['TerminalName']) && ctype_alnum($request['TerminalName']) &&
                            strlen($request['MembershipCardNumber']) > 0) && (isset($request['MembershipCardNumber']) && ctype_alnum($request['MembershipCardNumber']) &&
                            strlen($request['TerminalName']) > 0) && (isset($request['Amount']) && is_numeric($request['Amount']) &&
                            strlen($request['Amount']) > 0) && (isset($request['Purpose']) && is_numeric($request['Purpose']) &&
                            strlen($request['Purpose']) > 0) && isset($request['StackerBatchID']) && is_numeric($request['StackerBatchID'])) {
                        $trackingID = trim($request['TrackingID']);
                        $terminalName = trim($request['TerminalName']);
                        $amount = trim($request['Amount']);
                        $cardNumber = trim($request['MembershipCardNumber']);
                        $purpose = trim($request['Purpose']);
                        $stackerBatchID = trim($request['StackerBatchID']);
                        $allowedAmount = Yii::app()->params['allowedAmount'];
                        $voucherCode = trim($request['VoucherTicketBarcode']);

                        if ($amount >= $allowedAmount) {
                            $trackingIDisExists = $_ticketModel->isTrackingIDExists($trackingID);
                            if ($trackingIDisExists == 0) {
                                $m = $_memberCardsModel->getMIDByCardNumber($cardNumber);
                                $MID = $m['MID'];

                                if (!empty($MID)) {
                                    $terminalCode = Yii::app()->params['sitePrefix'] . $terminalName;
                                    $terminals = $_terminalsModel->getTerminalIDByCodeEGMType($terminalCode);

                                    if (!empty($terminals)) {
                                        $terminalID = $terminals[0]['TerminalID'];
                                        if ($terminalID == '' || empty($terminalID)) {
                                            $terminalID = $terminals[1]['TerminalID'];
                                        }
                                        if ((isset($request['AID']) && is_numeric($request['AID']) && strlen($request['AID']) > 0)) {
                                            $aid = trim($request['AID']);
                                        } else {
                                            //Each Site has a Virtual Cashier assigned
                                            $accountTypeID = CommonController::ACCOUNTTYPE_ID_VIRTUAL_CASHIER;
                                            $aid = $_accountsModel->getAIDByAccountTypeIDAndTerminalID($accountTypeID, $terminalID); //AID of Virtual Cashier
                                            if ($aid == '' || empty($aid)) {
                                                $transMsg = 'AID is not associated with any Terminal.';
                                                $errorCode = 43;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                $this->_sendResponse(200, CommonController::addTicketResponse($amount, $voucherCode, $validFromDate, $validToDate, $sequenceNo, $transMsg, $errCode));
                                                exit;
                                            }
                                        }

                                        if ($source == self::SOURCE_EGM) {
                                            $countTerminalEGMSession = $_egmSessionsModel->isEGMSessionExistsByTerminalID($terminalID);
                                            if ($countTerminalEGMSession > 0) {
                                                $_StackerSummaryModel = new StackerSummaryModel();
                                                $countStackerBatchID = $_StackerSummaryModel->isStackerSummaryIDExists($stackerBatchID);
                                                if ($countStackerBatchID > 0) {
                                                    $countEGMSession = $_egmSessionsModel->isEGMSessionExistsByBatchID($stackerBatchID);
                                                    if ($countEGMSession > 0) {
                                                        $countMatchedID = $_egmSessionsModel->isTerminalAndBatchIDMatched($terminalID, $stackerBatchID);
                                                        if ($countMatchedID > 0) {
                                                            $siteID = $_terminalsModel->getSiteIDfromterminals($terminalID);

                                                            $date = date('Y-m-d H:i:s');
                                                            $date_now1 = new DateTime($date);
                                                            $dateInterval = Yii::app()->params['dateInterval'];
                                                            $date_now2 = date_add($date_now1, date_interval_create_from_date_string($dateInterval));
                                                            $dateUpdated = $date;
                                                            $validFromDate = date('Y-m-d H:i:s');
                                                            $validToDate = date(date_format($date_now2, 'Y-m-d' . ' ' . Yii::app()->params['time_stamp']));
                                                            $updatedByAID = $aid;
//                                                                $ticketCodeResult = Helpers::insert_ticket_pad($voucherCode, $terminalName);

                                                            if ($purpose == self::PURPOSE_PAYOUT_TICKET) {
                                                                $status = self::STATUS_TICKET_ACTIVE;
                                                            } else if ($purpose == self::PURPOSE_VOID) {
                                                                $status = self::STATUS_TICKET_VOID;
                                                            } else if ($purpose == self::PURPOSE_CANCELLED) {
                                                                $status = self::STATUS_TICKET_VOID;
                                                            }
                                                            $lastInsertedID = $_ticketModel->insertTicketData($voucherCode, $siteID, $terminalID, $terminalCode, $MID, $amount, $source, $dateUpdated, $updatedByAID, $validFromDate, $validToDate, $trackingID, $status, $stackerBatchID);
                                                            if ($lastInsertedID != false) {
                                                                $transMsg = 'Transaction successful.';
                                                                $errorCode = 0;
                                                                $details = "AddTicket : " . $transMsg;
                                                                $stat = 'Success';
                                                                $status = 1;
                                                                $APIMethod = 8;
                                                                $sequenceNo = $lastInsertedID;
                                                                AuditLog::logAPITransactions($APIMethod, $source, $details, $voucherCode, $trackingID, $status);
                                                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate, $sequenceNo);
                                                            } else {
                                                                $transMsg = 'Transaction failed.';
                                                                $errorCode = 31;
                                                                $details = "AddTicket : " . $transMsg;
                                                                $stat = 'Failed';
                                                                $status = 2;
                                                                $APIMethod = 8;
                                                                AuditLog::logAPITransactions($APIMethod, $source, $details, $voucherCode, $trackingID, $status);
                                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                            }
                                                        } else {
                                                            $transMsg = 'Terminal and StackerBatchID does not match in EGM session.';
                                                            $errorCode = 42;
                                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                        }
                                                    } else {
                                                        $transMsg = 'StackerBatchID does not have a session.';
                                                        $errorCode = 41;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                    }
                                                } else {
                                                    $transMsg = 'StackerBatchID does not exists.';
                                                    $errorCode = 40;
                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                }
                                            } else {
                                                $transMsg = 'Terminal does not have a session.';
                                                $errorCode = 39;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                            }
                                        } else {
                                            $_StackerSummaryModel = new StackerSummaryModel();
                                            $countStackerBatchID = $_StackerSummaryModel->isStackerSummaryIDExists($stackerBatchID);
                                            if ($countStackerBatchID > 0) {
                                                $countEGMSession = $_egmSessionsModel->isEGMSessionExistsByBatchID($stackerBatchID);
                                                if ($countEGMSession > 0) {
                                                    $countMatchedID = $_egmSessionsModel->isTerminalAndBatchIDMatched($terminalID, $stackerBatchID);
                                                    if ($countMatchedID > 0) {
                                                        $siteID = $_terminalsModel->getSiteIDfromterminals($terminalID);

                                                        $date = date('Y-m-d H:i:s');
                                                        $date_now1 = new DateTime($date);
                                                        $dateInterval = Yii::app()->params['dateInterval'];
                                                        $date_now2 = date_add($date_now1, date_interval_create_from_date_string($dateInterval));
                                                        $dateUpdated = $date;
                                                        $validFromDate = date('Y-m-d H:i:s');
                                                        $validToDate = date(date_format($date_now2, 'Y-m-d' . ' ' . Yii::app()->params['time_stamp']));
                                                        $updatedByAID = $aid;
//                                                    $ticketCodeResult = Helpers::insert_ticket_pad($voucherCode, $terminalName);

                                                        if ($purpose == self::PURPOSE_PAYOUT_TICKET) {
                                                            $status = self::STATUS_TICKET_ACTIVE;
                                                        } else if ($purpose == self::PURPOSE_VOID) {
                                                            $status = self::STATUS_TICKET_VOID;
                                                        } else if ($purpose == self::PURPOSE_CANCELLED) {
                                                            $status = self::STATUS_TICKET_VOID;
                                                        }

                                                        $lastInsertedID = $_ticketModel->insertTicketData($voucherCode, $siteID, $terminalID, $terminalCode, $MID, $amount, $source, $dateUpdated, $updatedByAID, $validFromDate, $validToDate, $trackingID, $status, $stackerBatchID);
                                                        if ($lastInsertedID != false) {
                                                            $transMsg = 'Transaction successful.';
                                                            $errorCode = 0;
                                                            $details = "AddTicket : " . $transMsg;
                                                            $stat = 1;
                                                            $APIMethod = 8;
                                                            $sequenceNo = $lastInsertedID;
                                                            AuditLog::logAPITransactions($APIMethod, $source, $details, $voucherCode, $trackingID, $stat);
                                                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate, $sequenceNo);
                                                        } else {
                                                            $transMsg = 'Transaction failed.';
                                                            $errorCode = 31;
                                                            $details = "AddTicket : " . $transMsg;
                                                            $stat = 2;
                                                            $APIMethod = 8;
                                                            AuditLog::logAPITransactions($APIMethod, $source, $details, $voucherCode, $trackingID, $stat);
                                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                        }
                                                    } else {
                                                        $transMsg = 'Terminal and StackerBatchID does not match in EGM session.';
                                                        $errorCode = 42;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                    }
                                                } else {
                                                    $transMsg = 'StackerBatchID does not have a session.';
                                                    $errorCode = 41;
                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                                }
                                            } else {
                                                $transMsg = 'StackerBatchID does not exists.';
                                                $errorCode = 40;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                            }
                                        }
                                    } else {
                                        $transMsg = 'Invalid Terminal Name.';
                                        $errorCode = 38;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                    }
                                } else {
                                    $transMsg = 'Invalid Card Number.';
                                    $errorCode = 32;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                                }
                            } else {
                                $transMsg = 'Tracking ID is already existing.';
                                $errorCode = 32;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                            }
                        } else {
                            $transMsg = "Amount should not be less than " . $allowedAmount . ".";
                            $errorCode = 33;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                        }
                    } else {
                        $transMsg = 'Invalid input parameters.';
                        $errorCode = 3;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                    }
                    break;
                default :
                    $transMsg = 'Source is invalid.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                    break;
            }
        } else {
            $transMsg = 'Invalid input parameters.';
            $errorCode = 3;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
        }

        if (isset($ticketCodeResult)) {
            $voucherCode = $ticketCodeResult;
        }

        $this->_sendResponse(200, CommonController::addTicketResponse($amount, $voucherCode, $validFromDate, $validToDate, $sequenceNo, $transMsg, $errorCode));
    }

    public function actionUseTicket() {
        $request = $this->_readJsonRequest();

        Yii::import('application.controllers.*');
        $commonController = new CommonController();
        $_memberCardsModel = new MemberCardsModel();
        $terminals = new TerminalsModel();
        $_ticketModel = new TicketModel();
        $_accountsModel = new AccountsModel();
        $transMsg = "";

        if (isset($request["VoucherTicketBarcode"]) && ctype_alnum($request["VoucherTicketBarcode"]) &&
                isset($request['TrackingID']) && ctype_alnum($request['TrackingID']) &&
                isset($request['TerminalName']) && is_string($request['TerminalName']) &&
                isset($request['Source']) && is_numeric($request['Source']) &&
                isset($request['MembershipCardNumber']) && ctype_alnum($request['MembershipCardNumber'])) {
            $voucherCode = trim($request["VoucherTicketBarcode"]);
            if (isset($request["AID"])) {
                if (is_numeric($request["AID"])) {
                    $AID = trim($request["AID"]);
                } else {
                    $AID = 0;
                }
            } else {
                $AID = "";
            }

            $trackingID = trim($request['TrackingID']);
            $terminalName = trim($request['TerminalName']);
            $source = trim($request['Source']);
            $cardNumber = trim($request['MembershipCardNumber']);

            $m = $_memberCardsModel->getMIDByCardNumber($cardNumber);

            if (!empty($m)) {
                $mid = $m['MID'];
            } else {
                $transMsg = 'Invalid input parameters.';
                $errorCode = 14;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                $this->_sendResponse(200, CJSON::encode(array("UseTicket" => $result)));
                exit;
            }

            $terminalName = 'ICSA-' . $terminalName;
            $terminalID = $terminals->getTerminalSiteID($terminalName);

            if ($terminalID == false) {
                $errorCode = 28;
                $transMsg = "Invalid Terminal Name";
                Utilities::log($transMsg);
                $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                $this->_sendResponse(200, CJSON::encode(array("UseVoucher" => $result)));
                exit;
            }

            $terminalid = $terminalID['TerminalID'];
            $siteid = $terminalID['SiteID'];

            switch ($source) {
                case self::SOURCE_EGM:

                    if ($AID == '' || empty($AID) || $AID == 0) {
                        $vouchertype = CommonController::VOUCHER_TYPE_TICKET;
                        $transMsg = 'AID is not associated with any Terminal.';
                        $errorCode = 43;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg($vouchertype, $errorCode, $transMsg, $voucherCode, $amount, $validFromDate, "", $validToDate);
                        $this->_sendResponse(200, CommonController::addTicketResponse($amount, $voucherCode, $validFromDate, $validToDate, $sequenceNo, $transMsg, $errCode));
                        exit;
                    }

                    $result = $commonController->useTicket($voucherCode, $AID, $trackingID, $terminalid, $source, self::TICKET, $mid, $siteid, $terminalName);
                    $this->_sendResponse(200, CJSON::encode(array("UseTicket" => $result)));
                    exit;
                    break;
                case self::SOURCE_CASHIER:
//                    $result = $commonController->useCoupon($voucherCode, $AID,$trackingID,
//                            $terminalID, $source, self::COUPON, $MID, $siteID);
                    break;
                case self::SOURCE_KAPI;
                    if (isset($request['Amount']) && is_numeric($request['Amount'])) {
                        if (($AID != "") || ($AID != 0)) {
                            $amount = trim($request['Amount']);
//                            $terminalNameLength = strlen($reqTerminalName);
//                            if ($terminalNameLength > 3) {
//                                $terminalNameDelimiter = substr($reqTerminalName, 0, 3);
//                            } else {
//                                $terminalNameDelimiter = $reqTerminalName;
//                            }
//                            $terminalDelimiter = $terminalNameDelimiter;
//                            $constDelimiter = Yii::app()->params['constant_delimiter'];
//                            $finalConstDelimiter = $constDelimiter . $terminalDelimiter;
//                            $voucherLimited = str_replace($finalConstDelimiter, "", $voucherCode);
//
//                            $vCodeLength = strlen($voucherLimited);
//                            $trailingString = 1;
//                            do {
//                                if ($trailingString == 0) {
//                                    $trailingString = $trailingString - 1;
//                                }
//                                $voucherCodeLimited = substr($voucherLimited, 0, $trailingString);
//                                $ticketData = $_ticketModel->getTicketDetails($voucherCodeLimited);
//
//                                if (!empty($ticketAmountData)) {
//                                    $vCodeLength = 0;
//                                } else {
//                                    $trailingString--;
//                                    $vCodeLength--;
//                                }
//                                $ticketData = $ticketData;
//                            } while ($vCodeLength >= 7);
//                            $ticketData = $ticketData;
                            $ticketData = $_ticketModel->getTicketDetails($voucherCode);
                            if (!empty($ticketData)) {
                                $ticketAmountData = $ticketData['Amount'];
                            } else {
                                $transMsg = 'Ticket is invalid.';
                                $errorCode = 35;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                                $this->_sendResponse(200, CJSON::encode(array("UseTicket" => $result)));
                                exit;
                            }

                            if ((int) $ticketAmountData == (int) $amount) {
                                $result = $commonController->useTicket($voucherCode, $AID, $trackingID, $terminalid, $source, self::TICKET, $mid, $siteid, $terminalName, $amount);
                            } else {
                                $transMsg = 'Invalid Amount.';
                                $errorCode = 27;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                            }
                        } else {
                            $transMsg = 'Invalid input parameters.';
                            $errorCode = 14;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                        }
                    } else {
                        $transMsg = 'Invalid input parameters.';
                        $errorCode = 14;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                    }
                    break;
                default :
                    $errorCode = 29;
                    $transMsg = "Source is invalid.";
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
                    break;
            }
        } else {

            $transMsg = 'Invalid input parameters.';
            $errorCode = 14;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $result = $commonController->getUseReturnMsg(1, $transMsg, $errorCode);
        }
        $this->_sendResponse(200, CJSON::encode(array("UseTicket" => $result)));
    }

    /**
     * @todo
     * @return type
     */
    private function _readJsonRequest() {

        //read the post input (use this technique if you have no post variable name):
        $post = file_get_contents("php://input");

        //decode json post input as php array:
        $data = CJSON::decode($post, true);

        return $data;
    }

    /**
     *
     * @param type $status
     * @param string $body
     * @param type $content_type 
     * @link http://www.yiiframework.com/wiki/175/how-to-create-a-rest-api
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 200:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
                    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                    </head>
                    <body>
                        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                        <p>' . $message . '</p>
                        <hr />
                        <address>' . $signature . '</address>
                    </body>
                    </html>';

            echo $body;
        }
        //Yii::app()->end();
    }

    /**
     * HTTP Status Code Message
     * @param string $status
     * @return bool
     */
    private function _getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            200 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10-22-13
     * @param int $status
     * @param int $voucherType
     * @param string $vtcode
     * @param int $amount
     * @param string $datecreated
     * @param int $vouchertypeid
     * @param int $loyaltycreditable
     */
    private function _voucherStatus($status, $vouchertype) {
        $transMsg = "";
        $errorCode = "";

        if ($vouchertype == CommonController::VOUCHER_TYPE_TICKET) {
            switch ($status) {
                case CommonController::VOUCHER_STATUS_INACTIVE :
                    $transMsg = 'Ticket is not activated.';
                    $errorCode = 20;
                    break;
                case CommonController::VOUCHER_STATUS_ACTIVE :
                    $transMsg = 'Ticket is unclaimed.';
                    $errorCode = 0;
                    break;
                case CommonController::VOUCHER_STATUS_VOID :
                    $transMsg = 'Ticket is active.';
                    $errorCode = 0;
                    break;
                case CommonController::VOUCHER_STATUS_USED :
                    $transMsg = 'Ticket is already used.';
                    $errorCode = 23;
                    break;
                case CommonController::VOUCHER_STATUS_CLAIMED :
                    $transMsg = 'Ticket is already claimed.';
                    $errorCode = 24;
                    break;
                case CommonController::VOUCHER_STATUS_REIMBURSED :
                    $transMsg = 'Ticket is already reimbursed.';
                    $errorCode = 25;
                    break;
                case CommonController::VOUCHER_STATUS_EXPIRED :
                    $transMsg = 'Ticket is already expired.';
                    $errorCode = 26;
                    break;
                case CommonController::VOUCHER_STATUS_EXPIRED :
                    $transMsg = 'Ticket is cancelled.';
                    $errorCode = 27;
                    break;
            }
        } else {
            switch ($status) {
                case CommonController::VOUCHER_STATUS_INACTIVE :
                    $transMsg = 'Coupon is not activated.';
                    $errorCode = 12;
                    break;
                case CommonController::VOUCHER_STATUS_ACTIVE :
                    $transMsg = 'Coupon is unclaimed.';
                    $errorCode = 11;
                    break;
                case CommonController::VOUCHER_STATUS_VOID :
                    $transMsg = 'Coupon is void.';
                    $errorCode = 16;
                    break;
                case CommonController::VOUCHER_STATUS_USED :
                    $transMsg = 'Coupon is already used.';
                    $errorCode = 30;
                    break;
                case CommonController::VOUCHER_STATUS_CLAIMED :
                    $transMsg = 'Coupon is already claimed.';
                    $errorCode = 15;
                    break;
                case CommonController::VOUCHER_STATUS_REIMBURSED :
                    $transMsg = 'Coupon is already reimbursed.';
                    $errorCode = 14;
                    break;
                case CommonController::VOUCHER_STATUS_EXPIRED :
                    $transMsg = 'Coupon is already expired.';
                    $errorCode = 10;
                    break;
                case CommonController::VOUCHER_STATUS_EXPIRED :
                    $transMsg = 'Coupon is cancelled.';
                    $errorCode = 13;
                    break;
            }
        }
        return array($errorCode, $transMsg);
    }

}

?>
