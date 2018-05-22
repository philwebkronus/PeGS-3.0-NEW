<?php

/**
 * For Terminal Based Transaction
 * Common reload session for TerminalMonitoring. Stand-alone and Hotkey
 * Core transaction process for reload
 * Date Created 11 9, 11 2:41:32 PM <pre />
 * Date Modified May 6, 2013
 * @author Bryan Salazar
 * @author Edson Perez <elperez@philweb.com.ph>
 */
class CommonReload {

    /**
     * @param int $bcf
     * @param int $amount
     * @param int $terminal_id
     * @param int $site_id
     * @param int $service_id
     * @param int $acctid
     * @return array 
     */
    public function reload($bcf, $amount, $paymentType, $terminal_id, $site_id, $service_id, $acctid, $loyalty_card, $voucher_code = '', $trackingid = '', $mid = '', $userMode = '', $traceNumber = '', $referenceNumber = '', $locatorname = '', $CPV = '') {
        Mirage::loadComponents(array('CasinoApi', 'PCWSAPI.class'));
        Mirage::loadModels(array('TransactionSummaryModel', 'TerminalsModel', 'EgmSessionsModel', 'StackerSummaryModel',
            'SiteBalanceModel', 'CommonTransactionsModel',
            'PendingTerminalTransactionCountModel', 'BankTransactionLogsModel'));

        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $transSummaryModel = new TransactionSummaryModel();
        $siteBalance = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $pcwsapi = new PCWSAPI();
        $bankTransactionLogs = new BankTransactionLogsModel();

        if ($userMode == 2) {
            $loyalty_card = $terminal_id;
            $mid = $terminal_id;
        }

        list($terminal_balance, $service_name, $terminalSessionsModel,
                $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount) = $casinoApi->getBalance($terminal_id, $site_id, 'R', $service_id, $userMode, $CPV);

        $total_terminal_balance = $terminal_balance + $amount;

        if (($bcf - $amount) < 0) {
            $message = 'Error: BCF is not enough.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);

        if ($is_terminal_active === false) {
            $message = 'Error: Can\'t get status.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($is_terminal_active < 1) {
            $message = 'Error: Terminal has no active session.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        }

        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if (!$trans_summary_id) {
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            $message = 'Reload Session Failed. Please check if the terminal
                            has a valid start session.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        /*
         * Commented By JAV
         * Date 02-06-2018
         * 
          //get last transaction ID if service is MG
          if (strpos($service_name, 'MG') !== false) {
          $trans_origin_id = 0; //cashier origin Id
          $transaction_id = $terminalsModel->insertserviceTransRef($service_id, $trans_origin_id);
          if (!$transaction_id) {
          $message = "Error: Failed to insert record in transaction table [0001].";
          logger($message);
          CasinoApi::throwError($message);
          }
          } else {
          $transaction_id = '';
          }
         * 
         */

        //get terminal password 
        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];


        $udate = CasinoApi::udate('YmdHisu');

        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'R', $paymentType, $terminal_id, $site_id, $service_id, $loyalty_card, $mid, $userMode, $trackingid, $voucher_code, $transaction_id);

        $bankTransactionStatus = null;


        if (!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        //CasinoApi::throwError($trans_req_log_last_id.'-'.$traceNumber.'-'.$referenceNumber.'-'.$paymentType);
        if ($traceNumber != '' && $referenceNumber != '') {
            if ($trans_req_log_last_id) {
                $bankTransactionStatus = $bankTransactionLogs->insertBankTransaction($trans_req_log_last_id, $traceNumber, $referenceNumber, $paymentType);
            }
        }

        if ($bankTransactionStatus === false) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
            $message = 'Bank Transaction Failed.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'R';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Mirage::app()->param['mgcapi_event_id'][1]; //Event ID for Reload
        // check if casino's reply is busy, added 05/17/12
        if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
            $message = 'Can\'t connect to casino';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        /*         * *********************** RELOAD ************************************ */

        $resultdeposit = $casinoApiHandler->Deposit($terminal_name, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminal_pwd, $event_id, $transaction_id, $locatorname);

        //check if Deposit API reply is null
        if (is_null($resultdeposit)) {

            // check again if Casino Server is busy
            if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }

            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, $tracking1, $tracking2, $tracking3, $tracking4, $transaction_id);

            //check if TransactionSearchInfo API is not successful
            if (isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Error: Failed to reload session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $transSearchInfo['ErrorMessage']);
                CasinoApi::throwError($message);
            }

            //Check TransactionSearchInfo API
            if (isset($transSearchInfo['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                    $amount = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }
                /*
                 * Commented By JAV
                 * Date 02-06-2018
                 * 
                  //MG / Vibrant Vegas
                  if (isset($transSearchInfo['TransactionInfo']['MG'])) {
                  //$amount = abs($transSearchInfo['TransactionInfo']['Balance']);
                  $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                  $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                  }
                  //PT / PlayTech
                  if (isset($transSearchInfo['TransactionInfo']['PT'])) {
                  //$initial_deposit = $transSearchInfo['TransactionInfo']['PT']['']; //need to ask if reported amount will be passed from PT
                  $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                  $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                  }
                 * 
                 */
                //Habanero
                if (isset($transSearchInfo['TransactionInfo']) && ($transrefid == null || empty($transrefid))) {
                    //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                    $transrefid = $resultwithdraw['TransactionInfo']['TransactionId'];
                    $apiresult = $resultwithdraw['TransactionInfo']['Success'];
                }
            }
        } else {

            if ($service_id == 25) {
                $resultdeposit['IsSucceed'] = $resultdeposit['TransactionInfo']['Success'];
            }

            //check if TransactionSearchInfo API is not successful
            if (isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Error: Failed to reload session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $resultdeposit['ErrorMessage']);
                CasinoApi::throwError($message);
            }

            //check Deposit API Result
            if (isset($resultdeposit['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($resultdeposit['TransactionInfo']['DepositGenericResult'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionID'];
                    $apiresult = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                    $apierrmsg = $resultdeposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
                }
                /*
                 * Commented By JAV
                 * Date 02-06-2018
                 * 
                  //MG / Vibrant Vegas
                  if (isset($resultdeposit['TransactionInfo']['MG'])) {
                  $transrefid = $resultdeposit['TransactionInfo']['MG']['TransactionId'];
                  $apiresult = $resultdeposit['TransactionInfo']['MG']['TransactionStatus'];
                  $apierrmsg = $resultdeposit['ErrorCode'];
                  }
                  //PT / PlayTech
                  if (isset($resultdeposit['TransactionInfo']['PT'])) {
                  $transrefid = $resultdeposit['TransactionInfo']['PT']['TransactionId'];
                  $apiresult = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                  $apierrmsg = $resultdeposit['ErrorMessage'];
                  }
                 * 
                 */
                //Habanero
                if (isset($resultdeposit['TransactionInfo']) && ($transrefid == null || empty($transrefid))) {
                    $transrefid = $resultdeposit['TransactionInfo']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['Message'];
                }
            }
        }

        if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved' || $apiresult == "Deposit Success") {
            $transstatus = '1';
        } else {
            $transstatus = '2';
        }

        //if Deposit / TransactionSearchInfo API status is approved
        if ($apiresult == 'true' || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved' || $apiresult == "Deposit Success") {

            $trans_summary = $transSummaryModel->getTransactionSummaryDetail($site_id, $terminal_id);

            $total_reload_balance = $trans_summary['Reload'] + $amount;

            $isupdated = $commonTransactionsModel->reloadTransaction($amount, $trans_summary_id, $udate, $site_id, $terminal_id, 'R', $paymentType, $service_id, $acctid, $transstatus, $total_reload_balance, $total_terminal_balance, $loyalty_card, $mid);

            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus, $transrefid, $terminal_id);

            $newbal = $bcf - $amount;
            $siteBalance->updateBcf($newbal, $site_id, 'Reload session');

            if (!$isupdated) {
                $message = 'Error: Failed insert records in transaction tables';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }

            $terminalType = $terminalsModel->checkTerminalType($terminal_id);

            if ($terminalType == 1) {
                $stackerbatchid = $egmSessionsModel->getStackerBtachID($terminal_id, $mid);

                if (isset($stackerbatchid) && !empty($stackerbatchid)) {
                    $stackerSummaryid = $stackerbatchid['StackerBatchID'];
                    $stackerSummaryModel->updateStackerSummaryStatus(4, $stackerSummaryid, $acctid);
                }
            }

            //Call AddCompPoints API for terminal based casinos.
//            $systemusername = $systemusername = Mirage::app()->param['pcwssysusername'];
//            $pcwsapi->AddCompPoints($systemusername, $loyalty_card, $site_id, $service_id, $amount);



            $message = 'The amount of PhP ' . toMoney($amount) . ' is successfully loaded.';

            $new_terminal_balance = toInt($terminal_balance) + toInt($amount);

            return array('message' => $message, 'newbcf' => toMoney($newbal), 'reload_amount' => toMoney($amount),
                'terminal_balance' => toMoney($new_terminal_balance), 'trans_summary_id' => $trans_summary_id, 'udate' => $udate,
                'trans_ref_id' => $transrefid, 'terminal_name' => $terminal_name, 'trans_details_id' => $isupdated);
        } else {
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
            $message = 'Error: Request denied. Please try again.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }
    }

}
