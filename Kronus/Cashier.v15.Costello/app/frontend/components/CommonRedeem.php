<?php

/**
 * For Terminal Based Transaction 
 * Common redeem session for TerminalMonitoring. Stand-alone and Hotkey
 * Date Created 11 8, 11 9:17:55 AM <pre />
 * Date Modified May 6, 2013
 * @author Bryan Salazar
 * @author Edson Perez <elperez@philweb.com.ph>
 */
class CommonRedeem {

    /**
     * Redeem method for terminal based
     * @param str $login_pwd [Terminal Password (Hashed)]
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
     * @return array result
     */
    public function redeem($login_pwd, $terminal_id, $site_id, $bcf, $service_id, $amount, $paymentType, $acct_id, $loyalty_card, $mid = '', $userMode = '', $locatorname = '', $CPV = '') {

        Mirage::loadComponents(array('CasinoApi', 'PCWSAPI.class'));
        Mirage::loadModels(array('TerminalsModel', 'EgmSessionsModel', 'CommonTransactionsModel', 'StackerSummaryModel',
            'PendingTerminalTransactionCountModel', 'AutoEmailLogsModel', 'RefServicesModel', 'SiteAccountsModel', 'TerminalServicesModel', 'MemberCardsModel', 'HabaneroCompPointsLogModel'));

        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $autoemaillogs = new AutoEmailLogsModel();
        $refServicesModel = new RefServicesModel();
        $siteAccountsModel = new SiteAccountsModel();
        $pcwsAPI = new PCWSAPI();
        $terminalServicesModel = new TerminalServicesModel();

        $MemberCardsModel = new MemberCardsModel();
        $HabaneroCompPointsLogModel = new HabaneroCompPointsLogModel();

        $terminalname = $terminalsModel->getTerminalName($terminal_id);

        //check terminal type if Genesis = 1
        $terminalType = $terminalsModel->checkTerminalType($terminal_id);

        /*
         * Commented By JAV
         * Date 02-06-2018
         * 
          //call PT, freeze and force logout of session
          $casinoApi->_doCasinoRules($terminal_id, $service_id, $terminalname);
         * 
         */

        if ($userMode == 2) {
            $loyalty_card = $terminal_id;
            $mid = $terminal_id;
        }

        list($terminal_balance, $service_name, $terminalSessionsModel,
                $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount, $currentbet) = $casinoApi->getBalance(
                $terminal_id, $site_id, 'W', $service_id, $acct_id, $login_pwd, $userMode, $CPV);

        if ($terminalType == 1) {
            if ($redeemable_amount > 0) {
                $message = 'Redemptions are allowed only in Genesis Terminal.';
                logger($message);
                CasinoApi::throwError($message);
            }
        }
        //call SAPI, lock launchpad terminal
        if ($terminalType == 2) {
            $casinoApi->callSpyderAPI($commandId = 9, $terminal_id, $terminalname, $login_pwd, $service_id);
        } else {
            $casinoApi->callSpyderAPI($commandId = 1, $terminal_id, $terminalname, $login_pwd, $service_id);
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
            $terminal_name = $terminalname;
        }
        /*
         * Commented By JAV
         * Date 02-06-2018
         * 
          //revert player bet on hand regardless of the current bet, for PT only
          if (strpos($service_name, 'PT') !== false) {
          $result = $casinoApi->RevertBrokenGamesAPI($terminal_id, $service_id, $terminal_name);
          if ($result['RevertBrokenGamesReponse'][0] == false) {
          //unfreeze PT account
          $casinoApiHandler->ChangeAccountStatus($terminal_name, 0);
          //unlock launchpad gaming terminal
          $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id);
          CasinoApi::throwError("Unable to revert bet on hand.");
          }
          }
         * 
         */



        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];

        $pendingGames = '';
        //check if there was a pending game bet for RTG
        if (strpos($service_name, 'RTG') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($terminal_name);
            $pendingGames = $casinoApi->GetPendingGames($terminal_id, $service_id, $PID);
        }

        //check if there was a pending game bet for habanero
        if (strpos($service_name, 'HAB') !== false) {
            $pendingGames = $casinoApi->GetPendingGamesHabanero($terminal_id, $service_id, $terminal_name, $terminal_pwd);

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
            $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
            CasinoApi::throwError($message);
        }

        //logout player
        if (strpos($service_name, 'RTG') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($terminal_name);
            $casinoApi->LogoutPlayer($terminal_id, $service_id, $PID);
        }



        //logout player Habanero
        if (strpos($service_name, 'HAB') !== false) {
            $test = $casinoApi->LogoutPlayerHabanero($terminal_id, $service_id, $terminal_name, $terminal_pwd);
            logger($test . '  TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
        }


        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if (!$trans_summary_id) {
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            $message = 'Redeem Session Failed. Please check if the terminal
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

        $udate = CasinoApi::udate('YmdHisu');

	$transaction_id = null;
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'W', $paymentType, $terminal_id, $site_id, $service_id, $loyalty_card, $mid, $userMode, $transaction_id);


        if (!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }

        if (toMoney($amount) != toMoney(toInt($redeemable_amount))) {
            $transReqLogsModel->update($trans_req_log_last_id, false, 2, null, $terminal_id);
            $message = 'Error: Redeemable amount is not equal.';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
            CasinoApi::throwError($message);
        }
        //check if redeemable amount is greater than 0, else skip on calling Withdraw
        //API method
        if ($redeemable_amount > 0) {

            $tracking1 = $trans_req_log_last_id;
            $tracking2 = 'W';
            $tracking3 = $terminal_id;
//            $tracking4 = $site_id;
			$tracking4 = str_replace("ICSA-","",str_replace("VIP","",$terminal_name));
            $event_id = Mirage::app()->param['mgcapi_event_id'][2]; //Event ID for Withdraw
            // check if casino's reply is busy, added 05/17/12
            if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                //unlock launchpad gaming terminal
                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                CasinoApi::throwError($message);
            }


            /*             * ********************** WITHDRAW *********************************** */
            $resultwithdraw = $casinoApiHandler->Withdraw($terminal_name, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $terminal_pwd, $event_id, $transaction_id);

            //check if Withdraw API reply is null
            if (is_null($resultwithdraw)) {

                // check again if Casino Server is busy
                if (!(bool) $casinoApiHandler->IsAPIServerOK()) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                    $message = 'Can\'t connect to casino';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                    CasinoApi::throwError($message);
                }

                //execute TransactionSearchInfo API Method
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, $tracking1, $tracking2, $tracking3, $tracking4, $transaction_id);

                //check if TransactionSearchInfo API is not successful
                if (isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false || $transSearchInfo['TransactionInfo']['Success'] == false) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2, null, $terminal_id);
                    $message = 'Error: Request denied. Please try again.';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id .
                            ' ErrorMessage=' . $transSearchInfo['ErrorMessage']);
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                    CasinoApi::throwError($message);
                }

                //Check TransactionSearchInfo API
                if (isset($transSearchInfo['TransactionInfo'])) {
                    //RTG / Magic Macau
                    if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                        $amount = abs($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount']);
                        $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                        $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                    }
                    /*
                     * Commented By JAV
                     * Date 02-06-2018
                     * 
                      //MG / Vibrant Vegas
                      elseif (isset($transSearchInfo['TransactionInfo']['MG'])) {
                      //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                      $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                      $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                      }
                      //PT / PlayTech
                      else if (isset($transSearchInfo['TransactionInfo']['PT'])) {
                      $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                      $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                      }
                     * 
                     */
                    //Habanero
                    else if (isset($transSearchInfo['TransactionInfo']['querytransmethodResult']) && ($transrefid == null || empty($transrefid))) {
                        //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                        $transrefid = $transSearchInfo['TransactionInfo']['TransactionId'];
                        $apiresult = $transSearchInfo['TransactionInfo']['Success'];
                    }
                }
            } else {

                if ($service_id == 25) {
                    $resultwithdraw['IsSucceed'] = $resultwithdraw['TransactionInfo']['Success'];
                }

                //check if TransactionSearchInfo API is not successful
                if (isset($resultwithdraw['IsSucceed']) && $resultwithdraw['IsSucceed'] == false) {
                    $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
                    $message = 'Error: Request denied. Please try again.';
                    logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id .
                            ' ErrorMessage=' . $resultwithdraw['ErrorMessage']);
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                    CasinoApi::throwError($message);
                }

                //check Withdraw API Result
                if (isset($resultwithdraw['TransactionInfo'])) {
                    //RTG / Magic Macau
                    if (isset($resultwithdraw['TransactionInfo']['WithdrawGenericResult'])) {
                        $transrefid = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                        $apiresult = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                    }
                    /*
                     * Commented By JAV
                     * Date 02-06-2018
                     * 
                      //MG / Vibrant Vegas
                      if (isset($resultwithdraw['TransactionInfo']['MG'])) {
                      $transrefid = $resultwithdraw['TransactionInfo']['MG']['TransactionId'];
                      $apiresult = $resultwithdraw['TransactionInfo']['MG']['TransactionStatus'];
                      }
                      //PT / Rocking Reno
                      if (isset($resultwithdraw['TransactionInfo']['PT'])) {
                      $transrefid = $resultwithdraw['TransactionInfo']['PT']['TransactionId'];
                      $apiresult = $resultwithdraw['TransactionInfo']['PT']['TransactionStatus'];
                      }
                     * 
                     */
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
                    CasinoApi::throwError($message);
                }

                //AutoEmailLogs
                $service_name = $refServicesModel->getServiceNameById($service_id);
                $transactionDetails = $autoemaillogs->getTransactionDetails($trans_summary_id);

                if ($transactionDetails != 0) {
                    $accountName = $transactionDetails['Name'] . ' ' . $transactionDetails['SiteName'];

                    $totalLoads = $transactionDetails['Deposit'] + $transactionDetails['Reload'];
                    $netwins = $amount - $totalLoads;
                    $minNetwin = Mirage::app()->param['AutoEmailNetWin'];

                    if ($netwins >= $minNetwin) {
                        $autoemaillogs->insert($service_id, $service_name, $totalLoads, $amount, $netwins, $transactionDetails['TerminalCode'], $transactionDetails['SiteName'], $transactionDetails['POSAccountNo'], $transactionDetails['TerminalCode'], $accountName, $transactionDetails['DateStarted'], $transactionDetails['DateEnded'], null, $transactionDetails['TransactionsSummaryID']);
                    }
                }
                /*
                 * CHANGE PASSWORD
                 *  
                  $systemusername = Mirage::app()->param['pcwssysusername'];
                  $source = 2;
                  if (strstr($terminal_name, "ICSA-"))
                  {
                  $terminal_name = str_replace('ICSA-', '',$terminal_name );
                  }
                  $pcwsAPI->ChangePassword($systemusername, $terminal_name, $service_id, $userMode, $source);

                 * 
                 */


                /*                 * ************************** START COMPPOINTS REDEMPTION HABANERO ** [ 05 18 2018 @JAVIDA ] **************************** */
                $isHabaneroCompPointsON = 0;
                $isHabaneroCompPointsON = Mirage::app()->param['isHabaneroCompPointsON'];
                if ($isHabaneroCompPointsON == 1) {
                    if ($userMode != 2 && $service_id == 25) {

                        $HabaneroCompPointsLogID = $HabaneroCompPointsLogModel->insert($mid, $loyalty_card, $terminal_id, $site_id, 'W', $tracking1, 0);

                        if (!empty($HabaneroCompPointsLogID)) {
                            $WithdrawPlayerPointsHabanero = $casinoApiHandler->WithdrawPlayerPointsHabanero($tracking1, $terminal_name, $terminal_pwd);

                            $PointsWithdrawn = 0;
                            $updatePlayerHabaneroPoints = false;

                            if (!empty($WithdrawPlayerPointsHabanero['TransactionInfo']['Success']) && $WithdrawPlayerPointsHabanero['TransactionInfo']['Success'] != false && $WithdrawPlayerPointsHabanero['TransactionInfo']['Success'] != null) {
                                $PointsWithdrawn = abs($WithdrawPlayerPointsHabanero['TransactionInfo']['PointsWithdrawn']);

                                $updatePlayerHabaneroPoints = $MemberCardsModel->updatePlayerHabaneroPoints($PointsWithdrawn, $mid);

                                if ($updatePlayerHabaneroPoints) {
                                    $remarks = 'Success Points Redemption';
                                    $HabaneroCompPointsLogModel->updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, 1);
                                } else {
                                    $remarks = 'Failed to Update Points';
                                    $HabaneroCompPointsLogModel->updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, 2);
                                }



                            } else {

                                if (empty($WithdrawPlayerPointsHabanero['TransactionInfo']['Message'])) {
                                    $remarks = 'No API Response';
                                } else {
                                    $remarks = $WithdrawPlayerPointsHabanero['TransactionInfo']['Message'];
                                }

                                $HabaneroCompPointsLogModel->updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, 2);
                            }
                        }
                    }
                }
                /*                 * ************************** END COMPPOINTS REDEMPTION HABANERO **************************** */



                return array('message' => 'You have successfully redeemed the amount of PhP ' . toMoney($amount),
                    'trans_summary_id' => $trans_summary_id, 'udate' => $udate, 'amount' => $amount, 'terminal_login' => $terminal_name,
                    'trans_ref_id' => $transrefid, 'terminal_name' => $terminal_name, 'trans_details_id' => $isredeemed);
            } else {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2, null, $terminal_id);
                $message = 'Error: Request denied. Please try again.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                //unlock launchpad gaming terminal
                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                CasinoApi::throwError($message);
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
                CasinoApi::throwError($message);
            }

            /*
             * CHANGE PASSWORD
             *           
              $systemusername = Mirage::app()->param['pcwssysusername'];
              $source = 2;
              if (strstr($terminal_name, "ICSA-"))
              {
              $terminal_name = str_replace('ICSA-', '',$terminal_name );
              }
              $pcwsAPI->ChangePassword($systemusername, $terminal_name, $service_id, $userMode, $source);
             * 
             */


 /*             * ************************** START COMPPOINTS REDEMPTION HABANERO ** [ 05 18 2018 @JAVIDA ] **************************** */
            $isHabaneroCompPointsON = 0;
            $isHabaneroCompPointsON = Mirage::app()->param['isHabaneroCompPointsON'];
            if ($isHabaneroCompPointsON == 1) {
                if ($userMode != 2 && $service_id == 25) {

                    $HabaneroCompPointsLogID = $HabaneroCompPointsLogModel->insert($mid, $loyalty_card, $terminal_id, $site_id, 'W', $tracking1, 0);

                    if (!empty($HabaneroCompPointsLogID)) {
                        $WithdrawPlayerPointsHabanero = $casinoApiHandler->WithdrawPlayerPointsHabanero($tracking1, $terminal_name, $terminal_pwd);

                        $PointsWithdrawn = 0;
                        $updatePlayerHabaneroPoints = false;

                        if (!empty($WithdrawPlayerPointsHabanero['TransactionInfo']['Success']) && $WithdrawPlayerPointsHabanero['TransactionInfo']['Success'] != false && $WithdrawPlayerPointsHabanero['TransactionInfo']['Success'] != null) {
                            $PointsWithdrawn = abs($WithdrawPlayerPointsHabanero['TransactionInfo']['PointsWithdrawn']);

                            $updatePlayerHabaneroPoints = $MemberCardsModel->updatePlayerHabaneroPoints($PointsWithdrawn, $mid);

                            if ($updatePlayerHabaneroPoints) {
                                $remarks = 'Success Points Redemption';
                                $HabaneroCompPointsLogModel->updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, 1);
                            } else {
                                $remarks = 'Failed to Update Points';
                                $HabaneroCompPointsLogModel->updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, 2);
                            }

                        } else {

                            if (empty($WithdrawPlayerPointsHabanero['TransactionInfo']['Message'])) {
                                $remarks = 'No API Response';
                            } else {
                                $remarks = $WithdrawPlayerPointsHabanero['TransactionInfo']['Message'];
                            }

                            $HabaneroCompPointsLogModel->updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, 2);
                        }
                    }
                }
            }
            /*             * ************************** END COMPPOINTS REDEMPTION HABANERO **************************** */



            return array('message' => 'Info: Session has been ended.',
                'trans_summary_id' => $trans_summary_id, 'udate' => $udate, 'amount' => $amount, 'terminal_login' => $terminal_name,
                'terminal_name' => $terminal_name, 'trans_details_id' => $isredeemed);
        }
    }

}
