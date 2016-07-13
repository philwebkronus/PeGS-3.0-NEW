<?php

/**
 * @description of WsStackerApiController
 * @author JunJun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 11-04-13 04:32:07 PM
 */
class wsStackerApiController extends Controller {

    //A function to start or end a Stacker Session.
    public function actionLogStackerSession() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $collectedBy = '';
        $module = 'LogStackerSession';
        $APIMethodID = APILogsModel::API_METHOD_LOGSTACKERSESSION;
        if (isset($request['TerminalName']) && isset($request['SerialNumber']) && isset($request['Action'])) {

            if (($request['TerminalName'] == "") || ($request['SerialNumber'] == "") || ($request['Action'] == "")) {
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            } else {
                if (ctype_alnum($request['TerminalName']) && ctype_alnum($request['SerialNumber']) && is_numeric($request['Action'])) {

                    $_stackerSessionsModel = new StackerSessionsModel();
                    $_stackerInfoModel = new StackerInfoModel();
                    $terminalName = trim($request['TerminalName']);
                    $serialNumber = trim($request['SerialNumber']);
                    $action = trim($request['Action']);
                    $sc = Yii::app()->params['SitePrefix'] . $terminalName;

                    if ($action == 1) {
                        $countTerminal = $_stackerInfoModel->isTerminalExists($sc);
                        if ($countTerminal > 0) {
                            $stackerInfoID = $_stackerInfoModel->checkIfTerminalAndSerialMatched($terminalName, $serialNumber);

                            if (!empty($stackerInfoID)) {
                                $isTerminalActive = $_stackerInfoModel->checkTerminalStatus($terminalName, $serialNumber);
                                if ($isTerminalActive == CommonController::STACKER_INFO_STATUS_ACTIVE) {
                                    $countTerminalStatus = $_stackerSessionsModel->isTerminalStatusNotValid($sc);

                                    if ($countTerminalStatus >= 0) {
                                        $countTerminalEnded = $_stackerSessionsModel->isTerminalSessionUnendedExists($sc);
                                        if ($countTerminalEnded == 0) {
//
//                                            $isNotYetValidated = $_stackerSessionsModel->isTerminalStatusNotYetValidated($stackerInfoID);
//                                            if ($isNotYetValidated == 0) {
                                            $apiTransdetails = 'SN: ' . $serialNumber . ', Action: ' . $action . ', TName: ' . $terminalName;
                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                            $stackerSessionID = $_stackerSessionsModel->insertStacker($sc, $stackerInfoID, 0);
                                            if ($stackerSessionID != false) {
                                                $transMsg = 'Transaction successful.';
                                                $errorCode = 0;
                                                $apiStatus = 1;
                                                $referenceID = $stackerSessionID;
                                            } else {
                                                $transMsg = 'Transaction failed.';
                                                $errorCode = 5;
                                                $apiStatus = 2;
                                                $referenceID = '';
                                                $otherInfo = "TerminalName:" . $terminalName . " | SerialNumber: " . $serialNumber . " | Action: " . $action . " | ";
                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                            }
                                            $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
//                                            } else {
//                                                $transMsg = 'Terminal stacker session has not yet been validated.';
//                                                $errorCode = 26;
//                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                                            }
                                        } else {
                                            $transMsg = 'Terminal already has a stacker session.';
                                            $errorCode = 2;
                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        }
                                    } else {
                                        $transMsg = 'Terminal already has a stacker session.';
                                        $errorCode = 2;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    }
                                } else {
                                    $transMsg = 'Terminal stacker is inactive.';
                                    $errorCode = 32;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                }
                            } else {
                                $transMsg = 'Terminal and Serial Number did not match.';
                                $errorCode = 2;
                                $otherInfo = "TerminalName:" . $terminalName . " | SerialNumber: " . $serialNumber . " | Action: " . $action . " |  CollectedBy: " . $collectedBy . " | ";
                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                            }
                        } else {
                            $transMsg = 'Terminal does not exist.';
                            $errorCode = 3;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        }
                    } else if ($action == 2) {
                        if (isset($request['CollectedBy']) && isset($request['CollectedBy'])) {
                            $collectedBy = trim($request['CollectedBy']);
                            if (empty($collectedBy) || $collectedBy == "") {
                                $transMsg = 'Collected By is required when ending a stacker session.';
                                $errorCode = 36;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            } else {
                                if (ctype_alnum($request['TerminalName'])) {
                                    $countTerminal = $_stackerInfoModel->isTerminalExists($sc);
                                    if ($countTerminal > 0) {
                                        //check if terminal and serial are matched
                                        $stackerInfoID = $_stackerInfoModel->checkIfTerminalAndSerialMatched($terminalName, $serialNumber);
                                        if (!empty($stackerInfoID))
                                        {

                                            $stackerSessionID = $_stackerSessionsModel->getStackerSessionIDByTerminalName($sc);
                                            if (!empty($stackerSessionID)) {
                                                $apiTransdetails = 'SN: ' . $serialNumber . ', Action: ' . $action . ', TName: ' . $terminalName . ', Collected By: ' . $collectedBy;
                                                $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                                if ($_stackerSessionsModel->removeStacker($stackerSessionID, 1, $collectedBy) == true) {
                                                    $transMsg = 'Transaction successful.';
                                                    $errorCode = 0;
                                                    $apiStatus = 1;
                                                    $referenceID = $stackerSessionID;
                                                } else {
                                                    $transMsg = 'Transaction failed.';
                                                    $errorCode = 5;
                                                    $apiStatus = 2;
                                                    $referenceID = $stackerSessionID;
                                                    $otherInfo = "TerminalName:" . $terminalName . " | SerialNumber: " . $serialNumber . " | Action: " . $action . " |  CollectedBy: " . $collectedBy . " | ";
                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                }
                                                $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                            } else {
                                                $transMsg = 'Terminal has no active stacker session.';
                                                $errorCode = 6;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                            }
                                        } 
                                        else
                                        {
                                            $transMsg = 'Terminal and Serial Number did not match.';
                                            $errorCode = 2;
                                            $otherInfo = "TerminalName:" . $terminalName . " | SerialNumber: " . $serialNumber . " | Action: " . $action . " |  CollectedBy: " . $collectedBy . " | ";
                                            Utilities::errorLogger($transMsg, $module, $otherInfo);
                                        }
                                    }
                                    else {
                                        $transMsg = 'Terminal does not exist.';
                                        $errorCode = 3;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    }
                                } else {
                                    $transMsg = 'Invalid Collected By.';
                                    $errorCode = 35;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                }
                            }
                        } else {
                            $transMsg = 'Collected By is required when ending a stacker session.';
                            $errorCode = 36;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        }
                    } else {
                        $transMsg = 'Invalid input parameter.';
                        $errorCode = 2;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
        }
        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
    }

    //A function to log every transaction made in a stacker such as deposit or reload using cash or ticket.
    public function actionLogStackerTransaction() {
                        //******************* DISABLE Genesis Reload *********************//
//$transMsg = 'Reload using genesis terminal is temporarily disabled Please Use Cashier Load tab';
//$errorCode = 29;
//$module = 'LogStackerTransaction';
//Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//$this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//exit;
        $request = $this->_readJsonRequest();
        $module = 'LogStackerTransaction';
        $APIMethodID = APILogsModel::API_METHOD_LOGSTACKERTRANSACTION;
        $transMsg = '';
        $errorCode = '';
        Yii::import('application.components.VoucherTicketAPIWrapper');

        $voucherticket = new VoucherTicketAPIWrapper();
        $stackerBatchID = "";
        //Required input must be set
        if (isset($request['TrackingID']) && isset($request['TerminalName']) && isset($request['TransType']) && isset($request['Amount']) &&
                isset($request['CashType']) && isset($request['Source']) && isset($request['StackerBatchID']) && isset($request['MembershipCardNumber'])) {

            if (($request['TrackingID']) == "" || ($request['TerminalName']) == "" || ($request['TransType']) == "" ||
                    ($request['Amount']) == "" || ($request['CashType']) == "" || ($request['Source']) == "" ||
                    ($request['StackerBatchID']) == "" || ($request['MembershipCardNumber']) == "") {
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
            } else {

                if (ctype_alnum($request['TrackingID']) && ctype_alnum($request['TerminalName']) && is_numeric($request['TransType']) &&
                        is_numeric($request['Amount']) && is_numeric($request['CashType']) && is_numeric($request['Source']) &&
                        is_numeric($request['StackerBatchID']) && ctype_alnum($request['MembershipCardNumber'])) {

                    $terminalName = trim($request['TerminalName']);
                    $sc = Yii::app()->params['SitePrefix'] . $terminalName; //Blank prefix
                    $sc2 = Yii::app()->params['SitePrefix2'] . $terminalName; //Prefix (eg. ICSA-)
                    $source = trim($request['Source']);
                    $amount = trim($request['Amount']);
                    $allowedAmount = Yii::app()->params['allowedAmount'];
                    $transType = trim($request['TransType']);
                    $paymentType = trim($request['CashType']);
                    $trackingID = trim($request['TrackingID']);
                    $stackerBatchID = trim($request['StackerBatchID']);
                    $cardNumber = '';

                    //Load all modules to be used
                    $_stackerDetails = new StackerDetailsModel();
                    $_stackerInfoModel = new StackerInfoModel();
                    $_stackerSummaryModels = new StackerSummaryModels();
                    $_stackerSessionsModels = new StackerSessionsModel();
                    $_memberCardsModel = new MemberCardsModel();
                    $_terminalsModel = new TerminalsModel();
                    $_EGMSessions = new EGMSessionsModel();
                    $_accountsModel = new AccountsModel();
                    $_refCashDenominationModel = new RefCashDenominationModel();
                    $_stackerSessionCashDenomModel = new StackerSessionCashDenomModel();
//                    $getSiteID = $_terminalsModel->getTerminalIDByCode($sc2);
//                    $siteID = $getSiteID[0]['SiteID'];
//                    $enabledSite = Yii::app()->params['enabledSite'];
//                    if ($siteID == $enabledSite){
                    if ($amount >= $allowedAmount) { //Input amount should be greater than or equal to allowed amount
                        $isTrackingIDExists = $_stackerDetails->isTrackingIDExists($trackingID);

                        if ($isTrackingIDExists == 0) { //TrackingID must should be unique
                            if ($source == CommonController::SOURCE_KAPI || $source == CommonController::SOURCE_EGM || $source == CommonController::SOURCE_CASHIER) { //Source must only be KAPI, EGM, or Cashier
                                $stackerInfoID = $_stackerInfoModel->getStackerInfoIDByTerminalName($sc);
                                $isStackerBatchIDExists = $_stackerSummaryModels->isStackerBatchIdExists($stackerBatchID); //equal to StackerSummaryID

                                if ($isStackerBatchIDExists > 0) { //Check if StackerBatchID/StackerSummaryID is existing
                                    $totalDeposit = $_stackerSummaryModels->getTotalDepositByID($stackerBatchID); //Get the total deposit
                                    $finalTotalDeposit = $totalDeposit + $amount; //The value when we update Deposit in stackersessions
                                    $totalReload = $_stackerSummaryModels->getTotalReloadByID($stackerBatchID); //Get the total reload
                                    $finalTotalReload = $totalReload + $amount;  //The value when we update Reload in stackersessions

                                    if ($stackerInfoID > 0) { //Check if Terminal Stacker is existing
                                        $stackerSessionID = $_stackerSessionsModels->getStackerSessionIDByTerminalName($sc);

                                        if (!empty($stackerSessionID)) { //Check if Terminal Stacker has a session
                                            if ($transType == CommonController::TRANS_TYPE_DEPOSIT || $transType == CommonController::TRANS_TYPE_RELOAD) { //Execute if transaction type is deposit or reload
                                                $terminals = $_terminalsModel->getTerminalIDByCode($sc2);

                                                if (!empty($terminals)) { //If Terminal exists in Kronus (npos database)
                                                    $isEGMSession = $_EGMSessions->checkSessionIfExists($terminals[0]['TerminalID']);

                                                    if ($isEGMSession > 0) { //If Terminal has an EGM session
                                                        $terminalID = $terminals[0]['TerminalID']; //Regular
                                                        $isEGMSessionExists = $isEGMSession; //Regular session
                                                    } else { //If Terminal has no Regular EGM session
                                                        $terminalID = $terminals[1]['TerminalID']; //VIP
                                                        $isEGMSessionExists = $_EGMSessions->checkSessionIfExists($terminalID); //VIP session
                                                    }
                                                    //Each Site has a Virtual Cashier assigned
                                                    $accountTypeID = CommonController::ACOUNTTYPE_ID_VIRTUAL_CASHIER;
                                                    $AID = $_accountsModel->getAIDByAccountTypeIDAndTerminalID($accountTypeID, $terminalID); //AID of Virtual Cashier
                                                } else {
                                                    $transMsg = 'Terminal does not exist.';
                                                    $errorCode = 3;
                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                                                    exit;
                                                }

                                                if (isset($request['MembershipCardNumber']) && ctype_alnum($request['MembershipCardNumber'])) {
                                                    $cardNumber = trim($request['MembershipCardNumber']);
                                                    $MID = $_memberCardsModel->getMIDByCardNumber($cardNumber);
                                                    if (!empty($MID)) { //If membership card number is existing
                                                        if ($isEGMSessionExists > 0) { //If Terminal has an EGM session
                                                            $isTerminalMIDMatched = $_EGMSessions->isTerminalMIDMatched($terminalID, $MID);
                                                            if ((int) $isTerminalMIDMatched > 0) {
                                                                $isTerminalStackerBatchIDMatched = $_EGMSessions->isTerminalStackerBatchIDMatched($terminalID, $stackerBatchID);
                                                                if ($isTerminalStackerBatchIDMatched > 0) {
                                                                    if ($paymentType == CommonController::PAYMENT_TYPE_CASH) { //If payment type is cash
                                                                        $voucherCode = '';
                                                                        $stackerSessionDetails = $_stackerSessionsModels->getStackerSessionDetails($stackerSessionID);
                                                                        $cashAmount = (int) $stackerSessionDetails['CashAmount'] + $amount;
                                                                        $ticketCount = (int) $stackerSessionDetails['TicketCount'];
                                                                        $cashCount = (int) $stackerSessionDetails['CashCount'] + 1;
                                                                        $quantity = (int) $stackerSessionDetails['Quantity'] + 1;
                                                                        $totalAmount = (int) $stackerSessionDetails['TotalAmount'] + $amount;

                                                                        $denominationID = (int) $_refCashDenominationModel->getDenominationIDByAmount($amount);
                                                                        //If cash or bill denomination was found and status is active in ref_cashdenomination
                                                                        if (!empty($denominationID)) {
                                                                            $denominationCountDetails = $_stackerSessionCashDenomModel->getDenomCountBySessionIDAndDenomID($stackerSessionID, $denominationID);
                                                                            $denominationExists = $_stackerSessionCashDenomModel->isDenominationExists($stackerSessionID, $denominationID);

                                                                            if ($denominationExists > 0) {
                                                                                //If denomination is already existing in stackercashdenomination, we only need to update the table with denominationcount plus one
                                                                                $denomination = (int) $denominationCountDetails;
                                                                                $denominationCount = $denomination + 1;
                                                                                $returnValue = $_stackerSessionsModels->updateStackerSessionsDataCashDenom($cashCount, $ticketCount, $quantity, $cashAmount, $totalAmount, $stackerSessionID, $denominationID, $denominationCount, $stackerBatchID, $amount, $transType, $paymentType, $voucherCode, $trackingID, $finalTotalDeposit, $finalTotalReload, $AID);
                                                                            } else {
                                                                                //If denomination is non-existent in stackercashdenomination, we need to insert to the table with denominationcount of one
                                                                                $denominationCount = 1;
                                                                                $returnValue = $_stackerSessionsModels->updateStackerSessionsInsertDataCashDenom($cashCount, $ticketCount, $quantity, $cashAmount, $totalAmount, $stackerSessionID, $denominationID, $denominationCount, $stackerBatchID, $amount, $transType, $paymentType, $voucherCode, $trackingID, $finalTotalDeposit, $finalTotalReload, $AID);
                                                                            }
                                                                            $apiTransdetails = 'TransType = ' . $transType . ', Amount = ' . $amount . ', TID = ' . $terminalID . ', CashType = ' . $paymentType . ', TicketCode = "", SBatchID = ' . $stackerBatchID . ', MID = ' . $MID;
                                                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails, $trackingID);
                                                                            if ($returnValue > 0) {
                                                                                $transMsg = 'Transaction successful';
                                                                                $errorCode = 0;
                                                                                $apiStatus = 1;
                                                                            } else {
                                                                                $transMsg = 'Transaction failed.';
                                                                                $errorCode = 5;
                                                                                $apiStatus = 2;
                                                                                $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . " | TransType: " . $transType . " | CashType: " . $paymentType . " |  TicketCode: | Amount: " . $amount . "|";
                                                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                                            }
                                                                            $referenceID = $stackerBatchID;
                                                                            $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                                                        } else {
                                                                            $transMsg = 'Cash denomination amount is invalid.';
                                                                            $errorCode = 31;
                                                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                        }
                                                                        
                                                                    } else if ($paymentType == CommonController::PAYMENT_TYPE_TICKET) { //If payment type is ticket
                                                                        if (isset($request['VoucherTicketBarcode'])) {
                                                                            if (ctype_alnum($request['VoucherTicketBarcode'])) {
                                                                                $voucherCode = trim($request['VoucherTicketBarcode']);
                                                                                $ticketsModel = new TicketsModel();
                                                                                $ticketAmountData = $ticketsModel->getAmountByTicketCode($voucherCode);

                                                                                $compareAmount1 = (int) $amount;
                                                                                $compareAmount2 = (int) $ticketAmountData;

                                                                                if ($compareAmount1 == $compareAmount2) {
                                                                                    //Verify first if the Ticket is valid before using it. Call VerifyTicket of VAPI (API of Voucher Management System)
                                                                                    $verifyVoucherResult = $voucherticket->verifyTicket($voucherCode, $terminalName, $AID, $source, $cardNumber);
                                                                                    //If VerifyTicket returns no error, retry using the ticket by calling again the UseTicket of VAPI (API of Voucher Management System)
                                                                                    if (isset($verifyVoucherResult['VerifyTicket']['ErrorCode']) && $verifyVoucherResult['VerifyTicket']['ErrorCode'] == 0) {
                                                                                        //Call UseTicket of VAPI (API of Voucher Management System)
                                                                                        $useVoucherResult = $voucherticket->useTicket($terminalName, $voucherCode, $AID, $source, $trackingID, $cardNumber, $amount);

                                                                                        //If UseTicket returns no error
                                                                                        if (isset($useVoucherResult['UseTicket']['ErrorCode']) && $useVoucherResult['UseTicket']['ErrorCode'] == 0) {

                                                                                            $stackerSessionID = (int) $stackerSessionID;
                                                                                            $stackerSessionDetails = $_stackerSessionsModels->getStackerSessionDetails($stackerSessionID);

                                                                                            $cashAmount = (int) $stackerSessionDetails['CashAmount'];
                                                                                            $ticketCount = (int) $stackerSessionDetails['TicketCount'] + 1;
                                                                                            $cashCount = (int) $stackerSessionDetails['CashCount'];
                                                                                            $quantity = (int) $stackerSessionDetails['Quantity'] + 1;
                                                                                            $totalAmount = (int) $stackerSessionDetails['TotalAmount'] + $amount;
                                                                                            $apiTransdetails = 'TransType = ' . $transType . ', Amount = ' . $amount . ', TID = ' . $terminalID . ', CashType = ' . $paymentType . ', TicketCode = ' . $voucherCode . ', SBatchID = ' . $stackerBatchID . ', MID = ' . $MID;
                                                                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails, $trackingID);
                                                                                            $returnValue = $_stackerSessionsModels->updateStackerSessionsDetailsTickets($quantity, $amount, $totalAmount, $stackerSessionID, $transType, $paymentType, $voucherCode, $trackingID, $stackerBatchID, $finalTotalDeposit, $finalTotalReload, $AID, $ticketCount);
                                                                                            if ($returnValue > 0) {
                                                                                                $transMsg = 'Transaction successful.';
                                                                                                $errorCode = 0;
                                                                                                $apiStatus = 1;
                                                                                            } else {
                                                                                                $transMsg = 'Transaction failed.';
                                                                                                $errorCode = 5;
                                                                                                $apiStatus = 2;
                                                                                                $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . " | TransType: " . $transType . " | CashType: " . $paymentType . " |  TicketCode: " . $voucherCode . " | Amount: " . $amount . "|";
                                                                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                                                            }
                                                                                            $referenceID = $stackerBatchID;
                                                                                            $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                                                                        } else {
                                                                                            //If UseTicket returns an error, retry using the ticket by calling first the VerifyTicket of VAPI (API of Voucher Management System)
                                                                                            $verifyVoucherResult = $voucherticket->verifyTicket($voucherCode, $terminalName, $AID, $source, $cardNumber);
                                                                                            //If VerifyTicket returns no error, retry using the ticket by calling again the UseTicket of VAPI (API of Voucher Management System)
                                                                                            if (isset($verifyVoucherResult['VerifyTicket']['ErrorCode']) && $verifyVoucherResult['VerifyTicket']['ErrorCode'] == 0) {
                                                                                                $useVoucherResult = $voucherticket->useTicket($terminalName, $voucherCode, $AID, $source, $trackingID, $cardNumber, $amount);

                                                                                                //If UseTicket returns no error, finally, we need to update the necessary tables to be updated
                                                                                                if (isset($useVoucherResult['UseTicket']['ErrorCode']) && $useVoucherResult['UseTicket']['ErrorCode'] == 0) {
                                                                                                    $stackerSessionID = (int) $stackerSessionID;
                                                                                                    $stackerSessionDetails = $_stackerSessionsModels->getStackerSessionDetails($stackerSessionID);
                                                                                                    $cashAmount = (int) $stackerSessionDetails['CashAmount'];
                                                                                                    $ticketCount = (int) $stackerSessionDetails['TicketCount'] + 1;
                                                                                                    $cashCount = (int) $stackerSessionDetails['CashCount'];
                                                                                                    $quantity = (int) $stackerSessionDetails['Quantity'] + 1;
                                                                                                    $totalAmount = (int) $stackerSessionDetails['TotalAmount'] + $amount;
                                                                                                    $ticketCode = $voucherCode;
                                                                                                    $apiTransdetails = 'TransType = ' . $transType . ', Amount = ' . $amount . ', TID = ' . $terminalID . ', CashType = ' . $paymentType . ', TicketCode = ' . $ticketCode . ', SBatchID = ' . $stackerBatchID . ', MID = ' . $MID;
                                                                                                    $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails, $trackingID);
                                                                                                    $returnValue = $_stackerSessionsModels->updateStackerSessionsDetails($quantity, $amount, $totalAmount, $stackerSessionID, $transType, $paymentType, $voucherCode, $trackingID, $stackerBatchID, $finalTotalDeposit, $finalTotalReload, $AID);
                                                                                                    if ($returnValue > 0) {
                                                                                                        $transMsg = 'Transaction successful.';
                                                                                                        $errorCode = 0;
                                                                                                        $apiStatus = 1;
                                                                                                    } else {
                                                                                                        $transMsg = 'Transaction failed.';
                                                                                                        $errorCode = 5;
                                                                                                        $apiStatus = 2;
                                                                                                        $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . " | TransType: " . $transType . " | CashType: " . $paymentType . " |  TicketCode: " . $voucherCode . " | Amount: " . $amount . "|";
                                                                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                                                                    }
                                                                                                    $referenceID = $stackerBatchID;
                                                                                                    $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                                                                                } else {
                                                                                                    $transMsg = $useVoucherResult['UseTicket']['TransactionMessage'];
                                                                                                    $errorCode = $useVoucherResult['UseTicket']['ErrorCode'];
                                                                                                    $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . " | TransType: " . $transType . " | CashType: " . $paymentType . " |  TicketCode: " . $voucherCode . " | Amount: " . $amount . "|";
                                                                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                                                                }
                                                                                            } else {
                                                                                                $transMsg = $verifyVoucherResult['VerifyTicket']['TransactionMessage'];
                                                                                                $errorCode = $verifyVoucherResult['VerifyTicket']['ErrorCode'];
                                                                                                $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . " | TransType: " . $transType . " | CashType: " . $paymentType . " |  TicketCode: " . $voucherCode . " | Amount: " . $amount . "|";
                                                                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                                                            }
                                                                                        }
                                                                                    } else {
                                                                                        $transMsg = $verifyVoucherResult['VerifyTicket']['TransactionMessage'];
                                                                                        $errorCode = $verifyVoucherResult['VerifyTicket']['ErrorCode'];
                                                                                        $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . " | TransType: " . $transType . " | CashType: " . $paymentType . " |  TicketCode: " . $voucherCode . " | Amount: " . $amount . "|";
                                                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                                                    }
                                                                                } else {
                                                                                    $transMsg = 'Amount should be equal to Ticket Amount.';
                                                                                    $errorCode = 34;
                                                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                                }
                                                                            } else {
                                                                                $transMsg = 'Invalid Voucher Code.';
                                                                                $errorCode = 45;
                                                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                            }
                                                                        } else {
                                                                            $transMsg = 'Voucher Code is required.';
                                                                            $errorCode = 11;
                                                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                        }
                                                                    } else {
                                                                        $transMsg = 'Invalid Payment Type.';
                                                                        $errorCode = 28;
                                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                    }
                                                                } else {
                                                                    $transMsg = 'Terminal and StackerBatchID does not match in EGM session.';
                                                                    $errorCode = 44;
                                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                }
                                                            } else {
                                                                $transMsg = 'Terminal and CardNumber does not match in EGM Session.';
                                                                $errorCode = 33;
                                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            }
                                                        } else {
                                                            $transMsg = 'Terminal has no active EGM session.';
                                                            $errorCode = 25;
                                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        }
                                                    } else {
                                                        $transMsg = 'Invalid Card Number.';
                                                        $errorCode = 7;
                                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    }
                                                } else {
                                                    $transMsg = 'Card Number is required.';
                                                    $errorCode = 30;
                                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                }
                                            } else {
                                                $transMsg = 'Invalid Transaction Type.';
                                                $errorCode = 27;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                            }
                                        } else {
                                            $transMsg = 'Terminal has no active stacker session.';
                                            $errorCode = 6;
                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        }
                                    } else {
                                        $transMsg = 'Terminal does not exist.';
                                        $errorCode = 3;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    }
                                } else {
                                    $transMsg = 'Stacker Batch ID does not exist.';
                                    $errorCode = 22;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                }
                            } else {
                                $transMsg = 'Invalid Source.';
                                $errorCode = 29;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            }
                        } else {
                            $transMsg = 'Tracking ID already exists.';
                            $errorCode = 24;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        }
                    } else {
                        $transMsg = 'Amount must be greater than or equal to .' . $allowedAmount;
                        $errorCode = 4;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    }
//                    }else
//                        {
//                        // ******************* DISABLE Genesis Reload *********************//
//                        $transMsg = 'Reload using genesis terminal is temporarily disabled Please Use Cashier Load tab';
//                        $errorCode = 29;
//                        $module = 'LogStackerTransaction';
//                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                        exit;
//                        }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
        }
        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, "", "", "", "", $stackerBatchID)));
    }

    //A function that creates stackerbatchid/stackersummaryid then updates stackerbatchid field in egmsessions table of Kronus (npos database).
    public function actionGetStackerBatchId() {
                        //******************* DISABLE Genesis Reload *********************//
//$transMsg = 'Reload using genesis terminal is temporarily disabled Please Use Cashier Load tab';
//$errorCode = 29;
//$module = 'GetStackerId';
//Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//$this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//exit;
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'GetStackerId';
        $APIMethodID = APILogsModel::API_METHOD_GETSTACKERBATCHID;
        if (isset($request['TerminalName']) && isset($request['MembershipCardNumber'])) {
            if (($request['TerminalName']) == "" || ($request['MembershipCardNumber']) == "") {
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            } else {
                if (ctype_alnum($request['TerminalName']) && ctype_alnum($request['MembershipCardNumber'])) {
                    $_memberCardsModel = new MemberCardsModel();
                    $_stackerSessionsModel = new StackerSessionsModel();
                    $_EGMSessionsModel = new EGMSessionsModel();
                    $_stackerSummaryModel = new StackerSummaryModels();
                    $_accountsModel = new AccountsModel();
                    $_TerminalsModel = new TerminalsModel();
                    $terminalName = trim($request['TerminalName']);
                    $mCardNumber = trim($request['MembershipCardNumber']);
                    $sc = Yii::app()->params['SitePrefix'] . $terminalName;
                    $sc2 = Yii::app()->params['SitePrefix2'] . $terminalName; //Prefix (eg. ICSA-)
//                    $getSiteID = $_TerminalsModel->getTerminalIDByCode($sc2);
//                    $siteID = $getSiteID[0]['SiteID'];
//                    $enabledSite = Yii::app()->params['enabledSite'];
//                    if ($siteID == $enabledSite){
                    $countMCard = $_memberCardsModel->isCardNumberExists($mCardNumber);
                    if ($countMCard > 0) {
                        $MID = $_memberCardsModel->getMIDByCardNumber($mCardNumber);
                        if ($MID > 0) {
                            $stackerSessionID = $_stackerSessionsModel->getStackerSessionIDByTerminalName($sc);
                            if (!empty($stackerSessionID)) {
                                
                                $tc = Yii::app()->params['SitePrefix2'] . $terminalName;
                                $terminals = $_TerminalsModel->getTerminalIDByCode($tc);

                                if (!empty($terminals)) {
                                    $isEGMSession = $_EGMSessionsModel->checkSessionIfExists($terminals[0]['TerminalID']);
                                    if ($isEGMSession > 0) {
                                        $isEGMSessionExists = $isEGMSession;
                                        $terminalID = $terminals[0]['TerminalID'];
                                        $egmSessionID = $_EGMSessionsModel->getEGMSessionIDByTerminalID($terminalID);
                                    } else {
                                        $isEGMSessionExists = $_EGMSessionsModel->checkSessionIfExists($terminals[1]['TerminalID']);
                                        $terminalID = $terminals[1]['TerminalID'];
                                        $egmSessionID = $_EGMSessionsModel->getEGMSessionIDByTerminalID($terminalID);
                                    }

                                    $accountTypeID = CommonController::ACOUNTTYPE_ID_VIRTUAL_CASHIER; //Each Site has a Virtual Cashier assigned
                                    $AID = $_accountsModel->getAIDByAccountTypeIDAndTerminalID($accountTypeID, $terminalID); //AID of Virtual Cashier
                                    if ($isEGMSessionExists > 0) {
                                        $isTerminalMIDMatched = $_EGMSessionsModel->isTerminalMIDMatched($terminalID, $MID);
                                        if ($isTerminalMIDMatched > 0) {
                                            $apiTransdetails = 'MID = ' . $MID . ', TID = ' . $terminalID;
                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                            $stackerBatchID = $_stackerSummaryModel->updateEGMStackerBatchID($egmSessionID, $MID, $stackerSessionID, $AID);
                                            if ($stackerBatchID > 0) {
                                                $transMsg = 'Transaction successful.';
                                                $errorCode = 0;
                                                $apiStatus = 1;
                                                $referenceID = $stackerBatchID;
                                                $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, $stackerBatchID)));
                                                exit;
                                            } else {
                                                $transMsg = 'Transaction failed.';
                                                $errorCode = 5;
                                                $apiStatus = 2;
                                                $referenceID = 0;
                                                $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                                $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . "|";
                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                            }
                                        } else {
                                            $transMsg = 'Terminal and CardNumber does not match in EGM Session.';
                                            $errorCode = 75;
                                            $otherInfo = "TerminalName: " . $terminalName . " | MID: " . $MID . "|";
                                            Utilities::errorLogger($transMsg, $module, $otherInfo);
                                        }
                                    } else {
                                        $transMsg = 'Terminal has no active EGM session.';
                                        $errorCode = 25;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    }
                                } else {
                                    $transMsg = 'Terminal does not exist.';
                                    $errorCode = 3;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                }
                            } else {
                                $transMsg = 'Terminal has no active stacker session.';
                                $errorCode = 6;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            }
                        } else {
                            $transMsg = 'Invalid Membership Card Number.';
                            $errorCode = 8;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        }
                    } else {
                        $transMsg = 'Membership Card Number does not exists.';
                        $errorCode = 7;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    }
//                }  else {
//                        //******************* DISABLE Genesis Reload *********************//
//                        $transMsg = 'Reload using genesis terminal is temporarily disabled Please Use Cashier Load tab';
//                        $errorCode = 29;
//                        $module = 'GetStackerId';
//                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                        exit;
//                }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
        }
        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
    }

    //A function that verifies if the transaction was successful using TrackingID.
    public function actionVerifyLogStackerTransaction() {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'VerifyLogStackerTransaction';
        $APIMethodID = APILogsModel::API_METHOD_VERIFYLOGSTACKERTRANSACTION;

        if (isset($request['TrackingID'])) {
            if (($request['TrackingID']) == "") {
                $transMsg = 'Tracking ID is not set or is blank.';
                $errorCode = 10;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            } else {
                if (ctype_alnum($request['TrackingID'])) {
                    $trackingID = trim($request['TrackingID']);
                    $_stackerDetailsModel = new StackerDetailsModel();
                    $apiTransdetails = 'TrackingID = ' . $trackingID;
                    $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                    $countTrackingID = $_stackerDetailsModel->isTrackingIDExists($trackingID);
                    if ($countTrackingID > 0) {
                        $transMsg = 'Transaction successful.';
                        $errorCode = 0;
                        $apiStatus = 1;
                    } else {
                        $transMsg = 'Tracking ID does not exist.';
                        $errorCode = 9;
                        $apiStatus = 2;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    }
                    $this->_updateAPILogs($APIMethodID, $logID, $apiStatus);
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                }
            }
        } else {
            $transMsg = 'Tracking ID is not set or is blank.';
            $errorCode = 10;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
        }
        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
    }

    //A function that adds Terminal, StackerTagID, and SerialNumber each with corresponding status.
    public function actionAddStackerInfo() {
        $module = "AddStackerInfo";
        $APIMethodID = APILogsModel::API_METHOD_ADDSTACKERINFO;
        $transMsg = "";
        $errorCode = "";

        $request = $this->_readJsonRequest();
        if (isset($request['StackerTagID']) && isset($request['SerialNumber']) && isset($request['TerminalName'])) {
            if (($request['StackerTagID']) == "" || ($request['SerialNumber']) == "" || ($request['TerminalName']) == "") {
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            } else {
                $stackerTagID = trim($request['StackerTagID']);
                $serialNumber = trim($request['SerialNumber']);
                $status = 1;
                $terminalName = trim($request['TerminalName']);
                if (ctype_alnum($stackerTagID) && ctype_alnum($serialNumber) && is_numeric($status) && ctype_alnum($terminalName)) {
                    if (($status == CommonController::STACKER_INFO_STATUS_ON_STOCK) || ($status == CommonController::STACKER_INFO_STATUS_ACTIVE) || ($status == CommonController::STACKER_INFO_STATUS_DEACTIVATED)) {
                        $terminalName = Yii::app()->params['SitePrefix'] . $terminalName;
                        $stackerInfoModel = new StackerInfoModel();
                        $isTerminalExists = $stackerInfoModel->isTerminalExists($terminalName);
                        $isStackerTagIDExists = $stackerInfoModel->isStackerTagIDExists($stackerTagID);
                        $isSerialNumberExists = $stackerInfoModel->isSerialNumberExists($serialNumber);

                        if ($isStackerTagIDExists == 0) {
                            if ($isSerialNumberExists == 0) {
                                if ($isTerminalExists > 0) {
                                    $isTerminalActive = $stackerInfoModel->checkIfTerminalIsActive($terminalName);
                                    if ($isTerminalActive > 0) {
                                        $transMsg = 'Stacker may be active or onstock.';
                                        $errorCode = 15;
                                        $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
                                    } else {
                                        $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                        $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                        $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                        if ($stackerInfoID > 0) {
                                            $transMsg = 'Transaction successful.';
                                            $errorCode = 0;
                                            $apiStatus = 1;
                                            $referenceID = $stackerInfoID;
                                        } else {
                                            $transMsg = 'Failed to add stacker info.';
                                            $errorCode = 16;
                                            $apiStatus = 2;
                                            $referenceID = '';
                                            $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                            Utilities::errorLogger($transMsg, $module, $otherInfo);
                                        }
                                        $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                    }
                                } else {
                                    $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                    $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                    $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                    if ($stackerInfoID > 0) {
                                        $transMsg = 'Transaction successful.';
                                        $errorCode = 0;
                                        $apiStatus = 1;
                                        $referenceID = $stackerInfoID;
                                    } else {
                                        $transMsg = 'Failed to add stacker info.';
                                        $errorCode = 16;
                                        $apiStatus = 2;
                                        $referenceID = '';
                                        $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
                                    }
                                    $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                }
                            } else {
                                $serialNumberActive = $stackerInfoModel->checkIfSerialNumberIsActive($serialNumber);
                                if ($serialNumberActive > 0) {
                                    $transMsg = 'Serial Number is already active in another terminal.';
                                    $errorCode = 38;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                } else {
                                    if ($isTerminalExists > 0) {
                                        $isTerminalActive = $stackerInfoModel->checkIfTerminalIsActive($terminalName);
                                        if ($isTerminalActive > 0) {
                                            $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                            $transMsg = 'Stacker may be active or onstock.';
                                            $errorCode = 15;
                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        } else {
                                            $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                            if ($stackerInfoID > 0) {
                                                $transMsg = 'Transaction successful.';
                                                $errorCode = 0;
                                                $apiStatus = 1;
                                                $referenceID = $stackerInfoID;
                                            } else {
                                                $transMsg = 'Failed to add stacker info.';
                                                $errorCode = 16;
                                                $apiStatus = 2;
                                                $referenceID = '';
                                                $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                            }
                                            $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                        }
                                    } else {
                                        $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                        $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                        $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                        if ($stackerInfoID > 0) {
                                            $transMsg = 'Transaction successful.';
                                            $errorCode = 0;
                                            $apiStatus = 1;
                                            $referenceID = $stackerInfoID;
                                        } else {
                                            $transMsg = 'Failed to add stacker info.';
                                            $errorCode = 16;
                                            $apiStatus = 2;
                                            $referenceID = '';
                                            $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                            Utilities::errorLogger($transMsg, $module, $otherInfo);
                                        }
                                        $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                    }
                                }
                            }
                        } else {
                            $stackerTagIDActive = $stackerInfoModel->checkIfStackerTagIDIsActive($stackerTagID);
                            if ($stackerTagIDActive > 0) {
                                $transMsg = 'Stacker Tag ID is already active in another terminal.';
                                $errorCode = 37;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            } else {
                                $serialNumberActive = $stackerInfoModel->checkIfSerialNumberIsActive($serialNumber);
                                if ($serialNumberActive > 0) {
                                    $transMsg = 'Serial Number is already active in another terminal.';
                                    $errorCode = 37;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                } else {
                                    if ($isSerialNumberExists == 0) {
                                        if ($isTerminalExists > 0) {
                                            $isTerminalActive = $stackerInfoModel->checkIfTerminalIsActive($terminalName);
                                            if ($isTerminalActive > 0) {
                                                $transMsg = 'Stacker may be active or onstock.';
                                                $errorCode = 15;
                                                $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                            } else {

                                                $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                                $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                                if ($stackerInfoID > 0) {
                                                    $transMsg = 'Transaction successful.';
                                                    $errorCode = 0;
                                                    $apiStatus = 1;
                                                    $referenceID = $stackerInfoID;
                                                } else {
                                                    $transMsg = 'Failed to add stacker info.';
                                                    $errorCode = 16;
                                                    $apiStatus = 2;
                                                    $referenceID = '';
                                                    $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                }
                                                $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                            }
                                        } else {
                                            $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                            $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                            if ($stackerInfoID > 0) {
                                                $transMsg = 'Transaction successful.';
                                                $errorCode = 0;
                                                $apiStatus = 1;
                                                $referenceID = $stackerInfoID;
                                            } else {
                                                $transMsg = 'Failed to add stacker info.';
                                                $errorCode = 16;
                                                $apiStatus = 2;
                                                $referenceID = '';
                                                $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                Utilities::errorLogger($transMsg, $module, $otherInfo);
                                            }
                                            $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                        }
                                    } else {
                                        $serialNumberActive = $stackerInfoModel->checkIfSerialNumberIsActive($serialNumber);
                                        if ($serialNumberActive > 0) {
                                            $transMsg = 'Serial Number is already active in another terminal.';
                                            $errorCode = 38;
                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        } else {
                                            if ($isTerminalExists > 0) {
                                                $isTerminalActive = $stackerInfoModel->checkIfTerminalIsActive($terminalName);
                                                if ($isTerminalActive > 0) {
                                                    $transMsg = 'Stacker may be active or onstock.';
                                                    $errorCode = 15;
                                                    $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                } else {
                                                    $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                                    $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                                    $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                                    if ($stackerInfoID > 0) {
                                                        $transMsg = 'Transaction successful.';
                                                        $errorCode = 0;
                                                        $apiStatus = 1;
                                                        $referenceID = $stackerInfoID;
                                                    } else {
                                                        $transMsg = 'Failed to add stacker info.';
                                                        $errorCode = 16;
                                                        $apiStatus = 2;
                                                        $referenceID = '';
                                                        $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                    }
                                                    $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                                }
                                            } else {
                                                $apiTransdetails = 'StackerTagID = ' . $stackerTagID . ', SN = ' . $serialNumber . ', TerminalName = ' . $terminalName;
                                                $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                                                $stackerInfoID = $stackerInfoModel->addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
                                                if ($stackerInfoID > 0) {
                                                    $transMsg = 'Transaction successful.';
                                                    $errorCode = 0;
                                                    $apiStatus = 1;
                                                    $referenceID = $stackerInfoID;
                                                } else {
                                                    $transMsg = 'Failed to add stacker info.';
                                                    $errorCode = 16;
                                                    $apiStatus = 2;
                                                    $referenceID = '';
                                                    $otherInfo = "StackerTagID: " . $stackerTagID . " | SerialNumber: " . $serialNumber . " | TerminalName: " . $terminalName . " |";
                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
                                                }
                                                $this->_updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $transMsg = 'Invalid Status.';
                        $errorCode = 12;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
        }
        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
    }

    //A function that updates the status (status only) of the stacker information.
    public function actionUpdateStackerInfo() {
        $module = "UpdateStackerInfo";
        $transMsg = "";
        $errorCode = "";

        $request = $this->_readJsonRequest();
        if (isset($request['StackerTagID']) && isset($request['SerialNumber']) && isset($request['Status']) && isset($request['TerminalName'])) {
            if (($request['StackerTagID']) == "" || ($request['SerialNumber']) == "" || ($request['Status']) == "" || ($request['TerminalName']) == "") {
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
            } else {
                $stackerTagID = trim($request['StackerTagID']);
                $serialNumber = trim($request['SerialNumber']);
                $status = trim($request['Status']);
                $terminalName = trim($request['TerminalName']);

                if (ctype_alnum($stackerTagID) && ctype_alnum($serialNumber) && is_numeric($status) && ctype_alnum($terminalName)) {

                    if (($status == CommonController::STACKER_INFO_STATUS_ON_STOCK) || ($status == CommonController::STACKER_INFO_STATUS_ACTIVE) || ($status == CommonController::STACKER_INFO_STATUS_DEACTIVATED)) {
                        $terminalName = Yii::app()->params['SitePrefix'] . $terminalName;
                        $stackerInfoModel = new StackerInfoModel();
                        $isStackerTagIDExists = $stackerInfoModel->isStackerTagIDExists($stackerTagID);

                        if ($isStackerTagIDExists > 0) {
                            $isSerialNumberExists = $stackerInfoModel->isSerialNumberExists($serialNumber);
                            if ($isSerialNumberExists > 0) {
                                $isTerminalExists = $stackerInfoModel->isTerminalExists($terminalName);
                                if ($isTerminalExists > 0) {
                                    $terminalStatus = $stackerInfoModel->checkTerminalStatus($terminalName, $serialNumber);

                                    if ($status == $terminalStatus) {
                                        $stat = $terminalStatus['Status'];
                                        if ($stat == CommonController::STACKER_INFO_STATUS_ON_STOCK) {
                                            $statusStatement = 'On Stock';
                                        } else if ($stat == CommonController::STACKER_INFO_STATUS_ACTIVE) {
                                            $statusStatement = 'Active';
                                        } else if ($stat == CommonController::STACKER_INFO_STATUS_DEACTIVATED) {
                                            $statusStatement = 'Deactivated';
                                        }

                                        $transMsg = 'Terminal status is already ' . $statusStatement . '.';
                                        $errorCode = 25;
                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                                    } else {
                                        $stackerInfo = $stackerInfoModel->getStackerInfoIDByData($stackerTagID, $serialNumber, $terminalName);
                                        if (!empty($stackerInfo)) {
                                            $stackerInfoID = $stackerInfo['StackerInfoID'];
                                            $updateStackerInfo = $stackerInfoModel->updateStackerInfo($stackerInfoID, $status);

                                            if ($updateStackerInfo > 0) {
                                                $transMsg = 'Transaction successful.';
                                                $errorCode = 0;
                                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                                            } else {
                                                $transMsg = 'Failed to update stacker info.';
                                                $errorCode = 20;
                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                                            }
                                        } else {
                                            $transMsg = 'One or more values did not matched.';
                                            $errorCode = 19;
                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                                        }
                                    }
                                } else {
                                    $transMsg = 'Terminal does not exist.';
                                    $errorCode = 3;
                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                                }
                            } else {
                                $transMsg = 'Serial Number does not exist.';
                                $errorCode = 18;
                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                            }
                        } else {
                            $transMsg = 'Stacker Tag ID does not exist.';
                            $errorCode = 17;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                        }
                    } else {
                        $transMsg = 'Invalid Status.';
                        $errorCode = 12;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                    }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
        }
    }

    //A function that returns the information of the stacker such as it's terminal and status
    public function actionGetStackerInfo() {
                        //******************* DISABLE Genesis Reload *********************//
//$transMsg = 'Reload using genesis terminal is temporarily disabled Please Use Cashier Load tab';
//$errorCode = 29;
//$module = "GetStackerInfo";
//Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//$this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//exit;
        $module = "GetStackerInfo";
        $APIMethodID = APILogsModel::API_METHOD_GETSTACKERINFO;
        $transMsg = "";
        $errorCode = "";

        $request = $this->_readJsonRequest();
        if (isset($request['StackerTagID'])) {
            if ($request['StackerTagID'] == "") {
                $transMsg = 'StackerTagID is not set or is blank.';
                $errorCode = 1;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
            } else {
                $stackerTagID = trim($request['StackerTagID']);

                if (ctype_alnum($stackerTagID)) {
                    $stackerInfoModel = new StackerInfoModel();
                    $isStackerTagIDExists = $stackerInfoModel->isStackerTagIDExists($stackerTagID);
                    if ($isStackerTagIDExists > 0) {
                        $stackerInfo = $stackerInfoModel->getStackerInfoByStackerTagAndSerial($stackerTagID);
                        $apiTransdetails = 'StackerTagID = ' . $stackerTagID;
                        $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
                        if (!empty($stackerInfo)) {
                            $terminalName = $stackerInfo['TerminalName'];
                            $status = $stackerInfo['Status'];
                            $serialNumber = $stackerInfo['SerialNumber'];
                            $transMsg = 'Transaction Successful.';
                            $errorCode = 0;
                            $apiStatus = 1;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, '', $terminalName, $status, '', '', '', '', '', '', '', $serialNumber)));
                        } else {
                            $transMsg = 'Stacker Tag ID and Serial Number did not match.';
                            $errorCode = 21;
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                        }
                        $this->_updateAPILogs($APIMethodID, $logID, $apiStatus);
                    } else {
                        $transMsg = 'Stacker Tag ID does not exist.';
                        $errorCode = 17;
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                    }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
        }
    }

    //A function that cancels a deposit using StackerBatchID and TerminalName.
//    public function actionCancelDeposit() {
//        $module = "CancelDeposit";
//        $APIMethodID = APILogsModel::API_METHOD_CANCELDEPOSIT;
//        $transMsg = "";
//        $errorCode = "";
//        $isCancelled = "";
//        $amount = 0;
//        $voucherTicketBarcode = "";
//        $dateTime = "";
//        $expirationDate = "";
//        $sequenceNo = "";
//
//        $request = $this->_readJsonRequest();
//        if (isset($request['TrackingID']) && isset($request['StackerBatchID']) && isset($request['TerminalName'])) {
//            if (($request['TrackingID']) == "" || ($request['StackerBatchID']) == "" || ($request['TerminalName']) == "") {
//                $transMsg = 'One or more fields is not set or is blank.';
//                $errorCode = 1;
//                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//            } else {
//                $trackingID = trim($request['TrackingID']);
//                $stackerBatchID = trim($request['StackerBatchID']);
//                $terminalName = trim($request['TerminalName']);
//                $sc = Yii::app()->params['SitePrefix'] . $terminalName;
//                $_stackerDetailsModel = new StackerDetailsModel();
//                $_stackerSessionsModel = new StackerSessionsModel();
//                $_stackerSummaryModel = new StackerSummaryModels();
//                $_terminalSessions  = new TerminalSessionsModel();
//                $trackingIDExistsInStacker = $_stackerDetailsModel->isTrackingIDExists($trackingID);
//
//                if (is_numeric($stackerBatchID) && ctype_alnum($terminalName)) {
//                    if ($trackingIDExistsInStacker == 0) {
//                        $_ticketsModel = new TicketsModel();
//                        $trackingIDExistsInTickets = $_ticketsModel->isTrackingIDExists($trackingID);
//                        if ($trackingIDExistsInTickets == 0) {
//                            $stackerSessionID = $_stackerSessionsModel->getStackerSessionIDByTerminalName($sc);
//                            if (!empty($stackerSessionID)) {
//                                $amount = $_stackerSummaryModel->getDeposit($stackerBatchID, $stackerSessionID);
//
//                                $_TerminalsModel = new TerminalsModel();
//                                $tc = Yii::app()->params['SitePrefix2'] . $terminalName;
//                                $terminals = $_TerminalsModel->getTerminalIDByCode($tc);
//                                if (!empty($terminals)) {
//                                    $_EGMSessionsModel = new EGMSessionsModel();
//                                    $isEGMSession = $_EGMSessionsModel->checkSessionIfExists($terminals[0]['TerminalID']);
//                                    if ($isEGMSession > 0) { //If Terminal has an EGM session
//                                        $terminalID = $terminals[0]['TerminalID']; //Regular
//                                        $isEGMSessionExists = $isEGMSession; //Regular session
//                                    } else { //If Terminal has no Regular EGM session
//                                        $terminalID = $terminals[1]['TerminalID']; //VIP
//                                        $isEGMSessionExists = $_EGMSessionsModel->checkSessionIfExists($terminalID); //VIP session
//                                    }
//                                    $isStackerBatchAndSessionMatched = $_stackerSummaryModel->isStackerBatchIDAndTerminalMatched($stackerBatchID, $stackerSessionID);
//                                    if ($isStackerBatchAndSessionMatched > 0) {
//                                        if ($isEGMSessionExists > 0) {
//                                            $isStackerBatchAndTerminalMatched = $_EGMSessionsModel->isTerminalStackerBatchIDMatched($terminalID, $stackerBatchID);
//                                            if($isStackerBatchAndTerminalMatched > 0) {
//                                            $egmSessionID = $_EGMSessionsModel->getEGMSessionIDByTerminalID($terminalID);
//                                            $MID = $_EGMSessionsModel->getMID($egmSessionID);
//                                            //Each Site has a Virtual Cashier assigned
//                                            $accountTypeID = CommonController::ACOUNTTYPE_ID_VIRTUAL_CASHIER;
//                                            $_accountsModel = new AccountsModel();
//                                            $AID = $_accountsModel->getAIDByAccountTypeIDAndTerminalID($accountTypeID, $terminalID); //AID of Virtual Cashier
//                                            $voucherTicketAPIWrapper = new VoucherTicketAPIWrapper();
//                                            $source = CommonController::SOURCE_EGM;
//                                            $membershipCardNumber = $_stackerSummaryModel->getCardNumber($stackerBatchID, $stackerSessionID, $MID);
//                                            $amount = $_stackerSummaryModel->getDeposit($stackerBatchID, $stackerSessionID);
//
//                                            if ((int) $amount > 0) {
//                                                $purpose = CommonController::PURPOSE_VOID;
//                                                $voucherTicketBarcode = Helpers::generate_ticket();
//
//                                                if (isset($voucherTicketBarcode) || $voucherTicketBarcode != "") {
//
//                                                    $addTicket = $voucherTicketAPIWrapper->addTicket($terminalName, $amount, $AID, $source, $membershipCardNumber, $purpose, $stackerBatchID, $trackingID, $voucherTicketBarcode);
//
//                                                    if (isset($addTicket['AddTicket']['ErrorCode'])) {
//                                                        $isCancelled = 0;
//                                                        if (isset($addTicket['AddTicket']['TransactionMessage']) && $addTicket['AddTicket']['TransactionMessage'] == "") {
//                                                            $transMsg = $addTicket['AddTicket']['TransactionMessage'];
//                                                            $errorCode = $addTicket['AddTicket']['ErrorCode'];
//                                                            $amount = $addTicket['AddTicket']['Amount'];
//                                                            $voucherTicketBarcode = $addTicket['AddTicket']['VoucherTicketBarcode'];
//                                                            $dateTime = $addTicket['AddTicket']['DateTime'];
//                                                            $expirationDate = $addTicket['AddTicket']['ExpirationDate'];
//                                                            $sequenceNo = $addTicket['AddTicket']['SequenceNo'];
//                                                            $apiTransdetails = 'SBatchID = ' . $stackerBatchID . ', TID = ' . $terminalID;
//                                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
//                                                            $isCancelled = $_EGMSessionsModel->cancelDeposit($terminalID, $stackerBatchID, $AID);
//                                                            switch ($isCancelled) {
//                                                                case 0 :
//                                                                    $transMsg = 'Transaction was already canceled.';
//                                                                    $errorCode = 23;
//                                                                    $isCancelled = "";
//                                                                    $apiStatus = 2;
//                                                                    $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                                    break;
//                                                                case 1 :
//                                                                    $transMsg = 'Transaction successful.';
//                                                                    $errorCode = 0;
//                                                                    $isCancelled = 1;
//                                                                    $apiStatus = 1;
//                                                                    break;
//                                                                case 2 :
//                                                                    $transMsg = 'Transaction failed.';
//                                                                    $errorCode = 5;
//                                                                    $isCancelled = "";
//                                                                    $apiStatus = 2;
//                                                                    $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                                    break;
//                                                                default:
//                                                                    $transMsg = 'Transaction failed.';
//                                                                    $errorCode = 5;
//                                                                    $isCancelled = "";
//                                                                    $apiStatus = 2;
//                                                                    $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                                    break;
//                                                            }
//
//                                                            $this->_updateAPILogs($APIMethodID, $logID, $apiStatus);
//                                                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, "", "", "", $isCancelled, "", $amount, $voucherTicketBarcode, $dateTime, $expirationDate, $sequenceNo)));
//                                                            exit;
//                                                        } else if (isset($addTicket['AddTicket']['TransactionMessage']) && $addTicket['AddTicket']['TransactionMessage'] != "") {
//                                                            $transMsg = $addTicket['AddTicket']['TransactionMessage'];
//                                                            $errorCode = $addTicket['AddTicket']['ErrorCode'];
//
//                                                            $apiTransdetails = 'SBatchID = ' . $stackerBatchID . ', TID = ' . $terminalID;
//                                                            $logID = $this->_insertIntoAPILogs($APIMethodID, $apiTransdetails);
//                                                            //check if terminal has an active terminal session
//                                                            $hasActiveSession = $_terminalSessions->checkIfHasActiveSession($terminalID, $MID);
//                                                            if ($hasActiveSession == 0)
//                                                            {
//                                                                $isCancelled = $_EGMSessionsModel->cancelDeposit($terminalID, $stackerBatchID, $AID);
//                                                                switch ($isCancelled) {
//                                                                    case 0 :
//                                                                        $transMsg = 'Transaction was already canceled.';
//                                                                        $errorCode = 23;
//                                                                        $isCancelled = "";
//                                                                        $apiStatus = 2;
//                                                                        $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                                        break;
//                                                                    case 1 :
//                                                                        $transMsg = 'Transaction successful.';
//                                                                        $errorCode = 0;
//                                                                        $isCancelled = 1;
//                                                                        $apiStatus = 1;
//                                                                        break;
//                                                                    case 2 :
//                                                                        $transMsg = 'Transaction failed.';
//                                                                        $errorCode = 5;
//                                                                        $isCancelled = "";
//                                                                        $apiStatus = 2;
//                                                                        $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                                        break;
//                                                                    default:
//                                                                        $transMsg = 'Transaction failed.';
//                                                                        $errorCode = 5;
//                                                                        $isCancelled = "";
//                                                                        $apiStatus = 2;
//                                                                        $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                                        break;
//                                                                }
//                                                                $amount = $addTicket['AddTicket']['Amount'];
//                                                                $voucherTicketBarcode = $addTicket['AddTicket']['VoucherTicketBarcode'];
//                                                                $dateTime = $addTicket['AddTicket']['DateTime'];
//                                                                $expirationDate = $addTicket['AddTicket']['ExpirationDate'];
//                                                                $sequenceNo = $addTicket['AddTicket']['SequenceNo'];
//                                                                $this->_updateAPILogs($APIMethodID, $logID, $apiStatus);
//                                                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, "", "", "", $isCancelled, "", $amount, $voucherTicketBarcode, $dateTime, $expirationDate, $sequenceNo)));
//                                                                exit;
//                                                            }  
//                                                            else
//                                                            {
//                                                                $transMsg = 'There is an existing terminal session for the terminal.';
//                                                                $errorCode = 65;
//                                                                $isCancelled = "";
//                                                            }
//                                                        } else {
//                                                            $amount = 0;
//                                                            $transMsg = $addTicket['AddTicket']['TransactionMessage'];
//                                                            $errorCode = $addTicket['AddTicket']['ErrorCode'];
//                                                            $isCancelled = "";
//                                                            $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                            Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, "", "", "", $isCancelled, "", $amount, $voucherTicketBarcode, $dateTime, $expirationDate, $sequenceNo)));
//                                                            exit;
//                                                        }
//                                                    } else {
//                                                        $amount = 0;
//                                                        $transMsg = "Can't connect to VMS Server.";
//                                                        $errorCode = 40;
//                                                        $isCancelled = "";
//                                                        $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                        Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                    }
//                                                } else {
//                                                    $amount = 0;
//                                                    $transMsg = "Failed to generate ticket.";
//                                                    $errorCode = 42;
//                                                    $isCancelled = "";
//                                                    $otherInfo = "StackerBatchID:" . $stackerBatchID . " | TerminalName: " . $terminalName . " | ";
//                                                    Utilities::errorLogger($transMsg, $module, $otherInfo);
//                                                }
//                                            } else {
//                                                $amount = 0;
//                                                $transMsg = "Deposit amount must be greater than Php " . $amount . ".00.";
//                                                $errorCode = 41;
//                                                $isCancelled = "";
//                                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                            }
//                                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode, "", "", "", $isCancelled)));
//                                            } else {
//                                        $transMsg = 'Terminal and StackerBatchID does not match in EGM session.';
//                                        $errorCode = 44;
//                                        $isCancelled = "";
//                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                                    }
//                                    } else {
//                                        $transMsg = 'Terminal has no active EGM session.';
//                                        $errorCode = 25;
//                                        $isCancelled = "";
//                                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                                    }
//                                    } else {
//                                            $transMsg = 'Stacker Batch ID and Terminal does not match in stacker session.';
//                                            $errorCode = 39;
//                                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                                        }
//                                } else {
//                                    $transMsg = 'Terminal does not exist.';
//                                    $errorCode = 3;
//                                    $isCancelled = "";
//                                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                                }
//                            } else {
//                                $transMsg = 'Terminal has no active stacker session.';
//                                $errorCode = 6;
//                                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                            }
//                        } else {
//                            $transMsg = 'Tracking ID already exists';
//                            $errorCode = 24;
//                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                        }
//                    } else {
//                        $transMsg = 'Tracking ID already exists';
//                        $errorCode = 24;
//                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                        $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                    }
//                } else {
//                    $transMsg = 'Invalid input parameter.';
//                    $errorCode = 2;
//                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//                }
//            }
//        } else {
//            $transMsg = 'One or more fields is not set or is blank.';
//            $errorCode = 1;
//            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
//        }
//    }

    public function actionUpdateStackerSummaryStatus() {
        $module = "UpdateStackerSummaryStatus";
        $transMsg = "";
        $errorCode = "";

        $request = $this->_readJsonRequest();

        if (isset($request['TerminalName']) && isset($request['MembershipCardNumber']) && isset($request['TransType']) && isset($request['CasinoID']) && isset($request['AID'])) {
            if (($request['TerminalName'] == "") || ($request['MembershipCardNumber'] == "") || ($request['TransType'] == "") || ($request['CasinoID'] == "") || ($request['AID'] == "")) {
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
            } else {
                $terminalName = trim($request['TerminalName']);
                $cardNumber = trim($request['MembershipCardNumber']);
                $transType = trim($request['TransType']);
                $casinoID = trim($request['CasinoID']);
                $aid = trim($request['AID']);
                $_membersModel = new MembersModel();
                $_memberServicesModel = new MemberServicesModel();
                $_memberCardsModel = new MemberCardsModel();
                if (ctype_alnum($terminalName) && ctype_alnum($cardNumber) && is_numeric($transType) && is_numeric($casinoID) && is_numeric($aid)) {
                    $MID = $_memberCardsModel->getMID($cardNumber);
                    if (!empty($MID)) {
                        $isCardVip = $_memberServicesModel->isVip($MID);
                        $sc = Yii::app()->params['SitePrefix'] . $request['TerminalName'];
                        if ($isCardVip > 0) {
                            $sc = $sc . 'Vip';
                        } else {
                            $isCardNumberVip = $_membersModel->isVip($MID);
                            if ($isCardNumberVip > 0) {
                                $sc = $sc . 'Vip';
                            } else {
                                $sc = $sc;
                            }
                        }
                        //$egmsessionid = $gamingSessionsModel->insertEgmSession($mid, $terminalID, $terminalName, $casinoID, $aid);
                    } else {
                        $transMsg = "Invalid Membership Card Number";
                        $errCode = 24;
                        Utilities::log($message);
                        $this->_sendResponse(200, CommonController::creteEgmSessionResponse(1, '', $transMsg, $errCode));
                    }
                } else {
                    $transMsg = 'Invalid input parameter.';
                    $errorCode = 2;
                    Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
                }
            }
        } else {
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::StackerRetMsg($module, $transMsg, $errorCode)));
        }
    }

    private function _insertIntoAPILogs($APIMethodID, $apiTransdetails, $trackingID = '') {
        $_APILogsModel = new APILogsModel();
        $logID = $_APILogsModel->insertIntoAPILogs($APIMethodID, $apiTransdetails, $trackingID);

        if (empty($logID)) {
            $transMsg = 'Failed to inserting api log.';
            Utilities::log("Error Message: " . $transMsg);
            return 0;
        } else {
            return $logID;
        }
    }

    private function _updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID = '') {
        $_APILogsModel = new APILogsModel();
        $updateLog = $_APILogsModel->updateAPILogs($APIMethodID, $logID, $apiStatus, $referenceID);
        if ($updateLog == false) {
            $transMsg = 'Failed to update api log. LogID: ' . $logID;
            Utilities::log("Error Message: " . $transMsg);
        }
        return $updateLog;
    }

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

}

?>
