<?php

/**
 * @description of WsCommonAPIController
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 11/04/13
 */

class CommonController{
    CONST SOURCE_KAPI = 1;
    CONST SOURCE_EGM = 2;
    CONST SOURCE_CASHIER = 3;
    CONST TRANS_TYPE_DEPOSIT = 1;
    CONST TRANS_TYPE_RELOAD = 2;
    CONST PAYMENT_TYPE_CASH = 0;
    CONST PAYMENT_TYPE_TICKET = 2;
    CONST STACKER_INFO_STATUS_ON_STOCK = 0;
    CONST STACKER_INFO_STATUS_ACTIVE = 1;
    CONST STACKER_INFO_STATUS_DEACTIVATED = 2;
    CONST ACOUNTTYPE_ID_VIRTUAL_CASHIER = 15;
    CONST PURPOSE_PAYOUT_TICKET = 1;
    CONST PURPOSE_VOID = 2;
    CONST PURPOSE_CANCELLED = 3;
    
    /**
     * Get the return output of log stacker session API
     * @param string $transMsg
     * @param int $errorCode
     * @param string $module
     * @return array
     */
    public static function StackerRetMsg($module, $transMsg ='', $errorCode ='', $stackerSessionID = '', $terminalName = '', $status = '', $isCancelled = '', $stackerBatchID = '', $amount = '', $voucherTicketBarcode = '', $dateTime = '', $expirationDate = '', $sequenceNo = '', $serialNumber = ''){
        if($module == 'LogStackerSession') {
                  return array('LogStackerSession'=>array("TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'GetStackerId') {
                  return array('GetStackerId'=>array("StackerBatchID"=>$stackerSessionID,
                      "TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'LogStackerTransaction') {
                  return array('LogStackerTransaction'=>array("StackerBatchID"=>$stackerBatchID,
                      "TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'VerifyLogStackerTransaction') {
                  return array('VerifyLogStackerTransaction'=>array("TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'AddStackerInfo') {
                  return array('AddStackerInfo'=>array("TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'UpdateStackerInfo') {
                  return array('UpdateStackerInfo'=>array("TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'GetStackerInfo') {
                  return array('GetStackerInfo'=>array("TerminalName"=>$terminalName,
                               "Status"=>$status,
                               "SerialNumber"=>$serialNumber,
                               "TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'CancelDeposit') {
                  return array('CancelDeposit'=>array("IsCancelled"=>$isCancelled,
                               "Amount"=>$amount,
                               "VoucherTicketBarcode"=>$voucherTicketBarcode,
                               "DateTime"=>$dateTime,
                               "ExpirationDate"=>$expirationDate,
                               "SequenceNo"=>$sequenceNo,
                               "TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        } else if($module == 'UpdateStackerSummaryStatus') {
                return array('UpdateStackerSummaryStatus'=>array("TransactionMessage"=>$transMsg,
                               "ErrorCode"=>$errorCode));
        }
    }
}

?>