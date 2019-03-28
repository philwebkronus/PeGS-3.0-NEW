<?php

/**
 * For Userbased Withdraw All Transaction
 * Common reload session for TerminalMonitoring. Stand-alone and Hotkey
 * Core transaction process for reload
 * Date Created 06 19, 18 10:30:24 PM <pre />
 * @author javida
 */
class CommonUBReloadWithdrawAll {

    /**
     * @param int $bcf
     * @param int $amount
     * @param int $terminal_id
     * @param int $site_id
     * @param int $service_id
     * @param int $acctid
     * @return array 
     */
    public function reload($bcf, $amount, $paymentType, $terminal_id, $site_id, $service_id, $acctid, $loyalty_card, $voucher_code = '', $trackingid = '', $mid = '', $userMode = '', $casinoUsername = '', $casinoPassword = '', $casinoServiceID = '', $traceNumber = '', $referenceNumber = '', $locatorname = '') {

        Mirage::loadComponents('CasinoApiUB');
        Mirage::loadModels(array('TransactionSummaryModel', 'TerminalsModel', 'EgmSessionsModel', 'StackerSummaryModel',
            'SiteBalanceModel', 'CommonTransactionsModel',
            'PendingTerminalTransactionCountModel', 'BankTransactionLogsModel'));

        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $transSummaryModel = new TransactionSummaryModel();
        $siteBalance = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $bankTransactionLogs = new BankTransactionLogsModel();

        list($terminal_balance, $service_name, $terminalSessionsModel,
                $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount) = $casinoApi->getBalanceUB($terminal_id, $site_id, 'R', $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);

        $total_terminal_balance = $terminal_balance + $amount;

        if (($bcf - $amount) < 0) {
            $message = 'Error: BCF is not enough.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }

        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);

        if ($is_terminal_active === false) {
            $message = 'Error: Can\'t get status.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }

        if ($is_terminal_active < 1) {
            $message = 'Error: Terminal has no active session.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
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
            CasinoApiUB::throwError($message);
        }

        $udate = CasinoApiUB::udate('YmdHisu');

        $transaction_id = null;
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'R', $paymentType, $terminal_id, $site_id, $service_id, $loyalty_card, $mid, $userMode, $trackingid, $voucher_code, $transaction_id, $casinoUsername);

        $bankTransactionStatus = null;


        if (!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }

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
 //       $tracking4 = $site_id;
		$tracking4 = str_replace("ICSA-","",str_replace("VIP","",$terminal_name));
        $event_id = Mirage::app()->param['mgcapi_event_id'][1]; //Event ID for Reload
        // check if casino's reply is busy, added 05/17/12
        if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
            $message = 'Can\'t connect to casino';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }

        /*         * *********************** RELOAD ************************************ */
        $resultdeposit = $casinoApiHandler->Deposit($casinoUsername, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, $event_id, $transaction_id, $locatorname);

        //check if Deposit API reply is null
        if (is_null($resultdeposit)) {

            // check again if Casino Server is busy
            if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApiUB::throwError($message);
            }

            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($casinoUsername, $tracking1, $tracking2, $tracking3, $tracking4, $transaction_id);

            //check if TransactionSearchInfo API is not successful
            if (isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Error: Failed to reload session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $transSearchInfo['ErrorMessage']);
                CasinoApiUB::throwError($message);
            }

            //Check TransactionSearchInfo API
            if (isset($transSearchInfo['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                    $amount = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }

                //Habanero
                if (isset($transSearchInfo['TransactionInfo']) && ($transrefid == null || empty($transrefid))) {
                    $transrefid = $resultwithdraw['TransactionInfo']['TransactionId'];
                    $apiresult = $resultwithdraw['TransactionInfo']['Success'];
                }
            }
        } else {

            //check if TransactionSearchInfo API is not successful
            if (isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Error: Failed to reload session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $resultdeposit['ErrorMessage']);
                CasinoApiUB::throwError($message);
            }

            //check Deposit API Result
            if (isset($resultdeposit['TransactionInfo'])) {
				$transrefid = '';
                //RTG / Magic Macau
                if (isset($resultdeposit['TransactionInfo']['DepositGenericResult'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionID'];
                    $apiresult = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                    $apierrmsg = $resultdeposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
                }

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
                CasinoApiUB::throwError($message);
            }

            $terminalType = $terminalsModel->checkTerminalType($terminal_id);

            if ($terminalType == 1) {
                $stackerbatchid = $egmSessionsModel->getStackerBtachID($terminal_id, $mid);

                if (isset($stackerbatchid) && !empty($stackerbatchid)) {
                    $stackerSummaryid = $stackerbatchid['StackerBatchID'];
                    $stackerSummaryModel->updateStackerSummaryStatus(4, $stackerSummaryid, $acctid);
                }
            }

            $message = 'The amount of PhP ' . toMoney($amount) . ' is successfully loaded.';

            $new_terminal_balance = toInt($terminal_balance) + toInt($amount);

            return array('message' => $message, 'newbcf' => toMoney($newbal), 'reload_amount' => toMoney($amount),
                'terminal_balance' => toMoney($new_terminal_balance), 'trans_summary_id' => $trans_summary_id, 'udate' => $udate,
                'trans_ref_id' => $transrefid, 'terminal_name' => $terminal_name, 'trans_details_id' => $isupdated);
        } else {
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
            $message = 'Error: Request denied. Please try again.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }
    }

}
