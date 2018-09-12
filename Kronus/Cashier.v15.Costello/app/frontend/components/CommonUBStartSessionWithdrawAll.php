<?php

/**
 * For Userbased Withdraw All Transaction
 * Common start session for TerminalMonitoring. Stand-alone and Hotkey
 * Core transaction process for deposit
 * Date Created 06 19, 18 10:30:24 PM <pre />
 * @author javida
 */
class CommonUBStartSessionWithdrawAll {

    /**
     * @param int $terminal_id
     * @param int $site_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $bcf
     * @param int $initial_deposit
     * @param int $acctid
     * @return array 
     */
    public function start($terminal_id, $site_id, $trans_type, $paymentType, $service_id, $bcf, $initial_deposit, $acctid, $loyalty_card = '', $voucher_code = '', $trackingid = '', $casinoUsername = '', $casinoPassword = '', $casinoHashedPassword = '', $casinoServiceID = '', $mid = '', $userMode = '', $traceNumber = '', $referenceNumber = '', $locatorname = '', $CPV = '', $isVIP) {
        Mirage::loadComponents(array('CasinoApiUB', 'PCWSAPI.class'));
        Mirage::loadModels(array('TerminalsModel', 'EgmSessionsModel', 'SiteBalanceModel', 'CommonTransactionsModel',
            'PendingTerminalTransactionCountModel', 'BankTransactionLogsModel', 'RefServicesModel'));

        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $siteBalance = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $bankTransactionLogs = new BankTransactionLogsModel();
        $refServicesModel = new RefServicesModel();

        if ($terminalsModel->isPartnerAlreadyStarted($terminal_id)) {
            $message = 'Error: ' . $terminalsModel->terminal_code . ' terminal already started';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        list($terminal_balance, $service_name, $terminalSessionsModel, $transReqLogsModel, $redeemable_amount,
                $casinoApiHandler, $mgaccount) = $casinoApi->getBalanceUB($terminal_id, $site_id, 'D', $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);

        if (empty($casinoApiHandler)) {
            $message = 'Can\'t connect to casino';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);

        if ($is_terminal_active === false) {
            $message = 'Error: Can\'t get status.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($is_terminal_active != 0) {
            $message = 'Error: Terminal is already active.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($terminal_balance != 0) {
            $is_card_has_session = $terminalSessionsModel->checkSession($loyalty_card, $service_id);

            if ($is_card_has_session > 0) {
                $alias = $refServicesModel->getAliasById($service_id);
                $message = 'Error: Only one active session for ' . $alias . ' casino is allowed for this card.';
            } else {
                $message = 'Error: Please inform customer service for manual redemption.';
            }

            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if (($bcf - $initial_deposit) < 0) {
            $message = 'Error: BCF is not enough.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        }

        $udate = CasinoApi::udate('YmdHisu');

        //check terminal type if Genesis = 1
        $terminaltype = $terminalsModel->checkTerminalType($terminal_id);

        if ($terminaltype == 1) {
            //insert egm session
            $egmsessionsresult = $egmSessionsModel->insert($mid, $terminal_id, $service_id, $_SESSION['accID']);

            if (!$egmsessionsresult) {
                $message = 'Error: The terminal has an ongoing terminal deposit session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }
        }

        $checkegmsession = $egmSessionsModel->checkEgmSession($service_id, $mid);

        if (!empty($checkegmsession)) {
            $message = 'Error: User has an ongoing EGM deposit session.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        /*         * *********************** START CHANGE PLAYER CLASS *********************************** */
        if (strpos($service_name, 'RTG') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
            $changePlayerClass = $casinoApi->ChangePlayerClassification($terminal_id, $service_id, $PID, $isVIP);

            if ($changePlayerClass['IsSucceed'] === false) {
                $message = 'Change Player Classification Failed.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }
        }
        /*         * *********************** END CHANGE PLAYER CLASS *********************************** */




        //insert into terminalsessions, throw error if there is existing session 
        //this terminal / user
        $trans_summary_max_id = null;

        $is_terminal_exist = $terminalSessionsModel->insert($terminal_id, $service_id, $initial_deposit, $trans_summary_max_id, $loyalty_card, $mid, $userMode, $casinoUsername, $casinoPassword, $casinoHashedPassword);
        //$casinoHashedPassword, $viptype); // CCT added viptype VIP

        if (!$is_terminal_exist) {
            $message = 'Error: Terminal / User has an existing session.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        //insert into transaction request log
        $bankTransactionStatus = null;
        $transaction_id = null;
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $initial_deposit, 'D', $paymentType, $terminal_id, $site_id, $service_id, $loyalty_card, $mid, $userMode, $trackingid, $voucher_code, $transaction_id, $casinoUsername);

        if (!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($traceNumber != '' && $referenceNumber != '') {
            if ($trans_req_log_last_id) {
                $bankTransactionStatus = $bankTransactionLogs->insertBankTransaction($trans_req_log_last_id, $traceNumber, $referenceNumber, $paymentType);
            }
        }

        if ($bankTransactionStatus === false) {
            $message = 'Bank Transaction Failed.';
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'D';
        $tracking3 = $terminal_id;
        $tracking4 = str_replace("ICSA-", "", str_replace("VIP", "", $terminal_name));
        $event_id = Mirage::app()->param['mgcapi_event_id'][0]; //Event ID for Deposit
        // check if casino's reply is busy, added 05/17/12
        if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            $message = 'Can\'t connect to casino';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        /*         * *********************** DEPOSIT *********************************** */
        $resultdeposit = $casinoApiHandler->Deposit($casinoUsername, $initial_deposit, $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, $event_id, $transaction_id, $locatorname);

        //check if Deposit API reply is null
        if (is_null($resultdeposit)) {
            // check again if Casino Server is busy
            if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }

            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($casinoUsername, $tracking1, $tracking2, $tracking3, $tracking4, $transaction_id);

            //check if TransactionSearchInfo API is not successful
            if (isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false || $transSearchInfo['TransactionInfo']['Success'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Error: Failed to start session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $transSearchInfo['ErrorMessage']);
                CasinoApi::throwError($message);
            }

            //Check TransactionSearchInfo API
            if (isset($transSearchInfo['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                    $initial_deposit = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }

                //Habanero
                elseif (isset($transSearchInfo['TransactionInfo']['querytransmethodResult'])) {
                    //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                    $transrefid = $resultdeposit['TransactionInfo']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['Success'];
                }
            }
        } else {
            if ($service_id == 25) {
                $resultdeposit['IsSucceed'] = $resultdeposit['TransactionInfo']['Success'];
            }

            //check if TransactionSearchInfo API is not successful
            if (isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Error: Failed to start session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . 'ErrorMessage = ' . $resultdeposit['ErrorMessage']);
                CasinoApi::throwError($message);
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
                else if (isset($resultdeposit['TransactionInfo'])) {
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
        if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved' || $apiresult == "Deposit Success") {
            //this will return the transaction summary ID
            $trans_summary_id = $commonTransactionsModel->startTransaction($site_id, $terminal_id, $initial_deposit, $acctid, $udate, 'D', $paymentType, $service_id, $transstatus, $loyalty_card, $mid);
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus, $transrefid, $terminal_id);

            $newbal = $bcf - $initial_deposit;
            $siteBalance->updateBcf($newbal, $site_id, 'Start session'); //update bcf

            if (!$trans_summary_id) {
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Error: Failed to insert records in transaction tables.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }

            $message = 'New player session started.The player initial playing balance is PhP ' . toMoney($initial_deposit);

            return array('message' => $message, 'newbcf' => toMoney($newbal), 'initial_deposit' => toMoney($initial_deposit),
                'udate' => $udate, 'terminal_name' => $terminal_name, 'trans_ref_id' => $transrefid, 'trans_summary_id' => $trans_summary_id["trans_summary_max_id"],
                'trans_details_id' => $trans_summary_id["transdetails_max_id"]);
        } else {

            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            $message = 'Error: Request denied. Please try again.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }
    }

}
