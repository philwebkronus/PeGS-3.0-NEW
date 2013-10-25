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

    /**
     * @author Edson Perez
     * @datecreated 09/12/13
     * @purpose verify whether a coupon, ticket and tracking id is valid and can be used to transact
     */
    public function actionVerify(){
        Yii::import('application.controllers.*');
        $request = $this->_readJsonRequest();
        
        $commonController = new CommonController();
                    
        $trackingid = "";
        $voucherCode = "";
        $dateCreated = "";
        $loyaltyCreditable = 0;
        $amount = 0;
        $result = array();
        $status = 0;
            
        if(isset($request['aid']) && is_numeric($request['aid'])
           && isset($request['source']) && is_numeric($request['source'])) {           
            $AID = trim($request['aid']);
            $source = trim($request['source']);
            
            //will be called for validation of coupons
            if(isset($request['vouchercode']) && ctype_alnum($request['vouchercode']) && strlen($request['vouchercode']) > 0){
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
           } 
           
           //will be called for verification / fulfillment of transaction
           elseif(isset($request['trackingid']) && ctype_alnum($request['trackingid']) && 
                    strlen($request['trackingid']) > 0 ){
                
                $trackingid = trim($request['trackingid']);

                switch ($source) {
                    case self::SOURCE_EGM:
                        //todo
                        break;
                    case self::SOURCE_CASHIER:
                        $_couponModel = new CouponModel();

                        //verify the tracking id
                        $trackResult = $_couponModel->verifyCouponTransaction($trackingid);
                        if ((int) $trackResult['ctrtracking'] > 0) {
                            $errorCode = 0;
                            $status = 1;
                            $transMsg = "Transaction Approved";
                            $loyaltyCreditable = $trackResult['LoyaltyCreditable'];
                            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        } else {
                            $errorCode = 1;
                            $transMsg = "Tracking ID not found.";
                            Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        }
                        break;
                    case self::SOURCE_KAPI;
                    //todo
                    default :
                        $errorCode = 2;
                        $transMsg = "Source is invalid.";
                        Utilities::log("Error Message: " . $transMsg . " ErrorCode: " . $errorCode);
                        $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated);
                        break;
                }

                $details = "VerifyVoucher : " . $transMsg;
                AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, $status);
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
                    
        if(isset($request["vouchercode"]) && ctype_alnum($request["vouchercode"])
            && isset($request["aid"]) && is_numeric($request["aid"])
            && isset($request['trackingid']) && ctype_alnum($request['trackingid'])
            && isset($request['terminalid']) && is_numeric($request['terminalid'])
            && isset($request['source']) && is_numeric($request['source'])
            && isset($request['mid']) && is_numeric($request['mid'])
            && isset($request['siteid']) && is_numeric($request['siteid']))
        {
            $voucherCode = trim($request["vouchercode"]);
            $AID = trim($request["aid"]);
            $trackingID = trim($request['trackingid']);
            $terminalID = trim($request['terminalid']);
            $source = trim($request['source']);
            $MID = trim($request['mid']);
            $siteID = trim($request['siteid']);
            
            switch ($source){
                case self::SOURCE_EGM:
                    //todo
                    break;
                case self::SOURCE_CASHIER:
                    $result = $commonController->useCoupon($voucherCode, $AID,$trackingID,
                            $terminalID, $source, self::COUPON, $MID, $siteID);
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

        $this->_sendResponse(200, CJSON::encode(array("UseVoucher" => $result)));
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10-22-13
     * @purpose method for validating Voucher/Ticket
     */
    public function actionVoucherTicketValidation() {
        Yii::import('application.controllers.*');
        $_couponModel = new CouponModel();
        $_ticketModel = new TicketModel();
        $_terminalsModel = new TerminalsModel();
        $_eGMRequestLogsModel = new EGMRequestLogsModel();
        $request = $this->_readJsonRequest();
        $vtcode = "";
        $amount = "";
        $datecreated = "";
        $vouchertypeid = "";
        $loyaltycreditable = "";
        $transMsg = "";
        $errCode = "";

        //Check if Terminal Name is not blank. If not blank then
        if (((isset($request['TrackingID']) && $request['TrackingID']) != '') && ((isset($request['TerminalName']) && $request['TerminalName']) != '') && ((isset($request['VoucherTicketBarcode']) && $request['VoucherTicketBarcode']) != '')) {

            $trackingID = htmlentities($request['TrackingID']);
            $terminalName = htmlentities($request['TerminalName']);
            $voucherTicketBarcode = htmlentities($request['VoucherTicketBarcode']);
            $source = htmlentities($request['Source']);

            //If Input is valid then
            if (Utilities::validateInput($trackingID) && Utilities::validateInput($terminalName) && Utilities::validateInput($voucherTicketBarcode) && Utilities::validateInput($source)) {
                $egmrequestlogsdata = $_eGMRequestLogsModel->getStatusByTrackingId($trackingID);
                if (!empty($egmrequestlogsdata)) {

                    $trackingIdStatus = $egmrequestlogsdata[0]['Status'];

                    if (($trackingIdStatus == CommonController::TRACKING_ID_STATUS_SUCCESSFUL) || ($trackingIdStatus == CommonController::TRACKING_ID_STATUS_FULFILLMENT_APPROVED)) {
                        $terminalCode = Yii::app()->params['sitePrefix'] . $terminalName;
                        $terminalID = $_terminalsModel->getTerminalIDfromterminals($terminalCode);

                        if (!empty($terminalID)) {

                            $couponData = $_couponModel->getCouponDataByCode($voucherTicketBarcode);

                            if (!empty($couponData)) {
                                $vTerminalID = $_couponModel->getTerminalIDByCouponCode($voucherTicketBarcode);
                                if ($terminalID == $vTerminalID['TerminalID']) {

                                    if ((isset($request['AID']) && $request['AID']) != '') {

                                        $aid = htmlentities($request['AID']);
                                        if (Utilities::validateInput($aid)) {
                                            $findAID = $_couponModel->getAIDByCouponCode($voucherTicketBarcode);
                                            $countFindAID = count($findAID);
                                            if ($countFindAID > 0) {
                                                $couponID = $_couponModel->getCouponIDByValues($trackingID, $voucherTicketBarcode, $source);
                                                $countCouponID = count($couponID);
                                                if ($countCouponID > 0) {
                                                    $vtcode = $couponData[0]['CouponCode'];
                                                    $amount = $couponData[0]['Amount'];
                                                    $datecreated = $couponData[0]['DateCreated'];
                                                    $vouchertypeid = CommonController::VOUCHER_TYPE_COUPON;
                                                    $loyaltycreditable = $couponData[0]['LoyaltyCreditable'];
                                                    $status = $couponData[0]['Status'];

                                                    switch ($status) {

                                                        case CommonController::VOUCHER_STATUS_ACTIVE:
                                                            $transMsg = "Voucher/Ticket is unclaimed.";
                                                            $errorCode = 0;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_VOID:
                                                            $transMsg = "Voucher/Ticket is void.";
                                                            $errorCode = 1;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_INACTIVE:
                                                            $transMsg = "Voucher/Ticket is not activated.";
                                                            $errorCode = 2;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_USED:
                                                            $transMsg = "Voucher/Ticket is already used.";
                                                            $errorCode = 3;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_CLAIMED:
                                                            $transMsg = "Voucher/Ticket is already claimed.";
                                                            $errorCode = 3;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_REIMBURSED:
                                                            $transMsg = "Voucher/Ticket is already reimbursed.";
                                                            $errorCode = 3;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_EXPIRED:
                                                            $transMsg = "Voucher/Ticket is already expired.";
                                                            $errorCode = 3;
                                                            break;

                                                        case CommonController::VOUCHER_STATUS_CANCELLED:
                                                            $transMsg = "Voucher/Ticket is cancelled.";
                                                            $errorCode = 3;
                                                            break;
                                                    }
                                                } else {
                                                    $transMsg = "One or more fields have their values mismatched.";
                                                    $errorCode = 3;
                                                    Utilities::log($transMsg);
                                                }
                                            } else {
                                                $transMsg = "Voucher/Ticket cannot be used in Deposit/Reload in Cashier.";
                                                $errorCode = 3;
                                                Utilities::log($transMsg);
                                            }
                                        } else {
                                            $transMsg = "Parameter Error.";
                                            $errorCode = 3;
                                        }
                                    } else {
                                        $transMsg = "AID is required for Cashier.";
                                        $errorCode = 3;
                                    }
                                    Utilities::log($transMsg);
                                    $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                } else {
                                    $transMsg = "Voucher/Ticket is not allowed to used for this site.";
                                    $errorCode = 3;
                                    Utilities::log($transMsg);
                                    $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                }
                            } else {
                                $transMsg = "One or more fields have their values mismatched.";
                                $errorCode = 3;
                                Utilities::log($transMsg);
                                $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                            }
                        } else {
                            $ticketData = $_ticketModel->getTicketDataByCode($voucherTicketBarcode);
                            if (!empty($ticketData)) {
                                if ($terminalID == $_ticketModel->getTerminalIDByTicketCode($voucherTicketBarcode)) {
                                    $findAID = $_ticketModel->getAIDByTicketCode($voucherTicketBarcode);
                                    $countFindAID = count($findAID);
                                    if ($countFindAID > 0) {
                                        if ((isset($request['Source']) && $request['Source']) != '') {
                                            if ((isset($request['AID']) && $request['AID']) != '') {

                                                $aid = htmlentities($request['AID']);
                                                if (Utilities::validateInput($aid)) {
                                                    $ticketID = $_ticketModel->getTicketIDByValuesWithAID($trackingID, $voucherTicketBarcode, $source);
                                                } else {
                                                    $transMsg = "Parameter Error.";
                                                    $errorCode = 3;
                                                    Utilities::log($transMsg);
                                                    $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                                    exit;
                                                }
                                            } else {
                                                $ticketID = $_ticketModel->getTicketIDByValuesWithoutAID($trackingID, $voucherTicketBarcode, $source);
                                            }

                                            $countTicketID = count($ticketID);
                                            if ($countTicketID > 0) {
                                                $vtcode = $ticketData[0]['CouponCode'];
                                                $amount = $ticketData[0]['Amount'];
                                                $datecreated = $ticketData[0]['DateCreated'];
                                                $vouchertypeid = CommonController::VOUCHER_TYPE_TICKET;
                                                $loyaltycreditable = $ticketData[0]['LoyaltyCreditable'];
                                                $status = $ticketData[0]['Status'];

                                                switch ($status) {

                                                    case CommonController::VOUCHER_STATUS_ACTIVE:
                                                        $transMsg = "Voucher/Ticket is unclaimed.";
                                                        $errorCode = 0;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_VOID:
                                                        $transMsg = "Voucher/Ticket is void.";
                                                        $errorCode = 1;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_INACTIVE:
                                                        $transMsg = "Voucher/Ticket is not activated.";
                                                        $errorCode = 2;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_USED:
                                                        $transMsg = "Voucher/Ticket is already used.";
                                                        $errorCode = 3;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_CLAIMED:
                                                        $transMsg = "Voucher/Ticket is already claimed.";
                                                        $errorCode = 3;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_REIMBURSED:
                                                        $transMsg = "Voucher/Ticket is already reimbursed.";
                                                        $errorCode = 3;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_EXPIRED:
                                                        $transMsg = "Voucher/Ticket is already expired.";
                                                        $errorCode = 3;
                                                        break;

                                                    case CommonController::VOUCHER_STATUS_CANCELLED:
                                                        $transMsg = "Voucher/Ticket is cancelled.";
                                                        $errorCode = 3;
                                                        break;
                                                }
                                            } else {
                                                $transMsg = "One or more fields have their values mismatched.";
                                                $errorCode = 3;
                                                Utilities::log($transMsg);
                                            }
                                            Utilities::log($transMsg);
                                            $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                        } else {
                                            $transMsg = "Source is required for EGM.";
                                            $errorCode = 3;
                                            Utilities::log($transMsg);
                                            $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                        }
                                    } else {
                                        $transMsg = "Voucher/Ticket cannot be used in Deposit/Reload in Cashier.";
                                        $errorCode = 3;
                                        Utilities::log($transMsg);
                                        $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                    }
                                } else {
                                    $transMsg = "Voucher/Ticket is not allowed to used for this site.";
                                    $errorCode = 3;
                                    Utilities::log($transMsg);
                                    $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                                }
                            } else {
                                $transMsg = "Voucher/Ticket does not exists.";
                                $errCode = 2;
                                Utilities::log($transMsg);
                                $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                            }
                        }
                    } else if ($trackingIdStatus == CommonController::TRACKING_ID_STATUS_PENDING) {
                        $transMsg = "Tracking ID has a pending transaction.";
                        $errCode = 2;
                        Utilities::log($transMsg);
                        $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                    } else if ($trackingIdStatus == CommonController::TRACKING_ID_STATUS_FAILED) {
                        $transMsg = "Tracking ID has a failed transaction.";
                        $errCode = 2;
                        Utilities::log($transMsg);
                        $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                    } else if ($trackingIdStatus == CommonController::TRACKING_ID_STATUS_FULFILLMENT_DENIED) {
                        $transMsg = "Tracking ID has been denied.";
                        $errCode = 2;
                        Utilities::log($transMsg);
                        $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                    }
                } else {
                    $transMsg = "Tracking ID does not exists.";
                    $errCode = 2;
                    Utilities::log($transMsg);
                    $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
                }
            }
            //If Input has invalid characters then
            else {
                $transMsg = "Parameter Error.";
                $errCode = 2;
                Utilities::log($transMsg);
                $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
            }
            //If has blank Input then
        } else {
            $transMsg = "One or more field is not set or is blank.";
            $errCode = 2;
            Utilities::log($transMsg);
            $this->_sendResponse(200, CommonController::voucherTicketValidationResponse($vtcode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $transMsg, $errCode));
        }
    }

    /**
     * @author Edson Perez
     * @datecreated 09-16-13
     * @purpose generic api method of using whether coupon or ticket
     */
    public function actionGetVoucherTicket() {
        Yii::import('application.controllers.*');
        $commonController = new CommonController();
        $_couponModel = new CouponModel();
        $_ticketModel = new TicketModel();
        $_terminalsModel = new TerminalsModel();
        $_eGMRequestLogsModel = new EGMRequestLogsModel();
        $request = $this->_readJsonRequest();
        $amount = "";
        $voucherTicketBarcode = "";
        $dateTime = "";
        $expirationDate = "";
        $transMsg = "";
        $errCode = "";
        //If Input is valid then
        if (((isset($request['TrackingID']) && $request['TrackingID']) != '') && ((isset($request['TerminalName']) && $request['TerminalName']) != '') && ((isset($request['Amount']) && $request['Amount']) != '') && ((isset($request['Source']) && $request['Source']) != '')) {
            $trackingID = htmlentities($request['TrackingID']);
            $terminalName = htmlentities($request['TerminalName']);
            $amount = htmlentities($request['Amount']);
            $source = htmlentities($request['Source']);
            //If Input is valid then
            if (Utilities::validateInput($trackingID) && Utilities::validateInput($terminalName) && Utilities::validateInput($amount) && Utilities::validateInput($source)) {
                if (is_numeric($amount)) {
                    if (is_numeric($source)) {
                        $egmrequestlogsdata = $_eGMRequestLogsModel->getStatusByTrackingId($trackingID);
                        if (!empty($egmrequestlogsdata)) {
                            $transMsg = "Tracking ID is already existing.";
                            $errCode = 2;
                        } else {
                            $terminalCode = Yii::app()->params['sitePrefix'] . $terminalName;
                            $terminalID = $_terminalsModel->getTerminalIDfromterminals($terminalCode);
                            if (!empty($terminalID)) {
                                $couponStatus = CommonController::VOUCHER_STATUS_ACTIVE;
                                $couponID = $_couponModel->getCouponIDByCodeAndSource($terminalID, $amount, $couponStatus);
                                if ($couponID > 0) {
                                    $statusUsed = CommonController::VOUCHER_STATUS_USED;
                                    if (@$_couponModel->updateCouponStatus($couponID, $statusUsed)) {
                                        $transMsg = "Transaction successful.";
                                        $errCode = 2;
                                    } else {
                                        $transMsg = "Transaction failed.";
                                        $errCode = 2;
                                    }
                                } else {
                                    $ticketStatus = CommonController::VOUCHER_STATUS_ACTIVE;
                                    $ticketID = $_ticketModel->getTicketIDByCodeAndSource($terminalID, $amount, $ticketStatus, $aid);
                                    if ($couponID > 0) {
                                        if ((isset($request['AID']) && $request['AID']) != '') {
                                            $aid = htmlentities($request['AID']);
                                            if (is_numeric($aid)) {
                                                $statusUsed = CommonController::VOUCHER_STATUS_USED;
                                                if (@$_ticketModel->updateTicketStatus($ticketID, $statusUsed)) {
                                                    $transMsg = "Transaction successful.";
                                                    $errCode = 2;
                                                } else {
                                                    $transMsg = "Transaction failed.";
                                                    $errCode = 2;
                                                }
                                            }
                                        } else {
                                            $transMsg = "AID is required for EGM.";
                                            $errCode = 2;
                                        }
                                    } else {
                                        $transMsg = "Transaction failed.";
                                        $errCode = 2;
                                    }
                                }
                            } else {
                                $transMsg = "Terminal does not exists.";
                                $errCode = 2;
                            }
                        }
                    } else {
                        $transMsg = "Invalid Source.";
                        $errCode = 2;
                    }
                } else {
                    $transMsg = "Invalid Amount.";
                $errCode = 2;
                }
            }
        } else {
            $transMsg = "One or more field is not set or is blank.";
            $errCode = 2;
            Utilities::log($transMsg);
        }
        
        $this->_sendResponse(200, CommonController::getVoucherTicketResponse($amount, $voucherTicketBarcode, $dateTime, $expirationDate, $transMsg, $errCode));
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

}



?>
