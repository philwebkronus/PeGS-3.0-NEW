<?php

/**
 * Controller for ForceT Authentication webservice
 * @author gvjagolino,aqdepliyan,jefloresca,jdlachica,fdlsison
 */
class PcwsController extends Controller {

    public $_un; //username
    public $_dt; //accessdate
    public $_tkn; //token

    private function _authenticate() {
        $syscode = Yii::app()->params['SystemCode'];
        $onrequesttimeout = Yii::app()->params['onrequesttimeout'];
        $retval = 1;
        if (!empty($this->_un)) {
            foreach ($syscode as $key => $value) {
                if ($this->_un == $key) {
                    if (!empty($this->_dt)) {
                        $date1 = new DateTime($this->_dt);
                        $dt = $date1->format('YmdHis');
                        $processtkn = sha1($dt . $value);
                        if (!empty($this->_tkn)) {
                            if ($this->_tkn == $processtkn) {
                                if ($onrequesttimeout) {
//                                    $date2 = new DateTime(date('Y-m-d H:i:s'));
//                                    $diff = $date2->diff($date1);
//                                    $hours = $diff->h;
//                                    $hours = $hours + ($diff->days*24);

                                    $to_time = strtotime(date('Y-m-d H:i:s'));
                                    $from_time = strtotime($this->_dt);
                                    $mins = (int) round(abs($to_time - $from_time) / 60, 2);

                                    if ($mins <= Yii::app()->params['maxrequestprocesstime']) {
                                        $retval = 0; //Authentication Successful
                                    } else {
                                        $retval = 2;
                                    } //Expired Request
                                } else {
                                    $retval = 0;
                                } //Authentication Successful
                            } else {
                                $retval = 1;
                            } //Pass token parameters did not match token process by API.
                        } else {
                            $retval = 3;
                        } //Incomplete Data: Empty Token
                    } else {
                        $retval = 3;
                    } //Incomplete Data: Empty Date Time
                }
            }
        } else {
            $retval = 3;
        } //Incomplete Data: Empty System Username

        return $retval;
    }

    public function actionDeposit() {
        Yii::import('application.components.CasinoController');
        Yii::import('application.components.LoyaltyAPIWrapper');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[Deposit] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        $isconnvalid = $this->_authenticate();
	if ($isconnvalid == 0) {
            $serviceid = trim(trim($request['ServiceID']));
            $cardnumber = trim(trim($request['CardNumber']));
            $amount = trim(trim($request['Amount']));
            $paymenttype = trim(trim($request['PaymentType']));
            $siteid = trim(trim($request['SiteID']));
            $aid = trim(trim($request['AID']));
            $tracenumber = trim(trim($request['TraceNumber']));
            $referencenumber = trim(trim($request['ReferenceNumber']));
            $couponCode = isset($request['CouponCode']) ? trim($request['CouponCode']) : null;
            $paymentTrackingID = isset($request['PaymentTrackingID']) ? trim($request['PaymentTrackingID']) : null;
            if (isset($serviceid) && $serviceid !== '' && isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($paymenttype) && $paymenttype !== '' && isset($siteid) && $siteid !== '' && isset($aid) && $aid !== '') {
                if ($tracenumber == '' && $referencenumber == '') {
                    $bankid = null;
                    $approvalcode = null;
                }

                $membercards = new MemberCardsModel();
                $memberservices = new MemberServicesModel();
                $ewallet = new EwallettransModel();
                $terminalsessions = new TerminalSessionsModel();
                $casinocontroller = new CasinoController();
                $transactionsummarymodel = new TransactionSummaryModel();
                $sitebalancemodel = new SiteBalanceModel();
                $terminalsmodel = new TerminalsModel();
                $sites = new SitesModel();
                $services = new ServicesModel();
                $autoemailModel= new AutoemailLogsModel();
                $mid = $membercards->getMID($cardnumber);

                if (is_array($mid)) {
                    
                    $mid = $mid['MID'];
                    $status = $membercards->getStatusByCardNumber($cardnumber);
                    $cardstatus = $status['Status'];                   
                    if($cardstatus == 1){
		        $userMode = $services->getUserMode($serviceid);
                        if($userMode) {    
                        $hasTerminalSession = $terminalsessions->checkSessionIDwithcard($cardnumber, $serviceid);
                        if (is_array($hasTerminalSession)) {
                            $terminalid = $hasTerminalSession['TerminalID'];
                            $tID = $terminalid;
                            $siteID = $terminalsmodel->getSiteID($terminalid);
                            
                            if ($siteID['SiteID'] != $siteid) {
                                $transMsg = 'Error: Deposit Failed, Player has an existing session on a different site.';
                                $errCode = 24;

                                $data = CommonController::deposit($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }
                        } else {
                            $terminalid = '';
                            $tID = null;
                        }

                        $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid, $serviceid);

                        if ($casinocredentials) {
                            $serviceUsername = $casinocredentials['ServiceUsername'];
                            $servicePassword = $casinocredentials['ServicePassword'];

                            $bcf = $sitebalancemodel->getSiteBalance($siteid);
                            $bcf = $bcf['Balance'];

                            if (($bcf - $amount) < 0) {
                                $errCode = 7;
                                $transMsg = 'Error: BCF is not enough.';

                                $data = CommonController::deposit($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }

                            $balance = $casinocontroller->GetBalance($serviceid, $serviceUsername);

                            if (is_array($balance)) {
                                $playablebalance = $balance['balance'];

                                $transsumid = $terminalsessions->getTransSummaryID($mid, $serviceid);

                                if (is_array($transsumid)) {
                                    $transsumid = $transsumid['TransactionSummaryID'];
                                } else {
                                    $transsumid = null;
                                }
                                /*
                                 * Added By John Aaron Vida
                                 * June 17, 2016
                                 */
                                $idchecked = 0;
                                $csvalidated = 0;

                                $tracking1 = $ewallet->insertEwallet($idchecked, $csvalidated, $cardnumber, $siteid, $mid, $amount, $playablebalance, 'D', $serviceid, 1, $paymenttype, $aid, $transsumid, $tID, $tracenumber, $referencenumber, $paymentTrackingID, $couponCode);
//                                $tracking1 = $ewallet->insertEwallet($cardnumber, $siteid, $mid, $amount, $playablebalance, 'D', $serviceid, 1, $paymenttype, $aid, $transsumid, $tID, $tracenumber, $referencenumber, $paymentTrackingID, $couponCode);
                                $tracking2 = 'D';
                                $tracking3 = $terminalid;
                                $tracking4 = $siteid;

                                if (!$tracking1) {
                                    $errCode = 11;
                                    $transMsg = 'Deposit Failed, There was a pending transaction for this card.';

                                    $data = CommonController::deposit($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }
		                 
                                $count =Yii::app()->params['UBCasinoSkinCount'][$serviceid];
    
				if ($count == 2)
                                { //RTG V15  
                                    $siteclassification = $sites->getSitesClassification($siteid);
		       		    $locatorname = Yii::app()->params['SkinName'][$serviceid][$siteclassification['SitesClass'] - 1];
			        } else {
                                  $locatorname = '';
                                }

                                $resultdeposit = $casinocontroller->Deposit($serviceid, $serviceUsername, $servicePassword, 1, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname);

                                if (is_null($resultdeposit)) {
                                    $transSearchInfo = $casinocontroller->TransactionSerachInfo($serviceid, $serviceUsername, $tracking1, $tracking2, $tracking3, $tracking4);

                                    if (isset($transSearchInfo['TransactionInfo'])) {
                                        //RTG / Magic Macau
                                        if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                                            $initial_deposit = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                                            $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                            $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                                        }
                                        //MG / Vibrant Vegas
                                        elseif (isset($transSearchInfo['TransactionInfo']['MG'])) {
                                            //$initial_deposit = $transSearchInfo['TransactionInfo']['MG']['Balance'];
                                            $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                                            $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                                        }
                                        //PT / PlayTech
                                        elseif (isset($transSearchInfo['TransactionInfo']['PT'])) {
                                            //$initial_deposit = $transSearchInfo['TransactionInfo']['PT']['']; //need to ask if reported amount will be passed from PT
                                            $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                                            $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                                        }
                                    }
                                } else {
                                    if (isset($resultdeposit['TransactionInfo'])) {
                                        //RTG / Magic Macau
                                        if (isset($resultdeposit['TransactionInfo']['DepositGenericResult'])) {
                                            $transrefid = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionID'];
                                            $apiresult = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                                            $apierrmsg = $resultdeposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
                                        }
                                        //MG / Vibrant Vegas
                                        else if (isset($resultdeposit['TransactionInfo']['MG'])) {
                                            $transrefid = $resultdeposit['TransactionInfo']['MG']['TransactionId'];
                                            $apiresult = $resultdeposit['TransactionInfo']['MG']['TransactionStatus'];
                                            $apierrmsg = $resultdeposit['ErrorMessage'];
                                        }
                                        //Rockin Reno
                                        else if (isset($resultdeposit['TransactionInfo']['PT'])) {
                                            $transrefid = $resultdeposit['TransactionInfo']['PT']['TransactionId'];
                                            $apiresult = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                                            $apierrmsg = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                                        }
                                    } else {
                                        $apiresult = '';
                                        $transrefid = NULL;
                                        $apierrmsg = '';
                                    }
                                }

                                if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                                    $transstatus = '1';
                                } else {
                                    $transstatus = '2';
                                }

                                if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved') {

                                    $balance = $casinocontroller->GetBalance($serviceid, $serviceUsername);
                                    $playablebalance = $balance['balance'];

                                    if (!is_null($transsumid)) {
                                        $totalamount = $transactionsummarymodel->getTotalReload($transsumid);
                                        if (is_array($totalamount)) {
                                            $totalamount = $totalamount['WalletReloads'] + $amount;
                                        } else {
                                            $totalamount = $amount;
                                        }
                                        $isupdatedtranssum = $transactionsummarymodel->updateTransSummary($totalamount, $transsumid);

                                        if (!$isupdatedtranssum) {
                                            $errCode = 9;
                                            $transMsg = 'Failed to update Transaction Summary';

                                            $data = CommonController::deposit($transMsg, $errCode);
                                            $this->_sendResponse(200, $data);
                                            exit;
                                        }
                                    }

                                    $isupdatedewallet = $ewallet->updateEwallet($playablebalance, $transrefid, $apiresult, $aid, $transstatus, $tracking1);

                                    if ($isupdatedewallet) {
                                        if ($transstatus == 1) {
                                            $errCode = 0;
                                            $transMsg = 'e-SAFE loading successful';
                                            $newbal = $bcf - $amount;

                                            $sitebalancemodel->updateBcf($newbal, $siteid, 'load');

                                            $memberservices->UpdateBalances($playablebalance, "load-$tracking1", $mid, $serviceid);
    //------------------------------------------------------------------------------------------------->>>>>>>>>>>>>>>>>>>>>>>>

    //
                                            $terminalSessionsModel = new TerminalSessionsModel();
                                            $transdate = CommonController::udate('Y-m-d H:i:s.u');

                                            //Get Terminal Name    
                                            $terminalName = $terminalSessionsModel->getTerminalName($terminalid);

                                            //eWalltetTransID
                                            $eWalletTransID = $tracking1;

                                            //Check if Loyalty
                                            $isLoyalty = Yii::app()->params->Isloyaltypoints; 
                                            
                                            //Loyalty points
                                            if ($isLoyalty == 1) {

                                                $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
                                                $loyalty = new LoyaltyAPIWrapper();

                                                //Insert to loyaltyrequestlogs
                                                $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminalid, $amount, $eWalletTransID, $paymenttype, 1);
                                                
                                                //Insert to ewallettrans    
                                                 $isSuccessful = $loyalty->processPoints($cardnumber, $transdate, $paymenttype, 'R', $amount, $siteid, $eWalletTransID, $terminalName, 1, $couponCode, $serviceid, 1);
                                                    
                                              
                                                // Update loyaltyrequestlogs
                                                if ($isSuccessful) {

                                                   $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 1);
                                                } else {
                                                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 2);
                                                }
                                            }

    //-------------------------------------------;------------------------------------------------------>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>                                 
                                        } else {
                                            $errCode = 8;
                                            $transMsg = 'Deposit Transaction Failed';
                                        }
                                    } else {
                                        $errCode = 10;
                                        $transMsg = 'Failed to update in transaction Table';
                                    }
                                    $data = CommonController::deposit($transMsg, $errCode); 
                                    
                                    /*esafebigreload*/ 
                                    $autoemailamnt = Yii::app()->params->autoemailreload;   
                                    if ($amount>= $autoemailamnt){
                                       
                                        $details = $autoemailModel->getdetails($mid, $tracking2); 
                                         if (is_null($transsumid)){
                                              $transsumid = $details['EwalletTransID'];
                                              $totalamount = $amount;
                                              $terminalcode = null;
                                              $timein = null;
                                         }
                                        else {
                                             $transsumid = $details['TransactionsSummaryID'];
                                             $terminalcode =$details['TerminalCode'];
                                             $timein =  $details['DateStarted'];
                            
                                        }  
                                        $transdatetime =  $details['StartDate']; 
                                        $servicename = $details['ServiceName'];        
                                        $sitename = $details['SiteName'];
                                        $POS =$details['POSAccountNo'];
                                        $name =$details['Name'];
                                        $accname =$name .' '. $sitename;
                                        $timeout = null;
                                        $autoemailModel->insert(1,1,1,$serviceid, 0, 0, $totalamount, 0, $amount,0 ,0, $sitename,$terminalcode, 
                                                           $POS, $cardnumber, $accname,$servicename, $transsumid,$timein, $timeout,$transdatetime);
                                        }                               
           
                                }
                                else {
                                    $errCode = 8;
                                    $tobalance = '0.00';
                                       $ewallet->updateEwallet($tobalance, null, $apiresult, $aid, $transstatus, $tracking1);

                                    $transMsg = 'Deposit Transaction Failed';

                                    $data = CommonController::deposit($transMsg, 2);
                                }
                            } else {
                                $transMsg = $balance;
                                $errCode = 8;

                                $data = CommonController::deposit($transMsg, $errCode);
                            }
                        } else {
                            $transMsg = 'Card does not have existing casino account';
                            $errCode = 6;

                            $data = CommonController::deposit($transMsg, $errCode);
                        }
                     } else {
                            $transMsg = 'Terminal Based transaction is not allowed on this casino';
                            $errCode = 26;

                            $data = CommonController::deposit($transMsg, $errCode);
                        }
                    } else {
                        $errCode = 25; //Invalid cardnumber
                        switch ($cardstatus) {
                            case 0:
                                $transMsg = 'Card is inactive.';
                                break;
                            case 2:
                                $transMsg = 'Card is deactivated.';
                                break;
                            case 5:
                                $transMsg = 'Cannot load on temporary card.';
                                break;
                            case 7:
                                $transMsg = 'Card is already migrated.';
                                break;
                            case 8:
                                $transMsg = 'Card is already migrated.';
                                break;
                            case 9:
                                $transMsg = 'Card is banned.';
                                break;
                            default:
                                $errCode = 5;
                                $transMsg = 'Card is invalid.';
                                break;
                        }

                        $data = CommonController::deposit($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    }
                } else {
                    $transMsg = 'Can\'t get card information';
                    $errCode = 5;

                    $data = CommonController::deposit($transMsg, $errCode);
                }
            } else {
                $errCode = 4;
                if (empty($aid)) {
                    $transMsg = 'AccountID must not be blank';
                }
                if (empty($siteid)) {
                    $transMsg = 'SiteID must not be blank';
                }
                if (empty($amount)) {
                    $transMsg = 'Amount must not be blank';
                }
                if (empty($paymenttype)) {
                    $transMsg = 'Payment type must not be blank';
                }
                if (empty($serviceid)) {
                    $transMsg = 'ServiceID must not be blank';
                }
                if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank';
                }

                $data = CommonController::deposit($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
		    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
	    
        }

        $message = "[Deposit] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);                              
        $this->_sendResponse(200, $data);
    }

    //GetTermsAndCondition Method to Get read value of terms and conditions.txt
    //mcatangan
    public function actionGetTermsAndCondition() {

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[GetTermsAndCondition] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        $isconnvalid = $this->_authenticate();
        if ($isconnvalid == 0) {
            //$this->renderFile(Yii::app()->params['termsandconditionpath']);
            $errCode = 0;
            $transMsg = 'Success';
            $terms = file_get_contents(Yii::app()->params['termsandconditionpath']);
            $data = CommonController::gettermsandcondition($transMsg, $errCode, $terms);
            $this->_sendResponse(200, $data);
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
            $this->_sendResponse(200, $data);
        }
        $message = "[GetTermsAndCondition] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
    }

    public function actionGetbalance() {
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $paramval = CJSON::encode($request);
        $message = "[GetBalance] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $isconnvalid = $this->_authenticate();
        if ($isconnvalid == 0) {
            $cardnumber = trim(trim($request['CardNumber']));          
            if (isset($request['ServiceID'])) {
                $serviceid = trim(trim($request['ServiceID']));

                if ($cardnumber !== '') {
                    $membercards = new MemberCardsModel();
                    $memberservices = new MemberServicesModel();
		    $services = new ServicesModel();
                    $mid = $membercards->getMID($cardnumber);
		    $userMode = $services->getUserMode($serviceid);
                    if ($mid) {
                        $mid = $mid['MID'];
                         if ($userMode['UserMode'] == 1) {
                         
                            $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid, $serviceid);
                                 if ($casinocredentials) {
                                $serviceUsername = $casinocredentials['ServiceUsername'];
                                $casinocontroller = new CasinoController();
                                $balance = $casinocontroller->GetBalance($serviceid, $serviceUsername);
                                if (is_array($balance) && !empty($balance)) {
                                    $playablebalance = $balance['balance'];
                                    $bonusbalance = $balance['bonusBalance'];
                                    $compBalance = $balance['compBalance'];
                                    $playthroughbal = $balance['playthroughBalance'];
                                    $withdrawablebal = $playablebalance - $bonusbalance;
                                    $transMsg = 'Success';
                                    $errCode = 0;

                                    $data = CommonController::getbalance($playablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                                } else {
                                    $redeemablebalance = '';
                                    $bonusbalance = '';
                                    $compBalance = '';
                                    $playthroughbal = '';
                                    $withdrawablebal = '';
                                    $transMsg = 'Can\'t get balance';
                                    $errCode = 8;

                                    $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                                }
                                
                                 
                            } else {
                                $redeemablebalance = '';
                                $bonusbalance = '';
                                $compBalance = '';
                                $playthroughbal = '';
                                $withdrawablebal = '';
                                $transMsg = 'Card does not have existing casino account';
                                $errCode = 6;

                                $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                            }

                             
                            } else {
                            if (empty($serviceid)) {

                                $redeemablebalance = '';
                                $bonusbalance = '';
                                $compBalance = '';
                                $playthroughbal = '';
                                $withdrawablebal = '';
                                $transMsg = 'ServiceID must not be blank.';
                                $errCode = 4;

                                $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                            } else {
                                $redeemablebalance = '';
                                $bonusbalance = '';
                                $compBalance = '';
                                $playthroughbal = '';
                                $withdrawablebal = '';
                                $transMsg = 'Can\'t connect to casino';

                                $errCode = 8;

                                $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                            }
                        } 
                    } else {
                        $redeemablebalance = '';
                        $bonusbalance = '';
                        $compBalance = '';
                        $playthroughbal = '';
                        $withdrawablebal = '';
                        $transMsg = 'Can\'t get card information';
                        $errCode = 5;

                        $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                    }
                } else {
                    if (empty($cardnumber)) {
                        $errCode = 4;
                        $transMsg = 'Card Number must not be blank';
                        $redeemablebalance = '';
                        $bonusbalance = '';
                        $compBalance = '';
                        $playthroughbal = '';
                        $withdrawablebal = '';
                    }

                    $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                }
            } else {
                $redeemablebalance = '';
                $bonusbalance = '';
                $compBalance = '';
                $playthroughbal = '';
                $withdrawablebal = '';
                $transMsg = 'Missing ServiceID parameter.';
                $errCode = 4;

                $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        $message = "[GetBalance] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionWithdraw() {
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[Withdraw] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $serviceid = trim(trim($request['ServiceID']));
            $cardnumber = trim(trim($request['CardNumber']));
            $amount = trim(trim($request['Amount']));
            $siteid = trim(trim($request['SiteID']));
            $aid = trim(trim($request['AID']));
            /*
             * Added By John Aaron Vida
             * June 17, 2016
             */
            $idchecked = trim(trim($request['IDChecked']));
            $csvalidated = trim(trim($request['CSChecked']));

            if (isset($serviceid) && $serviceid !== '' && isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($siteid) && $siteid !== '' && isset($aid) && $aid !== '') {

                $membercards = new MemberCardsModel();
                $memberservices = new MemberServicesModel();
                $ewallet = new EwallettransModel();
                $terminalsessions = new TerminalSessionsModel();
                $casinocontroller = new CasinoController();
                $members = new MembersModel();
                $sites = new SitesModel();
                $services = new ServicesModel();
                $autoemail = new AutoemailLogsModel();
                $mid = $membercards->getMID($cardnumber);

                if (is_array($mid)) {

                    $mid = $mid['MID'];
                    $status = $membercards->getStatusByCardNumber($cardnumber);
                    $cardstatus = $status['Status'];
                    
                    if($cardstatus == 1){

                        $checkpinloginattempts = $members->checkPINLoginAttempts($mid);

                        if ($checkpinloginattempts['PINLoginAttemps'] > Yii::app()->params['maxPinAttempts']) {
                            $transMsg = 'Withdraw Failed, PIN is locked.';
                            $errCode = 13;

                            $data = CommonController::withdraw($transMsg, $errCode);
                            $this->_sendResponse(200, $data);
                            exit;
                        }

                        $terminalid = $terminalsessions->checkSessionIDwithcard($cardnumber, $serviceid);
                        if (is_array($terminalid)) {
                            $terminalid = $terminalid['TerminalID'];
                        } else {
                            $terminalid = null;
                        }

                        $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid, $serviceid);

                        if ($casinocredentials) {
                            $serviceUsername = $casinocredentials['ServiceUsername'];
                            $servicePassword = $casinocredentials['ServicePassword'];

                            $balance = $casinocontroller->GetBalance($serviceid, $serviceUsername);
                            if (is_array($balance)) {
                                $playablebalance = $balance['balance'];

                                if ($amount > $playablebalance) {
                                    $transMsg = 'Input amount is greater than existing balance.';
                                    $errCode = 13;

                                    $data = CommonController::withdraw($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }

                                $tracking1 = $ewallet->insertEwallet($idchecked, $csvalidated, $cardnumber, $siteid, $mid, $amount, $playablebalance, 'W', $serviceid, 1, 1, $aid, null, $terminalid, null, null);
//                                $tracking1 = $ewallet->insertEwallet($cardnumber, $siteid, $mid, $amount, $playablebalance, 'W', $serviceid, 1, 1, $aid, null, $terminalid, null, null);
                                $tracking2 = 'W';
                                $tracking3 = $siteid;
                                $tracking4 = $siteid;

                                if (!$tracking1) {
                                    $errCode = 11;
                                    $transMsg = 'Withdraw Failed, There was a pending transaction for this card.';

                                    $data = CommonController::withdraw($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }
                             
                                $count =Yii::app()->params['UBCasinoSkinCount'][$serviceid];
                                if ($count == 2)
                                { //RTG V15
                                    $siteclassification = $sites->getSitesClassification($siteid);
                                    $locatorname = Yii::app()->params['SkinName'][$serviceid][$siteclassification['SitesClass']-1];
                                } else {
                                    $locatorname = '';
                                }
                                
                                

                                $resultwithdraw = $casinocontroller->Withdraw($serviceid, $serviceUsername, $servicePassword, 1, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname);

                                if (is_null($resultwithdraw)) {
                                    $transSearchInfo = $casinocontroller->TransactionSerachInfo($serviceid, $serviceUsername, $tracking1, $tracking2, $tracking3, $tracking4);

                                    if (isset($transSearchInfo['TransactionInfo'])) {
                                        //RTG / Magic Macau
                                        if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                                            $amount = abs($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount']);
                                            $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                            $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                                        }
                                        //MG / Vibrant Vegas
                                        elseif (isset($transSearchInfo['TransactionInfo']['MG'])) {
                                            //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                                            $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                                            $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                                        }
                                        //PT / PlayTech
                                        if (isset($transSearchInfo['TransactionInfo']['PT'])) {
                                            $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                                            $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                                        }
                                    }
                                } else {
                                    //check Withdraw API Result
                                    if (isset($resultwithdraw['TransactionInfo'])) {
                                        //RTG / Magic Macau
                                        if (isset($resultwithdraw['TransactionInfo']['WithdrawGenericResult'])) {
                                            $transrefid = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                                            $apiresult = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                                        }
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
                                    }
                                }

                                if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                                    $transstatus = '1';
                                } else {
                                    $transstatus = '2';
                                }

                                if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved') {

                                    $balance = $casinocontroller->GetBalance($serviceid, $serviceUsername);
                                    $playablebalance = $balance['balance'];

                                    $ewallet->updateEwallet($playablebalance, $transrefid, $apiresult, $aid, $transstatus, $tracking1);
                                    $errCode = 0;
                                    $transMsg = 'e-SAFE withdraw successful';

                                    $memberservices->UpdateBalances($playablebalance, "withdraw-$tracking1", $mid, $serviceid);
                                    
                                    $Isupdated = $members->resetPinLoginAttempts($mid);
                                    if (!$Isupdated) {
                                        $message = "[Withdraw] Token: " . $this->_tkn . ", Output: CardNumber: $cardnumber, EwalletTransID: $tracking1, ErrorMessage: Failed to reset pin attempts.";
                                        CLoggerModified::log($message, CLoggerModified::WARNING);
                                    }
                                    
                                    $data = CommonController::withdraw($transMsg, $errCode);                                    
                                    
                                    /*eSafebigwithdraw*/
                                    $autoemailamnt = Yii::app()->params->autoemailwithdraw;   

                                    if ($amount>= $autoemailamnt){
                                            $details = $autoemail->getdetails($mid, $tracking2); 
                                            $servicename = $details['ServiceName'];
                                            $sitename = $details['SiteName'];
                                            $POS = $details['POSAccountNo'];
                                            $transdatetime = $details['StartDate'];
                                            $accname = $details['Name'] .' '. $details['SiteName'];
                                            $ewalletid = $details['EwalletTransID'];                                          
                                            $timein = null;
                                            $timeout = null;
                                            $terminalcode = null;

                                            $autoemail->insert(2,1,2,$serviceid, 0, 0, 0, $amount, 0,0 ,0, $sitename,$terminalcode, $POS, 
                                                    $cardnumber, $accname,$servicename, $ewalletid,$timein,$timeout,$transdatetime);
                                    }
                                    
                                
                                } else {
                                    $errCode = 8;
                                    $tobalance = '0.00';
                                    $ewallet->updateEwallet($tobalance, null, $apiresult, $aid, $transstatus, $tracking1);

                                    $transMsg = 'Withdraw Transaction Failed';

                                    $data = CommonController::withdraw($transMsg, $errCode);
                                }
                            } else {
                                $transMsg = $balance;
                                $errCode = 8;

                                $data = CommonController::withdraw($transMsg, $errCode);
                            }
                        } else {
                            if (empty($casinocredentials)) {
                                $transMsg = 'Card does not have existing casino account.';
                                $errCode = 4;
                            }
                            $data = CommonController::withdraw($transMsg, $errCode);
                        }
                    } else {
                        $errCode = 25; //Invalid cardnumber
                        switch ($cardstatus) {
                            case 0:
                                $transMsg = 'Card is inactive.';
                                break;
                            case 2:
                                $transMsg = 'Card is deactivated.';
                                break;
                            case 5:
                                $transMsg = 'Cannot withdraw on temporary card.';
                                break;
                            case 7:
                                $transMsg = 'Card is already migrated.';
                                break;
                            case 8:
                                $transMsg = 'Card is already migrated.';
                                break;
                            case 9:
                                $transMsg = 'Card is banned.';
                                break;
                            default:
                                $errCode = 5;
                                $transMsg = 'Card is invalid.';
                                break;
                        }
                        $data = CommonController::withdraw($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    }
                    
                } else {
                    $transMsg = 'Can\'t get card information.';
                    $errCode = 5;

                    $data = CommonController::withdraw($transMsg, $errCode);
                }
            } else {

                $errCode = 4;

                if (empty($aid)) {
                    $transMsg = 'AccountID must not be blank.';
                }
                if (empty($siteid)) {
                    $transMsg = 'SiteID must not be blank.';
                }
                if (empty($amount)) {
                    $transMsg = 'Amount must not be blank.';
                }
                if (empty($serviceid)) {
                    $transMsg = 'ServiceID must not be blank';
                }
                if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank.';
                }

                $data = CommonController::withdraw($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[Withdraw] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        $this->_sendResponse(200, $data);
    }

    public function actionGetCompPoints() {
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[GetCompPoints] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $compBalance = '';
            $cardnumber = trim(trim($request['CardNumber']));
            $serviceid = trim(trim($request['ServiceID']));
            if (isset($cardnumber) && $cardnumber !== ''&& isset($serviceid) && $serviceid !== '') {
                $membercards = new MemberCardsModel();
                $memberservices = new MemberServicesModel();
                 $services = new ServicesModel();
                $mid = $membercards->getMID($cardnumber);
                if ($mid) {
                    $mid = $mid['MID'];
                    $userMode = $services->getUserMode($serviceid);
                   if($userMode['UserMode']==1){                       
                        $CasinoServiceID = Yii::app()->params['UBCasinoServiceID'];
                        if($serviceid == $CasinoServiceID){
                            $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid, $serviceid);
                            if ($casinocredentials) {
                                $serviceUsername = $casinocredentials['ServiceUsername'];

                                $casinocontroller = new CasinoController();

                                $balance = $casinocontroller->GetBalance($serviceid, $serviceUsername);

                                if (is_array($balance) && !empty($balance)) {
                                    $compBalance = $balance['compBalance'];
                                    $transMsg = 'Success';
                                    $errCode = 0;

                                    $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                                } else {
                                    $transMsg = 'Can\'t get balance,';
                                    $errCode = 8;

                                    $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                                }


                            } else {
                                $transMsg = 'Card does not have existing casino account';
                                $errCode = 6;

                                $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                            }
                        }    else {
                            $transMsg = 'Inactive Casino Service.';
                            $errCode = 4;

                            $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                        }
                        
                    }
                         else{
                            $transMsg = 'Terminal Based transaction is not allowed on this casino';
                            $errCode = 26;
                            $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
             
                       }
                    } else {
                    $transMsg = 'Can\'t get card information';
                    $errCode = 5;

                    $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                }
                
                
                } else { 
                $errCode = 4;
              if (empty($serviceid)) {
                    $transMsg = 'ServiceID must not be blank.';
                }
              if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank';
                }
                $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[GetCompPoints] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionAddCompPoints() {
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[AddCompPoints] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $cardnumber = trim(trim($request['CardNumber']));
            $amount = trim(trim($request['Amount']));
            $siteID = trim(trim($request['SiteID']));
            $serviceID = trim(trim($request['ServiceID']));


            if (isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($siteID) && $siteID !== '' && isset($serviceID) && $serviceID !== '') {
                $membercards = new MemberCardsModel();
                $memberservices = new MemberServicesModel();
                $comppointslogs = new CompPointsLogsModel();
                $terminalsessions = new TerminalSessionsModel();
		$services = new ServicesModel();
                empty($siteID) ? $siteID = NULL : $siteID = $siteID;

                $mid = $membercards->getMID($cardnumber);
                if ($mid) {
                    $mid = $mid['MID'];
                     if(is_numeric($serviceID))   {
                    $userMode = $services->getUserMode($serviceID);
                    //check if terminal-based (UserMode = 0)
                    if ($userMode && $userMode['UserMode'] == 0) {

                        $terminalID = $terminalsessions->checkSessionIDwithcard($cardnumber, $serviceID);
                        $terminalID = $terminalID['TerminalID'];

                        $CasinoServiceID = Yii::app()->params['UBCasinoServiceID'];
                        
                        $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid, $CasinoServiceID);
                       
                        if (!empty($casinocredentials) || !is_null($casinocredentials)) {
                            $serviceUsername = $casinocredentials['ServiceUsername'];

                            $casinocontroller = new CasinoController();



                            $result = $casinocontroller->AddToCurrentBalance($CasinoServiceID, $serviceUsername, $amount);

                            if (is_array($result) && !empty($result)) {
                                $resultMsg = $result['success'];

                                if ($resultMsg == 1) {
                                    //update lifetime points
                                    $getLTpoints = $membercards->getCardPoints($cardnumber);
                                    $totalLTpoints = $getLTpoints['LifetimePoints'] + $amount;
                                    //update life time points
                                    $result = $membercards->updateLifetimePoints($cardnumber, $totalLTpoints);

                                    if ($result['TransCode'] == 0) {
                                        
                                        $cardid = $membercards->getCardID($cardnumber);
                                        $getDateUpdated = $membercards->getDateUpdated($cardnumber);
                                        $processdate = $getDateUpdated['DateUpdated'];
                                        
                                        //Insert cardtransactions for Last Played Site and Date (fire and forget)
                                        $IsSuccess = $membercards->insertcardtrans($serviceID, $siteID, $cardid['CardID'], $mid, 'D', $serviceUsername, $processdate);
                                        if (!$IsSuccess) {
                                            $message = "[AddCompPoints] Token: " . $this->_tkn . ", Output: CardNumber: $cardnumber, ServiceID: $serviceID, ProcessDate: $processdate, ErrorMessage: Failed to insert card transactions";
                                            CLoggerModified::log($message, CLoggerModified::WARNING);
                                        }
                                        
                                        $transMsg = 'Success';
                                        $errCode = 0;
                                    } else {
                                        $transMsg = 'Failure';
                                        $errCode = 8;
                                    }

                                    $comppointslogs->logEvent($mid, $cardnumber, $terminalID, $siteID, $serviceID, $amount, 'D');
                                } else {
                                    $transMsg = 'Failure';
                                    $errCode = 8;
                                }

                                $data = CommonController::addcomppoints($transMsg, $errCode);
                            } else {
                                $transMsg = 'Failed to process comp points';
                                $errCode = 8;

                                $data = CommonController::addcomppoints($transMsg, $errCode);
                            }
                        } else {
                            $transMsg = 'Card does not have existing casino account';
                            $errCode = 6;

                            $data = CommonController::addcomppoints($transMsg, $errCode);
                        }
                    } else {
                        $transMsg = 'Invalid Casino.';
                        $errCode = 12;

                        $data = CommonController::addcomppoints($transMsg, $errCode);
                    }
                    
                  }else{ 
                      $transMsg = 'Invalid Casino';
                      $errCode = 4; 
                      
                  $data = CommonController::addcomppoints($transMsg, $errCode);
                
                    }
                  } else {
                    $transMsg = 'Card not found';
                    $errCode = 5;

                    $data = CommonController::addcomppoints($transMsg, $errCode);
                  }
            } else {
                $errCode = 4;

                if (empty($serviceID)) {
                    $transMsg = 'ServiceID must not be blank';
                } 
                if (empty($siteID)) {
                    $transMsg = 'SiteID must not be blank.';
                }if (empty($amount)) {
                    $transMsg = 'Amount must not be blank.';
                }
                if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank.';
                }

                $data = CommonController::addcomppoints($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[AddCompPoints] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionDeductCompPoints() {
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[DeductCompPoints] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $cardnumber = trim(trim($request['CardNumber']));
            $amount = trim(trim($request['Amount']));
            $siteid = trim(trim($request['SiteID']));
            $serviceid = trim(trim($request['ServiceID']));  
            
            if (isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($siteid) && $siteid !== ''&& isset($serviceid) && $serviceid !== '') {
                empty($siteid) ? $siteid = NULL : $siteid = $siteid;
                $membercards = new MemberCardsModel();
                $memberservices = new MemberServicesModel();
                $comppointslogs = new CompPointsLogsModel();
                $services = new ServicesModel();
                
                $mid = $membercards->getMID($cardnumber);
                if ($mid) {
                    $mid = $mid['MID'];
                    $userMode = $services->getUserMode($serviceid);
                    if($userMode['UserMode']==1){
                        
                    $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid, $serviceid);
                    if ($casinocredentials) {
                        $serviceusername = $casinocredentials['ServiceUsername'];

                        $casinocontroller = new CasinoController();
                        $result = $casinocontroller->GetBalance($serviceid, $serviceusername);

                        $CasinoServiceID = Yii::app()->params['UBCasinoServiceID'];
                        
                        if($serviceid == $CasinoServiceID){
                            if ((float) $result['compBalance'] > 0 && (float) $result['compBalance'] >= $amount) {
                                $result = $casinocontroller->DeductToCurrentBalance($CasinoServiceID,$serviceusername, $amount);

                                if (is_array($result) && !empty($result)) {
                                    $resultMsg = $result['success'];

                                    if ($resultMsg == 1) {
                                        $transMsg = 'Success';
                                        $errCode = 0;
                                        $terminalid = NULL;
                                        $comppointslogs->logEvent($mid, $cardnumber, $terminalid, $siteid, $serviceid, $amount, 'W');
                                    }    else {
                                        $transMsg = 'Failed to process comp points.';
                                        $errCode = 8;
                                    }

                                    $data = CommonController::deductcomppoints($transMsg, $errCode);
                                } else {
                                    $transMsg = 'Failed to process comp points.';
                                    $errCode = 8;
                                }
                            } else {
                                $transMsg = 'Account does not have sufficient comp points.';
                                $errCode = 13;

                                $data = CommonController::deductcomppoints($transMsg, $errCode);
                            }
                        } else {
                            $transMsg = 'Inactive Casino Service.';
                            $errCode = 4;

                            $data = CommonController::deductcomppoints($transMsg, $errCode);
                        }
                        
                    } else {
                        $transMsg = 'Card does not have existing casino account';
                        $errCode = 6;

                        $data = CommonController::deductcomppoints($transMsg, $errCode);
                    }
                  } 
/*changes*/     else{
                    $transMsg = 'Terminal Based transaction is not allowed on this casino';
                        $errCode = 26;

                        $data = CommonController::deductcomppoints($transMsg, $errCode);
                }   
                } else {
                    $transMsg = 'Card not found';
                    $errCode = 5;

                    $data = CommonController::deductcomppoints($transMsg, $errCode);
                }
            } else {
                $errCode = 4;
                 if (empty($serviceid)) {
                    $transMsg = 'ServiceID must not be blank.';
                }
                if (empty($siteid)) {
                    $transMsg = 'SiteID must not be blank';
                }
                if (empty($amount)) {
                    $transMsg = 'Amount must not be blank';
                }
                if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank';
                }

                $data = CommonController::deductcomppoints($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            

            
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[DeductCompPoints] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionCheckpin() {
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[CheckPin] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $cardnumber = trim(trim($request['CardNumber']));
            $pin = trim(trim($request['PIN']));
            $membercards = new MemberCardsModel();
            $status = $membercards->getCardStatus($cardnumber);
            //Check if Card is Active

            if (isset($cardnumber) && $cardnumber != "" && isset($pin) && $pin != "" && $cardnumber != "" && isset($this->_dt) && $this->_dt != "" && isset($this->_tkn) && $this->_tkn != "") {
                // Check IF PIN is Numeric and not more than 6 digits
                if (is_numeric($pin) && strlen($pin) <= 6) {

                    switch ($status['Status']) {
                        case 0:
                            $transMsg = 'Card is Invalid.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                            break;

                        case 1:
                            // Get MID by Card Number
                            $MID = $membercards->getMID($cardnumber);
                            // Get PIN by MID
                            $members = new MembersModel();
                            $memberpin = $members->getPIN2($MID['MID']);

                            $pinlogattempts = $members->checkPINLoginAttempts($MID['MID']);

                            if ((int) $pinlogattempts['PINLoginAttemps'] >= Yii::app()->params['maxPinAttempts']) {
                                $transMsg = 'PIN is locked.';
                                $errCode = 25;
                                $data = CommonController::checkPin($transMsg, $errCode);
                                $members->incrementLoginAttempts($MID['MID']);
                            } else {
                                if (sha1($pin) == $memberpin['PIN']) {
                                    $transMsg = 'Transaction successful. PIN and UB Card is valid.';
                                    $errCode = 0;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                } else {
                                    $members->incrementLoginAttempts($MID['MID']);
                                    $transMsg = 'Invalid PIN Code';
                                    $errCode = 14;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                }
                            }
                            break;

                        case 2:
                            $transMsg = 'Checking of PIN is not allowed for Deactivated Card.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                            break;

                        case 5:
                            $transMsg = 'Checking of PIN is not allowed for Active Temporary Card.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                            break;

                        case 7:
                            $transMsg = 'Checking of PIN is not allowed for New Migrated Card.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                            break;

                        case 8:
                            $transMsg = 'Checking of PIN is not allowed for Temporary Migrated Card.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                            break;

                        case 9:
                            $transMsg = 'Checking of PIN is not allowed for Banned Card.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                            break;

                        default:
                            $transMsg = 'Invalid Card.';
                            $errCode = 4;
                            $data = CommonController::checkPin($transMsg, $errCode);
                    }
                } else {
                    $transMsg = 'PIN must be exactly 6 digits.';
                    $errCode = 9;
                    $data = CommonController::checkPin($transMsg, $errCode);
                }
            } else {

                $errCode = 4;

                if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank.';
                }
                if (empty($pin)) {
                    $transMsg = 'PIN must not be blank.';
                }
                if (empty($this->_dt)) {
                    $transMsg = 'Access Date must not be blank';
                }
                if (empty($this->_tkn)) {
                    $transMsg = 'Token must not be blank';
                }

                $data = CommonController::checkPin($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[CheckPin] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionChangepin() {
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $data = '';
        $paramval = CJSON::encode($request);
        $message = "[ChangePin] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $actionCode = trim(trim($request['ActionCode']));
            $cardNumber = trim(trim($request['CardNumber']));
            

            if (isset($actionCode) && $actionCode != "") {
                if (isset($cardNumber) && $cardNumber != "") {
                    // Get MID by Card Number
                    $membercards = new MemberCardsModel();
                    $MID = $membercards->getMID($cardNumber);
                    // Get PIN by MID
                    $members = new MembersModel();
                    $status = $membercards->getCardStatus($cardNumber);
                    // Check if Card is Active

                    switch ($status['Status']) {
                        case 0:
                            $transMsg = 'Card is Invalid.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                            break;

                        case 1:
                            // Reset PIN
                            if ($actionCode == 0) {
                                $MID = $MID['MID'];
                                if ($MID == "" || $MID > 0) {
                                    $isEwallet = $members->getIsEWallet($MID);
                                    if ($isEwallet == 1) {

                                        $digits = 6;
                                        $continue = true;

                                        while ($continue) {
                                            $default = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                                            $firstDigit = substr($default, 0, 1);
                                            $isIncrementalDigits = false;
                                            $isDecrementalDigits = false;
                                            $isIncrementalBy2 = false;
                                            $isDecrementalBy2 = false;

                                            $recentDigit = null;
                                            for ($i = 0; $i < $digits; $i++) {
                                                $currentDigit = substr($default, $i, 1);
                                                if ($currentDigit == $recentDigit + (1)) {
                                                    $isIncrementalDigits = true;
                                                    break;
                                                }
                                                $recentDigit = $currentDigit;
                                            }

                                            if ($isIncrementalDigits) {
                                                continue;
                                            }


                                            $recentDigit2 = null;
                                            for ($i = 0; $i < $digits; $i++) {
                                                $currentDigit = substr($default, $i, 1);
                                                if ($currentDigit == $recentDigit2 - (1)) {
                                                    $isDecrementalDigits = true;
                                                    break;
                                                }
                                                $recentDigit2 = $currentDigit;
                                            }

                                            if ($isDecrementalDigits) {
                                                continue;
                                            }


                                            $recentDigit3 = null;
                                            for ($i = 1; $i < $digits; $i++) {
                                                $currentDigit = substr($default, $i, 1);
                                                if ($currentDigit == $firstDigit + (2 * $i)) {
                                                    $isIncrementalBy2 = true;
                                                } else {
                                                    $isIncrementalBy2 = false;
                                                    break;
                                                }
                                                $recentDigit3 = $currentDigit;
                                            }

                                            if ($isIncrementalBy2) {
                                                continue;
                                            }

                                            $recentDigit4 = null;
                                            for ($i = 1; $i < $digits; $i++) {
                                                $currentDigit = substr($default, $i, 1);

                                                if ($currentDigit == $firstDigit - (2 * $i)) {
                                                    $isDecrementalBy2 = true;
                                                } else {
                                                    $isDecrementalBy2 = false;
                                                    break;
                                                }
                                                $recentDigit4 = $currentDigit;
                                            }

                                            if ($isDecrementalBy2) {
                                                continue;
                                            }


                                            $isSameDigits = false;
                                            for ($i = 0; $i < $digits; $i++) {
                                                $needleDigit = substr($default, $i, 1);
                                                for ($x = 0; $x < $digits; $x++) {
                                                    if ($i != $x) {
                                                        $currentDigit = substr($default, $x, 1);
                                                        if ($needleDigit == $currentDigit) {
                                                            $isSameDigits = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                /*
                                                  if($recentDigit3!=$currentDigit){
                                                  $isSameDigits=false;
                                                  break;
                                                  }
                                                  $recentDigit3 = $currentDigit; */
                                            }

                                            if ($isSameDigits) {
                                                continue;
                                            } else {
                                                $continue = false;
                                                break;
                                            }
                                        }

                                        $reset = $members->updatePINReset($MID, $default);
                                        if ($reset == 1) {
                                            //Pin reset success
                                            $transMsg = 'PIN reset successful.';
                                            $errCode = 0;
                                            $data = CommonController::resetPIN($transMsg, $errCode, $default);
                                        } else {
                                            // Pin reset failed
                                            $transMsg = 'PIN reset failed.';
                                            $errCode = 15;

                                            $data = CommonController::resetPIN($transMsg, $errCode, '');
                                        }
                                    } else {
                                        $transMsg = "Player's account must be e-SAFE.";
                                        $errCode = 26;
                                        $data = CommonController::resetPIN($transMsg, $errCode, '');
                                    }
                                } else {
                                    // Pin reset failed
                                    $transMsg = 'Invalid Card Number.';
                                    $errCode = 4;
                                    $data = CommonController::resetPIN($transMsg, $errCode, '');
                                }
                            } else {
                                // Change PIN
                                $currentPin = trim(trim($request['CurrentPin']));
                                $newPin = trim(trim($request['NewPin']));
                                if (isset($currentPin) && $currentPin != "" && isset($newPin) && $newPin != "") {
                                    if (is_numeric($currentPin) && strlen($currentPin) <= 6 && is_numeric($newPin) && strlen($newPin) <= 6) {
                                        // Change PIN
                                        // Validate current PIN
                                        $memberpin = $members->getPIN2($MID['MID']);
                                        if (sha1($currentPin) == $memberpin["PIN"]) {
                                            // Check if New PIN and Current PIN is the same
                                            if (sha1($currentPin) == sha1($newPin)) {
                                                $transMsg = 'New PIN and Current PIN is the same.';
                                                $errCode = 16;
                                                $data = CommonController::changePin($transMsg, $errCode);
                                            } else {
                                                // Change PIN
                                                $changePIN = $members->updatePIN($MID['MID'], $newPin);
                                                if ($changePIN == 1) {
                                                    $transMsg = 'PIN successfully changed.';
                                                    $errCode = 0;
                                                    $members->resetPinLoginAttempts($MID['MID']);
                                                    $data = CommonController::changePin($transMsg, $errCode);
                                                //insert to audittrail
                                                $auditTrailModel = new AuditTrailModel();    
                                                $transdetails = 'LP - ChangePIN' .' - '. $cardNumber;                  
                                                $logtoAuditrail = $auditTrailModel->logEvent($transdetails,110);
                                                
                                                } else {
                                                    $transMsg = 'Change PIN failed.';
                                                    $errCode = 15;
                                                    $data = CommonController::changePin($transMsg, $errCode);
                                                }
                                            }
                                        } else {
                                            $transMsg = 'Invalid PIN Code';
                                            $errCode = 14;
                                            $data = CommonController::changePin($transMsg, $errCode);
                                        }
                                    } else {
                                        $transMsg = 'PIN must be numeric and exactly 6 digits.';
                                        $errCode = 9;
                                        $data = CommonController::changePin($transMsg, $errCode);
                                    }
                                } else {
                                    $transMsg = 'Please enter Current PIN and New PIN.';
                                    $errCode = 4;
                                    $data = CommonController::changePin($transMsg, $errCode);
                                }
                            }
                            break;

                        case 2:
                            $transMsg = 'Cheking of PIN is not allowed for Deactivated Card.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                            break;

                        case 5:
                            $transMsg = 'Cheking of PIN is not allowed for Active Temporary Card.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                            break;

                        case 7:
                            $transMsg = 'Cheking of PIN is not allowed for New Migrated Card.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                            break;

                        case 8:
                            $transMsg = 'Cheking of PIN is not allowed for Temporary Migrated Card.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                            break;

                        case 9:
                            $transMsg = 'Cheking of PIN is not allowed for Banned Card.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                            break;

                        default:
                            $transMsg = 'Invalid Card.';
                            $errCode = 4;
                            $data = CommonController::changePin($transMsg, $errCode);
                    }
                } else {
                    $transMsg = 'Card Number must not be blank';
                    $errCode = 4;
                    $data = CommonController::changePin($transMsg, $errCode);
                }
            } else {
                $transMsg = 'Action Code must not be blank.';
                $errCode = 4;
                $data = CommonController::changePin($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[ChangePin] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        $this->_sendResponse(200, $data);
    }
    
    public function actionGetTerminalStatus() {
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[GetTerminalStatus] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        //Initialization of variablkes
        $terminalStatus = '';
        $statusDesc = '';
        $errCode = '';
        
        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            //Trim all white spaces
            $terminalcode = strtoupper(trim(trim($request['TerminalCode'])));

            if (isset($terminalcode) && $terminalcode !== '') {
                $terminalsmodel = new TerminalsModel();

                //Checking of Terminal Code
                if (substr($terminalcode, -3) == 'VIP') {
                    $transMsg = 'Only regular terminal is allowed.';
                    $errCode = 33;
                    $data = CommonController::getterminalstatus($transMsg, $errCode, $terminalStatus, $statusDesc);
                } else {

                    //Checking of Terminal Code
                    if (substr($terminalcode, 0, 5) == 'ICSA-') {
                        $terminalcode = substr($terminalcode, 5);
                    }
                    //Get Terminal ID
                    $terminalInfo = $terminalsmodel->getTerminalID($terminalcode);
                    $terminalID = $terminalInfo['TerminalID'];
                    if ($terminalID) { // If TerminalID exists
                        //Get Terminal Status
                        $terminalStatus = $terminalsmodel->getTerminalStatus($terminalID);
                        if ($terminalStatus <> '') {
                            switch ($terminalStatus) {
                                case 0: $statusDesc = 'Inactive or Deactivated'; break;
                                case 1: $statusDesc = 'Active'; break;
                            }

                            $transMsg = 'Successful';
                            $errCode = 0;
                            $data = CommonController::getterminalstatus($transMsg, $errCode, $terminalStatus, $statusDesc);
                        } else {
                            $transMsg = 'Can\'t get Terminal Status.';
                            $errCode = 35;
                            $data = CommonController::getterminalstatus($transMsg, $errCode, $terminalStatus, $statusDesc);
                        }
                    } else {
                        $transMsg = 'Invalid Terminal Code.';
                        $errCode = 34;
                        $data = CommonController::getterminalstatus($transMsg, $errCode, $terminalStatus, $statusDesc);
                    }
                }
            } else {
                $transMsg = 'TerminalCode must not be blank.';
                $errCode = 32;
                $data = CommonController::getterminalstatus($transMsg, $errCode, $terminalStatus, $statusDesc);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete or Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::getterminalstatus($transMsg, $errCode, $terminalStatus, $statusDesc);
        }

        $message = "[GetTerminalStatus] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
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
                case 404:
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
        Yii::app()->end();
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

    /*
     * @author Jeremiah D. Lachica
     * @date January 29, 2015
     * @param string TerminalCode
     * @param int ServiceID
     * @param string CardNumber
     * @return JSON
     */

    public function actionUnlock() {
        $data = $this->_readJsonRequest();

        $eCode = null;
        $transMessage = null;

        $paramval = CJSON::encode($data);
        $message = "[Unlock] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {

            if (isset($data['TerminalCode']) && isset($data['ServiceID']) && isset($data['CardNumber']) && isset($data['SystemUsername']) && isset($data['AccessDate']) && isset($data['AccessDate'])) {

                $terminalCode = trim($data['TerminalCode']);
                $serviceID = trim($data['ServiceID']);
                $cardNumber = trim($data['CardNumber']);

                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $servicesModel = new ServicesModel();
                $memberCardsModel = new MemberCardsModel();
                $memberServicesModel = new MemberServicesModel();
                $eWalletModel = new CommonEWalletTransactionsModel();
                $membersModel = new MembersModel();
                $transactionSummaryModel = new TransactionSummaryModel();

                if ($validate->isAllNotEmpty(array($terminalCode, $serviceID, $cardNumber, $systemUsername))) {
                    if (ctype_alnum($cardNumber)) {
                        $mid = Utilities::fetchFirstValue($memberCardsModel->getMID($cardNumber));
                        if ($mid) {
                            $cardStatus = Utilities::fetchFirstValue($memberCardsModel->getCardStatus($cardNumber));
                            if ($cardStatus == 1) {
                                $isVIP = Utilities::fetchFirstValue($membersModel->getIsVIPByMID($mid));
                                if ($isVIP !== false) {
                                    if (ctype_alnum($terminalCode)) {
                                        $terminalCode = str_replace("VIP", '', $terminalCode);
                                        $terminalCode .= $isVIP == 1 ? 'VIP' : '';
                                        $terminalID = Utilities::fetchFirstValue($terminalsModel->getTerminalID($terminalCode));
                                        if ($terminalID) {
                                            if (is_numeric($serviceID)) {

                                                $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));
                                                if ($siteID) {

                                                    if (Utilities::fetchFirstValue($terminalSessionsModel->isTerminalHasActiveUBSession($terminalID))) {

                                                        $userMode = Utilities::fetchFirstValue($servicesModel->getUserMode($serviceID));
                                                        if ($userMode) {
                                                            if (Utilities::fetchFirstValue($terminalSessionsModel->isCardHasActiveUBSession($cardNumber))) {

                                                                $credentials = $memberServicesModel->getCasinoCredentialsCostelloAbbott($mid, $serviceID);
                                                                if (isset($credentials['ServiceUsername']) && isset($credentials['ServicePassword']) && isset($credentials['HashedServicePassword'])) {

                                                                    $serviceUsername = $credentials['ServiceUsername'];
                                                                    $servicePassword = $credentials['ServicePassword'];
                                                                    $hashedServicePassword = $credentials['HashedServicePassword'];

                                                                    $balance = $this->retrieveBalance($serviceID, $serviceUsername);


                                                                    if ($balance) {

                                                                        $transactionReferenceID = Utilities::generateUDate('YmdHisu');
                                                                        $amount = '0';
                                                                        $transactionType = 'D';

                                                                        $trackingID = '';
                                                                        $voucherCode = '';
                                                                        $paymentType = 1;
                                                                        $serviceTransactionID = '';
                                                                        $deposit = '0';

                                                                        $accountsModel = new AccountsModel();
                                                                        $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteID($siteID));
                                                                        if ($AID) {
                                                                            //$dateEnded = $transactionSummaryModel->getDateEnded($mid, $terminalID);
                                                                            //checkeSafeSessionValidity
                                                                            if (Utilities::fetchFirstValue($terminalSessionsModel->checkeSafeSessionValidity($mid, $terminalID)) == '') {
                                                                                $transactionResult = $eWalletModel->insert($mid, $terminalID, $serviceID, $cardNumber, $userMode, $serviceUsername, $servicePassword, $hashedServicePassword, $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID, $voucherCode, $paymentType, $serviceTransactionID, $deposit, $AID, $balance);

                                                                                if ($transactionResult > 0) {
                                                                                    //Get Card ID in cards table and Date Started in transactionsummary
                                                                                    $transdate = $eWalletModel->getTransSumDate($transactionResult);
                                                                                    $cardid = $memberCardsModel->getCardID($cardNumber);

                                                                                    //Insert cardtransactions for Last Played Site and Date (fire and forget)
                                                                                    $IsSuccess = $memberCardsModel->insertcardtrans($serviceID, $siteID, $cardid['CardID'], $mid, 'D', $serviceUsername, $transdate['DateStarted']);
                                                                                    if (!$IsSuccess) {
                                                                                        $message = "[Unlock] Token: " . $this->_tkn . ", Output: CardNumber: $cardNumber, TransSumID: $transactionResult, ErrorMessage: Failed to insert card transactions";
                                                                                        CLoggerModified::log($message, CLoggerModified::WARNING);
                                                                                    }
                                                                                    
                                                                                    $Isupdated = $membersModel->resetPinLoginAttempts($mid);
                                                                                    if (!$Isupdated) {
                                                                                        $message = "[Unlock] Token: " . $this->_tkn . ", Output: CardNumber: $cardNumber, EwalletTransID: $transactionResult, ErrorMessage: Failed to reset pin attempts.";
                                                                                        CLoggerModified::log($message, CLoggerModified::WARNING);
                                                                                    }

                                                                                    $eCode = 0;
                                                                                    $transMsg = 'Transaction successful, Terminal is now unlocked.';
                                                                                } else {
                                                                                    $eCode = 18; //Failed to start session
                                                                                    $transMsg = 'Failed to start session.';
                                                                                }
                                                                            } else {
                                                                                $eCode = 23;
                                                                                $transMsg = 'Terminal is unlocked already.';
                                                                            }
                                                                        } else {
                                                                            $eCode = 4;
                                                                            $transMsg = 'Can\'t get account information';
                                                                        }
                                                                    } else {
                                                                        $eCode = 8; //Can't get balance
                                                                        $transMsg = 'Can\'t get balance.';
                                                                    }
                                                                } else {
                                                                    $eCode = 6; //Card does not have existing casino account
                                                                    $transMsg = 'Card does not have existing casino account.';
                                                                }
                                                            } else {
                                                                $eCode = 17; //Card has an existing active session
                                                                $transMsg = 'Card has an existing active session.';
                                                            }
                                                        } else {
                                                            $eCode = 12; //Invalid Usercode
                                                            $transMsg = 'Can\'t get account information.';
                                                        }
                                                    } else {
                                                        $eCode = 17; // Card/Terminal has no existing active session
                                                        $transMsg = 'Card/Terminal has no existing active session.';
                                                    }
                                                } else {
                                                    $eCode = 4; //Incomplete Data: No SiteID
                                                    $transMsg = 'Can\'t get site information.';
                                                }
                                            } else {
                                                $eCode = 4; //Invalid data
                                                $transMsg = 'Can\'t ge casino information';
                                            }
                                        } else {
                                            $eCode = 22; //Invalid data
                                            $transMsg = 'Cannot retrieve VIP Level';
                                        }
                                    } else {
                                        $eCode = 4; //Invalid data
                                        $transMsg = 'Can\'t get terminal information';
                                    }
                                } else {
                                    $eCode = 22;
                                    $transMsg = 'Cannot retrieve VIP Level';
                                }
                            } else {
                                $eCode = 25; //invalid card number
                                $transMsg = 'Not allowed to use temporary card number.';
                            }
                        } else {
                            $eCode = 5; //Card not found
                            $transMsg = 'Card not found';
                        }
                    } else {
                        $eCode = 4; //Invalid data
                        $transMsg = 'Can\'t get card information.';
                    }
                } else {
                    $eCode = 4; //Incomplete data

                    if (empty($terminalCode)) {
                        $transMsg = 'Terminal Code must not be empty.';
                    }
                    if (empty($serviceID)) {
                        $transMsg = 'ServiceID must not be empty.';
                    }
                    if (empty($cardNumber)) {
                        $transMsg = 'Card Number must not be empty.';
                    }
                    if (empty($systemUsername)) {
                        $transMsg = 'System Username must not be empty.';
                    }
                }
                $data = CommonController::unlock($transMsg, $eCode);
            } else {

                $eCode = 4; //Invalid data 
                if (empty($data['TerminalCode'])) {
                    $transMsg = 'Terminal Code must not be empty.';
                }
                if (empty($data['CardNumber'])) {
                    $transMsg = 'Card Number must not be empty.';
                }
                if (empty($data['SystemUsername'])) {
                    $transMsg = 'System Username must not be empty.';
                }
                if (empty($data['AccessDate'])) {
                    $transMsg = 'Access Date must not be empty.';
                }
                if (empty($data['ServiceID'])) {
                    $transMsg = 'ServiceID must not be empty.';
                }
                $data = CommonController::unlock($transMsg, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[Unlock] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    /*
     * @author Jeremiah D. Lachica
     * @date February 02, 2015
     * @param string TerminalCode
     * @param int ServiceID
     * @param string CardNumber
     * @return JSON
     */
    
   /**
     * @author Rica Anne de Mesa
     * @date October 1, 2015
     * Input parameter added: ServiceID
      */
    public function actionForceLogout() {
        $data = $this->_readJsonRequest();

        $eCode = null;
        $transMessage = null;

        $paramval = CJSON::encode($data);
        $message = "[ForceLogout] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));
            
         $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {

             if (isset($data['Login']) && !empty($data['Login']) && isset($data['SystemUsername']) && isset($data['AccessDate']) && isset($data['ServiceID']) && !empty($data['ServiceID']) ) {

                $serviceUsername = trim($data['Login']);
                $serviceid = trim($data['ServiceID']);

                

                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $eWalletModel = new CommonEWalletTransactionsModel();
                $casinocontroller = new CasinoController();
                $accountsModel = new AccountsModel();
                $membersModel = new MembersModel();
                $services = new ServicesModel();
                $autoemailModel = new AutoemailLogsModel();
                
                if (!$validate->isNullOrEmpty($serviceUsername)) {

                    if (ctype_alnum($serviceUsername)) {
                        $userMode = $services->getUserMode($serviceid);
                        if($userMode['UserMode']==1){
                        $casinoDetails = $terminalSessionsModel->getCasinoDetailsByUBServiceLogin($serviceUsername);
                        $transactionSummaryID = 0;
                        if ($casinoDetails) {
                           if($casinoDetails['ServiceID']==$serviceid){
                            $terminalID = $casinoDetails['TerminalID'];
                            $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));

                            if ($siteID) {
                                $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteID($siteID));
                                if ($AID) {

                                    $balances = $casinocontroller->GetBalance($serviceid, $serviceUsername);

                                    if (is_array($balances) && !empty($balances)) {
                                        $balance = $balances['balance'];
                                        $serviceID = $casinoDetails['ServiceID'];
                                        $cardNumber = $casinoDetails['LoyaltyCardNumber'];
                                        $mid = $casinoDetails['MID'];
                                        $transactionSummaryID = $casinoDetails['TransactionSummaryID'];

                                        $transactionReferenceID = Utilities::generateUDate('YmdHisu');

                                        $transactionType = 'W';

                                        $trackingID = '';
                                        $voucherCode = '';
                                        $paymentType = 1;
                                        $serviceTransactionID = '';
                                        $withdrawal = 0;
                                        $amount = 0;
                                        $userMode = $casinoDetails['UserMode'];

                                        $isWallet = Utilities::fetchFirstValue($membersModel->getIsWalletByMID($mid));
                                        if ($isWallet == 1) {
                                            //if the terminal is genesis. just exit game client
                                            $terminalType = $terminalsModel->getTerminalType($terminalID);
                                            if ($terminalType != 1) {
                                                $transactionResult = $eWalletModel->forceLogout($mid, $terminalID, $serviceID, $cardNumber, $userMode, $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID, $voucherCode, $paymentType, $serviceTransactionID, $AID, $transactionSummaryID, $withdrawal, $balance);

                                                if ($transactionResult) {
                                                    $eCode = 0;
                                                    //var_dump($serviceID,$serviceUsername);exit;
                                                    $transMsg = 'Transaction successful, Terminal is now locked.';
                                                    $casinocontroller->logout($serviceID, $serviceUsername);
   
                                                } else {
                                                    $eCode = 19; //Failed to end session
                                                    $transMsg = 'Failed to end session';
                                                }
                                            } else {
                                                $eCode = 0;
                                                $transMsg = 'Transaction successful, Terminal is now locked.';
                                            }
                                        } else {
                                            $eCode = 26; //Player must be an e-SAFE account.
                                            $transMsg = 'Player must be an e-SAFE account.';
                                        }
                                    } else {
                                        $eCode = 8; // Can't get balance.
                                        $transMsg = 'Can\'t get balance.';
                                    }
                                } else {
                                    $eCode = 4; //Invalid data
                                    $transMsg = 'Can\'t get account information';
                                }
                            } else {
                                $eCode = 4; //Invalid data
                                $transMsg = 'Can\'t get site information';
                            }
                         } else {
                            $eCode = 21; //Card does not have existing casino account
                            $transMsg = 'Card does not have an existing terminal session on this casino.';
                        }
                        } else {
                            $eCode = 21; //Card does not have existing casino account
                            $transMsg = 'Card does not have an existing terminal session.';
                        }
                        
                        } else {
                             $eCode = 4;//Invalid Casino Service
                             $transMsg = 'Failed to End Session. Invalid Casino.';
                        }
                    } else {
                        $eCode = 4; //Invalid data
                        $transMsg = 'Can\'t get service information';
                    }
                } else {
                    $eCode = 4; //Invalid data
                    $transMsg = 'Service Name must not be blank';
                }
                
                $data = CommonController::forceLogout($transMsg, $eCode);
                
                /*esafebigwinnings*/                                            
                $autoemailamnt = Yii::app()->params->autoemailwinnings;
                if($transactionSummaryID!=0){
                $trans = $autoemailModel->getValues($transactionSummaryID,$serviceid);
                $vstartbal = $trans['StartBalance'];
                $vendbal = $trans['EndBalance'];
                $vtotalload = $trans['WalletReloads'];
                $vnetwin = (float) $vendbal  - ((float) $vstartbal + (float) $vtotalload); //compute the net win  

                    if($vnetwin>=$autoemailamnt){
                        $servicename = $trans['ServiceName'];   
                        $terminalcode = $trans['TerminalCode'];
                        $sitename = $trans['SiteName'];
                        $POS = $trans['POSAccountNo'];
                        $accname = $trans['Name'] .' '. $trans['SiteName'];
                        $timein = $trans['DateStarted'];
                        $timeout =  $trans['DateEnded'];
                        $transdatetime = null;

                        $autoemailModel->insert(3,1,3,$serviceid, $vstartbal, $vendbal, $vtotalload, 0, 0,0 ,$vnetwin, 
                                $sitename,$terminalcode, $POS, $cardNumber, $accname,$servicename, $transactionSummaryID,
                                $timein, $timeout,$transdatetime);
                    }
                }
                } else {
                $eCode = 4; //Incomplete data
                if (empty($data['SystemUsername'])) {
                    $transMsg = 'System Username must not be blank';
                }
                if (empty($data['AccessDate'])) {
                    $transMsg = 'Access Date must not be blank';
                }
                if (empty($data['ServiceID'])) {
                    $transMsg = 'ServiceID must not be blank';
                }
                if (empty($data['Login'])) {
                    $transMsg = 'Login must not be blank';
                } 


                $data = CommonController::forceLogout($transMsg, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[ForceLogout] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);                               
        $this->_sendResponse(200, $data);
    }

    private function retrieveBalance($serviceID, $serviceUsername) {
        $casinocontroller = new CasinoController();
        $balance = null;
        $numberOfAttempts = 3;
        $attempts = 0;

        while ($attempts < $numberOfAttempts) {
            $balances = $casinocontroller->GetBalance($serviceID, $serviceUsername);
            if (is_array($balances) && !empty($balances)) {
                $balance = $balances['balance'];
                $attempts = 5;
            } else {
                $attempts++;
            }
        }
        return $balance;
    }

    public function actionUpdateTerminalState() {
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $params = preg_replace('/\s+/', ' ', print_r($request, true));
        $message = "[UpdateTerminalState] Input: " . $params;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();
        
        if ($isconnvalid == 0) {
            $terminals = new TerminalsModel();
            $terminalsessions = new TerminalSessionsModel();
            $terminalname = trim(trim($request['TerminalName']));
            $serviceid = trim(trim($request['ServiceID']));
            $cardnumber = trim(trim($request['CardNumber']));

            if (!empty($terminalname) && !empty($serviceid) && !empty($cardnumber)) {
                $terminalid = Utilities::fetchFirstValue($terminals->getTerminalID($terminalname));

                if (!empty($terminalid)) {
                    $isvalid = Utilities::fetchFirstValue($terminalsessions->checkSessionValidity($terminalid, $serviceid, $cardnumber));

                    if ($isvalid) {
                        $result = $terminalsessions->updateTerminalState($terminalid, $serviceid, $cardnumber);
                        if ($result) {
                            $eCode = 0;
                            $transMsg = "Terminal has been successfully locked.";
                            $data = CommonController::updateterminalstate($transMsg, $eCode);
                        } else {
                            $eCode = 20;
                            $transMsg = "Failed to lock terminal.";
                            $data = CommonController::updateterminalstate($transMsg, $eCode);
                        }
                    } else {
                        $eCode = 21;
                        $transMsg = "Failed to lock. Terminal has no valid session.";
                        $data = CommonController::updateterminalstate($transMsg, $eCode);
                    }
                } else {
                    $eCode = 4;
                    $transMsg = "Invalid terminal name.";
                    $data = CommonController::updateterminalstate($transMsg, $eCode);
                }
            } else {
                $eCode = 4;
                if (empty($terminalname)) {
                    $transMsg = "Terminal Name must not be blank.";
                }
                if (empty($serviceid)) {
                    $transMsg = "ServiceID must not be blank.";
                }
                if (empty($cardnumber)) {
                    $transMsg = "Card Number must not be blank.";
                }

                $data = CommonController::updateterminalstate($transMsg, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[UpdateTerminalState] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    /**
     * Create Session method for e-SAFE v15
     * 
     * Get Casino balance, then start a new session by inserting 
     * in terminalsessions table. 
     * 
     * @modified by MGE
     */
    public function actionCreateSession() {
        $data = $this->_readJsonRequest();

        $eCode = null;
        $transMessage = null;

        $paramval = CJSON::encode($data);
        $message = "[CreateSession] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {

            if (isset($data['TerminalCode']) && isset($data['ServiceID']) && isset($data['CardNumber']) && isset($data['SystemUsername']) && isset($data['AccessDate']) && isset($data['AccessDate'])) {

                $terminalCode = trim($data['TerminalCode']);
                $serviceID = trim($data['ServiceID']);
                $cardNumber = trim($data['CardNumber']);

                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $servicesModel = new ServicesModel();
                $memberCardsModel = new MemberCardsModel();
                $memberServicesModel = new MemberServicesModel();
                $eWalletModel = new CommonEWalletTransactionsModel();
                $membersModel = new MembersModel();
                
                if(strpos($terminalCode, 'ICSA-') !== false) {
                    $rawterminalCode = $terminalCode;
                } else { $rawterminalCode = "ICSA-".$terminalCode; }

                if ($validate->isAllNotEmpty(array($terminalCode, $serviceID, $cardNumber, $systemUsername))) {
                    if (ctype_alnum($cardNumber)) {
                        $mid = Utilities::fetchFirstValue($memberCardsModel->getMID($cardNumber));
                        if ($mid) {
                            $cardStatus = Utilities::fetchFirstValue($memberCardsModel->getCardStatus($cardNumber));
                            if ($cardStatus == 1) {

                                $isVIP = Utilities::fetchFirstValue($membersModel->getIsVIPByMID($mid));
                                if ($isVIP !== false) {
                                    if (ctype_alnum($terminalCode)) {
                                        $terminalCode = str_replace("VIP", '', $terminalCode);
                                        $terminalCode .= $isVIP == 1 ? 'VIP' : '';
                                        $terminalID = Utilities::fetchFirstValue($terminalsModel->getTerminalID($terminalCode));
                                        if ($terminalID) {
                                            if (is_numeric($serviceID)) {

                                                $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));
                                                if ($siteID) {

                                                    if (!Utilities::fetchFirstValue($terminalSessionsModel->isTerminalHasActiveUBSession($terminalID))) {
                                                        if (Utilities::fetchFirstValue($terminalSessionsModel->checkActiveSession($rawterminalCode)) == 0) {
                                                                $userMode = Utilities::fetchFirstValue($servicesModel->getUserMode($serviceID));
								if ($userMode) {
                                                                    if (!Utilities::fetchFirstValue($terminalSessionsModel->isCardHasActiveUBSession($cardNumber))) {
                                                                        //get service credentials
                                                                        $credentials = $memberServicesModel->getCasinoCredentialsAbbott($mid, $serviceID);
                                                                        if (isset($credentials['ServiceUsername']) && isset($credentials['ServicePassword']) && isset($credentials['HashedServicePassword'])) {

                                                                            $serviceUsername = $credentials['ServiceUsername'];
                                                                            $servicePassword = $credentials['ServicePassword'];
                                                                            $hashedServicePassword = $credentials['HashedServicePassword'];

                                                                            $balance = $this->retrieveBalance($serviceID, $serviceUsername);


                                                                            if ($balance) {

                                                                                $transactionReferenceID = Utilities::generateUDate('YmdHisu');
                                                                                $amount = '0';
                                                                                $transactionType = 'D';

                                                                                $trackingID = '';
                                                                                $voucherCode = '';
                                                                                $paymentType = 1;
                                                                                $serviceTransactionID = '';
                                                                                $deposit = '0';

                                                                                $accountsModel = new AccountsModel();
                                                                                $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteID($siteID));
                                                                                if ($AID) {

                                                                                    $transactionResult = $eWalletModel->insert2($mid, $terminalID, $serviceID, $cardNumber, $userMode, $serviceUsername, $servicePassword, $hashedServicePassword, $balance);

                                                                                    if ($transactionResult) {
                                                                                        $eCode = 0;
                                                                                        $transMsg = 'Successful';
                                                                                    } else {
                                                                                        $eCode = 18; //Failed to start session
                                                                                        $transMsg = 'Failed to start session';
                                                                                    }
                                                                                    } else {
                                                                                    $eCode = 4;
                                                                                    $transMsg = 'Virtual Cashier is required.';
                                                                                }
                                                                            } else {
                                                                                $eCode = 8; //Can't get balance
                                                                                $transMsg = 'Can\'t get balance.';
                                                                            }
                                                                        } else {
                                                                            $eCode = 6; //Card does not have existing casino account
                                                                            $transMsg = 'Card does not have existing casino account.';
                                                                        }
                                                                    } else {
                                                                        $eCode = 17; //Card has an existing active session
                                                                        $transMsg = 'Card has an existing active session.';
                                                                    }
                                                                } else {
                                                                    $eCode = 12; //Invalid Usercode
                                                                    $transMsg = 'Can\'t get casino information.';
                                                                }
                                                        } else {
                                                            $eCode = 17; // Terminal has an existing active session
                                                            $transMsg = 'Terminal has an existing active session.';
                                                        }
                                                    } else {
                                                        $eCode = 17; // Terminal has an existing active session
                                                        $transMsg = 'Terminal has an existing active session.';
                                                    }
                                                } else {
                                                    $eCode = 4; //Incomplete Data: No SiteID
                                                    $transMsg = 'Can\'t get site information.';
                                                }
                                            } else {
                                                $eCode = 4; //Invalid data
                                                $transMsg = 'Can\'t get casino information.';
                                            }
                                        } else {
                                            $eCode = 22; //Invalid data
                                            $transMsg = 'Can\'t get terminal information.';
                                        }
                                    } else {
                                        $eCode = 4; //Invalid data
                                        $transMsg = 'Can\'t get terminal name.';
                                    }
                                } else {
                                    $eCode = 22;
                                    $transMsg = 'Can\'t get player\'s account information.';
                                }
                            } else {
                                $eCode = 25; //Invalid cardnumber
                                switch ($cardStatus) {
                                    case 0:
                                        $transMsg = 'Card is inactive.';
                                        break;
                                    case 2:
                                        $transMsg = 'Card is deactivated.';
                                        break;
                                    case 7:
                                        $transMsg = 'Card is already migrated.';
                                        break;
                                    case 8:
                                        $transMsg = 'Card is already migrated.';
                                        break;
                                    case 9:
                                        $transMsg = 'Card is banned.';
                                        break;
                                    default:
                                        $transMsg = 'Card is invalid.';
                                        break;
                                }
                            }
                        } else {
                            $eCode = 5; //Card not found
                            $transMsg = 'Card is invalid.';
                        }
                    } else {
                        $eCode = 4; //Invalid data
                        $transMsg = 'Card is invalid.';
                    }
                } else {
                    $eCode = 4; //Incomplete data
                    if (empty($terminalCode)) {
                        $transMsg = 'Terminal Code must not be blank';
                    }
                    if (empty($serviceID)) {
                        $transMsg = 'ServiceID must not be blank.';
                    }
                    if (empty($cardNumber)) {
                        $transMsg = 'Card Number must not be blank.';
                    }
                    if (empty($systemUsername)) {
                        $transMsg = 'System Username must not be blank.';
                    }
                }
                $data = CommonController::createsession($transMsg, $eCode);
            } else {
                $eCode = 4; //Invalid data
                if (empty($data['TerminalCode'])) {
                    $transMsg = 'Terminal Code must not be blank';
                }
                if (empty($data['ServiceID'])) {
                    $transMsg = 'ServiceID must not be blank.';
                }
                if (empty($data['CardNumber'])) {
                    $transMsg = 'Card Number must not be blank.';
                }
                if (empty($data['SystemUsername'])) {
                    $transMsg = 'System Username must not be blank.';
                }
                if (empty($data['AccessDate'])) {
                    $transMsg = 'Access Date must not be blank.';
                }

                $data = CommonController::createsession($transMsg, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[CreateSession] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionRemovesession() {
        $data = $this->_readJsonRequest();

        $paramval = CJSON::encode($data);
        $message = "[RemoveSession] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {

            if (isset($data['TerminalCode']) && isset($data['CardNumber']) && isset($data['SystemUsername']) && isset($data['AccessDate']) && isset($data['AccessDate'])) {

                $terminalCode = trim(str_replace('VIP', '', $data['TerminalCode']));
                $cardNumber = trim($data['CardNumber']);

                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $memberCardsModel = new MemberCardsModel();

                //get mid by card number
                $mid = $memberCardsModel->getMID($cardNumber);
                if (count($mid) > 0) {
                    //get the terminalID
                    $terminalCode = Yii::app()->params['prefix'] . $terminalCode;
                    $terminalIDs = $terminalsModel->getRegVIPTerminalID($terminalCode);
                    if (count($terminalIDs) > 1) {
                        $arrterminal = array();
                        foreach ($terminalIDs as $t) {
                            $arrterminal[] = $t['TerminalID'];
                        }
                        unset($terminalIDs);
                        $terminalIDs = implode(",", $arrterminal);
                        $hasActiveSession = $terminalSessionsModel->isArrTerminalHasSession($terminalIDs, $cardNumber);
                        //check if has active session
                        if ($hasActiveSession) {
                            //check if the session has transaction summary, else cancel the removing of session
                            if (is_null($hasActiveSession['TransactionSummaryID'])) {
                                //remove session
                                $deleteSession = $terminalSessionsModel->deleteSessionByCardAndTerminal($hasActiveSession['TerminalID'], $cardNumber);
                                if ($deleteSession['TransCode'] == 0) {
                                    $eCode = 0; //successful
                                    $transMsg = 'Transaction successful, Terminal session has been successfully removed.';
                                } else {
                                    $eCode = 19;
                                    $transMsg = 'Failed to end session.';
                                }
                            } else {
                                $eCode = 24;
                                $transMsg = 'This process is not applicable for sessions that has transactions.';
                            }
                        } else {
                            $eCode = 21;
                            $transMsg = 'Card does not have an existing session.';
                        }
                    } else {
                        $eCode = 4;
                        $transMsg = 'Can\'t get terminal information';
                    }
                } else {
                    $eCode = 4;
                    $transMsg = 'Can\'t get member information';
                }
                //check if the terminal's sessions has already an transaction summary
            } else {

                $eCode = 4; //Invalid data
                if (empty($data['TerminalCode'])) {
                    $transMsg = 'Terminal Code must not be blank';
                }
                if (empty($data['CardNumber'])) {
                    $transMsg = 'Card Number must not be blank.';
                }
                if (empty($data['SystemUsername'])) {
                    $transMsg = 'System Username must not be blank.';
                }
                if (empty($data['AccessDate'])) {
                    $transMsg = 'Access Date must not be blank.';
                }
            }
//            $transMessage = $transactionMessage[$eCode];
            $data = CommonController::removeSession($transMsg, $eCode);
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[RemoveSession] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    /**
     * Unlock Genesis API Controller
     * Created by: Mark Kenneth Esguerra
     */
        public function actionUnlockgenesis() {
        $data = $this->_readJsonRequest();



        $eCode = null;
        $transMessage = null;

        $paramval = CJSON::encode($data);
        $message = "[UnlockGenesis] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {

            if (isset($data['TerminalCode']) && isset($data['ServiceID']) && isset($data['CardNumber']) && isset($data['SystemUsername']) && isset($data['AccessDate']) && isset($data['AccessDate'])) {

                $terminalCode = trim($data['TerminalCode']);
                $serviceID = trim($data['ServiceID']);
                $cardNumber = trim($data['CardNumber']);

                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $servicesModel = new ServicesModel();
                $memberCardsModel = new MemberCardsModel();
                $memberServicesModel = new MemberServicesModel();
                $eWalletModel = new CommonEWalletTransactionsModel();
                $membersModel = new MembersModel();
                $egmSessionModel = new EGMSessionModel();

                if ($validate->isAllNotEmpty(array($terminalCode, $serviceID, $cardNumber, $systemUsername))) {
                    if (ctype_alnum($cardNumber)) {
                        $mid = Utilities::fetchFirstValue($memberCardsModel->getMID($cardNumber));
                        if ($mid) {
                            $isVIP = Utilities::fetchFirstValue($membersModel->getIsVIPByMID($mid));
                            if ($isVIP !== false) {
                                if (ctype_alnum($terminalCode)) {
                                    $terminalCode = str_replace("VIP", '', $terminalCode);
                                    $terminalCode .= $isVIP == 1 ? 'VIP' : '';
                                    $terminalID = Utilities::fetchFirstValue($terminalsModel->getTerminalID($terminalCode));
                                    if ($terminalID) {
                                        //Check if the player has already an EGM session in the terminal
                                        $hasEGM = $egmSessionModel->checkEGMSession($mid, $terminalID);
                                        if ($hasEGM > 0) {
                                            if (is_numeric($serviceID)) {

                                                $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));
                                                if ($siteID) {

                                                    if (!Utilities::fetchFirstValue($terminalSessionsModel->isTerminalHasActiveUBSession($terminalID))) {

                                                        $userMode = Utilities::fetchFirstValue($servicesModel->getUserMode($serviceID));
                                                        if ($userMode) {
                                                            if (!Utilities::fetchFirstValue($terminalSessionsModel->isCardHasActiveUBSession($cardNumber))) {

                                                                $credentials = $memberServicesModel->getCasinoCredentialsAbbott($mid, $serviceID);
                                                                if (isset($credentials['ServiceUsername']) && isset($credentials['ServicePassword']) && isset($credentials['HashedServicePassword'])) {

                                                                    $serviceUsername = $credentials['ServiceUsername'];
                                                                    $servicePassword = $credentials['ServicePassword'];
                                                                    $hashedServicePassword = $credentials['HashedServicePassword'];

                                                                    $balance = $this->retrieveBalance($serviceID, $serviceUsername);


                                                                    if ($balance) {

                                                                        $transactionReferenceID = Utilities::generateUDate('YmdHisu');
                                                                        $amount = '0';
                                                                        $transactionType = 'D';

                                                                        $trackingID = '';
                                                                        $voucherCode = '';
                                                                        $paymentType = 1;
                                                                        $serviceTransactionID = '';
                                                                        $deposit = '0';

                                                                        $accountsModel = new AccountsModel();
                                                                        $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteIDGenesis($siteID));
                                                                        if ($AID) {

                                                                            $transactionResult = $eWalletModel->insertWithTerminalSession($mid, $terminalID, $serviceID, $cardNumber, $userMode, $serviceUsername, $servicePassword, $hashedServicePassword, $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID, $voucherCode, $paymentType, $serviceTransactionID, $deposit, $AID, $balance);

                                                                            if ($transactionResult) {
                                                                                
                                                                                $Isupdated = $membersModel->resetPinLoginAttempts($mid);
                                                                                if (!$Isupdated) {
                                                                                    $message = "[UnlockGenesis] Token: " . $this->_tkn . ", Output: CardNumber: $cardNumber, EwalletTransID: $transactionResult, ErrorMessage: Failed to reset pin attempts.";
                                                                                    CLoggerModified::log($message, CLoggerModified::WARNING);
                                                                                }
                                                                                
                                                                                $eCode = 0;
                                                                                $transMsg = 'Transaction successful, Terminal is now unlocked.';
                                                                            } else {
                                                                                $eCode = 18; //Failed to start session
                                                                                $transMsg = 'Failed to start session.';
                                                                            }
                                                                        } else {
                                                                            $eCode = 4;
                                                                            $transMsg = 'Can\'t get account information';
                                                                        }
                                                                    } else {
                                                                        $eCode = 8; //Can't get balance
                                                                        $transMsg = 'Can\'t get balance.';
                                                                    }
                                                                } else {
                                                                    $eCode = 6; //Card does not have existing casino account
                                                                    $transMsg = 'Card does not have existing casino account.';
                                                                }
                                                            } else {
                                                                $eCode = 17; //Card has an existing active session
                                                                $transMsg = 'Card has an existing active session.';
                                                            }
                                                        } else {
                                                            $eCode = 12; //Invalid Usercode
                                                            $transMsg = 'Can\'t get account information.';
                                                        }
                                                    } else {
                                                        $eCode = 17; // Card/Terminal has an existing active session
                                                        $transMsg = 'Card/Terminal has an existing active session.';
                                                    }
                                                } else {
                                                    $eCode = 4; //Incomplete Data: No SiteID
                                                    $transMsg = 'Can\'t get site information.';
                                                }
                                            } else {
                                                $eCode = 4; //Invalid data
                                                $transMsg = 'Can\'t get casino information';
                                            }
                                        } else {
                                            $eCode = 27; //No EGM
                                            $transMsg = 'Player has no EGM session.';
                                        }
                                    } else {
                                        $eCode = 22; //Invalid data
                                        $transMsg = 'Cannot retrieve VIP Level';
                                    }
                                } else {
                                    $eCode = 4; //Invalid data
                                    $transMsg = 'Can\'t get terminal information';
                                }
                            } else {
                                $eCode = 22;
                                $transMsg = 'Cannot retrieve VIP Level';
                            }
                        } else {
                            $eCode = 5; //Card not found
                            $transMsg = 'Card not found';
                        }
                    } else {
                        $eCode = 4; //Invalid data
                        $transMsg = 'Can\'t get card information';
                    }
                } else {
                    $eCode = 4; //Invalid data
                    if (empty($terminalCode)) {
                        $transMsg = 'Terminal Code must not be blank';
                    }
                    if (empty($serviceID)) {
                        $transMsg = 'ServiceID must not be blank';
                    }
                    if (empty($cardNumber)) {
                        $transMsg = 'Card Number must not be blank';
                    }
                    if (empty($systemUsername)) {
                        $transMsg = 'System Username must not be blank';
                    }
                }
             $data = CommonController::unlockgenesis($transMsg, $eCode);
            } else {
                $eCode = 4; //Invalid data
//                if (empty($data['TerminalCode'])) {
//                    $transMsg = 'Terminal Code must not be blank';
//                }
//                if (empty($data['CardNumber'])) {
//                    $transMsg = 'Card Number must not be blank.';
//                }
//                if (empty($data['ServiceID'])) {
//                    $transMsg = 'ServiceID must not be blank.';
//                }
//                if (empty($data['SystemUsername'])) {
//                    $transMsg = 'System Username must not be blank.';
//                }
//                if (empty($data['AccessDate'])) {
//                    $transMsg = 'Access Date must not be blank.';
//                }
                $transMsg = 'All Fields Are Required';
                $data = CommonController::unlockgenesis($transMsg, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[UnlockGenesis] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionForceLogoutGen() {
        $data = $this->_readJsonRequest();

        $eCode = null;
        $transMessage = null;

        $paramval = CJSON::encode($data);
        $message = "[ForceLogoutGen] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));


        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {

            if (  isset($data['Login']) && !empty($data['Login']) && isset($data['SystemUsername']) && !empty($data['AccessDate']) && isset($data['AccessDate'])&& isset($data['ServiceID']) && !empty($data['ServiceID']) ) {

                $serviceUsername = trim($data['Login']);
                $serviceid = trim($data['ServiceID']);
                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $eWalletModel = new CommonEWalletTransactionsModel();
                $casinocontroller = new CasinoController();
                $accountsModel = new AccountsModel();
                $membersModel = new MembersModel();
                $egmSessionsModel = new EGMSessionModel();
                $services = new ServicesModel();
                $autoemailModel = new AutoemailLogsModel();
                
                if (!$validate->isNullOrEmpty($serviceUsername)) {

                    if (ctype_alnum($serviceUsername)) {
                          
                        $userMode = $services->getUserMode($serviceid);
                        if($userMode['UserMode']==1){
                        $casinoDetails = $terminalSessionsModel->getCasinoDetailsByUBServiceLogin($serviceUsername);
                        //$transactionSummaryID = 0;
                        if (!empty($casinoDetails) && isset($casinoDetails['TerminalID'])  && isset($casinoDetails['LoyaltyCardNumber']) && isset($casinoDetails['MID']) && isset($casinoDetails['TransactionSummaryID']) && isset($casinoDetails['UserMode'])) {
                            if($casinoDetails['ServiceID']==$serviceid){
                            $serviceID=$casinoDetails['ServiceID'];
                            $terminalID = $casinoDetails['TerminalID'];
                            $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));

                            if ($siteID) {
                                $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteIDGenesis($siteID));
                                if ($AID) {

                                    $balances = $casinocontroller->GetBalance($serviceID, $serviceUsername);

                                    if (is_array($balances) && !empty($balances)) {
                                        $balance = $balances['balance'];
                                        
                                        $cardNumber = $casinoDetails['LoyaltyCardNumber'];
                                        $mid = $casinoDetails['MID'];
                                        $transactionSummaryID = $casinoDetails['TransactionSummaryID'];
                                        //check first if player has an EGM session
                                        $EGMSessionID = $egmSessionsModel->getEGMSessionID($mid, $terminalID);
                                        if ($EGMSessionID > 0) {
                                            $transactionReferenceID = Utilities::generateUDate('YmdHisu');

                                            $transactionType = 'W';

                                            $trackingID = '';
                                            $voucherCode = '';
                                            $paymentType = 1;
                                            $serviceTransactionID = '';
                                            $withdrawal = 0;
                                            $amount = 0;
                                            $userMode = $casinoDetails['UserMode'];

                                            $isWallet = Utilities::fetchFirstValue($membersModel->getIsWalletByMID($mid));
                                            if ($isWallet == 1) {
                                                //the terminal must be a genesis
                                                $terminalType = $terminalsModel->getTerminalType($terminalID);
                                                if ($terminalType != 0) {
                                                    $transactionResult = $eWalletModel->forceLogout($mid, $terminalID, $serviceID, $cardNumber, $userMode, $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID, $voucherCode, $paymentType, $serviceTransactionID, $AID, $transactionSummaryID, $withdrawal, $balance);
                                                    if ($transactionResult) {
                                                       
                                                        //remove the EGM Session
                                                        $egmSessionsModel->removeEGMSession($EGMSessionID);
                                                        $eCode = 0;
                                                        $transMsg = 'Transaction successful, Terminal is now locked.';
                                                        $casinocontroller->logout($serviceID, $serviceUsername);
                                                        $changepass = new PCWSAPI();
                                                        $r = $changepass->ChangePassword(1, $serviceUsername, $serviceID, 3); 
                                                    } else {
                                                        $eCode = 19; //Failed to end session
                                                        $transMsg = 'Failed to end session';
                                                    }
                                                }
                                            } else {
                                                $eCode = 22; //Player must be an e-SAFE account.
                                                $transMsg = 'Player must be an e-SAFE account.';
                                            }
                                        } else {
                                            $eCode = 26;
                                            $transMsg = 'Player has no EGM session.';
                                        }
                                    } else {
                                        $eCode = 8; // Can't get balance.
                                        $transMsg = 'Can\'t get balance.';
                                    }
                                } else {
                                    $eCode = 4; //Invalid data
                                    $transMsg = 'Can\'t get account information.';
                                }
                            } else {
                                $eCode = 4; //Invalid data
                                $transMsg = 'Can\'t get site information.';
                            }
                        } else {
                            $eCode = 23; //Card does not have existing casino account
                            $transMsg = 'Card does not have existing terminal session on this casino.';
                        }    
                        } else {
                            $eCode = 23; //Card does not have existing casino account
                            $transMsg = 'Card does not have existing terminal session.';
                        }
                      } else {            
                             $eCode = 4;//Invalid Casino Service
                             $transMsg = 'Failed to End Session. Invalid Casino.';                         
                        }        
                        
                        
                    } else {
                        $eCode = 4; //Invalid data
                        $transMsg = 'Can\'t get service information.';

                    }
                } else {
                    $eCode = 4; //Invalid data
                    $transMsg = 'Service Name must not be blank.';
                  
                }
                
                $data = CommonController::forceLogoutGen($transMsg, $eCode);
                
                /*esafebigwinnings*/
                $autoemailamnt = Yii::app()->params->autoemailwinnings;
                //if($transactionSummaryID!=0){                                    
                $trans = $autoemailModel->getValues($transactionSummaryID,$serviceID);                    
                $vstartbal = $trans['StartBalance'];
                $vendbal = $trans['EndBalance'];
                $vtotalload = $trans['WalletReloads'];
                $vgenwithdraw = $trans['GenesisWithdrawal'];
                $vnetwin = ((float) $vendbal + (float) $vgenwithdraw)  - ((float) $vstartbal + (float) $vtotalload); //compute the net win  
                                                    
                     if($vnetwin>=$autoemailamnt){ 
                         $servicename = $trans['ServiceName'];
                         $terminalcode = $trans['TerminalCode'];
                         $sitename = $trans['SiteName'];
                         $POS = $trans['POSAccountNo'];
                         $accname = $trans['Name'] .' '. $trans['SiteName'];
                         $timein = $trans['DateStarted'];
                         $timeout =  $trans['DateEnded'];
                         $transdatetime = null;
                         $autoemailModel->insert(3,1,3,$serviceid, $vstartbal, $vendbal, $vtotalload, 0, 0, 
                                         0 ,$vnetwin, $sitename,$terminalcode, $POS, $cardNumber, $accname, 
                                         $servicename, $transactionSummaryID,$timein, $timeout,$transdatetime);
                    }
                //  }
                } else {
                $eCode = 4; //Incomplete data
                
                if (empty($data['SystemUsername'])) {
                    $transMsg = 'System Username must not be blank';
                }
                if (empty($data['AccessDate'])) {
                    $transMsg = 'Access Date must not be blank';
                }
                if (empty($data['ServiceID'])) {
                    $transMsg = 'ServiceID must not be blank';
                }
                if (empty($data['Login'])) {
                    $transMsg = 'Login must not be blank';
                }
                
             $data = CommonController::forceLogoutGen($transMsg, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[ForceLogoutGen] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

    public function actionEsafeConversion() {
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[EsafeConversion] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $cardnumber = strip_tags(trim($request['CardNumber']));
            $password = strip_tags(trim($request['Password']));
            $pin = strip_tags(trim($request['PIN']));
            $confirmpin = strip_tags(trim($request['ConfirmPIN']));

            //models initialization
            $membercards = new MemberCardsModel();
            $members = new MembersModel();
            $refewalletalloweduser = new Ref_EwalletAllowedUserModel();
            $terminalsessions = new TerminalSessionsModel();
            $egmsessions = new EGMSessionModel();

            if (isset($cardnumber) && $cardnumber != "" && isset($password) && $password != "" && isset($pin) && $pin != "" && isset($confirmpin) && $confirmpin != "" && isset($this->_dt) && $this->_dt != "" && isset($this->_tkn) && $this->_tkn != "") {
                $mid = Utilities::fetchFirstValue($membercards->getMID($cardnumber));

                //check if account is e-SAFE
                $isesafe = Utilities::fetchFirstValue($members->getIsWalletByMID($mid));
                if ($isesafe == 0) {
                    //check if card is active
                    $cardstatus = Utilities::fetchFirstValue($membercards->getCardStatus($cardnumber));
                    if ($cardstatus == 1 || $cardstatus == 5) {
                        //check if card is whitelisted
                        $iswhitelisted = Utilities::fetchFirstValue($refewalletalloweduser->checkIfCardIsWhitelisted($mid));
                        $isallowed = $iswhitelisted == 1 && Yii::app()->params['isAllowedWhitelisting'] ? 1 : !Yii::app()->params['isAllowedWhitelisting'] ? 1 : 0;
                        if ($isallowed == 1) {
                            //check if password is valid
                            $ispwvalid = Utilities::fetchFirstValue($members->checkIfPWIsValid($mid, $password));
                            if ($ispwvalid == 1) {
                                //check if pin is exactly 6 characters long
                                if (is_numeric($pin) && strlen($pin) == 6) {
                                    //check if pin is the same as confirm pin
                                    if ($pin == $confirmpin) {
                                        //check if card has an existing terminal session
                                        $hasterminalsession = Utilities::fetchFirstValue($terminalsessions->isCardHasActiveSession($cardnumber));
                                        if ($hasterminalsession == 0) {
                                            //check if card has an existing egmsession
                                            $hasegmsession = Utilities::fetchFirstValue($egmsessions->hasEGMSession($mid));
                                            if ($hasegmsession == 0) {
                                                $isupdated = $members->convertToESAFE($mid);
                                                if ($isupdated == 1) {
                                                    $errCode = 0;
                                                    $transMsg = 'Conversion to e-SAFE is successful.';
                                                } else {
                                                    $errCode = 22;
                                                    $transMsg = 'Failed to convert to e-SAFE.';
                                                }
                                            } else {
                                                $errCode = 17;
                                                $transMsg = 'Card has an existing EGM session.';
                                            }
                                        } else {
                                            $errCode = 17;
                                            $transMsg = 'Card has an existing terminal session.';
                                        }
                                    } else {
                                        $errCode = 4;
                                        $transMsg = 'Pin is different from confirm pin.';
                                    }
                                } else {
                                    $errCode = 4;
                                    $transMsg = 'Pin is not exactly 6 digits long.';
                                }
                            } else {
                                $errCode = 4;
                                $transMsg = 'Password is invalid.';
                            }
                        } else {
                            $errCode = 29;
                            $transMsg = 'Card must be white listed.';
                        }
                    } else {
                        $errCode = 4;
                        $transMsg = 'Card must be active.';
                    }
                } else {
                    $errCode = 5;
                    $transMsg = 'Account is already e-SAFE.';
                }
            } else {
                $errCode = 4;

                if (empty($cardnumber)) {
                    $transMsg = 'Card Number must not be blank.';
                }
                if (empty($password)) {
                    $transMsg = 'Password must not be blank.';
                }
                if (empty($pin)) {
                    $transMsg = 'PIN must not be blank.';
                }
                if (empty($confirmpin)) {
                    $transMsg = 'Confirm PIN must not be blank.';
                }
                if (empty($this->_dt)) {
                    $transMsg = 'Access Date must not be blank';
                }
                if (empty($this->_tkn)) {
                    $transMsg = 'Token must not be blank';
                }
            }
            $data = CommonController::eSafeConversion($transMsg, $errCode);
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[EsafeConversion] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }
    
    
    
    public function actionChangePassword() {
        Yii::import('application.components.CasinoController');
        
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));

        $paramval = CJSON::encode($request);
        $message = "[ChangePassword] Input: " . $paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if ($isconnvalid == 0) {
            $usermode = trim(trim($request['Usermode']));
            $login = trim(trim($request['Login']));
            $serviceid = trim(trim($request['ServiceID']));
            $source = trim(trim($request['Source']));
            if (isset($usermode) && $usermode !== '' && isset($login) && $login !== '' && isset($serviceid) && $serviceid !== '' && isset($source) && $source !== ''
            ) {
                $memberservices = new MemberServicesModel();
                $services = new ServicesModel();
                $casinocontroller = new CasinoController();
                $terminals = new TerminalsModel();
                $auditTrailModel = new AuditTrailModel();
                $genpasswordtbModel= new GeneratePasswordTBModel();
                $genpasswordubModel= new GeneratePasswordUBModel();
                $terminalservicesModel = new TerminalServices();
                $vgenpwdid = 0;
                $terminal = 'ICSA-'.$login;
                if (strstr($login, "ICSA-"))
                {
                    $login1 = str_replace('ICSA-', '',$login );        
                }
                if (strstr($login, "VIP"))
                {
                    $login = str_replace('VIP', '',$login );        
                }
                if ($source ==0 || $source ==1 ||$source ==2 ||$source ==3){
                $servicegroupname = $services->getServiceGrpNameById($serviceid);
                $vprovidername = $servicegroupname;
                if ($usermode==0){
                                    $prefix = Yii::app()->params['prefix'];
                                    $terminalcode = $prefix . $login;
                                    $terminalid = $terminals->getTerminalID($login);
                                    $terminalidvip = $terminals->getTerminalID($login.'VIP');                         
                                    $siteid = $terminals->getSiteID($terminalid['TerminalID']);    
                                    $rpwdbatch = $genpasswordtbModel->chkpwdbatch();
                                    if ($rpwdbatch) {
                                    $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                                    }                                         
                if ($vgenpwdid > 0) 
                {                 
                    if (strstr($vprovidername, "RTG")) 
                    { 
                                    if ($vprovidername=="RTG")
                                    {
                                        $servicegrpid = 1;
                                    }
                                    else
                                    {
                                        $servicegrpid = 4;
                                    }
                                    $vretrievepwd = $genpasswordtbModel->getgeneratedpassword($vgenpwdid, $servicegrpid);
                                    $vgenpassword = $vretrievepwd['PlainPassword'];
                                    $vgenhashed = $vretrievepwd['EncryptedPassword'];
                                    $password = $vgenpassword;
                                    $oldpassresult = $terminalservicesModel->getterminalpassword($terminalid['TerminalID'], $serviceid);
                                    $oldpassresultvip = $terminalservicesModel->getterminalpassword($terminalidvip['TerminalID'], $serviceid);
                                    if (isset($oldpassresult['ServicePassword']))
                                    {
                                        $oldpassword = $oldpassresult['ServicePassword'];
                                        $oldpasswordvip = $oldpassresultvip['ServicePassword'];
                                        $result = $casinocontroller->changePassword($serviceid, 1, $terminalcode, $oldpassword, $password);
                                        $resultvip = $casinocontroller->changePassword($serviceid, 1, $terminalcode.'VIP', $oldpasswordvip, $password);
                                        if ($result && $resultvip) 
                                        {
                                            $result4 = $terminalservicesModel->updateterminalpassword($password, $vgenhashed, $terminalid['TerminalID'],$terminalidvip['TerminalID'], $serviceid);
                                            if ($result4 == 1) 
                                            {
                                                $result5 = $updbatchpwd = $genpasswordtbModel->updateGenPwdBatch($siteid['SiteID'], $vgenpwdid);
                                                $Source = ';Source[' .$source. ']';
                                                $transdetails = 'ChangePass' .' - '. $terminal .''. $Source;
                                                $logtoAuditrail = $auditTrailModel->logEvent($transdetails,111);
                                                if ($logtoAuditrail)
                                                {
                                                    $transMsg = 'Terminal Password Successfully Changed.';
                                                    $errCode = 0;
                                                    $data = CommonController::changepassword($transMsg, $errCode); 
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $transMsg = 'Invalid Login or TerminalCode.';
                                        $errCode = 34;
                                        $data = CommonController::changepassword($transMsg, $errCode);                                               
                                    }
                         }      
                elseif($vprovidername == "MG"){
                        $vretrievepwd = $genpasswordtbModel->getgeneratedpassword($vgenpwdid, 2);
                        $vgenpassword = $vretrievepwd['PlainPassword'];
                        $vgenhashed = $vretrievepwd['EncryptedPassword'];
                        $password = $vgenpassword;
                        $changePassword = $casinocontroller->ResetPasswordMG($terminalcode, $vgenpassword, $serviceid);
                        $changePasswordvip = $casinocontroller->ResetPasswordMG($terminalcode.'VIP', $vgenpassword, $serviceid);
                         if ($changePassword && $changePasswordvip) {
                                        $result4 = $terminalservicesModel->updateterminalpassword($password, $vgenhashed, $terminalid['TerminalID'],$terminalidvip['TerminalID'], $serviceid);
                                        if ($result4 == 1) {
                                            $result5 = $updbatchpwd = $genpasswordtbModel->updateGenPwdBatch($siteid['SiteID'], $vgenpwdid);
                                            $Source = ';Source[' .$source. ']';
                                            $transdetails = 'ChangePass' .' - '. $terminal .''. $Source;
                                            $logtoAuditrail = $auditTrailModel->logEvent($transdetails,111);
                                            if ($logtoAuditrail)
                                            {
                                                $transMsg = 'Terminal Password Successfully Changed.';
                                                $errCode = 0;
                                                 $data = CommonController::changepassword($transMsg, $errCode); 
                                            }
                                         }
                                     } 
                        }
                else {
                        $transMsg = 'Invalid Login.';
                        $errCode = 4;
                        $data = CommonController::changepassword($transMsg, $errCode);                                               
                        }
                }
                else {
                        $transMsg = 'Change Terminal Password: No available site to get plain and encrypted password.';
                        $errCode = 36;
                     }
                }
                elseif ($usermode==1){
                $vprovidername = "RTG2";
                                        $MID = $memberservices->getMIDbyLogin($login, $serviceid);
                                        if($MID){
                                        $genpassresult = $genpasswordubModel->getInactivePasswordBatchInfo();
                                             if($genpassresult==false)
                                                {   
                                                                $transMsg = 'Error.';
                                                                $errCode = 8; 
                                                                $data = CommonController::changepassword($transMsg, $errCode);
                                                                $apisuccess = 0;
                                                                //Call API Change Password again to revert back to original password
                                                                //$vapiResult = $casinocontroller->changePassword($serviceid, $userName, $newpassword, $password, $serviceID);
                                                }
                                        $genpassbatchid = $genpassresult['GeneratedPasswordBatchID'];
                                        $newpassword = $genpassresult['PlainPassword'];
                                        $hashednewpassword = $genpassresult['EncryptedPassword'];
                                        $checkMS = $memberservices->CheckMemberService($MID['MID'], $serviceid);
                                            if(!empty($checkMS))
                                            {
                                                $password = $checkMS['ServicePassword'];


                                                if(isset($password))
                                                {

                                                    $vapiResult = $casinocontroller->changePassword($serviceid, 1, $login, $password, $newpassword); 
                          
                                                    if($vapiResult['changePlayerPWResult'][0] == 1)
                                                     {
                                                            $isMemberServicesUpdated = $memberservices->updateMemberServicesUBPassword($newpassword, 
                                                            $hashednewpassword, $MID['MID'], $serviceid, $genpassbatchid);
                                                           
                                                            if($isMemberServicesUpdated)
                                                            {
                                                                $apisuccess = 1;  
                                                                $transMsg = 'Password Successfully Changed';
                                                                $errCode = 0;
                                                                $Source = ';Source[' .$source. ']';
                                                                $transdetails = 'ChangePass' .' - '. $login .''. $Source;
                                                                $logtoAuditrail = $auditTrailModel->logEvent($transdetails,111);
                                                                $data = CommonController::changepassword($transMsg, $errCode);
                                                                
                                                            }
                                                            else
                                                            {   
                                                                $transMsg = 'Error.';
                                                                $errCode = 8; 
                                                                $data = CommonController::changepassword($transMsg, $errCode);
                                                                $apisuccess = 0;
                                                                //Call API Change Password again to revert back to original password
                                                                //$vapiResult = $casinocontroller->changePassword($serviceid, $userName, $newpassword, $password, $serviceID);
                                                            }
                                                            
                                                     }
                                                     else
                                                     {
                                                        $apisuccess = 0;
                                                     }
                                                }
                                                else
                                                {
                                                    $apisuccess = 0;
                                                }
                                            }
                                            else
                                            {

                                                $apisuccess = 0;
                                            }
                                } else {
                                    $transMsg = 'Login or Card not found';
                                    $errCode = 5;
                                    $data = CommonController::changepassword($transMsg, $errCode);
                                        }                                               
                            }
                 else{
                      $transMsg = 'Invalid Usermode.';
                      $errCode = 12; 
                      $data = CommonController::changepassword($transMsg, $errCode);
                }
                
                 }
                 else{
                      $transMsg = 'Invalid Source.';
                      $errCode = 4; 
                      $data = CommonController::changepassword($transMsg, $errCode);
                 }
            } else {
                $errCode = 4;
                if (empty($source)) {
                    $transMsg = 'Source must not be blank.';
                }
                if (empty($serviceid)) {
                    $transMsg = 'ServiceID must not be blank.';
                }
                if (empty($login)) {
                    $transMsg = 'Login must not be blank';
                }
                if (empty($usermode)) {
                    $transMsg = 'Usermode must not be blank';
                }

                $data = CommonController::changepassword($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.';
                    break;
                case 2:
                    $transMsg = 'Request time out.';
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.';
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }

        $message = "[ChangePassword] Token: " . $this->_tkn . ", Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);

        $this->_sendResponse(200, $data);
    }

}

?>
