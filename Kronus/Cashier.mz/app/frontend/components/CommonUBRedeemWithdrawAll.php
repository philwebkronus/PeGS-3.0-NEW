<?php

/**
 * For Userbased Withdraw All Transaction
 * Common redeem session for TerminalMonitoring. Stand-alone and Hotkey
 * Core transaction process for redeem
 * Date Created 06 19, 18 10:30:24 PM <pre />
 * @author javida
 */
class CommonUBRedeemWithdrawAll {

    /**
     * Redeem method for user based
     * @param str $login_pwd [Membership Account Password (Hashed)]
     * @param int $terminal_id 
     * @param int $site_id
     * @param str $bcf
     * @param int $service_id
     * @param str $amount
     * @param int $paymentType [((1)Cash, (2)Voucher)]
     * @param int $acct_id [cashier id]
     * @param str $loyalty_card [membership card]
     * @param int $mid [membership id]
     * @param int $userMode [(0)Terminal, (1)User Based]
     * @param str $casinoUsername [Membership Account Username]
     * @param str $casinoPassword [Membership Account Password (Plain)]
     * @param int $casinoServiceID [Membership Casino Assigned]
     * @return array result
     */
    public function redeem($login_pwd, $terminal_id, $site_id, $bcf, $service_id, $amount, $paymentType, $acct_id, $loyalty_card, $mid = '', $userMode = '', $casinoUsername = '', $casinoPassword = '', $casinoServiceID = '', $isewallet = 0, $locatorname = '') {
        Mirage::loadComponents(array('CasinoApiUB', 'PCWSAPI.class'));
        Mirage::loadModels(array('TerminalsModel', 'EgmSessionsModel', 'CommonTransactionsModel', 'StackerSummaryModel',
            'PendingTerminalTransactionCountModel', 'PendingMzTransactionsModel'));

        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $pcwsAPI = new PCWSAPI();
        $pendingMzTransactionsModel = new PendingMzTransactionsModel();

        //check terminal type if Genesis = 1
        $terminalType = $terminalsModel->checkTerminalType($terminal_id);

        //call PT, freeze and force logout of session
        $casinoApi->_doCasinoRules($terminal_id, $service_id, $casinoUsername);

        list($terminal_balance, $service_name, $terminalSessionsModel,
                $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount, $currentbet) = $casinoApi->getBalanceUB($terminal_id, $site_id, 'W', $casinoServiceID, $acct_id, $casinoUsername, $casinoPassword, $login_pwd);

        if ($terminalType == 1) {
            if ($redeemable_amount > 0) {
                $message = 'Redemptions are allowed only in Genesis Terminal.';
                logger($message);
                CasinoApi::throwError($message);
            }
        }

        //Get Last Transaction Summary ID from terminalsessions
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if ($terminalType == 2 && $isewallet == 1 && ($service_id == 19 || $service_id == 20) && $trans_summary_id) {
            if ($redeemable_amount > 0) {
                $message = 'Error: You are not allowed to end a session for an e-SAFE account.';
                logger($message);
                CasinoApi::throwError($message);
            }
        }

	// MZ Cehcking 
	 $ActiveServiceStatus = (int) $terminalSessionsModel->getActiveServiceStatusByTerminalID($terminal_id);

        if ($ActiveServiceStatus > 1) {
           $message = 'End Session is currently not allowed at the moment. Please try again later.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if ($ActiveServiceStatus == 1) {
            $updateActiveServiceID = $terminalSessionsModel->updateActiveServiceIDByTerminalID($terminal_id, $service_id, 0);

            if (!$updateActiveServiceID) {
                $message = 'Error: Failed to update records in terminal sessions tables.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }
        }




        //call SAPI, lock launchpad terminal
        if ($terminalType == 2) {
            $casinoApi->callSpyderAPI($commandId = 9, $terminal_id, $casinoUsername, $login_pwd, $service_id);
        } else {
            $casinoApi->callSpyderAPI($commandId = 1, $terminal_id, $casinoUsername, $login_pwd, $service_id);
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
        
        $pendingGames = '';
        //check if there was a pending game bet for RTG
        if (strpos($service_name, 'RTG') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
            $pendingGames = $casinoApi->GetPendingGames($terminal_id, $service_id, $PID);
        }

        //check if there was a pending game bet for habanero
        if (strpos($service_name, 'HAB') !== false) {
            $pendingGames = $casinoApi->GetPendingGamesHabanero($terminal_id, $service_id, $casinoUsername, $casinoPassword);

            if ($pendingGames['TransactionInfo'][0]['GameName'] != null || $pendingGames['TransactionInfo'][0]['GameName'] != '') {
                $pendingGames['IsSucceed'] = true;
                $pendingGames['PendingGames']['GetPendingGamesByPIDResult']['Gamename'] = $pendingGames['TransactionInfo'][0]['GameName'];
            }
        } 

        //Display message
        if (!empty($pendingGames) && is_array($pendingGames) && $pendingGames['IsSucceed'] == true) {
            $message = "Redemption canceled-Pending bet encountered. Please Ask the player to complete the game.";
            logger($message . $pendingGames['PendingGames']['GetPendingGamesByPIDResult']['Gamename'] . '.' . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            $message = "Info: There was a pending game bet. ";
            //unlock launchpad gaming terminal
            $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id);
            CasinoApiUB::throwError($message);
        }


        //logout player
//        if (strpos($service_name, 'RTG') !== false) {
//            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
//            //$casinoApi->LogoutPlayer($terminal_id, $service_id,$PID); 
//            $systemusername = Mirage::app()->param['pcwssysusername'];
//            $pcwsAPI->Lock($systemusername, $casinoUsername, $service_id);
//        }
        //logout player
        if (strpos($service_name, 'RTG') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
            $casinoApi->LogoutPlayer($terminal_id, $service_id, $PID);
        }


        //logout player Habanero
        if (strpos($service_name, 'HAB') !== false) {
            $test = $casinoApi->LogoutPlayerHabanero($terminal_id, $service_id, $casinoUsername, $casinoPassword);
            logger($test . '  TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
        }


        //Check if terminal has an existing valid session
        if (!$trans_summary_id) {
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            if ($service_id != 19 && $service_id != 20) {
                $message = 'Redeem Session Failed. Please check if the terminal
                        has a valid start session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApiUB::throwError($message);
            } else {
                $udate = CasinoApiUB::udate('YmdHisu');
                $amount = 0;
                $isredeemed = 1;
                /*
                 * CHANGE PASSWORD
                 *                 
                  $systemusername = Mirage::app()->param['pcwssysusername'];
                  $source = "2";
                  if (strstr($casinoUsername, "ICSA-"))
                  {
                  $casinoUsername = str_replace('ICSA-', '',$casinoUsername );
                  }
                  $pcwsAPI->ChangePassword($systemusername, $casinoUsername, $service_id, $userMode, $source);
                 * 
                 */

                return array('message' => 'Info: Session has been ended.',
                    'trans_summary_id' => 0, 'udate' => $udate, 'amount' => $amount, 'terminal_login' => $terminal_name,
                    'terminal_name' => $terminal_name, 'trans_details_id' => $isredeemed);
            }
        }

        /* ADDED JAV 03202019 */
        $checkPendingMzTransactions = $pendingMzTransactionsModel->checkPendingMzTransactions($mid, $service_id);

        if ($checkPendingMzTransactions > 0) {
            $message = 'There was a pending transfer transaction for this user / terminal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }


        $udate = CasinoApiUB::udate('YmdHisu');

        $transaction_id = null;
        $trackingid = null;
        $voucher_code = null;

        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'W', $paymentType, $terminal_id, $site_id, $service_id, $loyalty_card, $mid, $userMode, $trackingid, $voucher_code, $transaction_id, $casinoUsername);

        if (!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }

        if (toMoney($amount) != toMoney(toInt($redeemable_amount))) {
            $transReqLogsModel->update($trans_req_log_last_id, false, 2, null, $terminal_id);
            $message = 'Error: Redeemable amount is not equal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApiUB::throwError($message);
        }

        //check if redeemable amount is greater than 0, else skip on calling Withdraw
        //API method
        if ($redeemable_amount > 0) {

            $tracking1 = $trans_req_log_last_id;
            $tracking2 = 'W';
            $tracking3 = $terminal_id;
            $tracking4 = str_replace("ICSA-","",str_replace("VIP","",$terminal_name));
            $event_id = Mirage::app()->param['mgcapi_event_id'][2]; //Event ID for Withdraw
            // check if casino's reply is busy, added 05/17/12
            if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                //unlock launchpad gaming terminal
                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id);
                CasinoApiUB::throwError($message);
            }


            /*             * ********************** WITHDRAW *********************************** */
            $resultwithdraw = $casinoApiHandler->Withdraw($casinoUsername, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, $event_id, $transaction_id, $locatorname);

            //check if Withdraw API reply is null
            if (is_null($resultwithdraw)) {

                // check again if Casino Server is busy
                if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                    $message = 'Can\'t connect to casino';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id);
                    CasinoApiUB::throwError($message);
                }

                //execute TransactionSearchInfo API Method
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($casinoUsername, $tracking1, $tracking2, $tracking3, $tracking4, $transaction_id);

                //check if TransactionSearchInfo API is not successful
                if (isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                    $message = 'Error: Request denied. Please try again.';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $transSearchInfo['ErrorMessage']);
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id);
                    CasinoApiUB::throwError($message);
                }

                //Check TransactionSearchInfo API
                if (isset($transSearchInfo['TransactionInfo'])) {
                    //RTG / Magic Macau
                    if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                        $amount = abs($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount']);
                        $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                        $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                    }
                    //Habanero
                    if (isset($transSearchInfo['TransactionInfo']['querytransmethodResult']) && ($transrefid == null || empty($transrefid))) {
                        //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                        $transrefid = $transSearchInfo['TransactionInfo']['TransactionId'];
                        $apiresult = $transSearchInfo['TransactionInfo']['Success'];
                    }
                }
            } else {
                //check if TransactionSearchInfo API is not successful
                if (isset($resultwithdraw['IsSucceed']) && $resultwithdraw['IsSucceed'] == false) {
                    $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
                    $message = 'Error: Request denied. Please try again.';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id . ' ErrorMessage=' . $resultwithdraw['ErrorMessage']);
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id);
                    CasinoApiUB::throwError($message);
                }

                //check Withdraw API Result
                if (isset($resultwithdraw['TransactionInfo'])) {
                    //RTG / Magic Macau
                    if (isset($resultwithdraw['TransactionInfo']['WithdrawGenericResult'])) {
                        $transrefid = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                        $apiresult = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                    }

                    //Habanero
                    if (isset($resultwithdraw['TransactionInfo']) && ($transrefid == null || empty($transrefid))) {
                        $transrefid = $resultwithdraw['TransactionInfo']['TransactionId'];
                        $apiresult = $resultwithdraw['TransactionInfo']['Message'];
                    }
                }
            }

            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved' || $apiresult == "Withdrawal Success") {
                $transstatus = '1';
            } else {
                $transstatus = '2';
            }

            //if Withdraw / TransactionSearchInfo API status is approved
            if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved' || $apiresult == "Withdrawal Success") {

                $isredeemed = $commonTransactionsModel->redeemTransaction($amount, $trans_summary_id, $udate, $site_id, $terminal_id, 'W', $paymentType, $service_id, $acct_id, $transstatus, $loyalty_card, $mid);

                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus, $transrefid, $terminal_id);

                //check terminal type if Genesis = 1
                $terminalType = $terminalsModel->checkTerminalType($terminal_id);

                if ($terminalType == 1) {
                    $stackerbatchid = $egmSessionsModel->getStackerBtachID($terminal_id, $mid);

                    if (isset($stackerbatchid) && !empty($stackerbatchid)) {
                        $stackersummaryid = $stackerbatchid['StackerBatchID'];
                        $updstatusdelegm = $stackerSummaryModel->deleteEgmUpdateSSStatus(5, $stackersummaryid, $terminal_id);
                    }

                    if ($updstatusdelegm == false) {
                        $message = 'Error: Failed to delete EGM Session.';
                        logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                        CasinoApi::throwError($message);
                    }
                }

                if (!$isredeemed) {
                    $message = 'Error: Failed update records in transaction tables';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    CasinoApiUB::throwError($message);
                }

                $updateActiveServiceID = $terminalSessionsModel->updateActiveServiceIDByTerminalID($terminal_id, $service_id, 1);

                return array('message' => 'You have successfully redeemed the amount of PhP ' . toMoney($amount),
                    'trans_summary_id' => $trans_summary_id, 'udate' => $udate, 'amount' => $amount, 'terminal_login' => $terminal_name,
                    'trans_ref_id' => $transrefid, 'terminal_name' => $terminal_name, 'trans_details_id' => $isredeemed);
            } else {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
                $message = 'Error: Request denied. Please try again.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                //unlock launchpad gaming terminal
                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id);
                CasinoApiUB::throwError($message);
            }
        } else {

            $isredeemed = $commonTransactionsModel->redeemTransaction($amount, $trans_summary_id, $udate, $site_id, $terminal_id, 'W', $paymentType, $service_id, $acct_id, 1, $loyalty_card, $mid);


            $transReqLogsModel->updateTransReqLogDueZeroBal($terminal_id, $site_id, 'W', $trans_req_log_last_id);

            //check terminal type if Genesis = 1
            $terminalType = $terminalsModel->checkTerminalType($terminal_id);

            if ($terminalType == 1) {
                $stackerbatchid = $egmSessionsModel->getStackerBtachID($terminal_id, $mid);

                if (isset($stackerbatchid) && !empty($stackerbatchid)) {
                    $stackersummaryid = $stackerbatchid['StackerBatchID'];

                    $stackerSummaryModel->updateStackerSummaryStatus(5, $stackersummaryid, $acct_id);

                    $egmSessionsModel->deleteEgmSessionById($terminal_id);
                }
            }

            if (!$isredeemed) {
                $message = 'Error: Failed update records in transaction tables';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApiUB::throwError($message);
            }
            /*
             * CHANGE PASSWORD
             *                 
              $systemusername = Mirage::app()->param['pcwssysusername'];
              $source = "2";
              if (strstr($casinoUsername, "ICSA-"))
              {
              $casinoUsername = str_replace('ICSA-', '',$casinoUsername );
              }
              $pcwsAPI->ChangePassword($systemusername, $casinoUsername, $service_id, $userMode, $source);
             * 
             */

            $updateActiveServiceID = $terminalSessionsModel->updateActiveServiceIDByTerminalID($terminal_id, $service_id, 1);

            return array('message' => 'Info: Session has been ended.',
                'trans_summary_id' => $trans_summary_id, 'udate' => $udate, 'amount' => $amount, 'terminal_login' => $terminal_name,
                'terminal_name' => $terminal_name, 'trans_details_id' => $isredeemed);
        }
    }
}

