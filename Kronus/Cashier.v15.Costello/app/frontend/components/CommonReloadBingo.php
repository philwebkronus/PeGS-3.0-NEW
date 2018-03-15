<?php

/**
 * For Terminal Based Transaction
 * Common start session for eBingo on TerminalMonitoring. Stand-alone and Hotkey
 * Core transaction process for deposit
 * Date Created 2 21, 18 2:00:24 PM <pre />
 * @author javida
 */
class CommonReloadBingo {

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
            'PendingTerminalTransactionCountModel', 'BankTransactionLogsModel', 'TransactionRequestLogsModel'));


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
        $terminalSessionsModel = new TerminalSessionsModel();
        $transReqLogsModel = new TransactionRequestLogsModel();

        $terminal_balance = $terminalSessionsModel->getLastBalanceBingo($terminal_id);
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


        $terminal_name = $terminalsModel->getTerminalName($terminal_id);


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

        $udate = CasinoApi::udate('YmdHisu');
        $transaction_id = '';

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

        $transstatus = '1';
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

        $message = 'The amount of PhP 0.00 is successfully loaded.';

        $new_terminal_balance = toInt($terminal_balance) + toInt($amount);

        return array('message' => $message, 'newbcf' => toMoney($newbal), 'reload_amount' => toMoney(0),
            'terminal_balance' => toMoney($new_terminal_balance), 'trans_summary_id' => $trans_summary_id, 'udate' => $udate,
            'trans_ref_id' => $transrefid, 'terminal_name' => $terminal_name, 'trans_details_id' => $isupdated);
    }

}

